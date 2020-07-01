<?php

namespace Vgsite\API\Exceptions;

class APINotFoundException extends \Exception
{
    public function __construct(string $message=null, int $code=404, \Throwable $previous=null)
    {
        parent::__construct($message, $code, $previous);
    }
}