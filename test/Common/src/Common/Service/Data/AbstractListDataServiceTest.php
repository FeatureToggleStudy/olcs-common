<?php

namespace CommonTest\Service\Data;

use CommonTest\Service\Data\Stub\AbstractListDataServiceStub;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Common\Service\Data\AbstractListDataService
 */
class AbstractListDataServiceTest extends MockeryTestCase
{
    /** @var  AbstractListDataServiceStub */
    private $sut;

    public function setUp()
    {
        $this->sut = new AbstractListDataServiceStub();
    }

    public function testFormatDataForGroup()
    {
        $data = [
            [
                'parent' => [
                    'id' => 9001,
                ],
                'id' => 7001,
                'description' => 'unit_Desc7001',
            ],
            [
                'id' => 9001,
                'description' => 'unit_WithChilds',
            ],
            [
                'id' => 8001,
                'description' => 'unit_Desc',
            ],
            [
                'parent' => [
                    'id' => 9001,
                ],
                'id' => 7003,
                'description' => 'unit_Desc7003',
            ],
        ];

        $actual = $this->sut->formatDataForGroups($data);

        static::assertEquals(
            [
                8001 => [
                    'label' => 'unit_Desc',
                    'options' => [],
                ],
                9001 => [
                    'label' => 'unit_WithChilds',
                    'options' => [
                        7001 => 'unit_Desc7001',
                        7003 => 'unit_Desc7003',
                    ],
                ],
            ],
            $actual
        );
    }

    /**
     * @dataProvider dpTestFetchListOptions
     */
    public function testFetchListOptions($data, $useGroup, $expect)
    {
        $context = 'unit_Context';

        $this->sut->mockFetchListData = $data;

        $actual = $this->sut->fetchListOptions($context, $useGroup);

        static::assertEquals($expect, $actual);
    }

    public function dpTestFetchListOptions()
    {
        return [
            [
                'data' => null,
                'useGroup' => false,
                'expect' => [],
            ],
            [
                'data' => [
                    [
                        'id' => 'unit_Id',
                        'description' => 'unit_Desc',
                    ],
                ],
                'useGroup' => false,
                'expect' => [
                    'unit_Id' => 'unit_Desc',
                ],
            ],
            [
                'data' => [
                    [
                        'parent' => [
                            'id' => 9001,
                        ],
                        'id' => 7001,
                        'description' => 'unit_Desc7001',
                    ],
                    [
                        'id' => 9001,
                        'description' => 'unit_WithChilds',
                    ],
                ],
                'useGroup' => true,
                'expect' => [
                    9001 => [
                        'label' => 'unit_WithChilds',
                        'options' => [
                            7001 => 'unit_Desc7001',
                        ],
                    ],
                ]
            ],
        ];
    }
}
