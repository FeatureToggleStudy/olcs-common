<?php

/**
 * Inspection Request Entity Service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Common\Service\Entity;

/**
 * Inspection Request Entity Service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InspectionRequestEntityService extends AbstractLvaEntityService
{
    const REPORT_TYPE_MAINTANANCE_REQUEST = 'insp_rep_t_maint';

    const RESULT_TYPE_NEW = 'insp_res_t_new';

    /**
     * Define entity for default behaviour
     *
     * @var string
     */
    protected $entity = 'InspectionRequest';

    /**
     * Get inspection request list
     * 
     * @param array $query
     * @param int $licenceId
     * @return array
     */
    public function getInspectionRequestList($query, $licenceId)
    {
        $listBundle = [
            'children' => [
                'reportType',
                'resultType',
                'application',
                'licence' => [
                    'criteria' => [
                        'id' => $licenceId,
                    ],
                    'required' => true,
                ],
            ]
        ];
        return $this->get($query, $listBundle);
    }

    /**
     * Get inspection request
     * 
     * @param int $id
     * @return array
     */
    public function getInspectionRequest($id)
    {
        $bundle = [
            'children' => [
                'reportType',
                'requestType',
                'resultType',
                'application',
                'licence',
                'operatingCentre'
            ]
        ];
        return $this->get($id, $bundle);
    }
}