<?

require_once("../bin/php/page.php");

$in = $_POST['in'];
$step = $_POST['step'];
$editid = $_POST['editid'];
$action = $_POST['action'];

// asyncrequests

if($action == "check_title") {
	
	if(!$_POST['title']) die("No title given");
	list($title, $title_url) = formatName($_POST['title'], TRUE);
	
	$q = "SELECT * FROM games WHERE title='".mysql_real_escape_string($title)."' LIMIT 1";
	if(mysql_num_rows(mysql_query($q))) {
		?>
		<p class="warn">The game <i><?=$title?></i> is already in the database (see <?=reformatLinks("[[G||".$title."]]")?> coverage).<br/>
		<input type="button" value="Input another title" onclick="window.location='games-add.php';"/> or continue with the form to add it anyway.</p>
		<?
	}
	
	?>
	<p><b>Unique Title URL:</b> http://videogam.in/games/~<input type="text" name="in[title_url]" id="input-title-url" value="<?=$title_url?>" size="40"/></p>
	<?
	$q = "SELECT * FROM games WHERE title_url='$title_url' LIMIT 1";
	if(mysql_num_rows(mysql_query($q))) {
		?>
		<p class="warn">The default title URL (<?=$title_url?>) is already in use. It's required that this value be unique in order to add the game to the database.</p>
		<p><input type="button" value="Check title URL's uniqueness" onclick="checkTitleUrl(document.getElementById('input-title-url').value);"/><p>
		<p id="check-title-url-results"></p>
		<?
	} else {
		?>
		<input type="hidden" name="submitform" value="1"/>
		<p><input type="button" value="Continue &gt;" onclick="var oktosubmit = '1'; document.submittitleform.submit();" style="font-size:21px"/></p>
		<?
	}
	
	exit;
	
}

if($action == "check_title_url") {
	
	if(!$_POST['title_url']) die("No title url given");
	
	if(ereg("/[^a-zA-Z0-9-]/", $_POST['title_url'])) die("Illegal characters (use only letters, numbers, and -)");
	$q = "SELECT * FROM games WHERE title_url='".$_POST['title_url']."' LIMIT 1";
	if(mysql_num_rows(mysql_query($q))) echo "Already taken";
	else echo '<input type="button" value="Continue &gt;" onclick="var oktosubmit = \'1\'; document.submittitleform.submit();" style="font-size:21px"/>';
	
	exit;
	
}

if($action == "insert_series") {
	
	if(!$gid = $_POST['gid']) die("No game id given");
	if(!$series = $_POST['series']) die("No series given");
	$series = str_replace("[AMP]", "&", $series);
	$series = htmlentities($series, ENT_QUOTES);
	
	$q = "INSERT INTO games_series (gid, series) VALUES ('$gid', '$series');";
	if(!mysql_query($q)) die("bad");
	else echo $series;
	
	exit;
	
}

if($action == "delete_series") {
	
	if(!$gid = $_POST['gid']) die("No game id given");
	if(!$series = $_POST['series']) die("No series given");
	$series = str_replace("[AMP]", "&", $series);
	$series = htmlentities($series, ENT_QUOTES);
	
	$q = "DELETE FROM games_series WHERE gid='$gid' AND series='$series' LIMIT 1";
	if(!mysql_query($q)) die("Couldn't delete from database");
	else echo "ok";
	
	exit;
	
}

if($step && !$_POST['submitform']) {
	
	//user navigated here without submitting
	
	$q = "SELECT * FROM games WHERE gid='$editid' LIMIT 1";
	if(!$in = mysql_fetch_assoc(mysql_query($q))) {
		$errors[] = "Couldn't get data from database";
	}
	
	$x = $in['series'];
	$in['series'] = array();
	if(strstr($x, "||")) $in['series'] = explode("||", $x);
	else $in['series'][0] = $x;
	
	$dbdat = mysql_fetch_object(mysql_query($q));
	
	if($_POST['skipsubmit']) $step = $step + 1;
	
} else {
	
	if($step > 1) {
		
		// get $dbdat
		$q = "SELECT * FROM games WHERE gid='$editid' LIMIT 1";
		$dbdat = mysql_fetch_object(mysql_query($q));
		
	}
	
	if($step == "0") {
		
		// TITLE //
		if(!$in['title']) die("No title");
		list($title, $title_url) = formatName($in['title']);
		if(!$title_url) die("No title URL");
		
		//exists?
		$q = "SELECT * FROM games WHERE title_url='$title_url' LIMIT 1";
		if(!mysql_num_rows(mysql_query($q))) {
			$now = date("Y-m-d H:i:s");
			$q2 = "INSERT INTO games (title, title_url, creator, contributors, created, modified) VALUES 
			('".mysql_real_escape_string($title)."', '$title_url', '$usrid', 'usrid:$usrid', '$now', '$now');";
			if(!mysql_query($q2)) $errors[] = "Couldn't insert into db; ".mysql_error();
			else {
				if(!$dbdat = mysql_fetch_object(mysql_query($q))) die("Couldn't get game id");
				$editid = $dbdat->gid;
				
				//dir
				if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/games/files/'.$editid.'/')) {
					if(!mkdir($_SERVER['DOCUMENT_ROOT'].'/games/files/'.$editid.'/', 0777)) {
						$errors[] = "Couldn't create files directory which is neccessary for uploads. Manually create /games/files/".$editid."/";
					}
				}
				if(!is_writable($_SERVER['DOCUMENT_ROOT'].'/games/files/'.$id.'/')) {
					if(!chmod($_SERVER['DOCUMENT_ROOT'].'/games/files/'.$id.'/', 0777)) {
						$errors[] = "Couldn't make this game's files dir writable. Manually CHMOD /games/files/".$editid."/ to 777.";
					}
				}
				
				$step = 1;
			}
		} else {
			$errors[] = "Title URL '".$title_url."' is already taken";
		}
		
	} elseif($step == "1") {
		
		// SYNOPSIS //
		
		if($in['synopsis'] = mysql_real_escape_string($in['synopsis'])) {
			$q = "UPDATE games SET synopsis='".$in['synopsis']."' WHERE gid='$editid' LIMIT 1";
			if(!mysql_query($q)) $errors[] = "Couldn't update database";
			else $step = 2;
		} else $step = 2;
		
	} elseif($step == "2") {
		
		// GENERAL //
		
		$in['developer'] = htmlentities($in['developer'], ENT_QUOTES);
		$in['developer'] = preg_replace("%( ?/ ?)%", "/", $in['developer']);
		
		$q = sprintf("UPDATE games SET 
			developer='%s',
			online='".$in['online']."' 
			WHERE gid='$editid' LIMIT 1",
			mysql_real_escape_string($in['developer']));
		if(!mysql_query($q)) $errors[] = "Couldn't update database";
		else $step = 3;
		
		//genres
		$q = "DELETE FROM games_genres WHERE gid='$editid'";
		mysql_query($q);
		$q = "INSERT INTO games_genres (gid, genre) VALUES ";
		if($in['genre']) {
			$genres = array();
			$genres = explode(",", $in['genre']);
			foreach($genres as $genre) {
				$genre = trim($genre);
				if($genre != "") $q.= "('$editid', '".mysql_real_escape_string($genre)."'),";
			}
			$q = substr($q, 0, -1).";";
			if(!mysql_query($q)) $errors[] = "Couldn't add genres to database; ".mysql_error();
		}
		
	} elseif($step == "3") {
		
		// PUBLICATIONS //
		
		if(!$in['platform_id']) $errors[] = "No platform selected";
		if(!$in['year']) $errors[] = "No release year input";
		$in['pub_title'] = htmlentities($in['pub_title'], ENT_QUOTES);
		if(!$in['pub_title']) $in['pub_title'] = htmlentities($dbdat->title, ENT_QUOTES);
		
		//get # of current pubs and decide if this should be the primary pub
		$q = "SELECT * FROM games_publications WHERE gid='$editid'";
		if(!mysql_num_rows(mysql_query($q))) {
			$primary = '1';
		} else {
			$primary = '0';
		}
		
		//get next id
		$query = mysql_query("SHOW TABLE STATUS LIKE 'games_publications'");
		$row = mysql_fetch_array($query);
		if(!$next_id = $row['Auto_increment']) die("Couldn't get next database ID; ".mysql_error());
		
		$q = "INSERT INTO games_publications (gid,platform_id,title,region,release_date,`primary`) VALUES 
			('$editid', '".$in['platform_id']."', '".$in['pub_title']."', '".$in['region']."', '".$in['year']."-".$in['month']."-".$in['day']."', '$primary')";
		if(!mysql_query($q)) {
			$errors[] = "Couldn't add publication to db: ".mysql_error();
		} else {
			$results[] = "Publication successfully added to the database.";
			
			if($_FILES['file']['name']) {
				
				require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/class.upload.php");
				
				//upload
				$handle = new Upload($_FILES['file']);
		    if ($handle->uploaded) {
		    	$handle->image_convert          = 'jpg';
					$handle->image_resize           = true;
					$handle->image_ratio_no_zoom_in = true;
					$handle->image_x                = 500;
					$handle->image_y                = 700;
		    	$handle->file_overwrite         = TRUE;
		    	$handle->file_safe_name         = FALSE;
		    	$handle->file_new_name_body     = $editid.'-box-'.$next_id;
		    	$handle->Process($_SERVER['DOCUMENT_ROOT']."/games/files/$editid/");
					if ($handle->processed) {
						$results[] = 'Box art uploaded: <a href="/games/files/'.$editid.'/'.$handle->file_dst_name.'" target="_blank">'.$handle->file_dst_name.'</a>';
						
						//small img
						$handle->file_overwrite = TRUE;
						$handle->file_safe_name = FALSE;
						$handle->file_new_name_body = $editid.'-box-'.$next_id.'-sm';
						$handle->image_convert         = 'png';
						$handle->image_resize          = true;
						$handle->image_ratio_y         = true;
						$handle->image_x               = 140;
						$handle->Process($_SERVER['DOCUMENT_ROOT']."/games/files/$editid/");
						if ($handle->processed) $results[] = 'Small image created: <a href="/games/files/'.$editid.'/'.$handle->file_dst_name.'" target=_blank">'.$handle->file_dst_name.'</a>';
						else $errors[] = 'Small image couldn\'t be created: ' . $handle->error;
									
						//thumbnail
						$handle->file_overwrite = TRUE;
						$handle->file_safe_name = FALSE;
						$handle->file_new_name_body = $editid.'-box-'.$next_id.'-tn';
						$handle->image_convert         = 'png';
						$handle->image_resize          = true;
						$handle->image_ratio_y         = true;
						$handle->image_x               = 80;
						$handle->Process($_SERVER['DOCUMENT_ROOT']."/games/files/$editid/");
						if ($handle->processed) $results[] = 'Thumbnail image created: <a href="/games/files/'.$editid.'/'.$handle->file_dst_name.'" target=_blank">'.$handle->file_dst_name.'</a>';
						else $errors[] = 'Thumbnail image couldn\'t be created: ' . $handle->error;
		        } else {
		        	$errors[] = 'file not uploaded to the wanted location: ' . $handle->error;
		        }
		    } else {
		        // if we're here, the upload file failed for some reasons
		        // i.e. the server didn't receive the file
		        $errors[] = 'file not uploaded on the server: ' . $handle->error;
		    }
		  }
		}
		
	} elseif($step == "4") {
		
		// PEOPLE //
		
		if($in['select-who'] == "pid" && !$in['pid']) $errors[] = "No person selected";
		if($in['select-who'] == "name" && !$in['name']) $errors[] = "No person's name input";
		$in['name'] = htmlentities($in['name'], ENT_QUOTES);
		if(!$in['role']) $errors[] = "No role specified";
		$in['role'] = htmlentities($in['role'], ENT_QUOTES);
		$in['notes'] = htmlentities($in['notes'], ENT_QUOTES);
		
		if(!$errors) {
			$q = sprintf("INSERT INTO people_work (pid, gid, role, notes, vital) VALUES 
				('".$in['pid']."', '$editid', '%s', '%s', '".$in['vital']."');",
				mysql_real_escape_string($in['role']),
				mysql_real_escape_string($in['notes']));
			if(!mysql_query($q)) $errors[] = "Couldn't add person to database";
			else {
				$results[] = "Person successfully added.";
				$dbdat->people = $peoplestr;
				unset($in);
				$in['title'] = $dbdat->title;
			}
		}
		
	} elseif($step == "5") {
		
		// SCREENS //
		
		$files = array();
	  foreach ($_FILES['screen'] as $k => $l) {
			foreach ($l as $i => $v) {
				if (!array_key_exists($i, $files)) {
					$files[$i] = array();
				}
				$files[$i][$k] = $v;
			}
	  }
		$filenum = 0;
		for($i = 0; $i < 5; $i++) {
			if($files[$i]['name']) $filenum++;
		}
		
		if($filenum) {
		
			include_once($_SERVER['DOCUMENT_ROOT']."/bin/php/class.upload.php");
			
			$dir = substr($dbdat->title_url, 0, 15);
			$dir = str_replace("-", "_", $dir);
			$dir = "/media/".$dir."-screens1";
			
			$q = "SELECT * FROM media WHERE directory='$dir' LIMIT 1";
			if(mysql_num_rows(mysql_query($q))) {
				$dir.= rand(10,99);
				$q = "SELECT * FROM media WHERE directory='$dir' LIMIT 1";
				if(mysql_num_rows(mysql_query($q))) $dir.= rand(10,99);
			}
			
			//make dir
			$subj = $_SERVER['DOCUMENT_ROOT'].$dir;
			if(!mkdir($subj, 0777) || !mkdir($subj."/thumbs", 0777)) {
				$errors[] = ("Couldn't make directories ($subj)");
			}
			
			//get next media ID
			$q = "SHOW TABLE STATUS LIKE 'media'";
			$r = mysql_query($q) or die ( "Query failed: " . mysql_error() . "<br/>" . $q );
			$row = mysql_fetch_assoc($r);
			if(!$nextid = $row['Auto_increment']) $errors[] = ("Couldn't get next id");
			
			$q = "INSERT INTO media (directory, category_id, description, gallery, datetime, usrid, quantity) VALUES 
			('$dir', '1', '<i>".$dbdat->title."</i> screenshots', '1', '".date("Y-m-d H:i:s")."', '$usrid', '$filenum')";
			if(!$errors) {
				if(!mysql_query($q)) $errors[] = "Couldn't add to db: ".mysql_error();
			}
			
			$q = "INSERT INTO media_tags (media_id, tag) VALUES ('$nextid', 'gid:$editid')";
			if(!$errors) {
				if(!mysql_query($q)) $errors[] = "Couldn't add to tag db: ".mysql_error();
			}
		  
		  if(!$errors) {
		  	
			  $capt = $_POST['caption'];
				
				$f = 0;
				foreach ($files as $file) {
					
			    $handle = new Upload($file);
			    
			    if($handle->uploaded) {
			    	
						$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir);
						
						if ($handle->processed) {
							
							//caption
							if($capt[$f]) {
								$capt[$f] = strip_tags($capt[$f]);
								$capt[$f] = htmlentities($capt[$f], ENT_QUOTES);
								$q = sprintf("INSERT INTO media_captions (media_id, `file`, `caption`) VALUES ('$nextid', '".$handle->file_dst_name."', '%s')",
									mysql_real_escape_string($capt[$f]));
								if(!mysql_query($q)) $errors[] = "Could not add caption (\"".$capt[$f]."\") to ".$handle->file_dst_name;
							}
							
							//thumb
							$handle->image_convert = 'jpg';
				      $handle->image_resize = TRUE;
				      $handle->image_ratio_crop = TRUE;
							$handle->image_x = 100;
							$handle->image_y = 100;
							$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir."/thumbs");
							
						} else {
							$errors[] = 'file not uploaded! Error: ' . $handle->error;
						}
			        
					}
					$f++;
				}
				
			}
			
		}
		
		if(!$errors) $step++;
		
	}
		
}