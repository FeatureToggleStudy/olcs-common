<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms as Filter;

/**
 * Class People
 * @package Common\Data\Object\Search
 */
class PeopleSelfServe extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'People';

    /**
     * @var string
     */
    protected $key = 'people';

    /**
     * @var string
     */
    protected $searchIndices = 'person';

    /**
     * Contains an array of the instantiated filters classes.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * @return array
     */
    public function getSettings()
    {
        return [
            'paginate' => [
                'limit' => [
                    'options' => [10, 25, 50, 100]
                ]
            ],
            'layout' => 'headless'
        ];
    }

    /**
     * Returns an array of filters for this index
     *
     * @return array
     */
    public function getFilters()
    {
        if (empty($this->filters)) {

            $this->filters = [
                new Filter\OrgType(),
                new Filter\LicenceType(),
                new Filter\LicenceStatus(),
            ];
        }

        return $this->filters;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            //['title' => 'Found As', 'name'=> 'foundAs'],
            [
                'title' => 'Licence number',
                'name'=> 'licNo',
                'formatter' => function ($data) {
                    return '<a href="/view-details/licence/' . $data['licId'] . '">' . $data['licNo'] . '</a>';
                }
            ],
            ['title' => 'Licence status', 'name'=> 'licStatusDesc'],
            [
                'title' => 'Operator name',
                'name'=> 'orgName',
                'formatter' => function ($data) {
                    $orgName = $data['orgName'];
                    return $orgName;
                }
            ],
            [
                'title' => 'Name',
                'formatter' => function ($row) {

                    $name = [

                        $row['personForename'],
                        $row['personFamilyName']
                    ];

                    return implode(' ', $name);
                }
            ],
            [
                'title' => 'Date of Birth',
                'formatter' => 'Date',
                'name' => 'personBirthDate'
            ]
        ];
    }
}
