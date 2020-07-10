<?php

namespace Vgsite\API\Exceptions;

class APISystemException extends APIException
{
    public function __construct(
        string $message = null,
        string $source = null,
        string $type = 'SYSTEM_ERROR',
        int $code = 500,
        \Throwable $previous = null
    )
    {
        if (! $message) {
            $message = 'Your request could not be processed because of an internal system error';
        }

        parent::__construct($message, $source, $type, $code, $previous);
    }
}