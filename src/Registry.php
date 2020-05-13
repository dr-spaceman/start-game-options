<?php

namespace Vgsite;

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

    private function __construct() {echo 'new Registry***************'.PHP_EOL;}

    private static function instance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    public static function get($key) 
    {
        echo 'Registry::get('.$key.'):';
        $inst = self::instance();
        if (isset($inst->registry[$key])) {
            echo 'found'.PHP_EOL;
            return $inst->registry[$key];
        }
        echo 'notfound'.PHP_EOL;

        return null;
    }

    public static function set($key, $value)
    {
        $inst = self::instance();
        $inst->registry[$key] = $value;
        echo 'Registry::set('.$key.')'.PHP_EOL;
    }

    public static function getMapper($class)
    {
        echo 'Registry::getMapper('.$class.'):';
        $inst = self::instance();
        if (isset($inst->mappers[$class])) {
            echo 'found'.PHP_EOL;
            return $inst->mappers[$class];
        }

        echo 'notfound;instantiating'.PHP_EOL;
        $mapper_class = $class.'Mapper';
        $inst->mappers[$class] = new $mapper_class;

        return $inst->mappers[$class];
    }
}

// // empty class for testing
// class Request {}

// $reg = Registry::instance();
// $reg->set( 'request', new Request() );

// $reg = Registry::instance();
// print_r( $reg->get( 'request' ) );
