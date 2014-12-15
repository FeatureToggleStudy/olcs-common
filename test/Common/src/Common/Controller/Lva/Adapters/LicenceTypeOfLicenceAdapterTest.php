<?php

/**
 * Licence Type Of Licence Adapter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace CommonTest\Controller\Lva\Adapters;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Common\Controller\Lva\Adapters\LicenceTypeOfLicenceAdapter;
use CommonTest\Bootstrap;
use Common\Service\Entity\LicenceEntityService;

/**
 * Licence Type Of Licence Adapter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceTypeOfLicenceAdapterTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    protected function setUp()
    {
        $this->sut = new LicenceTypeOfLicenceAdapter();

        $this->sm = Bootstrap::getServiceManager();
        $this->controller = m::mock('\Zend\Mvc\Controller\AbstractController');

        $this->sut->setServiceLocator($this->sm);
        $this->sut->setController($this->controller);
    }

    /**
     * @group licence_type_of_licence_adapter
     */
    public function testDoesChangeRequireConfirmation()
    {
        $postData = array('licence-type' => 'A');

        $currentData = array('licenceType' => 'B');

        $this->assertTrue($this->sut->doesChangeRequireConfirmation($postData, $currentData));
    }

    /**
     * @group licence_type_of_licence_adapter
     */
    public function testDoesChangeRequireConfirmationFalse()
    {
        $postData = array('licence-type' => 'B');

        $currentData = array('licenceType' => 'B');

        $this->assertFalse($this->sut->doesChangeRequireConfirmation($postData, $currentData));
    }

    /**
     * @group licence_type_of_licence_adapter
     * @dataProvider providerShouldDisableLicenceType
     */
    public function testShouldDisableLicenceType($id, $applicationType, $stubbedData, $expected)
    {
        $mockAppEntityService = m::mock();
        $mockAppEntityService->shouldReceive('getTypeOfLicenceData')
            ->with($id)
            ->andReturn($stubbedData);

        $this->sm->setService('Entity\Licence', $mockAppEntityService);

        $this->assertEquals($expected, $this->sut->shouldDisableLicenceType($id, $applicationType));
    }

    /**
     * @group licence_type_of_licence_adapter
     */
    public function testAlterForm()
    {
        $id = 4;
        $applicationType = 'internal';
        $stubbedData = [
            'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE
        ];

        $mockAppEntityService = m::mock();
        $mockAppEntityService->shouldReceive('getTypeOfLicenceData')
            ->with($id)
            ->andReturn($stubbedData);

        $form = m::mock('\Zend\Form\Form');
        $mockFormHelper = m::mock();
        $tolFieldset = m::mock();

        $operatorLocation = m::mock();
        $operatorLocation->shouldReceive('setLabel')
            ->with('operator-location');

        $operatorType = m::mock();
        $operatorType->shouldReceive('setLabel')
            ->with('operator-type');

        $tolFieldset->shouldReceive('get')
            ->with('operator-location')
            ->andReturn($operatorLocation)
            ->shouldReceive('get')
            ->with('operator-type')
            ->andReturn($operatorType)
            ->shouldReceive('get')
            ->with('licence-type')
            ->andReturn(
                m::mock()->shouldReceive('setLabel')
                ->with('licence-type')->getMock()
            );

        $form->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()->shouldReceive('get')
                ->with('save')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setLabel')
                    ->with('save')->getMock()
                )->getMock()
            )
            ->shouldReceive('get')
            ->with('type-of-licence')
            ->andReturn($tolFieldset);

        $mockFormHelper->shouldReceive('lockElement')
            ->with($operatorLocation, 'operator-location-lock-message')
            ->shouldReceive('lockElement')
            ->with($operatorType, 'operator-type-lock-message')
            ->shouldReceive('disableElement')
            ->with($form, 'type-of-licence->operator-location')
            ->shouldReceive('disableElement')
            ->with($form, 'type-of-licence->operator-type');

        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Entity\Licence', $mockAppEntityService);

        $this->assertSame($form, $this->sut->alterForm($form, $id, $applicationType));
    }

    public function providerShouldDisableLicenceType()
    {
        return [
            [
                1,
                'internal',
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE
                ],
                false
            ],
            [
                1,
                'internal',
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_PSV,
                    'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL
                ],
                false
            ],
            [
                1,
                'internal',
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_PSV,
                    'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL
                ],
                false
            ],
            [
                1,
                'internal',
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_PSV,
                    'licenceType' => LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED
                ],
                true
            ],
            [
                1,
                'external',
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_PSV,
                    'licenceType' => LicenceEntityService::LICENCE_TYPE_RESTRICTED
                ],
                true
            ],
            [
                1,
                'internal',
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_PSV,
                    'licenceType' => LicenceEntityService::LICENCE_TYPE_RESTRICTED
                ],
                false
            ]
        ];
    }
}
