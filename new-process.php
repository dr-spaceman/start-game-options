<?

use Verot\Upload;

if($_POST['preview']) {
	
	$_POST['content'] = str_replace("[AMP]", "&", $_POST['content']);
	$_POST['content'] = str_replace("[PLUS]", "+", $_POST['content']);
	echo '<div style="margin:0 0 5px; padding:5px; background-color:#EEE; color:#666; cursor:pointer;" onclick="$(this).next().slideToggle(); $(this).children(\'span\').toggleClass(\'arrow-toggle-on\');"><span class="arrow-toggle arrow-toggle-on">News List</span></div><div>'.($_POST['type'] == "audio" ? '<span class="warn">Audio player will not show properly on preview</span><br/><br/>' : '').$news->item(array("type" => $_POST['type'], "content" => $_POST['content']), "item").'</div>';
	echo '<div style="margin:10px 0 5px; padding:5px; background-color:#EEE; color:#666; cursor:pointer;" onclick="$(this).next().slideToggle(); $(this).children(\'span\').toggleClass(\'arrow-toggle-on\');"><span class="arrow-toggle arrow-toggle-on">Full Article</span></div><div>'.($_POST['type'] == "audio" ? '<span class="warn">Audio player will not show properly on preview</span><br/><br/>' : '').$news->item(array("type" => $_POST['type'], "content" => $_POST['content']), "article").'</div>';
	exit;
	
}

if($_GET['action'] == "upload_img") {
	use Vgsite\Page;
	echo Page::HTML_TAG;
	?>
	<body style="margin:0; padding:0; font-size:13px; font-family:Arial; background-color:#F5F5F5;">
	<form action="new-process.php?action=upload_img&sessid=<?=$_GET['sessid']?>" method="post" enctype="multipart/form-data">
		<?
		if($del = $_POST['del']) {
			$ext = substr($del, -3, 3);
			if(!unlink($_SERVER['DOCUMENT_ROOT']."/bin/temp/$del")) echo "Couldn't delete $del";
			@unlink($_SERVER['DOCUMENT_ROOT']."/bin/temp/".substr($del, 0, 4)."_561x.".$ext);
			@unlink($_SERVER['DOCUMENT_ROOT']."/bin/temp/".substr($del, 0, 4)."_350x.".$ext);
		}
		if($_FILES['file']['name']) {
			?><div id="upload-results"><?
			$ext = substr($_FILES['file']['name'], -3, 3);
			$exts = array("jpg","JPG","gif","GIF","png","PNG");
			if(!in_array($ext, $exts)) echo('Error: use only images that are JPG, GIF, or PNG. ');
			else {
				$handle = new Upload($_FILES['file']);
				if ($handle->uploaded) {
					
					//preview img
					$handle->image_convert          = 'jpg';
					$handle->file_new_name_body     = $_GET['sessid']."_preview";
					$handle->file_overwrite         = true;
					$handle->file_auto_rename       = false;
					$handle->image_resize           = true;
					$handle->image_ratio_no_zoom_in = true;
					$handle->image_x                = 561;
					$handle->image_y                = 800;
					$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/temp/");
					if(!$handle->processed) echo "Upload Error (preview img): ".$handle->error;
					
					$handle->file_new_name_body     = $_GET['sessid'];
					$handle->file_overwrite         = true;
					$handle->file_auto_rename       = false;
					$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/temp/");
					if ($handle->processed) {
						$file = $handle->file_dst_name;
						echo 'Your <a href="/bin/temp/'.$file.'" target="_blank" class="arrow-link">image</a> has been successfully uploaded!';
						list($width, $height, $type, $attr) = getimagesize($handle->file_dst_pathname);
						if($width > 561 || $height > 800) {
							$handle->file_new_name_body     = $_GET['sessid']."_561x";
							$handle->file_overwrite         = true;
							$handle->file_auto_rename       = false;
							$handle->image_resize           = true;
							$handle->image_ratio_no_zoom_in = true;
							$handle->image_x                = 561;
							$handle->image_y                = 800;
							$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/temp/");
						}
						if($width > 350 || $height > 500) {
							$handle->file_new_name_body     = $_GET['sessid']."_350x";
							$handle->file_overwrite         = true;
							$handle->file_auto_rename       = false;
							$handle->image_resize           = true;
							$handle->image_ratio_no_zoom_in = true;
							$handle->image_x                = 350;
							$handle->image_y                = 500;
							$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/temp/");
						}
					} else echo "Upload Error: ".$handle->error;
				} else echo "Upload Error: ".$handle->error;
			}
			?>
			 <input type="button" value="Delete & Re-upload" onclick="alert('A new upload might not be reflected in the preview frame because of a browser cache. Instead, click the result link after uploading to see the image.'); document.getElementById('upload-form').style.display='block'; document.getElementById('upload-results').style.display='none';"/>
			</div>
			<?
			$uploaded = true;
		}
		?> 
		<div id="upload-form" <?=($uploaded ? ' style="display:none"' : '')?>>
			<input type="hidden" name="del" value="<?=$file?>"/>
			<input type="file" name="file"/> 
			<input type="submit" name="submit_upload" value="Upload &gt;"/> 
			&nbsp; <span style="color:#808080">Process upload before previewing or submitting</span>
		</div>
	</form>
	</body>
	</html>
	<?
}

if($_POST['submit_new']) {
	
	////////////////
	// SUBMIT NEW //
	////////////////
	
	switch($in['type']) {
	case "":
		$errors[] = "No type given";
	break;
	case "text":
	
		// TEXT //
		
		$heading = trim($in['text']['heading']);
		$heading = bb2html($heading);
		$desc = strip_tags($heading);
		$heading = strip_tags($heading, "<i><del>");
		if(!$heading) $errors[] = "No heading given";
		
		$text = trim($in['text']['text']);
		if(!$text) $errors[] = "No body text given";
		
		$expanded = trim($in['text']['expanded_text']);
		
		$cont = $heading."|--|".$text."|--|".$expanded;
	break;
	case "quote":
		
		// QUOTE //
		
		$quote = trim($in['quote']['quote']);
		$quoter = trim($in['quote']['quoter']);
		if(!$quote || !$quoter) $errors[] = "Please input both a quote and quoter";
		
		$desc = "Quote from ".bb2html($quoter);
		$desc = strip_tags($desc);
		
		$cont = "|--||--|".$quote."|--|".$quoter;
	break;
	case "link":
		
		// LINK //
		
		$url = trim($in['link']['url']);
		if($url == "http://") unset($url);
		if(!$url) $errors[] = "No URL given";
		else {
			$url = preg_replace("@http://videogam.in/?@", "/", $url);
			if(!preg_match("@^/|(http://)@", $url)) $errors[] = "Invalid URL. It should be either a http:// link or an internal videogam.in link beginning with the '/' character.";
		}
		
		$heading = trim($in['link']['heading']);
		if(!$heading) $errors[] = "No Link Text given";
		$desc = strip_tags($heading);
		
		$text = trim($in['link']['text']);
		
		$cont = $heading."|--|".$text."|--|".$url;
	break;
	case "image":
	
		// IMAGE //
		
		$caption = trim($in['image']['caption']);
		$caption = bb2html($caption);
		$caption = strip_tags($caption);
		$caption = htmlSC($caption);
		if(!$caption) $errors[] = "No caption given";
		$desc = $caption;
		
		$heading = trim($in['image']['heading']);
		
		$text = trim($in['image']['text']);
		
		if(!$errors) {
			@unlink($_SERVER['DOCUMENT_ROOT']."/bin/temp/".$in['sessid']."_preview.jpg");
			
			if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/temp/".$in['sessid'].".jpg")) $ext = "jpg";
			elseif(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/temp/".$in['sessid'].".gif")) $ext = "gif";
			elseif(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/temp/".$in['sessid'].".png")) $ext = "png";
			else $errors[] = "Couldn't find the uploaded image";
			
			if(!$errors) {
				if(!rename($_SERVER['DOCUMENT_ROOT']."/bin/temp/".$in['sessid'].".".$ext, $_SERVER['DOCUMENT_ROOT']."/bin/uploads/news/".$in['sessid'].".".$ext)) {
					$errors[] = "Couldn't relocate image file";
				}
				if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/temp/".$in['sessid']."_561x.".$ext)) {
					if(!rename($_SERVER['DOCUMENT_ROOT']."/bin/temp/".$in['sessid']."_561x.".$ext, $_SERVER['DOCUMENT_ROOT']."/bin/uploads/news/".$in['sessid']."_561x.".$ext)) {
						$errors[] = "Couldn't relocate 561x image file";
					}
				}
				if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/temp/".$in['sessid']."_350x.".$ext)) {
					if(!rename($_SERVER['DOCUMENT_ROOT']."/bin/temp/".$in['sessid']."_350x.".$ext, $_SERVER['DOCUMENT_ROOT']."/bin/uploads/news/".$in['sessid']."_350x.".$ext)) {
						$errors[] = "Couldn't relocate 350x image file";
					}
				}
			}
		}
		
		$cont = $heading."|--|".$text."|--|/bin/uploads/news/".$in['sessid'].".".$ext."|--|".$caption;
		
	break;
	case "gallery":
		
		// GALLERY //
		
		$heading = trim($in['gallery']['heading']);
		if($heading) $desc = strip_tags($heading);
		else {
			$q = "SELECT `description` FROM media WHERE media_id='".$in['gallery']['dir']."' LIMIT 1";
			$mdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
			$desc = strip_tags($mdat->description);
		}
		
		$text = trim($in['gallery']['text']);
		
		$thumbs = trim($in['gallery']['thumbs']);
		if(!is_numeric($thumbs)) {
			$thumbs = 1;
		}
		
		$cont = $heading."|--|".$text."|--|".$in['gallery']['dir']."|--|".$thumbs."|--|".$in['gallery']['link_to'];
		
	break;
	case "video":
		
		// VIDEO //
		
		$heading = trim($in['video']['heading']);
		if($heading) $desc = strip_tags($heading);
		else $desc = "Video";
		
		$text = trim($in['video']['text']);
		
		$code = trim($in['video']['code']);
		if(!$code) $errors[] = "No embed code given";
		
		$cont = $heading."|--|".$text."|--|".$code;
		
	break;
	case "audio":
		
		// AUDIO //
		
		$heading = trim($in['audio']['heading']);
		$heading = bb2html($heading);
		$heading = strip_tags($heading, '<i><del>');
		if(!$heading) $errors[] = "No heading given";
		$desc = strip_tags($heading);
		
		$text = trim($in['audio']['text']);
		
		if($in['audio']['source'] == "sample") {
			$file = $in['audio']['source_sample'];
		} else {
			if(!$_FILES['audio_upload']['name']) $errors[] = "No source file given";
			else {
				$ext = substr($_FILES['audio_upload']['name'], -3, 3);
				if($ext != "mp3") $errors[] = "Only MP3 files allowed";
				else {
					$tmp_name = $_FILES['audio_upload']['tmp_name'];
					$name = $_FILES['audio_upload']['name'];
					$name = preg_replace("/[^a-z0-9\._-]/i", "", $name);
					if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/uploads/audio/$name")) $name = rand(0,999).$name;
					if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/uploads/audio/$name")) $name = rand(0,999).$name;
					if(!move_uploaded_file($tmp_name, $_SERVER['DOCUMENT_ROOT']."/bin/uploads/audio/$name")) {
						$errors[] = "Couldn't process upload";
					}
				}
			}
		}
		
		$cont = $heading."|--|".$text."|--|".$file;
		
	break;
	}
	
	if(!$in['post_to']) {
		$in['post_to'][] = "blog";
		$warnings[] = "You didn't select an option to post to so your blog was selected by default. (You have to post this thing <i>somewhere</i>!)";
	} elseif($in['post_to']['groups']) {
		$groupsi = " ".implode(" , ", $in['post_to']['groups'])." ";
	}
	
	if(!$errors) {
		
		//forum topic
		if($in['post_to']['forums']) {
			
			require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/class.forum.php");
			$forum = new forum;
			
			$description = $_POST['description'];
			$datetime = date('Y-m-d H:i:s');
			
			//if it's going in a forum, inherit invisible & close values
			if($tags) {
				$q = "SELECT invisible, closed FROM forums WHERE included_tags='".$in['fid']."' LIMIT 1";
				$fdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
			}
			
			$tid = mysqlNextAutoIncrement("forums_topics");
			$q = sprintf("INSERT INTO `forums_topics` (`type`,`title`,`usrid`,`created`,`last_post`,`last_post_usrid`,`invisible`,`closed`) VALUES 
				('$type','%s','$usrid','$datetime','$datetime','$usrid','".$fdat->invisible."','".$fdat->closed."')",
				mysqli_real_escape_string($GLOBALS['db']['link'], $desc));
			$res = mysqli_query($GLOBALS['db']['link'], $q);
			if(!$res) $errors[] = "Couldn't insert into forums topics table";
			else {
				
				$cont = $forum->parseForForumPost($cont, $tid);
				$message = '[newsitem='.$in['type'].']'.$cont.'[/newsitem]';
				
				$query = sprintf("INSERT INTO forums_posts (tid,usrid,posted,message,ip) VALUES 
					('$tid','$usrid','$datetime','%s','".$_SERVER['REMOTE_ADDR']."')",
					mysqli_real_escape_string($GLOBALS['db']['link'], $message));
				$res = mysqli_query($GLOBALS['db']['link'], $query);
				if(!$res) {
					die("Error: couldn't post into forums posts table");
				} else {
					
					//insert tags
					$tags = array();
					$tagsi = array("forum:".$in['fid']);
					if($in['tags'] = trim($in['tags'])) $tags = explode("\r\n", $in['tags']);
					if($tags) {
						foreach($tags as $tag) {
							$tag = trim($tag);
							if($tag) {
								$xx = substr($tag, 0, 4);
								if($xx != "gid:" && $xx != "pid:") $tag = $news->convertTag($tag);
							}
							if(!in_array($tag, $tagsi)) $tagsi[] = $tag;
						}
					}
					
					if($tagsi) {
						$q = "INSERT INTO forums_tags (`tid`, `tag`) VALUES ";
						foreach($tagsi as $t) {
							$q.= "('$tid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $t)."'),";
						}
						$q = substr($q, 0, -1).";";
						if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't insert tags into database; ".mysqli_error($GLOBALS['db']['link']);
					}
					
					$forum->updatePosts($tid);
					$redirect_to = "/forums/?tid=".$tid;
					
					header("Location: /forums/?tid=".$tid."&newpost=true");
					exit;
				}
			}
			
		} else {
		
			$x = formatName($desc);
			$x[1] = substr($x[1], 0, 45);
			
			$dt = date("Y-m-d H:i:s");
			$date = date("Y/m/d");
			
			//check if url already exists for this date
			$q = "SELECT * FROM news WHERE description_url='$x[1]' AND datetime LIKE '".substr($dt, 0, 10)."%' LIMIT 1";
			if(mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) {
				$warnings[] = "The default URL, '$x[1]' already exists for this date. A random number was appended to the end of the URL as to not conflict with the current one. You may want to input a new one below.";
				$x[1] = substr($x[1], 0, 43).rand(10,99);
			}
			
			if($in['options']) $opts = implode(" ", $in['options']);
			
			$nid = mysqlNextAutoIncrement("news");
			$q = "INSERT INTO news (`description`,`description_url`,`content`,`usrid`,`type`,`public`,`blog`,`groups`,`options`,`datetime`) VALUES 
				('".mysqli_real_escape_string($GLOBALS['db']['link'], $x[0])."', '$x[1]', '".mysqli_real_escape_string($GLOBALS['db']['link'], $cont)."', '$usrid', '".$in['type']."', '".$in['post_to']['public']."', '".$in['post_to']['blog']."', '$groupsi', '$opts', '$dt');";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't insert into database; ".mysqli_error($GLOBALS['db']['link']);
			else {
			
				//tags
				$tags = array();
				$tagsi = array();
				if($in['tags'] = trim($in['tags'])) $tags = explode("\r\n", $in['tags']);
				if($tags2 = $news->extractTags($cont)) $tags = array_merge($tags, $tags2);
				if($tags) {
					foreach($tags as $tag) {
						$tag = trim($tag);
						if($tag) {
							$xx = substr($tag, 0, 4);
							if($xx != "gid:" && $xx != "pid:") $tag = $news->convertTag($tag);
						}
						if(!in_array($tag, $tagsi)) $tagsi[] = $tag;
						
						$xx = explode(":", $tag);
						if($xx[0] == "gid") {
							$appends[] = $xx[1];
						}
						
					}
				}
				
				if($tagsi) {
					$q = "INSERT INTO news_tags (`nid`, `tag`) VALUES ";
					foreach($tagsi as $t) {
						$q.= "('$nid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $t)."'),";
					}
					$q = substr($q, 0, -1).";";
					if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't insert tags into database; ".mysqli_error($GLOBALS['db']['link']);
				}
				
				//heading img
				if($_FILES['headline_img_'.$in['type']]['name']) {
					$ext = substr($_FILES['headline_img_'.$in['type']]['name'], -3, 3);
					$exts = array("jpg","JPG","gif","GIF","png","PNG");
					if(!in_array($ext, $exts)) $warnings[] = 'Headline image can only be JPG, GIF, or PNG. The image was removed, but you can edit this item later and upload a new image.';
					else {
						$handle = new Upload($_FILES['headline_img_'.$in['type']]);
						if ($handle->uploaded) {
							$handle->file_new_name_ext      = 'png';
							$handle->image_convert          = 'png';
							$handle->file_new_name_body     = "headingimg_".$nid;
							$handle->file_overwrite         = true;
							$handle->file_auto_rename       = false;
							$handle->image_resize           = true;
							$handle->image_ratio_crop       = true;
							$handle->image_x                = 100;
							$handle->image_y                = 100;
							$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/img/news/");
							if(!$handle->processed) $warnings[] = "Upload Error (heading img): ".$handle->error;
						}
					}
				}
				
				$page->title = "Videogam.in / News / Submit ".$in['type']." item for processing";
				$page->header();
				?>
				<h2>One more step...</h2>
				Please confirm this news item's description and choose an appropriate URL:
				<form action="new.php" method="post">
					<input type="hidden" name="in[nid]" value="<?=$nid?>"/>
					<input type="hidden" name="in[date]" value="<?=$date?>"/>
					<input type="hidden" name="in[type]" value="<?=$in['type']?>"/>
					<textarea name="in[cont]" style="display:none"><?=$cont?></textarea>
					<p><input type="text" name="in[description]" value="<?=$x[0]?>" size="70" style="font-size:18px; font-family:Arial;"/></p>
					<p>
						<span style="font-size:18px">http://videogam.in/news/<?=$date?>/</span><input type="text" name="in[description_url]" value="<?=$x[1]?>" size="50" maxlength="45" style="padding:0 0 2px 0; font-family:Arial; font-size:18px; color:#666; border-width:0 0 1px; border-style:solid; border-color:#888;"/><br/>
						<span style="color:#888">Use only lowercase letters, numbers, _, and -</span>
					</p>
					
					<p><fieldset style="display:inline">
						<legend>Comment Options</legend>
						<label><input type="radio" name="in[options][comments]" value="" checked="checked"/> Anybody can comment</label>
						<p style="margin:3px 0 0;"><label><input type="radio" name="in[options][comments]" value="comments_disabled"/> Nobody can comment</label></p>
						<?=(!$in['post_to']['public'] && $in['post_to']['blog'] ? '<p style="margin:3px 0 0;"><label><input type="radio" name="in[options][comments]" value="comments_friends"/> Only my friends can comment</label></p>' : '')?>
					</fieldset></p>
					
					<?
					//append to gamepage options
					if($appends && $in['type'] == "quote") {
						?>
						<p><fieldset style="display:inline">
							<legend>Permanently add to game pages?</legend>
							Add this quote permanently to the following game pages (under "Hearsay"):
							<?
							foreach($appends as $gid) {
								echo '<label style="display:block; margin:5px 0 0;"><input type="checkbox" name="gappend[quote][]" value="'.$gid.'"/> '.outputTag("gid:".$gid).'</label>';
							}
							?>
						</fieldset></p>
						<?
					} elseif($appends && $in['type'] == "link") {
						?>
						<p><fieldset>
							<legend>Permanently add to game pages?</legend>
							Add this link permanently to the following game pages:
							<?
							foreach($appends as $gid) {
								echo '<label style="display:block; margin:5px 0 0;"><input type="checkbox" name="gappend[link][]" value="'.$gid.'"/> '.outputTag("gid:".$gid).'</label>';
							}
							?>
						</fieldset></p>
						<?
					}
					?>
					
					<p><input type="submit" name="process_new" value="Publish It"/></p>
					
				</form>
				<?
				$page->footer();
				exit;
				
			}
		}
	}
	
}

if($_POST['process_new']) {
	
	$in['description'] = trim($in['description']);
	$in['description'] = strip_tags($in['description']);
	$in['description_url'] = preg_replace("/[^a-z0-9-_]+/i", "", $in['description_url']);
	$in['description_url'] = strtolower($in['description_url']);
	if(!$in['description']) $errors[] = "No description given";
	if(!$in['description_url']) $errors[] = "No description URL given";
	
	if($_POST['gappend']) {
		$cont = explode("|--|", $in['cont']);
		if($_POST['gappend']['quote']) {
			foreach($_POST['gappend']['quote'] as $gid) {
				$q = sprintf("INSERT INTO games_quotes (gid, quote, cut_off, quoter, usrid, datetime) VALUES 
					('$gid', '%s', '1', '%s', '$usrid', '".date("Y-m-d H:i:s")."');",
					mysqli_real_escape_string($GLOBALS['db']['link'], $cont[0]),
					mysqli_real_escape_string($GLOBALS['db']['link'], $cont[1]));
				if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't insert into games quotes db; ".mysqli_error($GLOBALS['db']['link']);
			}
		}
		if($_POST['gappend']['link']) {
			foreach($_POST['gappend']['link'] as $gid) {
				$q = sprintf("INSERT INTO games_links (gid, url, site_name, description, usrid, datetime) VALUES 
					('$gid', '$cont[0]', '%s', '%s', '$usrid', '".date("Y-m-d H:i:s")."');",
					mysqli_real_escape_string($GLOBALS['db']['link'], $cont[1]),
					mysqli_real_escape_string($GLOBALS['db']['link'], $cont[2]));
				if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't insert into games links db; ".mysqli_error($GLOBALS['db']['link']);
			}
		}
	}
	
	//check if url already exists for this date
	$q = "SELECT * FROM news WHERE description_url='".$in['description_url']."' AND datetime LIKE '".str_replace("/", "-", $in['date'])."%' AND nid != '".$in['nid']."' LIMIT 1";
	if(mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) {
		$warnings[] = "The default URL, '".$in['description_url']."' already exists for this date. A random number was appended to the end of the URL as to not conflict with the current one. You may want to input a new one below.";
		$in['description_url'] .= substr($in['description_url'], 0, 43).rand(10,99);
	}
	
	if($errors || $warnings) {
		$page->title = "Videogam.in / News / Submit ".$in['type']." item for processing";
		$page->header();
		?>
		<h2>One more step...</h2>
		Please confirm this news item's description and choose an appropriate URL:
		<form action="new.php" method="post">
			<input type="hidden" name="in[nid]" value="<?=$in['nid']?>"/>
			<input type="hidden" name="in[date]" value="<?=$in['date']?>"/>
			<p><input type="text" name="in[description]" value="<?=$in['description']?>" size="70" style="font-size:18px; font-family:Arial;"/></p>
			<p>http://videogam.in/news/<input type="text" name="in[description_url]" value="<?=$in['description_url']?>" size="40" maxlength="35" style="border-width:0 0 1px; border-style:solid; border-color:#888;"/> <span style="color:#888">Use only lowercase letters, numbers, _, and -</span></p>
			<p><input type="submit" name="process_new" value="Publish It"/></p>
		</form>
		<?
		$page->footer();
		exit;
	}
	
	$q = "UPDATE news SET 
		description='".mysqli_real_escape_string($GLOBALS['db']['link'], $in['description'])."', 
		description_url='".mysqli_real_escape_string($GLOBALS['db']['link'], $in['description_url'])."', 
		`options` = '".implode(" ", $in['options'])."' 
		WHERE nid='".$in['nid']."' LIMIT 1";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't update database to reflect description and URL; ".mysqli_error($GLOBALS['db']['link']);
	else {
		header("Location: /news/".$in['date']."/".$in['description_url']);
		exit;
	}
	
}

?>