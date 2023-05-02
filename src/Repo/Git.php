<?php
declare(strict_types=1);

namespace Producer\Repo;

use Producer\Exception;
use Producer\Repo;

class Git extends Repo
{
    protected function setOrigin()
    {
        $data = $this->fsio->parseIni('.git/config', true);

        if (! isset($data['remote origin']['url'])) {
            throw new Exception('Could not determine remote origin.');
        }

        $this->origin = $data['remote origin']['url'];
    }

    public function getBranch()
    {
        $branch = $this->shell('git rev-parse --abbrev-ref HEAD', $output, $return);
        if ($return) {
            throw new Exception(implode(PHP_EOL, $output), $return);
        }
        return trim($branch);
    }

    public function sync()
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

    public function checkStatus()
    {
        $this->shell('git status --porcelain', $output, $return);
        if ($return || $output) {
            throw new Exception('Status failed.');
        }
    }

    public function getChangesDate()
    {
        $changes = $this->config->get('files')['changes'];
        if (! $this->fsio->isFile($changes)) {
            throw new Exception("File '{$changes}' is missing.");
        }

        $this->shell("git log -1 {$changes}", $output, $return);
        return $this->findDate($output);
    }

    public function getLastCommitDate()
    {
        $this->shell("git log -1", $output, $return);
        return $this->findDate($output);
    }

    protected function findDate(array $lines)
    {
        foreach ($lines as $line) {
            if (substr($line, 0, 5) == 'Date:') {
                return trim(substr($line, 5));
            }
        }

        throw new Exception("No 'Date:' line found.");
    }

    public function logSinceDate($date)
    {
        $this->shell("git log --name-only --since='$date' --reverse", $output);
        return $output;
    }

    public function tag($name, $message)
    {
        $message = escapeshellarg($message);
        $last = $this->shell("git tag -a $name --message=$message", $output, $return);
        if ($return) {
            throw new Exception($last);
        }
    }
}
