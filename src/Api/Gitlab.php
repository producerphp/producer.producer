<?php
declare(strict_types=1);

namespace Producer\Api;

use Producer\Api;
use Producer\Exception;
use Producer\Repo\RepoInterface;

class Gitlab extends Api
{
    protected string $token;

    protected string $hostname;

    public function __construct(string $origin, string $hostname, string $token)
    {
        $this->setHttp("https://{$hostname}/api/v3");
        $this->token = $token;
        $this->hostname = $hostname;
        $this->setRepoNameFromOrigin($origin);
    }

    protected function httpQuery(array $query, int $page = 0)
    {
        $query['private_token'] = $this->token;
        return parent::httpQuery($query, $page);
    }

    public function issues() : array
    {
        $issues = [];

        $repoName = urlencode($this->repoName);
        $yield = $this->httpGet(
            "/projects/{$repoName}/issues",
            [
                'sort' => 'asc',
            ]
        );

        foreach ($yield as $issue) {
            $issues[] = (object) [
                'number' => $issue->iid,
                'title' => $issue->title,
                'url' => "https://{$this->hostname}/{$this->repoName}/issues/{$issue->iid}",
            ];
        }

        return $issues;
    }

    public function release(RepoInterface $repo, $version)
    {
        $query = [];

        $data = [
            'id' => $this->repoName,
            'tag_name' => $version,
            'ref' => $repo->getBranch(),
            'release_description' => $repo->getChanges()
        ];

        $repoName = urlencode($this->repoName);
        $response = $this->httpPost(
            "/projects/{$repoName}/repository/tags",
            $query,
            $data
        );

        if (! isset($response->name)) {
            $message = var_export((array) $response, true);
            throw new Exception($message);
        }

        $repo->sync();
    }
}
