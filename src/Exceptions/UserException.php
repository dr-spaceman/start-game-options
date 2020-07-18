<?php

namespace Vgsite\Exceptions;

use Vgsite\Registry;
use Vgsite\User;

class UserException extends \Exception
{
    public function __construct($message = null, $code = 0, \Exception $previous = null, User $user = null)
    {
        if ($logger = Registry::get('logger')) {
            $logger->error($message, (!is_null($user) ? $user->getProps() : []));
        }

        parent::__construct($message, $code, $previous);
    }
}