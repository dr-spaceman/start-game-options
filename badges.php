<? 
require($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");
$page = new page;
$page->width = "fixed";
$page->minimalist = true;

require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.badges.php";
$_badges = new badges();

$un  = trim($_GET['username']);
$bid = trim($_GET['bid']);

if($un){
	
	$page->title = $un."'s badges - Videogam.in";
	$page->header();
	
	$q = "SELECT * FROM users WHERE username = '".mysql_real_escape_string($un)."' LIMIT 1";
	if(!$udat = mysql_fetch_object(mysql_query($q))) $page->die_("No user found named '$un'");
	
	if(!$bid) echo '<h1>'.$udat->username.'\'s Badges</h1>';
	else {
		if($o_badge = $_badges->show($bid, $udat->usrid)) echo $o_badge;
		else echo '<h1>'.$udat->username.'\'s Badges</h1>';
	}
	
	?><div class="hr" style="margin:10px 0;"></div><?
	
	echo $_badges->collection($udat->usrid, $un);
	
	$page->footer();
	exit;
	
}