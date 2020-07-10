<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/environment.php';

use Vgsite\Registry;

ini_set("error_reporting", 6135);
ini_set("session.save_path", ROOT_DIR . '/var/sessions');

// Register db handler
$db_options_default = array(
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_STRINGIFY_FETCHES => false,
);
$db_options = is_array($db_options) ? array_merge($db_options_default, $db_options) : $db_options_default;
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

session_start();

require_once ROOT_DIR . '/src/required_functions.php';
