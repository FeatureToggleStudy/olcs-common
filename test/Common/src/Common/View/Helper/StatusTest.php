<?php

/**
 * Test Status view helper
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace CommonTest\View\Helper;

use Common\RefData;
use Common\View\Helper\Status;
use Common\Service\Entity\LicenceEntityService;

/**
 * Test Status view helper
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class StatusTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    private $mockView;
    /**
     * Setup the view helper
     */
    public function setUp()
    {
        $this->sut = new Status();

        $this->mockView = \Mockery::mock('Zend\View\Renderer\PhpRenderer');
        $this->sut->setView($this->mockView);
    }

    /**
     * @dataProvider dataProviderStatus
     */
    public function testStatus($status, $color)
    {
        $html = $this->sut->__invoke($status);

        $expected = !empty($color) ? '<span class="status '. $color .'">value</span>' : '';
        $this->assertEquals($expected, $html);
    }

    public function dataProviderStatus()
    {
        return [
            [
                [],
                ''
            ],
            [
                ['id' => LicenceEntityService::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT, 'description' => ''],
                ''
            ],
            [
                ['colour' => 'red', 'value' => 'value'],
                'red'
            ],
            [
                ['id' => LicenceEntityService::LICENCE_STATUS_UNDER_CONSIDERATION, 'description' => 'value'],
                'orange'
            ],
            [
                ['id' => LicenceEntityService::LICENCE_STATUS_NOT_SUBMITTED, 'description' => 'value'],
                'grey'
            ],
            [
                ['id' => LicenceEntityService::LICENCE_STATUS_SUSPENDED, 'description' => 'value'],
                'orange'
            ],
            [
                ['id' => LicenceEntityService::LICENCE_STATUS_VALID, 'description' => 'value'],
                'green'
            ],
            [
                ['id' => LicenceEntityService::LICENCE_STATUS_CURTAILED, 'description' => 'value'],
                'orange'
            ],
            [
                ['id' => LicenceEntityService::LICENCE_STATUS_GRANTED, 'description' => 'value'],
                'orange'
            ],
            [
                ['id' => LicenceEntityService::LICENCE_STATUS_SURRENDERED, 'description' => 'value'],
                'red'
            ],
            [
                ['id' => LicenceEntityService::LICENCE_STATUS_WITHDRAWN, 'description' => 'value'],
                'red'
            ],
            [
                ['id' => LicenceEntityService::LICENCE_STATUS_REFUSED, 'description' => 'value'],
                'red'
            ],
            [
                ['id' => LicenceEntityService::LICENCE_STATUS_REVOKED, 'description' => 'value'],
                'red'
            ],
            [
                ['id' => LicenceEntityService::LICENCE_STATUS_NOT_TAKEN_UP, 'description' => 'value'],
                'red'
            ],
            [
                ['id' => LicenceEntityService::LICENCE_STATUS_TERMINATED, 'description' => 'value'],
                'red'
            ],
            [
                ['id' => LicenceEntityService::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT, 'description' => 'value'],
                'red'
            ],
            [
                ['id' => RefData::BUSREG_STATUS_ADMIN, 'description' => 'value'],
                'grey'
            ],
            [
                ['id' => RefData::BUSREG_STATUS_REGISTERED, 'description' => 'value'],
                'green'
            ],
            [
                ['id' => RefData::BUSREG_STATUS_REFUSED, 'description' => 'value'],
                'grey'
            ],
            [
                ['id' => RefData::BUSREG_STATUS_CANCELLATION, 'description' => 'value'],
                'orange'
            ],
            [
                ['id' => RefData::BUSREG_STATUS_WITHDRAWN, 'description' => 'value'],
                'grey'
            ],
            [
                ['id' => RefData::BUSREG_STATUS_VARIATION, 'description' => 'value'],
                'orange'
            ],
            [
                ['id' => RefData::BUSREG_STATUS_CNS, 'description' => 'value'],
                'grey'
            ],
            [
                ['id' => RefData::BUSREG_STATUS_CANCELLED, 'description' => 'value'],
                'grey'
            ],
            [
                ['id' => RefData::BUSREG_STATUS_NEW, 'description' => 'value'],
                'orange'
            ],
        ];
    }
}
