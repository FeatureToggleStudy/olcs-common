<?php

namespace Common\FormService\Form\Lva\TypeOfLicence;

use Common\Form\Elements\InputFilters\Lva\BackToLicenceActionLink;
use Common\FormService\Form\Lva\Licence;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Form\Form;

/**
 * Licence Type Of Licence Test
 */
class LicenceTypeOfLicenceTest extends MockeryTestCase
{
    /**
     * @var LicenceTypeOfLicence
     */
    protected $sut;

    protected $fsm;

    protected $fh;

    public function setUp()
    {
        $this->sut = new LicenceTypeOfLicence();

        $this->fsm = m::mock(FormServiceManager::class)->makePartial();
        $this->fh = m::mock(FormHelperService::class)->makePartial();

        $this->sut->setFormServiceLocator($this->fsm);
        $this->sut->setFormHelper($this->fh);
    }

    public function testGetForm()
    {
        $params = [
            'canUpdateLicenceType' => true,
            'canBecomeSpecialRestricted' => true
        ];

        $mockOl = m::mock(Element::class);
        $mockOl->shouldReceive('setLabel')
            ->once()
            ->with('operator-location');

        $mockOt = m::mock(Element::class);
        $mockOt->shouldReceive('setLabel')
            ->once()
            ->with('operator-type');

        $mockLt = m::mock(Element::class);
        $mockLt->shouldReceive('setLabel')
            ->once()
            ->with('licence-type');

        $tolFieldset = m::mock(Fieldset::class);
        $tolFieldset->shouldReceive('get')
            ->with('operator-location')
            ->andReturn($mockOl)
            ->shouldReceive('get')
            ->with('operator-type')
            ->andReturn($mockOt)
            ->shouldReceive('get')
            ->with('licence-type')
            ->andReturn($mockLt);

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('get')
            ->with('type-of-licence')
            ->andReturn($tolFieldset);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\TypeOfLicence')
            ->andReturn($mockForm)
            ->shouldReceive('lockElement')
            ->once()
            ->with($mockOl, 'operator-location-lock-message')
            ->shouldReceive('lockElement')
            ->once()
            ->with($mockOt, 'operator-type-lock-message')
            ->shouldReceive('disableElement')
            ->once()
            ->with($mockForm, 'type-of-licence->operator-location')
            ->shouldReceive('disableElement')
            ->once()
            ->with($mockForm, 'type-of-licence->operator-type');

        $licService = m::mock(Licence::class);
        $this->fsm->setService('lva-licence', $licService);

        $licService->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $form = $this->sut->getForm($params);

        $this->assertSame($mockForm, $form);
    }

    public function testGetFormWithFalse()
    {
        $params = [
            'canUpdateLicenceType' => false,
            'canBecomeSpecialRestricted' => false
        ];

        $mockOl = m::mock(Element::class);
        $mockOl->shouldReceive('setLabel')
            ->once()
            ->with('operator-location');

        $mockOt = m::mock(Element::class);
        $mockOt->shouldReceive('setLabel')
            ->once()
            ->with('operator-type');

        $mockLt = m::mock(Element\Select::class);
        $mockLt->shouldReceive('setLabel')
            ->once()
            ->with('licence-type');

        $tolFieldset = m::mock(Fieldset::class);
        $tolFieldset->shouldReceive('get')
            ->with('operator-location')
            ->andReturn($mockOl)
            ->shouldReceive('get')
            ->with('operator-type')
            ->andReturn($mockOt)
            ->shouldReceive('get')
            ->with('licence-type')
            ->andReturn($mockLt);

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('get')
            ->with('type-of-licence')
            ->andReturn($tolFieldset);

        $formActions = m::mock();
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('save');
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');
        $formActions->shouldReceive('add')->once()->with(m::type(BackToLicenceActionLink::class));

        $mockForm->shouldReceive('has')->with('form-actions')->andReturn(true);
        $mockForm->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\TypeOfLicence')
            ->andReturn($mockForm)
            ->shouldReceive('lockElement')
            ->once()
            ->with($mockOl, 'operator-location-lock-message')
            ->shouldReceive('lockElement')
            ->once()
            ->with($mockOt, 'operator-type-lock-message')
            ->shouldReceive('lockElement')
            ->once()
            ->with($mockLt, 'licence-type-lock-message')
            ->shouldReceive('disableElement')
            ->once()
            ->with($mockForm, 'type-of-licence->operator-location')
            ->shouldReceive('disableElement')
            ->once()
            ->with($mockForm, 'type-of-licence->operator-type')
            ->shouldReceive('disableElement')
            ->once()
            ->with($mockForm, 'type-of-licence->licence-type')
            ->shouldReceive('removeOption')
            ->once()
            ->with($mockLt, RefData::LICENCE_TYPE_SPECIAL_RESTRICTED);

        $licService = m::mock(Licence::class);
        $this->fsm->setService('lva-licence', $licService);

        $licService->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $form = $this->sut->getForm($params);

        $this->assertSame($mockForm, $form);
    }
}
