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
        $exec = $this->exec('git rev-parse --abbrev-ref HEAD');

        if ($exec->error) {
            throw new Exception(implode(PHP_EOL, $exec->output), $exec->error);
        }

        return trim($exec->last);
    }

    public function sync() : void
    {
        $exec = $this->exec('git pull');

        if ($exec->error) {
            throw new Exception('Pull failed.');
        }

        $exec = $this->exec('git push');

        if ($exec->error) {
            throw new Exception('Push failed.');
        }
    }

    public function checkStatus() : void
    {
        $exec = $this->exec('git status --porcelain');

        if ($exec->error || $exec->output) {
            throw new Exception('Status failed.');
        }
    }

    public function getChangelogDate() : string
    {
        $file = $this->checkSkeletonFile('CHANGELOG');
        $exec = $this->exec("git log -1 {$file}");
        return $this->findDate($exec->output);
    }

    public function getLastCommitDate() : string
    {
        $exec = $this->exec("git log -1");
        return $this->findDate($exec->output);
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
        $exec = $this->exec("git log --name-only --since={$date} --reverse");
        return $exec->output;
    }

    public function tag(string $version, string $message) : void
    {
        $version = escapeshellarg($version);
        $message = escapeshellarg($message);
        $exec = $this->exec("git tag -a {$version} --message={$message}");

        if ($exec->error) {
            throw new Exception($exec->last);
        }
    }
}
