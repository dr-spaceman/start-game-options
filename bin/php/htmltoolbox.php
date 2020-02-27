<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
/*require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/page_functions.php");*/

if($_POST['_action'] == "init"){
	//AJAX
	
	if(!$_POST['_field']) return false;
	
	if($_POST['_include']){
		$incl = array();
		if(strstr($_POST['_include'], ",")) $incl = explode(",", $_POST['_include']);
		elseif(strstr($_POST['_include'], " ")) $incl = explode(" ", $_POST['_include']);
		else $incl = $_POST['_include'];
	}
	
	$_tb = new toolbox();
	$_tb->field = $_POST['_field'];
	$_tb->bbcode = $_POST['_bbcode'];
	$_tb->include = $incl;
	echo $_tb->output();
	exit;
}
	

$page->javascripts[] = "/bin/script/htmltoolbox.js";
$page->css[] = "/bin/css/htmltoolbox.css";

class htmltoolbox {
	
	public $field;  //id of the field to insert stuff into
	public $bbcode; //output BB code (instead of HTML)
	public $include = array(); //list of included elements
	
	function __construct(){
		$this->in = array(
			'h3' => array("code" => "&lt;h3&gt;,&lt;/h3&gt;,[h3],[/h3]", "title" => "Heading level 3; Table of contents are created via this tag (if applicable)!", "img" => "/bin/img/icons/text_heading_3.png", "name" => "heading 3"),
			'h4' => array("code" => "&lt;h4&gt;,&lt;/h4&gt;,[h4],[/h4]", "title" => "Heading level 4", "img" => "/bin/img/icons/text_heading_4.png", "name" => "heading 4"),
			'h5' => array("code" => "&lt;h5&gt;,&lt;/h5&gt;,==,==", "title" => "Heading level 5", "img" => "/bin/img/icons/text_heading_5.png", "name" => "heading 5"),
			'h6' => array("code" => "&lt;h6&gt;,&lt;/h6&gt;,===,===", "title" => "Heading level 6", "img" => "/bin/img/icons/text_heading_6.png", "name" => "heading 6"),
			'b' => array("code" => "&lt;b&gt;,&lt;/b&gt;,&lt;b&gt;,&lt;/b&gt;", "title" => "Bold text", "img" => "/bin/img/icons/text_bold.png", "name" => "bold"),
			'i' => array("code" => "&lt;i&gt;,&lt;/i&gt;,&lt;i&gt;,&lt;/i&gt;", "title" => "Italic text", "img" => "/bin/img/icons/text_italic.png", "name" => "italic"),
			'big' => array("code" => "&lt;big&gt;,&lt;/big&gt;,&lt;big&gt;,&lt;/big&gt;", "title" => "big text", "img" => "/bin/img/icons/text_big.png", "name" => "big"),
			'small' => array("code" => "&lt;small&gt;,&lt;/small&gt;,&lt;small&gt;,&lt;/small&gt;", "title" => "small text", "img" => "/bin/img/icons/text_small.png", "name" => "small"),
			'strikethrough' => array("code" => "&lt;strikethrough&gt;,&lt;/strikethrough&gt;,[strike],[/strike]", "title" => "Strike-through text", "img" => "/bin/img/icons/text_strikethrough.png", "name" => "strikethrough text"),
			'p' => array("code" => "&lt;p&gt;,&lt;/p&gt;,&lt;p&gt;,&lt;/p&gt;", "title" => "Insert paragraph", "img" => "/bin/img/icons/paragraph.png", "name" => "paragraph"),
			'br' => array("code" => "&lt;br/&gt;,NL,&lt;br/&gt;,NL", "title" => "New line", "img" => "/bin/img/icons/nl.png", "name" => "new line"),
			'a' => array("code" => "&lt;a href=&quot;&quot;&gt;,&lt;/a&gt;,[url=],[/url]", "title" => "Hyperlink", "img" => "/bin/img/icons/link.png", "name" => "hyperlink"),
			'blockquote' => array("code" => "&lt;blockquote&gt;,&lt;/blockquote&gt;,[quote],[/quote]", "title" => "Blockquote (a long quote)", "img" => "/bin/img/icons/quote.png", "name" => "blockquote"),
			'cite' => array("code" => ",,[cite=],[/cite]", "title" => "Cite a source", "img" => "/bin/img/icons/book_open.png", "name" => "cite a source"),
			'spoiler' => array("code" => ",,[spoiler],[/spoiler]", "title" => "Spoiler (text that is initially hidden)", "img" => "/bin/img/icons/spoiler.png", "name" => "spoiler"),
			'ul' => array("code" => "&lt;ul&gt;NL&lt;li&gt;&lt;/li&gt;NL&lt;li&gt;&lt;/li&gt;NL,&lt;/ul&gt;,*,NL*NL*NL[/list]", "title" => "Unordered (bulleted) list", "img" => "/bin/img/icons/text_list_bullets.png", "name" => "unordered list"),
			'ol' => array("code" => "&lt;ol&gt;NL&lt;li&gt;&lt;/li&gt;NL&lt;li&gt;&lt;/li&gt;NL,&lt;/ol&gt;,[olist]NL*,NL*NL*NL[/olist]", "title" => "Ordered (numbered) list", "img" => "/bin/img/icons/text_list_numbers.png", "name" => "ordered list"),
			'img' => array("title" => "Generate, upload, or display a single image", "img" => "/bin/img/icons/image.png", "name" => "image", "onclick" => "img.init({fieldId:'#FIELDID#'})"),
			'gallery' => array("code" => ",,[gallery|thumb|show=|caption=],[/gallery] ", "title" => "Image Gallery", "img" => "/bin/img/icons/images.png", "name" => "image gallery"),
			'emoticon' => array("code" => ",,&lt;!--emoticon:,--&gt;", "title" => "emoticon", "img" => "/bin/img/icons/emoticons/smile.png", "name" => "emoticon")
		);
	}
	
	function output(){
		
		$ret = '
		<div class="htmltools'.($this->bbcode ? ' bbcode' : '').'">
			<input type="hidden" name="htmltools-field" value="'.$this->field.'"/>
			<table border="0" cellpadding="0" cellspacing="0" width="100%" class="htmltools-table">
				<tr>
					';
					foreach($this->in as $key => $in){
						$ret.= '<td'.(count($this->include) && !in_array($key, $this->include) ? ' style="display:none"' : '').'><a href="#'.$key.'" class="insert" rel="'.$in['code'].'" title="'.$in['title'].'"'.($in['onclick'] ? ' onclick="'.$in['onclick'].'"' : '').'><img src="'.$in['img'].'" alt="'.$in['name'].'"/></a></td>'."\n";
						$ret = str_replace('#FIELDID#', $this->field, $ret);
					}
					/*if(count($this->include) && in_array("links", $this->include)) {
						$ret.= '<td nowrap="nowrap" width="100%">
							<div class="linkgen">
								<div class="linkgen1">
									<table border="0" cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<td class="linkgen1">Link generator</td>
											<td width="100%">
												<div style="margin-right:6px"><input type="text" placeholder="Start typing here to find a page on this site..."/></div>
											</td>
										</tr>
									</table>
								</div>
							</div>
						</td>';
					} else {
						if($bbcode) $bbcodecell = 'width="100%"';
						else echo '<td width="100%">&nbsp;</td>';
					}*/
					$ret.= ($bbcode && !$incl['no_bbcode_link'] ? '<td nowrap="nowrap" '.$bbcodecell.'>&nbsp; <a href="/bbcode.htm" target="_blank" class="arrow-link">BB Code guide</a></td>' : '').'
				</tr>
			</table>
		</div>';
		return $ret;	
		
	}
	
}
	

function outputToolbox($field, $include="", $bbcode="") {
	
	$tb_it = rand(0,99999);
	
	foreach($include as $i) {
		$incl[$i] = true;
	}
	
	?>
	<div class="htmltools<?=($bbcode ? ' bbcode' : '')?>">
		<input type="hidden" name="htmltools-field" value="<?=$field?>"/>
		<table border="0" cellpadding="0" cellspacing="0" class="htmltools-table">
			<tr>
				<td<?=($incl && !$incl['h3'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel="&lt;h3&gt;,&lt;/h3&gt;,[h3],[/h3]" title="Heading level 3; Table of contents are created via this tag (if applicable)!"><img src="/bin/img/icons/text_heading_3.png" alt="heading 3"/></a></td>
				<td<?=($incl && !$incl['h4'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel="&lt;h4&gt;,&lt;/h4&gt;,[h4],[/h4]" title="Heading level 4"><img src="/bin/img/icons/text_heading_4.png" alt="heading 4"/></a></td>
				<td<?=($incl && !$incl['h5'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel="&lt;h5&gt;,&lt;/h5&gt;,== , ==" title="Heading level 5"><img src="/bin/img/icons/text_heading_5.png" alt="heading 5"/></a></td>
				<td<?=($incl && !$incl['h6'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel="&lt;h6&gt;,&lt;/h6&gt;,=== , ===" title="Heading level 6"><img src="/bin/img/icons/text_heading_6.png" alt="heading 6"/></a></td>
				<td<?=($incl && !$incl['b'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel="&lt;b&gt;,&lt;/b&gt;,&lt;b&gt;,&lt;/b&gt;" title="Bold text"><img src="/bin/img/icons/text_bold.png" alt="bold"/></a></td>
				<td<?=($incl && !$incl['i'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel="&lt;i&gt;,&lt;/i&gt;,&lt;i&gt;,&lt;/i&gt;" title="Italic text"><img src="/bin/img/icons/text_italic.png" alt="italic"/></a></td>
				<td<?=($incl && !$incl['big'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel="&lt;big&gt;,&lt;/big&gt;,&lt;big&gt;,&lt;/big&gt;" title="big text"><img src="/bin/img/icons/text_big.png" alt="big"/></a></td>
				<td<?=($incl && !$incl['small'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel="&lt;small&gt;,&lt;/small&gt;,&lt;small&gt;,&lt;/small&gt;" title="small text"><img src="/bin/img/icons/text_small.png" alt="small"/></a></td>
				<td<?=($incl && !$incl['strikethrough'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel="&lt;strikethrough&gt;,&lt;/strikethrough&gt;,[strike],[/strike]" title="Strike-through text"><img src="/bin/img/icons/text_strikethrough.png" alt="strikethrough text"/></a></td>
				<td<?=($incl && !$incl['p'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel="&lt;p&gt;,&lt;/p&gt;,&lt;p&gt;,&lt;/p&gt;" title="Insert paragraph"><img src="/bin/img/icons/paragraph.png" alt="paragraph"/></a></td>
				<td<?=($incl && !$incl['br'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel="&lt;br/&gt;,NL,&lt;br/&gt;,NL" title="New line"><img src="/bin/img/icons/nl.png" alt="new line"/></a></td>
				<td<?=($incl && !$incl['a'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel="&lt;a href=&quot;&quot;&gt;,&lt;/a&gt;,[url=],[/url]" title="Hyperlink"><img src="/bin/img/icons/link.png" alt="hyperlink"/></a></td>
				<td<?=($incl && !$incl['blockquote'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel="&lt;blockquote&gt;,&lt;/blockquote&gt;,[quote],[/quote]" title="Blockquote (a long quote)"><img src="/bin/img/icons/quote.png" alt="blockquote"/></a></td>
				<td<?=($incl && !$incl['cite'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel=",,[cite=],[/cite]" title="Cite a source"><img src="/bin/img/icons/book_open.png" alt="cite a source"/></a></td>
				<td<?=($incl && !$incl['spoiler'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel=",,[spoiler],[/spoiler]" title="Spoiler (text that is initially hidden)"><img src="/bin/img/icons/spoiler.png" alt="spoiler"/></a></td>
				<td<?=($incl && !$incl['ul'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel="&lt;ul&gt;NL&lt;li&gt;&lt;/li&gt;NL&lt;li&gt;&lt;/li&gt;NL,&lt;/ul&gt;,*,NL*NL*NL" title="Unordered (bulleted) list"><img src="/bin/img/icons/text_list_bullets.png" alt="unordered list"/></a></td>
				<td<?=($incl && !$incl['ol'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel="&lt;ol&gt;NL&lt;li&gt;&lt;/li&gt;NL&lt;li&gt;&lt;/li&gt;NL,&lt;/ol&gt;,#,NL#NL#NL" title="Ordered (numbered) list"><img src="/bin/img/icons/text_list_numbers.png" alt="ordered list"/></a></td>
				<td<?=($incl && !$incl['image'] && !$incl['img'] ? ' style="display:none"' : '')?>><a href="#insimg" class="insert" rel="" onclick="img.init({fieldId:'<?=$field?>'})" title="Generate, upload, or display a single image"><img src="/bin/img/icons/image.png" alt="image"/></a></td>
				<td<?=($incl && !$incl['gallery'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel="[[M||/location/of/media/||CAPTION]], ,[[M||/location/of/media/||CAPTION]], " title="Image Gallery"><img src="/bin/img/icons/images.png" alt="image gallery"/></a></td>
				<td<?=($incl && !$incl['emoticon'] ? ' style="display:none"' : '')?>><a href="javascript:void(0)" class="insert" rel=",,&lt;!--emoticon:,--&gt;" title="emoticon" onclick="$(this).children().toggleClass('on'); loadEmotes('<?=$tb_it?>', '<?=$field?>');"><img src="/bin/img/icons/emoticons/smile.png" alt="emoticon"/></a></td>
				<?
				/*if($include && in_array("links", $include)) {
					?>
					<td nowrap="nowrap" width="100%">
						<div class="linkgen">
							<div class="linkgen1">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td class="linkgen1">Link generator</td>
										<td width="100%">
											<div style="margin-right:6px"><input type="text" placeholder="Start typing here to find a page on this site..."/></div>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</td>
					<?
				} else {
					if($bbcode) $bbcodecell = 'width="100%"';
					else echo '<td width="100%">&nbsp;</td>';
				}*/
				?>
				<?=($bbcode && !$incl['no_bbcode_link'] ? '<td nowrap="nowrap" width="100%" style="text-align:right">&nbsp; <a href="/bbcode.htm" target="_blank" class="arrow-link">BB Code guide</a></td>' : '')?>
			</tr>
			<?
			if($include && in_array("emoticon", $include)) {
				?>
				<tr>
					<td colspan="21"><div id="emoticon-space-<?=$tb_it?>" class="tb-subspace emoticon-space"></div></td>
				</tr>
				<?
			}
			if($include && (in_array("img", $include) || in_array("image", $include))) {
				?>
				<tr>
					<td colspan="21"><div id="imggen-space-<?=$tb_it?>" class="tb-subspace imggen-space"></div></td>
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

if(isset($_POST['imggen_form'])) {
	if(!$field = $_POST['field']) die("Error: No field id given");
	?>
	<div class="imggen">
		<iframe src="/upload.php" frameborder="0" style="width:100%; height:55px;"></iframe>
	</div>
	<?
	exit;
}

if(isset($_POST['load_media_dir'])) {
	
	$dir = $_POST['load_media_dir'];
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
	echo '<br style="clear:left;"/>';
	exit;
}

?>