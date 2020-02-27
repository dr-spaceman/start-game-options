<?
require ($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");
$page = new page;

require ($_SERVER['DOCUMENT_ROOT']."/bin/php/class.groups.php");
$groups = new groups;

require ($_SERVER['DOCUMENT_ROOT']."/bin/php/class.badges.php");
$_badges = new badges;

require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/bbcode.php");
require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/htmltoolbox.php");
require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/class.upload.php");

$path = $_GET['path'];
if($path != "") {
	
	$patharr = array();
	$patharr = explode("/", $path);
	
	if($patharr[0] == "yours") {
		
		// Your Groups //
		
		$page->title = "Videogam.in / Your Groups";
		$page->header();
		$page->width = "fixed";
		
		$groups->header();
		
		$query = "SELECT gm.*, g.* FROM groups_members gm, groups g WHERE gm.usrid='$usrid' AND g.group_id=gm.group_id ORDER BY name";
		$res = mysql_query($query);
		if(!mysql_num_rows($res)) {
			echo 'You don\'t belong to any groups. Perhaps you should go out an socialize more.';
		} else {
			?>
			<ol id="groupslist">
				<?
				$i = 0;
				while($row = mysql_fetch_assoc($res)) {
					$img = "no";
					if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/img/groups/".$row['group_id']."_icon.png")) $img = $row['group_id'];
					if(strlen($row['name']) > 36) $p_name = substr($row['name'], 0, 35)."&hellip;";
					else $p_name = $row['name'];
					$half = substr($p_name, 0, 13);
					if(!strstr($half, " ")) $p_name = $half."-".substr($p_name, 13, 36);
					?>
					<li<?=($i % 4 == "0" ? ' style="clear:left"' : '')?>>
						<a href="/groups/<?=$row['group_id']?>/<?=formatNameURL($row['name'])?>" title="<?=htmlSC($row['name'])?>">
							<div class="img"><img src="/bin/img/groups/<?=$img?>_icon.png" alt="<?=htmlSC($row['name'])?>" border="0"/></div>
							<div class="name"><?=$p_name?></div>
						</a>
						<span style="font-size:11px">joined <?=timeSince($row['joined'])?> ago</span>
					</li>
					<?
					$i++;
				}
				?>
			</ol>
			<?
		}
		
		$page->footer();
		
		exit;
		
	} elseif($patharr[0] == "create") {
		
		// CREATE //
		
		$in = $_POST['in'];
		if($_POST['submit_new'] && $in) {
			
			$in['name'] = formatName($in['name']);
			if(!$in['name']) $errors[] = "Please input a group name (the previous name may have been parsed).";
			$in['about'] = parseText($in['about']);
			if(!$in['about']) $errors[] = "Please give a little information about the group in the About field.";
			
			//check name
			$q = "SELECT * FROM groups WHERE name='".mysql_real_escape_string($in['name'])."' LIMIT 1";
			if($dat = mysql_fetch_object(mysql_query($q))) {
				$errors[] = 'The group name "'.$in['name'].'" has already been taken. <a href="/groups/'.$dat->group_id.'/'.formatNameURL($dat->name).'">check out this group</a>.';
			}
			
			//uploads
			
			if($_FILES['file1']['name']) {
				$exts = array("jpg","JPG","jpeg","JPEG","gif","GIF","png","PNG");
				$ext = substr($_FILES['file1']['name'], -3, 3);
				if(in_array($ext, $exts)) {
					$handle = new Upload($_FILES['file1']);
					if($handle->uploaded){
						$handle->image_resize          = true;
						$handle->image_ratio_crop      = true;
						$handle->image_x               = 965;
						$handle->image_y               = 150;
						$handle->file_overwrite        = true;
						$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/img/groups/");
						$in['img'] = $handle->file_dst_name;
					}
				}
			}
			if($_FILES['file2']['name']) {
				$exts = array("jpg","JPG","jpeg","JPEG","gif","GIF","png","PNG");
				$ext = substr($_FILES['file2']['name'], -3, 3);
				if(in_array($ext, $exts)) {
					$handle = new Upload($_FILES['file2']);
					if ($handle->uploaded) {
						$handle->image_resize          = true;
						$handle->image_ratio_crop      = true;
						$handle->image_y               = 50;
						$handle->image_x               = 50;
						$handle->file_new_name_body    = $group_id."_icon";
						$handle->file_new_name_ext     = 'png';
						$handle->image_convert         = 'png';
						$handle->file_overwrite        = true;
						$handle->file_auto_rename      = false;
						$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/img/groups/");
					}
				}
			}
			
			if(!$errors) {
				$dt = date("Y-m-d H:i:s");
				$group_id = mysqlNextAutoIncrement("groups");
				$q = sprintf("INSERT INTO groups (`name`,`about`,`status`,`creator`,`created`,`img`) VALUES 
					('%s', '%s', '".$in['status']."', '$usrid', '$dt', '%s');",
					mysql_real_escape_string($in['name']),
					mysql_real_escape_string($in['about']),
					mysql_real_escape_string($in['img']));
				if(!mysql_query($q)) $errors[] = "Couldn't add group to database; ".mysql_error();
				else {
					//inset creator as member
					$q = "INSERT INTO groups_members (`group_id`,`usrid`,`status`,`joined`,`subscribe`) VALUES 
						('$group_id', '$usrid', '3', '$dt', '1');";
					if(!mysql_query($q)) {
						$errors[] = "Couldn't add you to group members database; ".mysql_error();
						$q2 = "DELETE FROM groups WHERE group_id='$group_id' LIMIT 1";
						mysql_query($q2);
					} else {
						
						//subscribe to forum
						$q = "INSERT INTO forums_mail (usrid, `location`) VALUES ('$usrid', 'group:".$group_id."');";
						if(!mysql_query($q)) $errors[] = "Couldn't subscribe to forum topics";
						
						//tags
						if($in['tags']) {
							$q = "";
							$tags = array();
							$tags = explode("\r\n", $in['tags']);
							foreach($tags as $tag) {
								$tag = trim($tag);
								$tag = formatName($tag);
								if($tag) $q.= "('$group_id', '".mysql_real_escape_string($tag)."'),";
							}
							if($q) {
								$q = "INSERT INTO groups_tags (group_id, tag) VALUES ".substr($q, 0, -1);
								if(!mysql_query($q)) $errors[] = "Couldn't add tags to the database; ".mysql_error();
							}
						}
						
						$_badges->earn(41);
						
						header("Location:/groups/".$group_id."/".formatNameURL($in['name']));
						exit;
					}
				}
			}
			
		}
		
		$page->width = "fixed";
		
		$page->title = "Videogam.in / Create a Group";
		$page->header();
		
		$groups->header();
		
		if(!$usrid) $page->die_('Please <a href="/login.php">log in</a> to create a group.');
		
		?>
		<form action="/groups.php?path=create" method="post" enctype="multipart/form-data">
			<table border="0" cellpadding="0" cellspacing="0" class="styled-form styled-form-alt">
				<tr>
					<th colspan="2">
						<h5 style="margin:0; padding:0; font-weight:bold; font-size:20px; color:#888;">Create a New Group</h5>
					</th>
				</tr>
				<tr>
					<th>Group Name</th>
					<td>
						<div class="inpfw">
							<input type="text" name="in[name]" value="<?=htmlSC($in['name'])?>" maxlength="100" style="font-size:17px;"/>
						</div>
					</td>
				</tr>
				<tr>
					<th>About</th>
					<td>
						<div class="inpfw">
							<textarea name="in[about]" rows="6"><?=$in['about']?></textarea>
						</div>
					</td>
				</tr>
				<tr>
					<th>Privacy</th>
					<td>
						<label><input type="radio" name="in[status]" value="open" checked="checked"/> <b>Public & Open</b>, anyone can join</label>
						<p><label><input type="radio" name="in[status]" value="request" disabled="disabled"/> <strike><b>Public & Closed</b>, anyone can request to join, but they must be approved by management</strike> Coming soon</label></p>
						<p><label><input type="radio" name="in[status]" value="invite" disabled="disabled"/> <strike><b>Private</b>, join by invitation only</strike> Coming soon</label></p>
					</td>
				</tr>
				<tr>
					<th>Upload images<br/><small>recommended</small></th>
					<td>
						<ul style="margin:0; padding:0 0 0 21px; list-style:square;">
							<li><b style="color:#444;">JPG GIF or PNG images only</b></li>
							<li>
								<p><b>Header Image</b> <span style="color:#666;">resized to 965 &times; 150 pixels</span></p>
								<p><input type="file" name="file1"/></p>
							</li>
							<li>
								<p><b>Thumbnail Image</b> <span style="color:#666;">resized to 50 &times; 50 pixels</span></p>
								<p><input type="file" name="file2"/></p>
							</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th>Tags<br/><small>optional</small></th>
					<td>
						Give this group more exposure by tagging games, people, consoles, companies, themes, etc. Please input one tag per line.
						<p></p>
						<div class="inpfw">
							<textarea name="in[tags]" rows="3" cols="90" id="input-tags"><?=$in['tags']?></textarea>
						</div>
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><input type="submit" name="submit_new" value="Create Group"/></td>
				</tr>
			</table>
		</form>
		<?
		
		$page->footer();
		
		exit;
		
	} else {
		
		// GROUP PAGES //
		
		$group_id = $patharr[0];
		$q = "SELECT * FROM groups WHERE group_id='".mysql_real_escape_string($group_id)."' LIMIT 1";
		$res = mysql_query($q);
		if(!$gdat = mysql_fetch_object($res)) require("404.php");
		
		//form submissions
		if($_POST['submit_memberlist']) {
			$q = "UPDATE groups_members SET status='0' WHERE group_id='".mysql_real_escape_string($group_id)."';";
			if(!mysql_query($q)) $errors[] = "Couldn't update managers";
			else {
				foreach($_POST['managers'] as $uid) {
					$q = "UPDATE groups_members SET `status`='2' WHERE group_id='$gdat->group_id' AND usrid='$uid' LIMIT 1";
					if(!mysql_query($q)) $errors[] = "Couldn't make usrid #$uid a manager";
				}
			}
			if($_POST['remove']) {
				foreach($_POST['remove'] as $uid) {
					$q = "DELETE FROM groups_members WHERE group_id='$gdat->group_id' AND usrid='$uid' LIMIT 1";
					if(!mysql_query($q)) $errors[] = "Couldn't remove usrid #$uid";
					if(in_array($uid, $_POST['ban'])) {
						$q = "INSERT INTO groups_banned (group_id, usrid, datetime, banned_by) VALUES 
							('$gdat->group_id', '$uid', '".date("Y-m-d H:i:s")."', '$usrid');";
						if(!mysql_query($q)) $errors[] = "Couldn't ban usrid #$uid";
					}
				}
			}
			if($_POST['unban']) {
				foreach($_POST['unban'] as $uid) {
					$q = "DELETE FROM groups_banned WHERE group_id='$gdat->group_id' AND usrid='$uid' LIMIT 1";
					if(!mysql_query($q)) $errors[] = "Couldn't unban usrid #$uid";
				}
			}
		}
		
		if($_POST['update_tags']) {
			$q = "DELETE FROM groups_tags WHERE group_id='$gdat->group_id'";
			mysql_query($q);
			$q = "";
			$tags = array();
			$tags = explode("\r\n", $_POST['update_tags']);
			foreach($tags as $tag) {
				$tag = formatName($tag);
				$tag = mysql_real_escape_string($tag);
				if($tag) $q.= "('$gdat->group_id', '".mysql_real_escape_string($tag)."'),";
			}
			if($q) {
				$q = "INSERT INTO groups_tags (group_id, tag) VALUES ".substr($q, 0, -1);
				if(!mysql_query($q)) $errors[] = "Couldn't add tags to the database; ".mysql_error();
			}
			if(!$errors) $results[] = "Tags updated";
		}
		
		if($_POST['upload_imgs']) {
			
			$exts = array("jpg","JPG","jpeg","JPEG","gif","GIF","png","PNG");
			
			if($_FILES['file1']['name']) {
				$ext = substr($_FILES['file1']['name'], -3, 3);
				if(in_array($ext, $exts)) {
					$handle = new Upload($_FILES['file1']);
					if ($handle->uploaded) {
						$handle->image_resize          = true;
						$handle->image_ratio_crop      = true;
						$handle->image_x               = 965;
						$handle->image_y               = 150;
						$handle->file_overwrite        = true;
						$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/img/groups/");
						if($handle->processed) $in['img'] = $handle->file_dst_name;
					}
				}
			}
			if($_FILES['file2']['name']) {
				$ext = substr($_FILES['file2']['name'], -3, 3);
				if(in_array($ext, $exts)) {
					$handle = new Upload($_FILES['file2']);
					if ($handle->uploaded) {
						$handle->image_resize          = true;
						$handle->image_ratio_crop      = true;
						$handle->image_y               = 50;
						$handle->image_x               = 50;
						$handle->file_new_name_body    = $gdat->group_id."_icon";
						$handle->file_new_name_ext     = 'png';
						$handle->image_convert         = 'png';
						$handle->file_overwrite        = true;
						$handle->file_auto_rename      = false;
						$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/img/groups/");
					}
				}
			}
			$results[] = "Images successfully processed";
			$warnings[] = "You might need to reload your browser's cache before the new upload will be reflected.";
			
			if($in['img']) mysql_query("UPDATE groups SET img = '".mysql_real_escape_string($in['img'])."' WHERE group_id = '$gdat->group_id' LIMIT 1");
			
		}
		
		if($_POST['update_genl_details']) {
			
			$in = $_POST['in'];
			$in['name'] = formatName($in['name']);
			$in['about'] = parseText($in['about']);
			$q = "UPDATE groups SET 
				`name` = '".mysql_real_escape_string($in['name'])."',
				`about` = '".mysql_real_escape_string($in['about'])."',
				`status` = '".$in['status']."'
				WHERE group_id='$gdat->group_id' LIMIT 1";
			if(!mysql_query($q)) $errors[] = "Couldn't update database; ".mysql_error();
			else {
				$results[] = "Details updated";
				$gdat->name = $in['name'];
				$gdat->about = $in['about'];
				$gdat->status = $in['status'];
			}
		}
		
		//invite
		if($inp = $_POST['inv_addresses']) {
			
			$inp = preg_replace("/[\r\n\t, ]+/", " ", $inp);
			$pms = array();
			$adds = array();
			$adds = explode(" ", $inp);
			for($i = 0; $i < count($adds); $i++) {
				$adds[$i] = trim($adds[$i]);
				if($adds[$i] == "") unset($adds[$i]);
				else {
					$add = $adds[$i];
					//validate
					if(!preg_match("/^[^@]+@.+\.[a-z]{2,6}$/i", $add)) {
						unset($adds[$i]);
						//it's not an email address, so check the user bank for a username and corresponding email address
						$q = "SELECT * FROM users LEFT JOIN users_prefs USING (usrid) WHERE username = '".mysql_real_escape_string($add)."' LIMIT 1";
						if($usrdat = mysql_fetch_object(mysql_query($q))){
							if(!in_array($usrdat->email, $adds)){
								if(!$usrdat->mail_from_users) $pms[] = $usrdat->usrid;
								else $adds[] = $usrdat->email;
							}
						}
					}
				}
			}
			
			$udat = getUserDat($usrid);
			$mail_message = "Hello, you have been invited by your friend, ".$udat->username.($udat->name && $udat->username != $udat->name ? " (".$udat->name.")" : "")." to join the Videogam.in gaming group, ".$gdat->name."!\n\nAbout this group:\n".strip_tags($gdat->about)."\n\n";
					if($_POST['inv_message']) $mail_message.= $udat->username." writes:\n".$_POST['inv_message']."\n\n";
					$mail_message.= "Check out the group hub here ----> http://videogam.in/groups/".$gdat->group_id."/".formatNameURL($gdat->name);
			
			if(count($adds)) {
				
				$mres = array();
				foreach($adds as $add) {
					$headers = "From: ".$udat->username." <".$udat->email.">\r\n" .
						'X-Mailer: PHP/' . phpversion();
					if(!mail($add, "Invitation to join $gdat->name", $mail_message, $headers)) {
						sendBug('Couldnt e-mail invitation to join group; to:'.$add.'; '.$headers);
						$errors[] = "There was an error and an e-mail invitation couldn't be sent to $add";
					} else $mres[] = $add;
				}
				
			}
			
			if(count($pms)){
				$q = "INSERT INTO pm (`to`, `from`, `date`, `subject`, `message`) VALUES ";
				foreach($pms as $uid){
					$q.= "('$uid', '$usrid', '".date("Y-m-d H:i:s")."', 'Invitation to join ".mysql_real_escape_string($gdat->name)."', '".mysql_real_escape_string($mail_message)."'),";
				}
				$q = substr($q, 0, -1);
				mysql_query($q);
			}
				
			if(!$errors) $results[] = "Invitations sent!";
					
			
		}
		
		//get members
		$query = "SELECT * FROM groups_members WHERE group_id='".$gdat->group_id."' ORDER BY joined";
		$res   = mysql_query($query);
		$managers = array();
		$members = array();
		while($row = mysql_fetch_assoc($res)) {
			$members[] = $row['usrid'];
			if($row['status'] > 1) $managers[] = $row['usrid'];
			if($row['usrid'] == $usrid) $my_details = $row;
		}
		
		//leave?
		if($_GET['leave']) {
			if( in_array($usrid, $managers) && count($managers) == 1 ) {
				$errors[] = "You can't leave since you're the only manager. Please appoint a new manager before leaving.";
			} else {
				$q = "DELETE FROM groups_members WHERE group_id='".$gdat->group_id."' AND usrid='$usrid' LIMIT 1";
				if(!mysql_query($q)) $errors[] = "Couldn't remove you from member database";
				else {
					header("Location:/groups/".$gdat->group_id."/".formatName($gdat->name));
					exit;
				}
			}
		}
		
		//join ?
		if($_GET['join'] && $usrid) {
			if(!in_array($usrid, $members)) {
				$dt = date("Y-m-d H:i:s");
				$q = "INSERT INTO groups_members (`group_id`,`usrid`,`status`,`joined`,`subscribe`) VALUES 
					('".$gdat->group_id."', '$usrid', '1', '$dt', '1');";
				if(!mysql_query($q)) {
					$errors[] = "There was a database error and you couldn't be added to the member list.";
				} else {
					
					//subscribe to forum
					$q = "INSERT INTO forums_mail (usrid, `location`) VALUES ('$usrid', 'group:".$gdat->group_id."');";
					if(!mysql_query($q)) $errors[] = "Couldn't subscribe to forum topics";
						
					$members[] = $usrid;
					$my_details = array("joined" => $dt, "subscribe" => "1", "status" => "1");
					
					$_badges->earn(40);
					
				}
			}
		}
		
		require_once ($_SERVER['DOCUMENT_ROOT']."/bin/php/class.forums.php");
		
		$page->title = "Videogam.in Groups / ".htmlSC($gdat->name).($patharr[1] ? " / ".ucwords($patharr[1]) : '');
		$page->width = "fixed";
		$page->header();
		
		$groups->header();
		
		if($gdat->status == "invite" && !in_array($usrid, $members)) $page->die_("Access to this group is for members only.");
				
		if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/img/groups/".$gdat->img)) $img = $gdat->img;
		
		?>
		<div id="grouppage">
			<h3 style="<?=($img ? 'background-image:url(/bin/img/groups/'.$img.');' : '')?>">
				<a href="/groups/<?=$gdat->group_id?>/<?=formatNameURL($gdat->name)?>">
					<span><?=$gdat->name?></span>
				</a>
			</h3>
			<?
				
			if($patharr[2] == "manage") {
				
				// MANAGER //
				
				if(!in_array($usrid, $managers) && $usrrank < 9) $page->die_("You can't manage this group since you aren't a manager.");
				
				?>
				<h4>Group Manager <span style="color:#888;">&middot;</span> <a href="/groups/<?=$gdat->group_id?>/<?=formatNameURL($gdat->name)?>" class="arrow-right">Back to group hub</a></h4>
				
				<dl id="groupmng">
					<dt><a href="#general">General Details</a></dt>
					<dd>
						<form action="." method="post">
							<table border="0" cellpadding="0" cellspacing="0" class="styled-form">
								<tr>
									<th>Group Name</th>
									<td><input type="text" name="in[name]" value="<?=htmlSC($gdat->name)?>" size="92" maxlength="100"/></td>
								</tr>
								<tr>
									<th>About This Group</th>
									<td><textarea name="in[about]" rows="6" cols="90"><?=$gdat->about?></textarea></td>
								</tr>
								<tr>
									<th>Status</th>
									<td>
										<label><input type="radio" name="in[status]" value="open" <?=($gdat->status == "open" ? 'checked="checked"' : '')?>/> <b>Public & Open</b>, anyone can join</label>
										<p><label><input type="radio" name="in[status]" value="request" <?=($gdat->status == "request" ? 'checked="checked"' : '')?>/> <b>Public & Closed</b>, anyone can request to join, but they must be approved my management</label></p>
										<p><label><input type="radio" name="in[status]" value="invite" <?=($gdat->status == "invite" ? 'checked="checked"' : '')?>/> <b>Private</b>, join by invitation only</label></p>
									</td>
								</tr>
								<tr>
									<th>&nbsp;</th>
									<td><input type="submit" name="update_genl_details" value="Update Details"/></td>
								</tr>
							</table>
						</form>
					</dd>
					
					<dt><a href="#images">Images</a></dt>
					<dd>
						<form action="." method="post" enctype="multipart/form-data">
							<ul style="margin:0; padding:0 0 0 21px; list-style:square;">
								<li>Upload new images (all current image files will be overwritten)</li>
								<li><p>Use JPG GIF or PNG images only</p></li>
								<li>
									<p><b>Header Image</b> <span style="color:#666;">resized to 900&times;150 pixels</span></p>
									<p><input type="file" name="file1"/></p>
								</li>
								<li>
									<p><b>Icon Image</b> <span style="color:#666;">resized to 50&times;50 pixels</span></p>
									<p><input type="file" name="file2"/></p>
								</li>
							</ul>
							<p><input type="submit" name="upload_imgs" value="Upload & Process"/></p>
						</form>
					</dd>
					
					<dt><a href="#tags">Tags</a></dt>
					<dd>
						<form action="." method="post">
							Give this group more exposure by tagging stuff. Please input one tag per line.<p></p>
							<textarea name="update_tags" rows="3" cols="90" id="input-tags"><?
								$query = "SELECT tag FROM groups_tags WHERE group_id='$gdat->group_id'";
								$res   = mysql_query($query);
								while($row = mysql_fetch_assoc($res)) {
									echo $row['tag']."\n";
								}
							?></textarea></p>
							<p><input type="submit" name="edit_tags" value="Submit Tags"/></p>
						</form>
						<br style="clear:both"/>
					</dd>
					
					<dt><a href="#members">Members</a></dt>
					<dd>
						<form action="." method="post" onsubmit="return checkMembersForm();">
							<table border="0" cellpadding="0" cellspacing="0" width="100%" class="mgmemberlist">
								<tr>
									<th>Username</th>
									<th>Join Date</th>
									<th><div title="Elected to be notified via e-mail whenever there's a new Forum thread or Sblog post" class="tooltip">Subscription<sup style="color:#06C;">?</sup></div></th>
									<th width="100"><div align="center">Management</div></th>
									<th width="100"><div align="center">Remove</div></th>
								</tr>
								<?
								//get stuff
								$query = "SELECT * FROM groups_members WHERE group_id='".$gdat->group_id."'";
								$res   = mysql_query($query);
								while($row = mysql_fetch_assoc($res)) {
									$member[$row['usrid']] = $row;
								}
								
								foreach($members as $uid) {
									?>
									<tr>
										<td><?=outputUser($uid, FALSE)?></td>
										<td><?=formatDate($member[$uid]['joined'])?></td>
										<td><span<?=(!$member[$uid]['subscribe'] ? ' style="color:#CCC;"' : '')?>>Subscribed</span></td>
										<td><div class="checkbox"><input type="checkbox" name="managers[]" value="<?=$uid?>"<?=(in_array($uid, $managers) ? ' checked="checked"' : '')?>/></div></td>
										<td><?=(in_array($uid, $managers) ? '&nbsp;' : '<div class="checkbox"><input type="checkbox" name="remove[]" value="'.$uid.'" onclick="$(this).next().toggle();"/><label style="display:none"> ~ <input type="checkbox" name="ban[]" value="'.$uid.'"/><acronym title="ban from re-joining this group">ban</acronym></label></div>')?></td>
									</tr>
									<?
								}
								?>
							</table>
							
							<?
							//ban list
							$query = "SELECT * FROM groups_banned WHERE group_id='$gdat->group_id'";
							$res   = mysql_query($query);
							if(mysql_num_rows($res)) {
								?>
								<fieldset style="margin:5px 0;">
									<legend>Ban List</legend>
									Check a user to un-ban.
										<?
										while($row = mysql_fetch_assoc($res)) {
											echo '<div style="margin-top:4px"><input type="checkbox" name="unban[]" value="'.$row['usrid'].'"/> '.outputUser($row['usrid'], FALSE).' banned on '.$row['datetime'].' by '.outputUser($row['banned_by'], FALSE).'</div>';
										}
										?>
								</fieldset>
								<?
							}
							?>
								
							<div style="margin-top:8px; text-align:right;">
								<input type="submit" name="submit_memberlist" value="Submit Changes" style="font-size:110%;"/>
							</div>
						</form>
					</dd>
					
				</dt>
				
				<?
				
			} else {
				
				// GROUP HUB //
				
				?>
				<div id="about"><?=bb2html($gdat->about)?></div>
				<div id="controls">
					<ul>
						<li id="created">
							<div>Created on <?=formatDate($gdat->created)?> by <?=outputUser($gdat->creator)?></div>
						</li>
						<?
						if($my_details) {
							?>
							<li id="your-join-info">
								<div>
									<?
									$diff = ( strtotime(date("Y-m-d H:i:s")) - strtotime($my_details['joined']) );
									if($diff < 20000) echo "You just joined!";
									else echo 'You have been a member for '.timeSince($my_details['joined']).'.';
									?>
								</div>
							</li>
							<li><div><a href="#invite" class="preventdefault arrow-toggle invite-link">Invite</a></div></li>
							<li id="options">
								<div>
									<a href="javascript:void(0)" class="arrow-toggle" onclick="$(this).toggleClass('arrow-toggle-on').next().toggle();">Options</a>
									<span id="options-inp">
										<p><label><img src="/bin/img/loading-arrows-small.gif" alt="loading..." style="display:none"/><input type="checkbox" name="subscribe" value="<?=$gdat->group_id?>"<?=($my_details['subscribe'] ? ' checked="checked"' : '')?>/> Subscribe to group news posts</label></p>
										<p style="padding:0 0 3px 3px;"><a href="?leave=true" class="arrow-right" onclick="if(confirm('Really leave this group?')) return true; else return false;">Leave Group</a></p>
									</span>
								</div>
							</li>
							<?
						} else {
							if(!$usrid) {
								echo '<li><div>Please <a href="/login.php">log in</a> to join this group</div></li>';
							} else {
								echo '<li><div><a href="?join=true">Join this Group</a></div></li>';
							}
						}
						?>
						<li>
							<?
							if(in_array($usrid, $managers) || $usrrank == 9) {
								?>
								<div id="manage-link"><a href="/groups/<?=$gdat->group_id?>/_/manage" style="padding-left:14px; background:url(/bin/img/icons/edit.gif) no-repeat 0 50%;">Manage Group</a></div>
								<?
							}
							?>
						</li>
					</ul>
					
					<?
					if($my_details) {
						?>
						<div id="invite">
							<form action="/groups/<?=$gdat->group_id?>/<?=formatNameURL($gdat->name_url)?>" method="post">
								Enter a few Videogam.in usernames and/or e-mail addresses of people to invite:<p style="margin:5px 0 0;"></p>
								<div class="inpfw"><textarea name="inv_addresses" cols="80" rows="4"><?=$_POST['inv_addresses']?></textarea></div>
								<p></p>
								Write an optional invitation message: <span style="color:#888">(the group's name, description, and URL will aleady be included)</span><p style="margin:5px 0 0;"></p>
								<div class="inpfw"><textarea name="inv_message" cols="80" rows="4"><?=$_POST['inv_message']?></textarea></div>
								<p></p>
								<input type="submit" value="Send Invitations"/>
							</form>
						</div>
						<?
					}
					?>
					
				</div>
				
				<div class="clear">&nbsp;</div>
				
				<div id="members">
					<ul class="tabbed-nav">
						<li class="on"><a href="javascript:void(0)" onclick="$(this).parent().addClass('on').siblings().removeClass('on'); $('#members ol li.show').show();"><span>All Members</span> (<?=count($members)?>)</a></li>
						<li><a href="javascript:void(0)" onclick="$(this).parent().addClass('on').siblings().removeClass('on'); $('#members ol li').hide(); $('#members ol li.manager').show();"><span>Managers</span> (<?=count($managers)?>)</a></li>
					</ul>
					<div id="memberlist">
						<ol>
							<?
							$i = 0;
							foreach($members as $uid) {
								$i++;
								echo '<li class="'.($i < 21 ? 'show' : 'more').(in_array($uid, $managers) ? ' manager' : '').'">'.outputUser($uid).'</li>';
							}
							if($i >= 21) echo '<li class="show"><a href="javascript:void(0)" class="arrow-right" onclick="$(this).parent().siblings(\'.more\').show();">More</a></li>';
							?>
						</ol>
						<div style="clear:both; height:3px;">&nbsp;</div>
					</div>
				</div>
	
				<?
				$forums = new forum;
				$forums->location = 'group:'.$gdat->group_id;
				?>
				<div id="group-forum">
					<fieldset>
						<legend>Message Forums</legend>
						<? $forums->showTopicList(); ?>
					</fieldset>
				</div>
				
				<div id="group-news">
					<fieldset>
						<legend>Blog Posts</legend>
						Coming soon!
					</fieldset>
				</div>
				<?
				
			}
			
			?>
		</div><!--#grouppage-->
		<?
		$page->footer();
		exit;
		
	}
	exit;
}

// Groups Index //

$page->title = "Videogam.in / Groups";
$page->header();

$groups->header();

if($find = $_GET['find']) {
	if(substr($find, 0, 4) == "gid:") {
		$query = "SELECT * FROM groups_tags LEFT JOIN groups USING (group_id) WHERE tag='$find'";
		$res   = mysql_query($query);
		if(mysql_num_rows($res)) {
			$findclause = " AND (";
			while($row = mysql_fetch_assoc($res)) {
				$findclause.= " g.group_id='".$row['group_id']."' OR ";
			}
			$findclause = substr($findclause, 0, -3).")";
		}
	} else {
		$findclause = " AND name LIKE '%".mysql_real_escape_string($find)."%'";
	}
}

if(!$orderby = $_GET['orderby']) $orderby = "name";
if($orderby != "name" && $orderby != "created" && $orderby != "members") $orderby = "name";
if($orderby == "name" || $orderby == "created") {
	$query = "SELECT g.*, COUNT(gm.group_id) AS members FROM groups_members gm, groups g WHERE g.group_id=gm.group_id AND g.`status` != 'invite'$findclause GROUP BY gm.group_id ORDER BY $orderby ".($orderby == "created" ? " DESC" : "ASC");
} else {
	$query = "SELECT g.*, COUNT(gm.group_id) AS members FROM groups_members gm, groups g WHERE g.group_id=gm.group_id AND g.`status` != 'invite'$findclause GROUP BY gm.group_id ORDER BY members DESC, name DESC";
}
$groupnum = mysql_num_rows(mysql_query($query));

$max = 28;
if(!$pg = $_GET['pg']) $pg = 1;
if($pg > 1) {
	$min = ($pg - 1) * $max;
	$query.= " LIMIT $min, $max";
} else $query.= " LIMIT 0, $max";

?>
<div style="margin:0 0 20px; font-size:110%;">
	<b><?=$groupnum?> Public Group<?=($groupnum != 1 ? 's' : '').($find ? ' found' : '')?></b> &middot; Sort by 
	<?=($orderby == "name" ? '<b>Name</b>' : '<a href="'.($find ? '?find='.$find : '.').'">Name</a>')?> &middot; 
	<?=($orderby == "created" ? '<b>Creation Date</b>' : '<a href="?orderby=created'.($find ? '&find='.$find : '').'">Creation Date</a>')?> &middot; 
	<?=($orderby == "members" ? '<b># of Members</b>' : '<a href="?orderby=members'.($find ? '&find='.$find : '').'"># of Members</a>')?>
</div>

<ol id="groupslist">
<?
$res = mysql_query($query);
$i = 0;
while($row = mysql_fetch_assoc($res)) {
	$img = "no";
	if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/img/groups/".$row['group_id']."_icon.png")) $img = $row['group_id'];
	if(strlen($row['name']) > 36) $p_name = substr($row['name'], 0, 35)."&hellip;";
	else $p_name = $row['name'];
	$half = substr($p_name, 0, 13);
	if(!strstr($half, " ") && strlen($p_name) > 13) $p_name = $half."-".substr($p_name, 13, 36);
	?>
	<li<?=($i % 4 == "0" ? ' style="clear:left"' : '')?>>
		<a href="/groups/<?=$row['group_id']?>/<?=formatNameURL($row['name'])?>" title="<?=htmlSC($row['name'])?>">
			<img src="/bin/img/groups/<?=$img?>_icon.png" alt="<?=htmlSC($row['name'])?>" border="0"/>
			<div>
				<big><?=$p_name?></big>
				<?=$row['members']?> member<?=($row['members'] > 1 ? 's' : '')?>
			</div>
		</a>
	</li>
	<?
	$i++;
}
?>
</ol>
<?

if($groupnum > $max) {
	?><div id="pagenav"><?
	$pgs = ceil($groupnum / $max);
	for($i = 1; $i <= $pgs; $i++) {
		if($i == $pg) echo ' <b>'.($i == 1 ? 'Page ' : '').$i.'</b>';
		else echo ' <a href="?orderby='.$orderby.'&pg='.$i.($find ? '&find='.$find : '').'">'.($i == 1 ? 'Page ' : '').$i.'</a>';
	}
	?></div><?
}

$page->footer();

?>