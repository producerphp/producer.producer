<?php
declare(strict_types=1);

namespace Producer\Infra\Repo;

use Producer\Infra\Exception;
use Producer\Infra\Repo;

class Git extends Repo
{
    protected function setOrigin() : void
    {
        /** @var array{'remote origin': ?array{'url': ?string}} */
        $data = $this->repofs->parseIni('.git/config', true);

        if (! isset($data['remote origin']['url'])) {
            throw new Exception('Could not determine remote origin.');
        }

        $this->origin = $data['remote origin']['url'];
    }

    public function getBranch() : string
    {
        $execResult = $this->exec->logged('git rev-parse --abbrev-ref HEAD');

        if ($execResult->isError()) {
            throw $execResult->asLongException();
        }

        return trim($execResult->lastLine);
    }

    public function sync() : void
    {
        $execResult = $this->exec->logged('git pull');

        if ($execResult->isError()) {
            throw new Exception('Pull failed.');
        }

        $execResult = $this->exec->logged('git push');

        if ($execResult->isError()) {
            throw new Exception('Push failed.');
        }
    }

    public function checkStatus() : void
    {
        $execResult = $this->exec->logged('git status --porcelain');

        if ($execResult->isError() || $execResult->lines) {
            throw new Exception('Status failed.');
        }
    }

    public function getChangelogDate() : string
    {
        $file = $this->checkSkeletonFile('CHANGELOG');
        $execResult = $this->exec->logged("git log -1 {$file}");
        return $this->findDate($execResult->lines);
    }

    public function getLastCommitDate() : string
    {
        $execResult = $this->exec->logged("git log -1");
        return $this->findDate($execResult->lines);
    }

    /**
     * @param string[] $lines
     */
    protected function findDate(array $lines) : string
    {
        foreach ($lines as $line) {
            if (substr($line, 0, 5) == 'Date:') {
                return trim(substr($line, 5));
            }
        }

        throw new Exception("No 'Date:' line found.");
    }

    /**
     * @return string[]
     */
    public function logSinceDate(string $date) : array
    {
        $date = escapeshellarg($date);
        $execResult = $this->exec->logged("git log --name-only --since={$date} --reverse");
        return $execResult->lines;
    }

    public function tag(string $version, string $message) : void
    {
        $version = escapeshellarg($version);
        $message = escapeshellarg($message);
        $execResult = $this->exec->logged("git tag -a {$version} --message={$message}");

        if ($execResult->isError()) {
            throw $execResult->asException();
        }
    }
}
