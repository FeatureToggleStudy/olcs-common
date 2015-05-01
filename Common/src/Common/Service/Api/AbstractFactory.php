<?php

namespace Common\Service\Api;

use Common\Util\RestClient;
use Zend\Filter\Word\CamelCaseToDash;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\Exception\InvalidServiceNameException;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Uri\Http;

/**
 * Class AbstractFactory
 * @package Common\Service\Api
 */
class AbstractFactory implements AbstractFactoryInterface
{
    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return strpos($requestedName, 'Olcs\\RestService\\') !== false;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @throws \Zend\ServiceManager\Exception\InvalidServiceNameException
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $api = str_replace('Olcs\\RestService\\', '', $requestedName);

        $api = explode('\\', $api);
        if (count($api) == 1) {
            array_unshift($api, 'backend');
        }

        list($endpoint, $uri) = $api;

        $config = $serviceLocator->getServiceLocator()->get('Config');
        if (!isset($config['service_api_mapping']['endpoints'][$endpoint])) {
            throw new InvalidServiceNameException('No endpoint defined for: ' . $endpoint);
        }

        /** @var \Zend\Mvc\I18n\Translator $translator */
        $translator = $serviceLocator->getServiceLocator()->get('translator');

        $filter = new CamelCaseToDash();
        $uri = strtolower($filter->filter($uri));
        $url = new Http($uri);
        $url->resolve($config['service_api_mapping']['endpoints'][$endpoint]);

        $rest = new RestClient($url);
        $rest->setLanguage($translator->getLocale());

        return $rest;
    }
}
