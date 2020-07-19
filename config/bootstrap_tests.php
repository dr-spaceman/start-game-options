<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/environment.php';

define('TEST_USER_ID', 2);
define('TEST_USER_EMAIL', 'test@test.com');
define('TEST_USER_USERNAME', 'test');
define('TEST_USER_PASSWORD', 'password');
define("TEST_ID", uniqid());

use Monolog\Logger;
use Vgsite\Registry;
use Vgsite\User;

// Register db handler
$db_options = array(
    PDO::MYSQL_ATTR_FOUND_ROWS => true,
);

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

// Catch uncaught exceptions
set_exception_handler(function (\Throwable $e) {
    Registry::get('logger')->warning($e);
    if (getenv('ENVIRONMENT') == "development") echo $e;
    else echo $e->getMessage();
});

require_once __DIR__ . '/bootstrap_common.php';

/** END TEST CONFIG */

$_SESSION['logged_in'] = 'true';
$_SESSION['user_id'] = TEST_USER_ID;
$current_user = Registry::getMapper(User::class)->findById($_SESSION['user_id']);
Registry::set('current_user', $current_user);
