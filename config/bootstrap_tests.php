<?php

define('ROOT_DIR', realpath(__DIR__.'/..'));

require_once ROOT_DIR.'/vendor/autoload.php';

use Monolog\Logger;

ini_set("error_reporting", 6135);
ini_set("session.save_path", ROOT_DIR.'/var/sessions');

$dotenv = Dotenv\Dotenv::createImmutable(ROOT_DIR);
$dotenv->load();
$dotenv->required(['ENVIRONMENT', 'DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_NAME_MAIN']);

$pdo = Vgsite\DB::instance();

$logger = new Logger('tests');
// Register a handler -- file loc and minimum error level to record
$logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__."/../var/logs/tests.log", Logger::DEBUG));
// Inject details of error source
$logger->pushProcessor(new Monolog\Processor\IntrospectionProcessor(Logger::DEBUG));
