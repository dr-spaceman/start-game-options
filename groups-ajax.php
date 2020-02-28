<?
require ($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");

$do = $_POST['do'];

if($do == "update option") {
	$op = $_POST['option'];
	$val = $_POST['val'];
	$group_id = $_POST['group_id'];
	
	$q = "UPDATE groups_members SET `$op`='$val' WHERE group_id='$group_id' AND usrid='$usrid' LIMIT 1";
	if(mysqli_query($GLOBALS['db']['link'], $q)) echo "ok";
	else echo "error: couldn't updata database; ".mysql_error();
}