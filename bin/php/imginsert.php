<?
use Vgsite\Page;
use Vgsite\Image;
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.ajax.php";

function loadMyImgSessions($min=0, $max=30) {
	
	global $usrid;
	if(!$usrid) return false;
	$num_myImgs = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM images_sessions WHERE usrid = '$usrid'"));
	if(!$num_myImgs) return false;
	$query = "SELECT * FROM images_sessions WHERE usrid = '$usrid' ORDER BY img_session_created DESC";
	$num_sessions = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query));
	if($num_sessions > $max) $query.= " LIMIT $min, $max";
	if(($min + $max) < $num_sessions){
		$num_pgs = $num_sessions / $max;
		$num_pgs = ceil($num_pgs);
		$this_pg = $min / $max;
		$this_pg++;
	}
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	$ret = '<div class="mouseposscroll" style="margin:-10px 0 0 -20px; padding:10px 0 0;"><ul class="imgslist mouseposscroll-container" style="width:'.(mysqli_num_rows($res) * 110 + 220).'px">';
	while($row=mysqli_fetch_assoc($res)){
		$file = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], "SELECT img_name FROM images WHERE img_session_id = '".$row['img_session_id']."' ORDER BY `sort` ASC LIMIT 1"));
		$img = new img($file->img_name);
		$ret.= '<li class="a" title="'.htmlSC($row['img_session_description']).' ('.$row['img_qty'].' images)" onclick="img.loadForm(\'select\',{img_session_id:\''.$row['img_session_id'].'\'})"><img src="'.$img->src['tn'].'"/><div class="caption">'.$row['img_session_description'].'</div><div class="num">'.$row['img_qty'].'</div></li>';
	}
	$ret.= '</ul></div><ul class="nav imgslist-nav">'.
	($min > 0 ? '<li><a title="previous set" onclick="img.loadForm(\'select\', {sessionlist:\''.($min - $max).'\'})"><span class="arrow"></span>Prev</a></li>' : '').
	(($min + $max) < $num_sessions ? '<li style="float:right; margin:0;"><a title="next set" onclick="img.loadForm(\'select\', {sessionlist:\''.($min + $max).'\'})">Next<span class="arrow" style="background-position:-11px -29px; margin:0 0 0 5px;"></span></a></li>' : '').
	'</ul>';
	
	return $ret;
}

$sessid = img::makeSessionID();

$act = $_GET['action'] ? $_GET['action'] : $_POST['action'];
if($act){
	
	switch($act){
		
		case "load_ins_frame":
			
			$tags = array();
			$query = "SELECT DISTINCT(`tag`) FROM images_tags ORDER BY `tag`";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)){
				$tags[] = $row['tag'];
			}
			$p_tags = implode("`", $tags);
			
			$query = "SELECT * FROM images_categories ORDER BY sort";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)){
				$img_category_opts.= '<option value="'.$row['img_category_id'].'"'.($row['img_category_id'] == $img->img_category_id ? ' selected' : '').'>'.$row['img_category'].'</option>';
			}
			
			?>
			<div id="insimg" class="insimg imgframe">
			
				<script type="text/javascript">
					imgTags = '<?=addslashes($p_tags)?>'.split('`');
					$("#imgtagsq").autocomplete({
						source:imgTags, 
						minLength:-1,
						open: function(){ $(this).autocomplete("widget").width(221).css("max-height", "300px") },
						select:function(event, ui){
							img.loadForm('search', {'query':ui.item.value});
							$(this).val('');
							return false;
						}
					});
				</script>
				
				<a class="close" onclick="img.closeForm()">Close <span class="arrow"></span></a>
				
				<ul class="nav">
					<li id="insimg-nav-select"><a onclick="img.nav('select')">My images</a></li>
					<li id="insimg-nav-upload"><a onclick="img.nav('upload')">Upload</a></li>
					<li id="insimg-nav-search">
						<a onclick="img.nav('search')">Search images</a>
						<form class="fftt" onsubmit="return false;">
							<input type="text" id="imgtagsq" class="ff" style="width:200px; border:none; margin:0; padding:0 0 0 21px; background:white url('/bin/img/search_gray.png') no-repeat 4px 5px; height:23px; line-height:23px; font-size:12px; border-radius:0;"/>
							<label class="tt" style="padding:0 0 0 21px; line-height:23px; font-size:12px;">Start typing to find a tag...</label>
						</form>
					</li>
				</ul>
				
				<div id="insimg-frame">
					<div id="insimg-select" class="frame"><?=loadMyImgSessions()?></div>
					<div id="insimg-upload" class="frame">
						<div>
							<form method="post" enctype="multipart/form-data" id="insimg-quickuploadform" onsubmit="submitUploadImg(); return false;">
								<input type="hidden" name="action" value="submimg"/>
								<input type="hidden" name="actionhandle" value="quick upload"/>
								<input type="hidden" name="sessid" value="<?=$sessid?>"/>
								<input type="hidden" name="usrid" value="<?=$usrid?>"/>
								<?
								if($_GET['upload_vars']){
									$uvars = array();
									if(is_array($_GET['upload_vars'])) $uvars = $_GET['upload_vars'];
									else $uvars[] = $_GET['upload_vars'];
									foreach($uvars as $key => $val){
										echo '<input type="hidden" name="'.$key.'" value="'.htmlSC($val).'"/>';
									}
								}
								?>
								<input type="file" name="upl" id="insimg-upload-file" onchange="$('form#insimg-quickuploadform').submit();"/> &nbsp; 
								<input type="text" name="upl_src" id="insimg-upload-url" value="http://" size="30" onfocus="if($(this).val()=='http://') $(this).val('')" style="background:url(/bin/img/icons/globe.png) no-repeat 4px 50%; padding-left:20px; background-color:white; color:blue; text-decoration:underline; font-size:12px; border-width:0; border-radius:0;"/> &nbsp;
								<button type="submit" style="border-width:0; font-size:12px; border-radius:0; background:#06C; color:white; text-shadow:none;">Upload</button>
							</form>
							<p>Upload a single image above, or use the <a href="/upload.php" target="_blank" class="arrow-link">Upload Manager</a> to upload multiple images.</p>
							<script>
								function submitUploadImg(){
									
									var file = document.getElementById('insimg-upload-file').files[0];
									console.log(file);
									
									if(file){
										if(file.type != "image/jpeg" && file.type != "image/gif" && file.type != "image/png"){
											alert("Only image files (JPEG, GIF, PNG) are allowed.");
											return;
										}
									} else {
										var imgUrl = $("#insimg-upload-url").val();
										if(!imgUrl || imgUrl == 'http://') return;
									}
									
									$("#insimg-quickuploadform :input").attr("disabled", true);
									img.closeForm(true);
									
									//start img console form
									
							    var inpFname;
							    var imgPreview = '';
									if(file){
										inpFname = file.name;
										if(window.FileReader) {
											var reader = new FileReader();
								      reader.onload = (function(theFile){
								        return function(e){
								          // Render thumbnail
								          $("#uplimg-editconsole .img").prepend('<img src="'+e.target.result.toString()+'" width="140" alt="your image"/>');
							        	}
								      })(file);
								      reader.readAsDataURL(file);
								    } else {
								    	//Safari
								    	$("#uplimg-editconsole .img").prepend('<img src="" width="140" alt="your image"/>');
								    }
							    } else {
							    	imgPreview = '<img src="'+imgUrl+'" width="140" alt="your image"/>';
							    	inpFname = imgUrl.substr((imgUrl.lastIndexOf("/")+1));
							    }
				          inpFname = inpFname.replace(/_/g, ' ').substr(0, inpFname.lastIndexOf('.'));
				          $("#uplimg-editconsole").remove();
				          $("body").append('<div id="uplimg-editconsole"><div class="container"><div class="img loading">'+imgPreview+'<div class="loading"></div><div class="tape"></div><div class="pgfold"></div></div><form onsubmit="return false;" style="margin-left:200px;" id="uplimg-edit-form"><ul style="margin:0; padding:0; list-style:none;"><li><input type="text" name="img_title" value="'+inpFname+'" placeholder="Image name" style="width:100%"/></li><li><textarea name="img_description" placeholder="Description" style="width:100%"></textarea></li><li><select name="img_category_id"><option value="">Unclassified</option><?=$img_category_opts?></select></li></ul><button type="submit" disabled="disabled" onclick="img.saveUplImgInpData();" style="font-weight:bold">Save & Insert</button> <button type="button" onclick="$(\'#uplimg-editconsole\').fadeOut(500).animate({opacity:0},500,function(){$(\'#uplimg-editconsole\').remove()}); window.xhr.abort();">Cancel</button></form><br style="clear:left"/></div></div>');
						      
						      //end img console form
						      
						      var formdata = new FormData();
									if(file) formdata.append("upl", file);
									if(imgUrl) formdata.append("upl_src", imgUrl);
									formdata.append("action", "submimg");
									formdata.append("actionhandle", "json");
									<?
									if(is_array($_GET['upload_vars'])){
										foreach($_GET['upload_vars'] as $key => $val){
											echo 'formdata.append("'.$key.'", "'.htmlSC($val).'");';
										}
									}
									?>
									
									window.xhr = new XMLHttpRequest();
									xhr.open("POST", "/uploadhandle.php", true);
									xhr.onload = function(){
										var res = JSON.parse(xhr.responseText);
										if(res.error){
											alert(res.error);
											$("#uplimg-editconsole").remove();
										}
										if(res.img_name){
											img.selected = res;
											$("#uplimg-editconsole .img").removeClass("loading").find("img").attr("src", res.src_box);
											$("#uplimg-editconsole form").append('<input type="hidden" name="img_name" value="'+res.img_name+'"/>');
											$("#uplimg-editconsole form button").attr("disabled", false);
											$.post(
												"/bin/php/imginsert.php",
												{ action:'load_img_data', img_name:res.img_name },
												function(res2){
													if(res2.error) alert(res2.error);
													if(res2.tags) $("#uplimg-editconsole form ul").append('<li>'+res2.tags+'</li>');
													if(res2.img_category_id){
														$("#uplimg-editconsole select[name='img_category_id']").each(function(){
															if(!$(this).val()) $(this).val(res2.img_category_id);
														});
													}
												}, "json"
											)
										}
						      }
							    xhr.send(formdata);
							  }
						  </script>
						</div>
					</div>
					<div id="insimg-search" class="frame">
						<div class="container mouseposscroll" style="margin-left:-20px"><div style="padding-left:20px;">Input a tag name (a game title, person's name, category, character, topic, etc.) to search all uploads.</div></div>
					</div>
				</div>
				
			</div>
			
			<div id="insimg-code" class="insimg popmsg">
				
				<a class="ximg" style="right:20px;" onclick="$(this).parent().fadeOut()">close</a>
				<h5>Generate image display code</h5>
				
				<form id="insimg-gencode" onsubmit="return false;">
					<dl>
						<dd style="margin-right:3px !important;">
							<input type="text" name="caption" value="" placeholder="Caption" style="width:100%;" onchange="img.generateCode()"/>
						</dd>
						<dd>
							<select name="size" onchange="img.generateCode()" style="width:155px">
								<option value="tn" selected>Thumbnail</option>
								<option value="ss">Screenshot</option>
								<option value="sm">Small</option>
								<option value="md">Medium</option>
								<option value="op">Large</option>
							</select><select name="align" onchange="img.generateCode()" style="width:155px; margin-left:5px;">
								<option value="left" selected>Align Left</option>
								<option value="right">Align Right</option>
								<option value="">Centered/Full width</option>
							</select>
						</dd>
						<dd><button onclick="img.generateCode()">Update code</button></dd>
					</dl>
				</form>
				
				<div class="codeframe">
					<textarea id="insimg-gencode-code" style="width:100%; height:45px; margin:0; font-size:11px;"></textarea>
					<button onclick="img.insertCode()">Insert code</button>
				</div>
				
				<h6>Preview</h6>
				<div id="insimg-gencode-preview"></div>
				
			</div>
			<?
			
			break;
		
		case "load_form":
			
			if(isset($_POST['sessionlist'])){
				echo loadMyImgSessions($_POST['sessionlist']);
				exit;
			}
			
			if($_POST['img_session_id'] || $_POST['query']){
				if($_POST['img_session_id']){
					$query = "SELECT img_name, img_id FROM images WHERE img_session_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['img_session_id'])."' ORDER BY `sort`";
					$back = 'img.loadForm(\'select\', {sessionlist:0})';
				} elseif($_POST['query']){
					$query = "SELECT img_name, img_id FROM images_tags LEFT JOIN images USING (img_id) WHERE `tag` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['query'])."'";
					$back = 'img.loadForm(\'search\', {query:\'\'})';
				} else {
					die("I am error.");
				}
				$res   = mysqli_query($GLOBALS['db']['link'], $query);
				$ret = '<form class="insimg-selimgs mouseposscroll"><ul class="imgslist mouseposscroll-container" style="width:'.(mysqli_num_rows($res) * 110 + 500).'px">';
				while($row=mysqli_fetch_assoc($res)){
					$img = new img($row['img_name']);
					$fsize = round($img->img_size * .0009765625);
					if($fsize > 1024) $fsize = round($fsize * .0009765625, 2).'mb';
					else $fsize.= 'kb';
					if(!$img->img_title) $img->img_title = $row['img_name'];
					$ret.= '<li class="a" title="'.htmlSC($img->img_title).' ('.$fsize.', '.$img->img_width.'x'.$img->img_height.')"><label for="imgsel-img-'.$row['img_id'].'"><img src="'.$img->src['tn'].'"/><div class="caption">'.$img->img_title.'</div><input type="checkbox" name="selimgs[]" value="'.$row['img_name'].'" class="selimgs" id="imgsel-img-'.$row['img_id'].'" onchange="if($(this).is(\':checked\')){ img.select({img_name:\''.$row['img_name'].'\', src_tn:\''.$img->src['tn'].'\'}) } else { img.deselect({img_name:\''.$row['img_name'].'\'}) }"/></label></li>';
				}
				$ret.= '</ul></form>'.
				($_POST['img_session_id'] ? '<ul class="nav imgslist-nav"><li><a title="Show all images" onclick="'.$back.'"><span class="arrow"></span>All images</a></li>' : '');
				
				die($ret);
			}
			
			exit;
		
		case "generate_code":
		
			header("Content-type: application/json");
			$ret = array();
			
			parse_str($_POST['vars'], $vars);
			$p_vars = $vars['size'].($vars['align'] ? '|'.$vars['align'] : '').'|caption='.$vars['caption'];
			$img_names = $vars['img_name'];
			$num_imgs = count($img_names);
			
			foreach($img_names as $img_name){
				$img = new img($img_name);
				$ret['code'].= '{img:'.$img_name.($num_imgs == 1 ? '|'.$p_vars : '').'}';
			}
			
			if($num_imgs > 1) $ret['code'] = '[gallery|'.$p_vars.']'.$ret['code'].'[/gallery]';
			
			$bb = new bbcode();
			$bb->text = $ret['code'];
			$ret['formatted'] = $bb->bb2html();
			
			die(json_encode($ret));
		
		case "load_img_data":
			
			// return img data
			
			header("Content-type: application/json");
			$ret = array();
			
			if(!$img_name = $_POST['img_name']) die(json_encode(array("error"=>"No image name given")));
			
			$img = new img($img_name);
			if($img->notfound) die(json_encode(array("error"=>"Couldn't get image data")));
			
			$ret['img_category_id'] = $img->img_category_id;
			
			require_once("class.tags.php");
			$tags = new tags("images_tags:img_id:".$img->img_id);
			$tags->allow_add = true;
			
			$ret['tags'] = $tags->taglist(0,0) . $tags->suggestForm();
			
			if($_POST['load_imgcategoryid_options']){
				$ret['imgcategoryid_options'] = '<option value="">Unclassified</option>';
				$query = "SELECT * FROM images_categories ORDER BY sort";
				$res   = mysqli_query($GLOBALS['db']['link'], $query);
				while($row = mysqli_fetch_assoc($res)){
					$ret['imgcategoryid_options'].= '<option value="'.$row['img_category_id'].'"'.($row['img_category_id'] == $img->img_category_id ? ' selected' : '').'>'.$row['img_category'].'</option>';
				}
			}
			
			die(json_encode($ret));
		
		case 'load_edit_form':
			
			$a = new ajax();
			
			$img = new img($_POST['img_name']);
			if($img->notfound) $a->kill("Couldn't get image data");
			
			require_once("class.tags.php");
			$tags = new tags('images_tags:img_id:'.$img->img_id);
			
			$query = "SELECT * FROM images_categories ORDER BY sort";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)){
				$img_category_opts.= '<option value="'.$row['img_category_id'].'"'.($row['img_category_id'] == $img->img_category_id ? ' selected' : '').'>'.$row['img_category'].'</option>';
			}
			
			$a->ret['formatted'] = '
				<div id="uplimg-editconsole" style="display:none">
					<div class="container">
						<div class="img">
							<a href="/image/'.$_POST['img_name'].'" class="imgupl"><img src="'.$img->src['sm'].'" id="uplimg-img-src"/></a>
							<div class="loading"></div>
							<div class="tape"></div>
							<div class="pgfold"></div>
						</div>
						<div style="clear:left; float:left; margin-top:10px;">
							&nbsp;&nbsp;<a class="arrow-up" onclick="$(\'#uplimg-editconsole-upl\').click()">Reupload</a>
							<script>
								function submitReploadImg(){
									
									var file = document.getElementById(\'uplimg-editconsole-upl\').files[0];
									if(!file.name) return;
									
									$("#uplimg-editconsole .img").addClass("loading");
									
									if(window.FileReader) {
										var reader = new FileReader();
							      reader.onload = (function(theFile){
							        return function(e){
							          $("#uplimg-img-src").attr("src", e.target.result.toString());
						        	}
							      })(file);
							      reader.readAsDataURL(file);
							    }
						      
						      var formdata = new FormData();
									if(file) formdata.append("upl", file);
									formdata.append("action", "submimg");
									formdata.append("actionhandle", "json");
									formdata.append("reupload", "'.$_POST['img_name'].'");
									
									window.xhr = new XMLHttpRequest();
									xhr.open("POST", "/uploadhandle.php", true);
									xhr.onload = function(){
										var res = JSON.parse(xhr.responseText);
										if(res.error){
											alert(res.error);
										}
										if(res.img_name){
											$("#uplimg-editconsole .img").removeClass("loading");
											$([res.src, res.src_op, res.src_sm, res.src_box, res.src_tn]).preload();
										}
						      }
							    xhr.send(formdata);
							  }
						  </script>
							<form style="visibility:hidden; width:140px; overflow:hidden;">
								<input type="file" name="upl" id="uplimg-editconsole-upl" onchange="submitReploadImg()"/>
							</form>
						</div>
						<div style="margin-left:200px;">
							<form onsubmit="return false;" name="uplimg-edit-form" id="uplimg-edit-form">
								<input type="hidden" name="img_name" value="'.$_POST['img_name'].'"/>
								<ul style="margin:0; padding:0; list-style:none;">
									<li><input type="text" name="img_title" value="'.htmlSC($img->img_title).'" placeholder="Image name" style="width:100%"/></li>
									<li><textarea name="img_description" placeholder="Description" style="width:100%">'.$img->img_description.'</textarea></li>
									<li>
										<select name="img_category_id">
											<option value="">Unclassified</option>
											'.$img_category_opts.'
										</select>
									</li>
								</ul>
							</form>
							<ul style="margin:0 0 10px; padding:0; list-style:none;">
								<li>'.$tags->taglist(0,0).$tags->suggestForm().'</li>
							</ul>
							<button type="submit" style="font-weight:bold" onclick="img.saveUplImgInpData()">Save</button>&nbsp;
							<button type="button" onclick="$(\'#uplimg-editconsole\').slideUp(500,function(){$(\'#uplimg-editconsole\').remove()});">Cancel</button>
						</div>
						<br style="clear:left"/>
					</div>
				</div>
			';
			
			exit;
		
		case 'set_img_data':
			
			header("Content-type: application/json");
			$ret = array();
			
			parse_str($_POST['in'], $in);//print_r($in);
			
			$img = new img($in['img_name']);
			if($img->notfound) die(json_encode(array("error"=>"Couldn't find image data")));
			
			$q = "UPDATE images SET img_title = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['img_title'])."', img_description = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['img_description'])."', img_category_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['img_category_id'])."' WHERE img_name = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['img_name'])."' LIMIT 1";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) $ret['error'] = "Couldn't save image data because of a database error";
			else $ret['success'] = '1';
			
			die(json_encode($ret));
		
	}
}