<?php
declare(strict_types=1);

namespace Producer\Infra\Api;

use Producer\Infra\Api;
use Producer\Infra\Exception;
use Producer\Infra\Repo;
use Psr\Log\LoggerInterface;
use stdClass;

class Bitbucket extends Api
{
    public function __construct(
        string $origin,
        string $hostname,
        string $user,
        string $pass,
    ) {
        $this->setHttp("https://{$user}:{$pass}@{$hostname}/2.0");
        $this->setRepoNameFromOrigin($origin);
    }

    protected function setRepoNameFromOrigin(string $origin) : void
    {
        $repoName = (string) parse_url($origin, PHP_URL_PATH);
        $repoName = (string) preg_replace('/\.hg$/', '', $repoName);
        $this->repoName = trim($repoName, '/');
    }

    protected function httpValues(stdClass $json) : stdClass
    {
        return $json->values;
    }

    /**
     * @return array<int, object{title:string, number:numeric-string, url:string}>
     */
    public function issues() : array
    {
        $issues = [];
        $yield = $this->httpGet(
            "/repositories/{$this->repoName}/issues",
            ['sort' => 'created_on'],
        );

        /** @var object{title: string, id: numeric-string} $issue */
        foreach ($yield as $issue) {
            $issues[] = (object) [
                'title' => $issue->title,
                'number' => $issue->id,
                'url' => "https://bitbucket.org/{$this->repoName}/issues/{$issue->id}",
            ];
        }

        return $issues;
    }

    public function release(Repo $repo, LoggerInterface $logger, string $version) : void
    {
        $logger->info("Tagging release {$version} locally.");
        $repo->tag($version, "Released {$version}");
        $logger->info("Tagged!");
    }
}
