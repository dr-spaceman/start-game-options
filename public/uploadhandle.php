<?php

require_once dirname(__FILE__) . '/../config/bootstrap.php';

use Vgsite\Page;
use Verot\Upload;
use Vgsite\Image;

$action = filter_input(INPUT_POST, 'action');
$actionhandle = filter_input(INPUT_POST, 'actionhandle');
$file = $_FILES['upl'];

if ($_FILES['upl_json']) {
	$file = $_FILES['upl_json'];
	$action = "submimg";
	$actionhandle = "json";
}

if ($action == "submimg") {
	if ($actionhandle == "json") {
		header("Content-type: application/json; charset=utf-8");
		$ret = array();
	}

	try {
		// Remote file (not an upload)
		if (!is_uploaded_file($file) && !empty($_POST['upl_src'])) {
			$file = filter_input(INPUT_POST, 'upl_src');
		}

		$handle = new Upload($file);
		if (!$handle->uploaded) {
			throw new UploadException($handle->error);
		}
		
		/** @var string/array A string or array with tagwords to tag this image with */
		$img_tag = filter_input(INPUT_POST, 'img_tag');
		
		/** @var integer A given category id to attribute to this image */
		$img_category_id = filter_input(INPUT_POST, 'img_category_id', FILTER_SANITIZE_NUMBER_INT);
		if ($img_category_id) {
			Image::getCategoryName($img_category_id); //Checks and throws error if not valid
		}
		
		// evaluate $handler [session_id, user_id] and use these given vars
		// otherwise fall back on defaults
		parse_str(base64_decode($_POST['handler']), $handler);

		// Rule out old form submission methods
		if (isset($handler['usrid'])) {
			throw new UploadException('Use of old form not permitted');
		}
		
		if ($handler['img_tag']) {
			$img_tag = $handler['img_tag'];
		}

		$session_id = (int)$handler['session_id'] ?: null;
		
		if ($img_name = filter_input(INPUT_POST, 'reupload')) {
			$is_reupload = true;

			$image_old = Image::getByName($img_name);
			if (is_null($image_old)) {
				throw new UploadException(sprintf('Could not find image data for "%s"', $img_name));
			}
			
			// Check and see if the user can do this
			// If the user is replacing an image uploaded by someone else, she must be trusted
			if ($image_old->user_id != $_SESSION['user_id'] && $_SESSION['user_rank'] < User::TRUSTED) {
				throw new UploadException('You do not have access to replace this image');
			}
			
			$session_id = (int)$handler['session_id'] ?: $image_old->img_session_id;
		}

		// Mapper and Collection objects
		$image_mapper = new ImageMapper();
		if (!empty($session_id)) {
			$image_collection = $image_mapper->findAllBySessionId($session_id);
			if (is_null($image_collection)) {
				throw new UploadException('Image session not found ['.$session_id.']');
			}
		} else {
			$image_collection = new ImageCollection();
		}

		// Wait! Why would the given user_id be different than the one in the current session...?
		// Seems to be assigned by $_POST['handler']
		/*Old Code:
		if($handler['user_id']) $user_id = $handler['user_id'];
		if(!$user_id) NNdie('no user session registered; Please log in to upload.');
		$q = "SELECT * FROM users WHERE user_id='".(int)$user_id."' LIMIT 1";
		if(!$usr = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) NNdie("Couldn't find user details");*/

		// Investigate...
		if (isset($handler['user_id']) && $handler['user_id'] != $user_id) {
			throw new UploadException("A coding investigation is taking place... Cannot continue. [ERROR ".__FILE__." ".__LINE__."]");
		}

		if ($is_reupload) {
		  	$file_name = $image_old->img_name;
		  	$file_body = substr($file_name, 0, strrpos($file_name, "."));
		  	$file_ext = $image_old->img_minor_mime;
		  	
		  	// Preserve the old image
		  	copy($image_old->src['original'], Image::DELETED_FILES_DIR.'/'.$file_name);
		  	
		  	// Delete optimized image to prevent conflicts
		  	unlink($image_old->src['original']);
		}
	  
		$handle->prepare($image_collection->getId(), $img_category_id, $current_user);

		if ($is_reupload) {
			$image_mapper->save($handle);
		} else {
			$image_mapper->insert($handle);

			$image_collection->save();
			
			//given tags
			if ($img_tag) {
				$tags = array();
				if (is_array($img_tag)) $tags = $img_tag;
				else $tags[0] = $img_tag;
				foreach($tags as $tag){
					$image_mapper->insertTag($tag, $handle, $current_user);
				}
			}			
		}
			
			if($width > 620){
				$handle->file_new_name_body     = $file_body;
				$handle->file_new_name_ext      = $file_ext;
				$handle->file_safe_name         = false;
				$handle->file_auto_rename       = false;
				$handle->file_overwrite         = true;
				$handle->image_resize           = true;
				$handle->image_ratio_no_zoom_in = true;
				$handle->image_x                = 620;
				$handle->image_ratio_y          = true;
				$handle->jpeg_quality           = 95;
				$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir."/op");
				$optimized = true;
			}
			if($width > 350) {
				$handle->file_new_name_body     = $file_body;
				$handle->file_new_name_ext      = $file_ext;
				$handle->file_safe_name         = false;
				$handle->file_auto_rename       = false;
				$handle->file_overwrite         = true;
				$handle->image_resize           = true;
				$handle->image_ratio_crop       = true;
				$handle->image_x                = 350;
				$handle->image_ratio_y          = true;
				$handle->jpeg_quality           = 95;
				$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir."/md");
			}
			
			//small
			$handle->file_new_name_body     = $file_body;
			$handle->file_new_name_ext      = $file_ext;
			$handle->file_safe_name         = false;
			$handle->file_auto_rename       = false;
			$handle->file_overwrite         = true;
			$handle->image_resize           = true;
			$handle->image_ratio            = true;
			$handle->image_x                = 240;
			$handle->image_y                = 240;
			$handle->jpeg_quality           = 95;
			$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir."/sm");
			
			//box art
			$handle->file_new_name_body     = $file_name;
			$handle->image_convert          = 'png';
			$handle->file_new_name_ext      = 'png';
			$handle->file_safe_name         = false;
			$handle->file_auto_rename       = false;
			$handle->file_overwrite         = true;
			$handle->image_resize           = true;
			$handle->image_ratio_crop       = true;
			$handle->image_x                = 140;
			$handle->image_ratio_y          = true;
			$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir."/box");
			
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
			$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir."/ss");
			
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
			$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir."/tn");
			if(!$handle->processed) NNdie($handle->error);
			
		} else NNdie($handle->error);
	}
	
	if($_POST['formsubm'] || $actionhandle == "quick upload") header("Location: /uploads.php?session_id=".$session_id);
	
	if($actionhandle == "json"){
		$ret['img_name'] = $file_name;
		$ret['src'] = $dir."/".$file_name;
		$ret['src_op'] = $optimized ? $dir."/op/".$file_name : $ret['src'];
		$ret['src_sm'] = $dir."/sm/".$file_name;
		$ret['src_box'] = $dir."/box/".$file_name.".png";
		$ret['src_tn'] = $dir."/tn/".$file_name.".png";
		die(json_encode($ret));
	}
	
	if($actionhandle == "boxartuploader_static"){
		$i = str_replace("boximg-", "", $_POST['retelid']);
		echo $GLOBALS['html_tag'];
		?>
		<body onload="parent.changePubImg('<?=$i?>', '<?=$file_name?>', '<?=($dir."/box/".$file_name.".png")?>')">Finished.</body>
		</html>
		<?
		exit;
	}
	
	die("ok");
	
}

function NNdie($msg) {
	global $_POST, $file;
	$msg = str_replace("\n", "", $msg);
	$msg = ($file ? '<b>'.$file['name'].'</b>: ' : '').addslashes($msg);
	
	if($actionhandle = "json"){
		$ret['error'] = $msg;
		die(json_encode($ret));
	}
	
	die($msg);
}