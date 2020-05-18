<?

// Upload and process a heading image

use Vgsite\Page;
use Verot\Upload;

?><?=Page::HTML_TAG?>
<head>
	<title>Videogam.in Sblog upload heading image</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<script type="text/javascript" src="/bin/script/jquery-1.4.2.js"></script>
	<?
	if($_POST) {
		
		if(!$sessid = $_POST['sessid']) NNdie('no session id given; Image not uploaded');
		
		$dir = "/posts/img/";
		
		$url = trim($_POST['url']);
		if($url == "http://") unset($url);
		
		if($url) {
			
			if(!filter_var($url, FILTER_VALIDATE_URL)) NNdie('The given URL is not valid; '.$url);
			
			$ext = substr($url, -4);
			$ext = strtolower($ext);
			$exts = array(".jpg", ".gif", ".png");
			if(!in_array($ext, $exts)) NNdie('The given image URL isn\'t an acceptable file type ['.$url.'] ['.$ext.']');
			
			$x = explode("/", $url);
			$br = count($x) - 1;
			
			$file = $_SERVER['DOCUMENT_ROOT']."/bin/temp/".$x[$br];
			if(!copy($url, $file)) NNdie($x[$br].' -- Couldn\'t copy the remote file to the local server');
			if(!file_exists($file)) NNdie($x[$br].' -- Couldn\'t copy the remote file to the local server (file not found)');
			
		} elseif($_FILES['upl']['name']) {
			
			$ext = substr($_FILES['upl']['name'], -3, 3);
			$exts = array("jpg","gif","png");
			if(!in_array(strtolower($ext), $exts)) NNdie('upload only JPG, GIF, or PNG images');
			
			$file = $_FILES['upl'];
			
		}
		
		$handle = new Upload($file);
		if($handle->uploaded) {
			
			$ls = "";
			if($handle->image_src_x >= 620) {
				$handle->file_name_body_pre     = "ls_";
				$handle->image_resize           = true;
				$handle->image_ratio_crop       = TRUE;
				$handle->image_ratio_no_zoom_in = true;
				$handle->image_x                = 620;
				$handle->image_y                = 250;
				$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir);
				if(!$handle->processed) NNdie("Couldn't upload landscape image");
				$ls = $handle->file_dst_name;
			}
			
			$handle->file_name_body_pre     = "tn_";
			$handle->image_convert          = "png";
			$handle->file_new_name_ext      = "png";
			$handle->image_resize           = true;
			$handle->image_ratio_crop       = true;
			$handle->image_min_width        = 140;
			$handle->image_min_height       = 91;
			$handle->image_x                = 140;
			$handle->image_y                = 91;
			$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir);
			if(!$handle->processed) NNdie("Your image is a bit too small. Please upload an image that is at least 140 x 91 pixels.");
			$tn = $handle->file_dst_name;
			
		} else NNdie("Couldn't upload heading image [2]");
		
		?>
		<script type="text/javascript">
			<?
			if(!$err){
				?>
				$('#imgdisp', window.parent.document).show().prev().show();
				$('#inp-img', window.parent.document).val('').next().addClass('on').siblings().removeClass('on');
				$('#imgdisptn', window.parent.document).attr("src", "<?=$dir.$tn?>");
				<?=($ls ? "$('#imgdispls', window.parent.document).attr('src', '".$dir.$ls."').parent().show();" : "$('#imgdispls', window.parent.document).parent().hide();")?>
				<?
			}
			?>
			
			$("#uplhimgform", window.parent.document).slideToggle();
			$("#uplhimgform :input", window.parent.document).removeAttr("disabled");
			$("#uplhimgform .inphimgfile", window.parent.document).val('');
			$("#uplhimgform .loading", window.parent.document).hide();
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
			<a href="/posts/process.php?delete_img=<?=$row['imgid']?>" target="draftspace" class="ximg" title="Delete this image" style="top:3px; right:0;" onclick="if(confirm('Permanently delete this image?')){ $(this).closest('.imgrecd').remove(); } else { return false; };">x</a>
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
	$msg = str_replace("\n", "", $msg);
	$msg = addslashes($msg);
	?><script type="text/javascript">alert('<?=$msg?>');</script><?
	$GLOBALS['err'] = 1;
}