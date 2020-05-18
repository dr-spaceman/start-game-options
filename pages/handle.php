<?
use Vgsite\Page;
$page = new Page();
require $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.pages.php";

$pieces = array("history", "links", "discuss", "edit");

$index = $_GET['index'];
$pgid  = $_GET['pgid'];
$title = $_GET['title'];
$piece = $_GET['piece'];
$path  = $_GET['path'];
if($_GET['via'] == "script_url"){
	$path = $_SERVER['SCRIPT_URL'];
	$viascript = true;
}
if($path){
	if(substr($path, 0, 1) == "/") $path = substr($path, 1);
	$patharr = explode("/", $path);
	$index = $patharr[0];
	$title = $patharr[1];
	$piece = $patharr[2];
	// if the last var is not a real dir, it's probably part of the title
	// ie Kingdom_Hearts:_358/2_Days
	if($piece && !in_array($piece, $pieces)) $title.= '/'.$piece;
}

// Special pages accessed via Special: namespace

if($title == "Special:random") {
	
	$q = "SELECT title FROM pages WHERE redirect_to = '' ORDER BY RAND() LIMIT 1";
	if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) header("Location: ".pageURL($row['title']));
	else die("Error finding random page");
	
} elseif($title == "Special:featured") {
	
	$page->title = "Featured Content - Videogam.in";
	$page->css = array_diff($page->css, array("/pages/pages_screen.css"));
	
	$page->header();
	
	?>
	<h1>Featured Content</h1>
	<ul>
		<?
		$query = "SELECT * FROM pages WHERE `title` != '' AND redirect_to = '' ORDER BY `title`";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)) {
			echo '<li><a href="'.pageURL($row['title'], $row['type']).'">'.$row['title'].'</a></li>';
		}
		?>
	</ul>
	<?
	
	$page->footer();
	exit;
	
} elseif($title == "Special:most_requested"){
	
	// Most requested pages
	
	$page->title = "Most Requested Content - Videogam.in";
	$page->css = array_diff($page->css, array("/pages/pages_screen.css"));
	
	$page->header();
	
	?>
	<h1>Most Reqested Content</h1>
	<p>The most requested page content that hasn't been started yet.</p>
	<ol>
		<?
		$query = "SELECT COUNT(*) AS `count`, `title` FROM `pagecount_requestfail` GROUP BY `title` ORDER BY `count` DESC LIMIT 50";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)){
			echo '<li><a href="/content/'.formatNameURL($row['title']).'">'.$row['title'].'</a> ('.$row['count'].' requests)</li>';
		}
		?>
	</ol>
	<?
	
	$page->footer();
	exit;
	
} elseif($title == "Special:new"){
	
	$page->title = "New Content Page - Videogam.in";
	$page->css = array_diff($page->css, array("/pages/pages_screen.css"));
	$page->minimalist = true;
	
	$page->header();
	
	?>
	<h1>New Content Page</h1>
	
	<div style="font-size:110%; line-height:1.5em;">
		
		<p>Welcome to the Videogam.in page creation wizard. To create a new content page, please continue with the below form.</p>
		
		There are several types of content pages:
		<ol style="color:#666; font-size:17px;">
			<li><b>Games</b> of all kinds</li>
			<li><b>People</b> who create games</li>
			<li><b>Categories</b> like <a href="/categories/Game_console">consoles</a>, <a href="/categories/Game_developer">companies</a>, <a href="/categories/Game_series">franchises</a>, <a href="/categories/Game_genre">genres</a>, <a href="/categories/Game_character">characters</a>, and <a href="/categories/Game_concept">game concepts</a></li>
			<li><b>Topics</b> like <a href="/topics/Censorship">censorship</a> and <a href="/topics/Cosplay">cosplay</a> that are none of the above</li>
		</ol>
		
		<p><span class="warn"></span>&nbsp;Before starting your first page, please read the <b><a href="/sblog/1823/page-editing-guide">Page Creating Guide &amp; F.A.Q.</a></b> and <b><a href="/bbcode.htm">BB Code Guide</a></b> for special formatting.</p>
		
		<p>If you think you're ready to start your page, input the page title exactly as you want it to appear. For help and guidelines regarding page names, please see <a href="/sblog/1823/page-editing-guide#Naming_Conventions">Naming Conventions</a> in the Page FAQ.</p>
		<form action="/pages/edit.php" method="get">
			<input type="text" name="title" value="<?=($_GET['title'] ? formatName($_GET['title']) : '')?>" size="35" style="font-size:18px;"/> 
			<input type="submit" value="Go" style="font-size:18px;"/>
		</form>
		<br/>
		
	</div>
	<?
	
	$page->footer();
	exit;
	
}

if($viascript) $givenfpath = "/$index/".formatNameURL_SC($title);
if($title) $title = formatName($title);
//echo "via:$_GET[via]|path:$path|givenfpath:$givenfpath|index:$index|title:$title|";

switch($piece){
	case "history":
		include "history.php";
		exit;
	
	case "links":
		include "links.php";
		exit;
		
	case "discuss":
		$_forum = new forum;
		$_forum->tag = $title;
		$_forum->depreciate_heading = TRUE;
		$_forum->showForum();
		exit;
	
	case "edit":
		$ile = TRUE;
		break;
}

do if($index && !$title){
	
	// INDEX //
	
	$p_index = ucwords($index);
	$p_index = htmlSC($p_index);
	
	$index_url = "/$index";
	
	// Check if it's a subcategory (special category index like characters, consoles, etc.
	$pgsubcategoriesF = array_flip($pgsubcategories);
	if($title = $pgsubcategoriesF[strtolower($index)]){
		$index = "categories";
		break;
	}
	
	$pgtypesF = array_flip($pgtypes);
	if(!$pgtypesF[$index]) $page->kill("<h1>Error</h1>'$index' is not a valid index type.");
	$index = $pgtypesF[$index];
	
	$page->title = "$p_index index &ndash; Videogam.in";
	$page->css = array_diff($page->css, array("/pages/pages_screen.css"));
	$page->freestyle.= '
		#indexcontainer nav {}
		#indexcontainer nav ul { margin:0; padding:0; list-style:none; height:29px; background:#EEE; background:RGBA(204,204,204,.5); }
		#indexcontainer nav li { margin:0; padding:0; float:left; }
		#indexcontainer nav li a { display:block; padding:0 11px; line-height:29px; font-size:16px; text-decoration:none; border-right:1px solid #CCC; }
		#indexcontainer nav .on a { font-weight:bold; color:#444; }
		#indexcontainer > ul { margin:10px 0; padding:0; list-style:none; }
		#indexcontainer > ul > li { margin:2px 0; padding:0 0 0 16px; font-size:15px; background:url(/bin/img/bullet-square.png) no-repeat 5px 6px; }
		#indexcontainer > .container { margin:1em 0; }
		#indexcontainer > .container.loading { opacity:.5 }
	';
	$page->javascript = '
		<script type="text/javascript">
			$(document).ready(function(){
				$("#indexcontainer nav").on("click", "a", function(ev){
					ev.preventDefault();
					if($(this).parent().hasClass("on")) return;
					$.address.value($(this).attr("href"));
				});
				$.address.change(function(event){
					console.log(event);
					if(!event.queryString) return;
					loading.on();
					$("#indexcontainer > .container").addClass("loading");
					$.get(
						"/pages/handle.index.php?index='.$index.'&"+event.queryString,
						function(ret){
							$("#indexcontainer nav a[href=\'"+event.value+"\']").parent().addClass("on bodybg").siblings().removeClass("on bodybg");
							$("#indexcontainer").animate({opacity:1}, 400, function(){
								$("#indexcontainer > .container").html(ret).removeClass("loading");
								loading.off();
							});
							tooltip();
						}
					);
				}).strict(false);
			});
		</script>
	';
	
	$page->header();
	
	$az = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","0");
	$letter = trim($_GET['letter']);
	$letter = strtolower($letter);
	if(!in_array($letter, $az)) $letter = "a";
	
	$nav = '<ul>';
	foreach($az as $a) $nav.= '<li class="'.($letter == $a ? 'on bodybg' : '').'"><a href="?letter='.$a.'" data-letter="'.$a.'">'.($a == "0" ? "#" : ucwords($a)).'</a></li>';
	$nav.= '</ul>';
	
	?>
	<h1><?=$p_index?></h1>
	<div id="indexcontainer">
		<nav><?=$nav?></nav>
		<div class="container"><? include "handle.index.php"; ?></div>
		<nav onclick="$('html, body').animate({scrollTop:180}, 700)"><?=$nav?></nav>
	</div>
	<?
	
	$page->footer();
	exit;
	
} while(false);

if($title) {
	
	// PAGE CONTENT //
	
	$pg = new pg($title);
	
	if($pg->redirect_to && $_GET['redirect'] != "no"){
		$redirected_from = $pg->title;
		$pg = new pg($pg->redirect_to);
		$pg->redirected_from = $redirected_from;
	}
	
	try{ $pg->loadData(); }
	catch(Exception $e){ $page->kill('There was an error loading data from the current version of this page: <code>' . $e->getMessage() . '</code>'); }
	
	//check the URL and redirect if necessary
	if($viascript && $pgindex && !$pg->redirected_from && strtolower($givenfpath) != strtolower("/$pgindex/$titleurl")){
		//header("Status: 301");
		echo("Location: /$pgindex/$titleurl");
	}
	
	$pg->header();
	$pg->output("include_footer");
	
	//badges for pageviews
	if($pg->trackView()){
		
		//Uber Trick - visit every page in the SSX series
		if (strstr($pg->title, "SSX")) {
			$query = "SELECT from_pgid AS pgid FROM pages_links WHERE `to` = 'SSX series' AND namespace = 'Category'";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($pl_row = mysqli_fetch_assoc($res)){
				$q = "SELECT * FROM pages_tracks WHERE pgid='".$pl_row['pgid']."' AND usrid='$usrid' LIMIT 1";
				if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))){
					$unvisited = true;
					break;
				}
			}
			if(!$unvisited) Badge::getById(60)->earn($user);
		}
	}
	
	$pg->footer();
	exit;

} else { include($_SERVER['DOCUMENT_ROOT']."/404.php"); }