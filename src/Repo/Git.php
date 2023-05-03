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
        $branch = $this->shell('git rev-parse --abbrev-ref HEAD', $output, $return);

        if ($return) {
            throw new Exception(implode(PHP_EOL, $output), $return);
        }

        return trim($branch);
    }

    public function sync() : void
    {
        $this->shell('git pull', $output, $return);

        if ($return) {
            throw new Exception('Pull failed.');
        }

        $this->shell('git push', $output, $return);

        if ($return) {
            throw new Exception('Push failed.');
        }

        $this->checkStatus();
    }

    public function checkStatus() : void
    {
        $this->shell('git status --porcelain', $output, $return);

        if ($return || $output) {
            throw new Exception('Status failed.');
        }
    }

    public function getChangelogDate() : string
    {
        $changes = $this->checkSkeletonFile('CHANGELOG');
        $this->shell("git log -1 {$changes}", $output, $return);
        return $this->findDate($output);
    }

    public function getLastCommitDate() : string
    {
        $this->shell("git log -1", $output, $return);
        return $this->findDate($output);
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
        $this->shell("git log --name-only --since='$date' --reverse", $output);
        return $output;
    }

    public function tag(string $version, string $message) : void
    {
        $message = escapeshellarg($message);
        $last = $this->shell("git tag -a $version --message=$message", $output, $return);

        if ($return) {
            throw new Exception($last);
        }
    }
}
