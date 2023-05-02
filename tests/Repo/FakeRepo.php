<?php
declare(strict_types=1);

namespace Producer\Repo;

use Producer\Config;
use Producer\Fsio\RepoFsio;
use Producer\Repo;
use Producer\Stdlog;

class FakeRepo extends Repo
{
    public function setOrigin() : void
    {
        $this->origin = 'FAKE';
    }

    public function getBranch() : string
    {
        return 'FAKE';
    }

    public function checkStatus() : void
    {
    }

    public function tag(string $version, string $message) : void
    {
    }

    public function sync() : void
    {
    }

    public function getChangesDate() : string
    {
        return 'FAKE';
    }

    public function getLastCommitDate() : string
    {
        return 'FAKE';
    }

    /**
     * @return string[]
     */
    public function logSinceDate(string $date) : array
    {
        return ['FAKE'];
    }
}
