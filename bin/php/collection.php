<?

require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.shelf.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.ajax.php";
require_once $_SERVER['DOCUMENT_ROOT']."/pages/class.pages.php";

if($_POST['collection_entry_input']){
	
	$a = new ajax();
	
	if(!$usrid) $a->kill('No user session registered. Please <a href="/login.php">log in</a>.');
	
	parse_str($_POST['collection_entry_input'], $in);
	
	if(!in_array($in['ownership'], array("own","own-digital","want","play"))) $a->kill("There was an input error in the [ownership] field");
	
	if(preg_match("/[^0-9]/", $in['completion'])) $a->kill("There was an input error in the [completion] field ($in[completion])");
	
	/*if($in['img_name']){
		$q = "SELECT platform FROM games_publications WHERE img_name = '".mysql_real_escape_string($in['img_name'])."' LIMIT 1";
		$row_img = mysql_fetch_assoc(mysql_query($q));
		$in['img_orientation'] = $row_img['platform'] != $in['platform'] ? $row_img['platform'] : '';
	}*/
	if($in['img_orientation'] == $in['platform']) $in['img_orientation'] = '';
	
	if($in['ownership'] != "own" && $in['ownership'] != "own-digital"){
		$in['purchase_date'] = "";
		$in['purchase_price'] = "";
		$in['purchase_currency'] = "";
	}
	if($in['ownership'] != "own"){
		$in['product_id'] = "";
		$in['condition'] = "";
		$in['incl_media'] = "";
		$in['incl_box'] = "";
		$in['incl_manual'] = "";
		$in['incl_inserts'] = "";
	}
	
	$play_start = '';
	if($in['play_start'] == "0000-00-00") $in['play_start'] = "";
	if($in['play_start']){
		if(($play_start = strtotime($in['play_start'])) === false){
			$a->error("Couldn't convert your Play Start time [".$in['play_start']."] into a real date.");
			$play_start = '';
		} else $play_start = date('Y-m-d', $play_start);
	}
	
	$purchase_date = '';
	if($in['purchase_date'] == "0000-00-00") $in['purchase_date'] = "";
	if($in['purchase_date']){
		if(($purchase_date = strtotime($in['purchase_date'])) === false){
			$a->error("Couldn't convert your Purchase Date [".$in['purchase_date']."] into a real date.");
			$purchase_date = '';
		} else $purchase_date = date('Y-m-d', $purchase_date);
	}
	
	$user = new user($usrid);
	$user->getPreferences();
	
	$sort = 0;
	if(!$user->preferences['collection_prepend']){
		$q = "SELECT `sort` FROM collection WHERE usrid='$usrid' ORDER BY `sort` DESC LIMIT 1";
		if($row = mysql_fetch_assoc(mysql_query($q))) $sort = $row['sort'] + 1;
	}
	
	$q = "SELECT * FROM collection WHERE usrid='$usrid' AND title='".mysql_real_escape_string($in['title'])."' LIMIT 1";
	if(!mysql_num_rows(mysql_query($q))){
		$id = mysqlNextAutoIncrement("collection");
		$q = "INSERT INTO `collection` (`usrid`, `title`, `sort`) VALUES ('$usrid', '".mysql_real_escape_string($in['title'])."', '$sort')";
		if(!mysql_query($q)) $a->kill("There was a database error and this game couldn't be added to your collection :(" . ($usrrank > 8 ? " [$q] ".mysql_error() : ""));
		$in['id'] = $id;
		$a->ret['added'] = 1;
	}
	$q = "UPDATE collection SET 
		platform = '".mysql_real_escape_string($in['platform'])."',
		img_name = '".mysql_real_escape_string($in['img_name'])."',
		img_orientation = '".mysql_real_escape_string($in['img_orientation'])."',
		notes = '".mysql_real_escape_string($in['notes'])."',
		ownership = '".mysql_real_escape_string($in['ownership'])."',
		completion = '".mysql_real_escape_string($in['completion'])."',
		purchase_date = ".($purchase_date ? "'$purchase_date'" : "NULL").",
		purchase_price = '".mysql_real_escape_string($in['purchase_price'])."',
		purchase_currency = '".mysql_real_escape_string($in['purchase_currency'])."',
		playing = '".mysql_real_escape_string($in[playing])."',
		play_start = ".($play_start ? "'$play_start'" : "NULL").",
		`network` = '".mysql_real_escape_string($in['network'])."',
		product_id = '".mysql_real_escape_string($in['product_id'])."',
		`condition` = '".mysql_real_escape_string($in['condition'])."',
		incl_media = '$in[incl_media]',
		incl_box = '$in[incl_box]',
		incl_manual = '$in[incl_manual]',
		incl_inserts = '$in[incl_inserts]'
		WHERE usrid='$usrid' AND title='".mysql_real_escape_string($in['title'])."' LIMIT 1";
	if(mysql_query($q)){
		$a->ret['success'] = 1;
		$a->ret['shelf_position'] = $sort;
		if($_POST['return_new_shelf_item']){
			$shelf = new shelfItem();
			$shelf->type = "game";
			$shelf->img = $in['img_name'];
			$in['href'] = pageURL($in['title'], "game");
			$a->ret['formatted'] = $shelf->outputItem($in);
			$a->ret['shelf_id'] = $shelf->id;
		}
	} else {
		$a->error("There was a database error and this game couldn't be added to your collection :(" . ($usrrank > 8 ? " [$q] ".mysql_error() : ""));
	}
	
	exit;
	
}

if($_POST['remove']){
	
	$a = new ajax();
	
	if(!$usrid) $a->kill('No user session registered. Please <a href="/login.php">log in</a>.');
	
	$q = "DELETE FROM collection WHERE `id` = '".mysql_real_escape_string($_POST['remove'])."' AND usrid = '$usrid' LIMIT 1";
	if(mysql_query($q)) $a->ret['success'] = 1;
	else $a->kill("Couldn't remove item..." . ($usrrank >= 7 ? " [$q] ".mysql_error() : ""));
	
	exit;
	
}

if($sort = $_POST['sort']){
	
	$a = new ajax();
	
	if(!$usrid) $a->kill('No user session registered. Please <a href="/login.php">log in</a>.');
	
	$sort = str_replace("shelf-item-id-", "", $sort);
	$i = 0;
	foreach(explode(",", $sort) as $id){
		$id = trim($id);
		$q = "UPDATE collection SET `sort` = '".++$i."' WHERE id = '".mysql_real_escape_string($id)."' AND usrid = '$usrid' LIMIT 1";
		if(!mysql_query($q)) $a->ret['not_sorted'][] = $id;
	}
		
	if($a->ret['not_sorted']) $num_not_sorted = count($a->ret['not_sorted']);
	if($num_not_sorted) $a->error($num_not_sorted." items couldn't be sorted because of a database error."); 
	else $a->ret['success'] = 1;
	
	exit;
	
}

class collection {
	
	public $in_collection;
	
	function form($title, $in=array()){
		
		global $usrid, $pf_regions, $pf_shorthand;
		
		include $_SERVER['DOCUMENT_ROOT']."/bin/php/currencies.include.php";
		
		if(!$usrid) return false;
		$user = new user($usrid);
		if($user->notfound) return false;
		
		$q = "SELECT * FROM collection WHERE `title` = '".mysql_real_escape_string($title)."' AND usrid = '$usrid' LIMIT 1";
		$this->in_collection = mysql_num_rows(mysql_query($q));
		if(!$in && $this->in_collection) $in = mysql_fetch_assoc(mysql_query($q));
		
		$ns = substr($title, 0, 8);
		if(strtolower($ns) == "albumid:"){
			$is_album = true;
			$albumid = substr($title, 8);
			require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.albums.php";
			try { $album = new album($albumid); }
			catch (exception $e){ return false; }
		} else {
			$pg = new pg($title);
			try { $pg->loadData(); }
			catch(Exception $e){ }
		}
		
		$pf_regions_x = array_flip($pf_regions);
		
		$num_shelfitems = 0;
		$o_shelf = '';
		$shelf_ids = array();
		$shelf_rows = array();
		$num_imgs = 0;
		
		if($is_album){
			
			$shelf = new shelfItem();
			$shelf->type = "album";
			$shelf->img = $album->coverimg;
			
			$o_shelf = $shelf->outputItem($album->row);
			
			/*if($in['img_name'] && $in['img_name'] == $img_name) $shelf_focus_index = $num_shelfitems;
			
			$o_title = '';
			if($o_title != $row['release_title']){
				$o_title = str_replace($row['title'], '', $row['release_title']);
				if(substr($o_title, 0, 2) == ": ") $o_title = substr($o_title, 2);
			}
			
			$js = "$(this).addClass('on').siblings().removeClass('on'); collection.selectBox($('#shelf-item-id-".$row['id']."')); shelf.traverse($('#pgop-form-collection .shelf'), {position:".$num_shelfitems."}, '', 300);";
			$o_title = ($o_title ? $o_title." &middot; " : "").$row['platform']." &middot; ".$pf_regions_x[$row['region']]." &middot; ".substr($row['release_date'], 0, 4);
			$shelf_select.= '<a onclick="'.$js.'" title="'.htmlsc($o_title).'">'.($img_name ? '<img src="/image.php?img_name='.$img_name.'&showimage=true&size=tn"/>' : '').'</a>';*/
			
			$num_shelfitems++;
			$shelf_ids[] = $albumid;
			
		} elseif($pg->pgid){
			$query = "SELECT * FROM games_publications WHERE pgid='$pg->pgid' ORDER BY release_date";
			$res   = mysql_query($query);
			while($row = mysql_fetch_assoc($res)){
				
				if(strtolower($row['platform']) == "ios"){
					//show an icon
					if($row['img_name_logo']){
						$row['img_name'] = $row['img_name_logo'];
						$shelf_rows[] = $row;
					}
					//show both an iphone and ipad with title screen
					if($row['img_name_title_screen']){
						$row['primary'] = '0';
						$row['img_name'] = $row['img_name_title_screen'];
						$row['platform'] = "iPhone";
						$row['id'].= "-iphone";
						$shelf_rows[] = $row;
						$row['platform'] = "iPad";
						$row['id'].= "-ipad";
						$shelf_rows[] = $row;
					}
					//ignore any box art for ios
					continue;
				}
				
				$shelf_rows[] = $row;
				
				if($row['img_name'] && $row['distribution'] != "digital"){
					$num_imgs++;
					continue;
				}
				
				if($row['img_name_title_screen']){
					$row['img_name'] = $row['img_name_title_screen'];
					$row['id'].= "-1";
					$row['primary'] = '0';
					$shelf_rows[] = $row;
				}
				if($row['img_name_logo']){
					$row['img_name'] = $row['img_name_logo'];
					$row['id'].= "-2";
					$row['primary'] = '0';
					$shelf_rows[] = $row;
				}
				
				if($row['img_name']) $num_imgs++;
			}
			
			foreach($shelf_rows as $row){
				
				if(!$row['img_name'] && $num_imgs) continue;
				
				if($row['primary']){
					$shelf_focus_indexes['primary'] = $num_shelfitems;
					$rows['primary'] = $row;
				}
				
				if($row['region'] == $user->region && !$shelf_focus_indexes[$user->region] && $rows['primary']['region'] != $user->region){
					$shelf_focus_indexes[$user->region] = $num_shelfitems;
					$rows[$user->region] = $row;
				}
				
				if($row['img_name']) $img_name = $row['img_name'];
				else $img_name = '';
				
				$shelf = new shelfItem();
				$shelf->type = "game";
				$shelf->img = $img_name;
				
				$o_shelf.= $shelf->outputItem($row);
				
				if($in['img_name'] && $in['img_name'] == $img_name) $shelf_focus_index = $num_shelfitems;
				
				$o_title = '';
				if($o_title != $row['release_title']){
					$o_title = str_replace($row['title'], '', $row['release_title']);
					if(substr($o_title, 0, 2) == ": ") $o_title = substr($o_title, 2);
				}
				
				$js = "$(this).addClass('on').siblings().removeClass('on'); collection.selectBox($('#shelf-item-id-".$row['id']."')); shelf.traverse($('#pgop-form-collection .shelf'), {position:".$num_shelfitems."}, '', 300);";
				$o_title = ($o_title ? $o_title." &middot; " : "").$row['platform']." &middot; ".$pf_regions_x[$row['region']]." &middot; ".substr($row['release_date'], 0, 4);
				$shelf_select.= '<a onclick="'.$js.'" title="'.htmlsc($o_title).'">'.($img_name ? '<img src="/image.php?img_name='.$img_name.'&showimage=true&size=tn"/>' : '').'</a>';
				
				$num_shelfitems++;
				$shelf_ids[] = $row['id'];
				
			}
		}
		
		if(!$num_shelfitems){
			$row = $rows['primary'];
			$shelf = new shelfItem();
			$shelf->type = "game";
			
			$row['href'] = "javascript:collection.selectBox($('#shelf-item-id-".$row['id']."'))";
			$o_shelf.= $shelf->outputItem($row);
			$num_shelfitems = 1;
		}
		
		//set default values
		if(!$this->in_collection){
			if($is_album){
			} else {
				$row_key = $rows[$user->region] ? $user->region : "primary";
				$in['completion'] = 0;
				$in['ownership'] = $rows[$row_key]['distribution'] == "digital" ? "own-digital" : "own";
				$in['img_name'] = $rows[$row_key]['img_name'];
				$in['platform'] = $rows[$row_key]['platform'];
				$shelf_focus_index = $shelf_focus_indexes[$row_key];
			}
		}
		
		if(!$shelf_focus_index) $shelf_focus_index = 0;
		
		/*$shelf = new shelfItem();
		$o_shelf.= $shelf->outputItem(array("title" => $title));
		$num_shelfitems++;*/
		
		while($num_shelfitems < 3){
			$o_shelf.= '<div class="shelf-item"></div>';
			$num_shelfitems++;
		}
		
		$fields_ownership = array(
			"own" => "own",
			"own-digital" => "own a digital copy of",
			"want" => "want",
		);
		if(!is_album) $fields_ownership["play"] = "have played";
		$fields_ownership_formatted = array(
			"play" => "have played",
			"own-digital" => "own a digital copy",
		);
		foreach($fields_ownership as $value => $name) $field_ownership.= '<li class="fauxselect-option '.($in['ownership'] == $value ? 'selected' : '').'" data-value="'.$value.'">'.$name.'</li>';
		
		if(!$is_album){
			foreach($pg->row['index_data']['platforms'] as $pf){
				if(!$pf_short = $pf_shorthand[strtolower($pf)]) $pf_short = $pf;
				$field_platform.= '<li class="fauxselect-option '.($in['platform'] == $pf ? 'selected' : '').'" data-value="'.htmlsc($pf).'">'.$pf_short.'</li>';
			}
			$field_platform.= '<li class="fauxselect-option" data-value="">&mdash;</li>';
			foreach(getPlatforms() as $pf){
				if(in_array($pf, $pg->row['index_data']['platforms'])) continue;
				if(!$pf_short = $pf_shorthand[strtolower($pf)]) $pf_short = $pf;
				$field_platform.= '<li class="fauxselect-option '.($in['platform'] == $pf ? 'selected' : '').'" data-value="'.htmlsc($pf).'">'.$pf.'</li>';
			}
			$field_platform.= '<li class="fauxselect-option" data-value="">&mdash;</li><li class="fauxselect-option" onclick="$(\'#collection-field-platform-output\').hide(); $(\'#collection-field-platform-input\').val(\'\').show().focus();">other...</li>';
			if(!$o_platform = $pf_shorthand[strtolower($in['platform'])]) $o_platform = $in['platform'];
		}
		
		$shelf_width = ($num_shelfitems + 1) * 170;
		$shelf_style = "";
		
		if(!$in['purchase_currency']){
			foreach($currencies as $currency => $c){
				if($c['region'] == $user->region) $in['purchase_currency'] = $currency;
			}
		}
		foreach($currencies as $currency => $c){
			$field_currency.= '<li title="'.$c['name'].'" class="fauxselect-option'.($in['purchase_currency'] == $currency ? ' selected' : '').'" data-value="'.$currency.'" onclick="$(\'#collection-field-purchasecurrency-input\').next().html($(this).html())">'.$c['html'].'</li>';
		}
		
		if(!$is_album){
			$options_network = '';
			if($pg->data->online && $pg->data->online->count()){
				foreach($pg->data->online->children() as $network){
					$network = str_replace("[[Category:", "", $network);
					$network = str_replace("]]", "", $network);
					$options_network.= '<li class="fauxselect-option" data-value="'.htmlsc($network).'">'.$network.'</li>';
				}
			}
		}
		
		if($is_album){
			$heading = ($this->in_collection ? 'Edit' : 'Add') . ' your copy of <b><a href="'.$album->url.'" target="_blank">'.$album->full_title.'</a></b>';
		} else {
			$heading = $this->in_collection ? 'Edit your copy of <b><a href="'.$pg->url.'" target="_blank">'.$title.'</a></b>' : 'Which version of <b><a href="'.$pg->url.'" target="_blank">'.$title.'</a></b> would you like to add?';
		}
		
		$tag = $title;
		
		$ret = '
		<h6>'.$heading.'</h6>
		<div class="shelf gameshelf horizontal"><div class="shelf-container" style="position:absolute; width:'.$shelf_width.'px;">'.$o_shelf.'</div></div>
		<div class="shelfselect">'.$shelf_select.'</div>
		
		<form name="collection-form" class="collection-form" onsubmit="collection.update($(this)); return false;">
			<input type="hidden" name="title" value="'.htmlsc($tag).'"/>
			<input type="hidden" name="id" value="'.$in['id'].'" id="collection-id"/>
			<div class="form-main">
				<div class="field" id="collection-field-img">
					<input type="text" name="img_name" value="'.$in['img_name'].'"/>
					<input type="text" name="img_orientation" value=""/>
				</div>
				<div class="field" id="collection-field-ownership-platform">
					<div>I </div>
					<div id="collection-field-ownership" class="foutput fauxselect" data-field="ownership" onclick="collection.changeField(\'ownership\', fauxselect(event));">
						<b class="fauxselect-output arrow-down" id="collection-field-ownership-output">'.$fields_ownership[$in['ownership']].'</b>
						<input type="hidden" name="ownership" value="'.$in['ownership'].'" id="collection-field-ownership-input" class="fauxselect-input"/>
						<ol class="fauxselect-options">'.$field_ownership.'</ol>
					</div>
					<div> it'.(!$is_album ? ' for </div>
					<div id="collection-field-platform" class="foutput fauxselect" style="" data-field="platform" onclick="collection.changeField(\'platform\', fauxselect(event));">
						<b class="fauxselect-output arrow-down" id="collection-field-platform-output">'.$o_platform.'</b>
						<input type="text" name="platform" value="'.$in['platform'].'" id="collection-field-platform-input" class="fauxselect-output" style="display:none; width:150px;" placeholer="Platform"/>
						<ol class="fauxselect-options" style="width:158px; max-height:170px;">'.$field_platform.'</ol>
					</div>' : '').'
				</div>'.(!$is_album ? '
				<div class="field" id="collection-field-completion">
					<input type="hidden" name="completion" value="'.$in['completion'].'" id="collection-field-completion-input"/>
					<div class="slider">
						<span class="mguidance mguidance-min"></span>
						<span class="mguidance mguidance-max"></span>
						<div class="steps">
							<div style="width:1%;"></div>
							<div style="width:33%; left:1%;"></div>
							<div style="width:33%; left:34%;"></div>
							<div style="width:32%; left:67%;"></div>
							<div style="width:1%; left:99%;"></div>
						</div>
					</div>
				</div>
				<div class="field" id="collection-field-playingdetails" style="'.(!$in['completion'] ? "display:none" : "").'">
					<div id="collection-field-playingsince">
						<span>Started playing</span>
						<span id="field-ps" class="foutput fauxselect fsx">
							<input type="text" name="play_start" value="'.($in['play_start'] ? formatDate($in['play_start'], 6) : '').'" placeholder="a while ago" class="fauxselect-input" style="width:80px;"/>
							<ol class="fauxselect-options" style="width:78px">
								<li class="fauxselect-option releaseday" data-value="**I WILL BE SET BY collection.selectBox()**">release day</li>
								<li class="fauxselect-option" data-value="today">today</li>
								<li class="fauxselect-option" data-value="yesterday">yesterday</li>
								<li class="fauxselect-option" data-value="last week">last week</li>
								<li class="fauxselect-option" data-value="last month">last month</li>
								<li class="fauxselect-option" data-value="'.date("j F").'">'.date("j F").'</li>
								<li class="fauxselect-option" data-value="1/31/2001">1/31/2001</li>
								<li class="fauxselect-option" data-value="1999">1999</li>
							</ol>
						</span>
					</div>' : '').'
					<label id="collection-field-playing">
						<input type="checkbox" name="playing" value="1" '.($in['playing'] ? 'checked' : '').' style="float:left; margin:2px 0 0;"/>
						<div style="margin-left:20px">Currently playing</div>
					</label>
					'.($options_network ? '<div id="collection-field-network">
						<span class=" fauxselect fsr fstyle" data-field="network">
							<input type="hidden" name="network" value="'.$in['network'].'" class="fauxselect-input"/>
							<b class="fauxselect-output arrow-down">'.($in['network'] ? $in['network'] : "Network play").'</b>
							<ol class="fauxselect-options" style="min-width:95px">'.$options_network.'<li class="fauxselect-option" data-value="">none</li></ol>
						</span>
					</div>' : '').'
				</div>
				<div class="field" id="collection-field-notes">
					<textarea name="notes" placeholder="Notes" rows="2">'.$in['notes'].'</textarea>
				</div>
				<details>
					<summary onclick="$(this).next().toggle()">
						<b class="arrow-toggle" onclick="$(this).toggleClass(\'arrow-toggle-on\')">Collection Details</b>
					</summary>
					<div class="container" style="display:none">
						<div class="field" id="collection-field-purchase">
							<b>Purchased</b> 
							<span id="field-purdat" class="foutput fauxselect fsx">
								<input type="text" name="purchase_date" value="'.($in['purchase_date'] ? formatDate($in['purchase_date'], 6) : '').'" placeholder="date" class="fauxselect-input" style="width:80px"/>
								<ol class="fauxselect-options">
									<li class="fauxselect-option releaseday" data-value="**I WILL BE SET BY collection.selectBox()**">release day</li>
									<li class="fauxselect-option" data-value="today">today</li>
									<li class="fauxselect-option" data-value="yesterday">yesterday</li>
									<li class="fauxselect-option" data-value="last week">last week</li>
									<li class="fauxselect-option" data-value="last month">last month</li>
									<li class="fauxselect-option" data-value="'.date("j F").'">'.date("j F").'</li>
									<li class="fauxselect-option" data-value="1/31/2001">1/31/2001</li>
									<li class="fauxselect-option" data-value="1999">1999</li>
								</ol>
							</span>&nbsp;&nbsp;
							<span class="fauxselect foutput fsr" id="field-ppcx"><input type="hidden" name="purchase_currency" value="'.$in['purchase_currency'].'" class="fauxselect-input" id="collection-field-purchasecurrency-input"/><b>'.$currencies[$in['purchase_currency']]['html'].'</b><ol class="fauxselect-options" onclick="$(\'#collection-field-purchase :input[name=purchase_price]\').focus()">'.$field_currency.'</ol></span>&nbsp;
							<span class="foutput" style="margin-left:-5px">
								<input type="text" name="purchase_price" value="'.ltrim($in['purchase_price'], '0').'" min="" max="100000" maxlength="6" placeholder="price" style="width:50px"/>
							</span>
						</div>
						<div class="field" id="collection-field-productid">
							<b>Product ID</b> 
							<span class="foutput"><input type="text" name="product_id" value="'.htmlsc($in['product_id']).'" style="width:140px; font-family:monospace;"/></span> 
							&nbsp;<span class="ttip" style="color:#888; position:relative;">ISBN, UPC, etc.<span style="width:6px; height:11px; position:absolute; z-index:5; top:4px; left:-6px; background:url(/bin/img/bubble_sprites.png) no-repeat 0 -4px;"></span></span>
						</div>
						<div class="field" id="collection-field-condition">
							<b>Condition</b> 
							<span class="foutput fauxselect fsr" id="field-con">
								<input type="hidden" name="condition" value="'.$in['condition'].'" class="fauxselect-input"/>
								<b class="fauxselect-output arrow-down">'.$in['condition'].'</b>
								<ol class="fauxselect-options">
									<li class="fauxselect-option" data-value="mint/sealed">mint/sealed</option>
									<li class="fauxselect-option" data-value="near mint">near mint</option>
									<li class="fauxselect-option" data-value="fine">fine</option>
									<li class="fauxselect-option" data-value="very good">very good</option>
									<li class="fauxselect-option" data-value="good">good</option>
									<li class="fauxselect-option" data-value="acceptable">acceptable</option>
									<li class="fauxselect-option" data-value="poor">poor</option>
									<li class="fauxselect-option" data-value="">unspecified</option>
								</ol>
							</span>
							<ul class="chboxlist">
								<li style="float:left"><label class="tooltip" title="cartridge, game pak, CD, etc."><input type="checkbox" name="incl_media" value="1" '.($in['incl_media'] ? 'checked' : '').'/> Original media</label></li> 
								<li style="margin-left:50%"><label><input type="checkbox" name="incl_box" value="1"'.($in['incl_box'] ? 'checked' : '').'/> Box/packaging</label></li> 
								<li style="float:left"><label><input type="checkbox" name="incl_manual" value="1"'.($in['incl_manual'] ? 'checked' : '').'/> Instruction manual</label></li> 
								<li style="margin-left:50%"><label><input type="checkbox" name="incl_inserts" value="1"'.($in['incl_inserts'] ? 'checked' : '').'/> Maps/inserts</label></li>
							</ul>
						</div>
					</div>
				</details>
			</div>
			<button type="submit">'.($this->in_collection ? 'Submit changes' : 'Add to my games').'</button> &nbsp; 
			<a id="collection-rm" class="red" style="'.(!$this->in_collection ? 'display:none' : '').'">Remove</a>
		</form>
		<span class="loading"><span></span></span>
		<div style="clear:both"></div>
		<script type="text/javascript">
			collection.selectBox($("#pgop-form-collection .shelf .shelf-item[data-img=\''.$in['img_name'].'\']").eq(0), false);
			shelf.traverse($("#pgop-form-collection .shelf"), {position:'.$shelf_focus_index.'}, "", 0);
			$(".collection-form .fsx").click(function(event){
				fauxselect(event);
				$(this).children(".fauxselect-output").hide().next().show().focus();
			});
			$(".collection-form .fsr").click(fauxselect);
			$(".collection-form .foutput, .collection-form .field").click(function(event){
				$(".collection-form .foutput, .collection-form :input").not(document.getElementById($(event.currentTarget).attr("id"))).removeClass("on");
				event.stopPropagation();
			});
			$("#collection-field-completion .slider").slider({
				range: "min",
				value: '.$in['completion'].',
				min: 0,
				max: 100,
				slide: function(event, ui){ 
					collection.changeField(\'completion\', ui.value);
				}
			}).find(".ui-slider-handle").wrap(\'<div class="ui-handle-helper-parent" style="margin:0 11px; position:relative;"></div>\').parent().append(\'<output class="ttip"></output><span class="pt"></span>\');
			collection.changeField(\'completion\', '.$in['completion'].');
			collection.changeField(\'ownership\', \''.$in['ownership'].'\');
			$("#collection-field-playing").on("click", "input", function(){
				if($(this).is(":checked") && $("#collection-field-playingsince input").val() == ""){
					$("#collection-field-playingsince .fauxselect-option[data-value=\'today\']").trigger("click");
				}
			});
		</script>';
		
		return $ret;
		
	}
	
	function add($subj){
		
		// var $subj arr what to add to the collection 
		
		global $usrid;
		
	}
}
?>