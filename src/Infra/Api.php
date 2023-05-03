<?php
declare(strict_types=1);

namespace Producer\Infra;

use stdClass;
use Generator;
use Psr\Log\LoggerInterface;

abstract class Api
{
    protected Http $http;

    protected string $repoName;

    public function getRepoName() : string
    {
        return $this->repoName;
    }

    protected function setHttp(string $base) : void
    {
        $this->http = new Http($base);
    }

    protected function setRepoNameFromOrigin(string $origin) : void
    {
        // if ssh, strip username off so  `parse_url` can work as expected
        if (strpos($origin, 'git@') !== false) {
            $origin = (string) substr($origin, 4);
        }

        // get path from url, strip .git from the end, and retain
        $repoName = (string) parse_url($origin, PHP_URL_PATH);
        $repoName = (string) preg_replace('/\.git$/', '', $repoName);
        $this->repoName = trim($repoName, '/');
    }

    /**
     * @param array<string, mixed> $query
     */
    protected function httpGet(string $path, array $query = []) : Generator
    {
        $page = 1;
        do {
            $found = false;
            $query['page'] = $page;
            $query = $this->httpQuery($query);
            $json = $this->http->get($path, $query);
            foreach ((array) $this->httpValues($json) as $item) {
                $found = true;
                yield $item;
            }
            $page ++;
        } while ($found);
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $data
     */
    protected function httpPost(string $path, array $query = [], array $data = []) : stdClass
    {
        $query = $this->httpQuery($query);
        return $this->httpValues($this->http->post($path, $query, $data));
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    protected function httpQuery(array $query, int $page = 0) : array
    {
        if ($page) {
            $query['page'] = $page;
        }

        return $query;
    }

    protected function httpValues(stdClass $json) : stdClass
    {
        return $json;
    }


    /**
     * @return array<int, object{title:string, number:numeric-string, url:string}>
     */
    abstract public function issues() : array;

    abstract public function release(Repo $repo, LoggerInterface $logger, string $version) : void;
}
