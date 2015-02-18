<?php

/**
 * Fee Payment Helper Service
 *
 * This exists as a fairly thin wrapper around CPMS’ own SDK
 * because it's otherwise still a *bit* verbose to just dump
 * in controllers. Also, logically we’re probably always going
 * to be paying fee objects, so tieing CPMS + fees together makes
 * sense from an OLCS perspective
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Common\Service\Cpms;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

use Common\Service\Entity\FeePaymentEntityService;
use Common\Service\Entity\PaymentEntityService;
use Common\Service\Entity\FeeEntityService;
use Common\Util\LoggerTrait;
use CpmsClient\Service\ApiService;
use Common\Service\Listener\FeeListenerService;
use Common\Service\Data\FeeTypeDataService;
use Olcs\Db\Traits\LoggerAwareTrait as OlcsLoggerAwareTrait;

/**
 * Fee Payment Helper Service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class FeePaymentCpmsService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait,
        LoggerTrait;

    const PAYMENT_SUCCESS      = 801;
    const PAYMENT_FAILURE      = 802;
    const PAYMENT_CANCELLATION = 807;

    const RESPONSE_SUCCESS = '000';

    const DATE_FORMAT = 'd-m-Y'; // CPMS' preferred date format

    // @TODO product ref shouldn't have to come from a whitelist...
    const PRODUCT_REFERENCE = 'GVR_APPLICATION_FEE';

    // @TODO this is a dummy value for testing purposes as cost_centre is now
    // a required parameter in cpms/payment-service. Awaiting further info on
    // what OLCS should pass for this field.
    const COST_CENTRE = '12345,67890';

    protected function getClient()
    {
        return $this->getServiceLocator()->get('cpms\service\api');
    }

    /**
     * @param string $customerReference usually organisation id
     * @param string $redirectUrl redirect back to here from payment gateway
     * @param array $fees
     *
     * @return array
     * @throws Common\Service\Cpms\PaymentInvalidResponseException on error
     */
    public function initiateCardRequest($customerReference, $redirectUrl, array $fees)
    {
        $paymentData = [];
        foreach ($fees as $fee) {
            $paymentData[] = [
                'amount' => $fee['amount'],
                'sales_reference' => (string)$fee['id'],
                'product_reference' => self::PRODUCT_REFERENCE,
                'payment_reference' => [
                    'rule_start_date' => $this->getRuleStartDate($fee),
                ],
            ];
        }
        $params = [
            // @NOTE CPMS rejects ints as 'missing', so we have to force a string...
            'customer_reference' => (string)$customerReference,
            'scope' => ApiService::SCOPE_CARD,
            'disable_redirection' => true,
            'redirect_uri' => $redirectUrl,
            'payment_data' => $paymentData,
            'cost_centre' => self::COST_CENTRE,
        ];

        $response = $this->getClient()->post('/api/payment/card', ApiService::SCOPE_CARD, $params);

        if (!is_array($response)
            || !isset($response['redirection_data'])
            || empty($response['redirection_data'])
        ) {
            throw new PaymentInvalidResponseException(json_encode($response));
        }

        $payment = $this->getServiceLocator()
            ->get('Entity\Payment')
            ->save(
                [
                    // yes, 'redirection_data' really is correct...
                    'guid' => $response['redirection_data'],
                    'status' => PaymentEntityService::STATUS_OUTSTANDING
                ]
            );

        foreach ($fees as $fee) {
            $this->getServiceLocator()
                ->get('Entity\FeePayment')
                ->save(
                    [
                        'payment' => $payment['id'],
                        'fee' => $fee['id'],
                        'feeValue' => $fee['amount']
                    ]
                );
        }

        return $response;
    }

    /**
     * Record a cash payment in CPMS
     *
     * @param string $customerReference
     * @param string $salesReference
     * @param float $amount
     * @param array $receiptDate (from DateSelect)
     * @param string $payer payer name
     * @param string $slipNo paying in slip number
     * @return boolean success
     */
    public function recordCashPayment(
        $fee,
        $customerReference,
        $amount,
        $receiptDate,
        $payer,
        $slipNo
    ) {
        // Partial payments are not supported. The form validation will normally catch
        // this but it relies on a hidden field so we have a secondary check here
        if ($fee['amount'] != $amount) {
            throw new PaymentInvalidAmountException("Amount must match the fee due");
        }

        $receiptDate   = $this->formatReceiptDate($receiptDate);
        $ruleStartDate = $this->getRuleStartDate($fee);
        $endPoint      = '/api/payment/cash';
        $scope         = ApiService::SCOPE_CASH;

        $params = [
            'customer_reference' => (string)$customerReference,
            'scope' => $scope,
            'total_amount' => $amount,
            'payment_data' => [
                [
                    'amount' => $amount,
                    'sales_reference' => (string)$fee['id'],
                    'product_reference' => self::PRODUCT_REFERENCE,
                    'payer_details' => $payer, // not sure this is supported for CASH payments
                    'payment_reference' => [
                        'slip_number' => (string)$slipNo,
                        'receipt_date' => $receiptDate,
                        'rule_start_date' => $ruleStartDate,
                    ],
                ]
            ],
            'cost_centre' => self::COST_CENTRE,
        ];

        $this->debug(
            'Cash payment request',
            [
                'method' => [
                    'location' => __METHOD__,
                    'data' => func_get_args()
                ],
                'endPoint' => $endPoint,
                'scope'    => $scope,
                'params'   => $params,
            ]
        );

        $response = $this->getClient()->post($endPoint, $scope, $params);

        $this->debug('Cash payment response', ['response' => $response]);

        if ($this->isSuccessfulResponse($response)) {
            $data = [
                'feeStatus'          => FeeEntityService::STATUS_PAID,
                'receivedDate'       => $receiptDate,
                'receiptNo'          => $response['receipt_reference'],
                'paymentMethod'      => FeePaymentEntityService::METHOD_CASH,
                'receivedAmount'     => $amount,
                'payerName'          => $payer,
                'payingInSlipNumber' => $slipNo,
            ];

            $this->updateFeeRecordAsPaid($fee['id'], $data);

            return true;
        }

        return false;
    }

    /**
     * Record a cheque payment in CPMS
     *
     * @param string $customerReference
     * @param string $salesReference
     * @param float $amount
     * @param array $receiptDate (from DateSelect)
     * @param string $payer payer name
     * @param string $slipNo paying in slip number
     * @param string $chequeNo cheque number
     * @return boolean success
     */
    public function recordChequePayment(
        $fee,
        $customerReference,
        $amount,
        $receiptDate,
        $payer,
        $slipNo,
        $chequeNo
    ) {
        // Partial payments are not supported
        if ($fee['amount'] != $amount) {
            throw new PaymentInvalidAmountException("Amount must match the fee due");
        }

        $receiptDate   = $this->formatReceiptDate($receiptDate);
        $ruleStartDate = $this->getRuleStartDate($fee);
        $endPoint      = '/api/payment/cheque';
        $scope         = ApiService::SCOPE_CHEQUE;

        $params = [
            'customer_reference' => (string)$customerReference,
            'scope' => $scope,
            'total_amount' => $amount,
            'payment_data' => [
                [
                    'amount' => $amount,
                    'sales_reference' => (string)$fee['id'],
                    'product_reference' => self::PRODUCT_REFERENCE,
                    'payer_details' => $payer,
                    'payment_reference' => [
                        'slip_number' => (string)$slipNo,
                        'receipt_date' => $receiptDate,
                        'cheque_number' => (string)$chequeNo,
                        'rule_start_date' => $ruleStartDate,
                    ],
                ]
            ],
            'cost_centre' => self::COST_CENTRE,
        ];

        $this->debug(
            'Cheque payment request',
            [
                'method' => [
                    'location' => __METHOD__,
                    'data' => func_get_args()
                ],
                'endPoint' => $endPoint,
                'scope'    => $scope,
                'params'   => $params,
            ]
        );

        $response = $this->getClient()->post($endPoint, $scope, $params);

        $this->debug('Cheque payment response', ['response' => $response]);

        if ($this->isSuccessfulResponse($response)) {
            $data = [
                'feeStatus'          => FeeEntityService::STATUS_PAID,
                'receivedDate'       => $receiptDate,
                'receiptNo'          => $response['receipt_reference'],
                'paymentMethod'      => FeePaymentEntityService::METHOD_CHEQUE,
                'receivedAmount'     => $amount,
                'payerName'          => $payer,
                'payingInSlipNumber' => $slipNo,
                'chequePoNumber'     => $chequeNo,
            ];

            $this->updateFeeRecordAsPaid($fee['id'], $data);

            return true;
        }

        return false;
    }

    /**
     * Record a Postal Order payment in CPMS
     *
     * @param string $customerReference
     * @param string $salesReference
     * @param float $amount
     * @param array $receiptDate (from DateSelect)
     * @param string $payer payer name
     * @param string $slipNo paying in slip number
     * @param string $poNo Postal Order number
     * @return boolean success
     */
    public function recordPostalOrderPayment(
        $fee,
        $customerReference,
        $amount,
        $receiptDate,
        $payer,
        $slipNo,
        $poNo
    ) {
        // Partial payments are not supported
        if ($fee['amount'] != $amount) {
            throw new PaymentInvalidAmountException("Amount must match the fee due");
        }

        $receiptDate   = $this->formatReceiptDate($receiptDate);
        $ruleStartDate = $this->getRuleStartDate($fee);
        $endPoint      = '/api/payment/postal-order';
        $scope         = ApiService::SCOPE_POSTAL_ORDER;

        $params = [
            'customer_reference' => (string)$customerReference,
            'scope' => $scope,
            'total_amount' => $amount,
            'payment_data' => [
                [
                    'amount' => $amount,
                    'sales_reference' => (string)$fee['id'],
                    'product_reference' => self::PRODUCT_REFERENCE,
                    'payer_details' => $payer,
                    'payment_reference' => [
                        'slip_number' => (string)$slipNo,
                        'receipt_date' => $receiptDate,
                        'postal_order_number' => [ $poNo ], // array!
                        'rule_start_date' => $ruleStartDate,

                    ],
                ]
            ],
            'cost_centre' => self::COST_CENTRE,
        ];


        $this->debug(
            'Postal order payment request',
            [
                'method' => [
                    'location' => __METHOD__,
                    'data' => func_get_args()
                ],
                'endPoint' => $endPoint,
                'scope'    => $scope,
                'params'   => $params,
            ]
        );

        $response = $this->getClient()->post($endPoint, $scope, $params);

        $this->debug('Postal order payment response', ['response' => $response]);

        if ($this->isSuccessfulResponse($response)) {
            $data = [
                'feeStatus'          => FeeEntityService::STATUS_PAID,
                'receivedDate'       => $receiptDate,
                'receiptNo'          => $response['receipt_reference'],
                'paymentMethod'      => FeePaymentEntityService::METHOD_POSTAL_ORDER,
                'receivedAmount'     => $amount,
                'payerName'          => $payer,
                'payingInSlipNumber' => $slipNo,
                'chequePoNumber'     => $poNo,
            ];

            $this->updateFeeRecordAsPaid($fee['id'], $data);

            return true;
        }

        return false;
    }

    /**
     * Helper function to update fee record and trigger the 'fee paid' event
     * after successful payment
     * @param int $feeId
     * @param array $data fee data
     * @return null
     */
    protected function updateFeeRecordAsPaid($feeId, $data)
    {
        $this->getServiceLocator()
            ->get('Entity\Fee')
            ->forceUpdate($feeId, $data);

        $this->getServiceLocator()->get('Listener\Fee')->trigger(
            $feeId, FeeListenerService::EVENT_PAY
        );
    }

    /**
     * Small helper to check if response was successful
     * (We require a successful response code AND a receipt reference)
     *
     * @param array $response response data
     * @return boolean
     */
    protected function isSuccessfulResponse($response)
    {
        return (
            is_array($response)
            && isset($response['code'])
            && $response['code'] === self::RESPONSE_SUCCESS
            && isset($response['receipt_reference'])
            && !empty($response['receipt_reference'])
        );
    }

    /**
     * Format a date as required by CPMS payment reference fields
     *
     * @param array|DateTime $date
     * @return string
     */
    public function formatReceiptDate($date)
    {
        if (is_array($date)) {
            $date = $this->getServiceLocator()->get('Helper\Date')->getDateObjectFromArray($date);
        }
        return $date->format(self::DATE_FORMAT);
    }

    /**
     * Determine 'rule start date' for a fee
     *
     * @see https://jira.i-env.net/browse/OLCS-6005 for business rules
     *
     * @param array $fee
     * @return string date in CPMS format <dd-mm-YYYY>
     */
    public function getRuleStartDate($fee)
    {
        if (isset($fee['feeType']['accrualRule']['id'])) {
            $rule = $fee['feeType']['accrualRule']['id'];
            $dateHelper = $this->getServiceLocator()->get('Helper\Date');
            switch ($rule) {
                case FeeTypeDataService::ACCRUAL_RULE_IMMEDIATE:
                    $date = $dateHelper->getDateObject();
                    return $date->format(self::DATE_FORMAT);
                case FeeTypeDataService::ACCRUAL_RULE_LICENCE_START:
                    $licenceStart = isset($fee['licence']['inForceDate'])
                        ? $fee['licence']['inForceDate']
                        : null;
                    if (!is_null($licenceStart)) {
                        $date = $dateHelper->getDateObject($licenceStart);
                        return $date->format(self::DATE_FORMAT);
                    }
                    break;
                case FeeTypeDataService::ACCRUAL_RULE_CONTINUATION:
                    // The licence continuation date + 1 day (according to calendar dates)
                    $licenceExpiry = isset($fee['licence']['expiryDate'])
                        ? $fee['licence']['expiryDate']
                        : null;
                    if (!is_null($licenceExpiry)) {
                        $date = $dateHelper->getDateObject($licenceExpiry);
                        $date->add(new \DateInterval('P1D'));
                        return $date->format(self::DATE_FORMAT);
                    }
                    break;
                default:
                    break;
            }
        }
        return null;
    }

    /**
     * @param array $data
     * @param string $paymentMethod FeePaymentEntityService::METHOD_CARD_OFFLINE|METHOD_CARD_OFFLINE
     */
    public function handleResponse($data, $paymentMethod)
    {
        $reference      = $data['receipt_reference'];
        $paymentService = $this->getServiceLocator()->get('Entity\Payment');
        $client         = $this->getServiceLocator()->get('cpms\service\api');

        /**
         * 1) Check what status we think this payment is currently in
         */
        $payment = $paymentService->getDetails($reference);

        if ($payment === false) {
            throw new PaymentNotFoundException('Payment not found');
        }

        if ($payment['status']['id'] !== PaymentEntityService::STATUS_OUTSTANDING) {
            throw new PaymentInvalidStatusException('Invalid payment status: ' . $payment['status']['id']);
        }

        /**
         * 2) Let CPMS know the response from the payment gateway
         *
         * We have to bundle up the response data verbatim as it can
         * vary per gateway implementation
         */
        $client->put('/api/gateway/' . $reference . '/complete', ApiService::SCOPE_CARD, $data);

        // do we need to handle an unexpected response here?

        /**
         * 3) Now actually look up the status of the transaction and
         * update our payment record & fee(s) accordingly
         */
        $params = [
            'required_fields' => [
                'payment' => [
                    'payment_status'
                ]
            ]
        ];

        $apiResponse = $client->get('/api/payment/' . $reference, ApiService::SCOPE_QUERY_TXN, $params);

        switch ($apiResponse['payment_status']['code']) {
            case self::PAYMENT_SUCCESS:
                $fees = $this->getServiceLocator()->get('Entity\FeePayment')
                    ->getFeesByPaymentId($payment['id']);
                foreach ($fees as $fee) {
                    $data = [
                        'feeStatus'      => FeeEntityService::STATUS_PAID,
                        'receivedDate'   => $this->getServiceLocator()->get('Helper\Date')->getDate('Y-m-d H:i:s'),
                        'receiptNo'      => $reference,
                        'paymentMethod'  => $paymentMethod,
                        'receivedAmount' => $fee['amount']
                    ];

                    $this->updateFeeRecordAsPaid($fee['id'], $data);
                }

                $paymentService->setStatus($payment['id'], PaymentEntityService::STATUS_PAID);
                $status = PaymentEntityService::STATUS_PAID;
                break;

            case self::PAYMENT_FAILURE:
                $status = PaymentEntityService::STATUS_CANCELLED;
                break;

            case self::PAYMENT_CANCELLATION:
                $status = PaymentEntityService::STATUS_FAILED;
                break;

            default:
                $this->log('Unknown CPMS payment_status: ' . $apiResponse['payment_status']['code']);
                $status = null;
                break;
        }

        if ($status !== null) {
            $paymentService->setStatus($payment['id'], $status);
            return $status;
        }
    }

    protected function debug($message, $data)
    {
         return $this->getLogger()->debug(
            $message,
            [
                'data' => array_merge(
                    [
                        'domain' => $this->getClient()->getOptions()->getDomain(),
                    ],
                    $data
                ),
            ]
        );
    }
}
