<?php
declare(strict_types=1);

namespace Producer;

use stdClass;

class Http
{
    public function __construct(protected string $base)
    {
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $data
     */
    public function __invoke(
        string $method,
        string $path,
        array $query = [],
        array $data = []
    ) : stdClass
    {
        $url = $this->base . $path;
        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        $context = $this->newContext($method, $data);
        return (object) json_decode((string) file_get_contents($url, false, $context));
    }

    /**
     * @param array<string, mixed> $query
     */
    public function get(string $path, array $query = []) : stdClass
    {
        return $this('GET', $path, $query);
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $data
     */
    public function post(string $path, array $query = [], array $data = []) : stdClass
    {
        return $this('POST', $path, $query, $data);
    }

    /**
     * @param array<string, mixed> $data
     * @return resource
     */
    protected function newContext(string $method, array $data = []) : mixed
    {
        $http = [
            'method' => $method,
            'header' => implode("\r\n", [
                'User-Agent: php/stream',
                'Accept: application/json',
                'Content-Type: application/json',
            ]),
        ];

        if ($data) {
            $http['content'] = json_encode($data);
        }

        return stream_context_create(['http' => $http, 'https' => $http]);
    }
}
