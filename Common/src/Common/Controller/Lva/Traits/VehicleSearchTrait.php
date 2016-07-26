<?php

/**
 * Vehicle Search Trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Common\Controller\Lva\Traits;

use Common\Service\Table\TableBuilder;

/**
 * Vehicle Search Trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
trait VehicleSearchTrait
{
    /**
     * Show removed vehicles action
     *
     * @return mixed
     */
    public function showRemovedVehiclesAction()
    {
        return $this->redirect()->toRouteAjax(
            null, ['action' => 'index'], ['query' => ['includeRemoved' => 1]], true
        );
    }

    /**
     * Hide removed vehicles action
     *
     * @return mixed
     */
    public function hideRemovedVehiclesAction()
    {
        return $this->redirect()->toRouteAjax(
            null, ['action' => 'index'], ['query' => []], true
        );
    }

    /**
     * Get vehicle search form
     *
     * @param array $headerData
     * @return \Zend\Form\FormInterface|null
     */
    protected function getVehcileSearchForm($headerData)
    {
        $searchForm = null;
        if (($headerData['allVehicleCount'] > self::SEARCH_VEHICLES_COUNT) && ($this->lva !== 'application')) {
            /** @var \Zend\Form\FormInterface $searchForm */
            $searchForm = $this->getServiceLocator()->get('FormServiceManager')
                ->get('lva-vehicles-search')
                ->getForm();

            $query = (array)$this->getRequest()->getQuery();

            if (!isset($query['limit']) || !is_numeric($query['limit'])) {
                $query['limit'] = 10;
            }
            if (isset($query['vehicleSearch']['clearSearch'])) {
                unset($query['vehicleSearch']);
            }
            $searchForm->setData($query);
            if (isset($query['vehicleSearch']['filter']) && !$searchForm->isValid()) {
                $translator = $this->getServiceLocator()->get('Helper\Translation');
                $message = [
                    'vehicleSearch' => [
                        'vrm' => [$translator->translate('vehicle-table.search.message')]
                    ]
                ];
                $searchForm->setMessages($message);
            }
        }
        return $searchForm;
    }

    /**
     * Remove unused parameters from query
     *
     * @param array $query
     * @return array
     */
    protected function removeUnusedParametersFromQuery($query)
    {
        if ((isset($query['vehicleSearch']['filter']) && !$query['vehicleSearch']['vrm']) ||
            isset($query['vehicleSearch']['clearSearch'])) {
            $query['vehicleSearch'] = null;
            unset($query['vehicleSearch']);
        }
        if (isset($query['includeRemoved']) && !$query['includeRemoved']) {
            unset($query['includeRemoved']);
        }
        return $query;
    }

    /**
     * Add removed vehicles actions
     *
     * @param array $filters
     * @param TableBuilder $table
     */
    protected function addRemovedVehiclesActions($filters, TableBuilder $table)
    {
        if (isset($filters['includeRemoved']) && $filters['includeRemoved'] == '1') {
            $table->addAction(
                'hide-removed-vehicles',
                [
                    'label' => 'label-hide-removed-vehciles',
                    'class' => 'secondary',
                ]
            );
        } else {
            $table->addAction(
                'show-removed-vehicles',
                [
                    'label' => 'label-show-removed-vehciles',
                    'class' => 'secondary',
                ]
            );
        }
        if (count($table->getRows()) === 0) {
            $table->setSetting('collapseAt', null);
        }
    }
}
