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
     * @var array<string, ?string>
     */
    protected array $data = [
        'bitbucket_hostname' => 'api.bitbucket.org',
        'bitbucket_username' => null,
        'bitbucket_password' => null,
        'github_hostname' => 'api.github.com',
        'github_username' => null,
        'github_token' => null,
        'gitlab_hostname' => 'gitlab.com',
        'gitlab_token' => null,
        'quality_command' => null,
    ];

    protected string $configFile = '.producer/config';

    public function __construct(HomeFsio $homefs, RepoFsio $repofs)
    {
        $this->loadHomeConfig($homefs);
        $this->loadRepoConfig($repofs);

        foreach ($this->data as $key => $val) {
            if (trim((string) $val) === '') {
                $this->data[$key] = null;
            }
        }
    }

    protected function loadHomeConfig(HomeFsio $homefs) : void
    {
        if (! $homefs->isFile($this->configFile)) {
            $path = $homefs->path($this->configFile);
            throw new Exception("Config file {$path} not found.");
        }

        $config = $homefs->parseIni($this->configFile);
        $this->data = array_replace_recursive($this->data, $config);
    }

    public function loadRepoConfig(RepoFsio $repofs) : void
    {
        if (! $repofs->isFile($this->configFile)) {
            return;
        }

        $config = $repofs->parseIni($this->configFile);
        $this->data = array_replace_recursive($this->data, $config);
    }

    public function get(string $key) : ?string
    {
        return $this->data[$key];
    }

    public function has(string $key) : bool
    {
        return (isset($this->data[$key]));
    }

    /**
     * @return array<string, ?string>
     */
    public function getAll() : array
    {
        return $this->data;
    }
}
