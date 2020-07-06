<?php

namespace Vgsite\HTTP;

use Vgsite\API\Exceptions\APIInvalidArgumentException;

class Request
{
    use MessageTrait;

    private $method;

    private $uri;

    /** URI path split into an array @var array */
    private $path = Array();
    
    /** URI query string parsed into an array @var array */
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
        [$this->path, $this->query] = $this->parseUri($uri);
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
        $path = explode('/', $path);
        array_shift($path); //_blank_
        array_shift($path); //api

        $querystring = parse_url($uri, PHP_URL_QUERY);
        parse_str($querystring, $query);

        return [$path, $query];
    }

    /**
     * Parse sort query string value
     * Format: [?sort=]fieldname[:asc|desc]
     *
     * @param string $sort_query The query string
     * @return array [fieldname, sort_direction(asc|desc)]
     */
    public function parseSortQuery(string $sort_query, array $allowed_fields=[]): array
    {
        $test = '/^\??(sort=)?([a-z\-_]*):?(asc|desc)?$/i';
        if (! preg_match($test, $sort_query, $matches)) {
            throw new APIInvalidArgumentException('Sort parameter not in valid format. Try: `?sort=fieldname[:asc|desc]`', '?sort');
        }

        if (! empty($allowed_fields) && false === array_search($matches[2], $allowed_fields)) {
            throw new APIInvalidArgumentException(
                sprintf('Sort parameter fieldname given (`%s`) is not allowed. Try one of: %s.', $matches[2], implode(', ', $allowed_fields)), 
                '?sort'
            );
        }

        return [$matches[2], $matches[3]];
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getPath(): array
    {
        return $this->path;
    }

    public function getQuery(): array
    {
        return $this->query;
    }
}