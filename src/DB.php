<?php

namespace Vgsite;

use \PDO;

class DB
{
    protected static $instance;
    protected $pdo;

    private function __construct()
    {
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function set($options=[])
    {
        if (!empty($this->pdo)) {
            return null;
        }

        $default_options = array(
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        );
        $options = array_merge($options, $default_options);

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;port=%d;charset=utf8',
            getenv('DB_HOST'),
            getenv('DB_NAME_MAIN'),
            getenv('DB_PORT'),
        );

        $this->pdo = new PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'), $options);
    }

    public function get(): PDO
    {
        return $this->pdo;
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
