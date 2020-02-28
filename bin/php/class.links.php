<?

$link_namespaces = array("Category", "Tag", "AlbumID", "User", "Special");
$link_regex = '@\[\[('.implode(':|', $link_namespaces).':)?(.*?)\]\]@ise';

class link {
	
	function html($link, $ppd=false){
		
		$pgtypes = array(
			"game"     => "games",
			"person"   => "people",
			"category" => "categories",
			"topic"    => "topics"
		);
		
		if($ppd) $ppd = "http://videogam.in";
		
		$namespaces = array("/^Category:/", "/^Tag:/");
		$pg = preg_replace($namespaces, "", $pg);
		
		//User profile
		if(substr($pg, 0, 5) == "User:"){
			$username = substr($pg, 5);
			$link_text = $username;
			if(strstr($username, "|")) {
				list($username, $link_text) = explode("|", $username);
				$link_text = trim($link_text);
			}
			return '<a href="'.$ppd.'/~'.$username.'" title="'.htmlSC($username).'\'s profile page" class="pglink">'.$link_text.'</a>';
		}
		
		//AlbimID ns
		$x = array();
		$x = explode(":", $pg);
		if($x[0] == "AlbumID" && $x[1]){
			if(strstr($x[1], "|")) {
				list($x[1], $link_text) = explode("|", $x[1]);
				$link_text = trim($link_text);
			}
			$q = "SELECT `title`, `subtitle` FROM albums WHERE albumid='$x[1]' LIMIT 1";
			if(!$album = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) return '<a href="'.$ppd.'/music" class="pglink nocoverage" rel="nofollow" title="No data for AlbumID:'.$x[1].'">'.($link_text ? $link_text : $pg).'</a>';
			return '<a href="'.$ppd.'/music?id='.$x[1].'" title="'.htmlSC($album->title.' '.$album->subtitle).'" class="pglink albumlink">'.($link_text ? $link_text : $album->title.($album->subtitle ? ' <i>'.$album->subtitle.'</i>' : '')).'</a>';
		}
		
		if(strstr($pg, "|")) {
			list($pg, $link_text) = explode("|", $pg);
			$link_text = trim($link_text);
		}
		$pg = formatName($pg);
		$link_text = stripslashes($link_text);
		
		$q = "SELECT `type`, `title`, redirect_to FROM pages WHERE `title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $pg)."' LIMIT 1";
		if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
			
			if(!$link_text) $link_text = $dat->title;
			
			//found pg
			
			if($dat->type == "game") $title = $dat->title." game overview";
			elseif($dat->type == "person") $title = $dat->title." (game developer) profile, biography, credits";
			else $title = $dat->title;
			
			if($dat->redirect_to) {
				return '<a href="'.$ppd.'/'.$pgtypes[$dat->type].'/'.formatNameURL($dat->title).'" title="This subject will redirect to a more appropriate page; Consider changing this link to the real destination page." class="pglink redirect">'.$link_text.'</a>';
				//redirected pg
				/*$q = "SELECT * FROM pages WHERE `title`='".mysqli_real_escape_string($GLOBALS['db']['link'], $dat->redirect_to)."' LIMIT 1";
				if(!$dat2 = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
					return '<a href="'.$ppd.'/pages/handle.php?title='.formatNameURL($dat->title).'" style="border-bottom:1px dotted #CA3535;" class="tooltip" title="This page is assigned to redirect, but the redirect info can\'t be found.">'.$link_text.'<sup>&dagger;</sup></a>';
				}*/
			}
			
			return '<a href="'.$ppd.'/'.$pgtypes[$dat->type].'/'.formatNameURL($dat->title).'" title="'.htmlSC($title).'" class="pglink">'.$link_text.'</a>';
			
		} else {
			//not yet in db
			return '<a href="'.$ppd.'/content/'.formatNameURL($pg).'" class="pglink nocoverage" title="No coverage yet">'.($link_text ? $link_text : $pg).'</a>';
		}
	}
	
}

class links {
	
	function extractFrom($str, $unique=true){
		
		// return array of links extracted from a string
		// @inp $str a string of text with [[links]]
		// @inp $unique return only unique links, deleting duplicates
		// @ret array [ original, tag , namespace , link_words ]
		
		$tags       = array();
		$added_tags = array();
		
		preg_match_all($GLOBALS['link_regex'], $str, $matches, PREG_SET_ORDER);
		if($matches){
			foreach($matches as $m){
				$ns = ($m[1] ? substr($m[1], 0, -1) : '');
				$tag = $m[2];
				$link_words = "";
				if(strstr($tag, "|")) {
					list($tag, $link_words) = explode("|", $tag);
				}
				$tag = formatName($tag);
				do if($tag != ""){
					if($unique && in_array($tag, $added_tags)) break;
					$tags[] = array(
						"original"   => $m,
						"tag"        => $tag, 
						"namespace"  => $ns,
						"link_words" => $link_words
					);
				} while(false);
				$added_tags[] = $tag;
			}
		}
		return $tags;
		
	}
	
}