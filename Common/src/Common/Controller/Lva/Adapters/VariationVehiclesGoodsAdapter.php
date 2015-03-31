<?php

/**
 * Variation Vehicles Goods Adapter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Controller\Lva\Adapters;

use Common\Controller\Lva\Interfaces\VehicleGoodsAdapterInterface;

/**
 * Variation Vehicles Goods Adapter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationVehiclesGoodsAdapter extends AbstractAdapter implements VehicleGoodsAdapterInterface
{
    public function getFormData($id)
    {
        return [];
    }

    /**
     * Get vehicles data for the given resource
     *
     * Here we can just wrap the application version
     *
     * @param int $id
     * @return array
     */
    public function getVehiclesData($id)
    {
        return $this->getServiceLocator()->get('ApplicationVehiclesGoodsAdapter')->getVehiclesData($id);
    }

    /**
     * Get all relevant form filters
     *
     * Here we can just wrap the application version
     */
    public function getFilters($params)
    {
        return $this->getServiceLocator()->get('ApplicationVehiclesGoodsAdapter')->getFilters($params);
    }
}
