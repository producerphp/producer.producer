<?php
declare(strict_types=1);

namespace Producer;

use Producer\Fsio\RepoFsio;
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

    protected RepoFsio $fsio;

    protected LoggerInterface $logger;

    protected Config $config;

    public function __construct(RepoFsio $fsio, LoggerInterface $logger, Config $config)
    {
        $this->fsio = $fsio;
        $this->logger = $logger;
        $this->config = $config;
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
     * @param string[] &$output
     */
    protected function shell(string $cmd, array &$output = [], int &$return = null) : string
    {
        $cmd = str_replace('; ', ';\\' . PHP_EOL, $cmd);
        $this->logger->debug("> $cmd");
        $output = null;
        $last = exec($cmd, $output, $return);

        foreach ($output as $line) {
            $this->logger->debug("< $line");
        }

        return (string) $last;
    }

    public function validateComposer() : void
    {
        $last = $this->shell('composer validate', $output, $return);

        if ($return) {
            throw new Exception($last);
        }
    }

    public function getComposer() : stdClass
    {
        if (! $this->composer) {
            $this->composer = (object) json_decode($this->fsio->get('composer.json'));
        }

        return $this->composer;
    }

    public function checkSkeletonFiles() : void
    {
        foreach (static::SKELETON_FILES as $name) {
            $this->checkSkeletonFile($name);
        }
    }

    protected function checkSkeletonFile(string $file) : string
    {
        $files = array_merge(
            $this->fsio->glob($file, GLOB_MARK),
            $this->fsio->glob("{$file}.*", GLOB_MARK)
        );

        if (count($files) < 1) {
            throw new Exception("The {$file} file is missing.");
        }

        if (count($files) > 1) {
            throw new Exception("There is more than one {$file} file.");
        }

        if (trim($this->fsio->get($file)) === '') {
            throw new Exception("The file {$file} is empty.");
        }

        return (string) array_pop($files);
    }

    public function checkLicenseYear() : void
    {
        $license = $this->fsio->get($this->checkSkeletonFile('LICENSE'));
        $year = date('Y');

        if (strpos($license, $year) === false) {
            $this->logger->warning('The LICENSE copyright year (or range of years) looks out-of-date.');
        }
    }

    public function checkQuality() : void
    {
        $command = 'composer check';
        $last = $this->shell($command, $output, $return);

        if ($return) {
            throw new Exception($last);
        }

        $this->checkStatus();
    }

    public function getChanges() : string
    {
        $file = $this->checkSkeletonFile('CHANGELOG');
        $text = $this->fsio->get($file);

        preg_match('/(\n\#\# .*\n)(.*)(\n\#\# )/Ums', $text, $matches);

        if (isset($matches[2])) {
            return trim($matches[2]);
        }

        return $text;
    }

    public function checkChanges() : void
    {
        $file = $this->checkSkeletonFile('CHANGELOG');

        $lastChangelog = $this->getChangesDate();
        $this->logger->info("Last changes date is $lastChangelog.");

        $lastCommit = $this->getLastCommitDate();
        $this->logger->info("Last commit date is $lastCommit.");

        if ($lastChangelog == $lastCommit) {
            $this->logger->info('Changes appear up to date.');
            return;
        }

        $this->logger->error('Changes appear out of date.');
        $this->logger->error('Log of possible missing changes:');
        $this->logSinceDate($lastChangelog);
        throw new Exception('Please update and commit the changes.');
    }

    abstract public function getBranch() : string;

    abstract public function checkStatus() : void;

    abstract public function getChangesDate() : string;

    abstract public function getLastCommitDate() : string;

    /**
     * @return string[]
     */
    abstract public function logSinceDate(string $date) : array;

    abstract public function tag(string $version, string $message) : void;

    abstract public function sync() : void;
}
