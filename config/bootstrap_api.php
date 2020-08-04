<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/environment.php';

define('API_BASE_URI', '/api');
define('API_BASE_URL', sprintf('%s://%s%s', getenv('ENVIRONMENT') == 'development' ? 'http' : 'https', getenv('HOST_DOMAIN'), API_BASE_URI));
define('API_TOKEN_URL', API_BASE_URL . '/token');
define('API_VERSION', '0.4');

use Vgsite\Registry;
use Monolog\Logger;

// Register logger
$logger = new Logger('api');
// Register a handler -- file loc and minimum error level to record
$log_level = getenv('ENVIRONMENT') == "development" ? Logger::DEBUG : Logger::INFO;
$logger_stream = new Monolog\Handler\StreamHandler(LOGS_DIR.'/api.log', $log_level);
$logger_date_format = "Y-m-d H:i:s";
$logger_stream->setFormatter(new Monolog\Formatter\LineFormatter(null, $logger_date_format));
$logger->pushHandler($logger_stream);
$logger->pushProcessor(function ($record) {
    $record['extra']['request_method'] = $_SERVER['REQUEST_METHOD'];
    $record['extra']['request_uri'] = $_SERVER['REQUEST_URI'];
    $record['extra']['ip'] = $_SERVER['REMOTE_ADDR'];
    return $record;
});
Registry::set('logger', $logger);

// Catch open exceptions
set_exception_handler(function (\Throwable $e) {
    Registry::get('logger')->warning($e);

    echo 'Uncaught Exception: ' . $e->getMessage();
});

require_once __DIR__.'/bootstrap_common.php';

require_once __DIR__ . '/user_session.php';

/** END API CONFIG */
