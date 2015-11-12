<?php

/**
 * Abstract data service class
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Common\Service\Data;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Abstract data service class
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractDataService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    protected function handleQuery($dtoData)
    {
        $annotationBuilder = $this->getServiceLocator()->get('TransferAnnotationBuilder');
        $queryService = $this->getServiceLocator()->get('QueryService');

        $query = $annotationBuilder->createQuery($dtoData);
        return $queryService->send($query);
    }

    protected function handleCommand($dtoData)
    {
        $annotationBuilder = $this->getServiceLocator()->get('TransferAnnotationBuilder');
        $commandService = $this->getServiceLocator()->get('CommandService');

        $command = $annotationBuilder->createCommand($dtoData);
        return $commandService->send($command);
    }

    /*
     * Format result
     *
     * @note for backwards compatibility we need to return result with keys starting with uppercase letters
     */
    protected function formatResult($result)
    {
        return [
            'Results' => $result['results'],
            'Count' => $result['count']
        ];
    }
}
