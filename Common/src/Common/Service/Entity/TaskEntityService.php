<?php

/**
 * Task Entity Service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Common\Service\Entity;

/**
 * Task Entity Service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class TaskEntityService extends AbstractEntityService
{
    /**
     * Define entity for default behaviour
     *
     * @var string
     */
    protected $entity = 'Task';

    const STATUS_OPEN = 'tst_open';
    const STATUS_CLOSED = 'tst_closed';
    const STATUS_ALL = 'tst_all';
    const CATEGORY_APPLICATION = 9;
    const SUBCATEGORY_FEE_DUE = 11;

    public function closeByQuery(array $query = array())
    {
        $query['isClosed'] = 'N';

        $results = $this->getAll($query);

        if (empty($results['Results'])) {
            return;
        }

        $updates = array();

        foreach ($results['Results'] as $task) {
            $updates[] = array(
                'id' => $task['id'],
                'isClosed' => 'Y',
                '_OPTIONS_' => array('force' => true)
            );
        }

        $updates['_OPTIONS_']['multiple'] = true;

        $this->put($updates);
    }
}
