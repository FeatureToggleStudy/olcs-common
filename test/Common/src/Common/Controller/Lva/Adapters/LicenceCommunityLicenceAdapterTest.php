<?php

/**
 * Licence Community Licence Adapter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace CommonTest\Controller\Lva\Adapters;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Controller\Lva\Adapters\LicenceCommunityLicenceAdapter;

/**
 * Licence Community Licence Adapter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceCommunityLicenceAdapterTest extends MockeryTestCase
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
        $this->sut = new LicenceCommunityLicenceAdapter();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * Test add community licences with issue specific numbers
     *
     * @group licenceCommunityLicenceAdapter
     */
    public function testAddCommunityLicencesWithIssueNos()
    {
        $licenceId = 1;

        $mockDateHelper = m::mock()
            ->shouldReceive('getDate')
            ->andReturn('2015-01-01')
            ->getMock();

        $this->sm->setService('Helper\Date', $mockDateHelper);

        $data = [
            'specifiedDate' => '2015-01-01',
            'status' => 'cl_sts_active'
        ];

        $this->sm->setService(
            'Entity\CommunityLic',
            m::mock()
                ->shouldReceive('addCommunityLicencesWithIssueNos')
                ->with($data, $licenceId, [5, 6])
                ->andReturn(
                    [
                        'id' => [1, 2, 3]
                    ]
                )
            ->getMock()
        );

        $this->sm->setService(
            'Helper\CommunityLicenceDocument',
            m::mock()
            ->shouldReceive('generateBatch')
            ->with(1, [1, 2, 3])
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertEquals(
            'foo',
            $this->sut->addCommunityLicencesWithIssueNos($licenceId, [5, 6])
        );
    }
}
