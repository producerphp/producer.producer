<?php
/**
 *
 * This file is part of Producer for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Producer\Api;


use Producer\Repo\RepoInterface;

/**
 * A Generic Git repo reached via SSH API.
 *
 * @package producer/producer
 */
class Generic extends AbstractApi
{
    /**
     *
     * Constructor.
     *
     * @param string $origin The repository remote origin.
     *
     * @param string $user The API username.
     *
     * @param string $token The API secret token.
     *
     */
    public function __construct($origin)
    {
        $repoName = parse_url($origin, PHP_URL_PATH);

        // DEBUG
        echo "parsed repoName = '$repoName'\n";
        // END DEBUG

        // check for SSH
        $ssh = 'git@';
        $len = strlen($ssh);
        if (substr($origin, 0, $len) == $ssh) {
            $repoName = substr($origin, $len);
        }

        // strip .git from the end
        if (substr($repoName, -4) == '.git') {
            $repoName = substr($repoName, 0, -4);
        }

        // retain
        $this->repoName = trim($repoName, '/');

        // DEBUG
        echo "this->repoName now = {$this->repoName}\n";
        // END DEBUG
    }

    /**
     *
     * Returns a list of open issues from the API.
     *
     * @return array
     *
     */
    public function issues()
    {
        // For a generic HTTP/SSH Git repo, any issue tracking would be in
        // an external location, e.g. Redmine, Trac, etc.
        // TODO: Return an empty (no issues) array for now.

        return [];
    }

    /**
     *
     * Submits a release to the API.
     *
     * @param RepoInterface $repo The repository.
     *
     * @param string $version The version number to release.
     *
     */
    public function release(RepoInterface $repo, $version)
    {
        // For a generic HTTP/SSH Git repo, a release could be something as
        // simple as an annotated Git Tag, or perhaps that plus a merge to
        // a release branch of some sort.

        $repo->tag($version, 'Release');

        // TODO: Implement release() method.
    }
}
