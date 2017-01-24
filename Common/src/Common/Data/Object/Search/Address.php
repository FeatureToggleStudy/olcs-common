<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms as Filter;
use Common\Util\Escape;
use Common\Service\Helper\UrlHelperService as UrlHelper;

/**
 * Class Address
 * @package Common\Data\Object\Search
 */
class Address extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Address';

    /**
     * @var string
     */
    protected $key = 'address';

    /**
     * @var string
     */
    protected $searchIndices = 'address';

    /**
     * Contains an array of the instantiated filters classes.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Returns an array of filters for this index
     *
     * @return array
     */
    public function getFilters()
    {
        if (empty($this->filters)) {

            $this->filters = [
                new Filter\AddressType(),
                new Filter\AddressComplaint(),
                new Filter\AddressOpposition(),
                new Filter\LicenceStatus(),
                new Filter\ApplicationStatus(),
                new Filter\AddressConditionUndertaking()
            ];
        }

        return $this->filters;
    }

    /**
     * Gets columns
     *
     * @return array
     */
    public function getColumns()
    {
        return [
            [
                'title' => 'Licence / Application',
                'name'=> 'licNoAppNo',
                'formatter' => 'LicenceApplication'
            ],
            [
                'title' => 'Operator name',
                'name'=> 'orgName',
                'formatter' => function ($data, $column, $serviceLocator) {
                    /** @var  UrlHelper $urlHelper */
                    $urlHelper  = $serviceLocator->get('Helper\Url');
                    return sprintf(
                        '<a href="%s">%s</a>',
                        $urlHelper->fromRoute('operator/business-details', ['organisation' => $data['orgId']]),
                        Escape::html($data['orgName'])
                    );
                }
            ],
            [
                'title' => 'Address',
                'formatter' => 'Address',
                'addressFields' => ['saonDesc', 'paonDesc', 'street', 'locality', 'town', 'postcode']
            ],
            [
                'title' => 'Complaint',
                'formatter' => function ($row, $column, $serviceLocator) {

                    if ($row['complaintCaseId']) {
                        /** @var  UrlHelper $urlHelper */
                        $urlHelper  = $serviceLocator->get('Helper\Url');
                        return sprintf(
                            '<a href="%s">Yes</a>',
                            $urlHelper->fromRoute('licence/opposition', ['licence' => $row['licId']])
                        );
                    }

                    return 'No';
                }
            ],
            [
                'title' => 'Opposition',
                'formatter' => function ($row, $column, $serviceLocator) {

                    if ($row['oppositionCaseId']) {
                        /** @var  UrlHelper $urlHelper */
                        $urlHelper  = $serviceLocator->get('Helper\Url');
                        return sprintf(
                            '<a href="%s">Yes</a>',
                            $urlHelper->fromRoute('licence/opposition', ['licence' => $row['licId']])
                        );
                    }

                    return 'No';
                }
            ],
            ['title' => 'Conditions', 'name'=> 'conditions'],
            [
                'title' => 'Date added',
                'formatter' => 'Date',
                'name'=> 'createdOn'
            ],
            [
                'title' => 'Date removed',
                'formatter' => 'Date',
                'name'=> 'deletedDate'
            ],
        ];
    }
}
