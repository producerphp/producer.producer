<?php
declare(strict_types=1);

namespace Producer;

use Producer\Config;
use Producer\Exception;
use Producer\Fsio\RepoFsio;
use Psr\Log\LoggerInterface;

abstract class Repo
{
    protected string $origin;

    protected $composer;

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

    abstract protected function setOrigin();

    public function getOrigin()
    {
        return $this->origin;
    }

    public function getPackage()
    {
        return $this->getComposer()->name;
    }

    protected function shell($cmd, &$output = [], &$return = null)
    {
        $cmd = str_replace('; ', ';\\' . PHP_EOL, $cmd);
        $this->logger->debug("> $cmd");
        $output = null;
        $last = exec($cmd, $output, $return);
        foreach ($output as $line) {
            $this->logger->debug("< $line");
        }
        return $last;
    }

    public function validateComposer()
    {
        $last = $this->shell('composer validate', $output, $return);
        if ($return) {
            throw new Exception($last);
        }
    }

    public function getComposer()
    {
        if (! $this->composer) {
            $this->composer = json_decode($this->fsio->get('composer.json'));
        }
        return $this->composer;
    }

    public function checkSupportFiles()
    {
        $files = $this->config->get('files');
        unset($files['changes']);
        foreach ($files as $file) {
            $this->checkSupportFile($file);
        }
    }

    protected function checkSupportFile($file)
    {
        if (! $this->fsio->isFile($file)) {
            throw new Exception("The file {$file} is missing.");
        }
        if (trim($this->fsio->get($file)) === '') {
            throw new Exception("The file {$file} is empty.");
        }
    }

    public function checkLicenseYear()
    {
        $license = $this->fsio->get($this->config->get('files')['license']);
        $year = date('Y');
        if (strpos($license, $year) === false) {
            $this->logger->warning('The LICENSE copyright year (or range of years) looks out-of-date.');
        }
    }

    public function checkTests()
    {
        $this->shell('composer update');

        $command = $this->config->get('commands')['phpunit'];

        $last = $this->shell($command, $output, $return);
        if ($return) {
            throw new Exception($last);
        }
        $this->checkStatus();
    }

    public function getChanges()
    {
        $file = $this->config->get('files')['changes'];

        $text = $this->fsio->get($file);
        $name = substr(basename(strtoupper($file)), 0, 9);
        if ($name !== 'CHANGELOG') {
            return $text;
        }

        preg_match('/(\n\#\# .*\n)(.*)(\n\#\# )/Ums', $text, $matches);
        if (isset($matches[2])) {
            return trim($matches[2]);
        }

        return $text;
    }

    public function checkChanges()
    {
        $file = $this->config->get('files')['changes'];
        $this->checkSupportFile($file);

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
}
