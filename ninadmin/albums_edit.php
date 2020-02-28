<?

if ($create = $_POST['create']) {
	
	$album1[0][albumid] = strtoupper($album1[0][albumid]);
	$Query = "SELECT l.id from albums as l order by l.id DESC limit 1";
	$Result = mysqli_query($GLOBALS['db']['link'], $Query);
	while ($row = mysqli_fetch_assoc($Result)) {
		$num = $row[id];
	}
	
	$Query = "SELECT l.albumid from albums as l where l.albumid = '".$album1[0][albumid]."'";
	$Result = mysqli_query($GLOBALS['db']['link'], $Query);
	$trackcheck = mysqli_num_rows($Result);
	if ($trackcheck == 0) {
		$action = "edit";
		$editid = $album1[0][albumid];
		$apr = "1";
		$type = "new";
		$indexid = $num + 1;
	} else {
		$action = "new";
		$apr = "0";
		$errors[] = "The album ID you submitted is already used by an existing album entry.";
	}
}
elseif ($action == "edit") {
	$apr = "1";
}

if($del = $_GET['delete_sample']) {
			
	// DELETE SAMPLE //
	$q = "SELECT * FROM albums_samples WHERE track_id='$del' LIMIT 1";
	if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Couldn't $q");
	else {
		$q = "DELETE FROM albums_samples WHERE track_id='$del' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't delete from db";
		else {
			if(!unlink($rootpath."/".$albumpath."/media/samples/".$dat->file)) $errors[] = "Couldn't delete file $rootpath/$albumpath/media/samples/$dat->file";
			else $results[] = "Deleted file";
		}
	}

}

if ($_POST['dbupdate'] == "1" || $_GET['dbupdate'] == "1") {
	
	if ($process == "1" && $apr == "1") {
		
		$dayline = $_POST['yrsort'];
		$dayline .= "-".$_POST['mosort'];
		$dayline .= "-".$_POST['daysort'];
		
		$releaseline = $_POST['mosort'];
		$releaseline .= "/".$_POST['daysort'];
		$releaseline .= "/".$_POST['yrsort'];
		
		$album1[0]['datesort'] = $dayline;
		$album1[0]['release'] = $releaseline;
		
		if(!$album1[0][title]) {
			$album1[0][title] = $album1[0][subtitle];
			$album1[0][subtitle] = '';
		}
		
		if ($album1[0][title] != '') {
			
			if(!$album1[0][keywords]) $album1[0][keywords] = trim($album1[0][title]." ".$album1[0][subtitle]);
			$album1[0][title] = htmlSC($album1[0][title]);
			$album1[0][subtitle] = htmlSC($album1[0][subtitle]);
			$album1[0][keywords] = htmlSC($album1[0][keywords]);
			$album1[0][keywords] = htmlSC($album1[0][keywords]);
			$album1[0][publisher] = htmlSC($album1[0][publisher]);
			$album1[0][albumid] = preg_replace("/[^a-zA-Z0-9\-]/", "", $album1[0][albumid]);
			
			if ($create = $_POST['create']) {
				if(!$album1[0][albumid]) die("No album ID given");
				$Query = "INSERT into albums (id, title, subtitle, keywords, cid, albumid, new) VALUES 
					('$indexid', '{$album1[0][title]}', '{$album1[0][subtitle]}', '{$album1[0][keywords]}', '{$album1[0][cid]}', '{$album1[0][albumid]}', '{$album1[0]['new']}')";
			    if(!$Result = mysqli_query($GLOBALS['db']['link'], $Query)) die("db error: ".mysql_error($Link)."<br />Query: ".$Query);
			  
			  $aid = $album1[0][albumid];
			  
			  if($cl = $_POST['clone']) {
			  	//albums_buy
			  	$q = "SELECT * FROM albums_buy WHERE album = '$cl'";
			  	$r   = mysqli_query($GLOBALS['db']['link'], $q);
			  	while($row = mysqli_fetch_assoc($r)) {
			  		$q = "INSERT INTO albums_buy (album, code, vendor, price, stock) VALUES ('$aid', '$row[code]', '$row[vendor]', '$row[price]', '$row[stock]')";
			  		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't clone row to albums_buy; ".mysql_error();
			  	}
			  	//albums_credits
			  	$q = "SELECT * FROM albums_credits WHERE albumid = '$cl'";
			  	$r   = mysqli_query($GLOBALS['db']['link'], $q);
			  	while($row = mysqli_fetch_assoc($r)) {
			  		$q = "INSERT INTO albums_credits (albumid,source,address,conttype) VALUES ('$aid', '$row[source]', '$row[address]', '$row[conttype]')";
			  		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't clone row to albums_credits; ".mysql_error();
			  	}
			  	//albums_other_people
			  	$q = "SELECT * FROM albums_other_people WHERE albumid = '$cl'";
			  	$r   = mysqli_query($GLOBALS['db']['link'], $q);
			  	while($row = mysqli_fetch_assoc($r)) {
			  		$q = "INSERT INTO albums_other_people (albumid,name,role,notes,vital) VALUES ('$aid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $row['name'])."', '$row[role]', '".mysqli_real_escape_string($GLOBALS['db']['link'], $row[notes])."', '$row[vital]')";
			  		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't clone row to albums_other_people; ".mysql_error();
			  	}
			  	//albums_related
			  	$q = "SELECT * FROM albums_related WHERE album = '$cl'";
			  	$r   = mysqli_query($GLOBALS['db']['link'], $q);
			  	while($row = mysqli_fetch_assoc($r)) {
			  		$q = "INSERT INTO albums_related (album,type,related) VALUES ('$aid', '$row[type]', '$row[related]')";
			  		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't clone row to albums_related; ".mysql_error();
			  	}
			  	$q = "INSERT INTO albums_related (album,type,related) VALUES ('$aid','5','$cl'), ('$cl','5','$aid');";
			  	mysqli_query($GLOBALS['db']['link'], $q);
			  	//albums_synopsis
			  	$q = "SELECT * FROM albums_synopsis WHERE album = '$cl'";
			  	$r   = mysqli_query($GLOBALS['db']['link'], $q);
			  	while($row = mysqli_fetch_assoc($r)) {
			  		$q = "INSERT INTO albums_synopsis (album,synopsis,author,link,date) VALUES ('$aid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $row[synopsis])."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $row[author])."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $row['link'])."', '".$row['date']."')";
			  		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't clone row to albums_synopsis; ".mysql_error();
			  	}
			  	//albums_tags
			  	$q = "SELECT * FROM albums_tags WHERE albumid = '$cl'";
			  	$r   = mysqli_query($GLOBALS['db']['link'], $q);
			  	while($row = mysqli_fetch_assoc($r)) {
			  		$q = "INSERT INTO albums_tags (albumid,gid) VALUES ('$aid', '$row[gid]')";
			  		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't clone row to albums_tags; ".mysql_error();
			  	}
			  	//albums_tracks
			  	$q = "SELECT * FROM albums_tracks WHERE albumid = '$cl'";
			  	$r   = mysqli_query($GLOBALS['db']['link'], $q);
			  	unset($q);
			  	while($row = mysqli_fetch_assoc($r)) {
			  		$q.= "('$aid', '$row[disc]','$row[track_number]','".mysqli_real_escape_string($GLOBALS['db']['link'], $row[track_name])."','".mysqli_real_escape_string($GLOBALS['db']['link'], $row[artist])."','$row[type]','".mysqli_real_escape_string($GLOBALS['db']['link'], $row[location])."','".$row['time']."'),";
			  	}
			  	if($q) {
			  		$q = "INSERT INTO albums_tracks (albumid,disc,track_number,track_name,artist,type,location,time) VALUES ".substr($q, 0, -1).";";
			  		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't clone row to albums_tracks; ".mysql_error();
			  	}
			  	//people_work
			  	$q = "SELECT * FROM people_work WHERE albumid = '$cl'";
			  	$r   = mysqli_query($GLOBALS['db']['link'], $q);
			  	unset($q);
			  	while($row = mysqli_fetch_assoc($r)) {
			  		$q.= "('$row[pid]', '$aid', '$row[role]','$row[notes]','$row[vital]'),";
			  	}
			  	if($q) {
			  		$q = "INSERT INTO people_work (pid,albumid,role,notes,vital) VALUES ".substr($q, 0, -1).";";
			  		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't clone row to people_work; ".mysql_error();
			  	}
			  }
			  
			}
			
			else {
			
				$Query = "UPDATE albums SET title = '{$album1[0][title]}', subtitle = '{$album1[0][subtitle]}', keywords = '{$album1[0][keywords]}', coverimg = '{$album1[0][coverimg]}', jp = '{$album1[0][jp]}', publisher = '{$album1[0][publisher]}', cid = '{$album1[0][cid]}', datesort = '{$album1[0][datesort]}', `release` = '{$album1[0][release]}', price = '{$album1[0][price]}', compose = '{$album1[0][compose]}', arrange = '{$album1[0][arrange]}', perform = '{$album1[0][perform]}', series = '{$album1[0][series]}', new = '{$album1[0]['new']}', view = '{$album1[0][view]}', media = '{$album1[0][media]}', path = '{$album1[0][path]}' where albumid = '{$album1[0][albumid]}'";
			    if(!$Result = mysqli_query($GLOBALS['db']['link'], $Query))
			    	die("db error: ".mysql_error($Link)."<br />Query: ".$Query);
			    else $results[] = "Changes have been saved";
			
			}
		} else die("No title given");
	
	}
	
	
	if ($_POST['process'] == "2") {
		
		////////////
		// PEOPLE //
		////////////
		
		if($_POST['submit_edit_people_work'] && $in = $_POST['in']) {
			
			//submit edits
			
			foreach($_POST['ids'] as $i) {
				list($table, $id) = explode("-", $i);
				if($in[$i]['linkto']) {
					$q = "DELETE FROM albums_other_people WHERE id='$id' LIMIT 1";
					if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't link to the people db. All changes canceled for this person.";
					else {
						$q = "INSERT INTO people_work (pid, role, vital, notes, albumid) VALUES ('".$in[$i]['linkto']."', '".htmlentities($in[$i]['role'], ENT_QUOTES)."', '$vital', '".addslashes($in[$i]['notes'])."', '$editid')";
					}
				} else {
					$q = "UPDATE $table SET ";
					if($in[$i]['name']) $q.= "`name`='".htmlentities($in[$i]['name'], ENT_QUOTES)."', ";
					$q.= "`role`='".htmlentities($in[$i]['role'], ENT_QUOTES)."', `vital`='".$in[$i]['vital']."', `notes`='".addslashes($in[$i]['notes'])."' WHERE id='$id' LIMIT 1";
				}
				if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't update entry: $q";
				else $results[] = "Entry successfully updated";
			}
		}
		
		if($_POST['delete_people_work']) {
				
			//remove people associations
			
			foreach($_POST['select_work'] as $s) {
				list($table, $id) = explode("-", $s);
				$q = "DELETE FROM $table WHERE id='$id' LIMIT 1";
				if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't disassociated entry";
				else $results[] = "Successfully disassociated entry";
			}
			
		}
		
		if($_POST['add_people_work']) {
			
			//add work
			if($_POST['name'] == "Start typing to find a name...") unset($_POST['name']);
			if($_POST['role'] == "Start typing to find a common role...") unset($_POST['role']);
			$name = formatName($_POST['name']);
			$name_url = formatNameURL($name);
			$role = trim($_POST['role']);
			$vital = $_POST['vital'];
			$notes = $_POST['notes'];
			
			if(!$name) $errors[] = "No name given";
			if(!$role) $errors[] = "No role given";
			
			if(!$errors) {
				
				$indb = FALSE;
				$row = "";
				
				$q = "SELECT * FROM people WHERE name='$name' OR name_url='$name_url' LIMIT 1";
				if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) {
					$indb = TRUE;
					$pid = $row['pid'];
					$q = sprintf("INSERT INTO people_work (pid, role, vital, notes, albumid) VALUES ('".$row['pid']."', '%s', '$vital', '%s', '$editid')",
						mysqli_real_escape_string($GLOBALS['db']['link'], $role),
						mysqli_real_escape_string($GLOBALS['db']['link'], $notes));
				} else {
					$aop_id = mysqlNextAutoIncrement("albums_other_people");
					$q = sprintf("INSERT INTO albums_other_people (name, role, vital, notes, albumid) VALUES ('%s', '%s', '$vital', '%s', '$editid')",
						mysqli_real_escape_string($GLOBALS['db']['link'], $name),
						mysqli_real_escape_string($GLOBALS['db']['link'], $role),
						mysqli_real_escape_string($GLOBALS['db']['link'], $notes));
				}
				if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Error adding entry to database; ".mysql_error();
				else {
					$results[] = "$name credited with the role of $role";
					if(!$indb) {
						
					} else {
						
						$q = "SELECT * from albums where albumid = '$editid' limit 1";
						$adat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
						
					}
				}
			}
			
		}
		
		if($_POST['add_person_to_db']) {
			
			if(!$aop_id = $_POST['albums_other_people_id']) $errors[] = "No entry id given.";
			$q = "SELECT * FROM albums_other_people WHERE id='$aop_id' LIMIT 1";
			if(!$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) $errors[] = "Couldn't find data for ID # $aop_id ; ".mysql_error();
			
			$name = formatName($row['name']);
			$name_url = formatNameURL($name);
			$role = trim($row['role']);
			
			if(!$name) $errors[] = "No name given";
			if(!$role) $errors[] = "No role given";
			
			if(!$errors) {
			
				$q = "SELECT * FROM people WHERE name='$name' OR name_url='$name_url' LIMIT 1";
				if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) {
					$errors[] = 'There was already a person in the database with the name "'.$name.'" or another name that is too similar';
				} else {
					
					$now = date("Y-m-d H:i:s");
					
					$pid = mysqlNextAutoIncrement("people");
					$q = "INSERT INTO people (name, name_url, created, modified) VALUES 
					('$name', '$name_url', '$now', '$now');";
					if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't add $name to the people database; ".mysql_error();
					else {
						$results[] = 'You successfully added '.$name.' to the People Database, but as of now there is no other information about this person. Please <a href="/people/'.$pid.'/'.$name_url.'/edit" target="_blank" class="arrow-link">edit '.$name.'\'s details</a>';
						
						//delete old credit entry without link to people db
						$q = "DELETE FROM albums_other_people WHERE id='$aop_id' LIMIT 1";
						if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't delete old entry so this person is credited twice. ".mysql_error();
						
					}
					
				}
				
			}
		}
	
	////////////
	// TRACKS //
	////////////
	
	} elseif($_POST['do'] == "get_track_num") {
		
		if(!$albumid = $_POST['albumid']) die("no albumid given");
		if(!$disc = $_POST['disc']) die("no disc name given");
		
		$q = "SELECT * FROM albums_tracks WHERE albumid='$albumid' AND disc='$disc'";
		if($num = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) echo $num;
		else echo '0';
	
	} elseif ($_POST['do'] == "add_track") {
		
		// ADD INDIVIDUAL TRACK //
		// POST data received from javascript
		
		if(!$disc = $_POST['disc']) die("no disc name given");
		$disc = str_replace("[[AMP]]", "&", $disc);
		$disc = htmlentities($disc, ENT_QUOTES);
		if(!$albumid = $_POST['albumid']) die("no albumid given");
		if(!$track_name = $_POST['track_name']) die("no track name given");
		$track_name = str_replace("[[AMP]]", "&", $track_name);
		$track_name = htmlentities($track_name, ENT_QUOTES);
		if(!$track_number = $_POST['track_number']) die("No track number given");
		$artist = $_POST['artist'];
		$artist = str_replace("[[AMP]]", "&", $artist);
		$artist = htmlentities($artist, ENT_QUOTES);
		$type = $_POST['type'];
		$type = str_replace("[[AMP]]", "&", $type);
		$type = htmlentities($type, ENT_QUOTES);
		$location = $_POST['location'];
		$location = str_replace("[[AMP]]", "&", $location);
		$location = htmlentities($location, ENT_QUOTES);
		$time = $_POST['time'];
		
		//already track number listed?
		$q = "SELECT * FROM albums_tracks WHERE albumid='$albumid' AND disc='$disc' AND track_number='$track_number' LIMIT 1";
		if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) die("There is already a track listed as track number $track_number for this disc");
		
		$q = sprintf("INSERT INTO albums_tracks (albumid, disc, track_number, track_name, artist, `type`, `location`, `time`) VALUES 
			('$albumid', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
			mysqli_real_escape_string($GLOBALS['db']['link'], $disc),
			mysqli_real_escape_string($GLOBALS['db']['link'], $track_number),
			mysqli_real_escape_string($GLOBALS['db']['link'], $track_name),
			mysqli_real_escape_string($GLOBALS['db']['link'], $artis),
			mysqli_real_escape_string($GLOBALS['db']['link'], $type),
			mysqli_real_escape_string($GLOBALS['db']['link'], $location),
			mysqli_real_escape_string($GLOBALS['db']['link'], $time));
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Couldn't add track to database");
		else echo "ok";
		
		exit;
		
	}
	
	do if($_POST['do'] == "mass_add_tracks"){
		
		// MASS ADD TRACKS //
		
		if(!$disc = $_POST['disc']){ $errors[] = "no disc name given"; break; }
		$disc = htmlentities($disc, ENT_QUOTES);
		if(!$albumid = $_POST['albumid']){ $errors[] = "no albumid given"; break; }
		if(!$v_list = $_POST['mass_tracklist']){ $errors[] = "no tracklist input given"; break; }
		
		//make sure there are no tracks already
		$q = "SELECT * FROM albums_tracks WHERE albumid='$albumid' AND disc='$disc' LIMIT 1";
		if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))){ $errors[] = "Error: There are already tracks posted for the disc '$disc'"; break; }
		
		$list = $v_list;
		$list = trim($list);
		$list = str_replace("\t", " ", $list);
		$list = preg_replace("/ +/", " ", $list);
		$list = str_replace("\r\n\r\n", "\r\n", $list);
		
		$list = explode("\n", $list);
		
		$qs = array();
		$i = -1;
		$n = 0;
		while($i < count($list)){
			$i++;
			$n++;
			$l = trim($list[$i]);
			//echo "$i: $l<br/>";
			if($l == ""){ $n--; continue; }
			$l = preg_replace("/^0?0?".$n."[ \.\):\-~\/|]*[\s\t]/", "", $l); //remove the track number from the title
			preg_match("/\(?(\d{1,4}:\d{2})\)?$/m", $l, $matches); //time
			if($matches[0]) $l = str_replace($matches[0], "", $l);
			$l = trim($l);
			if($matches[1] && $l == ''){
				//the line is only a time, so it belongs to the above line
				if($qs[$n - 1]['time'] == '') $qs[$n - 1]['time'] = $matches[1];
				$n--;
				continue;
			}
			$qs[$n] = array("track_name" => $l, "time" => $matches[1]);
		}
		//echo '<pre>';print_r($qs);exit;
		
		if(!$qs) break;
		
		$q = "INSERT INTO albums_tracks (albumid, disc, track_name, `time`, track_number) VALUES ";
		foreach($qs as $n => $arr){
			$q.= "('$albumid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $disc)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $arr['track_name'])."', '".$arr['time']."', '$n'),";
		}
		$q = substr($q, 0, -1) . ";";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't add tracks because of a database error; " . mysql_error();
		else $results[] = "All tracks added. Please check the disc below for any formatting errors and input more info if available.";
		
	} while(false);
	
	if ($_POST['do'] == "edit_tracks") {
		
		// EDIT //
		
		if(!$albumid = $_POST['editid']) die("no albumid given");
		
		//disc name changes?
		$old = $_POST['old_disc_names'];
		$new = $_POST['new_disc_names'];
		for($i = 0; $i < count($old); $i++) {
			if($old[$i] != $new[$i]) {
				if($new[$i] == "") $warnings[] = "There was no input detected for the new name of <i>".$old[$i]."</i> so it wasn't changed";
				else {
					$new[$i] = htmlentities($new[$i], ENT_QUOTES);
					$q = "UPDATE albums_tracks SET disc='".mysqli_real_escape_string($GLOBALS['db']['link'], $new[$i])."' WHERE disc='".htmlentities($old[$i], ENT_QUOTES)."' AND albumid='$albumid'";
					if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't change disc name from <i>".$old[$i]."</i> to <i>".$new[$i]."</i>";
				}
			}
		}
		
		foreach($_POST['in'] as $i) {
			$i['track_name'] = htmlentities($i['track_name'], ENT_QUOTES);
			$i['artist'] = htmlentities($i['artist'], ENT_QUOTES);
			$i['type'] = htmlentities($i['type'], ENT_QUOTES);
			$i['location'] = htmlentities($i['location'], ENT_QUOTES);
			
			$q = sprintf("UPDATE albums_tracks SET 
				track_name = '%s',
				artist = '%s',
				`type` = '%s',
				`location` = '%s',
				`time` = '%s' 
				WHERE id='".$i['id']."' LIMIT 1",
			mysqli_real_escape_string($GLOBALS['db']['link'], $i['track_name']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $i['artist']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $i['type']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $i['location']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $i['time']));
			if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't update track name <i>".$i['track_name']."</i>";	
		}
		if(!$errors) $results[] = "All track changes saved";
		
		//deletes?
		if($del = $_POST['delete']) {
			foreach($del as $d) {
				
				$q = "DELETE FROM albums_tracks WHERE id='$d' LIMIT 1";
				if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't delete track id # $id";
				
				//samples?
				if($_POST['in'][$d]['has_sample']) {
					$q = "SELECT * FROM albums_samples WHERE track_id='$d' LIMIT 1";
					if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) $errors[] = "Couldn't delete corresponding track sample";
					else {
						$q = "DELETE FROM albums_samples WHERE track_id='$d' LIMIT 1";
						if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't delete sample from db";
						else {
							if(!unlink($rootpath."/".$albumpath."/media/samples/".$dat->file)) $errors[] = "Couldn't delete sample file $rootpath/$albumpath/media/samples/$dat->file";
							else $results[] = "Deleted sample file";
						}
					}
				}
			}
		}
	
	} elseif($_POST['do'] == "upload_sample") {
		
		//UPLOAD SAMPLE //
		
		$in = $_POST['in'];
		
		if(!$in['track_id']) die("No corresponding track selected");
		if(!$_FILES['file']['name'] && !$in['file']) die("No file detected");
		
		//dir writable?
		$dir = "$rootpath/$albumpath/media/samples/";
		if(!is_dir($dir)) mkdir($dir, 0777) || $errors[] = "Couldn't create $dir for upload";
		if(!is_writeable($dir)) chmod($dir, 0777) || $errors[] = "Couldn't make $dir writeable for upload";
		else {
			if($_FILES['file']['name']) {
				$handle = new Upload($_FILES['file']);
				//check ext
				$exts = array("mp3", "wma", ".rm", "m4a");
				$ext = substr($_FILES['file']['name'], -3);
				if(!in_array($ext, $exts)) {
					$errors[] = "Invalid extension -- please upload only ".implode(", ", $exts);
				} else {
			    if ($handle->uploaded) {
			    	$handle->Process("$rootpath/$albumpath/media/samples/");
			      if ($handle->processed) {
			      	$results[] = '<a href="/music/media/samples/'.$handle->file_dst_name.'" target="_blank">File</a> successfully uploaded';
			      	$q = "INSERT INTO albums_samples (albumid, track_id, file, length, datetime, usrid) VALUES ('$editid', '".$in['track_id']."', '".addslashes($handle->file_dst_name)."', '".$in['length']."', '".date("Y-m-d H:i:s")."', '$usrid')";
			      	if(!mysqli_query($GLOBALS['db']['link'], $q)) {
			      		$errors[] = "Couldn't apend this info to the database; sample NOT added";
			      		if(!unlink($dir."/".$handle->file_dst_name)) $errors[] = "Couldn't delete file $dir/$handle->file_dst_name";
			      		else $results[] = "Successfully deleted file";
			      	}
			      } else {
			        $errors[] = 'file not uploaded to the wanted location: ' . $handle->error;
			      }
			    } else {
			    	$errors[] = 'file not uploaded on the server: ' . $handle->error;
			    }
				}
			} else {
				$q = "INSERT INTO albums_samples (albumid, track_id, file, length, datetime, usrid) VALUES ('$editid', '".$in['track_id']."', '".$in['file']."', '".$in['length']."', '".date("Y-m-d H:i:s")."', '$usrid')";
			  if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't add saple to database";
			  else $results[] = "Sample added";
			}
		}
	
	} elseif($_POST['add_track_source']) {
		
		// ADD SOURCE //
		
		if(!$albumid = $_POST['editid']) die("no albumid given");
		if(!$source = $_POST['source']) die("No source name given");
		$source = trim($source);
		$source = htmlentities($source, ENT_QUOTES);
		$address = trim($_POST['address']);
		$address = preg_replace("%http://videogam.in/?%", "/", $address);
		
		$q = "INSERT INTO albums_credits (albumid, source, address, conttype) VALUES 
			('$albumid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $source)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $address)."', 'track')";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't add to database $q";
		else $results[] = "Source successfully added";
	
	} elseif($x = $_GET['delete_credit']) {
	
		$q = "DELETE FROM albums_credits WHERE id='$x' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't $q";
		else $results[] = "Successfully Deleted";
	
	} elseif ($process == "4") {
		
		///////////////////
		// RELATED GAMES //
		///////////////////
		
		$gids = $_POST['gids'];
		
		$Query = "DELETE from albums_tags where albumid = '$editid'"; 
		if(!mysqli_query($GLOBALS['db']['link'], $Query)) $errors[] = "Couldn't proceed with changes because $Query";
		else {
			foreach($gids as $gid) {
				$Query = "INSERT into albums_tags (gid, albumid) VALUES ('$gid', '$editid')";
				if(!mysqli_query($GLOBALS['db']['link'], $Query)) $errors[] = "Couldn't $Query";
			}
		}
		if(!$errors) $results[] = "Changes successfully implemented";
	
	}
	
	elseif ($process == "5") {
		
		////////////////////
		// RELATED ALBUMS //
		////////////////////
		
		$in = $_POST['albumrel'];
		
		$q = "DELETE from albums_related where album = '$editid' OR related = '$editid'"; 
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't remove old album associations; New associations not recorded. ".mysql_error();
		elseif($in['related']) {
		
			foreach($in['related'] as $aid) {
				$type = $in[$aid]['type'];
				
				$Query = "INSERT INTO albums_related (`album`, `type`, `related`) VALUES ('$editid', '$type', '$aid')";
				if(!mysqli_query($GLOBALS['db']['link'], $Query)) $errors[] = "Error adding related album; ".mysql_error();
				
				$q = "SELECT * FROM albums_related WHERE album = '$aid' AND related = '$editid'";
				if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) {
					$q = "INSERT INTO albums_related (`album`, `type`, `related`) VALUES ('$aid', '$type', '$editid')";
					if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Error adding related album; ".mysql_error();
					else {
						$q = "SELECT title, subtitle FROM albums WHERE albumid='$aid' LIMIT 1";
						$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
						$results[] = $dat->title.' <i>'.$dat->subtitle.'</i> was also marked with this relationship.';
					}
				}
			}
			
		}
	
	}
	
	elseif ($process == "6") {
		
		//////////////
		// SYNOPSIS //
		//////////////
		
		$album6 = $_POST['album6'];
	
		$dayline = "$yrsort";
		$dayline .= "-$mosort";
		$dayline .= "-$daysort";
		
		$album6[0]['date'] = $dayline;
		$album6[0]['synopsis'] = addslashes($album6[0]['synopsis']);
		$album6[0]['author'] = addslashes($album6[0]['author']);
		$album6[0]['link'] = preg_replace("%http://(www\.)?videogam.in/?%", "/", $album6[0]['link']);
		
		if (!$album6[0][album] && $album6[0][synopsis]) {
			$album6[0][album] = "$alt";
			$Query = "INSERT into albums_synopsis values ('{$album6[0][album]}', '{$album6[0][synopsis]}', '{$album6[0][author]}', '{$album6[0][link]}', '{$album6[0][date]}')";
			if(!mysqli_query($GLOBALS['db']['link'], $Query)) $errors[] = "Couldn't update the database $Query";
			else $results[] = "Synopsis successfully added";
		} else {
			if (!$album6[0][synopsis]) {
				$Query = "DELETE from `albums_synopsis` where `album` = '{$album6[0][album]}'"; 
				if(!mysqli_query($GLOBALS['db']['link'], $Query)) $errors[] = "Couldn't delete synopsis";
				else $results[] = "Synopsis successfully deleted";
			} else {
				$Query = "UPDATE `albums_synopsis` SET `synopsis` = '{$album6[0][synopsis]}', `author` = '{$album6[0][author]}', `link` = '{$album6[0][link]}', `date` = '{$album6[0][date]}' where `album` = '$alt'";
			  if(!mysqli_query($GLOBALS['db']['link'], $Query)) $errors[] = "Couldn't update the database";
				else $results[] = "Synopsis successfully edited";
			}
		}
	
	} elseif ($process == "7") {
		
		///////////
		// MEDIA //
		///////////
	
		$handle = new Upload($_FILES['file']);
		//check ext
		$ext = substr($_FILES['file']['name'], -3);
		if($ext != "png" && $ext != "jpg" && $ext != "gif") {
			$errors[] = "Invalid extension -- please upload only JPG, PNG, or GIF";
		} else {
	    if ($handle->uploaded) {
	    	$handle->file_new_name_body    = $editid;
	    	$handle->image_convert         = 'jpg';
	    	$handle->file_overwrite        = true;
	    	$handle->file_auto_rename      = false;
	    	$handle->file_safe_name        = false;
	    	$handle->Process("$rootpath/$albumpath/media/cover/");
	        if ($handle->processed) {
	          $results[] = 'Full-sized image uploaded: <a href="/'.$albumpath.'/media/cover/'.$handle->file_dst_name.'" target="_blank">'.$handle->file_dst_name.'</a>';
						$warnings[] = "Your upload results may not be visible if your browser hasn't refreshed its cache. Click the result links to see the actual result.";
						//standard img
						$handle->file_new_name_body    = $editid;
						$handle->image_convert         = 'png';
						$handle->image_resize          = true;
						$handle->image_ratio_y         = true;
						$handle->image_x               = 140;
						$handle->file_overwrite        = true;
	    			$handle->file_auto_rename      = false;
	    			$handle->file_safe_name        = false;
						$handle->Process("$rootpath/$albumpath/media/cover/standard/");
						if ($handle->processed) {
							$results[] = 'Standard image uploaded: <a href="/'.$albumpath.'/media/cover/standard/'.$handle->file_dst_name.'" target="_blank">'.$handle->file_dst_name.'</a>';
						} else {
							$errors[] = 'Standard image couldn\'t be created: ' . $handle->error;
						}
						//thumbnail
						$handle->file_new_name_body    = $editid;
						$handle->image_convert         = 'png';
						$handle->image_resize          = true;
						$handle->image_ratio_y         = true;
						$handle->image_x               = 23;
						$handle->image_y               = 20;
						$handle->file_overwrite        = true;
	    			$handle->file_auto_rename      = false;
	    			$handle->file_safe_name        = false;
						$handle->Process("$rootpath/$albumpath/media/cover/thumb/");
						if ($handle->processed) {
							$results[] = 'Thumbnail uploaded: <a href="/'.$albumpath.'/media/cover/thumb/'.$handle->file_dst_name.'" target="_blank">'.$handle->file_dst_name.'</a>';
						} else {
							$errors[] = 'Thumbnail couldn\'t be created: ' . $handle->error;
						}
	        } else {
	        	$errors[] = 'file not uploaded to the wanted location: ' . $handle->error;
	        }
	    } else {
	    	$errors[] = 'file not uploaded on the server: ' . $handle->error;
	    }
		}
	} elseif ($process == "9") {
		
		//////////////
		// FACTOIDS //
		//////////////
		
		$factdelete = $_POST['factdelete'];
		$album6 = $_POST['album6'];
		$album9 = $_POST['album9'];
		$album9a = $_POST['album9a'];
		
		if ($factedit == "new") {
		
			$dayline = "$yrsort";
			$dayline .= "-$mosort";
			$dayline .= "-$daysort";
			$album6[0]['date'] = $dayline;
			
			$album6[0]['fact'] = trim($album6[0][fact]);
			$album6[0]['author'] = trim($album6[0][author]);
			$album6[0]['author'] = htmlSC($album6[0][author]);
			$album6[0]['link'] = preg_replace("%http://videogam.in/?%", "/", $album6[0]['link']);
			
			$Query = "INSERT into albums_trivia (album, fact, author, `link`, `date`) values 
				('".$_POST['alt']."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $album6[0]['fact'])."', '".$album6[0]['author']."','".mysqli_real_escape_string($GLOBALS['db']['link'], $album6[0]['link'])."','".$album6[0]['date']."')";
			if(!mysqli_query($GLOBALS['db']['link'], $Query)) $errors[] = "Couldn't $Query";
			else $results[] = "Factoid added";
			unset ($factedit);
			
		} elseif ($factedit) {
		
			// $album6[0][fact] = str_replace("'","\'",$album6[0][fact]);
			// $album6[0][author] = str_replace("'","\'",$album6[0][author]);
			// $album6[0][link] = str_replace("'","\'",$album6[0][link]);
			
			if($factdelete){
				$q = "DELETE FROM albums_trivia WHERE indexid = '$factedit' LIMIT 1";
				if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't $q";
				else $results[] = "Fact destoyed";
				return;
			}
			
			$dayline = "$yrsort";
			$dayline .= "-$mosort";
			$dayline .= "-$daysort";
			$album6[0]['date'] = $dayline;
			
			$album6[0]['fact'] = trim($album6[0][fact]);
			$album6[0]['author'] = trim($album6[0][author]);
			$album6[0]['author'] = htmlSC($album6[0][author]);
			$album6[0]['link'] = preg_replace("%http://videogam.in/?%", "/", $album6[0]['link']);
			
			$Query = "UPDATE albums_trivia SET 
				fact = '".mysqli_real_escape_string($GLOBALS['db']['link'], $album6[0]['fact'])."', 
				author = '".mysqli_real_escape_string($GLOBALS['db']['link'], $album6[0]['author'])."', 
				`link` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $album6[0]['link'])."', 
				`date` = '{$album6[0][date]}' 
				WHERE indexid = '$factedit' LIMIT 1";
			if(!mysqli_query($GLOBALS['db']['link'], $Query)) $errors[] = "Couldn't $Query";
			else $results[] = "Factoid edited";
			unset ($factedit);
		
		} else {
			
			// Retailers & Links //
			
			$q = "UPDATE albums SET no_commerce = '".$_POST['no_commerce']."' WHERE albumid = '$editid' LIMIT 1";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't update no_commerce field; ".mysql_error();
			
			$Query = "DELETE from albums_buy where album = '$editid'"; 
			$Result = mysqli_query($GLOBALS['db']['link'], $Query);
			
			for ($i=0; $i < count($album9a); $i++) {
				if ($album9a[$i][code]) {
				if ($album9a[$i][stock] != "0") {
				$album9a[$i][stock] = "1";
				}
				
				if(substr($album9a[$i][code], 0, 4) != "http") $errors[] = "Couldn't save ".$album9a[$i]['vendor']." link; the URL must begin with http://";
				else {
					$Query = "INSERT into albums_buy (album, code, vendor, price, stock) VALUES ('$editid', '{$album9a[$i][code]}', '{$album9a[$i][vendor]}', '{$album9a[$i][price]}', '{$album9a[$i][stock]}')";
					if(mysqli_query($GLOBALS['db']['link'], $Query)) $results[] = $album9a[$i][vendor].' link saved';
				}
				}
			}
			
			if($links = $_POST['in']['links']) {
				for($i = 0; $i < count($links['name']); $i++) {
					if(substr($links['url'][$i], 0, 4) != "http") $errors[] = "Couldn't add the link '".$links[url][$i]."' (".$links['name'][$i].") since it wasn't an http:// link";
					else {
						$Query = "INSERT into albums_buy (album, code, vendor, not_commerce) VALUES ('$editid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $links['url'][$i])."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $links['name'][$i])."', '1')";
						if(!mysqli_query($GLOBALS['db']['link'], $Query)) $errors[] = "Couldn't add link: ".$links['name'][$i]." <".$links['url'][$i]."> ;".mysql_error();
					}
				}
			}
		}
	}
	
	
	$datetime = date("Y-m-d H:i:s");
	
	if (!$editid) {
	$editid = $album1[0][albumid];
	}
	
	if ($type != "new") {
	$type = "edit";
	}
	
	$Query = "INSERT into albums_changelog (album, usrname, usrid, datetime, type) values ('$editid', '$usrname', '$usrid', '$datetime', '$type')";
	$Result = mysqli_query($GLOBALS['db']['link'], $Query);
	
}

?>