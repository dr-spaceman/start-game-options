<?php

namespace Vgsite\HTTP;

class Request
{
    use MessageTrait;

    private $method;
    private $uri;
    private $path = Array();
    private $query = Array();

    /**
     * @param string        $method  HTTP method
     * @param string        $uri     URI
     * @param array         $headers Request headers
     * @param string|null   $body    Request body
     * @param string        $version Protocol version
     */
    public function __construct(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $version = '1.1'
    ) {
        $this->method = strtoupper($method);
        $this->uri = $uri;
        // [$this->path, $this->query] = $this->parseUri($uri);
        $this->setHeaders($headers);
        $this->protocol = $version;

        if ($body !== '' && $body !== null) {
            $stream = fopen('php://temp', 'r+');
            fwrite($stream, $body);
            fseek($stream, 0);

            $this->stream = new \GuzzleHttp\Psr7\Stream($stream);
        }
    }

    public function parseUri($uri): array
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $query = parse_url($uri, PHP_URL_QUERY);

        return [$path, $query];
    }

    public function getMethod()
    {
        return $this->method;
    }
}