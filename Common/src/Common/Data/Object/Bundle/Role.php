<?php

namespace Common\Data\Object\Bundle;

use Common\Data\Object\Bundle;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class Licence
 * @package Common\Data\Object\Bundle
 */
class Role extends Bundle
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     */
    protected function doInit(ServiceLocatorInterface $serviceLocator)
    {
        $permission = new Bundle();
        $permission->addChild('permission');
        $this->addChild('rolePermissions', $permission);
    }
}
