<?

$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM games WHERE `gid`='$id' LIMIT 1"));
if(!$dat) die("Couldn't get game data");

//actions
if($_POST[draft] || $_POST[submit]) {
	
	if($_POST[submit]) {
		$it = "preview";
		$datetime = date('Y-m-d H:i:s');
	} else {
		$it = "draft";
		$datetime = '0000-00-00 00:00:00';
	}
	
	if($_POST[insert]) {
		$res = mysqli_query($GLOBALS['db']['link'], "SHOW TABLE STATUS LIKE 'games_previews'");
		if(!$d = mysqli_fetch_assoc($res)) {
			$errors[] = "couldn't get incremental data to properly insert into db.";
		} else {
			$pid = $d[Auto_increment];
			$query = sprintf("INSERT INTO games_previews (`id`,gid,datetime,usrid,contributor,words) VALUES 
				('$pid', '$id', '$datetime', '$usrid', '%s', '%s')",
				mysqli_real_escape_string($GLOBALS['db']['link'], $_POST[contributor]),
				mysqli_real_escape_string($GLOBALS['db']['link'], $_POST[words]));
		}
	} elseif($_POST[update]) {
		$pid = $_POST[update];
		$query = sprintf("UPDATE games_previews SET `datetime`='$datetime', `contributor`='%s', `words`='%s' WHERE `id` = '$pid' LIMIT 1",
			mysqli_real_escape_string($GLOBALS['db']['link'], $_POST[contributor]),
			mysqli_real_escape_string($GLOBALS['db']['link'], $_POST[words]));
	}
	
	if(!mysqli_query($GLOBALS['db']['link'], $query)) $errors[] = "Couldn't save $it to database";
	else {
		$results[] = $it.' saved; <a href="view-preview.php?id='.$pid.'" target="_blank">See it</a>';
		if($_POST[submit]) {
			unset($_GET[on]);
			unset($_POST);
			unset($pid);
		}
	}
}

if($_GET['delete']) {
	if($usrrank < 8) die("User rank not high enough to perform this");
	$query = "DELETE FROM games_previews WHERE `id`='$_GET[delete]' LIMIT 1";
	if(!mysqli_query($GLOBALS['db']['link'], $query)) $errors[] = "Couldn't delete version";
	else $results[] = "Version deleted";
}

$page->javascript = ('
<script type="text/javascript">
function previewImage(loc) {
	var x =\'<a href="\'+loc.replace(\'/thumbs\',\'\')+\'" target="_blank" class="thickbox"><img src="\'+loc+\'" alt="thumb"/></a>\';
	document.getElementById("img-frame").innerHTML=x;
}
</script>');

$page->freestyle.= ('
	#previewlist TH { padding:3px 2px; border-width:1px 0; border-style:solid; border-color:#BBB; text-align:left; }
	#previewlist TD { padding:3px 2px; border-bottom:1px solid #EEE; }
	.preview-notice { padding:5px; border:1px dotted #BBB; }
	CODE { display:block; margin:5px 0 5px 15px; padding:10px; border:1px solid #EEE; background-color:#F5F5F5; font:normal 12px monospace; }
');

$page->header();
echo $mod_header;

echo '
<p>
<b>Actions</b>: 
<a id="ver" href="#" onclick="document.getElementById(\'update\').style.display=\'none\'; document.getElementById(\'versions\').style.display=\'block\';">See Draft History</a> | 
<a id="upd" href="#" onclick="document.getElementById(\'update\').style.display=\'block\'; document.getElementById(\'versions\').style.display=\'none\';">Update the Preview</a>
</p>
<br/>

<div id="versions" style="display:'.($_GET[on] != 'update' ? 'block' : 'none').'">
	';
	$query = "SELECT * FROM games_previews WHERE gid='$id' ORDER BY `datetime` DESC";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)) {
		echo '<table border="0" cellpadding="0" cellspacing="0" width="100%" id="previewlist">
			<tr>
				<th>date time</th>
				<th>user</th>
				<th>view</th>
				<th>build</th>
				'.($usrrank >= 8 ? '<th style="display:none">delete</th>' : '').'
			</tr>';
		$i = 0;
		while($row = mysqli_fetch_assoc($res)) {
			$i++;
			$safe_words = str_replace('"', '&quot;', $row[words]);
			$safe_words = str_replace("'", "&prime;", $safe_words);
			$safe_words = stripslashes($safe_words);
			echo '
			<tr>
				<td>'.($row[datetime] == '0000-00-00 00:00:00' ? 'unpublished' : $row[datetime]).'</td>
				<td>'.outputUser($row[usrid], FALSE).'</td>
				<td><a href="view-preview.php?id='.$row[id].'" target="_blank">view</a></td>
				<td>'.($i == 1 && $row[datetime] != '0000-00-00 00:00:00' ? 'current build' : '<a href="?what=preview&id='.$id.'&build='.$row[id].'&on=update">build upon</a>').'</td>
				'.($usrrank >= 8 ? '<td style="display:none"><a href="#" onclick="if(confirm(\'Delete this version?\')) window.location=\'?what=preview&id='.$id.'&delete='.$row[id].'\';">delete</a></td>' : '').'
			</tr>';
		}
		echo '
		</table>';
	} else {
		echo '<p>No previews yet</p>';
		$no_previews = 1;
	}
	?>
</div>

<div id="update" style="display:<?=($_GET[on] == 'update' ? 'block' : 'none')?>">

	<div class="preview-notice"><?=($pid ? 'Editing an unpublished preview version (<a href="view-preview.php?id='.$pid.'" target="_blank">see it</a>). <a href="?what=preview&id='.$id.'&on=update">start a new version</a>' : 'Starting a new preview draft below. All old versions are saved, even after this one is edited and submitted.')?></div>
	
	<form action="?what=preview&id=<?=$id?>&on=update" method="post">
		<input type="hidden" name="insert" value="<?=($no_previews || !$pid ? '1' : '')?>"/>
		<input type="hidden" name="update" value="<?=$pid?>"/>
		
		<p>
			<div id="preview-rules-link"><a href="javascript:void(0)" class="arrow-right" onclick="toggle('preview-rules','preview-rules-link');">Preview formatting rules & tips</a></div>
			<fieldset id="preview-rules" style="display:none; line-height:1.5em">
				<legend>Preview Formatting Rules & Tips (<a href="javascript:void(0)" onclick="toggle('preview-rules-link','preview-rules');">hide</a>)</legend>
				<ol>
					<li><b>The script won't modify your input</b> so you need to include proper HTML where applicable.</li>
					<li>You will probably choose to format the article in paragraph form, such as in a magazine or newspaper. In order to do this, 
						you will need to enclose your paragraphs in &lt;p&gt;&lt;/p&gt; tags. For eaxample:
						<code>
							&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.&lt;/p&gt;<br/>
							&lt;p&gt;Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.&lt;/p&gt;<br/>
							&lt;p&gt;Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.&lt;/p&gt;
						</code>
					</li>
					<li><b>Don't indent paragraphs</b></li>
					<li>You can automatically create a table of contents using &lt;h3&gt;&lt;/h3&gt;.</li>
				  <li>
				  	Make sure your HTML is <b>XHTML friendly</b> (see a <a href="http://www.w3schools.com/Xhtml/xhtml_syntax.asp" target="_blank">short tutorial</a>). 
				  	All values inside the tag must be enclosed in double quotation marks and every tag must be closed.
				  </li>
				  <li>
				  	Any use of images in the news article will have a separate thumbnail file with a link to the separate full-sized image. 
				  	It is not acceptable to scale the full-sized image to a smaller size as a makeshift thumbnail. 
				  	The <a href="/ninadmin/media.php" target="_blank">media uploader</a> can create thumbnails automatically. 
				  	If you want a bigger thumbnail, make one with your image-editing software or quickly and easily online with <a href="http://www.wiredness.com" target="_blank">Wiredness</a>.
				  </li>
				  <li>
				  	For the sake of uniformity, please make no attempts to change the site's default style. 
				  	Examples include changing font-family and font-size or modifying the style of the default thumbnail-caption style.
				  </li>
				  <li>
				  	If other games are mentioned in your words that we cover, <b>always include a link to game coverage</b> on the first instance of the game title. 
						<code>... is quite different from &lt;a href="/games/~chrono-trigger"&gt;Chrono Trigger&lt;/a&gt; in gameplay terms.</code>
					</li>
					<li>You can also link to games by using the following code:
						<code>[[G||GAME TITLE||LINK WORDS (OPTIONAL)]]</code>
						However, be wary of using this code! If you don't spell the game title EXACTLY as it is in the database or if the game's 
						title changes (and games still in development often do), your link will not work any longer. 
						To be on the safe side, use this code only on games already published in North America.<br />
						Examples of the code in action:
						<code>
							[[G||Final Fantasy VII]] => <?=reformatLinks("[[G||Final Fantasy VII]]")?><br/>
							[[G||Final Fantasy X-2||A crappy game]] => <?=reformatLinks("[[G||Final Fantasy X-2||A crappy game]]")?>
						</code>
					</li>
					<li>The above game code also has a People DB equivalent! However, the same rules about exact spelling and name-changes also apply.<br/>
						<code>
							[[P||Yoshitaka Amano]] => <?=reformatLinks("[[P||Yoshitaka Amano]]")?><br />
							[[P||Yuji Horii||Boshi's twin]] => <?=reformatLinks("[[P||Yuji Horii||Boshi's twin]]")?>
						</code>
					</li>
					<li>A group of thumbnail images should be enclosed in a gallery fieldset. The appropriate HTML is like so:
						<code>
							&lt;fieldset class="gallery"&gt;<br/>
							&nbsp;&nbsp;&lt;legend&gt;GALLERY CAPTION&lt;/legend&gt;<br />
							&nbsp;&nbsp;content & stuff<br />
							&lt;/fieldset&gt;
						</code>
						You can also automatically post a gallery in your news item if you have a directory full of images and a subdirectory of corresponding thumbnails 
						(which you can make with the <a href="/ninadmin/media.php" target="_blank">media uploader</a>). 
						To automatically display the gallery, use the following code:
						<code>[[M||directory||caption]]</code>
						For example:
						<code>[[M||media/zelda-screens||Some Zelda Screenshots]]</code>
						The directory must be full of large images and have a directory within it named "thumbs" with corresponding small images.
					</li>
					<li>
						All stand-alone images should be in the proper style class. <b>It is not OK to deviate from this exact HTML and style.</b>
						<code>
							&lt;dl class="thumbnail [POSITION]"&gt;
							&lt;dt&gt;&lt;a href="/link/to/big/image.jpg"&gt;&lt;img src="/thumbnail/image.gif" alt="my thumbnail"/&gt;&lt/a>&lt;/dt>
							&lt;dd&gt;A witty caption here&lt;/dd>&lt;/dl>
						</code>
						In the example above, [POSITION] should be either "left" or "right".<br />
						Or, more easily, use the following code:
						<code>[[T||position||big image||thumbnail||caption?]]</code>
						BIG IMAGE and THUMBNAIL are the image SRCs (ie '/media/blahblah/a_big_image.jpg' or 'http://www.thegia.com/some/directory/small_image.gif'). CAPTION is optional.
					</li>
				</ol>
			</fieldset>
		</p>
		<p>Please do the best you can to make the article <a href="http://validator.w3.org/" target="_blank">HTML valid</a> 
		(Validate by direct input > more options > validate HTML fragment, use Doctype HTML 4.01)</p>
		<br/>
		
		<div class="html-toolbox">
			<div id="img-frame" style="float:right; width:102px; margin:3px;"></div>
			<?=outputToolbox("words")?>
		
			<div style="margin-top:-2px; padding:5px; border-bottom:1px solid #808080; background-color:#EEE;">
				<select id="insert-img" onchange="previewImage(this.options[this.selectedIndex].value);">
				<option value="">Insert an image...</option>
				<?
				$query = "SELECT * FROM media_tags LEFT JOIN media USING(media_id) WHERE tag='gid:$id'";
				$res   = mysqli_query($GLOBALS['db']['link'], $query);
				$curdir = "";
				$i = 0;
				while($row = mysqli_fetch_assoc($res)) {
					if(is_dir($_SERVER['DOCUMENT_ROOT'].'/'.$row['directory'].'/thumbs/')) {
						if($row['media_id'] != $curdir) {
							$i++;
							if($i > 1) echo '</optgroup>';
							$row['description'] = strip_tags($row['description']);
							if(strlen($row['description']) > 70) $row['description'] = substr($row['description'], 0, 69)."&hellip;";
							echo '<optgroup label="'.$row['description'].'">'."\n";
							$curdir = $row['media_id'];
						}
						//get captions
						$query2 = "SELECT * FROM media_captions WHERE media_id='".$row['media_id']."'";
						$res2   = mysqli_query($GLOBALS['db']['link'], $query2);
						while($row2 = mysqli_fetch_assoc($res2)) {
							$capt[$row2['file']] = $row2['caption'];
						}
						//read dir
						if ($handle = opendir($_SERVER['DOCUMENT_ROOT'].'/'.$row['directory'].'/thumbs/')) {
							while(false !== ($file = readdir($handle))) {
								if($file != '.' && $file != '..')
									echo '<option value="'.$row['directory'].'/thumbs/'.$file.'">'.($capt[$file] ? $capt[$file] : $file)."</option>\n";
							}
							closedir($handle);
						}
					}
				}
				echo '</optgroup>
				</select> 
				<input type="button" value="Insert" onclick="toolboxInsert(\'[[T||left||\'+document.getElementById(\'insert-img\').value.replace(\'/thumbs\',\'\')+\'||\'+document.getElementById(\'insert-img\').value+\'||IMAGE CAPTION HERE]]\', \'\', \'words\')"/>
			</div>
		
		';
		if($_POST[words]) {
			$words = $_POST[words];
		} elseif(!$no_previews && !$pid) {
			//get specific or otherwise latest version
			$q = "SELECT * FROM games_previews WHERE ".($_GET[build] ? "`id`='$_GET[build]'" : "gid='$id' AND `datetime`!='0000-00-00 00:00:00' ORDER BY `datetime` DESC")." LIMIT 1";
			$d = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
			$words = $d->words;
		}
		?>
		
		<textarea id="words" name="words" rows="25" cols="95"><?=stripslashes($words)?></textarea>
		
		</div><!-- .html-toolbox -->
		
		<p><?=saveDraftButton("words", $dat->title_url."_preview")?></p>
		
		<p>Additional contributor(s): <input type="text" name="contributor" value="<?
			$_POST[contributor] = stripslashes($_POST[contributor]);
			$_POST[contributor] = htmlspecialchars($_POST[contributor]);
			echo $_POST[contributor] ?>"/>&nbsp;
			<small>Use HTML to link to the contributer's user profile or web page</small></p>
		
		<p><input type="submit" name="draft" value="Save as a draft & preview the changes made" onclick="words.toggleEditor()"/> or 
		<input type="submit" name="submit" value="Submit for publication" onclick="words.toggleEditor()" style="font-weight:bold"/></p>
	</form>
	
</div>
<?

$page->footer();

?>