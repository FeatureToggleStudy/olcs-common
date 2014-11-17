<?php

namespace CommonTest\Filter;

use Common\Filter\DecompressUploadToTmp;
use Mockery as m;

/**
 * Class DecompressUploadToTmpTest
 *
 * This test mocks register_shutdown_function in the Common\Filter namespace, this mock will affect other tests in the
 * same namespace.
 *
 * @package CommonTest\Filter
 * @group UnsafeMocking
 */
class DecompressUploadToTmpTest extends \PHPUnit_Framework_TestCase
{
    public function testFilter()
    {
        $filename = 'testFile.zip';
        $tmpDir = '/tmp/';
        $extractDir = '/tmp/zipUvf4glz/';

        eval('namespace Common\Filter; function register_shutdown_function ($callback) { $callback(); }');

        $mockFilter = m::mock('\Zend\Filter\Decompress');
        $mockFilter->shouldReceive('filter')->with($filename);
        $mockFilter->shouldReceive('setOptions')->with(['options' => ['target' => $extractDir]]);

        $mockFileSystem = m::mock('Common\Filesystem\Filesystem');
        $mockFileSystem->shouldReceive('createTmpDir')->with($tmpDir, 'zip')->andReturn($extractDir);
        $mockFileSystem->shouldReceive('remove')->with($tmpDir);

        $sut = new DecompressUploadToTmp();
        $sut->setDecompressFilter($mockFilter);
        $sut->setTempRootDir($tmpDir);
        $sut->setFileSystem($mockFileSystem);

        $result = $sut->filter(['tmp_name' => $filename]);

        $this->assertEquals(['tmp_name' => $filename, 'extracted_dir' => $extractDir], $result);
    }
}
