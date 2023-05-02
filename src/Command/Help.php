<?php
declare(strict_types=1);

namespace Producer\Command;

use Psr\Log\LoggerInterface;

/**
 *
 * Shows the help output for Producer.
 *
 * @package producer/producer
 *
 */
class Help implements CommandInterface
{
    /**
     *
     * The logger.
     *
     * @var LoggerInterface
     *
     */
    protected $logger;

    /**
     *
     * Constructor.
     *
     * @param LoggerInterface $logger The logger.
     *
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     *
     * The command logic.
     *
     * @param array $argv Command line arguments.
     *
     * @return mixed
     *
     */
    public function __invoke(array $argv)
    {
        $this->logger->info('Producer: a tool for releasing library packages.');
        $this->logger->info('Available commands:');
        $this->logger->info('    issues -- Show open issues from the remote origin.');
        $this->logger->info('    validate <version> -- Validate the repository for a <version> release.');
        $this->logger->info('    release <version> -- Release the repository as <version>.');
    }
}
