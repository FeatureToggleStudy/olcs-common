<?php

/**
 * Transport Manager Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace CommonTest\Helper;

use CommonTest\Bootstrap;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Helper\TransportManagerHelperService;
use Common\Service\Data\CategoryDataService;

/**
 * Transport Manager Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagerHelperServiceTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new TransportManagerHelperService();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testGetCertificateFiles()
    {
        $tmId = 111;

        $mockTm = m::mock();

        $this->sm->setService('Entity\TransportManager', $mockTm);

        $mockTm->shouldReceive('getDocuments')
            ->with(
                111,
                null,
                null,
                CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
                CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CPC_OR_EXEMPTION
            )
            ->andReturn('RESPONSE');

        $response = $this->sut->getCertificateFiles($tmId);

        $this->assertEquals('RESPONSE', $response);
    }

    public function testGetCertificateFileData()
    {
        $tmId = 111;
        $file = ['name' => 'foo.txt'];

        $expected = [
            'transportManager' => 111,
            'description' => 'foo.txt',
            'issuedDate' => '2015-01-01 10:10:10',
            'category' => CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CPC_OR_EXEMPTION
        ];

        $this->sm->setService(
            'Helper\Date',
            m::mock()
            ->shouldReceive('getDate')
            ->andReturn('2015-01-01 10:10:10')
            ->once()
            ->getMock()
        );
        $response = $this->sut->getCertificateFileData($tmId, $file);

        $this->assertEquals($expected, $response);
    }

    public function testAlterResponsibilitiesFieldset()
    {
        // Params
        $fieldset = m::mock();
        $ocOptions = [
            111 => ['foo'],
            222 => ['bar']
        ];
        $otherLicencesTable = m::mock();

        // Mocks
        $mockFormHelper = m::mock();
        $mockTmTypeField = m::mock();
        $mockOtherLicenceField = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        // Expectations
        $fieldset->shouldReceive('get')
            ->once()
            ->with('operatingCentres')
            ->andReturn(
                m::mock()
                ->shouldReceive('setValueOptions')
                ->once()
                ->with($ocOptions)
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('tmType')
            ->andReturn($mockTmTypeField)
            ->shouldReceive('get')
            ->with('otherLicences')
            ->andReturn($mockOtherLicenceField);

        $mockFormHelper->shouldReceive('removeOption')
            ->once()
            ->with($mockTmTypeField, 'tm_t_b')
            ->shouldReceive('populateFormTable')
            ->with($mockOtherLicenceField, $otherLicencesTable);

        $this->sut->alterResponsibilitiesFieldset($fieldset, $ocOptions, $otherLicencesTable);
    }

    public function testGetResponsibilityFileData()
    {
        $tmId = 111;

        $expected = [
            'transportManager' => 111,
            'issuedDate' => '2014-01-20 10:10:10',
            'description' => 'Additional information',
            'category' => CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL
        ];

        // Mocks
        $mockDateHelper = m::mock();
        $this->sm->setService('Helper\Date', $mockDateHelper);

        // Expectations
        $mockDateHelper->shouldReceive('getDate')
            ->with(\DateTime::W3C)
            ->andReturn('2014-01-20 10:10:10');

        // Assertions
        $data = $this->sut->getResponsibilityFileData($tmId);

        $this->assertEquals($expected, $data);
    }

    public function testGetConvictionsAndPenaltiesTable()
    {
        $this->markTestSkipped();

        $tmId = 111;

        $mockTableBuilder = m::mock();
        $this->sm->setService('Table', $mockTableBuilder);

        $mockTable = $this->expectedGetConvictionsAndPenaltiesTable($mockTableBuilder);

        $this->assertSame($mockTable, $this->sut->getConvictionsAndPenaltiesTable($tmId));
    }

    public function testGetPreviousLicencesTable()
    {
        $this->markTestSkipped();

        $tmId = 111;

        $mockTableBuilder = m::mock();
        $this->sm->setService('Table', $mockTableBuilder);

        $mockTable = $this->expectGetPreviousLicencesTable($mockTableBuilder);

        $this->assertSame($mockTable, $this->sut->getPreviousLicencesTable($tmId));
    }

    public function testAlterPreviousHistoryFieldset()
    {
        $this->markTestSkipped();

        $convictionElement = m::mock();
        $licenceElement = m::mock();
        $fieldset = m::mock();
        $tmId = 111;

        $mockTableBuilder = m::mock();
        $this->sm->setService('Table', $mockTableBuilder);

        $fieldset->shouldReceive('get')
            ->with('convictions')
            ->andReturn($convictionElement)
            ->shouldReceive('get')
            ->with('previousLicences')
            ->andReturn($licenceElement);

        $convictionTable = $this->expectedGetConvictionsAndPenaltiesTable($mockTableBuilder);
        $licenceTable = $this->expectGetPreviousLicencesTable($mockTableBuilder);

        $mockFormHelper = m::mock();

        $this->sm->setService('Helper\Form', $mockFormHelper);

        $mockFormHelper->shouldReceive('populateFormTable')
            ->once()
            ->with($convictionElement, $convictionTable, 'convictions')
            ->shouldReceive('populateFormTable')
            ->once()
            ->with($licenceElement, $licenceTable, 'previousLicences');

        $this->sut->alterPreviousHistoryFieldset($fieldset, $tmId);
    }

    public function testPrepareOtherEmploymentTable()
    {
        $element = m::mock();
        $tmId = 111;

        // Mocks
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $mockTable = $this->expectGetOtherEmploymentTable();

        // Expectations
        $mockFormHelper->shouldReceive('populateFormTable')
            ->with($element, $mockTable, 'employment');

        $this->sut->prepareOtherEmploymentTable($element, $tmId);
    }

    public function testGetOtherEmploymentTable()
    {
        $tmId = 111;

        $mockTable = $this->expectGetOtherEmploymentTable();

        $this->assertSame($mockTable, $this->sut->getOtherEmploymentTable($tmId));
    }

    /**
     * @dataProvider providerGetOtherEmploymentData
     */
    public function testGetOtherEmploymentData($stubbedData, $expectedData)
    {
        $this->markTestIncomplete();

        $id = 111;

        // Mocks
        $mockTmEmployment = m::mock();
        $this->sm->setService('Entity\TmEmployment', $mockTmEmployment);

        // Expectations
        $mockTmEmployment->shouldReceive('getEmployment')
            ->with($id)
            ->andReturn($stubbedData);

        $this->assertEquals($expectedData, $this->sut->getOtherEmploymentData($id));
    }

    public function providerGetOtherEmploymentData()
    {
        return [
            [
                [
                    'id' => 111,
                    'version' => 1,
                    'position' => 'All of them',
                    'hoursPerWeek' => '24/7',
                    'employerName' => 'Foo ltd',
                    'contactDetails' => [
                        'address' => [
                            'addressLine1' => 'Foo street'
                        ]
                    ]
                ],
                [
                    'tm-employment-details' => [
                        'id' => 111,
                        'version' => 1,
                        'position' => 'All of them',
                        'hoursPerWeek' => '24/7'
                    ],
                    'tm-employer-name-details' => [
                        'employerName' => 'Foo ltd'
                    ],
                    'address' => [
                        'addressLine1' => 'Foo street'
                    ]
                ]
            ],
            [
                [
                    'id' => 111,
                    'version' => 1,
                    'position' => 'All of them',
                    'hoursPerWeek' => '24/7',
                    'employerName' => 'Foo ltd'
                ],
                [
                    'tm-employment-details' => [
                        'id' => 111,
                        'version' => 1,
                        'position' => 'All of them',
                        'hoursPerWeek' => '24/7'
                    ],
                    'tm-employer-name-details' => [
                        'employerName' => 'Foo ltd'
                    ]
                ]
            ]
        ];
    }

    protected function expectGetOtherEmploymentTable()
    {
        $tableData = [
            'foo' => 'bar'
        ];

        // Mocks
        $mockTableBuilder = m::mock();
        $mockTable = m::mock();
        $mockTmEmployment = m::mock();

        $this->sm->setService('Table', $mockTableBuilder);
        $this->sm->setService('Entity\TmEmployment', $mockTmEmployment);

        // Expectations
        $mockTmEmployment->shouldReceive('getAllEmploymentsForTm')
            ->once()
            ->with(111)
            ->andReturn($tableData);

        $mockTableBuilder->shouldReceive('prepareTable')
            ->once()
            ->with('tm.employments', $tableData)
            ->andReturn($mockTable);

        return $mockTable;
    }

    protected function expectedGetConvictionsAndPenaltiesTable($mockTableBuilder)
    {
        $tableData = [
            'foo' => 'bar'
        ];

        // Mocks
        $mockTable = m::mock();
        $mockPrevConviction = m::mock();

        $this->sm->setService('Entity\PreviousConviction', $mockPrevConviction);

        // Expectations
        $mockPrevConviction->shouldReceive('getDataForTransportManager')
            ->once()
            ->with(111)
            ->andReturn($tableData);

        $mockTableBuilder->shouldReceive('prepareTable')
            ->once()
            ->with('tm.convictionsandpenalties', $tableData)
            ->andReturn($mockTable);

        return $mockTable;
    }

    protected function expectGetPreviousLicencesTable($mockTableBuilder)
    {
        $tableData = [
            'foo' => 'bar'
        ];

        // Mocks
        $mockTable = m::mock();
        $mockOtherLicence = m::mock();

        $this->sm->setService('Entity\OtherLicence', $mockOtherLicence);

        // Expectations
        $mockOtherLicence->shouldReceive('getDataForTransportManager')
            ->once()
            ->with(111)
            ->andReturn($tableData);

        $mockTableBuilder->shouldReceive('prepareTable')
            ->once()
            ->with('tm.previouslicences', $tableData)
            ->andReturn($mockTable);

        return $mockTable;
    }
}
