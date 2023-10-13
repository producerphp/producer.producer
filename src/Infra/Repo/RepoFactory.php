<?php
declare(strict_types=1);

namespace Producer\Infra\Repo;

use Producer\Infra\Config;
use Producer\Infra\Exception;
use Producer\Infra\Exec;
use Producer\Infra\Fsio\RepoFsio;
use Producer\Infra\Repo;
use Psr\Log\LoggerInterface;

class RepoFactory
{
    public function __construct(
        protected RepoFsio $repofs,
        protected LoggerInterface $logger,
        protected Config $config,
        protected Exec $exec,
    ) {
    }

    public function new() : Repo
    {
        if ($this->repofs->isDir('.git')) {
            return new Git($this->repofs, $this->logger, $this->config, $this->exec);
        }

        if ($this->repofs->isDir('.hg')) {
            return new Hg($this->repofs, $this->logger, $this->config, $this->exec);
        }

        throw new Exception("Could not find .git or .hg files.");
    }
}
