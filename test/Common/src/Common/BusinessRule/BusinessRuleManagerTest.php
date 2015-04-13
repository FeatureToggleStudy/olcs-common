<?php

/**
 * Business Rule Manager Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace CommonTest\BusinessRule;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use CommonTest\Bootstrap;
use Common\BusinessRule\BusinessRuleManager;

/**
 * Business Rule Manager Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessRuleManagerTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new BusinessRuleManager();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testConstructWithConfig()
    {
        $config = m::mock('\Zend\ServiceManager\ConfigInterface');

        $config->shouldReceive('configureServiceManager')
            ->with(m::type('\Common\BusinessRule\BusinessRuleManager'));

        new BusinessRuleManager($config);
    }

    public function testInitializeWithoutInterface()
    {
        $instance = m::mock();
        $instance->shouldReceive('setServiceLocator')
            ->never();

        $this->sut->initialize($instance);
    }

    public function testInitializeWithInterface()
    {
        $instance = m::mock('\Zend\ServiceManager\ServiceLocatorAwareInterface');
        $instance->shouldReceive('setServiceLocator')
            ->once()
            ->with($this->sm);

        $this->sut->initialize($instance);
    }

    public function testInitializeWithBusinessRuleAwareInterface()
    {
        $instance = m::mock('\Common\BusinessRule\BusinessRuleAwareInterface');
        $instance->shouldReceive('setBusinessRuleManager')
            ->once()
            ->with($this->sut);

        $this->sut->initialize($instance);
    }

    public function testValidatePluginInvalid()
    {
        $this->setExpectedException('\Zend\ServiceManager\Exception\RuntimeException');

        $plugin = m::mock();

        $this->sut->validatePlugin($plugin);
    }

    public function testValidatePlugin()
    {
        $plugin = m::mock('\Common\BusinessRule\BusinessRuleInterface');

        $this->assertNull($this->sut->validatePlugin($plugin));
    }
}