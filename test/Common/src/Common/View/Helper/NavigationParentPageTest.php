<?php

namespace CommonTest\View\Helper;

use Common\View\Helper\NavigationParentPage;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Common\View\Helper\NavigationParentPage
 */
class NavigationParentPageTest extends MockeryTestCase
{
    /** @var  m\MockInterface */
    private $mockBreadcrumbs;

    /** @var  \Zend\View\Renderer\RendererInterface */
    private $mockView;

    /**
     * Setup the view helper
     */
    public function setUp()
    {
        $this->mockBreadcrumbs = m::mock();

        $this->mockView = m::mock(\Zend\View\Renderer\RendererInterface::class);
        $this->mockView->shouldReceive('navigation->breadcrumbs')->once()->andReturn($this->mockBreadcrumbs);
    }

    /**
     * @dataProvider dpTestInvoke
     */
    public function testInvoke($activePage, $expect)
    {
        $mockContainer = m::mock();

        $this->mockBreadcrumbs
            ->shouldReceive('getContainer')->once()->andReturn($mockContainer)
            ->shouldReceive('findActive')->once()->with($mockContainer)->andReturn($activePage);

        $sut = (new NavigationParentPage())
            ->setView($this->mockView);

        static::assertEquals($expect, $sut->__invoke());
    }

    public function dpTestInvoke()
    {
        return [
            [
                'activePage' => [],
                'expect' => null,
            ],
            [
                'activePage' => [
                    'page' => m::mock(\Zend\Navigation\Page\Mvc::class)
                        ->shouldReceive('getParent')->atMost(1)->andReturn('EXPECT')
                        ->getMock(),
                ],
                'expect' => 'EXPECT',
            ],
        ];
    }
}
