<?
function EPprocessimg($file, $fname='') {
	
	//process an image and make thumbnails
	//@param $file the uploaded file
	//@param $fnam the wanted filename (defaults to uploaded filename if blank)
	//@return array ( processed filename , array(errors) )
	
	global $in, $title_url, $filedir;
	
	$errs = array();
	
	if(!$fname) $fname = substr($file['name'], 0, -4);
	
	//check mime and width
	//$upl   = @GetImageSize($file['tmp_name']);
	//$mimes = array("image/jpg", "image/jpeg", "image/png", "image/gif");
	//if(!in_array($upl['mime'], $mimes)) return(array("", array($file['name'].": Invalid filetype selected for image upload (".$upl['mime']."); Only upload images that are JPG, GIF, or PNG.")));
	//$width = $upl[0];
	//if($width < 150) return(array("", array($file['name'].": Image selected for upload is less than 150 pixels in width. If your upload isn't a quality image larger than 150 pixels in width it's probably not worth uploading!")));
	
	$handle = new Upload($file);
  if ($handle->uploaded) {
  	if($handle->file_src_name_ext == "jpeg"){ $handle->image_convert = "jpg"; $handle->file_new_name_ext = "jpg"; }
  	$handle->file_new_name_body     = $fname;
		$handle->image_resize           = true;
		$handle->image_ratio_no_zoom_in = true;
		$handle->image_x                = 800;
		$handle->image_y                = 1000;
  	$handle->Process($_SERVER['DOCUMENT_ROOT'].$filedir);
		if ($handle->processed) {
			
			$final_file = $filedir.$handle->file_dst_name;
			$fname = $handle->file_dst_name_body;
			
			//med img
			$handle->file_new_name_body    = "md_".$fname;
			$handle->image_convert         = 'png';
			$handle->file_new_name_ext     = 'png';
			$handle->image_resize          = true;
			$handle->image_ratio_y         = true;
			$handle->image_x               = 200;
			$handle->Process($_SERVER['DOCUMENT_ROOT'].$filedir);
			if(!$handle->processed) $errs[] = $file['name'].': Small image; ' . $handle->error;
			
			//small img
			$handle->file_new_name_body    = "sm_".$fname;
			$handle->image_convert         = 'png';
			$handle->file_new_name_ext     = 'png';
			$handle->image_resize          = true;
			$handle->image_ratio_y         = true;
			$handle->image_x               = 140;
			$handle->Process($_SERVER['DOCUMENT_ROOT'].$filedir);
			if(!$handle->processed) $errs[] = $file['name'].': Small image; ' . $handle->error;
			
			//thumbnail
			$handle->file_new_name_body    = "tn_".$fname;
			$handle->image_convert         = 'png';
			$handle->file_new_name_ext     = 'png';
			$handle->image_resize          = true;
			$handle->image_ratio_y         = true;
			$handle->image_x               = 80;
			$handle->Process($_SERVER['DOCUMENT_ROOT'].$filedir);
			if (!$handle->processed) $errs[] = $file['name'].': Thumbnail image; ' . $handle->error;
			
			//icons
			$handle->file_new_name_body    = "icon50_".$fname;
			$handle->image_convert         = 'png';
			$handle->file_new_name_ext     = 'png';
			$handle->image_resize          = true;
			$handle->image_ratio_crop      = "TL";
			$handle->image_x               = 50;
			$handle->image_y               = 50;
			$handle->Process($_SERVER['DOCUMENT_ROOT'].$filedir);
			if (!$handle->processed) $errs[] = $file['name'].': Icon_50 image; ' . $handle->error;
			
			$handle->file_new_name_body    = "icon100_".$fname;
			$handle->image_convert         = 'png';
			$handle->file_new_name_ext     = 'png';
			$handle->image_resize          = true;
			$handle->image_ratio_crop      = "TL";
			$handle->image_x               = 100;
			$handle->image_y               = 100;
			$handle->Process($_SERVER['DOCUMENT_ROOT'].$filedir);
			if (!$handle->processed) $errs[] = $file['name'].': Icon_100 image; ' . $handle->error;
			
			if($in['pgtype'] == "person"){
				$handle->file_new_name_body    = "profile_".$fname;
				$handle->image_convert         = 'png';
				$handle->file_new_name_ext     = 'png';
				$handle->image_resize          = true;
				$handle->image_ratio_crop      = true;
				$handle->image_x               = 150;
				$handle->image_y               = 175;
		  	$handle->Process($_SERVER['DOCUMENT_ROOT'].$filedir);
		  	if(!$handle->processed) $errs[] = $file['name'].': Person profile pic; ' . $handle->error;
		  }
			
		} else $errs[] = ('file not processed: ' . $handle->error);
			
  } else {
		$errs[] = ('file not uploaded on the server: ' . $handle->error);
  }
	
  return(array($final_file, $errs));
	
}
?>