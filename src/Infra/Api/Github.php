<?php
declare(strict_types=1);

namespace Producer\Infra\Api;

use Producer\Infra\Api;
use Producer\Infra\Exception;
use Producer\Infra\Repo;
use Psr\Log\LoggerInterface;

class Github extends Api
{
    public function __construct(
        string $origin,
        string $hostname,
        string $user,
        string $token,
    ) {
        // @see https://developer.github.com/v3/enterprise
        if (strpos($hostname, 'github.com') === false) {
            $hostname .= '/api/v3';
        }

        $this->setHttp("https://{$user}:{$token}@{$hostname}");
        $this->setRepoNameFromOrigin($origin);
    }

    /**
     * @return array<int, object{title:string, number:numeric-string, url:string}>
     */
    public function issues() : array
    {
        $issues = [];
        $yield = $this->httpGet(
            "/repos/{$this->repoName}/issues",
            ['sort' => 'created', 'direction' => 'asc'],
        );

        /** @var object{title: string, number: numeric-string, html_url: string} $issue */
        foreach ($yield as $issue) {
            $issues[] = (object) [
                'title' => $issue->title,
                'number' => $issue->number,
                'url' => $issue->html_url,
            ];
        }

        return $issues;
    }

    public function release(Repo $repo, LoggerInterface $logger, string $version) : void
    {
        $logger->info("Releasing {$version} remotely.");
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
        $response = $this->httpPost("/repos/{$this->repoName}/releases", $query, $data);

        if (! isset($response->id)) {
            $message = var_export((array) $response, true);

            throw new Exception($message);
        }

        $logger->info("Released!");
    }
}
