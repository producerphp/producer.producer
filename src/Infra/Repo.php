<?php
declare(strict_types=1);

namespace Producer\Infra;

use Producer\Infra\Fsio\RepoFsio;
use Psr\Log\LoggerInterface;
use stdClass;

abstract class Repo
{
    protected const SKELETON_FILES = [
        'CHANGELOG',
        'LICENSE',
    ];

    protected string $origin;

    protected ?stdClass $composer = null;

    public function __construct(
        protected RepoFsio $repofs,
        protected LoggerInterface $logger,
        protected Config $config
    ) {
        $this->setOrigin();
    }

    abstract protected function setOrigin() : void;

    public function getOrigin() : string
    {
        return $this->origin;
    }

    public function getPackage() : string
    {
        return $this->getComposer()->name;
    }

    /**
     * @return object{output:string[], last:string, error:int}
     */
    protected function exec(string $command) : object
    {
        $this->logger->debug("> $command");
        $last = exec($command, $output, $error);

        foreach ($output as $line) {
            $this->logger->debug("< $line");
        }

        return (object) [
            'output' => $output,
            'last' => (string) $last,
            'error' => $error,
        ];
    }

    public function checkComposer() : void
    {
        $exec = $this->exec('composer validate');

        if ($exec->error) {
            throw new Exception($exec->last);
        }
    }

    public function getComposer() : stdClass
    {
        if (! $this->composer) {
            $this->composer = (object) json_decode($this->repofs->get('composer.json'));
        }

        return $this->composer;
    }

    protected function checkSkeletonFile(string $file) : string
    {
        $files = array_merge(
            $this->repofs->glob($file, GLOB_MARK),
            $this->repofs->glob("{$file}.*", GLOB_MARK)
        );

        if (count($files) < 1) {
            throw new Exception("The {$file} file is missing.");
        }

        if (count($files) > 1) {
            throw new Exception("There is more than one {$file} file.");
        }

        $file = (string) array_pop($files);

        if (trim($this->repofs->get($file)) === '') {
            throw new Exception("The file {$file} is empty.");
        }

        return $file;
    }

    public function checkLicenseYear() : void
    {
        $file = $this->checkSkeletonFile('LICENSE');
        $license = $this->repofs->get($file);
        $year = date('Y');

        if (strpos($license, $year) === false) {
            $this->logger->warning('The LICENSE copyright year (or range of years) looks out-of-date.');
        }
    }

    public function checkQuality() : void
    {
        $command = $this->config->get('quality_command') ?? 'composer check';

        if (! $command) {
            throw new Exception('The quality_command configuration value is empty.');
        }

        $exec = $this->exec($command);

        if ($exec->error) {
            throw new Exception($exec->last);
        }
    }

    public function getChanges() : string
    {
        $file = $this->checkSkeletonFile('CHANGELOG');
        $text = $this->repofs->get($file);

        preg_match('/(\n\#\# .*\n)(.*)(\n\#\# )/Ums', $text, $matches);

        if (isset($matches[2])) {
            return trim($matches[2]);
        }

        return $text;
    }

    public function checkChangelogDate() : void
    {
        $file = $this->checkSkeletonFile('CHANGELOG');

        $lastChangelogDate = $this->getChangelogDate();
        $this->logger->info("Last CHANGELOG date is $lastChangelogDate.");

        $lastCommitDate = $this->getLastCommitDate();
        $this->logger->info("Last repository commit date is $lastCommitDate.");

        if ($lastChangelogDate === $lastCommitDate) {
            $this->logger->info('CHANGELOG appears up to date.');
            return;
        }

        $this->logger->error('CHANGELOG appears out of date.');
        $this->logger->error('Log of possible missing CHANGELOG items:');
        $this->logSinceDate($lastChangelogDate);
        throw new Exception('Please update and commit the CHANGELOG.');
    }

    public function checkChangelogVersion(string $version) : void
    {
        $file = $this->checkSkeletonFile('CHANGELOG');
        $changelog = $this->repofs->get($file);

        $quotedVersion = preg_quote($version);
        $found = preg_match('/^\W*{$quotedVersion}[\s\t\r\n]/Umsi', $changelog, $matches);

        if ($found) {
            $this->logger->info("CHANGELOG contains {$version} heading: {$matches[0]}");
            return;
        }

        $this->logger->error("CHANGELOG appears to have no {$version} heading.");
        throw new Exception("Please add a {$version} heading to the CHANGELOG.");
    }

    abstract public function getBranch() : string;

    abstract public function checkStatus() : void;

    abstract public function getChangelogDate() : string;

    abstract public function getLastCommitDate() : string;

    /**
     * @return string[]
     */
    abstract public function logSinceDate(string $date) : array;

    abstract public function tag(string $version, string $message) : void;

    abstract public function sync() : void;
}
