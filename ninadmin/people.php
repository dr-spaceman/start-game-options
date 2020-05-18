<?
use Vgsite\Page;
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/admin.php");
use Verot\Upload;
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/htmltoolbox.php");

$page = new Page();
$page->title = "Videogam.in Admin / People Administration";
$page->min_rank = 6;
$page->admin = TRUE;

$page->javascript.= <<<EOF
<script type="text/javascript" src="/bin/script/htmltoolbox.js"></script>
<script type="text/javascript">
function checkwork(f) {
	if (f.gid.value != "" && f.albumid.value != "") {
		alert("Pick a game OR an album")
		return false
	}
	return true
}
function confirmDelete(loc) {
	if(confirm('Permanently delete?'))
		window.location=loc
}
</script>
EOF;

//$_GET & $_POST
if($_POST['select_name']) $name = $_POST['select_name'];
elseif($_POST['input_name']) $name = ucwords($_POST['input_name']);
$name = $_POST['name'];
if(!$name) $name = $_GET['name'];
if($name) $name = reformatName($name); //remove excess spaces, replace illegal characters, etc
$in = $_POST['in'];

$peopledir = "/people";
$dirname = str_replace(" ", "-", $name); //if u change $dirname, change it in addPersonToDatabase() below


//add person
if($_POST['addperson'] || $_GET['addperson']) {
	
	if(!$name = $_POST['name']) $name = $_GET['addperson'];
	if(!$name) die("no name given");
	
	if(addPersonToDatabase($name)) $action = "edit person";
	else $errors[] = "Couldn't add $name to db";
	
}

//delete person
if($_GET['action'] == "deleteperson") {
	if($_SESSION['user_rank'] < 8) die("Not ranked to do this");
	if(!$pid = $_GET['pid']) die("no id given");
	
	$query = "SELECT `name` FROM `people` WHERE `pid` = '$pid' LIMIT 1";
	if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query))) die("Couldnt find data");;
	
	$query = "DELETE FROM `people` WHERE `pid` = '$pid'";
	$query2 = "DELETE FROM `people_work` WHERE `pid` = '$pid'";
	if(mysqli_query($GLOBALS['db']['link'], $query)) {
		$results[] = "Deleted from `people`";
		adminAction($dat->name, "Deleted from people db");
	} else $errors[] = "Could not delete from `people` db";
	if(mysqli_query($GLOBALS['db']['link'], $query2)) {
		$results[] = "Deleted from `people_work`";
	} else $errors[] = "Could not delete from `people_work` db";
}
	

/////////////////
// FIND PERSON //
/////////////////

if($_POST['action'] == "find person") {
	
	$q = "SELECT * FROM people WHERE `name` LIKE '$name'";
	$res = mysqli_query($GLOBALS['db']['link'], $q);
	if(!mysqli_num_rows($res)) {
		$errors[] = "No results found for <i>$name</i>";
	} elseif(mysqli_num_rows($res) > 1) {
		$page->header();
		?><h2>Narrow Results</h2>
		<form action="people.php" name="people" method="post">
			<input type="hidden" name="action" value="edit person"/>
			<p>
				<select name="name" onchange="javascript:document.people.submit()">
					<option value="" selected="selected">Select a person...</option>
					<?
					while($row = mysqli_fetch_assoc($res)) {
						echo '<option value="'.htmlentities($row['name']).'">'.$row['name'].'</option>';
					}
					?>
				</select>
			</p>
		</form>
		<?
		$page->footer();
		exit;
	} else {
		$action = "edit person";
	}
	
}
	

/////////////////
// EDIT PERSON //
/////////////////

if($_POST['action'] == "edit person" || $_GET['action'] == "edit person" || $action == "edit person") {
	
	if(!$name && !$_GET['pid']) die("no name given");
	
	//submit details
	if($_POST['submitdetails']) {
		$name = htmlent($name);
		if(!$in['name_url']) $in['name_url'] = $name;
		$in['name_url'] = str_replace(" ", "-", $in['name_url']);
		$in['name_url'] = urlencode($in['name_url']);
		if(!$in['prolific']) $in['prolific'] = 0;
		if(!$in['not_creator']) $in['not_creator'] = 0;
		if(!preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $in['dob'])) {
			$in['dob'] = "";
			$errors[] = "Your birthdate input was not proper format and was deleted.";
		}
		$in['alias'] = htmlent($in['alias']);
		$in['title'] = htmlent($in['title']);
		$in['birthplace'] = htmlent($in['birthplace']);
		
		if($_POST['update_admin']) {
			if(is_array($in['restrictions'])) $in['restrictions'] = implode(' ', $in['restrictions']);
			$in['contributors'] = explode(',', $in['contributors']);
			for($i = 0; $i <= count($in['contributors']); $i++) {
				$in['contributors'][$i] = trim($in['contributors'][$i]);
				if($in['contributors'][$i] == "") unset($in['contributors'][$i]);
			}
			$contributors = implode(',', $in['contributors']);
		}
		
		$query = ("UPDATE `people` SET 
			`name` = '$name',
			`name_url` = '".$in['name_url']."',
			`alias` = '".$in['alias']."', 
			`title` = '".$in['title']."', 
			`bio` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['bio'])."', 
			`prolific` = '".$in['prolific']."',
			`not_creator` = '".$in['not_creator']."',
			`dob` = '".$in['dob']."', 
			`birthplace` = '".$in['birthplace']."', 
			`nationality` = '".$in['nationality']."',
			".($_POST['update_admin'] ? "`restrictions` = '".$in['restrictions']."', `contributors` = '$contributors', " : "")."
			`modified` = '".date("Y-m-d")."' 
			WHERE `pid` = '".$in['pid']."' LIMIT 1");
		if(mysqli_query($GLOBALS['db']['link'], $query)) {
			$results[] = "Details updated";
			adminAction($name, "Updated general details in people db");
			if(!$in['noappend']) {
				if(!contributeToPeople($in['pid'], "usrid:".$usrid)) $errors[] = "Could not add your contribution to the table";
			}
		} else
			$errors[] = "Could not update details ".mysqli_error($GLOBALS['db']['link']);
	}
	
	//upload pic
	if($_POST['submitupload']) {
		
		$here['picture'] = "here";
		
		if($_FILES['picture']) {
			$picture = $_FILES['picture'];
			if($picture[name]) {
				$handle = new Upload($picture);
				if ($handle->uploaded) {
					$handle->file_overwrite        = true;
					$handle->file_auto_rename      = false;
					$handle->image_convert         = 'png';
					$handle->image_resize          = true;
					$handle->image_ratio_crop      = true;
					$handle->image_y               = 175;
					$handle->image_x               = 150;
					$handle->file_new_name_body    = $in['pid'];
					$handle->file_safe_name        = false;
					$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/img/people/");
					if ($handle->processed) {
						$results[] = "Image uploaded successully -- <a href=\"/bin/img/people/".$in['pid'].".png\">/bin/img/people/".$in['pid'].".png</a>";
						$warnings[] = "If your image doesn't appear below, don't fret. You may need to refresh your browser.";
						adminAction($name, "Upload profile picture to people db");
						if(!contributeToPeople($in['pid'], "usrid:".$usrid)) $errors[] = "Could not add your contribution to table";
						//thumbnail
						$handle->file_overwrite        = true;
						$handle->file_auto_rename      = false;
						$handle->image_convert         = 'png';
						$handle->image_resize          = true;
						$handle->image_ratio_crop      = true;
						$handle->image_y               = 40;
						$handle->image_x               = 40;
						$handle->file_new_name_body    = $in['pid'].'-tn';
						$handle->file_safe_name        = false;
						$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/img/people/");
						if (!$handle->processed) $errors[] = "Could not make thumbnail";
					} else
						$errors[] = "Upload Error: ".$handle->error;
				} else $errors[] = "Upload Error: ".$handle->error;
			}
		}
	}
	
	//delete profile pic
	if($_GET['do'] == "deleteprofilepic") {
		$here['picture'] = "here";
		$handle1 = $_SERVER['DOCUMENT_ROOT']."/bin/img/people/".$_GET['pid'].".png";
		$handle2 = $_SERVER['DOCUMENT_ROOT']."/bin/img/people/".$_GET['pid']."-tn.png";
		if(unlink($handle1) && unlink($handle2)) $results[] = "successfully deleted pic";
		else $errors[] = "Couldn't delete pic";
	}
	
	//add work
	if($_POST['submitaddwork']) {
		if(!$pid = $_POST['pid']) die("Error: no PID input");
		$query = "INSERT INTO `people_work` (`id`, `pid`, `gid`, `albumid`, `role`, `notes`, `vital`) VALUES 
			(NULL, '$pid', '".$_POST['gid']."', '".$_POST['$albumid']."', '".addslashes($_POST['role'])."', '".addslashes($_POST['notes'])."', '".$_POST['vital']."')";
		if(mysqli_query($GLOBALS['db']['link'], $query)) {
			$results[] = "Work added to Database";
			adminAction("Added work: $name, gid $gid, $role", "people_work");
			if(!contributeToPeople($pid, "usrid:".$usrid)) $errors[] = "Could not add your contribution to table";
		} else $errors[] = "Work could not be added to DB. Query: $query";
		$here['work'] = "here";
	}
	
	//edit work
	if($_POST['submiteditwork']) {
		if(!$workid  = $_POST['workid']) die("Error: No workid given");
		$query = "UPDATE `people_work` SET 
			`role` = '".addslashes($_POST['role'])."', 
			`notes` = '".addslashes($_POST['notes'])."', 
			`vital` = ".($_POST['vital'] == 1 ? "'1'" : "NULL")." 
			WHERE `id` = '".$_POST['workid']."' LIMIT 1";
		if(mysqli_query($GLOBALS['db']['link'], $query)) {
			$results[] = "Work updated";
			adminAction("Updated people: id $workid ".addslashes($role), "people_work");
		} else $errors[] = "DB Error! Query: ".$query;
		$here['work'] = "here";
	}
	
	//delete work
	if($deletework = $_GET['deletework']) {
		$query = "DELETE FROM `people_work` WHERE `id` = '$deletework' LIMIT 1";
		if(mysqli_query($GLOBALS['db']['link'], $query)) {
			$results[] = "Deleted";
			adminAction("Deleted a work entry from  db for $name", "people_work");
		} else $errors[] = "Could not delete work";
		$here['work'] = "here";
	}
	
	//mass add work
	if($submitmassadd) {
		for($i=0; $i < 10; $i++) {
			if($workid[$i]) {
				list($pf, $pfid) = explode("||", $workid[$i]);
				$query = "INSERT INTO `people_work` (`id`, `pid`, `$pf`, `role`, `notes`, `vital`) VALUES (NULL, '$pid', '$pfid', '".addslashes($role[$i])."', '".addslashes($notes[$i])."', '$vital[$i]')";
				$res = mysqli_query($GLOBALS['db']['link'], $query);
				if(!$res) $errors[] = "Could not add. Query: ".$query;
			}
		}
		if(!$error) $results[] = "All work added.";
	}
	
	//add interview
	if($in['add_interview']) {
		if(!$in['title']) die("Error: no title");
		if($_POST['do_what'] == "text" && !$in['interview']) die("Error: no interview text");
		else unset($in['interview']);
		if(!$in['pid']) die("Error: no PID input");
		if($in['date']) {
			if(!eregi("[0-9]{4}-[0-9]{2}-[0-9]{2}", $in['date'])) {
				$in['date'] = date("Y-m-d");
				$warnings[] = "The date supplied was not in proper format and was changed to today's date. Edit the interview below to try again.";
			}
		} else $in['date'] = date("Y-m-d");
		$query = sprintf("INSERT INTO `people_interviews` (`id`, `pid`, `date`, `title`, `interview`, `source_name`, `source_url`, usrid, datetime) 
			VALUES (NULL, '".$in['pid']."', '".$in['date']."', '%s', '%s', '%s', '%s', '$usrid', '".date("Y-m-d H:i:s")."')",
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['title']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['interview']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['source_name']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['source_url']));
		if(mysqli_query($GLOBALS['db']['link'], $query)) {
			$results[] = "Interview added to Database";
			adminAction("$name interview: ".$in['title'], "Added");
			if(!contributeToPeople($in['pid'], "usrid:".$usrid)) $errors[] = "Could not add your contribution to table";
		} else $errors[] = "Interview could not be added to DB";
		$here['interviews'] = "here";
	}
	
	//edit interview
	if($_POST['submit_edited_interview']) {
		
		if(!$iid = $_POST['interviewid']) die("No interview id given");
		
		if($in[$iid]['delete']) {
			$query = "DELETE FROM `people_interviews` WHERE `id` = '".$iid."' LIMIT 1";
			if(mysqli_query($GLOBALS['db']['link'], $query)) {
				$results[] = "Deleted the interview";
				adminAction("$name interview id #".$iid, "Deleted");
			} else $errors[] = "Could not delete interview";
		} else {
			if(!$in[$iid]['title']) die("Error: no title");
			if($in[$iid]['date']) {
				if(!eregi("[0-9]{4}-[0-9]{2}-[0-9]{2}", $in[$iid]['date'])) {
					$in[$iid]['date'] = date("Y-m-d");
					$warnings[] = "The date supplied was not in proper format and was changed to today's date. Edit the interview below to try again.";
				}
			} else $in[$iid]['date'] = date("Y-m-d");
			$query = sprintf("UPDATE `people_interviews` SET `date`='".$in[$iid]['date']."', `title`='%s', interview='%s', source_name='%s', source_url='%s' WHERE id='".$iid."' LIMIT 1",
				mysqli_real_escape_string($GLOBALS['db']['link'], $in[$iid]['title']),
				mysqli_real_escape_string($GLOBALS['db']['link'], $in[$iid]['interview']),
				mysqli_real_escape_string($GLOBALS['db']['link'], $in[$iid]['source_name']),
				mysqli_real_escape_string($GLOBALS['db']['link'], $in[$iid]['source_url']));
			if(mysqli_query($GLOBALS['db']['link'], $query)) {
				$results[] = "Interview updated";
				adminAction("$name interview: ".$in[$iid]['title'], "Updated");
			} else $errors[] = "Couldn't update database";
		}
		$here['interviews'] = "here";
	}
	
	//add link
	if($in['add_link']) {
		if(!$in['pid']) die("No pid given");
		if(!$in['site'] || !$in['url'] || $in['url'] == "http://") die("No site or URL input");
		if(!eregi("^(http://)", $in['url'])) die("invalid URL (did you include 'http://')");
		$query = sprintf("INSERT INTO people_links (pid, `site`, `url`, `notes`, usrid, datetime) VALUES 
			('".$in['pid']."', '%s', '%s', '%s', '$usrid', '".date("Y-m-d H:i:s")."')",
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['site']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['url']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['notes']));
		if(mysqli_query($GLOBALS['db']['link'], $query)) {
			$results[] = "Link added";
			if(!contributeToPeople($in['pid'], "usrid:".$usrid)) $errors[] = "Could not add your contribution to table";
		} else $errors[] = "Couldn't add link to database";
		$here['links'] = "here";
	}
	
	//delete link
	if($_GET['deletelink']) {
		$query = "DELETE FROM `people_links` WHERE `id` = '".$_GET['deletelink']."' LIMIT 1";
		if(mysqli_query($GLOBALS['db']['link'], $query)) {
			$results[] = "Deleted link";
		} else $errors[] = "Could not delete link";
		$here['links'] = "here";
	}
	
	
	
	
	
	// GET AND SHOW DATA //
	
	//person's details
	$query = "SELECT * FROM `people` WHERE ".($name ? "`name` = '$name'" : "pid='".$_GET['pid']."'")." LIMIT 1";
	if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query))) die("couldn't get data for '$name'");
	
	$name = $dat->name;
	$dat->bio = stripslashes($dat->bio);
	$dat->title = stripslashes($dat->title);
	$dat->assoc_co = stripslashes($dat->assoc_co);
	$dat->assoc_other = stripslashes($dat->assoc_other);
	if($dat->prolific == 1) $ch[prolific] = "checked";
	if($dat->not_creator == 1) $ch[not_creator] = "checked";
	if(!$dat->name_url) {
		$dat->name_url = str_replace(" ", "-", $dat->name);
		$dat->name_url = urlencode($dat->name_url);
		$dat->name_url = strtolower($dat->name_url);
	}
	
	$page->title.= " / ".$dat->name;
	
	if(!$here) $here['general'] = "here";
	$page->javascript.= '<script type="text/javascript">
		var activeHI = "';
		if($here['general']) $page->javascript.= "general";
		elseif($here['picture']) $page->javascript.= "picture";
		elseif($here['work']) $page->javascript.= "work";
		elseif($here['interviews']) $page->javascript.= "interviews";
		elseif($here['links']) $page->javascript.= "links";
		$page->javascript.= '";
		function toggleHeaderItem(x) {
			document.getElementById(activeHI+"-tab").className="";
			document.getElementById(x+"-tab").className="here";
			document.getElementById(activeHI+"-content").style.display="none";
			document.getElementById(x+"-content").style.display="block";
			activeHI = x;
		}
		
		function addAssoc(what) {
			var inp = document.getElementById("input-assoc_"+what);
			var list = document.getElementById("list-assoc_"+what);
			var sbutton = document.getElementById("submit-assoc_"+what);
			
			sbutton.value="Adding...";
			sbutton.disabled=true;
			
			asyncRequest(
				"post",
				"../people/associations.php",
				function(response) {
					if(response.responseText) {
						list.innerHTML=list.innerHTML+"&bull; "+inp.value+"<br/>";
						sbutton.value="Add";
						sbutton.disabled=false;
					}
				},
				"do=add_assoc&what="+what+"&name="+inp.value+"&pid='.$dat->pid.'"
			);
		}
		
		function deleteAssoc(what, x) {
			var inp = document.getElementById("input-"+x);
			
			document.getElementById("x-"+x).style.display="none";
			document.getElementById("loading-"+x).style.display="inline";
			
			asyncRequest(
				"post",
				"../people/associations.php",
				function(response) {
					if(response.responseText) {
						document.getElementById("assoc-"+x).style.display="none";
					}
				},
				"do=delete_assoc&what="+what+"&name="+inp.value+"&pid='.$dat->pid.'"
			);
		}
		
		</script>';
	
	//admin
	if($_SESSION['user_rank'] == 9) {
		$print_admin_fieldset = ('
	<fieldset style="margin:10px -10px; border-width:1px 0 0 0;">
		<legend>Administration</legend>
		<input type="hidden" name="update_admin" value="1" />
		<label><input type="checkbox" name="in[restrictions][]" value="lock all"'.(strstr($dat->restrictions, "lock all") ? ' checked="checked"' : '').'/> 
			<b>Lock everything</b> <small>disable all changes</small></label>
		<p><label><input type="checkbox" name="in[restrictions][]" value="lock details"'.(strstr($dat->restrictions, "lock details") ? ' checked="checked"' : '').'/> 
			<b>Lock Details</b></label></p>
		<p><label><input type="checkbox" name="in[restrictions][]" value="lock picture"'.(strstr($dat->restrictions, "lock picture") ? ' checked="checked"' : '').'/> 
			<b>Lock Picture</b></label></p>
		<p><label><input type="checkbox" name="in[restrictions][]" value="limited visibility"'.(strstr($dat->restrictions, "limited visibility") ? ' checked="checked"' : '').'/> 
			<b>Limited visibility</b> <small>person will not be shown on "recently updated", "most popular", etc</small></label></p>
		<hr/>
		<p><b>Contributors:</b> <small>separate with comma</small></p>
		<p><textarea name="in[contributors]" rows="2" cols="60">'.$dat->contributors.'</textarea></p>
		<hr/>
		<label><input type="checkbox" id="delete_person" value="1"/> Delete all records of '.$name.'</label> <input type="button" value="Delete" onClick="if(document.getElementById(\'delete_person\').checked==true && confirm(\'Permanently delete all recors?\')) document.location=\'people.php?pid='.$dat->pid.'&name='.$name.'&action=deleteperson\';"/>
	</fieldset>');
	} else $print_admin_fieldset = "";
	
	
	//check if page is locked
	if($adminobj->lock_all == 1 && $_SESSION['user_rank'] < 9) {
		$errors[] = "$name is locked -- no changes can be made to this person's entry.";
		$page->header();
		$page->footer();
		exit;
	}
	
	//person's work
	//games
	$query = "SELECT * FROM people_work 
	LEFT JOIN games ON (people_work.gid=games.gid) 
	WHERE pid='$dat->pid' AND people_work.gid != '' ORDER BY games.title ASC";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)) {
		$gamework = '<table border="0" cellpadding="3" cellspacing="1" width="100%" class="persondata">
			<tr>
				<th width="45%"><h3>Games</h3></th>
				<th width="20%">role</th>
				<th width="35%">notes</th>
				<th>Edit</th>
			</tr>'."\n";
		while($row = mysqli_fetch_assoc($res)) {
			$i++;
			$row = stripslashesDeep($row);
			$row['printnotes'] = strip_tags($row['notes']);
			$gamework .= ('
				<tr>
					<td><a href="/games/~'.$row['title_url'].'" target="_blank">'.$row['title'].'</a></td>
					<td>'.($row['vital'] ? '<b>'.$row['role'].'</b>' : $row['role']).'&nbsp;</td>
					<td style="font-size:11px">'.(strlen($row['printnotes']) > 50 ? substr($row['printnotes'], 0, 50)."..." : $row['printnotes']).'&nbsp;</td>
					<td><input type="button" value="Edit" onClick="toggle(\'edit-'.$i.'\', \'\'); document.getElementById(\'edit-'.$i.'-cell\').className=\'\';"/></td>
				</tr>
				<tr>
					<td colspan="4" id="edit-'.$i.'-cell" class="nostyle">
						<form action="people.php" method="post" id="edit-'.$i.'" style="display:none">
							<input type="hidden" name="name" value="'.$name.'"/>
							<input type="hidden" name="action" value="edit person"/>
							<input type="hidden" name="workid" value="'.$row['id'].'"/>
							<table border="0" cellpadding="0" cellspacing="10">
								<tr>
									<td valign="top">
										<input type="text" name="role" value="'.$row['role'].'" size="20"/><br/>
										<label><input type="checkbox" name="vital" value="1" '.($row['vital'] == 1 ? "checked" : "").'> vital</label>
									</td>
									<td><textarea name="notes" rows="4" cols="60" style="font-size:90%">'.$row['notes'].'</textarea></td>
								</tr>
								<tr>
									<td colspan="2">
										<input type="submit" name="submiteditwork" value="Submit Changes" style="font-weight:bold"/> 
										<input type="button" value="Cancel Changes" onclick="toggle(\'\', \'edit-'.$i.'\'); document.getElementById(\'edit-'.$i.'-cell\').className=\'nostyle\';"/> 
										<input type="button" value="Delete" onclick="if(confirm(\'Really delete this credit?\')) document.location=\'people.php?name='.$name.'&action=edit+person&deletework='.$row['id'].'\';"/>
									</td>
								</tr>
							</table>
						</form>
					</td>
				</tr>');
		}
		$gamework .= "</table><br/>\n\n";
	}
	
	//albums
	$query = "SELECT *, albums.id AS albums_id, people_work.id AS work_id FROM people_work LEFT JOIN albums USING (albumid) WHERE pid='$dat->pid' AND people_work.albumid != '' ORDER BY title";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)) {
		$albumwork = '<table border="0" cellpadding="3" cellspacing="1" width="100%" class="persondata">
			<tr>
				<th width="45%"><h3>Albums</h3></th>
				<th width="20%">role</th>
				<th width="35%">notes</th>
				<th>Edit</th>
			</tr>'."\n";
		while($row = mysqli_fetch_assoc($res)) {
			
			$i++;
			$row = stripslashesDeep($row);
			$row['printnotes'] = strip_tags($row['notes']);
			
			$albumwork .= ('
				<tr>
					<td><a href="/music/?id='.$row['albumid'].'" target="_blank">'.$row['title'].' <em>'.$row['subtitle'].'</em></a> ('.$row['cid'].')</td>
					<td>'.($row['vital'] ? '<b>'.$row['role'].'</b>' : $row['role']).'&nbsp;</td>
					<td style="font-size:11px">'.(strlen($row['printnotes']) > 50 ? substr($row['printnotes'], 0, 50)."..." : $row['printnotes']).'&nbsp;</td>
					<td><input type="button" value="Edit" onClick="toggle(\'edit-'.$i.'\', \'\'); document.getElementById(\'edit-'.$i.'-cell\').className=\'\';"/></td>
				</tr>
				<tr>
					<td colspan="4" id="edit-'.$i.'-cell" class="nostyle">
						<form action="people.php" method="post" id="edit-'.$i.'" style="display:none">
							<input type="hidden" name="name" value="'.$name.'"/>
							<input type="hidden" name="action" value="edit person"/>
							<input type="hidden" name="workid" value="'.$row['work_id'].'"/>
							<table border="0" cellpadding="0" cellspacing="10">
								<tr>
									<td valign="top">
										<input type="text" name="role" value="'.$row['role'].'" size="20"/><br/>
										<label><input type="checkbox" name="vital" value="1" '.($row['vital'] == 1 ? "checked" : "").'> vital</label>
									</td>
									<td><textarea name="notes" rows="4" cols="60" style="font-size:90%">'.$row['notes'].'</textarea></td>
								</tr>
								<tr>
									<td colspan="2">
										<input type="submit" name="submiteditwork" value="Submit Changes" style="font-weight:bold"/> 
										<input type="button" value="Cancel Changes" onclick="toggle(\'\', \'edit-'.$i.'\'); document.getElementById(\'edit-'.$i.'-cell\').className=\'nostyle\';"/> 
										<input type="button" value="Delete" onclick="if(confirm(\'Really delete this credit?\')) document.location=\'people.php?name='.$name.'&action=edit+person&deletework='.$row['work_id'].'\';"/>
									</td>
								</tr>
							</table>
						</form>
					</td>
				</tr>');
		}
		$albumwork .= '</table>';
	}
		
	//games & album list
	$query = "SELECT games.gid, games.title, platform_shorthand FROM games 
	LEFT JOIN games_publications pub ON (games.gid=pub.gid AND `primary`='1') 
	LEFT JOIN games_platforms pf ON (pub.platform_id=pf.platform_id) ORDER BY games.title";
	if($res = mysqli_query($GLOBALS['db']['link'], $query)) {
		while($row = mysqli_fetch_assoc($res)) {
			$games .= '<option value="'.$row['gid'].'">'.$row['title'].' ('.$row['platform_shorthand'].')</option>'."\n";
		}
	}
	$query = "SELECT `albumid`, `title`, `subtitle` FROM `albums` ORDER BY `title`";
	if($res = mysqli_query($GLOBALS['db']['link'], $query)) {
		while($row = mysqli_fetch_assoc($res)) {
			if(strlen($row[title]) > 37)
				$row[title] = substr($row[title], 0, 35) . "...";
			$albums .= '<option value="'.$row['albumid'].'">'.$row['title'].': '.$row['subtitle']."</option>\n";
		}
	}
	
	$page->header();
	?>
	
	<h2 class="warn">This manager has been depreciated.</h2>
		You can perform almost all the functions here via the embedded page edits on any given person page. 
		If you continue with this form, your additions may be corrupted or may not be credited to your name.</big>
	<br/><br/>
	
	<table border="0" cellspacing="0" class="heading-tabs" width="100%">
		<tr>
			<th colspan="7"><h2><a href="/people/~<?=$dirname?>"><?=$dat->name?></a></h2></th>
		</tr>
		<tr>
			<td class="first">&nbsp;</td>
			<td class="<?=$here['general']?>" id="general-tab"><a href="#" onclick="toggleHeaderItem('general');">General Details</a></td>
			<td class="<?=$here['picture']?>" id="picture-tab"><a href="#" onclick="toggleHeaderItem('picture');">Picture</a></td>
			<td class="<?=$here['work']?>" id="work-tab"><a href="#" onclick="toggleHeaderItem('work');">Work</a></td>
			<td class="<?=$here['interviews']?>" id="interviews-tab"><a href="#" onclick="toggleHeaderItem('interviews');">Interviews</a></td>
			<td class="<?=$here['links']?>" id="links-tab"><a href="#" onclick="toggleHeaderItem('links');">External Links</a></td>
			<td class="last" width="100%">&nbsp;</td>
		</tr>
	</table>
	
	<fieldset id="general-content"<?=(!$here['general'] ? ' style="display:none"' : '')?>>
		<legend>General Details</legend>
		<?
	
		if($adminobj->lock_details == 1 && $_SESSION['user_rank'] < 9)
			echo '<span style="display:block; margin-bottom:.5em; text-align:center; padding:.5em; background-color:#FFFFAE; color:#FF0000; font-weight:bold; font-size:14px;">This section is locked</span>';
	
		?>
		<div style="margin-bottom:5px; text-align:right;">
			Created on <b><?=formatDate($dat->created)?></b> / Last modified on <b><?=formatDate($dat->modified)?></b>
		</div>
		<form action="people.php" method="post" enctype="multipart/form-data" onsubmit="document.getElementById('title-url').disabled=false;">
			<input type="hidden" name="name" value="<?=$name?>"/>
			<input type="hidden" name="in[pid]" value="<?=$dat->pid?>"/>
			<input type="hidden" name="action" value="edit person"/>
			<table border="0" cellspacing="0" class="styled-form">
				<tr>
					<th>Name:</th>
					<td><input type="text" name="name" value="<?=$dat->name?>" size="40"/></td>
				</tr>
				<tr>
					<th>Name URL:</th>
					<td>
						<div id="change-turl-warn" style="display:none; margin-bottom:5px;"><b style="color:red">Warning!</b> Don't wantonly change this! An established URL is necessary for good search results from search engines; Changing a page's URL will decrease its rank if it is already well established.</div>
						<input id="title-url" type="text" name="in[name_url]" value="<?=$dat->name_url?>" size="40" disabled="disabled"/> 
						<input type="button" value="Change" onclick="document.getElementById('title-url').disabled=false; document.getElementById('change-turl-warn').style.display='block'; this.style.display='none'"/>
					</td>
				</tr>
				<tr>
					<th>Alias:</th>
					<td><small>Alternate name, nickname, Japanese characters, etc.</small>
						<p><input type="text" name="in[alias]" value="<?=html_entity_decode($dat->alias)?>" size="40"/></p>
					</td>
				</tr>
				<tr>
					<th>Title:</th>
					<td><small>ie, "Music Composer", "Voice Actor", etc.</small>
						<p><input type="text" name="in[title]" value="<?=$dat->title?>" size="40"/></p>
						<p><img src="/bin/img/icons/warn.png" alt="warning"/> This is an all-encompassing title and this field should not be used if this person's work cannot be relegated to a single title.</p>
					</td>
				</tr>
				<tr>
					<th>Prolific:</th>
					<td><label><input type="checkbox" name="in[prolific]" value="1" <?=$ch['prolific']?> style="margin:7px 7px 0 0; float:left;"/> This person is a prolific creator and he/she should be highlighted whenever indexed amongst other people.</label></td>
				</tr>
				<tr>
					<th>Not A Creator:</th>
					<td><label><input type="checkbox" name="in[not_creator]" value="1" <?=$ch['not_creator']?> style="margin:7px 7px 0 0; float:left;"/> This person has nothing to do with the actual development of videogames. They are just a plain old boring person.</label></td>
				</tr>
				<tr>
					<th><label for="dob">Birth date:</label></th>
					<td><input type="text" name="in[dob]" value="<?=$dat->dob?>" id="dob" size="8" maxlength="10"/> Proper format: YYYY-MM-DD</td>
				</tr>
				<tr>
					<th><label for="birthplace">Birth place:</label></th>
					<td><input type="text" name="in[birthplace]" value="<?=$dat->birthplace?>" id="birthplace"/></td>
				</tr>
				<tr>
					<th><label for="nationality">Nationality:</label></th>
					<td>
						<select name="in[nationality]">
							<option value="">None/other/alien/robot</option>
							<?
							include($_SERVER['DOCUMENT_ROOT']."/bin/php/country_codes.php");
							while(list($key, $val) = each($cc)) {
								echo '<option value="'.$key.'"'.($key == $dat->nationality ? ' selected="selected"' : '').'>'.$val.'</option>'."\n";
							}
							?>
						</select> naturalization and/or citizenship
					</td>
				</tr>
			</table>
			
			<fieldset style="margin:10px -10px; border-width:1px 0 0 0;">
				<legend>Associations</legend>
				<table border="0" cellspacing="0" width="100%" class="styled-form" style="margin-top:5px">
					<tr>
						<th>Companies:</th>
						<td>
							<div id="list-assoc_co">
								<?
								if($dat->assoc_co) {
									$assoc_cos = explode("`", $dat->assoc_co);
									foreach($assoc_cos as $co) {
										$i++;
										if($co) echo '<div id="assoc-'.$i.'">&bull; '.$co.' <a href="#x" onclick="deleteAssoc(\'co\', \''.$i.'\');" class="x" id="x-'.$i.'">X</a><img src="/bin/img/loading-arrows-small.gif" alt="loading" id="loading-'.$i.'" style="display:none"/><textarea id="input-'.$i.'" style="display:none">'.$co.'</textarea></div>'."\n";
									}
								}
								?>
							</div>
							Add a company: <input type="text" id="input-assoc_co"/> <input type="button" value="Add" onclick="addAssoc('co')" id="submit-assoc_co"/>
							<p style="margin:3px 0 0 0; color:#808080;">Keep it simple (IE, "Konami" instead of "Konami Computer Entertainment")</p>
						</td>
					</tr>
					<tr>
						<th><label for="assoc_other">Other:</label></th>
						<td>
							<div id="list-assoc_other">
								<?
								if($dat->assoc_other) {
									$assoc_others = explode("`", $dat->assoc_other);
									foreach($assoc_others as $co) {
										$i++;
										if($co) echo '<div id="assoc-'.$i.'">&bull; '.$co.' <a href="#x" onclick="deleteAssoc(\'other\', \''.$i.'\');" class="x" id="x-'.$i.'">X</a><img src="/bin/img/loading-arrows-small.gif" alt="loading" id="loading-'.$i.'" style="display:none"/><textarea id="input-'.$i.'" style="display:none">'.$co.'</textarea></div>'."\n";
									}
								}
								?>
							</div>
							Add another: <input type="text" id="input-assoc_other"/> <input type="button" value="Add" onclick="addAssoc('other')" id="submit-assoc_other"/>
							</ul>
						</td>
					</tr>
				</table>
			</fieldset>
			
			<?=$print_admin_fieldset?>
			
			<div style="margin:0 -10px; border-top:1px solid #808080;">&nbsp;</div>
			
			<input type="submit" name="submitdetails" value="Submit Details" style="font:bold 13px verdana;"<?=($adminobj->lock_details == 1 && $_SESSION['user_rank'] < 9 ? ' disabled="disabled"' : '')?>/> 
			<label><input type="checkbox" name="in[noappend]" value="1"/> Don't append my name to the contribution list for this edit</label>
		</form>
	</fieldset>
	
	<!-- picture -->
	
	<fieldset id="picture-content"<?=(!$here['picture'] ? ' style="display:none"' : '')?>>
		<legend>Pictures</legend>
		<?
		if($adminobj->lock_picture == 1 && !$_SESSION['user_rank'] < 9)
		echo '<span style="display:block; margin-bottom:.5em; text-align:center; padding:.5em; background-color:#FFFFAE; color:#FF0000; font-weight:bold; font-size:14px;">This section is locked</span>';

		?>
		<form action="people.php" method="post" enctype="multipart/form-data">
			<input type="hidden" name="name" value="<?=$name?>" />
			<input type="hidden" name="in[pid]" value="<?=$dat->pid?>" />
			<input type="hidden" name="action" value="edit person" />
			<table border="0" cellspacing="0" class="styled-form">
				<tr>
					<th>Profile Picture:</th>
					<td>
						<?
						if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/img/people/".$dat->pid.".png")) {
							echo '<img src="/bin/img/people/'.$dat->pid.'.png" alt="'.$dat->name.'"/>';
						} else {
							$dat->picture = '<img src="/bin/img/people/nopicture.png" alt="no picture"/>';
							$nopic = 1;
						}
						if($_SESSION['user_rank'] >= 7 && !$nopic) {
							echo '<p><input type="button" value="Delete this picture" onclick="if(confirm(\'Permanently delete profile picture?\')) document.location=\'people.php?pid='.$dat->pid.'&name='.$name.'&action=edit+person&do=deleteprofilepic\';"/></p>';
						}
						?>
						<br/>
						<fieldset>
							<legend>Upload a New Profile Picture</legend>
							The only acceptable uploads here are pics of <?=$name?></b>.
							<p>Uploads will be auto-resized to 150x175 and converted to PNG.</p>
							<p><input type="file" name="picture" value=""/></p>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><input type="submit" name="submitupload" value="Submit"<?=($adminobj->lock_picture == 1 && $_SESSION['user_rank'] < 9 ? ' disabled="disabled"' : '')?>/></td>
				</tr>
			</table>
		</form>
	</fieldset>
	
	<!-- work -->
	
	<div id="work-content"<?=(!$here['work'] ? ' style="display:none"' : '')?>>
	<fieldset>
	<legend>Add a work entry</legend>
	<form action="people.php" method="post" style="margin:0;" onSubmit="return checkwork(this)">
	<input type="hidden" name="name" value="<?=$name?>" />
	<input type="hidden" name="pid" value="<?=$dat->pid?>" />
	<input type="hidden" name="action" value="edit person" />
	<table border="0" cellspacing="0" class="styled-form">
		<tr>
			<th>Source</th>
			<td>
				<select name="gid" id="game">
					<option value="" selected="selected">Game</option>
					<?=$games?>
				</select>
				<small style="display:block; margin:5px;">OR</small>
				<select name="albumid" id="album">
					<option value="" selected="selected">Album</option>
					<?=$albums?>
				</select>
			</td>
		</tr>
		<tr>
			<th>Role</th>
			<td>
				Input exactly as credited. Use multiple entries for multiple roles
				<p><input type="text" name="role" id="role" size="25"/></p>
			</td>
		</tr>
		<tr>
			<th>Notes<br/><small>Expanded notes & information on this role</small></th>
			<td>
				<textarea name="notes" id="notes" rows="5" cols="60"></textarea>
				<p>
					<b>Tip!</b> The script will auto-reformat links within the people db or to gamepages -- Just input people links 
					like this: <code>[[P||Yuji Horii]]</code> and game links like this: <code>[[G||Mario Kart]]</code> and the output 
					will be automatically formatted.
				</p>
			</td>
		</tr>
		<tr>
			<th>Vital</th>
			<td>
				<label>
					<input type="checkbox" name="vital" value="1" id="vital"/> This person's role in this work was vital to the development 
					and production of this game or soundtrack. The display of this person will have preferential appearance on this game or 
					album page.
				</label>
			</td>
		</tr>
		<tr>
			<th>&nbsp;</th>
			<td><input type="submit" name="submitaddwork" value="Add Work" style="font-weight:bold"/></td>
		</tr>
	</table>
	</form>
	</fieldset>
	
	<br/>
	
	<fieldset>
		<legend><?=$name?>&prime;s Work</legend>
		<?=$gamework?>
		
		<?=$albumwork?>
	</fieldset>
	
	</div>
	
	<!-- interviews -->
	
	<div id="interviews-content"<?=(!$here['interviews'] ? ' style="display:none"' : '')?>>
	<fieldset>
		<legend>Current Interviews</legend>
		
		<?
		//interviews
		$query = "SELECT * FROM people_interviews WHERE `pid` = '$dat->pid' ORDER BY `date` DESC";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		if(mysqli_num_rows($res)) {
			while($row = mysqli_fetch_assoc($res)) {
				?>
				<div id="interview-<?=$row['id']?>" style="padding:5px 0; border-top:1px solid #C0C0C0;">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td><?=FormatDate($row[date], 7)?> <a href="<?=($row['interview'] ? '/people/~'.$dirname.'?interview='.$row['id'] : $row['source_url'])?>"><?=$row['title']?></a></td>
							<td style="text-align:right"><input type="button" value="Edit" onclick="toggle('edit-interview-<?=$row['id']?>', 'interview-<?=$row['id']?>');" style="float:right"/></td>
						</tr>
					</table>
				</div>
				<div id="edit-interview-<?=$row['id']?>" style="display:none; padding:5px 0; border-top:1px solid #C0C0C0;">
					<form action="people.php" method="post">
						<input type="hidden" name="name" value="<?=$name?>"/>
						<input type="hidden" name="action" value="edit person"/>
						<input type="hidden" name="interviewid" value="<?=$row['id']?>"/>
						<img src="/bin/img/icons/edit.gif" alt="edit"/> Editing <a href="/people/~<?=$dirname?>?interview=<?=$row['id']?>"><?=$row['title']?></a>
						<table border="0" cellspacing="0" class="styled-form" style="margin-top:5px">
							<tr>
								<th>Date:</th>
								<td><input type="text" name="in[<?=$row['id']?>][date]" value="<?=$row['date']?>" size="8" maxlength="10"/> YYYY-MM-DD</td>
							</tr>
							<tr>
								<th>Title:</th>
								<td><input type="text" name="in[<?=$row['id']?>][title]" value="<?=htmlspecialchars($row['title'])?>" size="60"/></td>
							</tr>
							<tr>
								<th>Source Name:</th>
								<td><input type="text" name="in[<?=$row['id']?>][source_name]" value="<?=htmlspecialchars($row['source_name'])?>" size="60"/></td>
							</tr>
							<tr>
								<th>Source URL:</th>
								<td><input type="text" name="in[<?=$row['id']?>][source_url]" value="<?=$row['source_url']?>"size="60" style="text-decoration:underline; color:blue;"/></td>
							</tr>
							<tr>
								<th>Interview Words:<br/><small>Leave blank to link directly to Source URL</small></th>
								<td>
									<?=outputToolbox("interview-words-".$row['id'])?>
									<p><textarea name="in[<?=$row['id']?>][interview]" rows="10" cols="65" id="interview-words-<?=$row['id']?>"><?=$row['interview']?></textarea></p>
									<p><?=saveDraftButton("interview-words-".$row['id'], $dirname."_interview")?></p>
								</td>
							</tr>
							<?
							if($_SESSION['user_rank'] >= 8 || ($_SESSION['user_rank'] <= 7 && $row['usrid'] == $usrid)) {
							?>
							<tr>
								<th>Delete?</th>
								<td><label><input type="checkbox" name="in[<?=$row['id']?>][delete]" value="1"/> Permanently delete this interview</label></td>
							</tr>
							<?
							}
							?>
							<tr>
								<th>&nbsp;</th>
								<td>
									<input type="submit" name="submit_edited_interview" value="Submit Edits" style="font-weight:bold"/> 
									<input type="button" value="Cancel Edits" onclick="toggle('interview-<?=$row['id']?>', 'edit-interview-<?=$row['id']?>');"/>
								</td>
							</tr>
						</table>
					</form>
				</div><?
			}
		} else echo "none posted yet";
		
		?>
	</fieldset>
	<br/>
	
	<form action="people.php" method="post">
		<input type="hidden" name="name" value="<?=$name?>" />
		<input type="hidden" name="in[pid]" value="<?=$dat->pid?>" />
		<input type="hidden" name="action" value="edit person" />
		<fieldset>
			<legend>Add an Interview</legend>
			<table border="0" cellspacing="0" class="styled-form">
				<tr>
					<th><label for="title">Title:</label></th>
					<td><input type="text" name="in[title]" id="title" size="50"/></td>
				</tr>
				<tr>
					<th><label for="tdate">Date:</label><br/><small>Original date of interview</small>
					<td><input type="text" name="in[date]" id="date" size="15"/> Proper format: YYYY-MM-DD</td>
				</tr>
				<tr>
					<th><label for="source_name">Source Name:</label></th>
					<td><input type="text" name="in[source_name]" id="source_name" size="50"/></td>
				</tr>
				<tr>
					<th><label for="source_url">Source URL:</label></th>
					<td><input type="text" name="in[source_url]" value="http://" id="source_name" size="50"/></td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td>
						<label style="font-size:13px"><input type="radio" name="do_what" value="link" checked="checked" onclick="toggle('','fulltext')"/> Link directly to the source</label>
						<p><label style="font-size:13px"><input type="radio" name="do_what" value="text" onclick="toggle('fulltext','')"> Input the full interview text</label></p>
						<div id="fulltext" style="display:none; margin-left:15px;">
							<p><?=outputToolbox("interview")?></p>
							<p>The script will automatically edit your text for HTML output, adding &lt;br/> for each newline</p>
							<p><textarea name="in[interview]" id="interview" rows="15" cols="60"></textarea></p>
							<p><?=saveDraftButton("interview", $dirname."_interview")?></p>
						</div>
					</td>
				</tr>
				<tr>
					<th><label>&nbsp;</label></th>
					<td><input type="submit" name="in[add_interview]" value="Add Interview" /></td>
				</tr>
			</table>
		</fieldset>
	</form>
	</div>
	
	<!-- links -->
	
	<fieldset id="links-content"<?=(!$here['links'] ? ' style="display:none"' : '')?>>
		<legend>External Links</legend>
		
		<?
		$query = "SELECT * FROM `people_links` WHERE `pid` = '$dat->pid'";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		if(mysqli_num_rows($res)) {
			echo '<ul>';
			while($row = mysqli_fetch_assoc($res)) {
				echo '<li><a href="'.$row['url'].'">'.stripslashes($row['site']).'</a>';
				if($_SESSION['user_rank'] >= 8 || ($usrid <= 7 && $row['usrid'] == $usrid)) echo ' <a href="people.php?name='.$name.'&action=edit+person&deletelink='.$row['id'].'" class="x">X</a>';
				if($row['usrid'] && $row['datetime']) echo'<br/><small>Posted by '.outputUser($row['usrid'], FALSE).' on '.formatDate($row['datetime']).'</small>';
				echo "</li>\n";
			}
			echo '</ul>';
		} else echo "No links yet :(<br/><br/>";
		?>
		
		<h3 style="border-width:0">Add a Link</h3>
		<form action="people.php" method="post">
			<input type="hidden" name="name" value="<?=$name?>" />
			<input type="hidden" name="in[pid]" value="<?=$dat->pid?>" />
			<input type="hidden" name="action" value="edit person" />
			<table border="0" cellspacing="0" class="styled-form">
				<tr>
					<th><label for="site">Site Name:</label></th>
					<td><input type="text" name="in[site]" id="site" size="50"/></td>
				</tr>
				<tr>
					<th><label for="url">URL:</label></th>
					<td><input type="text" name="in[url]" value="http://" id="url" size="50" style="color:blue; text-decoration:underline;"/></td>
				</tr>
				<tr>
					<th><label for="notes">Description:</label></th>
					<td><textarea name="in[notes]" id="notes" rows="2" cols="48"></textarea></td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><input type="submit" name="in[add_link]" value="Add Link"/></td>
				</tr>
			</table>
		</form>
	</fieldset>
<?

$page->footer();
exit;
	
} elseif($action == "massadd") {
	
	// mass add
	
	if(!$name) die ("no name");
	if(!$pid) die ("no pid");
	
	//games & album list
	$query = "SELECT `indexid`, `title`, `platform` FROM `games` ORDER BY `title`";
	if($res = mysqli_query($GLOBALS['db']['link'], $query)) {
		while($row = mysqli_fetch_assoc($res)) {
			if(strlen($row[title]) > 47)
				$row[title] = substr($row[title], 0, 45) . "...";
			$games .= '<option value="gid||'.$row[indexid].'">'.$row[title].' ('.$platforms[$row[platform]].')</option>';
		}
	}
	$query = "SELECT `albumid`, `title`, `subtitle` FROM `albums` ORDER BY `title`";
	if($res = mysqli_query($GLOBALS['db']['link'], $query)) {
		while($row = mysqli_fetch_assoc($res)) {
			if(strlen($row[title]) > 37)
				$row[title] = substr($row[title], 0, 35) . "...";
			//if(strlen($row[subtitle]) > 15)
			//	$row[subtitle] = substr($row[subtitle], 0, 17) . "...";
			$albums .= '<option value="albumid||'.$row[albumid].'">'.$row[title].': '.$row[subtitle]."</option>\n";
		}
	}
	
	$mass_form = <<<EOF
	<p><select name="workid[]">
		<option value="" selected="selected">Game or Album...</option>
		<optgroup label="Games">$games</optgroup>
		<optgroup label="Albums">$albums</optgroup>
	</select></p>
	<p>Role: <input type="text" name="role[]" /></p>
	<p>Notes:<br/><textarea name="notes[]" rows="2" cols="80"></textarea></p>
	<p><input type="checkbox" name="vital[]" value="1" /> Vital</p>
	
EOF;
	
	echo <<<EOF
	
	$people_page_header
	
	<p>Mass add to <b>$name</b></p>
	
	<form action="people.php" method="post">
	<input type="hidden" name="name" value="$name" />
	<input type="hidden" name="pid" value="$pid" />
	<input type="hidden" name="action" value="edit person" />
	$mass_form
	$mass_form
	$mass_form
	$mass_form
	$mass_form
	$mass_form
	$mass_form
	$mass_form
	$mass_form
	$mass_form
	<input type="submit" name="submitmassadd" value="Submit" />
	</form>
EOF;
	
$page -> SetContent($cont);
$page -> Head();
$page -> Foot();
exit;

} elseif($action == "add by game") {
	
	if(!$gid) die("No game id given");
	
	if($submitwork) {
		if(!$work) {
			$errors[] = "There was no work submitted";
		} else {
			
			echo $people_page_header;
			echo ('
				<style>
					DL {
						margin: 0;
						border-top: 1px solid #808080; }
					DT {
						margin: 0;
						padding: 10px 10px 0 10px; }
					DD {
						margin: 0;
						padding: 3px 10px 10px 10px; }
					.pink DT, .pink DD {
						background-color: #F5D6D6; }
					.yellow DT, .yellow DD {
						background-color:#FFFFAE; }
					LABEL {
						font-weight: bold; }
				</style>
				
				<br/>Your input has been reformatted below, but it has not been submitted yet -- please double check for accuracy and expand your data before submitting. 
				Rows with <span style="background-color:#F5D6D6;">pink backgrounds</span> denote people that are not yet in the 
				database and will be added upon submission if the name does not change. Rows with <span style="background-color:#FFFFAE;">yellow backgrounds</span> 
				indicate a person that already has been credited for this game.<br/><br/><br/>
				<form action="people.php" method="post">
				<input type="hidden" name="action" value="add by game" />
				<input type="hidden" name="gid" value="'.$gid.'" />');
			
			$work = rtrim($work);
			foreach(explode("\n", $work) as $w) {
				
				$i++;
				
				list($role, $name) = explode("|", $w);
				$role = trim($role);
				$name = trim($name);
				$role = str_replace('"', '', $role);
				$role = str_replace('\\', '', $role);
				$name = eregi_replace("[^a-zA-Z ]", "", $name);
				
				$class = '';
				
				//check if person exists in db
				$query = "SELECT * FROM `people` WHERE `name` = '$name'";
				$res = mysqli_query($GLOBALS['db']['link'], $query);
				if(!mysqli_num_rows($res)) {
					$class = "pink";
					unset($dat);
				} else {
					$dat = mysqli_fetch_object($res);
					//echo '<input type="hidden" name="pid['.$i.']" value="$dat->id" />';
				}
				
				//check if person has work for this game already
				$query = "SELECT * FROM `people_work` WHERE `pid` = '$dat->id' and `gid` = '$gid'";
				$res = mysqli_query($GLOBALS['db']['link'], $query);
				if(mysqli_num_rows($res)) {
					$class = "yellow";
					echo '<input type="hidden" name="multiple_entry['.$i.']" value="1" />';
				}
				
				echo '<dl class="'.$class.'">';
				echo '<dt><input type="checkbox" name="include['.$i.']" value="1" id="include'.$i.'" checked /> <label for="include'.$i.'">Include this entry</label></dt><dd>Uncheck this box to disclude this entry with your submission</label></dt>';
				echo '<dt><label for="name'.$i.'">Name:</label></dt><dd><input type="text" name="xname['.$i.']" value="'.$name.'" id="name'.$i.'" size="55" /></dd>';
				echo '<dt><label for="role'.$i.'">Role:</label></dt><dd><input type="text" name="role['.$i.']" value="'.$role.'" id="role'.$i.'" size="55" /></dd>';
				echo '<dt><label for="vital'.$i.'">Vital:</label></dt><dd><input type="checkbox" name="vital['.$i.']" value="1" id="vital'.$i.'"> This person`s work was vital to this game`s development</dd>';
				echo '<dt><label for="notes'.$i.'">Notes:</label></dt><dd><textarea name="notes['.$i.']" id="'.$i.'" cols="55" rows="5"></textarea></dd>';
				echo '</dl>';
			}
			echo '<input type="hidden" name="count" value="'.$i.'" />';
			$i = 0;
			echo '<div style="border-top: 1px solid #808080; padding-top:2em;"><input type="submit" name="submitworkfinal" value="Submit" /></div></form>';
		}
		$page -> SetContent($cont);
		$page -> Head();
		$page -> Foot();
		exit;
	}
	
	if($submitworkfinal) {
		$xname = $_POST['xname'];
		for($i=1; $i <= $count; $i++) {
			$role[$i] = str_replace('"', '', $role[$i]);
			$xname[$i] = eregi_replace("[^a-zA-Z ]", "", $xname[$i]);
			$notes[$i] = addslashes($notes[$i]);
			if(!$xname[$i]) {
				$warnings[] = "Row $i had no name input so it won`t be submitted";
			} elseif(!$include[$i]) {
				$warnings[] = "Row $i ($xname[$i]) was not included because the box for inclusion was unchecked.";
			} else {
				//check if person exists in db
				$query = "SELECT * FROM `people` WHERE `name` = '$xname[$i]'";
				$res = mysqli_query($GLOBALS['db']['link'], $query);
				if(!mysqli_num_rows($res)) {
					if(addPersonToDatabase($xname[$i])) {
						$results[] = "Added <a href=\"/people/".str_replace(" ", "-", $xname[$i])."\">$xname[$i]</a> to the database";
					} else {
						$errors[] = "Could not add $xname[$i] to the database";
						$skip_add_work[$i] = 1;
					}
				}
				if(!$skip_add_work[$i]) {
					$query = "SELECT * FROM `people` WHERE `name` = '$xname[$i]'";
					$res = mysqli_query($GLOBALS['db']['link'], $query);
					$dat = mysqli_fetch_object($res);
					$query = "INSERT INTO `people_work` (`id`, `pid`, `gid`, `role`, `notes`, `vital`) VALUES (NULL, '$dat->id', '$gid', '$role[$i]', '$notes[$i]', '$vital[$i]')";
					if(mysqli_query($GLOBALS['db']['link'], $query)) {
						$results[] = "<a href=\"/people/".str_replace(" ", "-", $xname[$i])."\">$xname[$i]</a> work added";
						if($multiple_entry[$i]) $warnings[] = "$xname[$i] already had an entry for this game and your submission did not overwrite it. Please double check and make sure you didn't credit $xname[$i] for the same role.";
					} else
						$errors[] = "Could not add $xname[$i]";
				}
			}
		}
	}
	
	//gamedata
	$query = "SELECT * FROM `games` WHERE `indexid` = '$gid'";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	$gdat = mysqli_fetch_object($res);
	
	echo <<<EOF
	
	$people_page_header
	
	<br/><large>Adding credits to <a href="/games/link.php?id=$gid">$gdat->title</a></large><br/><br/>
	
	<fieldset style="background-color:#F5F5F5;">
	<legend>Proper Input Format</legend>
	&bull; Separate each role & name with a | character, and each entry with a new line.<br/>
	&bull; For example:<br/>
<textarea rows="3" cols="45" style="margin-left:1.5em;">Scenario Designer|Yuji Horii
Character and Monster Artist|Akira Toriyama
Music Composer|Koichi Sugiyama</textarea><br/>
&bull; If you include a person that is not yet in the database, the script will add them automatically (so please make sure spelling 
is correct).<br/>
&bull; If a person you include here is already credited, it will add another work entry for that person, so please make sure you double 
check your submission by navigating to this game`s credits page after submission.<br/>
&bull; Names cannot contain any special characters! Anything other than letters and spaces in a name will be 
deleted (this does not apply to roles).
</fieldset>
	
	<br/>
	
	<form action="people.php" method="post">
	<input type="hidden" name="action" value="add by game" />
	<input type="hidden" name="gid" value="$gid" />
	<textarea name="work" rows="12" cols="55"></textarea><br/>
	<input type="submit" name="submitwork" value="Submit" />
	</form>
	
EOF;
$page -> SetContent($cont);
$page -> Head();
$page -> Foot();
exit;
	
}

///////////
// INDEX //
///////////

//select names
$query = "SELECT * FROM `people` WHERE `prolific` = '1' and `not_creator` = '0' ORDER BY `name`";
if($res = mysqli_query($GLOBALS['db']['link'], $query)) {
	$names = '<optgroup label="Prolific Creators">';
	while($row = mysqli_fetch_assoc($res)) {
		$names .= '<option value="'.htmlentities($row[name]).'">' . $row[name] . '</option>' . "\n";
	}
}
$query = "SELECT * FROM `people` WHERE `prolific` = '0' and `not_creator` = '0' ORDER BY `name`";
if($res = mysqli_query($GLOBALS['db']['link'], $query)) {
	$names .= '</optgroup><optgroup label="Other Creators">';
	while($row = mysqli_fetch_assoc($res)) {
		$names .= '<option value="'.htmlentities($row[name]).'">' . $row[name] . '</option>' . "\n";
	}
	$names .= "</optgroup>";
}
$query = "SELECT * FROM `people` WHERE `not_creator` = '1' ORDER BY `name`";
if($res = mysqli_query($GLOBALS['db']['link'], $query)) {
	$names .= '</optgroup><optgroup label="Non-creators">';
	while($row = mysqli_fetch_assoc($res)) {
		$names .= '<option value="'.htmlentities($row[name]).'">' . $row[name] . '</option>' . "\n";
	}
	$names .= "</optgroup>";
}

$page->header();

?>
<h2>People Database</h2>

	<big class="warn"><b>This manager has been depreciated.</b> You can perform almost all the functions here via the embedded page edits on any given person page. 
		If you continue with this form, your additions may be corrupted or may not be credited to your name.</big>
	<br/><br/>

<fieldset>
	<legend>Manage A Person</legend>
	<small>Manage individual details, pictures, work & credits, etc.</small>
	<form action="people.php" method="post">
		<input type="hidden" name="action" value="find person"/>
		<p><input type="text" name="name" id="person" /> <input type="submit" name="in[submit]" value="Search"/> <small>Use % as wildcard (i.e.: Shigeru M% or %Miyamo%)</small></p>
	</form>
	<form action="people.php" name="people" method="post">
		<input type="hidden" name="action" value="edit person"/>
		<p>
			<select name="name" onchange="javascript:document.people.submit()">
				<option value="" selected="selected">Select a person...</option>
				<?=$names?>
			</select>
		</p>
	</form>
</fieldset>
<br/>

<form action="people.php" method="post">
	<fieldset>
		<legend>Add A Person</legend>
		<small>Please input only letters and spaces. No special characters or numbers allowed. You will have the option of inputting an alias or alternate name later.</small>
		<p>Name: <input type="text" name="name" size="40"/> <input type="submit" name="addperson" value="Add Person"/></p>
	</fieldset>
</form>
<?

$page->footer();

function addPersonToDatabase($name) {
	global $db, $peopledir;
	
	list($name, $dirname) = FormatName($name);
	
	//check if person already exists
	if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM `people` WHERE `name` = '$name' OR name_url = '$dirname' LIMIT 1")))
		die ("$name already exists in database");
	
	$query = "INSERT INTO `people` (`name`, name_url, created, modified) VALUES 
		('$name', '$dirname', '".date("Y-m-d")."', '".date("Y-m-d")."')";
	if(mysqli_query($GLOBALS['db']['link'], $query)) {
		$results[] = "$name has been added to the database";
		adminAction($name, "Added person to people db");
		return TRUE;
	} else {
		return FALSE;
	}
}

function reformatName($name) {
	return $name;
}

function contributeToPeople($pid, $contr) {
	global $db;
	$query = "SELECT `contributors` FROM `people` WHERE pid='$pid' LIMIT 1";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	$row = mysqli_fetch_object($res);
	if(!$row->contributors) {
		// none yet
		$query2 = "UPDATE `people` SET `contributors` = '$contr' WHERE pid='$pid'";
		$res = mysqli_query($GLOBALS['db']['link'], $query2);
		return $res;
	} else {
		$cons = array();
		$cons = explode(",", $row->contributors);
		if(in_array($contr, $cons)) {
			return TRUE;
		} else {
			$cons[] = $contr;
			$query2 = "UPDATE `people` SET `contributors` = '".implode(",", $cons)."' WHERE pid='$pid'";
			$res = mysqli_query($GLOBALS['db']['link'], $query2);
			return $res;
		}
	}
}

?>