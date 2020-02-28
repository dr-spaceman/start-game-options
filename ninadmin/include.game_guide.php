<? // This file is require()d for games-mod.php and should not be accessed on it's own

//get game guide data
$q = "SELECT * FROM games_guides WHERE gid='$id' LIMIT 1";
if($guidedat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
	//deny access if user is not an author
	if(strstr($guidedat->authors, " ")) {
		$authors = explode(" ", $guidedat->authors);
	} else {
		$authors[0] = $guidedat->authors;
	}
	if(!in_array($usrid, $authors) && $usrrank < 9) {
		die("No access to edit this guide since you aren't an author.");
	}
}

$guidedir = "/games/guides/".$gdat['title_url']."/";

if($do == "create") {
	
	// CREATE //
	if(!mkdir($_SERVER['DOCUMENT_ROOT'].$guidedir, 0777)) $errors[] = "Couldn't create guide dir ($guidedir)";
	
	if(!$errors) {
		$q = "SELECT * FROM games_guides WHERE gid='$id' LIMIT 1";
		if($guidedat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
			die("Guide already exists");
		}
		$q = "INSERT INTO games_guides (`gid`, `authors`) VALUES ('$id', '$usrid')";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Couldn't INSERT to games_guides: ".mysql_error());
		
		avert("?what=guide&id=$id");
	}
	
}

if($_POST['submit_new_dir']) {
	
	// MAKE DIR //
	
	$in['dirname'] = makeUrlStr($in['dirname']);
	if(!$in['dirname']) die("No directory name given");
	if(!mkdir($_SERVER['DOCUMENT_ROOT'].$guidedir.$in['dirname']."/")) {
		$errors[] = "Couldn't create media dir (".$guidedir.$in['dirname'].")";
	} else {
		if(!$conts = file_get_contents("templates/page-game.txt")) {
			die("Couldn't read from page template");
		} else {
			$conts = str_replace("[[GID]]", $id, $conts);
			$handle = fopen($_SERVER['DOCUMENT_ROOT'].$guidedir.$in['dirname']."/index.php", "w");
			if(!fwrite($handle, $conts)) {
				$errors[] = "The directory was created, but the index.php file couldn't be copied from the template (".$guidedir.$in['dirname']."/index.php)";
			} else {
				$results[] = "Directory successfully created";
			}
		}
	}
	
}

if($_GET['rename_dir']) {
	
	// RENAME DIR //
	
	$old = $_GET['rename_dir'];
	$new = $_GET['rename_to'];
	
	if(!$new) die("No name given to rename to");
	
	if(strstr("/", $old)) die("Error: illegal characters in old directory");
	if(strstr("/", $new)) die("Error: illegal characters in new directory");
	$new = preg_replace("/[^a-zA-Z0-9_-]/", "", $new);
	
	if(!rename($_SERVER['DOCUMENT_ROOT'].$guidedir.$old, $_SERVER['DOCUMENT_ROOT'].$guidedir.$new)) {
		$errors[] = "Couldn't rename '$old' to '$new'";
	} else {
		$results[] = "Renamed '$old' to '$new'";
	}
	
}

if($to = $_GET['new_file_to']) {
	
	// NEW FILE //
	
	if($_POST) {
		
		$in['filename'] = makeUrlStr($in['filename']);
		if(!$in['filename']) $errors[] = "No filename given";
		
		if(strstr($to, "/"))
			$errors[] = "Invalid directory ($to)";
		
		if(!is_dir($_SERVER['DOCUMENT_ROOT'].$guidedir.$to."/")) 
			$errors[] = "Error: Directory doesn't exist (".$guidedir.$to."/";
		
		if(file_exists($_SERVER['DOCUMENT_ROOT'].$guidedir.$to."/".$in['filename']))
			$errors[] = "File exists already ($to/$in[filename])";
		
		if($in['insert_template']) {
			$filename = $_SERVER['DOCUMENT_ROOT']."/ninadmin/templates/page-game.txt";
			$handle = fopen($filename, "r");
			$words = fread($handle, filesize($filename));
			fclose($handle);
		} else {
			$words = " ";
		}
		
		if(!$errors) {
			$handle = fopen($_SERVER['DOCUMENT_ROOT'].$guidedir.$to."/".$in['filename'], "a");
			if(!fwrite($handle, $words)) {
				$errors[] = "couldn't create file";
				unset($do);
			} else {
				avert("?what=guide&id=$id&edit_file=$to/".$in['filename']);
				exit;
			}
		}
	}
	
	$page->header();
	echo $mod_header;
	
	if(!$in['filename']) $in['filename'] = "myfile.php";
	
	?>
	Creating a new file in <i>/<?=$to?></i>
	<br/><br/>
	
	<form action="?what=guide&id=<?=$id?>&new_file_to=<?=$to?>" method="post" id="index">
		
		<b>File name:</b> <input type="text" name="in[filename]" value="<?=$in['filename']?>" size="18"/><br/>
		<small>Use only letters, numbers, -, _, and . and always include a file extension (.php, .htm, .css, etc)</small>
		<br/><br/>
		
		<label><input type="checkbox" name="in[insert_template]" value="1"/> Insert page template (.php files only)</label>
		<br/><br/>
		
		<input type="submit" value="Make file"/>
		
	</form>
	<?
	
	$page->footer();
	exit;
	
}

if($_POST['edit_file']) {
	
	// SUBMIT EDIT FILE //
	
	if(!$in['filename']) die("no filename specified");
	if(!$_POST['content']) die("content is blank");
	
	if($_POST['rename_file_to'] && $_POST['filedir']) {
		$new = $_POST['rename_file_to'];
		$new = preg_replace("/[^a-zA-Z0-9_\.-]/", "", $new);
		$new = $_POST['filedir']."/".$new;
		
		if(!rename($_SERVER['DOCUMENT_ROOT'].$guidedir.$in['filename'], $_SERVER['DOCUMENT_ROOT'].$guidedir.$new)) {
			$errors[] = "Couldn't rename '$in[filename]' to '$new'";
		} else {
			$results[] = "Renamed '$in[filename]' to '$new'";
			$in['filename'] = $new; //change file subject
		}
	}
	
	$handle = fopen($_SERVER['DOCUMENT_ROOT'].$guidedir.$in[filename], "w");
	if(!fwrite($handle, $_POST['content'])) {
		$errors[] = "File couldn't be written to";
	} else {
		adminAction($guidedir.$in['filename'], "edit");
		$results[] = "File successfully edited";
	}
	
	if($_POST['edit_file'] != "Save") { // continue editing
		$_GET['edit_file'] = $in['filename'];
	}
}

if($_GET['edit_file']) {
	
	// EDIT FILE FORM //
	
	$page->header();
	echo $mod_header;
	
	$file = $_SERVER['DOCUMENT_ROOT'].$guidedir.$_GET['edit_file'];
	if(!is_file($file)) die("File is not editable or doesn't exists ($file)");
	list($filedir, $filename) = explode("/", $_GET['edit_file']);
	?>
	
	<form action="?what=guide&id=<?=$id?>" method="post" onsubmit="content.toggleEditor();">
		<input type="hidden" name="in[filename]" value="<?=$_GET['edit_file']?>"/>
		
		Editing <a href="<?=str_replace($_SERVER['DOCUMENT_ROOT'], "", $file)?>" target="_blank"><?=$_GET['edit_file']?></a> 
		<input type="button" value="Rename file" onclick="document.getElementById('rename-container').style.display='inline'; document.getElementById('rename-file-to').disabled=false; this.style.display='none';"/> 
		<label id="rename-container" style="display:none">
			Rename file to: 
			<input type="text" name="rename_file_to" id="rename-file-to" value="<?=$filename?>" disabled="disabled"/>
			<input type="hidden" name="filedir" value="<?=$filedir?>"/>
		</label>
		<br/><br/>
		
		<input type="button" value="Toggle HTML color code editor" onclick="content.toggleEditor();"/>
		<p><textarea name="content" rows="25" cols="82" id="content" class="codepress php"><?
			readfile($file);
		?></textarea></p>
		<p>
			<input type="submit" name="edit_file" value="Save"/> 
			<input type="submit" name="edit_file" value="Save & Continue Editing"/>
		</p>
	</form>
	<?
	
	$page->footer();
	exit;
	
}

if($_GET['delete_file']) {
	
	// DELETE FILE //
	
	if(strstr($_GET['delete_file'], "..")) die("Invalid file");
	
	$handle = $_SERVER['DOCUMENT_ROOT'].$guidedir.$_GET['delete_file'];
	if(!is_file($handle)) {
		$errors[] = "Can't delete $handle since it isn't a file";
	} else {
		copy($handle, $_SERVER['DOCUMENT_ROOT']."/bin/deleted-files/games--guides--".$gdat['title_url']."--guide--".str_replace("/", "--", $_GET['delete_file'])."--".date('YmdHis'));
		if(!unlink($handle)) {
			$errors[] = "Couldn't delete $handle";
		} else {
			adminAction($guidedir.$_GET['delete_file'], "delete");
			$results[] = "File deleted";
		}
	}
	
}

if($_GET['delete_dir']) {
	
	// DELETE DIR //
	
	if(strstr($_GET['delete_dir'], "..")) die("Invalid directory");
	
	$handle = $_SERVER['DOCUMENT_ROOT'].$guidedir.$_GET['delete_dir'];
	if(!is_dir($handle)) {
		$errors[] = "Can't delete $handle since it isn't a directory";
	} else {
		$d = new RecursiveDirectoryIterator($handle);
		$tree = dirTree($d);
		while (list($k, $v) = each($tree)) {
			copy($handle."/".$k, $_SERVER['DOCUMENT_ROOT']."/bin/deleted-files/games--guides--".$gdat['title_url']."--guide--".$_GET['delete_dir']."--".$k);
			if(!unlink($handle."/".$k)) {
				$errors[] = "Couldn't delete file $handle/$k";
			} else {
				$results[] = "Deleted file $handle/$k";
				adminAction(str_replace($_SERVER['DOCUMENT_ROOT'], "", $handle)."/".$k, "delete");
			}
		}
		if(!rmdir($handle)) {
			$errors[] = "Couldn't delete dir $handle";
		} else {
			$results[] = "Dir deleted";
			adminAction(str_replace($_SERVER['DOCUMENT_ROOT'], "", $handle), "delete");
		}
	}
	
}

if($_POST['submit_features']) {
	
	// EDIT FEATURES //
	
	$q = "UPDATE games_guides SET 
		`authors` = '".implode(" ", $in[authors])."',
		`published` = '$in[published]',
		`characters` = '$in[characters]',
		`walkthrough` = '$in[walkthrough]',
		`secrets` = '$in[secrets]',
		`data` = '$in[data]',
		`equipment` = '$in[equipment]',
		`bestiary` = '$in[bestiary]' 
		WHERE gid='$id' LIMIT 1";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) {
		$errors[] = "Couldn't update features: ".mysql_error();
	} else {
		$results[] = "Features updated";
	}
	
}

if($do == "backup") {
	
	// BACKUP //
	
	require($_SERVER['DOCUMENT_ROOT']."/bin/php/class.archive.php");
	$datetime = date('Ymd_His');
	$bup = new zip_file($_SERVER['DOCUMENT_ROOT'].$guidedir."/backup-".$datetime.".zip");
	$bup->set_options(array(
		'basedir' => $_SERVER['DOCUMENT_ROOT']."/games/guides/",
		'overwrite' => 1, 
		'inmemory' => 0, 
		'recurse' => 1, 
		'storepaths' => 1));
	$bup->add_files($gdat['title_url']."/");
	$bup->exclude_files($gdat['title_url']."/*.zip"); 
	$bup->create_archive();
	if($bup->error) {
		foreach($bup->error as $err) {
			$errors[] = "Couldn't make backup: $err";
		}
	} else {
		$results[] = 'Backup file created. <a href="'.$guidedir.'backup-'.$datetime.'.zip">download the ZIP file</a>';
	}
	unset($do);

}

if($to = $_GET['upload_to']) {
	
	// UPLOAD //
	
	$subdir = $guidedir;
	
	if($_FILES) {
		$handle = new Upload($_FILES['file']);
	  if ($handle->uploaded) {
	  	$handle->Process($_SERVER['DOCUMENT_ROOT'].$subdir.$to);
	      if ($handle->processed) {
	         $results[] = 'File uploaded: <a href="'.$subdir.$to.'/'.$handle->file_dst_name.'" target="_blank">'.$handle->file_dst_name.'</a>';
	      } else {
	         $errors[] = 'file not uploaded to the wanted location: ' . $handle->error;
	      }
	  } else {
	  	$errors[] = 'file not uploaded on the server: ' . $handle->error;
	  }
	}
  
  $page->title.= " / upload to $to";
  $page->header();
  
  ?>
  <h2>Upload Stuff</h2>
  
  <input type="button" value="End upload session" onclick="document.location.href='games-mod.php?id=<?=$id?>&what=guide';"/>
  <br/><br/>
  
  <form action="games-mod.php?id=<?=$id?>&what=guide&upload_to=<?=$to?>" method="post" ENCTYPE="multipart/form-data">
		<fieldset>
			<legend>Upload a File</legend>
			Uploading to <a href="<?=$subdir.$to?>" target="_blank"><?=$subdir.$to?></a>
			<p><input type="file" name="file"/> 
		<input type="submit" name="upload" value="Upload"/></p>
		</fieldset>
	</form>
	<?
	
	$page->footer();
	exit;
	
}

if(!$do) {
	
	$page->freestyle.= "
		#help DT { margin-bottom:5px; font-size:14px; font-weight: bold; }
		#help DD { margin:0 0 5px 10px; }";
	
	$page->header();
	echo $mod_header;
	
	if(!$guidedat) {
		
		?>No game guide has been created for this game yet. 
		<input type="button" value="Create one" onclick="window.location='?what=guide&id=<?=$id?>&do=create'"/><?
		
	} else {
		
		///////////
		// INDEX //
		///////////
		
		//check files & dirs
		$req_dirs = array("", "bin/");
		$req_files = array("index.php", "menu.txt", "bin/style.css");
		$blank_menu = '<div id="game-guide-menu">
  <dl>
    <dt><a href="/games/'.$dir.'/'.$gdat[title_url].'/guide/parent-1/">Parent 1</a></dt>
      <dd><a href="/games/'.$dir.'/'.$gdat[title_url].'/guide/child-a/">Child A</a></dd>
      <dd><a href="/games/'.$dir.'/'.$gdat[title_url].'/guide/child-b/">Child B</a></dd>
  </dl>
</div>';
		foreach($req_dirs as $h) {
			if(!is_dir($_SERVER['DOCUMENT_ROOT'].$guidedir.$h)) {
				if(!mkdir($_SERVER['DOCUMENT_ROOT'].$guidedir.$h)) {
					die("Critical error: couldn't create required dir (".$guidedir.$h.")");
				}
			} elseif(!is_writeable($_SERVER['DOCUMENT_ROOT'].$guidedir.$h)) {
				if(!chmod($_SERVER['DOCUMENT_ROOT'].$guidedir.$h, 0777)) {
					die("Critical error: required dir not writable (".$guidedir.$h.")");
				}
			}
		}
		foreach($req_files as $h) {
			if(!is_file($_SERVER['DOCUMENT_ROOT'].$guidedir.$h)) {
				$handle = fopen($_SERVER['DOCUMENT_ROOT'].$guidedir.$h, "a");
				if($h == "menu.txt") {
					//menu
					if(!fwrite($handle, $blank_menu)) {
						die("Critical Error: couldn't create menu file");
					}
				} elseif(!$handle) {
					die("Critical error: couldn't create required file (".$guidedir.$h.")");
				}
			} elseif(!is_writeable($_SERVER['DOCUMENT_ROOT'].$guidedir.$h)) {
				if(!chmod($_SERVER['DOCUMENT_ROOT'].$guidedir.$h, 0777)) {
					die("Critical error: required dir not writable (".$guidedir.$h.")");
				}
			}
		}
		?>
		
		<a href="#content">Content</a> | 
		<a href="#backup">Backups</a> | 
		<a href="#menu">Menu File</a> | 
		<a href="#index">Index File</a> | 
		<a href="#features">Features & Access</a> | 
		<a href="#x" onclick="document.getElementById('help').style.display='block';">Help</a>
		
		<p>
		<fieldset id="help" style="display:none">
			<legend>Help <small>(<a href="#x" onclick="document.getElementById('help').style.display='none';">hide</a>)</small></legend>
			<dl>
				<dt><b>Getting Started</b></dt>
				
					<dd>The directory <i>bin</i> is where miscellaneous include and "helper" files are stored, not the actual game data. For example, a blank CSS (bin/style.css) is stored here and can be edited with unique styles for the guide.</dd>
					
					<dd>Your content, info, data go in their own related, appropriately named, and separate directories. For example, character lists and data can go in a directory named <i>characters</i>, while a bestiary and monster info will go into <i>bestiary</i>. Click the <i>New Directory</i> button to begin!</dd>
					
					<dd>You can store miscellaneous in <i>bin</i>, but it's recommended that you make a separate directory of upload them into their related content directory. For example, images of characters should go into either the <i>characters</i> directory or another directory named <i>images</i>.</dd>
				
				<dt>PHP variables</dt>
				
					<dd>The PHP template has several variables that you should adjust:<dd>
					
					<dd><b>$page->title</b> - The title of the page.</dd>
					
					<dd><b>$page->style[]</b> - Add a stylesheet to this page. For example, to add the default stylesheet created automatically in <i>bin</i>, link to it like so:<br/><code>$page->style[] = "../bin/style.css";</code></dd>
					
					<dd><b>$page->meta_description</b> - A description of this page for search engines and whatnot. A brief description of the information available on this particular page should go here. This is optional and the default site description will go here if left blank.</dd>
					
					<dd><b>$page->meta_keywords</b> - Keywords of this page for search engines and whatnot. This is optional and the default site keywords will go here if left blank.</dd>
					
					<dd><b>$page->gid</b> tells /bin/php/page.php which game this page relates to. It should have been automatically inserted and should be left alone.</dd>
					
				<dt>Backups</dt>
					
					<dd>Creating a backup file will zip the whole guide project up and store it here (you can download it onto your harddrive for extra safe keeping).</dd>
				
				<dt>Alternate menu display</dt>
				
					<dd>By default, the guide menu is displayed vertically, floating to the right of the content. You can alternatively display the menu along the top of the content, if for example you have a short menu with only a few items.</dd>
					
					<dd>To do this, include the following code in your menu file:</dd>
					
					<dd><code>&lt;dl class=&quot;top&quot;&gt;&lt;dt&gt;Parent&lt;/dt&gt;&lt;dd&gt;Child&lt;/dd&gt;&lt;dd&gt;Child&lt;/dd&gt;&lt;/dl&gt;</code></dd>
				
				<dt>External resources</dt>
					
					<dd>See the <a href="resources.php">External Resources page</a> for links to helpful sites for tips on HTML, CSS, etc.</dd>
				
			</dl>
		</fieldset>
		</p>
		
		<!-- CONTENT -->
		<h3 id="content" style="margin-bottom:5px">Content</h3>
		<div style="margin-bottom:5px;">
			<input id="new-dir-button" type="button" value="New Directory" onclick="document.getElementById('new-dir').style.display='block'; document.getElementById('new-dir-button').style.display='none';"/>
			<form action="?what=guide&id=<?=$id?>" method="post" id="new-dir" style="display:none">
				New directory name (use only letters, numbers, -, and _):<br/>
				<div style="margin-top:5px">
					<input type="text" name="in[dirname]"/> 
					<input type="submit" name="submit_new_dir" value="Make Directory"/> 
					<input type="button" value="Cancel" onclick="document.getElementById('new-dir').style.display='none'; document.getElementById('new-dir-button').style.display='inline';"/>
				</div>
			</form>
		</div>
		<table border="0" cellpadding="0" cellspacing="0" width="100%" class="dir-tree">
			<tr>
				<th>Directory/File</th>
				<th>Last Modified</th>
				<th>Bytes</th>
				<th style="text-align:center">New File</th>
				<th style="text-align:center">Upload</th>
				<th style="text-align:center">Edit</th>
				<th style="text-align:center">Delete</th>
			</tr><?
				
				$d = new RecursiveDirectoryIterator($_SERVER['DOCUMENT_ROOT'].$guidedir);
				$tree = dirTree($d);
				
				while (list($k, $v) = each($tree)) {
					if(is_array($v)) { //it's a dir
						
						?>
						<tr class="tree-directory">
							<td colspan="3"><a href="<?=$guidedir.$k?>/" target="_blank"><?=$k?></a></td>
							<td class="tree-action"><a href="?what=guide&id=<?=$id?>&new_file_to=<?=$k?>" title="add a file to this directory" class="tooltip"><img src="/bin/img/icons/page_white_add.png" alt="add" border="0"/></a></td>
							<td class="tree-action"><a href="?what=guide&id=<?=$id?>&upload_to=<?=$k?>" title="upload something to this directory" class="tooltip"><img src="/bin/img/icons/upload.png" alt="upload" border="0"/></a></td>
							<td class="tree-action"><a href="javascript:void(0)" onclick="if(rename_to=prompt('Rename this directory to (use only letters, numbers, -, and _):','')) window.location='?what=guide&id=<?=$id?>&rename_dir=<?=$k?>&rename_to='+rename_to;" title="rename this directory" class="tooltip"><img src="/bin/img/icons/page_white_edit.png" alt="rename" border="0"/></a></td>
							<td class="tree-action"><a href="javascript:void(0)" onclick="if(confirm('Permanently delete directory and all contents within?')) window.location='?what=guide&id=<?=$id?>&delete_dir=<?=$k?>'" title="permanently delete directory" class="tooltip"><img src="/bin/img/icons/x.gif" alt="delete" border="0"/></a></td>
						</tr><?
						
						while (list($k2, $v2) = each($v)) {
							
							$q = "SELECT * FROM admin_changelog WHERE `subject`='/games/".$dir."/".$gdat[title_url]."/guide/".$k."/".$k2."' ORDER BY datetime DESC LIMIT 1";
							if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
								$filedat = $dat->datetime.' by '.outputUser($dat->usrid, FALSE);
							}
							
							?>
							<tr class="tree-subdirectory">
								<td><a href="<?=$guidedir.$k."/".$k2?>" target="_blank"><?=$k2?></a></td>
								<td><?=$filedat?>&nbsp;</td>
								<td><?=$v2?></td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td class="tree-action"><a href="?what=guide&id=<?=$id?>&edit_file=<?=$k?>/<?=$k2?>" title="edit"><img src="/bin/img/icons/page_white_edit.png" alt="edit" border="0"/></a></td>
								<td class="tree-action"><a href="javascript:void(0)" onclick="if(confirm('Permanently delete this file?')) window.location='?what=guide&id=<?=$id?>&delete_file=<?=$k?>/<?=$k2?>'" alt="delete"><img src="/bin/img/icons/x.gif" alt="delete" border="0"/></a></td>
							</tr><?
						}
						
					} else { //it's a file
						/*?>
						<tr>
							<td colspan="2"<a href="/games/<?=$dir?>/<?=$gdat[title_url]?>/guide/<?=$k?>"><?=$k?></a></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td><?=$v?></td>
							<td><a href="?what=guide&id=<?=$id?>&edit=<?=$k?>" title="edit"><img src="/bin/img/icons/page_white_edit.png" alt="edit" border="0"/></a></td>
							<td><a href="#x" onclick="if(confirm('Permanently delete this file?')) window.location='?what=guide&id=<?=$id?>&delete_file=<?=$k?>/<?=$k2?>'" alt="delete"><img src="/bin/img/icons/x.gif" alt="delete" border="0"/></a></td>
						</tr><?*/
					}
				}

			?>
		</table>
		<p align="right">Deleted files copied to <a href="/bin/deleted-files/">/bin/deleted-files/</a></p>
		
		
		<!-- BACKUPS -->
		<h3 id="backup" style="margin-bottom:5px">Backups</h3>
		<input type="button" value="Create new backup file" onclick="window.location='?what=guide&id=<?=$id?>&do=backup'"/>
		<?
		if ($handle = opendir($_SERVER['DOCUMENT_ROOT'].$guidedir)) {
	    while (false !== ($file = readdir($handle))) {
				if(strstr($file, ".zip")) echo '<p>&bull; <a href="'.$guidedir.$file.'">'.$file.'</a></p>';
	    }
	    closedir($handle);
		}
		?>
		<br/><br/>
		
		
		<!-- MENU -->
		<form action="?what=guide&id=<?=$id?>" method="post" id="menu" onsubmit="content1.toggleEditor();">
			<input type="hidden" name="in[filename]" value="menu.txt"/>
			<fieldset>
				<legend>Menu File</legend>
				<textarea name="content" rows="15" cols="80" id="content1" class="codepress html"><?
					readfile($_SERVER['DOCUMENT_ROOT'].$guidedir."menu.txt");
					?></textarea>
				<p><input type="submit" name="edit_file" value="Submit"/></p>
			</fieldset>
		</form>
		<br/><br/>
		
		
		<!-- INDEX -->
		<form action="?what=guide&id=<?=$id?>" method="post" id="index" onsubmit="content2.toggleEditor()">
			<input type="hidden" name="in[filename]" value="index.php"/>
			<fieldset>
				<legend>Index File</legend>
				<div style="margin-bottom:5px">The file that is displayed at the front page of this guide. It should give general information about the guide and an outline or map with links to the information available.</div>
				<textarea name="content" rows="15" cols="80" id="content2" class="codepress php"><?
					readfile($_SERVER['DOCUMENT_ROOT'].$guidedir."index.php");
					?></textarea>
				<p><input type="submit" name="edit_file" value="Submit"/></p>
			</fieldset>
		</form>
		<br/><br/>
		
		<!-- FEATURES  & ACCESS-->
		<form action="?what=guide&id=<?=$id?>" method="post" id="features">
			<fieldset>
				<legend>Features & Access</legend>
				
				<fieldset style="width:50%; float:right; margin:0; border-width:0 0 0 1px;">
					<legend>Authors</legend>
					<div class="warn">Only authors have access to edit this guide.</div>
					<select name="in[authors][]" size="5" multiple="multiple" style="margin-top:5px">
						<optgroup label="Staff">
						<?
							$c_rank = "Staff";
							$query = "SELECT * FROM users LEFT JOIN users_ranks USING(rank) ORDER BY rank DESC, username";
							$res   = mysqli_query($GLOBALS['db']['link'], $query);
							while($row = mysqli_fetch_assoc($res)) {
								if($c_rank != $row[category]) {
									$c_rank = $row[category];
									echo '</optgroup><optgroup label="'.$row[category].'">';
								}
								echo '<option value="'.$row[usrid].'"'.(in_array($row[usrid], $authors) ? ' selected="selected"' : '').'>'.$row[username]."</option>\n";
							}
						?>
						</optgroup>
					</select>
				</fieldset>
				
				<label><input type="checkbox" name="in[published]" value="1"<?=($guidedat->published ? ' checked="checked"' : '')?>/> <b>Publish this guide</b> (it won't be viewable until it's published)</label><br/>
				<label><input type="checkbox" name="in[walkthrough]" value="1"<?=($guidedat->walkthrough ? ' checked="checked"' : '')?>/> Walkthrough</label><br/>
				<label><input type="checkbox" name="in[characters]" value="1"<?=($guidedat->characters ? ' checked="checked"' : '')?>/> Character Info</label><br/>
				<label><input type="checkbox" name="in[secrets]" value="1"<?=($guidedat->secrets ? ' checked="checked"' : '')?>/> Secrets & Strategies</label><br/>
				<label><input type="checkbox" name="in[data]" value="1"<?=($guidedat->data ? ' checked="checked"' : '')?>/> Game Data</label><br/>
				<label><input type="checkbox" name="in[equipment]" value="1"<?=($guidedat->equipment ? ' checked="checked"' : '')?>/> Items & Equipment</label><br/>
				<label><input type="checkbox" name="in[bestiary]" value="1"<?=($guidedat->bestiary ? ' checked="checked"' : '')?>/> Bestiary</label><br/>
				
				<p><input type="submit" name="submit_features" value="Submit"/></p>
			</fieldset>
		</form>



		<?
	
	}
	
	$page->footer();
	exit;
}

?>