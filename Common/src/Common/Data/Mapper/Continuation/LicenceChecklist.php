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
                'people' => self::mapPeople($licenceData, $translator),
                'vehicles' => self::mapVehicles($licenceData, $translator),
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
                'birthDate' => date(\DATE_FORMAT, strtotime($person['birthDate']))
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
                    'value' => date(\DATE_FORMAT, strtotime($person['birthDate']))
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
     * @param array                    $date       data
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
}