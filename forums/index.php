<?
use Vgsite\Page;
$page = new Page();
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.forums.php");

$do = mysqli_real_escape_string($GLOBALS['db']['link'], $_GET['do']);
$fid = mysqli_real_escape_string($GLOBALS['db']['link'], $_GET['fid']);
$tid = mysqli_real_escape_string($GLOBALS['db']['link'], $_GET['tid']);
$pg = mysqli_real_escape_string($GLOBALS['db']['link'], $_GET['page']);
$tag = mysqli_real_escape_string($GLOBALS['db']['link'], $_GET['tag']);

$forum = new forum();

if($tag) {
	
	$tag = formatName($tag);
	$page->title = "Videogam.in forums / ".htmlSC($tag);
	$page->header();
	$forum->tag = $tag;
	$forum->showForum();
	$page->footer();
	exit;
	
} elseif($_GET['location']) {
	
	$loc = $_GET['location'];
	$page->title = "Videogam.in forums / ".htmlSC($loc);
	$page->header();
	$forum->location = $loc;
	$forum->showForum();
	$page->footer();
	exit;

} elseif($_GET['category']) {
	
	// CATEGORY //
	
	$cid = trim($_GET['category']);
	$query = "SELECT * FROM forums_categories WHERE cid='".mysqli_real_escape_string($GLOBALS['db']['link'], $cid)."' LIMIT 1";
	if(!$cat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query))) $page->kill("Couldn't get category info for category '$cid'");
	
	$page->title = "Videogam.in forums / ".$cat->category;
	$page->header();
	
	$uval = $forum->getUserValue($usrid);
	$last_login = $forum->getLastLogin();
			
	?>
	<div id="forum">
		<h1><?=$cat->category?></h1>
		<div id="forum-body">
				
			<table border="0" cellpadding="-" cellspacing="0" width="100%" class="forum-index-list plain">
				<?
					$query = "SELECT * FROM `forums` WHERE `cid` = '$cid' AND `invisible` <= '$_SESSION['user_rank']' AND no_index != '1'";
					$res = mysqli_query($GLOBALS['db']['link'], $query);
					while($row = mysqli_fetch_assoc($res)) {
						
						$last = $forum->getLastForumInfo($row['fid']);
							
							?>
							<tr>
								<td>
									<span class="freshness-icon <?=($usrlastlogin > $last['last_post'] ? 'new' : 'old')?>" title="This forum has <?=($usrlastlogin > $last['last_post'] ? 'new' : 'no new')?> topics for you"></span>
									<a href="?fid=<?=$row['fid']?>" class="forum-name"><?=$row['title']?></a>
									<?=($row[description] ? '<div class="forum-desc">'.stripslashes($row['description']).'</div>' : '')?>
								</td>
								<td nowrap="nowrap" style="color:#999;">
									<span style="color:black"><?=mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM forums_topics WHERE fid='".$row['fid']."';"))?></span> topics
								</td>
							</tr>
							<?
						
					}
					
				?>
				</table>
				
			</div>
			</div>
			<?
			
	
	$page->footer();
	exit;
	
} elseif($fid == "fid" && $tid) {
	
	$page->title = "Videogam.in forums / forum id $tid";
	$page->header();
	$forum->showForum($tid);
	$page->footer();
	exit;

} elseif($fid) {
	$forum->fid = $fid;
	$query = "SELECT * FROM forums WHERE fid = '$fid' LIMIT 1";
} elseif($tid) {
	$query = "SELECT * FROM forums_topics WHERE tid = '$tid' LIMIT 1";
} else {
	
	///////////
	// INDEX //
	///////////
	
	$page->first_section = array("id"=>"forum", "class"=>"forum-index");
	
	$page->title = "The Videogam.in Message Forums of DEATH!!!";
	$page->meta_description = "The discussion community where the topic is not actually death, but rather the life of great videogames and the people, music, design, and philosophy that make them great (though death is often wished upon some of these subjects in particular).";
	$page->meta_keywords = "[GAME_TITLES],forums,forum,discussion,intelligent discussion,community,mature,adult";
	$page->header();
	
	$num_posts = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM forums_posts"));
	$num_topics = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM forums_topics"));
	
	//Count new topics
	$num_new = $usrid ? mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM forums_topics WHERE last_post > '$usrlastlogin'")) : mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM forums_topics WHERE last_post > DATE_ADD(CURDATE(), INTERVAL -1 DAY)"));
	
	?>
	<h1>Forums</h1>
	
	<div style="display:inline-block; border:1px solid #CCC; padding:10px 20px 10px 10px;">
		<ul style="font-size:110%">
			<li><a href="?fid=1">Gaming Topics</a></li>
			<li><a href="?fid=3">Non-Gaming Topics</a></li>
			<li><a href="new/">New Topics <b>(<?=($num_new ? $num_new : 'none')?>)</b></a></li>
		</ul>
	</div>
	
					
					<form action="/search.php" method="get" style="display:none">
						<input type="hidden" name="what" value="forums"/>
						<fieldset class="search-forums" style="width:300px">
							<legend>Search the Forums</legend>
							<table border="0" cellpadding="0" cellspacing="5" width="100%">
								<tr>
									<td width="100%">
										<div style="margin-right:6px"><input type="text" name="q" style="width:100%;"/></div>
									</td>
									<td>
										<input type="submit" value="Search"/>
									</td>
								</tr>
							</table>
						</fieldset>
					</form>
					
					<h2>Recent Topics</h2>
					<div style="line-height:25px">
						<?
						$query = "SELECT tag FROM forums_tags LEFT JOIN forums_posts USING (tid) WHERE posted > DATE_ADD(CURDATE(), INTERVAL -7 DAY)";
						$res = mysqli_query($GLOBALS['db']['link'], $query);
						if($topicnum = mysqli_num_rows($res)) {
							while($row = mysqli_fetch_assoc($res)) {
								$tags[$row['tag']]++;
							}
							//randomize
							$aux = array();
							$keys = array_keys($tags);
							shuffle($keys);
							foreach($keys as $key) {
								$aux[$key] = $tags[$key];
								unset($tags[$key]);
				    	}
				    	$tags = $aux;
				    	
							$mean = array_sum($tags) / count($tags);
							while(list($tag, $num) = each($tags)) {
								unset($tagwords);
								$fontsize = 7 + ($num / 17 * $mean);
								if($fontsize > 25) $fontsize = 25;
								$tagword = $tag;
								if(strstr($tagword, "AlbumID:")){
									$albumid = substr($tagword, 8);
									$q = "SELECT `title`, `subtitle` FROM albums WHERE albumid='".mysqli_real_escape_string($GLOBALS['db']['link'], $albumid)."' LIMIT 1";
									if($album = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) $tagword = $album->title.($album->subtitle ? ' <i>'.$album->subtitle.'</i>' : '');
								}
								if($tagword) echo '<a href="/forums/?tag='.formatNameURL($tag).'" class="forum-tag" style="font-size:'.$fontsize.'pt" title="'.$num.' post'.($num != 1 ? 's' : '').'">'.$tagword.'</a>'."&nbsp;\n";
							}
						} else {
							echo "No topics discussed during this timeframe. ";
						}
						?>
					</div>
					
					<h2>Top Posters</h2>
					<a href="#" class="arrow-toggle arrow-toggle-on preventdefault" onclick="if( $(this).hasClass('arrow-toggle-on') ) return; $(this).addClass('arrow-toggle-on').siblings('a').removeClass('arrow-toggle-on'); $('#posters1').show().siblings('.posters').hide();">This Week</a> &nbsp; 
					<a href="#" class="arrow-toggle preventdefault" onclick="if( $(this).hasClass('arrow-toggle-on') ) return; $(this).addClass('arrow-toggle-on').siblings('a').removeClass('arrow-toggle-on');$('#posters2').show().siblings('.posters').hide();">All Time</a> &nbsp; 
					<a href="#" class="arrow-toggle preventdefault" onclick="if( $(this).hasClass('arrow-toggle-on') ) return; $(this).addClass('arrow-toggle-on').siblings('a').removeClass('arrow-toggle-on'); $('#posters3').show().siblings('.posters').hide();">Top Rated</a>
					<ol id="posters1" class="posters" style="font-size:15px;">
						<?
						$query = "SELECT usrid, COUNT(usrid) AS postnum FROM forums_posts WHERE DATE_SUB(CURDATE(),INTERVAL 7 DAY) <= posted GROUP BY usrid ORDER BY postnum DESC LIMIT 5";
						$res   = mysqli_query($GLOBALS['db']['link'], $query);
						while($row = mysqli_fetch_assoc($res)) {
							echo '<li style="margin:4px 0;">'.outputUser($row['usrid']).' &nbsp; '.$row['postnum'].' <span style="color:#777;">posts</span></li>';
						}
						?>
					</ol>
					<ol id="posters2" class="posters" style="display:none; font-size:15px;">
						<?
						$query = "SELECT usrid, COUNT(usrid) AS postnum FROM forums_posts GROUP BY usrid ORDER BY postnum DESC LIMIT 5";
						$res   = mysqli_query($GLOBALS['db']['link'], $query);
						while($row = mysqli_fetch_assoc($res)) {
							echo '<li style="margin:4px 0;">'.outputUser($row['usrid']).' &nbsp; '.$row['postnum'].' <span style="color:#777;">posts</span></li>';
						}
						?>
					</ol>
					<ol id="posters3" class="posters" style="display:none; font-size:15px;">
						<?
						$query = "SELECT usrid FROM users WHERE usrid != '1' ORDER BY forum_rating DESC LIMIT 5";
						$res   = mysqli_query($GLOBALS['db']['link'], $query);
						while($row = mysqli_fetch_assoc($res)) {
							echo '<li style="margin:4px 0;">'.outputUser($row['usrid']).'</li>';
						}
						?>
					</ol>
	
	<h2>All Forums</h2>
			<table border="0" cellpadding="0" cellspacing="0" width="100%" class="forum-index-list plain">
				<?
				$query2 = "SELECT * FROM `forums_categories` ORDER BY `sort`";
				$res2 = mysqli_query($GLOBALS['db']['link'], $query2);
				while($c = mysqli_fetch_assoc($res2)) {
					$c['category'] = stripslashes($c['category']);
					$c['description'] = stripslashes($c['description']);
					?>
					<tr>
						<th colspan="2"><?=$c['category']?> &nbsp; <small><?=$c['description']?></small></th>
					</tr>
					<?
					
						$query = "SELECT * FROM `forums` WHERE `cid` = '$c[cid]' AND `invisible` <= '$_SESSION['user_rank']' AND no_index != '1'";
						$res = mysqli_query($GLOBALS['db']['link'], $query);
						while($row = mysqli_fetch_assoc($res)) {
							
							$last = $forum->getLastForumInfo($row['fid']);
							
							?>
							<tr>
								<td>
									<span class="freshness-icon <?=($usrlastlogin < $last['last_post'] ? 'new' : 'old')?>" title="This forum has <?=($usrlastlogin < $last['last_post'] ? 'new' : 'no new')?> topics for you"></span>
									<a href="?fid=<?=$row['fid']?>" class="forum-name"><?=$row['title']?></a>
									<?=($row[description] ? '<div class="forum-desc">'.stripslashes($row['description']).'</div>' : '')?>
								</td>
								<td nowrap="nowrap" style="color:#999;">
									<span style="color:black"><?=mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM forums_topics WHERE fid='".$row['fid']."';"))?></span> topics
								</td>
							</tr>
							<?
						}
				}
				?>
			</table>
			
	<div style="line-height:1.5em">
		<h2>Colophon</h2>
		<p>The Videogam.in <i>Message Forums fo DEATH!!!</i> is a place where serious gamers can discuss gaming topics in an unserious manner. So far, <b><?=number_format(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT DISTINCT(usrid) FROM forums_posts")))?></b> people have posted <b><?=number_format($num_posts)?></b> messages in <b><?=number_format($num_topics)?></b> topics.</p>
				<blockquote style="display:block; margin:0; padding:0 0 0 20px; background:url('/bin/img/quote_sm.png') no-repeat 0 5px;"><p>Frequent and loud laughter is the characteristic of folly and ill manners; it is the manner in which <a href="/users/">the mob</a> express their silly joy at silly things; and they call it being merry. In my mind, there is nothing so illiberal, and so ill-bred, as audible laughter. True wit, or sense, never yet made any body laugh; they are above it. They please the mind, and give a cheerfulness to the countenance.</p><p>But it is <a href="/forums">low buffoonery</a>, or silly accidents, that always excite laughter; and that is what people of sense and breeding should show themselves above.</p></blockquote>
				<div class="quoter" style="color:#333;">&mdash; <b>Philip Stanhope, 4th Earl of Chesterfield</b></div>
	</div>
			
	<?
	
	$page->footer();
	exit;
	
}

$res = mysqli_query($GLOBALS['db']['link'], $query);
$row = mysqli_fetch_object($res);

$page->title = "Videogam.in forums / " . stripslashes($row->title) . ($row->topic_title ? " / ".stripslashes($row->topic_title) : "").($pg > 1 ? ' / page '.$pg : '');
$page->header();
if($forum->fid) $forum->showForum();
else $forum->showTopic($tid, $pg);
$page->footer();

?>