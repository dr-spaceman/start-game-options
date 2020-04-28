<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.forums.php");
$forum = new forum();
require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.badges.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.ajax.php");

$do = $_GET['do'];
$fid = $_GET['fid'];
$tid = $_GET['tid'];
$in = $_POST['in'];

$html_head = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>';

if($pid = $_POST['load_message']){
	$q = "SELECT * FROM forums_posts WHERE pid='$pid' LIMIT 1";
	if(!$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) die("Couldn't fetch message #$pid");
	$forum->outputPost($row, FALSE, TRUE);
	exit;
}

if($_POST['do'] == "Post Topic") {
	
	///////////////
	// NEW TOPIC //
	///////////////
	
	if(!$usrid) die("You must be a registered user to post");
	
	$fid = $_POST['fid'];
	$type = trim($_POST['type']);
	if(!$type || $type != "comments") $type = "forum";
	
	$title = trim($_POST['title']);
	$bb = new bbcode($title);
	$title = $bb->bb2html();
	$title = strip_tags($title, "<i><em><del><ins>");
	
	$loc = urldecode($_POST['location']);
	$loc = trim($loc);
	
	if(!$message = trim($_POST['message'])) die("Error: no message to post");
	$message = parseText($message);
	if($_POST['disable_emoticons']) $message.= '<!--disable_emoticons-->';
	
	$description = parseText($_POST['description']);
	$datetime = date('Y-m-d H:i:s');
	
	//inherit invisible & close values of the forum (fid) it's going into
	$q = "SELECT invisible, closed FROM forums WHERE fid='".mysqli_real_escape_string($GLOBALS['db']['link'], $fid)."' LIMIT 1";
	$fdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
	
	//if unique location, make sure there isn't a topic already
	if($_POST['location_unique']){
		$q = "SELECT * FROM forums_topics WHERE `location` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $loc)."' LIMIT 1";
		if($topic = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))){
			$tid = $topic->tid;
		}
	}
	
	if(!$tid){
		$tid = mysqlNextAutoIncrement("forums_topics");
		$query = sprintf("INSERT INTO `forums_topics` (`fid`,`type`,`title`,`description`,`location`,`usrid`,`created`,`posts`,`last_post`,`last_post_usrid`,`invisible`,`closed`) VALUES 
			('%s','$type','%s','%s','%s','$usrid','$datetime','1','$datetime','$usrid','".$fdat->invisible."','".$fdat->closed."')",
			mysqli_real_escape_string($GLOBALS['db']['link'], $fid),
			mysqli_real_escape_string($GLOBALS['db']['link'], $title),
			mysqli_real_escape_string($GLOBALS['db']['link'], $description),
			mysqli_real_escape_string($GLOBALS['db']['link'], $loc));
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		if(!$res) die("Couldn't insert into forums topics table; ".mysqli_error($GLOBALS['db']['link']));
	}
	
	$pid = mysqlNextAutoIncrement("forums_posts");
	$query = sprintf("INSERT INTO forums_posts (tid, usrid, posted, message, ip) VALUES 
		('$tid','$usrid','$datetime','%s','".$_SERVER['REMOTE_ADDR']."')",
		mysqli_real_escape_string($GLOBALS['db']['link'], $message));
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(!$res) {
		die("Couldn't post into forums posts table");
	} else {
		
		//autotag [[Tag:foo]]
		if($etags = extractTags($message)){
			$q = "";
			foreach($etags as $t){
				if($t['namespace'] == "Tag") $q.= "('$tid', '$pid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $t['tag'])."', '$usrid'),";
			}
			if($q) mysqli_query($GLOBALS['db']['link'], "INSERT INTO forums_tags (tid, pid, tag, usrid) VALUES ".substr($q, 0, -1));
		}
		if($_POST['tags']){
			$q = "";
			foreach($_POST['tags'] as $tag) $q.= "('$tid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $tag)."', '$usrid'),";
			if($q) mysqli_query($GLOBALS['db']['link'], "INSERT INTO forums_tags (tid, tag, usrid) VALUES ".substr($q, 0, -1));
		}
		
		$redirect_to = $forum->topicURL($tid);
		
		if($s = $_POST['subscribe']) {
			if($s['pid']) $s['pid'] = $pid;
			if($s['tid']) { $s['tid'] = $tid; $s['pid'] = ""; }
			$forum->subscription($s, FALSE);
		}
		
		//poll
		$poll = $_POST['poll'];
		if($poll['question'] && $poll['opts']) {
			$poll['question'] = strip_tags($poll['question'], '<i><b><a>');
			$poll['opts'] = strip_tags($poll['opts'], '<i><b><a>');
			$opts = array();
			$opts = explode("\r\n", $poll['opts']);
			$p_opts = array();
			for($i = 0; $i < count($opts); $i++) {
				$opts[$i] = trim($opts[$i]);
				if($opts[$i] != "" && count($p_opts) <= 11) $p_opts[] = $opts[$i];
			}
			$p_opts = implode("|--|", $p_opts);
			
			$q = "INSERT INTO forums_polls(tid, question, options, answer_type) VALUES 
				('$tid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $poll['question'])."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $p_opts)."', '".$poll['answer']."');";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) die('Your topic has been posted but there was an error adding the poll to the database ('.mysqli_error($GLOBALS['db']['link']).'). <a href="'.$redirect_to.'">Go to topic</a>');
		}
		
		$subs = "";
		if($fid) $subs['fid'] = $fid;
		if($loc) $subs['location'] = $loc;
		if(!$_POST['dont_send_mail']) sendSubscription($subs, "/forums/?tid=".$tid, $title, $message);
		
		header("Location: ".$redirect_to);
		exit;
	}
	
} elseif($do == "edit_topic_details") {
	
	///////////////////
	// TOPIC DETAILS //
	///////////////////
	
	if(!$tid = $_GET['tid']) die("Error: no topic ID given");
	
	if($_POST['submit']) {
		
		$q = "UPDATE forums_topics SET 
			`type` = '".$in['ftype']."',
			`title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['title'])."',
			`description` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['description'])."',
			`fid` = '".$in['fid']."'
			WHERE tid='$tid' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) {
			die("Error: Couldn't update topic details");
		}
		
		header("Location: /forums/?tid=$tid");
		exit;
		
	}
	
	$q = "SELECT * FROM forums_topics WHERE tid='$tid' LIMIT 1";
	if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Couldn't get topic data for tid # $tid");
	
	if($usrrank < 5) die("No access");
	
	$dat->title = stripslashes($dat->title);
	$dat->description = stripslashes($dat->description);
	
	echo $html_head;
	?><title>Edit topic details</title>
	</head>
	<body>
		<form action="action.php?do=edit_topic_details&tid=<?=$tid?>" method="post">
			<fieldset>
				<legend>Editing Topic ID #<a href="/forums/?tid=<?=$tid?>"><?=$tid?></a></legend>
				<dl>
					
					<dt>Type:</dt>
					<dd><select name="in[ftype]">
						<option value="forum"<?=($dat->type == "forum" ? ' selected' : '')?>>forum</option>
						<option value="comments"<?=($dat->type == "comments" ? ' selected' : '')?>>comments</option>
					</select></dd>
					
					<dt>Forum:</dt>
					<dd>
						<select name="in[fid]">
							<?
							$query = "SELECT * FROM forums_categories ORDER BY sort";
							$res   = mysqli_query($GLOBALS['db']['link'], $query);
							while($row = mysqli_fetch_assoc($res)){
								echo '<optgroup label="'.htmlSC($row['category']).'">';
								$query2 = "SELECT * FROM forums WHERE cid='$row[cid]'";
								$res2   = mysqli_query($GLOBALS['db']['link'], $query2);
								while($row2 = mysqli_fetch_assoc($res2)){
									echo '<option value="'.$row2['fid'].'"'.($row2['fid'] == $dat->fid ? ' selected="selected"' : '').'>'.$row2['title'].'</option>';
								}
								echo '</optgroup>';
							}
							?>
						</select>
					</dd>
					
					<dt>Title:</dt>
					<dd><input type="text" name="in[title]" value="<?=htmlSC($dat->title)?>"></dd>
					
					<dt>Description:</dt>
					<dd><textarea name="in[description]"><?=($dat->description)?></textarea></dd>
					
				</dl>
				
				<input type="submit" name="submit" value="Submit Details"/>
			</fieldset>
		</form>
	</body>
	</html>
	<?
	
	exit;

} elseif($_GET['do'] == "delete") {
	
	////////////
	// DELETE //
	////////////
	
	$tid = $_GET[tid];
	$fid = $_GET[fid];
	if(!$tid && !$fid) {
		die("Error: no id given");
	}
	
	if($usrrank < 8) die("You can't do that since you aren't an admin.");
	
	if($tid) {
	
		if(!$_GET['sure']) {
			?><a href="?do=delete&tid=<?=$tid?>&sure=1">Yes, permanently delete this topic and all posts herein</a><?
		} else {
			
			$q = "SELECT * FROM forums_topics WHERE tid='".mysqli_real_escape_string($GLOBALS['db']['link'], $tid)."' LIMIT 1";
			if(!$topic = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Error: Couldn't find topic ID # $tid");
			
			$user = getUserDat($topic->usrid);
			if($user->rank > $usrrank) die("Can't remove topic since your rank is lower than the topic starter's");
			
			$q = "DELETE FROM `forums_posts` WHERE tid = '$tid'";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) die("couldn't delete posts");
			
			$q = "DELETE FROM `forums_topics` WHERE tid = '$tid' LIMIT 1";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) die("couldn't delete topic");
			
			$q = "DELETE FROM `forums_tags` WHERE tid = '$tid'";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) echo("Non-fatal Error: couldn't delete tags.<br/>");
			
			$q = "DELETE FROM `forums_mail` WHERE tid = '$tid'";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) echo("Non-fatal Error: couldn't delete subscriptions.<br/>");
			
			$q = "DELETE FROM `forums_ratings` WHERE tid = '$tid'";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) Echo("Non-fatal Error: couldn't delete ratings.");
			
			$forum->updatePosts($tid);
			
			header("Location:/forums/");
			exit;
			
		}
		
	} elseif($fid) {
		
		if(!$_GET['sure']) {
			?>
			<a href="?do=delete&fid=<?=$fid?>&sure=1">Yes, permanently delete this forum</a><br/>
			Note: topics and posts within will not be deleted, only the forum shell.
			<?
		} else {
			
			$q = "DELETE FROM `forums` WHERE fid = '$fid' LIMIT 1";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) die("couldn't delete forum");
			
			header("Location:/forums");
			
		}
	}

} elseif($_POST['_do'] == "post_reply") {
	
	///////////
	// REPLY //
	///////////
	
	$a = new ajax();
	
	parse_str($_POST['_in'], $post);
	
	$tid = $post['tid'];
	if(!$tid) $a->kill("no topic id given");
	if(!$usrid) $a->kill("You must be a registered user to post");
	if(!$message = parseText($post['message'])) $a->kill = "no message input";
	
	//check to see when user's last post was
	/*$query = "SELECT (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`posted`)) as seconds_since_post FROM `forums_posts` WHERE `poster` = '$usrid' ORDER BY `posted` DESC LIMIT 1";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if($dat = mysqli_fetch_object($res)) {
		if($dat->seconds_since_post < $max_seconds_between_posts)
			die('||||||Cannot post since it\'s been too soon since your last post. Try again in a few seconds.');
	}*/
	
	$datetime = date('Y-m-d H:i:s');
	
	//If there's a reply to, make sure it's a parent thread
	if($post['reply_to']){
		$q = "SELECT reply_to FROM forums_posts WHERE pid = '".mysqli_real_escape_string($GLOBALS['db']['link'], $post['reply_to'])."' LIMIT 1";
		if($parent_row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) $post['reply_to'] = $parent_row['reply_to'] ? $parent_row['reply_to'] : $post['reply_to'];
	}
	
	$pid = mysqlNextAutoIncrement("forums_posts");
	$q = "INSERT INTO forums_posts (tid, usrid, posted, message, ip, reply_to) VALUES 
		('$tid', '$usrid', '$datetime', '".mysqli_real_escape_string($GLOBALS['db']['link'], $message)."', '".$_SERVER['REMOTE_ADDR']."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $post['reply_to'])."')";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) $a->kill('Couldn\'t post this message because of a database error [IFP03]');
		
	$a->ret['pid'] = $pid;
	
	$forum->updatePosts($tid);
	
	//autotag [[Tag:foo]]
	if($tags = extractTags($message)){
		
		$ctags = array();
		$query = "SELECT * FROM forums_tags WHERE tid='$tid'";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)){
			$ctags[] = $row['tag'];
		}
		
		$q = "";
		foreach($tags as $t){
			if($t['namespace'] == "Tag" && !in_array($t['tag'], $ctags)) $q.= "('$tid', '$pid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $t['tag'])."', '$usrid'),";
		}
		if($q) mysqli_query($GLOBALS['db']['link'], "INSERT INTO forums_tags (tid, pid, tag, usrid) VALUES ".substr($q, 0, -1));
		
	}
	
	$q = "SELECT * FROM forums_topics WHERE tid='$tid' LIMIT 1";
	$topic = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
	
	$subs = array();
	$subs['tid'] = $tid;
	if($post['reply_to']) $subs['pid'] = $post['reply_to'];
	if(!$post['dont_send_mail']) sendSubscription($subs, "/forums/?tid=".$tid, $topic->title, $message);
	
	//add reply mail
	if($post['subscribe']) {
		$subscribe = array();
		if($post['subscribe']['pid']) $subscribe['pid'] = ($post['reply_to'] ? $post['reply_to'] : $pid);
		if($post['subscribe']['tid']) { $subscribe['tid'] = $tid; $subscribe['pid'] = ""; }
		if(count($subscribe)){
			if(!$forum->subscription($subscribe, FALSE)) $a->ret['errors'][] = "There was an error setting up your subscription to this topic.";
		}
	}
	
	$_badges = new badges;
	
	//Necromancy
	$lastpost = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], "SELECT posted FROM forums_posts WHERE tid='$tid' ORDER BY posted DESC LIMIT 1"));
	$lastpost = strtotime($lastpost->posted);
	if((time() - $lastpost) > 15552000){ //6 months
		$_badges->earn(49);
	}
	
	//Fairy Companion
	$q = "SELECT * FROM forums_posts WHERE tid='$tid' AND usrid = '$usrid'";
	if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q)) >= 20) $_badges->earn(53);
	
	exit;

} elseif($pid = $_POST['load_post']){
	
	/////////////
	//load post//
	/////////////
	
	$q = "SELECT * FROM forums_posts WHERE pid='".mysqli_real_escape_string($GLOBALS['db']['link'], $pid)."' LIMIT 1";
	$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
	$forum->outputPost($row, FALSE, TRUE);
	
	exit;

} elseif($_POST['_do'] == "load_reply_form"){
	
	// LOAD REPLY FORM //
	
	if(!$i = $_POST['i']) $i = rand(1,99999);
	$forum->outputReplyForm($i, FALSE, $_POST['textinp']);
	exit;

} elseif($pid = $_POST['load_edit_form']) {
	
	// load edit form //
	
	$q = "SELECT * FROM forums_posts WHERE pid='".mysqli_real_escape_string($GLOBALS['db']['link'], $pid)."' LIMIT 1";
	if(!$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) {
		die("Couldn't load edit form for post # $pid");
	} else {
		if(strstr($row['message'], '<!--disable_emoticons-->')) {
			$disemote = TRUE;
			$row['message'] = str_replace("<!--disable_emoticons-->", "", $row['message']);
		}
		?>
		<form>
			<input type="hidden" name="pid" value="<?=$pid?>"/>
			<div class="inpfw">
				<textarea name="message" id="edit-<?=$pid?>-text" rows="<?=ceil(strlen($row['message']) * .05)?>" style="max-height:300px;"><?=$row['message']?></textarea>
			</div>
			<div class="spacer" style="height:10px"></div>
			<div class="opts" style="float:left;"><?=($usrrank >= 5 ? '<label><input type="checkbox" name="" id="edit-'.$pid.'-clearedit" value="1"/> don\'t mark post as edited</label>' : '')?></div>
			<div class="buttons" style="text-align:right; margin-left:50%;">
				<button type="submit" class="submit">Submit Changes</button>
				<button type="reset" class="cancel">Cancel</button>
			</div>
		</form>
		<?
	}
	
	exit;
	
} elseif($_POST['_do'] == "edit_post") {
	
	// EDIT POST
	
	$a = new ajax();
	
	parse_str($_POST['_in'], $post);
	
	if(!$pid = $post['pid']) $a->kill("Error: no post id given");
	
	$q = "SELECT * FROM forums_posts WHERE pid='".mysqli_real_escape_string($GLOBALS['db']['link'], $pid)."' LIMIT 1";
	if(!$pdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) $a->kill("Error: Couldn't get post data for pid # $pid");
	
	if($usrrank < 5 && $pdat->usrid != $usrid) $a->kill("You don't have permission to edit this post.");
	
	$datetime = date('Y-m-d H:i:s');
	
	if(!$message = parseText($post['message'])) $a->kill("Error: No message received");
	
	$ed['editor'] = $usrname;
	
	$query = "UPDATE forums_posts SET `editor`='".(!$post['clearedit'] ? $ed['editor'] : '')."', `message`='".mysqli_real_escape_string($GLOBALS['db']['link'], $message)."' WHERE pid = '$pid' LIMIT 1";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(!$res) {
		$a->kill("Error: couldn't update posts table");
	} else {
		$a->ret['success'] = true;
		$a->ret['pid'] = $pid;
	}
	
	exit;
	
} elseif($pid = $_POST['delete_post']) {
	
	/////////////////
	// DELETE POST //
	/////////////////
	
	$ret = array();
	
	if($usrrank < 5) $ret['error'] = "You can't do that since you aren't a moderator.";
	
	//check to make sure it's not the beginning of a threads
	$q = "SELECT * FROM forums_posts WHERE reply_to='$pid' LIMIT 1";
	if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $ret['error'] = "Can't delete that post since it's the start of a thread. All replies must be removed first.";
	else {
		$q = "SELECT * FROM forums_posts WHERE pid='$pid' LIMIT 1";
		$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
		
		$query = "DELETE FROM `forums_posts` WHERE `pid` = '$pid' LIMIT 1";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		if(!$res) $ret['error'] = "couldn't delete the post because of a db error";
		
		$forum->updatePosts($row['tid']);
	}
	
	die(json_encode($ret));

} elseif($_GET['do'] == "sticky") {
	
	////////////
	// STICKY //
	////////////
	
	if(!$tid = $_GET[tid]) die("Error: no topic id given");
	
	if($usrrank < 5) die("You can't do that since you aren't a moderator.");
	
	$query = "UPDATE `forums_topics` SET `sticky` = 1 WHERE `tid` = '$tid' LIMIT 1";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(!$res) {
		die("Error: couldn't set sticky");
	} else {
		header("Location: /forums/?tid=$tid");
		exit;
	}
	
} elseif($_GET['do'] == "unsticky") {
	
	//////////////
	// UNSTICKY //
	//////////////
	
	if(!$tid = $_GET[tid]) die("Error: no topic id given");
	
	if($usrrank < 5) die("You can't do that since you aren't a moderator.");
	
	$query = "UPDATE `forums_topics` SET `sticky` = 0 WHERE `tid` = '$tid' LIMIT 1";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(!$res) {
		die("Error: couldn't set sticky");
	} else {
		header("Location: /forums/?tid=$tid");
		exit;
	}


} elseif($_GET['do'] == "hide") {
	
	//////////
	// HIDE //
	//////////
	
	$fid = $_GET[fid];
	$tid = $_GET[tid];
	if(!$fid && !$tid) {
		die("Error: no id given");
	}
	
	if($usrrank < 5) die("You can't do that since you aren't a moderator.");
	
	echo $html_head;
	?>
	<title>Hide Forum</title>
	</head>
	<body>
	
	<form action="action.php" method="post">
	<input type="hidden" name="do" value="hide" />
	<input type="hidden" name="fid" value="<?=$fid?>" />
	<input type="hidden" name="tid" value="<?=$tid?>" />
	<input type="submit" name="submit" value="Hide" /> this <?=($fid ? 'forum' : 'topic')?> from <select name="who">
		<option value="0">nobody -- everyone has access</option>
		<option value="2">registered users</option>
		<option value="5">V.I.P.s</option>
		<option value="6">non-staff moderators</option>
		<option value="7">low-level staff</option>
		<option value="8">mid-level staff</option>
		<option value="9">high-level staff</option>
	</select> and below.
	<?
	if($fid) {
		?>
		<p><label><input type="checkbox" name="hide_children" value="1"> Set all child threads to the same hide settings 
		(even if they are tagged with another forum's included tags) [this is only a one-time set-up; child threads can be changed afterward]</label></p>
		<?
	}
	?>
	</form>
	</body>
	</html>
	<?

} elseif($_POST['do'] == "hide") {
	
	$fid = $_POST['fid'];
	$tid = $_POST['tid'];
	$who = $_POST['who'];
	if($fid) {
		if($_POST['hide_children']) {
			// hide all child topics
			$q = "SELECT included_tags FROM forums WHERE fid='$fid' LIMIT 1";
			$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
			$q2 = "SELECT tid FROM forums_tags WHERE tag = '".$dat->included_tags."'";
			$res = mysqli_query($GLOBALS['db']['link'], $q2);
			while($row = mysqli_fetch_assoc($res)) {
				$tids[] = $row['tid'];
			}
			foreach($tids as $tid) {
				$q3 = "UPDATE forums_topics SET invisible='$who' WHERE tid='$tid' LIMIT 1";
				if(!mysqli_query($GLOBALS['db']['link'], $q3)) die("Error: Couldn't hide any child topics");
			}
		}
		$query = "UPDATE `forums` SET `invisible` = '$who' WHERE `fid` = '$fid' LIMIT 1";
	} elseif($tid) {
		$query = "UPDATE `forums_topics` SET `invisible` = '$who' WHERE `tid` = '$tid' LIMIT 1";
	} else {
		die("Error: no id given");
	}
	
	if(mysqli_query($GLOBALS['db']['link'], $query)) header("Location: /forums/".($fid ? '?fid='.$fid : '?tid='.$tid));
	else die("Couldn't set visibility");

} elseif($_GET['do'] == "close") {
	
	///////////
	// CLOSE //
	///////////
	
	$fid = $_GET[fid];
	$tid = $_GET[tid];
	if(!$fid && !$tid) {
		die("Error: no id given");
	}
	
	if($usrrank < 5) die("You can't do that since you aren't a moderator.");
	
	echo $html_head;
	?>
	<title>Close Forum</title>
	</head>
	<body>
		
	<form action="action.php" method="post">
	<input type="hidden" name="do" value="close" />
	<input type="hidden" name="fid" value="<?=$fid?>" />
	<input type="hidden" name="tid" value="<?=$tid?>" />
	<input type="submit" name="submit" value="Close" /> this <?=($fid ? 'forum' : 'topic')?> to <select name="who">
		<option value="0">nobody -- everyone has access</option>
		<option value="2">registered users</option>
		<option value="5">V.I.P.s</option>
		<option value="6">non-staff moderators</option>
		<option value="7">low-level staff</option>
		<option value="8">mid-level staff</option>
		<option value="9">high-level staff</option>
	</select> and below.
	<?
	if($fid) {
		?>
		<p><label><input type="checkbox" name="close_children" value="1"/> <b>Also close child topics</b>. 
		Set all child threads to the same hide settings 
		(even if they are tagged with another forum's included tags) 
		[this is only a one-time set-up; child threads can be changed afterward]</label></p>
		<?
	}
	?>
	</form>
	</body>
	</html>
	<?

} elseif($_POST['do'] == "close") {
	
	$fid = $_POST['fid'];
	$tid = $_POST['tid'];
	$who = $_POST['who'];
	if($fid) {
		if($_POST['close_children']) {
			// hide all child topics
			$q = "SELECT included_tags FROM forums WHERE fid='$fid' LIMIT 1";
			$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
			$q2 = "SELECT tid FROM forums_tags WHERE tag = '".$dat->included_tags."'";
			$res = mysqli_query($GLOBALS['db']['link'], $q2);
			while($row = mysqli_fetch_assoc($res)) {
				$tids[] = $row['tid'];
			}
			foreach($tids as $tid) {
				$q3 = "UPDATE forums_topics SET closed='$who' WHERE tid='$tid' LIMIT 1";
				if(!mysqli_query($GLOBALS['db']['link'], $q3)) die("Error: Couldn't hide any child topics");
			}
		}
		
		$query = "UPDATE `forums` SET `closed` = '$who' WHERE `fid` = '$fid' LIMIT 1";
	} elseif($tid) {
		$query = "UPDATE `forums_topics` SET `closed` = '$who' WHERE `tid` = '$tid' LIMIT 1";
	} else die("Error: no id given");
	
	if(mysqli_query($GLOBALS['db']['link'], $query)) avert("/forums/".($fid ? '?fid='.$fid : '?tid='.$tid), 1, "Close preference set.");
	else die("Couldn't close");

} elseif($_GET['do'] == "Edit Forum Details" || $_POST['do'] == "Edit Forum Details") {
	
	////////////////////////
	// EDIT FORUM DETAILS //
	////////////////////////
	
	if($usrrank < 5) die("You can't do that since you aren't a moderator.");
	
	if($fid = $_POST['fid']){
		
		$query = "UPDATE `forums` SET 
			`title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['title'])."',
			`description` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['description'])."',
			`cid` = '".$_POST['cid']."',
			`no_index` = '".$_POST['no_index']."'
			WHERE `fid` = '".$fid."' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $query)) die("Couldn't update forum");
		else header('Location: /forums/?fid='.$fid);
		exit;
	
	}
	
	$fid = $_GET[fid];
	if(!$fid) die("Error: no forum id given");
	
	$query = "SELECT * FROM `forums` WHERE `fid` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $fid)."' LIMIT 1";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(!$f = mysqli_fetch_object($res)) die("Couldn't get forum data for forum id '$fid'");
	$sel[$f->cid] = "selected";
	
	echo $html_head;
	?>
	<title>Edit forum details</title>
	</head>
	<body>
	
	<h1>Edit Forum Details</h1>
	
	<form action="action.php" method="post" name="frm">
	<input type="hidden" name="fid" value="<?=$fid?>" />
	
	<dl>
	
		<dt>Title:</dt>
		<dd><input type="text" name="title" value="<?=$f->title?>" size="40"/></dd>
		
		<dt>Description:</dt>
		<dd><textarea name="description" rows="3" cols="30"><?=stripslashes($f->description)?></textarea></dd>
		
		<dt>Category:</dt>
		<dd><select name="cid">
			<?	$query = "SELECT * FROM `forums_categories` ORDER BY `sort`";
				$res = mysqli_query($GLOBALS['db']['link'], $query);
				while($row = mysqli_fetch_assoc($res))
					echo '<option value="'.$row[cid].'" '.$sel[$row[cid]].'>'.$row[category].'</option>';
			?></select></dd>
		
		<dt>Index?</dt>
		<dd><label><input type="checkbox" name="no_index" value="1" <?=($f->no_index ? 'checked="checked"' : '')?>/> Don't show this forum on indexes</label></dd>
		
	</dl>
		
	<input type="submit" name="do" value="Edit Forum Details" />
	
	</body>
	</html>
	<?
	
} elseif($_GET['new_forum'] || $_POST['new_forum']) {
	
	///////////////
	// NEW FORUM //
	///////////////
	
	if($usrrank < 5) die("You can't do that since you aren't a moderator.");

	if($_POST['submit']) {
		
		$fid = mysqlNextAutoIncrement("forums");
		$q = "INSERT INTO `forums` (`title`,`description`,`cid`, no_index) VALUES ('".mysqli_real_escape_string($GLOBALS['db']['link'], $in['title'])."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['description'])."', '$in[cid]', '$in[no_index]')";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Could not insert into database ('forums' table)");
		header("Location:/forums/?fid=".$fid);
		exit;
		
	}
	
	echo $html_head;
	?>
	<title>New Forum</title>
	<link rel="stylesheet" href="/bin/css/forum-admin.css" type="text/css">
	</head>
	<body>
		
		<h1>Create a new Forum</h1>
		
		<form action="action.php" method="POST" name="frm">
			<input type="hidden" name="new_forum" value="1" />
			
			<table border="0" cellpadding="0" cellspacing="10">
				<tr><td><label for="forumtitle">Forum Title:</label></td><td><input type="text" name="in[title]" value="<?=$_GET['title']?>" id="forumtitle" size="50" maxlength="50" /></td></tr>
				<tr><td><label for="forumdescription">Description:</label></td><td><textarea name="in[description]" id="forumdescription" rows="3" cols="38"></textarea></td></tr>
				<tr><td valign="top">Category:</td><td><select name="in[cid]">
					<?	$query = "SELECT * FROM `forums_categories` ORDER BY `sort`";
						$res = mysqli_query($GLOBALS['db']['link'], $query);
						while($row = mysqli_fetch_assoc($res))
							echo '<option value="'.$row[cid].'" '.$sel[$row[cid]].'>'.$row[category].'</option>';
					?></select></td>
				</tr>
				<tr>
					<td></td>
					<td><label><input type="checkbox" name="in[no_index]" value="1"/> Don't show this forum on indexes</label></td>
				</tr>
				<tr><td><label for="submit" style="visibility:hidden;">submit</label></td><td><input type="submit" name="submit" value="Create this Forum" /></td></tr>
			</table>
		</form>
	</body>
	</html>
	<?
	
} elseif($_GET['do'] == "manage_categories") {
	
	///////////////////////
	// MANAGE CATEGORIES //
	///////////////////////
	
	if($usrrank < 5) die("You can't do that since you aren't a moderator.");
	
	$also = $_GET['also'];
	
	echo $html_head;
	?>
	<title>Manage Forum Categories</title>
	<link rel="stylesheet" href="/bin/css/forum-admin.css" type="text/css">
	</head>
	<body>
	
	<h1>Manage Categories</h1>
	<p><a href="/forums/">Forum index</a></p>
	
	<fieldset><legend>Result</legend>
	<?	if($also == "edit") {
				$c = $_POST['c'];
				$delete = $_POST['delete'];
				foreach($c as $cat) {
					if($delete[$cat[cid]] == 1) {
						$q = "UPDATE forums SET cid = '' WHERE cid = '$cat[cid]'";
						if(!mysqli_query($GLOBALS['db']['link'], $q)) echo "Error: couldn't update forums";
						else {
							$q = "DELETE FROM forums_categories WHERE cid = '$cat[cid]' LIMIT 1";
							if(!mysqli_query($GLOBALS['db']['link'], $q)) echo "Error: couldn't delete from forums_categories";
							else echo "Deleted $cat[category]<br />";
						}
					} else {
						if(!$cat[category]) die("no category name given");
						if(!$cat[sort]) $cat[sort] = 9;
						$q = sprintf("UPDATE forums_categories SET `category` = '%s', `description` = '%s', `sort` = '%s' WHERE cid = '$cat[cid]' LIMIT 1",
							mysqli_real_escape_string($GLOBALS['db']['link'], $cat[category]),
							mysqli_real_escape_string($GLOBALS['db']['link'], $cat[description]),
							mysqli_real_escape_string($GLOBALS['db']['link'], $cat[sort]));
						if(mysqli_query($GLOBALS['db']['link'], $q)) echo "Edited $cat[category]<br />";
						else echo "error: couldn't edit categories<br />";
					}
				}
			
			} elseif($also == "new") {
				if(!$category = $_POST['category']) die("Error: No category given");
				$q = sprintf("INSERT INTO forums_categories (`category`, `description`, `sort`) VALUES ('%s', '%s', '9')",
					mysqli_real_escape_string($GLOBALS['db']['link'], $category),
					mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['description']));
				if(mysqli_query($GLOBALS['db']['link'], $q)) echo "Added category";
				else echo "Error: couldn't add category";
			} else echo 'none';
			
	?></fieldset>
	
	<fieldset>
	<legend>Edit Categories</legend>
	
	<form action="action.php?do=manage_categories&also=edit" method="post">
	<table border="0">
	<tr><th>category</th>
			<th>description</th>
			<th>sort</th>
			<th>delete</th>
	</tr>
	<?
	
	$q = "SELECT * FROM forums_categories ORDER BY `sort` ASC";
	$res = mysqli_query($GLOBALS['db']['link'], $q);
	while($row = mysqli_fetch_assoc($res)) {
		$row = stripslashesDeep($row);
		echo '<input type="hidden" name="c['.$row[cid].'][cid]" value="'.$row[cid].'" />
					<tr><td valign="top"><input type="text" name="c['.$row[cid].'][category]" value="'.$row[category].'" /></td>
					<td><textarea name="c['.$row[cid].'][description]" rows="3">'.$row[description].'</textarea></td>
					<td valign="top"><input type="text" name="c['.$row[cid].'][sort]" value="'.$row[sort].'" maxlength="2" size="1" /></td>
					<td valign="top"><input type="checkbox" name="delete['.$row[cid].']" value="1" id="delete'.$row[cid].'" /> <label for="delete'.$row[cid].'">delete and disassociate all forums from this category</label></td>
					</tr>
					';
	}
	?><tr><td><input type="submit" value="Submit" /></td><td>&nbsp;</td><td>&nbsp;</td></tr>
	</table>
	</form>
	</fieldset>
	
	<fieldset>
	<legend>New Category</legend>
	<form action="action.php?do=manage_categories&also=new" method="post">
	Category name: <input type="text" name="category" /><br />
	Description: <textarea name="description"></textarea><br />
	<input type="submit" value="Submit" />
	</form>
	</fieldset>
	
	</body>
	</html>
	<?
	
} elseif($_GET['do'] == "manage_tags") {
	
	/////////////////
	// MANAGE TAGS //
	/////////////////
	
	if($usrrank < 5) die("You can't do that since you aren't a moderator.");
	
	echo $html_head;
	?>
	<title>Manage Forum Tags</title>
	<link rel="stylesheet" href="/bin/css/forum-admin.css" type="text/css">
	</head>
	<body>
	
	<h1>Manage Tags</h1>
	<p><a href="/forums/">Forum index &gt;</a></p>
	
	<?
	if($edit = $_GET['edit_tag']) {
		//edit a tag
		?>
		<form action="/forums/action.php?do=manage_tags" method="post">
			<input type="hidden" name="old_tag" value="<?=$edit?>"/>
			
			<fieldset>
				<legend><b>Rename</b></legend>
				Change all instances of the tag <i><?=$edit?></i> to: (select ONE of the following)
				<p><select name="new_tag[]">
					<option value="">Tag a game...</option>
					<?
					$query = "SELECT gid, title, platform_shorthand FROM games LEFT JOIN games_platforms USING (platform_id) ORDER BY title";
					$res   = mysqli_query($GLOBALS['db']['link'], $query);
					while($row = mysqli_fetch_assoc($res)) {
						$row[title] = stripslashes($row[title]);
						if(strlen($row[title]) > 50) $row[title] = substr($row[title], 0, 49) . '&hellip;';
						echo '<option value="gid:'.$row[gid].'">'.$row[title].' ('.$row[platform_shorthand].")</option>\n";
					}
					?>
				</select></p>
				<p><select name="new_tag[]">
					<option value="">Tag a person...</option>
					<optgroup label="Prolific creators">
					<?
					$query = "SELECT `name`, prolific FROM people ORDER BY prolific DESC, `name`";
					$res   = mysqli_query($GLOBALS['db']['link'], $query);
					$i = 0;
					while($row = mysqli_fetch_assoc($res)) {
						if(!$row[prolific]) {
							$i++;
							if($i == 1) echo '</optgroup><optgroup label="Other people">';
						}
						echo '<option value="'.$row[name].'">';
						if(strlen($row[name]) > 20) echo substr($row[name], 0, 19) . '&hellip;';
						else echo $row[name];
						echo "</option>\n";
					}
					?></optgroup>
				</select></p>
				<p><select name="new_tag[]">
					<option value="">Tag a forum...</option>
							<?
							$query = "SELECT * FROM forums";
							$res   = mysqli_query($GLOBALS['db']['link'], $query);
							while($row = mysqli_fetch_assoc($res)) {
								echo '<option value="'.$row['included_tags'].'">'.$row['title'].'</option>';
							}
						?>
					</select></p>
				<p>or input manually: <input type="text" name="new_tag[]"/></p>
			</fieldset>
			
			<br/>
			
			<fieldset>
				<legend><b>Delete</b></legend>
				<label><input type="checkbox" name="delete" value="1"/> Delete all instances of this tag</label>
			</fieldset>
			
			<br/>
			
			<input type="submit" name="submit_edit_tag" value="Make changes"/>
			
		</form>
		</body></html>
		<?
		exit;
		
	}
	
	?>
	
	<fieldset>
		<legend>Result</legend>
		<?
		if($_POST['submit_edit_tag']) { //submit edits
			if($_POST['old_tag'] && $_POST['delete']) {
				//delete
				$query = "DELETE FROM forums_tags WHERE tag='".$_POST['old_tag']."'";
				if(!mysqli_query($GLOBALS['db']['link'], $query)) echo "DB Error: Couldn't delete tags.";
				else echo "Tags successfully deleted.";
			} else {
				//rename
				if(!$_POST['new_tag'] || !$_POST['old_tag']) echo "ERROR: tag not changed: new & old tag names not received.<br/>";
				else {
					foreach($_POST['new_tag'] as $n) {
						if($n != "") $new = $n;
					}
					if(!$new) echo "Error: Couldn't find new tag";
					else {
						$query = "UPDATE forums_tags SET tag='$new' WHERE tag='".$_POST['old_tag']."'";
						if(!mysqli_query($GLOBALS['db']['link'], $query)) echo "Error: Couldn't update database";
						else echo 'Success: All tags named <i>'.$_POST['old_tag'].'</i> are now named <i><a href="/forums/?tag='.$new.'">'.$new.'</a></i>.';
					}
				}
			}
		} elseif($del = $_GET['delete_all_tags']) { //delete
			$q = "DELETE FROM forums_tags WHERE tag='$del' LIMIT 1";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) {
				echo "Error: Couldn't delete tags from db";
			} else {
				echo "Tags successfully deleted";
			}
		} else echo "none";
		?>
	</fieldset>
	
	<br/>
	
	<?
	$q = "SELECT tid FROM forums_topics";
	$r = mysqli_query($GLOBALS['db']['link'], $q);
	while($row = mysqli_fetch_assoc($r)) {
		$q2 = "SELECT * FROM forums_tags WHERE tid='$row[tid]' LIMIT 1";
		if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q2))) $tagless[] = $row[tid];
	}
	
	if($tagless) {
		?>
		<fieldset>
			<legend>Tagless Threads!</legend>
			Tagless threads have no way of showing up on any indexes. What a conundrum! Give these threads a home by taggind something... anything!.
			<br/><br/>
			Currently there are <b><?=count($tagless)?></b> tagless threads:
			<ul>
				<?
				foreach($tagless as $tid) {
					echo '<li><a href="/forums/?tid='.$tid.'">topic #'.$tid.'</a></li>';
				}
				?>
			</ul>
		</fieldset>
		<br/>
		<?
	}
	?>
	
		<table border="1" cellpadding="5" cellspacing="1">
			<tr>
				<th colspan="3" style="background-color:#EEE;">Manage Tags</th>
			</tr>
			<tr>
				<th><a href="action.php?do=manage_tags&orderby=tag" title="order by tag name">Tag</a></th>
				<th><a href="action.php?do=manage_tags&orderby=count+DESC" title="order by number of instances"># Instances</a></th>
				<th>Actions</th>
			</tr>
			<?
			if(!$orderby = $_GET['orderby']) $orderby = "tag";
			$query = "SELECT DISTINCT(tag), COUNT(tag) AS count FROM `forums_tags` GROUP BY tag ORDER BY $orderby";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)) {
				$i++;
				?>
				<tr>
					<td><a href="/forums/?tag=<?=urlencode($row['tag'])?>"><?=$row['tag']?></a></td>
					<td><?=$row['count']?></td>
					<td>
						<input type="button" value="Edit all instances" onclick="window.location='action.php?do=manage_tags&edit_tag=<?=urlencode($row['tag'])?>';"/> 
						<input type="button" value="Delete" onclick="if(confirm('Really delete this tag from all forum threads?')) window.location='action.php?do=manage_tags&delete_all_tags=<?=urlencode($row['tag'])?>';"/>
					</td>
				</tr>
				<?
			}
			?>
		</table>
	</form>
	
	</body>
	</html>
	<?
	exit;
	
}

if($_GET['do'] == "manage_subscriptions") {
	
	/////////////////
	// UNSUBSCRIBE //
	/////////////////
	
	echo $html_head.'<body>';
	
	if(!$usrid) die('Please <a href="/login.php">log in</a> to unsubscribe.</body></html>');
	
	if($_GET['unsubscribe']){
		$handle = explode(",", $_GET['unsubscribe']);
		$q = "DELETE FROM forums_mail WHERE `".mysqli_real_escape_string($GLOBALS['db']['link'], $handle[0])."` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $handle[1])."' AND usrid='$usrid'";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) echo "There was an error removing the subscription for USER ID # $usrid ( $handle[0] : $handle[1] )";
		else echo "You have been successfully unsubscribed.";
	} else echo "Unknown action attempted.";
	
	echo '<p><a href="/">Videogam.in</a> | <a href="/forums">Forums</a></p></body></html>';
	exit;

} elseif($_GET['subscribe']) {
	
	///////////////
	// SUBSCRIBE //
	///////////////
	
	$code = base64_decode($_GET['subscribe']);
	list($user, $tid) = explode(";;", $code);
	$q = sprintf("INSERT INTO forums_mail (`usrid`, `tid`) VALUES ('%s', '%s')",
		mysqli_real_escape_string($GLOBALS['db']['link'], $user),
		mysqli_real_escape_string($GLOBALS['db']['link'], $tid));
	$redirect_to = '/forums/?tid='.$tid;
	if(mysqli_query($GLOBALS['db']['link'], $q)) avert($redirect_to, 3, "You have been successfully subscribed and are being <a href=\"$redirect_to\">redirected</a>");
	else die("There was an error adding your subscription. Please submit a <a href=\"/bug.php\">bug report</a>.");
	exit;
}

if($_GET['do'] == 'update_posts') {
	$forum->updatePosts();
	header("Location:/forums");
	echo "updating posts...";
}

if($do == "turn_on_javascript" || $do == "turn_off_javascript") {
	
	////////////////////////////
	// TURN ON/OFF JAVASCRIPT //
	////////////////////////////
	
	if($usrid) {
		$query = "UPDATE user_prefs SET no_javascript = ".($do == "turn_on_javascript" ? "0" : "1")." WHERE user = '$usrid' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $query)) die("Couldn't update your preferences due to db error.");
		else avert(urldecode($_GET['loc']), 1, 'Preferences updated.');
	} else {
		die("No usrid detected");
	}
}

if($do == "post_quote") {
	
	///////////////////////////////////
	// POST REPLY VIA QUOTE (NON-JS) //
	///////////////////////////////////
	
	if(!$pid = mysqli_real_escape_string($GLOBALS['db']['link'], $_GET['pid'])) {
		
		if(!$_POST) die("No PID or post vars given");
		
		//if we're this far, POST input rec'd
		if(!$_POST['message']) die("No message input");
		if(!$tid = $_POST['tid']) die("No tid given");
		$message = strip_tags($_POST['message'], '<b><i><a><strike><big><small><blockquote><del><img>');
		$message = trim($message);
		$datetime = date('Y-m-d H:i:s');
		
		$query = sprintf("INSERT INTO `forums_posts` (`tid`,`poster`,`posted`,`message`,`ip`) VALUES ('$tid','$usrid','$datetime','%s','".$_SERVER['REMOTE_ADDR']."')",
			mysqli_real_escape_string($GLOBALS['db']['link'], $message));
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		if(!$res) {
			die('Error: couldn\'t post into forums posts table');
		}
			
		$forum->updatePosts($tid);
		
		//send mail to topic subscribers
		$q = "SELECT m.*, t.`title` FROM forums_mail as m, forums_topics as t WHERE m.tid = '$tid' AND t.tid = '$tid' AND m.`usrid` != '$usrid'";
		$res = mysqli_query($GLOBALS['db']['link'], $q);
		while($row = mysqli_fetch_assoc($res)) {
			$q2 = "SELECT username, email FROM `users` WHERE `usrid` = '$row[usrid]' LIMIT 1";
			$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q2));
			$mail_message = $dat->username.",\n".outputUser($usrid, FALSE, FALSE)." has replied to the Videogam.in forum topic \"".$row['title']."\".\n---> http://videogam.in/forums/?tid=$tid&page=unread\n\nSincerely,\nThe Videogam.in Forum Notification Robot\n\nP.S.: You received this e-mail because you are subscribed to this topic. To unsubscribe, go here -> http://videogam.in/forums/action.php?unsubscribe=".base64_encode($row['usrid'].';;'.$row['tid'])."";
			$headers = "From: Videogam.in <noreply@videogam.in>\r\n" .
				'X-Mailer: PHP/' . phpversion();
			if(!mail($dat->email, "New Videogam.in forum post", $mail_message, $headers)) sendBug('Couldnt e-mail forum subscriber. Details:'."$dat->email, New Videogam.in forum post, $mail_message, $headers");
		}
		
		header("Location: /forums/?fid=$fid&page=last#newest");
		
	} else {
		
		if(!$_GET['tid']) die ("no tid given");
		
		//see if pid exists & then get data
		$q = "SELECT * FROM forums_posts WHERE pid='$pid' LIMIT 1";
		if(!$post = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
			die("No data found for PID #$pid");
		}
		
		$title = "Videogam.in Forums / Post Reply with Quote";
		Head();
		?>
		<h1 class="plain" style="margin-bottom:1em"><a href="/forums/">Forums</a> <span>/</span> Post Reply via Quote</h1>
		<form action="/forums/action.php?do=post_quote" method="post">
			<input type="hidden" name="tid" value="<?=$tid?>"/>
			<textarea name="message" rows="10" cols="80"><blockquote><b><?=$post->poster?> said:</b>
<?=stripslashes($post->message)?></blockquote>
</textarea><br/>
			<input type="submit" name="submit" value="Post Reply"/>
		</form>
		<?
		Foot();
	
	}
	
	exit;
}

if($_POST['outputSuggestTag']) {
	
	// SUGGEST TAG FORM //
	
	if($usrid) {
		?>
		<div style="margin:5px 0 0; padding:5px 0 0; border-top:1px solid #DDD; color:#808080;">
			Tag a 
			<a href="javascript:void(0)" onclick="showTagField('game')">game</a> &middot; 
			<a href="javascript:void(0)" onclick="showTagField('person')">person</a> &middot; 
			<?
			//groups?
			$q_groups = "SELECT name, g.group_id FROM groups_members gm, groups g WHERE gm.usrid='$usrid' AND g.group_id=gm.group_id AND g.allow_forums='1'";
			if($groupnum = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q_groups))) echo '<a href="javascript:void(0)" onclick="showTagField(\'group\')">group</a> &middot; ';
			?>
			<a href="javascript:void(0)" onclick="showTagField('other')">other</a>
			<?=($usrrank >= 5 ? ' &middot; <a href="javascript:void(0)" onclick="showTagField(\'forum\')">new forum</a>' : '')?>
			
			<ul id="suggest-tag-fields" style="display:none">
				<li id="tag-field-game">
					<select name="tag[]">
						<option value="">Suggest a game...</option>
						<?
						$query = "SELECT games.gid, games.title, release_date, unpublished FROM games 
							LEFT JOIN games_publications ON (games.gid=games_publications.gid AND games_publications.primary='1') 
							LEFT JOIN games_platforms ON (games_publications.platform_id=games_platforms.platform_id) ORDER BY games.title";
						$res   = mysqli_query($GLOBALS['db']['link'], $query);
						while($row = mysqli_fetch_assoc($res)) {
							if(strlen($row['title']) > 80) $row['title'] = substr($row['title'], 0, 79) . '&hellip;';
							echo '<option value="gid:'.$row['gid'].'"'.($row['unpublished'] ? ' style="color:#999;"' : '').'>'.$row['title'].' ('.substr($row['release_date'], 0, 4).")</option>\n";
						}
						?>
					</select> 
					<input type="button" value="Suggest" class="suggest-tag-button" onclick="submitTag($(this).prev().val(), '<?=$_POST['tid']?>');"/>
				</li>
				<li id="tag-field-person">
					<select name="tag[]">
						<option value="">Suggest a person...</option>
						<?
						$query = "SELECT `name`, `prolific`, `title` FROM people ORDER BY `name`";
						$res   = mysqli_query($GLOBALS['db']['link'], $query);
						while($row = mysqli_fetch_assoc($res)) {
							echo '<option value="'.$row['name'].'"'.($row['prolific'] ? ' style="font-weight:bold"' : '').'>'.$row['name'].($row['title'] ? ' ('.$row['title'].')' : '').'</option>';
						}
						?>
					</select> 
					<input type="button" value="Suggest" class="suggest-tag-button" onclick="submitTag($(this).prev().val(), '<?=$_POST['tid']?>');"/>
				</li>
				<?
				if($groupnum) {
					?>
					<li id="tag-field-group">
						<div style="margin:0 0 3px; color:#666;" class="warn">Tagging a group will restrict all replies to group members.</div>
						<select name="tag[]">
							<option value="">Suggest a group...</option>
							<?
							$res = mysqli_query($GLOBALS['db']['link'], $q_groups);
							while($row = mysqli_fetch_assoc($res)) {
								echo '<option value="group:'.$row['group_id'].'">'.$row['name'].'</option>';
							}
							?>
						</select> 
						<input type="button" value="Suggest" class="suggest-tag-button" onclick="submitTag($(this).prev().val(), '<?=$_POST['tid']?>');"/>
					</li>
					<?
				}
				?>
				<li id="tag-field-other">
					<div style="margin:0 0 3px; color:black;">Please input only one tag at a time.</div>
					<input type="text" name="tag[]" size="50"/> 
					<input type="button" value="Suggest" class="suggest-tag-button" onclick="submitTag($(this).prev().val(), '<?=$_POST['tid']?>');"/>
				</li>
				<?
				if($usrrank >= 5) {
					?>
				<li id="tag-field-forum">
					<div style="margin:0 0 3px; color:black;"><b>Note:</b> Tagging a new forum will change this topic's forum association, removing the topic from the current forum.</div>
					<select name="tag[]">
						<option value="">ADMIN: Tag a forum</option>
						<?
						$query = "SELECT * FROM forums";
						$res   = mysqli_query($GLOBALS['db']['link'], $query);
						while($row = mysqli_fetch_assoc($res)) {
							echo '<option value="'.$row['included_tags'].'">'.$row['title'].'</option>';
						}
						?>
					</select> 
					<input type="button" value="Suggest" class="suggest-tag-button" onclick="submitTag($(this).prev().val(), '<?=$_POST['tid']?>');"/>
				</li>
					<?
				}
				?>
			</ul>
		</div>
		<?
	} else {
		echo 'Please register and log in to make a suggestion.';
	}
	
}

if($_POST['submit_tag_suggestion']) {
	
	// SUBMIT TAG SUGGESTION //
	
	if(!$tid = $_POST['tid']) die("Error: No topic ID given");
	if(!$tag = $_POST['tag']) die("Error: No tag name given");
	$tag = str_replace("[AMP]", "&", $tag);
	$tag = str_replace("[PLUS]", "+", $tag);
	
	//already has this tag?
	$q = "SELECT * FROM forums_tags WHERE tid='$tid' AND tag='".mysqli_real_escape_string($GLOBALS['db']['link'], $tag)."' LIMIT 1";
	if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) die("Error: This topic already has that tag");
	
	if(substr($tag, 0, 6) == "forum:") {
		if($usrrank <= 4) exit;
		//change forum assoc
		$q = "DELETE FROM forums_tags WHERE tid='$tid' AND tag LIKE 'forum:%'";
		mysqli_query($GLOBALS['db']['link'], $q);
	}
	
	$tagid = mysqlNextAutoIncrement("forums_tags");
	$query = "INSERT INTO forums_tags (tid, tag, datetime, usrid) VALUES ('$tid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $tag)."', '".date("Y-m-d H:i:s")."', '$usrid');";
	if(!mysqli_query($GLOBALS['db']['link'], $query)) {
		die("Error: Couldn't add tags because of a db error; ".mysqli_error($GLOBALS['db']['link']));
	}
	if($usrid != 1) {
		$q = "SELECT `title` FROM forums_topics WHERE tid='$tid' LIMIT 1";
		$topic = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
		@mail(getenv('NOTIFICATION_EMAIL'), "Videogam.in: new forum tag", outputUser($usrid, FALSE, FALSE)." has tagged the forum topic:\n\n   $topic->title\n\nwith a new tag:\n\n   $tag (".outputTag($tag).")\n\nView the topic at http://videogam.in/forums/?tid=$tid");
	}
	?>
	<span onmouseover="$(this).children('.x').show();" onmouseout="$(this).children('.x').hide();">
		 &middot; 
		<a href="/forums/?tag=<?=urlencode($tag)?>"><?=outputTag($tag)?></a> 
		<a href="javascript:void(0)" onclick="deleteTag('<?=$tagid?>'); $(this).parent().hide();" title="remove tag" class="x" style="display:none">X</a>
	</span>
	<?
	exit;
}

if($_POST['delete_tag']) {
	
	// DELETE TAG //
	
	//if($usrrank < 5) die("You can't do that since you aren't a moderator.");
	$del = $_POST['delete_tag'];
	
	$q = "DELETE FROM forums_tags WHERE id='$del' LIMIT 1";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) {
		die("Error: Couldn't delete tag '$del'; ".mysqli_error($GLOBALS['db']['link']));
	}
	exit;
	
}

if($do == "upload") {
	require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.upload.php");
	echo $html_tag;
	?>
	<head>
		<title>Upload a profile pic</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	</head>
	<body style="margin:0; padding:0; background-color:white; font:normal 13px arial;">
	<?
	if($_FILES['file']['name']) {
		$ext = substr($_FILES['file']['name'], -3, 3);
		$exts = array("jpg","JPG","jpeg","JPEG","gif","GIF","png","PNG");
		if(!in_array($ext, $exts)) die('Error: Please upload only images that are in JPG, GIF, or PNG format. <a href="action.php?do=upload">try again</a></body></html>');
		$handle = new Upload($_FILES['file']);
		if ($handle->uploaded) {
			$handle->image_resize           = true;
			$handle->image_ratio_no_zoom_in = true;
			$handle->image_x                = 670;
			$handle->image_y                = 1000;
			$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/uploads/forums/");
			if ($handle->processed) {
				?>
				Upload complete. Use the following BB Code to display your image:
					<blockquote style="margin:5px 10px;"><code style="background-color:#FFFFB9;">[img]/bin/uploads/forums/<?=$handle->file_dst_name?>[/img]</code></blockquote>
				<input type="button" value="Upload another image" onclick="document.location='action.php?do=upload';"/>
				<?
			} else echo "Upload Error: ".$handle->error;
		} else echo "Upload Error: ".$handle->error;
	} else {
		?>
		<form action="/forums/action.php?do=upload" method="post" target="upload-img" enctype="multipart/form-data" onsubmit="document.getElementById('submit').disabled=true; document.getElementById('submit').value='Uploading...';" style="margin:0;padding:0;">
			&nbsp;&bull; Upload and display any PNG, GIF, or JPG image.<br/>
			&nbsp;&bull; Maximum file size is 7 mb.
			<p style="margin:5px 0 0;"></p>
			<input type="file" name="file"/> <input type="submit" value="Upload" id="submit"/>
		</form>
		<?
	}
	?>
	</body>
	</html>
	<?
	
}

if($tid = $_POST['load_posts']) {
	
	$query = "SELECT * FROM forums_posts WHERE tid = '".mysqli_real_escape_string($GLOBALS['db']['link'], $tid)."' ORDER BY posted";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	$d_posts = array();
	while($row = mysqli_fetch_assoc($res)) {
		if($row['reply_to']) {
			$d_posts[$row['reply_to']]['replies'][$row['pid']] = $row;
		} else {
			$d_posts[$row['pid']] = $row;
		}
	}
	foreach($d_posts as $pid => $row){
		$forum->outputPost($row);
	}
	
	exit;
	
}

if($txt = $_POST['previewtxt']) {
	$txt = parseText($txt);
	$bb = new bbcode();
	$bb->text = $txt;
	$bb->params['nl2p'] = true;
	if(!$_POST['disable_emoticons']) $txt = $bb->params['emote'] = true;
	$txt = $bb->bb2html();
	die($txt);
}

if($_POST['submit_poll']) {
	
	if(!$tid = $_POST['tid']) die("No topic id given");
	if(!$_POST['pollopt']) {
		header("Location: /forums/?tid=$tid");
		exit;
	}
	//already voted?
	$q = "SELECT * FROM forums_polls_votes WHERE tid='$tid' AND usrid='$usrid' LIMIT 1";
	if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) {
		$q = "INSERT INTO forums_polls_votes (tid, usrid, answer) VALUES ";
		foreach($_POST['pollopt'] as $opt) {
			$q.= "('$tid', '$usrid', '$opt'),";
		}
		$q = substr($q, 0, -1).";";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Error: Couldn't update database with new vote; ".mysqli_error($GLOBALS['db']['link']));
	}
	header("Location: /forums/?tid=$tid");
	exit;
	
}

if($_GET['do'] == "orphan_topics"){
	
	// ORPHAN TOPICS //
	
	$sel = '<option value="">Select a forum...</option>';
		$query = "SELECT * FROM forums_categories ORDER BY sort";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)){
			$sel.= '<optgroup label="'.htmlSC($row['category']).'">';
			$query2 = "SELECT * FROM forums WHERE cid='$row[cid]'";
			$res2   = mysqli_query($GLOBALS['db']['link'], $query2);
			while($row2 = mysqli_fetch_assoc($res2)){
				$sel.= '<option value="'.$row2['fid'].'">'.$row2['title'].'</option>';
			}
			$sel.= '</optgroup>';
		}
	
	echo $html_tag;
	?>
	<head>
		<title>Orphan topics</title>
	</head>
	<body>
	
	<?
	if($_POST){
		$in = $_POST['in'];
		foreach($in as $tid => $fid){
			if($fid){
				$q = "UPDATE forums_topics SET fid='$fid' WHERE tid='$tid' LIMIT 1";
				if(!mysqli_query($GLOBALS['db']['link'], $q)) echo "<p><b>ERROR UPDATING FORUM TOPIC # $fid </b></p>";
			}
		}
	}
	?>
	
	<div style="float:right"><a href="/forums">Back to the Forums Index</a> &rarr;</div>
	<h1>Orphan Topics</h1>
	<p>Topics with no parent forum!</p>
	<form action="action.php?do=orphan_topics" method="post">
		<table border="0" cellpadding="5" cellspacing="0">
			<?
			$query = "SELECT * FROM forums_topics WHERE FID = '0'";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			if(!mysqli_num_rows($res)) echo '<tr><td>Hooray! No orphans right now</td></tr>';
			else {
				while($row = mysqli_fetch_assoc($res)){
					?>
					<tr>
						<td><a href="/forums/?tid=<?=$row['tid']?>" target="_blank"><?=$row['title']?></a></td>
						<td><select name="in[<?=$row['tid']?>]"><?=$sel?></select></td>
					</tr>
					<?
				}
			}
			?>
		</table>
		<input type="submit" value="Submit Changes"/>
	</form>
	</body>
	</html>
	<?
}

if($tid = $_GET['move_topic']) {
	
	if($usrrank < 5) die("You can't do that");
	
	if($fid = trim($_GET['to'])) {
		
		$q = "SELECT * FROM forums WHERE fid = '".mysqli_real_escape_string($GLOBALS['db']['link'], $fid)."' LIMIT 1";
		if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) die("ERROR: Can't find forum ID # $fid");
		
		$q = "UPDATE forums_topics SET fid='".mysqli_real_escape_string($GLOBALS['db']['link'], $fid)."' WHERE tid='".mysqli_real_escape_string($GLOBALS['db']['link'], $tid)."' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("A db error occurred whn trying to move {$tid} to {$fid}.");
		
		header("Location:/forums/?tid=$tid");
		exit;
		
	}
	
	$q = "SELECT * FROM forums_topics WHERE tid='".mysqli_real_escape_string($GLOBALS['db']['link'], $tid)."' LIMIT 1";
	if(!$topic = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Couldn't get topic details for id # $tid");
	
	echo $html_tag;
	?>
	<head>
		<title>Move topic</title>
	</head>
	<body>
	<form action="action.php" method="get">
		<input type="hidden" name="move_topic" value="<?=$tid?>"/>
		Move topic to: 
		<select name="to">
			<?
			$q = "SELECT * FROM forums ORDER BY cid";
			$r = mysqli_query($GLOBALS['db']['link'], $q);
			while($row = mysqli_fetch_assoc($r)){
				echo '<option value="'.$row['fid'].'"'.($row['fid'] == $topic->fid ? ' selected="selected"' : '').'>'.$row['title'].'</option>';
			}
			?>
		</select>
		<p></p>
		<input type="submit" value="Move it"/> &nbsp; <a href="/forums/?tid=<?=$tid?>">Never mind</a>
	</form>
	</body></html>
	<?
	
	exit;
	
}

if($_POST['_do'] == "manage_subscription") {
	
	if(!$subj = $_POST['_subj']) die("No subject given");
	if(!$id = $_POST['_id']) die("No ID given");
	if(!$usrid) die("No user session registered");
	
	if(!$forum->subscription(array($subj => $id))) die("Couldn't update subscription because of a db error");
	exit;
	
}

if($pid = $_POST['rate_post']){
	
	$ret = array();
	
	$rating = $_POST['rating'];
	
	if(!$usrid) $ret['error'] = "No user session registered";
	
	$post = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM forums_posts WHERE pid = '".mysqli_real_escape_string($GLOBALS['db']['link'], $pid)."' LIMIT 1"));
	if(!$post) $ret['error'] = "Could't get forum details for post ID # $pid";
	
	if($usrid == $post->usrid && $usrrank < 9) $ret['error'] = "Nice try, but you can't rate your own post";
	
	if(!$ret['error']) {
		mysqli_query($GLOBALS['db']['link'], "DELETE FROM forums_posts_ratings WHERE usrid='$usrid' AND pid='".mysqli_real_escape_string($GLOBALS['db']['link'], $pid)."';");
		$q = "INSERT INTO forums_posts_ratings (usrid, pid, rating) VALUES ('$usrid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $pid)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $rating)."');";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $ret['error'] = "There was a database error and the rating couldn't be recorded.";
		else {
			$q = "SELECT AVG(`rating`) AS `avgrating`, COUNT(*) AS `countrating` FROM forums_posts_ratings WHERE pid = '".mysqli_real_escape_string($GLOBALS['db']['link'], $pid)."';";
			$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
			$row['avgrating'] = ceil($row['avgrating'] * 100);
			
			$weighted = (($row['countrating'] < 4 ? ($row['countrating'] / 3) : 1) * ($row['avgrating'] - 50)) * ($row['countrating'] * .05);
			$weighted = round($weighted);
			
			$q = "UPDATE forums_posts SET `rating` = '".$row['avgrating']."', `ratings` = '".$row['countrating']."', rating_weighted = '$weighted' WHERE pid='".mysqli_real_escape_string($GLOBALS['db']['link'], $pid)."' LIMIT 1";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) $ret['error'] = "Your rating has been recorded but there was an error updating forums_posts database table";
			
			if($weighted != 0){
				$q = "SELECT SUM(rating_weighted) AS total_weighted FROM forums_posts WHERE usrid='".$post->usrid."'";
				$row2 = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
				$q = "UPDATE users SET forum_rating = '".$row2['total_weighted']."' WHERE usrid='".$post->usrid."' LIMIT 1";
				if(!mysqli_query($GLOBALS['db']['link'], $q)) $ret['error'] = "There was a database error updating forum_rating for user ID # ".$post->usrid;
			}
			
			$ret['title'] = "Post quality is ".$row['avgrating']."% based on ".$row['countrating']." rating".($row['countrating'] != 1 ? 's' : '')." [$weighted]";
			$ret['outp'] = $forum->heartRating($weighted);
		}
	}
	
	die(json_encode($ret));
	
}

if($do == "prune"){
	
	// PRUNE //
	
	echo $html_tag;
	?>
	<head>
		<title>Prune Topics</title>
		<link rel="stylesheet" type="text/css" href="/bin/css/screen.css" media="screen"/>
		<script type="text/javascript" src="/bin/script/jquery-1.4.2.js"></script>
		<script type="text/javascript">
			var i=1;
			function toggleCh(){
				if(i++ % 2){
					$('.inpprune').attr('checked', true);
				} else {
					$('.inpprune').attr('checked', false);
				}
			}
		</script>
		<style type="text/css">
			tr.sticky td { background-color:#FFF1E0; }
		</style>
	</head>
	<body style="padding:50px !important;">
		
		<? if($usrrank < 5) die("*"); ?>
		
		<h1>Prune topics</h1>
		
		<p>Clean up the forums by archiving dusty old topics</p>
		
		<p>Show inactive topics since <a href="?do=prune&since=365">12 months</a> | <a href="?do=prune&since=730">24 months</a></p>
		
		<?
		if($since = $_GET['since']){
			
			$query = "SELECT `title`, fid FROM forums";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)){
				$forums[$row['fid']] = $row['title'];
			}
			
			$query = "SELECT * FROM forums_topics WHERE `type`='forum' AND last_post < DATE_ADD(CURDATE(), INTERVAL -".$_GET['since']." DAY) ORDER BY last_post asc";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			
			?>
			<p><big><b><?=mysqli_num_rows($res)?></b> topics</big></p>
			
			<form action="action.php" method="post">
				<table border="1" cellpadding="5" cellspacing="3">
					<tr>
						<th>Topic</th>
						<th>Forum</th>
						<th>Posts</th>
						<th>Creator</th>
						<th>Created</th>
						<th>Last Reply</th>
						<th>Rating</th>
						<th><a href="#prune" onclick="toggleCh();">Prune</a></th>
					</tr>
					<?
					while($row = mysqli_fetch_assoc($res)){
						?>
						<tr class="<?=($row['sticky'] ? 'sticky' : '')?>">
							<td><?=($row['sticky'] ? '<b style="float:right;">STICKY</b>' : '')?><a href="/forums/?tid=<?=$row['tid']?>"><?=$row['title']?></a></td>
							<td><?=$forums[$row['fid']]?></td>
							<td><?=$row['posts']?></td>
							<td><?=outputUser($row['usrid'])?></td>
							<td><?=timeSince($row['created'], 1)?></td>
							<td><?=timeSince($row['last_post'], 1)?></td>
							<td><?=($row['rating'] ? $row['rating']."/".$row['ratings'] : '')?></td>
							<td><label>&nbsp;&nbsp;<input type="checkbox" name="tid[]" value="<?=$row['tid']?>" class="inpprune"/>&nbsp;&nbsp;</label>
						</tr>
						<?
					}
					?>
				</table>
				<p></p>
				<input type="submit" value="Prune"/> Selected topics
			</form>
			<?
		}
		?>
	</body>
	</html>
	<?						
	
}

function sendSubscription($handle, $url, $title, $message){
		
	global $usrid, $usrname;
	
	$bb = new bbcode($message);
	$bb->params['prepend_domain'] = true;
	$bb->params['nl2p'] = true;
	$message = $bb->bb2html();
	
	$emails = array();
	
	foreach($handle as $key => $val){
		if($val == "") continue;
		$query = "SELECT email FROM forums_mail LEFT JOIN users USING (usrid) WHERE `".mysqli_real_escape_string($GLOBALS['db']['link'], $key)."` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $val)."';";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)) {
			$emails[] = $row['email'];
		}
	}
	
	if($emails = array_unique($emails)){
		$headers  = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
			'From: Videogam.in <noreply@videogam.in>' . "\r\n";
		$message = "$usrname has replied to the topic <b><a href=\"http://videogam.in/$url\">$title</a></b><br/><br/><blockquote>".$message."</blockquote><br/><br/>You received this e-mail because you are subscribed to this particular forum, topic, or thread. To unsubscribe, go to the following URL: http://videogam.in/forums/action.php?do=manage_subscriptions&unsubscribe=".$key.",".$val."\n";
		foreach($emails as $emaddr){
			if(!@mail($emaddr, "[Videogam.in] ".$title, $message, $headers.'To: <'.$emaddr.'>'."\r\n")){
				sendBug('Couldnt e-mail forum group subscriber. Details:'.@implode("\n", $email));
				return false;
			}
		}
	}
	
	return $emails;
	
}