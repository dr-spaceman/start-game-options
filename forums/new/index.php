<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
$page = new page;
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.forums.php");
$forum = new forum();

$page->title = "Videogam.in forums / New Topics";
$page->header();

$since = $_GET['since'];
if($usrid && !$since) $since = 'last-login';
elseif(!$usrid && !$since) $since = 1;

if($since == 'last-login') {
	$time_interval = "'$usrlastlogin'";
	$words = 'New Topics For You';
} elseif(is_numeric($since)) {
	$time_interval = 'DATE_ADD(CURDATE(), INTERVAL -'.$since.' DAY)';
	$words = "New Topics in the past $since day";
	if($since != 1) $words.= "s";
} else {
	echo "Error: There is an illegal value input for time since last posts ($since).";
	$page->footer();
	exit;
}
$query = "SELECT * FROM forums_topics as t WHERE `last_post` > $time_interval AND `invisible` <= '$usrrank' ORDER BY `last_post` DESC";	
$new_topic_num = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query));

//page navigation
if($new_topic_num > $forum->topics_per_page) {
	if(!$pg = $_GET['pg']) $pg = 1;
	$pgs = ceil($new_topic_num / $forum->topics_per_page);
	$pgmin = ($forum->topics_per_page * $pg) - $forum->topics_per_page;
	$query.= " LIMIT $pgmin, $forum->topics_per_page";
	$didnt_show = 0;
	for($i = 1; $i <= $pgs; $i++) {
		$show = FALSE;
		if($i > ($pg - 5) && $i < ($pg + 5)) $show = TRUE;
		if($i == 1 || $i == $pgs) $show = TRUE;
		if($show) {
			$p_pagenav.= ($pg == $i ? '<li><b>'.($i == 1 ? 'Page 1' : $i).'</b></li>' : '<li><a href="?since='.$since.'&pg='.$i.'">'.($i == 1 ? 'Page 1' : $i).'</a></li>');
			$didnt_show = 0;
		} elseif(!$didnt_show) {
			$p_pagenav.= '<li><span>&middot;&middot;&middot;</span></li>';
			$didnt_show++;
		}
	}
}

?>
<div id="forum">

<h1><?=$words?></h1>

<div id="forumdesc">
	<?=$new_topic_num?> topic<?=($new_topic_num != 1 ? 's' : '')?> with new posts &nbsp; 
	<select onchange="window.location='/forums/new/?since='+this.options[this.selectedIndex].value">
			<option value="">Show new topics...</option>
			<?=($usrid ? '<option value="last-login">since your last login</option>' : '')?>
			<option value="1">in the past 24 hours</option>
			<option value="2">in the past 48 hours</option>
			<option value="7">in the past week</option>
			<option value="14">in the past 2 weeks</option>
			<option value="30">in the past month</option>
		</select>
	<div class="speechpt"></div>
</div>
<br style="clear:left;"/>

<div id="forum-body">
	
	<?=($p_pagenav ? '<div class="menu"><ul><li>'.$p_pagenav.'</li></ul></div>' : '')?>
	
	<table border="0" cellpadding="0" cellspacing="0" width="100%" class="topic-list plain">
	<tr>
		<th>Title</th>
		<th>Forum</th>
		<th nowrap="nowrap" style="text-align:center"># Replies</th>
		<th>Last Post</th>
	</tr>
	<?
	
	$q2 = "SELECT * FROM forums";
	$r2 = mysqli_query($GLOBALS['db']['link'], $q2);
	while($row = mysqli_fetch_assoc($r2)){
		$f[$row['fid']] = $row['title'];
	}
	
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)) {
			
			if(strstr($row['location'], "group:")){
				$group_id = substr($row['location'], 6);
				$q = "SELECT * FROM groups_members WHERE group_id='$group_id' AND usrid='$usrid' LIMIT 1";
				if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) continue;
			}
			
			$print_closed = '';
			if($usrrank < $row['closed'] || ($row['closed'] && $usrrank >= 5)) $print_closed = ' class="locked"';
			
			$num_replies = $row['posts'] - 1;
			
			if($row['last_post'] == "0000-00-00 00:00:00") {
				$last_post = "";
			} else {
				$last_post = timeSince($row['last_post']).' ago<br/>by '.($row['last_post_usrid'] ? outputUser($row['last_post_usrid'], FALSE) : $row['last_post_author']);
			}
				
			?>
			<tr>
				<td class="topic-title">
					<span class="freshness-icon <?=($usrlastlogin < $row['last_post'] ? 'new' : 'old')?>" title="This forum has <?=($usrlastlogin < $row['last_post'] ? 'new' : 'no new')?> topics for you"></span>
					<a href="<?=$forum->topicURL($row['tid'], $row)?>"<?=$print_closed?>><?=stripslashes($row['title'])?></a>
				</td>
				<td><?=($row['fid'] ? '<a href="/forums/?fid='.$row['fid'].'">'.$f[$row['fid']].'</a>' : '')?></td>
				<td style="text-align:center"><?=$num_replies?></td>
				<td nowrap="nowrap" class="last-post"><?=$last_post?></td>
			</tr>
			<?
		}
		
	?>
	</table>
	
	<?=($p_pagenav ? '<div class="menu"><ul><li>'.$p_pagenav.'</li></ul></div>' : '')?>
	
</div>
</div>
<?

$page->footer();
?>