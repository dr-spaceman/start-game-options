<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/environment.php';

define('TEMPLATE_DIR', ROOT_DIR.'/templates');

use Vgsite\Registry;
use Vgsite\User;
use Monolog\Logger;
use Vgsite\UserMapper;

// Register logger
$logger = new Logger('app');
// Register a handler -- file loc and minimum error level to record
$log_level = getenv('ENVIRONMENT') == 'development' ? Logger::DEBUG : Logger::INFO;
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
    'debug' => getenv('ENVIRONMENT') == 'development',
    'auto_reload' => getenv('ENVIRONMENT') == 'development',
]);

// Catch uncaught exceptions
set_exception_handler(function (\Throwable $e) use ($template) {
    Registry::get('logger')->warning($e);
    $message = (getenv('ENVIRONMENT') == 'development') ? $e : $e->getMessage();
    echo $template->render('error.html', ['message' => $message]);
    exit;
});

require_once __DIR__ . '/bootstrap_common.php';

$template->addGlobal('session', $_SESSION);

/** END APP CONFIG **/

//$betatesters = array("Matt", "Matt2", "Andrew", "Alex", "Nels", "Kanji");

$errors   = array();
$warnings = array();
$results  = array();

require_once __DIR__ . '/user_session.php';

// require "../bin/php/bbcode.php";
