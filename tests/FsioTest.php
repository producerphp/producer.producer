<?php
declare(strict_types=1);

namespace Producer;

class FsioTest extends \PHPUnit\Framework\TestCase
{
    protected $fsio;
    protected $base;

    protected function setUp() : void
    {
        $this->fsio = new Fsio(__DIR__);
    }

    public function testIsDir() : void
    {
        $this->assertTrue($this->fsio->isDir('../' . basename(__DIR__)));
    }

    public function testMkdir() : void
    {
        $dir = 'tmp';
        $this->fsio->rmdir($dir);

        $this->assertFalse($this->fsio->isDir($dir));
        $this->fsio->mkdir($dir);
        $this->assertTrue($this->fsio->isDir($dir));
        $this->fsio->rmdir($dir);
        $this->assertFalse($this->fsio->isDir($dir));

        $this->expectException('Producer\Exception');
        $this->expectExceptionMessage('mkdir(): File exists');
        $this->fsio->mkdir('../' . basename(__DIR__));
    }

    public function testPutAndGet() : void
    {
        $file = 'fakefile';
        $this->fsio->unlink($file);

        $expect = 'fake text';
        $this->fsio->put($file, $expect);
        $actual = $this->fsio->get($file);
        $this->assertSame($expect, $actual);
        $this->fsio->unlink($file);
    }

    public function testPut_error() : void
    {
        $this->expectException('Producer\Exception');
        $this->expectExceptionMessage('No such file or directory');
        $this->fsio->put('no-such-directory/fakefile', 'fake text');
    }

    public function testGet_error() : void
    {
        $this->expectException('Producer\Exception');
        $this->expectExceptionMessage('No such file or directory');
        $this->fsio->get('no-such-directory/fakefile');
    }
}
