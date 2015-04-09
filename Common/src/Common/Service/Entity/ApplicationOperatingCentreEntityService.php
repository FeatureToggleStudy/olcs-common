<?php

/**
 * Application Operating Centre Entity Service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Common\Service\Entity;

/**
 * Application Operating Centre Entity Service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ApplicationOperatingCentreEntityService extends AbstractOperatingCentreEntityService
{
    protected $entity = 'ApplicationOperatingCentre';

    protected $type = 'application';

    protected $dataBundle = array(
        'children' => array(
            'operatingCentre'
        )
    );

    public function getForApplication($id)
    {
        $query = array('application' => $id);

        $results = $this->getAll($query, $this->dataBundle);

        return $results['Results'];
    }

    public function getByApplicationAndOperatingCentre($applicationId, $operatingCentreId)
    {
        return $this->get(array('application' => $applicationId, 'operatingCentre' => $operatingCentreId))['Results'];
    }

    /**
     * Clear all interim markers against a set of application OCs
     */
    public function clearInterims(array $ids = [])
    {
        $data = array();

        foreach ($ids as $id) {

            $data[] = array(
                'id' => $id,
                'isInterim' => false,
                '_OPTIONS_' => ['force' => true]
            );
        }

        $data['_OPTIONS_']['multiple'] = true;

        $this->put($data);
    }

    /**
     * Get all OC for given application for inspection request listbox
     * 
     * @param int $applicationId
     * @return array
     */
    public function getAllForInspectionRequest($applicationId)
    {
        $query = [
            'application' => $applicationId,
            'action' => '!= D'
        ];
        $bundle = [
            'children' => [
                'operatingCentre' => [
                    'children' => [
                        'address'
                    ]
                ],
                'application'
            ]
        ];
        return $this->getAll($query, $bundle);
    }
}
