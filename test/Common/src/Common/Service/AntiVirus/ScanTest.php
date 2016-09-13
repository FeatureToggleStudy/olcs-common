<?php


namespace CommonTest\Service\AntVirus;

use Common\Service\AntiVirus\Scan;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Class ScanTest
 */
class ScanTest extends MockeryTestCase
{
    /**
     * @var Scan
     */
    private $sut;

    public function setup()
    {
        $this->sut = new Scan();
    }

    public function testCreateService()
    {
        $config = [
            'antiVirus' => [
                'cliCommand' => 'scanfile %s'
            ]
        ];
        $mockServiceManager = m::mock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $mockServiceManager->shouldReceive('get')->with('config')->once()->andReturn($config);

        $object = $this->sut->createService($mockServiceManager);

        $this->assertSame('scanfile %s', $object->getCliCommand());
    }

    public function testCreateServiceNoConfig()
    {
        $mockServiceManager = m::mock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $mockServiceManager->shouldReceive('get')->with('config')->once()->andReturn([]);

        $object = $this->sut->createService($mockServiceManager);

        $this->assertSame(null, $object->getCliCommand());
    }

    public function testIsEnabled()
    {
        $this->assertSame(false, $this->sut->isEnabled());
        $this->sut->setCliCommand('XXX');
        $this->assertSame(true, $this->sut->isEnabled());
    }

    public function testIsCleanMissingCommand()
    {
        $this->setExpectedException(\Common\Exception\ConfigurationException::class, 'Scan cliCommand is not set');
        $this->sut->isClean('foo.bar');
    }

    public function testIsCleanMissingCommandReplacement()
    {
        $this->sut->setCliCommand('XXX');
        $this->setExpectedException(
            \Common\Exception\ConfigurationException::class,
            '%s must be in the cliCommand, this is where the file to be scanned is inserted'
        );
        $this->sut->isClean('foo.bar');
    }

    public function testIsCleanFileNotString()
    {
        $this->sut->setCliCommand('scan %s');
        $this->setExpectedException(\InvalidArgumentException::class, 'file to scan must be a string');
        $this->sut->isClean(1);
    }

    public function testIsCleanFileNotExists()
    {
        $this->sut->setCliCommand('scan %s');
        $this->setExpectedException(\InvalidArgumentException::class, 'Cannot scan \'foo.bar\' as it does not exist');
        $this->sut->isClean('foo.bar');
    }

    public function testIsCleanOk()
    {
        $mockShell = m::mock(\Common\Filesystem\Shell::class);
        $mockShell->shouldReceive('execute')->with('scan '. __FILE__)->once()->andReturn(0);
        $this->sut->setShell($mockShell);

        $this->sut->setCliCommand('scan %s');
        $result = $this->sut->isClean(__FILE__);
        $this->assertSame(true, $result);
    }

    public function testIsCleanFailed()
    {
        $mockShell = m::mock(\Common\Filesystem\Shell::class);
        $mockShell->shouldReceive('execute')->with('scan '. __FILE__)->once()->andReturn(1);
        $this->sut->setShell($mockShell);

        $this->sut->setCliCommand('scan %s');
        $result = $this->sut->isClean(__FILE__);
        $this->assertSame(false, $result);
    }
}
