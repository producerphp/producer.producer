<?php
declare(strict_types=1);

namespace Producer;

use Producer\Repo\RepoInterface;

/**
 *
 * A container for all Producer objects.
 *
 * @package producer/producer
 *
 */
class ProducerContainer
{
    /**
     *
     * The user's home directory.
     *
     * @var string
     *
     */
    protected $homedir;

    /**
     *
     * The repository directory.
     *
     * @var string
     *
     */
    protected $repodir;

    /**
     *
     * A resource handle pointing to STDOUT.
     *
     * @var resource
     *
     */
    protected $stdout;

    /**
     *
     * A resource handle pointing to STDERR.
     *
     * @var resource
     *
     */
    protected $stderr;

    /**
     *
     * Constructor.
     *
     * @param string $homedir The user's home directory.
     *
     * @param string $repodir The repository directory.
     *
     * @param resource A resource handle pointing to STDOUT.
     *
     * @param resource A resource handle pointing to STDERR.
     *
     */
    public function __construct(
        $homedir,
        $repodir,
        $stdout = STDOUT,
        $stderr = STDERR
    ) {
        $this->homedir = $homedir;
        $this->repodir = $repodir;
        $this->logger = new Stdlog($stdout, $stderr);
    }

    /**
     *
     * Returns a new Command object.
     *
     * @param string $name The command name.
     *
     * @return Command\CommandInterface
     *
     */
    public function newCommand($name)
    {
        $name = trim($name);
        if (! $name || $name == 'help') {
            return new Command\Help($this->logger);
        }

        $class = "Producer\\Command\\" . ucfirst($name);
        if (! class_exists($class)) {
            throw new Exception("Command '$name' not found.");
        }

        $homefs = new HomeFsio($this->homedir);
        $repofs = new RepoFsio($this->repodir);
        $config = new Config($homefs, $repofs);

        $repo = $this->newRepo($repofs, $config);
        $api = $this->newApi($repo->getOrigin(), $config);

        return new $class($this->logger, $repo, $api, $config);
    }

    /**
     *
     * Returns a new Repo object.
     *
     * @param Fsio $fsio A filesystem I/O object for the repository.
     *
     * @param Config $config Global and project configuration.
     *
     * @return RepoInterface
     *
     */
    protected function newRepo($fsio, Config $config)
    {
        if ($fsio->isDir('.git')) {
            return new Repo\Git($fsio, $this->logger, $config);
        };

        if ($fsio->isDir('.hg')) {
            return new Repo\Hg($fsio, $this->logger, $config);
        }

        throw new Exception("Could not find .git or .hg files.");
    }

    /**
     *
     * Returns a new Api object.
     *
     * @param string $origin The repository remote origin.
     *
     * @param Config $config A config object.
     *
     * @return RepoInterface
     *
     */
    protected function newApi($origin, Config $config)
    {
        switch (true) {

            case ($this->isGithubBased($origin, $config)):
                return new Api\Github(
                    $origin,
                    $config->get('github_hostname'),
                    $config->get('github_username'),
                    $config->get('github_token')
                );

            case ($this->isGitlabBased($origin, $config)):
                return new Api\Gitlab(
                    $origin,
                    $config->get('gitlab_hostname'),
                    $config->get('gitlab_token')
                );

            case ($this->isBitbucketBased($origin, $config) !== false):
                return new Api\Bitbucket(
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
