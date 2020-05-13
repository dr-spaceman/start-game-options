<?php

namespace Vgsite\Exceptions;

use Vgsite\Registry;

trait LogException {
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        $logger = Registry::get('logger');
        $logger->error($message);

        parent::__construct($message, $code, $previous);
    }
}