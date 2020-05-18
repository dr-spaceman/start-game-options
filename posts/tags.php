<?
use Vgsite\Page;
$page = new Page();
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.posts.php");
$posts = new posts();
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");

$page->freestyle = '
h1 { color:#CCC; font-size:25px; text-shadow:none; }
';

if(!$tag) { // $tag could be sent already via games-output.php, otherwise .htaccess gives $path
	//$path could be like "gid:41/Final-Fantasy-V/" or "E3-2009/"
	$path = array();
	$path = explode("/", $_GET['path']);
	$tag = urldecode($path[0]);
}

if($tag == "not-so-recent" || $tag == "all") {
	$intv = $tag;
	$tag = "";
}

if($tag) {
	
	$query = array("tags" => array(formatName($tag)));
	$url = '/#/posts/?'.http_build_query($query);
	
	header("HTTP/1.1 301 Moved Permanently"); 
	header("Location: $url"); 
	
	exit;
	
	/* old method below */
	
	$tag = formatName($tag);
	
	$category = $_GET['category'];
	if($category == "news") {
		$where = " AND category = 'news'";
		$rootlink = "news";
	} elseif($category == "blogs") {
		$category = "blog";
		$where = " AND category = 'blog'";
		$rootlink = "blogs";
	} elseif($category == "content") {
		$where = " AND category = 'content'";
	}
	if(!$rootlink) $rootlink = "posts";
	
	$page->title = "Videogam.in ".($category ? ucwords($category) : 'Post')." Topics / ".htmlspecialchars($tag);
	$page->header();
	
	if($gamepg) echo '<div id="gamecont"><div class="conts">';
	else echo '<h1><a href="/'.$rootlink.'/">'.($category ? ucwords($category) : 'Sblogs').'</a> / <a href="/'.$rootlink.'/topics/">Topics</a> / <span style="color:#333">'.$tag.'</span></h1>';
	
	echo $addheading;
	
	$query = "SELECT * FROM posts_tags LEFT JOIN posts USING (nid) WHERE tag = '".mysqli_real_escape_string($GLOBALS['db']['link'], $tag)."' AND unpublished='0' AND pending='0' $where ORDER BY posts.`datetime` DESC";
	$posts->postsNav($query);
	
	if($gamepg) echo '</div></div>';
	$page->footer();
	
} else {
	
	$query = "SELECT tag FROM posts LEFT JOIN posts_tags using(nid) WHERE unpublished = '0' AND pending = '0'";
	$category = $_GET['category'];
	if($category == "news") {
		$query .= " AND category = 'news'";
	} elseif($category == "blogs") {
		$query.= " AND category = 'blog'";
	} elseif($category == "content") {
		$query.= " AND category = 'content'";
		$category = "";
	}
	if(!$category) $category = "posts";
	
	if(!$intv) $query.= " AND DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= posts.`datetime`";
	elseif($intv == "not-so-recent") $query.= " AND DATE_SUB(CURDATE(),INTERVAL 180 DAY) <= posts.`datetime`";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$tags[$row['tag']]++;
	}
	$page->title = "Videogam.in Topics Index";
	$page->freestyle.= '
		A.tag { padding:0 2px; }
		A.tag:HOVER { text-decoration:none; background-color:#06C; color:white; }
	';
	$page->header();
	?>
	<h1><a href="/posts/">Sblogs</a> / <span style="color:black;">Topic Index</span></h1>
	<table border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td style="padding:5px 8px; border:1px solid #DDD; color:#666;">
				<?=(!$intv ? '<span class="arrow-toggle arrow-toggle-on">Recent topics</span>' : '<a href="/'.$category.'/topics/" class="arrow-toggle">Recent topics</a>')?> &nbsp; 
				<?=($intv == "not-so-recent" ? '<span class="arrow-toggle arrow-toggle-on">Not-so-recent topics</span>' : '<a href="/'.$category.'/topics/not-so-recent" class="arrow-toggle">Not-so-recent topics</a>')?> &nbsp; 
				<?=($intv == "all" ? '<span class="arrow-toggle arrow-toggle-on">All topics</span>' : '<a href="/'.$category.'/topics/all" class="arrow-toggle">All topics</a>')?>
			</td>
		</tr>
	</table>
	
	<div style="line-height:40px">
		<?
		if(!$tags) {
			echo "No topics within this timeframe.";
		} else {
			//randomize
			$aux = array();
			$keys = array_keys($tags);
			shuffle($keys);
			foreach($keys as $key){
				$aux[$key] = $tags[$key];
				unset($tags[$key]);
			}
			$tags = $aux;
			
			$mean = array_sum($tags) / count($tags);
			$mean = ($mean < 5 ? 5 : $mean);
			while(list($tag, $num) = each($tags)) {
				unset($tagwords);
				$fontsize = 8 + (($num * $mean) * .1);
				if($fontsize > 40) $fontsize = 40;
				
				$tagword = $tag;
				
				if(strstr($tagword, "AlbumID:")){
					$albumid = substr($tagword, 8);
					$q = "SELECT `title`, `subtitle` FROM albums WHERE albumid='".mysqli_real_escape_string($GLOBALS['db']['link'], $albumid)."' LIMIT 1";
					if($album = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) $tagword = $album->title.($album->subtitle ? ' <i>'.$album->subtitle.'</i>' : '');
				}
				
				$query = array("tags" => array($tag));
				
				echo '<a href="/#/posts/?'.http_build_query($query).'" style="font-size:'.$fontsize.'pt;'.($fontsize < 12 ? ' white-space:nowrap;' : '').'" title="'.$num.' topic'.($num != 1 ? 's' : '').'" class="tag">'.$tagword.'</a>'."&nbsp;\n";
			}
		}
		?>
	</div>
	<?
	
	$page->footer();
	exit;

}

?>