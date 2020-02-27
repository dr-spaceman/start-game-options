<?
function bbcode2markdown($t, $br=false){
	
	//if(substr($t, 0, 9) == "#REDIRECT") return $t;
	
	$t = str_replace("\r\n", "\n", $t);
	
	$tags = array(
				'@\[video(.*?)?\](.*?)\[/video\]@ise', 
				'@\[audio(.*?)?\](.*?)\[/audio\]@ise', 
			);
			$tags_r = array(
				"evalVideoAudioTag_('video', '\\2', '\\1')",
				"evalVideoAudioTag_('audio', '\\2', '\\1')",
			);
	$t = preg_replace($tags, $tags_r, $t);
	
	$lines = explode("\n", $t);
	$num_lines = count($lines);
	$i_source = 0;
	$sources_added = 0;
	for($n = 0; $n < $num_lines; $n++){
		$line = $lines[$n];
		
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
		
		$t=str_replace($lines[$n], $line, $t);
		
	}
	
	$t = trim($t);
	
	$t = preg_replace("/\n{2,}/", "\n\n", $t);
	
	$t = preg_replace("/[ ]{2,}/", "  ", $t);
	
	return $t;
	
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