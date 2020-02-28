<?
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";

echo "Hello world!";

$query = "SELECT * FROM `pages` LIMIT 100";
$res = mysqli_query($GLOBALS['db']['link'], $query);
while ($row = mysqli_fetch_assoc($res)) {
	print_r($row);
}

echo "Goodbye cruel world!";