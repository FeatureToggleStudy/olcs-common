<?php

/**
 * Url Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Service\Helper;

/**
 * Url Helper Service
 *
 * @NOTE ZF2 has 2 different URL builder classes, one is a controller plugin, the other is a view helper.
 *  We have the requirement to build URLs outside of views and controllers, so this helper essentially wraps ZF2s url
 *  builder, but allows us to easily use it elsewhere.
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UrlHelperService extends AbstractHelperService
{
    const EXTERNAL_HOST = 'selfserve';
    const INTERNAL_HOST = 'internal';

    /**
     * Generates a URL based on a route
     *
     * @param  string             $route              RouteInterface name
     * @param  array|Traversable  $params             Parameters to use in url generation, if any
     * @param  array|bool         $options            RouteInterface-specific options to use in url generation, if any.
     *                                                If boolean, and no fourth argument, used as $reuseMatchedParams.
     * @param  bool               $reuseMatchedParams Whether to reuse matched parameters
     * @return string
     */
    public function fromRoute($route = null, $params = array(), $options = array(), $reuseMatchedParams = false)
    {
        $url = $this->getServiceLocator()->get('viewhelpermanager')->get('url');

        return $url($route, $params, $options, $reuseMatchedParams);
    }

    public function fromRouteWithHost(
        $hostKey,
        $route = null,
        $params = array(),
        $options = array(),
        $reuseMatchedParams = false
    ) {
        $hostname = $this->getHostname($hostKey);
        // this method isn't compatible with the canonical option
        $options['use_canonical'] = false;

        $url = $this->getServiceLocator()->get('viewhelpermanager')->get('url');

        return $hostname . $url($route, $params, $options, $reuseMatchedParams);
    }

    private function getHostname($key)
    {
        $config = $this->getServiceLocator()->get('config')['hostnames'];
        if (!isset($config[$key])) {
            throw new \RuntimeException('Hostname for \'' . $key . '\' not found');
        }
        return $config[$key];
    }
}
