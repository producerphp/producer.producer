<?php
/**
 *
 * This file is part of Producer for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Producer\Api;

use Producer\Exception;
use Producer\Repo\RepoInterface;

/**
 *
 * The Github API.
 *
 * @package producer/producer
 *
 */
class Github extends AbstractApi
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
    public function __construct($origin, $user, $token)
    {
        // set the HTTP object
        $this->setHttp("https://{$user}:{$token}@api.github.com");

        // start by presuming HTTPS
        $repoName = parse_url($origin, PHP_URL_PATH);

        // check for SSH
        $ssh = 'git@github.com:';
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
        $issues = [];

        $yield = $this->httpGet(
            "/repos/{$this->repoName}/issues",
            [
                'sort' => 'created',
                'direction' => 'asc',
            ]
        );

        foreach ($yield as $issue) {
            $issues[] = (object) [
                'title' => $issue->title,
                'number' => $issue->number,
                'url' => $issue->html_url,
            ];
        }

        return $issues;
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
        $prerelease = substr($version, 0, 2) == '0.'
            || strpos($version, 'dev') !== false
            || strpos($version, 'alpha') !== false
            || strpos($version, 'beta') !== false;

        $query = [];

        $data = [
            'tag_name' => $version,
            'target_commitish' => $repo->getBranch(),
            'name' => $version,
            'body' => $repo->getChangelog(),
            'draft' => false,
            'prerelease' => $prerelease,
        ];

        $response = $this->httpPost(
            "/repos/{$this->repoName}/releases",
            $query,
            $data
        );

        if (! isset($response->id)) {
            $message = var_export((array) $response, true);
            throw new Exception($message);
        }

        $repo->sync();
    }
}
