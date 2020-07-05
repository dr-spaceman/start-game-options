<?php

namespace Vgsite\API\Exceptions;

use Vgsite\HTTP\Response;

class APIException extends \Exception
{
    public static $types = Array(
        'UNDEFINED', 'MISSING_REQUIRED_PARAMETER', 'INVALID_PARAMETER', 'INVALID_REQUEST_METHOD', 'INVALID_RANGE_FORMAT'
    );

    private $error_message = Array();
    
    /**
     * Generic exception
     *
     * @param string $message       Specific exception message
     * @param string $source        Source of the exception (A variable, for example)
     * @param string $type          Machine-readable exception type; One of static::types
     * @param integer $code         HTTP status code to return with the response
     * @param \Throwable $previous
     */
    public function __construct(
        string $message = null,
        string $source = null,
        string $type = null,
        int $code = 422,
        \Throwable $previous = null
    )
    {
        if (! empty($source)) {
            $this->error_message['source'] = $source;
        }
        if (! empty($type)) {
            $this->error_message['type'] = $type;
        }
        if (! empty($message)) {
            $this->error_message['message'] = $message;
        }

        $exception_message = Response::$phrases[$code];

        parent::__construct($exception_message, $code, $previous);
    }

    public function __toString(): string
    {
        $response_json = [
            'message' => $this->getMessage(),
            'errors' => $this->error_message,
        ];

        if (getenv('ENVIRONMENT') == "development") {
            $response_json['trace'] = $this->getTraceAsString();
        }

        return json_encode($response_json);
    }
}