<?php
/**
 *
 * This file is part of Producer for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Producer;

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
        'bitbucket_password' => null,
        'bitbucket_username' => null,
        'github_token' => null,
        'github_username' => null,
        'gitlab_token' => null,
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
    public function __construct(Fsio $homefs, Fsio $repofs)
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
    protected function loadHomeConfig(Fsio $homefs)
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
    public function loadRepoConfig(Fsio $repofs)
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
