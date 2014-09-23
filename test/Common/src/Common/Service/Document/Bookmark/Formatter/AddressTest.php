<?php

namespace CommonTest\Service\Document\Bookmark\Formatter;

use Common\Service\Document\Bookmark\Formatter\Address;

class AddressTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider addressProvider
     */
    public function testFormat($input, $expected)
    {
        $this->assertEquals(
            $expected,
            Address::format($input)
        );
    }

    public function addressProvider()
    {
        return [
            [
                [
                    'addressLine1' => 'Line 1',
                    'addressLine2' => 'Line 2',
                    'addressLine3' => 'Line 3',
                    'addressLine4' => 'Line 4',
                    'town' => 'Leeds',
                    'postcode' => 'LS9 6NF'
                ],
                "Line 1\nLine 2\nLine 3\nLine 4\nLeeds\nLS9 6NF"
            ], [
                [
                    'addressLine1' => 'Line 1',
                    'addressLine2' => '',
                    'addressLine4' => 'Line 4',
                    'town' => 'Leeds',
                    'postcode' => 'LS9 6NF'
                ],
                "Line 1\nLine 4\nLeeds\nLS9 6NF"
            ]
        ];
    }
}
