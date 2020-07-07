<?php

namespace Vgsite\HTTP;

use Psr\Http\Message\ResponseInterface;
use Vgsite\API\CollectionJson;
use Vgsite\API\Exceptions\APIException;
use Vgsite\API\Exceptions\APIInvalidArgumentException;

/**
 * Handles HTTP response
 * 
 * Adopted from Guzzle PSR-7 Response
 * @link https://github.com/guzzle/psr7
 */

class Response implements ResponseInterface
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
        418 => 'I\'m a teapot', //Thanks, Guzzle!
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

    /** @var string */
    private $reasonPhrase = '';

    /** @var int */
    private $statusCode = 200;

    /**
     * @param int                                  $status  Status code
     * @param array                                $headers Response headers
     * @param string|null|resource|StreamInterface $body    Response body
     * @param string                               $version Protocol version
     * @param string|null                          $reason  Reason phrase (when empty a default will be used based on the status code)
     */
    public function __construct(
        $status = 200,
        array $headers = [],
        $body = null,
        $version = '1.1',
        $reason = null
    ) {
        $this->withStatus($status, $reason);

        if ($body !== '' && $body !== null) {
            $stream = fopen('php://temp', 'r+');
            $this->stream = new \GuzzleHttp\Psr7\Stream($stream);
        }

        $this->setHeaders($headers);

        $this->protocol = $version;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function withStatus($code, $reasonPhrase = ''): self
    {
        $this->assertStatusCodeIsInteger($code);
        $code = (int) $code;
        $this->assertStatusCodeRange($code);

        $this->statusCode = $code;
        if ($reasonPhrase == '' && isset(self::$phrases[$this->statusCode])) {
            $reasonPhrase = self::$phrases[$this->statusCode];
        }
        $this->reasonPhrase = $reasonPhrase;

        return $this;
    }

    private function assertStatusCodeIsInteger($statusCode): bool
    {
        if (filter_var($statusCode, FILTER_VALIDATE_INT) === false) {
            throw new APIException(
                sprintf('Status code must be an integer value (%s given)', $statusCode), 
                null, 
                'INVALID_REQUEST_METHOD', 
                500
            );
        }

        return true;
    }

    private function assertStatusCodeRange($statusCode): bool
    {
        if ($statusCode < 100 || $statusCode >= 600) {
            throw new APIException(
                sprintf('Status code must be an integer value between 1xx and 5xx. (%s given)', $statusCode),
                null,
                'INVALID_REQUEST_METHOD',
                500
            );
        }

        return true;
    }
    
    public function render(): void
    {
        $this->getBody()->write($this->stream);
    }
}
