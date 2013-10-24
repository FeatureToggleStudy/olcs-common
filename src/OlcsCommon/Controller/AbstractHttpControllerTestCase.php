<?php
/**
 * An abstract controller that all ordinary OLCS controllers' tests inherit from
 *
 * @package     olcscommon
 * @subpackage  controller
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace OlcsCommon\Controller;

use \Mockery as m;

class AbstractHttpControllerTestCase extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{
    protected $resolverMock;
    protected $clientMocks = array();

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../../../config/test/application.config.php'
        );
        parent::setUp();

        $this->resolverMock = m::mock('OlcsCommon\Utility\ResolveApi');

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('ServiceApiResolver',  $this->resolverMock);
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * Mocks a specific service response
     *
     * @param  string $service    The name of the service to mock
     * @param  string $method     The name of the service request to mock
     * @param  mixed  $response   The response of the mocked service
     * @param  mixed  $parameters The expected request parameters for the mocked service, can eg. be \Mockery::any()
     * @return \Mockery\Expectation The configured Mockery expectation for the request
     */
    protected function mockService($service, $method, $response)
    {
        if (!isset($this->clientMock[$service])) {
            $this->clientMock[$service] =  m::mock('Olcs\Utility\RestClient');
            $this->resolverMock
                ->shouldReceive('getClient')
                ->with($service)
                ->andReturn($this->clientMock[$service]);
        }

        $expectation = $this->clientMock[$service]->shouldReceive($method)->andReturn($response);

        return $expectation;
    }
}
