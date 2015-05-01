<?php

/**
 * Generic Licence Section Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Controller\Traits;

/**
 * Generic Licence Section Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait GenericLicenceSection
{
    /**
     * Licence type
     *
     * @var string
     */
    private $licenceType = null;

    /**
     * Cache licence data requests
     *
     * @var array
     */
    private $licenceData = array();

    /**
     * Check if is psv
     *
     * @var boolean
     */
    protected $isPsv = null;

    /**
     * Licence data service
     *
     * @var string
     */
    protected $licenceDataService = 'Licence';

    /**
     * Holds the licenceDataBundle
     *
     * @var array
     */
    protected $licenceDataBundle = array(
        'children' => array(
            'goodsOrPsv' => array(),
            'licenceType' => array(),
            'organisation' => array(
                'children' => array(
                    'type' => array()
                )
            )
        )
    );

    protected function getLicenceDataService()
    {
        return $this->licenceDataService;
    }

    protected function getLicenceDataBundle()
    {
        return $this->licenceDataBundle;
    }

    /**
     * Get the licence type
     *
     * @return string
     */
    protected function getLicenceType()
    {
        if (empty($this->licenceType)) {
            $licenceData = $this->getLicenceData();

            if (isset($licenceData['licenceType']['id'])) {
                $this->licenceType = $licenceData['licenceType']['id'];
            }
        }

        return $this->licenceType;
    }

    /**
     * Check if application is psv
     *
     * GetAccessKeys "should" always be called first so psv should be set
     *
     * @return boolean
     */
    protected function isPsv()
    {
        return $this->isPsv;
    }

    /**
     * Get the licence data
     *
     * @return array
     */
    protected function doGetLicenceData()
    {
        if (empty($this->licenceData)) {

            $results = $this->makeRestCall(
                $this->getLicenceDataService(),
                'GET',
                array('id' => $this->getIdentifier()),
                $this->getLicenceDataBundle()
            );

            $this->licenceData = $results;
        }

        return $this->licenceData;
    }
}
