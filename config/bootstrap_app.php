<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/constants.php';

define('TEMPLATE_DIR', ROOT_DIR.'/templates');

use Vgsite\Registry;
use Vgsite\User;
use Monolog\Logger;

// Register logger
$logger = new Logger('app');
// Register a handler -- file loc and minimum error level to record
$log_level = getenv('ENVIRONMENT') == "development" ? Logger::DEBUG : Logger::INFO;
$logger->pushHandler(new Monolog\Handler\StreamHandler(LOGS_DIR.'/app.log', $log_level));
// Inject details of error source
$logger->pushProcessor(new Monolog\Processor\IntrospectionProcessor(Logger::ERROR));
Registry::set('logger', $logger);

// Register db handler
$db_options = array();

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

require_once __DIR__ . '/bootstrap_common.php';

/** END APP CONFIG **/

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

// require "../bin/php/bbcode.php";
