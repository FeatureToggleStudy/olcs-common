<?php

/**
 * Authorisation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Common\Controller\Application\OperatingCentres;

/**
 * Authorisation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class AuthorisationController extends OperatingCentresController
{
    /**
     * Holds the table data
     *
     * @var array
     */
    private $tableData = null;

    /**
     * Holds the data bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'properties' => array(
            'id',
            'version',
            'totAuthSmallVehicles',
            'totAuthMediumVehicles',
            'totAuthLargeVehicles',
            'totCommunityLicences',
            'totAuthVehicles',
            'totAuthTrailers',
        ),
        'children' => array(
            'licence' => array(
                'properties' => array(
                    'id'
                ),
                'children' => array(
                    'trafficArea' => array(
                        'properties' => array(
                            'id',
                            'name'
                        )
                    )
                )
            ),
            'operatingCentre' => array(
                'properties' => array(
                    'id',
                    'version'
                ),
                'children' => array(
                    'address' => array(
                        'properties' => array(
                            'id',
                            'version',
                            'addressLine1',
                            'addressLine2',
                            'addressLine3',
                            'addressLine4',
                            'postcode',
                            'town'
                        ),
                        'children' => array(
                            'countryCode' => array(
                                'properties' => array('id')
                            )
                        )
                    ),
                    'adDocuments' => array(
                        'properties' => array(
                            'id',
                            'version',
                            'filename',
                            'identifier',
                            'size'
                        )
                    )
                )
            )
        )
    );

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'data',
                'dataTrafficArea'
            ),
        ),
    );

    /**
     * Holds the sub action service
     *
     * @var string
     */
    protected $actionService = 'ApplicationOperatingCentre';

    /**
     * Action data map
     *
     * @var array
     */
    protected $actionDataMap = array(
        '_addresses' => array(
            'address'
        ),
        'main' => array(
            'children' => array(
                'applicationOperatingCentre' => array(
                    'mapFrom' => array(
                        'data',
                        'advertisements'
                    )
                ),
                'operatingCentre' => array(
                    'mapFrom' => array(
                        'operatingCentre'
                    ),
                    'children' => array(
                        'addresses' => array(
                            'mapFrom' => array(
                                'addresses'
                            )
                        )
                    )
                ),
            )
        )
    );

    /**
     * Holds the actionDataBundle
     *
     * @var array
     */
    protected $actionDataBundle = array(
        'properties' => array(
            'id',
            'version',
            'noOfTrailersPossessed',
            'noOfVehiclesPossessed',
            'sufficientParking',
            'permission',
            'adPlaced',
            'adPlacedIn',
            'adPlacedDate'
        ),
    );

    /**
     * Form tables name
     *
     * @var string
     */
    protected $formTables = array(
        'table' => 'authorisation_in_form',
    );

    /**
     * Holds the Traffic Area details
     *
     * @var array
     */
    private $trafficArea;

    /**
     * Northern Ireland Traffic Area Code
     */
    const NORTHERN_IRELAND_TRAFFIC_AREA_CODE = 'N';

    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->renderSection();
    }

    /**
     * Add operating centre
     */
    public function addAction()
    {
        return $this->renderSection();
    }

    /**
     * Edit operating centre
     */
    public function editAction()
    {
        return $this->renderSection();
    }

    /**
     * Delete sub action
     *
     * @return Response
     */
    public function deleteAction()
    {
        $this->maybeClearTrafficAreaId();
        return $this->delete();
    }

    /**
     * Make form alterations
     *
     * This method enables the summary to apply the same form alterations. In this
     * case we ensure we manipulate the form based on whether the license is PSV or not
     *
     * @param Form $form
     * @param mixed $context
     * @param array $options
     *
     * @return $form
     */
    public static function makeFormAlterations($form, $context, $options = array())
    {
        // Need to enumerate the form fieldsets with their mapping, as we're
        // going to use old/new
        $fieldsetMap = Array();
        if ( $options['isReview'] ) {
            foreach ($options['fieldsets'] as $fieldset) {
                $fieldsetMap[$form->get($fieldset)->getAttribute('unmappedName')]=$fieldset;
            }
        } else {
            $fieldsetMap = Array(
                'dataTrafficArea' => 'dataTrafficArea',
                'data' => 'data',
                'table' => 'table'
            );
        }

        if ($options['isPsv']) {

            $formOptions = $form->get($fieldsetMap['data'])->getOptions();
            $formOptions['hint'] .= '.psv';
            $form->get($fieldsetMap['data'])->setOptions($options);

            $licenceType=$options['data']['data']['licence']['licenceType']['id'];
            if (!in_array(
                $licenceType,
                array('ltyp_sn', 'ltyp_si')
            )) {
                $form->get($fieldsetMap['data'])->remove('totAuthLargeVehicles');
            }

            if (!in_array(
                $context->getLicenceType(),
                array('ltyp_si', 'ltyp_r')
            )) {
                $form->get($fieldsetMap['data'])->remove('totCommunityLicences');
            }

            $form->get($fieldsetMap['data'])->remove('totAuthVehicles');
            $form->get($fieldsetMap['data'])->remove('totAuthTrailers');
            $form->get($fieldsetMap['data'])->remove('minTrailerAuth');
            $form->get($fieldsetMap['data'])->remove('maxTrailerAuth');

        } else {

            $form->get($fieldsetMap['data'])->remove('totAuthSmallVehicles');
            $form->get($fieldsetMap['data'])->remove('totAuthMediumVehicles');
            $form->get($fieldsetMap['data'])->remove('totAuthLargeVehicles');
            $form->get($fieldsetMap['data'])->remove('totCommunityLicences');
        }

        if ($options['isPsv']) {
            $table = $form->get($fieldsetMap['table'])->get('table')->getTable();
            $cols = $table->getColumns();
            unset($cols['trailersCol']);
            $table->setColumns($cols);
            $footer = $table->getFooter();
            $footer['total']['content'] .= '-psv';
            unset($footer['trailersCol']);
            $table->setFooter($footer);
        }

        // Review-only options - we set the traffic area field in a different way
        // because of the method scope.
        if ( $options['isReview'] ) {
            $form->get($fieldsetMap['dataTrafficArea'])->remove('trafficArea');
            $bundle = array(
                'children' => array(
                    'licence' => array(
                        'children' => array(
                            'trafficArea' => array(
                                'properties' => array(
                                    'name'
                                )
                            )
                        )
                    )
                )
            );

            $application = $context->makeRestCall(
                'Application',
                'GET',
                array(
                    'id' => $options['data']['id'],
                ),
                $bundle
            );

            if (is_array($application) && array_key_exists('licence', $application) &&
                is_array($application['licence']) &&
                array_key_exists('trafficArea', $application['licence'])) {
                $form
                    ->get($fieldsetMap['dataTrafficArea'])
                    ->get('trafficAreaInfoNameExists')
                    ->setValue($application['licence']['trafficArea']['name']);
            } else {
                $form
                    ->get($fieldsetMap['dataTrafficArea'])
                    ->get('trafficAreaInfoNameExists')
                    ->setValue('unset');
            }

        }

        return $form;
    }

    /**
     * Remove trailer elements for PSV and set up Traffic Area section
     *
     * @param object $form
     * @return object
     */
    protected function alterForm($form)
    {
        $options=Array(
            'isPsv' => $this->isPsv(),
            'isReview' => false,
            'data' => array(
                'data' => array(
                    'licence' => array(
                        'licenceType' => array(
                            'id' => $this->licenceType
                        )
                    )
                )
            )
        );
        $form=$this->makeFormAlterations($form, $this, $options);

        // set up Traffic Area section
        $operatingCentresExists = count($this->tableData);
        $trafficArea = $this->getTrafficArea();
        $trafficAreaId = $trafficArea ? $trafficArea['id'] : '';
        if (!$operatingCentresExists) {
            $form->remove('dataTrafficArea');
        } elseif ($trafficAreaId) {
            $form->get('dataTrafficArea')->remove('trafficArea');
            $template = $form->get('dataTrafficArea')->get('trafficAreaInfoNameExists')->getValue();
            $newValue = str_replace('%NAME%', $trafficArea['name'], $template);
            $form->get('dataTrafficArea')->get('trafficAreaInfoNameExists')->setValue($newValue);
        } else {
            $form->get('dataTrafficArea')->remove('trafficAreaInfoLabelExists');
            $form->get('dataTrafficArea')->remove('trafficAreaInfoNameExists');
            $form->get('dataTrafficArea')->remove('trafficAreaInfoHintExists');
            $form->get('dataTrafficArea')->get('trafficArea')->setValueOptions($this->getTrafficValueOptions());
        }

        return $form;
    }

    /**
     * Remove trailers for PSV
     *
     * @param Form $form
     */
    protected function alterActionForm($form)
    {
        if ($this->isPsv()) {
            $form->get('data')->remove('noOfTrailersPossessed');
            $form->remove('advertisements');

            $label = $form->get('data')->getLabel();
            $form->get('data')->setLabel($label .= '-psv');

            $label = $form->get('data')->get('sufficientParking')->getLabel();
            $form->get('data')->get('sufficientParking')->setLabel($label .= '-psv');

            $label = $form->get('data')->get('permission')->getLabel();
            $form->get('data')->get('permission')->setLabel($label .= '-psv');
        } else {

            $this->processFileUploads(
                array('advertisements' => array('file' => 'processAdvertisementFileUpload')),
                $form
            );

            $fileList = $form->get('advertisements')->get('file')->get('list');

            $bundle = array(
                'properties' => array(
                    'id',
                    'version',
                    'identifier',
                    'filename',
                    'size'
                )
            );

            $unlinkedFileData = $this->makeRestCall(
                'Document',
                'GET',
                array(
                    'application' => $this->getIdentifier(),
                    // @todo Add a better way to find the category id
                    'category' => 1,
                    'documentSubCategory' => 2,
                    'operatingCentre' => 'NULL'
                ),
                $bundle
            );

            $fileData = array();

            if ($this->getActionName() == 'edit') {
                $fileData = $this->actionLoad($this->getActionId())['operatingCentre']['adDocuments'];
            }

            $fileData = array_merge($fileData, $unlinkedFileData['Results']);

            $fileList->setFiles($fileData, $this->url());

            $this->processFileDeletions(array('advertisements' => array('file' => 'deleteFile')), $form);
        }

        // add traffic area validator
        $licenceData = $this->getLicenceData();

        $trafficAreaValidator = $this->getPostcodeTrafficAreaValidator();
        $trafficAreaValidator->setNiFlag($licenceData['niFlag']);
        $trafficAreaValidator->setOperatingCentresCount($this->getOperatingCentresCount());
        $trafficAreaValidator->setTrafficArea($this->getTrafficArea());

        $postcodeValidatorChain = $this->getPostcodeValidatorsChain($form);
        $postcodeValidatorChain->attach($trafficAreaValidator);

        $form->getInputFilter()->get('address')->get('postcode')->setRequired(false);

        if ($licenceData['niFlag'] == 'N' && !$this->getTrafficArea()) {
            $form->get('form-actions')->remove('addAnother');
        }

        return $form;
    }

    /**
     * Save the operating centre
     *
     * @param array $data
     * @param string $service
     * @return null|Response
     */
    protected function actionSave($data, $service = null)
    {
        $saved = parent::actionSave($data['operatingCentre'], 'OperatingCentre');

        if ($this->getActionName() == 'add') {
            if (!isset($saved['id'])) {
                throw new \Exception('Unable to save operating centre');
            }

            $data['applicationOperatingCentre']['operatingCentre'] = $saved['id'];

            $operatingCentreId = $saved['id'];
        } else {
            $operatingCentreId = $data['operatingCentre']['id'];
        }

        if (isset($data['applicationOperatingCentre']['file']['list'])) {
            foreach ($data['applicationOperatingCentre']['file']['list'] as $file) {
                $this->makeRestCall(
                    'Document',
                    'PUT',
                    array('id' => $file['id'], 'version' => $file['version'], 'operatingCentre' => $operatingCentreId)
                );
            }
        }

        if ($this->isPsv()) {
            $data['applicationOperatingCentre']['adPlaced'] = 0;
        }

        $saved = parent::actionSave($data['applicationOperatingCentre'], $service);

        if ($this->getActionName() == 'add' && !isset($saved['id'])) {
            throw new \Exception('Unable to save application operating centre');
        }

        // set default Traffic Area if we don't have one
        if (!array_key_exists('trafficArea', $data) || !$data['trafficArea']['id']) {
            $licenceData = $this->getLicenceData();
            if ($licenceData['niFlag'] == 'Y') {
                $this->setTrafficArea(self::NORTHERN_IRELAND_TRAFFIC_AREA_CODE);
            }
            if ($licenceData['niFlag'] == 'N' && $data['operatingCentre']['addresses']['address']['postcode']) {
                $ocCount = $this->getOperatingCentresCount();

                // first Operating Centre was just added or we are editing the first one
                if ($ocCount == 1) {
                    $postcodeService = $this->getPostcodeService();
                    list($trafficAreaId, $trafficAreaName) =
                        $postcodeService->getTrafficAreaByPostcode(
                            $data['operatingCentre']['addresses']['address']['postcode']
                        );
                    if ($trafficAreaId) {
                        $this->setTrafficArea($trafficAreaId);
                    }
                }
            }
        }
    }

    /**
     * Save method
     *
     * @param array $data
     * @param string $service
     */
    protected function save($data, $service = null)
    {
        if (isset($data['trafficArea']) && $data['trafficArea']) {
            $this->setTrafficArea($data['trafficArea']);
        }
        parent::save($data, $service);
    }

    /**
     * Process the action load data
     *
     * @param array $oldData
     */
    protected function processActionLoad($oldData)
    {
        $data['data'] = $oldData;

        if ($this->getActionName() != 'add') {
            $data['operatingCentre'] = $data['data']['operatingCentre'];
            $data['address'] = $data['operatingCentre']['address'];
            $data['address']['countryCode'] = $data['address']['countryCode']['id'];

            $data['advertisements'] = array(
                'adPlaced' => $data['data']['adPlaced'],
                'adPlacedIn' => $data['data']['adPlacedIn'],
                'adPlacedDate' => $data['data']['adPlacedDate']
            );

            unset($data['data']['adPlaced']);
            unset($data['data']['adPlacedIn']);
            unset($data['data']['adPlacedDate']);
            unset($data['data']['operatingCentre']);
        }

        $data['data']['application'] = $this->getIdentifier();
        $trafficArea = $this->getTrafficArea();
        if (is_array($trafficArea) && array_key_exists('id', $trafficArea)) {
            $data['trafficArea']['id'] = $trafficArea['id'];
        }

        return $data;
    }

    /**
     * Process the loading of data
     *
     * @param array $oldData
     */
    protected function processLoad($oldData)
    {
        $results = $this->getFormTableData($this->getIdentifier(), '');

        $data['data'] = $oldData;

        $data['data']['noOfOperatingCentres'] = count($results);
        $data['data']['minVehicleAuth'] = 0;
        $data['data']['maxVehicleAuth'] = 0;
        $data['data']['minTrailerAuth'] = 0;
        $data['data']['maxTrailerAuth'] = 0;
        $data['data']['licenceType'] = $this->getLicenceType();
        foreach ($results as $row) {

            $data['data']['minVehicleAuth'] = max(
                array($data['data']['minVehicleAuth'], $row['noOfVehiclesPossessed'])
            );
            $data['data']['minTrailerAuth'] = max(
                array($data['data']['minTrailerAuth'], $row['noOfTrailersPossessed'])
            );
            $data['data']['maxVehicleAuth'] += (int) $row['noOfVehiclesPossessed'];
            $data['data']['maxTrailerAuth'] += (int) $row['noOfTrailersPossessed'];
        }

        if (is_array($oldData) && array_key_exists('licence', $oldData) &&
            array_key_exists('trafficArea', $oldData['licence']) &&
            is_array($oldData['licence']['trafficArea']) &&
            array_key_exists('id', $oldData['licence']['trafficArea'])) {
            $data['dataTrafficArea']['hiddenId'] = $oldData['licence']['trafficArea']['id'];
        }
        return $data;
    }

    /**
     * Handle the file upload
     *
     * @param array $file
     */
    protected function processAdvertisementFileUpload($file)
    {
        $this->uploadFile(
            $file,
            array(
                'description' => 'Advertisement',
                // @todo Add a better way to find the category id
                'category' => 1,
                'documentSubCategory' => 2
            )
        );
    }

    /**
     * Get Traffic Area information for current application
     *
     * @return array
     */
    protected function getTrafficArea()
    {
        if (!$this->trafficArea) {
            $bundle = array(
                'properties' => array(
                    'id',
                    'version',
                ),
                'children' => array(
                    'licence' => array(
                        'properties' => array(
                            'id'
                        ),
                        'children' => array(
                            'trafficArea' => array(
                                'properties' => array(
                                    'id',
                                    'name'
                                )
                            )
                        )
                    )
                )
            );

            $application = $this->makeRestCall(
                'Application',
                'GET',
                array(
                    'id' => $this->getIdentifier(),
                ),
                $bundle
            );
            if (is_array($application) && array_key_exists('licence', $application) &&
                is_array($application['licence']) &&
                array_key_exists('trafficArea', $application['licence'])) {
                $this->trafficArea = $application['licence']['trafficArea'];
            }
        }
        return $this->trafficArea;
    }

    /**
     * Get Traffic Area value options for select element
     *
     * @return array
     */
    protected function getTrafficValueOptions()
    {
        $bundle = array(
            'properties' => array(
                'id',
                'name',
            ),
        );

        $trafficArea = $this->makeRestCall('TrafficArea', 'GET', array(), $bundle);
        $valueOptions = array();
        $results = $trafficArea['Results'];
        if (is_array($results) && count($results)) {
            usort(
                $results,
                function ($a, $b) {
                    return strcmp($a["name"], $b["name"]);
                }
            );

            // remove Northern Ireland Traffic Area
            foreach ($results as $key => $value) {
                if ($value['id'] == self::NORTHERN_IRELAND_TRAFFIC_AREA_CODE) {
                    unset($results[$key]);
                    break;
                }
            }

            foreach ($results as $element) {
                $valueOptions[$element['id']] = $element['name'];
            }
        }
        return $valueOptions;
    }

    /**
     * Clear Traffic Area if we are deleting last one operating centres
     */
    public function maybeClearTrafficAreaId()
    {
        $ocCount = $this->getOperatingCentresCount();
        if ($ocCount == 1 && $this->getActionId()) {
            $this->setTrafficArea(null);
        }
    }

    /**
     * Get operating centres count
     *
     * @return int
     */
    public function getOperatingCentresCount()
    {
        $bundle = array(
            'properties' => array(
                'id',
                'version'
            )
        );
        $operatingCentres = $this->makeRestCall(
            'ApplicationOperatingCentre',
            'GET',
            array(
                'application' => $this->getIdentifier(),
            ),
            $bundle
        );
        return $operatingCentres['Count'];
    }

    /**
     * Set traffic area to application's licence based on traarea id
     *
     * @param string $id
     */
    public function setTrafficArea($id = null)
    {
        $bundle = array(
            'properties' => array(
                'id',
                'version'
            ),
            'children' => array(
                'licence' => array(
                    'properties' => array(
                        'id',
                        'version'
                    )
                )
            )
        );
        $application = $this->makeRestCall('Application', 'GET', array('id' => $this->getIdentifier()), $bundle);
        if (is_array($application) && array_key_exists('licence', $application) &&
            array_key_exists('version', $application['licence'])) {
            $data = array(
                        'id' => $application['licence']['id'],
                        'version' => $application['licence']['version'],
                        'trafficArea' => $id
            );
            $this->makeRestCall('Licence', 'PUT', $data);
            if ($id) {
                $licenceService = $this->getLicenceService();
                $licenceService->generateLicence($this->getIdentifier());
            }
        }
    }

    /**
     * Check for actions
     *
     * @param string $route
     * @param array $params
     * @param string $itemIdParam
     *
     * @return boolean
     */
    public function checkForCrudAction($route = null, $params = array(), $itemIdParam = 'id')
    {
        $table = $this->params()->fromPost('table');
        $action = isset($table['action'])
            ? strtolower($table['action'])
            : strtolower($this->params()->fromPost('action'));

        if (empty($action)) {
            return false;
        }

        $params = array_merge($params, array('action' => $action));

        if ($action !== 'add') {
            $id = $this->params()->fromPost('id');

            if (empty($id)) {

                return false;
            }

            $params[$itemIdParam] = $id;
        }
        if (!$this->getTrafficArea()) {
            $dataTrafficArea = $this->params()->fromPost('dataTrafficArea');
            $trafficArea = is_array($dataTrafficArea) && isset($dataTrafficArea['trafficArea']) ?
                $dataTrafficArea['trafficArea'] : '';
            if ($action == 'add' && !$trafficArea && $this->getOperatingCentresCount()) {
                $this->addWarningMessage('Please select a traffic area');
                return $this->redirectToRoute(null, array(), array(), true);
            } elseif ($action == 'add' && $trafficArea) {
                $this->setTrafficArea($trafficArea);
            }
        }

        return $this->redirect()->toRoute($route, $params, [], true);
    }

    /**
     * Retrieve the relevant table data as we want to render it on the review summary page
     * Note that as with most controllers this is the same data we want to render on the
     * normal form page, hence why getFormTableData (declared later) simply wraps this
     */
    public static function getSummaryTableData($applicationId, $context, $tableName)
    {
        $actionDataBundle = array(
            'children' => array(
                'operatingCentre' => array(
                    'properties' => array(
                        'id',
                        'version'
                    ),
                    'children' => array(
                        'address' => array(
                            'properties' => array(
                                'id',
                                'version',
                                'addressLine1',
                                'addressLine2',
                                'addressLine3',
                                'addressLine4',
                                'postcode',
                                'town'
                            ),
                            'children' => array(
                                'countryCode' => array(
                                    'properties' => array(
                                        'id'
                                    )
                                )
                            )
                        ),
                        'adDocuments' => array(
                            'properties' => array(
                                'id',
                                'version',
                                'filename',
                                'identifier',
                                'size'
                            )
                        )
                    )
                )
            )
        );

        $data = $context->makeRestCall(
            'ApplicationOperatingCentre',
            'GET',
            array('application' => $applicationId),
            $actionDataBundle
        );
        
        $newData = array();

        foreach ($data['Results'] as $row) {

            $newRow = $row;

            if (isset($row['operatingCentre']['address'])) {

                unset($row['operatingCentre']['address']['id']);
                unset($row['operatingCentre']['address']['version']);

                $newRow = array_merge($newRow, $row['operatingCentre']['address']);
            }

            unset($newRow['operatingCentre']);

            $newData[] = $newRow;
        }

        return $newData;
    }

    /**
     * Get data for table
     *
     * @param string $id
     */
    public function getFormTableData($id, $table)
    {
        if (is_null($this->tableData)) {
            $this->tableData=$this->getSummaryTableData($id, $this, '');
        }

        return $this->tableData;
    }

    /**
     * Get postcode service
     *
     * @return Common\Service\Postcode\Postcode
     */
    public function getPostcodeService()
    {
        return $this->getServiceLocator()->get('postcode');
    }

    /**
     * Get postcode traffic area validator
     *
     * @return Common\Form\Elements\Validator\OperatingCentreTrafficAreaValidator
     */
    public function getPostcodeTrafficAreaValidator()
    {
        return $this->getServiceLocator()->get('postcodeTrafficAreaValidator');
    }

    /**
     * Get postcode validators chain
     *
     * @return Zend\Validator\ValidatorChain
     */
    public function getPostcodeValidatorsChain($form)
    {
        return $form->getInputFilter()->get('address')->get('postcode')->getValidatorChain();
    }

    /**
     * Get licence service
     *
     * @return Common\Service\Licence\Licence
     */
    public function getLicenceService()
    {
        return $this->getServiceLocator()->get('licence');
    }
}
