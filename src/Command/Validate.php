<?php
declare(strict_types=1);

namespace Producer\Command;

use AutoShell\Help;
use AutoShell\Option;
use AutoShell\Options;
use Producer\Command;
use Producer\Exception;

#[Help("Validate, and optionally release, the repository as _version_.")]
#[Option('release', help: "Release the package after validation.")]
class Validate extends Command
{
    protected string $package;

    protected string $version;

    public function __invoke(
        Options $options,

        #[Help("Validate at this version.")]
        string $version

    ) : int
    {
        if ($options['release']) {
            $this->logger->warning("THIS WILL RELEASE THE PACKAGE.");
        }

        $this->setVersion($version);

        $this->repo->sync();
        $this->repo->validateComposer();

        $this->package = $this->repo->getPackage();
        $this->logger->info("Validating {$this->package} {$this->version}");

        $this->repo->checkSupportFiles();
        $this->repo->checkLicenseYear();
        $this->repo->checkQuality();
        $this->repo->checkStatus();
        $this->repo->checkChanges();
        $this->checkIssues();

        $this->logger->info("{$this->package} {$this->version} appears valid for release!");

        if ($options['release']) {
            $this->logger->info("Releasing $this->package $this->version");
            $this->api->release($this->repo, $this->version);
            $this->logger->info("Released $this->package $this->version !");
        }

        return 0;
    }

    protected function setVersion(string $version) : void
    {
        if (! $version) {
            throw new Exception('Please specify a version number.');
        }

        if ($this->isValidVersion($version)) {
            $this->version = $version;
            return;
        }

        $message = "Please use the version format 1.2.3 or v1.2.3, optionally followed by -(dev|alpha|beta|RC|p), optionally followed by a number.";

        throw new Exception($message);
    }

    protected function isValidVersion(string $version) : bool
    {
        $format = '^(v?\d+.\d+.\d+)(-(dev|alpha|beta|RC|p)\d*)?$';
        preg_match("/$format/", $version, $matches);
        return (bool) $matches;
    }

    protected function checkIssues() : void
    {
        $issues = $this->api->issues();

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
