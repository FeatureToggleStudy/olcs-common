<?php

namespace Common\Service;

use Common\Service\Data\Interfaces\DataService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ShortNotice
 * @package Olcs\Service
 */
class ShortNotice implements FactoryInterface
{
    /**
     * @var DataService
     */
    protected $noticePeriodService;

    /**
     * @param $data
     * @return bool|null
     */
    public function isShortNotice($data)
    {
        $busRules = $this->getNoticePeriodService()->fetchOne($data['busNoticePeriod']);

        $effectiveDateTime = \DateTime::createFromFormat('Y-m-d', $data['effectiveDate']);
        $receivedDateTime = \DateTime::createFromFormat('Y-m-d', $data['receivedDate']);

        if ($busRules['standardPeriod'] > 0) {
            $interval = new \DateInterval('P' . $busRules['standardPeriod'] . 'D');

            if ($receivedDateTime->add($interval) >= $effectiveDateTime) {
                return true;
            }
        }

        if ($busRules['cancellationPeriod'] > 0 && $data['variationNo'] > 0) {
            if (!isset($data['parent'])) {
                //if we don't have a parent record, the result is undefined.
                return null;
            }

            $lastDateTime = \DateTime::createFromFormat('Y-m-d', $data['parent']['effectiveDate']);
            $interval = new \DateInterval('P' . $busRules['cancellationPeriod'] . 'D');

            if ($lastDateTime->add($interval) >= $effectiveDateTime) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Common\Service\Data\Interfaces\DataService $noticePeriodService
     * @return $this
     */
    public function setNoticePeriodService($noticePeriodService)
    {
        $this->noticePeriodService = $noticePeriodService;
        return $this;
    }

    /**
     * @return \Common\Service\Data\Interfaces\DataService
     */
    public function getNoticePeriodService()
    {
        return $this->noticePeriodService;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $service = $serviceLocator->get('DataServiceManager')->get('Generic\Service\Data\BusNoticePeriod');
        $this->setNoticePeriodService($service);

        return $this;
    }
}
