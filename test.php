<?
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";

if(!$GLOBALS['db']['link']) die("Couldn't find db link!");

echo "Hello world!";

$query = "UPDATE `foo` set `bar` = 'xxx' where `bar` = 'x'";
$res = mysqli_query($GLOBALS['db']['link'], $query);
if(!$res) {
	die("Database UPDATE error" . mysqli_error($GLOBALS['db']['link']));
}

echo "Goodbye cruel world!";

// foo
// bar