<?php

namespace CommonTest\Service\Cqrs\Command;

use Common\Exception\ResourceConflictException;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Exception;
use Common\Service\Cqrs\Response as CqrsResponse;
use Common\Util\FileContent;
use Dvsa\Olcs\Transfer\Command\CommandContainer;
use Dvsa\Olcs\Transfer\Command\CommandContainerInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\LoggerOmitContentInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Http\Client\Exception\RuntimeException;
use Zend\Http\Headers;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Router\Exception\RuntimeException as RouterRuntimeException;

/**
 * @covers \Common\Service\Cqrs\Command\CommandService
 */
class CommandServiceTest extends MockeryTestCase
{
    const ROUTE_NAME = 'backend/aaa/bbb';
    const METHOD = 'POST';

    /** @var  CommandService */
    private $sut;

    /** @var  m\MockInterface */
    private $mockDto;
    /** @var  m\MockInterface | CommandContainerInterface */
    private $mockCmd;

    /** @var  m\MockInterface | \Zend\Mvc\Router\RouteInterface */
    private $mockRouter;
    /** @var  m\MockInterface | \Zend\Http\Client */
    private $mockClient;
    /** @var  m\MockInterface | \Zend\Http\Request */
    private $mockRequest;
    /** @var  m\MockInterface | \Common\Service\Helper\FlashMessengerHelperService */
    private $mockFlashMsgr;


    public function setUp()
    {
        $this->mockDto = m::mock(CommandInterface::class);
        $this->mockDto->shouldReceive('getArrayCopy')->atMost(1)->andReturn([]);

        $this->mockCmd = m::mock(CommandContainer::class);
        $this->mockCmd
            ->shouldReceive('getRouteName')->atMost(1)->andReturn(self::ROUTE_NAME)
            ->shouldReceive('getMethod')->atMost(1)->andReturn(self::METHOD)
            ->shouldReceive('getDto')->atMost(1)->andReturn($this->mockDto);

        $this->mockRouter = m::mock(\Zend\Mvc\Router\RouteInterface::class)->makePartial();
        $this->mockClient = m::mock(\Zend\Http\Client::class)->makePartial();
        $this->mockRequest = m::mock(\Zend\Http\Request::class)->makePartial();
        $this->mockFlashMsgr = m::mock(\Common\Service\Helper\FlashMessengerHelperService::class);

        $this->sut = new CommandService(
            $this->mockRouter,
            $this->mockClient,
            $this->mockRequest,
            true,
            $this->mockFlashMsgr
        );
    }

    public function testSend404ErrorWithRoute()
    {
        $this->mockCmd->shouldReceive('isValid')->once()->andReturn(true);
        $this->mockRouter->shouldReceive('assemble')->andThrow(new RouterRuntimeException('err_message'));
        $this->mockFlashMsgr->shouldReceive('addErrorMessage')->with('DEBUG: err_message');

        $this->expectException(Exception::class, 'err_message', 404);
        $this->sut->send($this->mockCmd);
    }

    public function testSend422()
    {
        $this->mockCmd
            ->shouldReceive('isValid')->once()->andReturn(false)
            ->shouldReceive('getMessages')->once()->andReturn(['EXPECT_MESSAGES']);

        $this->mockFlashMsgr->shouldReceive('addErrorMessage')->once()->andReturn('EXPECT_MESSAGES');

        $actual = $this->sut->send($this->mockCmd);

        $this->assertInvalidResponse($actual, 'EXPECT_MESSAGES', HttpResponse::STATUS_CODE_422);
    }

    public function testSend404()
    {
        $this->mockCmd->shouldReceive('isValid')->once()->andReturn(true);
        $this->mockRouter->shouldReceive('assemble')->once()->andReturn('unit_uri');

        $mockResp = m::mock(HttpResponse::class);
        $mockResp->shouldReceive('getStatusCode')->atLeast()->times(1)->andReturn(HttpResponse::STATUS_CODE_404);

        $this->mockClient
            ->shouldReceive('getAdapter')->once()->andReturn()
            ->shouldReceive('send')->once()->andReturn($mockResp);

        $this->expectException(
            Exception\NotFoundException::class,
            "API responded with a 404 Not Found : unit_uri"
        );
        $this->sut->send($this->mockCmd);
    }

    public function testSend403()
    {
        $this->mockCmd->shouldReceive('isValid')->once()->andReturn(true);
        $this->mockRouter->shouldReceive('assemble')->once()->andReturn('unit_uri');

        $mockResp = m::mock(HttpResponse::class);
        $mockResp->shouldReceive('getStatusCode')->atLeast()->times(1)->andReturn(HttpResponse::STATUS_CODE_403);
        $mockResp->shouldReceive('getBody')->with()->once()->andReturn('HTTP BODY');

        $this->mockClient
            ->shouldReceive('getAdapter')->once()->andReturn()
            ->shouldReceive('send')->once()->andReturn($mockResp);

        $this->expectException(Exception\AccessDeniedException::class, "HTTP BODY : unit_uri");
        $this->sut->send($this->mockCmd);
    }

    public function testSend500()
    {
        $this->mockCmd->shouldReceive('isValid')->once()->andReturn(true);
        $this->mockRouter->shouldReceive('assemble')->once()->andReturn('unit_uri');

        $mockResp = m::mock(HttpResponse::class);
        $mockResp->shouldReceive('getStatusCode')->atLeast()->times(1)->andReturn(HttpResponse::STATUS_CODE_500);
        $mockResp->shouldReceive('getBody')->with()->once()->andReturn('HTTP BODY');

        $this->mockClient
            ->shouldReceive('getAdapter')->once()->andReturn()
            ->shouldReceive('send')->once()->andReturn($mockResp);

        $this->expectException(Exception::class, "HTTP BODY : unit_uri");
        $this->sut->send($this->mockCmd);
    }

    public function testSendOtherException()
    {
        $this->mockCmd->shouldReceive('isValid')->once()->andReturn(true);
        $this->mockRouter->shouldReceive('assemble')->once()->andReturn('unit_uri');

        $this->mockClient
            ->shouldReceive('getAdapter')->once()->andReturn()
            ->shouldReceive('send')->once()->andThrow(RuntimeException::class, 'ERROR');

        $this->expectException(Exception::class, "ERROR");
        $this->sut->send($this->mockCmd);
    }

    public function testSend409()
    {
        $this->expectException(ResourceConflictException::class, 'Resource conflict');

        $this->mockCmd->shouldReceive('isValid')->once()->andReturn(true);
        $this->mockRouter->shouldReceive('assemble')->once()->andReturn('unit_uri');

        $mockResp = m::mock(HttpResponse::class);
        $mockResp->shouldReceive('getStatusCode')->once()->andReturn(HttpResponse::STATUS_CODE_409);

        $this->mockClient
            ->shouldReceive('getAdapter')->once()->andReturn()
            ->shouldReceive('send')->once()->andReturn($mockResp);

        $this->sut->send($this->mockCmd);
    }

    public function testSendFile()
    {
        //  mock command
        $dtoData = [
            'xfile' => new FileContent('unit_fileName', 'unit_mime'),
        ];
        $mockDto = m::mock(LoggerOmitContentInterface::class);
        $mockDto->shouldReceive('getArrayCopy')->once()->andReturn($dtoData);

        $mockCmd = m::mock(CommandContainer::class)
            ->shouldReceive('getRouteName')->once()->andReturn(self::ROUTE_NAME)
            ->shouldReceive('getMethod')->once()->andReturn(self::METHOD)
            ->shouldReceive('getDto')->times(3)->andReturn($mockDto)
            ->shouldReceive('isValid')->once()->andReturn(true)
            ->getMock();

        //  mock
        $this->mockRouter
            ->shouldReceive('assemble')->once()->andReturnUsing(
                function ($data, $path) use ($dtoData) {
                    static::assertEquals($dtoData, $data);
                    static::assertEquals(['name' => 'api/backend/api/aaa/bbb/' . self::METHOD], $path);

                    return 'unit_uri';
                }
            );

        $headers = new Headers();
        $headers->addHeaderLine('Content-type: should be removed');
        $headers->addHeaderLine('x-header: test for headers');

        $this->mockRequest->setHeaders($headers);
        $this->mockRequest
            ->shouldReceive('setUri')->once()->with('unit_uri')
            ->shouldReceive('setMethod')->once()->with(self::METHOD);

        $mockAdapter = m::mock(LoggerOmitContentInterface::class)
            ->shouldReceive('getShouldLogData')->once()->andReturn('shouldLog')
            ->shouldReceive('setShouldLogData')->once()->with(false)
            ->shouldReceive('setShouldLogData')->once()->with('shouldLog')
            ->getMock();

        $mockResp = m::mock(HttpResponse::class)->makePartial()
            ->shouldReceive('getStatusCode')->andReturn(HttpResponse::STATUS_CODE_200)
            ->shouldReceive('getBody')->once()->andReturn('{"key":"EXPECTED"}')
            ->getMock();

        $this->mockClient
            ->shouldReceive('getAdapter')->once()->andReturn($mockAdapter)
            ->shouldReceive('setFileUpload')->once()->with('unit_fileName', 'xfile', null, 'unit_mime')
            ->shouldReceive('send')->once()->andReturn($mockResp);

        //  call & check
        $actual = $this->sut->send($mockCmd);

        static::assertEquals(1, $this->mockClient->getRequest()->getHeaders()->count());
        static::assertInstanceOf(CqrsResponse::class, $actual);
        static::assertEquals(['key' => 'EXPECTED'], $actual->getResult());
    }

    private function assertInvalidResponse(CqrsResponse $actual, $message, $statusCode)
    {
        static::assertInstanceOf(CqrsResponse::class, $actual);
        static::assertStringStartsWith($message, current($actual->getResult()['messages']));
        static::assertEquals($statusCode, $actual->getHttpResponse()->getStatusCode());
    }
}
