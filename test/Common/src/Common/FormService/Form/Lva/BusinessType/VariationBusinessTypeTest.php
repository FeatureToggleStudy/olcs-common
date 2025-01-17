<?php

/**
 * Variation Business Type Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace CommonTest\FormService\Form\Lva\BusinessType;

use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\BusinessType\VariationBusinessType;
use Common\FormService\FormServiceInterface;
use Zend\Form\Form;
use Zend\Form\Element;

/**
 * Variation Business Type Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationBusinessTypeTest extends MockeryTestCase
{
    /**
     * @var VariationBusinessType
     */
    protected $sut;

    protected $fsm;

    protected $fh;

    public function setUp()
    {
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();
        $this->fh = m::mock(FormHelperService::class)->makePartial();

        $this->sut = new VariationBusinessType();
        $this->sut->setFormServiceLocator($this->fsm);
        $this->sut->setFormHelper($this->fh);
    }

    /**
     * @dataProvider trueFalse
     */
    public function testGetForm()
    {
        $mockForm = m::mock(Form::class);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\BusinessType')
            ->andReturn($mockForm);

        $mockApplication = m::mock(FormServiceInterface::class);
        $mockApplication->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $this->fsm->setService('lva-variation', $mockApplication);

        $form = $this->sut->getForm(false);

        $this->assertSame($mockForm, $form);
    }

    public function trueFalse()
    {
        return [
            [
                true
            ],
            [
                false
            ]
        ];
    }
}
