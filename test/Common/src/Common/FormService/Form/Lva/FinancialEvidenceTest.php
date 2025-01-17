<?php

namespace CommonTest\FormService\Form\Lva;

use Common\FormService\Form\Lva\FinancialEvidence;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use CommonTest\Bootstrap;

/**
 * @covers Common\FormService\Form\Lva\FinancialEvidence
 */
class FinancialEvidenceTest extends MockeryTestCase
{
    /** @var  FinancialEvidence */
    protected $sut;

    /** @var  m\MockInterface|\Common\Service\Helper\FormHelperService */
    protected $formHelper;
    /** @var  \Common\FormService\FormServiceManager */
    protected $fsm;
    /** @var  m\MockInterface */
    protected $urlHelper;
    /** @var  m\MockInterface */
    protected $translator;

    public function setUp()
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $this->fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();
        $this->urlHelper = m::mock();
        $this->translator = m::mock();

        $sm = Bootstrap::getServiceManager();
        $sm->setService('Helper\Url', $this->urlHelper);
        $sm->setService('Helper\Translation', $this->translator);

        $this->fsm->shouldReceive('getServiceLocator')->andReturn($sm);

        $this->sut = new FinancialEvidence();
        $this->sut->setFormHelper($this->formHelper);
        $this->sut->setFormServiceLocator($this->fsm);
    }

    public function testGetForm()
    {
        $this->translator
            ->shouldReceive('translateReplace')
            ->with('lva-financial-evidence-evidence.hint', ['FOO'])
            ->andReturn('BAR')
            ->once()
            ->getMock();

        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with('guides/guide', ['guide' => 'financial-evidence'], [], true)
            ->andReturn('FOO')
            ->once()
            ->getMock();

        /** @var \Zend\Http\Request $request */
        $request = m::mock(\Zend\Http\Request::class);

        // Mocks
        $mockForm = m::mock()
            ->shouldReceive('get')
            ->with('evidence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('uploadNowRadio')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setName')
                            ->with('uploadNow')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('get')
                    ->with('uploadLaterRadio')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setName')
                            ->with('uploadNow')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('get')
                    ->with('sendByPostRadio')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setName')
                            ->with('uploadNow')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('setOption')
                    ->with('hint', 'BAR')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with('Lva\FinancialEvidence', $request)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'evidence->uploadNow')
            ->once()
            ->getMock();

        $form = $this->sut->getForm($request);

        $this->assertSame($mockForm, $form);
    }
}
