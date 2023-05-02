<?php
declare(strict_types=1);

namespace Producer;

use Producer\Exception;
use Producer\Http;

abstract class Api
{
    protected Http $http;

    protected string $repoName;

    public function getRepoName()
    {
        return $this->repoName;
    }

    protected function setHttp(string $base)
    {
        $this->http = new Http($base);
    }

    protected function setRepoNameFromOrigin(string $origin)
    {
        // if ssh, strip username off so  `parse_url` can work as expected
        if (strpos($origin, 'git@') !== false) {
            $origin = substr($origin, 4);
        }

        // get path from url, strip .git from the end, and retain
        $repoName = parse_url($origin, PHP_URL_PATH);
        $repoName = preg_replace('/\.git$/', '', $repoName);
        $this->repoName = trim($repoName, '/');
    }

    protected function httpGet(string $path, array $query = [])
    {
        $page = 1;
        do {
            $found = false;
            $query['page'] = $page;
            $query = $this->httpQuery($query);
            $json = $this->http->get($path, $query);
            foreach ($this->httpValues($json) as $item) {
                $found = true;
                yield $item;
            }
            $page ++;
        } while ($found);
    }

    protected function httpPost(string $path, array $query = [], array $data = [])
    {
        $query = $this->httpQuery($query);
        return $this->httpValues($this->http->post($path, $query, $data));
    }

    protected function httpQuery(array $query, int $page = 0)
    {
        if ($page) {
            $query['page'] = $page;
        }

        return $query;
    }

    protected function httpValues($json)
    {
        return $json;
    }
}
