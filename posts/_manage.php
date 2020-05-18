<?
use Vgsite\Page;
$page = new Page();
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.posts.php");
$posts = new posts;
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.tags.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/htmltoolbox.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/contribute.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.forums.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");

$page->title = "Videogam.in Post Management";
$page->javascripts[] = "/posts/posts_form.js";
$page->css[] = "/posts/posts_form.css";

//delete
if($del = $_GET['delete']) {
	$q = "SELECT * FROM posts WHERE session_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $del)."' LIMIT 1";
	$in = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
	if(!$in) $errors[] = "That post doesn't exist in the database; Either we couldn't find it or it hasn't been saved yet.";
	else {
		//user has access?
		if($_SESSION['user_rank'] <= 7 && $in['usrid'] != $usrid) $page->kill("<h1>Error</h1>You don't have access to edit this item.");
		if(strstr($in['options'], "access_8") && $_SESSION['user_rank'] < 8) $page->kill("<h1>Error</h1>This item is locked and can't be edited.");
		if(strstr($in['options'], "access_9") && $_SESSION['user_rank'] < 9) $page->kill("<h1>Error</h1>This item is locked and can't be edited.");
		
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
			
			//remove media
			if($in['type'] == "gallery" || $in['type'] == "image") {
				/* DELETE FROM MEDIA DB HERE!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! */
			}
			
			unset($in);
		}
	}
}

if($_POST['in'])  $in = $_POST['in'];
if($_GET['edit']) $edid = $_GET['edit'];
else $newpost = TRUE;

if(!$in && $edid) {
	//the user just navigated here in order to edit a post
	$page->title.= " / Edit Post";
	$q = "SELECT * FROM posts WHERE nid = '".$edid."' LIMIT 1";
	$in = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
	if(!$in) $page->kill("<h2>Error</h2>Couldn't get data for given news ID (".$edid.").");
	$in['sessid'] = $in['session_id'];
	$in = array_merge($in, $posts->splitData($in['type'], $in['content'])); //get the actual post content
	$opts = array();
	$opts = explode(" ", $in['options']);
	$in['options'] = $opts;
	unset($opts);
	//user has access?
	if($_SESSION['user_rank'] <= 7 && $in['usrid'] != $usrid) $page->kill("<h1>Error</h1>You don't have access to edit this item.");
	if(strstr($in['options'], "access_8") && $_SESSION['user_rank'] < 8) $page->kill("<h1>Error</h1>This item is locked and can't be edited.");
	if(strstr($in['options'], "access_9") && $_SESSION['user_rank'] < 9) $page->kill("<h1>Error</h1>This item is locked and can't be edited.");
} elseif($_GET['action'] == "newpost") {
	$page->title.= " / New Post";
} elseif(!$in && !$edid) {
	
	// Management Index 
	
	$page->header();
	
	?>
	<h1>Post Management</h1>
	
	<p><big><a href="manage.php?action=newpost" class="plus"><b>+</b> Post Something New</a></big></p>
	
	<?
	$query = "SELECT * FROM posts WHERE usrid='$usrid' ORDER BY datetime DESC";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	if(!$num = mysqli_num_rows($res)) {
		?>You have no posts to manage!<?
	} else {
		if($num > 20 && !$_GET['show']) {
			$words = 'Showing 20 of '.$num.' Posts &middot; <a href="?show=all">Show all</a>';
			$query.= " LIMIT 20";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
		} else $words = $num.' Post'.($num > 1 ? 's' : '');
		?>
		
		<p><b class="arrow-toggle arrow-toggle-on"><?=$words?></b></p>
		
		<table border="0" cellpadding="5" cellspacing="0" class="plain">
			<tr>
				<th>Date</th>
				<th>Category</th>
				<th>Description</th>
				<th>Status</th>
				<th>&nbsp;</th>
			</tr>
			<?
			while($row = mysqli_fetch_assoc($res)) {
				$subdir = "posts";
				if($row['category'] == "public") $subdir = "news";
				elseif($row['category'] == "blog") $subdir = "blogs";
		
				$typeimg = $row['type'];
				if($typeimg == "gallery") $typeimg = "image";
				
				$date = substr($row['datetime'], 0, 10);
				$date = str_replace("-", "/", $date);
				
				?>
				<tr>
					<td nowrap="nowrap"><?=formatDate($row['datetime'], 6)?></td>
					<td><?=$row['category']?></td>
					<td style="padding-left:24px; background:url(/bin/img/icons/news/<?=$typeimg?>_sm.png) no-repeat 3px 4px;"><a href="/<?=$subdir?>/<?=$date?>/<?=$row['permalink']?>"><?=$row['description']?></a></td>
					<td>
						<?
						if(!$row['unpublished'] && !$row['pending']) echo '<span style="color:#31B047">Published</span>';
						elseif($row['pending']) echo '<span style="color:#888">Pending Approval</span>';
						elseif($row['unpublished']) echo '<span style="color:#D23535">Not Published</span>';
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

if(!$in['options']) $in['options'] = array();
if(!$in['type']) $in['type'] = ($_GET['type'] ? $_GET['type'] : "text");
$catg = ($in['category'] ? $in['category'] : "");

if($in['sessid']) {
	$sessid = $in['sessid'];
	$q = "SELECT * FROM posts WHERE session_id = '$sessid' LIMIT 1";
	$postdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
}else {
	$sessid = date("YmdHis").sprintf("%07d",$usrid);
	$in['newpost'] = 1;
}

if($_POST) require("process.php");

if($in['type'] == "gallery") $in['type'] = "image";

$autotag_fields = "";
if($_GET['autotag']) $autotag_fields.= '<input type="hidden" name="autotags[]" value="'.htmlSC($_GET['autotag']).'"/>';
if($_POST['autotags']) {
	foreach($_POST['autotags'] as $tag) {
		$autotag_fields.= '<input type="hidden" name="autotags[]" value="'.htmlSC($tag).'"/>';
	}
}

if($_GET['autotag'] && $_GET['instruct'] == "pglink") {
	$in['type'] = "link";
	$warnings[] = "For your link to be included in the link list on the <i>".formatName($_GET['autotag'])."</i> page, please select <b>Public Post</b> and check <b>Archive Post</b> on Step 3 below.";
}

if($_GET['autotag'] && $_GET['instruct'] == "albumsynopsis") {
	$warnings[] = "For your post item to be considered as the featured synopsis on the album page, please select <b>Public Post</b> and check <b>Archive Post</b> on Step 3 below.";
}

$page->width = "fixed";
$page->header();

$page->openSection(array("id"=>"new-news"));

?>
<h1><span style="padding-left:36px; background:url(/bin/img/icons/add.png) no-repeat left center;"><?=($edid ? 'Edit a Post' : 'New Post')?></span></h1>

<?
if(!$usrid) $page->kill('<big style="font-size:22px;">Please <b><a href="/login.php">Log in</a></b> to continue.</big><p style="font-size:14px;">Don\'t have an account? <b><a href="/register.php">Register</a></b> in about one minute.</p>');
?>

<form action="manage.php<?=($edid ? '?edit='.$edid : '?action=newpost')?>" method="post" enctype="multipart/form-data" id="NNform" class="new" name="NNmg">
	<input type="hidden" name="in[type]" value="<?=$in['type']?>" id="inp-type"/>
	<input type="hidden" name="in[sessid]" value="<?=$sessid?>"/>
	<input type="hidden" name="in[newpost]" value="<?=$in['newpost']?>"/>
	<input type="hidden" name="submit_form" value="1"/>
	<input type="hidden" name="post_action" value="<?=($edid ? 'edit' : 'add')?>" id="post_action"/>
	<input type="hidden" name="submit_action" value=""/>
	<?=$autotag_fields?>
	
	<ul>
		<li><?=($edid ? 'You are editing <a href="/posts/?id='.$edid.'">Post #'.$edid.'</a>' : '<big style="font-size:15px;">Before posting your first article, please read <a href="/posts/2010/03/30/videogamin-sblog-faq">The Videogam.in Sblog F.A.Q.</a> and <a href="/posts/2010/03/01/writing-a-better-sblog-article">Writing a better Sblog article</a></big>')?></li>
		<li>Experiment with this form: 
			<label>
				<input type="checkbox" name="in[unpublished]" value="1"<?=($in['unpublished'] ? ' checked="checked"' : '')?> style="margin:-1px 0 0; vertical-align:middle;"/> 
				Use <b>Sandbox Mode</b> <a href="#help" class="tooltip helpinfo" title="Checking this box will prevent this post from going to publication; It won't appear on indexes or post lists, but you can still view and develop your article, ask others to help work on it, and eventually publish it when it's ready."><span>?</span></a>
			</label>
		</li>
	</ul>
	
	<div class="formbody">
		
		<div id="formtable" class="formtable">
			
			<div id="selconttype" class="selection">
				<ul>
					<li>
						<a href="#text" title="A text article (news, review, preview, opinion)" class="tooltip" style="background-image:url(/bin/img/icons/news/text_sm.png);">Text</a>
					</li>
					<li>
						<a href="#quote" title="A short, single quote" class="tooltip" style="background-image:url(/bin/img/icons/news/quote_sm.png);">Quote</a>
					</li>
					<li>
						<a href="#link" title="A link to a website or article" class="tooltip" style="background-image:url(/bin/img/icons/news/link_sm.png);">Link</a>
					</li>
					<li>
						<a href="#image" title="Images, screenshots, artwork, photos" class="tooltip" style="background-image:url(/bin/img/icons/news/image_sm.png);">Picture</a>
					</li>
					<li>
						<a href="#video" title="Video" class="tooltip" style="background-image:url(/bin/img/icons/news/video_sm.png);">Video</a>
					</li>
					<li>
						<a href="#audio" title="Audio" class="tooltip" style="background-image:url(/bin/img/icons/news/audio_sm.png);">Audio</a>
					</li>
				</ul>
			</div>
			
			<div id="inpform">
				
				<!--<a href="#" class="arrow-toggle preventdefault" onclick="$(this).toggleClass('arrow-toggle-on').next().toggle();">How to choose this option</a>
				<div style="display:none; margin:5px 0; padding:10px 15px; background-color:white; font-size:13px; color:black; line-height:1.5em; opacity:.6;">
					In choosing this option, consider:
						<ul>
							<li>The <b>object</b>, or main focus of your post</li>
							<li>How you would like your post to be <b>displayed</b></li>
							<li>Your post's <b>headline</b> or <b>description</b></li>
						</ul>
					<p>Post headlines usually reveal the main focus of a post. For example, "First look at new Zelda" = <b>image</b>; "Resident Evil 5: Gold Edition details" = <b>text article</b> (even if you have a totally amazing picture to go with it).</p>
					<p>Choose the option that will best support your post as a whole. Don't pick an option based on its features; Pick the one that best describes your object.</p>
				</div>-->
				
				<!--<span class="arrow-right"></span>&nbsp;
				Your object: <b>
					<span class="forms form-text">
						A text article, including a news post, review, preview, or opinion
					</span>
					<span class="forms form-quote">
						A short, single quote
					</span>
					<span class="forms form-link">
						A link to a website or outside article
					</span>
					<span class="forms form-image">
						Images, screenshots, artwork, or photos
					</span>
					<span class="forms form-video">
						A video
					</span>
					<span class="forms form-audio">
						Audio
					</span>
				</b>-->
				
				<!--video-->
				<?
				if($_GET['video_url']) {
					$in['video_url'] = urldecode($_GET['video_url']);
					if(substr($in['video_url'], 0, 7) != "http://") {
						$errors[] = "The video URL must be a http:// address.";
						unset($in['video_url']);
					}
				}
				?>
				<div class="forms form-video">
					
					<div class="hintguy" style="margin:10px 0;">
						<big><b><a href="http://youtube.com">YouTube</a></b> offers the best <acronym title="application programming interface">API</acronym> and is very friendly to sites like this one in giving information and media.</big><br/>
						Many content providers post their content on YouTube in addition to their own sites.
					</div>
					
					<h4 style="margin:10px 0 6px !important;">Video URL: <a href="#" class="tooltip helpinfo preventdefault" title="The URL where the video is located. It's always best to get this not from your browser's toolbar, but from the video's Share console."><span>?</span></a></h4>
					<div class="inpfw">
						<input type="text" name="in[video_url]" value="<?=($in['video_url'] ? htmlSC($in['video_url']) : 'http://')?>" tabindex="1" id="inpvidurl" maxlength="100" style="font-size:18px; font-family:Arial; color:blue; text-decoration:underline;" onfocus="if($(this).val()=='http://') $(this).val('');" onblur="if($(this).val()=='') $(this).val('http://');"/>
					</div>
					
					<div style="margin:10px 0 0;">
						<div style="float:left; padding:2px; background-color:#8AC5FF;"><input type="button" id="getVideoCode" value="Fetch"/></div>
						<span style="line-height:2em;">&nbsp;&nbsp;&nbsp;Attempt to fill in below fields automatically (works best with YouTube)</span>
						<br style="clear:both;"/>
					</div>
					
					<h4 style="margin:10px 0 6px !important;">Embed Code:</h4>
					<div class="inpfw">
						<textarea name="in[video_code]" rows="4" tabindex="3" id="inpembedcode"><?=$in['video_code']?></textarea>
					</div>
					
					<fieldset style="margin:10px 0 0;">
						<legend style="font-size:16px;">Video Thumbnail</legend>
						Your video won't be embedded into post lists, but you can elect to post a thumbnail instead, which is <b>highly recommended</b>.
						<dl>
							<dt>Current Thumbnail:</dt>
							<dd><img src="<?=($in['video_thumbnail'] ? $in['video_thumbnail'] : "/bin/img/video_tn.png")?>" alt="Your video thumbnail" id="video_thumbnail_src"/></dd>
							<dd><span class="redborder"><input type="button" value="Remove" onclick="$('#video_thumbnail_src').attr('src', '/bin/img/video_tn.png'); $('#in_video_thumbnail').val('');"/></span>
							
							<dt>Upload Thumbnail:</dt>
							<dd>Upload any image file larger than 200 x 200 pixels and in JPG, GIF, or PNG format.</dd>
							<dd><input type="file" name="video_tn"/></dd>
							
						</dl>
					</fieldset>
					<input type="hidden" name="in[video_thumbnail]" value="<?=$in['video_thumbnail']?>" id="in_video_thumbnail"/>
				</div>
				
				<!-- text -->
				<?
				if(!$in['scale_value']) {
					if(substr($in['rating'], 0, 5) == "scale") {
						$in['scale_value'] = substr($in['rating'], -1);
						$in['rating'] = "scale";
					} else $in['scale_value'] = 0;
				}
				$srbgpos = $in['scale_value'] * 16;
				if(substr($in['rating'], 0, 6) == "custom") {
					$in['custom_rating'] = substr($in['rating'], 7);
					$in['rating'] = "custom";
				}
				?>
				<input type="hidden" name="in[text_type]" value="<?=$in['text_type']?>" id="in-ttype"/>
				<div class="forms form-text" style="margin:10px 0 0;">
					<div id="ttype" class="selection">
						<ul>
							<li><a href="#article" title="General Article" class="tooltip<?=($in['text_type'] == "article" ? ' on' : '')?>">Article</a></li>
							<li><a href="#news" title="Gaming News" class="tooltip<?=($in['text_type'] == "news" ? ' on' : '')?>">News</a></li>
							<li><a href="#review" title="Review a game, music album, or pretty much anything" class="tooltip<?=($in['text_type'] == "review" ? ' on' : '')?>">Review</a></li>
							<li><a href="#preview" title="Preview a game, music album, or pretty much anything" class="tooltip<?=($in['text_type'] == "preview" ? ' on' : '')?>">Preview / Impressions</a></li>
							<?=(!$in['text_type'] ? '<li><div id="choosettype" style="position:relative; background-color:black; color:white; border-width:0;">Choose one<span style="position:absolute; top:0; left:-3px; width:3px; height:26px; background:url(/bin/img/speech_point_black_vertical.png) no-repeat left center;"></span></div></li>' : '')?>
						</ul>
						<br style="clear:left;"/>
					</div>
					
					<input type="hidden" name="in[rating]" value="<?=$in['rating']?>" id="in-rating"/>
					<input type="hidden" name="in[scale_value]" value="<?=$in['scale_value']?>" id="in-scaleval"/>
					<div id="ttype-review" class="ttype selection" style="<?=($in['text_type'] != "review" ? 'display:none;' : '')?>">
						<ul>
							<li><a href="#"<?=(!$in['rating'] ? ' class="on"' : '')?>>No rating</a></li>
							<li>
								<a href="#scale" title="star rating -- click to set!" id="star-rating" class="tooltip<?=($in['rating'] == "scale" ? ' on' : '')?>">
									<span style="background-position:0 -<?=$srbgpos?>px;"><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span></span>
								</a>
							</li>
							<li><a href="#thumbs_up" title="thumbs up" class="tooltip<?=($in['rating'] == "thumbs_up" ? ' on' : '')?>"><img src="/bin/img/icons/emoticons/_thumbs_up.png" border="0"/></a></li>
							<li><a href="#thumbs_down" title="thumbs down" class="tooltip<?=($in['rating'] == "thumbs_down" ? ' on' : '')?>"><img src="/bin/img/icons/emoticons/_thumbs_down.png" border="0"/></a></li>
							<li><a href="#heart" title="heart" class="tooltip<?=($in['rating'] == "heart" ? ' on' : '')?>"><img src="/bin/img/icons/emoticons/_heart.png" border="0"/></a></li>
							<li><a href="#poo" title="poo" class="tooltip<?=($in['rating'] == "poo" ? ' on' : '')?>"><img src="/bin/img/icons/emoticons/_poo.png" border="0"/></a></li>
							<li>
								<a href="#custom" title="input your own words" class="tooltip<?=($in['rating'] == "custom" ? ' on' : '')?>">
									<span class="hideonfocus">input a custom rating</span>
									<input type="text" name="in[custom_rating]" value="<?=htmlSC($in['custom_rating'])?>" maxlength="25"/>
								</a>
							</li>
						</ul>
						<br style="clear:left;"/>
					</div>
				</div>
				
				<div class="clear"></div>
				
				<!-- HEADLINE -->
				<div style="margin-top:10px;">
					<div class="inpfw fftt forms form-text form-link form-image form-video form-audio">
						<input type="text" name="in[heading]" value="<?=htmlSC($in['heading'])?>" maxlength="160" tabindex="4" id="inp-headline" class="ff" style="font:normal 18px arial;"/>
						<div class="tt" style="padding:7px; font-size:15px;">
							<span class="forms form-text">Headline / Title</span>
							<span class="forms form-link">Title / Destination Name</span>
							<span class="forms form-image">Title (recommended)</span>
							<span class="forms form-audio">Headline / Track Description</span>
							<span class="forms form-video">Headline / Video Description</span>
						</div>
					</div>
				</div>
				
				<!--link-->
				<div class="forms form-link" style="margin-top:15px">
					<div class="inpfw">
						<input type="text" name="in[link_url]" value="<?=($in['link_url'] ? htmlSC($in['link_url']) : 'http://')?>" maxlength="255" tabindex="5" style="font-size:18px; font-family:Arial; color:blue; text-decoration:underline;" onfocus="if($(this).val()=='http://') $(this).val('');" onblur="if($(this).val()=='') $(this).val('http://');"/>
					</div>
				</div>
				
				<!--img-->
				<div class="forms form-image">
					
				</div>
				
				<!--audio-->
				<div class="forms form-audio" style="margin:15px 0 20px;">
					<h4 style="margin-bottom:5px !important;">Source File:</h4>
					<?=($in['file'] ? '<div style="margin:0 0 5px;">Currently uploaded file: <b><a href="'.$in['file'].'" target="_blank">'.$in['file'].'</a></b></div>' : '')?>
					
					<input type="hidden" name="in[file]" value="<?=$in['file']?>"/>
					<input type="hidden" name="MAX_FILE_SIZE" value="7340032"/>
					<input type="file" name="audio_upload" tabindex="5"/>&nbsp;&nbsp;<span style="color:#666">MP3 files only; Size limit is 7MB</span>
					
					<table border="0" cellpadding="0" cellspacing="0" style="margin-top:5px;">
						<tr>
							<td valign="top" style="background:url(/bin/img/arrow-down-right.png) no-repeat 5px 3px; padding-left:16px;">
								This is a&nbsp;
							</td>
							<td>
								<label><input type="radio" name="in[audio_type]" value="full"<?=($in['audio_type'] != "sample" ? ' checked="checked"' : '')?> tabindex="6"/> full audio track</label>
								<p><label><input type="radio" name="in[audio_type]" value="sample" tabindex="6"/> partial or sample audio track</label></p>
							</td>
						</tr>
					</table>
					<br/><br/>
					
					<h4 style="margin-bottom:5px !important;">Corresponding Album & Track: <a href="#help" class="preventdefault helpinfo tooltip" title="Although it's not necessary to tag a track (for example, if the track doesn't exist in the database yet), it is important for the sake of cataloging, organizing, and sharing."><span>?</span></a></h4>
					<?
					
					if($in['audio_trackid']) {
						?>
						<fieldset style="margin:10px 0;">
							<legend>Currently tagged tracks</legend>
							<ul>
								<?
								foreach($in['audio_trackid'] as $tid) {
									if($in['audio_trackid'] != "") {
										$q = "SELECT track_name, disc, time, albumid, cid, title, subtitle FROM albums_tracks LEFT JOIN albums USING(albumid) WHERE albums_tracks.id='$tid' LIMIT 1";
										$tdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
										?>
										<li>
											<a href="/music/?id=<?=$tdat->albumid?>" target="_blank" class="arrow-link"><?=$tdat->title?> <i><?=$tdat->subtitle?></i></a>&nbsp;
											<?=$tdat->disc?>, 
											"<?=$tdat->track_name?>" &nbsp; 
											<a href="#remove" title="de-tag this track" class="preventdefault ximg" onclick="$(this).parent().remove();">x</a>
											<input type="hidden" name="in[audio_trackid][]" value="<?=$tid?>"/>
										</li>
										<?
									}
								}
								?>
							</ul>
						</fieldset>
						<?
					}
					
					$query = "SELECT albumid, cid, title, subtitle FROM albums WHERE `view` = '1' ORDER BY title";
					$res   = mysqli_query($GLOBALS['db']['link'], $query);
					while($row = mysqli_fetch_assoc($res)) {
						//check and see if there's corresponding tracks first
						$q = "SELECT * FROM albums_tracks WHERE albumid = '".$row['albumid']."' LIMIT 1";
						if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $albumopts.= '<option value="'.$row['albumid'].'">'.$row['title'].' '.$row['subtitle'].' ('.$row['cid'].')</option>';
					}
					?>
					<select name="in[audio_albumid][]" class="selectalbum" tabindex="7" style="font:normal 14px Arial; padding:2px;">
						<option value="">Select album&hellip;</option>
						<?=$albumopts?>
					</select>
					<div style="display:none; margin-top:6px;">
						<select name="in[audio_trackid][]" style="font:normal 14px Arial; padding:2px;"></select> 
						<a href="#cancel" title="cancel track association selection" class="ximg preventdefault" style="margin:5px;" onclick="$(this).prev().html('').closest('div').hide().prev().val('');">x</a>
						<p><a href="#another" class="preventdefault" onclick="$(this).closest('div').next().show();">Tag another track</a> [<a href="#" class="tooltip preventdefault" title="Because sometimes a track might appear on multiple albums, of course!">why?</a>]</p>
					</div>
					
					<select name="in[audio_albumid][]" class="selectalbum" style="display:none; margin-top:10px; font:normal 14px Arial; padding:2px;">
						<option value="">Select album&hellip;</option>
						<?=$albumopts?>
					</select>
					<div style="display:none; margin-top:6px;">
						<select name="in[audio_trackid][]" style="font:normal 14px Arial; padding:2px;"></select> 
						<a href="#cancel" title="cancel track association selection" class="ximg preventdefault" style="margin:5px;" onclick="$(this).prev().html('').closest('div').hide().prev().val('');">x</a>
						<p><a href="#another" class="preventdefault" onclick="$(this).closest('div').next().show();">Tag another track</a></p>
					</div>
					
					<select name="in[audio_albumid][]" class="selectalbum" style="display:none; margin-top:10px; font:normal 14px Arial; padding:2px;">
						<option value="">Select album&hellip;</option>
						<?=$albumopts?>
					</select>
					<div style="display:none; margin-top:6px;">
						<select name="in[audio_trackid][]" style="font:normal 14px Arial; padding:2px;"></select> 
						<a href="#cancel" title="cancel track association selection" class="ximg preventdefault" style="margin:5px;" onclick="$(this).prev().html('').closest('div').hide().prev().val('');">x</a>
					</div>
				</div>
				
				<div class="forms form-text" style="margin:12px 0 0;">
					<div id="form-text-toolbox" style="display:none;">
						<?=outputToolbox("inp-text", array("b","i","strikethrough","a","blockquote","cite","links","img"), "use_bbcode")?>
					</div>
				</div>
				<div class="forms form-link form-image form-video form-audio">
					<h4 style="margin-bottom:6px !important;" class="forms form-image form-video form-audio">Supplementary Text: <span style="color:#AAA">(optional)</span> <a href="#" title="Give some additional information, links, or descriptions in this field.&lt;br/&gt;If applicable, please give credit to you source by utilizing the [source/] tag in the below field." class="tooltip helpinfo"><span>?</span></a></h4>
					<h4 style="margin-bottom:6px !important;" class="forms form-link">Link Description: <span style="color:#AAA">(optional)</span></h4>
					<div id="stexttoolbox" style="display:none;"><?=outputToolbox("inp-text", array("b","i","strikethrough","a","links","img"), "use_bbcode")?></div>
				</div>
				
				<div class="forms form-quote hintguy" style="margin:10px 0;">
					If you have more than one quote, post an article using a few <a href="/bbcode.htm" target="_blank" class="arrow-link"><code>[quote]</code> tags</a> instead.
				</div>
				
				<div class="inpfw">
					<textarea name="in[text]" rows="2" tabindex="8" id="inp-text" style="height:<?=($in['type'] == "text" || $in['type'] == "quote" ? '80px' : '34px')?>;"><?=$in['text']?></textarea>
				</div>
				
				<!--text-->
				<div class="forms form-text">
					<ul style="line-height:1.5em;">
						<li>Split a long article into an introduction and a jump article with the <code>&lt;!--more--&gt;</code> tag. 
							<a href="#_example" class="arrow-toggle example-link">more info</a>
							<div class="example">On post lists, there is a limited space alloted for article text. Long posts (over 1000 characters or so) will be split up automatically and possibly abruptly. Using this tag gives you control over where the article is split, as long as it is inserted within the first 1000 characters.</div>
						</li>
						<li style="display:none;">Split the article into multiple pages with the <code>&lt;!--page--&gt;</code> code. 
							<a href="#_example" class="arrow-toggle example-link">example</a>
							<div class="example">This text will be the first page<br/>&lt;!--page--&gt;<br/>This the second<br/>&lt;!--page--&gt;<br/>This the third<br/>&lt;!--page--&gt;<br/>And so on...</div>
						</li>
						<li>Cluster your text into sections and subsections with the <code>[h5]</code> and <code>[h6]</code> heading tags. 
							<a href="#_example" class="arrow-toggle example-link">more info</a>
							<div class="example"><a href="/bbcode.htm#headings" target="_blank" class="arrow-link">See an example</a><br/>Tip: If using heading tags, insert an automatically-created table of contents with the <code>&lt;!--toc--&gt;</code> tag. This should probably go at the very top of the text, after a brief introduction, and definitely after the &lt;!--more--&gt; tag).</div>
						</li>
					</ul>
				</div>
				
				<!--quote-->
				<div class="forms form-quote">
					<table border="0" cellpadding="0" cellspacing="5" width="100%">
						<tr>
							<td style="text-align:center; vertical-align:middle;">
								<span style="font:normal 29px 'arial black',arial;">&mdash;</span>
							</td>
							<td style="vertical-align:top;">
								<textarea name="in[quoter]" rows="5" cols="38" tabindex="9" title="Quoter (who said the above quote)" id="inp-quoter"><?=$in['quoter']?></textarea>
							</td>
							<td style="vertical-align:top; padding:3px 0 0 3px;">
								<?=outputToolbox("inp-quoter", array("b","i","strikethrough","a","links","cite"), "use_bbcode")?>
								Use BB Code to link to the quoter and/or the source of the quote. For example:<br/>
								<code style="font-size:12px; font-family:monospace; color:#666;">
									[[Square Enix]] president [[Yoichi Wada]] in a [url=http://1up.com/interview/1234/]1UP interview[/url][source=http://kotaku.com/article/1234]via Kotaku[/source]</code>
							</td>
						</tr>
					</table>
				</div>
				
			</div><!-- #inpform -->
			
			<dl id="postsection">
				<!--blog-->
				<dt>
					<label><input type="radio" name="in[category]" value="blog"<?=($catg == "blog" ? ' checked="checked"' : '')?>/>Blog</label> 
					<i style="color:#666; font-weight:normal;">Unmoderated, Low Exposure</i>
				</dt>
				<dd style="<?=($catg != "blog" && $catg != "" ? 'display:none;' : '')?>">
					Post anything you want to your personal blog (<a href="/~<?=$usrname?>/blog" target="_blank">Videogam.in/~<?=$usrname?></a>). Your blog is never moderated by the editors, so you can write in any style or post stuff that you and your weird friends might find interesting.
				</dd>
				
				<!--public-->
				<dt>
					<label><input type="radio" name="in[category]" value="public"<?=($catg == "public" ? ' checked="checked"' : '')?>/>Public Post</label> 
					<i style="color:#666; font-weight:normal;">Moderated, High Exposure</i>
				</dt>
				<dd style="display:none;">
					News, Content, and Reviews that the gaming community will find interesting or important. These posts see significantly more exposure than Blogs.
					<p></p>
					<span class="warn"></span>Public posts must meet our News and Content Guidelines, which you can read in the <a href="/posts/2010/03/30/videogamin-sblog-faq">Sblog FAQ</a>.
					<p></p>
					<label><input type="checkbox" name="in[archive]" value="1"<?=($in['archive'] ? ' checked="checked"' : '')?>/> <b>Archive Post</b></label> &mdash this is supplementary content, send it directly to the related archives (game pages, people pages, etc.). <a href="#" class="tooltip helpinfo" title="Use this option when posting supplementary content that isn't newsworthy, like photographs of somebody, a non-news archive link, an album summary, etc."><span>?</span></a>
				</dd>
				
				<!--forum-->
				<dt style="display:none;">
					<label><input type="radio" name="in[category]" value="forum"<?=($catg == "forum" ? ' checked="checked"' : '')?>/>Forum Discussion</label> 
					<i style="color:#777; font-weight:normal;">Semi-moderated, Medium Exposure</i>
				</dt>
				<dd style="display:none;">
					Create a new forum thread with this information. Your thread is subject to the moderation of the forum administrators, just as a regular post is.
					<p></p>
					<fieldset style="display:inline;">
						<legend>Forum Category</legend>
						<?
						$query = "SELECT * FROM forums WHERE no_index != '1' AND invisible <= '$_SESSION['user_rank']' ORDER BY cid, title";
						$res   = mysqli_query($GLOBALS['db']['link'], $query);
						$i = 0;
						$ch = ($in['fid'] ? $in['fid'] : 1);
						while($row = mysqli_fetch_assoc($res)) {
							$i++;
							$d = strip_tags($row['description']);
							if(strlen($d) > 125) $d = substr($d, 0, 123)."&hellip;";
							echo '<p><label><input type="radio" name="in[fid]" value="'.$row['fid'].'"'.($ch == $row['fid'] ? ' checked="checked"' : '').'/> '.$row['title'].'</label> <span style="color:#888;">&mdash; '.$d.'</span></p>';
						}
						?>
					</fieldset>
				</dd>
				
			</dl>
			
			<div class="hr"></div>
			
			<div id="postopts">
				
				<!-- heading img -->
				<h4 id="uplhimgformspace"><a href="#" class="arrow-toggle" onclick="$('#uplhimgform').slideToggle();">Upload a Heading Image</a></h4>
				<div style="display:none; margin:0 0 10px 20px;">
					
					<?
					$imgsel = "";
					if(substr($in['img'], 0, 3) == "tn_"){
						$imgtnsrc = "/posts/img/".$in['img'];
						$imgsel = "tn";
					} else $imgtnsrc = "/bin/img/icons/question_block_med.png";
					if(substr($in['img'], 0, 3) == "ls_"){
						$imglssrc = "/posts/img/".$in['img'];
						$imgsel = "ls";
					} else $imglssrc = "/bin/img/icons/question_block_med.png";
					?>
					
					<big style="font-size:16px;">Include a pretty picture to beautify your article (<span class="warn">doesn't work with Video and Image posts</span>)</big>
					<br/>
					
					<dl>
						<dt>Step 1: <a href="#launchUploadForm" class="preventdefault arrow-link" onclick="$('#uplhimgform').fadeIn();">Upload an image</a></dt>
						
						<dt>Step 2: Choose an Image</dt>
						<dd id="imgdisp" style="<?=(!$in['img'] ? 'display:none;' : '')?>">
							<input type="hidden" name="in[img]" value="<?=$in['img']?>" id="inp-img"/>
							<a href="#imgno" title="No image" class="tooltip<?=(!$imgsel ? ' on' : '')?>"><img src="/bin/img/icons/none_med.png" alt="none" border="0"/></a>
							<a href="#imgtn" title="Thumbnail (80x80)" class="tooltip<?=($imgsel == "tn" ? ' on' : '')?>"><img src="<?=$imgtnsrc?>" border="0" id="imgdisptn" rel="imgsel"/></a>
							<a href="#imgls" title="Landscape (620x250)" class="tooltip<?=($imgsel == "ls" ? ' on' : '')?>"><img src="<?=$imglssrc?>" border="0" id="imgdispls" rel="imgsel"/></a>
							<br style="clear:left;"/>
						</dd>
					</dl>
					
					<div style="margin:20px 0; border-width:1px 0; border-style:solid; border-color:#AAA transparent white;"></div>
					
				</div>
				
				<h4><a href="#" class="arrow-toggle">Add a Poll</a></h4>
				<div style="display:none; margin:0 0 0 15px;">
					
					<?
					$q = "SELECT * FROM posts_polls WHERE nid='$postdat->nid' LIMIT 1";
					if($poll = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) {
						?>
						There is already a poll in session.
						<p><label><input type="checkbox" name="poll[closed]" value="1"<?=($poll['closed'] ? ' checked="checked"' : '')?>/> Close poll</label></p>
						<?
					} else {
						
						?>
						<table border="0" cellpadding="5" cellspacing="0">
							<tr>
								<td colspan="2">
									<div class="warn" style="font-size:15px; color:#666;">Once you submit this post, these options can't be changed. HTML and BB Code aren't allowed in these fields.</div>
								</td>
							</tr>
							<tr>
								<th>Poll question:</th>
								<td><input type="text" name="poll[question]" value="<?=htmlsc($_POST['poll']['question'])?>" size="95" maxlength="255"/></td>
							</tr>
							<tr>
								<th>Answer options:</th>
								<td>
									Put one option per line; 12 options maximum.
									<div style="margin:5px 0 0;"></div>
									<textarea name="poll[opts]" rows="6" cols="70"><?=$_POST['poll']['opts']?></textarea>
								</td>
							</tr>
							<tr>
								<th>Answer type:</th>
								<td>
									<label><input type="radio" name="poll[answer_type]" value="single" checked="checked"/> Single: voter can choose only one option</label><br/>
									<label><input type="radio" name="poll[answer_type]" value="multiple"/> Multiple: voter can choose multiple options</label>
								</td>
							</tr>
						</table>
						<?
						
					}
					?>
					
					<div style="margin:20px 0; border-width:1px 0; border-style:solid; border-color:#AAA transparent white;"></div>
					
				</div>
				
				<h4><a href="#adv_opts" class="arrow-toggle">Advanced Options</a></h4>
				<dl style="display:none; margin:0 0 0 15px;">
					
					<dt>Privacy & Access</dt>
					<dd>
						<select name="in[privacy]" style="padding:2px; font-size:14px;">
							<option value="public"<?=($in['privacy'] == "public" || !$in['privacy'] ? ' selected="selected"' : '')?>>Public &mdash; anyone can see it</option>
							<option value="private"<?=($in['privacy'] == "private" ? ' selected="selected"' : '')?>>Private &mdash; only my friends can see it</option>
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
						<label><input type="checkbox" name="in[no_home]" value="1" <?=($in['no_home'] ? 'checked="checked"' : '')?>/> <b>Bypass the home page</b>, sending this post directly to the indexes and archives.</label>
					</dd>
					
					<?
					$temp = "temp-".$sessid;
					if($in['permalink'] == $temp) unset($in['permalink']);
					if($in['description'] == $temp) unset($in['description']);
					
					$posted = strtotime($postdat->datetime);
					$hour = strtotime("-1 hour");
					if(!$postdat || $_SESSION['user_rank'] == 9 || $posted > $hour) {
						?>
						<dt>Permanent Link</dt>
						<dd>
							<span style="font:normal 12px monospace;">
								http://videogam.in/posts/<?=date("Y/m/d")?>/<input type="text" name="in[permalink]" value="<?=$in['permalink']?>" size="60" maxlength="100" id="inp-permalink" style="margin:0 !important; padding:0; font:normal 12px monospace; border-width:0 0 1px 0 !important; border-color:#999; background-color:transparent;"/>
							</span>
						</dd>
						<?
					}
					?>
					
					<dt>Post Description <a href="#" class="preventdefault tooltip helpinfo" title="A short description of the post that will go on post lists, RSS feeds, etc. Examples: 'Final Fantasy XVI screenshots', 'A quote from Shigeru Miyamoto'"><span>?</span></a></dt>
					<dd><textarea name="in[description]" rows="2" cols="50"><?=$in['description']?></textarea></dd>
					
					<dt>Commenting</dt>
						<?
						if(in_array("comments_disabled", $in['options'])) $commno = 'checked="checked"';
						else  $commok = 'checked="checked"';
						?>
					<dd>
						<label><input type="radio" name="in[options][comments]" value="" <?=$commok?>/> Anybody can comment</label>
					</dd>
					<dd>
						<label><input type="radio" name="in[options][comments]" value="comments_disabled" <?=$commno?>/> Nobody can comment</label>
					</dd>
					
					<dt>Tagging</dt>
					<dd>
						<label><input type="checkbox" name="in[options][tagging]" value="no_tagging"<?=(in_array("no_tagging", $in['options']) ? ' checked="checked"' : '')?>/> No tag suggestions</label>
						<a href="#" class="tooltip helpinfo preventdefault" title="By default, ranking users can offer additional tags for your post in order to better categorize it and create more links and exposure. Checking this box will limit tagging to only yourself and administrators."><span>?</span></a>
					</dd>
					
					<?
					if($_SESSION['user_rank'] >= 8) {
						if(in_array("access_8", $in['options'])) $acc8 = 'checked="checked"';
						elseif(in_array("access_9", $in['options'])) $acc9 = 'checked="checked"';
						else $accdef = 'checked="checked"';
						?>
						<dt>Editing Permissions</dt>
						<dd><label><input type="radio" name="in[options][access]" value="" <?=$accdef?>/> The Author, Level 8-9 Admins (default)</label></option></dd>
						<dd><label><input type="radio" name="in[options][access]" value="access_8" <?=$acc8?>/> Level 8-9 Admins</label></dd>
						<dd><label><input type="radio" name="in[options][access]" value="access_9" <?=$acc9?>/> Level 9 Admins</label></dd>
						<?
					}
					?>
					
				</dl>
				
			</div>
			
			<?
			if($postdat && $usrid != $postdat->usrid){
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
				<div style="margin-top:3px;"><label><input type="checkbox" name="pe[email]" value="<?=$postdat->usrid?>"/> Notify the author (<?=outputUser($postdat->usrid, FALSE, FALSE)?>) by e-mail of this edit</label></div>
				<?
			}
			?>

		</div>
		<br/>
		
		<div class="buttons">
			<span class="redborder" style="float:right;">
				<input type="button" value="Delete Post" tabindex="18" onclick="if(confirm('Permanently delete this post and all saved drafts?')) document.location='manage.php?delete=<?=$sessid?>';"/>
			</span>
			<input type="submit" name="submit_form" value="Submit" tabindex="15" onclick="confirm_exit=false;" style="font-weight:bold;"/> 
			<input type="button" value="Preview" tabindex="16" onclick="NNpreview();"/> 
			<?=(!$postdat->last_edited ? '<input type="button" value="Save Draft" tabindex="17" onclick="saveDraft();" id="savedraftbutton"/>' : '')?> &nbsp; 
			<span id="draftmsg" class="draftmsg"></span> 
		</div>
		
	</div><!-- .formbody -->
	
</form>

<!-- upload image form -->
<form action="uploadimg.php" method="post" enctype="multipart/form-data" target="acceptupl-1" name="uplimgform" id="uplimgform" class="" onsubmit="return NNuploadimg();">
	<input type="hidden" name="action" value="submimg"/>
	<input type="hidden" name="sessid" value="<?=$sessid?>"/>
	<input type="hidden" name="parentframe" value="upl-1" id="inpparentframe"/>
	<h4 style="margin-bottom:3px;">Upload up to 50 images in <i>JPG</i>, <i>GIF</i>, or <i>PNG</i> format each under 3 mb in size.</h4>
	<label><span class="arrow-right"></span>&nbsp;&nbsp;Upload: <input type="file" name="upl" id="inpimgupl" class="inpimgfile"/></label><br/>
	<label><span class="arrow-right"></span>&nbsp;&nbsp;or input image URL: <input type="text" name="url" value="http://" size="60" id="inpimgurl" class="inpimgfile" style="padding:0; font:normal 13px Arial; color:blue; border-width:0 0 1px; border-style:solid; border-color:#CCC; background-color:transparent;" onfocus="if($(this).val()=='http://') $(this).val('');"/></label>
	<p></p>
	<input type="button" value="Submit" onclick="NNuploadimg()"/> &nbsp;&nbsp; 
	<span class="loading" style="display:none">Buffering upload queue</span>
</form>

<!-- upload heading image form -->
<form action="uploadhimg.php" method="post" enctype="multipart/form-data" target="acceptupl-himg" name="uplhimgform" id="uplhimgform">
	<input type="hidden" name="sessid" value="<?=$sessid?>"/>
	<input type="hidden" name="parentframe" value="upl-1" id="inpparentframe"/>
	<h5>Upload a Heading Image</h5>
	<p>Your pic will be cropped into two different sizes: A <b>80 &times; 80</b> thumbnail and, if it's big enough, a larger <b>620 &times; 250 max</b> landscape image.</p>
	<p><i>Tip!</i> If you want your image to retain a pixely complexion, resize or crop to exactly either one of the above ratios using an image editor like <a href="http://pixlr.com" target="_blank" class="arrow-link">Pixlr</a>.</p>
	<p><input type="file" name="upl" id="inphimgupl" class="inphimgfile" onchange="return NNuploadhimg($(this).val());"/> &nbsp;
	or input image URL: <input type="text" name="url" value="http://" size="20" id="inphimgurl" class="inphimgfile" style="color:#06C; border-color:#DDD !important; background-color:#DDD; text-decoration:underline;" onfocus="if($(this).val()=='http://') $(this).val('');" onchange="return NNuploadhimg($(this).val());"/> &nbsp; 
	<input type="button" value="Upload"/> &nbsp; 
<span class="loading" style="display:none;">Uploading...</span></p>
</form>
<iframe name="acceptupl-himg" frameborder="0" style="display:none;"></iframe>

<iframe name="draftspace" style="display:none"></iframe>

<?
$page->footer();
?>