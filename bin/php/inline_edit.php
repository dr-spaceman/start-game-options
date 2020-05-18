<?
use Vgsite\Page;
require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/contribute.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");

die("This function has been depreciated. The new editing system will be online soon!");
exit;

if(!$usrid) die("Error: you need to be logged in first");

if($_POST) {
	
	if($ssubj = $_POST['watchlist']) {
		
		if(!$usrid) die("Log in first");
		
		$q = "SELECT * FROM watchlist WHERE supersubject = '".mysqli_real_escape_string($GLOBALS['db']['link'], $ssubj)."' AND usrid = '$usrid' LIMIT 1";
		if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))){
			$q = "DELETE FROM watchlist WHERE supersubject = '".mysqli_real_escape_string($GLOBALS['db']['link'], $ssubj)."' AND usrid = '$usrid'";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Couldn't delete $ssubj from watch list");
		} else {
			$q = "INSERT INTO watchlist (usrid, supersubject) VALUES ('$usrid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $ssubj)."');";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Couldn't add $ssubj to watch list");
		}
		
		exit;
		
	}
	
	if($cid = $_POST['forfeitpoints']) {
		
		if(!$usrid) die("Log in first");
		
		$q = "SELECT no_points FROM users_contributions WHERE contribution_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $cid)."' AND usrid = '$usrid' LIMIT 1";
		if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))){
			$q = "UPDATE users_contributions SET no_points = '".($dat->no_points ? '0' : '1')."' WHERE contribution_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $cid)."' LIMIT 1";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Couldn't update contribution data");
		} else die("Error: Couldn't get that data row");
		
		exit;
		
	}
	
	if($_POST['_action'] == "update_contr_notes") {
		
		if(!$cid = $_POST['_cid']) die("Error: No contribution id given");
		$notes = trim($_POST['_notes']);
		if($notes == "" || $notes == "Add notes about this particular update") exit;
		
		$q = "UPDATE users_contributions SET `notes` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $notes)."' WHERE contribution_id = '$cid' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Error updating notes; ".mysqli_error($GLOBALS['db']['link']));
		
		exit;
		
	}
	
	if($subj = $_POST['load_field_history']) {
		
		if($subj == "undefined") die('<div class="msg">Couldn\'t load field history :(</div>');
		
		$query = "SELECT usrid, datetime, data FROM users_contributions LEFT JOIN users_contributions_data USING (contribution_id) WHERE subject = '$subj' AND published = '1' ORDER BY datetime DESC;";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		$rows  = array();
		while($row = mysqli_fetch_assoc($res)) {
			$rows[] = $row;
		}
		if(!count($rows)) die('<div class="msg">No history found for this field</div>');
		
		?>
		<table border="0" cellpadding="3" cellspacing="0" width="100%">
			<?
			$ousrs = array();
			foreach($rows as $row) {
				if(!$ousrs[$row['usrid']]) $ousrs[$row['usrid']] = outputUser($row['usrid'], FALSE);
				$dt = substr($row['datetime'], 0, 10);
				$dt = str_replace("-", "/", $dt);
				$odata = $row['data'];
				if(strlen($odata) > 40) $odata = '<div style="cursor:pointer;" onclick="$(this).hide().next().show();">'.substr($odata, 0, 39).' <a href="javascript:void(0);">...</a></div><span style="display:none;">'.$odata.'</span>';
				?>
				<tr>
					<td nowrap="nowrap"><?=$dt?></td>
					<td nowrap="nowrap"><?=$ousrs[$row['usrid']]?></td>
					<td width="100%"><?=$odata?></td>
				</tr>
				<?
			}
			?>
		</table>
		<?
		exit;
		
	}
	
	if($_POST['_action'] == "output") {
		
		// manual handle some AJAX output when a field is changed //
		
		$inp = $_POST['_text'];
		switch($_POST['_what']) {
			case "gengamedata":
				$outp = array();
				$arr = array();
				$arr = preg_split("/\r|\n/", $inp);
				foreach($arr as $str) {
					$str = trim($str);
					if($str) $outp[] = '<a>'.$str.'</a>';
				}
				die(implode(", ", $outp));
				break;
			case "pub-platform":
				$query = "SELECT platform FROM games_platforms WHERE platform_id='$inp' LIMIT 1";
				$res   = mysqli_query($GLOBALS['db']['link'], $query);
				if($dat = mysqli_fetch_object($res)) die($dat->platform);
				break;
			case "person nationality":
				echo '<div style="padding-left:20px; background:url(/bin/img/flags/'.strtolower($ret).'.png) no-repeat 0 1px;">'.$ret.'</div>';
				break;
			case "game pub platform":
				$q = "SELECT platform FROM games_platforms WHERE platform_id='$ret' LIMIT 1";
				$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
				echo $dat->platform;
				break;
			case "bb2html":
				echo bb2html($inp);
				break;
			default:
				die($inp);
		}
		exit;
	}	
	
	if($_POST['submit_changes']) {
		
		// SUBMIT CHANGES //
		
		$page = new Page();
		$page->title = "Videogam.in / Submit edits";
		
		$contr = new contribution;
		$contr->setSessId();
		
		$fields = array();
		foreach($_POST['changes'] as $field) {
			//go through the [changes] array to see which fields to look at
			if(!in_array($field, $fields)) $fields[] = $field;
		}
		foreach($fields as $field) {
			
			$thisf = $_POST['contr'][$field];
			
			$contr->type      = $thisf['type'];
			$contr->desc      = $thisf['desc'];
			$contr->notify    = ($usrid == 1 ? FALSE : TRUE);
			$contr->subj      = $thisf['subj'];
			$contr->ssubj     = $thisf['ssubj'];
			$contr->data      = "";
			
			$mi = $thisf['manual_input']; // manually handle some input
			switch($mi) {
			
			case "person profile pic": //person pic
				
				if(!$_FILES['personpic']['name']) break;
				if(!$pid = substr($contr->ssubj, 4)) break;
				
				$contr->upload = $_FILES['personpic'];
				$contr->data = "{*uploaded_file:}true";
				
				if($_SESSION['user_rank'] >= 4) {
					$contr->status = "publish";
					$pic_fname = $pid;
					$pic_dir = "/bin/img/people/";
					//send current one to recycle bin
					$newf = "/bin/deleted-files/people--$pid.".date("Y-m-d").".png";
					@rename($_SERVER['DOCUMENT_ROOT']."/bin/img/people/$pid.png", $_SERVER['DOCUMENT_ROOT'].$newf);
				} else {
					$contr->status = "pend";
					$pic_fname = $contr->sessid;
					$pic_dir = "/bin/temp/";
				}
				
				// Upload
				include_once("class.upload.php");
				$handle = new Upload($_FILES['personpic']);
		    if ($handle->uploaded) {
		    	$handle->file_overwrite        = true;
					$handle->file_auto_rename      = false;
					$handle->image_convert         = 'png';
					$handle->image_resize          = true;
					$handle->image_ratio_crop      = true;
					$handle->image_y               = 175;
					$handle->image_x               = 150;
					$handle->file_new_name_body    = $pic_fname;
					$handle->file_safe_name        = false;
					$handle->Process($_SERVER['DOCUMENT_ROOT'].$pic_dir);
					if ($handle->processed) {
						//thumbnail
						$handle->file_overwrite        = true;
						$handle->file_auto_rename      = false;
						$handle->image_convert         = 'png';
						$handle->image_resize          = true;
						$handle->image_ratio_crop      = true;
						$handle->image_y               = 40;
						$handle->image_x               = 40;
						$handle->file_new_name_body    = $pic_fname."-tn";
						$handle->file_safe_name        = false;
						$handle->Process($_SERVER['DOCUMENT_ROOT'].$pic_dir);
						if (!$handle->processed) $errors[] = "Could not make thumbnail";
					} else $errors[] = "Upload Error: ".$handle->error;
		    } else {
					$errors[] = 'Background Image file not uploaded on the server: ' . $handle->error;
		    }
				
				$cres = $contr->submitNew();
				$success[] = $cres;
		    
		    break;
				
			case "person dob": //person DOB
				
				$date = $thisf['year']."-".$thisf['month']."-".$thisf['day'];
				if(!preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $date)) {
					$warnings[] = $thisf['desc'].": Birthdate ($date) not valid";
					$date = "";
				}
				
				$contr->data = "{dob:}$date";
				$contr->status = "pend";
				
				if($_SESSION['user_rank'] >= 4) {
					$contr->status = "publish";
					$contr->process_data = TRUE;
				}
				
				$cres = $contr->submitNew();
				$success[] = $cres;
				
				break;
				
			case "person associations": //person assoc
				
				$str = '`'.trim($thisf['data']).'`';
				$str = str_replace("\r", "\n", $str);
				$str = str_replace("\n", "`", $str);
				$str = preg_replace("/`+/", "`", $str);
				
				$contr->data = '{'.$field.':}'.$str;
				$contr->status = "pend";
				
				if($_SESSION['user_rank'] >= 4) {
					$contr->status = "publish";
					$contr->process_data = TRUE;
				}
				
				$cres = $contr->submitNew();
				$success[] = $cres;
				
				break;
			
			case "person role":
				
				//via the game page
				
				list($table, $ofield, $wid) = explode(":", $thisf['subj']);
				$pid = substr($thisf['ssubj'], 4);
				
				$thisf['role'] = trim($thisf['role']);
				if($thisf['role'] == "") $thisf['delete'] = TRUE;
				
				$contr->data = "{role:}".$thisf['role']."|--|{vital:}".$thisf['vital']."|--|{notes:}".$thisf['notes'];
				$contr->process_data = TRUE;
				
				//delete
				if($thisf['delete']) {
					$contr->data.= "|--|{*delete_row:}True; Delete this entry|--|{*previous_values:}";
					$q = "SELECT * FROM `people_work` WHERE id = '$wid' LIMIT 1";
					$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
					$contr->data.= "Role:".$row['role']."; Notes:".$row['notes'];
					$contr->no_points = TRUE;
					if($_SESSION['user_rank'] <= 7) $contr->status = "pend";
				}
				
				$success[] = $contr->submitNew();
				
				break;
			
			case "game title":
				
				list($title, $x) = formatName($thisf['field'][$thisf['subj']]);
				
				if($title == "") $errors[] = "No title given";
				else {
					$contr->data = "{title:}".$title;
					$contr->process_data = TRUE;
					$success[] = $contr->submitNew();
				}
				
				break;
			
			case "game list": //genres, devs, series
				
				$x = explode(":", $thisf['ssubj']);
				list($table, $ofield, $gid) = explode(":", $thisf['subj']);
				
				$arr = array();
				$arr = preg_split("/\r|\n/", $thisf['field']);
				$data = array();
				foreach($arr as $s) {
					$s = trim($s);
					$s = strip_tags($s);
					if($s != "") $data[] = $s;
				}
				
				$contr->data = "{".str_replace("games_", "", $table).":}".implode("|,,|", $data);
				
				if(!$table || !$gid) {
					$errors[] = $thisf['desc'].": Insufficient handle given";
				} else {
					
					$pend = $contr->isPending();
					if(!$pend) {
						
						$q = "DELETE FROM `".$table."` WHERE gid='$gid'";
						if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = $thisf['desc'].": Couldn't delete old rows from $table";
						else {
							$q = "";
							foreach($data as $str) $q.= "('$gid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $str)."'),";
							if($q) {
								$q = "INSERT INTO `$table` VALUES ".substr($q, 0, -1).";";
								if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = $thisf['desc'].": Couldn't update database; ".mysqli_error($GLOBALS['db']['link']);
							}
						}
						
					}
					
					$success[] = $contr->submitNew();
				}
				
				break;
				
			case "game publication":
					
				list($table, $ofield, $pubid) = explode(":", $thisf['subj']);
				$gid = substr($thisf['ssubj'], 4);
				
				//delete
				if($thisf['delete']) {
					
					if($_SESSION['user_rank'] == 9) {
						$q = "DELETE FROM games_publications WHERE id='$pubid' LIMIT 1";
						if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't delete from db; ".mysqli_error($GLOBALS['db']['link']);
						else $results[] = "Publication #$pubid deleted";
					} else {
						//suggest
						$to      = getenv('NOTIFICATION_EMAIL');
						$subject = '[Videogam.in] Suggest destruction';
						$message = '<html>
							'.$usrname.' suggests deleting <a href="/games/'.$gid.'/">publication #'.$pubid.'</a>
							</html>';
						$headers = 'From: noreply@videogam.in' . "\r\n" .
							'Reply-To: noreply@videogam.in' . "\r\n" .
							'MIME-Version: 1.0' . "\r\n" . 
							'Content-type: text/html; charset=iso-8859-1' . "\r\n" . 
							'X-Mailer: PHP/' . phpversion();
						
						@mail($to, $subject, $message, $headers);
					}
				} else {
				
					$thisf['title'] = trim($thisf['title']);
					
					$rd = $thisf['year']."-".$thisf['month']."-".$thisf['day'];
					if(!preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $rd)) {
						$warnings[] = $thisf['desc'].": Release date ($rd) not valid";
						$rd = "";
					}
					
					$contr->data = "{title:}".$thisf['title']."|--|{release_date:}".$rd."|--|{platform_id:}".$thisf['platform_id']."|--|{region:}".$thisf['region']."|--|{placeholder_img:}".$thisf['placeholder_img']."|--|{primary:}".$thisf['primary'];
					
					$boxfiles = array();
					if($_FILES[$field]['name']) {
						$boxfiles = processBoxes($_FILES[$field], $gid);
						$contr->upload = $_FILES[$field];
					}
					
					if($_SESSION['user_rank'] >= 4) {
						
						if($thisf['primary']) {
							$q = "UPDATE games_publications SET `primary`='' WHERE gid='$gid'";
							mysqli_query($GLOBALS['db']['link'], $q);
						}
						
						$contr->status = "publish";
						$contr->process_data = TRUE;
					
						if($boxfiles) {
							$new_body = $gid."-box-".$pubid;
							$new_dir = "/games/files/".$gid."/";
							processBoxesDirs($boxfiles, $new_body, $new_dir);
						}
						
					} else {
						$contr->status = "pend";
					}
					
					$cres = $contr->submitNew();
					$success[] = $cres;
					
				}
				
				break;
			
			case "game bgimg": //game pg bg img
				
				if(!$_FILES['bgimgfile']['name']) break;
				if(!$thisf['gid']) break;
				
				//send current one to recycle bin
				if($curr = $_POST['currentfile']) {
					$newf = "/bin/deleted-files/".str_replace("/", "--", $curr)."--".rand(10000, 99999);
					rename($_SERVER['DOCUMENT_ROOT'].$curr, $_SERVER['DOCUMENT_ROOT'].$newf);
				}
				if($usrid != 1) @mail(getenv('NOTIFICATION_EMAIL'), "[Videogam.in] New game bg img!", "A new image has been uploaded by ".outputUser($usrid, false, false)." to http://videogam.in/games/$id\n\n".($newf ? "Note old img here -> http://videogam.in/$newf\n\n" : ""));
				
				// Upload
				include_once("class.upload.php");
				$handle = new Upload($_FILES['bgimgfile']);
		    if ($handle->uploaded) {
		    	$handle->file_new_name_body = 'background_'.$_POST['bgimgalign'];
		    	$handle->file_overwrite = true;
		    	$handle->Process($_SERVER['DOCUMENT_ROOT']."/games/files/".$thisf['gid']."/");
		        if ($handle->processed) {
		           $results[] = 'Background Image file uploaded: <a href="/games/files/'.$thisf['gid'].'/'.$handle->file_dst_name.'" target="_blank">'.$handle->file_dst_name.'</a>';
		        } else {
		           $errors[] = 'Background Image file not uploaded to the wanted location: ' . $handle->error;
		        }
		    } else {
		        // if we're here, the upload file failed for some reasons
		        // i.e. the server didn't receive the file
		        $errors[] = 'Background Image file not uploaded on the server: ' . $handle->error;
		    }
		    
		    break;
			
			case "game status":
				
				if($_SESSION['user_rank'] < 8) break;
				if(!$thisf['gid']) {
					$errors[] = "[status] No game id given";
					break;
				}
				
				$q = "UPDATE games SET 
					`unpublished` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $thisf['field']['unpublished'])."',
					`classic` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $thisf['field']['classic'])."',
					`vapid` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $thisf['field']['vapid'])."',
					`featured` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $thisf['field']['featured'])."' 
					WHERE gid='$thisf[gid]' LIMIT 1";
				if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "[status] mysql error: ".mysqli_error($GLOBALS['db']['link']);
				else $results[] = "Game status updated";
				
				break;
			
			default:
			
				//automatic db update
				
				while(list($handle, $val) = each($thisf['field'])) {
					
					$contr->subj = $handle;
					list($table, $ofield, $oid, $_field) = explode(":", $handle);
					if($ofield && $oid && $_field) {
						if($thisf['allow_common_html']) $val = strip_tags($val, '<b><strong><i><em><big><small><a>');
						elseif($thisf['allow_this_html']) $val = strip_tags($val, $thisf['allow_this_html']); //$thisf['allow_this_html'] is a str with tags, ie: '<b><i>'
						else $val = strip_tags($val);
						$val = trim($val);
						$contr->data = "{".$_field.":}".$val;
						if($val == "") {
							if($thisf['on_null'] == "delete") {
								$contr->data.= "|--|{*delete_row:}True; Delete this entry|--|{*previous_value:}";
								$q = "SELECT `$_field` FROM `$table` WHERE `$ofield` = '$oid' LIMIT 1";
								$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
								$contr->data.= $row[$_field];
							}
							$contr->no_points = TRUE;
							if($_SESSION['user_rank'] <= 7) $contr->status = "pend";
						}
						$contr->process_data = TRUE;
						$success[] = $contr->submitNew();
					} else {
						$errors[] = $thisf['desc'].": insufficient db handle given";
					}
				}
		
			} //switch
		
		}
		
		$page->style[] = "/bin/css/inline_edit.css";
		$page->javascripts[] = "/bin/script/inline_edit.js";
		$page->header();
		?>
		<h2>Submission Results</h2>
		
		<big>Your submissions have been safely recorded into the climate-controlled Videogam.in databank centers. Thanks for contributing!</big>
		
		<p><b>Add notes about your updates</b> for the editors and future contributors by clicking "Data, Notes, etc" on each row of the Contribution Summary below.</p>
		
		<p>
			<span class="arrow-left" style="color:#666">Back to</span> <b><a href="<?=$_POST['return_url']?>"><?=$_POST['return_title']?></a></b>
			<?
			if($_POST['primary_ssubj']) {
				$q = "SELECT * FROM watchlist WHERE supersubject = '".mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['primary_ssubj'])."' AND usrid = '$usrid' LIMIT 1";
				if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $watching = TRUE;
				?>
				&nbsp;&nbsp;&nbsp;
				<span class="chbox<?=($watching ? ' chbox-checked' : '')?>" onclick="if(!chboxLoading(this)) watchList('<?=$_POST['primary_ssubj']?>');"><span></span>Add <?=$_POST['return_title']?> to your watch list</span> 
				<a href="#help" class="tooltip" title="Easily track any additions or changes made to this subject">?</a>
				<?
			}
			?>
		</p>
		
		<table border="0 cellpadding="0" cellspacing="0"><tr><td>
			<table border="0" cellpadding="5" cellspacing="0" id="ILres" class="plain">
				<tr>
					<th colspan="5" style="background-color:#EEE">Contribution Summary</th>
				</tr>
				<tr>
					<th colspan="2">Description</th>
					<th>Contribution Type</th>
					<th>Status</th>
					<th>Points Earned</th>
				</tr>
				<?
				
				if(!$success) {
					?><tr><td colspan="5">No contributions recorded</td></tr><?
				} else {
					foreach($success as $s) {
						
						if($s['errors']) {
							echo '<tr><td colspan="3" style="background-color:#FFD9D9;"><b>ERROR(s):</b> '.implode("; ", $s['errors']).'</td><td style="text-align:center;">0</td></tr>';
						} else {
							if($s['potential_points']) {
								$s['points'] = $s['potential_points'];
								$potential = TRUE;
							}
							$tpts = $tpts + $s['points'];
							
							?>
							<tr>
								<td><?=bb2html($s['desc'])?></td>
								<td><a href="#toggle_data" class="preventdefault arrow-toggle" onclick="$(this).toggleClass('arrow-toggle-on').closest('tr').toggleClass('bottomflush').next().toggle();">Data, Notes, etc.</a></td>
								<td><?=$s['type_desc']?></td>
								<td style="background-color:<?=($s['published'] ? '#D7F2D0;">Published' : '#FFB;">Pending Approval')?></td>
								<td style="text-align:center;"><?=($s['points'] ? $s['points'] : '0').($s['potential_points'] ? '*' : '')?></td>
							</tr>
							<tr style="display:none;">
								<td colspan="5">
									<b>Data:</b> <?=$s['data']?>
									<div style="margin:6px 0 3px;">
										<textarea name="cid_<?=$s['contribution_id']?>" rows="2" cols="100" class="contribution-notes resetonfocus">Add notes about this particular update</textarea>
									</div>
									<?
									if(!$s['no_points']) {
										?>
										<div style="float:right; background-color:#FFB; padding:3px 4px;">
											<span class="chbox" onclick="if(!chboxLoading(this)) forfeitPoints('<?=$s['contribution_id']?>');"><span></span>Forfeit points earned</span>
										</div>
										<?
									}
									?>
									<input type="button" value="Save" id="submit-<?=$s['contribution_id']?>"/> 
								</td>
							</tr>
							<?
						}
					}
					?>
					<tr>
						<td colspan="4" style="background-color:#F5F5F5; text-align:right; font-weight:bold;">TOTAL POINTS EARNED</td>
						<td style="background-color:#F5F5F5; text-align:center;"><?=$tpts.($potential ? '*' : '')?></td>
					</tr>
					<?
				}
				?>
			</table>
			<?=($potential ? '<div style="margin-top:3px; text-align:right; font-size:11px;">*potential points earned upon editor approval</div>' : '')?>
		</td></tr></table>
		
		<?
		if($_SESSION['user_rank'] == 9) {
			echo '<br/><a href="javascript:void(0);" class="arrow-toggle" onclick="$(this).toggleClass(\'arrow-toggle-on\').next().toggle();">Data Received</a><div style="display:none">';
			foreach($fields as $field) {
				?><pre><? print_r($_POST['contr'][$field]); ?></pre><?
			}
			echo '</div>';
		}
		
		$page->footer();
		
	}
	
}

?>