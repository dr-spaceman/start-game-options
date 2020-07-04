<?php

namespace Vgsite\API\Exceptions;

class APIException extends \Exception
{
    public function __construct(string $message=null, int $code, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}