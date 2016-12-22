<?php

namespace Common\Service\Cqrs\Query;

use Common\Service\Cqrs\CqrsTrait;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\FlashMessengerHelperService;
use Dvsa\Olcs\Transfer\Query\LoggerOmitResponseInterface;
use Dvsa\Olcs\Transfer\Query\QueryContainerInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Zend\Http\Client;
use Zend\Http\Client\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Zend\Http\Request;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Router\Exception\ExceptionInterface;
use Zend\Mvc\Router\RouteInterface;

/**
 * Query
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class QueryService implements QueryServiceInterface
{
    use CqrsTrait;

    /** @var RouteInterface */
    protected $router;
    /** @var Client */
    protected $client;
    /** @var Request */
    protected $request;

    /**
     * QueryService constructor.
     *
     * @param RouteInterface              $router          Router
     * @param Client                      $client          Http Client
     * @param Request                     $request         Http Request
     * @param boolean                     $showApiMessages Is Show Api Messages
     * @param FlashMessengerHelperService $flashMessenger  Flash messeger service
     *
     * @return void
     */
    public function __construct(
        RouteInterface $router,
        Client $client,
        Request $request,
        $showApiMessages,
        FlashMessengerHelperService $flashMessenger
    ) {
        $this->router = $router;
        $this->client = $client;
        $this->request = $request;
        $this->showApiMessages = $showApiMessages;
        $this->flashMessenger = $flashMessenger;
    }

    /**
     * Send a query and return the response
     *
     * @param QueryContainerInterface $query Query container
     *
     * @return Response
     */
    public function send(QueryContainerInterface $query)
    {
        if (!$query->isValid()) {
            return $this->invalidResponse($query->getMessages(), HttpResponse::STATUS_CODE_422);
        }

        $routeName = $query->getRouteName();

        /** @var QueryInterface $queryDto */
        $queryDto = $query->getDto();

        try {
            // @todo Tmp replace route name to prefix with api while we migrate all services
            $routeName = str_replace('backend/', 'backend/api/', $routeName);
            $uri = $this->router->assemble(
                $queryDto->getArrayCopy(),
                ['name' => 'api/' . $routeName . '/GET']
            );
        } catch (ExceptionInterface $ex) {
            return $this->invalidResponse([$ex->getMessage()], HttpResponse::STATUS_CODE_404);
        }

        $this->request->setUri($uri);
        $this->request->setMethod(Request::METHOD_GET);

        /** @var \Dvsa\Olcs\Utils\Client\ClientAdapterLoggingWrapper $adapter */
        $adapter = $this->client->getAdapter();

        try {
            $this->client->resetParameters(true);

            $shouldLogContent = true;
            $isOmitLog = ($queryDto instanceof LoggerOmitResponseInterface);

            if ($isOmitLog) {
                $shouldLogContent = $adapter->getShouldLogData();
                $adapter->setShouldLogData(false);
            }

            //  request should use stream for query or reset
            $this->client->setStream($query->isStream());

            $clientResponse = $this->client->send($this->request);

            if ($isOmitLog) {
                $adapter->setShouldLogData($shouldLogContent);
            }

            $response = new Response($clientResponse);

            if ($this->showApiMessages) {
                $this->showApiMessagesFromResponse($response);
            }

            return $response;

        } catch (HttpClientExceptionInterface $ex) {
            return $this->invalidResponse([$ex->getMessage()], HttpResponse::STATUS_CODE_500);
        }
    }
}
