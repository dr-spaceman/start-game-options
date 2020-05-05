<?php

namespace Vgsite;

class Registry
{
    private static $instance = null;
    private $registry = array();

    private function __construct() {}

    static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    function get($key) 
    {
        if (isset($this->registry[$key])) {
            return $this->registry[$key];
        }

        return null;
    }

    function set($key, $value) 
    {
        $this->registry[$key] = $value;
    }
}

// // empty class for testing
// class Request {}

// $reg = Registry::instance();
// $reg->set( 'request', new Request() );

// $reg = Registry::instance();
// print_r( $reg->get( 'request' ) );
