<?php
declare(strict_types=1);

namespace Producer\Api;

use Producer\Config;
use Producer\Exception;
use Producer\Http;
use Producer\Repo\RepoInterface;

class ApiFactory
{
    public function __construct(
        protected RepoInterface $repo,
        protected Config $config
    ) {
    }

    public function new()
    {
        $origin = $this->repo->getOrigin();
        $config = $this->config;

        switch (true) {

            case ($this->isGithubBased($origin, $config)):
                return new Github(
                    $origin,
                    $config->get('github_hostname'),
                    $config->get('github_username'),
                    $config->get('github_token')
                );

            case ($this->isGitlabBased($origin, $config)):
                return new Gitlab(
                    $origin,
                    $config->get('gitlab_hostname'),
                    $config->get('gitlab_token')
                );

            case ($this->isBitbucketBased($origin, $config) !== false):
                return new Bitbucket(
                    $origin,
                    $config->get('bitbucket_hostname'),
                    $config->get('bitbucket_username'),
                    $config->get('bitbucket_password')
                );

            default:
                throw new Exception("Producer will not work with {$origin}.");

        }
    }

    /**
     *
     * Is GitHub-based if hostname is `api.github.com` and the repo origin
     * contains `github.com`.
     *
     * Alternatively, the project is using GitHub Enterprise if hostname is NOT
     * `api.github.com` and the configured hostname matches the repo origin.
     *
     * @param $origin string The repo origin.
     *
     * @param $config Config A config object.
     *
     * @return bool
     *
     */
    protected function isGithubBased($origin, Config $config)
    {
        if ($config->get('github_hostname') === 'api.github.com') {
            return strpos($origin, 'github.com') !== false;
        } else {
            return strpos($origin, $config->get('github_hostname')) !== false;
        }
    }

    /**
     *
     * Is GitLab-based if hostname is `gitlab.com` and the repo origin contains
     * `github.com`.
     *
     * Alternatively, the project is using self-hosted GitLab if hostname is NOT
     * `gitlab.com` and the configured hostname matches the repo origin.
     *
     * @param $origin string The repo origin.
     *
     * @param $config Config A config object.
     *
     * @return bool
     *
     */
    protected function isGitlabBased($origin, Config $config)
    {
        if ($config->get('gitlab_hostname') === 'gitlab.com') {
            return strpos($origin, 'gitlab.com') !== false;
        } else {
            return strpos($origin, $config->get('gitlab_hostname')) !== false;
        }
    }

    /**
     *
     * Is Bitbucket-based if hostname is `api.bitbucket.org` and the repo origin
     * contains `bitbucket.org`.
     *
     * Alternatively, the project is using self-hosted Bitbucket if hostname is
     * NOT `bitbucket.org` and the configured hostname matches the repo origin.
     *
     * @param $origin string The repo origin.
     *
     * @param $config Config A config object.
     *
     * @return bool
     *
     */
    protected function isBitbucketBased($origin, Config $config)
    {
        if ($config->get('bitbucket_hostname') === 'api.bitbucket.org') {
            return strpos($origin, 'bitbucket.org') !== false;
        } else {
            return strpos($origin, $config->get('bitbucket_hostname')) !== false;
        }
    }
}
