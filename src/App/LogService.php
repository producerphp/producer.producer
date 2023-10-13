<?php
declare(strict_types=1);

namespace Producer\App;

use Producer\Infra\Api;
use Producer\Infra\Exception;
use Producer\Infra\Repo;
use Psr\Log\LoggerInterface;

class LogService
{
    public function __construct(protected Repo $repo, protected LoggerInterface $logger)
    {
    }

    public function __invoke() : void
    {
        $versions = $this->repo->getVersions();
        $latestVersion = (string) end($versions);
        $latestVersionDate = $this->repo->getVersionDate($latestVersion);
        $this->logger
            ->info("Latest release was {$latestVersion} on {$latestVersionDate}.");
        $this->logger->info('');
        $lines = $this->repo->logSinceDate($latestVersionDate);

        foreach ($lines as $line) {
            $this->logger->info($line);
        }
    }
}
