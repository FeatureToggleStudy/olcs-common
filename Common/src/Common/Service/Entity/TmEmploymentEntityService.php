<?php

/**
 * Transport Manager Employment Entity Service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Common\Service\Entity;

/**
 * Transport Manager Employment Entity Service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmEmploymentEntityService extends AbstractEntityService
{
    /**
     * Define entity for default behaviour
     *
     * @var string
     */
    protected $entity = 'TmEmployment';

    protected $dataBundle = [
        'children' => [
            'transportManager',
            'contactDetails' => [
                'children' => [
                    'address'
                ]
            ]
        ]
    ];

    /**
     * Get all Transport Manager employment for Transport Manager
     *
     * @param int $id
     * @return array
     */
    public function getAllEmploymentsForTm($id)
    {
        return $this->get(['transportManager' => $id], $this->dataBundle);
    }

    /**
     * Get Transport Manager other employment
     *
     * @param int $id
     * @return array
     */
    public function getEmployment($id)
    {
        return $this->get($id, $this->dataBundle);
    }
}
