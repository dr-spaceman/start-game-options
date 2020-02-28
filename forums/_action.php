<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.forum.php");

$do = $_GET['do'];
$fid = $_GET['fid'];
$tid = $_GET['tid'];
$in = $_POST['in'];

$max_seconds_between_posts = '2';

$no_foot_ad = 1;

$forum = new forum();

$html_head = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>';

if($_POST['do'] == "Post Topic") {
	
	///////////////
	// NEW TOPIC //
	///////////////
	
	// !! any important changes made here should be made to /news/new-preocess.php as well !! //
	
	if(!$usrid) die("You must be a registered user to post");
	
	if(!$type = $_POST['type']) $type = "forum";
	$title = strip_tags($_POST['title']);
	$tags = $_POST['tags'];
	$loc = $_POST['location'];
	if(!$message = $_POST['message']) die("Error: no message to post");
	$description = $_POST['description'];
	$datetime = date('Y-m-d H:i:s');
	
	//if it's going in a forum, inherit invisible & close values
	if($tags) {
		if(!is_array($tags)) {
			$arr = array();
			$arr = explode(",", $tags);
			$tags = array();
			$tags = $arr;
		}
		foreach($tags as $tag) {
			if(substr($tag, 0, 6) == "forum:") {
				$q = "SELECT invisible, closed FROM forums WHERE included_tags='$tag' LIMIT 1";
				$fdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
			}
		}
	}
	
	$tid = mysqlNextAutoIncrement("forums_topics");
	$query = sprintf("INSERT INTO `forums_topics` (`type`,`title`,`description`,`location`,`usrid`,`created`,`last_post`,`last_post_usrid`,`invisible`,`closed`) VALUES 
		('$type','%s','$description','$loc','$usrid','$datetime','$datetime','$usrid','".$fdat->invisible."','".$fdat->closed."')",
		mysqli_real_escape_string($GLOBALS['db']['link'], $title));
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(!$res) die("Couldn't insert into forums topics table");
	
	$message = $forum->parseForForumPost($message, $tid);
	
	$pid = mysqlNextAutoIncrement("forums_posts");
	$query = sprintf("INSERT INTO forums_posts (tid,usrid,posted,message,ip) VALUES 
		('$tid','$usrid','$datetime','%s','".$_SERVER['REMOTE_ADDR']."')",
		mysqli_real_escape_string($GLOBALS['db']['link'], $message));
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(!$res) {
		die("Couldn't post into forums posts table");
	} else {
		
		//insert tags
		if($tags) {
			if(!is_array($tags)) {
				if(strstr($tags, ",")) $tags = explode(",", $tags);
				else {
					$x = $tags;
					unset($tags);
					$tags = array();
					$tags[] = $x;
				}
			}
			foreach($tags as $tag) {
				$q = "SELECT * FROM forums_tags WHERE tid='$tid' AND tag='$tag' LIMIT 1";
				if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) {
					$qs[] = "('$tid', '$tag')";
				}
				if(substr($tag, 0, 6) == "group:") {
					$group_id = str_replace("group:", "", $tag);
				}
			}
			if($qs) {
				$q = "INSERT INTO forums_tags (tid, tag) VALUES ".implode(",", $qs);
				mysqli_query($GLOBALS['db']['link'], $q);
			}
		}
		
		$forum->updatePosts($tid);
		$redirect_to = "/forums/?tid=".$tid;
		
		if($_POST['add_reply_mail']) {
			if(!$forum->addReplyMail($tid)) die('Your topic has been posted but there was an error setting up a reply mail. <a href="'.$redirect_to.'">Go to topic</a>');
			else $reply_mail_note = "<br />Note: Successfully set up reply mail for this topic";
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
		
		//group subscribers
		if($group_id) {
			$q = "SELECT * FROM groups WHERE group_id='$group_id' LIMIT 1";
			$group_dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
			$query2 = "SELECT gm.usrid, username, email FROM groups_members gm LEFT JOIN users USING (usrid) WHERE group_id='$group_id' AND forums_notify='1'";
			$res2   = mysqli_query($GLOBALS['db']['link'], $query2);
			while($row2 = mysqli_fetch_assoc($res2)) {
				$udat = getUserDat($usrid);
				$p_message = bb2html($message);
				$p_message = strip_tags($p_message);
				$mail_message = $p_message."\n\n---> http://videogam.in/forums/?tid=$tid&page=unread#unread\n\nYou received this e-mail because you are subscribed to all threads for the group ".$group_dat->name.". To unsubscribe, click \"options\" on the group hub page -> http://videogam.in/groups/~".$group_dat->name_url." \n";
				$headers = "From: $usrname <".$udat->email.">\r\n" .
					'X-Mailer: PHP/' . phpversion();
				if(!mail($row2['email'], (strlen($title) > 25 ? substr($title, 0, 23)."..." : $title)." [".$group_dat->name."]", $mail_message, $headers)) sendBug('Couldnt e-mail forum group subscriber. Details:'."$row2[email], New Videogam.in forum post, $mail_message, $headers");
			}
		}
		
		if($_POST['ajax']) {
			
			$stags = array();
			$stags = $forum->suggstTags($message);
			
			$p_message = emote($message);
			$p_message = bb2html($message);
			$p_message = nl2br($message);
			echo "|--|$pid|--|";
			?>
			<div class="message-text toggle-edit">
				<?=$p_message?>
				<p id="tag-reminder">
					<a href="#x" onclick="toggle('', 'tag-reminder')" class="x" style="float:right;margin:0 0 0 5px">X</a>
					<? print_r($stags); ?>
				</p>
			</div>
			<div id="editpost-<?=$pid?>" class="toggle-edit" style="display:none">
				<form action="" method="">
					<div style="margin:-6px -7px 0 -7px;">
						<div style="margin-right:12px">
							<textarea name="message" rows="10" id="edit-text-<?=$pid?>" style="width:100%; background-color:#F5F5F5;"><?=$message?></textarea>
						</div>
					</div>
					<p style="margin:3px 0 0;">
						<img src="/bin/img/loading-arrows-small.gif" alt="loading" style="display:none"/> 
						<input type="button" value="Submit Changes" class="submit-edited-post" onclick="submitEditedForumPost('<?=$pid?>');"/> 
						<input type="reset" class="cancel-edit-forum-post" value="Cancel" onclick="$(this).closest('.toggle-edit').hide().prev().show();"/> 
						<?=($usrrank >= 8 ? '<label><input type="checkbox" name="no_track" value="1"/> leave no trace of this edit</label>' : '')?>
					</p>
				</form>
			</div>
			<?
			echo '
				<ul class="message-opts">
					<li><a href="?focus_post='.$pid.'" title="permalink to your post" class="tooltip postnum postnum-new">NEW</a></li>
					<li><a href="javascript:void(0)" class="edit" onclick="$(this).closest(\'ul\').siblings(\'.toggle-edit\').toggle();">Edit</a></li>'.
					($usrrank >= 5 ? '<li><a href="javascript:void(0)" onclick="confirmDelete(\''.$pid.'\')">Delete</a></li>' : '').'
				</ul>
			';
			exit;
		}
		
		header("Location: ".$redirect_to);
		exit;
	}
	
} elseif($do == "edit_topic_details") {
	
	///////////////////
	// TOPIC DETAILS //
	///////////////////
	
	if(!$tid = $_GET['tid']) die("Error: no topic ID given");
	
	if(!$_POST['submit']) {
		
		$q = "SELECT * FROM forums_topics WHERE tid='$tid' LIMIT 1";
		if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Couldn't get topic data for tid # $tid");
		
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
						
						<dt>Title:</dt>
						<dd><input type="text" name="in[title]" value="<?=stripslashes($dat->title)?>"></dd>
						
						<dt>Description:</dt>
						<dd><textarea name="in[description]"><?=stripslashes($dat->description)?></textarea></dd>
						
					</dl>
					
					<input type="submit" name="submit" value="Submit Details"/>
				</fieldset>
			</form>
		</body>
		</html>
		<?
		
	} else {
		
		$in[title] = addslashes($in[title]);
		$in[description] = addslashes($in[description]);
		$q = "UPDATE forums_topics SET 
			`type` = '$in[ftype]',
			`title` = '$in[title]',
			`description` = '$in[description]',
			`tags` = '$in[tags]'
			WHERE tid='$tid' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) {
			die("Error: Couldn't update topic details");
		} else {
			avert("/forums/?tid=$tid", 1, "Details updated");
		}
		
	}

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
			
			avert("/forums/", 1, 'Deleted.');
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
			
			echo $html_head.'<title>delete forum</title></head><body>Forum deleted. You might want to <a href="action.php?do=manage_tags">update tags</a> accordingly.<br/><a href="/forums/">Forums index &gt;</a></body></html>';
			exit;
			
		}
	}

} elseif($_POST['_do'] == "post_reply") {
	
	///////////
	// REPLY //
	///////////
	
	$tid = $_POST['tid'];
	if(!$tid) die("||Error: no topic id given");
	if(!$usrid) die("||You must be a registered user to post");
	
	//check to see when user's last post was
	/*$query = "SELECT (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`posted`)) as seconds_since_post FROM `forums_posts` WHERE `poster` = '$usrid' ORDER BY `posted` DESC LIMIT 1";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if($dat = mysqli_fetch_object($res)) {
		if($dat->seconds_since_post < $max_seconds_between_posts)
			die('||||||Cannot post since it\'s been too soon since your last post. Try again in a few seconds.');
	}*/
	
	
	$message = str_replace("[AMP]", '&', $_POST['message']);
	$message = str_replace("[PLUS]", '+', $message);
	$message = $forum->parseForForumPost($message, $tid);
	if($_POST['disable_emoticons']) $message.= '<!--disable_emoticons-->';
	
	$datetime = date('Y-m-d H:i:s');
	
	$q = "INSERT INTO forums_posts (tid, usrid, posted, message, ip) VALUES 
		('$tid', '$usrid', '$datetime', '".mysqli_real_escape_string($GLOBALS['db']['link'], $message)."', '".$_SERVER['REMOTE_ADDR']."')";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) die('|--|Error: couldn\'t post into forums posts table; '.mysqli_error($GLOBALS['db']['link']));
	else {
		
		$q = "UPDATE forums_topics SET last_post='$datetime', last_post_usrid='$usrid' WHERE tid='$tid' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errs.= "Couldn't 'UPDATE forums_topics' db: ".mysqli_error($GLOBALS['db']['link']);
			
		//get PID
		$query4 = "SELECT pid, tid FROM `forums_posts` WHERE `posted` = '$datetime' LIMIT 1";
		$this_post = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query4));
		
		$stags = array();
		$stags = $forum->extractTags($message, $tid);
		
		//make message displayable
		$p_message = $message;
		if(!$_POST['disable_emoticons']) $p_message = emote($p_message);
		$p_message = bb2html($p_message);
		$p_message = nl2br($p_message);
		
		$i = "ajxpost";
		
		?>
		<div class="message-text toggle-edit">
			<?=$p_message?>
			<fieldset id="tag-reminder">
				<legend>Suggest Tags <span style="color:#CCC;">[<a href="#x" onclick="$(this).closest('fieldset').hide();" style="color:#D72828;">close</a>]</span></legend>
				<a href="#x" onclick="$(this).parent().hide();" class="x" style="float:right;margin:0 0 0 5px">X</a>
				Did you change the course of discussion or mention something new in your post? Tag it!
				<ul>
					<?
					if($stags) {
						?><li class="point"><div></div><span>Click to tag</span></li><?
						foreach($stags as $tag) echo '<li><a href="#tags" onclick="submitTag(\''.$tag.'\', \''.$tid.'\');">'.outputTag($tag).'</a></li>';
					}
					?>
					<li class="suggest"><a href="#tags" onclick="suggestTag(<?=$tid?>);">Suggest a new tag...</a></li>
				</ul>
				<br style="clear:both"/>
			</p>
		</div>
		<div id="editpost-<?=$this_post->pid?>" class="toggle-edit" style="display:none">
			<form action="" method="">
				<div style="margin:-6px -7px 0 -7px;">
					<div style="margin-right:12px">
						<textarea name="message" rows="10" id="edit-text-<?=$this_post->pid?>" style="width:100%; background-color:#F5F5F5;"><?=$message?></textarea>
					</div>
				</div>
				<p style="margin:3px 0 0;">
					<img src="/bin/img/loading-arrows-small.gif" alt="loading" style="display:none"/> 
					<input type="button" value="Submit Changes" class="submit-edited-post" onclick="submitEditedForumPost('<?=$this_post->pid?>');"/> 
					<input type="reset" class="cancel-edit-forum-post" value="Cancel" onclick="$(this).closest('.toggle-edit').hide().prev().show();"/> 
					<?=($usrrank >= 8 ? '<label><input type="checkbox" name="no_track" value="1"/> leave no trace of this edit</label>' : '')?>
				</p>
			</form>
		</div>
		<ul class="message-opts">
			<li><a href="?tid=<?=$tid?>&focus_post=<?=$this_post->pid?>" title="permalink to your post" class="tooltip postnum postnum-new">NEW</a></li>
			<li><a href="javascript:void(0)" class="edit" onclick="$(this).closest('ul').siblings('.toggle-edit').toggle();">Edit</a></li><?
			if($usrrank >= 5) { //del
				?>
				<li><a href="javascript:void(0)" onclick="confirmDelete('<?=$this_post->pid?>')">Delete</a></li><?
			}
			?>
		</ul>
		|--|?tid=<?=$tid?>&focus_post=<?=$this_post->pid?>|--|<?
		
		$q = "SELECT * FROM forums_topics WHERE tid='$tid' LIMIT 1";
		$topic = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
		$p_title = (strlen($topic->title) > 35 ? substr($topic->title, 0, 33)."..." : $topic->title);
		
		$udat = getUserDat($usrid);
		
		//send mail to topic subscribers
		$q = "SELECT * FROM forums_mail WHERE tid = '$tid' AND usrid != '$usrid'";
		$res = mysqli_query($GLOBALS['db']['link'], $q);
		$adds = array();
		while($row = mysqli_fetch_assoc($res)) {
			$q2 = "SELECT username, email FROM `users` WHERE `usrid` = '$row[usrid]' LIMIT 1";
			$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q2));
			$mail_message = $dat->username.",\n".outputUser($usrid, FALSE, FALSE)." has replied to the Videogam.in forum topic \"".$topic->title."\".\n---> http://videogam.in/forums/?tid=$tid&page=unread#unread\n\nSincerely,\nThe Videogam.in Forum Notification Robot\n\nP.S.: You received this e-mail because you are subscribed to this topic. To unsubscribe, go here -> http://videogam.in/forums/action.php?unsubscribe=".base64_encode($row['usrid'].';;'.$row['tid'])."";
			$headers = "From: $usrname <".$udat->email.">\r\n" .
				'X-Mailer: PHP/' . phpversion();
			if(!mail($dat->email, $p_title, $mail_message, $headers)) sendBug('Couldnt e-mail forum subscriber. Details:'."$dat->email, New Videogam.in forum post, $mail_message, $headers");
			$adds[] = $dat->email;
		}
		
		//group subscribers
		if(!$_POST['dont_send_mail']) {
			$query = "SELECT tag FROM forums_tags WHERE tag LIKE 'group:%' AND tid='$tid'";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)) {
				$group_id = str_replace("group:", "", $row['tag']);
				$q = "SELECT * FROM groups WHERE group_id='$group_id' LIMIT 1";
				$group_dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
				$query2 = "SELECT gm.usrid, username, email FROM groups_members gm LEFT JOIN users USING (usrid) WHERE group_id='$group_id' AND forums_notify='1'";
				$res2   = mysqli_query($GLOBALS['db']['link'], $query2);
				while($row2 = mysqli_fetch_assoc($res2)) {
					if(!in_array($row2['email'], $adds)) {
						$mail_message = strip_tags($p_message)."\n\n---> http://videogam.in/forums/?tid=$tid&page=unread#unread\n\nYou received this e-mail because you are subscribed to all threads for the group $group_dat->name. To unsubscribe, click \"options\" on the group hub page -> http://videogam.in/groups/~$group_dat->name_url \n";
						$headers = "From: $usrname <".$udat->email.">\r\n" .
							'X-Mailer: PHP/' . phpversion();
						if(!mail($row2['email'], $p_title." [".$group_dat->name."]", $mail_message, $headers)) sendBug('Couldnt e-mail forum group subscriber. Details:'."$row2[email], New Videogam.in forum post, $mail_message, $headers");
						$adds[] = $row2['email'];
					}
				}
			}
		}
		
		//add reply mail
		if($_POST['add_reply_mail'] == "true") {
			if(!$forum->addReplyMail($tid)) echo "There was an error setting up your subscription to this topic.";
			else echo "Successfully set up reply mail for this topic; ";
		}
		
		if($_POST['no_js']) {
			header("Location: /forums/?fid-$fid&page=last#newest");
		}
		exit;
	}

} elseif($_POST['_do'] == "Edit Post") {
	
	///////////////
	// EDIT POST //
	///////////////
	
	if(!$pid = $_POST['pid']) die("Error: no post id given");
	
	$q = "SELECT * FROM forums_posts WHERE pid='$pid' LIMIT 1";
	if(!$pdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Error: Couldn't get post data for pid # $pid");
	
	if($usrrank < 5 && $pdat->usrid != $usrid) die("You don't have permission to edit this post.");
	
	$datetime = date('Y-m-d H:i:s');
	
	//get tid
	$q = "SELECT tid FROM forums_posts WHERE pid='$pid' LIMIT 1";
	$postdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
	
	if(!$message = $forum->parseForForumPost($_POST['message'], $postdat->tid)) die("No message received");
	
	if($_POST['disable_emoticons']) $message.= '<!--disable_emoticons-->';
	
	$query = sprintf("UPDATE forums_posts SET ".($_POST['no_track'] == 1 ? '' : "editor = '$usrname', edited = '$datetime',")." message = '%s' WHERE pid = '$pid' LIMIT 1",
		mysqli_real_escape_string($GLOBALS['db']['link'], $message));
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(!$res) {
		die("Error: couldn't update posts table");
	} else {
		if($_POST['_ajax']) {
			if(!$_POST['disable_emoticons']) $message = emote($message);
			$message = bb2html($message);
			$message = nl2br($message);
			echo $message;
		} else {
			header("Location: ".$_POST['redirect']);
		}
	}
	exit;
	
} elseif($_GET['do'] == "Delete Post") {
	
	/////////////////
	// DELETE POST //
	/////////////////
	
	if(!$pid = $_GET['pid']) die("Error: no post id given");
	
	if($usrrank < 5) die("You can't do that since you aren't a moderator.");
	
	//get titles in order to redirect
	$query2 = "SELECT tid FROM forums_posts WHERE pid='$pid' LIMIT 1";
	$res2 = mysqli_query($GLOBALS['db']['link'], $query2);
	$dat = mysqli_fetch_object($res2);
	
	$query = "DELETE FROM `forums_posts` WHERE `pid` = '$pid' LIMIT 1";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(!$res) {
		die("Error: couldn't delete the post");
	} else {
		$forum->updatePosts($dat->tid);
		header("Location: /forums/?tid=".$dat->tid);
		exit;
	}

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
	
	if($_GET['do']) {
	
		$fid = $_GET[fid];
		if(!$fid) die("Error: no forum id given");
		
		$query = "SELECT * FROM `forums` WHERE `fid` = '$fid' LIMIT 1";
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
				<option value="">none</option>
				<?	$query = "SELECT * FROM `forums_categories` ORDER BY `sort`";
					$res = mysqli_query($GLOBALS['db']['link'], $query);
					while($row = mysqli_fetch_assoc($res))
						echo '<option value="'.$row[cid].'" '.$sel[$row[cid]].'>'.$row[category].'</option>';
				?></select></dd>
			
			<dt>Included tags:</dt>
			<dd><input type="text" name="included_tags" value="<?=$f->included_tags?>" size="40"/></dd>
			
		</dl>
			
		<input type="submit" name="do" value="Edit Forum Details" />
		
		</body>
		</html>
		<?
	
	} else {
		
		$fid = $_POST['fid'];
		if(!$fid) die("Error: no forum id given");
		
		$query = "UPDATE `forums` SET 
			`title` = '".addslashes($_POST[title])."',
			`description` = '".addslashes($_POST[description])."', 
			`included_tags` = '$_POST[included_tags]',
			`cid` = '$_POST[cid]' 
			WHERE `fid` = '$_POST[fid]' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $query)) die("Couldn't update forum");
		else header('Location: /forums/?fid='.$fid);
	}
	
} elseif($_GET['new_forum'] || $_POST['new_forum']) {
	
	///////////////
	// NEW FORUM //
	///////////////
	
	//if($usrrank < 5) die("You can't do that since you aren't a moderator.");
	
	echo $html_head;
	?>
		<title>New Forum</title>
		<link rel="stylesheet" href="/bin/css/forum-admin.css" type="text/css">
		</head>
		<body>
	<?

	if($_POST['submit']) {
		
		$datetime = date("Y-m-d h:i:s");
		
		$q = sprintf("INSERT INTO `forums` (`title`,`description`,`included_tags`,`cid`,`created`) 
			VALUES ('$in[title]', '%s', '%s', '$in[cid]', '$datetime')",
			mysqli_real_escape_string($GLOBALS['db']['link'], $in[description]),
			mysqli_real_escape_string($GLOBALS['db']['link'], $in[included_tags]));
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Could not insert into database ('forums' table)");
		
		$res = mysqli_query($GLOBALS['db']['link'], "SELECT * FROM `forums` WHERE `created` = '$datetime' LIMIT 1");
		$row = mysqli_fetch_object($res);
		
		?>Your forum has been successfully created. 
		See it <a href="/forums/?fid=<?=$row->fid?>">here</a><?=($in[location] ? " or go back to the <a href=\"".$in[location]."\">location</a> in which you selected to display this forum." : ".")?><br /><br />
		To display your forum manually, use the following PHP code:<br />
		<pre>require ($_SERVER["DOCUMENT_ROOT"]."/index/inc/class.forum.php");
$forum = new Forum();
$forum->showForum(<?=$row->fid?>);</pre>
		<?
		
	} else {
		?><h1>Create a new Forum</h1>
		
		<form action="action.php" method="POST" name="frm">
		<input type="hidden" name="new_forum" value="1" />
		
		<fieldset style="display:none">
			<legend>Location</legend>
			If you do not specify a location, the forum can still be called manually.
			<p>Physical location:<br/>
				http://squarehaven.com/<input type="text" name="in[location]" value="<?=$loc?>" size="50" /></p>
			<p style="background-color:#EEE;">-- OR --</p>
			<p>Corresponding db table:<br/>
				<input type="text" name="in[corresponding_table]" value="<?=$_GET['corresponding_table']?>" size="20" /></p>
			<p>Corresponding ID in the db table:<br/>
				<input type="text" name="in[corresponding_id]" value="<?=$_GET['corresponding_id']?>" size="20" /></p>
		</fieldset>
		
		<fieldset style="display:none">
			<legend>Mirror</legend>
			If you mirror another forum, don't bother filling out the rest of the info below this field.
			<p><select name="mirrored_fid">
				<option value="">No mirror</option>
				<?	$query = "SELECT * FROM forums_categories ORDER BY `sort` ASC";
						$res = mysqli_query($GLOBALS['db']['link'], $query);
						while($row = mysqli_fetch_assoc($res)) {
							echo '<optgroup label="'.$row[category].'">';
							$q2 = "SELECT * FROM forums WHERE `type`!='mirror' AND cid = '$row[cid]' ORDER by `title` ASC";
							$res2 = mysqli_query($GLOBALS['db']['link'], $q2);
							while($row2 = mysqli_fetch_assoc($res2)) {
								if(strlen($row2[title]) > 50) $row2[title] = substr($row2[title], 0, 50) . '&hellip;';
								echo '<option value="'.$row2[fid].'">'.$row2[title]."</option>\n";
							}
							echo '</optgroup>';
						}
				?>
			</select> <input type="button" value="See Forum" onclick="window.open('/forums/fid/'+document.frm.mirrored_fid.value, 'showforum')" /></p>
		</fieldset>
		
		<table border="0" cellpadding="0" cellspacing="10">
			<tr><td><label for="forumtitle">Forum Title:</label></td><td><input type="text" name="in[title]" value="<?=$_GET['title']?>" id="forumtitle" size="50" maxlength="50" /></td></tr>
			<tr><td><label for="forumdescription">Description:</label></td><td><textarea name="in[description]" id="forumdescription" rows="3" cols="38"></textarea></td></tr>
			<tr><td valign="top"><label for="forumtag">Included Tags:</label></td><td><input type="text" name="in[included_tags]" id="forumtag" size="50" maxlength="50" /><br/><small>All topics tagged with this will be child topics of this forum.<br/>Note: this phrase will be matched using MySQL's <a href="http://dev.mysql.com/doc/refman/5.0/en/fulltext-boolean.html">Boolean Full-Text Search</a>. It will be caled upon using: <code>AGAINST ('"YOUR PHRASE"' IN BOOLAN MODE)</code></small></td></tr>
			<tr><td valign="top">Category:</td><td><select name="in[cid]">
				<option value="">none</option>
				<?	$query = "SELECT * FROM `forums_categories` ORDER BY `sort`";
					$res = mysqli_query($GLOBALS['db']['link'], $query);
					while($row = mysqli_fetch_assoc($res))
						echo '<option value="'.$row[cid].'" '.$sel[$row[cid]].'>'.$row[category].'</option>';
				?></select></td>
			<tr><td><label for="submit" style="visibility:hidden;">submit</label></td><td><input type="submit" name="submit" value="Create this Forum" /></td></tr>
		</table>
		</form>
		<?
	}
	
?></body></html><?
	
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
	
} elseif($_GET['unsubscribe']) {
	
	/////////////////
	// UNSUBSCRIBE //
	/////////////////
	
	$code = base64_decode($_GET['unsubscribe']);
	list($user, $tid) = explode(";;", $code);
	$q = sprintf("DELETE FROM forums_mail WHERE `usrid` = '%s' AND `tid` = '%s' LIMIT 1",
		mysqli_real_escape_string($GLOBALS['db']['link'], $user),
		mysqli_real_escape_string($GLOBALS['db']['link'], $tid));
	$redirect_to = '/forums/?tid='.$tid;
	if(mysqli_query($GLOBALS['db']['link'], $q)) avert($redirect_to, 3, "You have been successfully unsubscribed and are being <a href=\"$redirect_to\">redirected</a>");
	else die("There was an error removing your subscription. Please submit a <a href=\"/bug.php\">bug report</a>.");

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
	
}

if($_GET['do'] == 'update_posts') {
	$forum->updatePosts();
	avert('/forums/', 2, 'updating posts...');
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
			$headers = 'From: ' . $default_email . "\r\n" .
				'Reply-To: ' . $default_email . "\r\n" .
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
		@mail($default_email, "Videogam.in: new forum tag", outputUser($usrid, FALSE, FALSE)." has tagged the forum topic:\n\n   $topic->title\n\nwith a new tag:\n\n   $tag (".outputTag($tag).")\n\nView the topic at http://videogam.in/forums/?tid=$tid");
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

if($tid = $_POST['output_posts']) {
	
	$query = "SELECT * FROM forums_posts WHERE tid = '$tid' ORDER BY posted ASC";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	$i = 1;
	while($row = mysqli_fetch_assoc($res)) {
		$forum->outputPost($row, $i++);
	}
	
}

if($txt = $_POST['previewtxt']) {
	require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");
	if($usrrank < 4) $txt = preg_replace("@<([a-z0-9/]+.*?)>@is", "&lt;$1&gt;", $txt);
	if(!$_POST['disable_emoticons']) $txt = emote($txt);
	$txt = bb2html($txt);
	$txt = nl2br($txt);
	echo $txt;
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