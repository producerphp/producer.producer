<?php
declare(strict_types=1);

namespace Producer\Command;

use Producer\Api\ApiInterface;
use Psr\Log\LoggerInterface;
use Producer\Repo\RepoInterface;

/**
 *
 * Show all open issues.
 *
 * @package producer/producer
 *
 */
class Issues extends AbstractCommand
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
    public function __invoke(array $argv)
    {
        $issues = $this->api->issues();
        if (empty($issues)) {
            return;
        }

        $this->logger->info($this->api->getRepoName());
        $this->logger->info('');
        foreach ($issues as $issue) {
            $this->logger->info("    {$issue->number}. {$issue->title}");
            $this->logger->info("        {$issue->url}");
            $this->logger->info('');
        }
    }
}
