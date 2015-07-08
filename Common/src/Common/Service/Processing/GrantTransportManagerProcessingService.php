<?php

/**
 * Grant Transport Manager Processing Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Service\Processing;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Common\Service\Entity\LicenceEntityService;

/**
 * Grant Transport Manager Processing Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @todo migrate me
 */
class GrantTransportManagerProcessingService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function grant($id, $licenceId)
    {
        $licence = $this->getServiceLocator()->get('Entity\Licence')->getOverview($licenceId);
        if ($licence['licenceType']['id'] == LicenceEntityService::LICENCE_TYPE_RESTRICTED) {
            $this->deleteAllTransportManagersForLicence($licenceId);
        } else {
            $entityService = $this->getServiceLocator()->get('Entity\TransportManagerApplication');

            $results = $entityService->getGrantDataForApplication($id);

            foreach ($results as $row) {

                switch ($row['action']) {
                    case 'A':
                    case 'U':
                        $this->createTransportManager($row, $licenceId);
                        break;
                    case 'D':
                        $this->deleteTransportManager($row, $licenceId);
                        break;
                }
            }
        }
    }

    protected function createTransportManager($data, $licenceId)
    {
        if ($this->licenceHasTransportManager($data['transportManager']['id'], $licenceId)) {
            $this->deleteTransportManager($data, $licenceId);
        }

        $data = $this->getServiceLocator()->get('Helper\Data')->replaceIds($data);

        $otherLicences = $data['otherLicences'];
        unset($data['otherLicences']);

        unset($data['id']);
        unset($data['action']);
        unset($data['version']);
        unset($data['application']);
        unset($data['tmApplicationStatus']);

        $data['licence'] = $licenceId;

        foreach ($data['operatingCentres'] as $key => $row) {
            $data['operatingCentres'][$key] = $row['id'];
        }

        $entityService = $this->getServiceLocator()->get('Entity\TransportManagerLicence');
        $id = $entityService->save($data);

        foreach ($otherLicences as $otherLicence) {
            $this->createOtherLicence($otherLicence, $id);
        }
    }

    protected function createOtherLicence($otherLicence, $transportManagerLicenceId)
    {
        $data = $this->getServiceLocator()->get('Helper\Data')->replaceIds($otherLicence);

        unset($data['id']);
        unset($data['version']);
        unset($data['transportManagerApplication']);

        $data['transportManagerLicence'] = $transportManagerLicenceId;

        $this->getServiceLocator()->get('Entity\OtherLicence')->save($data);
    }


    protected function licenceHasTransportManager($transportManagerId, $licenceId)
    {
        $results = $this->getServiceLocator()->get('Entity\TransportManagerLicence')
            ->getByTransportManagerAndLicence($transportManagerId, $licenceId);

        return !empty($results);
    }

    protected function deleteTransportManager($data, $licenceId)
    {
        $this->getServiceLocator()->get('Entity\TransportManagerLicence')
            ->deleteList(
                [
                    'transportManager' => $data['transportManager']['id'],
                    'licence' => $licenceId
                ]
            );
    }

    protected function deleteAllTransportManagersForLicence($licenceId)
    {
        $this->getServiceLocator()->get('Entity\TransportManagerLicence')
            ->deleteList(
                [
                    'licence' => $licenceId
                ]
            );
    }
}
