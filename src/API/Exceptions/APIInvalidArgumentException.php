<?php

namespace Vgsite\API\Exceptions;

class APIInvalidArgumentException extends \InvalidArgumentException
{
    public function __construct($message, $code=422, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}