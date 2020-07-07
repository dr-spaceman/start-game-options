<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/constants.php';

define('API_BASE_URI', sprintf('/api', $_SERVER['HTTP_HOST']));
define('API_BASE_URL', sprintf('http://%s%s', $_SERVER['HTTP_HOST'], API_BASE_URI));

use Vgsite\Registry;
use Monolog\Logger;
use Vgsite\API\CollectionJson;

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

// Catch uncaught exceptions
set_exception_handler(function (\Throwable $e) {
    Registry::get('logger')->warning($e);

    header('HTTP/1.1 500 Internal Server Error');
    header("Content-Type: application/json; charset=UTF-8");

    $error = ['title' => 'Server error', 'message' => $e->getMessage()];
    $cj = new CollectionJson();
    $cj->setError($error);
    
    echo json_encode($cj);
});

require_once __DIR__.'/bootstrap_common.php';

/** END API CONFIG */

