<?php

namespace CommonTest\Filter;

use Common\Filter\DateSelectNullifier;

/**
 * Class DateSelectNullifierTest
 * @package CommonTest\Filter
 */
class DateSelectNullifierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideFilter
     * @param $input
     * @param $output
     */
    public function testFilter($input, $output)
    {
        $sut = new DateSelectNullifier();
        $this->assertEquals($output, $sut->filter($input));
    }

    /**
     * @return array
     */
    public function provideFilter()
    {
        return [
            [null, null],
            ['string', null],
            [['day'=>'', 'year'=>'', 'month'=>''], null],
            [['day'=>'04', 'year'=>'2012', 'month'=>''], null],
            [['day'=>'04', 'year'=>'2012', 'month'=>'10'], '2012-10-04'],
        ];
    }
}
