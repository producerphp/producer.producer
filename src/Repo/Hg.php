<?php
declare(strict_types=1);

namespace Producer\Repo;

use Producer\Exception;

/**
 *
 * A Mercurial repository.
 *
 * @package producer/producer
 *
 * @see https://www.mercurial-scm.org/wiki/GitConcepts
 *
 */
class Hg extends AbstractRepo
{
    /**
     *
     * Retains the remote origin for the repository from the VCS config file.
     *
     */
    protected function setOrigin()
    {
        $data = $this->fsio->parseIni('.hg/hgrc', true);

        if (! isset($data['paths']['default'])) {
            throw new Exception('Could not determine default path.');
        }

        $this->origin = $data['paths']['default'];
    }

    /**
     *
     * Returns the current branch.
     *
     * @return string
     *
     */
    public function getBranch()
    {
        $branch = $this->shell('hg branch', $output, $return);
        if ($return) {
            throw new Exception(implode(PHP_EOL, $output), $return);
        }
        return trim($branch);
    }

    /**
     *
     * Syncs the repository with the origin: pull, push, and status.
     *
     */
    public function sync()
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

    /**
     *
     * Checks that the local status is clean.
     *
     */
    public function checkStatus()
    {
        $this->shell('hg status', $output, $return);
        if ($return || $output) {
            throw new Exception('Status failed.');
        }
    }

    /**
     *
     * Gets the last-committed date of the CHANGES file.
     *
     * @return string
     *
     */
    public function getChangesDate()
    {
        $changes = $this->config->get('files')['changes'];
        if (! $this->fsio->isFile($changes)) {
            throw new Exception("File '{$changes}' is missing.");
        }

        $this->shell("hg log --limit 1 {$changes}", $output, $return);
        return $this->findDate($output);
    }

    /**
     *
     * Gets the last-committed date of the repository.
     *
     * @return string
     *
     */
    public function getLastCommitDate()
    {
        $this->shell("hg log --limit 1", $output, $return);
        return $this->findDate($output);
    }

    /**
     *
     * Finds the date: line within an array of lines.
     *
     * @param array $lines An array of lines.
     *
     * @return string
     *
     */
    protected function findDate(array $lines)
    {
        foreach ($lines as $line) {
            if (substr($line, 0, 5) == 'date:') {
                return trim(substr($line, 5));
            }
        }

        throw new Exception("No 'date:' line found.");
    }

    /**
     *
     * Returns the log since a particular date, in chronological order.
     *
     * @param string $date Return log entries since this date.
     *
     * @return array
     *
     */
    public function logSinceDate($date)
    {
        $this->shell("hg log --rev : --date '$date to now'", $output);
        return $output;
    }

    /**
     *
     * Tags the repository.
     *
     * @param string $name The tag name.
     *
     * @param string $message The message for the tag.
     *
     */
    public function tag($name, $message)
    {
        $message = escapeshellarg($message);
        $last = $this->shell("hg tag $name --message=$message", $output, $return);
        if ($return) {
            throw new Exception($last);
        }
    }
}
