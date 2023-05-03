<?php
declare(strict_types=1);

namespace Producer\Repo;

use Producer\Exception;
use Producer\Repo;

class Hg extends Repo
{
    protected function setOrigin() : void
    {
        /** @var array{'paths': ?array{'default': ?string}} */
        $data = $this->repofs->parseIni('.hg/hgrc', true);

        if (! isset($data['paths']['default'])) {
            throw new Exception('Could not determine default path.');
        }

        $this->origin = $data['paths']['default'];
    }

    public function getBranch() : string
    {
        $exec = $this->exec('hg branch');

        if ($exec->error) {
            throw new Exception(implode(PHP_EOL, $exec->output), $exec->error);
        }

        return trim($exec->last);
    }

    public function sync() : void
    {
        $exec = $this->exec('hg pull -u');

        if ($exec->error) {
            throw new Exception('Pull and update failed.');
        }

        // this allows for "no error" (0) and "nothing to push" (1).
        // cf. http://stackoverflow.com/questions/18536926/
        $exec = $this->exec('hg push --rev .');

        if ($exec->error > 1) {
            throw new Exception('Push failed.');
        }
    }

    public function checkStatus() : void
    {
        $exec = $this->exec('hg status');

        if ($exec->error || $exec->output) {
            throw new Exception('Status failed.');
        }
    }

    public function getChangelogDate() : string
    {
        $file = $this->checkSkeletonFile('CHANGELOG');
        $exec = $this->exec("hg log --limit 1 {$file}");
        return $this->findDate($exec->output);
    }

    public function getLastCommitDate() : string
    {
        $exec = $this->exec("hg log --limit 1");
        return $this->findDate($exec->output);
    }

    /**
     * @param string[] $lines
     */
    protected function findDate(array $lines) : string
    {
        foreach ($lines as $line) {
            if (substr($line, 0, 5) == 'date:') {
                return trim(substr($line, 5));
            }
        }

        throw new Exception("No 'date:' line found.");
    }

    /**
     * @return string[]
     */
    public function logSinceDate(string $date) : array
    {
        $date = escapeshellarg("{$date} to now");
        $exec = $this->exec("hg log --rev : --date {$date}");
        return $exec->output;
    }

    public function tag(string $version, string $message) : void
    {
        $version = escapeshellarg($version);
        $message = escapeshellarg($message);
        $exec = $this->exec("hg tag {$version} --message={$message}");

        if ($exec->error) {
            throw new Exception($exec->last);
        }
    }
}
