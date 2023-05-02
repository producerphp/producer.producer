<?php
declare(strict_types=1);

namespace Producer\Repo;

use Producer\Config;
use Producer\Exception;
use Producer\Fsio;
use Psr\Log\LoggerInterface;

/**
 *
 * Base class for local VCS repos.
 *
 * @package producer/producer
 *
 */
abstract class AbstractRepo implements RepoInterface
{
    /**
     *
     * The remote origin.
     *
     * @var array
     *
     */
    protected $origin = [];

    /**
     *
     * The `composer.json` data.
     *
     * @var object
     *
     */
    protected $composer;

    /**
     *
     * A filesystem I/O object.
     *
     * @var Fsio
     *
     */
    protected $fsio;

    /**
     *
     * A logger.
     *
     * @var LoggerInterface
     *
     */
    protected $logger;

    /**
     *
     * Global and project configuration.
     *
     * @var Config
     *
     */
    protected $config;

    /**
     *
     * Constructor.
     *
     * @param Fsio $fsio A filesystem I/O object.
     *
     * @param LoggerInterface $logger A logger.
     *
     * @param Config $config
     *
     */
    public function __construct(Fsio $fsio, LoggerInterface $logger, Config $config)
    {
        $this->fsio = $fsio;
        $this->logger = $logger;
        $this->config = $config;
        $this->setOrigin();
    }

    /**
     *
     * Retains the remote origin for the repository from the VCS config file.
     *
     */
    abstract protected function setOrigin();


    /**
     *
     * Returns the remote origin for the repository.
     *
     * @return string
     *
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     *
     * Returns the Composer package name.
     *
     * @return string
     *
     */
    public function getPackage()
    {
        return $this->getComposer()->name;
    }

    /**
     *
     * Executes shell commands.
     *
     * @param string $cmd The shell command to execute.
     *
     * @param array $output Returns shell output through the reference.
     *
     * @param mixed $return Returns the exit code through this reference.
     *
     * @return string The last line of output.
     *
     * @see exec
     */
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

    /**
     *
     * Validates the `composer.json` file.
     *
     */
    public function validateComposer()
    {
        $last = $this->shell('composer validate', $output, $return);
        if ($return) {
            throw new Exception($last);
        }
    }

    /**
     *
     * Gets the `composer.json` file data.
     *
     * @return object
     *
     */
    public function getComposer()
    {
        if (! $this->composer) {
            $this->composer = json_decode($this->fsio->get('composer.json'));
        }
        return $this->composer;
    }

    /**
     *
     * Checks all support files *except* for CHANGES; this is because updating
     * the changes should be the very last thing to deal with.
     *
     */
    public function checkSupportFiles()
    {
        $files = $this->config->get('files');
        unset($files['changes']);
        foreach ($files as $file) {
            $this->checkSupportFile($file);
        }
    }

    /**
     *
     * Checks one support file.
     *
     * @param string $file The file to check.
     *
     */
    protected function checkSupportFile($file)
    {
        if (! $this->fsio->isFile($file)) {
            throw new Exception("The file {$file} is missing.");
        }
        if (trim($this->fsio->get($file)) === '') {
            throw new Exception("The file {$file} is empty.");
        }
    }

    /**
     *
     * Checks to see that the current year is in the LICENSE.
     *
     */
    public function checkLicenseYear()
    {
        $license = $this->fsio->get($this->config->get('files')['license']);
        $year = date('Y');
        if (strpos($license, $year) === false) {
            $this->logger->warning('The LICENSE copyright year (or range of years) looks out-of-date.');
        }
    }

    /**
     *
     * Runs the tests using phpunit.
     *
     */
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

    /**
     *
     * Gets the contents of the CHANGES file.
     *
     * If the file is named CHANGELOG(*), then this will look for the first
     * set of double-hashes (indicating a version heading) and only return the
     * text until the next set of double-hashes. If there is no matching pair
     * of double-hashes, it will return the entire text.
     *
     */
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

    /**
     *
     * Checks to see if the changes are up to date.
     *
     */
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
