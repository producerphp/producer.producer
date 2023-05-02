<?php
declare(strict_types=1);

namespace Producer\Repo;

use Producer\Exception;

/**
 *
 * A Git repository.
 *
 * @package producer/producer
 *
 */
class Git extends AbstractRepo
{
    /**
     *
     * Retains the remote origin for the repository from the VCS config file.
     *
     */
    protected function setOrigin()
    {
        $data = $this->fsio->parseIni('.git/config', true);

        if (! isset($data['remote origin']['url'])) {
            throw new Exception('Could not determine remote origin.');
        }

        $this->origin = $data['remote origin']['url'];
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
        $branch = $this->shell('git rev-parse --abbrev-ref HEAD', $output, $return);
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

    /**
     *
     * Checks that the local status is clean.
     *
     */
    public function checkStatus()
    {
        $this->shell('git status --porcelain', $output, $return);
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

        $this->shell("git log -1 {$changes}", $output, $return);
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
        $this->shell("git log -1", $output, $return);
        return $this->findDate($output);
    }

    /**
     *
     * Finds the Date: line within an array of lines.
     *
     * @param array $lines An array of lines.
     *
     * @return string
     *
     */
    protected function findDate(array $lines)
    {
        foreach ($lines as $line) {
            if (substr($line, 0, 5) == 'Date:') {
                return trim(substr($line, 5));
            }
        }

        throw new Exception("No 'Date:' line found.");
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
        $this->shell("git log --name-only --since='$date' --reverse", $output);
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
        $last = $this->shell("git tag -a $name --message=$message", $output, $return);
        if ($return) {
            throw new Exception($last);
        }
    }
}
