<?php

namespace Common\Data\Mapper\Continuation;

use Common\Service\Helper\TranslationHelperService;
use Common\RefData;

/**
 * Licence checklist mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceChecklist
{
    /**
     * Map from result to view
     *
     * @param array                    $data       data
     * @param TranslationHelperService $translator translator
     *
     * @return array
     */
    public static function mapFromResultToView(array $data, TranslationHelperService $translator)
    {
        $licenceData = $data['licence'];
        return [
            'data' => [
                'typeOfLicence' => [
                    'operatingFrom' =>
                        $licenceData['trafficArea']['id'] === RefData::NORTHERN_IRELAND_TRAFFIC_AREA_CODE
                            ? $translator->translate('continuations.type-of-licence.ni')
                            : $translator->translate('continuations.type-of-licence.gb'),
                    'goodsOrPsv' => $licenceData['goodsOrPsv']['description'],
                    'licenceType' => $licenceData['licenceType']['description']
                ],
                'businessType' => [
                    'typeOfBusiness' => $licenceData['organisation']['type']['description'],
                    'typeOfBusinessId' => $licenceData['organisation']['type']['id'],
                ],
                'businessDetails' => self::mapBusinessDetails($licenceData, $translator),
                'addresses' => self::mapAddresses($licenceData),
                'people' => self::mapPeople($licenceData, $translator),
                'vehicles' => self::mapVehicles($licenceData, $translator),
                'operatingCentres' => self::mapOperatingCentres($data),
                'transportManagers' => self::mapTransportManagers($data),
                'safety' => self::mapSafetyDetails($licenceData, $translator),
                'continuationDetailId' => $data['id']
            ]
        ];
    }

    /**
     * Map business details
     *
     * @param array                    $data       data
     * @param TranslationHelperService $translator translator
     *
     * @return array
     */
    private static function mapBusinessDetails($data, TranslationHelperService $translator)
    {
        $organisation = $data['organisation'];
        $organisationType = $organisation['type'];
        $organisationTypeId = $organisationType['id'];
        $businessDetails = [];
        $baseCompanyTypes = [
            RefData::ORG_TYPE_REGISTERED_COMPANY,
            RefData::ORG_TYPE_LLP,
            RefData::ORG_TYPE_PARTNERSHIP,
            RefData::ORG_TYPE_OTHER,
        ];
        $limitedCompanyTypes = [
            RefData::ORG_TYPE_REGISTERED_COMPANY,
            RefData::ORG_TYPE_LLP
        ];
        $organisationLabels = [
            RefData::ORG_TYPE_REGISTERED_COMPANY =>
                $translator->translate('continuations.business-details.company-name'),
            RefData::ORG_TYPE_LLP =>
                $translator->translate('continuations.business-details.company-name'),
            RefData::ORG_TYPE_PARTNERSHIP =>
                $translator->translate('continuations.business-details.partnership-name'),
            RefData::ORG_TYPE_OTHER =>
                $translator->translate('continuations.business-details.organisation-name')
        ];
        if (in_array($organisationType['id'], $baseCompanyTypes)) {
            $businessDetails['companyName'] = $organisation['name'];
            $businessDetails['organisationLabel'] = $organisationLabels[$organisationTypeId];
        }
        if ($organisationTypeId !== RefData::ORG_TYPE_OTHER) {
            $businessDetails['tradingNames'] = count($data['tradingNames']) !== 0
                ? implode(', ', array_column($data['tradingNames'], 'name'))
                : $translator->translate('continuations.business-details.trading-names.none-added');
        }
        if (in_array($organisationTypeId, $limitedCompanyTypes)) {
            $businessDetails['companyNumber'] = $organisation['companyOrLlpNo'];
        }
        return $businessDetails;
    }

    /**
     * Map business details
     *
     * @param array $data data
     *
     * @return array
     */
    private static function mapAddresses($licenceData)
    {
        $addresses = [];
        if (isset($licenceData['correspondenceCd']['address'])) {
            $addresses['correspondenceAddress'] = self::formatAddress($licenceData['correspondenceCd']['address']);
        }
        if (isset($licenceData['establishmentCd']['address'])) {
            $addresses['establishmentAddress'] = self::formatAddress($licenceData['establishmentCd']['address']);
        }
        if (isset($licenceData['correspondenceCd']['phoneContacts'])) {
            foreach ($licenceData['correspondenceCd']['phoneContacts'] as $pc) {
                if ($pc['phoneContactType']['id'] === RefData::PHONE_TYPE_PRIMARY) {
                    $addresses['primaryNumber'] = $pc['phoneNumber'];
                }
                if ($pc['phoneContactType']['id'] === RefData::PHONE_TYPE_SECONDARY) {
                    $addresses['secondaryNumber'] = $pc['phoneNumber'];
                }
            }
        }
        $addresses['showEstablishmentAddress'] = in_array(
            $licenceData['licenceType']['id'],
            [RefData::LICENCE_TYPE_STANDARD_NATIONAL, RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL]
        );
        return $addresses;
    }

    /**
     * Format address
     *
     * @param array $inputAddress input address
     *
     * @return string
     */
    private static function formatAddress($inputAddress)
    {
        $fields = ['addressLine1', 'addressLine2', 'addressLine3', 'addressLine4', 'town', 'postcode'];
        $outputAddress = '';
        array_walk(
            $fields,
            function ($item) use ($inputAddress, &$outputAddress) {
                if (isset($inputAddress[$item]) && !empty($inputAddress[$item])) {
                    $outputAddress .= $inputAddress[$item] . ', ';
                }
            }
        );
        $outputAddress = trim($outputAddress, ', ');

        return $outputAddress;
    }

    /**
     * Map people
     *
     * @param array                    $data       data
     * @param TranslationHelperService $translator translator
     *
     * @return array
     */
    private static function mapPeople($data, $translator)
    {
        $people = [];
        $organisation = $data['organisation'];
        foreach ($organisation['organisationPersons'] as $op) {
            $person = $op['person'];
            $people[] = [
                'name' => implode(
                    ' ',
                    [$person['title']['description'], $person['forename'], $person['familyName']]
                ),
                'birthDate' => self::formatDate($person['birthDate'])
            ];
        }
        usort(
            $people,
            function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            }
        );
        return [
            'persons' => $people,
            'header' => $translator->translate('continuations.people-section-header.' . $organisation['type']['id']),
            'emptyTableMessage' =>
                $translator->translate('continuations.people-empty-table-message.' . $organisation['type']['id']),
            'displayPersonCount' => RefData::CONTINUATIONS_DISPLAY_PERSON_COUNT
        ];
    }

    /**
     * Map people section to view
     *
     * @param array                    $organisationPersons data
     * @param string                   $orgType             organisation type
     * @param TranslationHelperService $translator          translator
     *
     * @return array
     */
    public static function mapPeopleSectionToView($organisationPersons, $orgType, $translator)
    {
        $peopleHeader[] = [
            ['value' => $translator->translate('continuations.people-section.table.name'), 'header' => true],
            ['value' => $translator->translate('continuations.people-section.table.date-of-birth'), 'header' => true]
        ];
        $peopleDetails = [];
        foreach ($organisationPersons as $op) {
            $person = $op['person'];
            $peopleDetails[] = [
                [
                    'value' => implode(
                        ' ',
                        [$person['title']['description'], $person['forename'], $person['familyName']]
                    )
                ],
                [
                    'value' => self::formatDate($person['birthDate'])
                ]
            ];
        }
        usort(
            $peopleDetails,
            function ($a, $b) {
                return strcmp($a[0]['value'], $b[0]['value']);
            }
        );

        return [
            'people' => array_merge($peopleHeader, $peopleDetails),
            'totalPeopleMessage' => $translator->translate('continuations.people.section-header.' . $orgType),
        ];
    }

    /**
     * Map people
     *
     * @param array                    $data       data
     * @param TranslationHelperService $translator translator
     *
     * @return array
     */
    private static function mapVehicles($data, $translator)
    {
        $vehicles = [];
        $licenceVehicles = $data['licenceVehicles'];
        foreach ($licenceVehicles as $licenceVehicle) {
            $vehicles[] = [
                'vrm' => $licenceVehicle['vehicle']['vrm'],
                // no need to translate, the same in Welsh
                'weight' => $licenceVehicle['vehicle']['platedWeight'] . 'kg',
            ];
        }
        usort(
            $vehicles,
            function ($a, $b) {
                return strcmp($a['vrm'], $b['vrm']);
            }
        );

        return [
            'vehicles' => $vehicles,
            'header' => $translator->translate('continuations.vehicles-section-header'),
            'isGoods' => $data['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_GOODS_VEHICLE,
            'displayVehiclesCount' => RefData::CONTINUATIONS_DISPLAY_VEHICLES_COUNT
        ];
    }

    /**
     * Map people section to view
     *
     * @param array                    $data       data
     * @param TranslationHelperService $translator translator
     *
     * @return array
     */
    public static function mapVehiclesSectionToView($data, $translator)
    {
        $isGoods = $data['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_GOODS_VEHICLE;
        $header[] = [
            ['value' => $translator->translate('continuations.vehicles-section.table.vrm'), 'header' => true]
        ];
        if ($isGoods) {
            $header[0][] = [
                'value' => $translator->translate('continuations.vehicles-section.table.weight'), 'header' => true
            ];
        }

        $vehicles = [];
        $licenceVehicles = $data['licenceVehicles'];
        foreach ($licenceVehicles as $licenceVehicle) {
            $row = [];
            $row[] = ['value' => $licenceVehicle['vehicle']['vrm']];
            if ($isGoods) {
                // no need to translate, the same in Welsh
                $row[] = ['value' => $licenceVehicle['vehicle']['platedWeight']  . 'kg'];
            }
            $vehicles[] = $row;
        }
        usort(
            $vehicles,
            function ($a, $b) {
                return strcmp($a[0]['value'], $b[0]['value']);
            }
        );
        return [
            'vehicles' => array_merge($header, $vehicles),
            'totalVehiclesMessage' => $translator->translate('continuations.vehicles.section-header'),
        ];
    }

    /**
     * Map operating centres
     *
     * @param array $fullData data
     *
     * @return array
     */
    private static function mapOperatingCentres($fullData)
    {
        $data = $fullData['licence'];
        $operatingCentres = [];
        foreach ($data['operatingCentres'] as $loc) {
            $oc = $loc['operatingCentre'];
            $operatingCentres[] = [
                'name' => implode(', ', [$oc['address']['addressLine1'], $oc['address']['town']]),
                'vehicles' => $loc['noOfVehiclesRequired'],
                'trailers' => $loc['noOfTrailersRequired'],
            ];
        }
        usort(
            $operatingCentres,
            function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            }
        );
        return [
            'operatingCentres' => $operatingCentres,
            'totalOperatingCentres' => count($operatingCentres),
            'totalVehicles' => $data['totAuthVehicles'],
            'totalTrailers' => $data['totAuthTrailers'],
            'isGoods' => $data['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_GOODS_VEHICLE,
            'displayOperatingCentresCount' => RefData::CONTINUATIONS_DISPLAY_OPERATING_CENTRES_COUNT,
            'ocChanges' => $fullData['ocChanges'],
        ];
    }

    /**
     * Map operating centres section to view
     *
     * @param array                    $data       data
     * @param TranslationHelperService $translator translator
     *
     * @return array
     */
    public static function mapOperatingCentresSectionToView($data, $translator)
    {
        $isGoods = $data['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_GOODS_VEHICLE;
        $header[] = [
            ['value' => $translator->translate('continuations.oc-section.table.oc'), 'header' => true],
            ['value' => $translator->translate('continuations.oc-section.table.vehicles'), 'header' => true],
        ];
        if ($isGoods) {
            $header[0][] = [
                'value' => $translator->translate('continuations.oc-section.table.trailers'), 'header' => true
            ];
        }

        $operatingCentres = [];
        foreach ($data['operatingCentres'] as $loc) {
            $oc = $loc['operatingCentre'];
            $row = [
                ['value' => implode(', ', [$oc['address']['addressLine1'], $oc['address']['town']])],
                ['value' => $loc['noOfVehiclesRequired']]
            ];
            if ($isGoods) {
                $row[] = ['value' => $loc['noOfTrailersRequired']];
            }
            $operatingCentres[] = $row;
        }
        usort(
            $operatingCentres,
            function ($a, $b) {
                return strcmp($a[0]['value'], $b[0]['value']);
            }
        );
        return [
            'operatingCentres' => array_merge($header, $operatingCentres),
            'totalOperatingCentresMessage' => $translator->translate('continuations.operating-centres.section-header'),
        ];
    }

    /**
     * Map transport manager section to view
     *
     * @param array                    $fullData   data
     * @param TranslationHelperService $translator translator
     *
     * @return array
     */
    public static function mapTransportManagers($fullData)
    {
        $data = $fullData['licence'];
        $transportManagers = [];
        foreach ($data['tmLicences'] as $tmLicence) {
            $person = $tmLicence['transportManager']['homeCd']['person'];
            $transportManagers[] = [
                'name' => trim(
                    implode(
                        ' ',
                        [
                            isset($person['title']['description']) ? $person['title']['description'] : '',
                            $person['forename'],
                            $person['familyName']
                        ]
                    )
                ),
                'dob' => self::formatDate($person['birthDate']),
            ];
        }
        usort(
            $transportManagers,
            function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            }
        );
        return [
            'transportManagers' => $transportManagers,
            'totalTransportManagers' => count($transportManagers),
            'displayTransportManagersCount' => RefData::CONTINUATIONS_DISPLAY_TM_COUNT,
            'tmChanges' => $fullData['tmChanges'],
        ];
    }

    /**
     * Map transport manager section to view
     *
     * @param array                    $data       data
     * @param TranslationHelperService $translator translator
     *
     * @return array
     */
    public static function mapTransportManagerSectionToView($data, $translator)
    {
        $header[] = [
            ['value' => $translator->translate('continuations.tm-section.table.name'), 'header' => true],
            ['value' => $translator->translate('continuations.tm-section.table.dob'), 'header' => true],
        ];

        $transportManagers = [];
        foreach ($data['tmLicences'] as $tmLicence) {
            $person = $tmLicence['transportManager']['homeCd']['person'];
            $transportManagers[] = [
                [
                    'value' => trim(
                        implode(
                            ' ',
                            [
                                isset($person['title']['description']) ? $person['title']['description'] : '',
                                $person['forename'],
                                $person['familyName']
                            ]
                        )
                    ),
                ],
                ['value' => self::formatDate($person['birthDate'])]
            ];
        }
        usort(
            $transportManagers,
            function ($a, $b) {
                return strcmp($a[0]['value'], $b[0]['value']);
            }
        );
        return [
            'transportManagers' => array_merge($header, $transportManagers),
            'totalTransportManagersMessage' => $translator->translate('continuations.tm.section-header'),
        ];
    }

    /**
     * Map safety details
     *
     * @param array                    $data       data
     * @param TranslationHelperService $translator translator
     *
     * @return array
     */
    private static function mapSafetyDetails($data, $translator)
    {
        $safetyInspectors = [];
        foreach ($data['workshops'] as $workshop) {
            $contactDetails = $workshop['contactDetails'];
            $address = $contactDetails['address'];

            $safetyInspectors[] = [
                'name' => $contactDetails['fao']
                    . ' ('
                    . (($workshop['isExternal'] === 'Y')
                            ? $translator->translate('continuations.safety-section.table.external-contractor')
                            : $translator->translate('continuations.safety-section.table.owner-or-employee'))
                    . ')',
                'address' => implode(', ', [$address['addressLine1'], $address['town']])
            ];

        }
        usort(
            $safetyInspectors,
            function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            }
        );

        $safetyInsVehicles = null;
        $safetyInsTrailers = null;
        $safetyInsVaries = null;
        if (!empty($data['safetyInsVehicles'])) {
            $safetyInsVehicles = $data['safetyInsVehicles']
                . ' '
                . (
                ((int) $data['safetyInsVehicles'] === 1)
                    ? $translator->translate('continuations.safety-section.table.week')
                    : $translator->translate('continuations.safety-section.table.weeks')
                );
        }
        if (!empty($data['safetyInsTrailers'])) {
            $safetyInsTrailers = $data['safetyInsTrailers']
                . ' '
                . (
                ((int) $data['safetyInsTrailers'] === 1)
                    ? $translator->translate('continuations.safety-section.table.week')
                    : $translator->translate('continuations.safety-section.table.weeks')
                );
        }
        if ($data['safetyInsVaries'] !== null) {
            $safetyInsVaries = ($data['safetyInsVaries'] === 'Y')
                ? $translator->translate('Yes')
                : $translator->translate('No');
        }

        return [
            'safetyInspectors' => $safetyInspectors,
            'totalSafetyInspectors' => count($safetyInspectors),
            'safetyInsVehicles' => $safetyInsVehicles,
            'safetyInsTrailers' => $safetyInsTrailers,
            'safetyInsVaries' => $safetyInsVaries,
            'tachographIns'=> isset($data['tachographIns']['id'])
                ? $translator->translate('continuations.safety-section.table.' . $data['tachographIns']['id'])
                : null,
            'tachographInsName' => $data['tachographInsName'],
            'isGoods' => $data['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_GOODS_VEHICLE,
            'showCompany' =>
                isset($data['tachographIns']['id'])
                && $data['tachographIns']['id'] === RefData::LICENCE_SAFETY_INSPECTOR_EXTERNAL,
            'displaySafetyInspectorsCount' => RefData::CONTINUATIONS_DISPLAY_SAFETY_INSPECTORS_COUNT
        ];
    }

    /**
     * Map safety inspectors section to view
     *
     * @param array                    $data       data
     * @param TranslationHelperService $translator translator
     *
     * @return array
     */
    public static function mapSafetyInspectorsSectionToView($data, $translator)
    {
        $header[] = [
            ['value' => $translator->translate('continuations.safety-section.table.inspector'), 'header' => true],
            ['value' => $translator->translate('continuations.safety-section.table.address'), 'header' => true],
        ];

        $safetyInspectors = [];
        foreach ($data['workshops'] as $workshop) {
            $contactDetails = $workshop['contactDetails'];
            $address = $contactDetails['address'];
            $safetyInspectors[] = [
                [
                    'value' => $contactDetails['fao']
                        . ' ('
                        . (($workshop['isExternal'] === 'Y')
                            ? $translator->translate('continuations.safety-section.table.external-contractor')
                            : $translator->translate('continuations.safety-section.table.owner-or-employee'))
                        . ')',
                ],
                ['value' => implode(', ', [$address['addressLine1'], $address['town']])]
            ];
        }
        usort(
            $safetyInspectors,
            function ($a, $b) {
                return strcmp($a[0]['value'], $b[0]['value']);
            }
        );
        return [
            'safetyInspectors' => array_merge($header, $safetyInspectors),
            'totalSafetyInspectorsMessage' => $translator->translate('continuations.safety.section-header'),
        ];
    }

    /**
     * Format date
     *
     * @param string $date date
     *
     * @return string
     */
    private static function formatDate($date)
    {
        return !empty($date) ? date(\DATE_FORMAT, strtotime($date)) : '';
    }
}
