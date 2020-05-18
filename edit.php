<?
use Vgsite\Page;
$page = new Page();
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.news.php");
$news = new news;$page->javascript="";
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/htmltoolbox.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/contribute.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.forum.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");

if($_POST) require("edit-process.php");

if(!$nid = $_GET['nid']) dieFullpage("No news id given", "incl header");

$q = "SELECT * FROM news WHERE nid='$nid' LIMIT 1";
if(!$n = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) dieFullpage("Couldn't get data for news id # $nid", "head");

if($_SESSION['user_rank'] <= 7 && $n['usrid'] != $usrid) dieFullpage("You don't have access to edit this item.");

$page->title = "Videogam.in / News / Edit Item / ".htmlSC($n['description']);
$page->style[] = "/bin/css/news.css";
$page->javascript.= <<<EOF
	<script type="text/javascript" src="/bin/script/news.js"></script>
	<script type="text/javascript" src="/bin/script/news-form.js"></script>
EOF;

$page->header();

$on[$n['type']] = "on";

if(!$in) {
	$arr = explode("|--|", $n['content']);
	if($n['type'] == "text") {
		$in['text']['heading'] = $arr[0];
		$in['text']['text'] = $arr[1];
		$in['text']['expanded_text'] = $arr[2];
	} elseif($n['type'] == "quote") {
		$in['quote']['quote'] = $arr[2];
		$in['quote']['quoter'] = $arr[3];
	} elseif($n['type'] == "link") {
		$in['link']['heading'] = $arr[0];
		$in['link']['text'] = $arr[1];
		$in['link']['url'] = $arr[2];
	}
		
}

if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/img/news/headingimg_$nid.png")) $has_hi = TRUE;

?>

<div id="new-news">
<div class="h">
	<h2>Post a New Item</h2>
	<ul class="tabbed-nav">
		<li class="<?=$on['text']?>"><a href="#" rel="text">Text</a></li>
		<li class="<?=$on['quote']?>"><a href="#" rel="quote">Quote</a></li>
		<li class="<?=$on['link']?>"><a href="#" rel="link">Link</a></li>
		<li class="<?=$on['image']?>"><a href="#" rel="image">Image</a></li>
		<li class="<?=$on['gallery']?>"><a href="#" rel="gallery">Gallery</a></li>
		<li class="<?=$on['video']?>"><a href="#" rel="video">Video</a></li>
		<li class="<?=$on['audio']?>"><a href="#" rel="audio">Audio</a></li>
	</ul>
</div>
<br style="clear:both"/>

<div id="initial-message" style="font-size:21px; color:#666;<?=($in ? ' display:none;' : '')?>">Post something new to the <a href="/news/">public news roll</a>, <a href="/groups/">a group</a>, <a href="/~<?=$usrname?>/blog">your personal blog</a>, or all three.</div>

<form action="new.php" method="post" enctype="multipart/form-data" onsubmit="return checkNNform();">
	<input type="hidden" name="in[type]" value="<?=$n['type']?>" id="inp-type"/>
	<input type="hidden" name="in[nid]" value="<?=$nid?>"/>
	
	<?=($_SESSION['user_rank'] >= 6 ? 
	"You can use XHTML since you're an admin, but BB Code is preferred." : 
	"All HTML will be depreciated; Please use BB Code for special formatting.")?> 
	<a class="arrow-link" href="#_bbcode" onclick="window.open('/bbcode.htm','markup_guide_window','width=620,height=510,scrollbars=yes');">BB Code Guide</a>
	
	<div id="form-text" class="forms"<?=($n['type'] != "text" ? ' style="display:none"' : '')?>>
		<table border="0" cellpadding="0" cellspacing="0" class="styled-form">
			<tr>
				<th>Heading</th>
				<td><input type="text" name="in[text][heading]" value="<?=htmlSC($in['text']['heading'])?>" size="80" maxlength="255" tabindex="6" style="font:normal 21px arial;"/></td>
			</tr>
			<tr>
				<th>Text</th>
				<td>
					As a general rule of thumb, a text item should be no longer than the text input box below. If it is, consider an expanded article.
					<p>
						<?=outputToolbox("inp-text-heading-text", array("b","i","strikethrough","a","links","blockquote"), "use_bbcode")?>
						<textarea name="in[text][text]" rows="7" cols="85" maxlength="1000" tabindex="7" id="inp-text-heading-text"><?=$in['text']['text']?></textarea>
					</p>
					<p><a href="#_headline_img" class="arrow-toggle<?=($has_hi ? ' arrow-toggle-on' : '')?>" onclick="$(this).toggleClass('arrow-toggle-on').parent().next().slideToggle();">Headline Image</a></p>
					<div style="<?=(!$has_hi ? 'display:none' : '')?>">
						<p>
							<img src="/bin/img/news/headingimg_<?=$nid?>.png" alt="Headline img" style="float:left; margin:3px 10px 0 0;"/>
							<label><input type="checkbox" name="in[delete_headingimg]" value="1"/> Delete this image</label><br/>
							<p>Upload a new accompanying headline image <span style="color:#888;">(JPG, GIF, or PNG format; Image will be resized to 100 x 100 pixels.)</span>:
							<input type="file" name="headline_img_text"/></p>
						</p>
					</div>
					<p><a href="#_expanded_article" class="arrow-toggle<?=($in['text']['expanded_text'] ? ' arrow-toggle-on' : '')?>" onclick="$(this).toggleClass('arrow-toggle-on').parent().next().toggle();">Expanded Article</a></p>
					<div style="<?=(!$in['text']['expanded_text'] ? 'display:none' : '')?>">
						<p>Input the initial introductory heading text above and the expanded article text below.</p>
						<p>
							<?=outputToolbox("inp-text-exp-text", array("b","i","strikethrough","a","links","h5","h6","blockquote","cite","ol","ul","image","gallery"), "use_bbcode")?>
							<textarea name="in[text][expanded_text]" rows="12" cols="85" maxlength="10000" id="inp-text-exp-text"><?=$in['text']['expanded_text']?></textarea>
						</p>
						<p><fieldset style="position:relative; background-color:#F5F5F5;">
							<legend>Expanded Article Formatting</legend>
							<ul style="list-style-type:square">
								<li>Split the article into multiple pages with the <code>[page]</code> code. <a href="#_example" class="arrow-toggle example-link">example</a></li>
								<li class="example">This text will be the first page<br/>[page]<br/>This the second<br/>[page]<br/>This the third<br/>[page]<br/>And so on...</li>
								<li>Cluster your text into sections with the <code>[h5]</code> and <code>[h6]</code> heading tags. <a href="#_example" class="arrow-toggle example-link">example</a></li>
								<li class="example">[h5]Characters[/h5]<br/>There are many characters in this game!<br/>[h6]Main Characters[/h6]<br/>*Bob<br/>*Dick<br/>*Tom<br/>[h6]Supporting Characters[/h6]<br/>*John<br/>*Jane<br/><br/>[h5]Story[/h5]<br/>The story is really interesting!<br/><br/>[h5]Conclusion[/h5]<br/>This game is going to be great!</li>
								<li>If using heading tags, insert an automatically-created table of contents with the <code>[toc]</code> tag (this should probably go at the very top of the text or after a brief introduction).</li>
							</ul>
						</fieldset></p>
					</div>
				</td>
			</tr>
		</table>
	</div>
	
	<div id="form-quote" class="forms"<?=($n['type'] != "quote" ? ' style="display:none"' : '')?>>
		<p class="bullet"><b style="color:#888">TIP</b> If you have more than one quote, post a text item using a few [quote] tags.</p>
		<table border="0" cellpadding="0" cellspacing="5">
			<tr>
				<td valign="top"><span style="font:normal 50px 'arial black',arial; vertical-align:top; line-height:45px;">&ldquo;</span></td>
				<td colspan="2">
					<textarea name="in[quote][quote]" rows="6" cols="95" tabindex="6"><?=$in['quote']['quote']?></textarea>
					<span style="font:normal 50px 'arial black',arial; vertical-align:top; line-height:45px;">&rdquo;</span>
				</td>
			</tr>
			<tr>
				<td style="text-align:center"><span style="font:normal 30px 'arial black',arial;">&ndash;</span></td>
				<td style="vertical-align:top;"><textarea name="in[quote][quoter]" rows="4" cols="38" tabindex="7" id="inp-quoter"><?=$in['quote']['quoter']?></textarea></td>
				<td style="vertical-align:top; padding:3px 0 0 3px;">
					<?=outputToolbox("inp-quoter", array("b","i","strikethrough","a","links"), "use_bbcode")?>
					Use BB Code to link to the quoter and/or the source of the quote. IE:<br/>
					<code style="font-size:12px; font-family:monospace; color:#666;">Square Enix president [person]Yoichi Wada[/person] in a [url=http://1up.com/interview/1234/]1UP interview[/url]</code>
				</td>
			</tr>
		</table>
	</div>
	
	<div id="form-link" class="forms"<?=($n['type'] != "link" ? ' style="display:none"' : '')?>>
		<table border="0" cellpadding="0" cellspacing="0" class="styled-form">
			<tr>
				<th>URL</th>
				<td><input type="text" name="in[link][url]" value="<?=htmlSC($in['link']['url'])?>" size="100" tabindex="5" style="color:blue; text-decoration:underline;" onfocus="if($(this).val()=='http://') $(this).val('');"/></td>
			</tr>
			<tr>
				<th>Link Text</th>
				<td><input type="text" name="in[link][heading]" value="<?=htmlSC($in['link']['heading'])?>" size="100" tabindex="6"/></td>
			</tr>
			<tr>
				<th>Supplementary Text<br/><small>Optional</small></th>
				<td>
					<?=outputToolbox("inp-link-text", array("b","i","strikethrough","a","links"), "use_bbcode")?>
					<textarea name="in[link][text]" rows="4" cols="90" tabindex="7" id="inp-link-text"><?=$in['link']['text']?></textarea>
					<p><a href="#_headline_img" class="arrow-toggle<?=($has_hi ? ' arrow-toggle-on' : '')?>" onclick="$(this).toggleClass('arrow-toggle-on').parent().next().slideToggle();">Headline Image</a></p>
					<div style="<?=(!$has_hi ? 'display:none' : '')?>">
						<p>
							<img src="/bin/img/news/headingimg_<?=$nid?>.png" alt="Headline img" style="float:left; margin:3px 10px 0 0;"/>
							<label><input type="checkbox" name="in[delete_headingimg]" value="1"/> Delete this image</label><br/>
							<p>Upload a new accompanying headline image <span style="color:#888;">(JPG, GIF, or PNG format; Image will be resized to 100 x 100 pixels.)</span>:
							<input type="file" name="headline_img_text"/></p>
						</p>
					</div>
				</td>
			</tr>
		</table>
	</div>
	
	<div id="form-image" class="forms"<?=($n['type'] != "image" ? ' style="display:none"' : '')?>>
		<table border="0" cellpadding="0" cellspacing="0" class="styled-form">
			<tr>
				<th>Heading<br/><small>Optional</small></th>
				<td><input type="text" name="in[image][heading]" value="<?=htmlSC($in['image']['heading'])?>" size="80" maxlength="100" tabindex="5" id="img-heading"/></td>
			</tr>
			<tr>
				<th>Supplementary Text<br/><small>Optional</small></th>
				<td>
					<?=outputToolbox("inp-image-text", array("b","i","strikethrough","a","links"), "use_bbcode")?>
					<textarea name="in[image][text]" rows="4" cols="90" tabindex="6" id="inp-image-text"><?=$in['image']['text']?></textarea>
				</td>
			</tr>
			<tr>
				<th>Upload Image</th>
				<td><iframe src="new-process.php?action=upload_img&sessid=<?=$sessid?>" frameborder="0" style="width:700px; height:25px;"></iframe></td>
			</tr>
			<tr>
				<th>Caption <a href="javascript:void(0)" class="tooltip" title="[REQUIRED] A description of the image; invisible text that appears when the user mouse-overs the image"><span class="block">?</span></a></th>
				<td><input type="text" name="in[image][caption]" value="<?=htmlSC($in['image']['caption'])?>" size="80" maxlength="100" tabindex="7" onfocus="if($(this).val()=='') { $(this).val($('#img-heading').val()) };"/></td>
			</tr>
		</table>
	</div>
	
	<div id="form-gallery" class="forms"<?=($n['type'] != "gallery" ? ' style="display:none"' : '')?>>
		<table border="0" cellpadding="0" cellspacing="0" class="styled-form">
			<tr>
				<th>Heading<br/><small>Optional</small></th>
				<td><input type="text" name="in[gallery][heading]" value="<?=htmlSC($in['gallery']['heading'])?>" size="100" maxlength="100" tabindex="3"/></td>
			</tr>
			<tr>
				<th>Supplementary Text<br/><small>Optional</small></th>
				<td>
					<?=outputToolbox("inp-gallery-text", array("b","i","strikethrough","a","links"), "use_bbcode")?>
					<textarea name="in[gallery][text]" rows="4" cols="90" tabindex="4" id="inp-gallery-text"><?=$in['gallery']['text']?></textarea>
				</td>
			</tr>
			<tr>
				<th>Image Source</th>
				<td>
					<label><input type="radio" name="in[gallery][source]" value="dir" checked="checked"/> Existing media directory</label>
					<div>
						<p>
							<div class="media-sel" style="padding-left:37px; color:#666; background:url(/bin/img/arrow-down-right.png) no-repeat 26px 50%;">
								http://videogam.in/
								<select name="in[gallery][dir]" tabindex="5" style="border:1px solid #CCC;" onchange="$(this).next().attr('href','/media.php?dir='+$(this).val());">
									<optgroup label="My Uploads">
										<?
										$query = "SELECT * FROM media_categories";
										$res   = mysqli_query($GLOBALS['db']['link'], $query);
										while($row = mysqli_fetch_assoc($res)) {
											$mcat[$row['category_id']] = $row['category'];
										}
										$query = "SELECT * FROM media ORDER BY directory";
										$res   = mysqli_query($GLOBALS['db']['link'], $query);
										$i = 0;
										$j = 0;
										while($row = mysqli_fetch_assoc($res)) {
											$i++;
											if($in['gallery']['dir']) $x = $in['gallery']['dir'];
											elseif($i == 1) $x = $row['directory'];
											$opt = '<option value="'.$row['media_id'].'"'.($row['directory'] == $in['gallery']['dir'] ? ' selected="selected"' : '').'>'.substr($row['directory'], 1).' ('.$row['quantity'].' '.$mcat[$row['category_id']].')</option>';
											if($row['usrid'] == $usrid) {
												echo $opt;
											} else {
												$everyones.= $opt;
											}
										}
										?>
									</optgroup>
									<optgroup label="Everyone Else's">
										<?=$everyones?>
									</optgroup>
								</select> 
								<a href="/media.php?dir=<?=$x?>" target="_blank" class="arrow-link" onclick="">view</a>
							</div>
						</p>
					</div>
					<p><label><input type="radio" name="in[gallery][source]" value="new" disabled="disabled"/> <del>Upload</del> Coming soon</label></p>
				</td>
			</tr>
			<tr>
				<th>Display Options</th>
				<td>
					Show <input type="text" name="in[gallery][thumbs]" value="<?=($in['gallery']['thumbs'] ? $in['gallery']['thumbs'] : '4')?>" size="1" maxlength="2" tabindex="6"/> thumbnails
					<p><label><input type="checkbox" name="in[gallery][link_to]" value="1"<?=(!$in || $in['gallery']['link_to'] ? ' checked="checked"' : '')?> tabindex="7"/> Provide link to whole gallery</label></p>
				</td>
			</tr>
		</table>
	</div>
	
	<div id="form-video" class="forms"<?=($n['type'] != "video" ? ' style="display:none"' : '')?>>
		<table border="0" cellpadding="0" cellspacing="0" class="styled-form">
			<tr>
				<th>Heading<br/><small>Optional</small></th>
				<td><input type="text" name="in[video][heading]" value="<?=htmlSC($in['video']['heading'])?>" size="100" maxlength="100" tabindex="5"/></td>
			</tr>
			<tr>
				<th>Supplementary Text<br/><small>Optional</small></th>
				<td>
					<?=outputToolbox("inp-video-text", array("b","i","strikethrough","a","links"), "use_bbcode")?>
					<textarea name="in[video][text]" rows="4" cols="90" tabindex="6" id="inp-video-text"><?=$in['video']['text']?></textarea>
				</td>
			</tr>
			<tr>
				<th>Embed Code</th>
				<td>
					<span style="color:#666">Your video should be no bigger than 569 pixels in width. If it is, change the code to a smaller width.</span>
					<p><textarea name="in[video][code]" rows="4" cols="90" tabindex="7"><?=$in['video']['code']?></textarea></p>
				</td>
			</tr>
		</table>
	</div>
	
	<div id="form-audio" class="forms"<?=($n['type'] != "audio" ? ' style="display:none"' : '')?>>
		<table border="0" cellpadding="0" cellspacing="0" class="styled-form">
			<tr>
				<th>Source</th>
				<td>
					<label style="font-size:16px"><input type="radio" name="in[audio][source]" value="sample"<?=(!$in || $in['audio']['source'] == "sample" ? ' checked="checked"' : '')?>/> A local full-length track from the Album Database</label>
					<div class="sub"<?=($in && $in['audio']['source'] != "sample" ? ' style="display:none"' : '')?>>
						<p><select name="in[audio][source_sample]">
							<?
							$query = "SELECT s.albumid, file, title, subtitle, track_name FROM albums_samples s 
								LEFT JOIN albums USING (albumid) 
								LEFT JOIN albums_tracks t ON (t.id = s.track_id) 
								ORDER BY title";
							$res = mysqli_query($GLOBALS['db']['link'], $query);
							$i = 0;
							while($row = mysqli_fetch_assoc($res)) {
								if($cog != $row['albumid']) {
									echo ($i > 0 ? '</optgroup>' : '').'<optgroup label="'.htmlSC($row['title']).' '.htmlSC($row['subtitle']).'">';
									$cog = $row['albumid'];
								}
								echo '<option value="/music/media/samples/'.$row['file'].'">'.$row['track_name'].'</option>';
								$i++;
							}
							?>
							</optgroup>
						</select></p>
					</div>
					<div style="margin:5px 0 0;">
						<label style="font-size:16px"><input type="radio" name="in[audio][source]" value="upload"<?=($in['audio']['source'] == "upload" ? ' checked="checked"' : '')?>"/> Upload an MP3</label>
						<div class="sub"<?=($in['audio']['source'] != "upload" ? ' style="display:none"' : '')?>>
							<?=($_SESSION['user_rank'] >= 6 ? '<div class="warn" style="margin:0 0 2px;">Consider an addition to the <a href="/ninadmin/albums.php?action=new">album databse</a> first, then uploading this track as a sample, then selecting it above.</div>' : '')?>
							<input type="hidden" name="MAX_FILE_SIZE" value="7340032"/>
							<input type="file" name="audio_upload"/>&nbsp;&nbsp;<span style="color:#666">MP3 files only; Size limit is 7MB</span>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<th>Heading</th>
				<td><input type="text" name="in[audio][heading]" value="<?=htmlSC($in['audio']['heading'])?>" size="80" maxlength="100" tabindex="6"/></td>
			</tr>
			<tr>
				<th>Supplementary Text<br/><small>Optional</small></th>
				<td>
					Make sure you link to any albums in our Album Database if this track is from any.
					<p><?=outputToolbox("inp-audio-text", array("b","i","strikethrough","a","links"), "use_bbcode")?></p>
					<textarea name="in[audio][text]" rows="4" cols="90" tabindex="7" id="inp-audio-text"><?=$in['audio']['text']?></textarea>
					<p><a href="#_headline_img" class="arrow-toggle<?=($has_hi ? ' arrow-toggle-on' : '')?>" onclick="$(this).toggleClass('arrow-toggle-on').parent().next().slideToggle();">Headline Image</a></p>
					<div style="<?=(!$has_hi ? 'display:none' : '')?>">
						<p>
							<img src="/bin/img/news/headingimg_<?=$nid?>.png" alt="Headline img" style="float:left; margin:3px 10px 0 0;"/>
							<label><input type="checkbox" name="in[delete_headingimg]" value="1"/> Delete this image</label><br/>
							<p>Upload a new accompanying headline image <span style="color:#888;">(JPG, GIF, or PNG format; Image will be resized to 100 x 100 pixels.)</span>:
							<input type="file" name="headline_img_text"/></p>
						</p>
					</div>
				</td>
			</tr>
		</table>
	</div>
	
	<p>&nbsp;</p>
	
	<div style="float:right; width:300px;">
		<fieldset id="new-post-options">
			<legend>Options</legend>
			
			<label><input type="checkbox" name="in[post_to][forums]" value="1"<?=($in['post_to']['forums'] ? ' checked="checked"' : '')?> onclick="if($(this).is(':checked')) { $(this).parent().next().show(); $('#new-post-options table input').attr('disabled','disabled'); } else { $(this).parent().next().hide(); $('#new-post-options table input').removeAttr('disabled'); }"/> Post as a new forum topic</label>
			<div style="margin:0 0 0 10px;<?=(!$in['post_to']['forums'] ? 'display:none;' : '')?>">
				<?
				$query = "SELECT * FROM forums WHERE closed <= '$_SESSION['user_rank']' AND invisible <= '$_SESSION['user_rank']' ORDER BY cid, title";
				$res   = mysqli_query($GLOBALS['db']['link'], $query);
				$i = 0;
				while($row = mysqli_fetch_assoc($res)) {
					$i++;
					echo '<p><label><input type="radio" name="in[fid]" value="'.$row['fid'].'"'.($i == 1 ? ' checked="checked"' : '').'/> '.$row['title'].'</label></p>';
				}
				?>
			</div>
			
			<div style="margin:2px 0"><hr style="border-width:1px 0 0; border-style:solid; border-color:#CCC;"/></div>
			
			<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<th valign="top" nowrap="nowrap">Post to...&nbsp;</th>
					<td>
						<label><input type="checkbox" name="in[post_to][public]" value="1"<?=(!$in || $in['post_to']['public'] ? ' checked="checked"' : '')?>/> Public News</label>
						<p><label><input type="checkbox" name="in[post_to][blog]" value="1"<?=(!$in || $in['post_to']['blog'] ? ' checked="checked"' : '')?>/> My Personal Blog</label></p>
						<?
						if(!$in['post_to']['groups']) $in['post_to']['groups'] = array();
						$query = "SELECT name, name_url, g.group_id FROM groups_members gm LEFT JOIN groups g USING (group_id) WHERE gm.usrid='$usrid' ORDER BY name";
						$res   = mysqli_query($GLOBALS['db']['link'], $query);
						if(!mysqli_num_rows($res)) {
							echo '<p><span style="text-decoration:line-through;">A Group...</span> You don\'t belong to any groups yet.</p>';
						} else {
							echo '<p>A Group...</label></p>';
							while($row = mysqli_fetch_assoc($res)) {
								echo '<p><label><input type="checkbox" name="in[post_to][groups][]" value="'.$row['group_id'].'"'.(in_array($row['group_id'], $in['post_to']['groups']) ? ' checked="checked"' : '').'/> '.$row['name'].'</label> <a href="/groups/~'.$row['name_url'].'" target="_blank" class="arrow-link"></a></p>';
							}
						}
						?>
					</td>
				</tr>
			</table>
		</fieldset>
	</div>
	
	<div style="float:left; width:585px;">
		<fieldset>
			<legend>Tags</legend>
			Include some related tags in order to categorize this post and expose it on other pages.
			<p>&bull; Input one tag per line.</p>
			<p>&bull; All [game] and [person] tags included in your text are automatically tagged already.</p>
			<p><div style="padding:3px 5px; border:1px solid #DDD; color:#666;">
				Tag a <a href="#" class="arrow-toggle tag-selector" rel="games">Game</a> or <a href="#" class="arrow-toggle tag-selector" rel="people">Person</a>
				<div id="tagsel-games" class="tagsel" style="display:none"><p>
					<select onchange="newlineInsert(this.options[this.selectedIndex].value, 'input-tags');" style="font-size:12px; font-family:Arial;">
						<option value="">Games...</option>
						<?
						$query = "SELECT g.title, gp.release_date FROM games g LEFT JOIN games_publications gp ON (g.gid=gp.gid AND gp.primary='1') ORDER BY g.title";
						$res   = mysqli_query($GLOBALS['db']['link'], $query);
						while($row = mysqli_fetch_assoc($res)) {
							$row['title'] = stripslashes($row['title']);
							$outp = (strlen($row['title']) > 50 ? substr($row['title'], 0, 40)."&hellip;".substr($row['title'], -6, 6) : $row['title']);
							echo '<option value="'.htmlSC($row['title']).'">'.$outp.' ('.substr($row['release_date'], 0, 4).')</option>';
						}
						?>
					</select>
				</p></div>
				<div id="tagsel-people" class="tagsel" style="display:none"><p>
					<select onchange="newlineInsert(this.options[this.selectedIndex].value, 'input-tags');" style="font-size:12px; font-family:Arial;">
						<option value="">People...</option>
						<?
						$query = "SELECT name, title, prolific FROM people ORDER BY name";
						$res   = mysqli_query($GLOBALS['db']['link'], $query);
						while($row = mysqli_fetch_assoc($res)) {
							$row = stripslashesDeep($row);
							$outp = $row['name'].($row['title'] ? ' ('.$row['title'].')' : '');
							if(strlen($outp) > 50) $outp = substr($outp, 0, 48)."&hellip;)";
							echo '<option value="'.htmlSC($row['name']).'"'.($row['prolific'] ? ' style="font-weight:bold"' : '').'>'.$outp.'</option>';
						}
						?>
					</select>
				</p></div>
			</div></p>
			<p><textarea name="in[tags]" rows="5" cols="59" id="input-tags"><?=$in['tags']?></textarea></p>
		</fieldset>
	</div>
	
	<br style="clear:left"/>
	<p>&nbsp;</p>
	
	<input type="button" value="Preview" tabindex="8" id="preview-button" onclick="NNpreview();"/> 
	<input type="submit" name="submit_new" tabindex="9" value="Submit" style="font-weight:bold"/>
	
	<br style="clear:left"/>
	
	<div id="preview-space" class="news" style="margin-right:315px"></div>
	
</form>

</div>

<?
$page->footer();
?>