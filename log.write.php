<?
use Vgsite\Page;
print_r($_POST);
if($_POST['q']){
	$q=$_POST['q'];
	$fp = fopen($_SERVER['DOCUMENT_ROOT']."/log", "w");
	fwrite($fp, date("YmdHis")." $usrid $q\n");
	fclose($fp);
}
?>