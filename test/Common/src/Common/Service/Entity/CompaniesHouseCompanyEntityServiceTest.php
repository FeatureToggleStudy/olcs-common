<?php

/**
 * Companies House Company Entity Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace CommonTest\Service\Entity;

use Mockery as m;
use Common\Service\Entity\CompaniesHouseCompanyEntityService;

/**
 * Companies House Company Entity Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CompaniesHouseCompanyEntityServiceTest extends AbstractEntityServiceTestCase
{
    public function setUp()
    {
        $this->sut = new CompaniesHouseCompanyEntityService();

        parent::setUp();
    }

    public function testGetLatestByCompanyNumber()
    {
        $companyNumber = '01234567';

        $expectedQuery = [
            'companyNumber' => $companyNumber,
            'sort' => 'createdOn',
            'order' => 'DESC',
            'limit' => 1,
        ];

        $expectedBundle = [
            'children' => [
                'officers'
            ],
        ];

        $results = [
            'Count' => 2,
            'Results' => [
                ['COMPANY1'],
            ],
        ];

        // expectations
        $this->expectOneRestCall('CompaniesHouseCompany', 'GET', $expectedQuery, $expectedBundle)
            ->will($this->returnValue($results));

        // assertions
        $this->assertEquals(['COMPANY1'], $this->sut->getLatestByCompanyNumber($companyNumber));
    }

    public function testSaveNew()
    {
        $data = [
            'companyNumber' => '01234567',
            'officers' => [
                ['name' => 'Bob'],
                ['name' => 'Dave'],
            ]
        ];

        $expectedData = [
            'companyNumber' => '01234567',
        ];

        $saved = ['id' => '99'];

        $this->sm->setService(
            'Entity\CompaniesHouseOfficer',
            m::mock()
                ->shouldReceive('multiCreate')
                ->once()
                ->with(
                    [
                        ['companiesHouseCompany' => 99, 'name' => 'Bob'],
                        ['companiesHouseCompany' => 99, 'name' => 'Dave']
                    ]
                )
                ->getMock()
        );

        // expectations
        $this->expectOneRestCall('CompaniesHouseCompany', 'POST', $expectedData)
            ->will($this->returnValue($saved));

        $this->assertEquals($saved, $this->sut->saveNew($data));
    }
}
