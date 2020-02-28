<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
$page = new page;
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.posts.php");
$posts = new posts();
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/htmltoolbox.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");

$q = "";
if($nid = $_GET['id']) {
	$q = "SELECT nid, permalink FROM posts WHERE nid='".mysqli_real_escape_string($GLOBALS['db']['link'], $nid)."' LIMIT 1";
} elseif($sessid = $_GET['session_id']) {
	$q = "SELECT nid, permalink FROM posts WHERE session_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $sessid)."' LIMIT 1";
}
if($q) {
	if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
		$c = ($dat->category == "news" ? "news" : "posts");
		//$d = substr($dat->datetime, 0, 10);
		//$d = str_replace("-", "/", $d);
		header("Location: /sblog/".$dat->nid."/".$dat->permalink);
		exit;
	}
}

$catg = "news";
include("../posts/archive.php");

?>