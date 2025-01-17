<?php

namespace CommonTest\Service\Cqrs\Command;

use Common\Service\Cqrs\Command\CommandSender;
use CommonTest\Bootstrap;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Command Sender Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CommandSenderTest extends MockeryTestCase
{
    protected $sut;

    protected $commandService;
    protected $annotationBuilder;

    public function setUp()
    {
        $this->sut = new CommandSender();

        $this->commandService = m::mock();
        $this->annotationBuilder = m::mock();

        $sm = Bootstrap::getServiceManager();
        $sm->setService('CommandService', $this->commandService);
        $sm->setService('TransferAnnotationBuilder', $this->annotationBuilder);

        $service = $this->sut->createService($sm);

        $this->assertSame($service, $this->sut);
    }

    public function testSend()
    {
        $command = m::mock(CommandInterface::class);
        $constructedCommand = m::mock();
        $response = m::mock();

        $this->annotationBuilder->shouldReceive('createCommand')
            ->once()
            ->with($command)
            ->andReturn($constructedCommand);

        $this->commandService->shouldReceive('send')
            ->once()
            ->with($constructedCommand)
            ->andReturn($response);

        $this->assertSame($response, $this->sut->send($command));
    }
}
