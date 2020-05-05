<?
require_once ($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");
require_once ($_SERVER['DOCUMENT_ROOT']."/bin/php/class.upload.php");
require_once ($_SERVER['DOCUMENT_ROOT']."/bin/php/class.img.php");

$action = $_POST['action'];
$actionhandle = $_POST['actionhandle'];
$file = $_FILES['upl'];

if($_FILES['upl_json']){
	$file = $_FILES['upl_json'];
	$action = "submimg";
	$actionhandle = "json";
}

if($action == "submimg"){
	
	if($actionhandle == "json"){
		header("Content-type: application/json; charset=utf-8");
		$ret = array();
	}
	
	/** @var string/array A string or array with tagwords to tag this image with */
	$img_tag = $_POST['img_tag'];
	
	/** @var integer A given category id to attribute to this image */
	$img_category_id = (int) $_POST['img_category_id'];
	
	// Check if the given category id is valid
	if($img_category_id && !mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM images_categories WHERE img_category_id = '$img_category_id' LIMIT 1"))) unset($img_category_id);
	
	// evaluate $handler [sessid, usrid] and use these given vars
	// otherwise fall back on defaults
	parse_str(base64_decode($_POST['handler']), $handler);
	
	if($handler['img_tag']) $img_tag = $handler['img_tag'];
	
	if($img_name = $_POST['reupload']){
		
		$img = new img($img_name);
		if($img->notfound) NNdie("Couldn't find image data for '$img_name'");
		
		// Check and see if the user can do this
		// If the user is replacing an image uploaded by someone else, she must be trusted
		if($img->usrid != $usrid && $usrrank < User::TRUSTED) NNdie("Sorry, but you don't have access to replace this image. Only a user with \"trusted\" status can do that.");
		
		$handler['sessid'] = $img->img_session_id;
		
	}
	
	$sessid = (int) $handler['sessid'];
	
	// Wait! Why would the given usrid be different than the one in the current session...?
	// Seems to be assigned by $_POST['handler']
	/*Old Code:
	if($handler['usrid']) $usrid = $handler['usrid'];
	if(!$usrid) NNdie('no user session registered; Please log in to upload.');
	$q = "SELECT * FROM users WHERE usrid='".(int)$usrid."' LIMIT 1";
	if(!$usr = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) NNdie("Couldn't find user details");*/

	// Investigate...
	if(isset($handler['usrid']) && $handler['usrid'] != $usrid) {
		NNdie("A coding investigation is taking place... Cannot continue. [ERROR ".__FILE__." ".__LINE__."]");
	}
	
	$dir = "/images/".substr($sessid, 12, 7); // images/USRID
	if(!is_dir($_SERVER['DOCUMENT_ROOT'].$dir)){
		mkdir($_SERVER['DOCUMENT_ROOT'].$dir, 0777);
		mkdir($_SERVER['DOCUMENT_ROOT'].$dir."/op", 0777);
		mkdir($_SERVER['DOCUMENT_ROOT'].$dir."/md", 0777);
		mkdir($_SERVER['DOCUMENT_ROOT'].$dir."/sm", 0777);
		mkdir($_SERVER['DOCUMENT_ROOT'].$dir."/ss", 0777);
		mkdir($_SERVER['DOCUMENT_ROOT'].$dir."/tn", 0777);
	}
	
	if(!$file['name'] && $_POST['upl_src']) {
		$f = $_POST['upl_src'];
		if($_SERVER['HTTP_HOST'] == "videogamin.squarehaven.com") $f = str_replace("http://videogamin.squarehaven.com", $_SERVER['DOCUMENT_ROOT'], $f);
		if(substr($f, 0, 4) == "http"){
			if(!filter_var($f, FILTER_VALIDATE_URL)) NNdie("The given file URL is not valid");
			
			/*$ext = substr($imgurl, -4);
			$ext = strtolower($ext);
			$exts = array(".jpg", ".gif", ".png", "jpeg");
			if(!in_array($ext, $exts)) NNdie("The given image URL doesn\'t have a recognized extension");*/
			
			$x = explode("/", $f);
			$br = count($x) - 1;
			
			$file = $_SERVER['DOCUMENT_ROOT'] . Img::UPLOAD_TEMP_DIR . $x[$br];
			if(!copy($f, $file)) NNdie("Couldn't copy the remote file ($f) to the local server");
		} else $file = $f;
		if(!file_exists($file)) NNdie("Couldn't copy the remote file ($file) to the local server ($file) [file not found]");
		
		// Check if file is an image and get image sizes
		if(!$imagesize = getimagesize($file)){
			unlink($file);
			NNdie("Couldn't copy the remote file to the local server [file may not be an image]");
		}
		list($width, $height, $type, $attr) = $imagesize;
	} else {
		if(!$imagesize = getimagesize($file['tmp_name'])){
			unlink($file);
			NNdie("Couldn't copy the remote file to the local server [file may not be an image]");
		}
	}
	
	list($width, $height, $type, $attr) = $imagesize;
	
	$handle = new Upload($file);
	if (!$handle->uploaded) {
		NNdie($handle->error);
	} else {
		
		$mime = $handle->file_src_mime;
		$mimes = array("image/jpeg","image/jpg","image/gif","image/png","image/x-ms-bmp");
		if(!in_array($mime, $mimes)) NNdie('File is '.$mime.'; Only JPG, GIF, or PNG images can be uploaded');
	  
	  if($_POST['reupload']){
	  	
	  	$file_name = $img->img_name;
	  	$pos = strrpos($file_name, ".");
	  	$file_body = substr($file_name, 0, $pos);
	  	$file_ext = $img->img_minor_mime;
	  	
	  	//replacing the image; preserve the old image
	  	copy($_SERVER['DOCUMENT_ROOT'].$dir."/".$file_name, $_SERVER['DOCUMENT_ROOT']."/bin/deleted-files/".$file_name);
	  	
	  	//rm optimized image to prevent conflicts
	  	@unlink($_SERVER['DOCUMENT_ROOT'].$img->src['op']);
		 
		 } else {
		 	
		 	$file_ext = $handle->file_src_name_ext;
		
			//format safe name
			// *** CHANGE HTACCESS IF ANY CHANGES MADE!! *** //
			$file_body = $handle->file_src_name_body;
			$file_body = str_replace(array(' '), array('_'), $file_body);
		  $file_body = preg_replace('/[^A-Za-z0-9-_\.!]/', '', $file_body);
		  if($file_body == "") $file_body = "image".rand(1,99);
		  $file_name = $file_body.".".$handle->file_src_name_ext;
		 
			//check filename in database and avoid duplicates
		  $i = 0;
		  $t_file_body = $file_body;
		  while(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM images WHERE img_name = '".mysqli_real_escape_string($GLOBALS['db']['link'], $file_name)."' LIMIT 1"))){
		  	$i++;
		  	$t_file_body = $file_body."_".$i;
		  	$file_name = $t_file_body.".".$handle->file_src_name_ext;
		  }
		  $file_body = $t_file_body;
		  
		}
	  
		$handle->file_new_name_body = $file_body;
		$handle->file_new_name_ext  = $file_ext;
		$handle->file_safe_name     = false;
		$handle->file_auto_rename   = false;
		$handle->file_overwrite     = true;
		$handle->allowed = array('image/*');
		$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir);
		if($handle->processed){
			
			if($handle->file_dst_name != $file_name) NNdie("naming error; file not processed");
			
			//database data
			
			if($_POST['reupload']){
				
				$q = "UPDATE `images` SET `img_size` = '".$handle->file_src_size."', `img_width` = '".$handle->image_src_x."', `img_height` = '".$handle->image_src_y."', `img_bits` = '".$handle->image_src_bits."' WHERE img_id = '$img->img_id' LIMIT 1";
				if(!mysqli_query($GLOBALS['db']['link'], $q)){
					copy($_SERVER['DOCUMENT_ROOT']."/bin/deleted-files/".$file_name, $_SERVER['DOCUMENT_ROOT'].$dir."/".$file_name);
					NNdie("Database error [SD139]");
				}
				
			} else {
				
				//if the current imgs have been rearranged, make sure this one goes at the end
				$q = "SELECT `sort` FROM images WHERE img_session_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $sessid)."' ORDER BY `sort` DESC LIMIT 1";
				$last_img = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
				if($last_img->sort) $sort = $last_img->sort + 1;
				else $sort = '0';
				
				//images
				$img_id = mysqlNextAutoIncrement("images");
				$q = "INSERT INTO `images` (`img_name`, `img_session_id`, `img_size`, `img_width`, `img_height`, `img_bits`, `img_minor_mime`, `img_category_id`, `usrid`, `sort`) VALUES 
					('".mysqli_real_escape_string($GLOBALS['db']['link'], $file_name)."', '$sessid', '".$handle->file_src_size."', '".$handle->image_src_x."', '".$handle->image_src_y."', '".$handle->image_src_bits."', '".$handle->image_src_type."', '$img_category_id', '$usrid', '$sort')";
				if(!mysqli_query($GLOBALS['db']['link'], $q)) NNdie("Couldn't insert image entry into database");
				
				//given tags
				if($img_tag){
					$q = '';
					$tags = array();
					if(is_array($img_tag)) $tags = $img_tag;
					else $tags[0] = $img_tag;
					foreach($tags as $tag){
						$tag = formatName($tag);
						if($tag != '') $q.= "('$img_id', '".mysqli_real_escape_string($GLOBALS['db']['link'], $tag)."', '$usrid'),";
					}
					if($q) $q = "INSERT INTO images_tags (img_id, tag, usrid) VALUES ".substr($q, 0, -1).";";
					mysqli_query($GLOBALS['db']['link'], $q);
				}
				
				//images_sessions
				$query = "SELECT * FROM images_sessions WHERE img_session_id=? LIMIT 1";
				$statement = $GLOBALS['pdo']->prepare($query);
				$statement->execute([$sessid]);
				if (!$sessdat = $statement->fetch()) {
					$img_session_description = $img_category_id && $tags[0] ? $tags[0] : date("Y-M-d");
					$q = "INSERT INTO images_sessions (`img_session_id`, `img_session_description`, `usrid`) VALUES 
						('$sessid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $img_session_description)."', '$usrid')";
					if(!mysqli_query($GLOBALS['db']['link'], $q)) NNdie("Couldn't begin session with database");
				}
				$q = "UPDATE images_sessions SET img_qty = '".($sessdat['img_qty'] + 1)."', img_session_modified = CURRENT_TIMESTAMP() WHERE img_session_id='".$sessid."' LIMIT 1";
				mysqli_query($GLOBALS['db']['link'], $q);
				
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
	
	if($_POST['formsubm'] || $actionhandle == "quick upload") header("Location: /uploads.php?sessid=".$sessid);
	
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