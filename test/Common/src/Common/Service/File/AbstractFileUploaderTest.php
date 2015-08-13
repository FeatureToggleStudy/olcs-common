<?php

/**
 * Abstract File Uploader Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace CommonTest\Service\File;

/**
 * Abstract File Uploader Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AbstractFileUploaderTest extends \PHPUnit_Framework_TestCase
{
    public function testSetFile()
    {
        $data = array(
            'name' => 'Bob',
            'tmp_name' => '/sdflkajdsf/asdfjasldf',
            'size' => 45646,
            'content' => 'foo'
        );

        $expected = array(
            'identifier' => null,
            'name' => 'Bob',
            'path' => '/sdflkajdsf/asdfjasldf',
            'size' => 45646,
            'content' => 'foo'
        );

        $abstractFileUploader = $this->getMockForAbstractClass('\Common\Service\File\AbstractFileUploader');

        $abstractFileUploader->setFile($data);
        $file = $abstractFileUploader->getFile();

        $this->assertInstanceOf('Common\Service\File\File', $file);
        $this->assertEquals($expected, $file->toArray());
    }

    public function testSetConfig()
    {
        $config = array('foo' => 'bar');
        $abstractFileUploader = $this->getMockForAbstractClass('\Common\Service\File\AbstractFileUploader');

        $abstractFileUploader->setConfig($config);
        $this->assertEquals($config, $abstractFileUploader->getConfig());
    }

    public function testSetServiceLocator()
    {
        $mockServiceLocator = $this->getMock('\Zend\ServiceManager\ServiceManager');

        $abstractFileUploader = $this->getMockForAbstractClass('\Common\Service\File\AbstractFileUploader');

        $abstractFileUploader->setServiceLocator($mockServiceLocator);
        $this->assertEquals($mockServiceLocator, $abstractFileUploader->getServiceLocator());
    }
}
