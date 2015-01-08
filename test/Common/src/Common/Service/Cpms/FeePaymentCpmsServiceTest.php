<?php

/**
 * CPMS Fee Payment Service Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace CommonTest\Service\Cpms;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Cpms\FeePaymentCpmsService;
use Common\Service\Cpms\PaymentNotFoundException;
use Common\Service\Cpms\PaymentInvalidStatusException;
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

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        return parent::setUp();
    }

    public function testInitiateCardRequest()
    {
        $sut = new FeePaymentCpmsService();

        $params = [
            'customer_reference' => 'cust_ref',
            'scope' => 'CARD',
            'disable_redirection' => true,
            'redirect_uri' => 'redirect_url',
            'payment_data' => [
                [
                    'amount' => (double)525.25,
                    'sales_reference' => 'sales_ref',
                    'product_reference' => 'GVR_APPLICATION_FEE'
                ]
            ]
        ];

        $client = m::mock()
            ->shouldReceive('post')
            ->with('/api/payment/card', 'CARD', $params)
            ->andReturn(
                [
                    'redirection_data' => 'guid_123'
                ]
            )
            ->getMock();

        $sl = m::mock('Zend\ServiceManager\ServiceLocatorInterface')
            ->shouldReceive('get')
            ->with('cpms\service\api')
            ->andReturn($client)
            ->shouldReceive('get')
            ->with('Entity\Payment')
            ->andReturn(
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
            )
            ->shouldReceive('get')
            ->with('Entity\FeePayment')
            ->andReturn(
                m::mock()
                ->shouldReceive('save')
                ->with(
                    [
                        'payment' => 321,
                        'fee' => 1,
                        'feeValue' => 525.25
                    ]
                )
                ->getMock()
            )
            ->getMock();

        $sut->setServiceLocator($sl);

        $fees = [
            [
                'id' => 1,
                'amount' => 525.25
            ]
        ];
        $sut->initiateCardRequest('cust_ref', 'sales_ref', 'redirect_url', $fees);
    }

    public function testHandleResponseWithInvalidPayment()
    {
        $sut = new FeePaymentCpmsService();

        $sl = m::mock('Zend\ServiceManager\ServiceLocatorInterface')
            ->shouldReceive('get')
            ->with('cpms\service\api')
            ->andReturn(m::mock())
            ->shouldReceive('get')
            ->with('Entity\Payment')
            ->andReturn(
                m::mock()
                ->shouldReceive('getDetails')
                ->with('payment_reference')
                ->andReturn(false)
                ->getMock()
            )
            ->getMock();

        $sut->setServiceLocator($sl);

        $data = [
            'receipt_reference' => 'payment_reference'
        ];

        try {
            $sut->handleResponse($data, []);
        } catch (PaymentNotFoundException $ex) {
            $this->assertEquals('Payment not found', $ex->getMessage());
            return;
        }

        $this->fail('Expected exception not raised');
    }

    public function testHandleResponseWithInvalidPaymentStatus()
    {
        $sut = new FeePaymentCpmsService();

        $sl = m::mock('Zend\ServiceManager\ServiceLocatorInterface')
            ->shouldReceive('get')
            ->with('cpms\service\api')
            ->andReturn(m::mock())
            ->shouldReceive('get')
            ->with('Entity\Payment')
            ->andReturn(
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
            )
            ->getMock();

        $sut->setServiceLocator($sl);

        $data = [
            'receipt_reference' => 'payment_reference'
        ];

        try {
            $sut->handleResponse($data, []);
        } catch (PaymentInvalidStatusException $ex) {
            $this->assertEquals('Invalid payment status: bad_status', $ex->getMessage());
            return;
        }

        $this->fail('Expected exception not raised');
    }

    public function testHandleResponseWithValidPaymentStatus()
    {
        $sut = new FeePaymentCpmsService();

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
        $client = m::mock()
            ->shouldReceive('put')
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
            'paymentMethod' => FeePaymentEntityService::METHOD_CARD_OFFLINE,
            'receivedAmount' => 525.33
        ];

        $mockFeeListener = m::mock();
        $mockFeeListener->shouldReceive('trigger')
            ->with(1, FeeListenerService::EVENT_PAY);

        $sl = m::mock('Zend\ServiceManager\ServiceLocatorInterface')
            ->shouldReceive('get')
            ->with('cpms\service\api')
            ->andReturn($client)
            ->shouldReceive('get')
            ->with('Entity\Payment')
            ->andReturn(
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
                ->with(123, PaymentEntityService::STATUS_PAID)
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('Helper\Date')
            ->andReturn(
                m::mock()
                ->shouldReceive('getDate')
                ->with('Y-m-d H:i:s')
                ->andReturn('2014-12-30 01:20:30')
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('Entity\Fee')
            ->andReturn(
                m::mock()
                ->shouldReceive('forceUpdate')
                ->with(1, $saveData)
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('Listener\Fee')
            ->andReturn($mockFeeListener)
            ->getMock();

        $sut->setServiceLocator($sl);

        $fees = [
            [
                'amount' => 525.33,
                'id' => 1
            ]
        ];
        $resultStatus = $sut->handleResponse($data, $fees);

        $this->assertEquals(
            PaymentEntityService::STATUS_PAID, $resultStatus
        );
    }

    /**
     * @dataProvider nonSuccessfulStatusProvider
     */
    public function testHandleResponseWithNonSuccessfulPaymentStatus($code, $status)
    {
        $sut = new FeePaymentCpmsService();

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
        $client = m::mock()
            ->shouldReceive('put')
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

        $sl = m::mock('Zend\ServiceManager\ServiceLocatorInterface')
            ->shouldReceive('get')
            ->with('cpms\service\api')
            ->andReturn($client)
            ->shouldReceive('get')
            ->with('Entity\Payment')
            ->andReturn(
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
            )
            ->getMock();

        $sut->setServiceLocator($sl);

        $resultStatus = $sut->handleResponse($data, []);

        $this->assertEquals($status, $resultStatus);
    }

    public function nonSuccessfulStatusProvider()
    {
        return [
            [807, PaymentEntityService::STATUS_FAILED],
            [802, PaymentEntityService::STATUS_CANCELLED]
        ];
    }

    public function testHandleResponseWithUnhandledStatus()
    {
        $sut = new FeePaymentCpmsService();

        $sut->setLogger(
            m::mock('Zend\Log\LoggerInterface')
            ->shouldReceive('log')
            ->getMock()
        );

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
        $client = m::mock()
            ->shouldReceive('put')
            ->with('/api/gateway/payment_reference/complete', 'CARD', $data)
            ->shouldReceive('get')
            ->with('/api/payment/payment_reference', 'QUERY_TXN', $queryData)
            ->andReturn(
                [
                    'payment_status' => [
                        'code' => 12345
                    ]
                ]
            )
            ->getMock();

        $sl = m::mock('Zend\ServiceManager\ServiceLocatorInterface')
            ->shouldReceive('get')
            ->with('cpms\service\api')
            ->andReturn($client)
            ->shouldReceive('get')
            ->with('Entity\Payment')
            ->andReturn(
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
            )
            ->getMock();

        $sut->setServiceLocator($sl);

        $resultStatus = $sut->handleResponse($data, []);

        $this->assertEquals(null, $resultStatus);
    }

    public function testRecordCashPayment()
    {
        $sut = new FeePaymentCpmsService();

        $params = [
            'customer_reference' => 'cust_ref',
            'scope' => 'CASH',
            'total_amount' => (double)1234.56,
            'payment_data' => [
                [
                    'amount' => (double)1234.56,
                    'sales_reference' => 'sales_ref',
                    'product_reference' => 'GVR_APPLICATION_FEE',
                    'payer_details' => 'Payer',
                    'payment_reference' => [
                        'slip_number' => 123456,
                        'receipt_date' => '07-01-2015',
                    ],
                ]
            ]
        ];

        $client = m::mock()
            ->shouldReceive('post')
            ->with('/api/payment/cash', 'CASH', $params)
            ->andReturn(
                [
                    'code' => '000',
                    'message' => 'Success',
                    'receipt_reference' => 'unique_reference'
                ]
            )
            ->getMock();

        $this->sm->setService('cpms\service\api', $client);
        $this->sm->setService(
            'Entity\Fee',
            m::mock()
            ->shouldReceive('forceUpdate')
            ->with(
                1,
                [
                    'feeStatus'      => 'lfs_pd', //FeeEntityService::STATUS_PAID
                    'receivedDate'   => '07-01-2015',
                    'receiptNo'      => 'unique_reference',
                    'paymentMethod'  => 'fpm_cash', //FeePaymentEntityService::METHOD_CASH
                    'receivedAmount' => '1234.56',
                    'payer'          => 'Payer',
                    'slipNo'         => '123456',
                ]
            )
            //->withAnyArgs()
            ->getMock()
        );
        $this->sm->setService(
            'Listener\Fee',
            m::mock()
            ->shouldReceive('trigger')
            ->with(1, FeeListenerService::EVENT_PAY)
            ->getMock()
        );

        $sut->setServiceLocator($this->sm);

        $fee = [
            'id' => 1,
            'amount' => 1234.56
        ];

        $result = $sut->recordCashPayment(
            $fee,
            'cust_ref',
            'sales_ref',
            '1234.56',
            ['day' => '07', 'month' => '01', 'year' => '2015'],
            'Payer',
            '123456'
        );

        $this->assertTrue($result);
    }

    /**
     * @expectedException Common\Service\Cpms\PaymentInvalidAmountException
     */
    public function testRecordCashPaymentPartPaymentThrowsException()
    {
        $sut = new FeePaymentCpmsService();

        $fee = [
            'id' => 1,
            'amount' => 1234.56
        ];
        $sut->recordCashPayment(
            $fee,
            'cust_ref',
            'sales_ref',
            '234.56', // not enough!
            ['day' => '07', 'month' => '01', 'year' => '2015'],
            'Payer',
            '123456'
        );
    }

    public function testRecordCashPaymentFailureReturnsFalse()
    {
        $sut = new FeePaymentCpmsService();

        $client = m::mock()
            ->shouldReceive('post')
            ->with('/api/payment/cash', 'CASH', m::any())
            ->andReturn(
                [   // error responses aren't well documented
                    'code' => 'xxx',
                    'message' => 'error message',
                ]
            )
            ->getMock();

        $this->sm->setService('cpms\service\api', $client);
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

        $sut->setServiceLocator($this->sm);

        $fee = [
            'id' => 1,
            'amount' => 1234.56
        ];

        $result = $sut->recordCashPayment(
            $fee,
            'cust_ref',
            'sales_ref',
            '1234.56',
            ['day' => '07', 'month' => '01', 'year' => '2015'],
            'Payer',
            '123456'
        );

        $this->assertFalse($result);
    }
}
