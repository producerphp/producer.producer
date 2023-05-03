<?php
declare(strict_types=1);

namespace Producer;

class ProducerContainerTest extends \PHPUnit\Framework\TestCase
{
    public function test() : void
    {
        $container = new ProducerContainer(
            $_SERVER['HOME'],
            dirname(__DIR__),
            STDOUT,
            STDERR
        );

        $this->assertInstanceOf(
            Sapi\Cli\Command\Validate::class,
            $container->new(Sapi\Cli\Command\Validate::class)
        );
    }
}
