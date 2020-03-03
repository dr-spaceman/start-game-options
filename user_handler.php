<?

/**
 * Output a user profile
 * This is accessed with GET /~{username}/{path} via .htaccess
 * And asyncronously GET /user_handler.php?load=true&section=stream&min=50&usrid=1 HTTP/1.1
 */

require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.posts.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.shelf.php";

include_once $_SERVER['DOCUMENT_ROOT']."/pages/include.pages.php";

$user_params = array("username" => trim($_GET['username']), "usrid" => trim($_GET['usrid']));
$user = new User($user_params);

$http_request = false;
$section = $_GET['section'] ? $_GET['section'] : "stream";
$path = array();

// $vars arr captures query string of properties, eg from a form to filter data to return
if($_GET['vars']) parse_str($_GET['vars'], $vars); // in the form of a query string

if(strstr($_GET['path'], "/")) {
	if(substr($_GET['path'], 0, 1) == "/") $_GET['path'] = substr($_GET['path'], 1);
	$path = explode("/", $_GET['path']);
}
else $path[0] = $_GET['path'];
if($path[0]) $section = $path[0];

if($_GET['load']){
	// Information requested via HTTP/AJAX; Prepare a JSON response!
	$http_request = true;
	$ret = array();
	require $_SERVER['DOCUMENT_ROOT']."/bin/php/class.ajax.php";
	$ajax = new ajax();
}

$page = new page();
$page->title = "Videogam.in Users / " . $user->username;
$page->superminimalist = true;
$page->css[] = "/user_profile.css";

if(!$user->id){
	
	if($http_request) $ajax->kill("User not on file.");
	
	$page->header();
	?>
	<h2>User Profiles</h2>
	User not on file.<br/><br/>
	<input type="text" name="fuser" id="fuser"/>
	<input type="button" value="Find User" onclick="document.location='/~'+document.getElementById('fuser').value;"/>
	<?
	$page->footer();
	exit;
}

$user->getDetails();
$user->getScore();

$page->javascripts[] = "/bin/script/jquery.isotope.js";
$page->javascripts[] = "/user_profile.js";
$page->first_section = array("id" => "user-profile", "class" => $section);
$page->fb = true;

switch($section){
	case "blog":
	case "posts":
		
		// SBLOGS //
		
		$page->title.= " / Sblog";
		$page->freestyle.= '#posts { margin-top:0; } #posts aside { background-image:none; }';
		
		$posts = new posts();
		if($section == "blog") $posts->query_params['category'] = "blog";
		$posts->query_params = $vars;
		$posts->query_params['user'] = $user->username;
		$posts->parseParams();
		$posts->buildQuery();
		
		$ret['formatted'] = '<div id="posts" class="posts">' . $posts->postsList() . '</div>';
		
		break;
	
	case "fan":
		
		// FAN SPACE //
		
		require_once $_SERVER['DOCUMENT_ROOT']."/pages/class.pages.php";
		
		$page->title.= " / Fan Space";
		
		$query = "SELECT * FROM pages_fan WHERE usrid = '$user->id' ORDER BY `title`";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		if(!$num_fan = mysqli_num_rows($res)){
			$ret['formatted'] = $user->username.' isn\'t a fan of anything.';
			break;
		}
		
		$rows = array();
		$types = array();
		$sentiments = array();
		
		while($row = mysqli_fetch_assoc($res)){
			$sentiments[$row['op']]++;
			$num_sentiments++;
			
			if(substr($row['title'], 0, 8) == "AlbumId:"){
				require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.albums.php";
				$albumid = substr($row['title'], 8);
				$album = new album($albumid);
				$row['title'] = $album->full_title;
				$row['link'] = $album->link;
				$row['type'] = "album";
				$types['Albums']++;
				$num_types++;
			} else {
				$pg = new pg($row['title']);
				if($pg->pgid){
					$row['type'] = $pg->subcategory ? $pg->subcategory : $pg->type;
					$row['type'] = str_replace("Game ", "", $row['type']);
					$types[($pg->subcategoryPlural ? $pg->subcategoryPlural : $pg->typePlural)]++;
					$num_types++;
				}
			}
			
			$rows[strtolower($row['title'])] = $row;
		}
		
		foreach(array("love", "hate") as $op){
			$pc[$op] = $sentiments[$op] / $num_sentiments * 100;
			$o[$op] = '<span class="op '.$op.'"></span>'.($pc[$op] ? '<a class="bar tooltip" style="width:'.$pc[$op].'%;" title="'.ucwords($op).' &times; '.$sentiments[$op].' ('.round($pc[$op]).'%)"></a>' : '');
		}
		
		$ret['formatted'] = '<div id="lhchart" class="chart"><div class="container">'.$o['love'].$o['hate'].'</div></div>'."\n\n";
		
		arsort($types);
		$colors = array("#0059B3", "#0079F2", "#409FFF", "#77BBFF", "#BFDFFF", "#DFEFFF", "#F0F8FF", "white");
		$i = 0;
		foreach($types as $type => $num){
			$pc = $num / $num_types * 100;
			$o_chart.= '<a class="bar tooltip" style="width:'.$pc.'%; background-color:'.$colors[$i++].';" title="'.ucwords($type).' &times; '.$num.' ('.round($pc).'%)"></a>';
		}
		
		$ret['formatted'].= '<div id="typechart" class="chart"><div class="container">'.$o_chart.'</div></div>';
		
		$ret['formatted'].= 
			'<div id="fantable">
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr><th class="title">Title</th><th>Type</th><th>Remarks</th><th>Added</th></tr>';
		
		ksort($rows);
		foreach($rows as $row){
			$ret['formatted'].= '<tr>
				<td class="title"><span class="op '.$row['op'].'">'.$row['op'].'</span>'.($row['link'] ? $row['link'] : '[['.$row['title'].']]').'</td>
				<td class="type">'.($row['type'] ? $row['type'] : '<div class="tooltip" title="This page hasn\'t been started yet. Please start this page so we can classify it.">?</div>').'</td>
				<td class="remarks">'.($row['remarks'] ? '<blockquote><span class="quotes"></span>'.$row['remarks'].'</blockquote>' : '').'</td>
				<td class="added">'.substr($row['datetime'], 0, 10).'</td>
			</tr>';
		}
		
		$ret['formatted'].= '</table></div>';
		
		$bb = new bbcode();
		$bb->text = $ret['formatted'];
		$ret['formatted'] = $bb->bb2html();
		
		break;
	
	case "collection":
	
		// COLLECTION //
		
		$page->title.= " / Game Collection";
		
		// First build the form for filtering collection display

		//get a list of platforms for the select box
		$query = "SELECT COUNT(*) AS `rows`, `platform` FROM collection WHERE usrid='$user->id' GROUP BY `platform` ORDER BY `platform`";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)) $options_platforms.= '<li class="fauxselect-option" data-value="'.htmlsc($row['platform']).'">'.$row['platform'].'</li>';
		
		//get a list of networks for the select box
		$networks = array();
		$networks_2 = array();
		if(!isset($vars['network'])) $vars['network'] = array();
		$query = "SELECT COUNT(*) AS `rows`, `network` FROM collection WHERE usrid='$user->id' GROUP BY `network` ORDER BY `network`";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)){
			if($row['network'] == "other"){ $networks_2[] = "other"; continue; }
			if($row['network']) $networks[] = $row['network'];
		}
		if(count($networks_2)) $networks = array_merge($networks, $networks_2);
		foreach($networks as $network) $options_networks.= '<li><label><input type="checkbox" name="network[]" value="'.htmlsc($network).'"'.(in_array($network, $vars['network']) ? ' checked' : '').'/> '.($network == "other" ? "Other network" : $network).'</label></li>';
		if(count($networks)) $options_networks.= '<li>&nbsp;</li>';
		
		//Next apply filters
		$where = '';
		if(!$vars){
			$vars = array();
			$vars['completion'] = '0';
			$vars['completion_arr'] = array();
			$has_vars = false;
			$has_filters = false;
		} else {
			foreach($vars as $key => $val){
				if($val) $has_vars = true;
				if($val && $key != "platform" && $key != "show") $has_filters = true;
				if(in_array($key, array("playing", "incl_media", "incl_box", "incl_manual", "incl_inserts")) && $val) $where.= " AND `$key` = '1'";
			}
			if($vars['query']){
				$vars['query'] = trim($vars['query']);
				$query = mysqli_real_escape_string($GLOBALS['db']['link'], $vars['query']);
				$where.= " AND (`title` LIKE '%$query%' OR `notes` LIKE '%$query%')";
			}
			if($vars['platform'] == "all") $vars['platform'] = "";
			if($vars['platform']) $where.= " AND `platform` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $vars['platform'])."'";
			if(count($vars['network'])){
				$where.= " AND (";
				foreach($vars['network'] as $network) $where.= "`network` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $network)."' OR ";
				$where = substr($where, 0, -4).")";
			}
			if($vars['show'] == "all") $vars['show'] = "";
			if($vars['show']){
				switch($vars['show']){
					case "own-all": $where.= " AND (`ownership` = 'own' OR `ownership` = 'own-digital')"; break;
					case "own": $where.= " AND `ownership` = 'own'"; break;
					case "own-digital": $where.= " AND `ownership` = 'own-digital'"; break;
					case "want": $where.= " AND `ownership` = 'want'"; break;
					case "play": $where.= " AND `ownership` = 'play'"; break;
				}
			}
			if(!$vars['completion']) $vars['completion'] = '0';
			if(!$vars['completion_arr']) $vars['completion_arr'] = array();
			else {
				$where.= " AND (";
				if(in_array("0", $vars['completion_arr'])) $where.= "`completion` = '0' OR ";
				if(in_array("25", $vars['completion_arr'])) $where.= "(`completion` >= 1 AND `completion` <= 33) OR ";
				if(in_array("50", $vars['completion_arr'])) $where.= "(`completion` >= 34 AND `completion` <= 66) OR ";
				if(in_array("75", $vars['completion_arr'])) $where.= "(`completion` >= 67 AND `completion` <= 99) OR ";
				if(in_array("100", $vars['completion_arr'])) $where.= "`completion` = '100' OR ";
				$where = substr($where, 0, -4).")";
			}
			if($vars['condition'] && in_array($vars['condition'], array("mint/sealed", "near mint", "fine", "very good", "good", "acceptable", "poor"))) $where.= " AND `condition` = '".$vars['condition']."'";
			else $vars['condition'] = '';
		}
		
		$shelf = new Shelf();

		$shelf_output_props = array(
			"output_nav" => false,
			"output_container" => false,
		);
		
		// Build the query
		
		/**Removed LIMIT (Is there any reason to do this...? Don't we want to display the whole collection?)
		$min = is_numeric($_GET['min']) ? $_GET['min'] : 0;
		$max = $_GET['max'] == "*" ? 99 : 50;*/

		$query = "SELECT * FROM collection WHERE usrid='$user->id' $where ORDER BY sort ASC, date_added DESC";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		if ($rows_num = mysqli_num_rows($res)) {
			while($row = mysqli_fetch_assoc($res)){
				$shelf_item_properties = $row;
				$shelf_item_properties['type'] = "game";
				$shelf_item_properties['img'] = new Img($row['img_name']);
				$shelf_item_properties['href'] = pageURL($row['title'], "game");
				$shelf->addItem($shelf_item_properties);
			}

			/*$query = "SELECT FOUND_ROWS() AS totalRows";
	    	$num_rows = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $query));
			$num_total_shelf_items = $num_rows[0];*/
			$num_total_shelf_items = $rows_num;
			
			/*if($http_request) {
				$ret['formatted'] = $shelf->output();
				break;
			}*/

		}

		$min = 0;
		$max = $num_total_shelf_items;
		
		if (!$shelf->num_items && !$has_vars && $user->id != $usrid) {
			$ret['formatted'] = $user->username." hasn't collected any games yet.";
			break;
		}
		
		$num_shelf_rows = ceil($shelf->num_items / 5);
		
		$show_options = array(
			"own-all" => "owned",
			"own" => "owned (retail)",
			"own-digital" => "owned (digital)",
			"want" => "wanted",
			"play" => "played"
		);
		foreach ($show_options as $option => $formatted) {
			$options_show.= '<li class="fauxselect-option" data-value="'.$option.'" onclick="$(\'#collection-nav-ownership-output\').html(\''.$formatted.'\')">'.$formatted.'</li>';
		}
		
		$ret['formatted'] = '
			<div id="user-collection">
				<div id="pgop-form-collection"><div class="container"></div><a class="ximg preventdefault" style="top:15px; right:15px;" onclick="$(\'#pgop-form-collection\').fadeOut()">close</a></div>
				<nav>
					<form onsubmit="return false">
						<div class="nav-top">
							<div class="controls">
								'.($usrid == $user->id ? '
								<div class="buttons">
									<button type="button" id="collection-nav-edit">Edit Collection</button> 
									<button type="button" id="collection-nav-add" class="blue">Add a Game</button>
								</div>
								<div class="console">
									<p>Edit mode initated<span>_</p>
									<p>Shift + click an item to send it to the top</p>
									<p>Ctrl + click to send it to the bottom</p>
									<p>Double click an item to edit</p>
								</div>
								<div id="collection-edit" class="dropaction" data-dropaction="delete">
									<div class="remove-img"></div>
									<p>Double click an item to edit</p>
									<p>Drag an item here to throw it out</p>
								</div>
								' : '').'
							</div>
							<span>Showing </span>
							<span class="fauxselect">
								<input type="text" name="show" value="'.htmlsc($vars['show']).'" class="fauxselect-input submitonchange" style="display:none"/>
								<b class="arrow-down" id="collection-nav-ownership-output">'.($vars['show'] ? $show_options[$vars['show']] : 'all').'</b>
								<ol class="fauxselect-options">
									<li class="fauxselect-option" data-value="all"><i>all</i></li>
									'.$options_show.'
								</ol>
							</span>
							<span> games for </span>
							<span class="fauxselect">
								<input type="text" name="platform" value="'.htmlsc($vars['platform']).'" class="fauxselect-input submitonchange" style="display:none"/>
								<b class="arrow-down fauxselect-output">'.($vars['platform'] ? $vars['platform'] : 'all platforms').'</b>
								<ol class="fauxselect-options"><li class="fauxselect-option" data-value="all"><i>all platforms</i></li>'.$options_platforms.'</ol>
							</span> &nbsp; 
							<span class="arrow-toggle '.($has_filters ? 'arrow-toggle-on' : '').' filter-link" onclick="$(this).toggleClass(\'arrow-toggle-on open\'); $(\'#user-collection .details\').addClass(\'set\').slideToggle();"><a>Filter</a></span>
						</div>
						
						<!-- filter details -->
						<div class="details'.($has_filters ? ' set open' : '').'">
							<div id="collection-nav-query"><input type="text" name="query" value="'.htmlsc($vars['query']).'" placeholder="Search term" title="Search game titles + notes"/></div>
							<div><label><input type="checkbox" name="playing" value="1"'.($vars['playing'] ? ' checked' : '').'/> Currently Playing</label></div>
							<div>
								<ul class="chboxlist">'.$options_networks.'</ul>
							</div>
							<div id="collection-nav-completion">
								<input type="hidden" name="completion" value="'.$vars['completion'].'" id="collection-nav-completion-input"/>
								<div class="slider">
									<span class="mguidance mguidance-min"></span>
									<span class="mguidance mguidance-max"></span>
								</div>
							</div>
							<div>
								<ul id="collection-nav-completionarr" class="chboxlist">
									<li><label><input type="checkbox" name="completion_arr[]" value="0"'.(in_array("0", $vars['completion_arr']) ? 'checked' : '').'/> <b>Haven\'t played</b></label></li>
									<li><label><input type="checkbox" name="completion_arr[]" value="25"'.(in_array("25", $vars['completion_arr']) ? 'checked' : '').'/> <b>Played some</b></label></li>
									<li><label><input type="checkbox" name="completion_arr[]" value="50"'.(in_array("50", $vars['completion_arr']) ? 'checked' : '').'/> <b>Played a lot</b></label></li>
									<li><label><input type="checkbox" name="completion_arr[]" value="75"'.(in_array("75", $vars['completion_arr']) ? 'checked' : '').'/> <b>Beat</b></label></li>
									<li><label><input type="checkbox" name="completion_arr[]" value="100"'.(in_array("100", $vars['completion_arr']) ? 'checked' : '').'/> <b>Mastered</b></label></li>
									<li>&nbsp;</li>
								</ul>
							</div>
							<div id="collection-nav-condition">
								<span class="fauxselect" id="field-con">
									<input type="hidden" name="condition" value="'.$vars['condition'].'" class="fauxselect-input"/>
									<b class="fauxselect-output arrow-down">'.($vars['condition'] ? $vars['condition'] : "Condition").'</b>
									<ol class="fauxselect-options">
										<li class="fauxselect-option" data-value="any"><i>any</i></option>
										<li class="fauxselect-option" data-value="mint/sealed">mint/sealed</option>
										<li class="fauxselect-option" data-value="near mint">near mint</option>
										<li class="fauxselect-option" data-value="fine">fine</option>
										<li class="fauxselect-option" data-value="very good">very good</option>
										<li class="fauxselect-option" data-value="good">good</option>
										<li class="fauxselect-option" data-value="acceptable">acceptable</option>
										<li class="fauxselect-option" data-value="poor">poor</option>
									</ol>
								</span>
							</div>
							<div>
								<ul class="chboxlist">
									<li><label class="tooltip" title="cartridge, game pak, CD, etc."><input type="checkbox" name="incl_media" value="1" '.($vars['incl_media'] ? 'checked' : '').'/> <b>Original media</b></label></li> 
									<li><label><input type="checkbox" name="incl_box" value="1"'.($vars['incl_box'] ? 'checked' : '').'/> <b>Box/packaging</b></label></li>
									<li><label><input type="checkbox" name="incl_manual" value="1"'.($vars['incl_manual'] ? 'checked' : '').'/> <b>Instruction manual</b></label></li>
									<li><label><input type="checkbox" name="incl_inserts" value="1"'.($vars['incl_inserts'] ? 'checked' : '').'/> <b>Maps/inserts</b></label></li>
								</ul>
							</div>
							<div>
								<button type="button" onclick="collectionNav()" style="font-weight:bold">Apply Filter</button> 
								<button type="button" onclick="$.address.value(\'/collection\'); $(\'.nav-top .filter-link\').click();">Reset</button>
							</div>
						</div><!--/.details-->
					</form>
				</nav>
				'.(!$shelf->num_items ? 'No games found' : '
				<div class="shelf gameshelf" style="position:static;"><div class="shelf-container"><div class="container" id="collection-shelf-items">' . $shelf->output($shelf_output_props) . ((($min + $shelf->num_items) < $num_total_shelf_items) ? '<div id="load-more" data-section="collection" data-min="'.($min + $max).'" data-usrid="'.$user->id.'"><a>Load more</a></div>' : '') . '<div id="collection-shelf-end" style="clear:both"></div></div></div></div>').'
			</div>
			<div id="collection-add" class="bodyoverlay">
				<a class="ximg rm" onclick="$(this).parent().fadeOut()">Close</a>
				<div class="container">
					<form onsubmit="collectionAdd.init(); return false;">
						<input type="text" name="title" autocomplete="off" id="collection-add-input"/>
						<div class="acloading"></div>
						<button type="submit">Search</button>
					</form>
					<div id="collection-add-results"></div>
				</div>
			</div>
			<script>
				var has_filters = '.($has_vars ? 'true' : 'false').';
				$("#collection-nav-completion .slider").slider({
					range: "min",
					value: '.$vars['completion'].',
					min: 0,
					max: 100,
					step: 25,
					slide: function(event, ui){ 
						collection.changeField(\'completion\', ui.value, \'nav\');
						$("#collection-nav-completionarr :input").each(function(){
							if($(this).attr("value") > ui.value) $(this).prop("checked", false);
							else $(this).prop("checked", true);
						});
					}
				}).find(".ui-slider-handle").wrap(\'<div class="ui-handle-helper-parent" style="margin:0 11px; position:relative;"></div>\').parent().append(\'<output class="ttip"></output><span class="pt"></span>\');
				collection.changeField(\'completion\', '.$vars['completion'].', \'nav\');
			</script>';
		
		break;
	
	case "badges":
		
		// BADGES //
		
		$page->title.= " / Badges";
		
		require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.badges.php";
		$badges = new badges();
		
		$bid = $path[1];
		
		$ret['formatted'] = ($bid ? $badges->show($bid, $user->id) . '<div class="hr" style="margin:10px 0;"></div>' : '') . $badges->collection($user->id, $user->username);
		
		break;
	
	case "edits":
	case "forumposts":
	case "reputation":
		
		$ret['formatted'] = "In development.";
		
		break;
		
	default:
		
		// STREAM //
		
		// Problem! Right now ALL stream items EVER are fetched and processed, then filtered by date for the current page.
		// It would be better to paginate by date
		
		$page->title.= " / Activity";
		
		require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.img.php";
		
		$genderref = array("male" => "his", "female" => "her", "asexual" => "its", "" => "their");
		$genderref2 = array("male" => "him", "female" => "her", "asexual" => "it", "" => "them");
		$genderref3 = array("male" => "he", "female" => "she", "asexual" => "it", "" => "they");
		
		if($user->rank >= User::VIP) $status = "vip";
		if($user->rank >= User::ADMIN) $status = "staff";
		
		$limit = 25;
		
		$stream = array();
		
		function streamItem($s){
			$GLOBALS['stream'][strtotime($s['datetime'])].= 
			'<div class="streamitem nohov '.$s['class'].'" title="'.formatDate($s['datetime']).'">'.
				($s['img'] ? '<div class="img">'.$s['img'].'</div>' : '').
				'<div class="description">'.$s['description'].'<span class="pt"></span></div>'.
				'<div class="overlay"></div>'.
			'</div>';
		}
		
		function a_repimg($imgfile=''){
			if($imgfile == '') return '';
		  	if(substr($imgfile, 0, 4) == "img:"){
				$img_name = substr($imgfile, 4);
				$img = new img($img_name);
				$imgfile = $img->src['url'];
				$repimgtn = $img->src['md'];
			} else {
				$pos = strrpos($imgfile, "/");
				$repimgtn = substr($imgfile, 0, $pos) . "/md_" . substr($imgfile, ($pos + 1), -3) . "png";
			}
			if(!$repimgtn || !file_exists($_SERVER['DOCUMENT_ROOT'].$repimgtn)) return '';
			return $repimgtn;
		}
		
		// Badges
		require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.badges.php";
		$_badges = new badges();
		foreach($_badges->badgesEarnedList($user->id) as $row){
			$s = array(
				"class" => "badge",
				"datetime" => $row['datetime'],
				"img" => '<a href="/~'.$user->username.'/badges/'.$row['bid'].'/'.formatNameURL($row['name']).'" class="badge user-profile-nav"><img src="/bin/img/badges/'.$row['bid'].'.png" width="140" height="140" border="0" title="'.htmlSC($row['name']).'"/></a>',
				"description" => '<big><b>'.$row['name'].'</b></big><blockquote>'.$row['description'].'</blockquote>'
			);
			streamItem($s);
		}
		
		// Love & Hate
		$query = "SELECT op, remarks, `datetime`, `type`, `title`, `description`, rep_image FROM pages_fan LEFT JOIN pages USING(`title`) WHERE usrid = '$user->id' ORDER BY op, datetime DESC";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)){
			if(substr($row['title'], 0, 8) == "AlbumId:"){
				require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.albums.php";
				$albumid = substr($row['title'], 8);
				$album = new album($albumid);
				$title_sc = strip_tags($album->full_title);
				$title_sc = htmlSC($title_sc);
				$img = '';
				if($album->coverimg){
					$imgattr = getimagesize($_SERVER['DOCUMENT_ROOT'].$album->coverimg);
					$img = '<a href="'.$album->url.'" title="'.$title_sc.'"><div class="container"><img src="'.$album->coverimg.'" alt="'.$title_sc.'" border="0" width="'.$imgattr[0].'" height="'.$imgattr[1].'" style="margin-top:-'.(ceil($imgattr[1] / 2) + 1).'px"/></div><span class="overlay"></span></a>';
				}
				$s = array(
					"class" => "fan album ".$row['op'],
					"datetime" => $row['datetime'],
					"img" => $img,
					"description" => '<span class="op-sm" title="I '.$row['op'].' this"></span><a href="'.$album->url.'">'.$album->full_title.'</a>' . ($row['remarks'] ? '<blockquote>'.$row['remarks'].'<span class="pt"></span></blockquote>' : '')
				);
				streamItem($s);
				continue;
			}
			$title_sc = htmlSC($row['title']);
			$repimgtn = a_repimg($row['rep_image']);
			$url = pageURL($row['title'], $row['type']);
			$s = array(
				"class" => "fan ".$row['op'],
				"datetime" => $row['datetime'],
				"img" => ($repimgtn ? '<a href="'.$url.'" title="'.$title_sc.'"><img src="'.$repimgtn.'" alt="'.$title_sc.'" border="0"/></a>' : ''),
				"description" => '<span class="op-sm" title="I '.$row['op'].' this"></span>[['.$row['title'].']]' . ($row['remarks'] ? '<blockquote>'.$row['remarks'].'<span class="pt"></span></blockquote>' : '')
			);
			streamItem($s);
		}
		
		//collection
		$query = "SELECT * FROM collection WHERE usrid='$user->id' ORDER BY date_added DESC";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		if (mysqli_num_rows($res)) {
			while ($row = mysqli_fetch_assoc($res)) {
				$shelfitem_properties = array(
					"type" => "game",
				);
				if($row['img_name']){
					$shelfitem_properties['img'] = new Img($row['img_name']);
				}
				$shelfitem = new shelfItem($shelfitem_properties);

				$row['no_headings'] = true;
				$row['href'] = pageURL($row['title'], "game");
				$shelf_height = $shelfitem->thumb_height + 43; // Why + 43 ................????????????
				$shelf_offset = -245 + $shelf_height + 10; // Not sure what's going on here....
				if($shelf_offset > 0){
					$shelf_height-= $shelf_offset;
					$shelf_offset = 0;
				}
				$s = array(
					"class" => "shelf",
					"datetime" => $row['date_added'],
					"img" => '<div class="shelf gameshelf horizontal" style="height:'.$shelf_height.'px;"><div class="shelf-container">'.$shelfitem->output().'</div></div>',
					"description" => '[['.$row['title'].']]<br/><span class="pf">'.$row['platform'].'</span>'
				);
				streamItem($s);
			}
		}
		
		
		//pages
		$query = "SELECT `title`, `type`, `subcategory`, `created`, rep_image FROM pages WHERE redirect_to='' and creator='$user->id' ORDER BY `created`";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)){
			$class = "patronsaint";
			$repimgtn = a_repimg($row['rep_image']);
			$url = pageURL($row['title'], $row['type']);
			$s = array(
				"class" => $class,
				"datetime" => $row['created'],
				"img" => ($repimgtn ? '<a href="'.$url.'"><img src="'.$repimgtn.'" alt="" border="0"/></a>' : ''),
				"description" => 'Creator and Patron Saint of the <b><a href="'.$url.'">'.$row['title'].'</a></b> page.'
			);
			streamItem($s);
		}
		
		// other stream stuff
		$query = "SELECT * FROM stream WHERE usrid = '$user->id' AND action_type != 'earn badge'";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)){
			$class = "";
			if(strstr($row['action'], "Patron Saint")){
				$class = "patronsaint";
				$row['action'] = str_replace("[[User:{$user->username}]] became the ", "", $row['action']);
				$row['action'] = '<big>'.ucfirst($row['action']).'</big>';
			}
			$s = array(
				"class" => $class,
				"datetime" => $row['datetime'],
				"img" => "",
				"description" => $row['action']
			);
			streamItem($s);
		}
		
		krsort($stream);
		
		$o_stream = '';
		$min = is_numeric($_GET['min']) ? $_GET['min'] : 0;
		$max = $min + $limit;
		$i = 0;
		foreach($stream as $row){
			if($i++ < $min) continue;
			$o_stream.= $row;
			if($i >= $max){
				$o_stream.= '<div id="load-more" data-section="stream" data-min="'.$max.'" data-usrid="'.$user->id.'"><a>Load more</a></div>';
				break;
			}
		}
		$bb = new bbcode();
		$o_stream = $bb->bb2html($o_stream);
		
		$unlen = strlen($user->username);
		if($unlen > 14) $h1class = "ultracondensed";
		elseif($unlen > 11) $h1class = "condensed";
		
		if($user->homepage){
			preg_match('@^(http|https|ftp)://([^/]*)/?.*@i', $user->homepage, $matches);
			$o = str_replace("www.", "", $matches[2]);
			if(strlen($o) > 14) $o = substr($o, 0, 43).'&hellip;';
			if(strlen($o) > 26) $o = substr($o, 0, 25).'<br/>'.substr($o, 25);
			$o_homepage = '<dt>Web</dt>'."\n".'<dd><a href="'.$user->homepage.'" target="_blank" style="white-space:nowrap">'.$o.'</a></dd>'."\n";
		}
		$bday = formatDate($user->dob, 9);
		
		if($min){
			$ret['formatted'] = $o_stream;
			break;
		}
		
		$ret['formatted'] = '
			
			<div id="userinfo">
				
				'.($status ? '<span class="userstatus '.$status.'" title="'.$status.'"></span>' : '').'
				
				<div class="useravatarcontainer">'.$user->avatar("big").'<span class="overlay"></span></div>
				
				<div class="container">
					<dl>
						'.($user->handle ? '<dd class="handle">'.$user->handle.'</dd>' : '').'
						'.($user->interests ? '<dd class="bio">'.$user->interests.'</dd>' : '').'
						<dd class="since">Member for <b title="'.$user->registered.'">'.timeSince($user->registered).'</b></dd>
						<dd class="since">Last seen <b title="'.$user->activity.'">'.timeSince($user->activity).'</b> ago</dd>
						<dd class="buttons">
							<a href="/~'.$user->username.'/blog">Blog<span style="width:16px; height:16px; background-position:-40px -40px;"></span></a>
							<a href="/contact-user.php?user='.$user->username.'&method=pm">Contact<span style="width:20px; height:16px; background-position:0 -40px;"></span></a>
							<!--<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script><fb:like layout="button_count" show_faces="false" width="90" font="arial"></fb:like>-->
						</dd>
					</dl>
				</div>
				
				<div class="rolodex">
					<dl>
						<dt class="top">Name</dt>
						<dd class="top">'.($user->name ? $user->name : '?').'</dd>
						
						<dt>Location</dt>
						<dd>'.($user->location ? $user->location : '?').'</dd>
						
						<dt>Gender</dt>
						<dd>'.($user->gender ? ($user->gender == "asexual" ? 'Asexual or Robot' : ucwords($user->gender)) : '?').'</dd>
						
						'.($bday ? '<dt>Birthday</dt><dd>'.$bday.'</dd>' : '').$o_homepage.'
					</dl>
				</div>
				
				<div class="score" style="margin:15px 0 0;">
					<dl>
						<dt>'.number_format($user->score['vars']['num_forumposts']).'</dt>
						<dd>Forum Posts</dd>
						
						<dt>'.number_format($user->score['vars']['num_sblogposts']).'</dt>
						<dd>Sblog Posts</dd>
						
						<dt>'.number_format($user->score['vars']['num_pageedits']).'</dt>
						<dd>Page Edits</dd>
						
						<dt style="background-color:#E11E1E;">'.number_format(ceil($user->score['total'])).'</dt>
						<dd><b>Reputation</b></dd>
					</dl>
				</div>
				
			</div>
			
			<div class="userstream"><div>'.$o_stream.'</div></div>
			
			<div class="loading">Loading activity stream</div>
		';
		
}

if($http_request){
	$ajax->ret = $ret;
	exit();
}

$page->header();

$on[$section] = 'on';

if($section != "posts"){
	// Create a #posts container dummy so we can get posts nav functionality
	$ret['formatted'].= '<div id="posts" style="display:none"></div>';
}

?>
<script>$.jGrowl("You've accessed the new profile stream. This is a work in progress!")</script>

<div id="user-profile-header" data-uid="<?=$user->id?>" data-uname="<?=$user->username?>">
	
	<h1 title="#<?=$user->id?>" class="<?=$h1class?>"><?=$user->username?></h1>
	
	<nav>
		<ul>
			<li class="<?=$on['stream']?>" data-section="stream"><a href="/~<?=$user->username?>" class="user-profile-nav">Activity</a></li>
			<li class="<?=$on['posts']?>" data-section="posts"><a href="/~<?=$user->username?>/posts/" class="user-profile-nav">Sblog</a></li>
			<li class="<?=$on['fan']?>" data-section="fan"><a href="/~<?=$user->username?>/fan" class="user-profile-nav">Fan Space</a></li>
			<li class="<?=$on['collection']?>" data-section="collection"><a href="/~<?=$user->username?>/collection" class="user-profile-nav">Game Collection</a></li>
			<li class="<?=$on['badges']?>" data-section="badges"><a href="/~<?=$user->username?>/badges" class="user-profile-nav">Badges</a></li>
			<li class="<?=$on['edits']?>" data-section="edits"><a href="/~<?=$user->username?>/edits" class="user-profile-nav">Page Edits</a></li>
			<li class="<?=$on['forumposts']?>" data-section="forumposts"><a href="/~<?=$user->username?>/forumposts" class="user-profile-nav">Forum Posts</a></li>
			<li class="<?=$on['reputation']?>" data-section="reputation"><a href="/~<?=$user->username?>/reputation" class="user-profile-nav">Reputation</a></li>
		</ul>
	</nav>
	
</div>

<div id="user-profile-container" class="<?=$section?>"><?=$ret['formatted']?></div>
<?

$page->footer();