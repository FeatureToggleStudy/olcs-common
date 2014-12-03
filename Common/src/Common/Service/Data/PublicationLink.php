<?php

/**
 * Publication Link service
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Exception\ResourceNotFoundException;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Common\Util\RestClient;
use Common\Data\Object\Publication;

/**
 * Publication Link service
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PublicationLink extends AbstractData implements ServiceLocatorAwareInterface
{
    const NEW_PUBLICATION_STATUS = 'pub_s_new';

    use ServiceLocatorAwareTrait;

    /**
     * @var string
     */
    protected $serviceName = 'PublicationLink';

    /**
     * @return ArrayObject
     */
    public function createEmpty()
    {
        return new Publication();
    }

    /**
     * @param \Common\Data\Object\Publication $publication
     * @return mixed
     */
    public function save(Publication $publication)
    {
        return $this->getServiceLocator()->get('Helper\Rest')->makeRestCall(
            'PublicationLink',
            'POST',
            $publication->getArrayCopy()
        );
    }

    /**
     * @param \Common\Data\Object\Publication $publication
     * @param string $service
     * @return mixed
     */
    public function createPublicationLink(Publication $publication, $service)
    {
        $publication = $this->getServiceLocator()->get($service)->filter($publication);
        return $this->save($publication);
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function fetchList($params = [], $bundle = null)
    {
        $params['bundle'] = json_encode(empty($bundle) ? $this->getBundle() : $bundle);
        return $this->getRestClient()->get($params);
    }

    /**
     * @param int $id
     * @param bool $checkStatus
     * @return array
     * @throws ResourceNotFoundException
     * @throws DataServiceException
     */
    public function delete($id, $checkStatus = true)
    {
        $params = ['id' => $id];

        if ($checkStatus) {
            $existing = $this->fetchList($params);

            if (empty($existing)) {
                throw new ResourceNotFoundException('Publication record could not be found');
            }

            if ($existing['publication']['pubStatus']['id'] != self::NEW_PUBLICATION_STATUS) {
                throw new DataServiceException('Only unpublished entries may be deleted');
            };
        }

        return parent::delete($id);
    }

    /**
     * @return array
     */
    private function getBundle()
    {
        return [
            'properties' => 'ALL',
            'children' => [
                'publication' => [
                    'properties' => 'ALL',
                    'children' => [
                        'pubStatus' => [
                            'properties' => 'ALL'
                        ],
                        'trafficArea' => [
                            'properties' => 'ALL'
                        ]
                    ]
                ],
                'publicationSection' => [
                    'properties' => 'ALL'
                ]
            ]
        ];
    }
}
