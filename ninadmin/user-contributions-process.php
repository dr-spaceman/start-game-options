<?

//AJAX

if($ajax_do = $_POST['ajax_do']) {
	
	include_once($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");
	include_once($_SERVER['DOCUMENT_ROOT']."/bin/php/bbcode.php");
	
	if($ajax_do == "bb2html") {
		$txt = bb2html($_POST['_text'], "inline_citations");
		$txt = nl2br($txt);
		die($txt);
	}
	
	exit;
	
}


// CATEGORY MGT //

if($action == "categories") {
	
	foreach($in[ids] as $tid) {
		$q = "UPDATE users_contributions_types SET 
			category = '".$in[$tid]['category']."', 
			description = '".mysql_real_escape_string($in[$tid]['description'])."', 
			points = '".$in[$tid]['points']."' 
			WHERE type_id='$tid' LIMIT 1";
		if(!mysql_query($q)) $errors[] = mysql_error();
	}
	if(!$errors) $results[] = "Categories & Points updated";
	
}

if($action == "new category") {
	
	$q = "INSERT INTO users_contributions_types (category, description, points) VALUES 
		('".$in['category']."', '".mysql_real_escape_string($in['description'])."', '".$in['points']."');";
	if(!mysql_query($q)) $errors[] = mysql_error();
	else $results[] = "Category Set added";
	
}

// PROCESS CONTRIBUTION //

if(!is_array($_POST['cids'])) return;

foreach($_POST['cids'] AS $cid) {
	
	$q = "SELECT * FROM users_contributions uc LEFT JOIN users_contributions_data USING (contribution_id) WHERE uc.contribution_id='".$cid."' LIMIT 1";
	if(!$x = mysql_fetch_assoc(mysql_query($q))) die("Couldn't get data for id # $id: ".mysql_error());
	
	$type = $x['type_id'];
	
	$d = makeContrDataArr($x['data']);
	
	if($x['supersubject']) {
		list($ss_field, $ss_id) = explode(":", $x['supersubject']);
		if($ss_field == "gid") {
			$q = "SELECT * FROM games WHERE gid='".($in['gid'] ? $in['gid'] : $ss_id)."' LIMIT 1";
			$gdat = mysql_fetch_object(mysql_query($q));
			//if($gdat->unpublished) $warnings[] = '<a href="/games/'.$gdat->gid.'/">'.$gdat->title.'</a> is still unpublished.';
		} elseif($ss_field == "pid") {
			$q = "SELECT * FROM people WHERE pid='".($in['pid'] ? $in['pid'] : $ss_id)."' LIMIT 1";
			$pdat = mysql_fetch_object(mysql_query($q));
		}
	}

	$deny = $_POST['deny'];
	
	if($deny && $type != '1') {
		
		//DENIED!!! skip $action
		$denied_action = $action;
		unset($type);
		
	} else {
		
		//approved or new data submitted
		
		if(!$_POST['enmasse']) $in = $d; //if auto-approved via checkbox on the index
		
	}
	
	if($type == "2" || $type == "13" || $type == "16") $type = "wiki";
	
	if($type) {
		switch($type) {
		
		case "wiki":
			
			// WIKI //
			
			$in['text'] = codedBB($in['text']);
			$in['text'] = trim($in['text']);
			if(!$in['text']) $errors[] = "No text given";
			else {
				
				$subj = "wiki:id:".mysqlNextAutoIncrement("wiki").":";
				$q = sprintf("INSERT INTO wiki (`field`, subject_field, subject_id, `text`, `notes`, usrid, `datetime`) VALUES 
					('%s', '%s', '%s', '%s', '%s', '".$x['usrid']."', '".date("Y-m-d H:i:s")."');",
					mysql_real_escape_string($d['field']),
					mysql_real_escape_string($d['subject_field']),
					mysql_real_escape_string($d['subject_id']),
					mysql_real_escape_string($in['text']),
					mysql_real_escape_string($d['notes'])
				);
				if(!mysql_query($q)) $errors[] = "Couldn't update database; ".mysql_error();
				else $results[] = 'Successfully published wiki (<a href="/wiki.php?subj='.$d['subject_field'].'/'.$d['subject_id'].'/'.$d['field'].'">see it</a>)';
				
			}
			
			break;
			
		case "1":
			
			// NEW GAME //
			
			if($in['del_game']) {
				
				$deny = 1;
				
				$gid = $gdat->gid;
				
				$del = array(
					"albums_tags|gid|$gid",
					"forums_tags|tag|gid:$gid",
					"games_collection|gid|$gid",
					"games_developers|gid|$gid",
					"games_genres|gid|$gid",
					"games_publications|gid|$gid",
					"games_series|gid|$gid",
					"games_trivia|gid|$gid",
					"groups_tags|tag|gid:$gid",
					"people_work|gid|$gid",
					"posts_tags|tag|gid:$gid",
					"games|gid|$gid"
				);
				foreach($del as $d) {
					list($table, $f, $v) = explode("|", $d);
					$q = "DELETE FROM `$table` WHERE `$f` = '$v'";
					if(!mysql_query($q)) $errors[] = "Couldn't $q ".mysql_error();
				}
				
				$in['review_notes'] = '[Deleted game "'.$gdat->title.'"] '.$in['review_notes'];
				
				$q = "UPDATE users_contributions SET published='0', pending='0', review_notes='".mysql_real_escape_string($in['review_notes'])."' WHERE supersubject='gid:$gid' OR `description` LIKE '%[gid=$gid/]'";
				if(!mysql_query($q)) $errors[] = "Couldn't update users_contributions; ".mysql_error();
				
			} else {
				
				// publish it
				
				$q = "UPDATE games SET unpublished = '0' WHERE gid = '".$gdat->gid."' LIMIT 1";
				if(!mysql_query($q)) $errors[] = 'Couldn\'t publish <a href="/games/$gdat->gid/">$gdat->title</a>';
				else $results[] = 'Published <a href="/games/$gdat->gid/">$gdat->title</a>';
				
			}
			
			break;
			
		case "4":
			
			// GAMES TRIVIA //
			
			if(!$subj) {
				
				//new fact
				
				$in['fact'] = trim($in['fact']);
				$in['fact'] = codedBB($in['fact']);
				
				$subj = "games_trivia:id:".mysqlNextAutoIncrement("games_trivia").":fact";
				$q = sprintf(
					"INSERT INTO games_trivia (gid, fact, `datetime`, usrid) VALUES ('$gdat->gid', '%s', '".date("Y-m-d H:i:s")."', '".$x['usrid']."');",
					mysql_real_escape_string($in['fact'])
				);
				if(!mysql_query($q)) $errors[] = "Couldn't update database; ".mysql_error();
				else $results[] = 'Successfully published factoid (<a href="/games/'.$gdat->gid.'/">see it</a>)';
				
			}
			
			break;
			
		case "3":
			
			// PUBLICATION //
			
			//get # of current pubs and decide if this should be the primary pub
			$q = "SELECT * FROM games_publications WHERE gid='".$ssubjid."'";
			if(!mysql_num_rows(mysql_query($q))) $primary = '1';
			else $primary = '0';
			
			$nextid = mysqlNextAutoIncrement("games_publications");
			
			//check dirs
			$new_dir = "/games/files/".$ssubjid."/";
			if(!is_dir($_SERVER['DOCUMENT_ROOT'].$new_dir)) {
				if(!mkdir($_SERVER['DOCUMENT_ROOT'].$new_dir, 0777)) {
					$errors[] = "Couldn't create files directory which is neccessary for uploads. Manually create /games/files/".$dir."/".$id."/";
				}
			}
			if(!is_writable($_SERVER['DOCUMENT_ROOT'].$new_dir)) {
				if(!chmod($_SERVER['DOCUMENT_ROOT'].$new_dir, 0777)) {
					$errors[] = "Couldn't make this game's files dir writable. Manually CHMOD $new_dir to 777.";
				}
			}
			
			$new_body = $ssubjid."-box-".$nextid;
			
			if($_FILES['file']['name']) {
				//upload new img
				$handle = new Upload($_FILES['file']);
		    if ($handle->uploaded) {
		    	
		    	$handle->image_convert          = 'jpg';
					$handle->image_resize           = true;
					$handle->image_ratio_no_zoom_in = true;
					$handle->image_x                = 500;
					$handle->image_y                = 700;
		    	$handle->file_overwrite         = TRUE;
		    	$handle->file_safe_name         = FALSE;
		    	$handle->file_new_name_body     = $new_body;
		    	
		    	$handle->Process($_SERVER['DOCUMENT_ROOT'].$new_dir);
					if ($handle->processed) {
						$results[] = 'Box art uploaded: <a href="'.$new_dir.'/'.$handle->file_dst_name.'" target="_blank">'.$handle->file_dst_name.'</a>';
						
						//small img
						$handle->image_convert         = 'png';
						$handle->image_resize          = TRUE;
						$handle->image_ratio_y         = TRUE;
						$handle->image_x               = 140;
						$handle->file_overwrite        = TRUE;
						$handle->file_safe_name        = FALSE;
						$handle->file_new_name_body    = $new_body.'-sm';
						$handle->Process($_SERVER['DOCUMENT_ROOT'].$new_dir);
						if ($handle->processed) $results[] = 'Small image created: <a href="'.$new_dir.'/'.$handle->file_dst_name.'" target=_blank">'.$handle->file_dst_name.'</a>';
						else $errors[] = 'Small image couldn\'t be created: ' . $handle->error;
									
						//thumbnail
						$handle->image_convert         = 'png';
						$handle->image_resize          = TRUE;
						$handle->image_ratio_y         = TRUE;
						$handle->image_x               = 80;
						$handle->file_overwrite        = TRUE;
						$handle->file_safe_name        = FALSE;
						$handle->file_new_name_body    = $new_body.'-tn';
						$handle->Process($_SERVER['DOCUMENT_ROOT'].$new_dir);
						if ($handle->processed) {
							$results[] = 'Thumbnail image created: <a href="'.$new_dir.'/'.$handle->file_dst_name.'" target=_blank">'.$handle->file_dst_name.'</a>';
						} else $errors[] = 'Thumbnail image couldn\'t be created: ' . $handle->error;
		      } else $errors[] = 'file not uploaded to the wanted location: ' . $handle->error;
		    } else $errors[] = 'file not uploaded on the server: ' . $handle->error;
		    
		  } else {
		  	
		  	//rename & move user-submitted file
		  	
				$file = $in['file'];
				$file2 = substr($file, 0, -4)."_sm.png";
				$file3 = substr($file, 0, -4)."_tn.png";
				
				if(!rename($_SERVER['DOCUMENT_ROOT']."/bin/temp/".$file, $_SERVER['DOCUMENT_ROOT'].$new_dir.$new_body.".jpg")) $errors[]=("Couldn't move uploaded file");
				if(!rename($_SERVER['DOCUMENT_ROOT']."/bin/temp/".$file2, $_SERVER['DOCUMENT_ROOT'].$new_dir.$new_body."-sm.png")) $errors[]=("Couldn't move uploaded small file");
				if(!rename($_SERVER['DOCUMENT_ROOT']."/bin/temp/".$file3, $_SERVER['DOCUMENT_ROOT'].$new_dir.$new_body."-tn.png")) $errors[]=("Couldn't move uploaded thumbnail");
			
			}
			
			$q = "INSERT INTO games_publications (gid, title, platform_id, region, release_date, `primary`, placeholder_img) VALUES 
			('".$ssubjid."', '".htmlent($in['title'])."', '".$in['platform_id']."', '".$in['region']."', '".$in['release_date']."', '$primary', '".$in['placeholder_img']."');";
			if(!mysql_query($q)) $errors[] = "Couldn't update database; ".mysql_error();
			else {
				$results[] = 'Successfully added publication (<a href="/games/link.php?id='.$ssubjid.'">see it</a>)';
				
				$subj = "games_publications:".$nextid;
			}
			
			if($in['credit_author']) {
				$det = '<a href="'.$new_dir.$new_body.'.jpg" class="thickbox"><img src="'.$new_dir.$new_body.'-tn.png"/></a>';
				$q = "UPDATE users_contributions SET details='".mysql_real_escape_string($det)."' WHERE contribution_id='".$x['contribution_id']."' LIMIT 1";
				mysql_query($q);
			}
			
			break;
		
		case "6":
			
			// PERSON WORK //
			
			$pid = $in['pid'];
			$gid = $in['gid'];
			$in['role'] = htmlent($in['role']);
			$now = date('Y-m-d H:i:s');
			
			if(!$pid) $pid = addPersonToDb($in, $x['usrid']);
			
			$subj = "people_work:".mysqlNextAutoIncrement("people_work");
			$new_ssubj = "pid:".$pid;
			$q = "INSERT INTO people_work (pid, gid, role, vital, notes) VALUES 
			('$pid', '$gid', '".mysql_real_escape_string($in['role'])."', '".$in['vital']."', '".mysql_real_escape_string($in['notes'])."');";
			if(!mysql_query($q)) $errors[] = "Couldn't update database; ".mysql_error();
			else $results[] = 'Successfully added developer! <a href="/games/link.php?id='.$gid.'">game overview</a>';
			
			break;
			
		case "15":
			
			//PERSON PIC//
			
			list($ssubjid, $pid) = explode(":", $x['supersubject']);
			list($img, $tn) = explode("|--|", $x['submission']);
			
			if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/img/people/".$pid.".png")) {
				@rename($_SERVER['DOCUMENT_ROOT']."/bin/img/people/".$pid.".png", $_SERVER['DOCUMENT_ROOT']."/bin/deleted-files/people--".$pid."_pic_".rand(0,99999).".png");
				@unlink($_SERVER['DOCUMENT_ROOT']."/bin/img/people/".$pid."-tn.png");
			}
			if(!rename($_SERVER['DOCUMENT_ROOT']."/bin/uploads/person_pic/".$img, $_SERVER['DOCUMENT_ROOT']."/bin/img/people/".$pid.".png")) $errors[] = ("Couldn't move temporary image");
			if(!rename($_SERVER['DOCUMENT_ROOT']."/bin/uploads/person_pic/".$tn, $_SERVER['DOCUMENT_ROOT']."/bin/img/people/".$pid."-tn.png")) $errors[] = ("Couldn't move temporary thumbnail image");
			
			if(!$errors) {
				$res = mysql_query("SELECT name, name_url FROM people WHERE pid='$pid' LIMIT 1");
				$pdat = mysql_fetch_object($res);
				$results[] = 'Successfully changed profile picture! <a href="/people/~'.$pdat->name_url.'">'.$pdat->name.' profile</a>';
			}
			
			$subj = "people:pid:$pid";
			$new_details = '<img src="/bin/img/people/'.$pid.'.png" alt="'.htmlSC($pdat->name).'"/>';
			
			break;
		
		default:
			
			list($t, $k, $v, $f) = explode(":", $x['subject']);
			
			$reslink = "";
			list($ss, $ssid) = explode(":", $x['supersubject']);
			if($ss == "gid") $reslink = "/games/$ssid/";
			elseif($ss == "pid") $reslink = "/people/$ssid/";
			
			$in[$f] = trim($in[$f]);
			if(($t == "people" && $f == "name") || ($t == "games" && $f == "title")) {
				$fm = formatName($in[$f]);
				$in[$f] = $fm[0];
			} else {
				$in[$f] = codedBB($in[$f]);
			}
			
			if($in[$f] == "" && $d['*delete_row']) $q = "DELETE FROM `$t` WHERE `$k` = '$v' LIMIT 1";
			else $q = "UPDATE `$t` SET `$f` = '".mysql_real_escape_string($in[$f])."' WHERE `$k` = '$v' LIMIT 1";
			if(mysql_query($q)) $results[] = 'Updated '.$t.' database'.($reslink ? '; <a href="'.$reslink.'">Go to text page</a>' : '');
			else $errors[] = "Couldn't process data; ".mysql_error();
			
			break;
			
		}
	} // end approve & process
	
	//////////////////////////
	// COMPLETE THE PROCESS //
	//////////////////////////
	
	if(!$errors) {
		
		$pub = (!$deny && $in['credit_author'] ? 1 : 0);
		
		$q = "UPDATE users_contributions SET 
			".($subj ? "subject='$subj', " : "")."
			".($in['new_ssubj'] ? "supersubject = '".$in['new_ssubj']."', " : "")."
			published = '$pub',
			pending = '0',
			datetime_reviewed = '".date("Y-m-d H:i:s")."', 
			reviewer = '".(!$in['anonymous_review'] ? $usrid : '')."',
			review_notes = '".mysql_real_escape_string($in['review_notes'])."' 
			WHERE contribution_id='$cid' LIMIT 1";
		if(!mysql_query($q)) $errors[] = "Couldn't update contr queue; ".mysql_error();
		
		if(!$deny) {
			//mark ssubj as updated
			$contr = new contribution;
			if($ssubj == "gid" && $ssubjid) $contr->markUpd("games", "gid", $ssubjid);
			if($ssubj == "pid" && $ssubjid) $contr->markUpd("people", "pid", $ssubjid);
		}
		
	}
	
}

function addPersonToDb($in, $uid) {
		
	if(!$in['name']) die("No name given");
	$in['title'] = htmlent($in['title']);
	list($name, $name_url) = formatName($in['name']);
	$pid = mysqlNextAutoIncrement("people");
	if(!$name_url) $name_url = $name.$pid;
	$now = date("Y-m-d H:i:s");
	
	$q = sprintf("INSERT INTO people (name, name_url, title, prolific, not_creator, created, modified) VALUES 
	('%s', '$name_url', '%s', '".$in['prolific']."', '".$in['not_creator']."', '$now', '$now');",
	mysql_real_escape_string($name),
	mysql_real_escape_string($in['title']));
	if(!mysql_query($q)) die("Couldn't add $name to people database; ".mysql_error());
	
	if($in['credit_author']) {
		$q = "INSERT INTO users_contributions (type_id, usrid, datetime, description, published, subject, supersubject) VALUES 
		('12', '".$uid."', '$now', 'New creator: <a href=\"/people/~$name_url\">".htmlent($name)."</a>', '1', 'people:$pid', 'pid:$pid');";
		mysql_query($q);
	}
	
	return $pid;
	
}

?>