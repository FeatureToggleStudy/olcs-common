<?php

/**
 * Abstract Operating Centre Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace CommonTest\Controller\Lva\Adapters;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Entity\EnforcementAreaEntityService;

/**
 * Abstract Operating Centre Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class AbstractOperatingCentreAdapterTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    protected function setUp()
    {
        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $this->sm->setAllowOverride(true);

        $this->sut = m::mock('\Common\Controller\Lva\Adapters\AbstractOperatingCentreAdapter')
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testDisableValidation()
    {
        $inputFilter = m::mock();

        $form = m::mock('\Zend\Form\Form')
            ->shouldReceive('getInputFilter')
            ->andReturn($inputFilter)
            ->getMock();

        $this->sm->setService(
            'Helper\Form',
            m::mock()
            ->shouldReceive('disableValidation')
            ->with($inputFilter)
            ->getMock()
        );

        $this->sut->disableValidation($form);
    }

    public function testAlterFormData()
    {
        $data = ['foo' => 'bar'];

        $this->assertEquals($data, $this->sut->alterFormData(2, $data));
    }

    public function testAlterFormDataOnPost()
    {
        $data = ['foo' => 'bar'];

        $this->assertSame($data, $this->sut->alterFormDataOnPost('edit', $data, 123));
    }

    /**
     * Test that table and row count get set on the form
     */
    public function testGetMainForm()
    {
        $mockForm  = m::mock('\Zend\Form\Form');
        $mockTable = m::mock();
        $tableData = m::mock();

        $this->sm->setService(
            'Helper\Form',
            m::mock()
                ->shouldReceive('createForm')
                ->once()
                ->with('Lva\OperatingCentres')
                ->andReturn($mockForm)
                ->getMock()
        );

        $this->sm->setService(
            'Table',
            m::mock()
                ->shouldReceive('prepareTable')
                ->once()
                ->with('lva-operating-centres', $tableData)
                ->andReturn($mockTable)
                ->getMock()
        );

        $this->sut->shouldReceive('getTableData')->once()->andReturn($tableData);

        $mockTable->shouldReceive('getRows')->andReturn(['row1','row2']);

        $tableFieldset = m::mock()
            ->shouldReceive('get')
                ->with('table')
                ->andReturn(
                    m::mock()->shouldReceive('setTable')
                        ->with($mockTable)
                        ->getMock()
                )
            ->shouldReceive('get')
                ->with('rows')
                ->andReturn(
                    m::mock()->shouldReceive('setValue')
                        ->with(2)
                        ->getMock()
                )
            ->getMock();

        $mockForm->shouldReceive('get')->with('table')->andReturn($tableFieldset);

        $this->sut->shouldReceive('alterForm')->with($mockForm);

        $this->assertSame($mockForm, $this->sut->getMainForm());
    }

    public function testSaveMainFormData()
    {
        $id = 99;
        $licenceId = 101;

        // stub data
        $formData = [
            'dataTrafficArea' => [
                'enforcementArea' => 'V048',
            ],
            'data' => [
                'id' => $id,
                'version' => 1,
                'totAuthVehicles' => '3',
                'totAuthTrailers' => '4',
            ],
        ];

        $saveData = [
            'enforcementArea' => 'V048',
            'id' => $id,
            'version' => 1,
            'totAuthVehicles' => '3',
            'totAuthTrailers' => '4',
        ];

        // mocks
        $mockLvaEntityService = m::mock();
        $mockLicenceEntityService = m::mock();
        $mockLicenceAdapter = m::mock('\Common\Controller\Lva\Interfaces\LvaAdapterInterface');

        $this->sm->setService('Entity\Licence', $mockLicenceEntityService);
        $this->sm->setService('LicenceLvaAdapter', $mockLicenceAdapter);

        // use a real data helper
        $this->sm->setService('Helper\Data', new \Common\Service\Helper\DataHelperService());

        // expectations
        $this->sut
            ->shouldReceive('getLvaEntityService')
            ->andReturn($mockLvaEntityService);

        $mockLvaEntityService
            ->shouldReceive('save')
            ->once()
            ->with($saveData);

        $mockLicenceAdapter
            ->shouldReceive('setController')
            ->andReturnSelf()
            ->shouldReceive('getIdentifier')
            ->andReturn($licenceId);

        $mockLicenceEntityService
            ->shouldReceive('setEnforcementArea')
            ->once()
            ->with($licenceId, 'V048');

        $this->sut->saveMainFormData($formData);
    }

    public function testSetDefaultEnforcementAreaGb()
    {
        $id = 69;
        $licenceId = 77;

        $data = [
            'operatingCentre' => [
                'addresses' => [
                    'address' => [
                        'postcode' => 'LS9 6NF',
                    ],
                ],
            ],
        ];

        $mockTypeOfLicenceData = [
            'niFlag' => 'N',
            'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
            'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL,
        ];

        $mockLicenceEntityService = m::mock();
        $this->sm->setService('Entity\Licence', $mockLicenceEntityService);
        $mockPostcodeService = m::mock();
        $this->sm->setService('Postcode', $mockPostcodeService);
        $mockLvaEntityService = m::mock();
        $mockEntityService = m::mock();
        $mockLicenceAdapter = m::mock('\Common\Controller\Lva\Interfaces\LvaAdapterInterface');
        $this->sm->setService('LicenceLvaAdapter', $mockLicenceAdapter);

        $mockLicenceAdapter
            ->shouldReceive('setController')
            ->andReturnSelf()
            ->shouldReceive('getIdentifier')
            ->andReturn($licenceId);

        $mockEntityService
            ->shouldReceive('getOperatingCentresCount')
            ->andReturn(
                [
                    'Count' => 1,
                    'Results' => ['oc'],
                ]
            );

        $this->sut
            ->shouldReceive('getLvaEntityService')
            ->andReturn($mockLvaEntityService)
            ->shouldReceive('getEntityService')
            ->andReturn($mockEntityService)
            ->shouldReceive('getIdentifier')
            ->andReturn($id);

        $mockLvaEntityService->shouldReceive('getTypeOfLicenceData')
            ->with($id)
            ->andReturn($mockTypeOfLicenceData);

        $mockPostcodeService
            ->shouldReceive('getEnforcementAreaByPostcode')
            ->with('LS9 6NF')
            ->andReturn(
                [
                    'id' => 'V048',
                    'name' => 'Leeds',
                ]
            );

        $mockLicenceEntityService
            ->shouldReceive('setEnforcementArea')
            ->with($licenceId, 'V048');

        $this->sut->setDefaultEnforcementArea($data);
    }

    public function testSetDefaultEnforcementAreaNi()
    {
        $id = 69;
        $licenceId = 77;

        $data = [
            'operatingCentre' => [
                'addresses' => [
                    'address' => [
                        'postcode' => 'BT2 8ED',
                    ],
                ],
            ],
        ];

        $mockTypeOfLicenceData = [
            'niFlag' => 'Y',
            'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
            'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL,
        ];

        $mockLicenceEntityService = m::mock();
        $this->sm->setService('Entity\Licence', $mockLicenceEntityService);
        $mockLvaEntityService = m::mock();
        $mockLicenceAdapter = m::mock('\Common\Controller\Lva\Interfaces\LvaAdapterInterface');
        $this->sm->setService('LicenceLvaAdapter', $mockLicenceAdapter);

        $mockLicenceAdapter
            ->shouldReceive('setController')
            ->andReturnSelf()
            ->shouldReceive('getIdentifier')
            ->andReturn($licenceId);

        $this->sut
            ->shouldReceive('getLvaEntityService')
            ->andReturn($mockLvaEntityService)
            ->shouldReceive('getIdentifier')
            ->andReturn($id);

        $mockLvaEntityService->shouldReceive('getTypeOfLicenceData')
            ->with($id)
            ->andReturn($mockTypeOfLicenceData);

        $mockLicenceEntityService
            ->shouldReceive('setEnforcementArea')
            ->with($licenceId, EnforcementAreaEntityService::NORTHERN_IRELAND_ENFORCEMENT_AREA_CODE);

        $this->sut->setDefaultEnforcementArea($data);
    }
}
