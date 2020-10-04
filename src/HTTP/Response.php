<?php

namespace Vgsite\HTTP;

use GuzzleHttp\Psr7\Stream;
use Vgsite\API\Exceptions\APIException;
use Vgsite\API\Exceptions\APISystemException;

/**
 * Handles HTTP response
 * 
 * Adopted from Guzzle PSR-7 Response
 * @link https://github.com/guzzle/psr7
 */

class Response
{
    use MessageTrait;

    /** @var array Map of standard HTTP status code/reason phrases */
    public static $phrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    /** @var int */
    private $status_code = 200;

    /**
     * @param int                                  $status  Status code
     * @param array                                $headers Response headers
     * @param string|null|resource|StreamInterface $body    Response body
     * @param string                               $version Protocol version
     * @param string|null                          $reason  Reason phrase (when empty a default will be used based on the status code)
     */
    public function __construct(
        $status_code = 200,
        array $headers = [],
        $body = null,
        $version = '1.1'
    ) {
        $this->setStatusCode($status_code);

        // Output to buffer
        echo $body;

        // Default headers
        $this->setHeader('Content-Type', 'application/json; charset=UTF-8');
        $this->setHeader('Access-Control-Allow-Origin', getenv('HOST_DOMAIN'));
        $this->setHeader('Access-Control-Allow-Credentials', 'true');
        $this->setHeader('Access-Control-Max-Age', '3600');
        $this->setHeader('Access-Control-Allow-Headers', 'Origin, Content-Type, Authorization, X-Requested-With');
        $this->setHeader('Access-Control-Allow-Methods', 'OPTIONS, GET, POST, PUT, DELETE');

        $this->setHeaders($headers);

        $this->protocol = $version;
    }

    public function getStatusCode(): int
    {
        return $this->status_code;
    }

    public function getReasonPhrase(): string
    {
        return static::$phrases[$this->getStatusCode()];
    }

    public function setStatusCode($code): self
    {
        if (filter_var($code, FILTER_VALIDATE_INT) === false) {
            throw new APISystemException(
                sprintf('Status code must be an integer value (%s given)', $code)
            );
        }
        $code = (int) $code;

        if (! static::$phrases[$this->getStatusCode()]) {
            throw new APISystemException(
                sprintf('Status code must be an integer value within the standard range. (%s given)', $code)
            );
        }

        $this->status_code = $code;

        return $this;
    }
    
    public function render(string $body=null): void
    {
        header(
            sprintf(
                'HTTP/%s %d %s',
                $this->getProtocolVersion(),
                $this->getStatusCode(),
                $this->getReasonPhrase()
            )
        );
        $this->setHeader('API-Body-Rendered', 'true');
        foreach ($this->getHeaders() as $key => $value) {
            header($this->renderHeader($key));
        };

        // Output to buffer
        echo $body;
    }
}
