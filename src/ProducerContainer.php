<?php
declare(strict_types=1);

namespace Producer;

use Producer\Api\ApiFactory;
use Producer\Fsio\HomeFsio;
use Producer\Fsio\RepoFsio;
use Producer\Repo\RepoFactory;
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
    public function __construct(
        protected string $homedir,
        protected string $repodir,
        protected mixed $stdout = STDOUT,
        protected mixed $stderr = STDERR
    ) {
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
        $logger = new Stdlog($this->stdout, $this->stderr);

        $name = trim($name);
        if (! $name || $name == 'help') {
            return new Command\Help($logger);
        }

        $class = "Producer\\Command\\" . ucfirst($name);
        if (! class_exists($class)) {
            throw new Exception("Command '$name' not found.");
        }

        $homefs = new HomeFsio($this->homedir);
        $repofs = new RepoFsio($this->repodir);
        $config = new Config($homefs, $repofs);

        $repoFactory = new RepoFactory($repofs, $logger, $config);
        $repo = $repoFactory->new();

        $apiFactory = new ApiFactory($repo, $config);
        $api = $apiFactory->new();

        return new $class($logger, $repo, $api, $config);
    }
}
