<?php

namespace CommonTest\Controller\Plugin;

use Common\Controller\Plugin\FeaturesEnabledForMethod;
use Common\Service\Cqrs\Query\QuerySender;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class FeaturesEnabledForMethodTest extends MockeryTestCase
{
    protected $querySender;

    protected function setUp()
    {
        $this->querySender = m::mock(QuerySender::class);
    }

    public function testInvoke()
    {
        $toggleConfig = [
            'methodName' => ['toggleName']
        ];
        $this->querySender->shouldReceive('featuresEnabled')->once()->with(['toggleName'])->andReturn(true);
        $sut = new FeaturesEnabledForMethod($this->querySender);
        $this->assertEquals(true, $sut->__invoke($toggleConfig, 'methodName'));
    }

    public function testInvokeWithEmptyConfig()
    {
        $this->querySender->shouldNotReceive('featuresEnabled');
        $sut = new FeaturesEnabledForMethod($this->querySender);
        $this->assertEquals(false, $sut->__invoke([], 'wrong method name'));
    }

    public function testInvokeWithNoToggleConfig()
    {
        $toggleConfig = [
            'methodName' => []
        ];
        $sut = new FeaturesEnabledForMethod($this->querySender);
        $this->assertEquals(true, $sut->__invoke($toggleConfig, 'methodName'));
    }
}
