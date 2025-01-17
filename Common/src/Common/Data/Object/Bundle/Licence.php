<?php

namespace Common\Data\Object\Bundle;

use Common\Data\Object\Bundle;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class Licence
 * @package Common\Data\Object\Bundle
 */
class Licence extends Bundle
{
    /**
     * @TODO over time move these child bundles into separate classes and pull in via SL
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    protected function doInit(ServiceLocatorInterface $serviceLocator)
    {
        $appeal = new Bundle();
        $appeal->addChild('outcome')
                ->addChild('reason');

        $stays = new Bundle();
        $stays->addChild('stayType')
              ->addChild('outcome');

        $cases = new Bundle();
        $cases->addChild('appeal', $appeal)
              ->addChild('stays', $stays);

        $correspondenceCd = new Bundle();
        $correspondenceCd->addChild('address');

        $organisationPersons = new Bundle();
        $organisationPersons->addChild('person');

        $organisation = new Bundle();
        $organisation->addChild('organisationPersons', $organisationPersons)
                     ->addChild('tradingNames');

        $this->addChild('cases', $cases)
             ->addChild('status')
             ->addChild('goodsOrPsv')
             ->addChild('licenceType')
             ->addChild('trafficArea')
             ->addChild('organisation', $organisation)
             ->addChild('correspondenceCd', $correspondenceCd);
    }
}
