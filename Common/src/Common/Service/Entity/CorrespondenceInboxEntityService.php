<?php

/**
 * Correspondence Inbox (email) Entity Service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Common\Service\Entity;

/**
 * Correspondence Inbox (email) Entity Service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class CorrespondenceInboxEntityService extends AbstractLvaEntityService
{
    /**
     * Define entity for default behaviour
     *
     * @var string
     */
    protected $entity = 'CorrespondenceInbox';

    /**
     * Bundle for displaying the correspondence in a table.
     *
     * @var array
     */
    protected $completeBundle = array(
        'children' => array(
            'document',
            'licence'
        )
    );

    /**
     * Get the full correspondence record by its primary identifier.
     *
     * @param $id The correspondence id
     *
     * @return array The correspondence record.
     */
    public function getById($id)
    {
        return parent::get($id, $this->completeBundle);
    }

    /**
     * Get all correspondence using an array of licence identifiers
     * where the licence organisation matches to passed organisation
     * identifier.
     *
     * @param int $organisationId
     *
     * @return array
     */
    public function getCorrespondenceByOrganisation($organisationId = null)
    {
        $licences = $this->getServiceLocator()
            ->get('Entity\Licence')
            ->getAll(
                array(
                    'organisation' => $organisationId
                )
            );

        $ids = array_map(
            function ($licence) {
                return $licence['id'];
            },
            $licences['Results']
        );

        return $this->getAll(array('licence' => $ids), $this->completeBundle);
    }
}
