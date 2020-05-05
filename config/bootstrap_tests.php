<?php

define('ROOT_DIR', realpath(__DIR__.'/..'));
define('TEST_USER_ID', 2);
define('TEST_USER_EMAIL', 'test@test.com');
define('TEST_USER_USERNAME', 'test');
define('TEST_USER_PASSWORD', 'password');
define("TEST_ID", uniqid());

require_once ROOT_DIR.'/vendor/autoload.php';

use Vgsite\Registry;
use Monolog\Logger;

ini_set("error_reporting", 6135);
ini_set("session.save_path", ROOT_DIR.'/var/sessions');

// Load environmental variables
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_DIR);
$dotenv->load();
$dotenv->required(['ENVIRONMENT', 'DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_NAME_MAIN']);

// Registry
$registry = Registry::instance();

// Register db handler
$db_options = array(
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_STRINGIFY_FETCHES => false,
    PDO::MYSQL_ATTR_FOUND_ROWS => true,
);
$dsn = sprintf(
    'mysql:host=%s;dbname=%s;port=%d;charset=utf8',
    getenv('DB_HOST'),
    getenv('DB_NAME_MAIN'),
    getenv('DB_PORT'),
);
try {
    $pdo = new PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'), $db_options);
    $registry->set('pdo', $pdo);
} catch (PDOException $e) {
    echo "Database connection failed";
    exit;
}

// Register logger
$logger = new Logger('tests');
// Register a handler -- file loc and minimum error level to record
$logger_stream = new Monolog\Handler\StreamHandler(__DIR__."/../var/logs/tests.log", Logger::DEBUG);
$logger_date_format = "Y-m-d H:i:s";
$logger_stream->setFormatter(new Monolog\Formatter\LineFormatter(null, $logger_date_format));
$logger->pushHandler($logger_stream);
// Inject details of error source
$logger->pushProcessor(new Monolog\Processor\IntrospectionProcessor(Logger::DEBUG));
$registry->set('logger', $logger);
