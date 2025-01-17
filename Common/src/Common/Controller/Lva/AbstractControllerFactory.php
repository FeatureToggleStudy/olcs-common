<?php

namespace Common\Controller\Lva;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Abstract Controller Factory
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AbstractControllerFactory implements AbstractFactoryInterface
{
    /**
     * Determine if we can create a service with name
     *
     * @param \Zend\Mvc\Controller\ControllerManager $serviceLocator Controller service manager
     * @param string                                 $name           Name
     * @param string                                 $requestedName  Class Name
     *
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $config = $serviceLocator->getServiceLocator()->get('Config');

        return isset($config['controllers']['lva_controllers'][$requestedName]);
    }

    /**
     * Create service with name
     *
     * @param \Zend\Mvc\Controller\ControllerManager $serviceLocator Controller service manager
     * @param string                                 $name           Name
     * @param string                                 $requestedName  Class Name
     *
     * @return \Zend\Mvc\Controller\AbstractActionController
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        /** @var \Zend\ServiceManager\ServiceLocatorInterface $sm */
        $sm = $serviceLocator->getServiceLocator();

        $config = $sm->get('Config');

        $class = $config['controllers']['lva_controllers'][$requestedName];

        $controller = new $class;

        if ($controller instanceof FactoryInterface) {
            return $controller->createService($sm);
        }

        return $controller;
    }
}
