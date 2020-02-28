<?
require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");
require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/class.upload.php");

// accept and process an image upload
// form for accepting img upload

$imgurl = trim($_POST['upl_src']);
if($imgurl == "http://") unset($imgurl);

if($_FILES['upl']['name'] || $imgurl){
	
	$fdir = $_POST['fdir'] ? $_POST['fdir'] : "_";
	$fdir = rawurlencode($fdir);
	$fdir = "/pages/files/".preg_replace("/[^a-zA-Z0-9-_]/", "", $fdir)."/";
	
	if($imgurl){
		
		if(!filter_var($imgurl, FILTER_VALIDATE_URL)){ $jscript = 'alert("The given URL is not valid");'; unset($_POST); }
		
		/*$ext = substr($imgurl, -4);
		$ext = strtolower($ext);
		$exts = array(".jpg", ".gif", ".png", "jpeg");
		if(!in_array($ext, $exts)){ $jscript = 'alert("The given image URL doesn\'t have a recognized extension; Try saving to your harddrive and uploading. ['.$imgurl.'] ['.$ext.']'; unset($_POST); }*/
		
		$x = explode("/", $imgurl);
		$br = count($x) - 1;
		
		$file = $_SERVER['DOCUMENT_ROOT']."/bin/temp/".$x[$br];
		if(!copy($imgurl, $file)){ $jscript = 'alert("'.$x[$br].' -- Couldn\'t copy the remote file to the local server");'; unset($_POST); }
		if(!file_exists($file)){ $jscript = 'alert("'.$x[$br].' -- Couldn\'t copy the remote file to the local server (file not found)");'; unset($_POST); }
		
		if(!getimagesize($file)){ $jscript = 'alert("'.$x[$br].' -- Couldn\'t copy the remote file to the local server (file may not be an image)");'; unset($_POST); }
		
		if(!$_POST) unlink($file);
	
	} else {
		
		$file  = $_FILES['upl'];
		$fname = substr($file['name'], 0, -4);
		
		if(!getimagesize($file['tmp_name'])){ $jscript = 'alert("'.$x[$br].' -- Couldn\'t upload file (file may not be an image)");'; unset($_POST); unlink($file); }
		
	}
	
	switch($_POST['imgtype']){
		
		case "boxart":
		case "rep_image":
			
			$handle = new Upload($file);
			
		  if($handle->uploaded){
				$accept_src = array("png", "jpg", "jpeg", "gif");
				if(!in_array($handle->file_src_name_ext, $accept_src)){
					$jscript = 'alert("Error: Please upload a JPG, GIF, or PNG");';
					break;
				}
		  	if($handle->file_src_name_ext == "jpeg"){
		  		$handle->image_convert = "jpg";
		  		$handle->file_new_name_ext = "jpg";
		  	}
		  	$handle->file_new_name_body = $fname;
		  	$handle->allowed = array('image/*');
				//$handle->image_resize           = true;
				//$handle->image_ratio_no_zoom_in = true;
				//$handle->image_x                = 800;
				//$handle->image_y                = 1000;
		  	$handle->Process($_SERVER['DOCUMENT_ROOT'].$fdir);
				if ($handle->processed) {
					
					$final_file = $fdir.$handle->file_dst_name;
					$fname = $handle->file_dst_name_body;
					
					//med img
					$handle->file_new_name_body    = "md_".$fname;
					$handle->image_convert         = 'png';
					$handle->file_new_name_ext     = 'png';
					$handle->image_resize          = true;
					$handle->image_ratio_y         = true;
					$handle->image_x               = 200;
					$handle->Process($_SERVER['DOCUMENT_ROOT'].$fdir);
					if(!$handle->processed) $errs[] = $file['name'].': Md image; ' . $handle->error;
					
					//small img
					$handle->file_new_name_body    = "sm_".$fname;
					$handle->image_convert         = 'png';
					$handle->file_new_name_ext     = 'png';
					$handle->image_resize          = true;
					$handle->image_ratio_y         = true;
					$handle->image_x               = 140;
					$handle->Process($_SERVER['DOCUMENT_ROOT'].$fdir);
					if(!$handle->processed) $errs[] = $file['name'].': Small image; ' . $handle->error;
					
					//thumbnail
					$handle->file_new_name_body    = "tn_".$fname;
					$handle->image_convert         = 'png';
					$handle->file_new_name_ext     = 'png';
					$handle->image_resize          = true;
					$handle->image_ratio_y         = true;
					$handle->image_x               = 80;
					$handle->Process($_SERVER['DOCUMENT_ROOT'].$fdir);
					if (!$handle->processed) $errs[] = $file['name'].': Thumbnail image; ' . $handle->error;
					
					//icons
					$handle->file_new_name_body    = "icon50_".$fname;
					$handle->image_convert         = 'png';
					$handle->file_new_name_ext     = 'png';
					$handle->image_resize          = true;
					$handle->image_ratio_crop      = "TL";
					$handle->image_x               = 50;
					$handle->image_y               = 50;
					$handle->Process($_SERVER['DOCUMENT_ROOT'].$fdir);
					if (!$handle->processed) $errs[] = $file['name'].': Icon_50 image; ' . $handle->error;
					
					$handle->file_new_name_body    = "icon100_".$fname;
					$handle->image_convert         = 'png';
					$handle->file_new_name_ext     = 'png';
					$handle->image_resize          = true;
					$handle->image_ratio_crop      = "TL";
					$handle->image_x               = 100;
					$handle->image_y               = 100;
					$handle->Process($_SERVER['DOCUMENT_ROOT'].$fdir);
					if (!$handle->processed) $errs[] = $file['name'].': Icon_100 image; ' . $handle->error;
					
					$handle->file_new_name_body    = "profile_".$fname;
					$handle->image_convert         = 'png';
					$handle->file_new_name_ext     = 'png';
					$handle->image_resize          = true;
					$handle->image_ratio_crop      = true;
					$handle->image_x               = 150;
					$handle->image_y               = 175;
			  	$handle->Process($_SERVER['DOCUMENT_ROOT'].$fdir);
			  	if(!$handle->processed) $errs[] = $file['name'].': Person profile pic; ' . $handle->error;
					
				} else $errs[] = ('file not processed: ' . $handle->error);
					
		  } else {
				$errs[] = ('file not uploaded on the server: ' . $handle->error);
		  }
			
			if($errs){
				foreach($errs as $err) echo 'alert("'.stripslashes($err).'");';
			} else {
				$jscript = 'parent.upl.changeImg("'.$_POST['retelid'].'", "'.$final_file.'", "'.$fdir.'md_'.$fname.'.png");';
			}
			
			break;
		
		case "heading_image":
			
			$handle = new Upload($file);
		  if($handle->uploaded){
		  	
				$accept_src = array("png", "jpg", "jpeg", "gif");
				if(!in_array($handle->file_src_name_ext, $accept_src)){
					$jscript = 'alert("Error: Please upload a JPG, GIF, or PNG");';
					break;
				}
				
		  	$handle->image_min_width       = 940;
		  	$handle->image_resize          = true;
				$handle->image_x               = 940;
				$handle->jpeg_quality          = 95;
				$resize_ratio = 940 / $handle->image_src_x;
				$resize_ratio_y = $handle->image_src_y * $resize_ratio;
				if($resize_ratio_y <= 600){
					$handle->image_ratio_no_zoom_in = true;
					$handle->image_ratio_y = true;
				} else {
					$handle->image_ratio_crop = "T";
					$handle->image_y = 600;
				}
		  	$handle->Process($_SERVER['DOCUMENT_ROOT'].$fdir);
				if($handle->processed){
					$jscript = 'parent.upl.changeImg("'.$_POST['retelid'].'", "'.$fdir.$handle->file_dst_name.'", "'.$fdir.$handle->file_dst_name.'");';
				}
				else $jscript = 'alert("Error processing heading image; Make sure your image is at least 940 pixels in width");';
			} else $jscript = 'alert("There was an error and the image was not uploaded");';
			
			break;
		
		case "background_image":
			
			$handle = new Upload($file);
		  if($handle->uploaded){
				$accept_src = array("png", "gif");
				if(!in_array($handle->file_src_name_ext, $accept_src)){
					$jscript = 'alert("Error: Please upload a GIF or PNG");';
					break;
				}
		  	$handle->Process($_SERVER['DOCUMENT_ROOT'].$fdir);
				if($handle->processed){
					$jscript = 'parent.upl.changeImg("'.$_POST['retelid'].'", "'.$fdir.$handle->file_dst_name.'", "'.$fdir.$handle->file_dst_name.'");';
				}
				else $jscript = 'alert("Error processing background image; Make sure your image is a PNG or GIF with transparency to fluch with the background");';
			} else $jscript = 'alert("There was an error and the image was not uploaded");';
		
		default:
			break;
	}
}

if($_GET['component'] == "form"){
	
	// regular upload form
	
	$form_action = $_GET['imgtype'] == "boxart" ? "/uploadhandle.php" : "upload_handle.php?component=form";
	
	echo $html_tag;
	?>
	<head>
		<script type="text/javascript" src="/bin/script/jquery-1.4.4.min.js"></script>
		<script type="text/javascript">
			<?=$jscript?>
			function submUpl(){
				document.getElementById('uploadform').submit();
				$('#uplsubmit').hide().prev().show();
			}
		</script>
	</head>
	<body style="font-size:13px; font-family:Arial; color:white; margin:0; padding:0;">
	  <form action="<?=$form_action?>" method="post" enctype="multipart/form-data" id="uploadform">
	  	<?=($_GET['imgtype'] == "boxart" ? '
	  	<input type="hidden" name="action" value="submimg"/>
	  	<input type="hidden" name="actionhandle" value="boxartuploader_static"/>
	  	<input type="hidden" name="img_tag[]" value="'.$_GET['fdir'].'"/>
	  	<input type="hidden" name="img_category_id" value="4"/>
	  	<input type="hidden" name="handler" value="'.base64_encode("sessid=".$_GET['sessid']).'"/>
	  	' : '')?>
			<input type="hidden" name="imgtype" value="<?=($_GET['imgtype'] ? $_GET['imgtype'] : $_POST['imgtype'])?>"/>
			<input type="hidden" name="retelid" value="<?=($_GET['retelid'] ? $_GET['retelid'] : $_POST['retelid'])?>" id="retelid"/>
			<input type="hidden" name="fdir" value="<?=($_GET['fdir'] ? $_GET['fdir'] : $_POST['fdir'])?>"/>
	   	<img src="/bin/img/loading_ball_white.gif" alt="loading..." title="loading..." style="float:right; display:none;"/>
	    <input type="submit" value="Upload" id="uplsubmit" style="float:right; font-size:13px; font-family:arial; font-weight:bold;"/>
	    <input type="file" id="uploadelement" name="upl" accept="image/*" onchange="if(this.value){ submUpl() }" style="float:left;"/>
	    <input type="text" name="upl_src" value="http://" onfocus="this.value='';" onchange="if(this.value && this.value != 'http://'){ submUpl() }" style="float:left; margin-left:10px; padding-left:20px; background:url(/bin/img/icons/globe.png) no-repeat 4px 50% white; font-family:arial; color:#06C; text-decoration:underline;"/>
	  </form>
	</body>
	</html>
	<?
	exit;
}


//Drag and Drop form for box art

$query = "SELECT * FROM images_categories ORDER BY sort";
$res   = mysqli_query($GLOBALS['db']['link'], $query);
while($row = mysqli_fetch_assoc($res)){
	$img_category_opts.= '<option value="'.$row['img_category_id'].'"'.($row['img_category_id'] == $_GET['img_category_id'] ? ' selected' : '').'>'.$row['img_category'].'</option>';
}
	
?>
<!DOCTYPE html>
<html>
	<head>
		<script type="text/javascript" src="/bin/script/jquery.1.7.1.js"></script>
		<style>
			html, body { width:50px; height:50px; overflow:hidden; margin:0; padding:0; background:transparent; }
			#dropzone { width:50px; height:50px; }
			#pc.fin { background-color:green; color:white; }
		</style>
	</head>
	<body>
		
		<div id="dropzone"></div>
		
		<script>
		  function handleFileSelect(evt){
		  	
		    evt.stopPropagation();
		    evt.preventDefault();
		
		    var file = evt.dataTransfer.files[0]; // FileList object.
		    if(!file.name) return;
		    
		    var parent_key = '<?=$_GET['parent_key']?>';
	      
	      //start img console form
				
		    var inpFname;
		    var imgPreview = '';
				inpFname = file.name;
        inpFname = inpFname.replace(/_/g, ' ').substr(0, inpFname.lastIndexOf('.'));
        window.parent.$("#uplimg-editconsole").remove();
        window.parent.$("body").append('<div id="uplimg-editconsole"><div class="container"><div class="img loading">'+imgPreview+'<div class="loading"></div><div class="tape"></div><div class="pgfold"></div></div><form onsubmit="img.saveUplImgInpData(); return false;" style="margin-left:200px;" id="uplimg-edit-form"><ul style="margin:0; padding:0; list-style:none;"><li><input type="text" name="img_title" value="'+inpFname+'" placeholder="Image name" style="width:100%"/></li><li><textarea name="img_description" placeholder="Description" style="width:100%"></textarea></li><li><select name="img_category_id"><option value="">Unclassified</option><?=$img_category_opts?></select></li></ul><button type="submit" disabled="disabled" style="font-weight:bold">Save & Insert</button> <button type="button" onclick="$(\'#uplimg-editconsole\').fadeOut(500).animate({opacity:0},500,function(){$(\'#uplimg-editconsole\').remove()}); window.xhr.abort();">Cancel</button></form><br style="clear:left"/></div></div>');
	      
		    var reader = new FileReader();
	      reader.onload = (function(theFile){
	        return function(e){
	          // Render thumbnail
	          parent.changePubImg(parent_key, '', e.target.result);
	          window.parent.$("#uplimg-editconsole .img").prepend('<img src="'+e.target.result.toString()+'" width="140" alt="your image"/>');
        	}
	      })(file);
	      reader.readAsDataURL(file);
	      
	      //end img console form
		    
		    var formdata = new FormData();
				formdata.append("upl", file);
				formdata.append("action", "submimg");
				formdata.append("actionhandle", "json");
				formdata.append("img_category_id", "<?=$_GET['img_category_id']?>");
				formdata.append("handler", "<?=base64_encode('sessid='.$_GET['sessid'])?>");
				<?
				if(isset($_GET['img_tag'])){
					foreach($_GET['img_tag'] as $tag){
						echo 'formdata.append("img_tag[]", "'.htmlSC($tag).'");';
					}
				}
				?>
				
				var xhr = new XMLHttpRequest();
				xhr.open("POST", "/uploadhandle.php", true);
				xhr.onload = function(){
					var res = JSON.parse(xhr.responseText);
					if(res.error){
						alert(res.error);
						if(!res.src_box) parent.changePubImg(parent_key, '', '');
					}
					if(res.src_box) parent.changePubImg(parent_key, res.img_name, res.src_box);
					if(res.img_name){
						window.parent.$("#uplimg-editconsole .img").removeClass("loading").find("img").attr("src", res.src_box);
						window.parent.$("#uplimg-editconsole form").append('<input type="hidden" name="img_name" value="'+res.img_name+'"/>');
						window.parent.$("#uplimg-editconsole form button").attr("disabled", false);
						$.post(
							"/bin/php/imginsert.php",
							{ action:'load_img_data', img_name:res.img_name },
							function(res2){
								if(res2.error) alert(res2.error);
								if(res2.tags) window.parent.$("#uplimg-editconsole form ul").append('<li>'+res2.tags+'</li>');
								/*if(res2.img_category_id){
									window.parent.$("#uplimg-editconsole select[name='img_category_id']").val(res2.img_category_id);
								}*/
							}, "json"
						)
					}
        }
				xhr.send(formdata);
		    
		  }
		  
		  function updateProgress(e){
		  	if(e.lengthComputable) parent.pubImgUplProgress('<?=$_GET['parent_key']?>', (e.loaded / e.total));
			}
		
		  function handleDragOver(evt) {
		    evt.stopPropagation();
		    evt.preventDefault();
		    evt.dataTransfer.dropEffect = 'copy'; // Explicitly show this is a copy.
		  }
		
		  // Setup the dnd listeners.
		  var dropZone = document.getElementById('dropzone');
		  dropZone.addEventListener('dragover', handleDragOver, false);
		  dropZone.addEventListener('drop', handleFileSelect, false);
		  
		</script>
		
	</body>
</html>