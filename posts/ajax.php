<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.posts.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.ajax.php";

if($_POST['_action'] == "confirmdesc"){
	
	if(!$nid = $_POST['nid']) die("no id given");
	$desc = trim($_POST['desc']);
	$desc = strip_tags($desc);
	if($desc == "") die("no description given");
	
	$q = "SELECT * FROM posts WHERE nid='".mysqli_real_escape_string($GLOBALS['db']['link'], $nid)."' limit 1";
	if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Couldn't find post for nid $nid");
	if($usrid != $dat->usrid && $usrrank < 7) die("permission denied");
	
	$q = "UPDATE posts SET `description` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $desc)."' WHERE nid='$nid' LIMIT 1";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Error updating database");
	
	exit;
	
}

if($_POST['_action'] == "penote"){
	
	$pe = $_POST['pe'];
	if($pe['comment']) {
		$q = "UPDATE posts_edits SET `comments` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $pe['comment'])."' WHERE `id`='".mysqli_real_escape_string($GLOBALS['db']['link'], $pe['id'])."' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Error: Moderation comment not recorded because of a database error");
	}
	if($pe['mail']) {
		$q = "SELECT email, users.username, description, nid FROM posts_edits pe LEFT JOIN posts USING (nid) LEFT JOIN users ON(pe.usrid = users.usrid) WHERE pe.`id`='".mysqli_real_escape_string($GLOBALS['db']['link'], $pe['id'])."' LIMIT 1";
		if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Couldn't get data");
		$to      = $dat->email;
		$subject = 'Your Videogam.in post has been moderated';
		$message = $dat->username.",\nYour Videogam.in post, \"".$dat->description.",\" has been edited by $usrname.".($pe['comment'] ? "\n\nComments:\n".$pe['comment'] : "")."\n\nSee your edited post here -> http://videogam.in/posts/?id=".$dat->nid."\n\nSincerely,\nThe Friendly Videogam.in Post Edit Notfying Robot\n";
		$headers = 'From: noreply@videogam.in' . "\r\n" .
		    'X-Mailer: PHP/' . phpversion();
		mail($to, $subject, $message, $headers);
	}
	exit;
	
}

//load album tracks
if($_POST['_action'] == "loadtracks" && $albumid = $_POST['_albumid']) {
	$query = "SELECT id, disc, track_name, track_number, `time` FROM albums_tracks WHERE albumid='$albumid' ORDER BY disc, track_number";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	$cdisc = "";
	while($row = mysqli_fetch_assoc($res)) {
		if($cdisc != $row['disc']) {
			echo ($cdisc != "" ? '</optgroup>' : '').'<optgroup label="'.htmlSC($row['disc']).'">';
			$cdisc = $row['disc'];
		}
		if(strlen($row['track_name']) > 65) $row['track_name'] = substr($row['track_name'], 0, 58) . "&hellip;" . substr($row['track_name'], -6);
		echo '<option value="'.$row['id'].'">'.$row['track_number'].'. '.$row['track_name'].($row['time'] ? ' ['.$row['time'].']' : '').'</option>';
	}
	echo '</optgroup>';
	exit;
}

//load albums
if($_POST['_action'] == "loadalbums") {
	echo '<option value="">Select album&hellip;</option>';
	$query = "SELECT albumid, cid, title, subtitle FROM albums WHERE `view` = '1' ORDER BY title";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		//check and see if there's corresponding tracks first
		$q = "SELECT * FROM albums_tracks WHERE albumid = '".$row['albumid']."' LIMIT 1";
		if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) echo '<option value="'.$row['albumid'].'">'.$row['title'].' '.$row['subtitle'].' ('.$row['cid'].')</option>';
	}
}

if(isset($_POST['load_postslist'])){
	
	$a = new ajax();
	
	$posts = new posts();
	
	parse_str($_POST['load_postslist'], $posts->query_params);
	
	$posts->parseParams();
	$posts->buildQuery();
	
	$ret['formatted_aside_legend'] = $posts->nav_with_legend;
	$ret['formatted_aside_legend'] = str_replace('<aside>', '', $ret['formatted_aside_legend']);
	$ret['formatted_aside_legend'] = str_replace('</aside>', '', $ret['formatted_aside_legend']);
	$ret['formatted_aside'] = $posts->nav;
	$ret['formatted_aside'] = str_replace('<aside>', '', $ret['formatted_aside']);
	$ret['formatted_aside'] = str_replace('</aside>', '', $ret['formatted_aside']);
	$ret['formatted'] = $posts->postsList();
	//$ret['formatted'] = str_replace('<div class="postlist">', '', $ret['formatted']);
	//$ret['formatted'] = substr($ret['formatted'], 0, -6);//</div>
	
	$a->ret = $ret;
	exit;
	
}

//heart rating
if(isset($_POST['set_rating'])){
	
	$a = new ajax();
	
	$ret = array();
	$ret['title'] = "";
	$ret['outp'] = "";
	
	if(!$usrid) $a->kill("No user session registered");
	if(!$nid = $_POST['nid']) $a->kill("Error: no post id given");
	$r = $_POST['set_rating'];
	if($r != '0' && $r != '1') $a->kill("Error: invalid rating given");
	
	try{ $post = new post($nid); }
	catch(Exception $e){ $a->kill($e->getMessage()); }
	
	//Can't rate own
	//if($usrid == $post->dat['usrid'] && $usrrank < 9) $ret['error'] = "Nice try, but you can't rate your own post";
	
	//rm previous rating (if there is one)
	mysqli_query($GLOBALS['db']['link'], "DELETE FROM posts_ratings WHERE usrid='$usrid' AND nid='".mysqli_real_escape_string($GLOBALS['db']['link'], $nid)."';");
	
	$q = "INSERT INTO posts_ratings (usrid, nid, rating) VALUES ('$usrid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $nid)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $r)."');";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) $ret['error'] = "There was a database error and the rating couldn't be recorded.";
	else {
		$q = "SELECT AVG(`rating`) AS `avgrating`, COUNT(*) AS `countrating` FROM posts_ratings WHERE nid = '".mysqli_real_escape_string($GLOBALS['db']['link'], $nid)."';";
		$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
		$row['avgrating'] = ceil($row['avgrating'] * 100);
		
		$weighted = (($row['countrating'] < 4 ? ($row['countrating'] / 3) : 1) * ($row['avgrating'] - 50)) * ($row['countrating'] * .05);
		if($weighted > 0) $weighted = $weighted * ($row['avgrating'] / 100);
		$weighted = round($weighted);
		
		if($weighted >= 25){
			if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM badges_earned WHERE usrid = '".$post->dat['usrid']."' AND bid = '48' LIMIT 1"))){
				//badge!
				require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.badges.php");
				$_badges = new badges;
				$_badges->earn(48, $post->dat['usrid']);
			}
		}
		
		$q = "UPDATE posts SET `rating` = '".$row['avgrating']."', `ratings` = '".$row['countrating']."', rating_weighted = '$weighted' WHERE nid='".mysqli_real_escape_string($GLOBALS['db']['link'], $nid)."' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $ret['error'] = "Your rating has been recorded but there was an error updating posts database table";
		
		if($weighted != 0){
			$q = "SELECT SUM(rating_weighted) AS total_weighted FROM posts WHERE usrid='".$post->dat['usrid']."'";
			$row2 = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
			$q = "UPDATE users SET sblog_rating = '".$row2['total_weighted']."' WHERE usrid='".$post->dat['usrid']."' LIMIT 1";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) $ret['error'] = "There was a database error updating sblog_rating for user ID # ".$post->dat['usrid'];
		}
		
		$ret['title'] = "Post quality is ".$row['avgrating']."% based on ".$row['countrating']." rating".($row['countrating'] != 1 ? 's' : '')." [$weighted]";
		$ret['outp'] = $post->heartRating($weighted);
	}
	
	$a->ret = $ret;
	exit;
	
}

/* Load a video embed code (old method)
if(isset($_POST['load_video'])){
	
	$a = new ajax();
	
	if(!$nid = $_POST['load_video']) $a->kill("No NID given");
	
	$q = "SELECT * FROM posts WHERE nid = '".mysqli_real_escape_string($GLOBALS['db']['link'], $nid)."' LIMIT 1";
	if(!$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) $a->kill("Couldn't get data for NID $nid");
	
	$p = new post($row);
	$a->ret['formatted'] = $p->content['video_code'];
	
	exit();
	
}*/

if(isset($_POST['load_share'])){
	
	// FB Like button for a Sblog post
	// needs to be enclosed in an iframe!!!!!!!!!
	
	$nid = $_POST['load_share'];
	if(!$nid) exit;
	
	?>
	<b style="display:block; margin:5px 0 -3px 10px; font-weight:normal; color:#666;"><span title="short URL for status updates, etc.">http://videogam.in/s</span><?=$nid?></b>
	<iframe src="/bin/php/share.php?url=http://videogam.in/posts/handle.php?nid=<?=$nid?>&tinyurl=http://videogam.in/s<?=$nid?>&desc=<?=urlencode($_POST['desc'])?>" frameborder="0">Loading...</iframe>
	<?
	
	exit;
	
	require("class.post.php");
	$_p = new post($nid);
	$_p->cont = $_p->splitData();
	
	$img = ($_p->data['img'] ? '/posts/img/'.$_p->data['img'] : '');
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml"
	      xmlns:og="http://ogp.me/ns#"
	      xmlns:fb="http://www.facebook.com/2008/fbml">
	  <head>
	    <title><?=htmlSC($_POST['title'])?></title>
	    <meta property="og:title" content="<?=htmlSC($_POST['title'])?>"/>
	    <meta property="og:type" content="article"/>
	    <meta property="og:url" content="<?=$_POST['url']?>"/>
	    <meta property="og:image" content="<?=$_POST['img']?>"/>
	    <meta property="og:site_name" content="Videogam.in"/>
	    <meta property="fb:admins" content="USER_ID"/>
	    <meta property="og:description" content="<?=htmlSC($_POST['description'])?>"/>
	  </head>
	  <body style="margin:0; padding:0; background-color:transparent;"></body>
	</html>
	<?
	
	exit;
	
}