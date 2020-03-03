<?
#
# Class to handle linking to content pages
#
#

include_once $_SERVER['DOCUMENT_ROOT']."/pages/include.pages.php";

class pglinks {
	
	var $namespaces;
	var $regex;
	var $prepend_domain; // @t/f make all local links point to 'http://videogam.in/...' instead of '/...'
	var $attr=array();   // @array adjust link attributes like target, class, etc
	var $attrs=array();  // @array additional attributes (ie data-)
	var $rm_duplicates;  // @ remove duplicate links
	
	function __construct(){
		$this->regex = '@\[\[('.implode(':|', PAGES_NAMESPACES).':)?(.*?)\]\](s|\'s)?@is';
	}
	
	function parse($text){
		
		// parse a string of text, replacing all [[page links]] with html links
		
		// @input $text string
		
		//$text = preg_replace($this->regex, "$this->outputLink('\\2', '\\1')", $text);
		
		foreach($this->extractFrom($text, false) as $link){
			if($this->rm_duplicates){
				$s = $link['original'];
				$r = $this->outputLink($link['tag'], $link['namespace'], $link['link_words']);
				$r2 = $link['link_words'] ? $link['link_words'] : $link['tag'];
				$text = str_replace_once($s, $r, $text);
				$text = str_replace($s, $r2, $text);
			} else {
				$text = str_replace($link['original'], $this->outputLink($link['tag'], $link['namespace'], $link['link_words']), $text);
			}
		}
		
		return $text;
		
	}
	
	function outputLink($tag='', $namespace='', $title=''){
		
		global $pgtypes;
		
		if($tag == '') return;
		
		if($this->prepend_domain) $ppd = "http://videogam.in";
		
		$tag = formatName($tag);
		$title = trim($title);
		$title = $title ? $title : $tag;
		
		//User
		if(strtolower($namespace) == "user"){
			$attr = array("href" => $ppd.'/~'.$tag, "title" => $tag."'s profile page", "class" => "pglink");
			return $this->buildLink($attr, $title);
		}
		
		//AlbumID
		if(strtolower($namespace) == "albumid"){
			require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.albums.php";
			$album = new album($tag);
			if($album->notfound) return "[Album ID #" . $tag . " not found]";
			if(!$album->url)
				return 
					$this->buildLink(
						array("href" => $ppd."/music", "class" => "pglink nocoverage", "rel"=> "nofollow", "title" => "No data for AlbumID:".$tag),
						$title
					);
			return 
				$this->buildLink(
					array("href" => $ppd.$album->url, "title" => htmlSC($album->full_title), "class" => "pglink albumlink"), 
					($title != $tag ? $title : $album->full_title)
				);
		}
		
		$q = "SELECT `type`, `title`, subcategory, redirect_to FROM pages WHERE `title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $tag)."' LIMIT 1";
		if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))){
			
			//found pg
			
			if($dat->redirect_to) {
				return $this->buildLink(array("href"=>$ppd.'/'.$pgtypes[$dat->type].'/'.formatNameURL($dat->title), "title"=>"This subject will redirect to a more appropriate page; Consider changing this link to the real destination page.", "class"=>"pglink redirect"), $title);
				//redirected pg
				/*$q = "SELECT * FROM pages WHERE `title`='".mysqli_real_escape_string($GLOBALS['db']['link'], $dat->redirect_to)."' LIMIT 1";
				if(!$dat2 = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
					return '<a href="'.$ppd.'/pages/handle.php?title='.formatNameURL($dat->title).'" style="border-bottom:1px dotted #CA3535;" class="tooltip" title="This page is assigned to redirect, but the redirect info can\'t be found.">'.$link_text.'<sup>&dagger;</sup></a>';
				}*/
			}
			
			if($dat->type == "game"){
				$link['title'] = $dat->title." game overview";
				if($title == $tag) $link['tag'] = "cite";
			} elseif($dat->type == "person"){
				$link['title'] = $dat->title." (game developer) profile, biography, credits";
			} elseif($dat->subcategory == "Game series"){
				$link['tag'] = "cite";
			}
			
			$link['href'] = $ppd.'/'.$pgtypes[$dat->type].'/'.formatNameURL($dat->title);
			$link['class'] = "pglink";
			return $this->buildLink($link, $title);
			
		} else {
			//not yet in db
			$attr = array("href" => $ppd.'/content/'.formatNameURL($tag), "title" => "No coverage yet", "class" => "pglink nocoverage");
			return $this->buildLink($attr, $title);
		}
	}
	
	function buildLink($attr, $title) {
		
		# @var $attr array [title, href, target, class, id, tag ('cite', 'i', 'b', 'strong', etc.)]
		# @var $title link words, ie <a>$title</a>
		
		$ret = '<a href="'.$attr['href'].'" title="'.($attr['title'] ? htmlSC($attr['title']) : htmlSC($title)).'"';
		
		if($this->attr['target']) $attr['target'] = $this->attr['target'];
		if($attr['target']) $ret.= ' target="'.$attr['target'].'"';
		
		$attr['class'].= ' '.$this->attr['class'];
		$attr['class'] = trim($attr['class']);
		if($attr['class']) $ret.= ' class="'.$attr['class'].'"';
		
		$attr['id'] = $this->attr['id'] ? $this->attr['id'] : $attr['id'];
		if($attr['id']) $ret.= ' id="'.$attr['id'].'"';
		
		foreach($this->attrs as $tag => $val) $ret.= ' '.$tag.'="'.htmlsc($val).'"';
		
		if($attr['tag']) $title = '<'.$attr['tag'].'>'.$title.'</'.$attr['tag'].'>';
		
		$title = str_replace(' series</cite>', '</cite> series', $title);
		
		return $ret . '>'.$title.'</a>';
		
	}
	
	function extractFrom($str, $unique=true, $alpha=false) {
		
		// return array of links extracted from a string
		
		// @inp $str string A string of text with [[links]]
		// @inp $unique t/f Return only unique links, deleting duplicates
		// @inp $alpha t/f Alphabetize links
		
		// @ret array [ original, tag , namespace , link_words ]
		
		$tags       = array();
		$tags_index = array();
		$added_tags = array();
		$i = 0;
		
		preg_match_all($this->regex, $str, $matches, PREG_SET_ORDER);
		if($matches){
			foreach($matches as $m){
				$tag = $m[2];
				$ns  = $m[1];// ? str_replace(":", "", $m[1]) : '';
				/*$ns = '';
				if(strstr($tag, ":")){
					$ns_matches = '';
					preg_match('@^('.implode('|', $GLOBALS['link_namespaces']).'):@ise', $tag, $ns_match);
					if($ns_match[1]){
						$ns = $ns_match[1];
						$tag = substr($tag, strlen($ns));
					}
				}*/
				$link_words = "";
				if(strstr($tag, "|")) {
					list($tag, $link_words) = explode("|", $tag);
				} elseif($m[3]){
					$link_words = $tag.$m[3];
				}
				$tag = formatName($tag);
				do if($tag != ""){
					if($unique && in_array($tag, $added_tags)) break;
					$tags[$i] = array(
						"original"   => $m[0],
						"tag"        => $tag, 
						"namespace"  => $ns ? str_replace(":", "", $ns) : '',
						"link_words" => $link_words
					);
					$tags_index[$i] = ($link_words ? $link_words : $tag);
					$i++;
				} while(false);
				$added_tags[] = $tag;
			}
		}
		if($alpha){
			asort($tags_index);
			$tags_ = array();
			foreach($tags_index as $key => $tag){
				$tags_[] = $tags[$key];
			}
			$tags = $tags_;
		}
		return $tags;
		
	}

	/**
	 * Strip links from text
	 * Right now, this only strips and returns the text value of the first link found
	 * @param  string $str Input text to strip
	 * @return string      Output text stripped
	 */
	public static function strip ($str) {
		if(!strstr($str, "[[")) return $str;

		// There should be a better way to do this!
		$pglinks = new pglinks();
		if($exlinks = $pglinks->extractFrom($str)) {
			return $exlinks[0]['tag'];
		}
	}
	
}

function parseLink($str){
	//quickly parse a pagelink string into an array of data
	//ie [[Category:Foobar|Foo and Bar]]
	$l = new pglinks();
	foreach($l->extractFrom($str) as $link) return $link;
	return array();
}

function links($str){
	$l = new pglinks();
	return $l->parse($str);
}
