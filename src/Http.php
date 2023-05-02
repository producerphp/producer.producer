<?php
declare(strict_types=1);

namespace Producer;

/**
 *
 * An HTTP caller.
 *
 * @package producer/producer
 *
 */
class Http
{
    /**
     *
     * The base URL for HTTP calls.
     *
     * @var string
     *
     */
    protected $base;

    /**
     *
     * Constructor.
     *
     * @param string $base The base URL for HTTP calls.
     *
     */
    public function __construct($base)
    {
        $this->base = $base;
    }

    /**
     *
     * Make an HTTP request.
     *
     * @param string $method The HTTP method.
     *
     * @param string $path Append this path to the base URL.
     *
     * @param array $query Query parameters.
     *
     * @param array $data Data to be JSON-encoded as the message body.
     *
     * @return mixed The HTTP response.
     *
     */
    public function __invoke($method, $path, array $query = [], array $data = [])
    {
        $url = $this->base . $path;
        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        $context = $this->newContext($method, $data);
        return json_decode(file_get_contents($url, FALSE, $context));
    }

    /**
     *
     * Convenience method for HTTP GET.
     *
     * @param string $path Append this path to the base URL.
     *
     * @param array $query Query parameters.
     *
     * @return mixed The HTTP response.
     *
     */
    public function get($path, array $query = [])
    {
        return $this('GET', $path, $query);
    }

    /**
     *
     * Convenience method for HTTP POST.
     *
     * @param string $path Append this path to the base URL.
     *
     * @param array $query Query parameters.
     *
     * @param array $data Data to be JSON-encoded as the message body.
     *
     * @return mixed The HTTP response.
     *
     */
    public function post($path, array $query = [], array $data = [])
    {
        return $this('POST', $path, $query, $data);
    }

    /**
     *
     * Creates a new stream context.
     *
     * @param string $method The HTTP method.
     *
     * @param array $data Data to be JSON-encoded as the message body.
     *
     * @return resource
     *
     */
    protected function newContext($method, array $data = [])
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
