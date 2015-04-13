<?php

/**
 * Company Subsidiary Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace CommonTest\BusinessService\Service\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\BusinessService\Service\Lva\CompanySubsidiary;
use Common\BusinessService\Response;
use CommonTest\Bootstrap;

/**
 * Company Subsidiary Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CompanySubsidiaryTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    protected $bsm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();

        $this->sut = new CompanySubsidiary();
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setBusinessServiceManager($this->bsm);
    }

    public function testProcessCreateWithFail()
    {
        // Params
        $params = [
            'id' => null,
            'data' => [
                'name' => 'Foo ltd'
            ],
            'licenceId' => 222,
        ];

        $expectedTask = [
            'action' => 'added',
            'name' => 'Foo ltd',
            'licenceId' => 222
        ];

        // Mocks
        $mockResponse = m::mock();
        $mockChangeTask = m::mock('\Common\BusinessService\BusinessServiceInterface');

        $this->bsm->setService('Lva\CompanySubsidiaryChangeTask', $mockChangeTask);

        // Expectations
        $mockChangeTask->shouldReceive('process')
            ->with($expectedTask)
            ->andReturn($mockResponse);

        $mockResponse->shouldReceive('isOk')
            ->andReturn(false);

        $response = $this->sut->process($params);

        $this->assertSame($mockResponse, $response);
    }

    public function testProcessCreateWithSuccess()
    {
        // Params
        $params = [
            'id' => null,
            'data' => [
                'name' => 'Foo ltd'
            ],
            'licenceId' => 222,
        ];

        $expectedTask = [
            'action' => 'added',
            'name' => 'Foo ltd',
            'licenceId' => 222
        ];

        // Mocks
        $mockResponse = m::mock();
        $mockChangeTask = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $mockEntity = m::mock();

        $this->bsm->setService('Lva\CompanySubsidiaryChangeTask', $mockChangeTask);
        $this->sm->setService('Entity\CompanySubsidiary', $mockEntity);

        // Expectations
        $mockChangeTask->shouldReceive('process')
            ->with($expectedTask)
            ->andReturn($mockResponse);

        $mockResponse->shouldReceive('isOk')
            ->andReturn(true);

        $mockEntity->shouldReceive('save')
            ->with(
                [
                    'name' => 'Foo ltd',
                    'licence' => 222
                ]
            );

        $response = $this->sut->process($params);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals(Response::TYPE_SUCCESS, $response->getType());
        $this->assertEquals([], $response->getData());
    }

    public function testProcessUpdateWithFail()
    {
        // Params
        $params = [
            'id' => 111,
            'data' => [
                'name' => 'Foo ltd'
            ],
            'licenceId' => 222,
        ];

        $expectedTask = [
            'action' => 'updated',
            'name' => 'Foo ltd',
            'licenceId' => 222
        ];

        // Mocks
        $mockResponse = m::mock();
        $mockOrganisation = m::mock();
        $mockChangeTask = m::mock('\Common\BusinessService\BusinessServiceInterface');

        $this->sm->setService('Entity\Organisation', $mockOrganisation);
        $this->bsm->setService('Lva\CompanySubsidiaryChangeTask', $mockChangeTask);

        // Expectations
        $mockOrganisation->shouldReceive('hasChangedSubsidiaryCompany')
            ->with(111, ['name' => 'Foo ltd', 'licence' => 222])
            ->andReturn(true);

        $mockChangeTask->shouldReceive('process')
            ->with($expectedTask)
            ->andReturn($mockResponse);

        $mockResponse->shouldReceive('isOk')
            ->andReturn(false);

        $response = $this->sut->process($params);

        $this->assertSame($mockResponse, $response);
    }

    public function testProcessEditWithSuccess()
    {
        // Params
        $params = [
            'id' => 111,
            'data' => [
                'name' => 'Foo ltd'
            ],
            'licenceId' => 222,
        ];

        $expectedTask = [
            'action' => 'updated',
            'name' => 'Foo ltd',
            'licenceId' => 222
        ];

        // Mocks
        $mockResponse = m::mock();
        $mockOrganisation = m::mock();
        $mockChangeTask = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $mockEntity = m::mock();

        $this->bsm->setService('Lva\CompanySubsidiaryChangeTask', $mockChangeTask);
        $this->sm->setService('Entity\Organisation', $mockOrganisation);
        $this->sm->setService('Entity\CompanySubsidiary', $mockEntity);

        // Expectations
        $mockOrganisation->shouldReceive('hasChangedSubsidiaryCompany')
            ->with(111, ['name' => 'Foo ltd', 'licence' => 222])
            ->andReturn(false);

        $mockChangeTask->shouldReceive('process')
            ->with($expectedTask)
            ->andReturn($mockResponse);

        $mockResponse->shouldReceive('getType')
            ->andReturn(Response::TYPE_SUCCESS);

        $mockEntity->shouldReceive('save')
            ->with(
                [
                    'name' => 'Foo ltd',
                    'licence' => 222
                ]
            );

        $response = $this->sut->process($params);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals(Response::TYPE_SUCCESS, $response->getType());
        $this->assertEquals([], $response->getData());
    }
}