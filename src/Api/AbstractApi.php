<?php
declare(strict_types=1);

namespace Producer\Api;

use Producer\Exception;
use Producer\Http;

/**
 *
 * An abstract API class.
 *
 * @package producer/producer
 *
 */
abstract class AbstractApi implements ApiInterface
{
    /**
     *
     * An HTTP object.
     *
     * @var HTTP
     *
     */
    protected $http;

    /**
     *
     * The API repository name.
     *
     * @var string
     *
     */
    protected $repoName;

    /**
     *
     * Returns the API repository name.
     *
     * @return string
     *
     */
    public function getRepoName()
    {
        return $this->repoName;
    }

    /**
     *
     * Sets a new HTTP object.
     *
     * @param string $base The base URL for HTTP calls.
     *
     */
    protected function setHttp($base)
    {
        $this->http = new Http($base);
    }

    /**
     *
     * Sets the repo name based on the origin.
     *
     * @param string $origin The repo origin.
     *
     */
    protected function setRepoNameFromOrigin($origin)
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

    /**
     *
     * Repeats an HTTP GET to get all results from all pages.
     *
     * @param string $path GET from this path.
     *
     * @param array $query Query params.
     *
     * @return \Generator
     *
     */
    protected function httpGet($path, array $query = [])
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

    /**
     *
     * Makes one HTTP POST call and returns the results.
     *
     * @param string $path POST to this path.
     *
     * @param array $query Query params.
     *
     * @param array $data Data to be JSON-encoded as the HTTP message body.
     *
     * @return mixed
     *
     */
    protected function httpPost($path, array $query = [], array $data = [])
    {
        $query = $this->httpQuery($query);
        return $this->httpValues($this->http->post($path, $query, $data));
    }

    /**
     *
     * Modifies query params to add a page and other API-specific params.
     *
     * @param array $query The query params.
     *
     * @param int $page The page number, if any.
     *
     * @return array
     *
     */
    protected function httpQuery(array $query, $page = 0)
    {
        if ($page) {
            $query['page'] = $page;
        }
        return $query;
    }

    /**
     *
     * Extracts the value elements from the API JSON result.
     *
     * @param mixed $json The API JSON result.
     *
     * @return mixed
     *
     */
    protected function httpValues($json)
    {
        return $json;
    }
}
