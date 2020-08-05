<?php

namespace Vgsite;

use InvalidArgumentException;

/**
 * Store instantiated objects and variables for access among classes
 */

class Registry
{
    private static $instance = null;
    private $registry = [];

    /**
     * Mappers instantiated and stored here
     * @var array
     */
    private $mappers = array();

    private function __construct() {}

    private static function instance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    public static function get($key) 
    {
        $inst = self::instance();
        if (isset($inst->registry[$key])) {
            return $inst->registry[$key];
        }

        return null;
    }

    public static function set($key, $value)
    {
        $inst = self::instance();
        $inst->registry[$key] = $value;
    }

    /**
     * Get a mapper from registry, or register it
     *
     * @param string $class
     * @param class $mapper
     * 
     * @return Mapper
     */
    public static function getMapper($class=null, $mapper=null): Mapper
    {
        $inst = self::instance();
        if (isset($inst->mappers[$class])) {
            return $inst->mappers[$class];
        }

        if (!$mapper) {
            $mapper = $class."Mapper";
        }

        $inst->mappers[$class] = new $mapper();

        return $inst->mappers[$class];
    }
}

// // empty class for testing
// class Request {}

// $reg = Registry::instance();
// $reg->set( 'request', new Request() );

// $reg = Registry::instance();
// print_r( $reg->get( 'request' ) );
