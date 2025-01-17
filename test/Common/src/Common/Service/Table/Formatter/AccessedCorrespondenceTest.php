<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\AccessedCorrespondence;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Common\Service\Table\Formatter\AccessedCorrespondence
 */
class AccessedCorrespondenceTest extends MockeryTestCase
{
    /**
     * @dataProvider formatProvider
     */
    public function testFormat($data, $isNew, $expected)
    {
        $sm = m::mock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $sm->shouldReceive('get->fromRoute')
            ->with(
                'correspondence/access',
                array(
                    'correspondenceId' => $data['correspondence']['id'],
                )
            )
            ->andReturn('LICENCE_URL');

        if ($isNew) {
            $sm->shouldReceive('get->translate')->once()->andReturn('unit_New');
        }

        static::assertEquals($expected, AccessedCorrespondence::format($data, array(), $sm));
    }

    public function formatProvider()
    {
        return [
            [
                'data' => [
                    'correspondence' => [
                        'id' => 1,
                        'accessed' => 'N',
                        'document' => [
                            'description' => 'Description',
                        ],
                    ],
                ],
                'isNew' => true,
                'expect' => '<a class="strong" href="LICENCE_URL"><b>Description</b></a>' .
                    '<span class="status green">unit_New</span> ',
            ],
            [
                'data' => [
                    'correspondence' => [
                        'id' => 1,
                        'accessed' => 'Y',
                        'document' => [
                            'description' => 'Description',
                        ],
                    ],
                ],
                'isNew' => false,
                'expect' => '<a class="strong" href="LICENCE_URL"><b>Description</b></a>',
            ],
        ];
    }
}
