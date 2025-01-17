<?php

namespace CommonTest\Controller\Lva\Traits;

use CommonTest\Bootstrap;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CRUD Table Trait Test
 *
 * @covers \Common\Controller\Lva\Traits\CrudTableTrait
 */
class CrudTableTraitTest extends MockeryTestCase
{
    /** @var  Stubs\CrudTableTraitStub|m\MockInterface */
    protected $sut;
    /** @var  \Zend\ServiceManager\ServiceManager */
    protected $sm;

    protected function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = m::mock(Stubs\CrudTableTraitStub::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testHandlePostSaveWithAddAnother()
    {
        $prefix = 'unit_Prdx';
        $options = ['unit_options'];

        $redirectMock = m::mock()
            ->shouldReceive('toRoute')
            ->with(
                null,
                [
                    'application' => 123,
                    'action' => 'unit_Prdx-add'
                ],
                $options
            )
            ->andReturn('redirect')
            ->getMock();

        $this->sut->shouldReceive('postSave')
            ->shouldReceive('getIdentifierIndex')
            ->andReturn('application')
            ->shouldReceive('getIdentifier')
            ->andReturn(123)
            ->shouldReceive('isButtonPressed')
            ->with('addAnother')
            ->andReturn(true)
            ->shouldReceive('redirect')
            ->andReturn($redirectMock)
            ->shouldReceive('params')
            ->with('action')
            ->andReturn('add');

        $this->sm->setService(
            'Helper\FlashMessenger',
            m::mock()
            ->shouldReceive('addSuccessMessage')
            ->with('section.add.fake-section')
            ->getMock()
        );

        $this->assertEquals(
            'redirect',
            $this->sut->callHandlePostSave($prefix, $options)
        );
    }

    public function testHandlePostSave()
    {
        $prefix = 'unit_Prdx';
        $options = ['unit_options'];

        $route = 'unit_Route';

        $redirectMock = m::mock()
            ->shouldReceive('toRouteAjax')
            ->with($route, ['application' => 123], $options)
            ->andReturn('redirect')
            ->getMock();

        $this->sut->shouldReceive('getBaseRoute')->once()->andReturn($route);

        $this->sut->shouldReceive('postSave')
            ->shouldReceive('getIdentifierIndex')
            ->andReturn('application')
            ->shouldReceive('getIdentifier')
            ->andReturn(123)
            ->shouldReceive('isButtonPressed')
            ->with('addAnother')
            ->andReturn(false)
            ->shouldReceive('redirect')
            ->andReturn($redirectMock)
            ->shouldReceive('params')
            ->with('action')
            ->andReturn('add');

        $this->sm->setService(
            'Helper\FlashMessenger',
            m::mock()
            ->shouldReceive('addSuccessMessage')
            ->with('section.add.fake-section')
            ->getMock()
        );

        $this->assertEquals(
            'redirect',
            $this->sut->callHandlePostSave($prefix, $options)
        );
    }

    public function testDeleteAction()
    {
        $request = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(false)
            ->getMock();

        $form = m::mock();

        $this->sut
            ->shouldReceive('getRequest')->once()->andReturn($request)
            ->shouldReceive('render')
            ->with('delete', $form, ['sectionText' => 'delete.confirmation.text'])
            ->andReturn('render');

        $this->sm->setService(
            'Helper\Form',
            m::mock()
            ->shouldReceive('createFormWithRequest')
            ->with('GenericDeleteConfirmation', $request)
            ->andReturn($form)
            ->getMock()
        );

        $this->assertEquals(
            'render',
            $this->sut->deleteAction()
        );
    }

    public function testDeleteActionWithPost()
    {
        $route = 'unit_Route';
        $queryParams = ['unit_queryParams'];

        $mockFlashMessenger = m::mock();
        $mockFlashMessenger->shouldReceive('addSuccessMessage');
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        $redirectMock = m::mock()
            ->shouldReceive('toRouteAjax')
            ->with(
                $route,
                [
                    'application' => 123
                ],
                [
                    'query' => $queryParams,
                ]
            )
            ->andReturn('redirect')
            ->getMock();

        $mockRequest = m::mock(\Zend\Http\Request::class);
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $mockRequest->shouldReceive('getQuery->toArray')->andReturn($queryParams);

        $this->sut
            ->shouldReceive('getBaseRoute')->once()->andReturn($route)
            ->shouldReceive('getRequest')->once()->andReturn($mockRequest)
            ->shouldReceive('getIdentifierIndex')
            ->andReturn('application')
            ->shouldReceive('getIdentifier')
            ->andReturn(123)
            ->shouldReceive('params')
            ->with('action')
            ->andReturn('delete')
            ->shouldReceive('delete')
            ->shouldReceive('postSave')
            ->shouldReceive('redirect')
            ->andReturn($redirectMock);

        $this->assertEquals(
            'redirect',
            $this->sut->deleteAction()
        );
    }
}
