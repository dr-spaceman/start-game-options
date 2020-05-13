<?php

namespace Vgsite;

class TestClassInstantiated
{
    protected static $instantiated = false;
    protected static $instance;
    protected static $dbh;
    public $id;

    public function __construct(array $params, $dbh=[], $logger=[])
    {
        if (!self::$instantiated) $this->instance($dbh, $logger);
        if (!empty($params)) $this->id=$params['id'];
        
    }

    public static function instance($dbh, $logger=[]): self
    {
        if (self::$instance === null) {
            self::$instantiated = true;
            self::$dbh = $dbh;
            self::$instance = new self([], $dbh, $logger);
        }

        return self::$instance;
    }

    public static function get($id)
    {
        if (!self::$instantiated) return false;
        return new self(['id'=>$id]);
    }
}