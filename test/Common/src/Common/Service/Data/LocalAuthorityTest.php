<?php

namespace CommonTest\Service\Data;

use Common\Service\Data\LocalAuthority;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Class LocalAuthority Test
 * @package CommonTest\Service
 */
class LocalAuthorityTest extends MockeryTestCase
{
    public function testGetServiceName()
    {
        $sut = new LocalAuthority();
        $this->assertEquals('LocalAuthority', $sut->getServiceName());
    }

    public function testFormatData()
    {
        $source = $this->getSingleSource();
        $expected = $this->getSingleExpected();

        $sut = new LocalAuthority();

        $this->assertEquals($expected, $sut->formatData($source));
    }

    public function testFormatDataForGroups()
    {
        $source = $this->getSingleSource();
        $expected = $this->getGroupsExpected();

        $sut = new LocalAuthority();

        $this->assertEquals($expected, $sut->formatDataForGroups($source));
    }

    /**
     * @dataProvider provideFetchListOptions
     * @param $input
     * @param $expected
     * @param $useGroups
     */
    public function testFetchListOptions($input, $expected, $useGroups)
    {
        $sut = new LocalAuthority();
        $sut->setData('LocalAuthority', $input);

        $this->assertEquals($expected, $sut->fetchListOptions('', $useGroups));
    }

    public function provideFetchListOptions()
    {
        return [
            [$this->getSingleSource(), $this->getSingleExpected(), false],
            [false, [], false],
            [$this->getSingleSource(), $this->getGroupsExpected(), true],
        ];
    }

    /**
     * @dataProvider provideFetchListData
     * @param $data
     * @param $expected
     */
    public function testFetchListData($data, $expected)
    {
        $mockRestClient = m::mock('Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')
            ->once()
            ->with('', ['limit' => 1000, 'bundle' => '{"children":["trafficArea"]}'])
            ->andReturn($data);

        $sut = new LocalAuthority();
        $sut->setRestClient($mockRestClient);

        $this->assertEquals($expected, $sut->fetchListData());
        $sut->fetchListData(); //ensure data is cached
    }

    public function provideFetchListData()
    {
        return [
            [false, false],
            [['Results' => $this->getSingleSource()], $this->getSingleSource()],
            [['some' => 'data'],  false]
        ];
    }

    /**
     * @return array
     */
    protected function getSingleExpected()
    {
        $expected = [
            'val-1' => 'Value 1',
            'val-2' => 'Value 2',
            'val-3' => 'Value 3',
        ];
        return $expected;
    }

    /**
     * @return array
     */
    protected function getGroupsExpected()
    {
        $expected = [
            'B' => [
                'label' => 'Bee',
                'options' => [
                    'val-1' => 'Value 1',
                ],
            ],
            'A' => [
                'label' => 'Aye',
                'options' => [
                    'val-2' => 'Value 2',
                ],
            ],
            'C' => [
                'label' => 'Cee',
                'options' => [
                    'val-3' => 'Value 3',
                ],
            ]
        ];
        return $expected;
    }

    /**
     * @return array
     */
    protected function getSingleSource()
    {
        $source = [
            ['id' => 'val-1', 'description' => 'Value 1', 'txcName' => 'B', 'trafficArea' => ['name' => 'Bee']],
            ['id' => 'val-2', 'description' => 'Value 2', 'txcName' => 'A', 'trafficArea' => ['name' => 'Aye']],
            ['id' => 'val-3', 'description' => 'Value 3', 'txcName' => 'C', 'trafficArea' => ['name' => 'Cee']]
        ];
        return $source;
    }
}