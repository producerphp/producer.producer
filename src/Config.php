<?php
declare(strict_types=1);

namespace Producer;

use Producer\Fsio\HomeFsio;
use Producer\Fsio\RepoFsio;

/**
 *
 * Producer configuration values.
 *
 * @package producer/producer
 *
 */
class Config
{
    /**
     *
     * The config values with defaults.
     *
     * @var array
     *
     */
    protected $data = [
        'bitbucket_hostname' => 'api.bitbucket.org',
        'bitbucket_username' => null,
        'bitbucket_password' => null,
        'github_hostname' => 'api.github.com',
        'github_username' => null,
        'github_token' => null,
        'gitlab_hostname' => 'gitlab.com',
        'gitlab_token' => null,
        'package' => '',
        'commands' => [
            'phpdoc' => 'phpdoc',
            'phpunit' => 'phpunit',
        ],
        'files' => [
            'changes' => 'CHANGES.md',
            'contributing' => 'CONTRIBUTING.md',
            'license' => 'LICENSE.md',
            'phpunit' => 'phpunit.xml.dist',
            'readme' => 'README.md',
        ],
    ];

    /**
     *
     * The name of the Producer config file, wherever located.
     *
     * @var string
     *
     */
    protected $configFile = '.producer/config';

    /**
     *
     * Constructor.
     *
     * @param Fsio $homefs The user's home directory filesystem.
     *
     * @param Fsio $repofs The package repository filesystem.
     *
     * @throws Exception
     *
     */
    public function __construct(HomeFsio $homefs, RepoFsio $repofs)
    {
        $this->loadHomeConfig($homefs);
        $this->loadRepoConfig($repofs);
    }

    /**
     *
     * Loads the user's home directory Producer config file.
     *
     * @param Fsio $homefs
     *
     * @throws Exception
     *
     */
    protected function loadHomeConfig(HomeFsio $homefs)
    {
        if (! $homefs->isFile($this->configFile)) {
            $path = $homefs->path($this->configFile);
            throw new Exception("Config file {$path} not found.");
        }

        $config = $homefs->parseIni($this->configFile, true);
        $this->data = array_replace_recursive($this->data, $config);
    }

    /**
     *
     * Loads the project's config file, if it exists.
     *
     * @param Fsio $fsio
     *
     * @throws Exception
     *
     */
    public function loadRepoConfig(RepoFsio $repofs)
    {
        if (! $repofs->isFile($this->configFile)) {
            return;
        }

        $config = $repofs->parseIni($this->configFile, true);
        $this->data = array_replace_recursive($this->data, $config);
    }

    /**
     *
     * Returns a config value.
     *
     * @param string $key The config value.
     *
     * @return mixed
     *
     */
    public function get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        throw new Exception("No config value set for '$key'.");
    }

    /**
     *
     * Confirm that a config value is set
     *
     * @param $key
     *
     * @return bool
     *
     */
    public function has($key) {
        return (isset($this->data[$key]));
    }

    /**
     *
     * Return all configuration data
     *
     * @return array
     *
     */
    public function getAll()
    {
        return $this->data;
    }
}
