<?php

define('ROOT_DIR', realpath(__DIR__.'/..'));
define('TEMPLATE_DIR', ROOT_DIR.'/templates');

require_once ROOT_DIR.'/vendor/autoload.php';

use Vgsite\Registry;
use Vgsite\User;
use Monolog\Logger;

ini_set("error_reporting", 6135);
ini_set("session.save_path", ROOT_DIR.'/var/sessions');

// Load environmental variables
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_DIR);
$dotenv->load();
$dotenv->required(['ENVIRONMENT', 'DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_NAME_MAIN']);

// Registry
$registry = Registry::instance();

// Register db handler
$db_options = array(
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_STRINGIFY_FETCHES => false,
);
$dsn = sprintf(
    'mysql:host=%s;dbname=%s;port=%d;charset=utf8',
    getenv('DB_HOST'),
    getenv('DB_NAME_MAIN'),
    getenv('DB_PORT'),
);
try {
	$pdo = new PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'), $db_options);
	$registry->set('pdo', $pdo);
} catch (PDOException $e) {
    echo "Database connection failed";
    exit;
}

// Register logger
$logger = new Logger('app');
// Register a handler -- file loc and minimum error level to record
$logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__."/../var/logs/app.log", (getenv('ENVIRONMENT') == "development" ? Logger::DEBUG : Logger::INFO)));
// Inject details of error source
$logger->pushProcessor(new Monolog\Processor\IntrospectionProcessor(Logger::ERROR));
$registry->set('logger', $logger);

// Catch uncaught exceptions
set_exception_handler(function (\Throwable $e) {
    $GLOBALS['logger']->warning($e);
    if (getenv('ENVIRONMENT') == "development") echo $e;
    else echo $e->getMessage();
});

session_start();

// OLDER STUFF BELOW //


require "../bin/php/page_functions.php";
require "../bin/php/bbcode.php";

//$betatesters = array("Matt", "Matt2", "Andrew", "Alex", "Nels", "Kanji");

$errors   = array();
$warnings = array();
$results  = array();

$html_tag = '<!DOCTYPE html>
<html dir="ltr" lang="en-US" xmlns:fb="http://www.facebook.com/2008/fbml">';
$root = $_SERVER['DOCUMENT_ROOT'];

$usrid   = null;
$usrname = null;
$usrrank = 0;

//set login vars
if(isset($_SESSION['usrname'])) {
	
	$usrname = $_SESSION['usrname'];
	$usrid = $_SESSION['usrid'];
	$usrrank = base64_decode($_SESSION['usrkey']);
	$usrlastlogin = $_SESSION['usrlastlogin'];

} else {
	
	if(isset($_COOKIE['usrsession'])) {
		
		//login user from remembered cookie
		
		$usrsession = base64_decode($_COOKIE['usrsession']);
		list($usrid_, $password_) = explode("```", $usrsession);
		$q = sprintf(
			"SELECT * FROM users WHERE usrid='%s' AND password=PASSWORD('%s') LIMIT 1",
			mysqli_real_escape_string($GLOBALS['db']['link'], $usrid_),
			mysqli_real_escape_string($GLOBALS['db']['link'], $password_)
		);
		if($res = mysqli_query($GLOBALS['db']['link'], $q)) {
			$userdat = mysqli_fetch_assoc($res);
			login($userdat);
		}
	
	}
}
