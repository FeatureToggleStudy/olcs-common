<?php

/**
 * CPMS Fee Payment Service Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace CommonTest\Service\Cpms;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Cpms as CpmsService;
use Common\Service\Entity\PaymentEntityService;
use Common\Service\Entity\FeeEntityService;
use Common\Service\Entity\FeePaymentEntityService;
use Mockery as m;
use Common\Service\Listener\FeeListenerService;
use CommonTest\Traits\MockDateTrait;
use CommonTest\Bootstrap;

/**
 * CPMS Fee Payment Service Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class FeePaymentCpmsServiceTest extends MockeryTestCase
{
    use MockDateTrait;

    protected $sm;

    protected $sut;

    protected $client;

    /**
     * @var \Zend\Log\Writer\Mock
     */
    protected $logWriter;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->sut = new CpmsService\FeePaymentCpmsService();
        $this->sut->setServiceLocator($this->sm);
        $this->mockDate('2015-01-21');

        // Mock the logger
        $this->logWriter = new \Zend\Log\Writer\Mock();
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($this->logWriter);
        $this->sut->setLogger($logger);

        // Mock the CPMS client
        $this->client = m::mock()
            ->shouldReceive('getOptions')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getDomain')
                    ->andReturn('fake-domain')
                    ->getMock()
            )
            ->getMock();
        $this->sm->setService('cpms\service\api', $this->client);

        return parent::setUp();
    }

    public function testInitiateCardRequest()
    {
        $this->mockDate('2015-01-12');
        $fees = [
            [
                'id' => 1,
                'amount' => 525.25,
                'feeType' => [
                    'accrualRule' => [
                        'id' => 'acr_immediate',
                        // Common\Service\Data\FeeTypeDataService::ACCRUAL_RULE_IMMEDIATE
                    ]
                ],
            ],
            [
                'id' => 2,
                'amount' => 125.25,
                'feeType' => [
                    'accrualRule' => [
                        'id' => 'acr_licence_start',
                         // Common\Service\Data\FeeTypeDataService::ACCRUAL_RULE_LICENCE_START
                    ]
                ],
                'licence' => ['id' => 7, 'inForceDate' => '2014-12-25'],
            ],
        ];

        $params = [
            'customer_reference' => 'cust_ref',
            'scope' => 'CARD',
            'disable_redirection' => true,
            'redirect_uri' => 'redirect_url',
            'payment_data' => [
                [
                    'amount' => (double)525.25,
                    'sales_reference' => '1',
                    'product_reference' => 'GVR_APPLICATION_FEE',
                    'payment_reference' => [
                        'rule_start_date' => '2015-01-12',
                    ],
                ],
                [
                    'amount' => (double)125.25,
                    'sales_reference' => '2',
                    'product_reference' => 'GVR_APPLICATION_FEE',
                    'payment_reference' => [
                        'rule_start_date' => '2014-12-25',
                    ],
                ]
            ],
            'cost_centre' => '12345,67890',
            'total_amount' => '650.50',
        ];

        $this->client->shouldReceive('post')
            ->with('/api/payment/card', 'CARD', $params)
            ->andReturn(
                [
                    'receipt_reference' => 'guid_123'
                ]
            )
            ->getMock();

        $this->sm->setService(
            'Entity\Payment',
            m::mock()
                ->shouldReceive('save')
                ->with(
                    [
                        'guid' => 'guid_123',
                        'status' => PaymentEntityService::STATUS_OUTSTANDING
                    ]
                )
                ->andReturn(
                    [
                        'id' => 321
                    ]
                )
                ->getMock()
        );

        $this->sm->setService(
            'Entity\FeePayment',
            m::mock()
                ->shouldReceive('save')
                ->with(
                    [
                        'payment' => 321,
                        'fee' => 1,
                        'feeValue' => 525.25
                    ]
                )
                ->shouldReceive('save')
                ->with(
                    [
                        'payment' => 321,
                        'fee' => 2,
                        'feeValue' => 125.25
                    ]
                )
                ->getMock()
        );

        $this->sm->setService(
            'Entity\Fee',
            m::mock()
                ->shouldReceive('forceUpdate')
                ->with(1, ['paymentMethod' => 'fpm_card_offline'])
                ->shouldReceive('forceUpdate')
                ->with(2, ['paymentMethod' => 'fpm_card_offline'])
                ->getMock()
        );

        $this->sut->initiateCardRequest('cust_ref', 'redirect_url', $fees);

        $this->assertCount(2, $this->logWriter->events);
        $this->assertEquals('Card payment request', $this->logWriter->events[0]['message']);
        $this->assertEquals('Card payment response', $this->logWriter->events[1]['message']);
    }

    /**
     * @expectedException Common\Service\Cpms\Exception\PaymentInvalidResponseException
     * @expectedExceptionMessage some kind of error
     */
    public function testInitiateCardRequestWithInvalidResponseThrowsException()
    {
        $this->client->shouldReceive('post')
            ->with('/api/payment/card', 'CARD', m::any())
            ->andReturn('some kind of error')
            ->getMock();

        $fees = [
            [
                'id' => 1,
                'amount' => 525.25
            ]
        ];

        $this->sut->initiateCardRequest('cust_ref', 'redirect_url', $fees);
    }

    public function testHandleResponseWithInvalidPayment()
    {
        $this->sm->setService(
            'Entity\Payment',
            m::mock()
                ->shouldReceive('getDetails')
                ->with('payment_reference')
                ->andReturn(false)
                ->getMock()
        );

        $data = [
            'receipt_reference' => 'payment_reference'
        ];

        try {
            $this->sut->handleResponse($data, 'PAYMENT_METHOD');
        } catch (CpmsService\Exception\PaymentNotFoundException $ex) {
            $this->assertEquals('Payment not found', $ex->getMessage());
            return;
        }

        $this->fail('Expected exception not raised');
    }

    public function testHandleResponseWithInvalidPaymentStatus()
    {
        $this->sm->setService(
            'Entity\Payment',
            m::mock()
                ->shouldReceive('getDetails')
                ->with('payment_reference')
                ->andReturn(
                    [
                        'status' => [
                            'id' => 'bad_status'
                        ]
                    ]
                )
                ->getMock()
        );

        $data = [
            'receipt_reference' => 'payment_reference'
        ];

        try {
            $this->sut->handleResponse($data, 'PAYMENT_METHOD');
        } catch (CpmsService\Exception\PaymentInvalidStatusException $ex) {
            $this->assertEquals('Invalid payment status: bad_status', $ex->getMessage());
            return;
        }

        $this->fail('Expected exception not raised');
    }

    public function testHandleResponseWithValidPaymentStatus()
    {
        $data = [
            'receipt_reference' => 'payment_reference'
        ];

        $queryData = [
            'required_fields' => [
                'payment' => [
                    'payment_status'
                ]
            ]
        ];
        $this->client->shouldReceive('put')
            ->with('/api/gateway/payment_reference/complete', 'CARD', $data)
            ->shouldReceive('get')
            ->with('/api/payment/payment_reference', 'QUERY_TXN', $queryData)
            ->andReturn(
                [
                    'payment_status' => [
                        'code' => 801
                    ]
                ]
            )
            ->getMock();

        $saveData = [
            'feeStatus' => FeeEntityService::STATUS_PAID,
            'receivedDate' => '2014-12-30 01:20:30',
            'receiptNo' => 'payment_reference',
            'paymentMethod' => 'PAYMENT_METHOD',
            'receivedAmount' => 525.33
        ];

        $mockFeeListener = m::mock();
        $mockFeeListener->shouldReceive('trigger')
            ->with(1, FeeListenerService::EVENT_PAY);

        $this->sm->setService(
            'Entity\Payment',
            m::mock()
                ->shouldReceive('getDetails')
                ->with('payment_reference')
                ->andReturn(
                    [
                        'id' => 123,
                        'status' => [
                            'id' => PaymentEntityService::STATUS_OUTSTANDING
                        ]
                    ]
                )
                ->shouldReceive('forceUpdate')
                ->with(
                    123,
                    [
                        'status' => PaymentEntityService::STATUS_PAID,
                        'completedDate' => '2014-12-30 01:20:30',
                    ]
                )
                ->once()
                ->getMock()
        );

        $this->sm->setService(
            'Helper\Date',
            m::mock()
                ->shouldReceive('getDate')
                ->with('Y-m-d H:i:s')
                ->andReturn('2014-12-30 01:20:30')
                ->getMock()
        );

        $this->sm->setService(
            'Entity\Fee',
            m::mock()
                ->shouldReceive('forceUpdate')
                ->with(1, $saveData)
                ->getMock()
        );

        $this->sm->setService('Listener\Fee', $mockFeeListener);

        $fees = [
            [
                'amount' => 525.33,
                'id' => 1
            ]
        ];

        $this->sm->setService(
            'Entity\FeePayment',
            m::mock()
                ->shouldReceive('getFeesByPaymentId')
                ->with(123)
                ->andReturn($fees)
                ->getMock()
        );

        $resultStatus = $this->sut->handleResponse($data, 'PAYMENT_METHOD');

        $this->assertEquals(
            PaymentEntityService::STATUS_PAID, $resultStatus
        );
    }

    /**
     * @dataProvider nonSuccessfulStatusProvider
     */
    public function testHandleResponseWithNonSuccessfulPaymentStatus($code, $status)
    {
        $data = [
            'receipt_reference' => 'payment_reference'
        ];

        $queryData = [
            'required_fields' => [
                'payment' => [
                    'payment_status'
                ]
            ]
        ];
        $this->client->shouldReceive('put')
            ->with('/api/gateway/payment_reference/complete', 'CARD', $data)
            ->shouldReceive('get')
            ->with('/api/payment/payment_reference', 'QUERY_TXN', $queryData)
            ->andReturn(
                [
                    'payment_status' => [
                        'code' => $code
                    ]
                ]
            )
            ->getMock();

        $this->sm->setService(
            'Entity\Payment',
            m::mock()
                ->shouldReceive('getDetails')
                ->with('payment_reference')
                ->andReturn(
                    [
                        'id' => 123,
                        'status' => [
                            'id' => PaymentEntityService::STATUS_OUTSTANDING
                        ]
                    ]
                )
                ->shouldReceive('setStatus')
                ->with(123, $status)
                ->getMock()
        );

        $resultStatus = $this->sut->handleResponse($data, 'PAYMENT_METHOD');

        $this->assertEquals($status, $resultStatus);
    }

    public function nonSuccessfulStatusProvider()
    {
        return [
            'cancellation' => [807, PaymentEntityService::STATUS_CANCELLED],
            'failure'      => [802, PaymentEntityService::STATUS_FAILED],
            'in progress'  => [800, PaymentEntityService::STATUS_FAILED],
        ];
    }

    public function testHandleResponseWithUnhandledStatus()
    {
        $data = [
            'receipt_reference' => 'payment_reference'
        ];

        $queryData = [
            'required_fields' => [
                'payment' => [
                    'payment_status'
                ]
            ]
        ];
        $this->client->shouldReceive('put')
            ->once()
            ->with('/api/gateway/payment_reference/complete', 'CARD', $data)
            ->shouldReceive('get')
            ->once()
            ->with('/api/payment/payment_reference', 'QUERY_TXN', $queryData)
            ->andReturn(
                [
                    'payment_status' => [
                        'code' => 12345
                    ]
                ]
            )
            ->getMock();

        $this->sm->setService(
            'Entity\Payment',
            m::mock()
                ->shouldReceive('getDetails')
                ->with('payment_reference')
                ->andReturn(
                    [
                        'id' => 123,
                        'status' => [
                            'id' => PaymentEntityService::STATUS_OUTSTANDING
                        ]
                    ]
                )
                ->getMock()
        );

        $resultStatus = $this->sut->handleResponse($data, 'PAYMENT_METHOD');

        $this->assertEquals(null, $resultStatus);
    }

    /**
     * @expectedException Common\Service\Cpms\Exception\StatusInvalidResponseException
     */
    public function testHandleResponseWithInvalidStatusResponse()
    {
        $data = [
            'receipt_reference' => 'payment_reference'
        ];

        $queryData = [
            'required_fields' => [
                'payment' => [
                    'payment_status'
                ]
            ]
        ];
        $this->client->shouldReceive('put')
            ->once()
            ->with('/api/gateway/payment_reference/complete', 'CARD', $data)
            ->shouldReceive('get')
            ->once()
            ->with('/api/payment/payment_reference', 'QUERY_TXN', $queryData)
            ->andReturn(['something_unexpected'])
            ->getMock();

        $this->sm->setService(
            'Entity\Payment',
            m::mock()
                ->shouldReceive('getDetails')
                ->with('payment_reference')
                ->andReturn(
                    [
                        'id' => 123,
                        'status' => [
                            'id' => PaymentEntityService::STATUS_OUTSTANDING
                        ]
                    ]
                )
                ->getMock()
        );

        $this->sut->handleResponse($data, 'PAYMENT_METHOD');
    }

    /**
     * @expectedException \Common\Service\Cpms\Exception
     */
    public function testHandleResponseWithInvalidGatewayData()
    {
        $data = [];
        $this->sut->handleResponse($data, 'PAYMENT_METHOD');
    }

    public function testRecordCashPaymentSuccess()
    {
        $params = [
            'customer_reference' => 'cust_ref',
            'scope' => 'CASH',
            'total_amount' => 1334.66,
            'payment_data' => [
                [
                    'amount' => (double)1234.56,
                    'sales_reference' => '1',
                    'product_reference' => 'GVR_APPLICATION_FEE',
                    'payer_details' => 'Payer',
                    'payment_reference' => [
                        'slip_number' => '123456',
                        'receipt_date' => '2015-01-07',
                        'rule_start_date' => null, // tested separately
                    ],
                ],
                [
                    'amount' => 100.10,
                    'sales_reference' => '2',
                    'product_reference' => 'GVR_APPLICATION_FEE',
                    'payer_details' => 'Payer',
                    'payment_reference' => [
                        'slip_number' => '123456',
                        'receipt_date' => '2015-01-07',
                        'rule_start_date' => null,
                    ],
                ]
            ],
            'cost_centre' => '12345,67890',
        ];

        $this->client->shouldReceive('post')
            ->with('/api/payment/cash', 'CASH', $params)
            ->andReturn(
                [
                    'code' => '000',
                    'message' => 'Success',
                    'receipt_reference' => 'unique_reference'
                ]
            )
            ->getMock();

        $this->sm->setService(
            'Entity\Fee',
            m::mock()
            ->shouldReceive('forceUpdate')
            ->once()
            ->with(
                1,
                [
                    'feeStatus'          => 'lfs_pd', //FeeEntityService::STATUS_PAID
                    'receivedDate'       => '2015-01-07',
                    'receiptNo'          => 'unique_reference',
                    'paymentMethod'      => 'fpm_cash', //FeePaymentEntityService::METHOD_CASH
                    'receivedAmount'     => '1234.56',
                    'payerName'          => 'Payer',
                    'payingInSlipNumber' => '123456',
                ]
            )
            ->shouldReceive('forceUpdate')
            ->once()
            ->with(
                2,
                [
                    'feeStatus'          => 'lfs_pd',
                    'receivedDate'       => '2015-01-07',
                    'receiptNo'          => 'unique_reference',
                    'paymentMethod'      => 'fpm_cash',
                    'receivedAmount'     => '100.10',
                    'payerName'          => 'Payer',
                    'payingInSlipNumber' => '123456',
                ]
            )
            ->getMock()
        );
        $this->sm->setService(
            'Listener\Fee',
            m::mock()
                ->shouldReceive('trigger')->with(1, FeeListenerService::EVENT_PAY)->once()
                ->shouldReceive('trigger')->with(2, FeeListenerService::EVENT_PAY)->once()
                ->getMock()
        );

        $this->sut->setServiceLocator($this->sm);

        $fee1 = ['id' => 1, 'amount' => 1234.56];
        $fee2 = ['id' => 2, 'amount' => 100.10];

        $result = $this->sut->recordCashPayment(
            array($fee1, $fee2),
            'cust_ref',
            '1334.66',
            ['day' => '07', 'month' => '01', 'year' => '2015'],
            'Payer',
            '123456'
        );

        $this->assertTrue($result);

        $this->assertCount(2, $this->logWriter->events);
        $this->assertEquals('Cash payment request', $this->logWriter->events[0]['message']);
        $this->assertEquals('Cash payment response', $this->logWriter->events[1]['message']);
    }

    /**
     * @expectedException Common\Service\Cpms\Exception\PaymentInvalidAmountException
     */
    public function testRecordCashPaymentPartPaymentThrowsException()
    {
        $fee = ['id' => 1, 'amount' => 1234.56];

        $this->sut->recordCashPayment(
            array($fee),
            'cust_ref',
            '234.56', // not enough!
            ['day' => '07', 'month' => '01', 'year' => '2015'],
            'Payer',
            '123456'
        );
    }

    public function testRecordCashPaymentFailureReturnsFalse()
    {
        $this->client->shouldReceive('post')
            ->with('/api/payment/cash', 'CASH', m::any())
            ->andReturn(
                [   // error responses aren't well documented
                    'code' => 'xxx',
                    'message' => 'error message',
                ]
            )
            ->getMock();

        $this->sm->setService(
            'Entity\Fee',
            m::mock()
            ->shouldReceive('forceUpdate')
            ->never()
            ->getMock()
        );
        $this->sm->setService(
            'Listener\Fee',
            m::mock()
            ->shouldReceive('trigger')
            ->never()
            ->getMock()
        );

        $this->sut->setServiceLocator($this->sm);

        $fee = ['id' => 1, 'amount' => 1234.56];

        $result = $this->sut->recordCashPayment(
            array($fee),
            'cust_ref',
            '1234.56',
            ['day' => '07', 'month' => '01', 'year' => '2015'],
            'Payer',
            '123456'
        );

        $this->assertFalse($result);
    }

    public function testRecordChequePayment()
    {
        $params = [
            'customer_reference' => 'cust_ref',
            'scope' => 'CHEQUE',
            'total_amount' => (double)1234.56,
            'payment_data' => [
                [
                    'amount' => (double)1234.56,
                    'sales_reference' => '1',
                    'product_reference' => 'GVR_APPLICATION_FEE',
                    'payer_details' => 'Payer',
                    'payment_reference' => [
                        'slip_number' => '123456',
                        'receipt_date' => '2015-03-10',
                        'cheque_number' => '234567',
                        'cheque_date' => '2015-03-01',
                        'rule_start_date' => null, // tested separately
                    ],
                ]
            ],
            'cost_centre' => '12345,67890',
        ];

        $this->client->shouldReceive('post')
            ->with('/api/payment/cheque', 'CHEQUE', $params)
            ->andReturn(
                [
                    'code' => '000',
                    'message' => 'Success',
                    'receipt_reference' => 'unique_reference'
                ]
            )
            ->getMock();

        $this->sm->setService(
            'Entity\Fee',
            m::mock()
            ->shouldReceive('forceUpdate')
            ->with(
                1,
                [
                    'feeStatus'          => 'lfs_pd', //FeeEntityService::STATUS_PAID
                    'receivedDate'       => '2015-03-10',
                    'receiptNo'          => 'unique_reference',
                    'paymentMethod'      => 'fpm_cheque', //FeePaymentEntityService::METHOD_CHEQUE
                    'receivedAmount'     => '1234.56',
                    'payerName'          => 'Payer',
                    'payingInSlipNumber' => '123456',
                    'chequePoNumber'     => '234567',
                    'chequePoDate'       => '2015-03-01',
                ]
            )
            ->getMock()
        );
        $this->sm->setService(
            'Listener\Fee',
            m::mock()
            ->shouldReceive('trigger')
            ->with(1, FeeListenerService::EVENT_PAY)
            ->getMock()
        );

        $this->sut->setServiceLocator($this->sm);

        $fee = ['id' => 1, 'amount' => 1234.56];

        $result = $this->sut->recordChequePayment(
            array($fee),
            'cust_ref',
            '1234.56',
            ['day' => '10', 'month' => '03', 'year' => '2015'],
            'Payer',
            '123456',
            '234567',
            ['day' => '01', 'month' => '03', 'year' => '2015']
        );

        $this->assertTrue($result);

        $this->assertCount(2, $this->logWriter->events);
        $this->assertEquals('Cheque payment request', $this->logWriter->events[0]['message']);
        $this->assertEquals('Cheque payment response', $this->logWriter->events[1]['message']);
    }

    /**
     * @expectedException Common\Service\Cpms\Exception\PaymentInvalidAmountException
     */
    public function testRecordChequePaymentPartPaymentThrowsException()
    {
        $fee = ['id' => 1, 'amount' => 1234.56];

        $this->sut->recordChequePayment(
            array($fee),
            'cust_ref',
            '234.56', // not enough!
            ['day' => '08', 'month' => '03', 'year' => '2015'],
            'Payer',
            '123456',
            '234567',
            ['day' => '01', 'month' => '03', 'year' => '2015']
        );
    }

    public function testRecordChequePaymentFailureReturnsFalse()
    {
        $this->client->shouldReceive('post')
            ->with('/api/payment/cheque', 'CHEQUE', m::any())
            ->andReturn(
                [
                    'code' => 'xxx',
                    'message' => 'error message',
                ]
            )
            ->getMock();

        $this->sm->setService(
            'Entity\Fee',
            m::mock()
            ->shouldReceive('forceUpdate')
            ->never()
            ->getMock()
        );
        $this->sm->setService(
            'Listener\Fee',
            m::mock()
            ->shouldReceive('trigger')
            ->never()
            ->getMock()
        );

        $this->sut->setServiceLocator($this->sm);

        $fee = ['id' => 1, 'amount' => 1234.56];

        $result = $this->sut->recordChequePayment(
            array($fee),
            'cust_ref',
            '1234.56',
            ['day' => '07', 'month' => '01', 'year' => '2015'],
            'Payer',
            '123456',
            '234567',
            ['day' => '02', 'month' => '01', 'year' => '2015']
        );

        $this->assertFalse($result);
    }

    public function testRecordPostalOrderPayment()
    {
        $params = [
            'customer_reference' => 'cust_ref',
            'scope' => 'POSTAL_ORDER',
            'total_amount' => (double)1234.56,
            'payment_data' => [
                [
                    'amount' => (double)1234.56,
                    'sales_reference' => '1',
                    'product_reference' => 'GVR_APPLICATION_FEE',
                    'payer_details' => 'Payer',
                    'payment_reference' => [
                        'slip_number' => '123456',
                        'receipt_date' => '2015-01-08',
                        'postal_order_number' => ['234567'], // array expected according to api docs
                        'rule_start_date' => null, // tested separately
                    ],
                ]
            ],
            'cost_centre' => '12345,67890',
        ];

        $this->client->shouldReceive('post')
            ->with('/api/payment/postal-order', 'POSTAL_ORDER', $params)
            ->andReturn(
                [
                    'code' => '000',
                    'message' => 'Success',
                    'receipt_reference' => 'unique_reference'
                ]
            )
            ->getMock();

        $this->sm->setService(
            'Entity\Fee',
            m::mock()
            ->shouldReceive('forceUpdate')
            ->with(
                1,
                [
                    'feeStatus'          => 'lfs_pd', //FeeEntityService::STATUS_PAID
                    'receivedDate'       => '2015-01-08',
                    'receiptNo'          => 'unique_reference',
                    'paymentMethod'      => 'fpm_po', //FeePaymentEntityService::METHOD_POSTAL_ORDER
                    'receivedAmount'     => '1234.56',
                    'payerName'          => 'Payer',
                    'payingInSlipNumber' => '123456',
                    'chequePoNumber'     => '234567',
                ]
            )
            ->getMock()
        );
        $this->sm->setService(
            'Listener\Fee',
            m::mock()
            ->shouldReceive('trigger')
            ->with(1, FeeListenerService::EVENT_PAY)
            ->getMock()
        );

        $this->sut->setServiceLocator($this->sm);

        $fee = ['id' => 1, 'amount' => 1234.56];

        $result = $this->sut->recordPostalOrderPayment(
            array($fee),
            'cust_ref',
            '1234.56',
            ['day' => '08', 'month' => '01', 'year' => '2015'],
            'Payer',
            '123456',
            '234567'
        );

        $this->assertTrue($result);

        $this->assertCount(2, $this->logWriter->events);
        $this->assertEquals('Postal order payment request', $this->logWriter->events[0]['message']);
        $this->assertEquals('Postal order payment response', $this->logWriter->events[1]['message']);
    }

    /**
     * @expectedException Common\Service\Cpms\Exception\PaymentInvalidAmountException
     */
    public function testRecordPostalOrderPaymentPartPaymentThrowsException()
    {
        $fee = ['id' => 1, 'amount' => 1234.56];

        $this->sut->recordPostalOrderPayment(
            array($fee),
            'cust_ref',
            '234.56', // not enough!
            ['day' => '08', 'month' => '01', 'year' => '2015'],
            'Payer',
            '123456',
            '234567'
        );
    }

    public function testRecordPostalOrderPaymentFailureReturnsFalse()
    {
        $this->client->shouldReceive('post')
            ->with('/api/payment/postal-order', 'POSTAL_ORDER', m::any())
            ->andReturn(
                [
                    'code' => 'xxx',
                    'message' => 'error message',
                ]
            )
            ->getMock();

        $this->sm->setService(
            'Entity\Fee',
            m::mock()
            ->shouldReceive('forceUpdate')
            ->never()
            ->getMock()
        );
        $this->sm->setService(
            'Listener\Fee',
            m::mock()
            ->shouldReceive('trigger')
            ->never()
            ->getMock()
        );

        $this->sut->setServiceLocator($this->sm);

        $fee = ['id' => 1, 'amount' => 1234.56];

        $result = $this->sut->recordPostalOrderPayment(
            array($fee),
            'cust_ref',
            '1234.56',
            ['day' => '07', 'month' => '01', 'year' => '2015'],
            'Payer',
            '123456',
            '234567'
        );

        $this->assertFalse($result);
    }

    /**
     * @dataProvider ruleStartDateProvider
     */
    public function testRuleStartDateCalculation($fee, $expectedDateStr)
    {
        $this->mockDate('2015-01-20');

        $this->assertEquals($expectedDateStr, $this->sut->getRuleStartDate($fee));
    }

    public function ruleStartDateProvider()
    {
        return [
            'immediate rule' => [
                [
                    'id' => 88,
                    'amount' => '99.99',
                    'feeType' => [
                        'accrualRule' => ['id' => 'acr_immediate']
                        // Common\Service\Data\FeeTypeDataService::ACCRUAL_RULE_IMMEDIATE
                    ],
                ],
                '2015-01-20'
            ],
            'licence start date rule' => [
                [
                    'id' => 89,
                    'amount' => '99.99',
                    'feeType' => [
                        'accrualRule' => ['id' => 'acr_licence_start']
                        // Common\Service\Data\FeeTypeDataService::ACCRUAL_RULE_LICENCE_START
                    ],
                    'licence' => [
                        'id' => 7,
                        'inForceDate' => '2015-02-28',
                    ]
                ],
                '2015-02-28'
            ],
            'continuation date rule' => [
                [
                    'id' => 90,
                    'amount' => '99.99',
                    'feeType' => [
                        'accrualRule' => ['id' => 'acr_continuation']
                        // Common\Service\Data\FeeTypeDataService::ACCRUAL_RULE_CONTINUATION
                    ],
                    'licence' => [
                        'id' => 7,
                        'expiryDate' => '2015-03-31',
                    ]
                ],
                '2015-04-01'
            ],
            'no accrualRule' => [
                [],
                null,
            ],
            'licence start with no inForceDate' => [
                [
                    'id' => 91,
                    'amount' => '99.99',
                    'feeType' => [
                        'accrualRule' => ['id' => 'acr_licence_start']
                    ],
                    'licence' => []
                ],
                null
            ],
            'continuation with no expiryDate' => [
                [
                    'id' => 92,
                    'amount' => '99.99',
                    'feeType' => [
                        'accrualRule' => ['id' => 'acr_continuation']
                    ],
                    'licence' => []
                ],
                null
            ],
            'invalid accrualRule' => [
                [
                    'id' => 93,
                    'amount' => '99.99',
                    'feeType' => [
                        'accrualRule' => ['id' => 'unknown']
                    ],
                ],
                null
            ],
        ];
    }

    /**
     * @dataProvider formatAmountValidAmountProvider
     * @param mixed $amount
     * @param string $expected
     */
    public function testFormatAmountValidAmounts($amount, $expected)
    {
        $this->assertEquals($expected, $this->sut->formatAmount($amount));
    }

    public function formatAmountValidAmountProvider()
    {
        return [
            [null, '0.00'],
            ['',    '0.00'],
            [false, '0.00'],
            [0, '0.00'],
            ['0', '0.00'],
            ['123', '123.00'],
            [456, '456.00'],
            [456.1, '456.10'],
            ['12345.67', '12345.67'],
            ['1234.56667', '1234.57'],
        ];
    }

    /**
     * @dataProvider formatAmountInvalidAmountProvider
     * @param mixed $amount
     * @expectedException \InvalidArgumentException
     */
    public function testFormatAmountInvalidAmounts($amount)
    {
        $result = $this->sut->formatAmount($amount);
    }

    public function formatAmountInvalidAmountProvider()
    {
        return [
            // anything non-empty and non-numeric is invalid
            'string' => ['foo'],
            'array'  => [array(123)],
            'object' => [new \Stdclass],
        ];
    }

    /**
     * @dataProvider isCardPaymentProvider
     * @param array $data
     * @param boolean $expected
     */
    public function testIsCardPayment($data, $expected)
    {
        $this->assertEquals($expected, $this->sut->isCardPayment($data));
    }

    /**
     * @return array
     */
    public function isCardPaymentProvider()
    {
        return [
            'card online' => [
                ['details' => ['paymentType' => FeePaymentEntityService::METHOD_CARD_ONLINE]],
                true,
            ],
            'card offline' => [
                ['details' => ['paymentType' => FeePaymentEntityService::METHOD_CARD_OFFLINE]],
                true,
            ],
            'not card' => [
                ['details' => ['paymentType' => FeePaymentEntityService::METHOD_CASH]],
                false,
            ],
            'invalid' => [
                [],
                false,
            ],
        ];
    }

    /**
     * @dataProvider hasOutstandingPaymentProvider
     * @param array $feeData
     * @param boolean $expected
     */
    public function testHasOutstandingPayments($feeData, $expected)
    {
        $this->assertEquals($expected, $this->sut->hasOutstandingPayment($feeData));
    }

    /**
     * @return array
     */
    public function hasOutstandingPaymentProvider()
    {
        return [
            'one outstanding' => [
                [
                    'id' => 1,
                    'feePayments' => [
                        [
                            'payment' => [
                                'id' => 1,
                                'status' => ['id' => 'pay_s_os'], //PaymentEntityService::STATUS_OUTSTANDING
                            ],
                        ]
                    ]
                ],
                true,
            ],
            'none outstanding' => [
                [
                    'id' => 1,
                    'feePayments' => [
                        [
                            'payment' => [
                                'id' => 1,
                                'status' => ['id' => 'pay_s_somethingelse'],
                            ],
                        ]
                    ]
                ],
                false,
            ],
            'two payments with one outstanding' => [
                [
                    'id' => 1,
                    'feePayments' => [
                        [
                            'payment' => [
                                'id' => 1,
                                'status' => ['id' => 'pay_s_cn'], //PaymentEntityService::STATUS_CANCELLED
                            ],
                        ],
                        [
                            'payment' => [
                                'id' => 2,
                                'status' => ['id' => 'pay_s_os'], //PaymentEntityService::STATUS_OUTSTANDING
                            ],
                        ]
                    ]
                ],
                true,
            ],
            'no payments' =>[
                [
                    'id' => 1,
                    'feePayments' => [],
                ],
                false,
            ],
        ];
    }

    public function testResolveOutstandingPaymentsSinglePaidFee()
    {
        $fee = [
            'id' => 1,
            'amount' => 1234.56,
            'feePayments' => [
                [
                    'payment' => [
                        'id' => 11,
                        'status' => ['id' => 'pay_s_os'], //PaymentEntityService::STATUS_OUTSTANDING
                        'guid' => 'payment_reference'
                    ],
                ]
            ],
            'paymentMethod' => ['id' => 'fpm_card_offline'], //FeePaymentEntityService::METHOD_CARD_OFFLINE]
        ];

        $this->client
            ->shouldReceive('get')
            ->with(
                '/api/payment/payment_reference',
                'QUERY_TXN',
                ['required_fields' => ['payment' => ['payment_status']]]
            )
            ->andReturn(
                [
                    'payment_status' => [
                        'code' => 801 // FeePaymentCpmsService::PAYMENT_SUCCESS
                    ]
                ]
            );

        $this->sm->setService(
            'Entity\FeePayment',
            m::mock()
                ->shouldReceive('getFeesByPaymentId')
                ->with(11)
                ->andReturn([$fee])
                ->getMock()
        );

        $this->mockDate('2015-03-09');

        $this->sm->setService(
            'Entity\Fee',
            m::mock()
                ->shouldReceive('forceUpdate')
                ->with(
                    1,
                    [
                        'feeStatus'      => FeeEntityService::STATUS_PAID,
                        'receivedDate'   => '2015-03-09',
                        'receiptNo'      => 'payment_reference',
                        'paymentMethod'  => 'fpm_card_offline',
                        'receivedAmount' => 1234.56
                    ]
                )
                ->getMock()
        );

        $this->sm->setService(
            'Listener\Fee',
            m::mock()
                ->shouldReceive('trigger')
                ->with(1, FeeListenerService::EVENT_PAY)
                ->getMock()
        );

        $this->sm->setService(
            'Entity\Payment',
            m::mock()
                ->shouldReceive('forceUpdate')
                ->with(
                    11,
                    [
                        'status' => PaymentEntityService::STATUS_PAID,
                        'completedDate' => '2015-03-09',
                    ]
                )
                ->getMock()
        );

        $this->assertTrue($this->sut->resolveOutstandingPayments($fee));
    }
}
