<?php

require_once dirname(__FILE__) . '/../config/bootstrap_app.php';

use Monolog\Logger;

$logger_login = new Logger('login');
// Register a handler -- file loc and minimum error level to record
$logger_login->pushHandler(new Monolog\Handler\StreamHandler(LOGS_DIR . '/login.log', (getenv('ENVIRONMENT') == "development" ? Logger::DEBUG : Logger::INFO)));
$logger_login->pushProcessor(new Monolog\Processor\IntrospectionProcessor(Logger::DEBUG));

if ($_SESSION['logged_in']) {
    $logger_login->info(
        'User logged out',
        ['user_id' => $current_user->getId(), 'username' => $current_user->getUsername()],
    );
    
    setcookie(session_name(), '', time() - 42000, '/');
    unset($_SESSION['username'], $_SESSION['user_id'], $_SESSION['user_rank'], $_SESSION['logged_in']);
    session_destroy();
}

header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK', true, 200);
