<?php

/**
 * User Entity Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Service\Entity;

/**
 * User Entity Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UserEntityService extends AbstractEntityService
{
    protected $entity = 'User';

    protected $currentUserBundle = [
        'children' => [
            'team'
        ]
    ];

    protected $userDetailsBundle = [
        'children' => [
            'localAuthority',
            'contactDetails' => [
                'children' => [
                    'person',
                    'contactType'
                ]
            ],
            'transportManager',
            'team',
            'roles',
            'organisationUsers' => [
                'children' => ['organisation'],
            ],
        ]
    ];

    protected $tmaBundle = [
        'children' => [
            'transportManager' => [
                'children' => [
                    'tmApplications' => [
                        'children' => [
                            'tmApplicationStatus',
                            'application' => [
                                'children' => [
                                    'licence'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    /**
     * Bundle for standard list
     *
     * @var array
     */
    protected $listBundle = [
        'children' => [
            'contactDetails' => [
                'children' => [
                    'person'
                ]
            ],
            'transportManager',
            'team',
            'roles'
        ]
    ];

    /**
     * Get the current logged in user ID
     *
     * @return int
     */
    public function getCurrentUserId()
    {
        return $this->getServiceLocator()->get('ZfcRbac\Service\AuthorizationService')->getIdentity()->getId();
    }

    /**
     * Get the current user
     */
    public function getCurrentUser()
    {
        return $this->get($this->getCurrentUserId(), $this->currentUserBundle);
    }

    public function getUserDetails($id)
    {
        return $this->get($id, $this->userDetailsBundle);
    }

    /**
     * Get Transport Manager Applications for a User
     *
     * @param int $userId User ID
     * @return array Entity data tmApplications
     */
    public function getTransportManagerApplications($userId)
    {
        $query = [
            'id' => $userId,
        ];

        $results = $this->getAll($query, $this->tmaBundle);

        return (isset($results['transportManager']['tmApplications'])) ?
            $results['transportManager']['tmApplications'] :
            [];
    }
}
