<?php
declare(strict_types=1);

namespace Producer;

class ProducerContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testNewCommand()
    {
        $container = new ProducerContainer($_SERVER['HOME'], getcwd(), STDOUT, STDERR);
        $this->assertInstanceOf(Command\Validate::class, $container->newCommand('validate'));
    }
}
