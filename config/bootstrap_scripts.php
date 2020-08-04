<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/environment.php';

use Monolog\Logger;
use Vgsite\Registry;

// Register logger
$logger = new Logger('scripts');
// Register a handler -- file loc and minimum error level to record
$logger_stream = new Monolog\Handler\StreamHandler(LOGS_DIR.'/scripts.log', Logger::DEBUG);
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

/** END SCRIPTS CONFIG */
