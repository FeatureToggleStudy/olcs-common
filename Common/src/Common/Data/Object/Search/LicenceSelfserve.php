<?php
namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms as Filter;

/**
 * Class Licence
 * @package Common\Data\Object\Search
 */
class LicenceSelfserve extends InternalSearchAbstract
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
                new Filter\EntityType(),
                new Filter\LicenceType(),
                new Filter\LicenceStatus(),
                new Filter\TrafficArea(),
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
            [
                'title' => 'Licence number',
                'name'=> 'licNo',
                'formatter' => function ($data) {
                    return '<a href="/licence/' . $data['licId'] . '">' . $data['licNo'] . '</a>';
                }
            ],
            ['title' => 'Licence status', 'name'=> 'licStatusDesc'],
            [
                'title' => 'Operator name',
                'name'=> 'orgName',
                'formatter' => function ($data) {

                    $orgName = $data['orgName'];
                    if ($data['noOfLicencesHeld'] > 1) {
                        $orgName .= ' (MLH)';
                    }

                    return '<a href="/operator/' . $data['orgId'] . '">' .$orgName . '</a>';
                }
            ],
            ['title' => 'Trading name', 'name'=> 'tradingName']
        ];
    }
}
