<?php

namespace Common\Controller\Lva;

use Common\Category;
use Common\Data\Mapper\Lva\OperatingCentre;
use Common\Data\Mapper\Lva\OperatingCentres;
use Dvsa\Olcs\Transfer\Command\Application\CreateOperatingCentre as AppCreateOperatingCentre;
use Dvsa\Olcs\Transfer\Command\Application\DeleteOperatingCentres as AppDeleteOperatingCentres;
use Dvsa\Olcs\Transfer\Command\Application\UpdateOperatingCentres as AppUpdateOperatingCentres;
use Dvsa\Olcs\Transfer\Command\ApplicationOperatingCentre\Update as AppUpdate;
use Dvsa\Olcs\Transfer\Command\Licence\CreateOperatingCentre as LicCreateOperatingCentre;
use Dvsa\Olcs\Transfer\Command\Licence\DeleteOperatingCentres as LicDeleteOperatingCentres;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateOperatingCentres as LicUpdateOperatingCentres;
use Dvsa\Olcs\Transfer\Command\LicenceOperatingCentre\Update as LicUpdate;
use Dvsa\Olcs\Transfer\Command\Variation\DeleteOperatingCentre as VarDeleteOperatingCentre;
use Dvsa\Olcs\Transfer\Command\VariationOperatingCentre\Update as VarUpdate;
use Dvsa\Olcs\Transfer\Query\Application\OperatingCentres as AppOperatingCentres;
use Dvsa\Olcs\Transfer\Query\ApplicationOperatingCentre\ApplicationOperatingCentre;
use Dvsa\Olcs\Transfer\Query\Licence\OperatingCentres as LicOperatingCentres;
use Dvsa\Olcs\Transfer\Query\LicenceOperatingCentre\LicenceOperatingCentre;
use Dvsa\Olcs\Transfer\Query\VariationOperatingCentre\VariationOperatingCentre;
use Zend\Mvc\MvcEvent;

/**
 * Shared logic between Operating Centres controllers
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractOperatingCentresController extends AbstractController
{
    use Traits\CrudTableTrait {
        Traits\CrudTableTrait::handleCrudAction as traitHandleCrudAction;
    }

    protected $section = 'operating_centres';
    protected $baseRoute = 'lva-%s/operating_centres';

    protected $listQueryMap = [
        'licence' => LicOperatingCentres::class,
        'variation' => AppOperatingCentres::class,
        'application' => AppOperatingCentres::class,
    ];

    protected $getItemCommandMap = [
        'licence' => LicenceOperatingCentre::class,
        'variation' => VariationOperatingCentre::class,
        'application' => ApplicationOperatingCentre::class,
    ];

    protected $updateCommandMap = [
        'licence' => LicUpdateOperatingCentres::class,
        'variation' => AppUpdateOperatingCentres::class,
        'application' => AppUpdateOperatingCentres::class,
    ];

    protected $updateItemCommandMap = [
        'licence' => LicUpdate::class,
        'variation' => VarUpdate::class,
        'application' => AppUpdate::class,
    ];

    protected $deleteCommandMap = [
        'licence' => LicDeleteOperatingCentres::class,
        'variation' => VarDeleteOperatingCentre::class,
        'application' => AppDeleteOperatingCentres::class,
    ];

    protected $createCommandMap = [
        'licence' => LicCreateOperatingCentre::class,
        // Variation create is the same as New Apps
        'variation' => AppCreateOperatingCentre::class,
        'application' => AppCreateOperatingCentre::class,
    ];

    protected $documents;

    /**
     * Operating centre ID, when editing
     * @var int|null
     */
    protected $operatingCentreId;

    /** @var  \Common\Service\Helper\FlashMessengerHelperService */
    private $hlpFlashMsgr;
    /** @var  \Common\Service\Helper\TranslationHelperService */
    private $hlpTranslator;


    /**
     * On Dispatch
     *
     * @param MvcEvent $e Event
     *
     * @return void
     */
    public function onDispatch(MvcEvent $e)
    {
        $this->hlpFlashMsgr = $this->getServiceLocator()->get('Helper\FlashMessenger');
        $this->hlpTranslator = $this->getServiceLocator()->get('Helper\Translation');

        parent::onDispatch($e);
    }

    /**
     * Operating centre list action
     *
     * @return \Common\View\Model\Section|\Zend\Http\Response
     */
    public function indexAction()
    {
        $resultData = $this->fetchOcData();
        if ($resultData === null) {
            return $this->notFoundAction();
        }

        if ($resultData['requiresVariation']) {
            $this->getServiceLocator()->get('Lva\Variation')
                ->addVariationMessage($this->getIdentifier(), $this->section);
        }

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $data = OperatingCentres::mapFromResult($resultData);
        }

        $query = $this->getRequest()->getQuery();
        $params = array_merge((array)$query, ['query' => $query]);
        $resultData['query'] = $params;

        /** @var \Zend\Form\FormInterface $form */
        $form = $this->getServiceLocator()->get('FormServiceManager')
            ->get('lva-' . $this->lva . '-operating_centres')
            ->getForm($resultData)
            ->setData($data);

        if ($request->isPost()) {
            $crudAction = $this->getCrudAction([$data['table']]);

            if ($crudAction !== null) {
                if ($this->isInternalReadOnly()) {
                    return $this->handleCrudAction($crudAction);
                }
                $this->getServiceLocator()->get('Helper\Form')->disableValidation($form->getInputFilter());
            }

            if ($form->isValid()) {
                $response = $this->processUpdateOc($form, $crudAction);

                if ($response !== null) {
                    return $response;
                }
            }
        }

        if (isset($resultData['isVariation']) && $resultData['isVariation']) {
            $this->getServiceLocator()->get('Script')->loadFile('lva-crud-delta');
        } else {
            $this->getServiceLocator()->get('Script')->loadFile('lva-crud');
        }

        // if traffic area dropdown and enforement area dropdown exists, then add JS to popoulate enforcement
        // area when traffic area is changed
        if ($form->has('dataTrafficArea') &&
            $form->get('dataTrafficArea')->has('trafficArea') &&
            $form->get('dataTrafficArea')->has('enforcementArea')
        ) {
            $this->getServiceLocator()->get('Script')->loadFile('application-oc');
        }

        return $this->render('operating_centres', $form);
    }

    /**
     * Process Update Operation Center
     *
     * @param \Zend\Form\FormInterface $form       Form
     * @param array                    $crudAction Table parameters
     *
     * @return \Zend\Http\Response
     */
    protected function processUpdateOc($form, $crudAction)
    {
        $dtoData = OperatingCentres::mapFromForm($form->getData());
        $dtoData['id'] = $this->getIdentifier();

        if ($crudAction !== null) {
            $dtoData['partial'] = true;
            $dtoData['partialAction'] = $this->getActionFromCrudAction($crudAction);
        } else {
            $dtoData['partial'] = false;
        }

        $dtoClass = $this->updateCommandMap[$this->lva];
        $response = $this->handleCommand($dtoClass::create($dtoData));

        if ($response->isOk()) {
            if ($crudAction !== null) {
                return $this->handleCrudAction($crudAction);
            }

            return $this->completeSection('operating_centres');
        }

        if ($response->isServerError()) {
            $this->hlpFlashMsgr->addUnknownError();
        } else {
            $errors = $response->getResult()['messages'];

            if ($crudAction !== null) {
                $this->displayCrudErrors($errors);
                return null;
            }

            OperatingCentres::mapFormErrors($form, $errors, $this->hlpFlashMsgr, $this->hlpTranslator, $this->location);
        }

        return null;
    }

    /**
     * Display error from updating OC page after a CRUD action
     *
     * @param array $errors Errors
     *
     * @return void
     */
    private function displayCrudErrors($errors)
    {
        if (empty($errors)) {
            $this->hlpFlashMsgr->addUnknownError();
            return;
        }

        OperatingCentres::mapApiErrors($this->location, $errors, $this->hlpFlashMsgr, $this->hlpTranslator);
    }

    /**
     * Create Operating centre action
     *
     * @return \Common\View\Model\Section|\Zend\Http\Response
     */
    public function addAction()
    {
        /** @var \Zend\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();

        $data = [];

        if ($request->isPost()) {
            $data = OperatingCentre::mapFromPost((array) $request->getPost());
        }

        $resultData = $this->fetchOcData();

        $this->documents = $resultData['documents'];

        $resultData['action'] = 'add';
        // Only applicable when editing (On a variation)
        $resultData['wouldIncreaseRequireAdditionalAdvertisement'] = false;
        // Only applicable when editing (On a variation)
        $resultData['canUpdateAddress'] = true;

        $resultData['isVariation'] = ($this->lva === 'variation');
        if ($this->lva !== 'licence') {
            $lvaData = $this->fetchDataForLva();
            $resultData['licNo'] = $lvaData['licence']['licNo'];
            $resultData['applicationId'] = $this->getIdentifier();
        }

        /** @var \Zend\Form\FormInterface $form */
        $form = $this->getServiceLocator()->get('FormServiceManager')
            ->get('lva-' . $this->lva . '-operating_centre')
            ->getForm($resultData, $request)
            ->setData($data);

        $hasProcessedPostcode = $this->getServiceLocator()->get('Helper\Form')
            ->processAddressLookupForm($form, $request);

        if ($form->has('advertisements')) {
            $hasProcessedFiles = $this->processFiles(
                $form,
                'advertisements->adPlacedContent->file',
                [$this, 'processAdvertisementFileUpload'],
                [$this, 'deleteFile'],
                [$this, 'getDocuments']
            );
        } else {
            $hasProcessedFiles = false;
        }

        if (!$hasProcessedFiles && !$hasProcessedPostcode && $request->isPost() && $form->isValid()) {
            $formData = array_merge($form->getData(), ['isTaOverridden' => $request->getPost('form-actions')['confirm-add']]);
            $dtoData = OperatingCentre::mapFromForm($formData);
            $dtoData[$this->getIdentifierIndex()] = $this->getIdentifier();


            $dtoClass = $this->createCommandMap[$this->lva];
            $response = $this->handleCommand($dtoClass::create($dtoData));

            if ($response->isOk()) {
                return $this->handlePostSave(null, false);
            }

            $fm = $this->getServiceLocator()->get('Helper\FlashMessenger');

            if ($response->isServerError()) {
                $fm->addUnknownError();
            } else {
                $taGuidesUrl = $this->url()->fromRoute(
                    'guides/guide',
                    ['guide' => 'traffic-area']
                );
                $translator = $this->getServiceLocator()->get('Helper\Translation');
                OperatingCentre::mapFormErrors(
                    $form,
                    $response->getResult()['messages'],
                    $fm,
                    $translator,
                    $this->location,
                    $taGuidesUrl
                );
            }
        }

        $this->getServiceLocator()->get('Script')->loadFile('add-operating-centre');

        return $this->render('add_operating_centre', $form);
    }

    /**
     * Update Operating centre action
     *
     * @return \Common\View\Model\Section | \Zend\Http\Response
     */
    public function editAction()
    {
        //normally we validate adverts have been uploaded, but not for variations where authorisation hasn't increased
        $validateAdverts = true;

        /** @var \Zend\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();

        $resultData = $this->fetchOcItemData();

        $this->documents = $resultData['operatingCentre']['adDocuments'];
        // need to store the operating centre ID so that uploaded documents can be attached
        $this->operatingCentreId = $resultData['operatingCentre']['id'];

        if ($request->isPost()) {
            $data = (array)$request->getPost();

            if ($this->isVariationWithNoAuthIncrease($data, $resultData)) {
                $data = $this->clearAdvertisementData($data);
                $validateAdverts = false;
            }

            if (!$resultData['canUpdateAddress']) {
                $data['address'] = $resultData['operatingCentre']['address'];
            }
        } else {
            $data = OperatingCentre::mapFromResult($resultData);
        }

        if (!isset($data['advertisements']['adPlacedContent']['file']['list'])) {
            $data['advertisements']['adPlacedContent']['file']['list'] = [];
        }

        $data['advertisements']['uploadedFileCount'] =
            count($data['advertisements']['adPlacedContent']['file']['list']);

        $resultData['canAddAnother'] = false;
        $resultData['action'] = 'edit';

        $resultData['isVariation'] = ($this->lva === 'variation');
        if ($this->lva !== 'licence') {
            $lvaData = $this->fetchDataForLva();
            $resultData['licNo'] = $lvaData['licence']['licNo'];
            $resultData['applicationId'] = $this->getIdentifier();
        }

        /** @var \Zend\Form\FormInterface $form */
        $form = $this->getServiceLocator()->get('FormServiceManager')
            ->get('lva-' . $this->lva . '-operating_centre')
            ->getForm($resultData, $request)
            ->setData($data);

        if ($form->get('address')->has('searchPostcode')) {
            $hasProcessedPostcode = $this->getServiceLocator()->get('Helper\Form')
                ->processAddressLookupForm($form, $request);
        } else {
            $hasProcessedPostcode = false;
        }

        if ($form->has('advertisements') && $validateAdverts) {
            $hasProcessedFiles = $this->processFiles(
                $form,
                'advertisements->adPlacedContent->file',
                [$this, 'processAdvertisementFileUpload'],
                [$this, 'deleteFile'],
                [$this, 'getDocuments']
            );
        } else {
            $hasProcessedFiles = false;
        }

        if (!$hasProcessedFiles && !$hasProcessedPostcode && $request->isPost() && $form->isValid()) {
            $formData = array_merge($form->getData(), ['isTaOverridden' => $request->getPost('form-actions')['confirm-add']]);
            $dtoData = OperatingCentre::mapFromForm($formData);
            if (!$resultData['canUpdateAddress']) {
                unset($dtoData['address']);
            }

            $dtoData['id'] = $this->params('child_id');

            // Only needed for variations
            $dtoData[$this->getIdentifierIndex()] = $this->getIdentifier();

            $dtoClass = $this->updateItemCommandMap[$this->lva];
            $response = $this->handleCommand($dtoClass::create($dtoData));

            if ($response->isOk()) {
                return $this->handlePostSave(null, false);
            }

            $fm = $this->getServiceLocator()->get('Helper\FlashMessenger');

            if ($response->isServerError()) {
                $fm->addUnknownError();
            } else {
                $taGuidesUrl = $this->url()->fromRoute(
                    'guides/guide',
                    ['guide' => 'traffic-area']
                );
                $translator = $this->getServiceLocator()->get('Helper\Translation');
                OperatingCentre::mapFormErrors(
                    $form,
                    $response->getResult()['messages'],
                    $fm,
                    $translator,
                    $this->location,
                    $taGuidesUrl
                );
            }
        }

        $this->getServiceLocator()->get('Script')->loadFile('add-operating-centre');

        return $this->render('edit_operating_centre', $form);
    }

    /**
     * Get Documents
     *
     * @return array
     */
    public function getDocuments()
    {
        if ($this->documents === null) {
            if ($this->params('child_id')) {
                $this->documents = $this->fetchOcItemData()['operatingCentre']['adDocuments'];
            } else {
                $this->documents = $this->fetchOcData()['documents'];
            }
        }

        return $this->documents;
    }

    /**
     * Delete
     *
     * @return bool
     */
    protected function delete()
    {
        $id = $this->params('child_id');
        $data = [
            $this->getIdentifierIndex() => $this->getIdentifier(),
            'id' => $id,
            'ids' => explode(',', $id)
        ];

        $dtoClass = $this->deleteCommandMap[$this->lva];
        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->handleCommand($dtoClass::create($data));

        if ($response->isOk()) {
            return true;
        }

        $fm = $this->getServiceLocator()->get('Helper\FlashMessenger');

        if ($response->isClientError()) {
            $messages = $response->getResult()['messages'];

            foreach ($messages as $message) {
                $fm->addErrorMessage($message);
            }

            if (empty($messages)) {
                $fm->addUnknownError();
            }

            return false;
        }

        $fm->addUnknownError();
    }

    /**
     * Override method to turn off parent befaviour
     *
     * @return void
     */
    protected function deleteFailed()
    {
        // do nothing as message already display in delete method
    }

    /**
     * Handle the file upload
     *
     * @param array $file File
     *
     * @return void
     */
    public function processAdvertisementFileUpload($file)
    {
        // Reset the list, so we have to fetch it again
        $this->documents = null;

        $data = [
            'description' => $file['name'],
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => Category::DOC_SUB_CATEGORY_APPLICATION_ADVERT_DIGITAL,
            'isExternal'  => $this->isExternal(),
            'licence' => $this->getLicenceId(),
        ];

        if ($this->lva !== 'licence') {
            $data['application'] = $this->getIdentifier();
        }

        if (!empty($this->operatingCentreId)) {
            $data['operatingCentre'] = $this->operatingCentreId;
        }
        $this->uploadFile($file, $data);
    }

    /**
     * Delete message title key
     *
     * @return string
     */
    protected function getDeleteTitle()
    {
        return 'delete-oc';
    }

    /**
     * Redirect to the most appropriate CRUD action
     *
     * @param array  $data             Data
     * @param array  $rowsNotRequired  Action
     * @param string $childIdParamName Child route identifier
     * @param string $route            Route
     *
     * @return \Zend\Http\Response
     * @overridden
     */
    protected function handleCrudAction(
        $data,
        $rowsNotRequired = ['add'],
        $childIdParamName = 'child_id',
        $route = null
    ) {
        if ($data['action'] === 'Add schedule 4/1') {
            return $this->redirect()->toRouteAjax('lva-application/schedule41', [], [], true);
        }

        return $this->traitHandleCrudAction($data, $rowsNotRequired, $childIdParamName, $route);
    }

    /**
     * Fetch application/licence with OC
     *
     * @return array
     */
    protected function fetchOcData()
    {
        $queryDtoClass = $this->listQueryMap[$this->lva];
        if ($this->getRequest()->isPost()) {
            $query = $this->getRequest()->getPost('query');
        } else {
            $query = $this->getRequest()->getQuery();
        }
        $defaultSort = $this->lva == 'variation' ? 'lastModifiedOn' : 'createdOn';
        $params = [
            'id' => $this->getIdentifier(),
            'sort'  => isset($query['sort']) ? $query['sort'] : $defaultSort,
            'order' => isset($query['order']) ? $query['order'] : 'DESC',
        ];

        $response = $this->handleQuery($queryDtoClass::create($params));

        return $response->isForbidden() ? null : $response->getResult();
    }

    /**
     * Fetch application/licence operating centre data
     *
     * @return array
     */
    protected function fetchOcItemData()
    {
        $dtoClass = $this->getItemCommandMap[$this->lva];
        $response = $this->handleQuery($dtoClass::create(['id' => $this->params('child_id')]));
        return $response->getResult();
    }

    /**
     * For lva variations we check whether the authorisation has increased
     *
     * @param array $data       posted form data
     * @param array $resultData the original operating centre data
     *
     * @return bool
     */
    private function isVariationWithNoAuthIncrease($data, $resultData)
    {
        //if we're in a variation and the authorisation hasn't increased
        return (
            $this->lva === 'variation'
            && isset($data['data']['noOfVehiclesRequired'])
            && isset($data['data']['noOfTrailersRequired'])
            && $data['data']['noOfVehiclesRequired'] <= $resultData['currentVehiclesRequired']
            && $data['data']['noOfTrailersRequired'] <= $resultData['currentTrailersRequired']
        );
    }

    /**
     * Clear the advertisement data, used on variations where the authorisation has not increased
     *
     * @param array $data posted form data
     *
     * @return array
     */
    private function clearAdvertisementData($data)
    {
        //overwrite fields, and remove file upload fields
        $data['advertisements'] = [
            'radio' => 'adSendByPost',
            'adPlacedContent' => [
                'adPlacedIn' => '',
                'adPlacedDate' => [
                    'year' => null,
                    'month' => null,
                    'day' => null
                ],
            ]
        ];

        return $data;
    }
}
