<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/page_old.php");
$page->javascript.= '<script type="text/javascript" src="/bin/script/htmltoolbox.js"></script>';

$tb_it = 0;
function outputToolbox($field, $include="", $bbcode="") {
	$tb_it++;
	foreach($include as $i) {
		$incl[$i] = true;
	}
	?>
	<div class="htmltools<?=($bbcode ? ' bbcode' : '')?>">
		<input type="hidden" name="htmltools-field" value="<?=$field?>"/>
		<table border="0" cellpadding="0" cellspacing="0" width="100%" class="htmltools-table">
			<tr>
				<td<?=($incl && !$incl['h3'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel="&lt;h3&gt;,&lt;/h3&gt;,[h3],[/h3]" title="Heading level 3; Table of contents are created via this tag (if applicable)!"><img src="/bin/img/icons/text_heading_3.png" alt="heading 3"/></a></td>
				<td<?=($incl && !$incl['h4'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel="&lt;h4&gt;,&lt;/h4&gt;,[h4],[/h4]" title="Heading level 4"><img src="/bin/img/icons/text_heading_4.png" alt="heading 4"/></a></td>
				<td<?=($incl && !$incl['h5'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel="&lt;h5&gt;,&lt;/h5&gt;,[h5],[/h5]" title="Heading level 5"><img src="/bin/img/icons/text_heading_5.png" alt="heading 5"/></a></td>
				<td<?=($incl && !$incl['h6'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel="&lt;h6&gt;,&lt;/h6&gt;,[h6],[/h6]" title="Heading level 6"><img src="/bin/img/icons/text_heading_6.png" alt="heading 6"/></a></td>
				<td<?=($incl && !$incl['b'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel="&lt;b&gt;,&lt;/b&gt;,[b],[/b]" title="Bold text"><img src="/bin/img/icons/text_bold.png" alt="bold"/></a></td>
				<td<?=($incl && !$incl['i'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel="&lt;i&gt;,&lt;/i&gt;,[i],[/i]" title="Italic text"><img src="/bin/img/icons/text_italic.png" alt="italic"/></a></td>
				<td<?=($incl && !$incl['big'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel="&lt;big&gt;,&lt;/big&gt;,[big],[/big]" title="big text"><img src="/bin/img/icons/text_big.png" alt="big"/></a></td>
				<td<?=($incl && !$incl['small'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel="&lt;small&gt;,&lt;/small&gt;,[small],[/small]" title="small text"><img src="/bin/img/icons/text_small.png" alt="small"/></a></td>
				<td<?=($incl && !$incl['strikethrough'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel="&lt;strikethrough&gt;,&lt;/strikethrough&gt;,[strike],[/strike]" title="Strike-through text"><img src="/bin/img/icons/text_strikethrough.png" alt="strikethrough text"/></a></td>
				<td<?=($incl && !$incl['p'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel="&lt;p&gt;,&lt;/p&gt;,[p],[/p]" title="Insert paragraph"><img src="/bin/img/icons/paragraph.png" alt="paragraph"/></a></td>
				<td<?=($incl && !$incl['a'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel="&lt;a href=&quot;&quot;&gt;,&lt;/a&gt;,[url=],[/url]" title="Hyperlink"><img src="/bin/img/icons/link.png" alt="hyperlink"/></a></td>
				<td<?=($incl && !$incl['blockquote'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel="&lt;blockquote&gt;,&lt;/blockquote&gt;,[quote],[/quote]" title="Blockquote (a long quote)"><img src="/bin/img/icons/quote.png" alt="blockquote"/></a></td>
				<td<?=($incl && !$incl['cite'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel=",,[cite=],[/cite]" title="Cite a source"><img src="/bin/img/icons/book_open.png" alt="cite a source"/></a></td>
				<td<?=($bbcode && $incl && !$incl['spoiler'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel=",,[spoiler],[/spoiler]" title="Spoiler (text that is initially hidden)"><img src="/bin/img/icons/spoiler.png" alt="spoiler"/></a></td>
				<td<?=($incl && !$incl['ul'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel="&lt;ul&gt;NL&lt;li&gt;&lt;/li&gt;NL&lt;li&gt;&lt;/li&gt;NL,&lt;/ul&gt;,[list]NL*,NL*NL*NL[/list]" title="Unordered (bulleted) list"><img src="/bin/img/icons/text_list_bullets.png" alt="unordered list"/></a></td>
				<td<?=($incl && !$incl['ol'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel="&lt;ol&gt;NL&lt;li&gt;&lt;/li&gt;NL&lt;li&gt;&lt;/li&gt;NL,&lt;/ol&gt;,[olist]NL*,NL*NL*NL[/olist]" title="Ordered (numbered) list"><img src="/bin/img/icons/text_list_numbers.png" alt="ordered list"/></a></td>
				<td<?=($incl && !$incl['image'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" onclick="TBimgGen($(this).parents('tr').next().children('td'), '<?=$field?>');" title="Generate, upload, or display a single image"><img src="/bin/img/icons/image.png" alt="image"/></a></td>
				<td<?=($incl && !$incl['img'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel="&lt;img src=&quot;&gt;,&lt;&quot; alt=&quot;&quot; border=&quot;0&quot;/&gt;,[img],[/img]" title="Simple Image"><img src="/bin/img/icons/image.png" alt="image"/></a></td>
				<td<?=($incl && !$incl['gallery'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel="[[M||/location/of/media/||CAPTION]], ,[[M||/location/of/media/||CAPTION]], " title="Image Gallery"><img src="/bin/img/icons/images.png" alt="image gallery"/></a></td>
				<td<?=($incl && !$incl['emoticon'] ? ' style="display:none"' : '')?>><a href="#" class="insert" rel=",,&lt;!--emoticon:,--&gt;" title="emoticon"><img src="/bin/img/icons/emoticons/smile.png" alt="emoticon" onclick="$(this).toggleClass('on'); loadEmotes('<?=$tb_it?>', '<?=$field?>');"/></a></td>
				<?
				if($include && in_array("links", $include)) {
					?>
					<td nowrap="nowrap" width="100%">
						<div class="linkgen">
							<div class="linkgen1">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td class="linkgen1">Link generator</td>
										<td width="100%">
											<div style="margin-right:6px"><input type="text" value="Start typing here to find a page on this site..." class="resetonfocus"/></div>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</td>
					<?
				} elseif(in_array("autotag", $include)) {
					?>
					<td nowrap="nowrap" width="100%">
						<div class="linkgen">
							<div class="linkgen1">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td class="linkgen1">Autotag/Link 
											<a href="javascript:void(0)" class="tooltip" style="padding:0 2px; background-color:#06C; color:white; font:bold 10px verdana; text-decoration:none;" title="&bull; Create a hyperlink to a game page, person profile, music album, or other page in your forum post.<br/>&bull; Create a link back to this this forum topic on that page (if applicable)<br/><br/>Autotags create more synergy within the site and give more exposure to the conversation you participated in. If you mention any games, game creators, series, music albums, etc. in your post, you should autotag them!">?</a>&nbsp;
										</td>
										<td width="100%">
											<div style="margin-right:6px"><input type="text" value="Start typing here to find a page on this site..." class="resetonfocus"/></div>
										</td>
									</tr>
								</table>
							</div>
							<div class="linkgen2" style="display:none"><span class="space"></span> <a href="javascript:void(0)" class="linkgen-toggleback x" title="reset link generator">x</a></div>
						</div>
					</td>
					<?
				} else {
					if($bbcode) $bbcodecell = 'width="100%"';
					else echo '<td width="100%">&nbsp;</td>';
				}
				?>
				<?=($bbcode ? '<td nowrap="nowrap" '.$bbcodecell.'>&nbsp; <a href="/bbcode.htm" target="_blank" class="arrow-link">BB Code guide</a></td>' : '')?>
			</tr>
			<?
			if($include && in_array("emoticon", $include)) {
				?>
				<tr>
					<td colspan="21"><div id="emoticon-space-<?=$tb_it?>" class="tb-subspace"></div></td>
				</tr>
				<?
			}
			?>
			<tr>
				<td colspan="21"></td>
			</tr>
		</table>
	</div>
	<?
}

/*if($what = $_POST['genlink']) {
	
	if(!$field = $_POST['field']) die("Error: No field id given");
	
	?><select class="linkgen-selected" onchange="toolboxInsert(this.options[this.selectedIndex].value, '', '<?=$field?>');"><?
	if($what == "game") {
		?>
			<option value="">Insert a game link...</option>
			<?
			$query = "SELECT `title`, `gid` FROM `games` ORDER BY `title`";
			$res   = mysql_query($query);
			while($row = mysql_fetch_assoc($res)) {
				$row['title'] = htmlSC($row['title']);
				echo '<option value="[game]'.htmlSC($row['title']).'[/game]">'.(strlen($row['title']) > 55 ? substr($row['title'], 0, 48)."&hellip;".substr($row['title'], -6) : $row['title']).'</option>';
			}
	} elseif($what == "game_series") {
		?>
			<option value="">Insert a game series...</option>
			<?
			$query = "SELECT DISTINCT(`series`) FROM games_series ORDER BY `series`";
			$res   = mysql_query($query);
			while($row = mysql_fetch_assoc($res)) {
				$row['series'] = htmlSC($row['series']);
				echo '<option value="[url=/games/series/'.urlencode($row['series']).']'.$row['series'].'[/url]">'.$row['series'].'</option>';
			}
	} elseif($what == "person") {
		?>
			<option value="">Insert a person...</option>
			<?
			$query = "SELECT `name`, `name_url`, `title`, `prolific` FROM `people` ORDER BY `name`";
			$res   = mysql_query($query);
			while($row = mysql_fetch_assoc($res)) {
				$row['name'] = htmlSC($row['name']);
				$row['title'] = strlen($row['title']) > 25 ? substr($row['title'], 0, 23).'&hellip;' : $row['title'];
				echo '<option'.($row['prolific'] ? ' style="font-weight:bold"' : '').' value="[person]'.htmlSC($row['name']).'[/person]">'.$row['name'].($row['title'] ? ' ('.$row['title'].')' : '').'</option>';
			}
	} elseif($what == "association") {
		?>
			<option value="">Insert an association...</option>
			<?
			$query = "SELECT DISTINCT(`developer`) FROM games_developers ORDER BY `developer`";
			$res   = mysql_query($query);
			while($row = mysql_fetch_assoc($res)) {
				$row['developer'] = htmlSC($row['developer']);
				echo '<option value="[url=/associations/'.urlencode($row['developer']).']'.$row['developer'].'[/url]">'.$row['developer'].'</option>';
			}
	} elseif($what == "album") {
		?>
				<option value="">Insert an album link...</option>
				<?
				$query = "SELECT `title`, subtitle, cid, albumid FROM albums ORDER BY `title`";
				$res = mysql_query($query);
				while($row = mysql_fetch_assoc($res)) {
					$p_title = $row['title'].($row['subtitle'] ? ' '.$row['subtitle'] : '');
					if(strlen($p_title) > 50) $p_title = substr($p_title, 0, 40)."&hellip;".substr($p_title, -8);
					echo '<option value="[url=/music/?id='.$row['albumid'].']'.htmlSC($row['title']).($row['subtitle'] ? ' '.htmlSC($row['subtitle']) : '').'[/url]" title="'.htmlSC($row['title'].' '.$row['subtitle']).'">'.$p_title.' ('.$row['cid'].')</option>'."\n";
				}
	}
	?></select><?
	
}*/

if($_POST['imggen_form']) {
	if(!$field = $_POST['field']) die("Error: No field id given");
	?>
	<div class="imggen">
		<a href="javascript:void(0)" class="x" style="float:right" onclick="$(this).parent().hide();">x</a>
		<h5>
			Image Generator&nbsp;
			<a href="javascript:void(0)" class="imggen-sel arrow-toggle arrow-toggle-on" onclick="$('.imggen-sel').toggleClass('arrow-toggle-on'); $('.imggen-opts').toggle();">Choose</a>&nbsp;
			<a href="javascript:void(0)" class="imggen-sel arrow-toggle" onclick="$('.imggen-sel').toggleClass('arrow-toggle-on'); $('.imggen-opts').toggle();">Upload</a>
		</h5>
		
		<div class="imggen-opts">
			<select onchange="TBloadMediaDir($(this).next(), $(this).val(), '<?=$field?>', '1');">
				<option value="">Select a media directory...</option>
				<optgroup label="Your Uploads">
					<?
					$query = "SELECT * FROM media_categories";
					$res   = mysql_query($query);
					while($row = mysql_fetch_assoc($res)) {
						$mcat[$row['category_id']] = $row['category'];
					}
					$query = "SELECT * FROM media ORDER BY directory";
					$res   = mysql_query($query);
					$i = 0;
					while($row = mysql_fetch_assoc($res)) {
						$i++;
						$opt = '<option value="'.$row['directory'].'">'.$row['directory'].' ('.$row['quantity'].' '.$mcat[$row['category_id']].')</option>';
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
			</select><div></div>
		</div>
		<div class="imggen-opts" style="display:none">
			<iframe src="/upload.php" frameborder="0" style="width:100%; height:50px;"></iframe>
		</div>
		
	</div>
	<?
	exit;
}

if($dir = $_POST['load_media_dir']) {
	
	$field = $_POST['field'];
	$pg = ($_POST['pg'] ? $_POST['pg'] : 1);
	
	$query = "SELECT * FROM media WHERE directory='$dir' LIMIT 1";
	$res = mysql_query($query);
	$dat = mysql_fetch_object($res);
	$dir = $dat->directory;
	
	//captions
	$query = "SELECT c.* FROM media_captions c, media m WHERE m.directory='$dir' AND c.media_id=m.media_id";
	$res = mysql_query($query);
	while($row = mysql_fetch_assoc($res)) {
		$capts[$row['file']] = $row['caption'];
	}
	
	if(substr($dir, 0, 1) != "/") $dir = "/".$dir;
	if($handle = opendir($_SERVER['DOCUMENT_ROOT'].$dir)) {
		while(false !== ($file = readdir($handle))) {
			if($file != "thumbs" && $file != "." && $file != "..") $imgs[] = $file;
		}
	}
	sort($imgs);
	if($handle = opendir($_SERVER['DOCUMENT_ROOT'].$dir."/thumbs")) {
		while(false !== ($file = readdir($handle))) {
			if($file != "." && $file != "..") $tns[] = $file;
		}
	}
	$img_count = count($imgs);
	sort($tns);
	if($img_count != count($tns)) {
		echo "Error displaying gallery: thumbnail count doesn't match image count";
	} else {
		
		$min = ($pg * 6) - 1;
		$max = $min + 6;
		if($pg > 1) {
			$prev_link = "javascript:void(0)";
			$prev_js = "TBloadMediaDir($(this).parents('.media-dir').parent(), '$dir', '$field', '".($pg - 1)."')";
		}
		if($max < $img_count) {
			$next_link = "javascript:void(0)";
			$next_js = "TBloadMediaDir($(this).parents('.media-dir').parent(), '$dir', '$field', '".($pg + 1)."')";
		}
		if($max > $img_count) $max = $img_count;
		
		?>
		<div class="media-dir">
			<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td><a <?=($prev_link ? 'href="'.$prev_link.'"' : '')?> class="arrow-left<?=(!$prev_link ? '-off' : '')?>" onclick="<?=$prev_js?>"> </a></td>
					<?
					for($i=$min; $i < $max; $i++) {
						echo '<td><a href="javascript:void(0)" title="'.$capts[$imgs[$i]].'" onclick="toolboxInsert(\'[img|left|'.$dir.'/'.$imgs[$i].'|'.($capts[$imgs[$i]] ? $capts[$imgs[$i]] : 'CAPTION').']'.$dir.'/thumbs/'.$tns[$i].'[/img]\', \'\', \''.$field.'\');"><img src="'.$dir.'/thumbs/'.$tns[$i].'" alt="'.$capts[$imgs[$i]].'"/></a></td>'."\n";
					}
					?>
					<td><a <?=($next_link ? 'href="'.$next_link.'"' : '')?> class="arrow-right<?=(!$next_link ? '-off' : '')?>" onclick="<?=$next_js?>"> </a></td>
				</tr>
			</table>
		</div>
		<?
	}
	
}

if($_POST['_action'] == "load_emoticons") {
	$field = $_POST['_field'];
	if ($handle = opendir($_SERVER['DOCUMENT_ROOT']."/bin/img/icons/emoticons")) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				echo '<a href="javascript:void(0);" onclick="toolboxInsert(\' <!--emoticon:'.$file.'\', \'-->\', \''.$field.'\');"><img src="/bin/img/icons/emoticons/'.$file.'" border="0"/></a> ';
			}
		}
		closedir($handle);
	}
	exit;
}

?>