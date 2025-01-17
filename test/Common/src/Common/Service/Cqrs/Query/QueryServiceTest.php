<?php

namespace CommonTest\Service\Cqrs\Query;

use Common\Service\Cqrs\Exception;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Cqrs\Response as CqrsResponse;
use Common\Service\Helper\FlashMessengerHelperService;
use Dvsa\Olcs\Transfer\Query\LoggerOmitResponseInterface;
use Dvsa\Olcs\Transfer\Query\QueryContainerInterface;
use Dvsa\Olcs\Utils\Client\ClientAdapterLoggingWrapper;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Router\RouteInterface;

/**
 * @covers \Common\Service\Cqrs\Query\QueryService
 */
class QueryServiceTest extends MockeryTestCase
{
    private static $queryData = [
        'foo' => 'bar',
    ];

    /** @var  m\MockInterface|RouteInterface */
    private $mockRouter;
    /** @var  m\MockInterface|\Zend\Http\Client */
    private $mockCli;
    /** @var  m\MockInterface|\Zend\Http\Request */
    private $mockRequest;
    /** @var  m\MockInterface|FlashMessengerHelperService */
    private $mockFlashMsgr;

    /** @var  QueryService | m\MockInterface */
    private $sut;

    /** @var  QueryContainerInterface | m\MockInterface */
    private $mockQueryCntr;
    /** @var  m\MockInterface */
    private $mockQuery;

    public function setUp()
    {
        $this->mockRouter = m::mock(RouteInterface::class);
        $this->mockCli = m::mock(\Zend\Http\Client::class);
        $this->mockRequest = m::mock(\Zend\Http\Request::class);
        $this->mockFlashMsgr = m::mock(FlashMessengerHelperService::class);

        $this->sut = m::mock(
            QueryService::class . '[invalidResponse, showApiMessagesFromResponse]',
            [
                $this->mockRouter,
                $this->mockCli,
                $this->mockRequest,
                true,
                $this->mockFlashMsgr
            ]
        )
            ->shouldAllowMockingProtectedMethods();

        $this->mockQuery = m::mock(LoggerOmitResponseInterface::class)
            ->shouldReceive('getArrayCopy')->atMost(1)->andReturn(self::$queryData)
            ->getMock();

        $this->mockQueryCntr = m::mock(QueryContainerInterface::class)
            ->shouldReceive('getDto')->atMost(1)->andReturn($this->mockQuery)
            ->getMock();
    }

    public function testSendFailQueryIsNotValid()
    {
        $this->mockQueryCntr
            ->shouldReceive('isValid')->once()->andReturn(false)
            ->shouldReceive('getMessages')->once()->andReturn(['unit_MESSAGES']);

        $this->sut
            ->shouldReceive('invalidResponse')
            ->once()
            ->with(['unit_MESSAGES'], HttpResponse::STATUS_CODE_422)
            ->andReturn('unit_EXPECT');

        static::assertEquals('unit_EXPECT', $this->sut->send($this->mockQueryCntr));
    }

    public function testSendFailRouteException()
    {
        $this->mockQueryCntr
            ->shouldReceive('isValid')->once()->andReturn(true)
            ->shouldReceive('getRouteName')->once()->andReturn('unit_RouteName');

        $this->mockRouter
            ->shouldReceive('assemble')
            ->once()
            ->andThrow(new \Zend\Mvc\Router\Exception\RuntimeException('unit_ExcMsg'));

        $this->expectException(Exception::class, 'unit_ExcMsg', HttpResponse::STATUS_CODE_404);
        $this->sut->send($this->mockQueryCntr);
    }

    public function testSendFailHttpClientException()
    {
        $this->mockQueryCntr
            ->shouldReceive('isValid')->once()->andReturn(true)
            ->shouldReceive('getRouteName')->once()->andReturn('unit_RouteName');

        $this->mockRouter->shouldReceive('assemble')->once();

        $this->mockRequest
            ->shouldReceive('setUri')->once()->andReturn()
            ->shouldReceive('setMethod')->once()->andReturn();

        $this->mockCli
            ->shouldReceive('getAdapter')->once()->andReturn()
            ->shouldReceive('resetParameters')
            ->once()
            ->andThrow(new \Zend\Http\Client\Exception\RuntimeException('unit_ExcMsg'));

        $this->expectException(Exception::class, 'unit_ExcMsg', HttpResponse::STATUS_CODE_500);
        $this->sut->send($this->mockQueryCntr);
    }

    public function testSendRecoverHttpClientException()
    {
        $this->mockQueryCntr
            ->shouldReceive('isValid')->once()->andReturn(true)
            ->shouldReceive('getRouteName')->once()->andReturn('unit_RouteName');

        $this->sut->setRecoverHttpClientException(true);

        $this->mockRouter->shouldReceive('assemble')->once();

        $this->mockRequest
            ->shouldReceive('setUri')->once()->andReturn()
            ->shouldReceive('setMethod')->once()->andReturn();

        $this->mockCli
            ->shouldReceive('getAdapter')->once()->andReturn()
            ->shouldReceive('resetParameters')
            ->once()
            ->andThrow(new \Zend\Http\Client\Exception\RuntimeException('unit_ExcMsg'));

        $expectedResponse = new CqrsResponse((new HttpResponse())->setStatusCode(HttpResponse::STATUS_CODE_500));
        $response = $this->sut->send($this->mockQueryCntr);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testSendOk()
    {
        $this->mockQueryCntr
            ->shouldReceive('isValid')->once()->andReturn(true)
            ->shouldReceive('getRouteName')->once()->andReturn('backend/unit_RouteName')
            ->shouldReceive('isStream')->once()->andReturn('unit_IsStream');

        $uri = 'init_Uri';

        $this->mockRouter
            ->shouldReceive('assemble')
            ->once()
            ->with(self::$queryData, ['name' => 'api/backend/api/unit_RouteName/GET'])
            ->andReturn($uri);

        $mockAdapter = m::mock(ClientAdapterLoggingWrapper::class)
            ->shouldReceive('getShouldLogData')->once()->andReturn('unit_ShouldLogData')
            ->shouldReceive('setShouldLogData')->once()->with(false)
            ->shouldReceive('setShouldLogData')->once()->with('unit_ShouldLogData')
            ->getMock();

        $this->mockRequest
            ->shouldReceive('setUri')->once()->with($uri)->andReturn($mockAdapter)
            ->shouldReceive('setMethod')->once()->with(Request::METHOD_GET)->andReturn();

        $mockResp = m::mock(Response::class);
        $mockResp->shouldReceive('getStatusCode')->with()->atLeast()->times(1)
            ->andReturn(HttpResponse::STATUS_CODE_200);

        $this->mockCli
            ->shouldReceive('getAdapter')->once()->andReturn($mockAdapter)
            ->shouldReceive('resetParameters')->once()->with(true)
            ->shouldReceive('setStream')->once()->with('unit_IsStream')
            ->shouldReceive('send')->once()->with($this->mockRequest)->andReturn($mockResp);

        $this->sut
            ->shouldReceive('showApiMessagesFromResponse')
            ->once()
            ->with(m::type(CqrsResponse::class));

        static::assertInstanceOf(CqrsResponse::class, $this->sut->send($this->mockQueryCntr));
    }

    public function testSend404()
    {
        $this->mockQueryCntr
            ->shouldReceive('isValid')->once()->andReturn(true)
            ->shouldReceive('getRouteName')->once()->andReturn('backend/unit_RouteName')
            ->shouldReceive('isStream')->once()->andReturn('unit_IsStream');

        $uri = 'init_Uri';

        $this->mockRouter
            ->shouldReceive('assemble')
            ->once()
            ->with(self::$queryData, ['name' => 'api/backend/api/unit_RouteName/GET'])
            ->andReturn($uri);

        $mockAdapter = m::mock(ClientAdapterLoggingWrapper::class)
            ->shouldReceive('getShouldLogData')->once()->andReturn('unit_ShouldLogData')
            ->shouldReceive('setShouldLogData')->once()->with(false)
            ->shouldReceive('setShouldLogData')->once()->with('unit_ShouldLogData')
            ->getMock();

        $this->mockRequest
            ->shouldReceive('setUri')->once()->with($uri)->andReturn($mockAdapter)
            ->shouldReceive('setMethod')->once()->with(Request::METHOD_GET)->andReturn();

        $mockResp = m::mock(Response::class);
        $mockResp->shouldReceive('getStatusCode')->with()->atLeast()->times(1)
            ->andReturn(HttpResponse::STATUS_CODE_404);

        $this->mockCli
            ->shouldReceive('getAdapter')->once()->andReturn($mockAdapter)
            ->shouldReceive('resetParameters')->once()->with(true)
            ->shouldReceive('setStream')->once()->with('unit_IsStream')
            ->shouldReceive('send')->once()->with($this->mockRequest)->andReturn($mockResp);

        $this->expectException(Exception\NotFoundException::class, 'API responded with a 404 Not Found : '. $uri);
        $this->sut->send($this->mockQueryCntr);
    }

    public function testSend403()
    {
        $this->mockQueryCntr
            ->shouldReceive('isValid')->once()->andReturn(true)
            ->shouldReceive('getRouteName')->once()->andReturn('backend/unit_RouteName')
            ->shouldReceive('isStream')->once()->andReturn('unit_IsStream');

        $uri = 'init_Uri';

        $this->mockRouter
            ->shouldReceive('assemble')
            ->once()
            ->with(self::$queryData, ['name' => 'api/backend/api/unit_RouteName/GET'])
            ->andReturn($uri);

        $mockAdapter = m::mock(ClientAdapterLoggingWrapper::class)
            ->shouldReceive('getShouldLogData')->once()->andReturn('unit_ShouldLogData')
            ->shouldReceive('setShouldLogData')->once()->with(false)
            ->shouldReceive('setShouldLogData')->once()->with('unit_ShouldLogData')
            ->getMock();

        $this->mockRequest
            ->shouldReceive('setUri')->once()->with($uri)->andReturn($mockAdapter)
            ->shouldReceive('setMethod')->once()->with(Request::METHOD_GET)->andReturn();

        $mockResp = m::mock(Response::class);
        $mockResp->shouldReceive('getStatusCode')->with()->atLeast()->times(1)
            ->andReturn(HttpResponse::STATUS_CODE_403);
        $mockResp->shouldReceive('getBody')->with()->times(1)->andReturn('BODY');

        $this->mockCli
            ->shouldReceive('getAdapter')->once()->andReturn($mockAdapter)
            ->shouldReceive('resetParameters')->once()->with(true)
            ->shouldReceive('setStream')->once()->with('unit_IsStream')
            ->shouldReceive('send')->once()->with($this->mockRequest)->andReturn($mockResp);

        $this->expectException(Exception\AccessDeniedException::class, 'BODY : '. $uri);
        $this->sut->send($this->mockQueryCntr);
    }

    public function testSendGreaterThan400()
    {
        $this->mockQueryCntr
            ->shouldReceive('isValid')->once()->andReturn(true)
            ->shouldReceive('getRouteName')->once()->andReturn('backend/unit_RouteName')
            ->shouldReceive('isStream')->once()->andReturn('unit_IsStream');

        $uri = 'init_Uri';

        $this->mockRouter
            ->shouldReceive('assemble')
            ->once()
            ->with(self::$queryData, ['name' => 'api/backend/api/unit_RouteName/GET'])
            ->andReturn($uri);

        $mockAdapter = m::mock(ClientAdapterLoggingWrapper::class)
            ->shouldReceive('getShouldLogData')->once()->andReturn('unit_ShouldLogData')
            ->shouldReceive('setShouldLogData')->once()->with(false)
            ->shouldReceive('setShouldLogData')->once()->with('unit_ShouldLogData')
            ->getMock();

        $this->mockRequest
            ->shouldReceive('setUri')->once()->with($uri)->andReturn($mockAdapter)
            ->shouldReceive('setMethod')->once()->with(Request::METHOD_GET)->andReturn();

        $mockResp = m::mock(Response::class);
        $mockResp->shouldReceive('getStatusCode')->with()->atLeast()->times(1)
            ->andReturn(HttpResponse::STATUS_CODE_401);
        $mockResp->shouldReceive('getBody')->with()->times(1)->andReturn('BODY');

        $this->mockCli
            ->shouldReceive('getAdapter')->once()->andReturn($mockAdapter)
            ->shouldReceive('resetParameters')->once()->with(true)
            ->shouldReceive('setStream')->once()->with('unit_IsStream')
            ->shouldReceive('send')->once()->with($this->mockRequest)->andReturn($mockResp);

        $this->expectException(Exception::class, 'BODY : '. $uri);
        $this->sut->send($this->mockQueryCntr);
    }
}
