<?
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";

if(!$GLOBALS['db']['link']) die("Couldn't find db link!");

echo "Hello world!";

$query = "SELECT * FROM `pages` LIMIT 100";
$res = mysqli_query($GLOBALS['db']['link'], $query);
while ($row = mysqli_fetch_assoc($res)) {
	print_r($row);
}

$query = "UPDATE `foo` set `bar` = 'xxx' where `bar` = 'x'";
$res = mysqli_query($GLOBALS['db']['link'], $query);
if(!$res) {
	die("Database UPDATE error: " . mysqli_error($GLOBALS['db']['link']));
}

echo "Goodbye cruel world!";