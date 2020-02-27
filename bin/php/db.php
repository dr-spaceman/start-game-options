<?
/*$db = array(
	"host" => "localhost",
	"user" => "root",
	"pass" => "",
	"main" => "nintendo",
	"guides" => "nintendo_guides");*/

$db = array(
	"host" => "localhost.amazingpants.com",
	"user" => "nintendo_default",
	"pass" => "nin909",
	"main" => "nintendo",
	"guides" => "nintendo_guides");

$db2 = array( // Square Haven DB
	'host' => 'localhost',
	'user' => 'root',
	'pass' => '',
	'name' => 'sqhav_main');

$db['link'] = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['main']);
if (mysqli_connect_errno()) {
	die("Failed to connect to database: " . mysqli_connect_error());
}
mysqli_query($db['link'], "SET character_set_client=utf8");
mysqli_query($db['link'], "SET character_set_connection=utf8");
mysqli_query($db['link'], "SET character_set_results=utf8");
?>