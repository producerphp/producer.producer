<?php
declare(strict_types=1);

namespace Producer;

use Psr\Log\LogLevel;
use RuntimeException;

class StdlogTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var resource
     */
    protected mixed $stdout;

    /**
     * @var resource
     */
    protected mixed $stderr;

    protected Stdlog $logger;

    public function setUp() : void
    {
        $this->stdout = $this->memory();
        $this->stderr = $this->memory();
        $this->logger = new Stdlog($this->stdout, $this->stderr);
    }

    /**
     * @return resource
     */
    protected function memory() : mixed
    {
        $handle = fopen('php://memory', 'rw+');

        if (! $handle) {
            throw new RuntimeException("Could not open memory handle.");
        }

        return $handle;
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

    /**
     * @param resource $handle
     */
    protected function assertLogged(string $expect, mixed $handle) : void
    {
        rewind($handle);
        $actual = '';
        while ($read = fread($handle, 8192)) {
            $actual .= $read;
        }

        $this->assertSame($expect, $actual);
    }
}
