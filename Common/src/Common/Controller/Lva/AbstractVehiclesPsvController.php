<?php

namespace Common\Controller\Lva;

use Common\Data\Mapper\Lva\PsvVehicles;
use Common\Data\Mapper\Lva\PsvVehiclesVehicle;
use Common\Service\Entity\LicenceEntityService;
use Dvsa\Olcs\Transfer\Command as CommandDto;
use Dvsa\Olcs\Transfer\Query as QueryDto;
use Dvsa\Olcs\Transfer\Query\Licence\PsvVehiclesExport;
use Zend\Form\Form;
use Zend\Form\FormInterface;

/**
 * Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class AbstractVehiclesPsvController extends AbstractController
{
    const SEARCH_VEHICLES_COUNT = 20;

    use Traits\TransferVehiclesTrait,
        Traits\VehicleSearchTrait;
    use Traits\CrudTableTrait {
        handleCrudAction as protected traitHandleCrudAction;
    }

    protected $section = 'vehicles_psv';
    protected $baseRoute = 'lva-%s/vehicles_psv';

    private $queryMap = [
        'licence' => QueryDto\Licence\PsvVehicles::class,
        'variation' => QueryDto\Variation\PsvVehicles::class,
        'application' => QueryDto\Application\PsvVehicles::class
    ];

    private $deleteMap = [
        'licence' => CommandDto\Vehicle\DeleteLicenceVehicle::class,
        'variation' => CommandDto\Application\DeletePsvVehicle::class,
        'application' => CommandDto\Application\DeletePsvVehicle::class
    ];

    protected $createVehicleMap = [
        'licence' => CommandDto\Licence\CreatePsvVehicle::class,
        'variation' => CommandDto\Application\CreatePsvVehicle::class,
        'application' => CommandDto\Application\CreatePsvVehicle::class
    ];

    /**
     * Process action - Index
     *
     * @return \Common\View\Model\Section|\Zend\Http\Response
     */
    public function indexAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        $resultData = $this->fetchResultData();
        if ($resultData === null) {
            return $this->notFoundAction();
        }

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $data = PsvVehicles::mapFromResult($resultData);
        }

        $form = $this->getServiceLocator()
            ->get('FormServiceManager')
            ->get('lva-' . $this->lva . '-' . $this->section)
            ->getForm()
            ->setData($data);

        $removeActions = false;
        if (!$resultData['hasBreakdown'] && in_array($this->lva, ['licence', 'variation'], true)) {
            $removeActions = true;
            $this->addGuidance();
        }

        $form = $this->alterForm($form, $resultData, $removeActions);

        if ($request->isPost()) {
            $crudAction = $this->getCrudAction([$data['vehicles']]);
            $haveCrudAction = ($crudAction !== null);

            if ($haveCrudAction && $this->isInternalReadOnly()) {
                return $this->handleCrudAction($crudAction);
            }

            if ($form->isValid()) {
                $response = $this->updateVehiclesSection($form, $haveCrudAction);
                if ($response !== null) {
                    return $response;
                }

                if ($haveCrudAction) {
                    return $this->handleCrudAction($crudAction);
                }

                return $this->completeSection('vehicles_psv');
            }
        }

        return $this->renderForm($form, 'vehicles_psv', $resultData);
    }

    /**
     * Override handleCrudAction
     *
     * @param array $crudActionData Action data
     *
     * @return \Zend\Http\Response
     */
    protected function handleCrudAction(array $crudActionData)
    {
        $alternativeCrudResponse = $this->checkForAlternativeCrudAction(
            $this->getActionFromCrudAction($crudActionData)
        );
        if ($alternativeCrudResponse !== null) {
            return $alternativeCrudResponse;
        }

        return $this->traitHandleCrudAction(
            $crudActionData,
            [
                'add', 'show-removed-vehicles', 'hide-removed-vehicles'
            ]
        );
    }

    /**
     * Update Vehicle Section on API
     *
     * @param \Common\Form\Form $form           Form
     * @param boolean           $haveCrudAction Have Crud Action
     *
     * @return \Common\View\Model\Section|null
     */
    protected function updateVehiclesSection(FormInterface $form, $haveCrudAction)
    {
        /** @var \Common\Service\Helper\FlashMessengerHelperService $flashMssgr */
        $flashMssgr = $this->getServiceLocator()->get('Helper\FlashMessenger');

        $resultData = [];

        if ($this->lva === 'application') {
            $formData = $form->getData();

            $dtoData = [
                'id' => $this->getIdentifier(),
                'version' => $formData['data']['version'],
                'hasEnteredReg' => $formData['data']['hasEnteredReg'],
                'partial' => $haveCrudAction,
            ];

            $resultData['hasEnteredReg'] = $formData['data']['hasEnteredReg'];

            $response = $this->handleCommand(CommandDto\Application\UpdatePsvVehicles::create($dtoData));

            if ($response->isClientError()) {
                PsvVehicles::mapFormErrors(
                    $form,
                    $response->getResult()['messages'],
                    $flashMssgr
                );
                return $this->renderForm($form, 'vehicles_psv', $resultData);
            }

            if ($response->isServerError()) {
                $flashMssgr->addUnknownError();
                return $this->renderForm($form, 'vehicles_psv', $resultData);
            }
        }

        if ($this->lva === 'licence') {
            $shareInfo = $form->getData()['shareInfo']['shareInfo'];

            $dtoData = [
                'id' => $this->getIdentifier(),
                'shareInfo' => $shareInfo
            ];

            $response = $this->handleCommand(CommandDto\Licence\UpdateVehicles::create($dtoData));

            if (!$response->isOk()) {
                $flashMssgr->addCurrentErrorMessage('unknown-error');
                return $this->renderForm($form, 'vehicles_psv', $resultData);
            }
        }

        return null;
    }

    /**
     * Get the delete message.
     *
     * @return string
     */
    public function getDeleteMessage()
    {
        if ($this->lva === 'application') {
            return 'delete.confirmation.text';
        }

        $resultData = $this->fetchResultData();

        $toDelete = count(explode(',', $this->params('child_id')));
        $total = $this->getTotalNumberOfVehicles($resultData);

        $acceptedLicenceTypes = [
            LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL,
            LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL
        ];

        if (in_array($resultData['licenceType']['id'], $acceptedLicenceTypes, true)
            && $total === $toDelete
        ) {
            return 'deleting.all.vehicles.message';
        }

        return 'delete.confirmation.text';
    }

    /**
     * Transfer vehicles
     *
     * @return \Common\View\Model\Section | \Zend\Http\Response
     */
    public function transferAction()
    {
        /* @see \Common\Controller\Lva\Traits\TransferVehiclesTrait::transferVehicles */
        return $this->transferVehicles();
    }

    /**
     * Add a vehicle action
     *
     * @return \Common\View\Model\Section|\Zend\Http\Response
     */
    public function addAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        $data = [];
        if ($request->isPost()) {
            $data = (array)$request->getPost();
        }

        $params = [
            'mode' => 'add',
            'canAddAnother' => true,
            'action' => $this->params('action'),
            'isRemoved' => false
        ];

        /** @var \Common\Form\Form $form */
        $form = $this->getServiceLocator()
            ->get('FormServiceManager')
            ->get('lva-' . $this->lva . '-' . $this->section . '-vehicle')
            ->getForm($this->getRequest(), $params)
            ->setData($data);

        if ($request->isPost() && $form->isValid()) {
            $formData = $form->getData();

            $dtoClass = $this->createVehicleMap[$this->lva];

            $dtoData = PsvVehiclesVehicle::mapFromForm($formData);
            $dtoData[$this->getIdentifierIndex()] = $this->getIdentifier();

            $response = $this->handleCommand($dtoClass::create($dtoData));

            if ($response->isOk()) {
                return $this->handlePostSave();
            }

            /** @var \Common\Service\Helper\FlashMessengerHelperService $fm */
            $fm = $this->getServiceLocator()->get('Helper\FlashMessenger');

            if ($response->isClientError()) {
                PsvVehiclesVehicle::mapFormErrors($form, $response->getResult()['messages'], $fm);
            }

            if ($response->isServerError()) {
                $fm->addCurrentUnknownError();
            }
        }

        return $this->render('add_vehicle', $form);
    }

    /**
     * Edit action
     *
     * @return \Common\View\Model\Section|\Zend\Http\Response
     */
    public function editAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        $id = $this->params('child_id');
        $resultData = $this->fetchItemData($id);
        $data = PsvVehiclesVehicle::mapFromResult($resultData);

        if ($request->isPost()) {
            $postData = (array)$request->getPost();
            $data = $postData;
            $data['data'] = array_merge($data['data'], $postData['data']);
            if (isset($data['licence-vehicle']) && isset($postData['licence-vehicle'])) {
                $data['licence-vehicle'] = array_merge($data['licence-vehicle'], $postData['licence-vehicle']);
            }
        }

        $params = [
            'mode' => 'edit',
            'canAddAnother' => false,
            'action' => $this->params('action'),
            'isRemoved' => !empty($resultData['removalDate']),
            'location' => $this->location
        ];

        /** @var \Common\Form\Form $form */
        $form = $this->getServiceLocator()
            ->get('FormServiceManager')
            ->get('lva-' . $this->lva . '-' . $this->section . '-vehicle')
            ->getForm($this->getRequest(), $params)
            ->setData($data);

        if ($resultData['showHistory']) {
            /** @var \Common\Service\Table\TableBuilder $table */
            $table = $this->getServiceLocator()->get('Table')
                ->prepareTable('lva-psv-vehicles-history', $resultData['history']);

            $table->removeColumn('discNo');

            $this->getServiceLocator()->get('Helper\Form')->populateFormTable(
                $form->get('vehicle-history-table'),
                $table
            );
        }

        if ($request->isPost() && $form->isValid()) {
            $formData = $form->getData();

            $dtoData = PsvVehiclesVehicle::mapFromForm($formData);
            $dtoData['id'] = $id;
            $dtoData[$this->getIdentifierIndex()] = $this->getIdentifier();

            $response = $this->handleCommand(CommandDto\LicenceVehicle\UpdatePsvLicenceVehicle::create($dtoData));

            if ($response->isOk()) {
                return $this->handlePostSave();
            }

            /** @var \Common\Service\Helper\FlashMessengerHelperService $fm */
            $fm = $this->getServiceLocator()->get('Helper\FlashMessenger');

            if ($response->isClientError()) {
                PsvVehiclesVehicle::mapFormErrors($form, $response->getResult()['messages'], $fm);
            }

            if ($response->isServerError()) {
                $fm->addCurrentUnknownError();
            }
        }

        return $this->render('edit_vehicle', $form);
    }

    /**
     * Hijack the crud action check so we can validate the add button
     *
     * @param string $action Action
     *
     * @return null|\Zend\Http\Response
     */
    protected function checkForAlternativeCrudAction($action)
    {
        // export to CSV action
        if ($this->lva === 'licence' && $this->location === 'external' && $action === 'export') {
            $includeRemoved = $this->params()->fromQuery('includeRemoved');
            $data = [
                'id' => $this->getIdentifier(),
                'includeRemoved' => (isset($includeRemoved) && $includeRemoved == '1')
            ];
            $response = $this->handleQuery(PsvVehiclesExport::create($data));
            if (!$response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addUnknownError();
                return $this->redirect()->toRouteAjax(
                    $this->getBaseRoute(),
                    [$this->getIdentifierIndex() => $this->getIdentifier()]
                );
            }
            $data = $response->getResult();

            return $this->getServiceLocator()
                ->get('Helper\Response')
                ->tableToCsv(
                    $this->getResponse(),
                    $this->getServiceLocator()->get('Table')->prepareTable('lva-psv-vehicles-export', $data['result']),
                    'psv-vehicles'
                );
        }

        return null;
    }

    /**
     * Alter Table
     *
     * @param \Common\Service\Table\TableBuilder $table   Table
     * @param array                              $filters Filters
     *
     * @return \Common\Service\Table\TableBuilder
     */
    private function alterTable($table, array $filters = [])
    {
        // if licence on external then add an "Export" action
        if ($this->lva === 'licence' && $this->location === 'external') {
            $table->addAction(
                'export',
                [
                    'requireRows' => true,
                    'class' => ' more-actions__item js-disable-crud action--secondary'
                ]
            );
        }

        return $table;
    }

    /**
     * Remove vehicle size tables based on OC data
     *
     * @param FormInterface $form          Form
     * @param array         $resultData    Api data
     * @param boolean       $removeActions Is need to remove actions
     *
     * @return Form
     */
    private function alterForm(Form $form, $resultData, $removeActions)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $table = $this->getTable($resultData['licenceVehicles'], $removeActions);

        $formHelper->populateFormTable($form->get('vehicles'), $table, 'vehicles');

        if (!$removeActions && !$resultData['canTransfer']) {
            $table->removeAction('transfer');
        }

        if ((int)$resultData['allVehicleCount'] > (int)$resultData['activeVehicleCount']) {
            $this->addRemovedVehiclesActions($this->getFilters(), $table);
        }

        if (in_array($this->lva, ['licence', 'variation'], true)) {
            $this->getServiceLocator()->get('FormServiceManager')
                ->get('lva-licence-variation-vehicles')->alterForm($form);
        }

        return $form;
    }

    /**
     * Add Guidance
     *
     * @return void
     */
    private function addGuidance()
    {
        $message = 'psv-vehicles-' . $this->lva . '-missing-breakdown';

        if ($this->lva === 'variation') {
            $link = $this->url()->fromRoute('lva-variation/operating_centres', [], [], true);

            $class = '';
        } else {
            $params = ['licence' => $this->getIdentifier(), 'redirectRoute' => 'operating_centres'];
            $link = $this->getServiceLocator()->get('Helper\Url')->fromRoute('lva-licence/variation', $params);

            $class = 'js-modal-ajax';
        }

        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $message = $translator->translateReplace($message, [$link, $class]);

        $this->getServiceLocator()->get('Helper\Guidance')->append($message);
    }

    /**
     * Get the total number of vehicles
     *
     * @param array $resultData Data
     *
     * @return int
     */
    private function getTotalNumberOfVehicles($resultData)
    {
        return isset($resultData['total']) ? $resultData['total'] : 0;
    }

    /**
     * Delete vehicles
     *
     * @return void
     */
    protected function delete()
    {
        $licenceVehicleIds = explode(',', $this->params('child_id'));

        $dtoClass = $this->deleteMap[$this->lva];

        $dtoData = [
            'ids' => $licenceVehicleIds,
            $this->getIdentifierIndex() => $this->getIdentifier()
        ];

        $this->handleCommand($dtoClass::create($dtoData));
    }

    /**
     * Get the altered table
     *
     * @param array $tableData Table data
     * @param bool  $readOnly  Is Read only
     *
     * @return \Common\Service\Table\TableBuilder
     */
    private function getTable($tableData, $readOnly = false)
    {
        return $this->alterTable($this->getTableBasic($tableData, $readOnly), $this->getFilters());
    }

    /**
     * Get the table
     *
     * @param array $tableData Table data
     * @param bool  $readOnly  Is Read only
     *
     * @return \Common\Service\Table\TableBuilder
     */
    private function getTableBasic($tableData, $readOnly = false)
    {
        $tableName = 'lva-psv-vehicles';
        if ($readOnly) {
            $tableName .= '-readonly';
        }
        $query = $this->removeUnusedParametersFromQuery(
            (array) $this->getRequest()->getQuery()
        );
        $params = array_merge($query, ['query' => $query]);
        return $this->getServiceLocator()->get('Table')->prepareTable($tableName, $tableData, $params);
    }

    /**
     * Render Form
     *
     * @param FormInterface $form       Form
     * @param string        $section    Section title
     * @param array         $headerData Header data
     *
     * @return \Common\View\Model\Section
     */
    private function renderForm($form, $section, $headerData)
    {
        $params = [];

        $files = $this->getScripts();

        $searchForm = $this->getVehcileSearchForm($headerData);
        if ($searchForm) {
            $params['searchForm'] = $searchForm;
            $files[] = 'forms/vehicle-search';
        }
        $this->getServiceLocator()->get('Script')->loadFiles($files);
        return $this->render($section, $form, $params);
    }

    /**
     * Fetch vehicle data
     *
     * @return array
     */
    private function fetchResultData()
    {
        $dtoClass = $this->queryMap[$this->lva];
        $dtoData = $this->getFilters();
        $dtoData['id'] = $this->getIdentifier();
        $response = $this->handleQuery($dtoClass::create($dtoData));
        if ($response->isForbidden()) {
            return null;
        }
        return $response->getResult();
    }

    /**
     * Fetch one vehicles data
     *
     * @param int $id Id
     *
     * @return array
     */
    private function fetchItemData($id)
    {
        $response = $this->handleQuery(QueryDto\LicenceVehicle\PsvLicenceVehicle::create(['id' => $id]));

        return $response->getResult();
    }

    /**
     * Get table filter parameter
     * @return array
     */
    protected function getFilters()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $query = $request->getPost('query');
        } else {
            $query = $request->getQuery();
        }

        return $this->formatFilters((array)$query);
    }

    /**
     * Build filter from query params
     *
     * @param array $query Query parameters
     *
     * @return array
     */
    protected function formatFilters($query)
    {
        $filters = [];
        $filters['includeRemoved'] = (isset($query['includeRemoved']) && $query['includeRemoved'] == '1');
        $filters['page'] = isset($query['page']) ? $query['page'] : 1;
        $filters['limit'] = isset($query['limit']) ? $query['limit'] : 10;
        $filters['sort'] = isset($query['sort']) ? $query['sort'] : 'createdOn';
        $filters['order'] = isset($query['order']) ? $query['order'] : 'DESC';
        if (isset($query['vehicleSearch']['vrm']) && !isset($query['vehicleSearch']['clearSearch'])) {
            $filters['vrm'] = $query['vehicleSearch']['vrm'];
        }
        return $filters;
    }

    protected function getScripts()
    {
        return ['lva-crud', 'vehicle-psv'];
    }
}
