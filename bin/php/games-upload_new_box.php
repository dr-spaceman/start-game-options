<?
//upload a new publication box image to replace an old one
use Vgsite\Page;
use Verot\Upload;
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/contribute.php");

echo Page::HTML_TAG;
?>
<head>
	<title>Upload a profile pic</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<style>
		P { margin:5px 0; }
	</style>
</head>
<body style="margin:0; padding:0; background-color:#EEE; font:normal 13px arial;">
<div style="position:absolute; border:1px solid #CCC; padding:5px;">
<?
if($_FILES['file']['nameXXXXXXXXXXX']) {
	$ext = substr($_FILES['file']['name'], -3, 3);
	if($ext != "jpg" && $ext != "gif" && $ext != "png") die("Error: Please upload only images that are in JPG, GIF, or PNG format.");
	?>Preview your picture:<?
	$handle = new Upload($_FILES['file']);
	if ($handle->uploaded) {
		$handle->image_resize          = true;
		$handle->image_ratio_crop      = true;
		$handle->image_y               = 175;
		$handle->image_x               = 150;
		$handle->file_new_name_body    = 'pid_'.$_POST['pid'].'_upload_usrid_'.$usrid;
		$handle->file_new_name_ext     = 'png';
		$handle->image_convert         = 'png';
		$handle->file_overwrite        = true;
		$handle->file_auto_rename      = false;
		$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/uploads/person_pic/");
		if ($handle->processed) {
			$img = $handle->file_dst_name;
			
			//thumbnail
			$handle->image_resize          = true;
			$handle->image_ratio_crop      = true;
			$handle->image_y               = 40;
			$handle->image_x               = 40;
			$handle->file_new_name_body    = 'pid_'.$_POST['pid'].'_upload_usrid_'.$usrid.'_tn';
			$handle->file_new_name_ext     = 'png';
			$handle->image_convert         = 'png';
			$handle->file_overwrite        = true;
			$handle->file_auto_rename      = false;
			$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/uploads/person_pic/");
			if (!$handle->processed) $err = "Could not make thumbnail";
			else $tn = $handle->file_dst_name;
			
		} else $err = "Upload Error: ".$handle->error;
	} else $err = "Upload Error: ".$handle->error;
	if($err) echo $err;
	else {
		?>
		<p><img src="/bin/uploads/person_pic/<?=$img?>" style="float:left; margin-right:10px;"/><img src="/bin/uploads/person_pic/<?=$tn?>"/></p>
		<br style="clear:both"/>
		<form action="upload_profile_pic.php" method="post">
			<input type="hidden" name="pid" value="<?=$_POST['pid']?>"/>
			<input type="hidden" name="img" value="<?=$img?>"/>
			<input type="hidden" name="tn" value="<?=$tn?>"/>
			<p><input type="submit" name="submit" value="Submit this image"/> or try again</p>
		</form>
		<?
	}
} elseif($_POST['submit']) {
	
	if(!$pid = $_POST['pid']) die("Error: no person ID given");
	if(!$_POST['img'] || !file_exists($_SERVER['DOCUMENT_ROOT']."/bin/uploads/person_pic/".$_POST['img'])) die("Error: image lost during submission");
	if(!$_POST['tn'] || !file_exists($_SERVER['DOCUMENT_ROOT']."/bin/uploads/person_pic/".$_POST['tn'])) die("Error: thumbnail image lost during submission");
	
	if($_SESSION['user_rank'] >= 4) {
		if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/img/people/".$pid.".png")) {
			@rename($_SERVER['DOCUMENT_ROOT']."/bin/img/people/".$pid.".png", $_SERVER['DOCUMENT_ROOT']."/bin/deleted-files/people--".$pid."_pic_".rand(0,99999).".png");
			@unlink($_SERVER['DOCUMENT_ROOT']."/bin/img/people/".$pid."-tn.png");
		}
		if(!rename($_SERVER['DOCUMENT_ROOT']."/bin/uploads/person_pic/".$_POST['img'], $_SERVER['DOCUMENT_ROOT']."/bin/img/people/".$pid.".png")) die("Error: Couldn't move temporary image");
		if(!rename($_SERVER['DOCUMENT_ROOT']."/bin/uploads/person_pic/".$_POST['tn'], $_SERVER['DOCUMENT_ROOT']."/bin/img/people/".$pid."-tn.png")) die("Error: Couldn't move temporary thumbnail image");
		$details = '<img src="/bin/img/people/'.$pid.'.png"/>';
		echo '<b>Success!</b> Your uploaded image has been set as this person\'s profile picture.';
	} else {
		$pend_subm = $_POST['img']."|--|".$_POST['tn'];
		echo '<b>Success!</b> Your uploaded image has been sent to the editors for review.';
	}
	
	//give points ?
	$q = "SELECT * FROM users_contributions WHERE usrid='$usrid' AND type_id='15' AND supersubject='pid:$pid' AND published='1' LIMIT 1";
	if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $no_points = 1;
	
	//get desc
	$q = "SELECT name, name_url FROM people WHERE pid='$pid' LIMIT 1";
	$pdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
	
	addUserContribution(
		15, 
		'Profile picture for <a href="/people/~'.$pdat->name_url.'">'.$pdat->name.'</a>', 
		$details, 
		($usrid == 1 ? '' : '1'), 
		$pend_subm, 
		'', 
		'pid:'.$pid, 
		'', 
		$no_points
	);
	
} else {
	?><img src="/bin/img/loading-thickbox.gif" alt="loading"/><?
}
?>
</div>
</body>
</html>