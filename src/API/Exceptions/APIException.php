<?php

namespace Vgsite\API\Exceptions;

class APIException extends \Exception
{
    public function __construct($message = null, $code, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}