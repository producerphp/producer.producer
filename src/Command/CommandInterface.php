<?php
declare(strict_types=1);

namespace Producer\Command;

/**
 *
 * Interface for command classes.
 *
 * @package producer/producer
 *
 */
interface CommandInterface
{
    /**
     *
     * The command logic.
     *
     * @param array $argv Command line arguments.
     *
     * @return mixed
     *
     */
    public function __invoke(array $argv);
}
