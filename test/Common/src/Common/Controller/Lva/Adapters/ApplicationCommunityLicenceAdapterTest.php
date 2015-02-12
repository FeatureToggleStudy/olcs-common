<?php

/**
 * Application Community Licence Adapter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace CommonTest\Controller\Lva\Adapters;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Controller\Lva\Adapters\ApplicationCommunityLicenceAdapter;

/**
 * Application Community Licence Adapter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationCommunityLicenceAdapterTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $this->sm->setAllowOverride(true);
        $this->sut = new ApplicationCommunityLicenceAdapter();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * Test add office copy
     * 
     * @group applicationCommunityLicenceAdapter
     */
    public function testAddOfficeCopy()
    {
        $licenceId = 1;

        $mockDateHelper = m::mock()
            ->shouldReceive('getDate')
            ->andReturn('2015-01-01')
            ->getMock();
        $this->sm->setService('Helper\Date', $mockDateHelper);

        $data = [
            'status' => 'cl_sts_pending'
        ];

        $mockAddOfficeCopy = m::mock()
            ->shouldReceive('addOfficeCopy')
            ->with($data, $licenceId)
            ->getMock();
        $this->sm->setService('Entity\CommunityLic', $mockAddOfficeCopy);

        $this->sut->addOfficeCopy($licenceId);
    }

    /**
     * Test get total authority
     * 
     * @group applicationCommunityLicenceAdapter
     */
    public function testGetTotalAuthority()
    {
        $id = 1;

        $mockApplicationService = m::mock()
            ->shouldReceive('getById')
            ->with($id)
            ->andReturn(['totAuthVehicles' => 5])
            ->getMock();
        $this->sm->setService('Entity\Application', $mockApplicationService);

        $this->assertEquals($this->sut->getTotalAuthority($id), 5);
    }

    /**
     * Test add commuinty licences
     * 
     * @group applicationCommunityLicenceAdapter
     */
    public function testAddCommunityLicences()
    {
        $licenceId = 1;

        $mockDateHelper = m::mock()
            ->shouldReceive('getDate')
            ->andReturn('2015-01-01')
            ->getMock();
        $this->sm->setService('Helper\Date', $mockDateHelper);

        $data = [
            'status' => 'cl_sts_pending'
        ];

        $mockAddCommunityLicences = m::mock()
            ->shouldReceive('addCommunityLicences')
            ->with($data, $licenceId, 2)
            ->getMock();
        $this->sm->setService('Entity\CommunityLic', $mockAddCommunityLicences);

        $this->sut->addCommunityLicences($licenceId, 2);
    }
}