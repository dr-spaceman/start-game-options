<?

/**
 * Class to organize, arrange, and output a shelf of products
 */

class Shelf {
	
	/** @var integer number of items currently on the shelf */
	public $num_items = 0;

	/** @var array of shelf items added */
	private $items = array();

	/** @var integer the default item hight, obviously! Can be changed with contructions params 
		If changed, also should be changed in class ShelfItem ... bad programming! */
	private $default_item_height = 120;

	/**
	 * Construct a new shelf with specified properties
	 * @param array $props Property values
	 */
	
	public function __construct($props = array()) {

		if (isset($props['default_item_height'])) $this->default_item_height = (int) $props['default_item_height'];

		//echo "Shelf::__Construct;"; print_r($props);
	}

	public function addItem($props = array()) {

		$this->items[] = new ShelfItem($props);
		$this->num_items++;

	}

	/**
	 * return a shelf populated with items added
	 * @param array $props Propertis to configure output [output_nav bool, output_container bool]
	 * @return string HTML
	 */
	public function output($props = array()) {

		$output_html = "<!-- Shelf:output ({$this->num_items} items) -->";

		if ($this->num_items == 0) return $output_html;

		// Add a nav panel if there's enough items
		if ($this->num_items > 5 && $props['output_nav'] !== false) {
			$output_html.= '<a href="/js.htm" title="Traverse left" class="trav prev" onclick="shelf.traverse($(this).parent(), -1, 6); return false;"></a><a href="/js.htm" title="Traverse right" class="trav next" onclick="shelf.traverse($(this).parent(), 1, 6); return false;"></a>' . PHP_EOL;
		}
		
		foreach ($this->items as $item) {
			$output_html.= $item->output();
		}
		
		if ($props['output_container'] !== false) {
			$output_html = '<div class="shelf-container" style="width:' . ($this->num_items * 198 + 800) . 'px;">' . $output_html . '</div>';
		}
		
		return $output_html;

	}

}

class ShelfItem {

	private $instance = false;

	private static $instance_id = 0;

	/** @var int A flexible ID assigned at __construct. Because the DOM needs a unique ID to perform certain actions, this can be reset outside the class */
	public $id;

	/** @var integer the default item hight, obviously! Can be changed with contructions params 
		If changed, also should be changed in class Shelf ... bad programming! */
	private $default_item_height = 120;

	/** @var string Type of item for custom styling output
	 	Supported: [game, album] (Others won't get special styling) */
	private $type;

	private $title;

	/** @var string A raw file name, relative location of an image file */
	private $filename = null;

	/** @var object An image object called from the Img class */
	private $img;
	
	/** @var array result of getimagesize($filename);
		0:width; 
		1:height; 
		3:string for IMG tag (height="yyy" width="xxx") */
	public $thumb_size = array();

	public $thumb_height = null;

	/** @var array Overloaded properties */
	private $properties = array();

	public function __construct($props = array()) {

		if (isset($props['default_item_height'])) $this->default_item_height = (int) $props['default_item_height'];

		if ($this->instance) return;
        $this->instance = true;

        static::$instance_id++;
        $this->id = $props['id'] ? $props['id'] : uniqid();

        //echo "ShelfItem::init;";print_r($props);

		if (isset($props['filename'])) {

			// A raw image file name has been specified
			
			$this->filename = $props['filename'];
			if(substr($this->filename, 0, 13) == "/pages/files/") {
				$pos = strrpos($this->filename, "/");
				$this->filename = substr($this->filename, 0, $pos) . "/" . ($props['type'] == "person" ? "profile_" : "md_") . substr($this->filename, ($pos + 1), -3) . "png";
			}
		    $this->thumb_size = getimagesize($_SERVER['DOCUMENT_ROOT'].$this->filename);
		    $this->thumb_height = round($this->thumb_size[1] / ($this->thumb_size[0] / 140));
		
		} elseif (isset($props['img']) && $props['img'] instanceof Img) {
		
			// An image from the `images` database has been specified

			$this->img = $props['img'];
			if($this->img->img_category_id == 15){ //logo/icon
				// Don't show the image
				/*$this->filename = $this->img->src['tn'];
				$this->thumb_height = 100;*/
			} elseif(in_array($this->img->img_category_id, array(1,11,12,13,14))){ //screenshots
				// Don't show the image
				/*$this->filename = $this->img->src['sm'];
				$this->thumb_height = 1000;
				$this->properties['is_screenshot'] = true;*/
			} else {
				$this->filename = $this->img->src['box'];
				$this->thumb_height = $this->img->img_height / ($this->img->img_width / 140);
			}
			$this->thumb_height = round($this->thumb_height);
		
		} else {

			// A shelf item without an image
			
			$this->thumb_height = $this->default_item_height;

		}

		if (isset($props['type'])) $this->type = $props['type'];
		if (isset($props['title'])) $this->title = $props['title'];

		$this->properties = $props;

		return $item;

	}

	/**
	 * A shelf item in HTML output
	 * @param  array  $props Properties to modify output
	 * @return string        HTML
	 */
	public function output($props = array()) {

		// Properties that modify output:
		// [platform] a platform name (eg. "Nintendo DS"), evaluated for special box art orientation
		// [img_orientation] Platform name to evaluate and display box art orientation (ie "Nintendo DS")
		// [orientation] (not a db row -- set manually after calling $shelf) a classname of a box art orientation ( ie "ds") override any default orientations
		// [default_orientation] fall back on this orientation if there isn't a special orientation for [platform] or [img_orientation]
		
		$dl_class = '';
		$or = '';
		$title_safe = htmlSC($this->title);
		$tn_offset = $this->thumb_height ? ceil($this->thumb_height / 2) : '';

		$props = array_merge($this->properties, $props);
		
		if ($this->type == "game") {
			
			$rd = $props['release_date'] ? $props['release_date'] : $props['release_year']."-".$props['release_month']."-".$props['release_day'];
			if($rd == "--" || $rd == "0000-00-00") $rd = '';
			
			if($platform_safe = $props['platform']) {
				
				//create $pf string to evaluate and decide which image orientation to output
				$pf = $props['img_orientation'] ? $props['img_orientation'] : $platform_safe;
        		$pf = strtolower($pf);
				
				// if the image type is a screenshot, display a screen instead of box art
				/*if(!$props['orientation'] && $this->properties['is_screenshot']){
					//echo "eval $pf ::";
					switch($pf){
						case "nintendo 3ds": $props['orientation']="screen-n3ds"; break;
						case "nintendo ds": $props['orientation']="screen-ds"; break;
						case "android": $props['orientation']="screen-android"; break;
						case "iphone":
						case "ios": $props['orientation']="screen-iphone"; $platform_safe="iPhone"; $props['platform']="[[iPhone]]"; break;
						case "ipad": $props['orientation']="screen-ipad"; break;
						default: $props['orientation']="screen";
					}
				} elseif(!$props['orientation'] && $this->img->img_category_id == 15) $props['orientation'] = "icon";*/
				
				if($props['orientation']) $or = $props['orientation'];
				//elseif($this->filename && $this->img->img_category_id != 4 && $this->img->img_category_id != 16) $or = "screen";
				//if it's not box art (4) or faux box art (16), display screen orientation
				//and skip the following platform check
				elseif(strstr($pf, "3ds")) $or = "n3ds";
				elseif(strstr($pf, "ds")) $or = "ds";
				elseif(strstr($pf, "playstation portable")) $or = "psp";
				elseif(strstr($pf, "vita")) $or = "vita";
				elseif(strstr($pf, "playstation 3")) $or = "ps3";
				elseif(strstr($pf, "playstation 2") && $this->thumb_height > 190) $or = "dvd";
				elseif(strstr($pf, "dvd") && $this->thumb_height > 190) $or = "dvd";
				elseif($pf == "playstation" && $this->thumb_height > 130) $or = "ps";
				elseif(strstr($pf, "wii") && $this->thumb_height > 190) $or = "wii";
				elseif(strstr($pf, "gamecube") && $this->thumb_height > 190) $or = "dvd";
				elseif(strstr($pf, "dreamcast")) $or = "ps dc";
				elseif(strstr($pf, "xbox") && $this->thumb_height > 190) $or = "xbox";
				else $or = $props['default_orientation'];
				
			}

			// Special style orientations with jewel case overlay
			// $special_style = array("screen", "jewelcase", "ps", "n3ds", "ds", "psp", "vita", "ps3", "dvd", "wii", "ps dc", "xbox");
			
			if(strlen($props['region']) == 2){
				$props['region_abbr'] = $props['region'];
				$pf_regions_r = array_flip($GLOBALS['pf_regions']);
				$props['region'] = $pf_regions_r[$props['region']];
			} elseif($props['region']) {
				$props['region_abbr'] = $GLOBALS['pf_regions'][$props['region']];
			}
			
			if(!$props['href']) $props['href'] = "#";
			
			if($platform_safe) $platform_safe = htmlSC($platform_safe);
			$platform_safe_short = $GLOBALS['pf_shorthand'][strtolower($platform_safe)];
			if(!$platform_safe_short) $platform_safe_short = $platform_safe;
			$platform_safe_heading = strstr($props['platform'], "]]") ? ($props['platform']) : $platform_safe_short;
			
			$output_html = 
				'<div id="shelf-item-id-'.$this->id.'" class="shelf-item game '.$or.'" data-platform="'.$platform_safe.'" data-img="'.$this->img->img_name.'" data-distribution="'.$props['distribution'].'" data-release="'.$rd.'" rel="'.$pf.'">';
					if(!$props['no_headings']){ $output_html.= '
					<div class="shelf-headings">
						<dl>
							<dt>Title</dt><dd class="game-title"><strong>'.$this->title.'</strong></dd>
							<dt>Platform</dt><dd>'.$platform_safe_heading.'</dd>
							'.($rd ? '<dt>Release date</dt><dd><span title="'.$props['region'].'" style="padding-right:20px; background:url(/bin/img/flags/'.$props['region_abbr'].'.png) no-repeat right center;">'.formatDate($rd, 6).'</span></dd>' : '').'
						</dl>
					</div>'; } $output_html.= '
					<div class="shelf-img'.(!$this->filename ? ' noimg' : '').'" style="">
						<a href="'.$props['href'].'" title="'.$title_safe.($platform_safe_short ? ' &middot; '.$platform_safe_short : '').($props['region'] ? ' &middot; '.$props['region'] : '').($rd ? ' &middot; '.substr($rd, 0, 4) : '').'" class="'.(substr($props['href'], 0, 7) == "/image/" ? "imgupl" : "").'">'.
							($this->filename ? '<span class="tn"><img src="'.$this->filename.'" alt="'.$title_safe.' box art for '.$platform_safe_short.'" border="0"/></span>' : '<span class="tn noimg"><b>'.$this->title.'</b></span>').
							'<span class="overlay"></span>'.
						'</a>'.
						//???????????????
						($this->op ? '<span class="op '.$this->op.'" title="'.ucwords($this->op).'"></span>' : '').'
					</div>
				</div>';
			
		} elseif($this->type=="album") {
			
			// Removed this but have to address it later
			//if(!$this->no_img && file_exists($_SERVER['DOCUMENT_ROOT'].'/music/media/cover/standard/'.$props['albumid'].'.png')) $this->setImg('/music/media/cover/standard/'.$props['albumid'].'.png');
			
			if(!$props['href']) $props['href'] = "/music/?id=".$props['albumid'];
			
			$output_html = 
				'<div id="shelf-item-id-'.$this->id.'" class="shelf-item album jewelcase positioned">
					<div class="shelf-headings">
						<dl>
							<dt>Title</dt><dd><strong>'.$this->title.'</strong></dd>
							'.($props['description'] ? '<dd>'.($props['description']).'</dd>' : '').'
						</dl>
					</div>
					<div class="shelf-img'.(!$this->filename ? ' noimg' : '').'">
						<a href="'.$props['href'].'" title="'.$title_safe.'" class="'.(substr($props['href'], 0, 7) == "/image/" ? "imgupl" : "").'">'.($this->filename ? '<span class="tn"><img src="'.$this->filename.'" alt="'.$title_safe.'" border="0" style="margin-top:-'.ceil($this->thumb_height / 2).'px"/></span>' : '<span class="tn noimg"><b>'.$props['title'].'</b></span>').'<span class="overlay"></span></a>
						'.($this->op ? '<span class="op '.$this->op.'" title="'.ucwords($this->op).'"></span>' : '').'
					</div>
				</div>';
			
		} else {
			
			$output_html = 
				'<div id="shelf-item-id-'.$this->id.'" class="shelf-item '.$this->type.'" data-img="'.$this->img->img_name.'">
					<div class="shelf-headings">
						<dl>
							<dt>Title</dt><dd><strong>'.$this->title.'</strong></dd>
							'.($props['description'] ? '<dd>'.($props['description']).'</dd>' : '').'
						</dl>
					</div>
					<div class="shelf-img'.(!$this->filename ? ' noimg' : '').'" style="'.($this->thumb_height ? 'bottom:50%; margin-bottom:-'.ceil(($this->thumb_height/2)).'px;' : '').'">
						<a href="'.$props['href'].'" title="'.$title_safe.'" class="'.(substr($props['href'], 0, 7) == "/image/" ? "imgupl" : "").'">'.($this->filename ? '<span class="tn"><img src="'.$this->filename.'" alt="'.$title_safe.'" border="0"/></span>' : '<span class="tn noimg"><b>'.$this->title.'</b></span>').'<span class="overlay"></span></a>
						'.($this->op ? '<span class="op '.$this->op.'" title="'.ucwords($this->op).'"></span>' : '').'
					</div>
				</div>';
				
		}
		
		return $output_html;

	}

	// Old methods below
	
	/*function OLDoutputItem($row) {
		
		// @var $row array a database row from `collection`
		// [platform] a platform name (ie "Nintendo DS") that is evaluated for special box art orientation
		// [img_orientation] Platform name to evaluate and display box art orientation (ie "Nintendo DS")
		// [orientation] (not a db row -- set manually after calling $shelf) a classname of a box art orientation ( ie "ds") override any default orientations
		// [default_orientation] fall back on this orientation if there isn't a special orientation for [platform] or [img_orientation]
		//print_r($row);
		$this->id = $row['id'] ? $row['id'] : rand(0,9999999);
		
		$dl_class = '';
		$or = '';
		$titleSC = htmlSC($row['title']);
		
		$tn_offset = $this->tn->height ? ceil($this->tn->height / 2) : '';
		
		//$bb = new bbcode();
		//$bb->params['minimal'] = true;
		
		if($this->type=="game"){
			
			if(!$row['platform'] && $this->img->img_name){
				$q = "SELECT platform FROM games_publications WHERE img_name = '".(string)$this->img->img_name."' LIMIT 1";
				if($row2 = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) $row = array_merge((array)$row, $row2);
			}
			
			$rd = $row['release_date'] ? $row['release_date'] : $row['release_year']."-".$row['release_month']."-".$row['release_day'];
			if($rd == "--" || $rd == "0000-00-00") $rd = '';
			if($platform_safe = $row['platform']){
				
				//strip links
				if(strstr($platform_safe, "[[")){
					$pglinks = new pglinks();
					if($exlinks = $pglinks->extractFrom($platform_safe)){
						$platform_safe = $exlinks[0]['tag'];
					}
				}
				
				//create $pf string to evaluate and decide which image orientation to output
				$pf = $row['img_orientation'] ? $row['img_orientation'] : $platform_safe;
        $pf = strtolower($pf);
				
				// if the image type is a screenshot, display a screen instead of box art
				if(!$row['orientation'] && $this->img->is_screenshot){
					//echo "eval $pf ::";
					switch($pf){
						case "nintendo 3ds": $row['orientation']="screen-n3ds"; break;
						case "nintendo ds": $row['orientation']="screen-ds"; break;
						case "android": $row['orientation']="screen-android"; break;
						case "iphone":
						case "ios": $row['orientation']="screen-iphone"; $platform_safe="iPhone"; $row['platform']="[[iPhone]]"; break;
						case "ipad": $row['orientation']="screen-ipad"; break;
						default: $row['orientation']="screen";
					}
				} elseif(!$row['orientation'] && $this->img->img_category_id == 15) $row['orientation'] = "icon";
				
				if($row['orientation']) $or = $row['orientation'];
				elseif($this->tn->src && $this->img->img_category_id != 4 && $this->img->img_category_id != 16) $or = "screen";
				//if it's not box art (4) or faux box art (16), display screen orientation
				//and skip the following platform check
				elseif(strstr($pf, "3ds")) $or = "n3ds";
				elseif(strstr($pf, "ds")) $or = "ds";
				elseif(strstr($pf, "playstation portable")) $or = "psp";
				elseif(strstr($pf, "vita")) $or = "vita";
				elseif(strstr($pf, "playstation 3")) $or = "ps3";
				elseif(strstr($pf, "playstation 2") && $this->tn->height > 190) $or = "dvd";
				elseif(strstr($pf, "dvd") && $this->tn->height > 190) $or = "dvd";
				elseif($pf == "playstation" && $this->tn->height > 130) $or = "ps";
				elseif(strstr($pf, "wii") && $this->tn->height > 190) $or = "wii";
				elseif(strstr($pf, "gamecube") && $this->tn->height > 190) $or = "dvd";
				elseif(strstr($pf, "dreamcast")) $or = "ps dc";
				elseif(strstr($pf, "xbox") && $this->tn->height > 190) $or = "xbox";
				else $or = $row['default_orientation'];
				
			}
			
			$positioned_orientations = array("screen", "jewelcase", "ps", "n3ds", "ds", "psp", "vita", "ps3", "dvd", "wii", "ps dc", "xbox");
			if($or && in_array($or, $positioned_orientations)){
				if($this->img->is_screenshot) $img_style = 'position:absolute; bottom:0;';
				else $img_style = 'position:absolute; top:50%; margin-top:-'.$tn_offset.'px;';
				$i_class = "positioned";
			}
			
			if(strlen($row['region']) == 2){
				$row['region_abbr'] = $row['region'];
				$pf_regions_r = array_flip($GLOBALS['pf_regions']);
				$row['region'] = $pf_regions_r[$row['region']];
			} elseif($row['region']) {
				$row['region_abbr'] = $GLOBALS['pf_regions'][$row['region']];
			}
			
			if(!$row['href']) $row['href'] = "#";
			
			if($platform_safe) $platform_safe = htmlSC($platform_safe);
			$platform_safe_short = $GLOBALS['pf_shorthand'][strtolower($platform_safe)];
			if(!$platform_safe_short) $platform_safe_short = $platform_safe;
			$platform_safe_heading = strstr($row['platform'], "]]") ? ($row['platform']) : $platform_safe_short;
			
			$ret = 
				'<div id="shelf-item-id-'.$this->id.'" class="shelf-item game '.$or.' '.$i_class.'" data-platform="'.$platform_safe.'" data-img="'.$this->img->img_name.'" data-distribution="'.$row['distribution'].'" data-release="'.$rd.'" rel="'.$pf.'">';
					if(!$row['no_headings']){ $ret.= '
					<div class="shelf-headings">
						<dl>
							<dt>Title</dt><dd class="game-title"><strong>'.$row['title'].'</strong></dd>
							<dt>Platform</dt><dd>'.$platform_safe_heading.'</dd>
							'.($rd ? '<dt>Release date</dt><dd><span title="'.$row['region'].'" style="padding-right:20px; background:url(/bin/img/flags/'.$row['region_abbr'].'.png) no-repeat right center;">'.formatDate($rd, 6).'</span></dd>' : '').'
						</dl>
					</div>'; } $ret.= '
					<div class="shelf-img'.(!$this->tn->src ? ' noimg' : '').'" style="">
						<a href="'.$row['href'].'" title="'.$titleSC.($platform_safe_short ? ' &middot; '.$platform_safe_short : '').($row['region'] ? ' &middot; '.$row['region'] : '').($rd ? ' &middot; '.substr($rd, 0, 4) : '').'" class="'.(substr($row['href'], 0, 7) == "/image/" ? "imgupl" : "").'">'.
							($this->tn->src ? '<span class="tn" style="'.($or=="icon" ? 'background-image:url(\''.$this->tn->src.'\');' : '').'"><img src="'.$this->tn->src.'" alt="'.$titleSC.' box art for '.$platform_safe_short.'" border="0" style="'.$img_style.'"/></span>' : '<span class="tn noimg"><b>'.$row['title'].'</b></span>').
							'<span class="overlay"></span>'.
						'</a>'.
						($this->op ? '<span class="op '.$this->op.'" title="'.ucwords($this->op).'"></span>' : '').'
					</div>
				</div>';
			
		} elseif($this->type=="album"){
			
			if(!$this->no_img && file_exists($_SERVER['DOCUMENT_ROOT'].'/music/media/cover/standard/'.$row['albumid'].'.png')) $this->setImg('/music/media/cover/standard/'.$row['albumid'].'.png');
			
			if(!$row['href']) $row['href'] = "/music/?id=".$row['albumid'];
			
			$ret = 
				'<div id="shelf-item-id-'.$this->id.'" class="shelf-item album jewelcase positioned">
					<div class="shelf-headings">
						<dl>
							<dt>Title</dt><dd><strong>'.$row['title'].'</strong></dd>
							'.($row['description'] ? '<dd>'.($row['description']).'</dd>' : '').'
						</dl>
					</div>
					<div class="shelf-img'.(!$this->tn->src ? ' noimg' : '').'">
						<a href="'.$row['href'].'" title="'.$titleSC.'" class="'.(substr($row['href'], 0, 7) == "/image/" ? "imgupl" : "").'">'.($this->tn->src ? '<span class="tn"><img src="'.$this->tn->src.'" alt="'.$titleSC.'" border="0" style="margin-top:-'.ceil($this->tn->height / 2).'px"/></span>' : '<span class="tn noimg"><b>'.$row['title'].'</b></span>').'<span class="overlay"></span></a>
						'.($this->op ? '<span class="op '.$this->op.'" title="'.ucwords($this->op).'"></span>' : '').'
					</div>
				</div>';
			
		} else {
			
			$ret = 
				'<div id="shelf-item-id-'.$this->id.'" class="shelf-item '.$this->type.'" data-img="'.$this->img->img_name.'">
					<div class="shelf-headings">
						<dl>
							<dt>Title</dt><dd><strong>'.$row['title'].'</strong></dd>
							'.($row['description'] ? '<dd>'.($row['description']).'</dd>' : '').'
						</dl>
					</div>
					<div class="shelf-img'.(!$this->tn->src ? ' noimg' : '').'" style="'.($this->tn->height ? 'bottom:50%; margin-bottom:-'.ceil(($this->tn->height/2)).'px;' : '').'">
						<a href="'.$row['href'].'" title="'.$titleSC.'" class="'.(substr($row['href'], 0, 7) == "/image/" ? "imgupl" : "").'">'.($this->tn->src ? '<span class="tn"><img src="'.$this->tn->src.'" alt="'.$titleSC.'" border="0"/></span>' : '<span class="tn noimg"><b>'.$row['title'].'</b></span>').'<span class="overlay"></span></a>
						'.($this->op ? '<span class="op '.$this->op.'" title="'.ucwords($this->op).'"></span>' : '').'
					</div>
				</div>';
				
		}
		
		return $ret;
		
	}*/
}
?>
