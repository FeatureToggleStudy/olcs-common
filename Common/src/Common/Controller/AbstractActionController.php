<?php

/**
 * An abstract controller that all ordinary OLCS controllers inherit from
 *
 * @author Pelle Wessman <pelle.wessman@valtech.se>
 * @author Michael Cooperr <michael.cooper@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Common\Controller;

use Common\Util;
use Zend\Mvc\MvcEvent;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Http\Response;
use Zend\InputFilter\Input;
use Zend\View\Model\ViewModel;
use Zend\InputFilter\InputFilter;
use Zend\Validator\ValidatorChain;
use Common\Form\Elements\Types\Address;

/**
 * An abstract controller that all ordinary OLCS controllers inherit from
 *
 * @author Pelle Wessman <pelle.wessman@valtech.se>
 * @author Michael Cooperr <michael.cooper@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class AbstractActionController extends \Zend\Mvc\Controller\AbstractActionController
{
    use Util\LoggerTrait,
        Util\FlashMessengerTrait,
        Util\RestCallTrait,
        Traits\ViewHelperManagerAware,
        Traits\GenericRenderView;

    private $loggedInUser;

    /**
     * These properties must be defined to use the GenericRenderView trait
     */
    protected $pageTitle = null;
    protected $pageSubTitle = null;
    protected $pageLayout = null;
    protected $headerViewTemplate = 'partials/header';

    /**
     * onDispatch now populates this with the route for the index of
     * the controller curently being executed.
     *
     * @var array
     */
    protected $indexRoute = [];

    /**
     * Holds any inline scripts for the current page
     *
     * @var array
     */
    protected $inlineScripts = [];

    protected $enableCsrf = true;

    protected $validateForm = true;

    protected $persist = true;

    protected $fieldValues = array();

    /**
     * Holds the caught response
     *
     * @var mixed
     */
    protected $caughtResponse = null;

    /**
     * Holds the loaded data
     *
     * @var array
     */
    protected $loadedData;

    /**
     * Holds the layout
     *
     * @var string
     */
    protected $layout;

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'data'
            )
        )
    );

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = null;

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = null;

    /**
     * Holds the form tables
     *
     * @var array
     */
    protected $formTables;

    protected $sectionServiceName;
    protected $sectionServices = array();

    /**
     * @codeCoverageIgnore
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->setupIndexRoute($e);

        $this->preOnDispatch();
        parent::onDispatch($e);
    }

    /**
     * Sets up the index route array.
     *
     * @codeCoverageIgnore
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function setupIndexRoute(\Zend\Mvc\MvcEvent $e)
    {
        if (empty($this->indexRoute)) {
            $this->indexRoute = [
                $e->getRouteMatch()->getMatchedRouteName(),
                array_merge($this->params()->fromRoute(), ['action' => 'index', 'id' => null])
            ];
        }
    }

    /**
     * Olcs specific onDispatch method.
     */
    public function preOnDispatch()
    {
        $response = $this->getResponse();
        $headers = $response->getHeaders();

        $headers->addHeaderLine('Cache-Control', 'no-cache, must-revalidate');
        $headers->addHeaderLine('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');

        $this->setLoggedInUser(1);
    }

    /**
     * Does what it says on the tin.
     *
     * @codeCoverageIgnore
     * @return mixed
     */
    public function redirectToIndex()
    {
        return call_user_func_array([$this->redirect(), 'toRoute'], $this->indexRoute);
    }

    /**
     * Set navigation for breadcrumb
     * @param type $label
     * @param type $params
     */
    public function setBreadcrumb($navRoutes = array())
    {
        foreach ($navRoutes as $route => $routeParams) {
            $navigation = $this->getServiceLocator()->get('navigation');
            $page = $navigation->findBy('route', $route);
            if ($page) {
                $page->setParams($routeParams);
            }
        }
    }

    /**
     * Sets loaded data
     *
     * @param array $loadedData
     * @return $this
     */
    public function setLoadedData($loadedData)
    {
        $this->loadedData = $loadedData;
        return $this;
    }

    /**
     * Returns the loaded data
     *
     * @return array
     */
    public function getLoadedData()
    {
        return $this->loadedData;
    }

    /**
     * Get all request params from the query string and route and send back the required ones
     * @param type $keys
     * @return type
     */
    public function getParams($keys)
    {
        $params = [];
        $getParams = $this->getAllParams();
        foreach ($getParams as $key => $value) {
            if (in_array($key, $keys)) {
                $params[$key] = $value;
            }
        }
        return $params;
    }

    public function getAllParams()
    {
        $getParams = array_merge(
            $this->getEvent()->getRouteMatch()->getParams(),
            $this->getRequest()->getQuery()->toArray()
        );

        return $getParams;
    }

    /**
     * Check for crud actions
     *
     * @param string $route
     * @param array $params
     * @param string $itemIdParam
     *
     * @return boolean
     */
    protected function checkForCrudAction($route = null, $params = array(), $itemIdParam = 'id')
    {
        $action = $this->getCrudActionFromPost();

        if (empty($action)) {
            return false;
        }

        if (!is_array($action)) {
            $action = strtolower($action);
        }

        $response = $this->checkForAlternativeCrudAction($action);

        if ($response instanceof Response) {
            return $response;
        }

        $params = array_merge($params, array('action' => $action));

        $options = array();

        $action = $this->getActionFromFullActionName($action);

        if (!in_array($action, $this->getNoActionIdentifierRequired())) {

            $post = (array)$this->getRequest()->getPost();

            if (isset($post['table']['id'])) {
                $id = $post['table']['id'];
            } else {
                $id = $this->params()->fromPost('id');
            }

            if (empty($id)) {

                $this->crudActionMissingId();
                return false;
            }

            if (is_array($id) && count($id) === 1) {
                $id = $id[0];
            }

            // If we have an array of id's we need to use a query string param rather than the route
            if (is_array($id)) {
                $options = array(
                    'query' => array(
                        $itemIdParam => $id
                    )
                );
            } else {
                $params[$itemIdParam] = $id;
            }
        }

        return $this->redirect()->toRoute($route, $params, $options, true);
    }

    protected function getNoActionIdentifierRequired()
    {
        return array('add');
    }

    /**
     * Get the last part of the action from the action name
     *
     * @return string
     */
    protected function getActionFromFullActionName($action = null)
    {
        if ($action == null) {
            return '';
        }

        if (!strstr($action, '-')) {
            return $action;
        }

        $parts = explode('-', $action);
        return array_pop($parts);
    }

    /**
     * Do nothing, this method can be overridden to hijack the crud action check
     *
     * @param string $action
     */
    protected function checkForAlternativeCrudAction($action)
    {

    }

    /**
     * We can now extend our check for crud action
     *
     * @return string
     */
    protected function getCrudActionFromPost()
    {
        $post = (array)$this->getRequest()->getPost();

        if (isset($post['table']['action'])) {
            return $post['table']['action'];
        }

        if (isset($post['action'])) {
            return $post['action'];
        }

        return null;
    }

    /**
     * Called when a crud action is missing a required ID
     */
    protected function crudActionMissingId()
    {
        $this->addWarningMessage('Please select a row');
        return $this->redirectToRoute(null, array(), array(), true);
    }

    /**
     * Build a table from config and results, and return the table object
     *
     * @param string $table
     * @param array $results
     * @param array $data
     * @return string
     */
    public function getTable($table, $results, $data = array())
    {
        if (!isset($data['url'])) {
            $data['url'] = $this->getPluginManager()->get('url');
        }

        return $this->getServiceLocator()->get('Table')->buildTable($table, $results, $data, false);
    }

    /**
     * Return a new view model
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function getViewModel($params = array())
    {
        return new ViewModel($params);
    }

    /**
     * Get url from route
     *
     * @param string $route
     * @return string
     */
    public function getUrlFromRoute($route, $params = array())
    {
        return $this->url()->fromRoute($route, $params);
    }

    /**
     * Wraps the redirect()->toRoute to help with unit testing
     *
     * @param string $route
     * @param array $params
     * @param array $options
     * @param bool $reuse
     * @return \Zend\Http\Response
     */
    public function redirectToRoute($route = null, $params = array(), $options = array(), $reuse = false)
    {
        return $this->redirect()->toRoute($route, $params, $options, $reuse);
    }

    /**
     * Wraps the redirect()->toRouteAjax method to help with unit testing
     *
     * @param string $route
     * @param array $params
     * @param array $options
     * @param bool $reuse
     * @return \Zend\Http\Response
     */
    public function redirectToRouteAjax($route = null, $params = array(), $options = array(), $reuse = false)
    {
        return $this->redirect()->toRouteAjax($route, $params, $options, $reuse);
    }

    /**
     * Get param from route
     *
     * @param string $name
     * @return string
     */
    public function getFromRoute($name)
    {
        return $this->params()->fromRoute($name);
    }

    /**
     * Get param from post
     *
     * @param string $name
     * @return string
     */
    public function getFromPost($name)
    {
        return $this->params()->fromPost($name);
    }

    public function getLoggedInUser()
    {
        return $this->loggedInUser;
    }

    public function setLoggedInUser($id)
    {
        $this->loggedInUser = $id;
        return $this;
    }

    /**
     * Get uploader
     *
     * @return object
     */
    public function getUploader()
    {
        return $this->getServiceLocator()->get('FileUploader')->getUploader();
    }

    /*
     * Load an array of script files which will be rendered inline inside a view
     *
     * @param array $scripts
     * @return array
     */
    protected function loadScripts($scripts)
    {
        return $this->getServiceLocator()->get('Script')->loadFiles($scripts);
    }

    /**
     * Get the inline scripts
     *
     * @return array
     */
    public function getInlineScripts()
    {
        return $this->inlineScripts;
    }

    /**
     * Set the inline scripts
     *
     * @param array $inlineScripts
     * @return array
     */
    public function setInlineScripts($inlineScripts)
    {
        $this->inlineScripts = $inlineScripts;
        return $this;
    }

    protected function attachDefaultListeners()
    {
        parent::attachDefaultListeners();
        if ($this instanceof CrudInterface) {
            $this->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'cancelButtonListener'), 100);
        }
    }

    /**
     * Allow csrf to be enabled and disabled
     */
    public function setEnabledCsrf($boolean = true)
    {
        $this->enableCsrf = $boolean;
    }

    /**
     * Get enableCsrf flag
     *
     * @return bool
     */
    public function getEnabledCsrf()
    {
        return $this->enableCsrf;
    }

    /**
     * Switch form validation on or off
     *
     * @param boolean $validateForm
     */
    protected function setValidateForm($validateForm = true)
    {
        $this->validateForm = $validateForm;
    }

    /**
     * Switch form persistence on or off
     *
     * @param boolean $persist
     */
    protected function setPersist($persist = true)
    {
        if ($this->getSectionServiceName() !== null) {
            $this->getSectionService()->setPersist($persist);
        } else {
            $this->persist = $persist;
        }
    }

    /**
     * Check whether we are persisting
     *
     * @return boolean
     */
    protected function getPersist()
    {
        if ($this->getSectionServiceName() !== null) {
            return $this->getSectionService()->getPersist();
        }

        return $this->persist;
    }

    /**
     * set the field value for a given key. This allows us
     * to override form data which has been previously set
     *
     * @param string $key
     * @param mixed $value
     */
    protected function setFieldValue($key, $value)
    {
        $this->fieldValues[$key] = $value;
    }

    protected function normaliseFormName($name, $ucFirst = false)
    {
        $name = str_replace([' ', '_'], '-', $name);

        $name = $this->getServiceLocator()->get('Helper\String')->dashToCamel($name);

        if (!$ucFirst) {
            return lcfirst($name);
        }

        return $name;
    }

    /**
     * @param $type
     * @return \Zend\Form\Form
     * @TO-DO Turn this into a proper service/factory for forms
     */
    protected function getFormClass($type)
    {
        return $this->getServiceLocator()->get('Helper\Form')->createForm(
            $this->normaliseFormName($type, true),
            $this->getEnabledCsrf()
        );
    }

    /**
     * Gets a from from either a built or custom form config.
     * @param type $type
     * @return type
     */
    public function getForm($type)
    {
        $form = $this->getFormClass($type);

        // The vast majority of forms thus far don't have actions, but
        // that means when rendered out of context (e.g. in a JS modal) they
        // submit the parent page.
        // Adding an explicit attribute should be completely backwards compatible
        // because browsers interpret no action as submit the current page
        if (!$form->hasAttribute('action')) {
            $form->setAttribute('action', $this->getRequest()->getUri()->getPath());
        }

        $form = $this->processPostcodeLookup($form);

        return $form;
    }

    /**
     * Process the postcode lookup functionality
     *
     * @param Form $form
     * @return Form
     */
    protected function processPostcodeLookup($form)
    {
        $request = $this->getRequest();

        $post = array();

        if ($request->isPost()) {

            $post = (array)$request->getPost();
        }

        $fieldsets = $form->getFieldsets();

        foreach ($fieldsets as $fieldset) {

            if ($fieldset instanceof Address) {

                $removeSelectFields = false;

                $name = $fieldset->getName();

                // If we haven't posted a form, or we haven't clicked find address
                if (isset($post[$name]['searchPostcode']['search'])
                    && !empty($post[$name]['searchPostcode']['search'])) {

                    $this->persist = false;

                    $postcode = trim($post[$name]['searchPostcode']['postcode']);

                    if (empty($postcode)) {

                        $removeSelectFields = true;

                        $fieldset->get('searchPostcode')->setMessages(
                            array('Please enter a postcode')
                        );
                    } else {

                        try {
                            $addressList = $this->getAddressesForPostcode($postcode);

                            if (empty($addressList)) {

                                $removeSelectFields = true;

                                $fieldset->get('searchPostcode')->setMessages(
                                    array('No addresses found for postcode')
                                );

                            } else {

                                $fieldset->get('searchPostcode')->get('addresses')->setValueOptions(
                                    $this->getAddressService()->formatAddressesForSelect($addressList)
                                );
                            }
                        } catch (\Exception $e) {
                            $fieldset->get('searchPostcode')->setMessages(
                                array('postcode.error.not-available')
                            );
                            $removeSelectFields = true;
                        }

                    }
                } elseif (isset($post[$name]['searchPostcode']['select'])
                    && !empty($post[$name]['searchPostcode']['select'])) {

                    $this->persist = false;

                    $address = $this->getAddressForUprn($post[$name]['searchPostcode']['addresses']);

                    $removeSelectFields = true;

                    $addressDetails = $this->getAddressService()->formatPostalAddressFromBs7666($address);

                    $this->fieldValues[$name] = array_merge($post[$name], $addressDetails);

                } else {

                    $removeSelectFields = true;
                }

                if ($removeSelectFields) {
                    $fieldset->get('searchPostcode')->remove('addresses');
                    $fieldset->get('searchPostcode')->remove('select');
                }
            }
        }

        return $form;
    }

    protected function getAddressService()
    {
        return $this->getServiceLocator()->get('Helper\Address');
    }

    protected function getAddressForUprn($uprn)
    {
        return $this->sendGet('postcode\address', array('id' => $uprn), true);
    }

    protected function getAddressesForPostcode($postcode)
    {
        return $this->sendGet('postcode\address', array('postcode' => $postcode), true);
    }

    protected function alterFormBeforeValidation($form)
    {
        return $form;
    }

    /**
     * Method to process posted form data and validate it and process a callback
     * @param type $form
     * @param type $callback
     * @return \Zend\Form
     */
    public function formPost($form, $callback = null, $additionalParams = array())
    {
        if (!$this->enableCsrf) {
            $form->remove('csrf');
        }

        $form = $this->alterFormBeforeValidation($form);

        if ($this->getRequest()->isPost()) {

            $data = array_merge(
                (array)$this->getRequest()->getPost(),
                $this->fieldValues
            );

            $form->setData($data);

            $form = $this->postSetFormData($form);

            /**
             * validateForm is true by default, we set it to false if we want to continue processing the form without
             * validation.
             */
            if (!$this->validateForm || ($this->getPersist() && $form->isValid())) {

                if ($this->validateForm) {
                    $validatedData = $form->getData();
                } else {
                    $validatedData = $data;
                }

                $params = [
                    'validData' => $validatedData,
                    'form' => $form,
                    'params' => $additionalParams
                ];

                $this->callCallbackIfExists($callback, $params);
            } elseif (!$this->validateForm || ($this->getPersist() && !$form->isValid())) {
                if (method_exists($this, 'onInvalidPost')) {
                    $this->onInvalidPost($form);
                }
            }
        }

        return $form;
    }

    /**
     * Added extra method called after setting form data
     *
     * @param Form $form
     * @return Form
     */
    protected function postSetFormData($form)
    {
        // @NOTE progressivly start to include the logic within a service
        //  but maintain the old logic for backwards compatability
        if ($this->getSectionServiceName() !== null) {
            return $this->getSectionService()->postSetFormData($form);
        }

        return $form;
    }

    /**
     * Calls the callback function/method if exists.
     *
     * @param unknown_type $callback
     * @param unknown_type $params
     * @throws \Exception
     */
    public function callCallbackIfExists($callback, $params)
    {
        if (is_callable($callback)) {
            $callback($params);
        } elseif (is_callable(array($this, $callback))) {
            call_user_func_array(array($this, $callback), $params);
        } elseif (!empty($callback)) {
            throw new \Exception('Invalid form callback: ' . $callback);
        }
    }

    /**
     * Generate a form with a callback
     *
     * @param string $name
     * @param callable $callback
     * @param boolean $tables
     * @return object
     */
    protected function generateForm($name, $callback, $tables = false)
    {
        $form = $this->getForm($name);

        if ($tables) {
            return $form;
        }

        return $this->formPost($form, $callback);
    }

    /**
     * Create a table form with data
     *
     * @param string $name
     * @param array $callbacks
     * @param mixed $data
     * @param array $tables
     * @return object
     */
    public function generateTableFormWithData($name, $callbacks, $data = null, $tables = array())
    {
        $callback = $callbacks['success'];

        $form = $this->generateFormWithData($name, $callbacks['success'], $data, true);

        foreach ($tables as $fieldsetName => $details) {

            $table = $this->getTable(
                $details['config'],
                $details['data'],
                (isset($details['variables']) ? $details['variables'] : array())
            );

            $form->get($fieldsetName)->get('table')->setTable($table, $fieldsetName);
            $form->get($fieldsetName)->get('rows')->setValue(count($table->getRows()));
        }

        $postData = null;

        if ($this->getRequest()->isPost()) {

            $postData = (array)$this->getRequest()->getPost();
        }

        foreach ($tables as $fieldsetName => $details) {

            if (
                !is_null($postData)
                && isset($postData[$fieldsetName]['action'])
                && !empty($postData[$fieldsetName]['action'])
            ) {

                $form = $this->disableEmptyValidation($form);
                $callback = $callbacks['crud_action'];
                break;
            }
        }

        return $this->formPost($form, $callback);
    }

    /**
     * Generate a form with data
     *
     * @param string $name
     * @param callable $callback
     * @param mixed $data
     * @param boolean $tables
     * @return object
     */
    public function generateFormWithData($name, $callback, $data = null, $tables = false)
    {
        $form = $this->generateForm($name, $callback, $tables);

        if (!$this->getRequest()->isPost() && is_array($data)) {
            $form->setData($data);
        }

        return $form;
    }

    /**
     * Disable empty validation
     *
     * @param object $form
     */
    private function disableEmptyValidation($form)
    {
        foreach ($form->getElements() as $key => $element) {

            if (empty($value)) {

                $form->getInputFilter()->get($key)->setAllowEmpty(true);
                $form->getInputFilter()->get($key)->setValidatorChain(
                    new ValidatorChain()
                );
            }
        }

        foreach ($form->getFieldsets() as $key => $fieldset) {

            foreach ($fieldset->getElements() as $elementKey => $element) {

                $value = $element->getValue();

                if (empty($value)) {

                    $form->getInputFilter()->get($key)->get($elementKey)->setAllowEmpty(true);
                    $form->getInputFilter()->get($key)->get($elementKey)->setValidatorChain(
                        new ValidatorChain()
                    );
                }
            }
        }

        return $form;
    }

    public function processAdd($data, $entityName)
    {
        $data = $this->trimFormFields($data);

        $result = $this->makeRestCall($entityName, 'POST', $data);

        $data['id'] = $result['id'];
        $this->generateDocument($data);

        return $result;
    }

    public function processEdit($data, $entityName)
    {
        $data = $this->trimFormFields($data);

        $result = $this->makeRestCall($entityName, 'PUT', $data);

        $this->generateDocument($data);

        return $result;

    }

    /**
     * Method to trigger generation of a document providing a generate checkbox
     * is found in $data
     *
     * @param array $data
     * @return array
     * @throws \RuntimeException
     */
    protected function generateDocument($data = array())
    {
        $documentData = [];
        if (isset($data['document']['generate']) && $data['document']['generate'] == '1') {

            if (!method_exists($this, 'mapDocumentData')) {
                throw new \RuntimeException('Controller requires mapDocumentData method');
            }
            $bookmarks = $this->mapDocumentData($data);

            $documentData = $this->sendPost(
                'Olcs\Document\GenerateRtf', [
                    'data' => [
                        'formName' => $data['document']['formName'],
                        'licence' => $this->fromRoute('licence'),
                        'case' => $this->fromRoute('case'),
                        'id' => $data['id']
                    ],
                    'bookmarks' => $bookmarks,
                    'country' =>
                        isset($data['document']['country']) ?
                        $data['document']['country'] : 'en_GB',
                    'templateId' => $data['document']['templateId'],
                    'format' =>
                        isset($data['document']['format']) ?
                        $data['document']['format'] : 'rtf'
                    ]
            );
        }

        return $documentData;
    }

    protected function trimFormFields($data)
    {
        return $this->trimFields($data, array('csrf', 'submit', 'fields', 'form-actions'));
    }

    protected function trimFields($data = array(), $unwantedFields = array())
    {
        foreach ($unwantedFields as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Check if a button was pressed
     *
     * @param string $button
     * @param array $data
     * @return bool
     */
    public function isButtonPressed($button, $data = null)
    {
        $request = $this->getRequest();

        if (is_null($data)) {
            $data = (array)$request->getPost();
        }

        return $request->isPost() && isset($data['form-actions'][$button]);
    }

    public function cancelButtonListener(MvcEvent $event)
    {
        $this->setupIndexRoute($event);
        $cancelResponse = $this->checkForCancelButton('cancel');
        if (!is_null($cancelResponse)) {
            $event->setResult($cancelResponse);
            return $cancelResponse;
        }
    }

    /**
     * This method needs some things.
     *
     * 1. A form element with the name of "cancel"
     *
     * @return \Zend\Http\Response
     */
    public function checkForCancelButton($buttonName = 'cancel')
    {
        if ($this->isButtonPressed($buttonName)) {

            $this->addInfoMessage('Action cancelled successfully');

            return $this->redirectToIndex();
        }
    }

    /**
     * Gets a view model with optional params
     *
     * @param array $params
     * @return ViewModel
     */
    public function getView(array $params = null)
    {
        return new ViewModel($params);
    }

    /**
     * Disable field validation
     *
     * @param \Zend\InputFilter\InputFilter $inputFilter
     * @return null
     */
    protected function disableValidation($inputFilter)
    {
        if ($inputFilter instanceof InputFilter) {
            foreach ($inputFilter->getInputs() as $input) {
                $this->disableValidation($input);
            }
            return;
        }

        if ($inputFilter instanceof Input) {
            $inputFilter->setAllowEmpty(true);
            $inputFilter->setRequired(false);
            $inputFilter->setValidatorChain(new ValidatorChain());
        }
    }

    /**
     * Disable all elements recursively
     *
     * @param \Zend\Form\Fieldset $elements
     * @return null
     */
    protected function disableElements($elements)
    {
        if ($elements instanceof Fieldset) {
            foreach ($elements->getElements() as $element) {
                $this->disableElements($element);
            }

            foreach ($elements->getFieldsets() as $fieldset) {
                $this->disableElements($fieldset);
            }
            return;
        }

        if ($elements instanceof Element) {
            $elements->setAttribute('disabled', 'disabled');
        }
    }

    /**
     * Sets the caught response
     *
     * @param mixed $response
     */
    protected function setCaughtResponse($response)
    {
        $this->caughtResponse = $response;
    }

    /**
     * Getter for caughtResponse
     *
     * @return mixed
     */
    protected function getCaughtResponse()
    {
        return $this->caughtResponse;
    }

    /**
     * Set the layout
     *
     * @param string $layout
     */
    protected function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Get the layout
     *
     * @param string $layout
     */
    protected function getLayout()
    {
        return $this->layout;
    }

    /**
     * Gets the data map
     *
     * @return array
     */
    protected function getDataMap()
    {
        // @NOTE progressivly start to include the logic within a service
        //  but maintain the old logic for backwards compatability
        if ($this->getSectionServiceName() !== null) {
            return $this->getSectionService()->getDataMap();
        }

        return $this->dataMap;
    }

    /**
     * Getter for data bundle
     *
     * @return array
     */
    protected function getDataBundle()
    {
        // @NOTE progressivly start to include the logic within a service
        //  but maintain the old logic for backwards compatability
        if ($this->getSectionServiceName() !== null) {
            return $this->getSectionService()->getDataBundle();
        }

        return $this->dataBundle;
    }

    /**
     * Get the service name
     *
     * @return string
     */
    protected function getService()
    {
        // @NOTE progressivly start to include the logic within a service
        //  but maintain the old logic for backwards compatability
        if ($this->getSectionServiceName() !== null) {
            return $this->getSectionService()->getService();
        }

        return $this->service;
    }

    /**
     * Get table settings
     *
     * This method should be overridden
     *
     * @return array
     */
    protected function getTableSettings()
    {
        return array();
    }

    /**
     * Check if a form exists
     *
     * @param string $formName
     * @return boolean
     */
    protected function formExists($formName)
    {
        return file_exists($this->getFormLocation($formName));
    }

    /**
     * Get a form location
     *
     * @param string $formName
     * @return string
     */
    protected function getFormLocation($formName)
    {
        return $this->getServiceLocator()->get('Config')['forms_path'] . $formName . '.form.php';
    }

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    protected function processLoad($data)
    {
        // @NOTE progressivly start to include the logic within a service
        //  but maintain the old logic for backwards compatability
        if ($this->getSectionServiceName() !== null) {
            return $this->getSectionService()->processLoad($data);
        }

        return $data;
    }

    /**
     * Delete
     *
     * @return Response
     */
    protected function delete($id = null, $service = null)
    {
        // @NOTE progressivly start to include the logic within a service
        //  but maintain the old logic for backwards compatability
        if ($this->getSectionServiceName() !== null) {
            return $this->getSectionService()->delete($id, $service);
        }

        if ($service === null) {
            $service = $this->getService();
        }

        if (!empty($id) && !empty($service)) {

            $this->makeRestCall($service, 'DELETE', array('id' => $id));

            return true;
        }

        return false;
    }

    /**
     * Save data
     *
     * @param array $data
     * @param string $service
     * @return array
     */
    protected function save($data, $service = null)
    {
        // @NOTE progressivly start to include the logic within a service
        //  but maintain the old logic for backwards compatability
        if ($this->getSectionServiceName() !== null) {
            return $this->getSectionService()->save($data, $service);
        }

        $method = 'POST';

        if (isset($data['id']) && !empty($data['id'])) {
            $method = 'PUT';
        }

        if (empty($service)) {
            $service = $this->getService();
        }

        return $this->makeRestCall($service, $method, $data);
    }

    /**
     * Complete section and save
     *
     * @param array $data
     * @return array
     */
    protected function processSave($data)
    {
        $data = $this->processDataMapForSave($data, $this->getDataMap());

        $response = $this->save($data);

        if ($response instanceof Response || $response instanceof ViewModel) {
            $this->setCaughtResponse($response);
            return;
        }

        return $response;
    }

    /**
     * Get form tables
     *
     * @return array
     */
    protected function getFormTables()
    {
        // @NOTE progressivly start to include the logic within a service
        //  but maintain the old logic for backwards compatability
        if ($this->getSectionServiceName() !== null) {
            return $this->getSectionService()->getFormTables();
        }

        return $this->formTables;
    }

    /**
     * Process save when we have a table form
     *
     * @to-do at some point we need to trim this down a bit and move the non-controller logic into a service
     *
     * @param array $data
     */
    protected function processSaveCrud($data)
    {
        $oldData = $data;

        $data = $this->processDataMapForSave($data, $this->getDataMap());

        $response = $this->saveCrud($data);

        if ($response instanceof Response || $response instanceof ViewModel) {
            $this->setCaughtResponse($response);
            return;
        }

        $formTables = $this->getFormTables();

        foreach (array_keys($formTables) as $table) {

            if (!is_array($oldData[$table]['action'])) {
                $action = strtolower($oldData[$table]['action']);
                $id = isset($oldData[$table]['id']) ? $oldData[$table]['id'] : null;
            } else {
                $action = array_keys($oldData[$table]['action'])[0];
                $id = (isset($oldData[$table]['action'][$action])
                    ? array_keys($oldData[$table]['action'][$action])[0]
                    : null);
            }

            if (!empty($action)) {

                $routeAction = $action;

                if ($table !== 'table') {
                    $routeAction = $table . '-' . $action;
                }

                // Incase we want to try and hi-jack the crud action check
                if (method_exists($this, 'checkForAlternativeCrudAction')) {
                    $response = $this->checkForAlternativeCrudAction($routeAction);

                    if ($response instanceof Response) {
                        $this->setCaughtResponse($response);
                        return;
                    }
                }

                if ($action == 'add') {
                    $this->setCaughtResponse(
                        $this->redirectToRoute(null, array('action' => $routeAction), array(), true)
                    );
                    return;
                }

                if (empty($id)) {
                    $this->setCaughtResponse($this->crudActionMissingId());
                    return;
                }

                $options = array();
                $params = array(
                    'action' => $routeAction
                );

                if (is_array($id) && count($id) === 1) {
                    $id = $id[0];
                }

                if (is_array($id)) {
                    $options = array(
                        'query' => array(
                            'id' => $id
                        )
                    );
                } else {
                    $params['id'] = $id;
                }

                $this->setCaughtResponse(
                    $this->redirectToRoute(
                        null,
                        $params,
                        $options,
                        true
                    )
                );
                return;
            }
        }
    }

    /**
     * Save crud data
     *
     * @param array $data
     * @return mixed
     */
    protected function saveCrud($data)
    {
        // @NOTE progressivly start to include the logic within a service
        //  but maintain the old logic for backwards compatability
        if ($this->getSectionServiceName() !== null) {
            return $this->getSectionService()->saveCrud($data);
        }

        return $this->save($data);
    }

    /**
     * Load data for the form
     *
     * This method should be overridden
     *
     * @param int $id
     * @return array
     */
    protected function load($id)
    {
        // @NOTE progressivly start to include the logic within a service
        //  but maintain the old logic for backwards compatability
        if ($this->getSectionServiceName() !== null) {
            return $this->getSectionService()->load($id);
        }

        if (empty($this->loadedData)) {
            $service = $this->getService();

            $result = $this->makeRestCall($service, 'GET', $id, $this->getDataBundle());

            if (empty($result)) {
                $this->setCaughtResponse($this->notFoundAction());
                return;
            }

            $this->loadedData = $result;
        }

        return $this->loadedData;
    }

    /**
     * Process the data map for saving
     *
     * @NOTE This method has been left in for backwards compatability, but now just wraps a helper service
     *
     * @param type $data
     */
    public function processDataMapForSave($oldData, $map = array(), $section = 'main')
    {
        return $this->getServiceLocator()->get('Helper\Data')->processDataMap($oldData, $map, $section);
    }

    /**
     * Get the namespace parts
     *
     * @return array
     */
    public function getNamespaceParts()
    {
        $controller = get_called_class();

        return explode('\\', $controller);
    }

    /**
     * Return the section service
     *
     * @return \Common\Controller\Service\SectionServiceInterface
     */
    protected function getSectionService($sectionServiceName = null)
    {
        if ($sectionServiceName === null) {
            $sectionServiceName = $this->getSectionServiceName();
        }

        if (!isset($this->sectionServices[$sectionServiceName])) {
            $this->sectionServices[$sectionServiceName] = $this->getServiceLocator()
                ->get('SectionService')->getSectionService($sectionServiceName);
        }

        return $this->sectionServices[$sectionServiceName];
    }

    /**
     * Get section service name
     *
     * @return string
     */
    protected function getSectionServiceName()
    {
        return $this->sectionServiceName;
    }
}
