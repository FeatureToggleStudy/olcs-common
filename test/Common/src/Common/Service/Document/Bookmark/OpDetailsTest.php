<?php

namespace CommonTest\Service\Document\Bookmark;

use Common\Service\Document\Bookmark\OpDetails;

/**
 * OpDetails test
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class OpDetailsTest extends \PHPUnit_Framework_TestCase
{
    public function testGetQueryContainsExpectedKeys()
    {
        $bookmark = new OpDetails();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertEquals('Licence', $query['service']);
        $this->assertEquals(['id' => 123], $query['data']);
    }

    public function testRenderValidDataProvider()
    {
        return array(
            array(
                "Mr Testy Test\nTesting Test Limited\nT/A: Trading Test Limited \n" .
                "Test\nTest Place\nTest\nTesting\ntest",
                array(
                    'organisation' => array(
                        'name' => 'Testing Test Limited',
                        'tradingNames' => array(
                            array(
                                'name' => 'Trading Test Limited'
                            )
                        ),
                    ),
                    'correspondenceCd' => array(
                        'fao' => 'Mr Testy Test',
                        'address' => array(
                            'addressLine1' => 'Test',
                            'addressLine2' => 'Test Place',
                            'addressLine3' => '',
                            'addressLine4' => 'Test',
                            'town' => 'Testing',
                            'postcode' => 'test'
                        )
                    )
                )
            ),
            array(
                "Testing Test Limited\n" .
                "Test\nTest Place\nTest\nTesting\ntest",
                array(
                    'organisation' => array(
                        'name' => 'Testing Test Limited',
                        'tradingNames' => array(),
                    ),
                    'correspondenceCd' => array(
                        'fao' => '',
                        'address' => array(
                            'addressLine1' => 'Test',
                            'addressLine2' => 'Test Place',
                            'addressLine3' => '',
                            'addressLine4' => 'Test',
                            'town' => 'Testing',
                            'postcode' => 'test'
                        )
                    )
                )
            )
        );
    }

    /**
     * @dataProvider testRenderValidDataProvider
     */
    public function testRender($expected, $results)
    {
        $bookmark = new OpDetails();
        $bookmark->setData($results);

        $this->assertEquals($expected, $bookmark->render());
    }
}
