<?php

namespace Common\Data\Object\Search;
use Common\Data\Object\Search\Aggregations\Terms\BusRegStatus;

/**
 * Class BusReg
 * @package Common\Data\Object\Search
 */
class BusReg extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Bus registrations';
    /**
     * @var string
     */
    protected $key = 'bus_reg';

    /**
     * @var string
     */
    protected $searchIndices = 'busreg';

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
                new BusRegStatus(),
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
                'title' => 'Registration no',
                'name'=> 'regNo',
                'formatter' => function ($data) {

                    return '<a href="/licence/'
                    . $data['licId'] . '/bus/' . $data['busregId']
                    . '/details">' . $data['regNo'] . '</a>';
                }
            ],
            [
                'title' => 'Name',
                'name'=> 'orgName',
                'formatter' => function ($data) {

                    //die('<pre>' . print_r($data, 1));
                    $orgName = $data['organisationName'];
                    return $orgName;
                }
            ],
            ['title' => 'Variation number', 'name'=> 'variationNo'],
            ['title' => 'Status', 'name'=> 'busRegStatus'],
            ['title' => 'Date first registered / cancelled', 'name'=> 'date_1stReg'],
            ['title' => 'Service no', 'name'=> 'serviceNo'],
            ['title' => 'Start point', 'name'=> 'startPoint'],
            ['title' => 'Finish point', 'name'=> 'finishPoint'],
            ['title' => 'Conditions on licence', 'name'=> 'finishPoint']
        ];
    }
}
