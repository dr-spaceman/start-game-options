<?
require_once "class.pglinks.php";
require_once "markdown.php";

function bb2html($text, $vars=''){
	//this stand-alone function has been depreciated and incoporated into the below class
	//but is widely used still -- workaround:
	$n = new bbcode();
	$n->text = $text;
	$varsArr = array();
	$varsArr = explode(" ", $vars);
	foreach($varsArr as $var){ if($var != '') $n->{$var} = true; }
	return $n->bb2html();
}

class bbcode {
	
	public $params; // array parameters with which to outpub bb2html:
		// prepend_domain: prepend the domain name to local links
		// pages_only: evaluate [[page links]] only and ignore other markup
		// pagelinks: don't evaluate [[page links]] if false
		// minimal: evaluate basic markup only
		// inline_citations: output [cite] citations cleanly inline instead of in a list at the end of the html output
		// links_rm_duplicates: remove duplicate links
		// markdown: Don't convert to Markdown if false
	var $openTags = array();
	var $headings_offset; //increase heading level
		//for example $headings_offset=2: <h1> => <h3>
	public $sources;
	public $space_x; //max-width in pixels to output videos and images (default = 620)
	private $footnotes_sources_i;
	private $footnotes_sources = array();
	
	public function __construct($t=''){
		$this->space_x = 620;
		if($t) $this->t = $t;
	}
	
	public function __set($name, $value){
		if($name == "text"){
			//Just set a new text block --
			//Reset (some) parameters and variables
			$this->params=array();
			//unset($this->headings);
			//unset($this->sources);
			unset($this->openTags);
			unset($GLOBALS['footnotes_sources']);
			unset($this->footnotes_sources);
			$this->t = $value;
		}
		else $this->{$name} = $value;
	}
	
	function bb2html($text=''){
		
		if($text){
			$this->params=array();
			unset($this->headings);
			unset($this->openTags);
			unset($GLOBALS['footnotes_sources']);
			unset($this->footnotes_sources);
			$this->t = $text;
		}
		
		$this->h_level = 0;
		
		$ppd = $this->params['prepend_domain'];
		
		if($this->params['pagelinks'] !== false){
			$pglinks = new pglinks();
			$pglinks->prepend_domain = $ppd;
			$pglinks->rm_duplicates = $this->params['links_rm_duplicates'];
		}
		
		if(!$this->t) return;
		
		//$this->t = stripslashes($this->t);
		
		//don't parse html & bbcode inside <code/>
		$this->t = preg_replace('@<code>(.*?)</code>@ise', "wrapCode('\\1')", $this->t);
		
		if($this->params['pages_only']) return $pglinks->parse($this->t);
		
		if($this->params['minimal']){
			
			// minimal replacing -- Markdown & pagelinks
			
			if($this->params['pagelinks'] !== false) $this->t = $pglinks->parse($this->t);
			
			if($this->params['markdown'] !== false) $this->t = Markdown($this->t);
			
		} else {
			
			//Parse stuff before Markdown
			//Give block elements the markdown attr so their contents get marked-down
			$tags = array(
				'@\[aside\|?(.*?)\](.*?)\[/aside\]@ise',
				'/<blockquote>/',
				'/<aside>/',
				//'/<(\/)?aside>/is',
				'@\{img:([a-z0-9-_!\.]+)\|?(.*?)\}@ise',
			);
			$tags_r = array(
				"evalAside('\\1', '\\2')",
				'<blockquote markdown="1">',
				'<aside markdown="1">',
				//'<$1div aside>',
				"evalImgUploadTag('\\1', '\\2')",
				"evalFootnoteHack('\\1')",
			);
			$this->t = preg_replace($tags, $tags_r, $this->t);
			
			//Footnotes Hack -- add uncited footnotes
			//create global variables that Markdown can look for and add (hack)
			$regx = '/^ {0,3}\[\^\]:[ ]?(.+?)$/sm';
			$this->t = preg_replace_callback($regx, array($this, 'footnotesHack_callback'), $this->t);
			if($this->footnotes_sources_i){
				$GLOBALS['footnotes_sources'] = $this->footnotes_sources;
			}
			$GLOBALS['footnotes_heading'] = '<h1>Sources</h1>';
			
			if($this->params['markdown'] !== false) $this->t = Markdown($this->t);
			
			$tags = array(
				'@\~([^\s])(.*?)([^\s])\~(\((.*?)\))?@e', //strikethrough
				//'@\[b\](.*?)\[/b\]@is', 
				//'@\[i\](.*?)\[/i\]@is', 
				'@\[spoiler\](.*?)\[/spoiler\]@is',
				//'@\[big\](.*?)\[/big\]@is', 
				//'@\[small\](.*?)\[/small\]@is', 
				//'@\[strike\](.*?)\[/strike\]@is',
				//'@\[url\](.*?)\[/url\]@ise', 
				//'@\[url=(.*?)\](.*?)\[/url\]@ise', 
				//"@\[img\](.*?)\[/img\]@is",
				//'@\[img\|(.*?)\](.*?)\[/img\](?:\s)?@ise',
				'@\[gallery\|?(.*?)\](.*?)\[/gallery\]@ise',
				'@\[video\|?(.*?)\](.*?)\[/video\]@ise',
				'@\[audio\|?(.*?)\](.*?)\[/audio\]@ise',
				'@\{Template:(.*?)\}(?:\s)?@ise',
			);
			$tags_r = array(
				"bbEvalDel('\\1','\\2','\\3','\\4')",
				//'<b>$1</b>', 
				//'<i>$1</i>', 
				'<span class="spoiler"><del>$1</del></span>', 
				//'<big>$1</big>', 
				//'<small>$1</small>', 
				//'<del>$1</del>',
				//"evaluateLink('\\1', '\\1', '$ppd')",
				//"evaluateLink('\\1', '\\2', '$ppd')",
				//'<img src="$1" alt="my picture"/>',
				//"evalImgTag('\\2', '\\1')",
				"embedGallery('\\1', '\\2')",
				"embedVideo('\\2', '\\1', '$this->space_x')",
				"embedAudio('\\2', '\\1')",
				"insertTemplate('\\1')",
			);
			$this->t = preg_replace($tags, $tags_r, $this->t);
			
			// [[Page links]]
			if(!$this->params['no_pagelinks']) $this->t = $pglinks->parse($this->t);
			
			/*if(strstr($this->t, "[quote]")) {
				$open = '<blockquote>';
				$close = '</blockquote>';
				preg_match_all ('@\[quote\]@i', $this->t, $matches);
				$opentags = count($matches['0']);
				preg_match_all ('@\[/quote\]@i', $this->t, $matches);
				$closetags = count($matches['0']);
				$unclosed = $opentags - $closetags;
				for ($i = 0; $i < $unclosed; $i++) {
					$this->t .= '</blockquote>';
				}
				$this->t = str_replace ('[quote]', $open, $this->t);
				$this->t = str_replace ('[/quote]', $close, $this->t);
			}*/
			
			/*if(strstr($this->t, "[cite") || strstr($this->t, "[source")) {
				preg_match_all("@\[(cite|source)=?(.*?)\[/(cite|source)\]@is", $this->t, $matches, PREG_PATTERN_ORDER);
				for($i = 0; $i < count($matches[0]); $i++){
					$url = "";
					$name ="";
					list($url, $name) = explode("]", $matches[2][$i]);
					if($this->params['inline_citations']){
						$n = ++$this->i_source;
						if($matches[1][$i] == "source"){
							$this->t = str_replace($matches[0][$i], ' <cite class="source">['.($url ? '<a href="'.$url.'" target="_blank">'.$name.'</a>' : '<i>'.$name.'</i>').']</cite>', $this->t);
						} else {
							$this->t = str_replace($matches[0][$i], '<cite class="cite tooltip" title="'.htmlSC($name).'">'.($url ? '<a href="'.$url.'" target="_blank">'.$n.'</a>' : '<i>source</i>').'</cite>', $this->t);
						}
					} else {
						$n = ++$this->i_source;
						if($matches[1][$i] == "cite") $this->t = str_replace($matches[0][$i], '<cite class="cite" id="citeback-'.$n.'"><a href="#cite-'.$n.'" onclick="$(\'#cite-'.$n.'\').addClass(\'on\');">'.$n.'</a></cite>', $this->t);
						else $this->t = str_replace($matches[0][$i], '', $this->t);
						$this->sources .= '<li id="cite-'.$n.'">'.($url ? '<a href="'.$url.'" target="_blank">'.$name.'</a>' : $name).($matches[1][$i] == "cite" ? '&nbsp;<span class="citeback"><a href="#citeback-'.$n.'" onclick="$(this).closest(\'li\').removeClass(\'on\');" title="jump back to the cited text" class="citeback arrow-up"></a></span>' : '').'</li>';
					}
				}
				if($this->sources && !$this->params['sources_noappend']){
					$this->t.= $this->outputSources();
				}
			}*/
			
			//iterate lines, finding stuff like headings and definition lists
			$this->parseLines();
			//$this->t = str_replace("\n</", "</", $this->t);
			/*$this->t = str_replace("\n</li>", "</li>", $this->t);
			$this->t = str_replace("\n</ol>", "</ol>", $this->t);
			$this->t = str_replace("\n</ul>", "</ul>", $this->t);*/
			
			//toc
			if(strstr($this->t, "<!--toc-->") && $this->toc){
				$this->t = str_replace("<!--toc-->", '<div class="toc"><b>Contents</b>'.$this->toc.'</div>', $this->t);
			}
		
		}
		
		if($this->params['emote']){
			$f = array(
				'/(^| )(:|=|;){1}([\|\)\(PD0oO]{1})/me',
				'@\<!--emoticon:([a-z0-9-_\.]+)-->@ise'
			);
			$r = array(
				"emoteImg('\\1', '\\2\\3')",
				"emoteImg('', '', '\\1')"
			);
			$this->t = preg_replace($f, $r, $this->t);
			$this->t = str_replace("<3", '<span class="emoticon" style="background-image:(/bin/img/icons/emoticons/_heart.png);" title="&lt;3">&nbsp;</span>', $this->t);
		}
		
		if($ppd){
			$this->t = str_replace('href="/', 'href="http://videogam.in/', $this->t);
			$this->t = str_replace('src="/', 'src="http://videogam.in/', $this->t);
		}
		
		//Tidy up
		//fixes unclosed tags
		//$this->t = tidyHtml($this->t);
		// issues (ie adding <p> tags)
		
		return trim($this->t);
		
	} // bb2html()
	
	function footnotesHack_callback($matches){
		$this->footnotes_sources_i++;
		$this->footnotes_sources[$this->footnotes_sources_i] = $matches[1];
		return '';//"[^__source". $this->footnotes_sources_i . "]: " . $matches[1];
	}
	
	function outputSources(){
		
		if(!$this->sources) return;
		return '<h1>Sources</h1>'."\n".'<ol>'.$this->sources.'</ol>';
		unset($this->sources);
		
	}
	
	function html2bb($text=''){
		return $this->html2markdown($text);
	}
	
	function html2markdown($text=''){
		
		if($text) $this->t = $text;
		
		if(!strstr($this->t, "<")) return $this->t;
		
		$tags = array(
			'@\</?(i|em)\>@is', 
			'@\</?(b|strong)\>@is',
			'@\<span class="spoiler"\>\<del\>(.*?)\</del\>\<\/span\>@is', 
			'@\</?del\>(.*?)\</del\> \</?ins\>(.*?)\</ins\>@is',
			'@\</?del\>@is',
			//'@<big>(.*?)</big>@is', 
			//'@<small>(.*?)</small>@is', 
			//'@<strikethrough>(.*?)</strikethrough>@is',
			'@\<a (.*?)\>(.*?)\<\/a\>@ise', 
			'@\<img (.*?)\>@ise',
			'@\<h([0-6])\>(.*?)\</h([0-6])\>@ise',
			'@\</?p\>@i',
			"/\n{2,}/is",
		);
		$tags_r = array(
			'*',
			'**',
			'[spoiler]$1[/spoiler]',
			'~$1~($2)',
			'~',
			//'[big]$1[/big]',
			//'[small]$1[/small]',
			//'[strike]$1[/strike]',
			"bbFilterLink('\\1','\\2')",
			"bbFilterImg('\\1')",
			"bbFilterHeading('\\1', '\\2')",
			"\n",
			"\n",
		);
		$this->t = preg_replace($tags, $tags_r, $this->t);
		
		/*if(strstr($this->t, "<blockquote>")) {
			$open = '[quote]';
			$close = '[/quote]';
			preg_match_all ('@<blockquote>@i', $text, $matches);
			$opentags = count($matches['0']);
			preg_match_all ('@</blockquote>@i', $text, $matches);
			$closetags = count($matches['0']);
			$unclosed = $opentags - $closetags;
			for ($i = 0; $i < $unclosed; $i++) {
				$text .= '[/quote]';
			}
			$text = str_replace ('<blockquote>', $open, $text);
			$text = str_replace ('</blockquote>', $close, $text);
		}*/
		
		$this->t = trim($this->t);
		
		return $this->t;
		
	} // html2bb()
	
	function parseLines(){
		
		$lines = str_replace("\r\n", "\n", $this->t);
		$lines = explode("\n", $lines);
		$num_lines = count($lines);
		for($n = 0; $n < $num_lines; $n++){
			$lines[$n] = ltrim($lines[$n]);
		}
		//echo count($lines);print_r($lines);return $this->t;
		
		for($n = 0; $n < $num_lines; $n++){
			
			$line = $lines[$n];
			$p = substr($line, 0, 3) == '<p>' ? '<p>' : '';
			$line = str_replace('<p>', '', $line);
			
			$s1 = substr($line, 0, 1);
			$s2 = substr($line, 0, 2);
			$s3 = substr($line, 0, 3);
			$lnext = $n < ($num_lines - 1) ? substr($lines[($n + 1)], 0, 1) : '';
			
			//echo $n.htmlspecialchars($s3)." ";
			
			if($s1 == ";" || $s2 == "::"){
				if($s1 == ";"){
					$line = (!$this->openTags['dl'] ? $this->openTag("dl") : '') . '<dt>' . trim(substr($line, 1));
					if(strstr($line, "::")){
						$items = explode("::", $line);
						for($i=0; $i < count($items); $i++){
							if($i == 0) $items[$i] = trim($items[$i]) . '</dt>';
							else $items[$i] = '<dd>' . trim($items[$i]) . '</dd>';
						}
						$line = implode("", $items);
					} else $line.= '</dt>';
				} elseif($s2 == "::") {
					$items = explode("::", $line);
					$line = '';
					foreach($items as $item){
						$item = trim($item);
						if($item != '') $line.= '<dd>'.$item.'</dd>';
					}
					$line = (!$this->openTags['dl'] ? $this->openTag("dl") : '') . $line;
				}
				if($lnext != ';' && $lnext != ':') $line.= $this->closeTags();
			} else {
				
				//track headings (for TOC, etc) and convert to two-levels below (ie h1 > h3)
				
				preg_match('@<h([1-6])>(.*?)</h([1-6])>@is', $line, $m);
				if($m){
					
					list($h_string, $h_level, $h_tag, ) = $m;
					
					$h_level_o = $h_level + ($this->headings_offset ? $this->headings_offset : 0);
					$h_tag = trim($h_tag);
					
					$h_tag_formatted = strip_tags($h_tag);
					$h_tag_formatted = formatNameURL($h_tag_formatted, 1);
					
					$line = str_replace($h_string, '<h'.$h_level_o.'>'.$h_tag.'<a name="'.$h_tag_formatted.'"></a></h'.$h_level_o.'>', $line);;
					
					$toc = '';
					if($h_level == 1 || $h_level == 2){
						if($h_level > $this->h_level) $toc = '<ol>';
						elseif($h_level < $this->h_level) $toc = '</ol>';
						$toc.= '<li><a href="#'.$h_tag_formatted.'">'.strip_tags($h_tag).'</a></li>';
					}
					
					$this->toc.= $toc;
					$this->headings[] = $h_tag;
					$this->h_level = $h_level;
					
				}
			}
			
			$lines[$n] = $p . $line . "\n";
			
		}
		
		$lines_str = implode("", $lines) . $this->closeTags();
		$lines_str = str_replace("\r", "", $lines_str);
		//$lines_str = str_replace("</p>\n<p>", "<br/>\n", $lines_str);
		$this->t = $lines_str;return;
		
	}
	
	function openTag($tag){
		$this->openTags[$tag]++;
		return '<'.$tag.'>';
	}
	
	function closeTag($tag){
		if(!$this->openTags[$tag]) return;
		$this->openTags[$tag]--;
		return '</'.$tag.'>';
	}
	
	function closeTags(){
		if(!$this->openTags) return;
		foreach($this->openTags as $tag => $n){
			while($this->openTags[$tag] >= 1){
				$ret.= $this->closeTag($tag) . "\n";
			}
		}
		return $ret;
	}
	
}

function bbFilterLink($inp, $words){
	
	//check a given link for conflicts
	//@ret markdown-encoded link
	
	$words = strip_tags($words);
	
	$inp = str_replace("'", "", $inp);
	$inp = str_replace('"', '', $inp);
	$inp = str_replace('\\', '', $inp);
	$attrs = array();
	$attrs = explode(" ", $inp);
	foreach($attrs as $attr){
		if(substr($attr, 0, 5) == "href=") $href = substr($attr, 5);
	}
	if(strstr($inp, "pglink")){
		$hrefs = explode("/", $href);
		$pglink = formatName($hrefs[2]);
		return '[['.$pglink.($pglink != $words ? '|'.$words : '').']]';
	}
	return '['.$ret.']('.$words.')';
	
}

function bbFilterImg($inp) {
	$inp = str_replace("'", "", $inp);
	$inp = str_replace('"', '', $inp);
	$inp = str_replace('\\', '', $inp);
	$attrs = array();
	$attrs = explode(" ", $inp);
	foreach($attrs as $attr) {
		list($k, $v) = explode("=", $attr);
		if($k == "src") $ret = $v;
	}
	return '[img]'.$ret.'[/img]';
}

function bbFilterHeading($h, $content){
	for($i=0; $i<$h; $i++) $hashes.="#";
	return $hashes . $content . $hashes;
}

/*function BBencode($what, $desc) {
	$ret = "[".$what."]".$desc."[/".$what."]";
	if($what == "game") {
		$q = "SELECT gid FROM games WHERE title='".mysqli_real_escape_string($GLOBALS['db']['link'], $desc)."' LIMIT 1";
		if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) $ret = "[gid=".$dat->gid."/]";
	} elseif($what == "person") {
		$q = "SELECT pid FROM people WHERE name='".mysqli_real_escape_string($GLOBALS['db']['link'], $desc)."' LIMIT 1";
		if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) $ret = "[pid=".$dat->pid."/]";
	}
	return $ret;
}*/

function evaluateLink($a, $b, $ppd=false) {
	return '<a href="'.($ppd && substr($a, 0, 4) != "http" ? 'http://videogam.in' : '').$a.'">'.stripslashes($b).'</a>';
}

function evaluatePageLink($pg, $ppd=false) {
	// depreciated -- incorporated into class.pglinks.php
	return $pg;
}

function parseText($t) {
	
	//evaluate text input and fix errors
	
	$t = trim($t);
	//trim hidden tags like <!--more--> and <!--toc-->
	$t = preg_replace("/<!--\s*(.*?)\s*-->/i", "<!--$1-->", $t);
	$t = str_replace('<3', '<!--emoticon:_heart.png-->', $t);
	$t = strip_tags_attributes($t, "<a><abbr><acronym><aside><b><big><blockquote><br><cite><code><del><dl><dt><dd><em><fieldset><i><legend><li><ol><q><s><small><strike><sub><sup><s><strong><table><tbody><thead><tfoot><tr><td><th><ul>", "href,title,rel,src,start,alt");
	$t = strip_tags_selected($t, array("span","div"));
	
	//replace HTML headings with BB headings because sometimes we want to read headings (in order to output a ToC, for example)
	//$t = preg_replace("@<(/?)h([1-6])>@", "[$1h$2]", $t);
	
	//ltrim citations
	if(strstr($t, "[^")){
		$t = preg_replace("@ +\[\^@", "[^", $t);
	}
	
	//$t = preg_replace("@(^|\s){1}http([^\s]+)+@m", "$1[url]http$2[/url]", $t);
	
	$t = closeTags($t);
	
	return $t;
	
}

/*function heading_($h, $name){
	
	//output a HTML heading and record it for TOC
	
	global $headings;
	
	if($h == "5") $GLOBALS['headings'][] = '<li><a href="#'.urlencode($name).'">'.$name.'</a></li>';
	
	return '<h'.$h.' name="'.htmlSC($name).'">'.$name.'</h'.$h.'>'."\r\n\r\n";
	
}*/

function embedVideo($url, $attr='', $width=620){
	
	$url = trim($url);
	
	$attrs = array();
	if($attr){
		$attrs = explode("|", $attr);
		$position = $attrs[0];
		$caption = $attrs[1];
	}
	//if($position != "left" && $position != "right" && $position != "center") $position = "center";
	
	$ret = getVideoEmbedCode($url);
	
	if(!$ret) return '<a href="'.$url.'" target="_blank" class="arrow-link">My video</a>';
	if($position || $caption) return '<dl class="thumbnail '.$position.' embvideo"><dt>'.$ret.'</dt>'.($caption ? '<dd>'.$caption.'</dd>' : '').'</dl>';
	else return '<div class="embvideo">'.$ret.'</div>';
	
}

function embedAudio($url, $attr='') {
	
	$url = trim($url);
	
	$attrs = array();
	if($attr){
		$attrs = explode("|", $attr);
		$position = $attrs[0];
		$caption = $attrs[1];
	}
	if($position != "left" && $position != "right" && $position != "center") $position = "center";
	
	//check URL for Sblog post
	preg_match_all("/^(http:\/\/videogam.in)?\/?s(\d+)/", $url, $match);
	if($nid = $match[2][0]){
		require_once "class.posts.php";
		try{ $post = new post($nid); }
		catch(Exception $e){ unset($post); }
		if($post && $post->content['audio_file']) $url = 'http://videogam.in'.$post->content['audio_file'];
	}
	
	$ret = '<script type="text/javascript" src="https://media.dreamhost.com/ufo.js"></script><div id="'.$url.'"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.</div><script type="text/javascript">var FO = { movie:"https://media.dreamhost.com/mediaplayer.swf",width:"100%",height:"20",majorversion:"7",build:"0",bgcolor:"#FFFFFF",flashvars:"file='.$url.'&showdigits=true&autostart=false" };UFO.create(FO,"'.$url.'");</script>';
	
	if(!$ret) return '<a href="'.$url.'" target="_blank" class="arrow-link">My Audio</a>';
	if($position || $caption) return '<div class="thumbnail '.$position.' embaudio" style="width:'.($position == "center" ? "auto" : "200px").';"><div class="image">'.$ret.'</div>'.($caption ? '<div class="caption">'.$caption.'</div>' : '').'</div>';
	else return '<div class="embaudio">'.$ret.'</div>';
	
}

function embedGallery($str='', $conts=''){
	
	require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/class.img.php");
	$_gallery = new gallery();
	$_gallery->files = $conts;
	$_gallery->opt_str = $str;
	$_gallery->parse();
	return $_gallery->HTMLencode();
	
}

function evalImgUploadTag($img_src, $str=''){
	
	// {img:FILE|SIZE(thumb,thumbnail,screen,screenshot,small,medium,large,X)|POSITION(left,right,center)|caption=CAPTION|link=LINK|optimize=(true[default],false)}
	
	require_once("class.img.php");
	$imgs = new imgs;
	
	$arr = array();
	$arr = explode("|", $str);
	foreach($arr as $opt){
		if(substr($opt, 0, 8)=="caption=") $caption = substr($opt, 8);
		if(substr($opt, 0, 5)=="link=") $link = substr($opt, 5);
		if(substr($opt, 0, 9)=="optimize=") $optimize = substr($opt, 9);
		if(in_array($opt, array("left","right","center"))) $position = $opt;
		if(is_numeric($opt) === TRUE && (int)$opt == $opt) $width = $opt;
		if(in_array($opt, array_keys($imgs->sizes))) $size = $imgs->sizes[$opt];
	}
	
	try{ $img = new img($img_src); }
	catch(Exception $e){ unset($img); }
	
	if(!$img->established) return outputThumbnail(array("position" => $position, "link" => $link, "src" => '/bin/img/icons/question_block_med.png', "caption" => $caption, "width" => $width));
	
	if($optimize == "false" || $optimize == "0"){
		$size = "0";
		$width = $img->img_width;
	} else {
		if($width && !isset($size)){
			if($width <= 100) $size='tn';
			elseif($width <= 240) $size = 'sm';
			elseif($width <= 350) $size = 'md';
			else $size = 'op';
		}
		if(!$size) $size = 'op';
		//if(!$width) $width = $imgs->sizes_widths[$size];
		//if($width > $img->img_width) $width = $img->img_width;
	}
	
	$ret = array(
		"imgname" => $img_src,
		"position" => $position,
		"link" => "/image/".$img_src,
		"caption" => $caption,
		"src" => $img->src[$size],
		"width" => $width,
		"imguplfile" => $img->src['original']
	);
	
	return outputThumbnail($ret);
	
}

function evalImgTag($img_src, $str=''){
	
	$arr = array();
	$arr = explode("|", $str);
	
	$ret = array(
		"position" => $arr[0],
		"link" => $arr[1],
		"caption" => $arr[2],
		"src" => $img_src
	);
	
	return outputThumbnail($ret);
	
}

function outputThumbnail($vars) {
	
	// @ $vars array = position, link (page, large image, etc), src, caption, width, imguplfile
	
	if($vars['position'] != "left" && $vars['position'] != "right" && $vars['position'] != "center") $vars['position'] = "nofloat";
	$vars['caption'] = stripslashes($vars['caption']);
	$vars['caption'] = nl2br($vars['caption']);
	$vars['caption'] = str_replace("\r", "", $vars['caption']);
	$vars['caption'] = str_replace("\n", "", $vars['caption']);
	$p_capt = htmlSC($vars['caption']);
	$p_capt = str_replace('[', '', $p_capt);
	$p_capt = str_replace(']', '', $p_capt);
	$p_capt = strip_tags($p_capt);
	
	/*if(!$vars['width']){
		$vars['getimagesize'] = @getimagesize($_SERVER['DOCUMENT_ROOT'].$vars['src']);
		$vars['width'] = $vars['getimagesize'][0];
	}*/
	
	if($vars['src'] == "/bin/img/icons/question_block_med.png") $alt = "File not found";
	else $alt = $p_capt;
	
	$capt_tag = ($vars['width'] && $vars['width'] <= 100 ? "small" : "div");
	
	$ret = 
	'<div class="imagefigure '.$vars['position'].($vars['width'] ? ' noresize' : '').'" title="'.$alt.'" style="width:'.($vars['width'] ? $vars['width'].'px' : 'auto').';">'.
			($vars['link'] ? '<a href="'.$vars['link'].'" title="'.$p_capt.'" class="'.($vars['imguplfile'] ? 'imgupl' : '').'"'.($vars['imgname'] ? ' data-imgname="'.$vars['imgname'].'"' : '').'>' : '').
				'<img src="'.$vars['src'].'" alt="'.$alt.'" border="0"/>'.
			($vars['link'] ? '</a>' : '').
			($vars['imguplfile'] ? '<a href="'.$vars['link'].'" class="permalink" title="permanent link for this image"></a>' : '').
			($vars['caption'] ? '<'.$capt_tag.' class="caption">'.$vars['caption'].'</'.$capt_tag.'>' : '').
	'</div>';
	
	return $ret;
}

function evalAside($attr='', $cont=''){
	
	if(strstr($attr, "%") && strlen($attr >= 4)) $width = $attr;
	elseif(is_int($attr)){
		$width = ((int)$attr > 780 ? 780 : (int)$attr) . 'px';
	}
	elseif($attr == "short" || $attr == "s" || $attr == "small") $class = "short";
	elseif($attr == "medium" || $attr == "med" || $attr == "m") $class = "medium";
	//elseif($attr == "large" || $attr == "l" || $attr == "long") $class = "long";
	
	return '<aside'.($width ? ' style="width:'.$width.'"' : '').($class ? ' class="'.$class.'"' : '').' markdown="1">'.$cont.'</aside>';
	
}

function strip_tags_attributes($string, $allowtags=NULL, $allowattributes=NULL){
	
	//comments & other allowable tags
	$allow = array("f" => array('<!--', '-->', '<br/>', '<br />'), "p" => array('``!--', '--``', '``br/``', '``br/``'));
	$string = str_replace($allow['f'], $allow['p'], $string);
	$allow_regex = array("f" => '@\<(https?:\/\/)([^\>]+)\>@is', "p" => '[URL]$1$2 ');
	$string = preg_replace($allow_regex['f'], $allow_regex['p'], $string);
	
    $string = strip_tags($string,$allowtags); 
    if (!is_null($allowattributes)) { 
        if(!is_array($allowattributes)) 
            $allowattributes = explode(",",$allowattributes); 
        if(is_array($allowattributes)) 
            $allowattributes = implode(")(?<!",$allowattributes); 
        if (strlen($allowattributes) > 0) 
            $allowattributes = "(?<!".$allowattributes.")"; 
        $string = preg_replace_callback("/<[^>]*>/i",create_function(
            '$matches', 
            'return preg_replace("/ [^ =]*'.$allowattributes.'=(\"[^\"]*\"|\'[^\']*\')/i", "", $matches[0]);'    
        ),$string); 
    }
	
	$string = str_replace($allow['p'], $allow['f'], $string);
	$allow_regex = array("f" => '@\[URL\](https?:\/\/)([^ ]+)@s', "p" => '<$1$2>');
	$string = preg_replace($allow_regex['f'], $allow_regex['p'], $string);
	
	return $string; 
}

function strip_tags_selected($text, $tags=array()){
	// POSSIBLE CONFLICT:
	// the following code will strip a tag that also BEGINS with the tagname, ie <appletid>
	$forbidden = array("applet","base","basefont","head","html","body","applet","object","iframe","frame","frameset","script","layer","ilayer","embed","bgsound","link","meta","style","title","blink","xml");
	$tags=array_merge($tags, $forbidden);
        foreach ($tags as $tag){
            if(preg_match_all('/<\/?'.$tag.'[^>]*>/iU', $text, $found)){
                $text = str_replace($found[0],$found[1],$text);
          }
        }

        return $text;
}

function closeTags($html) {
	#put all opened tags into an array
	preg_match_all ( "#<([a-z]+)( .*)?(?!/)>#iU", $html, $result );
	$openedtags = $result[1];
	$openedtags = array_diff($openedtags, array("img", "hr", "br"));
	$openedtags = array_values($openedtags);
	 
	#put all closed tags into an array
	preg_match_all ( "#</([a-z]+)>#iU", $html, $result );
	$closedtags = $result[1];
	$len_opened = count ( $openedtags );
	# all tags are closed
	if( count ( $closedtags ) == $len_opened )
	{
	return $html;
	}
	$openedtags = array_reverse ( $openedtags );
	# close tags
	for( $i = 0; $i < $len_opened; $i++ )
	{
	if ( !in_array ( $openedtags[$i], $closedtags ) )
	{
	$html .= "</" . $openedtags[$i] . ">";
	}
	else
	{
	unset ( $closedtags[array_search ( $openedtags[$i], $closedtags)] );
	}
	}
	return $html;
}

$emotes = array(
	":(" => "frown",
	"=(" => "frown",
	":D" => "laugh", 
	"=D" => "laugh",
	":|" => "meh",
	"=|" => "meh",
	":)" => "smile",
	"=)" => "smile",
	":P" => "tongue",
	"=P" => "tongue",
	";)" => "wink",
	":o" => "wow",
	":O" => "omfg",
	":0" => "omfg"
);

function emoteImg($prep, $em='', $emfile=''){
	global $emotes;
	if($em){
		return $prep.($emotes[$em] ? '<span class="emoticon" style="background-image:url(/bin/img/icons/emoticons/'.$emotes[$em].'.png);" title="'.$em.'">&nbsp;</span>' : $em);
	} elseif($emfile) {
		return '<span class="emoticon" style="background-image:url(/bin/img/icons/emoticons/'.$emfile.');" title="'.$emfile.'">&nbsp;</span>';
	}
}

function insertTemplate($title){
	require_once $_SERVER['DOCUMENT_ROOT']."/pages/class.pages.php";
	$tmpl = new pg("Template:".$title);
	$tmpl->loadData();
	return $tmpl->data->content;
}

function wrapCode($t){
	$t = htmlSC($t, "bbcode");
	//$t = htmlentities($t);
	$t = nl2br($t);
	$t = (string)str_replace(array("\r", "\r\n", "\n"), '', $t);
	//$t = stripslashes($t);
	return '<code>'.$t.'</code>';
}

// Curl helper function
function curl_get($url) {
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	$return = curl_exec($curl);
	curl_close($curl);
	return $return;
}

function getVideo($url, $append_wmode=true){
	// @var $append_wmode add wmode=transparent to the video iframe url so it doesn't conflict with positioned CSS layers (we'll only do this with Youtube and Vimeo since it might break others)
	if(preg_match('/(youtube\.com|youtu\.be)/i', $url)){
		//Youtube
		$oembed_url = 'http://www.youtube.com/oembed?url='.rawurlencode($url).'&format=xml&maxwidth=700';
		$oembed = @simplexml_load_string(curl_get($oembed_url));
	} elseif(strstr($url, "vimeo.com")) {
		//Vimeo
		$oembed_url = 'http://vimeo.com/api/oembed.json?url='.rawurlencode($url).'&maxwidth=700';
		$oembed = json_decode(curl_get($oembed_url));
	}
	if(!$oembed) return false;
	if($append_wmode !== false) $oembed->html = append_wmode($oembed->html);
	return $oembed;
}

function getVideoEmbedCode($url){
	$video = getVideo($url);
	if(!$video->html) return false;
	return html_entity_decode($video->html);
}

function append_wmode($html){
			preg_match("/src=[\"\']?([^\"\']*)/i", $html, $matches);
			if($iframe_url = $matches[1]){
				$iframe_url.= (strstr($html, "?") ? "&" : "?") . "wmode=transparent";
				$html = str_replace($matches[1], $iframe_url, $html);
			}
			return $html;
}

function bbEvalDel($a, $b, $c, $d=''){
	if($d) $ins = ' <ins>'.$d.'</ins>';
	$ins = str_replace("(", "", $ins);
	$ins = str_replace(")", "", $ins);
	return "<del>$a$b$c</del>".$ins;
}

function tidyHtml($t){
	//Tidy up
	//fixes unclosed tags
	$tidy_config = array(
			'clean' => false,
      'char-encoding' => 'utf8',
      'output-xhtml' => true,
      'numeric-entities' => false,
      'ascii-chars' => false,
      'bare' => true,
      'fix-uri' => true,
      'wrap' => 0,
      'show-body-only' => true,
      'enclose-block-text' => true,
      'fix-bad-comments' => true,
      'fix-backslash' => true,
  );
	$tidy = tidy_parse_string($t, $tidy_config, 'UTF8'); 
	$tidy->cleanRepair();
	return $tidy;
}
?>