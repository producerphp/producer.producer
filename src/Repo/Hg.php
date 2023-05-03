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
        $shell = $this->shell('hg branch');

        if ($shell->error) {
            throw new Exception(implode(PHP_EOL, $shell->output), $shell->error);
        }

        return trim($shell->last);
    }

    public function sync() : void
    {
        $shell = $this->shell('hg pull -u');

        if ($shell->error) {
            throw new Exception('Pull and update failed.');
        }

        // this allows for "no error" (0) and "nothing to push" (1).
        // cf. http://stackoverflow.com/questions/18536926/
        $shell = $this->shell('hg push --rev .');

        if ($shell->error > 1) {
            throw new Exception('Push failed.');
        }

        $this->checkStatus();
    }

    public function checkStatus() : void
    {
        $shell = $this->shell('hg status');

        if ($shell->error || $shell->output) {
            throw new Exception('Status failed.');
        }
    }

    public function getChangelogDate() : string
    {
        $file = $this->checkSkeletonFile('CHANGELOG');
        $shell = $this->shell("hg log --limit 1 {$file}");
        return $this->findDate($shell->output);
    }

    public function getLastCommitDate() : string
    {
        $shell = $this->shell("hg log --limit 1");
        return $this->findDate($shell->output);
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
        $shell = $this->shell("hg log --rev : --date {$date}");
        return $shell->output;
    }

    public function tag(string $version, string $message) : void
    {
        $version = escapeshellarg($version);
        $message = escapeshellarg($message);
        $shell = $this->shell("hg tag {$version} --message={$message}");

        if ($shell->error) {
            throw new Exception($shell->last);
        }
    }
}
