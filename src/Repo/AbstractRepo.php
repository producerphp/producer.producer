<?php
/**
 *
 * This file is part of Producer for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
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
     * Checks the `src/` docblocks using phpdoc.
     *
     * @param string $version A version number. It provided, will skip checking
     * if the version is 0.*, dev, or alpha; if not provided, will never skip.
     *
     */
    public function checkDocblocks($version = null)
    {
        switch (true) {
            case substr($version, 0, 2) == '0.':
                $skip = '0.x';
                break;
            case strpos($version, 'dev') !== false:
                $skip = 'dev';
                break;
            case strpos($version, 'alpha') !== false:
                $skip = 'alpha';
                break;
            default:
                $skip = false;
        }

        if ($skip) {
            $this->logger->info("Skipping docblock check for $skip release.");
            return;
        }

        // where to write validation records?
        $target = $this->fsio->sysTempDir('/phpdoc/' . $this->getPackage());

        // remove previous validation records, if any
        $this->shell("rm -rf {$target}");

        // validate
        $phpdoc = $this->config->get('commands')['phpdoc'];
        $command = "{$phpdoc} -d src/ -t {$target} --force --verbose --template=xml";
        $line = $this->shell($command, $output, $return);

        // get the XML file
        $xml = simplexml_load_file("{$target}/structure.xml");

        // what is the expected @package name?
        $expectPackage = $this->getPackage();
        $customPackage = $this->config->get('package');
        if ($customPackage) {
            $expectPackage = $customPackage;
        }

        // are there missing or misvalued @package tags?
        $missing = false;
        foreach ($xml->file as $file) {
            // class-level tag (don't care about file-level)
            $actualPackage = $file->class['package'] . $file->interface['package'];
            if ($actualPackage != $expectPackage) {
                $missing = true;
                $class = $file->class->full_name . $file->interface->full_name;
                $message = "  Expected class-level @package {$expectPackage}, "
                    . "actual value '{$actualPackage}', "
                    . "in class {$class}";
                $this->logger->error($message);
            }
        }

        if ($missing) {
            throw new Exception('Docblocks do not appear valid.');
        }

        // are there other invalidities?
        foreach ($output as $line) {
            // this line indicates the end of parsing
            if (substr($line, 0, 41) == 'Transform analyzed project into artifacts') {
                break;
            }
            // invalid lines have 2-space indents
            if (substr($line, 0, 2) == '  ') {
                throw new Exception('Docblocks do not appear valid.');
            }
        }
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
