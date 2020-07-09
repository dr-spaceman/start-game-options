<?php

namespace Vgsite\API\Exceptions;

use Vgsite\HTTP\Response;

class APIException extends \Exception
{
    public static $types = [
        'UNDEFINED', 'MISSING_REQUIRED_PARAMETER', 'INVALID_PARAMETER', 'INVALID_REQUEST_METHOD', 'INVALID_RANGE_FORMAT'
    ];

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
        $this->error_message['title'] = Response::$phrases[$code];
        if (! empty($source)) {
            $this->error_message['source'] = $source;
        }
        if (! empty($type)) {
            $this->error_message['code'] = $type;
        }
        if (! empty($message)) {
            $this->error_message['message'] = $message;
        }

        parent::__construct($this->error_message['title'], $code, $previous);
    }

    public function getErrorMessage(): array
    {
        return $this->error_message;
    }

    public function __toString(): string
    {
        if (getenv('ENVIRONMENT') == "development") {
            $this->error_message['trace'] = sprintf(
                '%s Line %s %s', 
                $this->getFile(), 
                $this->getLine(), 
                $this->getTraceAsString()
            );
        }

        return json_encode($this->error_message);
    }
}