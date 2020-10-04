<?php

namespace Vgsite\API\Exceptions;

class APILoginException extends APIException
{
    public function __construct(string $message, string $source, \Exception $previous=null)
    {
        parent::__construct($message, $source, 'INVALID_PARAMETER', 401, $previous);
    }
}
