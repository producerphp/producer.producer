<?php
declare(strict_types=1);

namespace Producer\App;

use Producer\Infra\Api;
use Producer\Infra\Repo;
use Psr\Log\LoggerInterface;

class IssuesService
{
    public function __construct(
        protected Api $api,
        protected LoggerInterface $logger,
    ) {
    }

    public function __invoke() : void
    {
        $issues = $this->api->issues();

        $this->logger->info($this->api->getRepoName());
        $this->logger->info('');

        if (empty($issues)) {
            $this->logger->info('No open issues.');
            return;
        }

        $this->logger->warning('There are open issues:');

        foreach ($issues as $issue) {
            $this->logger->warning("    {$issue->number}. {$issue->title}");
            $this->logger->warning("        {$issue->url}");
        }
    }
}
