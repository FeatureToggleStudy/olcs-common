<?php

/**
 * BusinessType Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Controller\Application\YourBusiness;

use CommonTest\Controller\Application\AbstractApplicationControllerTestCase;
use Common\Controller\Application\Application\ApplicationController;

/**
 * BusinessType Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessTypeControllerTest extends AbstractApplicationControllerTestCase
{

    protected $controllerName = '\Common\Controller\Application\YourBusiness\BusinessTypeController';
    protected $defaultRestResponse = array();

    /**
     * Test back button
     */
    public function testBackButton()
    {
        $this->setUpAction('index', null, array('form-actions' => array('back' => 'Back')));

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction
     */
    public function testIndexAction()
    {
        $this->setUpAction('index');

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Mock the rest call
     *
     * @param string $service
     * @param string $method
     * @param array $data
     * @param array $bundle
     */
    protected function mockRestCalls($service, $method, $data = array(), $bundle = array())
    {
        if ($service == 'Application' && $method == 'GET'
            && $bundle == ApplicationController::$applicationLicenceDataBundle) {

            return $this->getLicenceData('goods');
        }

        if ($service == 'ApplicationCompletion' && $method == 'GET') {

            return $this->getApplicationCompletionData();
        }
    }
}
