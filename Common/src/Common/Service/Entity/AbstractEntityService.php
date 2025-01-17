<?php

/**
 * Abstract Entity Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Service\Entity;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Common\Exception\ConfigurationException;

/**
 * Abstract Entity Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractEntityService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Define entity for default behaviour
     *
     * @var string
     */
    protected $entity;

    protected $cache = [];

    protected $listBundle;

    public function getById($id)
    {
        return $this->get($id);
    }

    public function getList($query)
    {
        return $this->get($query, $this->listBundle);
    }

    /**
     * Get the defined entity name
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Save the entity
     *
     * @param array $data
     */
    public function save($data)
    {
        if (($entity = $this->getEntity()) === null) {
            throw new ConfigurationException('Entity is not defined');
        }

        if (isset($data['id']) && !empty($data['id'])) {
            return $this->put($data);
        }

        return $this->post($data);
    }

    public function update($id, $data)
    {
        $data['id'] = $id;

        return $this->put($data);
    }

    public function forceUpdate($id, $data)
    {
        $data['_OPTIONS_']['force'] = true;

        return $this->update($id, $data);
    }

    public function multiUpdate($data)
    {
        $data['_OPTIONS_']['multiple'] = true;

        return $this->put($data);
    }

    public function multiCreate($data)
    {
        $data['_OPTIONS_']['multiple'] = true;

        return $this->post($data);
    }

    /**
     * Delete the entity by its ID
     *
     * @param int $id
     */
    public function delete($id)
    {
        return $this->deleteList(['id' => $id]);
    }

    /**
     * Delete the entity by arbitrary params
     *
     * @param array $data
     */
    public function deleteList($data)
    {
        if (($entity = $this->getEntity()) === null) {
            throw new ConfigurationException('Entity is not defined');
        }

        $this->clearCache();

        return $this->getServiceLocator()->get('Helper\Rest')->makeRestCall($entity, 'DELETE', $data);
    }

    /**
     * Delete multiple entity by its IDs
     *
     * @param array $data
     */
    public function deleteListByIds($data)
    {
        if (($entity = $this->getEntity()) === null) {
            throw new ConfigurationException('Entity is not defined');
        }

        if (array_key_exists('id', $data) === false) {
            return $this->deleteList($data);
        } else {
            $this->clearCache();
            foreach ($data['id'] as $id) {
                $this->getServiceLocator()->get('Helper\Rest')->makeRestCall($entity, 'DELETE', ['id' => $id]);
            }
        }
    }

    /**
     * Put data
     *
     * @param array $data
     */
    protected function put(array $data)
    {
        return $this->write('PUT', $data);
    }

    /**
     * POST data
     *
     * @param array $data
     */
    protected function post(array $data)
    {
        return $this->write('POST', $data);
    }

    /**
     * Write with a PUT or a POST
     *
     * @param array $data
     */
    protected function write($method, array $data)
    {
        $this->clearCache();

        return $this->getServiceLocator()->get('Helper\Rest')->makeRestCall($this->entity, $method, $data);
    }

    /**
     * Wrap the rest client
     *
     * @param mixed $id
     * @param array $bundle
     * @return array
     */
    protected function get($id, $bundle = null)
    {
        return $this->getServiceLocator()->get('Helper\Rest')->makeRestCall($this->entity, 'GET', $id, $bundle);
    }

    /**
     * Wrap the rest client to fetch all records, not just the default backend limit (currently 10)
     *
     * @param mixed $query
     * @param array $bundle
     * @param mixed $limit
     * @return array
     */
    protected function getAll($query, $bundle = null, $limit = 'all')
    {
        if (!is_array($query)) {
            // assume id => "foo" shorthand
            $query = array(
                'id' => $query
            );
        }
        $query['limit'] = $limit;

        return $this->getServiceLocator()->get('Helper\Rest')->makeRestCall($this->entity, 'GET', $query, $bundle);
    }

    protected function setCache($reference, $id, $data)
    {
        $this->cache[$reference][$id] = $data;
    }

    protected function getCache($reference, $id)
    {
        return $this->cache[$reference][$id];
    }

    protected function isCached($reference, $id)
    {
        return isset($this->cache[$reference][$id]);
    }

    protected function clearCache()
    {
        $this->cache = [];
    }
}
