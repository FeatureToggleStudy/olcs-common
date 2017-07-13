<?php

namespace CommonTest\View\Helper;

use Common\View\Helper\LicenceChecklist;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\I18n\View\Helper\Translate;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\Placeholder;
use Zend\View\Helper\ViewModel;
use Zend\View\HelperPluginManager;
use Common\RefData;

/**
 * @covers Common\View\Helper\LicenceChecklist
 */
class LicenceChecklistTest extends MockeryTestCase
{
    /**
     * @var LicenceChecklist
     */
    private $sut;

    public function setUp()
    {
        $this->sut = new LicenceChecklist();
    }

    /**
     * @dataProvider providerInvoke
     */
    public function testInvoke($type, $data, $expected)
    {
        $mockTranslator = m::mock(Translate::class)
            ->shouldReceive('__invoke')
            ->andReturnUsing(
                function ($arg) {
                    return $arg . '_translated';
                }
            )
            ->getMock();

        /** @var ServiceLocatorInterface | m\MockInterface $sm */
        $sm = m::mock(ServiceLocatorInterface::class);
        $sm->shouldReceive('get')->with('translate')->andReturn($mockTranslator);

        $sut = $this->sut->createService($sm);

        $this->assertEquals($sut->__invoke($type, $data), $expected);
    }

    public function providerInvoke()
    {
        return [
            [
                'foo',
                ['bar'],
                []
            ],
            [
                RefData::LICENCE_CHECKLIST_TYPE_OF_LICENCE,
                [
                    'typeOfLicence' => [
                        'operatingFrom' => 'GB',
                        'goodsOrPsv' => 'goods',
                        'licenceType' => 'type'
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.type-of-licence.operating-from_translated',
                            'header' => true
                        ],
                        [
                            'value' => 'GB'
                        ]
                    ],
                    [
                        [
                            'value' => 'continuations.type-of-licence.type-of-operator_translated',
                            'header' => true
                        ],
                        [
                            'value' => 'goods'
                        ],
                    ],
                    [
                        [
                            'value' => 'continuations.type-of-licence.type-of-licence_translated',
                            'header' => true
                        ],
                        [
                            'value' => 'type'
                        ]
                    ]
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_BUSINESS_TYPE,
                [
                    'businessType' => [
                        'typeOfBusiness' => 'ltd'
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.business-type.type-of-business_translated',
                            'header' => true
                        ],
                        [
                            'value' => 'ltd'
                        ]
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_BUSINESS_DETAILS,
                [
                    'businessDetails' => [
                        'companyNumber' => '12345678',
                        'companyName' => 'foo',
                        'organisationLabel' => 'bar',
                        'tradingNames' => 'trading,names'
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.business-details.company-number_translated',
                            'header' => true
                        ],
                        [
                            'value' => '12345678'
                        ]
                    ],
                    [
                        ['value' => 'bar', 'header' => true],
                        ['value' => 'foo']
                    ],
                    [
                        [
                            'value' => 'continuations.business-details.trading-names_translated',
                            'header' => true
                        ],
                        [
                            'value' => 'trading,names'
                        ]
                    ]
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_PEOPLE,
                [
                    'people' => [
                        'persons' => [
                            [
                                'name' => 'name1',
                                'birthDate' => 'birthDate1'
                            ],
                            [
                                'name' => 'name2',
                                'birthDate' => 'birthDate2'
                            ],
                        ],
                        'header' => 'foo',
                        'displayPersonCount' => 2
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.people-section.table.name_translated',
                            'header' => true
                        ],
                        [
                            'value' => 'continuations.people-section.table.date-of-birth_translated',
                            'header' => true
                        ],
                    ],
                    [
                        ['value' => 'name1'],
                        ['value' => 'birthDate1']
                    ],
                    [
                        ['value' => 'name2'],
                        ['value' => 'birthDate2']
                    ]
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_PEOPLE,
                [
                    'people' => [
                        'persons' => [
                            [
                                'name' => 'name1',
                                'birthDate' => 'birthDate1'
                            ],
                            [
                                'name' => 'name2',
                                'birthDate' => 'birthDate2'
                            ],
                            [
                                'name' => 'name3',
                                'birthDate' => 'birthDate3'
                            ],
                        ],
                        'header' => 'foo',
                        'displayPersonCount' => 2
                    ]
                ],
                [
                    [
                        ['value' => 'foo', 'header' => true],
                        ['value' => 3]
                    ]
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_VEHICLES,
                [
                    'vehicles' => [
                        'vehicles' => [
                            [
                                'vrm' => 'vrm1',
                                'weight' => 1000
                            ],
                            [
                                'vrm' => 'vrm2',
                                'weight' => 2000
                            ],
                        ],
                        'displayVehiclesCount' => 2,
                        'isGoods' => true,
                        'header' => 'foo'
                    ],
                ],
                [
                    [
                        [
                            'value' => 'continuations.vehicles-section.table.vrm_translated',
                            'header' => true
                        ],
                        [
                            'value' => 'continuations.vehicles-section.table.weight_translated',
                            'header' => true
                        ]
                    ],
                    [
                        ['value' => 'vrm1'],
                        ['value' => 1000],
                    ],
                    [
                        ['value' => 'vrm2'],
                        ['value' => 2000]
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_VEHICLES,
                [
                    'vehicles' => [
                        'vehicles' => [
                            [
                                'vrm' => 'vrm1',
                                'weight' => 1000
                            ],
                            [
                                'vrm' => 'vrm2',
                                'weight' => 2000
                            ],
                            [
                                'vrm' => 'vrm2',
                                'weight' => 3000
                            ],
                        ],
                        'displayVehiclesCount' => 2,
                        'isGoods' => true,
                        'header' => 'foo'
                    ],
                ],
                [
                    [
                        ['value' => 'foo', 'header' => true],
                        ['value' => 3]
                    ]
                ]
            ]
        ];
    }
}