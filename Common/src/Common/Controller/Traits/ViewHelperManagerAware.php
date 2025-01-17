<?php

namespace Common\Controller\Traits;

/**
 * View Helper Manager Aware Trait.
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
trait ViewHelperManagerAware
{
    /**
     * Returns the view helper plugin manager.
     *
     * @return \Zend\View\HelperPluginManager
     */
    public function getViewHelperManager()
    {
        return $this->getServiceLocator()->get('viewHelperManager');
    }
}
