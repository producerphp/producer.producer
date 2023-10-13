<?php
declare(strict_types=1);

namespace Producer\App;

use Producer\Infra\Api;
use Producer\Infra\Exception;
use Producer\Infra\Repo;
use Psr\Log\LoggerInterface;

class ValidateService
{
    public function __construct(
        protected Api $api,
        protected Repo $repo,
        protected LoggerInterface $logger,
    ) {
    }

    public function __invoke(string $version, bool $release = false) : void
    {
        $this->assertValidVersion($version);

        if ($release) {
            $this->logger->warning("THIS WILL RELEASE THE PACKAGE.");
        }

        $this->repo->checkComposer();
        $package = $this->repo->getPackage();
        $this->logger->info("Validating {$package} {$version}");
        $this->repo->sync();
        $this->repo->checkStatus();
        $this->repo->checkQuality();
        $this->repo->checkStatus();
        $this->repo->checkLicenseYear();
        $this->repo->checkChangelogDate();
        $this->repo->checkChangelogVersion($version);
        $this->logger->info("{$package} {$version} appears valid!");

        if ($release) {
            $this->api->release($this->repo, $this->logger, $version);
            $this->repo->sync();
            $this->repo->checkStatus();
        }
    }

    protected function assertValidVersion(string $version) : void
    {
        if (! $version) {
            throw new Exception('Please specify a version number.');
        }

        if ($this->isValidVersion($version)) {
            return;
        }

        $message = "Please use the version format 1.2.3 or v1.2.3, optionally followed by -(dev|alpha|beta|RC|p), optionally followed by a number.";

        throw new Exception($message);
    }

    protected function isValidVersion(string $version) : bool
    {
        $format = '^(v?\d+.\d+.\d+)(-(dev|alpha|beta|RC|p)\d*)?$';
        preg_match("/{$format}/", $version, $matches);
        return (bool) $matches;
    }
}
