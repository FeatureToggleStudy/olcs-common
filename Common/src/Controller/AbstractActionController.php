<?php
/**
 * An abstract controller that all ordinary OLCS controllers inherit from
 *
 * @package     olcscommon
 * @subpackage  controller
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Common\Controller;

abstract class AbstractActionController extends \Zend\Mvc\Controller\AbstractActionController
{
    use \Common\Util\ResolveApiTrait;
    use \Common\Util\LoggerTrait;
    use \Common\Util\FlashMessengerTrait;
    use \Common\Util\RestCallTrait;

    /**
     * Set navigation for breadcrumb
     * @param type $label
     * @param type $params
     */
    protected function setBreadcrumb($route, $params)
    {
        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('route', $route);
        $page->setParams($params);
    }

    /**
     * Get all request params from the query string and route and send back the required ones
     * @param type $keys
     * @return type
     */
    protected function getParams($keys)
    {
        $params = [];
        $getParams = array_merge($this->getEvent()->getRouteMatch()->getParams(), $this->getRequest()->getQuery()->toArray());
        foreach ($getParams as $key => $value) {
            if (in_array($key, $keys)) {
                $params[$key] = $value;
            }
        }
        return $params;
    }
    
    /**
     * Gets a from from either a built or custom form config.
     * @param type $type
     * @return type
     */
    protected function getForm($type) {
        $form = $this->getServiceLocator()->get('OlcsCustomForm')->createForm($type);
        return $form;
    }
    
    protected function getFormGenerator() {
        return $this->getServiceLocator()->get('OlcsCustomForm');
    }
    
    /**
     * Method to process posted form data and validate it and process a callback
     * @param type $form
     * @param type $callback
     * @return type
     */
    protected function formPost($form, $callback)
    {
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $validatedData = $form->getData();
                $params = ['validData' => $validatedData];
                if (is_callable($callback)) {
                    $callback(array('validData' => $params));
                }
                call_user_func_array(array($this, $callback), $params);
            }
        }
        return $form;
    }
}
