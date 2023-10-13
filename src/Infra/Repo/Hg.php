<?php
declare(strict_types=1);

namespace Producer\Infra\Repo;

use Producer\Infra\Exception;
use Producer\Infra\Repo;

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
        $execResult = $this->exec->logged('hg branch');

        if ($execResult->isError()) {
            throw $execResult->asLongException();
        }

        return trim($execResult->lastLine);
    }

    public function sync() : void
    {
        $execResult = $this->exec->logged('hg pull -u');

        if ($execResult->isError()) {
            throw new Exception('Pull and update failed.');
        }

        // this allows for "no error" (0) and "nothing to push" (1).
        // cf. http://stackoverflow.com/questions/18536926/
        $execResult = $this->exec->logged('hg push --rev .');

        if ($execResult->exitCode > 1) {
            throw new Exception('Push failed.');
        }
    }

    public function checkStatus() : void
    {
        $execResult = $this->exec->logged('hg status');

        if ($execResult->isError() || $execResult->lines) {
            throw new Exception('Status failed.');
        }
    }

    public function getChangelogDate() : string
    {
        $file = $this->checkSkeletonFile('CHANGELOG');
        $execResult = $this->exec->logged("hg log --limit 1 {$file}");
        return $this->findDate($execResult->lines);
    }

    public function getLastCommitDate() : string
    {
        $execResult = $this->exec->logged("hg log --limit 1");
        return $this->findDate($execResult->lines);
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
        $execResult = $this->exec->logged("hg log --rev : --date {$date}");
        return $execResult->lines;
    }

    public function tag(string $version, string $message) : void
    {
        $version = escapeshellarg($version);
        $message = escapeshellarg($message);
        $execResult = $this->exec->logged("hg tag {$version} --message={$message}");

        if ($execResult->isError()) {
            throw $execResult->asException();
        }
    }

    /**
     * @return array<int, string>
     */
    public function getVersions() : array
    {
        $execResult = $this->exec->result('hg tags -q');
        $versions = $execResult->lines;
        usort(
            $versions,
            fn ($a, $b) => version_compare($a, $b),
        );
        return $versions;
    }

    public function getVersionDate(string $version) : string
    {
        $execResult = $this->exec->result("hg log --rev {$version}");
        $dateToTimestamp = function (array $lines) : int {
            foreach ($lines as $line) {
                if (substr($line, 0, 5) == 'date:') {
                    $date = trim(substr($line, 5));
                    return (int) strtotime($date);
                }
            }

            throw new Exception('No date found in log.');
        };
        return date('r', $dateToTimestamp($execResult->lines) + 1);
    }
}
