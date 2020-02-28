<?
require($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
include($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.upload.php");

if($_GET['action'] == 'delete_av') {
	if(!unlink($_SERVER['DOCUMENT_ROOT']."/bin/img/avatars/custom/".$usrid.".png")) die("Error: couldn't delete full-sized image.");
	if(!unlink($_SERVER['DOCUMENT_ROOT']."/bin/img/avatars/tn/custom/".$usrid.".png")) die("Error: couldn't delete thumbnail.");
	@unlink($_SERVER['DOCUMENT_ROOT']."/bin/img/avatars/sm/custom/".$usrid.".png");
	$q = "UPDATE users SET avatar='' WHERE usrid='$usrid' LIMIT 1";
	mysqli_query($GLOBALS['db']['link'], $q);
}

if($_FILES['file']) {
	$dothumb = $_POST['dothumb'];
	if($_FILES['file']['name']){
		$handle = new Upload($_FILES['file']);
	  if($handle->uploaded) {
	  	
	  	$handle->file_auto_rename      = false;
	  	$handle->file_overwrite        = true;
			$handle->file_new_name_body    = $usrid;
			$handle->file_safe_name        = false;
	  	$handle->image_convert         = 'png';
	  	$handle->file_new_name_ext     = 'png';
			$handle->image_resize          = true;
			$handle->image_ratio_crop      = true;
			$handle->image_y               = 150;
			$handle->image_x               = 135;
	  	$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/img/avatars/custom/");
	    
	    if ($handle->processed) {
	  		$handle->file_auto_rename      = false;
	  		$handle->file_overwrite        = true;
				$handle->file_new_name_body    = $usrid;
				$handle->file_safe_name        = false;
				$handle->image_convert         = 'png';
	  		$handle->file_new_name_ext     = 'png';
				$handle->image_resize          = true;
				$handle->image_ratio_crop      = true;
				$handle->image_y               = 20;
				$handle->image_x               = 20;
				$handle->image_watermark_y     = 0;
				$handle->image_watermark_x     = 0;
				$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/img/avatars/tn/custom/");
				
				if ($handle->processed) $tn = $handle->file_dst_name;
				else $errors[] = 'Thumbnail couldn\'t be created: ' . $handle->error;
				
				$q = "UPDATE users SET avatar='custom/".$usrid.".png' WHERE usrid='$usrid' LIMIT 1";
				mysqli_query($GLOBALS['db']['link'], $q);
				
			} else $errors[] = 'Couldn\t upload image: ' . $handle->error;
		} else {
			$errors[] = 'file not uploaded to the wanted location: ' . $handle->error;
		}
	}
	
	if($_FILES['thumb']['name'] && $dothumb == "upload"){
		$handle = new Upload($_FILES['thumb']);
		if($handle->uploaded) {
			$handle->file_auto_rename      = false;
  		$handle->file_overwrite        = true;
			$handle->file_new_name_body    = $usrid;
			$handle->file_safe_name        = false;
			$handle->image_convert         = 'png';
  		$handle->file_new_name_ext     = 'png';
			$handle->image_resize          = true;
			$handle->image_ratio_crop      = true;
			$handle->image_y               = 20;
			$handle->image_x               = 20;
			$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/img/avatars/tn/custom/");
			if ($handle->processed) $tn = $handle->file_dst_name;
			else $errors[] = 'Thumbnail couldn\'t be created: ' . $handle->error;
		} else $errors[] = 'file not uploaded to the wanted location: ' . $handle->error;
	}
}

?>
<html>
<head>
<style>
BODY, INPUT, TD, TH { font:normal 13px arial; }
BODY {	margin:0; padding:0; }
FORM {	margin:0; padding:0; }
TD, TH { vertical-align:top; text-align:left; padding:5px; }
TD { background-color:#F5F5F5; }
TH { background-color:#EEE; border-top:1px solid #CCC; }
A { color: blue; }
P { margin: 3px 0 0 0; }
</style>
</head>
<body>

<?
if($errors) {
	?><ul><?
	foreach($errors as $e) {
		?><li>Error:<?=$e?></li><?
	}
	?></ul><?
}
?>

<form action="custom-avatar.php" method="post" enctype="multipart/form-data">
	
	<div style="margin:0 0 10px;"><?
	if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/img/avatars/custom/".$usrid.".png")) $has_fs = TRUE;
	if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/img/avatars/tn/custom/".$usrid.".png")) $has_tn = TRUE;
	if($tn) echo '<div style="padding:6px 10px; background-color:#FFFFBF; margin-bottom:10px; font-size:15px;"><b>Upload success!</b> Your new custom avatar has been set.<br/><a href="javascript:void(0)" onclick="window.location.reload();return false;">Reload this frame</a> if the new images aren\'t appering.</div>';
	elseif($has_fs) echo '<div style="margin-bottom:5px">Your uploaded avatar has been selected for use</div>';
	if($has_fs || $has_tn) echo '<input type="button" value="Delete your current uploads" onclick="document.location=\'?action=delete_av\';"/>';
	else echo 'You have no custom avatars uploaded.';
	?></div>
	
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<th colspan="2"><b>New Upload</b></th>
		</tr>
		<tr>
			<td colspan="2"><input type="file" name="file" size="17"/></td>
		</tr>
		<tr>
			<th><b>Full-Sized Avatar</b> (135 x 150)</th>
			<th><b>Thumbnail</b> (20 x 20)</th>
		</tr>
		<tr>
			<td><img src="/bin/img/avatars/<?=($has_fs ? 'custom/'.$usrid.'.png' : 'unknown.png')?>" alt="your full-sized upload"/></td>
			<td>
				<img src="/bin/img/avatars/<?=($has_tn ? 'tn/custom/'.$usrid.'.png' : 'tn/unknown.png')?>" alt="your thumbnail upload" style="float:left; padding-top:4px;"/>
				<div style="margin-left:25px">
					<label><input type="radio" name="dothumb" value="use_source" checked="checked" onclick="document.getElementById('upload-thumb').style.display='none';"/> Resize the full-sized source file</label>
					<p><label><input type="radio" name="dothumb" value="upload" onclick="document.getElementById('upload-thumb').style.display='block';"/> Upload a new image</label></p>
					<p id="upload-thumb" style="display:none"><input type="file" name="thumb" size="15"/></p>
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="background-color:#EEE;"><input type="submit" value="Submit Uploads" style="font-weight:bold"/></td>
		</tr>
	</table>

</form>

</body>
</html>
<?