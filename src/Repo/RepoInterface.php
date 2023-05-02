<?php
/**
 *
 * This file is part of Producer for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Producer\Repo;

/**
 *
 * Interface for repository classes.
 *
 * @package producer/producer
 *
 */
interface RepoInterface
{
    /**
     *
     * Returns the VCS repo origin (i.e., the remote API origin).
     *
     * @return string
     *
     */
    public function getOrigin();

    /**
     *
     * Returns the current branch.
     *
     * @return string
     *
     */
    public function getBranch();

    /**
     *
     * Returns the Composer package name.
     *
     * @return string
     *
     */
    public function getPackage();

    /**
     *
     * Validates the `composer.json` file.
     *
     */
    public function validateComposer();

    /**
     *
     * Gets the `composer.json` file data.
     *
     * @return object
     *
     */
    public function getComposer();

    /**
     *
     * Syncs the repository with the origin: pull, push, and status.
     *
     */
    public function sync();

    /**
     *
     * Checks that the local status is clean.
     *
     */
    public function checkStatus();

    /**
     *
     * Checks the various support files.
     *
     */
    public function checkSupportFiles();

    /**
     *
     * Checks to see that the current year is in the LICENSE.
     *
     */
    public function checkLicenseYear();

    /**
     *
     * Runs the tests using phpunit.
     *
     */
    public function checkTests();

    /**
     *
     * Tags the repository.
     *
     * @param string $name The tag name.
     *
     * @param string $message The message for the tag.
     *
     */
    public function tag($name, $message);
}
