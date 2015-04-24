<?php

namespace CommonTest\Service\Document\Bookmark;

use Common\Service\Document\Bookmark\CaseworkerName;

/**
 * Case worker name test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class CaseworkerNameTest extends \PHPUnit_Framework_TestCase
{
    public function testGetQueryContainsExpectedKeys()
    {
        $bookmark = new CaseworkerName();
        $query = $bookmark->getQuery(['user' => 123]);

        $this->assertEquals('User', $query['service']);

        $this->assertEquals(
            [
                'id' => 123
            ],
            $query['data']
        );
    }

    public function testRender()
    {
        $bookmark = new CaseworkerName();
        $bookmark->setData(
            [
                'contactDetails' => [
                    'person' => [
                        'forename' => 'Bob',
                        'familyName' => 'Smith'
                    ]
                ]
            ]
        );

        $this->assertEquals(
            'Bob Smith',
            $bookmark->render()
        );
    }
}
