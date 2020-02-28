<?

function bb2html($text, $vars='') {
	
	$text = stripslashes($text);
	
	if(strstr($vars, "prepend_domain")) $ppd = 1;
	
	if(strstr($vars, "pages_only")) {
		$text = preg_replace('@\[\[(.*?)\]\]@ise', "evaluatePageLink('\\1', '$ppd')", $text);
		return $text;
	}
	
	if(strstr($vars, "minimal")) {
		
		$tags = array(
			'@\[b\](.*?)\[/b\]@is', 
			'@\[i\](.*?)\[/i\]@is', 
			'@\[big\](.*?)\[/big\]@is', 
			'@\[small\](.*?)\[/small\]@is', 
			'@\[strike\](.*?)\[/strike\]@is',
			'@\[url\](.*?)\[/url\]@ise', 
			'@\[url=(.*?)\](.*?)\[/url\]@ise'
		);
		$tags_r = array(
			'<b>$1</b>', 
			'<i>$1</i>', 
			'<big>$1</big>', 
			'<small>$1</small>', 
			'<del>$1</del>',
			"evaluateLink('\\1', '\\1', '$ppd')",
			"evaluateLink('\\1', '\\2', '$ppd')"
		);
		$text = preg_replace($tags, $tags_r, $text);
	
	} else {
		
		if(strstr($text, "[")) {
		
			$tags = array(
				'@\[b\](.*?)\[/b\]@is', 
				'@\[i\](.*?)\[/i\]@is', 
				'@\[spoiler\](.*?)\[/spoiler\]@is', 
				'@\[big\](.*?)\[/big\]@is', 
				'@\[small\](.*?)\[/small\]@is', 
				'@\[strike\](.*?)\[/strike\]@is',
				'@\[url\](.*?)\[/url\]@ise', 
				'@\[url=(.*?)\](.*?)\[/url\]@ise', 
				"@\[img\](.*?)\[/img\]@is",
				"@\[img\|(.*?)\](.*?)\[/img\]@ise",
				'@\[h([3-6])\](.*?)\[/h[3-6]\]\s*@is',
				"@\[video\|?(.*?)\](.*?)\[/video\]@ise",
				'@\[game=?(.*?)\](.*?)\[/game\]@ise',
				'@\[gid=([0-9]*)/\]@ise',
				'@\[person=?(.*?)\](.*?)\[/person\]@ise',
				'@\[pid=([0-9]*)/\]@ise',
				'@\[\[(.*?)\]\]@ise'
			);
			$tags_r = array(
				'<b>$1</b>', 
				'<i>$1</i>', 
				'<span class="spoiler"><strong>SPOILER WARNING!</strong> Highlight to read: <del>$1</del></span>', 
				'<big>$1</big>', 
				'<small>$1</small>', 
				'<del>$1</del>',
				"evaluateLink('\\1', '\\1', '$ppd')",
				"evaluateLink('\\1', '\\2', '$ppd')",
				'<img src="$1" alt="my picture"/>',
				"outputThumbnail('','','\\2','','\\1')",
				"<h$1>$2</h$1>",
				"embedVideo('\\2', '\\1')",
				"evaluateTag('game', '\\1', '\\2', '$ppd')",
				"evaluateTag('game-id', '\\1', '', '$ppd')",
				"evaluateTag('person', '\\1', '\\2', '$ppd')",
				"evaluateTag('person-id', '\\1', '', '$ppd')",
				"evaluatePageLink('\\1', '$ppd')"
			);
			$text = preg_replace($tags, $tags_r, $text);
			
			if(strstr($text, "[quote]")) {
				$open = '<blockquote>';
				$close = '</blockquote>';
				preg_match_all ('@\[quote\]@i', $text, $matches);
				$opentags = count($matches['0']);
				preg_match_all ('@\[/quote\]@i', $text, $matches);
				$closetags = count($matches['0']);
				$unclosed = $opentags - $closetags;
				for ($i = 0; $i < $unclosed; $i++) {
					$text .= '</blockquote>';
				}
				$text = str_replace ('[quote]', $open, $text);
				$text = str_replace ('[/quote]', $close, $text);
			}
			
			if(strstr($text, "[cite")) {
				preg_match_all("@\[cite=?(.*?)\[/cite\]@is", $text, $matches);
				for($i = 0; $i < count($matches[0]); $i++) {
					$url = "";
					$name ="";
					list($url, $name) = explode("]", $matches[1][$i]);
					if(strstr($vars, "inline_citations")) {
						$text = str_replace($matches[0][$i], ' <span class="inline-cite">['.($url ? '<a href="'.$url.'" target="_blank">'.$name.'</a>' : $name).']</span>', $text);
					} else {
						$n = $i + 1;
						$text = str_replace($matches[0][$i], '<span class="cite" id="citeback-'.$n.'">[<a href="#cite-'.$n.'" onclick="document.getElementById(\'cite-'.$n.'\').className=\'on\';">'.$n.'</a>]</span>', $text);
						$append .= '<li id="cite-'.$n.'"><span class="citeback"><a href="#citeback-'.$n.'" onclick="document.getElementById(\'cite-'.$n.'\').className=\'\';" title="jump back to the text">&uArr;</a> </span>'.($url ? '<a href="'.$url.'" target="_blank">'.$name.'</a>' : $name).'</li>';
					}
				}
				if($append) {
					$text.= '<div class="sources"><h5>Sources:</h5><ol>'.$append.'</ol><br style="clear:both"/></div>';
				}
			}
		
			//lists
			$matches = "";
			preg_match_all("@\[(o)?list\](.*?)\[/(o)?list\]@is", $text, $matches);
			if($matches) {
				for($i = 0; $i < count($matches[0]); $i++) {
					if($matches[1][$i] == "o") {
						$open = "<ol>";
						$close = "</ol>";
					} else {
						$open = "<ul>";
						$close = "</ul>";
					}
					$listcont = $open."<li>".preg_replace("/^\*/m", "</li><li>", $matches[2][$i])."</li>".$close;
					$listcont = str_replace("\r\n</li>", "</li>", $listcont);
					$listcont = str_replace("\n</li>", "</li>", $listcont);
					$listcont = str_replace("<li></li>", "", $listcont);
					$text = str_replace($matches[0][$i], $listcont, $text);
				}
			}
			
			//news item
			if(strstr($text, "[newsitem")) {
				require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/class.posts.php");
				$news = new posts;
				preg_match_all("@\[newsitem=([a-z]+)\](.*?)\[/newsitem\]@is", $text, $matches);
				$n['type'] = $matches[1][0];
				$n['content'] = $matches[2][0];
				$message = $news->item($n, "article");
				$message = str_replace("\r\n", "", $message);
				$text = str_replace($matches[0][0], $message, $text);
			}
			
		}
		
		//list without a tag
		//$text.= "\n";
		/*$lines = explode("\n", $text);
		if(count($lines) > 1) {
			$text = "";
			$j = 0;
			for($i = 0; $i < count($lines); $i++) {
				$chr = substr($lines[$i], 0, 1);
				if($chr == "#" || $chr == "*") {
					$j++;
					if($j == 1) {
						if($chr == "#") $x = '<ol><li>'.substr($lines[$i], 1).'</li>';
						if($chr == "*") $x = '<ul><li>'.substr($lines[$i], 1).'</li>';
					} else {
						$x = '<li>'.substr($lines[$i], 1).'</li>';
					}
					if(substr($lines[$i + 1], 0, 1) != $chr) {
						$j = 0;
						if($chr == "#") $x.= '</ol>';
						if($chr == "*") $x.= '</ul>';
					}
				} else $x = $lines[$i]."\n";
				$text.= $x;
			}
		}*/
	
	}
	
	return $text;
	
}

function html2bb($text) {
	
	if(!strstr($text, "<")) return $text;
	
	$tags = array(
		'@<b>(.*?)</b>@is', 
		'@<strong>(.*?)</strong>@is', 
		'@<i>(.*?)</i>@is', 
		'@<del>(.*?)</del>@is', 
		'@<big>(.*?)</big>@is', 
		'@<small>(.*?)</small>@is', 
		'@<strikethrough>(.*?)</strikethrough>@is',
		'@<a (.*?)>(.*?)</a>@ise', 
		'@<img (.*?)>@ise',
		'@<h3>(.*?)</h3>@is',
		'@<h4>(.*?)</h4>@is'
	);
	$tags_r = array(
		'[b]$1[/b]', 
		'[b]$1[/b]', 
		'[i]$1[/i]', 
		'[spoiler]$1[/spoiler]', 
		'[big]$1[/big]', 
		'[small]$1[/small]', 
		'[strike]$1[/strike]',
		"bbFilterLink('\\1','\\2')",
		"bbFilterImg('\\1')",
		'[h3]$1[/h3]',
		'[h4]$1[/h4]'
	);
	$text = preg_replace($tags, $tags_r, $text);
	
	if(strstr($text, "<blockquote>")) {
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
	}
	
	return $text;
	
}

function bbFilterLink($inp, $words) {
	$inp = str_replace("'", "", $inp);
	$inp = str_replace('"', '', $inp);
	$inp = str_replace('\\', '', $inp);
	$attrs = array();
	$attrs = explode(" ", $inp);
	foreach($attrs as $attr) {
		if(substr($attr, 0, 5) == "href=") $ret = substr($attr, 5);
	}
	return '[url='.$ret.']'.$words.'[/url]';
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

function sh2vi($x) {
	$x = preg_replace('@(http://)?(www\.)?(\square-?haven\.com)?/games/([a-z0-9-]+)/([a-z0-9-]+)/?([a-z0-9-_]+)?@ise', "sh2viLink('\\4','\\5','\\6')", $x);
	$x = str_replace("/gallery.cgi?dir=", "/media.php?dir=", $x);
	$x = preg_replace('@http://(www\.)?square-?haven\.com@ise', "", $x);
	$x = str_Replace("/games/series.php?series=", "/games/series/", $x);
	$x = str_Replace("/features/albums", "/music", $x);
	$x = preg_replace('@\[\[(G|P)\|\|(.*?)\]\]@ise', "sh2viCont('\\1', '\\2')", $x);
	return $x;
}
function sh2viLink($pf, $sh_gid, $extra) {
	$q = "SELECT title_url, gid FROM games WHERE sh_id = '$sh_gid' LIMIT 1";
	$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
	if(!$dat || $extra) return "/games/sh/$pf/$sh_gid/$extra";
	else return "/games/".$dat->gid."/".$dat->title_url;
}
function sh2viCont($what, $cont) {
	if($what == "G"){
		$q = "SELECT gid FROM games WHERE title = '".mysqli_real_escape_string($GLOBALS['db']['link'], $cont)."' LIMIT 1";
		$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
		if($dat) $ret = "[gid=$dat->gid/]";
		else $ret = "[game]".$cont."[/game]";
	} elseif($what == "P"){
		$q = "SELECT pid FROM people WHERE name = '$cont' LIMIT 1";
		if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) $ret = "[pid=$dat->pid/]";
		else $ret = "[person]".$cont."[/person]";
	}
	return $ret;
}

function readableBB($text) {
	//convert coded IDs so they're readableBB
	global $ppd;
	$tags = array(
		'@\[gid=([0-9]*)/\]@ise',
		'@\[pid=([0-9]*)/\]@ise'
	);
	$tags_r = array(
		"evaluateTag('game-id', '\\1', '', '$ppd', true)",
		"evaluateTag('person-id', '\\1', '', '$ppd', true)"
	);
	$text = preg_replace($tags, $tags_r, $text);
	return $text;
}

function codedBB($text) {
	//encode certain bb tags like [game] and [person] to their db ids
	$tags = array(
		'@\[game\](.*?)\[/game\]@ise',
		'@\[person\](.*?)\[/person\]@ise'
	);
	$tags_r = array(
		"BBencode('game', '\\1')",
		"BBencode('person', '\\1')"
	);
	$text = preg_replace($tags, $tags_r, $text);
	return $text;
}

function BBencode($what, $desc) {
	$ret = "[".$what."]".$desc."[/".$what."]";
	if($what == "game") {
		$q = "SELECT gid FROM games WHERE title='".mysqli_real_escape_string($GLOBALS['db']['link'], $desc)."' LIMIT 1";
		if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) $ret = "[gid=".$dat->gid."/]";
	} elseif($what == "person") {
		$q = "SELECT pid FROM people WHERE name='".mysqli_real_escape_string($GLOBALS['db']['link'], $desc)."' LIMIT 1";
		if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) $ret = "[pid=".$dat->pid."/]";
	}
	return $ret;
}

function evaluateLink($a, $b, $ppd=false) {
	return '<a href="'.($ppd ? 'http://videogam.in' : '').$a.'">'.stripslashes($b).'</a>';
}

function evaluateTag($type, $id='', $link_text='', $prepend_domain=false, $bbcode=false) {
	
	if(!$id) $id = $link_text;
	$id = mysqli_real_escape_string($GLOBALS['db']['link'], $id);
	
	if($type == "game-id") {
		$res = mysqli_query($GLOBALS['db']['link'], "SELECT title_url, title FROM games WHERE `gid` = '$id' LIMIT 1");
		if($row = @mysqli_fetch_object($res)) {
			if($bbcode) return '[game]'.$row->title.'[/game]';
			else return '<a href="'.($prepend_domain ? "http://videogam.in" : "") . '/games/'.$id.'/'.$row->title_url.'" title="'.htmlSC($row->title).' overview" class="game-link">'.$row->title.'</a>';
		}
	} elseif($type == "game") {
		$query = "SELECT gid, title, title_url FROM games WHERE title='".htmlSC($id)."' LIMIT 1";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		if($row = @mysqli_fetch_object($res)) {
			$ret = '<a href="' . ($prepend_domain ? "http://videogam.in" : "") . '/games/'.$row->gid.'/'.$row->title_url.'" title="'.htmlSC($row->title).' overview" class="game-link">'.($link_text ? $link_text : $id).'</a>';
		} else {
			$ret = '<a href="/games/add.php?title='.$id.'" title="Add this game to the database" rel="nofollow" class="nocoverage">'.($link_text ? $link_text : $id).'</a>';
		}
		return stripslashes($ret);
	} elseif($type == "person") {
		$q = "SELECT pid, name_url FROM people WHERE name='$id' LIMIT 1";
		if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
			return '<a href="' . ($prepend_domain ? "http://videogam.in" : "") . '/people/'.$dat->pid.'/'.$dat->name_url.'" title="'.$id.' profile, biography, credits" class="person-link">'.($link_text ? $link_text : $id).'</a>';
		} else return $id;
	} elseif($type == "person-id") {
		$q = "SELECT pid, name, name_url FROM people WHERE pid='$id' LIMIT 1";
		if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
			if($bbcode) return '[person]'.$dat->name.'[/person]';
			else return '<a href="' . ($prepend_domain ? "http://videogam.in" : "") . '/people/'.$dat->pid.'/'.$dat->name_url.'" title="'.htmlSC($dat->name).' biography, credits, news" class="person-link">'.$dat->name.'</a>';
		} else return $id;
	}
	
}

function evaluatePageLink($pg, $ppd=false) {
	
	global $pgtypes;
	
	if($ppd) $ppd = "http://videogam.in";
	
	$namespaces = array("/^Category:/", "/^Tag:/");
	$pg = preg_replace($namespaces, "", $pg);
	
	if(strstr($pg, "|")) {
		list($pg, $link_text) = explode("|", $pg);
		$link_text = trim($link_text);
	}
	list($pg, $err) = formatName($pg);
	
	$q = "SELECT * FROM pages WHERE `title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $pg)."' LIMIT 1";
	if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
		
		if(!$link_text) $link_text = $dat->title;
		
		//found pg
		
		if($dat->type == "game") $title = $dat->title." game overview";
		elseif($dat->type == "person") $title = $dat->title." (game developer) profile, biography, credits";
		else $title = $dat->title;
		
		if($dat->redirect_to) {
			//redirected pg
			$q = "SELECT * FROM pages WHERE `title`='".mysqli_real_escape_string($GLOBALS['db']['link'], $dat->redirect_to)."' LIMIT 1";
			if(!$dat2 = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
				return '<a href="'.$ppd.'/pages/handle.php?title='.formatNameURL($dat->title).'" style="border-bottom:1px dotted #CA3535;" class="tooltip" title="This page is assigned to redirect, but the redirect info can\'t be found.">'.$link_text.'<sup>&dagger;</sup></a>';
			} //else $dat = $dat2;
		}
		
		return '<a href="'.$ppd.'/pages/handle.php?index='.$pgtypes[$dat->type].'&title='.formatNameURL($dat->title).'" title="'.htmlSC($title).'">'.$link_text.'</a>';
		
	} else {
		//not yet in db
		return '<a href="'.$ppd.'/pages/handle.php?title='.formatNameURL($pg).'" class="nocoverage" rel="nofollow" title="No coverage yet">'.($link_text ? $link_text : $pg).'</a>';
	}
}

function emote($t) {
	$f = array(
		'/(^| )(:|=|;){1}([\|\)\(PD0oO]{1})/me',
		'@\<!--emoticon:([a-z0-9-_\.]+)-->@ise'
	);
	$r = array(
		"emoteImg('\\1', '\\2\\3')",
		"emoteImg('', '', '\\1')"
	);
	$t = preg_replace($f, $r, $t);
	$t = str_replace("<3", '<img src="/bin/img/icons/emoticons/_heart.png" alt="<3" width="16" height="16" class="emoticon"/>', $t);
	/*$t = str_replace(":)", '<img src="/bin/img/icons/emoticons/smile.png" alt=":)" width="16" height="16" class="emoticon"/>', $t);
	$t = str_replace("=)", '<img src="/bin/img/icons/emoticons/smile.png" alt=":)" width="16" height="16" class="emoticon"/>', $t);
	$t = str_replace(":P", '<img src="/bin/img/icons/emoticons/tongue.png" alt=":P" width="16" height="16" class="emoticon"/>', $t);
	$t = str_replace("=P", '<img src="/bin/img/icons/emoticons/tongue.png" alt=":P" width="16" height="16" class="emoticon"/>', $t);
	$t = str_replace(";)", '<img src="/bin/img/icons/emoticons/wink.png" alt=";)" width="16" height="16" class="emoticon"/>', $t);
	$t = str_replace(":o", '<img src="/bin/img/icons/emoticons/wow.png" alt=":o" width="16" height="16" class="emoticon"/>', $t);
	$t = str_replace(":O", '<img src="/bin/img/icons/emoticons/omfg.png" alt=":O" width="16" height="16" class="emoticon"/>', $t);
	$t = str_replace(":0", '<img src="/bin/img/icons/emoticons/omfg.png" alt=":O" width="16" height="16" class="emoticon"/>', $t);*/
	return $t;
}

function emoteImg($prep, $em='', $emfile='') {
	if($em) {
		$arr = array(
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
		return $prep.($arr[$em] ? '<img src="/bin/img/icons/emoticons/'.$arr[$em].'.png" alt="'.$em.'" width="16" height="16" class="emoticon"/>' : $em);
	} elseif($emfile) {
		return '<img src="/bin/img/icons/emoticons/'.$emfile.'" alt="'.$emfile.'" width="16" height="16" class="emoticon"/>';
	}
}

function embedVideo($url, $varstr="") {
	
	$url = trim($url);
	if($varstr) {
		$vars = explode("|", $varstr);
		$align = $vars[0];
		$caption = $vars[1];
		if($align != "left" && $align != "right") {
			if(!$caption) $caption = $align;
			unset($align);
		}
	}
	
	if(preg_match('#http://(www\.)?youtube\.com/watch\?([a-z])=([^&]+)(.*?)#', $url, $matches)) {
		//Youtube
		if($matches[2] && $matches[3]) {
			return '<dl class="thumbnail tn-'.$align.'"><dt><object><param name="movie" value="http://www.youtube.com/'.$matches[2].'/'.$matches[3].'?fs=1&hd=1"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.youtube.com/'.$matches[2].'/'.$matches[3].'?fs=1&hd=1" type="application/x-shockwave-flash" allowfullscreen="true"></embed></object></dt>'.($caption ? "<dd>$caption</dd>" : "").'</dl>';
		}
	} elseif(strstr($url, "viddler.com")) {
		
		//Viddler
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$html = curl_exec($ch);
		
		preg_match('@href="(http://www.viddler.com/player/(.*?)/)"@', $html, $viddler);
		if($vidd_url = $viddler[1]) {
			$m = get_meta_tags($url);
			return '<dl class="thumbnail tn-'.$align.'><dt><object width="'.$m['video_width'].'" height="'.$m['video_height'].'"><param name="movie" value="'.$vidd_url.'" /><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="true" /><param name="flashvars" value="fake=1"/><embed src="'.$vidd_url.'" width="'.$m['video_width'].'" height="'.$m['video_height'].'" type="application/x-shockwave-flash" allowScriptAccess="always" allowFullScreen="true" flashvars="fake=1" name="viddler" ></embed></object></dt>'.($caption ? "<dd>$caption</dd>" : "").'</dl>';
		}

	}

	return '<a href="'.$url.'" target="_blank" class="arrow-link">My video</a>';
	
}

function outputThumbnail($position='', $img='', $thumb='', $capt='', $blob='') {
	
	if($blob) list($position, $img, $capt) = explode("|", $blob);
	
	if($position != "left" && $position != "right") $position = "left";
	$capt = stripslashes($capt);
	if(strstr($capt, "||")) list($size, $capt) = explode("||", $capt);
	if(!$size) {
		$imgsize = @getimagesize($_SERVER['DOCUMENT_ROOT'].$thumb);
		$size = $imgsize[0];
	}
	$size = $size + 2;
	$ret = '<dl class="thumbnail tn-'.$position.'"><dt>';
	if($img) $ret.= '<a href="'.$img.'" title="'.$capt.'" class="thickbox">';
	$ret.= '<img src="'.$thumb.'" alt="'.($capt ? $capt : "thumbnail").'"/>';
	if($img) $ret.= '</a>';
	$ret.= '</dt>';
	if($capt) $ret.= '<dd style="width:'.$size.'px">'.$capt.'</dd>';
	$ret.= '</dl>';
	return $ret;
}

?>