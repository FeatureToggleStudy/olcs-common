<?php

namespace CommonTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\CommonVehiclesSearch;

/**
 * @covers \Common\FormService\Form\Lva\CommonVehiclesSearch
 */
class CommonVehiclesSearchTest extends MockeryTestCase
{
    /** @var CommonVehiclesSearch  */
    protected $sut;
    /** @var  \Common\Service\Helper\FormHelperService | m\MockInterface */
    protected $formHelper;

    public function setUp()
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);

        $this->sut = new CommonVehiclesSearch();
        $this->sut->setFormHelper($this->formHelper);
    }

    public function testGetForm()
    {
        $mockForm = m::mock();

        $this->formHelper->shouldReceive('createForm')
            ->with('Lva\VehicleSearch', false)
            ->andReturn($mockForm);

        $form = $this->sut->getForm();

        $this->assertSame($mockForm, $form);
    }
}
