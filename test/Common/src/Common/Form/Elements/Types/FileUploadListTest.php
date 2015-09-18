<?php

/**
 * FileUploadListTest
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace CommonTest\Form\Elements\Types;

use PHPUnit_Framework_TestCase;
use Common\Form\Elements\Types\FileUploadList;

/**
 * FileUploadListTest
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FileUploadListTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test the element configuration
     */
    public function testElement()
    {
        $files = array(
            array(
                'identifier' => 'hgafdjklhaldsf',
                'filename' => 'someFile.png',
                'description' => 'someFile',
                'size' => 50,
                'id' => 7,
                'version' => 1
            ),
            array(
                'identifier' => 'hgafdjklhalsdgs',
                'filename' => 'someOtherFile.png',
                'description' => 'someOtherFile',
                'size' => 5000,
                'id' => 8,
                'version' => 1
            ),
            array(
                'identifier' => 'hdsfgafdjklhalsdgs',
                'filename' => 'anotherFile.png',
                'description' => 'anotherFile',
                'size' => 50000000,
                'id' => 9,
                'version' => 1
            )
        );

        $mockUrl = $this->getMock('\stdClass', array('fromRoute'));
        $mockUrl->expects($this->any())
            ->method('fromRoute')
            ->will($this->returnValue('url'));

        $element = new FileUploadList();
        $element->setFiles($files, $mockUrl);

        $this->assertTrue($element->has('file-7'));
        $this->assertTrue($element->get('file-7')->has('id'));
        $this->assertTrue($element->get('file-7')->has('version'));
        $this->assertTrue($element->get('file-7')->has('link'));
        $this->assertTrue($element->get('file-7')->has('remove'));

        $this->assertTrue($element->has('file-8'));
        $this->assertTrue($element->get('file-8')->has('id'));
        $this->assertTrue($element->get('file-8')->has('version'));
        $this->assertTrue($element->get('file-8')->has('link'));
        $this->assertTrue($element->get('file-8')->has('remove'));

        $this->assertTrue($element->has('file-9'));
        $this->assertTrue($element->get('file-9')->has('id'));
        $this->assertTrue($element->get('file-9')->has('version'));
        $this->assertTrue($element->get('file-9')->has('link'));
        $this->assertTrue($element->get('file-9')->has('remove'));
    }
}
