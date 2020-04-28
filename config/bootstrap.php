<?php

define('ROOT_DIR', realpath(__DIR__.'/..'));
define('TEMPLATE_DIR', ROOT_DIR.'/templates');

require_once ROOT_DIR.'/vendor/autoload.php';

use Vgsite\User;
use Monolog\Logger;

ini_set("error_reporting", 6135);
ini_set("session.save_path", ROOT_DIR.'/var/sessions');

$dotenv = Dotenv\Dotenv::createImmutable(ROOT_DIR);
$dotenv->load();
$dotenv->required(['ENVIRONMENT', 'DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_NAME_MAIN']);

$pdo = Vgsite\DB::instance();

session_start();

$logger = new Logger('app');
// Register a handler -- file loc and minimum error level to record
$logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__."/../var/logs/app.log", (getenv('ENVIRONMENT') == "development" ? Logger::DEBUG : Logger::INFO)));
// Inject details of error source
$logger->pushProcessor(new Monolog\Processor\IntrospectionProcessor(Logger::ERROR));

// Catch uncaught exceptions
set_exception_handler(function (\Throwable $e) {
    $GLOBALS['logger']->warning($e);
    if (getenv('ENVIRONMENT') == "development") echo $e;
    else echo $e->getMessage();
});

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

function updateActivity(){
	
	global $usrid;
	
	$u = new user($usrid);
	$u->getDetails(); //$dob for birthday badge
	
	//update activity
	$query = "UPDATE users SET activity='".date("Y-m-d H:i:s")."', previous_activity='".$u->activity."' WHERE usrid='".$usrid."' LIMIT 1";
	mysqli_query($GLOBALS['db']['link'], $query);
	
	//record current scores and counts
	$query = "SELECT * FROM users_data WHERE usrid = '".$usrid."' AND `date` = '".date("Y-m-d")."' LIMIT 1";
	if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query))) {
		
		$u->calculateScore(); //recalculate score
		
		if($u->score['total'] >= 1){
			$q2 = "INSERT INTO users_data (usrid, `date`, ".implode(", ", array_keys($u->score['vars'])).", score_forums, score_pages, score_sblogs, score_total) VALUES 
				('$usrid', '".date("Y-m-d")."', '".implode("', '", array_values($u->score['vars']))."', '".$u->score['forums']."', '".$u->score['pages']."', '".$u->score['sblogs']."', '".$u->score['total']."');";
			mysqli_query($GLOBALS['db']['link'], $q2);
		}
		
		$q2 = "UPDATE users SET 
			score_forums = '".$u->score['forums']."',
			score_pages = '".$u->score['pages']."',
			score_sblogs = '".$u->score['sblogs']."',
			score_total = '".$u->score['total']."'
			WHERE usrid = '$usrid' LIMIT 1";
		mysqli_query($GLOBALS['db']['link'], $q2);
		
	}
	
	//badges
	//check birthday
	$dob = str_replace("-", "", $u->dob);
	$dob = substr($dob, 4);
	if($dob == date("md")){
		require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.badges.php";
		$_badges = new badges();
		$_badges->earn(37);
	}
	
}

function newBadges(){
	
	// check for new badges earned since last login
	// return array badge IDs
	
	$new = array();
	
	$query = "SELECT * FROM badges_earned WHERE usrid = '".$GLOBALS['usrid']."' AND `new` = '1';";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	while ($row = mysqli_fetch_assoc($res)) {
		$new[] = $row['bid'];
	}
	
	return $new;
	
}
	
?>