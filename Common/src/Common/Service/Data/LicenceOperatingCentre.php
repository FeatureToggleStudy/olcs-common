<?php

namespace Common\Service\Data;

use Zend\ServiceManager\FactoryInterface;

/**
 * Class LicenceOperatingCentre
 *
 * @package Olcs\Service\Data
 */
class LicenceOperatingCentre extends AbstractDataService implements FactoryInterface, ListDataInterface
{
    use LicenceServiceTrait;

    const OUTPUT_TYPE_FULL = 1;
    const OUTPUT_TYPE_PARTIAL = 2;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $outputType = self::OUTPUT_TYPE_FULL;

    /**
     * Fetch list options
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     */
    public function fetchListOptions($context = null, $useGroups = false)
    {
        $id = $this->getId();

        if (is_null($this->getData($id))) {
            $data = array();
            $rawData =  $this->getLicenceService()->fetchOperatingCentreData($this->getId());

            if (is_array($rawData['operatingCentres'])) {
                $outputType = $this->getOutputType();

                $fields = ($outputType == self::OUTPUT_TYPE_PARTIAL)
                    ? ['addressLine1', 'town']
                    : ['addressLine1', 'addressLine2', 'addressLine3', 'addressLine4', 'town', 'postcode'];

                foreach ($rawData['operatingCentres'] as $licenceOperatingCentre) {
                    $addressString = '';

                    foreach ($fields as $field) {
                        $addressString .= !empty($licenceOperatingCentre['operatingCentre']['address'][$field]) ?
                            $licenceOperatingCentre['operatingCentre']['address'][$field] . ', ' : '';
                    }

                    $data[$licenceOperatingCentre['operatingCentre']['id']] = substr($addressString, 0, -2);
                }
            }

            $this->setData($id, $data);
        }

        return $this->getData($id);
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getLicenceService()->getId();
    }

    /**
     * Get output type
     *
     * @return int
     */
    public function getOutputType()
    {
        return $this->outputType;
    }

    /**
     * Set output type
     *
     * @param int $outputType Output type
     *
     * @return $this
     */
    public function setOutputType($outputType)
    {
        $this->outputType = $outputType;

        return $this;
    }
}
