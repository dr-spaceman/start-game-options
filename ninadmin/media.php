<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/admin.php");
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.upload.php");
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.unzip.php");

$page = new page;
$page->title = "Nintendosite Admin / Media Manager";
$page->min_rank = 6;
$page->admin = TRUE;

$subdir   = "/media";
$mediadir = $_SERVER["DOCUMENT_ROOT"].$subdir;

$in = $_POST['in'];

$page->javascript.= <<<EOF
<script type="text/javascript">
function browse(locn) {
	var goto = "../media/"
	locn = goto+locn
	new_window = window.open(locn,'window_name','toolbar=yes,location=yes,menubar=no,resizable=no,scrollbars=yes,dependent=0,status=yes,width=600,height=500,left=25,top=25')
}
function checkform() {
	if(""==document.upload.uploaddirectory.value) {
	alert("Select a directory first");
	return false;
	}
}
function goToOperations(locn) {
	var goto = "media.php?operatedir="
	locn = goto+locn
	window.location=locn
}
function confirmSubmit(messg) {
	var agree=confirm(messg);
	if (agree) return true;
	else return false;
}
var activeField = 1;
function addFields(i) {
	if(!i) i = activeField;
	if(i >= 14) {
		alert("Max of 15 fields reached");
		return;
	} else {
		document.getElementById('field-'+i).style.display="block";
	}
	activeField = i + 1;
}
function deleteTag(t) {
	document.getElementById('x-'+t).style.display='none';
	document.getElementById('loading-'+t).style.display='inline';
	asyncRequest(
		"post",
		"/ninadmin/media.php",
		function(response) {
			if(response.responseText) document.getElementById('li-'+t).style.display='none';
		},
		"delete_tag="+t
	);
}
</script>
EOF;

if(!is_writeable($mediadir)) {
	die("Fatal Error! The media directory($mediadir) is not writeable!");
}

$media_page_head = '
<h2>Media Management</h2>
'.($_POST || $_GET ? '<input type="button" value="End this upload session" onclick="window.location=\'media.php\';" style="margin:-5px 0 10px 0;"/>' : '');

//delete tag (asyncRequest)
if($del = $_POST['delete_tag']) {
	$q = "DELETE FROM media_tags WHERE id='$del' LIMIT 1";
	if(mysql_query($q)) echo '1';
}

//////////////////
// ADVANCED OPS //
//////////////////

if($operatedir = $_GET['operatedir']) {

	//get mediadata
	$q = "SELECT * FROM media WHERE directory='/media/$operatedir' LIMIT 1";
	if(!$dat = mysql_fetch_object(mysql_query($q))) {
		die("Couldn't get media data");
	}
	
	//user has acccess to dir?
	if($dat->usrid != $usrid && $usrrank <= 7) {
		die("No access to $operatedir");
	}
	
}

//delete the directory
if($operatedir && ($_POST['operation'] == "deletedir" || $_GET['operation'] == "deletedir")) {
	if($_GET['confirmdelete'] != "yes") {
		echo ("<a href=\"media.php?operatedir=$operatedir&operation=deletedir&confirmdelete=yes\" style=\"font:bold 28px 'lucida sans unicode'; background-color:#ECFFFF;\">Yes</a>, 
		premanently delete this directory & all contents.");
		exit;
	} else {
		$subj = $mediadir."/".$operatedir;
		if(deleteDirectory($subj, TRUE)) {
			$results[] = 'Directory deleted & all files copied to <a href="/bin/deleted-files/media/'.$operatedir.'/" target="_blank">/bin/deleted-files/media/'.$operatedir.'/</a>';
			adminAction(str_replace($_SERVER['DOCUMENT_ROOT'], "", $subj), "delete");
			$q = "DELETE FROM media WHERE media_id='$dat->media_id' LIMIT 1";
			mysql_query($q);
			$q = "DELETE FROM media_tags WHERE media_id='$dat->media_id'";
			mysql_query($q);
			$q = "DELETE FROM media_captions WHERE media_id='$dat->media_id'";
			mysql_query($q);
		} else $errors[] = "Could not delete directory";
		unset($operatedir);
	}
}

//dir operations
if($operatedir) {
		
	//edits/changes
	if($_POST['submit_edits']) {
		if(!$operatedir) die("no directory value assigned");
		//delete all original captions
		$q = "DELETE FROM media_captions WHERE media_id='".$dat->media_id."'";
		if(!mysql_query($q)) {
			$errors[] = "Couldn't delete original captions, so no captions were added or changed";
			$caption_error = TRUE;
		}
		foreach($in as $i) {
			if($i['delete']) {
				if(unlink($mediadir."/".$operatedir."/".$i['file'])) $results[] = "Deleted ".$i['file'];
				else $errors[] = "Could not delete ".$i['file'];
				if($i['thumb']) unlink($mediadir."/".$operatedir."/thumbs/".$i['thumb']);
			} else {
				if($i['caption']) {
					$i['caption'] = strip_tags($i['caption']);
					$i['caption'] = htmlentities($i['caption']);
					$q = sprintf("INSERT INTO media_captions (media_id, `file`, `caption`) VALUES ('$dat->media_id', '".$i['file']."', '%s')",
						mysql_real_escape_string($i['caption']));
					if(!mysql_query($q)) $errors[] = "Couldn't add caption (\"".$i['caption']."\") to file ".$i['file'];
				}
			}
		}
		if(!$errors) $results[] = "All changes successfully made";
		if($err = updateQty($operatedir)) $errors[] = $err;
	}
	
	if($_POST['submitoperation']) {
		if($_POST['operation'] == "deletethumbs") {
			//delete thumbs
			if($handle = opendir($mediadir."/".$operatedir."/thumbs")) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						if(!unlink($mediadir."/".$operatedir."/thumbs/".$file))
							$errors[] = "could not delete $file";
					}
				}
			}
			if(!$error) $results[] = "All thumbs deleted";
		
		}
		
		if($_POST['operation'] == "makethumbs" || $_POST['also'] == "makethumbs") {
			//make thumbs
			if(!$operatedir) die("no directory value assigned");
			if(!file_exists($mediadir."/".$operatedir."/thumbs")) {
				if(!mkdir($mediadir."/".$operatedir."/thumbs", 0777))
					die("could not create thumbs directory");
			}
			if($h = opendir($mediadir."/".$operatedir)) {
				while (false !== ($file = readdir($h))) {
					if ($file != "." && $file != ".." && $file != "thumbs") {
						$handle = new Upload($mediadir."/".$operatedir."/".$file);
						if ($handle->uploaded) {
							$handle->image_resize = true;
							$handle->image_ratio_crop = 'T';
							$handle->image_y = 100;
							$handle->image_x = 100;
							$handle->Process($mediadir."/".$operatedir."/thumbs");
							if (!$handle->processed)
								$errors[]=$handle->error;
						} else
							$errors[]=$handle->error;
					}
				}
			} else
				$errors[]="could not open dir $mediadir."/".$operatedir";
			if(!$error)
				$results[]="Thumbnails created";
		
		} elseif($_POST['operation'] == "rename") {
			//rename dir
			if($usrrank <= 7) die("Can't rename with your rank");
			if(!$newname = $_POST['newname']) $errors[] = ("no new name given");
			if(!eregi("^([a-zA-Z0-9_-])+$", $newname) || !eregi("[a-zA-Z]+", $newname))
				$errors[] = ("Error: illegal directory name! Shame on you!");
			if(is_dir($mediadir."/".$newname))
				$errors[] = ("That directory (".$newname.") already exists");
			if(!$errors) {
				if(rename($mediadir."/".$operatedir, $mediadir."/".$newname)) {
					$results[] = "You have renamed $operatedir to $newname";
					adminAction("/media/".$newname, "renamed from /media/$operatedir");
					$warnings[] = "You only RENAMED the directory. All links, updates, and database entries that were pointing to your old directory remain unchanged and will need to be updated manually.";
					//reflect new name in db
					$q = "UPDATE media SET directory='/media/$newname' WHERE directory='/media/$operatedir' LIMIT 1";
					if(!mysql_query($q)) {
						die("The directory has been renamed but there was a fatal error: Couldn't reflect new name in media database! ARG!!!");
						sendBug("Fatal error: couldn't rename media dir in db table `media`: /media/$operatedir => /media/$newname");
					}
					$operatedir = $newname;
				} else
					$errors[] = "could not rename";
			}
			
		} elseif($_POST['operation'] == "updateqty") {
			//update qty
			if($err = updateQty($operatedir)) $errors[] = $err;
			else $results[] = "Quantity updated";
		}
	}
	
	//check thumbcount
	if($handle = opendir($mediadir."/".$operatedir."/thumbs")) {
		while (false !== ($file = readdir($handle))) {
			if($file != '.' && $file != '..') $thumbcount++;
		}
	}
	if($thumbcount) {
		if($handle = opendir($mediadir."/".$operatedir)) {
			while (false !== ($file = readdir($handle))) {
				if($file != '.' && $file != '..') $imgcount++;
			}
		}
		if(($imgcount - 1) != $thumbcount) {
			$warnings[] = 'The number of thumbnails doesn\'t equal the number of full-sized images. 
				This could lead to problems with the gallery module. 
				<form action="media.php?operatedir='.$operatedir.'" method="post">
					<input type="hidden" name="operation" value="deletethumbs"/>
					<input type="hidden" name="also" value="makethumbs"/>
					<input type="submit" name="submitoperation" value="Delete and remake thumbnails"/>
				</form>';
		}
	}
	
	$page->header();
	echo $media_page_head;
	
	?>
	
	<fieldset style="background-color:#EEE;">
		<legend>Working directory</legend>
		<big><b><a href="javascript:browse('<?=$operatedir?>')">/media/<?=$operatedir?></a></b></big>
		<p style="margin-left:5px; padding-left:13px; background:url(/bin/img/arrow-down-right.png) no-repeat 0 2px;">
			<a href="?uploaddirectory=<?=$operatedir?>">upload / general operations</a>
		</p>
	</fieldset>
	<br/>
	
	<fieldset>
		<legend>Directory Operations</legend>
		
		<form action="media.php?operatedir=<?=$operatedir?>" method="post">
			<select name="operation">
				<option value="">Directory Actions...</option>
				<option value="updateqty">Update file quantities</option>
				<option value="deletethumbs">Delete all thumbnail images</option>
				<option value="makethumbs">Make thumbnails of all fullsize images</option>
				<option value="deletedir">Delete this directory & all contents</option>
			</select> 
			<input type="submit" name="submitoperation" value="Submit" onClick="javascript:return confirmSubmit('Are you sure?')" />
		</form>
		
		<? if($usrrank >= 8) { ?>
		<br />
		<form action="media.php?operatedir=<?=$operatedir?>" method="post">
			<input type="hidden" name="operation" value="rename" />
			Rename directory to:
			<p><input type="text" name="newname" value="<?=$operatedir?>" maxlength="40" size="30" /> 
			<input type="submit" name="submitoperation" value="Rename" /></p>
		</form>
		<? } ?>
	</fieldset>
	
	<br />
	
	<fieldset>
		<legend>Individual File Operations</legend>
		<?
		if($handle = opendir($mediadir."/".$operatedir)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && $file != "thumbs") $files[] = $file;
			}
		}
		if($handle = opendir($mediadir."/".$operatedir."/thumbs")) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && $file != "thumbs") $thumbs[] = $file;
			}
		}
		$query = "SELECT * FROM media_captions WHERE media_id='$dat->media_id'";
		$res   = mysql_query($query);
		while($row = mysql_fetch_assoc($res)) {
			$capts[$row['file']] = $row['caption'];
		}
		if(!$files) {
			echo "No files uploaded";
		} else {
			sort($files);
			sort($thumbs);
			$filenum = count($files);
			$thumbnum = count($thumbs);
			if($filenum != $thumbnum) unset($thumbs);
			?>
			<form action="media.php?operatedir=<?=$operatedir?>" method="post">
				<table border="0" cellpadding="0" cellspacing="0" width="100%" class="styled-form">
					<thead>
						<tr>
							<th>File</th>
							<th>Caption</th>
							<th>Delete</th>
						</tr>
					</thead>
					<?
					for($i = 0; $i < $filenum; $i++) {
						echo '<input type="hidden" name="in['.$i.'][file]" value="'.$files[$i].'"/>';
						echo '<input type="hidden" name="in['.$i.'][action]" value="'.($capts[$files[$i]] ? 'update' : 'insert').'"/>';
						echo '<tr><th colspan="3">'.$files[$i].'</th></tr><tr id="row-'.$i.'">';
						echo '<th style="border-top-width:0"><a href="'.$dat->directory.'/'.$files[$i].'" class="thickbox" rel="gallery" title="'.htmlentities($capts[$files[$i]]).'">';
						if($thumbs[$i]) {
							echo '<img src="'.$dat->directory.'/thumbs/'.$thumbs[$i].'" alt="'.$thumbs[$i].'" style="margin-left:10px"/></a>';
							echo '<input type="hidden" name="in['.$i.'][thumb]" value="'.$thumbs[$i].'"/>';
						}
						else echo $files[$i].'</a></p>(No thumbnail)';
						echo '</td><td><textarea name="in['.$i.'][caption]" rows="3" cols="42">'.$capts[$files[$i]].'</textarea><p><abbr title="Use this code to display this image thumbnail. Replace \'left\' with \'right\' to float the thumbnail to the right.">Code</abbr>: <input type="text" value="[[T||left||'.$dat->directory.'/'.$files[$i].'||'.$dat->directory.'/thumbs/'.$thumbs[$i].'||'.($capts[$files[$i]] ? htmlentities($capts[$files[$i]]) : 'CAPTION (optional)').']]" size="47"/></p></td>';
						echo '<td><label><input type="checkbox" name="in['.$i.'][delete]" value="1" onclick="if(this.checked==true) document.getElementById(\'row-'.$i.'\').className=\'selected\'; else document.getElementById(\'row-'.$i.'\').className=\'\';"/> delete</label></td></tr>'."\n";
					}
					?>
				</table>
				<p><input type="submit" name="submit_edits" value="Submit Changes"/></p>
			</form>
			<?
		}
		?>
	</fieldset>
	
<?
$page->footer();
exit;
}

//create dir
if($_POST['createdirectory'] && $directoryname = $_POST['directoryname']) {
	if(preg_match("/[^a-zA-Z0-9-_]/", $directoryname)) {
		$directoryname = preg_replace("/[^a-zA-Z0-9-_]/", "", $directoryname);
		if($directoryname = "") die("Illegal directory name!");
		$warnings[] = "Your directory name contained illegal characters and has been changed";
	}
	$subj = $mediadir."/".$directoryname;
	if(is_dir($subj))
		die("That directory ($directoryname) already exists");
	if(mkdir($subj, 0777) && mkdir($subj."/thumbs", 0777)) {
		$results[] = "Your directory has been created";
		//add to db
		$q = "INSERT INTO media (directory, datetime, usrid) VALUES ('/media/".$directoryname."', '".date("Y-m-d H:i:s")."', '$usrid')";
		if(!mysql_query($q)) {
			die("Directory created but couldn't update media details database table! That's bad!!!");
		}
		$_GET['uploaddirectory'] = $directoryname; // go directly to upload/general
		adminAction(str_replace($_SERVER['DOCUMENT_ROOT'], "", $subj), "create");
	} else {
		$errors[] = "Could not create directory!";
	}
}

//////////////////////
// UPLOAD / GENERAL //
//////////////////////

if(($_POST['submitupload'] && $uploaddirectory = $_POST['uploaddirectory']) || $uploaddirectory = $_GET['uploaddirectory']) {
	
	//get media_id
	$q = "SELECT * FROM media WHERE directory='/media/$uploaddirectory' LIMIT 1";
	if(!$dat = mysql_fetch_object(mysql_query($q))) {
		die("Couldn't get media_id");
	}
	$mid = $dat->media_id;
	
	//zip file
	if($_FILES['zip']) {
		$handle = new Upload($_FILES['zip']);
		if($handle->uploaded) {
			$handle->Process($mediadir."/".$uploaddirectory);
			if ($handle->processed) {
				$zip = new dUnzip2($mediadir."/".$uploaddirectory."/".$handle->file_dst_name);
				$zip->unzipAll($mediadir."/".$uploaddirectory);
				unlink($mediadir."/".$uploaddirectory."/".$handle->file_dst_name);
				$results[] = "All zipped files have been extracted to the directory.";
				if($err = updateQty($uploaddirectory)) $errors[] = $err;
			}
		}
	}
	
	//individual files
	if($_FILES['userfile']) {
		$files = array();
	  foreach ($_FILES['userfile'] as $k => $l) {
			foreach ($l as $i => $v) {
				if (!array_key_exists($i, $files)) {
					$files[$i] = array();
				}
				$files[$i][$k] = $v;
			}
	  }
	  
	  $capt = $_POST['caption'];
		
		$f = 0;
		foreach ($files as $file) {
	    $handle = new Upload($file);
	    
	    if($_POST['addwatermark'])
	    	$handle->image_watermark = "../index/watermark.png";
	    
	    if($handle->uploaded) {
				$handle->Process($mediadir."/".$uploaddirectory);
				if ($handle->processed) {
					$upl_res = '<dl><dt><b><a href="/media/'.$uploaddirectory.'/'.$handle->file_dst_name.'" target="_blank">' . $handle->file_dst_name . '</a></b></dt>';
					$upl_res.= '<dd>'.ceil((filesize($handle->file_dst_pathname)/256)/4) . 'KB</dd>';
					//caption
					if($capt[$f]) {
						$capt[$f] = strip_tags($capt[$f]);
						$capt[$f] = htmlentities($capt[$f]);
						$q = sprintf("INSERT INTO media_captions (media_id, `file`, `caption`) VALUES ('$mid', '".$handle->file_dst_name."', '%s')",
							mysql_real_escape_string($capt[$f]));
						if(!mysql_query($q)) $errors[] = "Could not add caption (\"".$capt[$f]."\") to ".$handle->file_dst_name;
					}
					//thumbs
					if(!$_POST['makethumbs']) {
						if(file_exists($mediadir."/".$uploaddirectory."/thumbs/".$handle->file_dst_name_body.".jpg")) {
							$upl_res.= '<dd>Individual link: <code style="font-size:11px">&lt;a href="/media/'.$uploaddirectory.'/'.$handle->file_dst_name.'" title="'.$capt[$f].'" class="thickbox">&lt;img src="/media/'.$uploaddirectory.'/thumbs/'.$handle->file_dst_name_body.'.jpg" alt="'.$capt[$f].'"/>&lt;/a></code></dd>';
						}
					} else {
						$handle->image_convert = 'jpg';
			      $handle->image_resize = TRUE;
			      $handle->image_ratio_crop = TRUE;
						$handle->image_x = 100;
						$handle->image_y = 100;
						$handle->Process($mediadir."/".$uploaddirectory."/thumbs");
						$upl_res.= '<dd><a href="/media/'.$uploaddirectory . '/thumbs/' . $handle->file_dst_name . '" target="_blank">thumbnail</a></dd>';
						$upl_res.= '<dd style="clear:both"><code class="code-for-img">&lt;a href="/media/'.$uploaddirectory.'/'.$handle->file_dst_name.'" title="'.$capt[$f].'" class="thickbox">&lt;img src="/media/'.$uploaddirectory.'/thumbs/'.$handle->file_dst_name.'" alt="'.$capt[$f].'"/>&lt;/a></code>
							<a href="/media/'.$uploaddirectory.'/'.$handle->file_dst_name.'" title="'.$capt[$f].'" class="thickbox"><img src="/media/'.$uploaddirectory.'/thumbs/'.$handle->file_dst_name.'" alt="'.$capt[$f].'"/></a></dd>';
					}
					$upl_res.= "</dl>\n";
					$results[] = $upl_res;
					$upl_res = "";
				} else {
					$errors[] = 'xfile not uploaded! Error: ' . $handle->error;
				}
	        
			}
			$f++;
		}
		if($err = updateQty($uploaddirectory)) $errors[] = $err;
	}
	
	//details
	if($det = $_POST['det']) {
		if(!preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/", $det['datetime'])) {
			$errors[] = "Date Time given (".$det['datetime'].") not valid (YYYY-MM-DD HH:MM:SS); Reverting to previous value.";
		} else {
			$q_append = ", datetime='".$det['datetime']."'";
		}
		$q = sprintf("UPDATE media SET 
			category_id = '".$det['category_id']."', 
			description = '%s', 
			gallery = '".$det['gallery']."', 
			source = '%s'".$q_append.",
			unpublished = '".$det['unpublished']."' 
			WHERE directory='/media/$uploaddirectory' LIMIT 1",
			mysql_real_escape_string($det['description']), 
			mysql_real_escape_string($det['source']));
		if(!mysql_query($q)) {
			$errors[] = "Couldn't update delection details";
		} else {
			$results[] = "Selection details updated";
		}
		
		/*
		if($dat->unpublished && !$det['unpublished']) {
			//credit the uploader with publication
			$q = "UPDATE users_contributions SET published='1' WHERE `subject`='media:".$mid."'";
			mysql_query($q);
		} elseif(!$dat->unpublished && $det['unpublished']) {
			//de-credit the uploader with publication
			$q = "UPDATE users_contributions SET published='0' WHERE `subject`='media:".$mid."'";
			mysql_query($q);
		}
		*/
		
	}
	
	//get media data
	$q = "SELECT * FROM media WHERE directory='/media/$uploaddirectory' LIMIT 1";
	if(!$dat = mysql_fetch_object(mysql_query($q))) {
		die("Couldn't get media data");
	}
  
	//tags
	if($tag = $_POST['tag']) {
		foreach($tag as $t) {
			if($t != "") {
				$t = mysql_real_escape_string($t);
				$q = "INSERT INTO media_tags (media_id, tag) VALUES ('$dat->media_id', '$t')";
				if(!mysql_query($q)) {
					$errors[] = "Couldn't tag ".outputTag($t);
				} else {
					$results[] = "Successfully tagged <i>".outputTag($t)."</i> ($t)";
				}
			}
		}
	}
	
	if($dat->unpublished) $warnings[] = "This media selection is currently unpublished (no one can see it!). To change this, update the 'Selection Details' form below.";

	$page->header();
	echo $media_page_head;

	if($upl_res) {
	echo ("
	<fieldset id=\"uploadresult\">
	<legend>Upload status</legend>
	<div class=\"notice\"><b>You have uploaded media!</b> Link to images with the gallery module: <a href=\"/gallery.cgi?dir=media/$uploaddirectory\">/gallery.cgi?dir=media/$uploaddirectory</a> or link directly to individual media items below:</div>
	$upl_res
	</fieldset><br />");
	}

?>

<form action="media.php" method="post" name="upload" enctype="multipart/form-data" onSubmit="document.getElementById('submit-button').style.display='none'; document.getElementById('submit-loading').style.display='inline';">
<input type="hidden" name="uploaddirectory" value="<?=$uploaddirectory?>" />

<fieldset id="working-dir">
	<legend>Working directory</legend>
	<big><b><a href="/media/<?=$uploaddirectory?>" target="_blank">/media/<?=$uploaddirectory?></a></b></big>
	<p><a href="?operatedir=<?=$uploaddirectory?>">advanced operations</a></p>
	<?
	if($handle = opendir($mediadir."/".$uploaddirectory)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && $file != "thumbs") {
				$fnum++;
			}
		}
	}
	if($handle = opendir($mediadir."/".$uploaddirectory."/thumbs")) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				$tnum++;
			}
		}
	}
	?><p><b><?=($fnum ? $fnum : '0')?></b> files <span style="color:#888">|</span> <b><?=($tnum ? $tnum : '0')?></b> thumbnails</p>
	<p>Created on <?=formatDate($dat->datetime)?> by <?=outputUser($dat->usrid, FALSE)?></p>
	<?=($dat->gallery ? '<p>Link to this gallery: <a href="/media.php?mid='.$dat->media_id.'">/media.php?mid='.$dat->media_id.'</a></p>' : '')?>
</fieldset>
<br/>

<fieldset>
	<legend>.ZIP file</legend>
	Upload and uncompress a .zip file
	<p><b>Size limit is 7 MB!</b></p>
	<p><input name="zip" type="file"/></p>
</fieldset>
<br/>

<fieldset id="themedia">
	<legend>Individual media items</legend>
	
	<div><input type="file" name="userfile[0]" onchange="addFields(1);"/>&nbsp; <label><abbr title="A description of the image (optional)">caption</abbr>: <input type="text" name="caption[0]" size="40"/></label></div>
	<?
	for($i = 1; $i < 15; $i++) {
		echo '<div id="field-'.$i.'" style="display:none"><input type="file" name="userfile['.$i.']"'.($i != 14 ? ' onchange="addFields('.($i + 1).');"' : '').'/>&nbsp; <label>caption: <input type="text" name="caption['.$i.']" size="40"/></label></div>'."\n";
	}
	?>
	<p><input type="button" value="Add Field" onclick="addFields();"/></p>
	<br/>
	
	<label><input type="checkbox" name="makethumbs" value="1" checked="checked"/> Create thumbnails of all images</label><br />
	<label><input type="checkbox" name="addwatermark" value="1"/> Add the site watermark to all images</label><br />
	<p><small><img src="/bin/img/icons/warn.png" style="margin-top:0px; vertical-align:bottom;" /> 
		Don't put the watermark on any images that already have another site's watermark on them. 
		Watermarks are only warranted when the images <b>originated</b> from this site, but use your own discretion if there is 
		no watermark.</small></p>

</fieldset>

<br />

<fieldset>
	<legend>Selection Details</legend>
	Please input some details here to better categorize this media selection
	<?
	$dat->description = stripslashes($dat->description);
	?>
	<table cellspacing="0" width="100%" class="styled-form" style="margin-top:5px">
		<tr>
			<th>Category</th>
			<td><select name="det[category_id]">
				<option value="">Select...</option>
				<?
				$query = "SELECT * FROM media_categories ORDER BY category";
				$res   = mysql_query($query);
				while($row = mysql_fetch_assoc($res)) {
					if($row['category_id'] == $dat->category_id) {
						$sel[$row['category_id']] = ' selected="selected"';
						$cat_desc = $row['category_description'];
					}
					$row['category_description'] = str_replace("'", "`", $row['category_description']);
					echo '<option value="'.$row['category_id'].'"'.$sel[$row['category_id']].' onclick="document.getElementById(\'cat-desc\').innerHTML=\''.$row['category_description'].'\';">'.$row['category']."</option>\n";
				}
				?>
				</select>
				<p><small><b>Category description</b>: <span id="cat-desc"><?=$cat_desc?></span></small></p>
			</td>
		</tr>
		<tr>
			<th>Description</th>
			<td><textarea name="det[description]" rows="2" cols="50"><?=$dat->description?></textarea></td>
		</tr>
		<tr>
			<th>Gallery module</th>
			<td>
				<label><input type="radio" name="det[gallery]" value="1"<?=($dat->gallery == '1' ? ' checked="checked"' : '')?>/> Yes, link this selection to the gallery module (<b>default</b>)</label><br/>
				<label><input type="radio" name="det[gallery]" value="0"<?=($dat->gallery == '0' ? ' checked="checked"' : '')?>/> No, link directly to the raw media</label>
			</td>
		</tr>
		<tr>
			<th>Source</th>
			<td>
				<small>Include HTML to link back to the source</small>
				<p><textarea name="det[source]" id="source-field" rows="2" cols="50"><?=$dat->source?></textarea></p>
			</td>
		</tr>
		<tr<?=($usrrank <= 7 ? ' style="display:none"' : '')?>>
			<th>Date Time</th>
			<td>
				<small>YYYY-MM-DD HH:MM:SS</small>
				<p><input type="text" name="det[datetime]" value="<?=$dat->datetime?>" maxlength="19"/></p>
			</td>
		</tr>
		<tr>
			<th>Unpublish?</th>
			<td><label><input type="checkbox" name="det[unpublished]" value="1"<?=($dat->unpublished ? ' checked="checked"' : '')?>/> Yes, unpublish this media, hiding it from all regular site visitors.</label></td>
		</tr>
	</table>
</fieldset>
<br/>
				

<fieldset>
	<legend>Tags</legend>
	
	<div class="tag-list">
		<b>Current Tags:</b>
		<ul>
			<?
			$query = "SELECT * FROM media_tags WHERE media_id='$dat->media_id'";
			$res   = mysql_query($query);
			if(!mysql_num_rows($res)) {
				echo '<li>Nothing tagged yet</li>';
			} else {
				while($row = mysql_fetch_assoc($res)) {
					if($usrrank >= 7) $tag_admin = ' <a href="#x" onclick="deleteTag(\''.$row['id'].'\');" class="x" id="x-'.$row['id'].'">X</a><img src="/bin/img/loading-arrows-small.gif" alt="loading" style="display:none" id="loading-'.$row['id'].'"/>';
					echo '<li id="li-'.$row['id'].'">'.outputTag($row['tag'], '', true).$tag_admin."</li>\n";
				}
			}
			?>
		</ul>
	</div>
	
	<hr style="margin:10px 0"/>
	
	<select name="tag[1]">
		<option value="">Tag a game...</option>
		<?
		$query = "SELECT * FROM games order by `title`";
		$res   = mysql_query($query);
		while($row = mysql_fetch_assoc($res)) {
			echo '<option value="gid:'.$row['gid'].'">'.(strlen($row['title']) > 55 ? substr($row['title'], 0, 54)."&hellip;" : $row['title'])."</option>\n";
		}
		?>
	</select> 
	
	<select name="tag[2]">
		<option value="">Tag a person...</option>
		<?
		$query = "SELECT * FROM people ORDER BY `name` ASC";
		$res   = mysql_query($query);
		while($row = mysql_fetch_assoc($res)) {
			echo '<option value="pid:'.$row['pid'].'">'.$row['name']."</option>\n";
		}
		?>
	</select> 
	
	<br/>or other: <input type="text" name="tag[3]"/>
	
</fieldset>

<br />

<input type="submit" name="submitupload" value="Submit & Upload" id="submit-button" style="font-weight:bold"/>
<img src="/bin/img/loading-thickbox.gif" alt="loading" id="submit-loading" style="display:none"/>

</form>

<?

$page->footer();
exit;
	
}


/////////////
// DEFAULT //
/////////////

$page->freestyle.= '
#dir-list {}
#dir-list FIELDSET { padding:0; background-color:#EEE; }
#dir-list LEGEND { margin-left:12px; }
#dir-list TABLE { margin-top: 8px; }
#dir-list TH { padding:5px; border-bottom:1px solid #808080; background-color:#EEE; }
#dir-list TH SMALL { font-weight:normal; color:#666; }
#dir-list TH.here { background:transparent url(/bin/img/gradient-t2b-eee.png) repeat-x scroll 0 -150px; border-right:1px solid #CCC; border-left:1px solid #CCC; }
#dir-list TH.here A { color:#DB5959 !important; }
#dir-list TD { padding:5px; border-top: 1px solid #808080; font-size:11px; background-color:#FFF; }
#dir-list TD SMALL { font-size:10px; }
#dir-list .unpublished { text-decoration:line-through; color:#D62929; }
';

$page->header();
echo $media_page_head;

?>

<big><b>This feature has been depreciated.</b> Please post and manage media via the <a href="/posts/manage.php">Content Manager</a>.</big>
<br/><br/>

<form action="media.php" method="post" onsubmit="return false;">
	<fieldset>
		<legend>Make New Directory</legend>
		<input type="text" name="directoryname" maxlength="30" size="40"/> 
		<input type="submit" name="createdirectory" value="Create Directory" disabled="disabled"/></p>
		<p>
			<img src="/bin/img/icons/warn.png" style="margin-top:0px; vertical-align:bottom;" /> 
			Your directory name should describe the media you will upload into it. 
			It must contain some letters and otherwise can only include numbers, dashes (-), and underscores (_). 
			As a general rule of thumb, call the directory <i>SUBJECT-DESCRIPTION</i>. For example: <i>dragon_quest_ix-screens</i>, 
			<i>destinys_child_groove-wallpaper</i>, etc.
		</p>
	</fieldset>
</form>
<br/>

<div id="dir-list">
<fieldset>
	<legend>Current Directories</legend>
	
	<table border="0" cellspacing="0" width="100%">
		<tr>
			<? 
			if(!$orderby  = $_GET['orderby']) {
				$orderby  = "datetime";
				$orderdir = "DESC";
			}
			if(!$orderdir && !$orderdir = $_GET['orderdir']) $orderdir = "ASC";
			$here[$orderby] = ' class="here"';
			?>
			<th nowrap="nowrap"<?=$here['directory']?>><a href="?orderby=directory" class="arrow-down">Manage Directory</a></th>
			<th>Content Description</th>
			<th<?=$here['category_id']?>><a href="?orderby=category_id" class="arrow-down">Category</a></th>
			<th<?=$here['quantity']?>><a href="?orderby=quantity&orderdir=DESC" class="arrow-down">Qty</a></th>
			<th<?=$here['datetime']?>><a href="?orderby=datetime&orderdir=DESC" class="arrow-down">Created</a></th>
			<th<?=$here['usrid']?>><a href="?orderby=usrid" class="arrow-down">Creator</a></th>
		</tr>
		<?
		$query = "SELECT *, description as dir_description FROM media LEFT JOIN media_categories USING (category_id) ORDER BY `$orderby` $orderdir";
		$res   = mysql_query($query);
		while($row = mysql_fetch_assoc($res)) {
			//user access?
			if($row['usrid'] != $usrid && $usrrank <= 7) {
				continue;
			} 
			$dir = str_replace("/media/", "", $row['directory']);
			?><tr>
			<td><a href="?uploaddirectory=<?=$dir?>"<?=($row['unpublished'] ? ' class="unpublished"' : '')?>><?=$dir?></a></td>
			<td><small><?=$row['dir_description']?></small></td>
			<td><?=$row['category']?></td>
			<td><?=$row['quantity']?></td>
			<td><?=formatDate($row['datetime'], 7)?></td>
			<td><?=outputUser($row['usrid'], FALSE)?></td>
		</tr>
		<?
		}
		?>
	</table>
</fieldset>
</div>
<?

$page->footer();

function updateQty($dir) {
	global $mediadir;
	$qty = 0;
	if($handle = opendir($mediadir."/".$dir)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && $file != "thumbs") {
				$qty++;
			}
		}
	}
	$q = "UPDATE media SET quantity='$qty' WHERE directory='/media/$dir' LIMIT 1";
	if(!mysql_query($q)) return "Couldn't update media quantity for $subdir/$dir!!!";
}

?>