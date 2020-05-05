<?php

namespace Vgsite\Exceptions;

use Vgsite\Registry;
use Vgsite\User;

class UserException extends \Exception
{
    public function __construct($message = null, $code = 0, \Exception $previous = null, User $user = null)
    {
        $registry = Registry::instance();
        $logger = $registry->get('logger');
        $logger->error($message, (!is_null($user) ? $user->getProperties() : []));

        parent::__construct($message, $code, $previous);
    }
}