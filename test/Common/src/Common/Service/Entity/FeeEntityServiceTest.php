<?php

/**
 * Fee Entity Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace CommonTest\Service\Entity;

use Common\Service\Entity\FeeEntityService;
use Common\Service\Data\FeeTypeDataService;
use Mockery as m;
use Common\Service\Entity\ApplicationEntityService;
use Common\Service\Entity\LicenceEntityService;

/**
 * Fee Entity Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FeeEntityServiceTest extends AbstractEntityServiceTestCase
{
    protected function setUp()
    {
        $this->sut = new FeeEntityService();

        parent::setUp();
    }

    /**
     * @group entity_services
     */
    public function testCancelForLicenceWithNoResults()
    {
        $id = 3;

        $query = array(
            'licence' => $id,
            'feeStatus' => array(
                FeeEntityService::STATUS_OUTSTANDING,
                FeeEntityService::STATUS_WAIVE_RECOMMENDED
            ),
            'limit' => 'all'
        );

        $results = array('Results' => array());

        $this->expectOneRestCall('Fee', 'GET', $query)
            ->will($this->returnValue($results));

        $this->sut->cancelForLicence($id);
    }

    /**
     * @group feeService
     */
    public function testCancelForLicence()
    {
        $id = 3;

        $query = array(
            'licence' => $id,
            'feeStatus' => array(
                FeeEntityService::STATUS_OUTSTANDING,
                FeeEntityService::STATUS_WAIVE_RECOMMENDED
            ),
            'limit' => 'all'
        );

        $results = array(
            'Results' => array(
                array(
                    'id' => 7,
                    'task' => array(
                        'id' => 1,
                        'version' => 1
                    )
                )
            )
        );

        $data = array(
            '_OPTIONS_' => array('multiple' => true),
            array(
                'id' => 7,
                'feeStatus' => FeeEntityService::STATUS_CANCELLED,
                '_OPTIONS_' => array('force' => true)
            )
        );
        $taskData = array(
            array(
                'id' => 1,
                'isClosed' => 'Y',
                'version' => 1
            )
        );

        $this->expectedRestCallInOrder('Fee', 'GET', $query)
            ->will($this->returnValue($results));

        $this->expectedRestCallInOrder('Fee', 'PUT', $data);

        $mockTaskService = m::mock()
            ->shouldReceive('multiUpdate')
            ->with($taskData)
            ->getMock();
        $this->sm->setService('Entity\Task', $mockTaskService);

        $this->sut->cancelForLicence($id);
    }

    /**
     * @group entity_services
     */
    public function testCancelForApplicationWithNoResults()
    {
        $id = 123;

        $query = array(
            'application' => $id,
            'feeStatus' => array(
                FeeEntityService::STATUS_OUTSTANDING,
                FeeEntityService::STATUS_WAIVE_RECOMMENDED
            ),
            'limit' => 'all'
        );

        $results = array('Results' => array());

        $this->expectOneRestCall('Fee', 'GET', $query)
            ->will($this->returnValue($results));

        $this->sut->cancelForApplication($id);
    }

    /**
     * @group entity_services
     */
    public function testCancelForApplication()
    {
        $id = 123;

        $query = array(
            'application' => $id,
            'feeStatus' => array(
                FeeEntityService::STATUS_OUTSTANDING,
                FeeEntityService::STATUS_WAIVE_RECOMMENDED
            ),
            'limit' => 'all'
        );

        $results = array(
            'Results' => array(
                array(
                    'id' => 7
                )
            )
        );

        $data = array(
            '_OPTIONS_' => array('multiple' => true),
            array(
                'id' => 7,
                'feeStatus' => FeeEntityService::STATUS_CANCELLED,
                '_OPTIONS_' => array('force' => true)
            )
        );

        $this->expectedRestCallInOrder('Fee', 'GET', $query)
            ->will($this->returnValue($results));

        $this->expectedRestCallInOrder('Fee', 'PUT', $data);

        $this->sut->cancelForApplication($id);
    }

    /**
     * @group entity_services
     */
    public function testGetApplication()
    {
        $id = 3;

        $response = array(
            'application' => 1
        );

        $this->expectOneRestCall('Fee', 'GET', $id)
            ->will($this->returnValue($response));

        $this->assertEquals(1, $this->sut->getApplication($id));
    }

    /**
     * @group entity_services
     */
    public function testGetApplicationWithoutApplication()
    {
        $id = 3;

        $response = array();

        $this->expectOneRestCall('Fee', 'GET', $id)
            ->will($this->returnValue($response));

        $this->assertEquals(null, $this->sut->getApplication($id));
    }

    /**
     * @group entity_services
     */
    public function testGetOutstandingFeesForApplication()
    {
        $id = 3;

        $query = array(
            'application' => 3,
            'feeStatus' => array(
                FeeEntityService::STATUS_OUTSTANDING,
                FeeEntityService::STATUS_WAIVE_RECOMMENDED
            ),
            'limit' => 'all'
        );

        $response = array(
            'Results' => 'RESPONSE'
        );

        $this->expectOneRestCall('Fee', 'GET', $query)
            ->will($this->returnValue($response));

        $this->assertEquals('RESPONSE', $this->sut->getOutstandingFeesForApplication($id));
    }

    /**
     * @group entity_services
     */
    public function testGetLatestOutstandingFeeForApplication()
    {
        $id = 3;

        $query = array(
            'application' => 3,
            'feeStatus' => array(
                FeeEntityService::STATUS_OUTSTANDING,
                FeeEntityService::STATUS_WAIVE_RECOMMENDED
            ),
            'limit' => 1,
            'sort' => 'invoicedDate',
            'order' => 'DESC'
        );

        $response = array(
            'Results' => ['fee1']
        );

        $this->expectOneRestCall('Fee', 'GET', $query)
            ->will($this->returnValue($response));

        $this->assertEquals('fee1', $this->sut->getLatestOutstandingFeeForApplication($id));
    }

    /**
     * @group entity_services
     */
    public function testGetLatestFeeForBusReg()
    {
        $id = 3;

        $query = array(
            'busReg' => 3,
            'limit' => 1,
            'sort' => 'invoicedDate',
            'order' => 'DESC'
        );

        $response = array(
            'Results' => ['fee1']
        );

        $this->expectOneRestCall('Fee', 'GET', $query)
            ->will($this->returnValue($response));

        $this->assertEquals('fee1', $this->sut->getLatestFeeForBusReg($id));
    }

    /**
     * @group entity_services
     */
    public function testGetOverview()
    {
        $id = 3;

        $this->expectOneRestCall('Fee', 'GET', $id);

        $this->sut->getOverview($id);
    }

    /**
     * @group entity_services
     */
    public function testGetOrganisation()
    {
        $id = 3;

        $organisation = array('id' => 1);

        $response = array(
            'licence' => array(
                'organisation' => $organisation
            )
        );

        $this->expectOneRestCall('Fee', 'GET', $id)
            ->will($this->returnValue($response));

        $this->assertEquals($organisation, $this->sut->getOrganisation($id));
    }

    /**
     * @group entity_services
     */
    public function testGetOrganisationWithoutOrganisation()
    {
        $id = 3;

        $response = array();

        $this->expectOneRestCall('Fee', 'GET', $id)
            ->will($this->returnValue($response));

        $this->assertEquals(null, $this->sut->getOrganisation($id));
    }

    /**
     * Test get fee by type, statuses and applicationId
     *
     * @group feeEntity
     */
    public function testGetFeeByTypeStatusesAndApplicationId()
    {
        $id = 3;
        $statuses = array(
            FeeEntityService::STATUS_OUTSTANDING,
            FeeEntityService::STATUS_WAIVE_RECOMMENDED
        );
        $query = array(
            'application' => $id,
            'feeStatus' => $statuses,
            'feeType' => 1,
            'limit' => 'all'
        );

        $response = array(
            'Results' => ['fee1']
        );

        $this->expectOneRestCall('Fee', 'GET', $query)
            ->will($this->returnValue($response));

        $this->assertEquals(['fee1'], $this->sut->getFeeByTypeStatusesAndApplicationId(1, $statuses, $id));
    }

    /**
     * Test get fee by type, statuses and applicationId
     *
     * @group feeEntity
     */
    public function testCancelByIds()
    {
        $ids = array(1,2);
        $query = array(
            array(
                'id' => 1,
                'feeStatus' => FeeEntityService::STATUS_CANCELLED,
                '_OPTIONS_' => array('force' => true)
            ),
            array(
                'id' => 2,
                'feeStatus' => FeeEntityService::STATUS_CANCELLED,
                '_OPTIONS_' => array('force' => true)
            ),
            '_OPTIONS_' => array('multiple' => true)
        );
        $this->expectOneRestCall('Fee', 'PUT', $query);
        $this->sut->cancelByIds($ids);
    }

    /**
     * Test get latest fee by type, statuses and applicationId
     *
     * @group feeEntity
     */
    public function testGetLatestFeeByTypeStatusesAndApplicationId()
    {
        $id = 3;
        $statuses = [
            FeeEntityService::STATUS_OUTSTANDING,
            FeeEntityService::STATUS_WAIVE_RECOMMENDED
        ];

        $query = [
            'application' => $id,
            'feeStatus'   => $statuses,
            'feeType'     => 1,
            'sort'        => 'invoicedDate',
            'order'       => 'DESC',
            'limit'       => 1,
        ];

        $response = [
            'Results' => ['fee1']
        ];

        $this->expectOneRestCall('Fee', 'GET', $query)->will($this->returnValue($response));

        $this->assertEquals('fee1', $this->sut->getLatestFeeByTypeStatusesAndApplicationId(1, $statuses, $id));
    }

    /**
     * @group entity_services
     */
    public function testCancelInterimForApplication()
    {
        $query = [
            'application' => 3,
            'feeStatus' => [
                FeeEntityService::STATUS_OUTSTANDING,
                FeeEntityService::STATUS_WAIVE_RECOMMENDED
            ],
            'limit' => 'all'
        ];

        $response = [
            'Results' => [
                [
                    'id' => 10,
                    'feeType' => [
                        'feeType' => FeeTypeDataService::FEE_TYPE_GRANTINT
                    ]
                ], [
                    'id' => 20,
                    'feeType' => [
                        'feeType' => FeeTypeDataService::FEE_TYPE_APP
                    ]
                ]
            ]
        ];

        $this->expectedRestCallInOrder('Fee', 'GET', $query)
            ->will($this->returnValue($response));

        $data = [
            '_OPTIONS_' => ['multiple' => true],
            [
                'id' => 10,
                'feeStatus' => FeeEntityService::STATUS_CANCELLED,
                '_OPTIONS_' => ['force' => true]
            ]
        ];

        $this->expectedRestCallInOrder('Fee', 'PUT', $data);

        $this->sut->cancelInterimForApplication(3);
    }

    public function testGetOutstandingFeesForOrganisation()
    {
        // stub data
        $organisationId = 69;

        $applications = [
            ['id' => 2],
            ['id' => 4],
            ['id' => 6],
        ];

        $licences = [
            ['id' => 3],
            ['id' => 5],
            ['id' => 7],
        ];

        // mocks
        $mockOrganisationEntityService = m::mock();
        $this->sm->setService('Entity\Organisation', $mockOrganisationEntityService);

        // expectations
        $mockOrganisationEntityService
            ->shouldReceive('getLicencesByStatus')
            ->with(
                $organisationId,
                [
                    LicenceEntityService::LICENCE_STATUS_VALID,
                    LicenceEntityService::LICENCE_STATUS_CURTAILED,
                    LicenceEntityService::LICENCE_STATUS_SUSPENDED,
                ]
            )
            ->once()
            ->andReturn($licences);

        $mockOrganisationEntityService
            ->shouldReceive('getAllApplicationsByStatus')
            ->with(
                $organisationId,
                [
                    ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION,
                    ApplicationEntityService::APPLICATION_STATUS_GRANTED,
                ]
            )
            ->once()
            ->andReturn($applications);

        $expectedQuery = array(
            'feeStatus' => FeeEntityService::STATUS_OUTSTANDING,
            [
                'application' => "IN [2,4,6]",
                'licence' => "IN [3,5,7]",
            ],
            'sort'  => 'invoicedDate',
            'order' => 'ASC',
            'limit' => 'all',
        );

        $this->expectOneRestCall('Fee', 'GET', $expectedQuery)
            ->will($this->returnValue('RESPONSE'));

        // assertions
        $this->assertEquals('RESPONSE', $this->sut->getOutstandingFeesForOrganisation($organisationId));
    }

    /**
     * Test getOutstandingFeesForOrganisation method when there are no
     * licences, variations or applications
     */
    public function testGetOutstandingFeesForOrganisationNoLva()
    {
        // stub data
        $organisationId = 69;

        $applications = [];

        $licences = [];

        // mocks
        $mockOrganisationEntityService = m::mock();
        $this->sm->setService('Entity\Organisation', $mockOrganisationEntityService);

        // expectations
        $mockOrganisationEntityService
            ->shouldReceive('getLicencesByStatus')
            ->once()
            ->andReturn($licences);

        $mockOrganisationEntityService
            ->shouldReceive('getAllApplicationsByStatus')
            ->once()
            ->andReturn($applications);

        // assertions
        $this->assertNull($this->sut->getOutstandingFeesForOrganisation($organisationId));
    }
}
