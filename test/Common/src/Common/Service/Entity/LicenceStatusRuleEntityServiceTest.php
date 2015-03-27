<?php

namespace CommonTest\Service\Entity;

use Common\Service\Entity\LicenceStatusRuleEntityService;

/**
 * Class LicenceStatusRuleEntityServiceTest
 * @package CommonTest\Service\Entity
 */
class LicenceStatusRuleEntityServiceTest extends AbstractEntityServiceTestCase
{
    protected $sut = null;

    protected function setUp()
    {
        $this->sut = new LicenceStatusRuleEntityService();

        parent::setUp();
    }

    public function testCreateStatusForLicence()
    {
        $licenceId = 99;

        $this->expectOneRestCall(
            'LicenceStatusRule',
            'POST',
            array(
                'licence' => $licenceId,
                'licenceStatus' => null,
                'startDate' => null,
                'endDate' => null,
                'startProcessedDate' => null,
                'endProcessedDate' => null,
            )
        )->will($this->returnValue('RESPONSE'));

        $this->assertEquals('RESPONSE', $this->sut->createStatusForLicence($licenceId));
    }

    public function testGetStatusesForLicence()
    {
        $licenceId = 99;

        $this->expectOneRestCall(
            'LicenceStatusRule',
            'GET',
            array(
                'licence' => $licenceId,
                'licenceStatus' => array()
            )
        )->will($this->returnValue('RESPONSE'));

        $this->assertEquals(
            'RESPONSE',
            $this->sut->getStatusesForLicence(
                array(
                    'query' => array(
                        'licence' => $licenceId
                    )
                )
            )
        );
    }

    public function testRemoveStatusesForLicence()
    {
        $licenceStatusId = 101;

        $this->expectOneRestCall(
            'LicenceStatusRule',
            'DELETE',
            array(
                'id' => $licenceStatusId
            )
        )->will($this->returnValue('RESPONSE'));

        $this->assertEquals(null, $this->sut->removeStatusesForLicence($licenceStatusId));
    }

    public function testGetPendingChangesForLicence()
    {
        $licenceId = 99;

        $this->expectOneRestCall(
            'LicenceStatusRule',
            'GET',
            array(
                'licence' => $licenceId,
                'deletedDate' => 'NULL',
                'endProcessedDate' => 'NULL',
                'licenceStatus' => array(), // this gets added as default arg
            )
        )->will($this->returnValue(['Count' => 1, 'Results' => 'RESPONSE']));

        $this->assertEquals('RESPONSE', $this->sut->getPendingChangesForLicence($licenceId));
    }

    public function testGetLicencesToRevokeCurtailSuspend()
    {
        $mockDate = \Mockery::mock('StdClass');
        $mockDate->shouldReceive('getDate')->andReturn('2015-03-25');
        $this->sm->setService('Helper\Date', $mockDate);

        $this->restHelper->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(['Results' => [1,2,3]]));

        $results = $this->sut->getLicencesToRevokeCurtailSuspend();
        $this->assertEquals([1,2,3], $results);
    }

    public function testGetLicencesToValid()
    {
        $mockDate = \Mockery::mock('StdClass');
        $mockDate->shouldReceive('getDate')->andReturn('2015-03-25');
        $this->sm->setService('Helper\Date', $mockDate);

        $this->restHelper->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(['Results' => [1,2,3]]));

        $results = $this->sut->getLicencesToValid();
        $this->assertEquals([1,2,3], $results);
    }
}
