<?php

/**
 * Document Stub Print Scheduler factory test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace CommonTest\Service\Printing;

use CommonTest\Bootstrap;
use Common\Service\Printing\DocumentStubPrintScheduler;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Document Stub Print Scheduler factory test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DocumentStubPrintSchedulerTest extends MockeryTestCase
{
    public function testEnqueueFile()
    {
        $this->markTestSkipped('Being removed shortly');

        $file = m::mock('\Common\Service\File\File')
            ->shouldReceive('getIdentifier')
            ->andReturn('f123')
            ->shouldReceive('getSize')
            ->andReturn(12345)
            ->getMock();

        $data = [
            'identifier'    => 'f123',
            'description'   => 'A Test Job',
            'filename'      => 'A_Test_Job.rtf',
            'licence'       => 7,
            'category'      => 1,
            'subCategory'   => 91,
            'isExternal'    => false,
            'isReadOnly'    => true,
            'issuedDate'    => '2014-01-01 01:23:45',
            'size'          => 12345
        ];

        $sm = m::mock('\Zend\ServiceManager\ServiceLocatorInterface')
            ->shouldReceive('get')
            ->with('Helper\Rest')
            ->andReturn(
                m::mock()
                ->shouldReceive('makeRestCall')
                ->with('Document', 'POST', $data)
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('Helper\Date')
            ->andReturn(
                m::mock()
                ->shouldReceive('getDate')
                ->with('Y-m-d H:i:s')
                ->andReturn('2014-01-01 01:23:45')
                ->getMock()
            )
            ->getMock();

        $sut = new DocumentStubPrintScheduler();
        $sut->setServiceLocator($sm);

        $sut->enqueueFile($file, 'A Test Job');
    }
}
