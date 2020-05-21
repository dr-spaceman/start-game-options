<?php

define('ROOT_DIR', realpath(__DIR__.'/..'));
define('PUBLIC_DIR', ROOT_DIR.'/public');
define('TEMPLATE_DIR', ROOT_DIR.'/templates');
define('CACHE_DIR', ROOT_DIR.'/var/cache');
define('LOGS_DIR', ROOT_DIR.'/var/logs');

require_once ROOT_DIR.'/vendor/autoload.php';

use Vgsite\Registry;
use Vgsite\User;
use Monolog\Logger;

ini_set("error_reporting", 6135);
ini_set("session.save_path", ROOT_DIR.'/var/sessions');

// Load environmental variables
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_DIR);
$dotenv->load();
$dotenv->required(['ENVIRONMENT', 'DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_NAME_MAIN']);

// Register logger
$logger = new Logger('app');
// Register a handler -- file loc and minimum error level to record
$logger->pushHandler(new Monolog\Handler\StreamHandler(LOGS_DIR.'/app.log', (getenv('ENVIRONMENT') == "development" ? Logger::DEBUG : Logger::INFO)));
// Inject details of error source
$logger->pushProcessor(new Monolog\Processor\IntrospectionProcessor(Logger::ERROR));
Registry::set('logger', $logger);

// Register db handler
$db_options = array(
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
try {
    $pdo = new PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'), $db_options);
    Registry::set('pdo', $pdo);
} catch (PDOException $e) {
    $logger->error($e);
    echo "Database connection failed";
    exit;
}

// Templates
$loader = new \Twig\Loader\FilesystemLoader(TEMPLATE_DIR);
$template = new \Twig\Environment($loader, [
    'cache' => CACHE_DIR.'/compilation_cache',
    'debug' => (getenv('ENVIRONMENT') == "development" ? true : false),
]);

// Catch uncaught exceptions
set_exception_handler(function (\Throwable $e) {
    Registry::get('logger')->warning($e);
    if (getenv('ENVIRONMENT') == "development") echo $e;
    else echo $e->getMessage();
});

session_start();

//$betatesters = array("Matt", "Matt2", "Andrew", "Alex", "Nels", "Kanji");

$errors   = array();
$warnings = array();
$results  = array();

$current_user = null;
$usrname = null;
$usrid = -1;
$_SESSION['user_rank'] = User::GUEST;

if ($_SESSION['logged_in'] && $_SESSION['user_id']) {
    $current_user = User::findById($_SESSION['user_id']);
    // Dicouraged old variable references
    $usrname = $current_user->getUsername();
    $usrid = $_SESSION['user_id'];
    $usrlastlogin = $current_user->getLastLogin();
}

if ($_SESSION['user_rank'] == User::RESTRICTED) {
    die("*");
}

require ROOT_DIR.'/src/required_functions.php';
// require "../bin/php/bbcode.php";
