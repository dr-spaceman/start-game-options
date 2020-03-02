<?
require_once "class.pglinks.php";
require_once "class.img.php";

$footnotes_grouping_i = 0; //increase for every grouping and assign links accordingly so there's no conflicts

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
	public $headings_offset; //increase heading level
		//for example $headings_offset=2: <h1> => <h3>
	public $openTags = array();
	public $space_x; //max-width in pixels to output videos and images (default = 620)
	
	var $footnote_counter = 1;
	
	public function __construct($t=''){
		$this->space_x = 620;
		if($t) $this->t = $t;
	}
	
	public function __set($name, $value){
		if($name == "text"){
			//Just set a new text block --
			//Reset (some) parameters and variables
			//$this->params=array();
			unset($this->openTags);
			$this->t = $value;
		}
		else $this->{$name} = $value;
	}
	
	function bb2html($text=''){
		
		if($text){
			/*$this->params=array();
			unset($this->headings);*/
			unset($this->openTags);
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
		$this->t = preg_replace_callback(
			'@<code>(.*?)</code>@is',
			function ($matches) {
				return wrapCode($matches[1]);
			},
			$this->t
		);
		
		if($this->params['pages_only']) return $pglinks->parse($this->t);
		
		if($this->params['minimal']){
			
			// minimal replacing -- Markdown & pagelinks
			
			if($this->params['pagelinks'] !== false) $this->t = $pglinks->parse($this->t);
			
			$this->markdown(false);
			
		} else {
			
			$this->markdown();
			
			$tags = array(
				//'@\[b\](.*?)\[/b\]@is', 
				//'@\[i\](.*?)\[/i\]@is', 
				'@\[spoiler\](.*?)\[/spoiler\]@is',
				//'@\[big\](.*?)\[/big\]@is', 
				//'@\[small\](.*?)\[/small\]@is', 
				//'@\[strike\](.*?)\[/strike\]@is',
				//"@\[img\](.*?)\[/img\]@is",
			);
			$tags_r = array(
				//'<b>$1</b>', 
				//'<i>$1</i>', 
				'<span class="spoiler"><del>$1</del></span>', 
				//'<big>$1</big>', 
				//'<small>$1</small>', 
				//'<del>$1</del>',
				//'<img src="$1" alt="my picture"/>',
			);
			$this->t = preg_replace($tags, $tags_r, $this->t);

			$this->t = preg_replace_callback_array(
				[
					//strikethrough
					'@\~([^\s])(.*?)([^\s])\~(\((.*?)\))?@' => function ($match) {
						return bbEvalDel($match[1], $match[2], $match[3], $match[4]);
					},
					'@\[gallery\|?(.*?)\](.*?)\[/gallery\]@is' => function ($match) {
						return embedGallery($match[1], $match[2]);
					},
					'@\{Template:(.*?)\}(?:\s)?@is' => function ($match) {
						return insertTemplate($match[1]);
					}
				]
				, $this->t
			);
			
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
		
		$this->t = stripslashes($this->t);
		
		return trim($this->t);
		
	} // bb2html()
	
	function markdown($premarkdown = true){
		
		if($premarkdown){
			
			//Parse stuff before Markdown
			//Give block elements the markdown attr so their contents get marked-down
			$tags = array(
				'/<blockquote>/',
				'/<aside>/',
			);
			$tags_r = array(
				'<blockquote markdown="1">',
				'<aside markdown="1">',
			);
			$this->t = preg_replace($tags, $tags_r, $this->t);
			
			//Aside
			$this->t = preg_replace_callback('@\[aside\|?(.*?)?\](.*?)\[/aside\]@is', array(&$this, 'evalAside_callback'),$this->t);
			
			//Media tags
			$this->t = preg_replace_callback('@\{(img|image|video|audio|tweet):([^\}]+)\}@i', array(&$this, 'evalMediaTag_callback'),$this->t);
			
			$this->citations(); //Footnotes & Sources
			
		}
		
		require_once "markdown.php";
		if($this->params['markdown'] !== false) $this->t = Markdown($this->t);
		
		return $this->t;
		
	}
	
	function evalAside_callback($matches){
		
		$attr = trim($matches[1]);
		$cont = $matches[2];
		
		if($attr){
			if((int)$attr){
				preg_match("/(\d{1,3})(px|\%)?/i", $attr, $m);
				if(!empty($m)){
					$width = $m[2] ? $m[0] : $m[1] . "px";
					//make the string width_int if the given width is in px or nothing, where px is assumed (ie a width of '50' is assumed to be 50px)
					//if the width is a %, give 0!
					$width_int = $m[2] != "%" ? (int)$m[1] : 0;
					//width cannot exceed 100% or 780px
					if($width_int > 780){ unset($width); unset($width_int); }
					if($m[2] == "%" && (int)$m[1] > 100){ unset($width); unset($width_int); }
				}
			}
			elseif($attr == "short") $class = "short";
			elseif($attr == "long") $class = "long";
		}
		
		// check for media tags inside the aside and give them the aside=true attr
		$cont = preg_replace('@\{([a-z]+):([^\}]+)\}@i', '{\\1:\\2|aside=true}', $cont);
		
		return '<aside'.($width ? ' style="width:'.$width.'"' : '').($class ? ' class="'.$class.'"' : '').' markdown="1">'.$cont.'</aside>';
		
	}
	
	function evalMediaTag_callback($matches){
		#
		# handle matches of media tags, including {img}, {video}, {audio}, {tweet}
		$tag    = $matches[0];
		$type   = $matches[1];
		$params = $this->splitMediaParams($matches[2]);
		$ret    = '';
		
		switch($type){
			case "video":
				$ret = getVideoEmbedCode($params['handle'], $params['width_int']);
				if(!$ret) $ret = '<a href="'.$params['handle'].'" target="_blank" class="arrow-link">My video</a>';
				break;
				
			case "audio":
				//check URL for Sblog post
				$url = '';
				if(ctype_digit($params['handle'])){
					$nid = $params['handle'];
					require_once "class.posts.php";
					try{ $post = new post($nid); }
					catch(Exception $e){ unset($post); }
					if($post && $post->content['audio_file']) $url = 'http://videogam.in'.$post->content['audio_file'];
				} elseif(strstr($params['handle'], ".mp3")){
					$url = "/bin/uploads/audio/".$params['handle'];
					if(!file_exists($_SERVER['DOCUMENT_ROOT'].$url)) unset($url);
				}
				
				if(!$params['width']) $params['width'] = "200px";
				
				if($url){
					$ret = embedAudio($url, $params['width']);
					//old player $ret = '<script type="text/javascript" src="https://media.dreamhost.com/ufo.js"></script><div id="'.$url.'"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.</div><script type="text/javascript">var FO = { movie:"https://media.dreamhost.com/mediaplayer.swf",width:"100",height:"20",majorversion:"7",build:"0",bgcolor:"#FFFFFF",flashvars:"file='.$url.'&showdigits=true&autostart=false" };UFO.create(FO,"'.$url.'");</script>';
				} else $ret = '<a href="'.$url.'" target="_blank" class="arrow-link">My Audio</a>';
				
				break;
			
			case "image":
			case "img":
				$type = "img";
				$img = new img($params['handle']);
				
				$params['link'] = $img->src['url'];
				
				if($params['optimize'] == "false" || $params['optimize'] == "0"){
					$params['size'] = "0";
					$params['width'] = $img->img_width;
				} else {
					//decide the optimal image size to show
					if(!$params['size']){
						//by default, use the optimal size (620px, or less if the image is smaller)
						$params['size'] = "op";
						//if width is specified as px, iterate over the standard sizes to find the best
						if($params['width_int']){
							foreach($GLOBALS['img_normal_sizes_widths'] as $size => $width){
								if($params['width_int'] <= $width){
									$params['size'] = $size;
									break;
								}
							}
						}
					}
					if(!$params['width']){
						if($params['position'] == "center"){
							$params['width'] = "100%";
						}
						elseif($params['size']){
							$imagesize = getimagesize($_SERVER['DOCUMENT_ROOT'].$img->src[$params['size']]);
							$params['width'] = $imagesize[0]."px";
						}
					} else {
						$class_noresize = " noresize";
					}
					//print_r($params);
				}
				
				$alt = $params['caption'] ? $params['caption'] : $img->img_title;
				if(!$alt) $alt = $img->img_name;
				
				$ret = '<img src="'.$img->src[$params['size']].'" alt="%P_CAPT%" border="0"/>';
				
				break;
			
			case "tweet":
				$json = getTweet($params['handle']);
				if($json->html) $ret = $json->html;
				else $ret = '<a href="'.$params['handle'].'">Tweet</a>';
		}
		
		//If there was a failure parsing media above, return the raw tag
		if(!$ret) $ret = $tag;
		
		//Center position by default
		if(!$params['position']) $params['position'] = "center";
		
		//Use <figure> for all positioning except [inline] = <span>
		$tag = $params['position'] != "inline" ? "figure" : "span";
		$tag_caption = $tag == "figure" ? "figcaption" : "span";
		
		if($params['caption']){
			$params['caption'] = stripslashes($params['caption']);
		}
		if($params['caption'] || $alt){
			$p_capt = $params['caption'] ? htmlSC($params['caption']) : $alt;
			$p_capt = str_replace('[', '', $p_capt);
			$p_capt = str_replace(']', '', $p_capt);
			$p_capt = str_replace("\r", "", $p_capt);
			$p_capt = str_replace("\n", " ", $p_capt);
			$p_capt = strip_tags($p_capt);
			if(strlen($p_capt) > 50) $p_capt = substr($p_capt, 0, 49) . "&hellip;";
			$ret = str_replace("%P_CAPT%", $p_capt, $ret);
		}
		
		$capt_class = ($params['width_int'] && $params['width_int'] <= 100 ? "small" : "");
		$a_class = '';
		$a_data = '';
		
		if($type == "img"){
			$a_class = "imgupl";
			$a_data = 'data-imgname="'.$params['handle'].'"';
		}
		
		$class = $type . $class_noresize . ($params['position'] ? " ".$params['position'] : "") . ($params['size'] ? " ".$params['size'] : "");
		
		$ret = 
		'<'.$tag.' title="'.$p_capt.'" class="mediafigure '.$class.'" style="width:'.($params['width'] ? $params['width'] : 'auto').';">'.
				($params['link'] ? '<a href="'.$params['link'].'" title="'.$p_capt.'" class="'.$a_class.'" '.$a_data.'>' : '').
					$ret.
				($params['link'] ? '</a>' : '').
				($type == "img" ? '<a href="'.$img->src['url'].'" class="permalink" title="permanent link for this image"></a>' : '').
				($params['caption'] ? '<'.$tag_caption.' class="caption '.$capt_class.'" markdown="1">'.$params['caption'].'</'.$tag_caption.'>' : '').
		'</'.$tag.'>';
		
		return $ret;
		
	}
	function splitMediaParams($str=''){
		#
		# sort through a |-delinieted set of parameters from a media tag, ie {img:filename.png|center|100px|caption=Fuuuu}
		#
		$p = array();
		$p = explode("|", $str);
		$ret = array();
		$ret['handle'] = $p[0];
		unset($p[0]);
		foreach($p as $opt){
			$opt = trim($opt);
			if(substr($opt, 0, 8)=="caption="){
				$ret['caption'] = substr($opt, 8);
				continue;
			}
			if(substr($opt, 0, 5)=="link="){
				$ret['link'] = substr($opt, 5);
				continue;
			}
			if(substr($opt, 0, 9)=="optimize="){
				$ret['optimize'] = substr($opt, 9);
				continue;
			}
			if(substr($opt, 0, 6)=="aside="){
				$ret['aside'] = substr($opt, 6);
				continue;
			}
			if(in_array($opt, array("left","right","center","inline"))){
				$ret['position'] = $opt;
				continue;
			}
			//Given image size format [thumbnail, screenshot, optimized, original, small, medium, large, etc.]
			//$img_sizes given at class.img.php
			if($GLOBALS['img_sizes'][$opt]){
				$ret['size'] = $GLOBALS['img_sizes'][$opt];
				continue;
			}
			preg_match("/^(\d{1,3})(px|\%)?$/i", $opt, $m);
			if(!empty($m)){
				$ret['width'] = $m[2] ? $m[0] : $m[1] . "px";
				//make the string width_int if the given width is in px or nothing, where px is assumed (ie a width of '50' is assumed to be 50px)
				//if the width is a %, give 0!
				$ret['width_int'] = $m[2] != "%" ? (int)$m[1] : 0;
				//width cannot exceed 100% or 780px
				if($ret['width_int'] > 780){ unset($ret['width']); unset($ret['width_int']); }
				if($m[2] == "%" && (int)$m[1] > 100){ unset($ret['width']); unset($ret['width_int']); }
				continue;
			}
		}
		return $ret;
	}
	
	function citations(){
		$this->t = $this->stripFootnotes($this->t);
		if($this->params['strip_citations']) return;
		$this->t = $this->doFootnotes($this->t);
		$this->t = $this->appendFootnotes($this->t);
	}
	function stripFootnotes($text) {
	#
	# Strips link definitions from text, stores the URLs and titles in
	# hash references.
	#

		# Link defs are in the form: [^id]: url "optional title"
		$regx = '{
			^[ ]{0,3}\[\^(.+?)?\][ ]?:	# note_id = $1
			  [ ]*
			  \n?					# maybe *one* newline
			(						# text = $2 (no blank lines allowed)
				(?:					
					.+				# actual text
				|
					\n				# newlines but 
					(?![ ]{0,3}\[\^?(.+?)?\]:\s)# negative lookahead for footnote marker.
					(?!\n+[ ]{0,3}\S)# ensure line is not blank and followed 
									# by non-indented content
				)*
			)		
			}xm';
		$text = preg_replace_callback($regx, array(&$this, '_stripFootnotes_callback'),$text);
		return $text;
	}
	function _stripFootnotes_callback($matches) {
		$refid = trim($matches[1]);
		$ref   = $this->outdent($matches[2]);
		
		//Check if reference is in standard format (URL REFERENCENAME)
		preg_match("/^(https?:\/\/)([^ ]+)[ ]+(.*?)$/i", trim($ref), $m);
		if(!empty($m)) $ref = '<a href="'.$m[1].$m[2].'" target="_blank">'.$m[3].'</a>';
		
		$this->footnotes[] = array("refid" => $refid, "ref" => $ref);
		if($refid) $this->footnotes_referenced[$refid] = $ref;
		return ''; # String that will replace the block
	}
	function doFootnotes($text) {
	#
	# Replace footnote references in $text [^id] with a special text-token 
	# which will be replaced by the actual footnote marker in appendFootnotes.
	#
	
	if($this->params['strip_citations']) return preg_replace('{\[\^(.+?)\]}', "", $text);
	
	$text = preg_replace('{\[\^(.+?)\]}', "F\x1Afn:\\1\x1A:", $text);
	return $text;
	}
	function appendFootnotes($text) {
	#
	# Append footnote list to text.
	#
		
		if (!empty($this->footnotes)) {
			//print_r($this->footnotes);
			
			if(++$GLOBALS['footnotes_grouping_i'] > 1){
				$i = 1;
				foreach (range('a', 'z') as $letter) {
					if(++$i == $GLOBALS['footnotes_grouping_i']){
						$this->footnotes_prefix = "-".$letter;
						break;
					}
				}
			}
			
			$text = preg_replace_callback('{F\x1Afn:(.*?)\x1A:}', array(&$this, '_appendFootnotes_callback'), $text);
			
			$num = 0;
			$notes_o = '';
			$sources_o = '';
			
			foreach($this->footnotes as $f){
				$ref = trim($f['ref']);
				$refid = $f['refid'];
				
				if($refid == ""){
					//Reference -- intentionally uncited ie [^]:
					$this->fn_references[] = "<li class=\"footnote_source\"><span markdown=\"1\" class=\"footnote-ref\">".
					                         trim($ref).
					                         "</span></li>\n";
				} else {
					//Footnote -- cited above ie [^1]:
					//check that it was cited above ie [^1]
					//if not ignore it
					if(!count($this->footnotes_referenced_appended) || !in_array($refid, $this->footnotes_referenced_appended)){
						continue;
					} else {
						
						$refid = ($refid);
						
						# Add backlink to last paragraph; create new paragraph if needed.
						$backlink = "<a href=\"#fnref:".$refid.$this->footnotes_prefix."\" class=\"backlink\">&#8617;</a>";
						$ref .= " $backlink";
						
						$this->fn_notes[] = "<li id=\"fn:".$refid.$this->footnotes_prefix."\" class=\"footnote-ref\"><span markdown=\"1\">".
						                    trim($ref).
						                    "</span></li>\n";
					}
				}
			}
			
			$this->footnotes = array();
			
			if(!$this->params['footnotes_noappend']){
				$text.= $this->outputFootnotes();
			}
			
			return $text;
		}
		
		return $text;
	}
	function _appendFootnotes_callback($matches) {
		
		$refid = $matches[1];
		
		if (isset($this->footnotes_referenced[$refid])) {
			
			$num = $this->footnote_counter++;
			$refid = ($refid);
			$this->footnotes_referenced_appended[] = $refid;
			$ref = "<a href=\"#fn:".$refid.$this->footnotes_prefix."\" rel=\"footnote\">$num</a>";
			
			return "<sup id=\"fnref:".$refid.$this->footnotes_prefix."\" class=\"citation".($this->params['inline_citations'] ? " inline_citations" : "")."\">".$ref."</sup>";
		}
		
		return "[^".$refid."]";
	}
	function outputFootnotes($reparse=false){
		
		$text = "";//echo "Ofn:";print_r($this);//(empty($this->fn_notes) ? "fn_notes empty" : print_r($this->fn_notes));print_r($this->fn_references);
		
		if($this->fn_notes) $text.= "\n\n".
			                   "<div class=\"footnotes notes".($this->params['inline_citations'] ? " inline_citations" : "")."\"></div>\n".
			                   "<h1>Notes</h1>\n".
			                   "<ol>".implode("", $this->fn_notes)."</ol>\n";
		
		if($this->fn_references) $text.= "\n\n".
			                     "<div class=\"footnotes references".($this->params['inline_citations'] ? " inline_citations" : "")."\"></div>\n".
			                     "<h1>Sources</h1>\n".
			                     "<ul>".implode("", $this->fn_references)."</ul>\n";
		
		if($reparse === true && $text != "") $text = $this->bb2html($text);
		
		return $text;
	}

	function outdent($text) {
	#
	# Remove one level of line-leading tabs or spaces
	#
		return preg_replace('/^(\t|[ ]{1,4})/m', '', $text);
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
				
				preg_match('@<h([1-6])([^\>])?>(.*?)</h([1-6])>@is', $line, $m);
				if(!empty($m)){
					list($h_string, $h_level, $h_attr, $h_tag, ) = $m;
					
					$h_level_o = $h_level + ($this->headings_offset ? $this->headings_offset : 0);
					$h_tag = trim($h_tag);
					
					$h_tag_formatted = strip_tags($h_tag);
					$h_tag_formatted = formatNameURL($h_tag_formatted, 1);
					
					$line = str_replace($h_string, '<h'.$h_level_o.$h_attr.'>'.$h_tag.'<a name="'.$h_tag_formatted.'"></a></h'.$h_level_o.'>', $line);;
					
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
	$t = strip_tags_attributes($t, "<a><abbr><acronym><aside><b><big><blockquote><br><cite><code><del><dl><dt><dd><em><fieldset><i><ins><legend><li><ol><q><s><small><strike><sub><sup><s><strong><table><tbody><thead><tfoot><tr><td><th><ul>", "href,title,rel,src,start,alt,width,height");
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

function embedGallery($str='', $conts=''){
	
	require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/class.img.php");
	$_gallery = new gallery();
	$_gallery->files = $conts;
	$_gallery->opt_str = $str;
	$_gallery->parse();
	return $_gallery->HTMLencode();
	
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
        $string = preg_replace_callback(
        	"/<[^>]*>/i", 
        	function($matches) {
        		return preg_replace("/ [^ =]*'.$allowattributes.'=(\"[^\"]*\"|\'[^\']*\')/i", "", $matches[0]);
        	},
        	$string
       	); 
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

function getVideo($url, $maxwidth='', $append_wmode=true){
	if(!$maxwidth) $maxwidth = 620;
	// @var $append_wmode add wmode=transparent to the video iframe url so it doesn't conflict with positioned CSS layers (we'll only do this with Youtube and Vimeo since it might break others)
	if(preg_match('/(youtube\.com|youtu\.be)/i', $url)){
		//Youtube
		$oembed_url = 'http://www.youtube.com/oembed?url='.rawurlencode($url).'&format=xml&maxwidth='.$maxwidth;
		$oembed = @simplexml_load_string(curl_get($oembed_url));
	} elseif(strstr($url, "vimeo.com")) {
		//Vimeo
		$oembed_url = 'http://vimeo.com/api/oembed.json?url='.rawurlencode($url).'&maxwidth='.$maxwidth;
		$oembed = json_decode(curl_get($oembed_url));
	}
	if(!$oembed) return false;
	if($append_wmode !== false) $oembed->html = append_wmode($oembed->html);
	return $oembed;
}

function getVideoEmbedCode($url, $maxwidth=''){
	$video = getVideo($url, $maxwidth);
	if(!$video->html) return false;
	return html_entity_decode($video->html);
}

function getTweet($url){
	//@return array JSON array of data from a tweet
	//$json['html'] is the tweet to embed
	$oembed_url = 'https://api.twitter.com/1/statuses/oembed.json?url='.rawurlencode($url);
	$oembed = json_decode(curl_get($oembed_url));
	return $oembed;
}

function embedAudio($file, $width=200){
	return '<object type="application/x-shockwave-flash" data="http://videogam.in/bin/player/template_mini/player_mp3_mini.swf" width="'.str_replace("px", "", $width).'" height="20"><param name="movie" value="http://videogam.in/bin/player/template_mini/player_mp3_mini.swf" /><param name="bgcolor" value="000000" /><param name="FlashVars" value="mp3='.$file.'" /></object>';
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