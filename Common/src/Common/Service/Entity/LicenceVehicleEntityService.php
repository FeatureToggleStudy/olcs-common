<?php

/**
 * Licence Vehicle Entity Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Service\Entity;

/**
 * Licence Vehicle Entity Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceVehicleEntityService extends AbstractEntityService
{
    /**
     * Define entity for default behaviour
     *
     * @var string
     */
    protected $entity = 'LicenceVehicle';

    protected $vehicleBundle = array(
        'children' => array(
            'goodsDiscs',
            'vehicle'
        )
    );

    /**
     * Disc pending bundle
     */
    private $discBundle = array(
        'children' => array(
            'goodsDiscs'
        )
    );

    /**
     * Holds the bundle to retrieve VRM
     *
     * @var array
     */
    protected $vrmBundle = array(
        'children' => array(
            'vehicle'
        )
    );


    protected $currentVrmBundle = array(
        'children' => array(
            'vehicle'
        )
    );

    protected $vehiclePsvBundle = array(
        'children' => array(
            'vehicle' => array(
                'children' => array(
                    'psvType'
                )
            )
        )
    );

    protected $vehicleDataBundle = array(
        'children' => array(
            'goodsDiscs' => [

            ],
            'interimApplication',
            'vehicle' => [

            ]
        )
    );

    public function getVehicle($id)
    {
        return $this->get($id, $this->vehicleBundle);
    }

    public function getVehiclePsv($id)
    {
        return $this->get($id, $this->vehiclePsvBundle);
    }

    /**
     * Delete functionality just sets the removal date for licence vehicle
     *
     * @param int $id
     * @NOTE this has been migrated
     */
    public function delete($id)
    {
        $date = $this->getServiceLocator()->get('Helper\Date')->getDate();

        $this->forceUpdate($id, array('removalDate' => $date));
    }

    public function getVrm($id)
    {
        return $this->get($id, $this->vrmBundle)['vehicle']['vrm'];
    }

    public function getCurrentVrmsForLicence($licenceId)
    {
        $data = array(
            'licence' => $licenceId,
            'removalDate' => 'NULL'
        );

        $results = $this->getAll($data, $this->currentVrmBundle);

        $vrms = array();

        foreach ($results['Results'] as $row) {
            $vrms[] = $row['vehicle']['vrm'];
        }

        return $vrms;
    }

    public function getForApplicationValidation($licenceId, $applicationId)
    {
        $query = array(
            'licence' => $licenceId,
            'removalDate' => 'NULL',
            'application' => $applicationId
        );

        $results = $this->getAll($query);

        return $results['Results'];
    }

    /**
     * Fetch all results against a licence which are valid and NOT related
     * to the given application
     */
    public function getExistingForLicence($licenceId, $applicationId)
    {
        $query = [
            'licence' => $licenceId,
            'specifiedDate' => 'NOT NULL',
            'removalDate' => 'NULL',
            'interimApplication' => 'NULL',
            'application' => '!= ' . $applicationId
        ];

        $results = $this->getAll($query, $this->discBundle);

        return $results['Results'];
    }

    /**
     * Fetch all results related to the given application
     */
    public function getExistingForApplication($applicationId)
    {
        $query = [
            'removalDate' => 'NULL',
            'application' => $applicationId
        ];

        $results = $this->getAll($query, $this->discBundle);

        return $results['Results'];
    }

    public function removeVehicles(array $ids = array())
    {
        $removalDate = $this->getServiceLocator()->get('Helper\Date')->getDate(\DateTime::W3C);
        $data = [];

        foreach ($ids as $id) {
            $data[] = [
                'id' => $id,
                'removalDate' => $removalDate,
                '_OPTIONS_' => ['force' => true]
            ];
        }

        return $this->multiUpdate($data);
    }

    public function removeForApplication($applicationId)
    {
        $licenceVehicles = $this->getExistingForApplication($applicationId);
        $ids = [];
        foreach ($licenceVehicles as $lv) {
            $ids[] = $lv['id'];
        }
        return $this->removeVehicles($ids);
    }
}
