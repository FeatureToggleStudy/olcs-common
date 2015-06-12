<?php

/**
 * Business Type
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Data\Mapper\Lva;

use PHPUnit_Framework_TestCase;
use Common\Data\Mapper\Lva\BusinessType;

/**
 * Business Type
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessTypeTest extends PHPUnit_Framework_TestCase
{
    public function testMapFromResult()
    {
        $input = [
            'version' => 111,
            'type' => ['id' => 'org_t_rc']
        ];

        $output = BusinessType::mapFromResult($input);

        $expected = [
            'version' => 111,
            'data' => [
                'type' => 'org_t_rc'
            ]
        ];

        $this->assertEquals($expected, $output);
    }
}