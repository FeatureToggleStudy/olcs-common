<?php

/**
 * Transport Manager Application Entity Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace CommonTest\Service\Entity;

use Common\Service\Entity\TransportManagerApplicationEntityService;

/**
 * Transport Manager Application Entity Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerApplicationEntityServiceTest extends AbstractEntityServiceTestCase
{
    protected $dataBundle =[
        'children' => [
            'application' => [
                'children' => [
                    'status',
                    'licence' => [
                        'children' => [
                            'organisation'
                        ]
                    ]
                ]
            ],
            'transportManager',
            'tmType',
            'operatingCentres',
            'tmApplicationStatus'
        ]
    ];

    protected function setUp()
    {
        $this->sut = new TransportManagerApplicationEntityService();

        parent::setUp();
    }

    /**
     * Test get transport manager appplications
     *
     * @group transportManagerApplication
     */
    public function testGetTransportManagerApplications()
    {
        $id = 1;
        $returnValue = [
            'Results' => [
                [
                    'application' => [
                        'status' => [
                            'id' => 'apsts_consideration'
                        ]
                    ],
                    'operatingCentres' => [
                        'one',
                        'two'
                    ],
                    'tmApplicationStatus' => [
                        'id' => 'foo'
                    ]
                ],
                [
                    'application' => [
                        'status' => [
                            'id' => 'foo'
                        ]
                    ],
                    'operatingCentres' => [
                        'one',
                        'two',
                        'three'
                    ],
                    'tmApplicationStatus' => [
                        'id' => 'bar'
                    ]
                ],
            ]
        ];
        $status = [
            'apsts_consideration',
            'apsts_not_submitted',
            'apsts_granted'
        ];

        $expectedValue = [
            [
                'application' => [
                    'status' => [
                        'id' => 'apsts_consideration'
                    ]
                ],
                'operatingCentres' => [
                    'one',
                    'two'
                ],
                'tmApplicationStatus' => [
                    'id' => 'foo'
                ],
                'ocCount' => 2
            ]
        ];

        $query = [
            'transportManager' => $id,
            'action' => '!=D',
            'limit' => 'all'
        ];

        $this->expectOneRestCall('TransportManagerApplication', 'GET', $query, $this->dataBundle)
            ->will($this->returnValue($returnValue));

        $this->assertEquals($expectedValue, $this->sut->getTransportManagerApplications($id, $status));
    }

    /**
     * Test get transport manager appplication
     *
     * @group transportManagerApplication
     */
    public function testGetTransportManagerApplication()
    {
        $this->expectOneRestCall('TransportManagerApplication', 'GET', 1, $this->dataBundle)
            ->will($this->returnValue('RESPONSE'));

        $this->assertEquals('RESPONSE', $this->sut->getTransportManagerApplication(1));
    }

    public function testGetByApplication()
    {
        $id = 3;

        $this->expectOneRestCall('TransportManagerApplication', 'GET', ['application' => $id])
            ->will($this->returnValue('RESPONSE'));

        $this->assertEquals('RESPONSE', $this->sut->getByApplication($id));
    }

    public function testGetGrantDataForApplication()
    {
        $applicationId = 123;

        $query = ['application' => $applicationId, 'limit' => 'all'];

        $this->expectOneRestCall('TransportManagerApplication', 'GET', $query)
            ->will($this->returnValue(['Results' => 'RESPONSE']));

        $this->assertEquals('RESPONSE', $this->sut->getGrantDataForApplication($applicationId));
    }

    public function testDeleteForApplication()
    {
        $applicationId = 123;

        $query = ['application' => $applicationId];

        $this->expectOneRestCall('TransportManagerApplication', 'DELETE', $query)
            ->will($this->returnValue('RESPONSE'));

        $this->assertEquals('RESPONSE', $this->sut->deleteForApplication($applicationId));
    }

    public function testDeleteWithOneId()
    {
        $this->expectOneRestCall('TransportManagerApplication', 'DELETE', ['id' => 412])
            ->will($this->returnValue('RESPONSE'));

        $this->sut->delete(412);

    }

    public function testDeleteWithMultipleIds()
    {
        $this->expectedRestCallInOrder('TransportManagerApplication', 'DELETE', ['id' => 12])
            ->will($this->returnValue('RESPONSE'));
        $this->expectedRestCallInOrder('TransportManagerApplication', 'DELETE', ['id' => 64])
            ->will($this->returnValue('RESPONSE'));
        $this->expectedRestCallInOrder('TransportManagerApplication', 'DELETE', ['id' => 345])
            ->will($this->returnValue('RESPONSE'));

        $this->sut->delete([12,64,345]);
    }

    public function testGetByApplicationWithHomeContactDetails()
    {
        $this->expectOneRestCall('TransportManagerApplication', 'GET', ['application' => 821, 'limit' => 'all'])
            ->will($this->returnValue('RESPONSE'));

        $this->assertEquals('RESPONSE', $this->sut->getByApplicationWithHomeContactDetails(821));
    }

    public function testGetByApplicationTransportManager()
    {
        $this->expectOneRestCall(
            'TransportManagerApplication',
            'GET',
            ['application' => 821, 'transportManager' => 55, 'limit' => 'all']
        )->will($this->returnValue('RESPONSE'));

        $this->assertEquals('RESPONSE', $this->sut->getByApplicationTransportManager(821, 55));
    }

    public function testGetTransportManagerDetails()
    {
        $this->expectOneRestCall('TransportManagerApplication', 'GET', 111)
            ->will($this->returnValue('RESPONSE'));

        $this->assertEquals('RESPONSE', $this->sut->getTransportManagerDetails(111));
    }

    public function testGetTransportManagerId()
    {
        $data = [
            'transportManager' => [
                'id' => 222
            ]
        ];

        $this->expectOneRestCall('TransportManagerApplication', 'GET', 111)
            ->will($this->returnValue($data));

        $this->assertEquals(222, $this->sut->getTransportManagerId(111));
    }

    public function testUpdateStatus()
    {
        $this->expectOneRestCall(
            'TransportManagerApplication',
            'PUT',
            ['id' => 34324, 'tmApplicationStatus' => 'STATUS', '_OPTIONS_' => ['force' => true]]
        );

        $this->sut->updateStatus(34324, 'STATUS');
    }

    public function testGetContactApplicationDetails()
    {
        $this->expectOneRestCall('TransportManagerApplication', 'GET', 23)
            ->will($this->returnValue('RESPONSE'));

        $this->assertEquals('RESPONSE', $this->sut->getContactApplicationDetails(23));
    }
}
