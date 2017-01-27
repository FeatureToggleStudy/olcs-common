<?php
namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms as Filter;
use Common\Util\Escape;

/**
 * Class Licence
 * @package Common\Data\Object\Search
 */
class Licence extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Licence';
    /**
     * @var string
     */
    protected $key = 'licence';

    /**
     * @var string
     */
    protected $searchIndices = 'licence';

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
                new Filter\LicenceType(),
                new Filter\LicenceStatus(),
                new Filter\LicenceTrafficArea(),
                new Filter\EntityType(),
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
                    'create-operator' => [
                        'label' => 'Create operator',
                        'class' => 'primary js-modal-ajax',
                        'route' => [
                            'route' => 'create_operator'
                        ]
                    ],
                    'create-unlicensed-operator' => [
                        'label' => 'Create unlicensed operator',
                        'class' => 'primary js-modal-ajax',
                        'route' => [
                            'route' => 'create_unlicensed_operator'
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
            [
                'title' => 'Licence number',
                'name'=> 'licNo',
                'formatter' => function ($data) {
                    return '<a href="/licence/' . $data['licId'] . '?' . '13-10-09' .'">' . $data['licNo'] . '</a>';
                }
            ],
            ['title' => 'Licence status', 'name'=> 'licStatusDesc'],
            [
                'title' => 'Operator name',
                'name'=> 'orgName',
                'formatter' => function ($data, $column, $sl) {

                    $orgName = $data['orgName'];
                    if ($data['noOfLicencesHeld'] > 1) {
                        $orgName .= ' (MLH)';
                    }
                    $url = $sl->get('Helper\Url')->fromRoute(
                        'operator/business-details',
                        ['organisation' => $data['orgId']]
                    );

                    return '<a href="' . $url . '">' . Escape::html($orgName) . '</a>';
                }
            ],
            [
                'title' => 'Trading name',
                'name'=> 'licenceTradingNames',
                'formatter' => function ($data) {
                    return str_replace('|', ', <br />', $data['licenceTradingNames']);
                }
            ],
            ['title' => 'Entity type', 'name'=> 'orgTypeDesc'],
            ['title' => 'Licence type', 'name'=> 'licTypeDesc'],
            ['title' => 'FABS Reference', 'name'=> 'fabsReference'],
            [
                'title' => 'Cases',
                'name'=> 'caseCount',
                'formatter' => function ($data) {
                    return '<a href="/licence/' . $data['licId'] . '/cases">' . $data['caseCount'] . '</a>';
                }
            ]
        ];
    }
}
