<?php

/**
 * Generic Vehicles Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace CommonTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\GenericVehiclesVehicle;

/**
 * Generic Vehicles Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GenericVehiclesVehicleTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');

        $this->sut = new GenericVehiclesVehicle();
        $this->sut->setFormHelper($this->formHelper);
    }

    public function testAlterFormNoOp()
    {
        $mockForm = m::mock();
        $params = [
            'mode' => 'add',
            'isPost' => false,
            'canAddAnother' => true
        ];

        $this->assertNull($this->sut->alterForm($mockForm, $params));
    }

    public function testAlterFormAddCantAddAnother()
    {
        $mockForm = m::mock();
        $params = [
            'mode' => 'add',
            'isPost' => false,
            'canAddAnother' => false
        ];

        $mockForm->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                ->shouldReceive('remove')
                ->with('addAnother')
                ->getMock()
            );

        $this->assertNull($this->sut->alterForm($mockForm, $params));
    }

    public function testAlterFormEdit()
    {
        $mockForm = m::mock();
        $params = [
            'mode' => 'edit',
            'isPost' => false,
            'canAddAnother' => false
        ];

        $this->formHelper->shouldReceive('disableElement')
            ->with($mockForm, 'data->vrm');

        $mockForm->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                ->shouldReceive('remove')
                ->with('addAnother')
                ->getMock()
            );

        $this->assertNull($this->sut->alterForm($mockForm, $params));
    }
}
