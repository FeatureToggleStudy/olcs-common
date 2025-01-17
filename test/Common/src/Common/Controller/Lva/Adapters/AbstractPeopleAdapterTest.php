<?php

namespace CommonTest\Controller\Lva\Adapters;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Lva\Adapters\AbstractPeopleAdapter;
use Common\Service\Table\TableBuilder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Form\Form;
use Zend\Mvc\Controller\Plugin\FlashMessenger;

/**
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @covers \Common\Controller\Lva\Adapters\AbstractPeopleAdapter
 */
class AbstractPeopleAdapterTest extends MockeryTestCase
{
    const ID = 9001;
    const LIC_ID = 8001;

    /** @var  m\MockInterface | AbstractPeopleAdapter */
    private $sut;

    /** @var  m\MockInterface | AbstractPeopleAdapter */
    private $mockResp;

    public function setUp()
    {
        $this->mockResp = m::mock(\Zend\Http\Response::class);
        $this->mockResp->shouldReceive('isOk')->andReturn(true);

        $this->sut = m::mock(AbstractPeopleAdapter::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sut->shouldReceive('handleQuery')->andReturn($this->mockResp);
    }

    public function testLoadPeopleDataLic()
    {
        $this->sut->shouldReceive('loadPeopleDataForLicence')->once()->with(self::ID);

        static::assertTrue($this->sut->loadPeopleData(AbstractController::LVA_LIC, self::ID));
    }

    public function testLoadPeopleDataOth()
    {
        $this->sut->shouldReceive('loadPeopleDataForApplication')->twice()->with(self::ID);

        static::assertTrue($this->sut->loadPeopleData(AbstractController::LVA_VAR, self::ID));
        static::assertTrue($this->sut->loadPeopleData(AbstractController::LVA_APP, self::ID));
    }

    public function testHasInforceLicences()
    {
        $this->mockResp->shouldReceive('getResult')->once()->andReturn(['hasInforceLicences' => 'unit_EXPECT']);

        $this->sut->loadPeopleData(AbstractController::LVA_LIC, self::LIC_ID);

        static::assertEquals('unit_EXPECT', $this->sut->hasInforceLicences());
    }

    public function testIsExceptionalOrganisation()
    {
        $this->mockResp->shouldReceive('getResult')->once()->andReturn(['isExceptionalType' => 'unit_EXPECT']);

        $this->sut->loadPeopleData(AbstractController::LVA_LIC, self::LIC_ID);

        static::assertEquals('unit_EXPECT', $this->sut->isExceptionalOrganisation());
    }

    public function testIsSoleTrader()
    {
        $this->mockResp->shouldReceive('getResult')->once()->andReturn(['isSoleTrader' => 'unit_EXPECT']);

        $this->sut->loadPeopleData(AbstractController::LVA_LIC, self::LIC_ID);

        static::assertEquals('unit_EXPECT', $this->sut->isSoleTrader());
    }

    public function testIsPartnership()
    {
        $this->mockResp->shouldReceive('getResult')
            ->once()
            ->andReturn(
                [
                    'organisation' => [
                        'type' => [
                            'id' => \Common\RefData::ORG_TYPE_PARTNERSHIP,
                        ],
                    ],
                ]
            );

        $this->sut->loadPeopleData(AbstractController::LVA_LIC, self::LIC_ID);

        static::assertTrue($this->sut->isPartnership());
    }

    /**
     * @dataProvider dpTestAlterFormForOrganisation
     */
    public function testAlterFormForOrganisation($type, $expected)
    {
        $mockTable = m::mock(TableBuilder::class)
            ->shouldReceive('getAction')
            ->with('add')
            ->andReturn([])
            ->once()
            ->shouldReceive('removeAction')
            ->with('add')
            ->once()
            ->shouldReceive('addAction')
            ->with('add', ['label' => $expected])
            ->once()
            ->getMock();

        $this->sut->shouldReceive('getOrganisationType')
            ->andReturn($type)
            ->twice()
            ->getMock();

        $this->sut->alterFormForOrganisation(m::mock(Form::class), $mockTable);
    }

    public function dpTestAlterFormForOrganisation()
    {
        return [
            'ltd' => [
                \Common\RefData::ORG_TYPE_RC,
                'lva.section.title.add_director'
            ],
            'llp' => [
                \Common\RefData::ORG_TYPE_LLP,
                'lva.section.title.add_partner'
            ],
            'partnership' => [
                \Common\RefData::ORG_TYPE_PARTNERSHIP,
                'lva.section.title.add_partner'
            ],
            'other' => [
                \Common\RefData::ORG_TYPE_OTHER,
                'lva.section.title.add_person'
            ],
            'irfo' => [
                \Common\RefData::ORG_TYPE_IRFO,
                'lva.section.title.add_person'
            ]
        ];
    }

    public function testGetAddLabelTextForOrganisationReturnsNullIfNoOrganisationType()
    {
        $this->assertNull($this->sut->getAddLabelTextForOrganisation());
    }

    /**
     * @dataProvider dpTestAlterFormForOrganisation
     */
    public function testGetAddLabelTextForOrganisationReturnsAppropriateLabel($type, $expected)
    {
        $this->sut->shouldReceive('getOrganisationType')
            ->andReturn($type)
            ->twice()
            ->getMock();
        $this->assertEquals($expected, $this->sut->getAddLabelTextForOrganisation());
    }


    /**
     * @dataProvider  dpTestAlterFormForOrganisation
     */
    public function testAmendLicencePeopleListTableAltersTable($type, $expected)
    {
        $settingsArray = [
            'actions' => [
                'add' => [
                    'label' => $expected
                ]
            ]
        ];

        $this->sut->shouldReceive('getOrganisationType')
            ->andReturn($type)
            ->twice()
            ->getMock();

        $mockTable = m::mock(TableBuilder::class)
            ->shouldReceive('setSetting')
            ->with(
                'crud',
                $settingsArray
            )
            ->once()
            ->andReturnSelf()
            ->getMock();

        $this->sut->amendLicencePeopleListTable($mockTable);
    }

    public function testStatusesAreAddedToPeopleFromFlashMessenger()
    {
        $mockFM = m::mock(FlashMessenger::class);

        $this->sut
            ->shouldReceive('getController->plugin')
            ->with('FlashMessenger')
            ->andReturn($mockFM);

        $mockTableBuilder = m::mock(TableBuilder::class);

        $this->sut
            ->shouldReceive('getServiceLocator->get')
            ->with('Table')
            ->andReturn($mockTableBuilder);

        $mockFM
            ->shouldReceive('getMessages')
            ->with(AbstractController::FLASH_MESSENGER_CREATED_PERSON_NAMESPACE)
            ->andReturn([53]);

        $this->sut
            ->shouldReceive('formatTableData')
            ->andReturn(
                [
                    [
                        'id' => 39
                    ],
                    [
                        'id' => 53
                    ]
                ]
            );

        $expected = [
            [
                'id' => 39,
                'status' => null
            ],
            [
                'id' => 53,
                'status' => 'new'
            ]
        ];

        $mockTableBuilder
            ->shouldReceive('prepareTable')
            ->andReturnUsing(
                function($tableConfig, $tableData) use ($expected) {
                    $this->assertSame($expected, $tableData);
                }
            );

        $this->sut->createTable();
    }
}
