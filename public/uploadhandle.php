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
		
		// evaluate $handler [session_id] and use these given vars
		// otherwise fall back on defaults
		parse_str(base64_decode($_POST['handler']), $handler);
		
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

		// Investigate...
		if (isset($handler['user_id']) && $handler['user_id'] != $_SESSION['user_id']) {
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
			$image_mapper->save($handle->image);
		} else {
			$image_mapper->insert($handle->image);
			$image_mapper->saveSession($image_collection);
			
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
	} catch (UploadException | Exception $e) {
		if ($actionhandle = "json") {
			$ret['error'] = $e->getMessage();
			die(json_encode($ret));
		}

		header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request", true, 400);
		echo $e->getMessage();
		exit;
	}

	// Everything OK
	
	header($_SERVER['SERVER_PROTOCOL'].' 200 OK', true, 200);
	
	if ($_POST['formsubm'] || $actionhandle == "quick upload") {
		header("Location: /uploads.php?session_id=".$session_id);
	}
	
	if ($actionhandle == "json") {
		$ret['img_name'] = $file_name;
		$ret['src'] = $handle->image->getSrc();
		$ret['src_op'] = $handle->image->getSrc(Image::OPTIMAL);
		$ret['src_sm'] = $handle->image->getSrc(Image::SMALL);
		$ret['src_box'] = $handle->image->getSrc(Image::BOX);
		$ret['src_tn'] = $handle->image->getSrc(Image::THUMBNAIL);
		
		echo json_encode($ret);
		exit;
	}
	
	if ($actionhandle == "boxartuploader_static") {
		$i = str_replace("boximg-", "", $_POST['retelid']);
		?><html>
		<body onload="parent.changePubImg('<? echo $i; ?>', '<? echo $file_name; ?>', '<? echo $handle->image->getSrc(Image::BOX); ?>')">Finished.</body>
		</html>
		<?
		exit;
	}
	
	die("ok");
}