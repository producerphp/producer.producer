<?php
declare(strict_types=1);

namespace Producer\Api;

use Producer\Api;
use Producer\Exception;
use Producer\Repo;

class Bitbucket extends Api
{
    public function __construct(string $origin, string $hostname, string $user, string $pass)
    {
        $this->setHttp("https://{$user}:{$pass}@{$hostname}/2.0");
        $this->setRepoNameFromOrigin($origin);
    }

    protected function setRepoNameFromOrigin($origin)
    {
        $repoName = parse_url($origin, PHP_URL_PATH);
        $repoName = preg_replace('/\.hg$/', '', $repoName);
        $this->repoName = trim($repoName, '/');
    }

    protected function httpValues($json)
    {
        return $json->values;
    }

    public function issues() : array
    {
        $issues = [];

        $yield = $this->httpGet(
            "/repositories/{$this->repoName}/issues",
            [
                'sort' => 'created_on'
            ]
        );

        foreach ($yield as $issue) {
            $issues[] = (object) [
                'title' => $issue->title,
                'number' => $issue->id,
                'url' => "https://bitbucket.org/{$this->repoName}/issues/{$issue->id}",
            ];
        }

        return $issues;
    }
    public function release(Repo $repo, string $version)
    {
        $repo->tag($version, "Released $version");
        $repo->sync();
    }
}
