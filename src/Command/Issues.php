<?php
declare(strict_types=1);

namespace Producer\Command;

use AutoShell\Help;
use AutoShell\Options;
use Producer\Command;

#[Help("Show open issues from the remote origin.")]
class Issues extends Command
{
    public function __invoke(Options $options) : int
    {
        $issues = $this->api->issues();

        if (empty($issues)) {
            return 0;
        }

        $this->logger->info($this->api->getRepoName());
        $this->logger->info('');

        foreach ($issues as $issue) {
            $this->logger->info("    {$issue->number}. {$issue->title}");
            $this->logger->info("        {$issue->url}");
            $this->logger->info('');
        }

        return 0;
    }
}
