<?

require_once("bbcode.php");

class contribution {

//new() vars
var $sessid;
var $type;				//the type_id of the contribution
var $desc; 				//a brief description-reference of this contribution
var $data;        //the raw data; format: {tablefield:}data|--|{tablefield:}data|--| ... refers to subj
var $process_data;//(true or false) process the data into the database, as specified by $subj, otherwise the data should be processed beforehand
var $notify; 			//(true or false) notify editor of submission
var $subj; 				//reference to a specific table:field:id:optionalSpecification, ie: people_work:id:5:role
var $ssubj; 			//reference to gid, pid, albumid, etc.
var $status;      //('publish', 'pend') othewise the script will decide
var $no_points;		//if true, no points are allotted for the contribution, otherwise the script will decide
var $watch;				//add the ssubj to watch list


function setSessId() {
	global $usrid;
	$this->sessid = date("YmdHis").sprintf("%07d", $usrid);
}
	

function submitNew() {
	global $usrid, $usrname, $usrrank, $default_email;
	
	if(!$this->type) return array("errors" => array("type_id required for contribution"));
	if(!$this->desc) return array("errors" => array("description required for contribution"));
	if(!$this->data) return array("errors" => array("no contrbution data given"));
	
	$ret['desc'] = $this->desc;
	
	/*$pend = $this->isPending();
	if(!$pend) $pub = 1;
	else $pub = FALSE;*/
	$pub = 1;
	$ret['published'] = $pub;
	
	//No points for previous similar conr in past 24 hours
	if(!$this->no_points) {
		$q = "SELECT * FROM users_contributions 
			WHERE `subject`='".mysqli_real_escape_string($GLOBALS['db']['link'], $this->subj)."' 
			AND type_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $this->type)."' 
			AND published='1' 
			AND usrid='$usrid' 
			AND datetime >= DATE_SUB(CURDATE(),INTERVAL 1 DAY)
			LIMIT 1";
		if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $this->no_points = 1;
	}
	//calculate points and get type desc
	$q = "SELECT * FROM users_contributions_types WHERE type_id='".$this->type."' LIMIT 1";
	$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
	$ret['type_id'] = $this->type;
	$ret['type_desc'] = $dat->description;
	if($pub) $ret['points'] = $dat->points;
	elseif(!$this->no_points) $ret['potential_points'] = $dat->points;
	if($this->no_points) {
		$ret['points'] = '0';
		$ret['no_points'] = TRUE;
	}
	
	//process the given data (optional)
	if($pub && $this->process_data && $this->subj) {
		$data = makeContrDataArr($this->data);
		$proc_act = "insert";
		list($table, $ofield, $oid, $_field) = explode(":", $this->subj);
		if($ofield && $oid) {
			$query = "SELECT * FROM `".mysqli_real_escape_string($GLOBALS['db']['link'], $table)."` WHERE `".mysqli_real_escape_string($GLOBALS['db']['link'], $ofield)."`='".mysqli_real_escape_string($GLOBALS['db']['link'], $oid)."' LIMIT 1";
			if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query))) $proc_act = "update";
		}
		$q = "";
		if($proc_act == "update") {
			//UPDATE
			$q = "UPDATE `".mysqli_real_escape_string($GLOBALS['db']['link'], $table)."` SET ";
			while(list($key, $val) = each($data)) {
				if($key == "*delete_row") {
					$q = "DELETE FROM `".mysqli_real_escape_string($GLOBALS['db']['link'], $table)."` ";
					break;
				}
				if(!strstr($key, "*")) { //exclude any keys with the * char
					$q.= "`$key`='".mysqli_real_escape_string($GLOBALS['db']['link'], $val)."',";
				}
			}
			$q = substr($q, 0, -1)." WHERE `".mysqli_real_escape_string($GLOBALS['db']['link'], $ofield)."`='".mysqli_real_escape_string($GLOBALS['db']['link'], $oid)."' LIMIT 1";
		} elseif($ofield) {
			//INSERT
			$newkey = mysqlNextAutoIncrement(mysqli_real_escape_string($GLOBALS['db']['link'], $table));
			$q = "INSERT INTO `".mysqli_real_escape_string($GLOBALS['db']['link'], $table)."` ";
			$keys = array();
			$vals = array();
			foreach(array_keys($data) as $key) {
				$keys[] = "`".mysqli_real_escape_string($GLOBALS['db']['link'], $key)."`";
				$vals[] = "'".mysqli_real_escape_string($GLOBALS['db']['link'], $data[$key])."'";
			}
			$q.= "(".implode(",", $keys).") VALUES (".implode(",", $vals).");";
			if(strstr($this->subj, "[ID]")) $this->subj = str_replace("[ID]", $newkey, $this->subj);
			else $this->subj .= (substr($this->subj, -1) != ":" ? ":" : "").$newkey;
		}
		if($q) {
			if(!mysqli_query($GLOBALS['db']['link'], $q)) return array("errors" => array($thisf['desc'].": Couldn't process the given data; this field will remain unchanged. ".mysql_error()));
		}
	}
	
	//upload img?
	if($this->upload) {
		require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/class.upload.php");
		$handle = new Upload($this->upload);
	  if ($handle->uploaded) {
	  	
	  	$handle->file_new_name_body     = $this->sessid;
	  	$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/uploads/contributions/");
	  	if($handle->processed) {
				$uploaded_file = $handle->file_dst_name;
			}
	  	
	  	$handle->file_new_name_body     = $this->sessid."_tn";
			$handle->image_convert          = 'png';
			$handle->image_resize           = true;
			$handle->image_ratio_no_zoom_in = true;
			$handle->image_x                = 100;
	  	$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/uploads/contributions/");
			if($handle->processed) {
				$this->data.= '|--|{*upload:}<a href="/bin/uploads/contributions/'.$uploaded_file.'" class="thickbox"><img src="/bin/uploads/contributions/'.$handle->file_dst_name.'" alt="Uploaded File"/></a>';
			}
			
		}
	}
	
	$cid = mysqlNextAutoIncrement("users_contributions");
	$q = sprintf(
		"INSERT INTO users_contributions (type_id, usrid, datetime, description, published, pending, subject, supersubject, no_points) VALUES 
		('$this->type', '$usrid', '".date("Y-m-d H:i:s")."', '%s', '$pub', '$pend', '$this->subj', '$this->ssubj', ".($this->no_points ? "'1'" : "NULL").");",
		mysqli_real_escape_string($GLOBALS['db']['link'], $this->desc)
	);
	if(!mysqli_query($GLOBALS['db']['link'], $q)) return array("errors" => array("couldn't add contribution; ".mysql_error()));
	
	$this->data = str_replace("[CID]", $cid, $this->data);
	$ret['data'] = $this->data;
	$ret['contribution_id'] = $cid;
	
	$q = "INSERT INTO users_contributions_data (contribution_id, data) VALUES ('$cid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $this->data)."')";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) return array("errors" => array("Error adding contribution data; ".mysql_error()));
	
	if($pub && !$this->no_points) $this->recalculateContributions($usrid);
	
	if(!isset($this->notify)) $this->notify = TRUE;
	if($this->notify/* && $usrid != 1*/) {
		
		if(!$data) $data = makeContrDataArr($this->data);
		foreach(array_keys($data) as $key) {
			$datatbl.= '<dt>'.$key.'</dt><dd style="margin-bottom:5px;">'.$data[$key].'</dd>';
		}
		
		$em_desc = bb2html($this->desc, "prepend_domain");
		$em_desc = str_replace('="/', '="http://videogam.in/', $em_desc);
		$datatbl = str_replace('="/', '="http://videogam.in/', $datatbl);
		
		//notify admin via email
		$to      = $default_email;
		$subject = 'New Videogam.in submission'.(!$pub ? ' [approval required]' : '');
		$message = '<html>
			<a href="http://videogam.in/~'.$usrname.'">'.$usrname.'</a> has contributed something!
			<p><big style="padding:3px 5px; border:1px solid #DDD; background-color:#F5F5F5;">'.$em_desc.'</big></p>
			Data:<dl style="margin:1em;">'.$datatbl.'</dl>
			'.(!$pub ? 'A <a href="http://videogam.in/ninadmin/user-contributions.php">review</a> is required for this submission.' : '').'
			</html>';
		$headers = 'From: noreply@videogam.in' . "\r\n" .
			'Reply-To: noreply@videogam.in' . "\r\n" .
			'MIME-Version: 1.0' . "\r\n" . 
			'Content-type: text/html; charset=iso-8859-1' . "\r\n" . 
			'X-Mailer: PHP/' . phpversion();
		
		//@mail($to, $subject, $message, $headers);
		
	}
	
	//mark ssubj page as updated?
	if($pub && $this->ssubj) {
		list($field, $id) = explode(":", $this->ssubj);
		$upd_table = "";
		if($field == "pid") $this->markUpd("people", "pid", $id);
		elseif($field == "gid") $this->markUpd("games", "gid", $id);
	}
	
	if($this->watch) {
		$q = "INSERT INTO watchlist (usrid, supersubject) VALUES ('$usrid', '$this->ssubj');";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't add $this->ssubj to watch list.";
	}
	
	return $ret;
	
}

function markUpd($table, $field, $id, $mod_field='') {
	if(!$mod_field) $mod_field = "modified";
	$q = "UPDATE `$table` SET `$mod_field`='".date("Y-m-d H:i:s")."' WHERE `$field`='$id' LIMIT 1";
	mysqli_query($GLOBALS['db']['link'], $q);
}

function contributeToGame($contr='') {
	//updates the modified date and the people who contributed to a game
	//this function is also at /ninadmin/games-mod.php! if you make any changes, change that one, too.
	global $gid, $usrid;
	
	$q = "UPDATE games SET modified='".date("Y-m-d H:i:s")."' WHERE gid='$gid' LIMIT 1";
	mysqli_query($GLOBALS['db']['link'], $q);
	
	if(!$contr) $contr = "usrid:".$usrid;
	
	$query = "SELECT `contributors` FROM games WHERE gid='$gid' LIMIT 1";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	$row = mysqli_fetch_object($res);
	if(!$row->contributors) {
		// none yet
		$query2 = "UPDATE games SET `contributors`='$contr' WHERE gid='$gid'";
		if(!mysqli_query($GLOBALS['db']['link'], $query2)) $errors[] = "Couldn't add to contributors; ".mysql_error();
	} else {
		$cons = array();
		$cons = explode(",", $row->contributors);
		if(!in_array($contr, $cons)) {
			$cons[] = $contr;
			$query2 = "UPDATE games SET `contributors` = '".implode(",", $cons)."' WHERE gid='$gid'";
			if(!mysqli_query($GLOBALS['db']['link'], $query2)) $errors[] = "Couldn't add to contributors; ".mysql_error();
		}
	}
}

function recalculateContributions($uid) {
	
	$r = mysqli_query($GLOBALS['db']['link'], "SELECT * FROM users_contributions LEFT JOIN users_contributions_types USING (type_id) WHERE usrid='$uid' AND published='1'");
	while($row = mysqli_fetch_assoc($r)) {
		$points = $points + $row['points'];
	}
	
	$q = "UPDATE users SET contribution_score='$points' WHERE usrid='$uid' LIMIT 1";
	mysqli_query($GLOBALS['db']['link'], $q);
	
}

function isPending() {
	global $usrrank;
	
	$pend = 1;
	if($this->status == "publish") $pend = 0;
	elseif($this->status == "pend") $pend = 1;
	else {
		//decide to publish or pend it
		if($usrrank >= 4) $pend = 0;
		else {
			if(substr($this->ssubj, 0, 4) == "gid:") {
				//unpublished game -> publish
				$gid = substr($this->ssubj, 4);
				$q = "SELECT * FROM games WHERE gid='$gid' LIMIT 1";
				if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
					if($dat->unpublished) $pend = 0;
				}
			} elseif($this->ssubj) {
				//if there's already a value in the field, pend it
				list($table, $index_field, $index_val, $field) = explode(":", $this->subj);
				$q = "SELECT `$field` FROM `$table` WHERE `$index_field` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $index_val)."' LIMIT 1";
				$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
				if($row[$field] == "") $pend = 0;
				//if anyone else edited it, pend it
				/*$q = "SELECT usrid FROM users_contributions WHERE `subject`='".mysqli_real_escape_string($GLOBALS['db']['link'], $this->subj)."' AND `published`='1' AND usrid !='$usrid' LIMIT 1";
				if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $pend = 1;
				else $pend = 0;*/
			}
		}
	}
	
	return $pend;

}

} // class



function makeContrDataArr($str) {
	
	$arr = array();
	$arr = explode("|--|", $str);
	$ret = array();
	foreach($arr as $a) {
		if(preg_match("/^\{([a-z0-9_\*]+):\}/i", $a, $matches)) {
			$ret[$matches[1]] = str_replace($matches[0], "", $a);
		}
	}
	
	return $ret;
	
}

function processBoxes($uploaded_file, $gid) {
	
	require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/class.upload.php");
	
	if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/games/files/'.$gid.'/')) @mkdir($_SERVER['DOCUMENT_ROOT'].'/games/files/'.$gid.'/', 0777);
	
	$handle = new Upload($uploaded_file);
  if ($handle->uploaded) {
  	$handle->file_new_name_body     = 'box_'.$gid.'_'.rand(0,99999).'_____';
		$handle->image_convert          = 'jpg';
		$handle->image_resize           = true;
		$handle->image_ratio_no_zoom_in = true;
		$handle->image_x                = 500;
		$handle->image_y                = 700;
  	$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/temp/");
		if ($handle->processed) {
			
			$file[] = $handle->file_dst_name;
			$file_body = substr($file[0], 0, -9);
			
			//small img
			$handle->file_new_name_body    = $file_body.'___sm';
			$handle->image_convert         = 'png';
			$handle->image_resize          = true;
			$handle->image_ratio_y         = true;
			$handle->image_x               = 140;
			$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/temp/");
			if(!$handle->processed) die('Small image couldn\'t be created: ' . $handle->error);
			$file[] = $handle->file_dst_name;
			
			//thumbnail
			$handle->file_new_name_body    = $file_body.'___tn';
			$handle->image_convert         = 'png';
			$handle->image_resize          = true;
			$handle->image_ratio_y         = true;
			$handle->image_x               = 80;
			$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/temp/");
			if (!$handle->processed) die('Thumbnail image couldn\'t be created: ' . $handle->error);
			$file[] = $handle->file_dst_name;
			
			//icons
			$handle->file_new_name_body    = $file_body.'__i25';
			$handle->image_convert         = 'png';
			$handle->image_resize          = true;
			$handle->image_ratio_crop      = "TL";
			$handle->image_x               = 25;
			$handle->image_y               = 25;
			$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/temp/");
			if (!$handle->processed) die('Icon image couldn\'t be created: ' . $handle->error);
			$file[] = $handle->file_dst_name;
			$handle->file_new_name_body    = $file_body.'__i35';
			$handle->image_convert         = 'png';
			$handle->image_resize          = true;
			$handle->image_ratio_crop      = "TL";
			$handle->image_x               = 35;
			$handle->image_y               = 35;
			$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/temp/");
			if (!$handle->processed) die('Icon image couldn\'t be created: ' . $handle->error);
			$file[] = $handle->file_dst_name;
			$handle->file_new_name_body    = $file_body.'__i55';
			$handle->image_convert         = 'png';
			$handle->image_resize          = true;
			$handle->image_ratio_crop      = "TL";
			$handle->image_x               = 55;
			$handle->image_y               = 55;
			$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/temp/");
			if (!$handle->processed) die('Icon image couldn\'t be created: ' . $handle->error);
			$file[] = $handle->file_dst_name;
			$handle->file_new_name_body    = $file_body.'_i100';
			$handle->image_convert         = 'png';
			$handle->image_resize          = true;
			$handle->image_ratio_crop      = "TL";
			$handle->image_x               = 100;
			$handle->image_y               = 100;
			$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/temp/");
			if (!$handle->processed) die('Icon image couldn\'t be created: ' . $handle->error);
			$file[] = $handle->file_dst_name;
			
			return $file;
			
		} else die('file not processed: ' . $handle->error);
			
  } else {
		die('file not uploaded on the server: ' . $handle->error);
  }
}

function processBoxesDirs($files, $body, $dir) {
	
	foreach($files as $file) {
		$ext = substr($file, -3, 3);
		$subext = substr($file, -8, 4);
		$subext = str_replace("_", "", $subext);
		$dst = $_SERVER['DOCUMENT_ROOT'].$dir.$body.($subext ? "-".$subext : "").".".$ext;
		if(file_exists($dst)) unlink($dst);
		//echo "unlink $dst<br/>".$_SERVER['DOCUMENT_ROOT']."/bin/temp/".$file." -> $dst<br/>";
		if(!rename($_SERVER['DOCUMENT_ROOT']."/bin/temp/".$file, $dst)) die("Couldn't move uploaded file ($file)");
	}
	
}

?>