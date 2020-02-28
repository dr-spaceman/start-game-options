<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
$page = new page;
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.forums.php");
$forum = new forum();

if($q = $_GET['query']) {
	$source = $_GET['source'];
	if(!$source || $source != "t") $source = "tp";
	$query = "
		SELECT tid, MATCH (title) AGAINST ('".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."') AS score 
		FROM forums_topics 
		WHERE MATCH (title) AGAINST ('".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."') AND `invisible` <= '$usrrank';
	";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$matches[$row['tid']] = $row['score'];
	}
	if($source == "tp") {
		//check posts, too
		$query = "
			SELECT tid, MATCH (message) AGAINST ('".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."') AS score 
			FROM forums_posts 
			WHERE MATCH (message) AGAINST ('".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."') AND `invisible` <= '$usrrank';
		";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)) {
			//give the matched item the best score, wether from the title or the post message
			$matches[$row['tid']] = ($matches[$row['tid']] > $row['score'] ? $matches[$row['tid']] : $row['score']);
		}
	}
}

$matchnum = count($matches);

$page->title = "Videogam.in forums / search" . ($q ? " [$q]" : "");
$page->header();

?>
<div id="forum">
	
	<h1><small><a href="/forums/">Forums</a> / <span>Search Results</span> / </small><?=$q?></h1>
	
	<div id="forumdesc">
		<?=$matchnum?> topic<?=($matchnum != 1 ? 's' : '')?> found
		<div class="speechpt"></div>
	</div>
	<br style="clear:left;"/>
	
	<p><a href="http://www.google.com/search?q=site:videogam.in/forums+<?=$q?>" class="arrow-link">Use Google instead to search these forums for "<?=$q?>"</a></p>
	
	<p></p>
	
		<form action="search.php" method="get">
			<table border="0" cellpadding="0" cellspacing="5">
				<tr>
					<td colspan="2" style="font-size:12px; color:#666;">
						<label><input type="radio" name="source" value="tp" checked="checked"/>Search topic titles and posts</label> &nbsp; 
						<label style="white-space:nowrap"><input type="radio" name="source" value="t"/>Search only topic titles</label>
					</td>
				</tr>
				<tr>
					<td><input type="text" name="query" value="<?=htmlSC($q)?>" size="40"/></td>
					<td><input type="submit" value="Search"/></td>
				</tr>
			</table>
		</form>
	
	<?
	
	if(!$matchnum = count($matches)) {
		$page->footer();
		exit;
	}
	
	arsort($matches);
	
	$last_login = $usrlastlogin;
	
	//page navigation
	if($matchnum > $forum->topics_per_page) {
		if(!$pg = $_GET['pg']) $pg = 1;
		$pgs = ceil($matchnum / $forum->topics_per_page);
		$pgmin = ($forum->topics_per_page * $pg) - $forum->topics_per_page;
		$querylm = " LIMIT $pgmin, $forum->topics_per_page";
		$didnt_show = 0;
		for($i = 1; $i <= $pgs; $i++) {
			$show = FALSE;
			if($i > ($pg - 5) && $i < ($pg + 5)) $show = TRUE;
			if($i == 1 || $i == $pgs) $show = TRUE;
			if($show) {
				$p_pagenav.= ($pg == $i ? '<li><b>'.($i == 1 ? 'Page 1' : $i).'</b></li>' : '<li><a href="?source='.$source.'&query='.$q.'&pg='.$i.'">'.($i == 1 ? 'Page 1' : $i).'</a></li>');
				$didnt_show = 0;
			} elseif(!$didnt_show) {
				$p_pagenav.= '<li><span>&middot;&middot;&middot;</span></li>';
				$didnt_show++;
			}
		}
	}
	
	?>
	<div id="forum-body">
		
		<?=($p_pagenav ? '<div class="menu"><ul><li>'.$p_pagenav.'</li></ul></div>' : '')?>
		
		<table border="0" cellpadding="0" cellspacing="0" width="100%" class="topic-list">
		<tr>
			<th width="20">&nbsp;</th>
			<th>Title</th>
			<th>Forum</th>
			<th nowrap="nowrap" style="text-align:center"># Replies</th>
			<th>Last Post</th>
		</tr>
		<?
		$tids = array_keys($matches);
		for($i = ($pgmin - 1); $i < ($pgmin + $forum->topics_per_page); $i++) {
			
			$tid = $tids[$i];
			
			$query = "SELECT * FROM forums_topics WHERE tid='$tid' LIMIT 1";
			$res = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)) {
				
				$row['title'] = stripslashes($row['title']);
				
				if($last_login < $row['last_post']) {
					$lightbulb = '<img src="/bin/img/mascot.png" alt="new posts" border="0"/>';
				} else {
					$lightbulb = '<img src="/bin/img/mascot-off.png" alt="no new posts" border="0"/>';
				}
				
				//get forum
				$q = "SELECT title, fid FROM forums WHERE fid='".$row['fid']."' LIMIT 1";
				if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
					$p_forum = '<a href="/forums/?fid='.$dat->fid.'">'.$dat->title.'</a>';
				} else $p_forum = "";
				
				echo '
				<tr>
					<td>'.$lightbulb.'</td>
					<td>'.($print_forum ? $print_forum.' / ' : '').'<a href="/forums/?tid='.$row['tid'].'">'.$row['title'].'</a></td>
					<td nowrap="nowrap">'.$p_forum.'</td>
					<td style="text-align:center">'.($forum->numberOfPosts($row['tid']) - 1).'</td>
					<td nowrap="nowrap">'.($row['last_post'] ? timeSince($row['last_post']).' ago by '.outputUser($row['last_post_usrid'], FALSE) : '&nbsp;').'</td>
				</tr>';
			}
		}
		?>
		</table>
		
		<?=($p_pagenav ? '<div class="menu"><ul><li>'.$p_pagenav.'</li></ul></div>' : '')?>
		
	</div>
</div>

<?
$page->footer();
?>