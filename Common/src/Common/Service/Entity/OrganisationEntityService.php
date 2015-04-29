<?php

/**
 * Organisation Entity Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Service\Entity;

use Common\Service\Entity\LicenceEntityService as Licence;

/**
 * Organisation Entity Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OrganisationEntityService extends AbstractEntityService
{
    /**
     * Organisation type keys
     */
    const ORG_TYPE_PARTNERSHIP = 'org_t_p';
    const ORG_TYPE_OTHER = 'org_t_pa';
    const ORG_TYPE_REGISTERED_COMPANY = 'org_t_rc';
    const ORG_TYPE_LLP = 'org_t_llp';
    const ORG_TYPE_SOLE_TRADER = 'org_t_st';

    /**
     * Define entity for default behaviour
     *
     * @var string
     */
    protected $entity = 'Organisation';

    /**
     * Holds the organisation bundle
     *
     * @var array
     */
    private $organisationFromUserBundle = array(
        'children' => array(
            'organisation'
        )
    );

    /**
     * Holds the organisation type bundle
     *
     * @var array
     */
    private $typeBundle = array(
        'children' => array(
            'type'
        )
    );

    /**
     * Bundle to retrieve data to update completion status
     *
     * @var array
     */
    private $businessDetailsBundle = array(
        'children' => array(
            'contactDetails' => array(
                'children' => array(
                    'address',
                    'contactType'
                )
            ),
            'type',
            'tradingNames'
        )
    );

    /**
     * Holds the applications bundle
     *
     * @var array
     */
    private $applicationsBundle = array(
        'children' => array(
            'licences' => array(
                'children' => array(
                    'applications' => array(
                        'children' => array(
                            'status',
                            'licenceType',
                            'goodsOrPsv',
                        )
                    ),
                    'licenceType',
                    'status'
                )
            )
        )
    );

    /**
     * Holds the licences bundle
     *
     * @var array
     */
    private $licencesBundle = array(
        'children' => array(
            'licences' => array(
                'children' => array(
                    'licenceType',
                    'status',
                    'goodsOrPsv'
                )
            )
        )
    );

    protected $natureOfBusinessDataBundle = array(
        'children' => array(
            'natureOfBusinesses'
        )
    );

    protected $registeredUsersBundle = array(
        'children' => array(
            'organisationUsers' => array(
                'children' => array(
                    'user' => array(
                        'children' => array(
                            'contactDetails' => array(
                                'children' => array(
                                    'person'
                                )
                            )
                        )
                    )
                )
            )
        )
    );

    public function getApplications($id)
    {
        return $this->get($id, $this->applicationsBundle);
    }

    /**
     * @param int $id organisation id
     * @param array $applicationStatuses only return child applications
     *        matching these statuses (excluding variations)
     * @return array
     */
    public function getNewApplicationsByStatus($id, $applicationStatuses)
    {
        $applications = [];

        $bundle = $this->applicationsBundle;
        $bundle['children']['licences']['children']['applications']['criteria'] = [
            'status' => 'IN ' . json_encode($applicationStatuses),
            'isVariation' => false,
        ];

        $data = $this->get($id, $bundle);
        foreach ($data['licences'] as $licence) {
            $applications = array_merge($applications, $licence['applications']);
        }

        return $applications;
    }

    /**
     * @param int $id organisation id
     * @param array $applicationStatuses only return child applications
     *        matching these statuses (including variaions)
     * @return array
     */
    public function getAllApplicationsByStatus($id, $applicationStatuses)
    {
        $applications = [];

        $bundle = $this->applicationsBundle;
        $bundle['children']['licences']['children']['applications']['criteria'] = [
            'status' => 'IN ' . json_encode($applicationStatuses)
        ];

        $data = $this->get($id, $bundle);
        foreach ($data['licences'] as $licence) {
            $applications = array_merge($applications, $licence['applications']);
        }

        return $applications;
    }

    /**
     * Get the organisation for the given user
     *
     * @param int $userId
     */
    public function getForUser($userId)
    {
        $organisation = $this->getServiceLocator()->get('Helper\Rest')
            ->makeRestCall('OrganisationUser', 'GET', ['user' => $userId], $this->organisationFromUserBundle);

        if ($organisation['Count'] < 1) {
            throw new Exceptions\UnexpectedResponseException('Organisation not found');
        }

        return $organisation['Results'][0]['organisation'];
    }

    /**
     * Get type of organisation
     *
     * @param int $id
     * @return array
     */
    public function getType($id)
    {
        return $this->get($id, $this->typeBundle);
    }

    /**
     * Get business details data
     *
     * @param type $id
     * @param int $licenceId
     */
    public function getBusinessDetailsData($id, $licenceId = null)
    {
        if ($licenceId) {
            $bundle = [
                'children' => [
                    'contactDetails' => [
                        'children' => [
                            'address',
                            'contactType'
                        ]
                    ],
                    'tradingNames' => [
                        'criteria' => [
                            'licence' => $licenceId
                        ]
                    ],
                    'type'
                ]
            ];
        } else {
            $bundle = $this->businessDetailsBundle;
        }
        return $this->get($id, $bundle);
    }

    public function findByIdentifier($identifier)
    {
        return $this->get($identifier);
    }

    public function hasInForceLicences($id)
    {
        $licences = $this->getServiceLocator()
            ->get('Entity\Licence')
            ->getInForceForOrganisation($id);

        return $licences['Count'] > 0;
    }

    public function hasChangedTradingNames($id, $tradingNames, $licenceId)
    {
        $data = $this->getBusinessDetailsData($id, $licenceId);

        $map = function ($v) {
            return $v['name'];
        };

        $existing = array_map($map, $data['tradingNames']);
        $updated  = array_map($map, $tradingNames);

        $diff = array_diff($updated, $existing);

        return count($existing) !== count($updated) || !empty($diff);
    }

    public function hasChangedRegisteredAddress($id, $address)
    {
        $data = $this->getBusinessDetailsData($id);

        $diff = $this->compareKeys(
            $data['contactDetails']['address'],
            $address,
            [
                'addressLine1', 'addressLine2',
                'addressLine3', 'addressLine4',
                'postcode', 'town',
            ]
        );

        return !empty($diff);
    }

    public function hasChangedNatureOfBusiness($id, $updated)
    {
        $existing = $this->getNatureOfBusinessesForSelect($id);

        $diff = array_diff($updated, $existing);

        return count($existing) !== count($updated) || !empty($diff);
    }

    public function hasChangedSubsidiaryCompany($id, $company)
    {
        $existing = $this->getServiceLocator()
            ->get('Entity\CompanySubsidiary')
            ->getById($id);

        $diff = $this->compareKeys(
            $existing,
            $company,
            [
                'companyNo',
                'name'
            ]
        );

        return !empty($diff);
    }

    private function compareKeys($from, $to, $keys = [])
    {
        return $this->getServiceLocator()->get('Helper\Data')->compareKeys($from, $to, $keys);
    }

    /**
     * @param int $id organisation id
     * @param array $licenceStatuses only return child licences matching
     *        these statuses
     * @return array
     */
    public function getLicencesByStatus($id, $licenceStatuses)
    {
        $bundle = $this->licencesBundle;
        $bundle['children']['licences']['criteria'] = [
            'status' => 'IN ' . json_encode($licenceStatuses)
        ];
        return $this->get($id, $bundle)['licences'];
    }

    /**
     * Determine is an organisation isMlh (has at least one valid licence)
     *
     * @param $id
     * @return bool
     */
    public function isMlh($id)
    {
        $licences = $this->getLicencesByStatus($id, [Licence::LICENCE_STATUS_VALID]);
        return (bool) count($licences);
    }

    /**
     * Determine is an organisation is IRFO
     *
     * @param $id
     * @return bool
     */
    public function isIrfo($id)
    {
        $data = $this->get($id);
        return (!empty($data['isIrfo']) && ('Y' === $data['isIrfo'])) ? true : false;
    }

    public function getNatureOfBusinesses($id)
    {
        return $this->getAll($id, $this->natureOfBusinessDataBundle)['natureOfBusinesses'];
    }

    public function getNatureOfBusinessesForSelect($id)
    {
        $naturesOfBusiness = $this->getNatureOfBusinesses($id);

        $normalized = [];

        foreach ($naturesOfBusiness as $value) {
            $normalized[] = $value['id'];
        }

        return $normalized;
    }

    public function getRegisteredUsersForSelect($id)
    {
        $organisation = $this->get($id, $this->registeredUsersBundle);

        $people = [];

        foreach ($organisation['organisationUsers'] as $orgUser) {

            $user = $orgUser['user'];
            $person = $user['contactDetails']['person'];

            $people[$user['id']] = trim($person['forename'] . ' ' . $person['familyName']);
        }

        asort($people);

        return $people;
    }
}
