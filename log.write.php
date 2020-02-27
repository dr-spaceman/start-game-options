<?
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php";
print_r($_POST);
if($_POST['q']){
	$q=$_POST['q'];
	$fp = fopen($_SERVER['DOCUMENT_ROOT']."/log", "w");
	fwrite($fp, date("YmdHis")." $usrid $q\n");
	fclose($fp);
}
?>