<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.upload.php");

?><?=$html_tag?>
<head>
	<title>Videogam.in Sblog upload image</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<?
	if($_POST['action'] == "submimg") {
		
		if(!$sessid = $_POST['sessid']) NNdie('no session id given; Image not uploaded');
		
		$dir = "/media/".substr($sessid, 0, 4); // media/YEAR/
		if(!is_dir($_SERVER['DOCUMENT_ROOT'].$dir)) mkdir($_SERVER['DOCUMENT_ROOT'].$dir, 0777);
		$dir.= "/".substr($sessid, 4)."/"; // media/YEAR/SESSION_ID
		if(!is_dir($_SERVER['DOCUMENT_ROOT'].$dir)) mkdir($_SERVER['DOCUMENT_ROOT'].$dir, 0777);
		
		$url = trim($_POST['url']);
		if($url == "http://") unset($url);
		
		if($url) {
			
			if(!filter_var($url, FILTER_VALIDATE_URL)) NNdie('The given URL is not valid; '.$url);
			
			$ext = substr($url, -4);
			$ext = strtolower($ext);
			$exts = array(".jpg", ".gif", ".png");
			if(!in_array($ext, $exts)) NNdie('The given image URL isn\'t an acceptable file type; '.$url.' ['.$ext.']');
			
			$x = explode("/", $url);
			$br = count($x) - 1;
			
			$file = $_SERVER['DOCUMENT_ROOT']."/bin/temp/".$x[$br];
			if(!copy($url, $file)) NNdie($x[$br].' -- Couldn\'t copy the remote file to the local server');
			if(!file_exists($file)) NNdie($x[$br].' -- Couldn\'t copy the remote file to the local server (file not found)');
			
		} elseif($_FILES['upl']['name']) {
			
			$ext = substr($_FILES['upl']['name'], -3, 3);
			$exts = array("jpg","JPG","gif","GIF","png","PNG");
			if(!in_array($ext, $exts)) NNdie('upload only JPG, GIF, or PNG images');
			
			$file = $_FILES['upl'];
			
		}
			
		$q = "SELECT * FROM posts WHERE session_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $sessid)."' LIMIT 1";
		if($pdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
			$nid = $pdat->nid;
		} else {
			$q2 = "INSERT INTO posts (session_id, description, type, datetime, usrid, unpublished) VALUES 
				('".mysqli_real_escape_string($GLOBALS['db']['link'], $sessid)."', 'Misc. media', 'image', '".date("Y-m-d H:i:s")."', '$usrid', '1')";
			if(!mysqli_query($GLOBALS['db']['link'], $q2)) NNdie("Error: Couldn't begin session with posts database</body></html>");
			if(!$pdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) NNdie("Couldn't retrieve data from posts table for session ID # $sessid");
			$nid = $pdat->nid;
		}
		
		$handle = new Upload($file);
		if ($handle->uploaded) {
			$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir);
			if ($handle->processed) {
				$file = $handle->file_dst_name;
				$fileb = substr($file, 0, -4);
				$sfile = $file;
				if(strlen($sfile) > 22) {
					$sfile = substr($file, 0, 13) . '&hellip;' . substr($file, -8);
				}
				
				$imgid = mysqlNextAutoIncrement("media_files");
				$q = "INSERT INTO media_files (imgid, nid, file) VALUES ('$imgid', '$nid', '$file')";
				mysqli_query($GLOBALS['db']['link'], $q);
				
				list($width, $height, $type, $attr) = getimagesize($handle->file_dst_pathname);
				if($width > 620 || $height > 950) {
					$handle->file_new_name_body     = $fileb."-optim";
					$handle->file_safe_name         = false;
					$handle->image_convert          = 'jpg';
					$handle->file_new_name_ext      = 'jpg';
					$handle->file_overwrite         = true;
					$handle->file_auto_rename       = false;
					$handle->image_resize           = true;
					$handle->image_ratio_no_zoom_in = true;
					$handle->image_x                = 620;
					$handle->image_y                = 950;
					$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir);
				}
				if($width > 350) {
					$handle->file_new_name_body     = $fileb."-350x";
					$handle->file_safe_name         = false;
					$handle->image_convert          = 'jpg';
					$handle->file_new_name_ext      = 'jpg';
					$handle->file_overwrite         = true;
					$handle->file_auto_rename       = false;
					$handle->image_resize           = true;
					$handle->image_ratio_crop       = true;
					$handle->image_x                = 350;
					$handle->image_ratio_y          = true;
					$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir);
				}
				//screenshot tn
				$handle->image_convert          = 'png';
				$handle->file_new_name_ext      = 'png';
				$handle->file_new_name_body     = $fileb."-ss";
				$handle->file_safe_name         = false;
				$handle->file_overwrite         = true;
				$handle->file_auto_rename       = false;
				$handle->image_resize           = true;
				$handle->image_ratio_crop       = TRUE;
				$handle->image_x                = 200;
				$handle->image_y                = 130;
				$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir);
				
				//tn
				$handle->image_convert          = 'png';
				$handle->file_new_name_ext      = 'png';
				$handle->file_new_name_body     = $fileb."-tn";
				$handle->file_safe_name         = false;
				$handle->file_overwrite         = true;
				$handle->file_auto_rename       = false;
				$handle->image_resize           = true;
				$handle->image_ratio_crop       = TRUE;
				$handle->image_x                = 100;
				$handle->image_y                = 100;
				$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir);
				
				$handle->image_convert          = 'png';
				$handle->file_new_name_ext      = 'png';
				$handle->file_new_name_body     = $fileb."-50x50";
				$handle->file_safe_name         = false;
				$handle->file_overwrite         = true;
				$handle->file_auto_rename       = false;
				$handle->image_resize           = true;
				$handle->image_ratio_crop       = TRUE;
				$handle->image_x                = 50;
				$handle->image_y                = 50;
				$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir);
				
				$handle->image_convert          = 'png';
				$handle->file_new_name_ext      = 'png';
				$handle->file_new_name_body     = $fileb."-20x20";
				$handle->file_safe_name         = false;
				$handle->file_overwrite         = true;
				$handle->file_auto_rename       = false;
				$handle->image_resize           = true;
				$handle->image_ratio_crop       = TRUE;
				$handle->image_x                = 20;
				$handle->image_y                = 20;
				$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir);
				
			} else echo "Upload Error: ".$handle->error;
		} else echo "Upload Error: ".$handle->error;
		
		$capt = htmlSC($fileb);
		$capt = str_replace("_", " ", $capt);
		
		$ret='<div class="imgrecd"><a href="/posts/process.php?delete_img='.$imgid.'" target="draftspace" class="ximg" title="Delete this image" style="top:3px; right:0;" onclick="if(confirm(\'Permanently delete this image?\')){ $(this).closest(\'.imgrecd\').parent().remove(); } else { return false; };">x</a><a href="'.$dir.$file.'" target="_blank"><img src="'.$dir.$fileb.'-50x50.png" border="0" width="50" height="50" alt="thumbnail" style="float:left; margin:2px 10px 0 0;"/>'.$file.'<br/></a>Image caption: <a href="#change_caption" class="imgcapt" title="click to change this image\'s caption" onclick="$(this).hide().next().show().focus();">'.$fileb.'</a><input type="text" name="'.$imgid.'" value="'.$capt.'" size="100" maxlength="255" class="imgcapt" style="display:none; margin:0 !important; border-width:0 0 1px !important; border-color:#CCC !important;" onchange="updCaption($(this))"/></div>';
		$ret = str_replace("\n", "", $ret);
		$ret = addslashes($ret);
		
		?>
		<script type="text/javascript">
			parent.document.getElementById("<?=$_POST['parentframe']?>").innerHTML = '<?=$ret?>';
			//parent.document.getElementById("loading-<?=$_POST['parentframe']?>").style.display = 'none';
		</script>
		<?
	}
	?>
</head>
<body style="margin:0; padding:0; font:normal 13px arial;">
Sorry, an unknown error occurred
<?
//echo $ret;
/*if($imgid = $_GET['imgid']) {
	
	$q = "SELECT * FROM media_files WHERE imgid='$imgid' LIMIT 1";
	if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) {
		
		$q = "SELECT * FROM posts WHERE nid='$row[nid]' LIMIT 1";
		if($pdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
			$sessid = $pdat->session_id;
		}
		
		$mdir = "/media/".substr($sessid, 0, 4)."/".substr($sessid, 4);
		$fileb = substr($row['file'], 0, -4);
		if(!$row['caption']) $row['caption'] = substr($row['file'], 0, 79);
		?>
		<div class="imgrecd">
			<a href="/posts/process.php?delete_img=<?=$row['imgid']?>" target="draftspace" class="ximg" title="Delete this image" style="top:3px; right:0;" onclick="if(confirm('Permanently delete this image?')){ $(this).closest('.imgrecd').parent().remove(); } else { return false; };">x</a>
			<a href="<?=$mdir?>/<?=$row['file']?>" target="_blank">
				<img src="<?=$mdir?>/<?=$fileb?>-50x50.png" border="0" style="float:left; margin:2px 10px 0 0;"/>
				<?=$row['file']?><br/>
			</a>
			Image caption: <a href="#change_caption" class="imgcapt" title="click to change this image's caption" onclick="$(this).hide().next().show().focus();"><?=(strlen($row['caption']) > 30 ? substr($row['caption'], 0, 29)."&hellip;" : $row['caption'])?></a>
			<input type="text" name="<?=$row['imgid']?>" value="<?=htmlSC($row['caption'])?>" size="40" maxlength="80" class="imgcapt" style="display:none; margin:0 !important; border-width:0 0 1px !important; border-color:#CCC !important;"/>
		</div>
		<?
	}
}*/
?>
</body>
</html>
<?

function NNdie($msg) {
	global $_POST, $file;
	$msg = str_replace("\n", "", $msg);
	$msg = addslashes($msg);
	die('
	<script type="text/javascript">
		parent.document.getElementById("'.$_POST['parentframe'].'").innerHTML = \'<b style="color:#DB2424;">Error:'.($file ? ' uploading <i>'.$file.'</i>' : '').'</b> '.$msg.'\';
	</script>
	');
}