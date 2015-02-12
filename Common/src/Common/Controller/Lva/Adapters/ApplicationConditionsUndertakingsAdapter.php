<?php

/**
 * Application Conditions Undertakings Adapter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Controller\Lva\Adapters;

use Common\Service\Entity\ConditionUndertakingEntityService;

/**
 * Application Conditions Undertakings Adapter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationConditionsUndertakingsAdapter extends AbstractConditionsUndertakingsAdapter
{
    /**
     * Save the data
     *
     * @param array $data
     * @return int
     */
    public function save($data)
    {
        $data['addedVia'] = ConditionUndertakingEntityService::ADDED_VIA_APPLICATION;

        return parent::save($data);
    }

    /**
     * Get the table data
     *
     * @param int $id
     * @return array
     */
    public function getTableData($id)
    {
        return $this->getServiceLocator()->get('Entity\ConditionUndertaking')
            ->getForApplication($id);
    }

    /**
     * Process the data for saving
     *
     * @param array $data
     * @param int $id
     * @return array
     */
    public function processDataForSave($data, $id)
    {
        $data = parent::processDataForSave($data, $id);

        $data['fields']['application'] = $id;
        $data['fields']['isDraft'] = 'Y';
        $data['fields']['action'] = 'A';

        return $data;
    }

    /**
     * Get licence id from the given lva id
     *
     * @param int id
     * @return int
     */
    protected function getLicenceId($id)
    {
        return $this->getServiceLocator()->get('Entity\Application')->getLicenceIdForApplication($id);
    }

    /**
     * Get the LVA operating centre entity service
     *
     * @return \Common\Service\Entity\AbstractEntity
     */
    protected function getLvaOperatingCentreEntityService()
    {
        return $this->getServiceLocator()->get('Entity\ApplicationOperatingCentre');
    }
}