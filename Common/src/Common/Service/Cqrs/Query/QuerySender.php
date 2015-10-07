<?php

namespace Common\Service\Cqrs\Query;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;

/**
 * Query Sender
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class QuerySender implements FactoryInterface
{
    /**
     * @var TransferAnnotationBuilder
     */
    private $annotationBuilder;

    /**
     * @var QueryService
     */
    private $queryService;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->queryService = $serviceLocator->get('QueryService');
        $this->annotationBuilder = $serviceLocator->get('TransferAnnotationBuilder');

        return $this;
    }

    /**
     * @param QueryInterface $query
     * @return \Common\Service\Cqrs\Response
     */
    public function send(QueryInterface $query)
    {
        $query = $this->annotationBuilder->createQuery($query);
        return $this->queryService->send($query);
    }
}