<? 
use Vgsite\Page;
$page = new Page();
$page->width = "fixed";
$page->minimalist = true;

use Vgsite\Badge;
$_badges = new badges();

$un  = trim($_GET['username']);
$bid = trim($_GET['bid']);

if($un){
	
	$page->title = $un."'s badges - Videogam.in";
	$page->header();
	
	$q = "SELECT * FROM users WHERE username = '".mysqli_real_escape_string($GLOBALS['db']['link'], $un)."' LIMIT 1";
	if(!$udat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) $page->kill("No user found named '$un'");
	
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