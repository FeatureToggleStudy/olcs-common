<?php

/**
 * Fee Entity Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Service\Entity;

/**
 * Fee Entity Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FeeEntityService extends AbstractLvaEntityService
{
    /**
     * Define entity for default behaviour
     *
     * @var string
     */
    protected $entity = 'Fee';

    const STATUS_OUTSTANDING = 'lfs_ot';
    const STATUS_PAID = 'Paid';
    const STATUS_WAIVE_RECOMMENDED = 'lfs_wr';
    const STATUS_WAIVED = 'lfs_w';
    const STATUS_CANCELLED = 'lfs_cn';

    protected $applicationIdBundle = array(
        'properties' => array(),
        'children' => array(
            'application' => array(
                'properties' => array(
                    'id',
                    'isVariation'
                ),
                'children' => array(
                    'status' => array(
                        'properties' => array('id')
                    )
                )
            )
        )
    );

    public function getApplication($id)
    {
        $data = $this->get($id, $this->applicationIdBundle);

        return isset($data['application']) ? $data['application'] : null;
    }

    public function getOutstandingFeesForApplication($applicationId)
    {
        $query = array(
            'application' => $applicationId,
            'feeStatus' => array(
                self::STATUS_OUTSTANDING,
                self::STATUS_WAIVE_RECOMMENDED
            )
        );

        $data = $this->getAll($query, array('properties' => array('id')));

        return $data['Results'];
    }

    public function cancelForLicence($licenceId)
    {
        $query = array(
            'licence' => $licenceId,
            'feeStatus' => array(
                self::STATUS_OUTSTANDING,
                self::STATUS_WAIVE_RECOMMENDED
            )
        );

        $results = $this->getAll($query, array('properties' => array('id')));

        if (empty($results['Results'])) {
            return;
        }

        $updates = array();

        foreach ($results['Results'] as $fee) {
            $updates[] = array(
                'id' => $fee['id'],
                'feeStatus' => self::STATUS_CANCELLED,
                '_OPTIONS_' => array('force' => true)
            );
        }

        $updates['_OPTIONS_']['multiple'] = true;

        $this->put($updates);
    }
}
