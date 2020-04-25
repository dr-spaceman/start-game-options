<?php

namespace Vgsite;

use \PDO;

class DB
{
    protected static $instance;
    protected $pdo;

    public function __construct()
    {
        $db_options  = array(
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        );

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;port=%d;charset=utf8',
            getenv('DB_HOST'),
            getenv('DB_NAME_MAIN'),
            getenv('DB_PORT'),
        );

        $this->pdo = new PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'), $db_options);
    }

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance->pdo;
    }

    // a proxy to native PDO methods
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->pdo, $method), $args);
    }

    // a helper function to run prepared statements smoothly
    public function run($sql, $args = [])
    {
        if (!$args) {
             return $this->query($sql);
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);
        return $stmt;
    }
}
