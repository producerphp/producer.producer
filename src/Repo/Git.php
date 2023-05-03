<?php
declare(strict_types=1);

namespace Producer\Repo;

use Producer\Exception;
use Producer\Repo;

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
        $shell = $this->shell('git rev-parse --abbrev-ref HEAD');

        if ($shell->error) {
            throw new Exception(implode(PHP_EOL, $shell->output), $shell->error);
        }

        return trim($shell->last);
    }

    public function sync() : void
    {
        $shell = $this->shell('git pull');

        if ($shell->error) {
            throw new Exception('Pull failed.');
        }

        $shell = $this->shell('git push');

        if ($shell->error) {
            throw new Exception('Push failed.');
        }

        $this->checkStatus();
    }

    public function checkStatus() : void
    {
        $shell = $this->shell('git status --porcelain');

        if ($shell->error || $shell->output) {
            throw new Exception('Status failed.');
        }
    }

    public function getChangelogDate() : string
    {
        $file = $this->checkSkeletonFile('CHANGELOG');
        $shell = $this->shell("git log -1 {$file}");
        return $this->findDate($shell->output);
    }

    public function getLastCommitDate() : string
    {
        $shell = $this->shell("git log -1");
        return $this->findDate($shell->output);
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
        $shell = $this->shell("git log --name-only --since={$date} --reverse");
        return $shell->output;
    }

    public function tag(string $version, string $message) : void
    {
        $version = escapeshellarg($version);
        $message = escapeshellarg($message);
        $shell = $this->shell("git tag -a {$version} --message={$message}");

        if ($shell->error) {
            throw new Exception($shell->last);
        }
    }
}
