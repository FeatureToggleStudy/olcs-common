<?php

/**
 * Financial Standing Rate Entity Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace CommonTest\Service\Entity;

use Common\Service\Entity\FinancialStandingRateEntityService;

/**
 * Financial Standing Rate Entity Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FinancialStandingRateEntityServiceTest extends AbstractEntityServiceTestCase
{
    protected function setUp()
    {
        $this->sut = new FinancialStandingRateEntityService();

        parent::setUp();
    }

    /**
     * @group entity_services
     */
    public function testGetRatesInEffectWithNoParams()
    {
        $this->mockDate('2015-04-23');

        $rates = ['RATES'];

        $expectedQuery = [
            'effectiveFrom' => '<=2015-04-23',
            'deletedDate' => 'NULL',
            'sort' => 'effectiveFrom',
            'order' => 'DESC',
        ];

        $this->expectOneRestCall('FinancialStandingRate', 'GET', $expectedQuery)
            ->will($this->returnValue(['Results' => $rates]));

        $this->assertSame($rates, $this->sut->getRatesInEffect());
    }

    /**
     * @group entity_services
     */
    public function testGetRatesInEffectWithDate()
    {
        $rates = ['RATES'];

        $expectedQuery = [
            'effectiveFrom' => '<=2016-04-23',
            'deletedDate' => 'NULL',
            'sort' => 'effectiveFrom',
            'order' => 'DESC',
        ];

        $this->expectOneRestCall('FinancialStandingRate', 'GET', $expectedQuery)
            ->will($this->returnValue(['Results' => $rates]));

        $this->assertSame($rates, $this->sut->getRatesInEffect('2016-04-23'));
    }

    public function testGetRecordById()
    {
        $id = 123;

        $this->expectOneRestCall('FinancialStandingRate', 'GET', $id)
            ->will($this->returnValue('RESPONSE'));

        $this->assertEquals('RESPONSE', $this->sut->getRecordById($id));
    }

    public function testGetFullList()
    {
        $this->expectOneRestCall(
            'FinancialStandingRate',
            'GET',
            ['sort' => 'effectiveFrom', 'order' => 'ASC', 'limit' => 'all']
        )
        ->will($this->returnValue('RESPONSE'));

        $this->assertEquals('RESPONSE', $this->sut->getFullList());
    }
}
