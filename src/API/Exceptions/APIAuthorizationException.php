<?php

namespace Vgsite\API\Exceptions;

class APIAuthorizationException extends APIException
{
    public function __construct(
        string $message = null,
        string $source = null,
        string $type = 'INVLID_CREDENTIALS',
        int $code = 401,
        \Throwable $previous = null
    )
    {
        if (! $message) {
            $message = 'Unauthorized';
        }

        parent::__construct($message, $source, $type, $code, $previous);
    }
}