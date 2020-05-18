<?
use Vgsite\Page;
$page = new Page();
use Vgsite\Image;
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.pglinks.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.tags.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/htmltoolbox.php");

if($_POST) include("uploads.ajax.php");

$page->css[] = "/uploads.css";
$page->javascripts[] = "/uploads.js";
$page->title = "Videogam.in Upload Manager";
$page->header();

$page->closeSection();
$page->openSection(array("css"=>"position:relative;"));

?>
<header class="uploads-header">
	<h1>Upload Manager</h1>
	<div class="uploads-header-controls">
		<?=($_GET['session_id'] ? '<a href="/uploads.php" class="link-alluploads">Your Uploads</a>&nbsp;&nbsp;&nbsp;' : '')?>
		<a href="/upload.php" class="bluebutton link-newupload"><b>+</b> New Upload</a>
	</div>
</header>
<div class="clear"></div>
<?

if(!$usrid) $page->kill('Please <a href="/login.php">log in</a> to access your uploads.');

do if($_GET['session_id'] || $_GET['img_id']){
	
	// view/edit image or upload session
	
	$session_id = trim($_GET['session_id']);
	
	$query = "SELECT * FROM images_sessions WHERE img_session_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $session_id)."' LIMIT 1";
	$sess_res = mysqli_query($GLOBALS['db']['link'], $query);
	if(!mysqli_num_rows($sess_res)) $page->kill("Couldn't find session data fro ID # $session_id");
	$sess = mysqli_fetch_object($sess_res);
	if($sess->usrid != $usrid && $_SESSION['user_rank'] < 8) $page->kill("Sorry, but you don't have access to this upload session");
	
	if($_POST){
		
		$img_ids = $_POST['img_ids'];
		
		if(isset($_POST['img_category_id_mass']) && is_array($img_ids)){
			
			//classify
			
			foreach($img_ids as $img_id){
				$q = "UPDATE images SET img_category_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['img_category_id_mass'])."' WHERE img_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $img_id)."' LIMIT 1";
				if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't update table row for img_id # $img_id ; ".mysqli_error($GLOBALS['db']['link']);
				else $num_upd++;
			}
			
			if($num_upd) $results[] = $num_upd." images classified!";
			
		}
				
		
		if($_POST['addtags'] && is_array($img_ids)){
			
			//tags
			
			$tags = array();
			if($xtags = extractTags($_POST['addtags'])){
				foreach($xtags as $tag){
					$tags[] = ($tag['namespace'] ? $tag['namespace'].":" : '') . $tag['tag'] . ($tag['link_words'] ? "|".$tag['link_words'] : '');
				}
			} else $warnings[] = "No tags extracted (did you enclose tag phrases in [[double square brackets]]?)";
			
			if(count($tags)){
				foreach($img_ids as $img_id){
					//img_id exists?
					$q1 = "SELECT * FROM images WHERE img_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $img_id)."' LIMIT 1";
					foreach($tags as $tag){
						//not yet tagged with this phrase?
						$q2 = "SELECT * FROM images_tags WHERE img_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $img_id)."' AND tag='".mysqli_real_escape_string($GLOBALS['db']['link'], $tag)."' LIMIT 1";
						if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q1)) && !mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q2))){
							$ins['_return'] = "return";
							$ins['_subject'] = "images_tags:img_id:".$img_id;
							$ins['_tag'] = $tag;
							$res = addTag($ins);
							if($res['error']) $errors[] = $res['error'];
						}
					}
				}
			}
		}
		
		if($_POST['deleteselected'] && is_array($_POST['img_ids'])){
			foreach($_POST['img_ids'] as $img_id){
				$rm_img_ids[] = $img_id;
			}
		}
		
	}
	
	if($rm_img_id = $_GET['rmimg']){
		$rm_img_ids[] = $rm_img_id;
	}
	if($rm_img_ids){
		
		foreach($rm_img_ids as $rm_img_id){
			$img = new img($rm_img_id);
			if($img->notfound) $errors[] = "Couldn't find the image ID [$rm_img_id]";
			elseif($img->remove() === false) $errors[] = $img->img_remove_error;
			else $num_rm++;
		}
		if($num_rm) $results[] = $num_rm." image".($num_rm != 1 ? "s" : "")." removed";
		
		//re-fetch session data
		$query = "SELECT * FROM images_sessions WHERE img_session_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $session_id)."' LIMIT 1";
		$sess_res = mysqli_query($GLOBALS['db']['link'], $query);
		if(!mysqli_num_rows($sess_res)) break;
		$sess = mysqli_fetch_object($sess_res);
	
	}
	
	$img_categories = imgGetCategories();
	foreach($img_categories as $row){
		$img_category_options.= '<option value="'.$row['img_category_id'].'">'.$row['img_category'].'</option>';
	}
	
	?>
	<input type="hidden" name="img_session_id" value="<?=$session_id?>" id="img_session_id"/>
	
	<div style="width:500px; height:36px; overflow:hidden;">
		<form class="iledit nobuttons form-imgsessiondescription">
			<input type="hidden" name="img_session_id" value="<?=$session_id?>"/>
			This image set is called <input type="text" name="img_session_description" value="<?=htmlSC($sess->img_session_description)?>" id="img_session_description" class="ileditinp input-imgsessiondescription" onblur="iledit.submit($(this).closest('form'))"/>
		</form>
		<div id="iledit-imgsessdesc" class="ileditme" title="click to change" style="font-size:22px; padding:4px 3px 5px; white-space:nowrap;"><?=$sess->img_session_description?></div>
	</div>
	<div style="margin:10px 0 0; font-size:15px; color:#CCC;">
		<b style="color:black;"><?=$sess->img_qty?> Image<?=($sess->img_qty != 1 ? 's' : '')?></b> &nbsp; 
		<a href="/upload.php?session_id=<?=$session_id?>" title="Upload more images to this set" style="text-decoration:none"><b style="font-size:14px;">+</b> <u>Upload More</u></a>
	</div>
	<?
	if(substr($sess->img_session_description, 0, 4) == date("Y")){
		/*?>
		<div class="bubble" style="position:relative; float:left; margin:-8px 0 0; padding:5px; color:white; background-color:black; border-radius:2px; -moz-border-radius:2px; -webkit-border-radius:2px;">
			Give this group of images a good description! 
			<a class="tooltip helpinfo" title="For example: 'Super Metroid screenshots', 'Erotic Hironobu Sakaguchi mustache art', etc." style="padding:0 4px; background-position:right center;"><span>?</span></a>
			<span style="position:absolute; top:-3px; left:10px; display:block; width:6px; height:3px; background:url('/bin/img/speech_point_black_up.png') no-repeat center top;"></span>
		</div>
		<br style="clear:left;"/>
		<?*/
	} else echo '<div style="clear:both;"></div>';
	?>
	
	<div id="consoleform" class="alert">
		<a href="#close" class="closealert ximg" onclick="$(this).parent().fadeOut();">close</a>
		<div id="addTagsSelected" class="container console-section">
			<h6>Add/Edit Tags</h6>
			<form action="" onsubmit="return addTagsSelected($('#inp-addtag').val());" id="imgaddtagform">
				<input type="text" name="tagname" placeholder="Start typing to find a tag..." id="inp-addtag" class="focusonme" style="width:230px; float:left;"/>
				<input type="submit" value="Add Tag" class="submit" style="float:left; margin-left:5px;"/>
				<br style="clear:left;"/>
			</form>
			<h6>Current Tags</h6>
			<div id="imgseltaglist" style="padding:0 0 0 1em; color:#888;"></div>
		</div>
		<div id="classifySelected" class="container console-section">
			<h6>Classify Images</h6>
			
				<form action="" method="" id="massclassimgs" onsubmit="return submitMassClassify()">
					<ul style="list-style:none; margin:0 0 15px; padding:0;">
						<?
						foreach($img_categories as $row){
							echo '<li style="margin:0 0 5px;"><label><input type="radio" name="img_category_id_mass" value="'.$row['img_category_id'].'"/><b>'.$row['img_category'].'</b> &ndash; '.$row['img_category_description'].'</label></li>';
						}
						?>
						<li style="margin:0 0 10px;"><label><input type="radio" name="img_category_id_mass" value="0" checked="checked"/>No Classification</label></li>
					</ul>
					<button type="submit" class="submit" style="font-weight:bold">Submit</button> &nbsp; 
					<button type="button" class="cancel" onclick="$('#consoleform').fadeOut();">Cancel</button>
				</form>
		</div>
	</div>
	
	<!--<form action="uploads.php?session_id=<?=$session_id?>" method="post" name="edimgform" id="edimgform" onsubmit="return ($('#clicksubmit').val() ? true : false);" style="display:block;position:relative;">-->
	<div id="edimgform">
		
		<?
		if($sess->img_qty > 1){
			?>
			<div style="height:10px;"></div>
			<div id="imgeditconsole">
				<!--<div style="float:right; margin:5px 0 0; color:#888;">
					Select by <input type="checkbox" checked="checked" style="margin:0; vertical-align:middle;"/> or <img src="/bin/img/icons/selection.png" title="Click & drag to mass select" style="vertical-align:middle;"/>
				</div>-->
				
				<div class="pgnav" style="float:right; border:1px solid #CCC;">
					<ul>
						<li class="viewmode sprite on"><a href="#sm" title="view mode: edit"><span style="">Edit</span></a></li>
						<li class="viewmode sprite"><a href="#tn" title="view mode: thumbnails"><span style="background-position:0 -30px;">Thumbnails</span></a></li>
					</ul>
				</div>
				<br style="clear:both;"/>
				
				<p></p>
				<label id="checkmass" style="display:block; width:100px; float:right;"><input type="checkbox" style="float:left;"/><span class="a">Mass Select</span></label>
				<br style="clear:both;"/>
				
				<p></p>
				<div style="text-align:right;">
					<span class="arrow-right" style="margin:0 -11px 0 0; font-size:13px; color:#888;"><span id="numimgssel">None</span> Selected</span>
					<ul class="consoleacts">
						<li><a href="#addTagsSelected" title="Add or remove tags" class="consoleact"><span style="background-position:-147px 3px;"></span> Add/Remove Tags</a></li>
						<li><a href="#classifySelected" title="Classify Image Type" class="consoleact" style="background-position:-179px 1px;">Classify Image Type</a></li>
						<li><a href="#getCode" title="Generate Display Code" class="consoleact neveron" style="background-position:-60px 3px;" onclick="genImgCode('gallery')">Generate Display Code</a></li>
						<li><a href="#deleteSelected" title="Delete" class="consoleact red" style="background-position:-28px 2px;">Delete</a></li>
					</ul>
				</div>
			</div>
			<?
		}
		?>
		
		<div id="imgsetedit" class="sm<?=($sess->img_qty > 1 ? ' selectable' : '')?>">
			<?
			$query = "SELECT img_name FROM images WHERE img_session_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $session_id)."' ORDER BY `sort`, img_id";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)){
				
				$img = new img($row['img_name']);
				if($img->notfound) continue;
				
				$img_title = '';
				if($img->img_title) $img_title = $img->img_title;
				else {
					$img->img_title = substr($img->img_name, 0, -4);
					$img_title = str_replace("_", " ", $img->img_title);
					$img_title = str_replace("-", " ", $img_title);
					$img_title = str_replace(".", " ", $img_title);
				}
				
				?>
				<div id="img-<?=$img->img_id?>" class="imgcontainer sortme nohov">
					<?=($sess->img_qty > 1 ? '<div class="sort"></div>' : '')?>
					<div class="img">
						<?
						$fsize = round($img->img_size * .0009765625);
						if($fsize > 1024) $fsize = round($fsize * .0009765625, 2).'mb';
						else $fsize.= 'kb';
						$title = $img->img_name.'<br/>'.$img->img_width.'x'.$img->img_height.'<br/>'.$fsize;
						
						if($sess->img_qty > 1){
							?>
							<label for="img-ch-<?=$img->img_id?>" style="display:block; cursor:pointer;" title="<?=$title?>" class="tooltip"><img src="<?=$img->src['sm']?>" alt="<?=$img->img_name?>" class="sm"/><img src="<?=$img->src['tn']?>" alt="<?=$img->img_name?>" width="100" height="100" class="tn"/></label>
							<div class="bodybg" style="position:absolute; left:0; top:0; width:16px; height:16px;">
								<input type="checkbox" name="img_ids[]" value="<?=$img->img_id?>" id="img-ch-<?=$img->img_id?>" class="imgselch" style="margin:0; padding:0;" onclick="$(this).closest('.imgcontainer').toggleClass('checked');"/>
							</div>
							<?
						} else {
							?>
							<a href="/image/<?=$img->img_name?>/session/<?=$session_id?>" title="Permanent link for this image" class="tooltip"><img src="<?=$img->src['sm']?>" alt="<?=$img->img_name?>" title="<?=$title?>" class="tooltip"/></a>
							<?
						}
						?>
					</div>
					<div class="det">
						<dl>
							<dt>
								<input type="hidden" name="img_name" value="<?=$img->img_name?>"/>
								<form class="iledit">
									<input type="hidden" name="img_session_id" value="<?=$session_id?>"/>
									<input type="hidden" name="img_id" value="<?=$img->img_id?>"/>
									<input type="text" name="img_title" value="<?=htmlSC($img_title)?>" class="ileditinp" style="width:300px;"/>
								</form>
								<div class="ileditme" title="click to change"><?=$img->img_title?></div>
							</dt>
							<dd class="desc">
								<?
								$img_desc = '';
								if($img->img_description){
									$img_desc = $img->img_description;
									$bb = new bbcode();
									$bb->params['inline_citations'] = 1;
									$o_img_desc = $bb->bb2html($img_desc);
								}
								?>
								<form class="iledit">
									<input type="hidden" name="img_session_id" value="<?=$session_id?>"/>
									<input type="hidden" name="img_id" value="<?=$img->img_id?>"/>
									<textarea name="img_description" class="ileditinp tagging autosize" style="width:480px; height:28px;"><?=$img_desc?></textarea>
								</form>
								<div class="ileditme" title="click to change"><?=(!$img_desc ? '<i class="null"><a>Add a description</a></i>' : $img_desc)?></div>
							</dd>
							<dd class="tags taglist">
									<?
									$_tags = new tags("images_tags:img_id:".$img->img_id);
									$_tags->allow_add = true;
									$_tags->allow_rm = true;
									
									echo $_tags->taglist(0, 0);
									echo $_tags->suggestForm();
									?>
							</dd>
							<dd class="catg">
								<form class="iledit">
									<input type="hidden" name="img_session_id" value="<?=$session_id?>"/>
									<input type="hidden" name="img_id" value="<?=$img->img_id?>"/>
									<select name="img_category_id" class="ileditinp" style="width:auto;">
										<?=$img_category_options?>
									</select>
								</form>
								<div class="ileditme" title="click to change"><?=(!$img->img_category_id ? '<i class="null">Not yet classified! <a>Categorize this image</a></i>' : '<strong>'.$img_categories[$img->img_category_id]['img_category'].'</strong>')?></div>
							</dd>
							<dd class="info">
								<time datetime="<?=$img->img_timestamp?>" title="Uploaded <?=$img->img_timestamp?>"><?=formatDate($img->img_timestamp, 7)?></time> &middot; 
								<?=($img->img_views ? '<strong>'.$img->img_views.'</strong>' : 'No')?> views &middot; 
								<?=($num_comments ? '<a href="/image/'.$img->img_name.'/session/'.$session_id.'">'.$num_comments.' comments</a>' : 'No comments')?>
							</dd>
							<dd class="controls">
								<ul>
									<li class="permalink">
										<a href="/image/<?=$img->img_name?>/session/<?=$session_id?>" title="Permanent link for this image"><span style="background-position:-89px 1px;">permalink</span></a>
									</li>
									<li class="code">
										<a href="#generateImgCode" title="Generate display code" onclick="genImgCode('<?=$img->img_name?>')"><span style="background-position:-60px 3px;">display code</span></a>
									</li>
									<li class="delete">
										<a href="#delete" title="Delete image" class="rmImg" rel="<?=$img->img_id?>"><span style="background-position:-28px 2px;">delete</span></a>
									</li>
								</ul>
							</dd>
						</dl>
					</div>
					<br class="clear"/>
				</div>
				<?
			}
			?>
			<div style="clear:both; height:40px;"></div>
		</div>
	<!--</form>-->
	</div>
	<?
	
	$page->footer();
	exit;
	
} while(false);

$num_imgs = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM images WHERE usrid = '$usrid'"));
$num_sess = !$num_imgs ? 0 : mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM images_sessions WHERE usrid = '$usrid'"));

?>
<form action="uploads.php" method="get" name="uploadsq" id="uploadsq">
	<h6 style="float:right; margin:0; padding:0; font-size:14px; font-weight:normal;">You have uploaded <b><?=$num_imgs?></b> images (<b><?=$num_sess?></b> sessions). Your most recent uploads are below:</h6>
	<a class="submit preventdefault" tabindex="2" title="Submit search query" onclick="document.uploadsq.submit()">Search</a>
	<input type="text" tabindex="1" value="<?=htmlSC($_GET['q'])?>" name="q" placeholder="Search your uploads...">
	<?=($_GET['q'] ? ' &nbsp; <a href="/uploads.php" class="arrow-right">All image uploads</a>' : '')?>
</form>
<div style="margin-bottom:20px;"></div>
<?

if($q = trim($_GET['q'])){
	
	$sessions = array();
	
	//get image sessions by tag
	$query = "SELECT DISTINCT(img_session_id) FROM images_tags LEFT JOIN images USING (img_id) WHERE tag = '".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."' AND images.usrid='$usrid'";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) $sessions[] = $row['img_session_id'];
	
	//get image sessions by description
	$query = "SELECT img_session_id FROM images_sessions WHERE img_session_description LIKE '%".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."%' and usrid='$usrid'";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)){
		if(!in_array($row['img_session_id'], $sessions)) $sessions[] = $row['img_session_id'];
	}
	
	if(!count($sessions)) echo '<i>No images found.</i> <a href="/uploads.php" class="arrow-right">All images</a>';
	else {
		?><div class="uploadslist"><?
		arsort($sessions);
		foreach($sessions as $session_id){
			$query = "SELECT * FROM images_sessions WHERE img_session_id = '$session_id' LIMIT 1";
			if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $query))){
				$file = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], "SELECT img_name FROM images WHERE img_session_id = '".$row['img_session_id']."' ORDER BY `sort` ASC LIMIT 1"));
				$img = new img($file->img_name);
				?>
				<figure>
					<a href="?session_id=<?=$row['img_session_id']?>">
						<img src="<?=$img->src['ss']?>" border="0"/>
						<figcaption>
							<h6><?=$row['img_session_description']?></h6>
							<b><?=$row['img_qty']?> image<?=($row['img_qty'] > 1 ? 's' : '')?></b> &middot; <b><?=substr($row['img_session_created'], 0, 10)?></b>
						</figcaption>
					</a>
				</figure>
				<?
			}
		}
		?></div><?
	}
	
} else {

	$query = "SELECT * FROM images_sessions WHERE usrid='$usrid' ORDER BY img_session_created DESC limit 0, 32";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)){
		?>
		<div class="uploadslist">
			<?
			while($row = mysqli_fetch_assoc($res)){
				$file = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], "SELECT img_name FROM images WHERE img_session_id = '".$row['img_session_id']."' ORDER BY `sort` ASC LIMIT 1"));
				$img = new img($file->img_name);
				?>
				<figure>
					<a href="?session_id=<?=$row['img_session_id']?>">
						<img src="<?=$img->src['ss']?>" border="0"/>
						<figcaption>
							<h6><?=$row['img_session_description']?></h6>
							<b><?=$row['img_qty']?> image<?=($row['img_qty'] > 1 ? 's' : '')?></b> &middot; <b><?=substr($row['img_session_created'], 0, 10)?></b>
						</figcaption>
					</a>
				</figure>
				<?
			}
			?>
		</div>
		<?
	} else echo '<p>You haven\'t uploaded anything yet.</p>';
	
}

$page->footer();