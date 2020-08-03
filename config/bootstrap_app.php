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

$current_user = null;

if ($_SESSION['logged_in'] && $_SESSION['user_id']) {
    try {
        /** @var User */
        $current_user = Registry::getMapper(User::class)->findById($_SESSION['user_id']);
        Registry::set('current_user', $current_user);
        $_SESSION['user_rank'] = $current_user->getRank();
        $_SESSION['username'] = $current_user->getUsername();
    } catch (Exception $e) {
        unset($_SESSION['logged_in'], $_SESSION['user_id']);
        echo $template->render('error.html', ['message' => 'There was an error registering your user session. We have deleted your user cookies. Try logging in again. Details: ' . $e->getMessage()]);
        exit;
    }

    // Dicouraged old variable references
    // $usrname = $current_user->getUsername();
    // $usrid = $_SESSION['user_id'];
    // $usrlastlogin = $current_user->getLastLogin();
}

if ($_SESSION['user_rank'] == User::RESTRICTED) {
    die("*");
}

// require "../bin/php/bbcode.php";
