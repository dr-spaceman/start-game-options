<?
use Vgsite\Page;
use Verot\Upload;
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");
use Vgsite\Badge;

if($_POST['_action'] == "confirmdesc"){
	
	if(!$nid = $_POST['nid']) die("no id given");
	$desc = trim($_POST['desc']);
	$desc = strip_tags($desc);
	if($desc == "") die("no description given");
	
	$q = "SELECT * FROM posts WHERE nid='".mysqli_real_escape_string($GLOBALS['db']['link'], $nid)."' limit 1";
	if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Couldn't find post for nid $nid");
	if($usrid != $dat->usrid && $_SESSION['user_rank'] < 7) die("permission denied");
	
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

/*if($_POST['action'] == "submit_reviewed_post") {
	
	// SUBMIT REVIEWED POST //
	
	$q = "SELECT * FROM posts WHERE session_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $sessid)."' LIMIT 1";
	if(!$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) die("Error getting data for post session #".$sessid);
	
	$pl = trim($in['permalink']);
	if($pl == "") $pl = $row['permalink'];
	$pl = makePermalink($pl);
	
	//check if permalink already exists for this date
	$i = 0;
	while(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM posts WHERE permalink='".$pl."' AND datetime LIKE '".substr($row['datetime'], 0, 10)."%' AND session_id != '$sessid' LIMIT 1"))) {
		$pl = substr($pl, 0, 53).++$i;
	}
	
	$desc = trim($in['description']);
	if($desc == "") $desc = $row['description'];
	$desc = bb2html($desc);
	$desc = strip_tags($desc);
	
	if(is_array($in['options'])) $opts = implode(" ", $in['options']);
	
	$q = "UPDATE posts SET 
		`permalink` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $pl)."',
		`description` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $desc)."',
		`privacy` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['privacy'])."',
		`options` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $opts)."',
		`unpublished` = '".$in['unpublished']."',
		`archive` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['archive'])."'
		WHERE session_id='$sessid' LIMIT 1";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) {
		$errors[] = "Error updating database with reviewed values :(";
		return;
	}
	
	$pe = $_POST['pe'];
	if($pe['comments']) {
		$q = "UPDATE posts_edits SET `comments` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $pe['comments'])."' WHERE id='".$pe['id']."' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Error: Moderation comment not recorded because of a database error");
	}
	if($pe['email']) {
		$usrdat = getUserDat($pe['email']);
		$to      = $usrdat->email;
		$subject = 'Your Videogam.in post has been moderated';
		$message = $usrdat->username.",\nYour Videogam.in post, \"".$desc.",\" has been edited by $usrname.".($pe['comments'] ? "\n\nComments:\n".$pe['comments'] : "")."\n\nSee your edited post here -> http://videogam.in/posts/?id=".$postdat->nid."\n\nSincerely,\nThe Friendly Videogam.in Post Edit Notfying Robot\n";
		$headers = 'From: noreply@videogam.in' . "\r\n" .
		    'X-Mailer: PHP/' . phpversion();
		mail($to, $subject, $message, $headers);
	}
	
	$date = substr($row['datetime'], 0, 10);
	$date = str_replace("-", "/", $date);
	
	$subdir = "posts";
	if($row['category'] == "public") $subdir = "news";
	elseif($in['category'] == "blog") {
		$usrdat = getUserDat($row['usrid']);
		$subdir = "~".$usrdat->username."/blog";
	}
	
	//$loc = "/posts/handle.php?path=$date/".$pl;
	$loc = "/".($row['category'] == "blog" ? "~$usrname/blog" : "posts")."/".$date."/".$pl;
	header("Location: ".$loc);
	exit;
	
}*/

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

//fetch video vars from url
if($url = $_POST['videourl']) {
	
	$ret = array();
	
	$tags = @get_meta_tags($url);
	$ret['desc'] = $tags['description'];
	$ret['title'] = $tags['title'];
	if($ret['title'] == $ret['desc']) $ret['desc'] = "";
	
	if(preg_match('#http://(www\.)?youtube\.com/watch\?([a-z])=([^&]+)(.*?)#', $url, $matches)) {
		//Youtube
		if($matches[2] && $matches[3]) {
			$ret['code'] = '<object width="100%" height="400"><param name="movie" value="http://www.youtube.com/'.$matches[2].'/'.$matches[3].'?fs=1&hd=1"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.youtube.com/'.$matches[2].'/'.$matches[3].'?fs=1&hd=1" type="application/x-shockwave-flash" allowfullscreen="true" width="100%" height="400"></embed></object>';
			$tn = 'http://img.youtube.com/vi/'.$matches[3].'/0.jpg';
			$tn_local = $_SERVER['DOCUMENT_ROOT']."/bin/temp/".$matches[3].rand(0,999).".jpg";
			if(!copy($tn, $tn_local)) $ret['error'] = "Couldn't copy youtube tn ($tn)";
			else $ret['tn'] = uploadVideoThumb($tn_local);
		}
	} elseif(strstr($url, "vimeo.com")) {
		//vimeo
		$oembed_url = 'http://www.vimeo.com/api/oembed.xml?url='.rawurlencode($url).'&maxwidth=620';
		$oembed = @simplexml_load_string(curl_get($oembed_url));
		if($oembed){
			$ret['code'] = html_entity_decode($oembed->html);
			$ret['title'] = $oembed->title;
			$tn = $oembed->thumbnail_url;
			$tn_local = $_SERVER['DOCUMENT_ROOT']."/bin/temp/".substr(strrchr($tn, '/'), 1);
			if(!copy($tn, $tn_local)) $ret['error'] = "Couldn't copy youtube tn ($tn)";
			else $ret['tn'] = uploadVideoThumb($tn_local);
		}
	}
	die(json_encode($ret));
}

//update img caption
if($_POST['_do'] == "update_caption") {
	$_POST['_caption'] = htmlentities($_POST['_caption']);
	$q = "UPDATE media_files SET caption = '".mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['_caption'])."' WHERE imgid = '".mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['_imgid'])."' LIMIT 1";
	mysqli_query($GLOBALS['db']['link'], $q);
	exit;
}

//delete img
if($_GET['delete_img']) {
	if(!$imgid = $_GET['delete_img']) die("Error: No image id given");
	$q = "SELECT usrid, session_id, file FROM media_files LEFT JOIN posts USING (nid) WHERE imgid='$imgid' LIMIT 1;";
	$mdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
	if($mdat->usrid != $usrid && $_SESSION['user_rank'] < 8) die("Error: You don't have access to delete that image.");
	$dir = $_SERVER['DOCUMENT_ROOT']."/media/".substr($mdat->session_id, 0, 4)."/".substr($mdat->session_id, 4)."/";
	unlink($dir."/".$mdat->file);
	@unlink($dir."/".substr($mdat->file, 0, -4)."-561x.jpg");
	@unlink($dir."/".substr($mdat->file, 0, -4)."-350x.jpg");
	unlink($dir."/".substr($mdat->file, 0, -4)."-tn.png");
	unlink($dir."/".substr($mdat->file, 0, -4)."-50x50.png");
	unlink($dir."/".substr($mdat->file, 0, -4)."-20x20.png");
	$q = "DELETE FROM media_files WHERE imgid='$imgid' LIMIT 1";
	mysqli_query($GLOBALS['db']['link'], $q);
	die(Page::HTML_TAG.'<body style="margin:0; padding:0; font-size:13px; font-family:Arial;"><i>'.$mdat->file.'</i> deleted.</body></html>');
}

if($_POST['submit_form']) {
	
	////////////
	// SUBMIT //
	////////////
	
	if(!$usrid) {
		$errors[] = "No user session registered. Please log in to post this.";
		return;
	}
	
	if(!$in['category']) { $errors[] = 'Please choose an appropriate <a href="#postsection" onclick="$(\'#notify\').slideUp();">Post Section</a>'; }
	
	$heading = parseText($in['heading']);
	$heading = bb2html($heading);
	$heading = strip_tags($heading, "<i><em><del><b><strong>");
	
	$in['text'] = parseText($in['text']);
	$text = $in['text'];
	
	//heading img
	if($in['img'] == "/bin/img/icons/question_block_med.png") $in['img'] = '';
	
	$desc = strip_tags($heading);
	
	switch($in['type']) {
	case "":
		$errors[] = "No type given";
	break;
	case "text":
	
		// TEXT //
		
		if(!$heading) $errors[] = "No headline given";
		if(!$text) $errors[] = "No text given";
		if(!$in['text_type']) $errors[] = 'Please choose a <a href="#ttype" onclick="$(\'#notify\').slideUp();">Text Type</a>';
		elseif($in['text_type'] == "review") {
			$rating = $in['rating'];
			if($rating == "scale") $rating.= ":".$in['scale_value'];
			if($rating == "custom") {
				$in['custom_rating'] = trim($in['custom_rating']);
				if($in['custom_rating'] == "") $rating = "";
				else $rating.= ":".htmlentities($in['custom_rating'], ENT_QUOTES);
			}
		}
		$in['type2'] = $in['text_type'];
		
		$cont = $heading."|--|".$text."|--|".$in['text_type']."|--|".$rating;
		
		break;
	
	case "quote":
		
		// QUOTE //
		
		$quoter = parseText($in['quoter']);
		if(!$text || !$quoter) $errors[] = "Please input both a quote and quoter";
		
		//$desc = preg_replace("/ ?\[cite[^\[]+\[\/cite\]/", "", $quoter);
		$desc = $text;
		$desc = bb2html($desc);
		$desc = strip_tags($desc);
		if(strlen($desc) > 58) $desc = substr($desc, 0, 55)."...";
		
		$cont = "|--|".$text."|--|".$quoter;
		
		break;
		
	case "link":
		
		// LINK //
		
		$url = trim($in['link_url']);
		if($url == "http://") unset($url);
		if(!$url) $errors[] = "No URL given";
		else {
			$url = preg_replace("@http://(www.)?videogam.in/?@", "/", $url);
			if(!preg_match("@^/|(http)@", $url)) $errors[] = "Invalid URL. It should be either a http:// link or an internal videogam.in link beginning with the '/' character.";
		}
		
		if(!$heading) $errors[] = "No Link Text given";
		
		$cont = $heading."|--|".$text."|--|".$url;
	break;
	case "image":
	
		// IMAGE(S) //
		
		if(!$heading) $errors[] = "No Media Description given";
		if(!$in['media_category_id']) $errors[] = "No Media Type selected";
		
		$in['description'] = ($in['description'] ? $in['description'] : $heading);
		
		$sname = trim($in['source_name']);
		if($in['source_url'] == "http://") $in['source_url'] = "";
		if($surl = trim($in['source_url'])) {
			if(!preg_match("/^http/", $surl)) $errors[] = "Invalid Source URL; It must begin with http://.";
		}
		
		//check for uploads
		$dir = "/media/".substr($in['sessid'], 0, 4)."/".substr($in['sessid'], 4);
		if(!is_dir($_SERVER['DOCUMENT_ROOT'].$dir)) $errors[] = "No image files found.";
		$files = scandir($_SERVER['DOCUMENT_ROOT'].$dir);
		if(count($files) < 3) $errors[] = "No image files found.";
		
		$query = "SELECT * FROM posts LEFT JOIN media_files USING (nid) WHERE `session_id`='".$in['sessid']."'";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		$filenum = 0;
		$files = array();
		while($row = mysqli_fetch_assoc($res)) {
			$files[$filenum] = array("file" => $row['file'], "caption" => $row['caption']);
			$filenum++;
		}
		if($filenum < 1) $errors[] = "No image files found.";
		elseif($filenum > 1) {
			
			//it's a gallery
			$in['type'] = "gallery";
			$thumbs = trim($in['image']['thumbs']);
			if(!is_numeric($thumbs)) {
				$thumbs = 1;
			}
			$cont = $heading."|--|".$text."|--|".$dir."|--|".$in['media_category_id']."|--|".$surl."|--|".$sname;
			
		} else {
			
			//it's a single image
			$cont = $heading."|--|".$text."|--|".$dir."/".$files[0]['file']."|--|".$in['media_category_id']."|--|".$surl."|--|".$sname;
			
		}
		
		$imgfilenum = $filenum;
		$imgdir = $dir;
		
	break;
	case "video":
		
		// VIDEO //
		
		if(!$heading) $desc = "A Video";
		
		$url = trim($in['video_url']);
		if($url == "http://") unset($url);
		if(substr($url, 0, 4) != "http") $errors[] = "Your video url must be an http:// link.";
		if(!$url) $errors[] = "No video URL given.";
		$code = trim($in['video_code']);
		if(!$code) $errors[] = "No embed code given";
		
		if($_FILES['video_tn']['name']) {
			$tn = uploadVideoThumb($_FILES['video_tn']);
			if($tn) $in['video_thumbnail'] = $tn;
		}
		
		$cont = $heading."|--|".$text."|--|".$code."|--|".$url."|--|".$in['video_thumbnail'];
		
	break;
	case "audio":
		
		// AUDIO //
		
		if(!$heading) $errors[] = "No Headline / Track Description given";
		
		if(!$_FILES['audio_upload']['name'] && !$in['file']) $errors[] = "No source file uploaded";
		if($_FILES['audio_upload']['name']){
			$ext = substr($_FILES['audio_upload']['name'], -3, 3);
			if($ext != "mp3") $errors[] = "Only MP3 files allowed";
			else {
				$tmp_name = $_FILES['audio_upload']['tmp_name'];
				$name = $_FILES['audio_upload']['name'];
				$name = preg_replace("/[^a-z0-9\._-]/i", "", $name);
				if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/uploads/audio/$name")) $name = rand(0,999).$name;
				if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/uploads/audio/$name")) $name = rand(0,999).$name;
				if(!move_uploaded_file($tmp_name, $_SERVER['DOCUMENT_ROOT']."/bin/uploads/audio/$name")) {
					$errors[] = "Couldn't process upload ($tmp_name)";
				}
				$in['file'] = "/bin/uploads/audio/".$name;
			}
		}
		
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].$in['file'])) $errors[] = "Couldn't find the file: ".$in['file'];
		
		$cont = $heading."|--|".$text."|--|".$in['file']."|--|".$in['audio_type']."|--|".implode("|", $in['audio_trackid'])."|--|";
		
	break;
	default:
		$errors[] = "No type given";
	}
	
	//Process the data & insert/update db
	
	$temp = "temp-".$sessid;
	
	$in['description'] = trim($in['description']);
	if(!$in['description']) $in['description'] = ($postdat->description && $postdat->description != $temp ? $postdat->description : $desc);
	$in['description'] = bb2html($in['description'], "minimal");
	$in['description'] = strip_tags($in['description']);
	
	// check/make permalink
	$in['permalink'] = trim($in['permalink']);
	if(!$in['permalink']) {
		$q = "SELECT permalink FROM posts WHERE session_id = '$sessid' LIMIT 1";
		if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) $in['permalink'] = $dat->permalink;
	}
	if(!$in['permalink'] || $in['permalink'] == $temp) $in['permalink'] = $in['description'];
	$in['permalink'] = makePermalink($in['permalink']);
	
	if($postdat) {
		$nid = $postdat->nid;
		$dt = $postdat->datetime;
		$q = "UPDATE posts SET 
			`description` = '$temp',
			`permalink` = '$temp',
			`content` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $cont)."', 
			`type` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['type'])."', 
			`type2` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['type2'])."', 
			`category` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['category'])."',
			`privacy` = '".($in['privacy'] ? mysqli_real_escape_string($GLOBALS['db']['link'], $in['privacy']) : "public")."',
			`unpublished` = '1',
			`no_home` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['no_home'])."',
			`img` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['img'])."'
			WHERE session_id='$sessid' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Critical error: Couldn't update entry on posts database.";
	} else {
		$dt = date("Y-m-d H:i:s");
		$q = sprintf("INSERT INTO posts 
			(`session_id`, `description`, `permalink`, `content`, `usrid`, `type`, `type2`, `category`, `privacy`, `unpublished`, `no_home`, `img`, `datetime`) VALUES 
			('$sessid', '$temp', '$temp', '%s', '$usrid', '%s', '%s', '%s', 'public', '1', '%s', '%s', '$dt');",
			mysqli_real_escape_string($GLOBALS['db']['link'], $cont),
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['type']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['type2']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['category']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['no_home']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['img']));
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Critical error: Couldn't add entry to posts database.";
	}
	
	if(!$nid) {
		$q = "SELECT nid FROM posts WHERE session_id = '$sessid' LIMIT 1";
		$postdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
		$nid = $postdat->nid;
	}
	
	//check if url already exists for this date
	$q = "SELECT * FROM posts WHERE permalink='".$in['permalink']."' AND datetime LIKE '".substr($dt, 0, 10)."%' AND session_id != '$sessid' LIMIT 1";
	if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) {
		$in['permalink'].= rand(0, 999);
		$q = "UPDATE posts SET `permalink` = '".$in['permalink']."' WHERE session_id='$sessid' LIMIT 1";
		mysqli_query($GLOBALS['db']['link'], $q);
	}
	
	//preview
	if($_POST['submit_action'] == "preview") {
		
		//get nid
		$q = "SELECT nid FROM posts WHERE session_id = '$sessid' LIMIT 1";
		$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
		$nid = $dat->nid;
		
		$page->css = array_diff($page->css, array("posts_form.css"));
		$page->freestyle = '
			#togglepreview A { font-size:14px; font-weight:bold; }
			.arrow-toggle-on { text-decoration:none; color:black; }
		';
		$page->header();
		?>
		<h1>Preview</h1>
		<p style="font-size:14px; font-weight:bold;">
			<a href="#togglepreview" class="arrow-toggle arrow-toggle-on" onclick="togglePreview();">List Item</a> &nbsp; 
			<a href="#togglepreview" class="arrow-toggle" onclick="togglePreview();">Full Article</a>
		</p>
		<div style="margin:10px 330px 0 0;">
			<div class="news newslist">
				<dl>
					<dd class="listitem">
						<?=$posts->item(array("nid" => $nid, "session_id" => $sessid, "type" => $in['type'], "content" => $cont), "item")?>
					</dd>
				</dl>
			</div>
			<div class="news" style="display:none;">
				<?=$posts->item(array("nid" => $nid, "session_id" => $sessid, "type" => $in['type'], "content" => $cont), "article")?>
			</div>
		</div>
		<?
		$page->footer();
		exit;
	
	} elseif($_POST['submit_action'] == "draft") {
		$draft = true;
	}
	
	//forum topic
	if($in['category'] == "forum" && !$errors && !$draft) {
		
		require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/class.forums.php");
		$forum = new forum;
		
		if(!$in['fid']) $in['fid'] = 5; //Congenialtalia
		
		//if it's going in a forum, inherit invisible & close values
		$q = "SELECT invisible, closed FROM forums WHERE fid='".$in['fid']."' LIMIT 1";
		$fdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
		
		$tid = mysqlNextAutoIncrement("forums_topics");
		$q = sprintf("INSERT INTO `forums_topics` (`type`,`title`,`usrid`,`posts`,`created`,`last_post`,`last_post_usrid`,`invisible`,`closed`) VALUES 
			('forum','%s','$usrid','$dt','$dt','$usrid','1','".$fdat->invisible."','".$fdat->closed."')",
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['description']));
		$res = mysqli_query($GLOBALS['db']['link'], $q);
		if(!$res) $errors[] = "Couldn't insert into forums topics table";
		else {
			
			$message = '<div class="news">[newsitem='.$in['type'].']'.$cont.'[/newsitem]</div>';
			
			$query = sprintf("INSERT INTO forums_posts (tid, usrid, posted, message, ip) VALUES 
				('$tid','$usrid','$dt','%s','".$_SERVER['REMOTE_ADDR']."')",
				mysqli_real_escape_string($GLOBALS['db']['link'], $message));
			$res = mysqli_query($GLOBALS['db']['link'], $query);
			if(!$res) die("Error: Couldn't post into forums posts table; ".mysqli_error($GLOBALS['db']['link']));
			
			$forum->updatePosts($tid);
			
			$q = "DELETE FROM posts WHERE session_id='$sessid' LIMIT 1";
			mysqli_query($GLOBALS['db']['link'], $q);
			
			header("Location: /forums/?tid=".$tid);
			exit;
			
		}
		
	}
	
	if(!$draft && !$errors) {
		
		//FINAL PROCESSING
		
		if($in['unpublished'] && strstr($postdat->options, "not_yet_published")){
			$in['options'][] = "not_yet_published";
		}
		
		if(!$in['unpublished'] && strstr($postdat->options, "not_yet_published")){
			
			// it was never published,
			// so set the post date to now
			
			$dt = date("Y-m-d H:i:s");
			$tm = ", `datetime` = '$dt'";
			
			$in['options'] = array_diff($in['options'], array("not_yet_published"));
			
		}
		
		if($in['options']) $opts = implode(" ", $in['options']);
		
		$q = "UPDATE posts SET 
			`description` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['description'])."', 
			`permalink` = '".$in['permalink']."', 
			`unpublished` = '".$in['unpublished']."',
			`pending` = '$pending', 
			`archive` = '".$in['archive']."',
			`options` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $opts)."'
			".(!$in['unpublished'] ? ", last_edited = '".date("Y-m-d H:i:s")."'" : "")."
			".$tm." 
			WHERE session_id='$sessid' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't update posts database;".mysqli_error($GLOBALS['db']['link']);
				
		//image or gallery
		if($in['type'] == "gallery" || $in['type'] == "image") {
			$q = "UPDATE media SET `quantity`='$imgfilenum', `unpublished`='".($pending || $in['unpublished'] ? '1' : '')."' WHERE `directory`='$imgdir'";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't update media database table: ".mysqli_error($GLOBALS['db']['link']);
		}
		
		//album samples
		if($in['type'] == "audio"){
			
			$q = "DELETE FROM albums_samples WHERE nid='$nid'";
			mysqli_query($GLOBALS['db']['link'], $q);
			
			if(!$in['unpublished'] && $in['audio_trackid']){
				foreach($in['audio_trackid'] as $trackid){
					
					$q = "SELECT * FROM albums_samples WHERE track_id='$trackid' LIMIT 1";
					if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))){
						$errors[] = "There's already a sample for this track; removing track association";
						$in['audio_trackid'] = array_diff($in['audio_trackid'], array($trackid));
					} else {
						$q = "SELECT albumid FROM albums_tracks WHERE id='$trackid' LIMIT 1";
						if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))){
							$albumid = $dat->albumid;
							$_POST['autotags'][] = "AlbumID:".$albumid;
							$file = str_replace("/bin/uploads/audio/", "", $in['file']);
							if(!mysqli_query($GLOBALS['db']['link'], "INSERT INTO albums_samples (`albumid`,`track_id`,`file`,`length`,`usrid`,`nid`) VALUES ('$albumid', '$trackid', '$file', '".$in['audio_type']."', '$usrid', '$nid');")) $errors[] = "Couldn't insert into albums samples database";
						}
					}
				}
			}
			
		}
		
		//autotags
		$_tags = new tags;
		$_tags->subj = "posts_tags:nid:".$nid;
		$_tags->autoTag($cont);
		
		if($_POST['autotags']){
			foreach($_POST['autotags'] as $tag) {
				$tag = formatName($tag);
				if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM posts_tags WHERE nid='$nid' AND tag='".mysqli_real_escape_string($GLOBALS['db']['link'], $tag)."' LIMIT 1"))){
					$q = "INSERT INTO posts_tags (nid, tag, usrid) VALUES ('$nid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $tag)."', '$usrid');";
					mysqli_query($GLOBALS['db']['link'], $q);
				}
			}
		}
		
		$pe = $_POST['pe'];
		$q = "INSERT INTO posts_edits (nid, usrid, comments) VALUES ('$nid', '$usrid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $pe['comments'])."');";
		$posts_edits_id = mysqlNextAutoIncrement("posts_edits");
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $warnings[] = "Couldn't record this edit record into posts_edits table; All other data was saved, unless otherwise noted.";
		$fp = fopen($_SERVER['DOCUMENT_ROOT']."/posts/edits/".$posts_edits_id.".txt", "w");
		fwrite($fp, $cont);
		fclose($fp);
		
		if($pe['email']) {
			$usrdat = getUserDat($pe['email']);
			$to      = $usrdat->email;
			$subject = 'Your Videogam.in post has been moderated';
			$message = $usrdat->username.",\nYour Videogam.in post, \"".$in['description'].",\" has been edited by $usrname.".($pe['comments'] ? "\n\nComments:\n".$pe['comments'] : "")."\n\nSee your edited post here -> http://videogam.in/posts/?id=".$nid."\n\nSincerely,\nThe Friendly Videogam.in Post Edit Notfying Robot\n";
			$headers = 'From: noreply@videogam.in' . "\r\n" .
			    'X-Mailer: PHP/' . phpversion();
			mail($to, $subject, $message, $headers);
		}
		
		if($errors) return;
		
		//poll
		$poll = $_POST['poll'];
		$q = "SELECT * FROM posts_polls WHERE nid='$nid' LIMIT 1";
		if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) {
			if(!mysqli_query($GLOBALS['db']['link'], "UPDATE posts_polls SET `closed` = '".$poll['closed']."' WHERE nid='$nid' LIMIT 1")) $errors[] = "Couldn't update poll closed status";
		} else {
			$poll['question'] = bb2html($poll['question']);
			$poll['question'] = strip_tags($poll['question'], '<i>');
			if($poll['question']) {
				$poll['opts'] = trim($poll['opts']);
				if(!$poll['opts']) $errors[] = "No poll options given";
				else {
					$fopts = array();
					$opts = array();
					$opts = explode("\r\n", $poll['opts']);
					foreach($opts as $opt) {
						$opt = trim($opt);
						if($opt != "") $fopts[] = $opt;
					}
					if(count($fopts) > 1) {
						$foptsins = implode("|--|", $fopts);
						$foptsins = bb2html($foptsins);
						$foptsins = strip_tags($foptsins, '<i>');
						$q = sprintf("INSERT INTO posts_polls (nid, question, options, answer_type) VALUES ('$nid', '%s', '%s', '%s');",
							mysqli_real_escape_string($GLOBALS['db']['link'], $poll['question']),
							mysqli_real_escape_string($GLOBALS['db']['link'], $foptsins),
							mysqli_real_escape_string($GLOBALS['db']['link'], $poll['answer_type'])
						);
						if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't insert poll to DB; ".mysqli_error($GLOBALS['db']['link']);
					} else $errors[] = "Your poll needs more than one option";
				}
			}
		}
		
		//remove heading images
		if($in['remove_himg_big']) {
			$himg_big = $_SERVER['DOCUMENT_ROOT']."/posts/img/".$sessid."_big.jpg";
			if(file_exists($himg_big)) {
				@copy($himg_big, $_SERVER['DOCUMENT_ROOT']."/bin/deleted-files/posts--img--".$sessid."_big.jpg");
				if(!unlink($himg_big)) $errors[] = "Couldn't remove big heading image";
			}
		}
		$himg_tn = "/posts/img/".$sessid."_tn.png";
		if($in['himg_tn_source'] == "none" && file_exists($_SERVER['DOCUMENT_ROOT'].$himg_tn)) {
			@copy($_SERVER['DOCUMENT_ROOT'].$himg_tn, $_SERVER['DOCUMENT_ROOT']."/bin/deleted-files/posts--img--".$sessid."_tn.png");
			if(!unlink($_SERVER['DOCUMENT_ROOT'].$himg_tn)) $errors[] = "Couldn't remove thumbnail heading image";
		}
		
		if($errors) return;
		
		//badges
		$_badges = new badges;
		if($in['text_type'] == "review" && !$in['unpublished']){
			$_badges->earn(13, $postdat->usrid); //budding critic
		}
		$query = "SELECT type, rating, ratings, rating_weighted FROM posts WHERE usrid='$postdat->usrid' AND unpublished != '1' AND `pending` != '1'";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		if(mysqli_num_rows($res)){
			
			$_badges->earn(10, $postdat->usrid); //lightweight
			
			$goodposts = 0;
			$picposts = 0;
			$postedtypes = array();
			while($row = mysqli_fetch_assoc($res)){
				
				if($row['ratings'] > 4 && $row['rating'] >= 80) $goodposts++;
				
				if($row['type'] == "gallery") $row['type'] = "image";
				if($row['type'] == "image") $picposts++;
				if(!in_array($row['type'], $postedtypes)) $postedtypes[] = $row['type'];
			}
			if($goodposts >= 24){
				$_badges->earn(11, $postdat->usrid); //welterweight
			}
			if($goodposts >= 99){
				$_badges->earn(12, $postdat->usrid); //heavyweight
			}
			if(count($postedtypes) == 6){
				$_badges->earn(14, $postdat->usrid); //kungfu master (one of every type)
			}
			if($picposts >= 5){
				$_badges->earn(38, $postdat->usrid); //photo man
			}
		}
		
		$date = substr($dt, 0, 10);
		$date = str_replace("-", "/", $date);
		
		$loc = "/".($in['category'] == "blog" ? "~$usrname/blog" : "posts")."/".$date."/".$in['permalink'];
		if($_SERVER['HTTP_HOST'] == "localhost") $loc = "/posts/handle.php?path=".$date."/".$in['permalink'];
		header("Location: ".$loc);
		exit;
		
	}

}

function makePermalink($p) {
	
	$p = trim($p);
	$p = strtolower($p);
	$p = str_replace(" ", "-", $p);
	$p = preg_replace("/\-+/", "-", $p);
	$p = preg_replace("/[^a-z0-9_-]+/i", "", $p);
	$p = substr($p, 0, 99);
	if($p == "") $p = "post";
	return $p;
	
}

function uploadVideoThumb($f) {
	
	//@var $f location of a file to handle
	//@ret final tn URL filename (ie /bin/img/uploads/posts/abc.jpg)
	
	$dir = "/bin/uploads/posts/";
	
	$handle = new Upload($f);
	if($handle->uploaded) {
		$handle->image_resize           = true;
		$handle->image_ratio_crop       = true;
		$handle->image_x                = 200;
		$handle->image_y                = 130;
		$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir);
		if ($handle->processed) {
			
			return $dir.$handle->file_dst_name;
			
		} else return FALSE;
	} else return FALSE;

	
}

?>