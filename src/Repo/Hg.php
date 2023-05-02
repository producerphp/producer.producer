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
        $data = $this->fsio->parseIni('.hg/hgrc', true);

        if (! isset($data['paths']['default'])) {
            throw new Exception('Could not determine default path.');
        }

        $this->origin = $data['paths']['default'];
    }

    public function getBranch() : string
    {
        $branch = $this->shell('hg branch', $output, $return);
        if ($return) {
            throw new Exception(implode(PHP_EOL, $output), $return);
        }
        return trim($branch);
    }

    public function sync() : void
    {
        $this->shell('hg pull -u', $output, $return);
        if ($return) {
            throw new Exception('Pull and update failed.');
        }

        // this allows for "no error" (0) and "nothing to push" (1).
        // cf. http://stackoverflow.com/questions/18536926/
        $this->shell('hg push --rev .', $output, $return);
        if ($return > 1) {
            throw new Exception('Push failed.');
        }

        $this->checkStatus();
    }

    public function checkStatus() : void
    {
        $this->shell('hg status', $output, $return);
        if ($return || $output) {
            throw new Exception('Status failed.');
        }
    }

    public function getChangesDate() : string
    {
        $changes = $this->checkSupportFile('CHANGELOG');
        $this->shell("hg log --limit 1 {$changes}", $output, $return);
        return $this->findDate($output);
    }

    public function getLastCommitDate() : string
    {
        $this->shell("hg log --limit 1", $output, $return);
        return $this->findDate($output);
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
        $this->shell("hg log --rev : --date '$date to now'", $output);
        return $output;
    }

    public function tag(string $version, string $message) : void
    {
        $message = escapeshellarg($message);
        $last = $this->shell("hg tag $version --message=$message", $output, $return);

        if ($return) {
            throw new Exception($last);
        }
    }
}
