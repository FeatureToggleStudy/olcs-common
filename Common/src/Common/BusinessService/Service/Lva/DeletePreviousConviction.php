<?php

/**
 * Delete Previous Conviction
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\BusinessService\Service\Lva;

use Common\BusinessService\BusinessServiceInterface;
use Common\BusinessService\Response;
use Common\BusinessService\BusinessServiceAwareInterface;
use Common\BusinessService\BusinessServiceAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Delete Previous Conviction
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DeletePreviousConviction implements
    BusinessServiceInterface,
    BusinessServiceAwareInterface,
    ServiceLocatorAwareInterface
{
    use BusinessServiceAwareTrait,
        ServiceLocatorAwareTrait;

    /**
     * Processes the data by passing it through a number of business rules and then persisting it
     *
     * @param array $params
     * @return Common\BusinessService\ResponseInterface
     */
    public function process(array $params)
    {
        $ids = $params['ids'];

        $service = $this->getServiceLocator()->get('Entity\PreviousConviction');

        foreach ($ids as $id) {
            $service->delete($id);
        }

        return new Response(Response::TYPE_SUCCESS);
    }
}
