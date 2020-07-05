<?php

namespace Vgsite\API\Exceptions;

class APINotFoundException extends APIException
{
    public function __construct(
        string $message = null,
        string $source = null,
        string $type = null,
        int $code = 404,
        \Throwable $previous = null
    )
    {
        if (! $message) {
            $message = 'The requested resource could not be found';
        }

        parent::__construct($message, $source, $type, $code, $previous);
    }
}