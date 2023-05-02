<?php
declare(strict_types=1);

namespace Producer\Api;

use Producer\Repo\RepoInterface;

/**
 *
 * The interface APIs.
 *
 * @package producer/producer
 *
 */
interface ApiInterface
{
    /**
     *
     * Returns the API repository name.
     *
     * @return string
     *
     */
    public function getRepoName();

    /**
     *
     * Returns a list of open issues from the API.
     *
     * @return array
     *
     */
    public function issues();

    /**
     *
     * Submits a release to the API.
     *
     * @param RepoInterface $repo The repository.
     *
     * @param string $version The version number to release.
     *
     */
    public function release(RepoInterface $repo, $version);
}
