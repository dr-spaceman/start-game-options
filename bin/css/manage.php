<?
use Vgsite\Page;
$page = new Page();
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.posts.php");
use Vgsite\Image;
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/contribute.php");

$page->title = "Videogam.in Post Management";
$page->javascripts[] = "/posts/posts_form.js";
$page->javascripts[] = "/bin/script/jquery.textareaautosize.js";
$page->css[] = "/posts/posts_form.css";

//delete
if($del = $_GET['delete']){
	$q = "SELECT * FROM posts WHERE session_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $del)."' LIMIT 1";
	$in = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
	if(!$in) $errors[] = "That post doesn't exist in the database; Either we couldn't find it or it hasn't been saved yet.";
	else {
		//user has access?
		if($in['options']) $in['options'] = json_decode($in['options'], true);
		if($_SESSION['user_rank'] <= 7 && $in['usrid'] != $usrid) $page->kill("<h1>Error</h1>You don't have access to edit this item.");
		if($in['options']['access'] && $_SESSION['user_rank'] < $in['options']['access']) $page->kill("<h1>Error</h1>This item is locked and can't be edited. [$_SESSION['user_rank'] < ".$in['options']['access']."]");
		
		$q = "DELETE FROM posts_edits WHERE nid = '$in[nid]'";
		mysqli_query($GLOBALS['db']['link'], $q);
		
		$q = "DELETE FROM posts_tags WHERE nid = '$in[nid]'";
		mysqli_query($GLOBALS['db']['link'], $q);
		
		$q = "DELETE FROM posts_ratings WHERE nid = '$in[nid]'";
		mysqli_query($GLOBALS['db']['link'], $q);
		
		$q = "DELETE FROM posts WHERE session_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $del)."' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) {
			$errors[] = "Database delete failure!";
			$edid = $in['nid'];
		} else {
			$results[] = "That item has been purged from the post records.";
			unset($in);
		}
	}
}

//edit
if($edid = $_GET['edit']) {
	//fetch edit data, etc
	$page->title.= " / Edit Post";
	$q = "SELECT * FROM posts WHERE nid = '".$edid."' LIMIT 1";
	$in = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
	if(!$in) $page->kill(404);
	$session_id = $in['session_id'];
	$postdat = $in;
	$_p = new post($in);
	$cont = $_p->content;
	$in['options'] = $in['options'] ? json_decode($in['options'], 1) : array();
	unset($opts);
	//user has access?
	if($_SESSION['user_rank'] <= 7 && $in['usrid'] != $usrid) $page->kill("Access Error.");
	if($in['options']['access'] && $_SESSION['user_rank'] < $in['options']['access']) $page->kill("Access Error: This item is locked and can't be edited.");

//newpost
} elseif($_GET['action'] == "newpost") {
	$newpost = TRUE;
	$session_id = date("YmdHis").sprintf("%07d",$usrid);
	$page->title.= " / New Post";

// Management Index 
} elseif(!$in && !$edid) {
	
	$page->header();
	
	?><h1>Sblog Manager</h1><?
	
	$query = "SELECT * FROM posts WHERE usrid='$usrid' ORDER BY datetime DESC";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	if(!$num = mysqli_num_rows($res)) {
		?>You have no posts to manage. <a href="manage.php?action=newpost" class="plus"><b>+</b> New Sblog Post</a><?
	} else {
		if($num > 20 && !$_GET['show']) {
			$words = 'Showing 20 of '.$num.' Posts &middot; <a href="?show=all">Show all</a>';
			$query.= " LIMIT 20";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
		} else $words = $num.' Post'.($num > 1 ? 's' : '');
		?>
		
		<p><b class="arrow-toggle arrow-toggle-on"><?=$words?></b> &nbsp; <a href="manage.php?action=newpost" class="plus"><b>+</b> New Sblog Post</a></p>
		
		<table border="0" cellpadding="5" cellspacing="0" class="plain">
			<?
			while($row = mysqli_fetch_assoc($res)) {
				$subdir = "posts";
				if($row['category'] == "public") $subdir = "news";
				elseif($row['category'] == "blog") $subdir = "blogs";
		
				$typeimg = $row['attachment'];
				
				$date = substr($row['datetime'], 0, 10);
				$date = str_replace("-", "/", $date);
				
				?>
				<tr>
					<td nowrap="nowrap"><?=formatDate($row['datetime'], 6)?></td>
					<td><?=($row['post_type'] ? $row['post_type'] : '?')?></td>
					<td><div style="<?=($typeimg ? 'padding-right:24px; background:url(/bin/img/icons/news/'.$typeimg.'_sm.png) no-repeat right top;' : '')?>"><a href="/sblog/<?=$row['nid']?>"><?=$row['description']?></a></td>
					<td>
						<?
						if($row['pending']) echo '<span style="color:#888">Pending Approval</span>';
						elseif($row['category'] == "draft") echo '<span style="color:#D23535">Draft</span>';
						else echo '<span style="color:#31B047">'.$row['category'].'</span>';
						?>
					</td>
					<td><a href="?edit=<?=$row['nid']?>">EDIT</a></td>
				</tr>
				<?
			}
			?>
		</table>
		<?
	}
	$page->footer();
	exit;
}

if($_GET['autotag'] && $_GET['instruct'] == "pglink") {
	$in['type'] = "link";
	$warnings[] = "For your link to be included in the link list on the <i>".formatName($_GET['autotag'])."</i> page, please select <b>Public Post</b> and check <b>Archive Post</b> on Step 3 below.";
}

if($_GET['autotag'] && $_GET['instruct'] == "albumsynopsis") {
	$warnings[] = "For your post item to be considered as the featured synopsis on the album page, please select <b>Public Post</b> and check <b>Archive Post</b> on Step 3 below.";
}

if(!$in['post_type']) $in['post_type'] = "general";

$page->width = "fixed";
$page->header();

$page->openSection(array("id"=>"new-news"));

?>
<h1 style="float:left; margin:0; padding:0;"><?=($edid ? 'Edit Post <a href="/sblog/'.$edid.'">#'.$edid.'</a>' : 'New Post')?></h1>

<?
if(!$usrid) $page->kill('<br style="clear:both"/><big style="font-size:22px;">Please <b><a href="/login.php">Log in</a></b> to continue.</big><p style="font-size:14px;">Don\'t have an account? <b><a href="/register.php">Register</a></b> in about one minute.</p>');
?>

<div style="text-align:right; margin:0 0 0 400px">
	<span class="helpinfo"><span>?</span></span>
	<span style="color:#CCC">
		<a href="/sblog/1767/videogamin-sblog-faq">Sblog F.A.Q.</a> | 
		<a href="/sblog/1741/writing-a-better-sblog-article">Writing a better Sblog article</a> | 
		<a href="/markdown.php">Formatting your article</a>
	</span>
</div>

<div style="clear:both; height:20px;"></div>

<form method="post" id="NNform" onsubmit="return false;">
	<input type="hidden" name="in[session_id]" value="<?=$session_id?>"/>
	<input type="hidden" name="submit_form" value="1"/>
	<input type="hidden" name="post_action" value="<?=($edid ? 'edit' : 'add')?>" id="post_action"/>
	<?
	if($_GET['autotag']) echo '<input type="hidden" name="autotags[]" value="'.htmlSC($_GET['autotag']).'"/>';
	if($_POST['autotags']) {
		foreach($_POST['autotags'] as $tag) {
			echo '<input type="hidden" name="autotags[]" value="'.htmlSC($tag).'"/>';
		}
	}
	?>
	
	<div class="formcontainer">
		
		<div id="viewspace">
			
			<div id="inpcontent" class="viewspace view-edit">
				
				<input type="hidden" name="in[post_type]" value="<?=$in['post_type']?>" id="in-ttype"/>
				<div id="ttype" class="selection">
					<ul>
						<li><a href="#general" title="General Article" class="tooltip">General Post</a></li>
						<li><a href="#news" title="Gaming News" class="tooltip">News</a></li>
						<li><a href="#blog" title="Opinion / Blog post" class="tooltip">Blog</a></li>
						<li><a href="#playlog" title="Log a game play session and include play details" class="tooltip">Play Log</a></li>
						<li><a href="#review" title="Review of a game, music album, or pretty much anything" class="tooltip">Review</a></li>
						<li><a href="#preview" title="Preview or Impressions of a game, music album, or pretty much anything" class="tooltip">Preview</a></li>
						<li><a href="#quote" title="A quote" class="tooltip">Quote</a></li>
						<li class="null"><span id="choosettype">Choose a post type</span></li>
					</ul>
					<br style="clear:left;"/>
				</div>
				
				<?
				if(is_array($cont['rating'])){
					foreach($cont['rating'] as $key => $val){
						$cont['rating'] = $key;
						if($key == "scale") $cont['scale_rating'] = $val;
						elseif($key == "custom") $cont['custom_rating'] = $val;
						else $cont['rating'] = $val;
					}
				}
				$srbgpos = ($cont['scale_rating'] ? $cont['scale_rating'] : 0) * 16;
				?>
				<input type="hidden" name="cont[rating]" value="<?=$cont['rating']?>" id="in-rating"/>
				<input type="hidden" name="cont[scale_rating]" value="<?=$cont['scale_rating']?>" id="in-scaleval"/>
				<div id="ttype-review" class="ttype ttype-review selection" style="display:none">
					<ul>
						<li class="<?=(!$cont['rating'] ? 'on' : '')?>"><a href="#">No rating</a></li>
						<li class="<?=($cont['rating'] == "scale" ? 'on' : '')?>">
							<a href="#scale" title="star rating -- click to set!" id="star-rating" class="tooltip">
								<span style="background-position:0 -<?=$srbgpos?>px;"><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span></span>
							</a>
						</li>
						<li class="<?=($cont['rating'] == "thumbs_up" ? 'on' : '')?>"><a href="#thumbs_up" title="thumbs up" class="tooltip"><img src="/bin/img/icons/emoticons/_thumbs_up.png" border="0"/></a></li>
						<li class="<?=($cont['rating'] == "thumbs_down" ? 'on' : '')?>"><a href="#thumbs_down" title="thumbs down" class="tooltip"><img src="/bin/img/icons/emoticons/_thumbs_down.png" border="0"/></a></li>
						<li class="<?=($cont['rating'] == "heart" ? 'on' : '')?>"><a href="#heart" title="heart" class="tooltip"><img src="/bin/img/icons/emoticons/_heart.png" border="0"/></a></li>
						<li class="<?=($cont['rating'] == "poo" ? 'on' : '')?>"><a href="#poo" title="poo" class="tooltip"><img src="/bin/img/icons/emoticons/_poo.png" border="0"/></a></li>
						<li class="<?=($cont['rating'] == "custom" ? 'on' : '')?>">
							<a href="#custom" title="input your own words" class="tooltip">
								<span class="hideonfocus">input a custom rating</span>
								<input type="text" name="cont[custom_rating]" value="<?=htmlSC($cont['custom_rating'])?>" maxlength="25"/>
							</a>
						</li>
					</ul>
					<br style="clear:left;"/>
				</div>
				
				<div class="main">
				
					<!-- heading -->
					<div class="inpfw fftt" style="margin:20px 0 0">
						<input type="text" name="cont[heading]" value="<?=htmlSC($cont['heading'])?>" maxlength="80" id="inp-headline" title="Allowed formatting: bold, italic, strikethrough" class="ff big"/>
						<div class="tt">
							<span class="headline hl-default">Headline / Title (optional)</span>
							<span class="headline hl-news">Headline</span>
							<span class="headline hl-link">Headline / Title / Link Destination Name</span>
							<span class="headline hl-image">Title (optional)</span>
							<span class="headline hl-audio">Headline / Track Description</span>
							<span class="headline hl-video">Headline / Video Description</span>
							<span class="headline hl-quote">Post Description (optional)</span>
							<span class="headline hl-review">Review Title</span>
							<span class="headline hl-preview">Preview Title</span>
						</div>
					</div>
					
					<!-- text -->
					<div class="inpfw" style="margin:20px 0 0">
						<div id="inp-text-htmltoolbox" style="display:none;"><?=outputToolbox("inp-text", array("b","i","strikethrough","a","links","cite","img","spoiler","h1","h2","toc","more"), "use_bbcode")?></div>
						<textarea name="cont[text]" rows="2" id="inp-text" class="tagging"><?=$cont['text']?></textarea>
					</div>
					
					<!-- quote source -->
					<div id="ttype-quote" class="ttype ttype-quote inpfw fftt" style="margin:10px 0 0; <?=($in['post_type'] != "quote" ? 'display:none;' : '')?>">
						<textarea name="cont[quote_source]" rows="2" id="inp-quoter" class="tagging ff"><?=$cont['quote_source']?></textarea>
						<label class="tt" style="padding:7px; font-size:15px;">Quote source <a class="tooltip" title="The person attributed to the quote and/or the source of the quote. ie: &lt;code&gt;[[Square Enix]] president [[Yoichi Wada]] in a [1UP interview](http://1up.com/interview/1234/) [source=http://kotaku.com/article/1234]via Kotaku[/source]&lt;/code&gt;">?</a></label>
					</div>
					
					<!--subject-->
					<div class="ttype ttype-review ttype-preview ttype-playlog inpfw fftt" style="display:none; margin:20px 0 0">
						<input type="text" name="in[subject]" value="<?=htmlsc($in['subject'])?>" id="inp-subject" class="ff big"></textarea>
						<label class="tt">
							<span class="ttype ttype-review">What are you reviewing?</span>
							<span class="ttype ttype-preview">What are you previewing?</span>
							<span class="ttype ttype-playlog">What game did you play?</span>
						</label>
					</div>
					<div id="subject-details" class="ttype ttype-review ttype-preview ttype-playlog"></div>
					<div id="playlog" class="ttype ttype-playlog"></div>
				
				</div><!--.main-->
				
				<div id="appendcontent">
					
					<a class="close ximg" title="cancel append content" onclick="$.address.value('?type=')">close</a>
					
					<!--link-->
					<div class="forms form-link">
						<div class="inpfw">
							<input type="text" name="cont[link_url]" value="<?=($cont['link_url'] ? htmlSC($cont['link_url']) : 'http://')?>" maxlength="255" style="font-size:18px; font-family:Arial; color:blue; text-decoration:underline;" onfocus="if($(this).val()=='http://') $(this).val('');" onblur="if($(this).val()=='') $(this).val('http://');"/>
						</div>
					</div>
					
					<!--img-->
					<div class="forms form-image">
						<div id="image-items">
							<?
							$imgs = new imgs(); //start a new session
							$handler = base64_encode('sessid='.$GLOBALS['imgs']->sessid);
							$imgtmpl = '<div class="image-item" ondragenter="event.stopPropagation(); event.preventDefault();" ondragover="event.stopPropagation(); event.preventDefault();" ondrop="event.stopPropagation(); event.preventDefault(); nnHandleImageDrop(event, {parent_key:\'image-%I\', handler:\''.$handler.'\'});"><input type="hidden" name="cont[img_names][]" value="%s" id="image-%I-filename"/><img src="%s" id="image-%I-img" width="100" height="100"/><a href="%s" class="imgupl"></a><div class="blank" title="Drop an image file from your desktop here to upload"></div><strong>%s</strong><ul><li><span class="arrow-left" style="color:#888">Drop here</span></li><li><a data-imagei="%I" data-nav="upload" data-handler="'.$handler.'">Upload an image</a></li><li><a data-imagei="%I" data-nav="" data-handler="'.$handler.'">Browse uploads</a></li></ul><a class="rm ximg-small" style="top:3px; right:3px;">remove</a></div>';
							$i = 0;
							if($cont['img_names']){
								foreach($cont['img_names'] as $img_name){
									try{ $img = new img($img_name); }
									catch(Exception $e){ unset($img); continue; }
									$out = str_replace("%I", ++$i, $imgtmpl);
									$out = sprintf($out, $img_name, $img->src['tn'], $img->src['url'], ($img->img_title ? $img->img_title : $img_name));
									echo $out;
								}
							} else {
								$out = str_replace("%I", 1, $imgtmpl);
								$out = str_replace("%s", '', $out);
								echo $out;
							}
							?>
						</div>
						<div id="image-selectlayout" title="choose image layout">
							<input type="hidden" name="cont[img_layout]" value="<?=$cont['img_layout']?>"/>
							<?
							$layouts = array("2"=>array("1x1", "2"), "3"=>array("1x2", "2x1", "3"), "4"=>array("1x2x1", "1x3", "3x1", "2x2"), "5"=>array("1x2x2", "2x1x2", "2x3", "3x2"), "6"=>array("1x2x3", "2x2x2", "3x3"), "7"=>array("1x2x2x2", "1x3x3", "2x2x3", "2x3x2", "3x2x2"), "8"=>array("1x2x2x3", "1x3x2x2", "2x2x2x2", "2x3x3", "3x2x3", "3x3x2"), "9"=>array("1x2x3x3", "2x2x2x3", "3x2x2x2", "3x3x3"), "10"=>array("1x3x3x3", "2x2x3x3", "2x3x3x2", "3x2x2x3", "3x3x2x2"));
							foreach($layouts as $num => $layouts){
								foreach($layouts as $l){
									echo '<a class="l'.$num.($cont['img_layout'] == $l ? ' on' : '').'" data-layout="'.$l.'">';
									$cells = array();
									$cells = explode("x", $l);
									foreach($cells as $cell){
										echo '<div class="row width-'.$cell.'">';
										for($i=0; $i < $cell; $i++) echo '<span></span>';
										echo '</div>';
									}
									echo '</a>';
								}
							}
							?>
						</div>
						<div style="clear:left"></div>
						<button type="button" id="image-add"><b>+</b> Add another image</button>
						<?/*<div id="image-itemtemplate" style="display:none"><?=$imgtmpl?></div> //moving blank template to after <form> so submit doesnt catch it! */?>
					</div>
					
					<!--video-->
					<div class="forms form-video inpfw fftt">
						<textarea name="video_input" rows="2" id="inpembedcode" class="ff"><?
							if($cont['video_url']) echo $cont['video_url'];
							elseif($cont['video_embedcode']) echo $cont['video_embedcode'];
							else echo $_POST['video_input'];
							?></textarea>
						<label class="tt">Video URL or embed code <small>(ie. https://www.youtube.com/watch?v=FuX5_OWObA0)</small></label>
					</div>
					
					<!--audio-->
					<div class="forms form-audio">
						<input type="hidden" name="cont[audio_file]" value="<?=$cont['audio_file']?>" id="inp-audiofile"/>
						<?
						$audio_filename = '';
						if($cont['audio_file']){
							$audio_filename = substr($cont['audio_file'], strrpos($cont['audio_file'], "/"));
						}
						?>
						<div id="audiofile" style="<?=(!$audio_filename ? 'display:none;' : '')?>">
							Currently uploaded file: <b><a href="<?=$cont['audio_file']?>" target="_blank" id="audiofile-link"><?=$audio_filename?></a></b> [<a class="rm red">remove</a>]
						</div>
						<iframe src="uploadaudio.php" frameborder="0" scrolling="no" style="width:100%; height:24px; overflow:hidden;<?=($audio_filename ? 'display:none;' : '')?>"></iframe>
						
						<h4 style="margin:10px 0 5px !important;">Corresponding Album & Track: <a href="#help" class="preventdefault helpinfo tooltip" title="Although it's not necessary to tag a track (for example, if the track doesn't exist in the database yet), it is important for the sake of cataloging, organizing, and sharing."><span>?</span></a></h4>
						<?
						if($cont['audio_trackids']){
							?>
							<div style="margin:15px;">
								<ul>
									<?
									foreach($cont['audio_trackids'] as $tid) {
										if($tid != "") {
											$q = "SELECT track_name, disc, time, albumid, cid, title, subtitle FROM albums_tracks LEFT JOIN albums USING(albumid) WHERE albums_tracks.id='$tid' LIMIT 1";
											$tdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
											?>
											<li style="position:relative">
												<a href="/music/?id=<?=$tdat->albumid?>" target="_blank" class="arrow-link"><?=$tdat->title?> <i><?=$tdat->subtitle?></i></a>&nbsp;
												<?=$tdat->disc?>, 
												"<?=$tdat->track_name?>" &nbsp; 
												<a href="#remove" title="remove track association" class="preventdefault ximg-small" onclick="$(this).parent().remove();">x</a>
												<input type="hidden" name="cont[audio_trackids][]" value="<?=$tid?>"/>
											</li>
											<?
										}
									}
									?>
								</ul>
							</div>
							<?
						}
						
						?>
						<select name="cont[audio_albumid][]" class="selectalbum" style="font:normal 14px Arial; padding:2px;">
							<option value="">Loading albums&hellip;</option>
						</select>
						<div style="display:none; margin-top:6px;">
							<select name="cont[audio_trackids][]" style="font:normal 14px Arial; padding:2px;"></select> 
							<a href="#cancel" title="cancel track association selection" class="ximg preventdefault" style="margin:5px;" onclick="$(this).prev().html('').closest('div').hide().prev().val('');">x</a>
							<p><a href="#another" class="preventdefault" onclick="$(this).parent().hide().closest('div').next().show();">Tag another track</a> [<a href="#" class="tooltip preventdefault" title="Because sometimes a track might appear on multiple albums, of course!">why?</a>]</p>
						</div>
						
						<select name="cont[audio_albumid][]" class="selectalbum" style="display:none; margin-top:10px; font:normal 14px Arial; padding:2px;">
							<option value="">Loading albums&hellip;</option>
						</select>
						<div style="display:none; margin-top:6px;">
							<select name="cont[audio_trackids][]" style="font:normal 14px Arial; padding:2px;"></select> 
							<a href="#cancel" title="cancel track association selection" class="ximg preventdefault" style="margin:5px;" onclick="$(this).prev().html('').closest('div').hide().prev().val('');">x</a>
							<p><a href="#another" class="preventdefault" onclick="$(this).parent().hide().closest('div').next().show();">Tag another track</a></p>
						</div>
						
						<select name="cont[audio_albumid][]" class="selectalbum" style="display:none; margin-top:10px; font:normal 14px Arial; padding:2px;">
							<option value="">Loading albums&hellip;</option>
						</select>
						<div style="display:none; margin-top:6px;">
							<select name="cont[audio_trackids][]" style="font:normal 14px Arial; padding:2px;"></select> 
							<a href="#cancel" title="cancel track association selection" class="ximg preventdefault" style="margin:5px;" onclick="$(this).prev().html('').closest('div').hide().prev().val('');">x</a>
						</div>
					</div>
					
					<!--tweet-->
					<div class="forms form-tweet inpfw" style="margin-top:10px">
						<h4 style="margin:0 0 5px !important;">Tweet URL <small></small> <a href="https://twitter.com/EvilSharkey/status/187089472150708224" target="_blank" class="tooltip helpinfo" title="To embed a Tweet into your Sblog post, input the permanent link of a single Tweet, ie:&lt;br/&gt;https://twitter.com/EvilSharkey/status/187089472150708224"><span>?</span></a></h4>
						<input type="text" name="cont[tweet_url]" value="<?=($cont['tweet_url'] ? htmlSC($cont['tweet_url']) : 'http://')?>" maxlength="255" style="font-size:18px; font-family:Arial; color:blue; text-decoration:underline;" onfocus="if($(this).val()=='http://') $(this).val('');" onblur="if($(this).val()=='') $(this).val('http://');"/>
					</div>
					
				</div>
				
				<!-- content attachments -->
				<input type="hidden" name="in[attachment]" value="<?=$in['attachment']?>" id="in-attachment"/>
				<div id="selconttype" class="selconttype selection">
					<ul>
						<li><a href="#link" title="Add a link to a website or article"><span></span>Link</a></li>
						<li><a href="#image" title="Add Images, screenshots, artwork, photos"><span style="background-position:-18px 0;"></span>Picture</a></li>
						<li><a href="#video" title="Add a Video"><span style="background-position:-36px 0;"></span>Video</a></li>
						<li><a href="#audio" title="Add Audio"><span style="background-position:-54px 0;"></span>Audio</a></li>
						<li><a href="#tweet" title="Add a Twitter Tweet"><span style="background-position:-72px 0;"></span>Tweet</a></li>
					</ul>
				</div>
				
			</div>
			
			<div class="viewspace view-preview"></div>
			
			<div id="editswitcher" class="switch editswitcher">
				<div class="editswitcher-inset"></div>
				<div class="editswitcher-nub left" id="editswitcher-nub"></div>
				<a href="#edit" title="Edit" class="editswitcher-edit">Edit</a>
				<a href="#preview" title="Preview" class="editswitcher-view">Preview</a>
			</div>
			
		</div><!--#viewspace-->
		
		<div id="formfooter">
			
			<div style="height:30px"></div>
			
			<div id="postsection">
				<dl>
					<dt>Post to</dt>
					<!--draft-->
					<dd>
						<input type="radio" name="in[category]" value="draft" <?=($in['category'] == "draft" ? ' checked="checked"' : '')?> id="in-category-draft"/> 
						<label for="in-category-draft">Drafts</label>
						<p>Your drafts won't appear on indexes or post lists, but you can still view and develop your article, ask others to help work on it, and eventually publish it when it's ready.</p>
					</dd>
					<!--blog-->
					<dd>
						<input type="radio" name="in[category]" value="blog"<?=($in['category'] == "blog" ? ' checked="checked"' : '')?> id="in-category-blog"/> 
						<label for="in-category-blog">My Blog</label>
						<p>Post anything you want to your personal blog (<a href="/~<?=$usrname?>/blog" target="_blank">Videogam.in/~<?=$usrname?></a>). Your blog is never moderated by the editors, so you can write in any style or post stuff that you and your weird friends might find interesting.</p>
					</dd>
					<!--public-->
					<dd>
						<input type="radio" name="in[category]" value="public"<?=($in['category'] == "public" ? ' checked="checked"' : '')?> id="in-category-public"/> 
						<label for="in-category-public">Public Stream</label>
						<p>News, Content, and Reviews that the gaming community will find interesting or important. These posts see significantly more exposure than Blogs.</p>
						<p><span class="warn"></span>Public posts must meet our News and Content Guidelines, which you can read in the <a href="/sblog/1767/videogamin-sblog-faq">Sblog FAQ</a>.</p>
						<p><label><input type="checkbox" name="in[archive]" value="1"<?=($in['archive'] ? ' checked="checked"' : '')?>/> <b>Archive Post</b></label> <a href="#" class="tooltip helpinfo" title="This is supplementary content, send it directly to the related database archives (game pages, people pages, etc.). Use this option when posting supplementary content that isn't newsworthy."><span>?</span></a></p>
					</dd>
				</dl>
				<ul style="display:none">
					<li class="red"><a href="#draft">Draft</a></li>
					<li class="yellow"><a href="#blog">Blog</a></li>
					<li class="green"><a href="#public">Public</a></li>
					<li class="null"><span>Choose post visibility</span></li>
				</ul>
			</div>
			
			<!--share opts-->
			<div id="postshare">
				<?
				$query = "SELECT * FROM users_oauth WHERE usrid='$usrid'";
				$res = mysqli_query($GLOBALS['db']['link'], $query);
				$oauth = array();
				while($row = mysqli_fetch_assoc($res)) $oauth[$row['oauth_provider']] = $row;
				?>
				<dl>
					<dt>Share</dt>
					<dd><input type="checkbox" name="in[share][facebook]" value="facebook" id="share-facebook" <?=(!in_array("facebook", array_keys($oauth)) ? 'disabled="disabled"' : '')?>/> <label for="share-facebook">Post on my Facebook timeline</label> <a onclick="nnInitOauth('facebook')">Authorize Facebook access</a></dd>
					<dd><input type="checkbox" name="in[share][twitter]" value="twitter" id="share-twitter" <?=(!in_array("twitter", array_keys($oauth)) ? 'disabled="disabled"' : '')?>/> <label for="share-twitter">Tweet<?=($oauth['twitter'] ? ' <span class="a">@'.$oauth['twitter']['oauth_username'].'</span>' : '')?></label> <a onclick="nnInitOauth('facebook')">Authorize Twitter access</a></dd>
					<?=($_SESSION['user_rank'] >= 8 ? '<dd><label><input type="checkbox" name="in[share][twitter_site]" value="twitter_site"/> Tweet <span class="a">@Videogamin</span></label></dd>' : '')?>
				</dl>
			</div>
			
			<div style="clear:both"></div>
			<div class="hr"></div>
			
			<div id="postopts">
				
				<!--poll-->
				<h4><a href="#" class="arrow-toggle">Add a Poll</a></h4>
				<div style="display:none; margin:0 0 0 15px; max-width:500px;">
					
					<?
					$q = "SELECT * FROM posts_polls WHERE nid='$postdat[nid]' LIMIT 1";
					if($poll = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) {
						?>
						There is already a poll in session. <label><input type="checkbox" name="poll[closed]" value="1"<?=($poll['closed'] ? ' checked="checked"' : '')?>/> Close poll</label>
						<?
					} else {
						
						?>
						<div class="warn" style="font-size:15px; color:#666;">Once you submit this post, these options can't be changed.</div>
						
						<div class="inpfw" style="margin:10px 0 0">
							<input type="text" name="poll[question]" value="<?=htmlsc($_POST['poll']['question'])?>" maxlength="255" placeholder="Poll question"/>
						</div>
						
						<div class="inpfw" style="margin:10px 0 0">
							<textarea name="poll[opts]" rows="6" wrap="off" placeholder="Answer options (one option per line; 12 options maximum)" style="font-family:Arial; font-size:12px;"><?=$_POST['poll']['opts']?></textarea>
						</div>
						
						<div style="margin:10px 0 0">
							<select name="poll[answer_type]">
								<option value="single">Single answer: voter can choose only one option</option>
								<option value="multiple">Multiple answer: voter can choose multiple options</option>
							</select>
						</div>
						
						<div class="hr"></div>
						<?
						
					}
					?>
					
				</div>
				
				<h4><a href="#adv_opts" class="arrow-toggle">Advanced Options</a></h4>
				<dl style="display:none; margin:0 0 0 15px;">
					
					<dt>Privacy & Access</dt>
					<dd>
						<select name="in[privacy]" style="padding:2px; font-size:14px;">
							<option value="public"<?=($in['privacy'] == "public" || !$in['privacy'] ? ' selected="selected"' : '')?>>Public &mdash; anyone can see it</option>
							<option value="private"<?=($in['privacy'] == "private" ? ' selected="selected"' : '')?>>Private &mdash; anyone with the link can see it</option>
							<?
							$query = "SELECT name, name_url, g.group_id FROM groups_members gm LEFT JOIN groups g USING (group_id) WHERE gm.usrid='$usrid' ORDER BY name";
							$res   = mysqli_query($GLOBALS['db']['link'], $query);
							if(mysqli_num_rows($res)) {
								?>
								<optgroup label="Group &mdash; only members of a group can see it">
									<?
									while($row = mysqli_fetch_assoc($res)) {
										echo '<option value="group:'.$row['group_id'].'"'.($in['privacy'] == "group:".$row['group_id'] ? ' selected="selected"' : '').'> '.$row['name'].'</option>';
									}
									?>
								</optgroup>
								<?
							}
							?>
						</select>
					</dd>
					<dd>
						<label><input type="checkbox" name="in[archive]" value="1" <?=($in['archive'] ? 'checked="checked"' : '')?>/> <b>Archive this post</b> &ndash; Bypass the home page and send this post directly to the related indexes and archives.</label>
					</dd>
					
					<?
					$temp = "temp-".$session_id;
					if($in['permalink'] == $temp) unset($in['permalink']);
					if($in['description'] == $temp) unset($in['description']);
					
					$posted = strtotime($postdat['datetime']);
					$hour = strtotime("-1 hour");
					if(!$postdat || $_SESSION['user_rank'] == 9 || $posted > $hour) {
						?>
						<dt>Permanent Link</dt>
						<dd>
							<span style="font:normal 12px monospace;">
								http://videogam.in/sblog/<?=($nid ? $nid : '123456')?>/<input type="text" name="in[permalink]" value="<?=$in['permalink']?>" size="60" maxlength="100" id="inp-permalink" style="margin:0 !important; padding:0; font:normal 12px monospace; border-width:0 0 1px 0 !important; border-color:#999; background-color:transparent;"/>
							</span>
						</dd>
						<?
					}
					?>
					
					<dt>Post Description <a href="#" class="preventdefault tooltip helpinfo" title="A short description of the post that will go on post lists, RSS feeds, etc. Examples: 'Final Fantasy XVI screenshots', 'A quote from Shigeru Miyamoto'"><span>?</span></a></dt>
					<dd><textarea name="in[description]" rows="2" cols="50"><?=$in['description']?></textarea></dd>
					
					<dt>Commenting</dt>
					<dd>
						<label><input type="radio" name="in[options][comments]" value="" <?=($in['options']['comments'] != "disabled" ? 'checked="checked"' : '')?>/> Anybody can comment</label>
					</dd>
					<dd>
						<label><input type="radio" name="in[options][comments]" value="disabled" <?=($in['options']['comments'] == "disabled" ? 'checked="checked"' : '')?>/> Nobody can comment</label>
					</dd>
					
					<dt>Tagging</dt>
					<dd>
						<label><input type="checkbox" name="in[options][tagging]" value="disabled" <?=($in['options']['tagging'] == "disabled" ? 'checked="checked"' : '')?>/> No tag suggestions</label>
						<a href="#" class="tooltip helpinfo preventdefault" title="By default, ranking users can offer additional tags for your post in order to better categorize it and create more links and exposure. Checking this box will limit tagging to only yourself and administrators."><span>?</span></a>
					</dd>
					
					<?
					if($_SESSION['user_rank'] >= 8) {
						if($in['options']['access']) $opt_access[$in['options']['access']] = 'checked="checked"';
						else $opt_access['default'] = 'checked="checked"';
						?>
						<dt>Editing Permissions</dt>
						<dd><label><input type="radio" name="in[options][access]" value="" <?=$opt_access['default']?>/> The Author, Mid- and Top-level Admins (default)</label></option></dd>
						<dd><label><input type="radio" name="in[options][access]" value="8" <?=$opt_access['8']?>/> Mid- and Top-level Admins only</label></dd>
						<dd><label><input type="radio" name="in[options][access]" value="9" <?=$opt_access[9]?>/> Top-level Admins only</label></dd>
						<?
					}
					?>
					
				</dl>
				
				<?
				if($_SESSION['user_rank'] >= 8){
					?>
					<h4><a href="#postdata" class="preventdefault arrow-toggle">Post Data</a></h4>
					<dl style="display:none">
						<?
						foreach($in as $k => $v){
							echo '<dt>'.$k.'</dt><dd style="white-space:pre-wrap;">';
							if(is_array($v)) print_r($v);
							else echo htmlentities($v);
							echo '</dd>';
						}
						?>
					</dl>
					<?
				}
				?>
				
			</div>
			
			<?
			if($postdat && $usrid != $postdat['usrid']){
				?>
				<div class="hr"></div>
				<h4>Edit/Moderation Notes</h4>
				<div style="margin:3px 0 5px; color:#666;">
					<span class="arrow-right">&nbsp;</span>&nbsp;
					Briefly summarize your edits, making clear your intention and purpose for editing.<br/>
					<span class="arrow-right">&nbsp;</span>&nbsp;
					Mention specifically what was edited and why, and give suggestions for improvement.
				</div>
				<textarea name="pe[comments]" rows="4" cols="80"></textarea> 
				<div style="margin-top:3px;"><label><input type="checkbox" name="pe[email]" value="<?=$postdat['usrid']?>"/> Notify the author (<?=outputUser($postdat['usrid'], FALSE, FALSE)?>) by e-mail of this edit</label></div>
				<?
			}
			?>
			
			<div style="height:20px"></div>
			
			<div class="buttons">
				<span class="redborder" style="float:right;">
					<input type="button" value="Delete Post" onclick="if(confirm('Permanently delete this post and all saved drafts?')) document.location='manage.php?delete=<?=$session_id?>';"/>
				</span>
				<input type="button" name="submit_form" value="Submit" onclick="nnSubmitForm()" style="font-weight:bold;"/> 
				<input type="button" value="Save Draft" onclick="saveDraft();" id="savedraftbutton"/> &nbsp; 
				<span id="draftmsg" class="draftmsg"></span> 
			</div>
			
		</div><!--#formfooter-->
		
	</div><!-- .formcontainer -->
	
</form>

<div id="image-itemtemplate" style="display:none"><?=$imgtmpl?></div>

<iframe name="draftspace" style="display:none"></iframe>

<?
$page->footer();
?>