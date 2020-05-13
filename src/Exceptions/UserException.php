<?php

namespace Vgsite\Exceptions;

class UserException extends \Exception
{
    public function __construct($message = null, $code = 0, \Exception $previous = null, Vgsite\User $user = null)
    {
        $logger = Vgsite\Registry::get('logger');
        $logger->error($message, (!is_null($user) ? $user->getProperties() : []));

        parent::__construct($message, $code, $previous);
    }
}