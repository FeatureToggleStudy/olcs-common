<?php

/**
 * Financial Evidence Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace CommonTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\FinancialEvidence;

/**
 * Financial Evidence Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FinancialEvidenceTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    protected $fsm;

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();

        $this->sut = new FinancialEvidence();
        $this->sut->setFormHelper($this->formHelper);
        $this->sut->setFormServiceLocator($this->fsm);
    }

    public function testGetForm()
    {
        // Mocks
        $mockForm = m::mock();

        $this->formHelper->shouldReceive('createForm')
            ->with('Lva\FinancialEvidence')
            ->andReturn($mockForm);

        $form = $this->sut->getForm();

        $this->assertSame($mockForm, $form);
    }
}
