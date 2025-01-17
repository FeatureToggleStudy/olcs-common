<?php

namespace CommonTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\LicencePsvVehiclesVehicle;

/**
 * Licence Psv Vehicles Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicencePsvVehiclesVehicleTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    protected $formService;

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');
        $this->formService = m::mock('\Common\FormService\FormServiceManager')->makePartial();

        $this->sut = new LicencePsvVehiclesVehicle();
        $this->sut->setFormHelper($this->formHelper);
        $this->sut->setFormServiceLocator($this->formService);
    }

    public function testGetFormAdd()
    {
        $mockRequest = m::mock();
        $params = [
            'mode' => 'add',
            'location' => 'internal',
            'isRemoved' => false,
        ];

        // Mocks
        $mockForm = m::mock();
        $mockPsvVehiclesVehicle = m::mock('\Common\FormService\FormServiceInterface');
        $mockGenericVehiclesVehicle = m::mock('\Common\FormService\FormServiceInterface');

        $this->formService->setService('lva-psv-vehicles-vehicle', $mockPsvVehiclesVehicle);
        $this->formService->setService('lva-generic-vehicles-vehicle', $mockGenericVehiclesVehicle);

        $mockPsvVehiclesVehicle->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $mockGenericVehiclesVehicle->shouldReceive('alterForm')
            ->once()
            ->with($mockForm, $params);

        // <<-- START SUT::alterForm

        $mockLicenceVehicle = m::mock();
        $mockSpecifiedDate = m::mock();

        // Expectations
        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with('Lva\PsvVehiclesVehicle', $mockRequest)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'licence-vehicle->discNo')
            ->shouldReceive('enableDateTimeElement')
            ->with($mockSpecifiedDate)
            ->once()
            ->getMock();

        $mockForm->shouldReceive('get')
            ->with('licence-vehicle')
            ->andReturn($mockLicenceVehicle);

        $mockLicenceVehicle->shouldReceive('get')
            ->with('specifiedDate')
            ->andReturn($mockSpecifiedDate);

        $this->formHelper->shouldReceive('enableDateElement')
            ->with($mockSpecifiedDate);

        // <<-- END SUT::alterForm

        $this->formHelper->shouldReceive('remove')
            ->with($mockForm, 'vehicle-history-table');

        $form = $this->sut->getForm($mockRequest, $params);

        $this->assertSame($mockForm, $form);
    }

    public function testGetFormEdit()
    {
        $mockRequest = m::mock();
        $params = [
            'mode' => 'edit',
            'location' => 'internal',
            'isRemoved' => false,
        ];

        // Mocks
        $mockForm = m::mock();
        $mockPsvVehiclesVehicle = m::mock('\Common\FormService\FormServiceInterface');
        $mockGenericVehiclesVehicle = m::mock('\Common\FormService\FormServiceInterface');

        $this->formService->setService('lva-psv-vehicles-vehicle', $mockPsvVehiclesVehicle);
        $this->formService->setService('lva-generic-vehicles-vehicle', $mockGenericVehiclesVehicle);

        // Expectations

        $mockPsvVehiclesVehicle->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $mockGenericVehiclesVehicle->shouldReceive('alterForm')
            ->once()
            ->with($mockForm, $params);

        // <<-- START SUT::alterForm

        $mockLicenceVehicle = m::mock();
        $mockSpecifiedDate = m::mock();

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with('Lva\PsvVehiclesVehicle', $mockRequest)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'licence-vehicle->discNo')
            ->shouldReceive('enableDateTimeElement')
            ->with($mockSpecifiedDate)
            ->once()
            ->getMock();

        $mockForm->shouldReceive('get')
            ->with('licence-vehicle')
            ->andReturn($mockLicenceVehicle);

        $mockSpecifiedDate->shouldReceive('setShouldCreateEmptyOption')
            ->with(false)
            ->once();

        $mockLicenceVehicle->shouldReceive('get')
            ->with('specifiedDate')
            ->andReturn($mockSpecifiedDate);

        $this->formHelper->shouldReceive('enableDateElement')
            ->with($mockSpecifiedDate);

        // <<-- END SUT::alterForm

        $form = $this->sut->getForm($mockRequest, $params);

        $this->assertSame($mockForm, $form);
    }

    public function testGetFormRemoved()
    {
        $mockRequest = m::mock();
        $params = [
            'mode' => 'edit',
            'location' => 'internal',
            'isRemoved' => true,
        ];

        // Mocks
        $mockForm = m::mock();
        $mockPsvVehiclesVehicle = m::mock('\Common\FormService\FormServiceInterface');
        $mockGenericVehiclesVehicle = m::mock('\Common\FormService\FormServiceInterface');

        $this->formService->setService('lva-psv-vehicles-vehicle', $mockPsvVehiclesVehicle);
        $this->formService->setService('lva-generic-vehicles-vehicle', $mockGenericVehiclesVehicle);

        // Expectations
        $mockPsvVehiclesVehicle->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $mockGenericVehiclesVehicle->shouldReceive('alterForm')
            ->once()
            ->with($mockForm, $params);

        // <<-- START SUT::alterForm

        $mockLicenceVehicle = m::mock();
        $mockSpecifiedDate = m::mock();
        $mockRemovalDate = m::mock();

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with('Lva\PsvVehiclesVehicle', $mockRequest)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'licence-vehicle->discNo')
            ->shouldReceive('enableDateTimeElement')
            ->with($mockSpecifiedDate)
            ->once()
            ->getMock();

        $mockForm->shouldReceive('get')
            ->with('licence-vehicle')
            ->andReturn($mockLicenceVehicle);

        $mockSpecifiedDate->shouldReceive('setShouldCreateEmptyOption')
            ->with(false)
            ->once();

        $mockRemovalDate->shouldReceive('setShouldCreateEmptyOption')
            ->with(false)
            ->once();

        $mockLicenceVehicle->shouldReceive('get')
            ->with('specifiedDate')
            ->andReturn($mockSpecifiedDate)
            ->shouldReceive('get')
            ->with('removalDate')
            ->andReturn($mockRemovalDate);

        $this->formHelper->shouldReceive('enableDateElement')
            ->with($mockSpecifiedDate);

        // <<-- END SUT::alterForm

        $mockData = m::mock();

        $mockForm->shouldReceive('get')
            ->with('data')
            ->andReturn($mockData);

        $mockData->shouldReceive('has')
            ->with('makeModel')
            ->andReturn(true);

        $this->formHelper->shouldReceive('disableElement')
            ->with($mockForm, 'data->vrm')
            ->shouldReceive('disableElement')
            ->with($mockForm, 'data->makeModel')
            ->shouldReceive('disableElements')
            ->with($mockLicenceVehicle)
            ->shouldReceive('enableDateElement')
            ->with($mockRemovalDate);

        $form = $this->sut->getForm($mockRequest, $params);

        $this->assertSame($mockForm, $form);
    }
}
