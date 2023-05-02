<?php
declare(strict_types=1);

namespace Producer\Api;

use Producer\Api;
use Producer\Config;
use Producer\Exception;
use Producer\Http;
use Producer\Repo;

class ApiFactory
{
    public function __construct(
        protected Repo $repo,
        protected Config $config
    ) {
    }

    public function new() : Api
    {
        $origin = $this->repo->getOrigin();

        if ($this->isGithubBased($origin)) {
            return new Github(
                $origin,
                (string) $this->config->get('github_hostname'),
                (string) $this->config->get('github_username'),
                (string) $this->config->get('github_token')
            );
        }

        if ($this->isGitlabBased($origin)) {
            return new Gitlab(
                $origin,
                (string) $this->config->get('gitlab_hostname'),
                (string) $this->config->get('gitlab_token')
            );
        }

        if ($this->isBitbucketBased($origin) !== false) {
            return new Bitbucket(
                $origin,
                (string) $this->config->get('bitbucket_hostname'),
                (string) $this->config->get('bitbucket_username'),
                (string) $this->config->get('bitbucket_password')
            );
        }

        throw new Exception("Producer will not work with {$origin}.");
    }

    protected function isGithubBased(string $origin) : bool
    {
        if ($this->config->get('github_hostname') === 'api.github.com') {
            return strpos($origin, 'github.com') !== false;
        }

        return strpos($origin, (string) $this->config->get('github_hostname')) !== false;
    }

    protected function isGitlabBased(string $origin) : bool
    {
        if ($this->config->get('gitlab_hostname') === 'gitlab.com') {
            return strpos($origin, 'gitlab.com') !== false;
        }

        return strpos($origin, (string) $this->config->get('gitlab_hostname')) !== false;
    }

    protected function isBitbucketBased(string $origin) : bool
    {
        if ($this->config->get('bitbucket_hostname') === 'api.bitbucket.org') {
            return strpos($origin, 'bitbucket.org') !== false;
        }

        return strpos($origin, (string) $this->config->get('bitbucket_hostname')) !== false;
    }
}
