<?php

/**
 * Transaction status formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Table\Formatter\TransactionStatus as Sut;
use PHPUnit_Framework_TestCase;

/**
 * Transaction status formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransactionStatusTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test the format method
     *
     * @group Formatters
     * @group FeeStatusFormatter
     *
     * @dataProvider provider
     */
    public function testFormat($data, $expected)
    {
        $this->assertEquals($expected, Sut::format($data));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            'outstanding' => [
                [
                    'transaction' => [
                        'status' => [
                            'id' => RefData::TRANSACTION_STATUS_OUTSTANDING,
                            'description' => 'outstanding',
                        ],
                    ],
                ],
                '<span class="status orange">outstanding</span>',
            ],
            'complete' => [
                [
                    'transaction' => [
                        'status' => [
                            'id' => RefData::TRANSACTION_STATUS_COMPLETE,
                            'description' => 'complete',
                        ],
                    ],
                ],
                '<span class="status green">complete</span>',
            ],
            'cancelled' => [
                [
                    'transaction' => [
                        'status' => [
                            'id' => RefData::TRANSACTION_STATUS_CANCELLED,
                            'description' => 'cancelled',
                        ],
                    ],
                ],
                '<span class="status red">cancelled</span>',
            ],
            'failed' => [
                [
                    'transaction' => [
                        'status' => [
                            'id' => RefData::TRANSACTION_STATUS_FAILED,
                            'description' => 'failed',
                        ],
                    ],
                ],
                '<span class="status red">failed</span>',
            ],
            'other' => [
                [
                    'transaction' => [
                        'status' => [
                            'id' => 'foo',
                            'description' => 'bar',
                        ],
                    ],
                ],
                '<span class="status grey">bar</span>',
            ],
        ];
    }
}
