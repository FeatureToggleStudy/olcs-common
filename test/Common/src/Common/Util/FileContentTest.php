<?php

namespace CommonTest\Util;

use Common\Util\FileContent;

/**
 * @covers \Common\Util\FileContent
 */
class FileContentTest extends \PHPUnit_Framework_TestCase
{
    public function testFileContent()
    {
        $mimeType = 'mimeType';

        $fileContent = new FileContent('foo.pdf', $mimeType);

        $this->assertEquals('foo.pdf', $fileContent->getFileName());
        static::assertEquals($mimeType, $fileContent->getMimeType());
        $this->assertEquals('foo.pdf', (string)$fileContent);
    }
}
