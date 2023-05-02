<?php
declare(strict_types=1);

namespace Producer\Repo;

use Producer\Config;
use Producer\Exception;
use Producer\Fsio\RepoFsio;
use Psr\Log\LoggerInterface;

class RepoFactory
{
    public function __construct(
        protected RepoFsio $repofs,
        protected LoggerInterface $logger,
        protected Config $config
    ) {
    }

    public function new()
    {
        if ($this->repofs->isDir('.git')) {
            return new Git($this->repofs, $this->logger, $this->config);
        };

        if ($this->repofs->isDir('.hg')) {
            return new Hg($this->repofs, $this->logger, $this->config);
        }

        throw new Exception("Could not find .git or .hg files.");
    }
}
