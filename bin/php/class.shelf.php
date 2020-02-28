<?

class shelfItem {
	
	var $type;
	var $tn;
	var $id;
	var $op; // ['love', 'hate']
	//var $img;
	var $no_img; // set true to display generic cover img
	
	function __set($name, $val){
		if($name == "img") $this->setImg($val);
	}
	
	function setImg($file=''){
		
		if(!$file){
			$this->tn->height = 120;
			return;
		}
		
		if(substr($file, 0, 1) == "/"){
			
			$this->tn->src = $file;
			if(substr($file, 0, 13) == "/pages/files/"){
				$pos = strrpos($file, "/");
				$this->tn->src = substr($file, 0, $pos) . "/" . ($this->type == "person" ? "profile_" : "md_") . substr($file, ($pos + 1), -3) . "png";
			}
      $this->tn->size = getimagesize($_SERVER['DOCUMENT_ROOT'].$this->tn->src);
      $this->tn->height = $this->tn->size[1] / ($this->tn->size[0] / 140);
      $this->tn->height = round($this->tn->height);
			
		} else {
			
			require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.img.php";

			$file = str_replace("img:", "", $file);
			
			$this->tn = '';
			try { $this->img = new img($file); }
			catch (Exception $e) {
				$this->no_img = true;
				return;
			}
			
			if($this->img->img_category_id == 15){//logo/icon
				$this->tn->src = $this->img->src['tn']; 
				$this->tn->height = 100;
			} elseif(in_array($this->img->img_category_id, array(1,11,12,13,14))){ //screenshots
				$this->tn->src = $this->img->src['sm'];
				$this->tn->height = 1000;
				$this->img->is_screenshot = true;
			} else {
				$this->tn->src = $this->img->src['box'];
				$this->tn->height = $this->img->img_height / ($this->img->img_width / 140);
			}
			$this->tn->height = round($this->tn->height);
			
		}
		
	}
	
	function outputItem($row){
		
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
			if($o_pf = $row['platform']){
				
				//strip links
				if(strstr($o_pf, "[[")){
					$pglinks = new pglinks();
					if($exlinks = $pglinks->extractFrom($o_pf)){
						$o_pf = $exlinks[0]['tag'];
					}
				}
				
				//create $pf string to evaluate and decide which image orientation to output
				$pf = $row['img_orientation'] ? $row['img_orientation'] : $o_pf;
        $pf = strtolower($pf);
				
				// if the image type is a screenshot, display a screen instead of box art
				if(!$row['orientation'] && $this->img->is_screenshot){
					//echo "eval $pf ::";
					switch($pf){
						case "nintendo 3ds": $row['orientation']="screen-n3ds"; break;
						case "nintendo ds": $row['orientation']="screen-ds"; break;
						case "android": $row['orientation']="screen-android"; break;
						case "iphone":
						case "ios": $row['orientation']="screen-iphone"; $o_pf="iPhone"; $row['platform']="[[iPhone]]"; break;
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
			
			if($o_pf) $o_pf = htmlSC($o_pf);
			$o_pf_short = $GLOBALS['pf_shorthand'][strtolower($o_pf)];
			if(!$o_pf_short) $o_pf_short = $o_pf;
			$o_pf_heading = strstr($row['platform'], "]]") ? ($row['platform']) : $o_pf_short;
			
			$ret = 
				'<div id="shelf-item-id-'.$this->id.'" class="shelf-item game '.$or.' '.$i_class.'" data-platform="'.$o_pf.'" data-img="'.$this->img->img_name.'" data-distribution="'.$row['distribution'].'" data-release="'.$rd.'" rel="'.$pf.'">';
					if(!$row['no_headings']){ $ret.= '
					<div class="shelf-headings">
						<dl>
							<dt>Title</dt><dd class="game-title"><strong>'.$row['title'].'</strong></dd>
							<dt>Platform</dt><dd>'.$o_pf_heading.'</dd>
							'.($rd ? '<dt>Release date</dt><dd><span title="'.$row['region'].'" style="padding-right:20px; background:url(/bin/img/flags/'.$row['region_abbr'].'.png) no-repeat right center;">'.formatDate($rd, 6).'</span></dd>' : '').'
						</dl>
					</div>'; } $ret.= '
					<div class="shelf-img'.(!$this->tn->src ? ' noimg' : '').'" style="">
						<a href="'.$row['href'].'" title="'.$titleSC.($o_pf_short ? ' &middot; '.$o_pf_short : '').($row['region'] ? ' &middot; '.$row['region'] : '').($rd ? ' &middot; '.substr($rd, 0, 4) : '').'" class="'.(substr($row['href'], 0, 7) == "/image/" ? "imgupl" : "").'">'.
							($this->tn->src ? '<span class="tn" style="'.($or=="icon" ? 'background-image:url(\''.$this->tn->src.'\');' : '').'"><img src="'.$this->tn->src.'" alt="'.$titleSC.' box art for '.$o_pf_short.'" border="0" style="'.$img_style.'"/></span>' : '<span class="tn noimg"><b>'.$row['title'].'</b></span>').
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
		
	}
}
?>
