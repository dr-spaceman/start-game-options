<?
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.ajax.php";

$a = new ajax();

if($_POST['submit_avatar']) {
	$av = $_POST['avatar'];
	if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/img/avatars/".$av)) {
		$q = "UPDATE users SET avatar='".$av."' WHERE usrid='$usrid' LIMIT 1";
		if(!mysql_query($q)) $a->kill("Couldn't update user database; Avatar not set.");
	} else $a->kill("Avatar file [".$av."] doesn't exist?");
	//success -- return formatted Avatar
	$user = new user();
	$a->ret['formatted'] = $user->avatar();
	exit;
}