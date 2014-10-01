<?php

/**
 * Abstract Journey Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Controller;

use Zend\Http\Response;
use Zend\View\Model\ViewModel;
use Common\Helper\RestrictionHelper;
use Common\Controller\AbstractSectionController;

/**
 * Abstract Journey Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractJourneyController extends AbstractSectionController
{
    /**
     * Render the navigation
     *
     * @var boolean
     */
    protected $renderNavigation = true;

    /**
     * Hold the journey name
     *
     * @var string
     */
    private $journeyName;

    /**
     * Hold the section name
     *
     * @var string
     */
    private $sectionName;

    /**
     * Hold the sub section name
     *
     * @var string
     */
    private $subSectionName;

    /**
     * Hold the journey config
     *
     * @var array
     */
    private $journeyConfig = array();

    /**
     * Holds the section reference
     *
     * @var string
     */
    private $sectionReference;

    /**
     * Holds the section completion
     *
     * @var array
     */
    private $sectionCompletion;

    /**
     * Holds the steps
     *
     * @var array
     */
    private $steps = array();

    /**
     * Holds the step number
     *
     * @var int
     */
    private $stepNumber;

    /**
     * Holds the access keys
     *
     * @var array
     */
    protected $accessKeys;

    /**
     * Holds the view name
     *
     * @var string
     */
    protected $viewName;

    /**
     * Holds hasView
     *
     * @var boolean
     */
    protected $hasView = null;

    protected function getApplicationConfig()
    {
        $config = $this->getServiceLocator()->get('Config');

        return $config[strtolower($this->getJourneyName()) . '_journey'];
    }

    /**
     * Override the not found action
     */
    public function notFoundAction()
    {
        $view = $this->getViewModel();
        $view->setTemplate($this->getApplicationConfig()['templates']['not-found']);

        return $this->render($view);
    }

    /**
     * Render navigation
     *
     * @param boolean $renderNavigation
     */
    protected function setRenderNavigation($renderNavigation)
    {
        $this->renderNavigation = $renderNavigation;
    }

    /**
     * Get render navigation
     *
     * @return boolean
     */
    protected function getRenderNavigation()
    {
        return $this->renderNavigation;
    }

    /**
     * Extend the default and reverse the array
     *
     * @return array
     */
    public function getNamespaceParts()
    {
        $parts = parent::getNamespaceParts();

        return array_reverse($parts);
    }

    /**
     * Getter for journey name
     *
     * @return string
     */
    protected function getJourneyName()
    {
        if (empty($this->journeyName)) {
            $this->journeyName = $this->getNamespaceParts()[2];
        }

        return $this->journeyName;
    }

    /**
     * Getter for the current section name
     *
     * @return string
     */
    protected function getSectionName()
    {
        if (empty($this->sectionName)) {
            $this->sectionName = $this->getNamespaceParts()[1];
        }

        return $this->sectionName;
    }

    /**
     * Getter for the current sub section name
     *
     * @return string
     */
    protected function getSubSectionName()
    {
        if (empty($this->subSectionName)) {

            if (isset($this->getNamespaceParts()[0])) {
                $this->subSectionName = str_replace('Controller', '', $this->getNamespaceParts()[0]);
            }
        }

        return $this->subSectionName;
    }

    /**
     * Getter for the current journey config
     *
     * @return array
     */
    protected function getJourneyConfig()
    {
        if (empty($this->journeyConfig)) {
            $this->journeyConfig = $this->getServiceLocator()->get('Config')['journeys'][$this->getJourneyName()];
        }

        return $this->journeyConfig;
    }

    /**
     * Get the sections
     *
     * @return array
     */
    protected function getSections()
    {
        return $this->getJourneyConfig()['sections'];
    }

    /**
     * Get the section config
     *
     * @return array
     */
    protected function getSectionConfig()
    {
        return $this->getSections()[$this->getSectionName()];
    }

    /**
     * Get a journey, section, or sub section config
     *
     * @param string $section
     * @param string $subSection
     * @return array
     */
    protected function getConfig($section = null, $subSection = null)
    {
        $config = $this->getJourneyConfig();

        if (!is_null($subSection)) {

            return isset($config['sections'][$section]['subSections'][$subSection])
                ? $config['sections'][$section]['subSections'][$subSection]
                : array();
        }

        return isset($config['sections'][(string) $section]) ? $config['sections'][(string) $section] : array();
    }

    /**
     * Get the section reference
     *
     * @return string
     */
    protected function getSectionReference()
    {
        if (empty($this->sectionReference)) {

            $journey = $this->getJourneyName();
            $section = $this->getSectionName();
            $subSection = $this->getSubSectionName();

            $suffix = '';

            if ($this->isAction()) {
                $suffix = '-' . $this->getActionName();
            }

            $this->sectionReference = $this->getHelperService('StringHelper')
                ->camelToDash($journey . '_' . $section . '_' . $subSection . $suffix);
        }

        return $this->sectionReference;
    }

    /**
     * Get table name
     *
     * @return string
     */
    protected function getTableName()
    {
        if (empty($this->tableName)) {
            $this->tableName = $this->getFormName();
        }

        return $this->tableName;
    }

    /**
     * Get form name
     *
     * @return string
     */
    protected function getFormName()
    {
        if (empty($this->formName)) {

            $this->formName = $this->formatFormName(
                $this->getJourneyName(),
                $this->getSectionName(),
                $this->getSubSectionName(),
                $this->isAction(),
                $this->getActionName()
            );

            // If the guessed form name doesn't exist
            //  call the parent getFormName
            //  this is useful for any generic action form such as delete confirmation
            if (!$this->formExists($this->formName)) {
                $this->formName = null;
                $this->formName = parent::getFormName();
            }
        }

        return $this->formName;
    }

    /**
     * Format a form name for a section
     *
     * @param string $journey
     * @param string $section
     * @param string $subSection
     * @param string $isAction
     * @return string
     */
    protected function formatFormName($journey, $section, $subSection, $isAction = false, $action = null)
    {
        $suffix = '';

        if ($isAction) {

            $suffix = $this->getSuffixForAction($action);
        }

        return $this->getHelperService('StringHelper')
            ->camelToDash($journey . '_' . $section . '_' . $subSection . $suffix);
    }

    /**
     * Setter for section completion
     *
     * @param array $sectionCompletion
     */
    protected function setSectionCompletion($sectionCompletion)
    {
        $this->sectionCompletion = $sectionCompletion;
    }

    /**
     * Get the section completion
     *
     * @return array
     */
    protected function getSectionCompletion()
    {
        if (empty($this->sectionCompletion)) {
            $id = $this->getIdentifier();

            $foreignKey = $this->getJourneyConfig()['completionStatusJourneyIdColumn'];

            $completionStatus = $this->makeRestCall(
                $this->getJourneyConfig()['completionService'], 'GET', array('id' => $id)
            );

            $this->sectionCompletion = $completionStatus;

            $this->sectionCompletion[$foreignKey] = $id;
            $this->sectionCompletion['id'] = $id;
        }

        return $this->sectionCompletion;
    }

    /**
     * Set the steps
     */
    protected function setSteps()
    {
        $this->steps = array();

        $config = $this->getJourneyConfig();

        $journey = $this->getJourneyName();

        foreach ($config['sections'] as $section => $details) {

            foreach (array_keys($details['subSections']) as $subSection) {
                $this->steps[] = array($journey, $section, $subSection);
            }
        }
    }

    /**
     * Get stepts
     *
     * @return array
     */
    protected function getSteps()
    {
        if (empty($this->steps)) {
            $this->setSteps();
        }

        return $this->steps;
    }

    /**
     * Get the step number
     * @return type
     */
    protected function getStepNumber()
    {
        if (empty($this->stepNumber)) {
            $steps = $this->getSteps();

            $this->stepNumber = array_search(
                array($this->getJourneyName(), $this->getSectionName(), $this->getSubSectionName()), $steps
            );
        }

        return $this->stepNumber;
    }

    /**
     * Get a list of access keys to match the restrictions
     *
     * This method should be extended
     *
     * @param boolean $force
     * @return array
     */
    protected function getAccessKeys($force = false)
    {
        unset($force);

        if (empty($this->accessKeys)) {
            $this->accessKeys = array(null);
        }

        return $this->accessKeys;
    }

    /**
     * Get form name
     *
     * @return string
     */
    protected function getViewName()
    {
        if (empty($this->viewName)) {

            $journey = $this->getJourneyName();
            $section = $this->getSectionName();
            $subSection = $this->getSubSectionName();

            $this->viewName = $this->getHelperService('StringHelper')
                ->camelToDash($journey . '/' . $section . '/' . $subSection);

            if ($this->isAction()) {
                $this->viewName .= '-' . $this->getActionName();
            }
        }

        return $this->viewName;
    }

    /**
     * Get a list of accessible sections
     *
     * @return array
     */
    protected function getAccessibleSections()
    {
        $accessibleSections = array();

        $sections = $this->getSections();

        foreach ($sections as $name => $details) {

            if (!$this->isSectionAccessible($details)) {
                continue;
            }

            $accessibleSections[$name] = $this->getAccessibleSection($name, $details);
        }

        return $accessibleSections;
    }

    /**
     * Format the accessible section
     *
     * @param string $name
     * @return array
     */
    protected function getAccessibleSection($name, $details)
    {
        $journeyName = $this->getJourneyName();
        $sectionName = $this->getSectionName();

        $status = $this->getSectionStatus($name);

        $current = false;

        if ($name == $sectionName) {
            $current = true;
        }

        return array(
            'status' => $status,
            'current' => $current,
            'display' => (isset($details['collapsible']) && $details['collapsible']),
            'class' => (isset($details['collapsible']) && $details['collapsible']) ? 'js-visible' : '',
            'enabled' => $this->isSectionEnabled($name),
            'title' => $this->getSectionLabel($journeyName, $name),
            'route' => $this->getSectionRoute($journeyName, $name),
            'subSections' => $this->getAccessibleSubSections($name, $details)
        );
    }

    /**
     * Get accessible sub sections for section
     *
     * @param string $sectionName
     * @param array $sectionDetails
     * @return array
     */
    protected function getAccessibleSubSections($sectionName, $sectionDetails)
    {
        $journeyName = $this->getJourneyName();
        $currentSubSectionName = $this->getSubSectionName();

        $subSections = array();

        foreach ($sectionDetails['subSections'] as $subSectionName => $subSection) {
            if (!$this->isSectionAccessible($sectionName, $subSectionName)) {
                continue;
            }

            $status = $this->getSectionStatus($sectionName, $subSectionName);

            $current = false;

            if ($subSectionName == $currentSubSectionName) {
                $current = true;
            }

            $subSections[] = array(
                'status' => $status,
                'current' => $current,
                'class' => (isset($sectionDetails['collapsible']) && $sectionDetails['collapsible']) ? 'js-hidden' : '',
                'enabled' => $this->isSectionEnabled($sectionName, $subSectionName),
                'title' => $this->getSectionLabel($journeyName, $sectionName, $subSectionName),
                'route' => $this->getSectionRoute($journeyName, $sectionName, $subSectionName)
            );
        }

        return $subSections;
    }

    /**
     * Get section status
     *
     * @param string $section
     * @return string
     */
    protected function getSectionStatus($section, $subSection = null)
    {
        $sectionCompletion = $this->getSectionCompletion();

        $statusMap = $this->getJourneyConfig()['completionStatusMap'];

        $index = 'section' . $section . (!is_null($subSection) ? $subSection : '') . 'Status';

        if (!array_key_exists($index, $sectionCompletion)) {
            return null;
        }

        return $statusMap[(int) $sectionCompletion[$index]];
    }

    /**
     * Build the navigation view
     *
     * @return ViewModel
     */
    protected function getNavigationView()
    {
        $sections = $this->getAccessibleSections();

        $view = $this->getViewModel(array('sections' => $sections));
        $view->setTemplate($this->getApplicationConfig()['templates']['navigation']);

        return $view;
    }

    /**
     * Format the section label
     *
     * @param string $journey
     * @param string $section
     * @param string $subSection
     * @return string
     */
    protected function getSectionLabel($journey, $section, $subSection = null)
    {
        return strtolower(
            $this->getHelperService('StringHelper')
                ->camelToDash($journey . '.' . $section . (!empty($subSection) ? '.' . $subSection : ''))
        );
    }

    /**
     * Format the section route
     *
     * @param string $journey
     * @param string $section
     * @param string $subSection
     * @return string
     */
    protected function getSectionRoute($journey = null, $section = null, $subSection = null)
    {
        if (is_null($journey) && is_null($section) && is_null($subSection)) {
            $journey = $this->getJourneyName();
            $section = $this->getSectionName();
            $subSection = $this->getSubSectionName();
        }

        return $journey . '/' . $section . (!empty($subSection) ? '/' . $subSection : '');
    }

    /**
     * Get an array of sub sections
     */
    protected function getSubSectionsForLayout()
    {
        $sectionConfig = $this->getSectionConfig();

        $subSections = array();

        foreach ($sectionConfig['subSections'] as $name => $details) {

            if (!$this->isSectionAccessible($details)) {
                continue;
            }

            $subSections[$name] = $this->getSectionDetailsForLayout($name, $details);
        }

        return $subSections;
    }

    /**
     * Format the section details for the layout
     *
     * @param string $name
     * @param array $details
     * @return array
     */
    protected function getSectionDetailsForLayout($name, $details)
    {
        $journey = $this->getJourneyName();
        $section = $this->getSectionName();
        $subSection = $this->getSubSectionName();
        $isAction = $this->isAction();

        $sectionDetails = array(
            'label' => $this->getSectionLabel($journey, $section, $name),
            'class' => '',
            'link' => true,
            'route' => $this->getSectionRoute($journey, $section, $name),
            'routeParams' => array($this->getIdentifierName() => $this->getIdentifier()),
            'action' => false
        );

        if (!$this->isSectionEnabled($details)) {
            $sectionDetails['class'] = 'disabled';
            $sectionDetails['link'] = false;
        } else {

            if ($name == $subSection) {
                $sectionDetails['class'] = 'current';
            }

            if ($name == $subSection && !$isAction) {
                $sectionDetails['link'] = false;
            }
        }

        if ($name == $subSection && $isAction) {
            $sectionDetails['action'] = $this->getSectionReference();
        }

        return $sectionDetails;
    }

    /**
     * Check if the current sub section has a view
     *
     * @return boolean
     */
    protected function hasView()
    {
        if (is_null($this->hasView)) {

            $fileExists = false;

            foreach ($this->getServiceLocator()->get('Config')['view_manager']['template_path_stack'] as $location) {
                if (file_exists($location . '/' . $this->getViewName() . '.phtml')) {
                    $fileExists = true;
                    break;
                }
            }

            $this->hasView = $fileExists;
        }

        return $this->hasView;
    }

    /**
     * Check if a section is accessible
     *
     * @param array|string $details (Section if string)
     * @param string $subSection
     * @return boolean
     */
    protected function isSectionAccessible($details, $subSection = null)
    {
        if (is_string($details)) {
            $details = $this->getConfig($details, $subSection);
        }

        if (isset($details['restriction'])) {

            $accessKeys = $this->getAccessKeys();

            return $this->isRestrictionSatisfied($details['restriction'], $accessKeys);
        }

        return true;
    }

    /**
     * Check if a restriction is satisfied
     *
     * @param mixed $restriction
     * @param array $accessKeys
     */
    protected function isRestrictionSatisfied($restriction, $accessKeys)
    {
        $helper = new RestrictionHelper();

        return $helper->isRestrictionSatisfied($restriction, $accessKeys);
    }

    /**
     * Check if a section is enabled
     *
     * @param array|string $details (Section if string)
     * @param string $subSection
     * @return boolean
     */
    protected function isSectionEnabled($details, $subSection = null)
    {
        if (is_string($details)) {
            $details = $this->getConfig($details, $subSection);
        }

        $sectionCompletion = $this->getSectionCompletion();

        $enabled = true;

        $completeKey = array_search('complete', $this->getJourneyConfig()['completionStatusMap']);

        if (isset($details['required'])) {

            foreach ($details['required'] as $requiredSection) {

                if (strstr($requiredSection, '/')) {
                    list($sectionName, $subSectionName) = explode('/', $requiredSection);
                } else {
                    $sectionName = $requiredSection;
                    $subSectionName = null;
                }

                $requiredSection = str_replace('/', '', $requiredSection);

                if ($this->isSectionAccessible($sectionName, $subSectionName)
                    && (!isset($sectionCompletion['section' . $requiredSection . 'Status'])
                    || $sectionCompletion['section' . $requiredSection . 'Status'] != $completeKey)) {

                    $enabled = false;
                }
            }
        }

        if ($enabled && isset($details['enabled'])) {

            if (is_callable(array($this, $details['enabled']))) {
                $enabled = $this->$details['enabled']();
            }
        }

        return $enabled;
    }

    /**
     * Alter the form before validation
     *
     * @param Form $form
     * @return Form
     */
    protected function alterFormBeforeValidation($form)
    {
        // @todo Might want to take this out so we can go back to dashboard
        if ($this->getStepNumber() == 0) {
            $form->get('form-actions')->remove('back');
        }

        return parent::alterFormBeforeValidation($form);
    }

    /**
     * Setup the view for renderring
     *
     * @param ViewModel $view
     * @return ViewModel
     */
    protected function setupView($view = null, $params = array())
    {
        $view = parent::setupView($view, $params);

        if ($this->isAction()) {
            $view->setVariable('title', $this->getSectionReference());
        }

        return $view;
    }

    /**
     * Get view template name
     *
     * @return string
     */
    protected function getViewTemplateName()
    {
        if ($this->hasView()) {
            return $this->getViewName();
        }

        return $this->getApplicationConfig()['templates']['main'];
    }

    /**
     * Check for redirect
     *
     * @return Response
     */
    protected function checkForRedirect()
    {
        $redirect = parent::checkForRedirect();

        if ($redirect instanceof Response || $redirect instanceof ViewModel) {
            return $redirect;
        }

        $sectionName = $this->getSectionName();
        $subSectionName = $this->getSubSectionName();

        if (!$this->isSectionAccessible($sectionName, null)
            || !$this->isSectionAccessible($sectionName, $subSectionName)) {
            return $this->goToNextStep();
        }

        if (!$this->isSectionEnabled($sectionName, null)
            || !$this->isSectionEnabled($sectionName, $subSectionName)
            || $this->isButtonPressed('back')) {
            return $this->goToPreviousStep();
        }
    }

    /**
     * Render the view
     *
     * @param ViewModel $view
     * @return ViewModel
     */
    protected function render($view)
    {
        $layout = $this->getViewModel(
            array(
                'subSections' => $this->getSubSectionsForLayout(),
                'isCollapsible' => $this->isCollapsible(),
                'id' => $this->getIdentifier()
            )
        );

        $layoutName = $this->getLayout();

        if (empty($layoutName)) {
            $layoutName = $this->getApplicationConfig()['templates']['layout'];
        }

        $layout->setTemplate($layoutName);

        $children = array();

        if ($this->getRenderNavigation()) {
            $navigation = $this->getNavigationView();
            $layout->addChild($navigation, 'navigation');
            $children[] = 'navigation';
        }

        $children[] = 'main';

        $layout->addChild($view, 'main');

        $layout->setVariable('children', $children);

        $config = $this->getApplicationConfig();

        if (isset($config['render']['pre-render'])) {
            $serviceName = $config['render']['pre-render']['service'];
            $method = $config['render']['pre-render']['method'];
            $service = $this->getServiceLocator()->get($serviceName);
            $layout = $service->$method($layout, $this->getIdentifier());
        }

        return $layout;
    }

    /**
     * Complete section and save
     *
     * @param array $data
     * @return array
     */
    protected function processSave($data)
    {
        if ($this->shouldCollapseSection()) {
            $this->completeSection();
        } else {
            $this->completeSubSection();
        }

        $response = parent::processSave($data);

        if ($response instanceof Response || $response instanceof ViewModel) {
            $this->setCaughtResponse($response);
            return;
        }

        $this->setCaughtResponse($this->goToNextStep());
    }

    /**
     * Complete the current sub section
     */
    protected function completeSubSection()
    {
        $this->completeSubSections([$this->getSubSectionName()]);
    }

    /**
     * Complete the current over-arching section and all its subsections
     */
    protected function completeSection()
    {
        $section = $this->getSectionConfig();
        $subSections = array_keys($section['subSections']);
        $this->completeSubSections($subSections);
    }

    /**
     * Complete an array of sub sections; this allows multiple steps
     * to be marked complete while only triggering a single API request
     *
     * @param array $subSections
     */
    protected function completeSubSections(array $subSections)
    {
        $sectionCompletion = $this->updateSectionStatuses($subSections);

        // Persist the findings
        $this->makeRestCall($this->getJourneyConfig()['completionService'], 'PUT', $sectionCompletion);

        // Cache the statuses locally
        $sectionCompletion['version']++;
        $this->setSectionCompletion($sectionCompletion);
    }

    /**
     * Update the section statuses
     * - By default the statuses are marked as complete if there were no form errors,
     *      we may want to override this at a later date
     *
     * @param array $subSections
     * @return array
     */
    protected function updateSectionStatuses(array $subSections)
    {
        $sectionCompletion = $this->getSectionCompletion();
        $sectionName = $this->getSectionName();
        $completeKey = array_search('complete', $this->getJourneyConfig()['completionStatusMap']);
        $incompleteKey = array_search('incomplete', $this->getJourneyConfig()['completionStatusMap']);
        $sectionConfig = $this->getSectionConfig();

        foreach ($subSections as $subSection) {

            // Mark the sub section as complete
            $key = $this->formatSectionStatusIndex($sectionName, $subSection);
            $sectionCompletion[$key] = $completeKey;

            // Check if all sub sections are complete
            $complete = true;

            foreach (array_keys($sectionConfig['subSections']) as $subSectionName) {
                $sectionStatusKey = $this->formatSectionStatusIndex($sectionName, $subSectionName);

                if ($this->isSectionAccessible($sectionName, $subSectionName)
                    && (!isset($sectionCompletion[$sectionStatusKey])
                    || $sectionCompletion[$sectionStatusKey] != $completeKey)) {
                    $complete = false;
                    break;
                }
            }

            // If all sub sections are complete, mark the section as complete
            $sectionIndex = $this->formatSectionStatusIndex($sectionName, '');
            $sectionCompletion[$sectionIndex] = ($complete ? $completeKey : $incompleteKey);
        }

        return $sectionCompletion;
    }

    /**
     * Format section status index
     *
     * @param string $section
     * @param string $subSection
     * @return string
     */
    protected function formatSectionStatusIndex($section = null, $subSection = null)
    {
        $section = $section !== null ? $section : $this->getSectionName();
        $subSection = $subSection !== null ? $subSection : $this->getSubSectionName();

        return 'section' . $section . $subSection . 'Status';
    }

    /**
     * Journey finished
     */
    protected function journeyFinished()
    {
        return $this->goHome();
    }

    /**
     * Redirect to a section
     *
     * @param string $name
     * @param array $params
     * @return Response
     */
    protected function goToSection($name, $params = array(), $reuse = true)
    {
        return $this->redirectToRoute($name, $params, array(), $reuse);
    }

    /**
     * Go to the first section
     *
     * @return Response
     */
    protected function goToFirstSection()
    {
        $name = $this->getJourneyName();
        $config = $this->getJourneyConfig();

        $section = array_keys($config['sections'])[0];

        $route = $name . '/' . $section;

        if (isset($config['sections'][$section]['subSections'])) {
            $route .= '/' . array_keys($config['sections'][$section]['subSections'])[0];
        }

        return $this->goToSection($route);
    }

    /**
     * Go to the first subSection
     *
     * @return Response
     */
    protected function goToFirstSubSection()
    {
        $name = $this->getJourneyName();
        $section = $this->getSectionName();
        $config = $this->getJourneyConfig();

        $route = $name . '/' . $section;

        if (isset($config['sections'][$section]['subSections'])) {
            $route .= '/' . array_keys($config['sections'][$section]['subSections'])[0];
        }

        return $this->goToSection($route);
    }

    /**
     * Redirect to the next step
     */
    protected function goToNextStep()
    {
        $steps = $this->getSteps();

        $key = $this->getStepNumber();

        $startSection = $steps[$key][1];

        $nextKey = $key + 1;

        $this->getAccessKeys(true);

        while (isset($steps[$nextKey])) {
            list($app, $section, $subSection) = $steps[$nextKey];

            $nextKey++;

            if ($this->shouldCollapseSection() && $section === $startSection) {
                continue;
            }

            if ($this->isSectionAccessible($section, $subSection)) {
                return $this->goToSection(
                    $this->getSectionRoute($app, $section, $subSection)
                );
            }
        }

        return $this->journeyFinished();
    }

    /**
     * Redirect to the previous step
     */
    protected function goToPreviousStep()
    {
        $steps = $this->getSteps();

        $key = $this->getStepNumber();

        $prevKey = $key - 1;

        while (isset($steps[$prevKey])) {
            list($app, $section, $subSection) = $steps[$prevKey];

            $prevKey--;

            $subSection = $this->shouldCollapseSection($section) ? null : $subSection;

            if ($this->isSectionAccessible($section, $subSection)) {

                return $this->goToSection(
                    $this->getSectionRoute($app, $section, $subSection)
                );
            }
        }

        return $this->goHome();
    }

    /**
     * Check whether this section is suitable for collapsing (i.e. merging
     * all sub sections into one top-level step) and whether the appropriate
     * preconditions which satisfy a collapse are set.
     *
     * @param string $section
     *
     * @return bool
     */
    protected function shouldCollapseSection($section = null)
    {
        return $this->isCollapsible($section) && $this->isJavaScriptSubmission();
    }

    /**
     * Does this section, or a given section, indicate that it could
     * be collapsible?
     *
     * @param string $section
     *
     * @return bool
     */
    protected function isCollapsible($section = null)
    {
        if ($section === null) {
            $section = $this->getSectionName();
        }

        $sectionConfig = $this->getConfig($section);

        return isset($sectionConfig['collapsible']) && $sectionConfig['collapsible'];
    }

    /**
     * Is this a POST, and if so was it from a JS-enabled browser?
     *
     * @return bool
     */
    protected function isJavaScriptSubmission()
    {
        $request = $this->getRequest();

        return $request->isPost() && $request->getPost('js-submit');
    }

    /**
     * Redirect to sub section
     *
     * @return Response
     */
    protected function goBackToSection()
    {
        $route = $this->getSectionRoute();

        return $this->goToSection($route, array($this->getIdentifierName() => $this->getIdentifier()), false);
    }

    /**
     * Get the identifier name
     *
     * @return string
     */
    protected function getIdentifierName()
    {
        return $this->getJourneyConfig()['identifier'];
    }

    /**
     * Go back to the home route
     *
     * @return Response
     */
    protected function goHome()
    {
        return $this->redirectToRoute($this->getJourneyConfig()['homeRoute']);
    }
}
