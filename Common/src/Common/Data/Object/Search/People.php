<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms as Filter;

/**
 * Class People
 * @package Common\Data\Object\Search
 */
class People extends InternalSearchAbstract
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
     * Returns an array of filters for this index
     *
     * @return array
     */
    public function getFilters()
    {
        if (empty($this->filters)) {

            $this->filters = [
                new Filter\FoundBy()
            ];
        }

        return $this->filters;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return [
            'crud' => [
                'links' => [
                    'create-transport-manager' => [
                        'label' => 'Create transport manager',
                        'class' => 'primary js-modal-ajax',
                        'route' => [
                            'route' => 'create_transport_manager'
                        ]
                    ]
                ]
            ],
            'paginate' => [
                'limit' => [
                    'options' => [10, 25, 50]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            ['title' => 'Found As', 'name'=> 'foundAs'],
            [
                'title' => 'Record',
                'formatter' => function ($row) {

                    if (!empty($row['tmId'])) {
                        return '<a href="/transport-manager/' . $row['tmId'] . '">'
                            . 'TM ' . $row['tmId']
                            . '</a>';
                    }

                    $licence = '<a href="/licence/' . $row['licId'] . '">'
                             . $row['licNo']
                             . '</a>, '
                             . $row['licTypeDesc']
                             . '<br />'
                             . $row['licStatusDesc'];

                    return $licence;
                }
            ],
            [
                'title' => 'Name',
                'formatter' => function ($row) {

                    return $row['personForename'] . '  ' .$row['personFamilyName'];
                }
            ],
            [
                'title' => 'DOB',
                'name'=> 'personBirthDate',
                'formatter' => function ($row) {

                    return empty($row['personBirthDate']) ? 'Not known' : date('d/m/Y', strtotime($row['personBirthDate']));
                }
            ],
            [
                'title' => 'Date added',
                'name'=> 'dateAdded',
                'formatter' => function ($row) {

                    return empty($row['dateAdded']) ? 'NA' : date('d/m/Y', strtotime($row['dateAdded']));
                }
            ],
            [
                'title' => 'Date removed',
                'name'=> 'dateRemoved',
                'formatter' => function ($row) {

                    return empty($row['dateRemoved']) ? 'NA' : date('d/m/Y', strtotime($row['dateRemoved']));
                }
            ],
            [
                'title' => 'Disq?',
                'name'=> 'disqualified',
                'formatter' => function ($row) {

                    return empty($row['disqualified']) ? 'No' : 'Yes';
                }
            ]
        ];
    }
}
