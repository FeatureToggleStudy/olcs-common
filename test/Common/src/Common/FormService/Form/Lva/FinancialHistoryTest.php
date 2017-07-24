<?php

/**
 * Financial History Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace CommonTest\FormService\Form\Lva;

use Common\Form\Elements\InputFilters\SingleCheckbox;
use Common\Form\Form;
use Common\FormService\Form\Lva\FinancialHistory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Form\Fieldset;
use Zend\Http\Request;
use Zend\InputFilter\Input;

/**
 * Financial History Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FinancialHistoryTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    protected $fsm;

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();

        $this->sut = new FinancialHistory();
        $this->sut->setFormHelper($this->formHelper);
        $this->sut->setFormServiceLocator($this->fsm);
    }

    public function testGetForm()
    {
        $request = m::mock(Request::class);

        // Mocks
        $mockForm = m::mock(Form::class);

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with('Lva\FinancialHistory', $request)
            ->andReturn($mockForm);

        $form = $this->sut->getForm($request, []);

        $this->assertSame($mockForm, $form);
    }

    public function testGetFormWithNiFlagSetToY()
    {
        $request = m::mock(Request::class);

        // Mocks
        $mockConfirmationLabel = m::mock(SingleCheckbox::class);
        $mockConfirmationLabel->shouldReceive('setLabel')
            ->with('application_previous-history_financial-history.insolvencyConfirmation.title.ni')
            ->andReturnSelf();

        $mockDataFieldset = m::mock(Fieldset::class);
        $mockDataFieldset->shouldReceive('get')
            ->with('insolvencyConfirmation')
            ->andReturn($mockConfirmationLabel);

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('get')
            ->with('data')
            ->andReturn($mockDataFieldset);

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with('Lva\FinancialHistory', $request)
            ->andReturn($mockForm);

        $form = $this->sut->getForm(
            $request,
            [
                'lva' => 'variation',
                'niFlag' => 'Y',
            ]
        );

        $this->assertSame($mockForm, $form);
    }
}
