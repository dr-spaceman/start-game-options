<?
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.shelf.php";

if(!$GLOBALS['db']['link']) die("Couldn't find db link!");

echo "Hello world!";

$shelf = new Shelf();

$min = 0;
$max = 5000;
$query = "SELECT SQL_CALC_FOUND_ROWS * FROM collection WHERE usrid='1' ORDER BY sort ASC, date_added DESC LIMIT $min, $max";
$res = mysqli_query($GLOBALS['db']['link'], $query);
while($row = mysqli_fetch_assoc($res)){
	$shelf->addItem($row);
}

$shelf->output();

echo "Goodbye cruel world!";