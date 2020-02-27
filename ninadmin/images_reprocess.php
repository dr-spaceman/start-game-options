<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
require_once ($_SERVER['DOCUMENT_ROOT']."/bin/php/class.upload.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.posts.php");
$_posts = new posts;
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.img.php");
$_imgs = new imgs;

$lm = trim($_GET['lm']);
$lm = (integer)$lm;
if(!$lm) $lm = 0;

echo $html_tag.'<body>Init '.$lm.'... <a href="?lm='.($lm + 4).'">Next</a><dl>';

$q = "SELECT * FROM images LIMIT $lm, 4";
$r = mysql_query($q);
if(!mysql_num_rows($r)) die('</dl><br/>End.</body></html>');
while($image = mysql_fetch_assoc($r)){
	
	$dir = "/images/".substr($image[img_session_id], 12, 7)."/";
	$file = $dir.$image['img_name'];
	
	echo '<dt>Processing <a href="'.$file.'" target="_blank">'.$file.'</a>...</dt>';
	
	$handle = new Upload($_SERVER['DOCUMENT_ROOT'].$file);
	if($handle->uploaded){
	  
	  $file_body = $handle->file_src_name_body;
	  $file_name = $image['img_name'];
	  
	  $handle->file_new_name_body = $image['img_name'];
		$handle->file_safe_name     = false;
		$handle->file_auto_rename   = false;
		$handle->file_overwrite     = true;

			list($width, $height, $type, $attr) = getimagesize($_SERVER['DOCUMENT_ROOT'].$file);
			if($width > 620 || $height > 900) {
				$handle->file_new_name_body     = $file_body;
				$handle->file_safe_name         = false;
				$handle->file_auto_rename       = false;
				$handle->file_overwrite         = true;
				$handle->image_resize           = true;
				$handle->image_ratio_no_zoom_in = true;
				$handle->image_x                = 620;
				$handle->image_y                = 900;
				$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir."op");
			}
			if($width > 350) {
				$handle->file_new_name_body     = $file_body;
				$handle->file_safe_name         = false;
				$handle->file_auto_rename       = false;
				$handle->file_overwrite         = true;
				$handle->image_resize           = true;
				$handle->image_ratio_crop       = true;
				$handle->image_x                = 350;
				$handle->image_ratio_y          = true;
				$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir."md");
			}
			
			//small
			$handle->file_new_name_body     = $file_body;
			$handle->file_safe_name         = false;
			$handle->file_auto_rename       = false;
			$handle->file_overwrite         = true;
			$handle->image_resize           = true;
			$handle->image_ratio            = true;
			$handle->image_x                = 240;
			$handle->image_y                = 240;
			$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir."sm");
			
			//screenshot
			$handle->file_new_name_body     = $file_name;
			$handle->image_convert          = 'png';
			$handle->file_new_name_ext      = 'png';
			$handle->file_safe_name         = false;
			$handle->file_auto_rename       = false;
			$handle->file_overwrite         = true;
			$handle->image_resize           = true;
			$handle->image_ratio_crop       = "T";
			$handle->image_x                = 200;
			$handle->image_y                = 130;
			$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir."ss");
			
			//tn
			$handle->file_new_name_body     = $file_name;
			$handle->image_convert          = 'png';
			$handle->file_new_name_ext      = 'png';
			$handle->file_safe_name         = false;
			$handle->file_auto_rename       = false;
			$handle->file_overwrite         = true;
			$handle->image_resize           = true;
			$handle->image_ratio_crop       = "T";
			$handle->image_x                = 100;
			$handle->image_y                = 100;
			$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir."tn");
			
			echo '<dd><a href="/image.php?img_name='.$file_name.'" target="_blank">Processed</a></dd>';
			echo '<dd><img src="'.$dir.'/tn/'.$file_name.'.png"/></dd>';
		
	} else { $error = 1; echo('<dd><b>Error</b> '.$handle->error.'</dd>'); }
	
}

if(!$error) echo '<script type="text/javascript"> window.location="images_reprocess.php?lm='.($lm + 4).'"; </script>';

?>
</body></html>