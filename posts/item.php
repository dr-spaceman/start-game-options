<?
use Vgsite\Image;

if(!$nid){
	$date = "$y-$m-$d";
	if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date)) {
		$page->title.= "[INVALID DATE]";
		$page->error_404 = TRUE;
		$page->kill('<h1>Input Error</h1>The date given ('.$date.') is invalid.');
	}
	$q = "SELECT nid FROM posts WHERE permalink='".mysqli_real_escape_string($GLOBALS['db']['link'], $desc_url)."' AND datetime LIKE '$date%' LIMIT 1";
	if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){ $nid = $row['nid']; }
}

try { $post = new post($nid); }
catch(Exception $e){
	require($_SERVER['DOCUMENT_ROOT']."/404.php");
	exit;
}

$post->options = $post->options ? json_decode($post->options, 1) : array();

$postuser = new user($post->usrid);

if(!$date){
	$date = substr($post->datetime, 0, 10);
	$date = str_replace("-", "/", $date);
	list($y, $m, $d) = explode("/", $date);
}

$tinyurl = "http://videogam.in/s".$post->nid;

$rootlink = "/posts/handle.php?";
$rootlink_qs['category'] = $post->category;
if($post->category == "public"){
	if($post->post_type == "review") $postedto = "Reviews";
	elseif($post->post_type == "preview") $postedto = "Previews";
	elseif($post->post_type == "quote") $postedto = "Quotes";
	elseif($post->post_type == "news") $postedto = "Gaming News";
	else $postedto = "Articles";
	$rootlink_qs['post_type'] = $post->post_type;
} elseif($post->category == "blog"){
	$rootlink_qs['user'] = $postuser->username;
	$postedto = $postuser->username."'s Blog";
}
if($post->archive){
	$rootlink_qs['archive'] = "1";
	$postedto = "Content Archives";
}

if($usrid == $post->usrid || $_SESSION['user_rank'] >= 7) {
	
	if($post->pending) $warnings[] = "This post has yet to be approved or vouched for and, as such, has yet to be published. In order for a ".$post->category." item to be published, it must be approved by an administrator or vouched for by several V.I.P. users.";
	
	$sincepub = time() - strtotime($post->datetime);
	$sincemod = ($post->datetime_last_edited ? time() - strtotime($post->datetime_last_edited) : $sincepub);

}

$naked_title = htmlSC($post->description);

//try to get a representative img
$img = '';
if($post->attachment == "image"){
	try { $img = new img($post->content['img_name'][0]); }
	catch(Exception $e){ unset($img); }
} else {
	preg_match('@\{img:([a-z0-9-_!\.]+)\|?(.*?)\}(?:\s)?@is', $post->content['text'], $imgmatch);
	if($imgmatch[1]){
		try { $img = new img($imgmatch[1]); }
		catch(Exception $e){ unset($img); }
	}
}
if($img) $repimg = "http://videogam.in/".$img->src['sm'];

$page->title = $naked_title." -- ";
if($post->category == "public") $page->title.= "Videogam.in Sblog";
elseif($post->category == "blog") $page->title.= $postuser->username."'s Sblog @ Videogam.in";
$page->fb = true;
$page->meta_title = $naked_title;
if($page->meta_description = $post->content['text_intro']){
	$page->meta_description = links($page->meta_description);
	$page->meta_description = strip_tags($page->meta_description);
	$page->meta_description = str_replace("\n", "", $page->meta_description);
	$page->meta_description = str_replace("\r", "", $page->meta_description);
	if(strlen($page->meta_description > 200)) $page->meta_description = substr($page->meta_description, 0, 195) . '...';
	$page->meta_description = htmlSC($page->meta_description);
} else $page->meta_description = $post->description;
$page->meta_data = '
	<link rel="canonical" href="http://videogam.in/sblog/'.$post->nid.'"/>
	<meta property="og:title" content="'.$naked_title.'"/>
	<meta property="og:type" content="article"/>
	<meta property="og:url" content="http://videogam.in/sblog/'.$post->nid.'"/>
	<meta property="og:image" content="'.$repimg.'"/>
	<meta property="og:site_name" content="Videogam.in"/>
	<meta property="og:description" content="'.$page->meta_description.'"/>
';

//tags
if($post->options["no_tagging"]) $allow_tag = FALSE;
elseif($_SESSION['user_rank'] >= 3) $allow_tag = TRUE;
if($_SESSION['user_rank'] >= 7) $allow_tag = TRUE;
if($usrid == $post->usrid) $allow_tag = TRUE;

$allowrm = FALSE;
if($_SESSION['user_rank'] > 4) $allowrm = TRUE; //Mods & above
if($_SESSION['user_rank'] < $postuser->rank) $allowrm = FALSE;
if($usrid == $postuser->id) $allowrm = TRUE;

$_tags = new tags("posts_tags:nid:".$post->nid);
$_tags->allow_add = $allow_tag;
$_tags->allow_rm = $allowrm;

if($keywords = $_tags->tagArr()){
	$page->meta_keywords = implode(",", $keywords);
	$page->meta_keywords = htmlSC($page->meta_keywords);
}

$page->css[] = "/posts/item.css";

$page->javascript.= '
<script>
	$(document).ready(function(){
		if($("#postform").length){
			$(["/bin/img/promo/thankheavens.png", "/bin/img/icons/toad_bow_big.gif"]).preload();
			$("#postform").fadeIn();
			$("#postdesc").focus();
		}
	});
	function confirmDesc(nid) {
		var pd = $("#postdesc").val();
		$.post(
			"/posts/ajax.php",
			{ _action:"confirmdesc", desc:pd, nid:nid },
			function(res){
				if(res) alert(res);
				else {
					$("#postform").fadeOut();
					$(".video-code").show();
					$("#tagstt").animate({opacity:1, top:"-43px"}, 600);
					$("#tagstt, #tags").click(function(){ $("#tagstt").fadeOut() });
				}
			}
		);
	}
</script>
';

$page->first_section = array(
	"id" => "posts-fullarticle",
	"class" => "posts white",
	"contain" => true
);

$page->header();

$months = array(
	'01' => 'January',
  '02' => 'February',
  '03' => 'March',
  '04' => 'April',
  '05' => 'May',
  '06' => 'June',
  '07' => 'July',
  '08' => 'August',
  '09' => 'September',
  '10' => 'October',
  '11' => 'November',
  '12' => 'December'
);

$_qs = array();

//traversing and lists related to this index
if($post->privacy == "public"){
	
	$prev_link = '<span class="article-trav arrow-left"></span>';
	$next_link = '<span class="article-trav arrow-right"></span>';
	
	if($post->category != "draft"){
		
		$morewhere = "";
		if($post->category == "blog"){
			$_qs['category'] = $post->category;
			$_qs['user'] = $postuser->username;
			$morewhere = "AND usrid = '".$post->usrid."' ";
			$rootdir = '<a href="/posts/handle.php?'.http_build_query($_qs).'" class="postsnavlink">'.$postedto.'</a>';
		} else {
			$rootdir = '<a href="/posts/handle.php" class="postsnavlink">Sblogs</a>';
		}
		
		$q = "SELECT nid, description, permalink, datetime FROM posts WHERE privacy = 'public' AND datetime < '".$post->datetime."' AND pending != 1 AND `archive`='".$post->archive."' AND `category` = '".$post->category."' $morewhere ORDER BY datetime DESC LIMIT 1";
		if($x = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
			$desc = (strlen($x['description']) > 100 ? substr($x['description'], 0, 99)."&hellip;" : $x['description']);
			$prev_link = '<a href="/sblog/'.$x['nid'].'/'.$x['permalink'].'" title="Previous article: '.htmlSC($x['description']).'" class="article-trav arrow-left tooltip"></a>';
		}
		$q = "SELECT nid, description, permalink, datetime FROM posts WHERE privacy = 'public' AND datetime > '".$post->datetime."' AND category = '$post->category' AND pending != 1 $morewhere ORDER BY datetime ASC LIMIT 1";
		if($x = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) {
			$desc = (strlen($x['description']) > 100 ? substr($x['description'], 0, 99)."&hellip;" : $x['description']);
			$next_link = '<a href="/sblog/'.$x['nid'].'/'.$x['permalink'].'" title="Next article: '.htmlSC($x['description']).'" class="article-trav arrow-right tooltip"></a>';
		}
	}
}

?>
<article id="nid-item-<?=$post->nid?>" class="post-item full" data-posttype="<?=$post->post_type?>">
	
	<?=($post->category == "draft" ? '<span class="notelabel tooltip" title="This article has yet to be published; It is viewable by direct link, but is not listed in any indexes.">DRAFT</span>' : '')?>
	<?=($post->privacy == "private" ? '<span class="notelabel tooltip" title="This article is viewable by direct link, but is not listed in any indexes.">PRIVATE</span>' : '')?>
	<?=($post->archive ? '<span class="notelabel tooltip" title="This article is only viewable on related (tagged) pages and is not viewable on indexes and post lists (such as the home page)">ARCHIVED</span>' : '')?>
	
	<?=$post->output("full")?>
	
	<div class="meta meta-side">
		<ul>
			<li class="posted">
				<time datetime="<?=$post->datetime?>" title="Posted <?=substr($post->datetime, 11, 5)?> <?=date("T").' GMT '.date("P")?>"><?=formatDate($post->datetime)?></time>
			</li>
			<li class="poster">
				Posted by <a href="<?=$postuser->url?>"><?=$postuser->avatar().$postuser->username?></a>
				<!-- to <a href="<?=$rootlink.http_build_query($rootlink_qs)?>"><?=ucfirst($post->post_type)?></a>-->
			</li>
			<li><span class="tooltip preventdefault" title="Use this URL for Tweets and sharing" style="color:#666;"><?=$tinyurl?></span></li>
			<?=($post->attachment == "audio" ? '<li>Embed Audio: <code class="tooltip preventdefault" title="Use this code to embed this audio file into Videogam.in forums and content pages" style="color:black;">{audio:'.str_replace("http://videogam.in/s", "", $tinyurl).'} <span class="helpinfo"></span></code></li>' : '')?>
		</ul>
		<?=($_SESSION['user_rank'] >= 8 || $post->usrid == $usrid ? '<div class="controls"><a href="/posts/manage.php?edit='.$post->nid.'" style="padding-right:14px; background:url(/bin/img/icons/edit.gif) no-repeat right top;">Edit this article</a> &nbsp;&nbsp;&nbsp; <a href="/posts/history.php?nid='.$post->nid.'">Article History</a></div>' : '')?>
	</div>
	
	<header>
		<nav>
			<?=$prev_link?> &nbsp; 
			<?=$next_link?>
		</nav><?
		if($post->category != "draft" && $post->privacy != "private"){
			?><div class="dir">
				<?=$rootdir?> / 
				<?
				$_qs2 = $_qs;
				$_qs2['date']['y'] = $y;
				?>
				<a href="/posts/handle.php?<?=http_build_query($_qs2)?>" class="postsnavlink"><?=$y?></a> / 
				<? $_qs2['date']['m'] = $m; ?>
				<a href="/posts/handle.php?<?=http_build_query($_qs2)?>" class="postsnavlink"><?=$months[$m]?></a> / 
				<? $_qs2['date']['d'] = $d; ?>
				<a href="/posts/handle.php?<?=http_build_query($_qs2)?>" class="postsnavlink"><?=$d?></a>
			</div>
			<?
		}
		?>
	</header>
	
	<div style="clear:both"></div>
	
	<?
	//poll
	$q = "SELECT * FROM posts_polls WHERE nid='$post->nid' LIMIT 1";
	if($poll = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))){
		
		if($_POST['pollopt']) {
			//record ballot
			if(!$usrid && !$_SERVER['REMOTE_ADDR']) $errors[] = "Couldn't record your vote since you're not registered or have no IP address";
			elseif(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM posts_polls_votes WHERE nid='$post->nid' AND (".($usrid ? "usrid='$usrid' OR " : "")."ip_address = '".$_SERVER['REMOTE_ADDR']."') LIMIT 1"))) $voted = TRUE;
			else {
				$voted = $_POST['pollopt'];
				$q = "INSERT INTO posts_polls_votes (nid, ip_address, usrid, answer) VALUES ";
				foreach($voted as $vote) {
					$q.= "('$post->nid', '".$_SERVER['REMOTE_ADDR']."', '$usrid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $vote)."'),";
				}
				if(!mysqli_query($GLOBALS['db']['link'], substr($q, 0, -1))) $errors[] = "Couldn't record your vote to the database";
			}
		}
		
		$query = "SELECT * FROM posts_polls_votes WHERE nid='$post->nid'";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		if($total_votes = mysqli_num_rows($res)) {
			$data   = array();
			$voted  = array();
			while($row = mysqli_fetch_assoc($res)) {
				$data[$row['answer']]++;
				if($row['usrid'] == $usrid || $row['ip_address'] == $_SERVER['REMOTE_ADDR']) $voted[] = $row['answer'];
			}
		}
		if(!count($voted)) $voted = FALSE;
		
		if($poll->answer_type == "single") $inptype = "radio";
		else $inptype = "checkbox";
		
		?>
		
		<div id="poll" class="<?=(!$voted ? 'hideres' : '')?>">
			<h4>Poll Question: <b><?=$poll->question?></b></h4>
			<form action="<?=$post->url?>#poll" method="post">
				<input type="hidden" name="nid" value="<?=$post->nid?>"/>
				<ol>
					<?
					$opts = array();
					$opts = explode("|--|", $poll->options);
					$i = 0;
					foreach($opts as $opt) {
						if($data[$i]) {
							$pc = ($data[$i] / $total_votes);
							$xpos = 813 * $pc + 23 . "px";
							$pc = $pc * 100;
							$pc = round($pc, 1);
							$thisdata = '<span class="data">'.$pc.'%</span><span class="data" style="font-weight:bold;">'.$data[$i].'</span>';
						} else {
							$pc = 0;
							$thisdata = "No Votes";
							$xpos = "23px";
						}
						?>
						<li>
							<span class="poll-data res"><?=$thisdata?></span>
							<span class="poll-bg res" style="width:<?=$pc?>%;"></span>
							<label>
								<?=(!$voted ? '<input type="'.$inptype.'" name="pollopt[]" value="'.$i.'"/> ' : '')?>
								<?=$opt?> &nbsp; 
								<?=($voted ? (in_array($i, $voted) ? '<span class="res yourvote">Your Vote</span>' : '') : '')?> &nbsp; 
							</label>
						</li>
						<?
						$i++;
					}
					?>
				</ol>
				<big style="float:right; margin-right:5px; color:#888;"><?=$total_votes?> Vote<?=($total_votes != 1 ? 's' : '')?></big>
				<?=(!$voted ? '<input type="submit" name="submit_poll" value="Vote"/> ' : '')?>
				<button type="button" onclick="$('#poll').toggleClass('hideres');">Toggle Results</button>
			</form>
		</div>
		<?
		
	}
	
	/*if($post->privacy == "public") {
		?>
		<h6>Recent posts to <a href="/<?=$rootlink?>"><?=$hdg?></a></h6>
		<?
		$query = "SELECT description, permalink, datetime, type FROM posts WHERE privacy = 'public' AND unpublished != 1 AND pending != 1 AND `archive`='".$post->archive."' $morewhere ORDER BY datetime DESC LIMIT 5";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		$rows  = array();
		while($row = mysqli_fetch_assoc($res)) {
			$rows[] = $row;
		}
		if(!count($rows)) {
			echo '<p>No recent posts</p>';
		} else {
			echo '<ul class="posts-shortlist">';
			foreach($rows as $row) {
				if($row['type'] == "gallery") $row['type'] = "image";
				$d = substr($row['datetime'], 0, 10);
				$d = str_replace("-", "/", $d);
				echo '<li style="background-image:url(/bin/img/icons/news/'.$row['type'].'_sm.png);"><a href="/'.$rootlink.'/'.$d.'/'.$row['permalink'].'">'.$row['description'].'</a></li>';
			}
			echo '</ul>';
		}
	} elseif(strstr($post->privacy, "group:")) {
			
		$groups = array();
		$groups = explode(",", $post->groups);
		foreach($groups as $group_id) {
			$group_id = trim($group_id);
			
			$q = "SELECT * FROM groups WHERE group_id='$group_id' LIMIT 1";
			$gdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
			
			//member?
			$q = "SELECT * FROM groups_members WHERE usrid='$usrid' AND group_id='$group_id' LIMIT 1";
			$member = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q));
			if($gdat->status == "invite" && !$member) {
				//don't show it
			} else {
			
				$q = "SELECT description, permalink, datetime FROM posts WHERE blog='1' AND groups LIKE '% $group_id %' AND datetime < '".$post->datetime."' ORDER BY datetime DESC LIMIT 1";
				if($x = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) {
					$d = substr($x['datetime'], 0, 10);
					$d = str_replace("-", "/", $d);
					$prev_link = '<a href="/posts/'.$d.'/'.$x['permalink'].'" class="arrow-left"><div>'.$x['description'].'</div></a>';
				} else $prev_link = '<span class="arrow-left"></span>';
				$q = "SELECT description, permalink, datetime FROM posts WHERE blog='1' AND groups LIKE '% $group_id %' AND datetime > '".$post->datetime."' ORDER BY datetime DESC LIMIT 1";
				if($x = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) {
					$d = substr($x['datetime'], 0, 10);
					$d = str_replace("-", "/", $d);
					$next_link = '<a href="/posts/'.$d.'/'.$x['permalink'].'" class="arrow-right"><div>'.$x['description'].'</div></a>';
				} else $next_link = '<span class="arrow-right"></span>';
				$uid = outputUser($post->usrid, FALSE, FALSE);
				?>
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<th colspan="2"><a href=""><?=$gdat->name?></a></th>
						</tr>
						<tr>
							<td class="prev"><?=$prev_link?></td>
							<td class="next"><?=$next_link?></td>
						</tr>
					</table>
				<?
				
			}
		}
	}*/
	
	?>
	
	<footer>
		
		<div class="hrate">
			<?=$post->outputHeartRating()?>
		</div>
		
		<div id="raters">
			<?
			$query = "SELECT DISTINCT(usrid), avatar, username FROM `posts_ratings` LEFT JOIN users USING(usrid) WHERE posts_ratings.nid='$nid' AND posts_ratings.rating='1'";
			$res = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)){
				if($row['usrid'] == $post->usrid) continue;// Dont show author's self-love
				if(!$row['avatar']) $row['avatar'] = "unknown.png";
				echo '<a href="~'.$row['username'].'" title="'.$row['username'].' loves this" class="tooltip"><img src="/bin/img/avatars/icon/'.$row['avatar'].'" alt="'.$row['username'].'"/></a>';
			}
			?>
		</div>
		
		<?=($usrid == $post->usrid && $sincepub < 20 ? '<div id="tagstt" class="tooltip-bubble above" style="display:block; opacity:0; top:-23px; left:279px; width:134px; cursor:pointer;">Add tags to help categorize this post and give it more exposure</div>' : '')?>
		
		<div id="tags" class="tags taglist">
			<?
			if($usrid == $post->usrid && $sincemod < 20){
				$_tags->allow_add = TRUE;
				$_tags->allow_rm = TRUE;
				$_tags->suggest($post->content['text']);
				if(!$_tags->tagarr()) echo '<script type="text/javascript">var confirm_exit=true; var confirm_exit_msg="Without tags your post has no place to go. Abandon your poor, orphan, homeless, tagless post?";</script>';
			}
			
			echo $_tags->taglist();
			echo $_tags->suggestForm();
			?>
			<div class="clear" style="height:0;"></div>
		</div>
		
		<div class="fblike"><fb:like href="http://videogam.in/posts/handle.php?nid=<?=$post->nid?>" layout="standard" show_faces="true" font="arial" width="620"></fb:like></div>
		
	</footer>
</article>
<?

$page->closeSection();
$page->openSection();

//forum
$forum = new forum;
$forum->unique_location = 'post:'.$post->nid;
$forum->suggest['fid'] = "8";
$forum->suggest['type'] = "comments";
$forum->suggest['title'] = $naked_title;
$forum->suggest['tags'] = $_tags->list;
if(!$post->options["comments_disabled"] || $forum->numberOfPosts()) {
	$forum->showTopic();
}

if($usrid == $post->usrid && $sincepub < 20){
	
	$numposts = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM posts WHERE usrid='$usrid' AND category != 'draft'"));
	function ordinal_suffix($value, $sup = 0){
	// Function written by Marcus L. Griswold (vujsa)
	// Can be found at http://www.handyphp.com
	// Do not remove this header!
	    is_numeric($value) or trigger_error("<b>\"$value\"</b> is not a number!, The value must be a number in the function <b>ordinal_suffix()</b>", E_USER_ERROR);
	    if(substr($value, -2, 2) == 11 || substr($value, -2, 2) == 12 || substr($value, -2, 2) == 13){
	        $suffix = "th";
	    }
	    else if (substr($value, -1, 1) == 1){
	        $suffix = "st";
	    }
	    else if (substr($value, -1, 1) == 2){
	        $suffix = "nd";
	    }
	    else if (substr($value, -1, 1) == 3){
	        $suffix = "rd";
	    }
	    else {
	        $suffix = "th";
	    }
	    if($sup){
	        $suffix = "<sup>" . $suffix . "</sup>";
	    }
	    return $value . $suffix;
	}
	
	?>
	<div id="postform" class="alert" style="width:496px; height:496px; max-height:100%; top:50%; left:50%; margin:-248px 0 0 -248px; border-width:0; background:url(/bin/img/promo/thankheavens.png) no-repeat 0 0 black;">
		<div style="position:absolute; top:54px; right:68px; bottom:253px; left:84px; overflow:hidden; padding:10px; font:bold 15px Arial; line-height:24px;">Oh, thank heavens! I'm back to my old self again. Thank you so much, <?=$usrname?>, for posting your <?=ordinal_suffix($numposts, 1)?> Sblog to the Videogam.in community. Here is a letter from the princess.</div>
		<div style="position:absolute; top:224px; left:293px; width:32px; height:50px;"><img src="/bin/img/icons/toad_bow_big.gif" width="32" height="50"/></div>
		<b style="position:absolute; top:340px; right:20px; left:20px; font-size:15px; color:white;">Confirm post description <a class="preventdefault tooltip helpinfo" title="A short description of the post that will go on post lists, RSS feeds, etc."><span>?</span></a></b>
		<div style="position:absolute; top:374px; left:16px; right:16px; padding:10px;">
			<textarea name="postdesc" id="postdesc" style="width:100%; height:55px; border-width:0; margin:0; padding:0; background:transparent;"><?=$post->description?></textarea>
		</div>
		<div style="position:absolute; top:439px; left:25px;">
			<input type="button" value="Confirm" onclick="$(this).attr('disabled','disabled').val('Confirming...'); confirmDesc('<?=$post->nid?>');" style="border:2px solid black !important; border-radius:0; box-shadow:none; font-weight:bold; color:black;"/>
		</div>
	</div>
	<?
}

$page->footer();
?>
