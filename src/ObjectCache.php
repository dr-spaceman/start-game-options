<?php

namespace Vgsite;

/**
 * An Identity Map for DomainObjects to keep them consistent and prevent duplication
 */

class ObjectCache
{
    private static $instance = null;
    private $cache = [];
    
    private function __construct() {}

    private static function instance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }

    private function globalKey(DomainObject $obj): string
    {
        $key = get_class($obj) . "." . $obj->getId();
        return $key;
    }

    public static function set(DomainObject &$obj)
    {
        $inst = self::instance();
        $inst->cache[$inst->globalKey($obj)] = $obj;
    }

    public static function get($classname, $id): ?DomainObject
    {
        $inst = self::instance();
        $key = "{$classname}.{$id}";
        
        if (isset($inst->cache[$key])) {
            return $inst->cache[$key];
        }
        
        return null;
    }
}
