<?php

namespace Vgsite\API\Exceptions;

class APIInvalidArgumentException extends APIException
{
    public function __construct(
        string $message,
        string $source = null,
        string $type = 'INVALID_PARAMETER',
        int $code = 422,
        \Throwable $previous = null
    )
    {        
        parent::__construct($message, $source, $type, $code, $previous);
    }
}