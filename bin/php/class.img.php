<?

$img_sizes = array(
	'tn'         => 'tn',
	'thumb'      => 'tn',
	'thumbnail'  => 'tn',
	'screen'     => 'ss',
	'screenshot' => 'ss',
	'ss'         => 'ss',
	'small'      => 'sm',
	'sm'         => 'sm',
	'medium'     => 'md',
	'med'        => 'md',
	'md'         => 'md',
	'large'      => 'op',
	'lg'         => 'op',
	'optimal'    => 'op',
	'op'         => 'op',
	'default'    => 'op',
	'original'   => 'or',
);
$img_sizes_widths = array(
	'tn' => 100,
	'ss' => 200,
	'sm' => 240,
	'md' => 350,
	'op' => 620
);
//This string includes resized image sizes that use the original aspect ratio
$img_normal_sizes_widths = array(
	'tn' => 100,
	'sm' => 240,
	'md' => 350,
	'op' => 620
);
function imgGetCategories(){
	#
	# image categories stored in database
	# returns an array with img_category_id as the keys
	# @array [ img_category_id, img_category, img_category_description, sort ]
	#
	$categories = array();
	$query = "SELECT * FROM `images_categories` order by sort";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)){
		$categories[$row['img_category_id']] = $row;
	}
	return $categories;
}

class Img {
	
	public $notfound = false; //set to TRUE if after __construct image is not found in database
	public $sessid; // Session ID for uploads
	public $img_name;
	public $img_id;
	public $established;
	public $optimized; // t or f if optimized size exitst
	public $src;
	
	function __construct($img_params=""){
		
		# var $img_params string Either the numeric img_id or the img_name (ie "DonkeyKong.jpg")
		# if not found, sets $notfound=true and grabs "unknown.png", a placeholder image
		
		$this->notfound = false;
		
		$img_params = trim($img_params);
		if($img_params == "") return $this->emptyImg();
		
		//if the given string is all numeric, find the image by img_id,
		//otherwise find by img_name
		if(ctype_digit($img_params)) $img_id = $img_params;
		else $img_name = $img_params;
		
		$q = "SELECT * FROM images WHERE ".($img_name ? "img_name='".mysqli_real_escape_string($GLOBALS['db']['link'], $img_name)."'" : "img_id='$img_id'")." LIMIT 1";
		if(!$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) return $this->emptyImg();
		
		$this->img_name = $row['img_name'];
		$this->img_id = $row['img_id'];
		$this->img_session_id = $row['img_session_id'];
		$this->sessid = $row['img_session_id'];
		$this->img_size = $row['img_size'];
		$this->img_width = $row['img_width'];
		$this->img_height = $row['img_height'];
		$this->img_bits = $row['img_bits']; 
		$this->img_minor_mime = $row['img_minor_mime'];
		$this->img_category_id = $row['img_category_id'];
		$this->img_title = $row['img_title'];
		$this->img_description = $row['img_description'];
		$this->sort = $row['sort'];
		$this->usrid = $row['usrid'];
		$this->img_timestamp = $row['img_timestamp'];
		$this->img_views = $row['img_views'];
		
		$this->src = $this->src();
		
	}
	
	function emptyImg(){
		$this->notfound = true;
		$this->img_name = "unknown.png";
		$this->img_id = 0;
		$this->img_session_id = '121006204647000000199';
		$this->img_size = 3810;
		$this->img_width = 601;
		$this->img_height = 601;
		$this->img_minor_mime = "png";
		$this->img_category_id = 0;
		$this->img_title = "";
		$this->img_description = "";
		$this->sort = 0;
		$this->src = $this->src();
		return false;
	}
	
	function src(){
		
		// @return array list of img files and URLs
		
		$src[0] = '';
		$src['dir'] = "/images/".substr($this->img_session_id, 12, 7)."/";
		$src[0] = $src['dir'].$this->img_name;
		$src['or'] = $src[0];
		$src['url'] = "/image/".$this->img_name;
		$src['op'] = $src['dir']."op/".$this->img_name;
		if(file_exists($_SERVER['DOCUMENT_ROOT'].$src['op'])) $this->optimized = true;
		else{ $this->optimized = false; $src['op'] = $src[0]; }
		$src['optimized'] = $src['op'];
		$src['md'] = $src['dir']."md/".$this->img_name;
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].$src['md'])) $src['md'] = $src[0];
		$src['box'] = $src['dir']."box/".$this->img_name.".png";
		$src['sm'] = $src['dir']."sm/".$this->img_name;
		$src['ss'] = $src['dir']."ss/".$this->img_name.".png";
		$src['tn'] = $src['dir']."tn/".$this->img_name.".png";
		return $src;
		
	}
	
	function output($size='op', $rel='', $figstyle=''){
		//@attr $rel image group
		$alt = $this->img_title ? htmlsc($this->img_title) : $this->img_name;
		$src = $this->src[$size] ? $this->src[$size] : $this->src['op'];
		return '<div class="imagefigure" style="'.$figstyle.'"><a href="'.$this->src['url'].'" title="'.$alt.'" rel="'.$rel.'" class="imgupl" data-imgname="'.$this->img_name.'"><img src="'.$src.'" alt="'.$alt.'"/></a></div>';
	}
	
	function remove(){
		
		// remove an image
		// @return boolean
		// any errors passed to $img_remove_error
		
		if(!$this->img_id){
			if(!$this->img_name) return $this->removeError("No image data could be found with which to remove");
			if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], "SELECT img_id FROM images WHERE img_name = '".mysqli_real_escape_string($GLOBALS['db']['link'], $this->img_name)."' LIMIT 1"))) return $this->removeError("No image data could be found with which to remove");
			$this->img_id = $dat->img_id;
		}
		
		$q = "SELECT * FROM images WHERE img_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $this->img_id)."' LIMIT 1";
		if(!$img = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) return $this->removeError("No image data found for image ID #".$this->img_id);
		
		$q = "DELETE FROM images WHERE img_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $this->img_id)."' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)){
			$img = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM images WHERE img_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $this->img_id)."' LIMIT 1"));
			if(!$img) return $this->removeError("Unknown error removing image: img_id:".$this->img_id."; ".mysqli_error($GLOBALS['db']['link']));
			else return $this->removeError("Couldn't remove image file <i>".$img->img_name."</i> from database; ".mysqli_error($GLOBALS['db']['link']));
		}
		
		$q = "SELECT * FROM images WHERE img_session_id = '$img->img_session_id'";
		if($num_sess_imgs = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))){
			$q2 = "UPDATE images_sessions SET img_qty = '$num_sess_imgs' WHERE img_session_id = '$img->img_session_id' LIMIT 1";
		} else {
			$q2 = "DELETE FROM images_sessions WHERE img_session_id = '$img->img_session_id' LIMIT 1";
		}
		
		$q = "DELETE FROM images_tags WHERE img_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $this->img_id)."'";
		mysqli_query($GLOBALS['db']['link'], $q);
		
		if(!mysqli_query($GLOBALS['db']['link'], $q2)) return $this->removeError("Couldn't update session database table");
		
		return true;
		
	}
	function removeError($error_message=''){
		$this->img_remove_error = $error_message ? $error_message : "An unknown error occurred.";
		return false;
	}

	/**
	 * Create a unique (hopefully...) integer to identify upload sessions
	 * @return integer The ID
	 */
	public static function makeSessionID() {
		return date("ymdHis").sprintf("%07d",$GLOBALS['usrid']).mt_rand(0,9).mt_rand(0,9);
	}
	
	function getSessionData(){
		
		// get info about this image's session, including previous & next files
		
		$q = "SELECT * FROM images_sessions WHERE img_session_id = '".$this->sessid."' LIMIT 1";
		if(!$this->session_row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) return;
		
		/*if($this->session_data['img_qty'] > 1){
			$q = "SELECT * FROM images WHERE ";*/
		
	}
	
	function categoryName(){
		if(!$this->img_category_id) return;
		if($this->img_category) return $this->img_category;
		$query = "SELECT img_category FROM `images_categories` WHERE img_category_id = '$this->img_category_id' LIMIT 1";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)){
			$this->img_category = $row['img_category'];
		}
		return $this->img_category;
	}
	
}

class gallery {
	
	var $id;
	var $sessid;
	var $parsed; // t or f if parse()
	var $bbcode;
	var $html;
	var $caption;
	var $show;
	var $size;
	var $width;
	var $files; // array of img names, or str of {img} files ie '{img:foobar.jpg}{img:fuuuu.png}'
	var $opt_str; //str of options, ie caption=foo|show=3|50|thumbnail
	
	function __construct(){
		$this->id = rand(0, 99999);
	}
	
	function parse(){
		
		// parse data using $files and $opt_str
		
		if(!$this->files && !$this->opt_str) return false;
		
		if($this->opt_str){
			$opts = array();
			$opts = explode("|", $this->opt_str);
			foreach($opts as $opt){
				if(substr($opt, 0, 8)=="session=") $this->sessid = substr($opt, 8);
				if(substr($opt, 0, 8)=="caption=") $this->caption = substr($opt, 8);
				if(substr($opt, 0, 5)=="show=") $this->show = substr($opt, 5);
				if(in_array($opt, array_keys($GLOBALS['img_sizes']))) $this->size = $GLOBALS['img_sizes'][$opt];
				if(is_numeric($opt) === TRUE && (int)$opt == $opt) $this->width = $opt;
			}
		}
		
		if(is_numeric($this->show) === FALSE || (int)$this->show != $this->show) unset($this->show);
		if($this->width && $this->width < 25) $this->width = 25;
		if($this->width && $this->width > 240){ /*unset($this->width);*/ $this->size = "op"; }
		
		if(!$this->size && !$this->width) $this->size = 'tn';
		elseif(!$this->size && $this->width <= 100) $this->size = "tn";
		elseif(!$this->size && $this->width <= 240) $this->size = "sm";
		
		if(!in_array($this->size, array('tn', 'ss', 'sm', 'op'))) $this->size = 'tn';
		
		if($this->sessid){
			$q = "SELECT * FROM images_sessions WHERE img_session_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $this->sessid)."' LIMIT 1";
			if(!$img_session = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) return '{Error displaying gallery: session ID "'.$this->sessid.'" doesn\'t exist}';
			$query = "SELECT * FROM images WHERE img_session_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $this->sessid)."' ORDER BY `sort` ASC";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)){
				$img = new img($row['img_name']);
				$row['src'] = $img->src;
				$this->imgs[$row['img_name']] = $row;
			}
		} else {
			if(is_string($this->files)){
				//echo "STR:".htmlspecialchars($this->files);
				preg_match_all('@\{img:([a-z0-9-_!\.]+)\|?(?:.*?)\}@is', $this->files, $matches);
				if(count($matches[1])) $this->files = $matches[1];
				//echo "MATCHES:";print_r($matches);
			}
			if(is_array($this->files)){
				//echo "FILES:";print_r($this->files);
				foreach($this->files as $img_name){
					$q = "SELECT * FROM images WHERE img_name = '".mysqli_real_escape_string($GLOBALS['db']['link'], $img_name)."' LIMIT 1";
					if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
						$img = new img($img_name);
						$row['src'] = $img->src;
						$this->imgs[$row['img_name']] = $row;
					}
				}
			}
		}
		
		$this->parsed = true;
		
	}
	
	function BBencode(){
		
		// build (or rebuild) a gallery code
		// sets code to $this->bbcode
		
		if(!$this->parsed) return false;
		
		if(is_array($this->files)){
			foreach($this->files as $file) $files_str.= '{img:'.$file.'}';
		} else {
			$files_str = $this->files;
		}
		
		$this->bbcode = '[gallery';
		if($this->sessid) $this->bbcode.= '|session='.$this->sessid;
		if($this->size) $this->bbcode.= '|'.$this->size;
		if($this->width) $this->bbcode.= '|'.$this->width;
		if($this->caption) $this->bbcode.= '|caption='.$this->caption;
		if($this->show) $this->bbcode.= '|show='.$this->show;
		$this->bbcode.= ']'.$files_str.'[/gallery]';
		
		return $this->bbcode;
		
	}
	
	function HTMLencode(){
		
		if(!$this->parsed) $this->parse();
		
		$ret = '<div class="gallery">'.
			($this->caption ? '<div class="caption">'.$this->caption.'</div>' : '').
			'<div class="container">';
		if(is_array($this->imgs)){
			foreach($this->imgs as $img){
				$i++;
				$ret.= '<figure style="'.($this->show && $i > $this->show ? 'display:none;' : '').'"><a href="/image/'.$img['img_name'].'" class="imgupl" rel="'.$this->id.'" data-imgname="'.$img['img_name'].'" style="width:'.$this->width.'px;"><img src="'.$img['src'][$this->size].'"/></a></figure>';
			}
		}
		$ret.= '</div></div>'."\n\n";
		return $ret;
		
	}
	
}

?>