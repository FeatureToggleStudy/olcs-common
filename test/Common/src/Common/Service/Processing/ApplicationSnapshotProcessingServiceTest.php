<?php

/**
 * Application Snapshot Processing Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace CommonTest\Service\Processing;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Processing\ApplicationSnapshotProcessingService;
use CommonTest\Bootstrap;
use Common\Service\Entity\ApplicationEntityService;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Data\CategoryDataService;

/**
 * Application Snapshot Processing Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationSnapshotProcessingServiceTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    public function setUp()
    {
        $this->sut = new ApplicationSnapshotProcessingService();

        $this->sm = Bootstrap::getServiceManager();

        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * @dataProvider providerNewApplication
     */
    public function testStoreSnapshotNewApplicationOnGrant($stubbedTol, $code)
    {
        // Params
        $applicationId = 123;
        $event = ApplicationSnapshotProcessingService::ON_GRANT;

        // Expected data
        $expectedDocumentData = [
            'identifier' => 'ABCDEF',
            'application' => 123,
            'licence' => 321,
            'category' => CategoryDataService::CATEGORY_APPLICATION,
            'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_FORMS_ASSISTED_DIGITAL,
            'filename' => $code . ' Application Snapshot Grant.html',
            'issuedDate' => '2015-01-01 10:10:10',
            'description' => $code . ' Application Snapshot (at grant/valid)',
            'isExternal' => false,
            'isScan' => false
        ];

        // Mocks
        $mockApplicationEntity = m::mock();
        $mockControllerPluginManager = m::mock();
        $mockApplication = m::mock();
        $mockMvcEvent = m::mock();
        $mockRouteMatch = m::mock();
        $mockAdapter = m::mock();
        $mockControllerManager = m::mock();
        $mockController = m::mock();
        $mockView = m::mock();
        $mockViewRenderer = m::mock();
        $mockFileUploader = m::mock();
        $mockFile = m::mock();
        $mockDocumentEntity = m::mock();
        $mockDate = m::mock();

        $this->sm->setService('Entity\Application', $mockApplicationEntity);
        $this->sm->setService('ControllerPluginManager', $mockControllerPluginManager);
        $this->sm->setService('Application', $mockApplication);
        $this->sm->setService('ApplicationReviewAdapter', $mockAdapter);
        $this->sm->setService('ControllerManager', $mockControllerManager);
        $this->sm->setService('ViewRenderer', $mockViewRenderer);
        $this->sm->setService('FileUploader', $mockFileUploader);
        $this->sm->setService('Entity\Document', $mockDocumentEntity);
        $this->sm->setService('Helper\Date', $mockDate);

        // Expectations
        $mockApplicationEntity->shouldReceive('getApplicationType')
            ->with(123)
            ->andReturn(ApplicationEntityService::APPLICATION_TYPE_NEW)
            ->shouldReceive('getLicenceIdForApplication')
            ->with(123)
            ->andReturn(321)
            ->shouldReceive('getTypeOfLicenceData')
            ->with(123)
            ->andReturn($stubbedTol);

        $mockApplication->shouldReceive('getMvcEvent')
            ->andReturn($mockMvcEvent);

        $mockMvcEvent->shouldReceive('getRouteMatch')
            ->andReturn($mockRouteMatch);

        $mockRouteMatch->shouldReceive('getParam')
            ->with('application')
            ->andReturn($applicationId);

        $mockControllerManager->shouldReceive('get')
            ->with('LvaApplication/Review')
            ->andReturn($mockController);

        $mockController->shouldReceive('setPluginManager')
            ->with($mockControllerPluginManager)
            ->shouldReceive('setEvent')
            ->with($mockMvcEvent)
            ->shouldReceive('setAdapter')
            ->with($mockAdapter)
            ->shouldReceive('indexAction')
            ->andReturn($mockView);

        $mockViewRenderer->shouldReceive('render')
            ->with($mockView)
            ->andReturn('HTML');

        $mockFileUploader->shouldReceive('getUploader')
            ->andReturnSelf()
            ->shouldReceive('setFile')
            ->with(['content' => 'HTML'])
            ->shouldReceive('upload')
            ->andReturn($mockFile);

        $mockDate->shouldReceive('getDate')
            ->with('Y-m-d H:i:s')
            ->andReturn('2015-01-01 10:10:10');

        $mockFile->shouldReceive('getIdentifier')
            ->andReturn('ABCDEF');

        $mockDocumentEntity->shouldReceive('save')
            ->with($expectedDocumentData);

        $this->sut->storeSnapshot($applicationId, $event);
    }

    /**
     * @dataProvider providerNewApplication
     */
    public function testStoreSnapshotNewApplicationOnSubmit($stubbedTol, $code)
    {
        // Params
        $applicationId = 123;
        $event = ApplicationSnapshotProcessingService::ON_SUBMIT;

        // Expected data
        $expectedDocumentData = [
            'identifier' => 'ABCDEF',
            'application' => 123,
            'licence' => 321,
            'category' => CategoryDataService::CATEGORY_APPLICATION,
            'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_FORMS_DIGITAL,
            'filename' => $code . ' Application Snapshot Submit.html',
            'issuedDate' => '2015-01-01 10:10:10',
            'description' => $code . ' Application Snapshot (at submission)',
            'isExternal' => true,
            'isScan' => false
        ];

        // Mocks
        $mockApplicationEntity = m::mock();
        $mockControllerPluginManager = m::mock();
        $mockApplication = m::mock();
        $mockMvcEvent = m::mock();
        $mockRouteMatch = m::mock();
        $mockAdapter = m::mock();
        $mockControllerManager = m::mock();
        $mockController = m::mock();
        $mockView = m::mock();
        $mockViewRenderer = m::mock();
        $mockFileUploader = m::mock();
        $mockFile = m::mock();
        $mockDocumentEntity = m::mock();
        $mockDate = m::mock();

        $this->sm->setService('Entity\Application', $mockApplicationEntity);
        $this->sm->setService('ControllerPluginManager', $mockControllerPluginManager);
        $this->sm->setService('Application', $mockApplication);
        $this->sm->setService('ApplicationReviewAdapter', $mockAdapter);
        $this->sm->setService('ControllerManager', $mockControllerManager);
        $this->sm->setService('ViewRenderer', $mockViewRenderer);
        $this->sm->setService('FileUploader', $mockFileUploader);
        $this->sm->setService('Entity\Document', $mockDocumentEntity);
        $this->sm->setService('Helper\Date', $mockDate);

        // Expectations
        $mockApplicationEntity->shouldReceive('getApplicationType')
            ->with(123)
            ->andReturn(ApplicationEntityService::APPLICATION_TYPE_NEW)
            ->shouldReceive('getLicenceIdForApplication')
            ->with(123)
            ->andReturn(321)
            ->shouldReceive('getTypeOfLicenceData')
            ->with(123)
            ->andReturn($stubbedTol);

        $mockApplication->shouldReceive('getMvcEvent')
            ->andReturn($mockMvcEvent);

        $mockMvcEvent->shouldReceive('getRouteMatch')
            ->andReturn($mockRouteMatch);

        $mockRouteMatch
            ->shouldReceive('getParam')
                ->with('application')
                ->andReturn(null)
            ->shouldReceive('setParam')
                ->with('application', $applicationId);

        $mockControllerManager->shouldReceive('get')
            ->with('LvaApplication/Review')
            ->andReturn($mockController);

        $mockController->shouldReceive('setPluginManager')
            ->with($mockControllerPluginManager)
            ->shouldReceive('setEvent')
            ->with($mockMvcEvent)
            ->shouldReceive('setAdapter')
            ->with($mockAdapter)
            ->shouldReceive('indexAction')
            ->andReturn($mockView);

        $mockViewRenderer->shouldReceive('render')
            ->with($mockView)
            ->andReturn('HTML');

        $mockFileUploader->shouldReceive('getUploader')
            ->andReturnSelf()
            ->shouldReceive('setFile')
            ->with(['content' => 'HTML'])
            ->shouldReceive('upload')
            ->andReturn($mockFile);

        $mockDate->shouldReceive('getDate')
            ->with('Y-m-d H:i:s')
            ->andReturn('2015-01-01 10:10:10');

        $mockFile->shouldReceive('getIdentifier')
            ->andReturn('ABCDEF');

        $mockDocumentEntity->shouldReceive('save')
            ->with($expectedDocumentData);

        $this->sut->storeSnapshot($applicationId, $event);
    }

    /**
     * @dataProvider providerVariation
     */
    public function testStoreSnapshotVariationOnGrant($stubbedTol, $code, $isUpgrade)
    {
        // Params
        $applicationId = 123;
        $event = ApplicationSnapshotProcessingService::ON_GRANT;

        // Expected data
        $expectedDocumentData = [
            'identifier' => 'ABCDEF',
            'application' => 123,
            'licence' => 321,
            'category' => CategoryDataService::CATEGORY_APPLICATION,
            'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_FORMS_ASSISTED_DIGITAL,
            'filename' => $code . ' Application Snapshot Grant.html',
            'issuedDate' => '2015-01-01 10:10:10',
            'description' => $code . ' Application Snapshot (at grant/valid)',
            'isExternal' => false,
            'isScan' => false
        ];

        // Mocks
        $mockApplicationEntity = m::mock();
        $mockControllerPluginManager = m::mock();
        $mockApplication = m::mock();
        $mockMvcEvent = m::mock();
        $mockRouteMatch = m::mock();
        $mockAdapter = m::mock();
        $mockControllerManager = m::mock();
        $mockController = m::mock();
        $mockView = m::mock();
        $mockViewRenderer = m::mock();
        $mockFileUploader = m::mock();
        $mockFile = m::mock();
        $mockDocumentEntity = m::mock();
        $mockDate = m::mock();
        $mockVariationSection = m::mock();

        $this->sm->setService('Entity\Application', $mockApplicationEntity);
        $this->sm->setService('ControllerPluginManager', $mockControllerPluginManager);
        $this->sm->setService('Application', $mockApplication);
        $this->sm->setService('VariationReviewAdapter', $mockAdapter);
        $this->sm->setService('ControllerManager', $mockControllerManager);
        $this->sm->setService('ViewRenderer', $mockViewRenderer);
        $this->sm->setService('FileUploader', $mockFileUploader);
        $this->sm->setService('Entity\Document', $mockDocumentEntity);
        $this->sm->setService('Helper\Date', $mockDate);
        $this->sm->setService('Processing\VariationSection', $mockVariationSection);

        // Expectations
        $mockApplicationEntity->shouldReceive('getApplicationType')
            ->with(123)
            ->andReturn(ApplicationEntityService::APPLICATION_TYPE_VARIATION)
            ->shouldReceive('getLicenceIdForApplication')
            ->with(123)
            ->andReturn(321)
            ->shouldReceive('getTypeOfLicenceData')
            ->with(123)
            ->andReturn($stubbedTol);

        $mockApplication->shouldReceive('getMvcEvent')
            ->andReturn($mockMvcEvent);

        $mockMvcEvent->shouldReceive('getRouteMatch')
            ->andReturn($mockRouteMatch);

        $mockRouteMatch->shouldReceive('getParam')
            ->with('application')
            ->andReturn($applicationId);

        $mockControllerManager->shouldReceive('get')
            ->with('LvaVariation/Review')
            ->andReturn($mockController);

        $mockController->shouldReceive('setPluginManager')
            ->with($mockControllerPluginManager)
            ->shouldReceive('setEvent')
            ->with($mockMvcEvent)
            ->shouldReceive('setAdapter')
            ->with($mockAdapter)
            ->shouldReceive('indexAction')
            ->andReturn($mockView);

        $mockViewRenderer->shouldReceive('render')
            ->with($mockView)
            ->andReturn('HTML');

        $mockFileUploader->shouldReceive('getUploader')
            ->andReturnSelf()
            ->shouldReceive('setFile')
            ->with(['content' => 'HTML'])
            ->shouldReceive('upload')
            ->andReturn($mockFile);

        $mockDate->shouldReceive('getDate')
            ->with('Y-m-d H:i:s')
            ->andReturn('2015-01-01 10:10:10');

        $mockFile->shouldReceive('getIdentifier')
            ->andReturn('ABCDEF');

        $mockDocumentEntity->shouldReceive('save')
            ->with($expectedDocumentData);

        $mockVariationSection->shouldReceive('isRealUpgrade')
            ->with(123)
            ->andReturn($isUpgrade);

        $this->sut->storeSnapshot($applicationId, $event);
    }

    public function providerNewApplication()
    {
        return [
            [
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE
                ],
                'GV79'
            ],
            [
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_PSV,
                    'licenceType' => LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED
                ],
                'PSV356'
            ],
            [
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_PSV,
                    'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL
                ],
                'PSV421'
            ]
        ];
    }

    public function providerVariation()
    {
        return [
            [
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE
                ],
                'GV80A',
                true
            ],
            [
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE
                ],
                'GV81',
                false
            ],
            [
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_PSV
                ],
                'PSV431A',
                true
            ],
            [
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_PSV
                ],
                'PSV431',
                false
            ]
        ];
    }

    public function dataProviderTestStoreSnapshot()
    {
        return [
            [
                ApplicationSnapshotProcessingService::ON_REFUSE,
                0,
                false,
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL
                ],
                'GV79 Application Snapshot Refuse.html',
                'GV79 Application Snapshot (at refuse)',
            ],
            [
                ApplicationSnapshotProcessingService::ON_REFUSE,
                0,
                false,
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_PSV,
                    'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL
                ],
                'PSV421 Application Snapshot Refuse.html',
                'PSV421 Application Snapshot (at refuse)',
            ],
            [
                ApplicationSnapshotProcessingService::ON_REFUSE,
                0,
                false,
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_PSV,
                    'licenceType' => LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED
                ],
                'PSV356 Application Snapshot Refuse.html',
                'PSV356 Application Snapshot (at refuse)',
            ],
            [
                ApplicationSnapshotProcessingService::ON_REFUSE,
                1,
                false,
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licenceType' => LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED
                ],
                'GV81 Application Snapshot Refuse.html',
                'GV81 Application Snapshot (at refuse)',
            ],
            [
                ApplicationSnapshotProcessingService::ON_REFUSE,
                1,
                true,
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licenceType' => LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED
                ],
                'GV80A Application Snapshot Refuse.html',
                'GV80A Application Snapshot (at refuse)',
            ],
            [
                ApplicationSnapshotProcessingService::ON_REFUSE,
                1,
                false,
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_PSV,
                    'licenceType' => LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED
                ],
                'PSV431 Application Snapshot Refuse.html',
                'PSV431 Application Snapshot (at refuse)',
            ],
            [
                ApplicationSnapshotProcessingService::ON_REFUSE,
                1,
                true,
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_PSV,
                    'licenceType' => LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED
                ],
                'PSV431A Application Snapshot Refuse.html',
                'PSV431A Application Snapshot (at refuse)',
            ],
            [
                ApplicationSnapshotProcessingService::ON_WITHDRAW,
                1,
                true,
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_PSV,
                    'licenceType' => LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED
                ],
                'PSV431A Application Snapshot Withdraw.html',
                'PSV431A Application Snapshot (at withdraw)',
            ],
            [
                ApplicationSnapshotProcessingService::ON_NTU,
                0,
                false,
                [
                    'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_PSV,
                    'licenceType' => LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED
                ],
                'PSV356 Application Snapshot NTU.html',
                'PSV356 Application Snapshot (at NTU)',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestStoreSnapshot
     *
     * @param int    $event
     * @param int    $appType
     * @param bool   $isUpgrade
     * @param array  $licenceData
     * @param string $filename
     * @param string $description
     */
    public function testStoreSnapshot($event, $appType, $isUpgrade, $licenceData, $filename, $description)
    {
        // Params
        $applicationId = 123;

        // Expected data
        $expectedDocumentData = [
            'identifier' => 'ABCDEF',
            'application' => 123,
            'licence' => 321,
            'category' => CategoryDataService::CATEGORY_APPLICATION,
            'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_FORMS_ASSISTED_DIGITAL,
            'filename' => $filename,
            'issuedDate' => '2015-01-01 10:10:10',
            'description' => $description,
            'isExternal' => false,
            'isScan' => false
        ];

        // Mocks
        $mockApplicationEntity = m::mock();
        $mockControllerPluginManager = m::mock();
        $mockApplication = m::mock();
        $mockMvcEvent = m::mock();
        $mockRouteMatch = m::mock();
        $mockAdapter = m::mock();
        $mockControllerManager = m::mock();
        $mockController = m::mock();
        $mockView = m::mock();
        $mockViewRenderer = m::mock();
        $mockFileUploader = m::mock();
        $mockFile = m::mock();
        $mockDocumentEntity = m::mock();
        $mockDate = m::mock();
        $mockVariationSection = m::mock();

        $appTypeName = ($appType === 0) ? 'Application' : 'Variation';

        $this->sm->setService('Entity\Application', $mockApplicationEntity);
        $this->sm->setService('ControllerPluginManager', $mockControllerPluginManager);
        $this->sm->setService('Application', $mockApplication);
        $this->sm->setService($appTypeName .'ReviewAdapter', $mockAdapter);
        $this->sm->setService('ControllerManager', $mockControllerManager);
        $this->sm->setService('ViewRenderer', $mockViewRenderer);
        $this->sm->setService('FileUploader', $mockFileUploader);
        $this->sm->setService('Entity\Document', $mockDocumentEntity);
        $this->sm->setService('Helper\Date', $mockDate);
        $this->sm->setService('Processing\VariationSection', $mockVariationSection);

        // Expectations
        $mockApplicationEntity->shouldReceive('getApplicationType')
            ->with(123)
            ->andReturn($appType)
            ->shouldReceive('getLicenceIdForApplication')
            ->with(123)
            ->andReturn(321)
            ->shouldReceive('getTypeOfLicenceData')
            ->with(123)
            ->andReturn($licenceData);

        $mockApplication->shouldReceive('getMvcEvent')
            ->andReturn($mockMvcEvent);

        $mockMvcEvent->shouldReceive('getRouteMatch')
            ->andReturn($mockRouteMatch);

        $mockRouteMatch->shouldReceive('getParam')
            ->with('application')
            ->andReturn($applicationId);

        $mockControllerManager->shouldReceive('get')
            ->with('Lva'. $appTypeName .'/Review')
            ->andReturn($mockController);

        $mockController->shouldReceive('setPluginManager')
            ->with($mockControllerPluginManager)
            ->shouldReceive('setEvent')
            ->with($mockMvcEvent)
            ->shouldReceive('setAdapter')
            ->with($mockAdapter)
            ->shouldReceive('indexAction')
            ->andReturn($mockView);

        $mockViewRenderer->shouldReceive('render')
            ->with($mockView)
            ->andReturn('HTML');

        $mockFileUploader->shouldReceive('getUploader')
            ->andReturnSelf()
            ->shouldReceive('setFile')
            ->with(['content' => 'HTML'])
            ->shouldReceive('upload')
            ->andReturn($mockFile);

        $mockDate->shouldReceive('getDate')
            ->with('Y-m-d H:i:s')
            ->andReturn('2015-01-01 10:10:10');

        $mockFile->shouldReceive('getIdentifier')
            ->andReturn('ABCDEF');

        $mockDocumentEntity->shouldReceive('save')
            ->with($expectedDocumentData);

        if ($appType == ApplicationEntityService::APPLICATION_TYPE_VARIATION) {
            $mockVariationSection->shouldReceive('isRealUpgrade')
                ->with(123)
                ->andReturn($isUpgrade);
        }

        $this->sut->storeSnapshot($applicationId, $event);
    }
}
