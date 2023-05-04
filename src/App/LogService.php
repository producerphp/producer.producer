<?php
declare(strict_types=1);

namespace Producer\App;

use Producer\Infra\Api;
use Producer\Infra\Exception;
use Producer\Infra\Repo;
use Psr\Log\LoggerInterface;

class LogService
{
    public function __construct(
        protected Repo $repo,
        protected LoggerInterface $logger,
    ) {
    }

    public function __invoke()
    {
        $versions = $this->repo->getVersions();
        $latestVersion = end($versions);
        $latestVersionDate = $this->repo->getVersionDate($latestVersion);

        $this->logger->info("Latest release was {$latestVersion} on {$latestVersionDate}.");
        $this->logger->info("");

        $lines = $this->repo->logSinceDate($latestVersionDate);

        foreach ($lines as $line) {
            $this->logger->info($line);
        }
    }

    public function getVersions() : array
    {
        $execResult = $this->exec->result('git tag --list');
        $versions = $execResult->lines;
        usort($versions, 'version_compare');
        return $versions;
    }

    public function getVersionDate(string $version) : string
    {
        $execResult = $this->exec->result("git show {$version}");

        $dateToTimestamp = function ($output) {
            foreach ($output as $line) {
                if (substr($line, 0, 5) == 'Date:') {
                    $date = trim(substr($line, 5));
                    return strtotime($date);
                }
            }

            throw new Exception('No date found in log.');
        };

        return date('r', $dateToTimestamp($execResult->lines) + 1);
    }

    public function logSince(string $date) : array
    {
        $date = escapeshellarg($date);
        $execResult = $this->exec->result("git log --name-status --reverse --after={$date}");
        return $execResult->lines;
    }
}
