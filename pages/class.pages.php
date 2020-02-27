<?

require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page_headers.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page_functions.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.pglinks.php";
require_once $_SERVER['DOCUMENT_ROOT']."/pages/include.pages.php";

$page->javascripts[] = "/pages/pages.js";

class pg {
	
	public $title;  // Page title
								  // ** To avoid overlapping problems with re-formatting name, $title will not be formatted here
								  // ** Format BEFORE calling this class with formatName($title)
								  
	public $pgid;   // Unique numerical ID of a page (if it's aleady been established)
	public $type;   // [game, person, category, topic]
	public $typePlural;
	public $subcategory;
	public $subcategoryPlural;
	public $row;    // a database row from PAGES table
	public $data;   // data (obtained from an xml file); Can be set with $this->loadData()
	public $url;    // auto __constructed
	public $index;  // Access indexes and index rows -- see $this->accessIndex()
	public $length; //length of xml data file (used to measure edit weight, etc)
	public $length_old;
	
	function __construct($title='', $replace_redirect_data=''){
		$this->title = $title;
		if($title == ''){
			throw new Exception("No title given");
			return;
		}
    $q = "SELECT * FROM pages WHERE `title`='".mysql_real_escape_string($title)."' LIMIT 1";
    if($this->row = mysql_fetch_assoc(mysql_query($q))){
	    if($this->row['redirect_to'] && $replace_redirect_data == true){
	    	$q = "SELECT * FROM pages WHERE `title`='".mysql_real_escape_string($this->row['redirect_to'])."' LIMIT 1";
	    	$this->row = mysql_fetch_assoc(mysql_query($q));
	    }
    	$this->title = $this->row['title'];
    	$this->pgid = $this->row['pgid'];
    	$this->redirect_to = $this->row['redirect_to'];
    	$this->type = $this->row['type'];
    	$this->typePlural = $GLOBALS['pgtypes'][$this->type];
    	if($this->subcategory = $this->row['subcategory']) $this->subcategoryPlural = $GLOBALS['pgsubcategories'][$this->subcategory];
    	$this->row['index_data'] = json_decode($this->row['index_data'], true);
    }
    $this->url = pageURL($this->title, $this->type);
    $this->edit_url = "/pages/edit.php?title=".formatNameUrl($this->title, 1);
    $this->link = '<a href="'.$this->url.'" class="pglink'.(!$this->pgid ? ' nocoverage' : '').'">'.$this->title.'</a>';
  }
  
  public function __toString(){ return $this->title; }
	
	function loadData($source=''){
		
		// Load data from an XML file
		// @param $source str [current] (default. the current version of this page), [sessid] (a session draft), a file location
		
		if($source == "sessid" || $source == "session") $xml_file = "drafts/".$this->sessid.".xml";
		else {
			if(!$this->pgid) return;
			$xml_file = $this->pgid.".xml";
		}
		
		if(!$xmld = @file_get_contents($_SERVER['DOCUMENT_ROOT']."/pages/xml/".$xml_file)) throw new Exception("Couldn't load data from ".$xml_file.".");
		$this->length = strlen($xmld);
		if(!$this->data = simplexml_load_string($xmld)) throw new Exception("Couldn't load data STRING from ".$xml_file.".");
		
		if(!$this->pgid && !$this->data['pgid']) throw new Exception("Couldn't get pgid #.");
		if(!$this->pgid) $this->pgid = (string)$this->data['pgid'];
		if(!$this->type) $this->type = (string)$this->data['type'];
		
	}
	
	function header(){
		global $page, $pgtypes, $usrid;
		
		if(!$page->called) $page = new page();
		
		$titleurl = formatNameURL($this->title);
		$ptitle = str_replace('"', "'", $this->title);
		$pgindex = $pgtypes[$this->type];
		$pdesc = $this->type ? 'A '.$this->type : '';
		if($this->data->description){
			$pdesc = bb2html($this->data->description);
			$pdesc = strip_tags($pdesc);
			$pdesc = htmlSC($pdesc);
		}
		
		$repimgtn = "/bin/img/videogam.in.png";
		if($this->row['rep_image']){
			$repimg = (string)$this->row['rep_image'];
			if(substr($repimg, 0, 4) == "img:"){
				require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.img.php";
				$img_name = substr($repimg, 4);
				$img = new img($img_name);
				$repimg = $img->src['url'];
				$repimgtn = $img->src['sm'];
				$repimgclassname = "imgupl";
			} else {
				$pos = strrpos($repimg, "/");
				$repimgtn = substr($repimg, 0, $pos) . "/" . ($this->type == "person" ? "profile_" : "md_") . substr($repimg, ($pos + 1), -3) . "png";
			}
		}
		
		if($usrid){
			// get user prefs to post to fb if user allows
			$q = "SELECT * FROM users_prefs WHERE usrid='$usrid' LIMIT 1";
			$usr_prefs = mysql_fetch_assoc(mysql_query($q));
		}
		
		//Facebook data
	  $page->head = ' prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# videogamin: http://ogp.me/ns/fb/videogamin#"';
		$page->meta["og:site_name"] = "Videogam.in, a site about videogames";
		$page->meta['og:title'] = $ptitle;
		$page->meta['og:description'] = $pdesc;
	  $page->meta['og:image'] = "http://videogam.in".$repimgtn;
	  
		if($this->type == "game"){
	  	$page->meta['og:type'] = "videogamin:game";
	  	$page->meta['og:url'] = "http://videogam.in/games/".$titleurl;
	    $page->javascript.= '<script>
				function fbAddPlay(){'.($_SESSION['fb_142628175764082_access_token'] ? '
					FB.api("/me/videogamin:play?game=http://videogam.in/games/'.$titleurl.'&access_token='.$_SESSION['fb_142628175764082_access_token'].'","post",function(response){
						console.log(response);
						if(!response.id) return false;
						return true;
					})' : 'return false;').'
				}
      </script>';
      $fb_fan_prop = 'game=http://videogam.in/games/'.$titleurl;
	  } elseif($this->type == "person"){
	  	$page->meta['og:type'] = "videogamin:person";
	  	$page->meta['og:url'] = "http://videogam.in/people/".$titleurl;
      $fb_fan_prop = 'person='.$page->meta['og:url'];
	  } else {
	  	$page->meta['og:type'] = "videogamin:page";
	  	$page->meta['og:url'] = 'http://videogam.in/'.($this->type ? $pgtypes[$this->type] : 'content').'/'.$titleurl;
      $fb_fan_prop = 'page='.$page->meta['og:url'];
	  }
		
		$page->fb = 1;
		$page->title = $ptitle." - Videogam.in";
		$page->dom['header']['class'][] = "condensed";
		$page->css[] = "/pages/pages_screen.css";
		if($usrid) $page->javascripts[] = "/bin/script/jquery-ui-1.js"; // for input-range (slider) functionality
		$page->dom['body']['class'][] = "pg pgt-".$pg->type;
		$page->no_first_section = true;
		$page->header();
		
	}
	
	function output($outp_params=''){
		
		// OUTPUT a Page //
		
		// @param $outp_params str options
		
		global $usrid, $usrname, $page, $usrrank;
		
		require $_SERVER['DOCUMENT_ROOT']."/pages/handle.page.php";
		
	}
	
	function footer(){
		global $page;
		
		$page->footer();
		// 2012-10-20 some sort fo strange conflict with /search.php outputting a pglabel() -- if there is no page it 404s, but removing the above line fixes the error......................
		
	}
	
	function trackView(){
		
		// track that the user viewed this pg
		// data used for badges & whatnot
		// @return true or false if tracked successfully
		
		if(!$GLOBALS['usrid']) return false;
		if(!$pgid = $this->pgid) return false;
		
		$q = "SELECT * FROM pages_tracks WHERE pgid='$pgid' AND usrid='".$GLOBALS['usrid']."' LIMIT 1";
		if($row = mysql_fetch_assoc(mysql_query($q))){
			$q = "UPDATE pages_tracks SET views='".(++$row['views'])."' WHERE pgid='$pgid' AND usrid='".$GLOBALS['usrid']."' LIMIT 1";
			if(mysql_query($q)) return true;
		}
		
		$q = "INSERT INTO pages_tracks (pgid, usrid, views) VALUES ('$pgid', '".$GLOBALS['usrid']."', '1');";
		if(mysql_query($q)) return true;
		
		return false;
		
	}
	
	function accessIndex($index=''){
		
		// Access a JSON index and return a row based on the given key
		// Prevents having to fetch & encode data more than once per session
		// Fetch the whole index of a pgtype, or fetch a specific row
		
		// @param $index str [game, person, category, topic]
		// @return array the values of a given $key or the whole index if $key==''
		
		if(!$index && !$index = $this->type) return;
		
		if(!$this->index->{$index}){
			$q = "SELECT `json` FROM pages_index_json WHERE `type` = '$index' LIMIT 1";
			$r = mysql_query($q);
			if(!$in = mysql_fetch_assoc($r)) return false;
			$this->index->{$index} = json_decode($in['json']);
		}
		
		if($this->title) return $this->index->{$index}->{$this->title};
		else return $this->index->{$index};
		
	}
	
	function catchTags(){
		
		// catch tags from redirected pages
		// ie [[Warcraft III]] catches [[Warcraft 3]], [[Warcraft III: Reign of Chaos]]
		// @return array titles of caught pages
		
		$ret = array();
		$query = "SELECT DISTINCT(`title`) FROM pages_links LEFT JOIN pages ON (from_pgid = pgid) WHERE `to` = '".mysql_real_escape_string($this->title)."' AND is_redirect = '1'";
		$res   = mysql_query($query);
		while($row = mysql_fetch_assoc($res)) $ret[] = $row['title'];
		return $ret;
		
	}
  
}