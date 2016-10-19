<?php

namespace Common\Controller\Lva;

use Common\Controller\Traits\GenericUpload;
use Common\Exception\ResourceConflictException;
use Common\Util;
use Dvsa\Olcs\Transfer\Query\Application\Application;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Zend\Form\Form;
use Zend\Form\FormInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Exception;
use Zend\Mvc\MvcEvent;

/**
 * Lva Abstract Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @method \Common\Service\Cqrs\Response handleQuery(\Dvsa\Olcs\Transfer\Query\QueryInterface $query)
 * @method \Common\Service\Cqrs\Response handleCommand(\Dvsa\Olcs\Transfer\Command\CommandInterface $query)
 * @method \Common\Service\Cqrs\Response handleCancelRedirect($lvaId)
 * @method \Zend\Http\Response handlePostSave($prefix = null)
 * @method \Common\Controller\Plugin\Redirect redirect()
 * @method boolean isGranted(string $permission)
 * @method \Common\Controller\Plugin\CurrentUser currentUser()
 * @method \Zend\Http\Response completeSection($section, $prg = [])
 * 
 * @see   \Olcs\Controller\Lva\Traits\ApplicationControllerTrait::render
 * @method \Common\View\Model\Section render($titleSuffix, Form $form = null, $variables = [])
 */
abstract class AbstractController extends AbstractActionController
{
    use Util\FlashMessengerTrait,
        GenericUpload;

    /**
     * Internal/External
     *
     * @var string
     */
    protected $location;

    /**
     * Licence/Variation/Application
     *
     * @var string
     */
    protected $lva;

    /**
     * Current messages
     *
     * @var array
     */
    protected $currentMessages = [
        'default' => [],
        'error' => [],
        'info' => [],
        'warning' => [],
        'success' => []
    ];

    protected $defaultBundles = [
        'licence' => Licence::class,
        'variation' => Application::class,
        'application' => Application::class
    ];

    /**
     * Execute the request
     *
     * @param  MvcEvent $e
     * @return mixed
     * @throws Exception\DomainException
     */
    public function onDispatch(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        if (!$routeMatch) {
            throw new Exception\DomainException('Missing route matches; unsure how to retrieve action');
        }

        $this->maybeTranslateForNi();

        $action = $routeMatch->getParam('action', 'not-found');
        $method = static::getMethodFromAction($action);

        if (!method_exists($this, $method)) {
            $method = 'notFoundAction';
        }

        if ($routeMatch->getParam('skipPreDispatch', false) || ($actionResponse = $this->preDispatch()) === null) {
            try {
                $actionResponse = $this->$method();
            } catch (ResourceConflictException $ex) {
                $this->addErrorMessage('version-conflict-message');
                $actionResponse = $this->reload();
            }
        }

        $e->setResult($actionResponse);

        return $actionResponse;
    }

    protected function maybeTranslateForNi()
    {
        if ($this->lva !== null && $this->getIdentifier() !== null) {
            $tolData = $this->getTypeOfLicenceData();
            $niTranslation = $this->getServiceLocator()->get('Utils\NiTextTranslation');
            $niTranslation->setLocaleForNiFlag($tolData['niFlag']);
        }
    }

    /**
     * Hook into the dispatch before the controller action is executed
     */
    protected function preDispatch()
    {

    }

    /**
     * Check if a button is pressed
     *
     * @param string $button
     * @return boolean
     */
    protected function isButtonPressed($button, $data = [])
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        }

        return isset($data['form-actions'][$button]);
    }

    /**
     * Get accessible sections
     */
    protected function getAccessibleSections($keysOnly = true)
    {
        $data = $this->fetchDataForLva();

        $sections = $data['sections'];

        if ($keysOnly) {
            $sections = array_keys($sections);
        }

        return $sections;
    }

    /**
     * @NOTE This is a new method to load the generic LVA bundle (which is cached)
     */
    protected function fetchDataForLva()
    {
        $dtoClass = $this->defaultBundles[$this->lva];

        $response = $this->handleQuery($dtoClass::create(['id' => $this->getIdentifier()]));

        return $response->getResult();
    }

    /**
     * Get licence type information
     *
     * @NOTE migrated
     *
     * @return array
     */
    protected function getTypeOfLicenceData()
    {
        $data = $this->fetchDataForLva();

        return [
            'version' => $data['version'],
            'niFlag' => $data['niFlag'],
            'licenceType' => isset($data['licenceType']['id']) ? $data['licenceType']['id'] : null,
            'goodsOrPsv' => isset($data['goodsOrPsv']['id']) ? $data['goodsOrPsv']['id'] : null
        ];
    }

    /**
     * Wrapper method so we can extend this behaviour
     *
     * @return \Zend\Http\Response
     */
    protected function goToOverviewAfterSave($lvaId = null)
    {
        return $this->goToOverview($lvaId);
    }

    /**
     * Go to overview page
     *
     * @param int $lvaId
     * @return \Zend\Http\Response
     */
    protected function goToOverview($lvaId = null)
    {
        if ($lvaId === null) {
            $lvaId = $this->getIdentifier();
        }

        return $this->redirect()->toRouteAjax('lva-' . $this->lva, [$this->getIdentifierIndex() => $lvaId]);
    }

    /**
     * Add the section updated message
     *
     * @param string $section
     */
    protected function addSectionUpdatedMessage($section)
    {
        $this->addSuccessMessage(
            $this->getServiceLocator()->get('Helper\Translation')->formatTranslation(
                '%s %s',
                ['section.name.' . $section, 'section-updated-successfully-message-suffix']
            )
        );
    }

    /**
     * Redirect to the next section
     */
    protected function goToNextSection($currentSection)
    {
        $sections = $this->getAccessibleSections();

        $index = array_search($currentSection, $sections, false);

        // If there is no next section
        if (!isset($sections[$index + 1])) {
            return $this->goToOverview($this->getApplicationId());
        } else {
            return $this->redirect()
                ->toRoute(
                    'lva-' . $this->lva . '/' . $sections[$index + 1],
                    [$this->getIdentifierIndex() => $this->getApplicationId()]
                );
        }
    }

    /**
     * Check for redirect
     *
     * @param int $lvaId
     * @return null|\Zend\Http\Response
     */
    protected function checkForRedirect($lvaId)
    {
        if (!$this->isButtonPressed('cancel')) {
            return null;
        }

        // If we are on a sub-section, we need to go back to the section
        if ($this->params('action') !== 'index') {
            return $this->redirect()->toRoute(
                $this->getBaseRoute(),
                [$this->getIdentifierIndex() => $lvaId],
                ['query' => $this->getRequest()->getQuery()->toArray()]
            );
        }

        return $this->handleCancelRedirect($lvaId);
    }

    /**
     * No-op but extended
     */
    protected function alterFormForLva(Form $form, $data = null)
    {

    }

    /**
     * A method to be called post save, this can be hi-jacked to do things like update completion status
     */
    protected function postSave($section)
    {

    }

    /**
     * Reload the current page
     *
     * @return \Zend\Http\Response
     */
    protected function reload()
    {
        return $this->redirect()->refreshAjax();
    }

    /**
     * Add current message
     *
     * @param string $message
     * @param string $namespace
     */
    protected function addCurrentMessage($message, $namespace = 'default')
    {
        $this->currentMessages[$namespace][] = $message;
    }

    /**
     * Attach messages to display in the current response
     */
    protected function attachCurrentMessages()
    {
        foreach ($this->currentMessages as $namespace => $messages) {
            foreach ($messages as $message) {
                $this->addMessage($message, $namespace);
            }
        }
    }

    protected function getLvaEntityService()
    {
        if ($this->lva === 'variation') {
            $service = 'Application';
        } else {
            $service = ucwords($this->lva);
        }

        return $this->getServiceLocator()->get('Entity\\' . $service);
    }

    protected function getIdentifier()
    {
        return $this->params($this->getIdentifierIndex());
    }

    protected function getIdentifierIndex()
    {
        if ($this->lva === 'licence') {
            return 'licence';
        }

        return 'application';
    }

    /**
     * This method is overidden for applications
     *
     * @param int $applicationId
     * @return int
     */
    protected function getLicenceId($applicationId = null)
    {
        return $this->getIdentifier();
    }

    protected function isExternal()
    {
        return $this->location === 'external';
    }
}
