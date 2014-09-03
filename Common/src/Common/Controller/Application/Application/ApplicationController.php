<?php

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Controller\Application\Application;

use Common\Controller\AbstractJourneyController;
use Common\Controller\Traits\GenericLicenceSection;

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationController extends AbstractJourneyController
{
    use GenericLicenceSection;

    /**
     * Journey completion statuses
     */
    const COMPLETION_STATUS_NOT_STARTED = 0;
    const COMPLETION_STATUS_INCOMPLETE = 1;
    const COMPLETION_STATUS_COMPLETE = 2;

    /**
     * Goods or PSV keys
     */
    const LICENCE_CATEGORY_GOODS_VEHICLE = 'lcat_gv';
    const LICENCE_CATEGORY_PSV = 'lcat_psv';

    /**
     * Licence types keys
     */
    const LICENCE_TYPE_RESTRICTED = 'ltyp_r';
    const LICENCE_TYPE_STANDARD_INTERNATIONAL = 'ltyp_si';
    const LICENCE_TYPE_STANDARD_NATIONAL = 'ltyp_sn';
    const LICENCE_TYPE_SPECIAL_RESTRICTED = 'ltyp_sr';

    /**
     * Organisation type keys
     */
    const ORG_TYPE_PARTNERSHIP = 'org_t_p';
    const ORG_TYPE_OTHER = 'org_t_pa';
    const ORG_TYPE_REGISTERED_COMPANY = 'org_t_rc';
    const ORG_TYPE_LLP = 'org_t_llp';
    const ORG_TYPE_SOLE_TRADER = 'org_t_st';

    /**
     * Holds the licenceDataBundle
     *
     * @var array
     */
    public static $applicationLicenceDataBundle = array(
        'children' => array(
            'licence' => array(
                'properties' => array(
                    'id',
                    'version',
                    'niFlag'
                ),
                'children' => array(
                    'goodsOrPsv' => array(
                        'properties' => array(
                            'id'
                        )
                    ),
                    'licenceType' => array(
                        'properties' => array(
                            'id'
                        )
                    ),
                    'organisation' => array(
                        'children' => array(
                            'type' => array(
                                'properties' => array(
                                    'id'
                                )
                            )
                        )
                    )
                )
            )
        )
    );

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Application';

    /**
     * Get licence data service (Used to extend trait)
     *
     * @return string
     */
    protected function getLicenceDataService()
    {
        return 'Application';
    }

    /**
     * Get licence data bundle
     *
     * @return array
     */
    protected function getLicenceDataBundle()
    {
        return static::$applicationLicenceDataBundle;
    }

    /**
     * Redirect to the first section
     *
     * @return Response
     */
    public function indexAction()
    {
        $completion = $this->getSectionCompletion();

        if (isset($completion['lastSection'])) {
            return $this->goToSection($completion['lastSection']);
        }

        return $this->goToFirstSection();
    }

    /**
     * Check if the vehicle safety section is enabled
     *
     * @return boolean
     */
    public function isVehicleSafetyEnabled()
    {
        if (!$this->isPsv()) {
            return true;
        }

        return ($this->getSectionStatus('OperatingCentres') == 'complete');
    }

    /**
     * Save the last section
     *
     * @param ViewModel $view
     * @return ViewModel
     */
    protected function preRender($view)
    {
        $this->saveLastSection();

        return parent::preRender($view);
    }

    /**
     * Save the last section
     */
    protected function saveLastSection()
    {
        // We use the full section completion as it gets cached and will be used again
        $completion = $this->getSectionCompletion();

        $foreignKey = $this->getJourneyConfig()['completionStatusJourneyIdColumn'];

        $data = array(
            'id' => $completion['id'],
            'version' => $completion['version'],
            'lastSection' => $this->getJourneyName() . '/' . $this->getSectionName() . '/' . $this->getSubSectionName()
        );

        $this->makeRestCall('ApplicationCompletion', 'PUT', $data);

        $completion['version']++;

        $this->setSectionCompletion($completion);
    }

    /**
     * Return an array of access keys
     *
     * @param boolean $force
     * @return array
     */
    protected function getAccessKeys($force = false)
    {
        if (empty($this->accessKeys) || $force) {

            $this->accessKeys = array();

            $licence = $this->getLicenceData();

            if (empty($licence)) {
                return parent::getAccessKeys($force);
            }

            $goodsOrPsv = $this->getGoodsOrPsvFromLicenceData($licence);
            $type = $this->getLicenceTypeFromLicenceData($licence);

            if ($goodsOrPsv == 'psv') {
                $this->isPsv = true;
                $this->accessKeys[] = 'psv';
            } else {
                $this->isPsv = false;
                $this->accessKeys[] = 'goods';
            }

            $this->accessKeys[] = trim($goodsOrPsv . '-' . $type, '-');

            if (isset($licence['niFlag']) && !is_null($licence['niFlag']) && $licence['niFlag'] !== '') {
                $this->accessKeys[] = ($licence['niFlag'] == 1 ? 'ni' : 'gb');
            }

            $sectionCompletion = $this->getSectionCompletion();

            if (isset($sectionCompletion['sectionPaymentSubmissionStatus'])
                && $sectionCompletion['sectionPaymentSubmissionStatus'] == 2) {

                $this->accessKeys[] = 'paid';
            } else {
                $this->accessKeys[] = 'unpaid';
            }

            $this->accessKeys[] = $licence['organisation']['type']['id'];
        }

        $config = $this->getApplicationConfig();

        if (isset($config['access_keys'])) {
            $this->accessKeys = array_merge($this->accessKeys, $config['access_keys']);
        }

        return $this->accessKeys;
    }

    /**
     * Get Goods Or Psv From Licence Data
     *
     * @param array $licence
     * @return string|null
     */
    private function getGoodsOrPsvFromLicenceData($licence)
    {
        if (!isset($licence['goodsOrPsv']['id'])) {
            return null;
        }

        if ($licence['goodsOrPsv']['id'] == self::LICENCE_CATEGORY_PSV) {
            return 'psv';
        }

        return 'goods';
    }

    /**
     * Get Licence Type From Licence Data
     *
     * @param array $licence
     * @return string|null
     */
    private function getLicenceTypeFromLicenceData($licence)
    {
        if (!isset($licence['licenceType']['id'])) {
            return null;
        }

        if (
            in_array(
                $licence['licenceType']['id'],
                [self::LICENCE_TYPE_STANDARD_INTERNATIONAL, self::LICENCE_TYPE_STANDARD_NATIONAL]
            )
        ) {
            return 'standard';
        }

        switch ($licence['licenceType']['id']) {
            case self::LICENCE_TYPE_STANDARD_INTERNATIONAL:
            case self::LICENCE_TYPE_STANDARD_NATIONAL:
                return 'standard';
            case self::LICENCE_TYPE_RESTRICTED:
                return 'restricted';
            case self::LICENCE_TYPE_SPECIAL_RESTRICTED:
                return 'special-restricted';
        }

        return null;
    }

    /**
     * Get licence id
     *
     * @return int
     */
    protected function getLicenceId()
    {
        $licence = $this->getLicenceData();

        return $licence['id'];
    }

    /**
     * Upload a file
     *
     * @param array $file
     * @param array $data
     */
    protected function uploadFile($file, $data)
    {
        $uploader = $this->getUploader();
        $uploader->setFile($file);
        $uploader->upload();

        $file = $uploader->getFile();

        $fileData = $file->toArray();

        $licence = $this->getLicenceData();

        $fileData['filename'] = $fileData['name'];
        $fileData['application'] = $this->getIdentifier();
        $fileData['licence'] = $licence['id'];

        unset($fileData['path']);
        unset($fileData['type']);
        unset($fileData['name']);

        $this->makeRestCall('Document', 'POST', array_merge($fileData, $data));
    }

    /**
     * Get the licence data
     *
     * @return array
     */
    protected function getLicenceData()
    {
        $results = $this->doGetLicenceData();

        return $results['licence'];
    }
}
