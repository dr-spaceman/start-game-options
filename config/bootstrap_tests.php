<?php

define('ROOT_DIR', realpath(__DIR__.'/..'));
define('CACHE_DIR', ROOT_DIR.'/var/cache');
define('LOGS_DIR', ROOT_DIR.'/var/logs');
define('TEST_USER_ID', 2);
define('TEST_USER_EMAIL', 'test@test.com');
define('TEST_USER_USERNAME', 'test');
define('TEST_USER_PASSWORD', 'password');
define("TEST_ID", uniqid());

require_once ROOT_DIR.'/vendor/autoload.php';

use Monolog\Logger;
use Vgsite\Registry;
use Vgsite\User;

ini_set("error_reporting", 6135);
ini_set("session.save_path", ROOT_DIR.'/var/sessions');

session_start();

// Load environmental variables
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_DIR);
$dotenv->load();
$dotenv->required(['ENVIRONMENT', 'DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_NAME_MAIN']);

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
    Registry::set('pdo', $pdo);
} catch (PDOException $e) {
    echo "Database connection failed";
    exit;
}

// Register logger
$logger = new Logger('tests');
// Register a handler -- file loc and minimum error level to record
$logger_stream = new Monolog\Handler\StreamHandler(LOGS_DIR.'/tests.log', Logger::DEBUG);
$logger_date_format = "Y-m-d H:i:s";
$logger_stream->setFormatter(new Monolog\Formatter\LineFormatter(null, $logger_date_format));
$logger->pushHandler($logger_stream);
// Inject details of error source
$logger->pushProcessor(new Monolog\Processor\IntrospectionProcessor(Logger::DEBUG));
Registry::set('logger', $logger);

$_SESSION['logged_in'] = 'true';
$_SESSION['user_id'] = TEST_USER_ID;
$current_user = User::findById($_SESSION['user_id']);

require ROOT_DIR.'/src/required_functions.php';
