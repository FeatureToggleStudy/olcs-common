<?php

/**
 * Goods Disc Entity Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Service\Entity;

/**
 * Goods Disc Entity Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GoodsDiscEntityService extends AbstractEntityService
{
    /**
     * Define entity for default behaviour
     *
     * @var string
     */
    protected $entity = 'GoodsDisc';

    /**
     * Create a blank disc for each given licence vehicle
     *
     * @param array $licenceVehicles
     */
    public function createForVehicles($licenceVehicles)
    {
        $defaults = [
            'ceasedDate' => null,
            'issuedDate' => null,
            'discNo' => null,
            'isCopy' => 'N'
        ];

        $data = [];
        foreach ($licenceVehicles as $licenceVehicle) {
            $data[] = array_merge(
                $defaults,
                ['licenceVehicle' => $licenceVehicle['id']]
            );
        }

        $this->multiCreate($data);
    }

    /**
     * Void any discs for each given ID
     *
     * @param array $ids
     */
    public function ceaseDiscs(array $ids = array())
    {
        $ceasedDate = $this->getServiceLocator()->get('Helper\Date')->getDate(\DateTime::W3C);
        $data = [];

        foreach ($ids as $id) {
            $data[] = [
                'id' => $id,
                'ceasedDate' => $ceasedDate,
                '_OPTIONS_' => ['force' => true]
            ];
        }

        $this->multiUpdate($data);
    }

    /**
     * Void any existing discs relating to the given application
     *
     * @param int $applicationId
     */
    public function voidExistingForApplication($applicationId)
    {
        $vehicles = $this->getServiceLocator()
            ->get('Entity\LicenceVehicle')
            ->getExistingForApplication($applicationId);

        $discsToCease = [];
        foreach ($vehicles as $vehicle) {
            foreach ($vehicle['goodsDiscs'] as $disc) {
                if ($disc['ceasedDate'] === null) {
                    $discsToCease[] = $disc['id'];
                }
            }
        }

        if (!empty($discsToCease)) {
            $this->ceaseDiscs($discsToCease);
        }
    }
}
