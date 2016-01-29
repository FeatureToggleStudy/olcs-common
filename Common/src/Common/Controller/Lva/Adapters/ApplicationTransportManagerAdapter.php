<?php

/**
 * Application Transport Manager Adapter
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Common\Controller\Lva\Adapters;

use Common\Service\Entity\LicenceEntityService;

/**
 * Application Transport Manager Adapter
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ApplicationTransportManagerAdapter extends AbstractTransportManagerAdapter
{
    protected $applicationData;

    /**
     * Load data into the table
     */
    public function getTableData($applicationId, $licenceId)
    {
        $query = $this->getServiceLocator()->get('TransferAnnotationBuilder')
            ->createQuery(\Dvsa\Olcs\Transfer\Query\Application\TransportManagers::create(['id' => $applicationId]));

        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->getServiceLocator()->get('QueryService')->send($query);
        $data = $response->getResult();
        $this->applicationData = $data;

        return $this->mapResultForTable($data['transportManagers']);
    }

    /**
     * Must this licence type have at least one Transport Manager
     *
     * @param int $applicationId Application ID
     *
     * @return bool
     */
    public function mustHaveAtLeastOneTm()
    {
        if (!isset($this->applicationData['licenceType']['id'])) {
            throw new \RuntimeException('Application data is not setup');
        }

        $mustHaveTypes = [
            LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL,
        ];

        return in_array($this->applicationData['licenceType']['id'], $mustHaveTypes);
    }

    /**
     * Delete Transport Managers
     *
     * @param array $ids Transport Manager Application IDs
     *
     * @return bool whether successful
     */
    public function delete(array $ids, $applicationId)
    {
        $command = $this->getServiceLocator()->get('TransferAnnotationBuilder')
            ->createCommand(\Dvsa\Olcs\Transfer\Command\TransportManagerApplication\Delete::create(['ids' => $ids]));

        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->getServiceLocator()->get('CommandService')->send($command);

        return $response->isOk();
    }
}
