<?php
declare(strict_types=1);

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
     * @param string $hostname The hostname of GitHub service.
     *
     * @param string $user   The API username.
     *
     * @param string $token  The API secret token.
     */
    public function __construct($origin, $hostname, $user, $token)
    {
        // @see https://developer.github.com/v3/enterprise
        if (strpos($hostname, 'github.com') === false) {
            $hostname .= '/api/v3';
        }

        $this->setHttp("https://{$user}:{$token}@{$hostname}");
        $this->setRepoNameFromOrigin($origin);
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
            'body' => $repo->getChanges(),
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
