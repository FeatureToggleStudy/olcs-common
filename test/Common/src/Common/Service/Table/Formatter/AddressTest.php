<?php

/**
 * Address formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\Address;

/**
 * Address formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AddressTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test the format method
     *
     * @group Formatters
     * @group AddressFormatter
     *
     * @dataProvider provider
     */
    public function testFormat($data, $column, $expected)
    {
        $mockTranslator = $this->getMock('\stdClass', array('translate'));

        $sm = $this->getMock('\stdClass', array('get'));
        $sm->expects($this->any())
            ->method('get')
            ->with('translator')
            ->will($this->returnValue($mockTranslator));

        $this->assertEquals($expected, Address::format($data, $column, $sm));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return array(
            array(
                array('addressLine1' => 'foo'), array(), 'foo'
            ),
            array(
                array('addressLine1' => 'foo', 'addressLine2' => 'bar'), array(), 'foo, bar'
            ),
            array(
                array('addressLine1' => 'foo', 'addressLine2' => 'bar', 'addressLine3' => ''), array(), 'foo, bar'
            ),
            array(
                array('addressLine1' => 'foo', 'addressLine2' => 'bar', 'addressLine3' => 'cake'),
                array(),
                'foo, bar'
            ),
            array(
                array(
                    'addressLine1' => 'foo',
                    'addressLine2' => 'bar',
                    'addressLine3' => 'cake',
                    'addressLine4' => 'fourth'
                ),
                array(),
                'foo, bar'
            ),
            array(
                array('addressLine1' => 'foo', 'addressLine2' => 'bar', 'addressLine3' => 'cake'),
                array('addressFields' => array('addressLine1')),
                'foo'
            )
        );
    }
}
