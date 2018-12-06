<?php

namespace CommonTest\Filter;

use Common\Filter\StripSpaces;

class StripSpacesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideFilter
     *
     * @param $input
     * @param $expected
     */
    public function testFilter($input, $expected)
    {
        $sut = new StripSpaces();
        $this->assertEquals($expected, $sut->filter($input));
    }

    /**
     * @return array
     */
    public function provideFilter()
    {
        return [
            [null, null],
            ['string', 'string'],
            [' string', 'string'],
            ['string ', 'string'],
            ['st ring ', 'string'],
            ['  st ri   n g   ', 'string'],
            [['  st ri   n g   ', ' s  tri ng2  '], ['string', 'string2']],
        ];
    }
}
