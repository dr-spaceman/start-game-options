<?
function bbcode2markdown($t, $br=false){
	
	if(substr($t, 0, 9) == "#REDIRECT") return $t;
	
	$t = str_replace("\r\n", "\n", $t);
	
	$t = preg_replace("@\[\/?o?list\]\n?@is", "\n", $t); //[olist] and [list] tags depreciated
	
	$tags = array(
				'@\[big\](.*?)\[/big\]@is', 
				'@\[small\](.*?)\[/small\]@is', 
				'@\[strike\](.*?)\[/strike\]@is',
				'@\<strike\>(.*?)\</strike\>@is',
				"@\[img\](.*?)\[/img\]@is",
				'@\[img\|(.*?)\](.*?)\[/img\](?:\s)?@ise',
				'@\[url\](.*?)\[/url\]@is', 
				'@\[url=(.*?)\](.*?)\[/url\]@is', 
				'@\[quote\]@is',
				'@\[/quote\]@is',
				'@<blockquote>@is',
				'@</blockquote>@is',
				'@\<h(5|6)\>(.*?)\</h(5|6)\>@is',
				'@\<br ?/?\>@is',
				'@\[video(.*?)?\](.*?)\[/video\]@ise', 
				'@\[audio(.*?)?\](.*?)\[/audio\]@ise', 
			);
			$tags_r = array(
				'<big>$1</big>', 
				'<small>$1</small>', 
				'<del>$1</del>',
				'<del>$1</del>',
				'![]($1)'."\n\n",
				"evalImgTag_('\\2', '\\1')",
				'<$1>',
				'[$2]($1)',
				"<blockquote>",
				"</blockquote>",
				"\n\n<blockquote>",
				"</blockquote>\n\n",
				'[h$1]$2[/h$1]',
				"\n",
				"evalVideoAudioTag_('video', '\\2', '\\1')",
				"evalVideoAudioTag_('audio', '\\2', '\\1')",
			);
	$t = preg_replace($tags, $tags_r, $t);
	
	$blockquote_open = 0;
	$blockquote_close = 0;
	$lines = explode("\n", $t);
	$num_lines = count($lines);
	$i_source = 0;
	$sources_added = 0;
	for($n = 0; $n < $num_lines; $n++){
		$line = $lines[$n];
		$s1 = substr($line, 0, 1);
		$s2 = substr($line, 0, 2);
		$s3 = substr($line, 0, 3);
		
		$op = ($blockquote_open - $blockquote_close);
		for($i=0; $i<$op; $i++) $line = "> ".$line;
		
		if(strstr($line, '<blockquote>')){
			$blockquote_open++;
			$line=str_replace("<blockquote>", "> ", $line);
		}
		//for($i=0; $i<($blockquote_open - $blockquote_close); $i++) $line = "> ".$line;
		
		if(strstr($line, '</blockquote>')){
			$blockquote_close++;
			$line=str_replace("</blockquote>", "", $line);
		}
		
		preg_match_all("@\[(cite|source)=?([^\]]+)?\](.*?)\[/(cite|source)\]@is", $line, $matches, PREG_PATTERN_ORDER);
		//echo '<pre>'; print_r($matches);echo '</pre>';
		for($i = 0; $i < count($matches[0]); $i++){
					$type = $matches[1][$i];
					$url = $matches[2][$i];
					$name = $matches[3][$i];
					if(substr($name, 0, 5) == "(http") continue; //its a link with the name "source" [source](http://wikipesdsda.com/sdsd)
					if($type == "cite") $line = str_replace($matches[0][$i], '[^'.(++$i_source).']', $line);
					else $line = str_replace($matches[0][$i], '', $line);
					$t .= ($sources_added==0 ? "\n" : "")."\n[^".($type=="cite" ? $i_source : "")."]: ".
					($url ? "$url $name" : $name);
					$sources_added++;
		}
		
		//RMOVING ALL TABS:
		$t=str_replace($lines[$n], str_replace("\t", "", $line), $t);
		
		//ol ul
		if($s1 == "*" || $s1 == "#"){
			$m = '';
			preg_match("/^[\*\#]+/A", $line, $m);
			$tag = $m[0];
			if($tag){
				$l = substr($line, strlen($tag));
				$l = trim($l);
				if($tag == '*') $r = "* $l";
				elseif($tag=="**") $r="    * $l";
				elseif($tag=="***") $r="        * $l";
				elseif($tag=='#') $r = "1. $l";
				elseif($tag=="##") $r="    1. $l";
				elseif($tag=="###") $r="        1. $l";
				else $r="";
				if(!$openlist) $r = "\n".$r;
				if($r) $t = str_replace($line, $r, $t);
			}
			$openlist = true;
			continue;
		} else {
			$openlist = false;
		}
		
		//h6
		if($s3 == "===" || $s3 == "[h6"){
			$l = str_replace("===", "", $line);
			$l = str_replace("[h6]", "", $l);
			$l = str_replace("[/h6]", "", $l);
			$l = trim($l);
			$l.= "\n" . sprintf("%'-".strlen($l)."s", '-');
			$t = str_replace($line, $l, $t);
			$s2 = ''; $s3 = '';
		}
		
		//h5
		elseif($s2 == "==" || $s3 == "[h5"){
			$l = str_replace("==", "", $line);
			$l = str_replace("[h5]", "", $l);
			$l = str_replace("[/h5]", "", $l);
			$l = trim($l);
			$l.= "\n" . sprintf("%'=".strlen($l)."s", '=');
			$t = str_replace($line, $l, $t);
			$s2 = ''; $s3 = '';
		}
		
		if($br && $lines[($n + 1)]){
			$nextline = trim($lines[($n + 1)]);
			if(strlen($nextline) && trim($line)) $t = str_replace($line, $line."  ", $t);//echo '[R:'.$line.']';
		}
		
	}
	
	$tags = array(
				'@\[b\](.*?)\[/b\]@is', 
				'@\[i\](.*?)\[/i\]@is', 
				'@\<b\>(.*?)\</b\>@is',
				'@\<i\>(.*?)\</i\>@is',
				'@\<strong\>(.*?)\</strong\>@is',
				'@\<cite\>(.*?)\</cite\>@is',
			);
			$tags_r = array(
				'**$1**', 
				'*$1*', 
				'**$1**', 
				'*$1*', 
				'**$1**', 
				'*$1*', 
			);
			$t = preg_replace($tags, $tags_r, $t);
	
	$t = trim($t);
	
	$t = preg_replace("/\n{2,}/", "\n\n", $t);
	
	$t = preg_replace("/[ ]{2,}/", "  ", $t);
	
	return $t;
	
}

function evalImgTag_($img_src, $str=''){
	
	$arr = array();
	$arr = explode("|", $str);
	
	$ret = array(
		"position" => $arr[0],
		"link" => $arr[1],
		"caption" => $arr[2],
		"src" => $img_src
	);
	
	return "![".$ret[caption]."]($img_src)\n\n";
	
}

function evalCitation_($source='', $url=''){
	
	global $cite_i;
	
	$cite_i++;
	
	return "[^".$cite_i."][]";
	
}

function evalVideoAudioTag_($tag, $url, $options=""){
	$positions=array("left","right","center");
	$ret_options=array();
	if($options){
		$ops = array();
		$ops = explode("|", $options);
		if($ops[1]){
			if(in_array($ops[1], $positions)) $ret_options[] = $ops[1];
			elseif((int)(substr($ops[1], 0, 2))) $ret_options[] = $ops[1];
		}
		if($ops[2]) $ret_options[] = "caption=".$ops[2];
	}
	if($tag=="video"){
		$ret = $url;
	}
	elseif($tag=="audio"){
		
		//SBLOG ID
		preg_match_all("/^(http:\/\/videogam.in)?\/?s(\d+)/i", $url, $match);
		if($nid = $match[2][0]){
			$ret = $nid;
		}
		
		//URL
		preg_match_all("/^(http:\/\/(www.)?videogam.in)?\/bin\/uploads\/audio\/(.+)/i", $url, $match);
		if($filename = $match[3][0]){
			$ret = $filename;
		}
	}
	
	if($ret) return "{".$tag.":".$ret.(count($ret_options) ? "|".implode("|", $ret_options) : "")."}";
	
}
?>