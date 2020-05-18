<?
use Vgsite\Page;

$in = $_POST['in'];

$page = new Page();
$page->title = "Nintendosite Admin / Your Drafts & Documents";
$page->admin = TRUE;

$dir = $_SERVER['DOCUMENT_ROOT']."/ninadmin/drafts/$usrid/";

// NEW //
if($_POST['new_draft']) {
	$filename = strtolower($_POST['filename']);
	if(!$_POST['dont_echo']) $filename.=".txt";
	$filename = preg_replace("/[^a-zA-Z0-9_\.-]/", "", $filename);
	$words = str_replace("[AMP]", "&", $_POST['words']);
	//dir ok?
	if(!is_dir($dir)) mkdir($dir, 0777);
	if(!is_writeable($dir)) chmod($dir, 0777);
	if(file_exists($dir.$filename)) {
		$ext = substr(strrchr($filename, "."), 1);
		$filebody = str_replace(".".$ext, "", $filename);
		$i = 1;
		while(file_exists($dir.$filebody."-".$i.".".$ext)) {
			$i++;
		}
		$filename = $filebody."-".$i.".".$ext;
	}
	if($handle = fopen($dir.$filename, 'w')) {
	  if(fwrite($handle, $words) === FALSE) {
	  	//error
	  } else {
	  	if($_POST['dont_echo']) $results[] = "File successfully created";
	  	else echo "Success, wrote <p>".$words."</p> to file (".$dir.$filename.")";
		}
	}
	fclose($handle);
	if(!$_POST['dont_echo']) exit;
}

// DELETE //
if($_POST['submit_deletes'] && $delete = $_POST['delete']) {
	foreach($delete as $del) {
		if(unlink($dir.$del)) $results[] = "Deleted $del";
		else $errors[] = "Couldn't delete $del";
	}
}

// SUBMIT EDIT FILE //
if($_POST['edit_file']) {
	
	if(!$in['filename']) die("no filename specified");
	if(!$in['content']) die("content is blank");
	
	if($_POST['rename_file_to']) {
		$new = $_POST['rename_file_to'];
		$new = preg_replace("/[^a-zA-Z0-9_\.-]/", "", $new);
		
		if(!rename($dir.$in['filename'], $dir.$new)) {
			$errors[] = "Couldn't rename '$in[filename]' to '$new'";
		} else {
			$results[] = "Renamed '$in[filename]' to '$new'";
			$in['filename'] = $new; //change file subject
		}
	}
	
	$handle = fopen($dir.$in[filename], "w");
	if(!fwrite($handle, $in['content'])) {
		$errors[] = "File couldn't be written to";
	} else {
		$results[] = "File successfully edited";
	}
	
	if($_POST['edit_file'] != "Save") { // continue editing
		$_GET['edit_file'] = $in['filename'];
	}
}

// EDIT FILE FORM //
if($_GET['edit_file']) {
	
	$page->header();
	
	$file = $dir.$_GET['edit_file'];
	if(!is_file($file)) die("File is not editable or doesn't exist ($file)");
	?>
	<h2>Edit Document</h2>
	
	<form action="drafts.php" method="post" onsubmit="content.toggleEditor('off')">
		<input type="hidden" name="in[filename]" value="<?=$_GET['edit_file']?>"/>
		
		Editing <a href="<?=str_replace($_SERVER['DOCUMENT_ROOT'], "", $file)?>" target="_blank"><?=$_GET['edit_file']?></a> 
		<input type="button" value="Rename file" onclick="document.getElementById('rename-container').style.display='inline'; document.getElementById('rename-file-to').disabled=false; this.style.display='none';"/> 
		<label id="rename-container" style="display:none">
			Rename file to: <input type="text" name="rename_file_to" id="rename-file-to" value="<?=$_GET['edit_file']?>" disabled="disabled"/>
		</label>
		<br/><br/>
		
		<input type="button" value="Toggle HTML color code editor" onclick="content.toggleEditor()"/>
		<p><textarea name="in[content]" rows="25" cols="82" id="content" class="codepress php"><?
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

$page->freestyle.= 'TR.selected TD { background-color:#F3CFCF !important; }';

$page->header();

?>

<h2>Drafts & Documents</h2>

These are your personal, private text documents that you can access at any time. 
Save backups of your written work here automatically wherever you see the draft icon (<img src="/bin/img/icons/folder_add.png" alt="drafts"/>).
<br/><br/>

<form action="drafts.php" method="post">
<fieldset style="padding:0; background-color:#EEE;">
	<legend style="margin-left:5px">Your Drafts & Documents</legend>
<?
//get files
if ($handle = opendir($dir)) {
	while(false !== ($file = readdir($handle))) {
		if($file != '.' && $file != '..') {
			$fstat = fstat(fopen($dir.$file, "r"));
			$files[$file] = $fstat['ctime'];
			$filesizes[$file] = $fstat['size'];
		}
	}
}
closedir($handle);
if($files) {
?>
	<table border="0" cellpadding="5" cellspacing="0" width="100%" style="margin:6px 0 0 0;">
		<tr>
			<th style="border-bottom:1px solid #808080;"><a href="?sort=filename" class="arrow-down">File</a></th>
			<th style="border-bottom:1px solid #808080;">Size</th>
			<th style="border-bottom:1px solid #808080;"><a href="?sort=changedate" class="arrow-down">Last Changed</a></th>
			<th style="border-bottom:1px solid #808080;">Edit</th>
			<th style="border-bottom:1px solid #808080;">Delete</th>
		</tr><?
		if(!$sort = $_GET['sort']) $sort = "filename";
		if($sort == "filename") ksort($files);
		elseif($sort == "changedate") asort($files);
		$i = 0;
		while(list($file, $tm) = each($files)) {
			$i++
			?>
			<tr id="row-<?=$i?>">
				<td style="border-top:1px solid #808080; background-color:white;"><a href="drafts/<?=$usrid?>/<?=$file?>" target="_blank"><?=$file?></a></td>
				<td style="border-top:1px solid #808080; background-color:white;"><?=$filesizes[$file]?> bytes</td>
				<td style="border-top:1px solid #808080; background-color:white;"><?=date("M d Y H:i:s", $tm)?></td>
				<td style="border-top:1px solid #808080; background-color:white;"><input type="button" value="Edit" onclick="document.location='drafts.php?edit_file=<?=htmlspecialchars($file)?>';"/></td>
				<td style="border-top:1px solid #808080; background-color:white;"><label><input type="checkbox" name="delete[]" value="<?=$file?>" onclick="if(this.checked == true) document.getElementById('row-<?=$i?>').className='selected'; else document.getElementById('row-<?=$i?>').className='';"/> delete</label></td>
			</tr><?
		}
		?>
		<tr>
			<td colspan="5" style="text-align:right; border-top:1px solid #808080; background-color:white;"><input type="submit" name="submit_deletes" value="Delete Selected"/></td>
		</tr>
	</table>
<?
} else echo '<div style="padding:10px">No drafts saved</div>';
?>
</fieldset>
</form>
<br/><br/>

<form action="drafts.php" method="post" id="newdocform" onsubmit="content.toggleEditor();">
	<input type="hidden" name="dont_echo" value="1"/>
	<fieldset>
		<legend>New Document</legend>
		File name: <input type="text" name="filename" value="untitled_<?=date("Y-m-d_His")?>.txt" size="35"/>
		<p><textarea name="words" rows="15" cols="80" id="content" class="codepress html"></textarea></p>
		<p><input type="submit" name="new_draft" value="Submit"/></p>
	</fieldset>
</form>

<?
$page->footer();
?>