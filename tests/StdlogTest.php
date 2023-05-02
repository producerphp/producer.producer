<?php
declare(strict_types=1);

namespace Producer;

use Psr\Log\LogLevel;

class StdlogTest extends \PHPUnit\Framework\TestCase
{
    public function setUp() : void
    {
        $this->stdout = fopen('php://memory', 'rw+');
        $this->stderr = fopen('php://memory', 'rw+');
        $this->logger = new Stdlog($this->stdout, $this->stderr);
    }

    public function testLog_stdout() : void
    {
        $this->logger->log(LogLevel::INFO, 'Foo {bar}', ['bar' => 'baz']);
        $this->assertLogged('Foo baz' . PHP_EOL, $this->stdout);
        $this->assertLogged('', $this->stderr);
    }

    public function testLog_stderr() : void
    {
        $this->logger->log(LogLevel::ERROR, 'Foo {bar}', ['bar' => 'baz']);
        $this->assertLogged('', $this->stdout);
        $this->assertLogged('Foo baz' . PHP_EOL, $this->stderr);
    }

    protected function assertLogged($expect, $handle) : void
    {
        rewind($handle);
        $actual = '';
        while ($read = fread($handle, 8192)) {
            $actual .= $read;
        }

        $this->assertSame($expect, $actual);
    }
}
