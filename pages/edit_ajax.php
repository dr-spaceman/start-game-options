<?
use Vgsite\Page;
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.ajax.php";

if($_POST['_do']) $do = $_POST['_do'];
if($_GET['_do'])  $do = $_GET['_do'];

switch($do){
	case "watch_page":
	
		$a = new ajax();
		
		if(!$pgtitle = formatName($_POST['_pgtitle'])) $a->kill("No page tile received");
		elseif(!$usrid) $a->kill('Please <a href="/login.php">log in</a> to watch this page.');
		else {
			$q = "SELECT * FROM pages_watch WHERE `title`='".mysqli_real_escape_string($GLOBALS['db']['link'], $pgtitle)."' AND usrid='$usrid' LIMIT 1";
			if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) {
				$q2 = "DELETE FROM pages_watch WHERE `title`='".mysqli_real_escape_string($GLOBALS['db']['link'], $pgtitle)."' AND usrid='$usrid'";
				if(!mysqli_query($GLOBALS['db']['link'], $q2)) $a->error("Error removing from watch list: ".mysqli_error($GLOBALS['db']['link']));
				$a->ret['removed'] = true;
			} else {
				$q2 = "INSERT INTO pages_watch (`title`, usrid) VALUES ('".mysqli_real_escape_string($GLOBALS['db']['link'], $pgtitle)."', '$usrid');";
				if(!mysqli_query($GLOBALS['db']['link'], $q2)) $a->error("Error adding; ".mysqli_error($GLOBALS['db']['link']));
				else $a->ret['added'] = 'Success! This page has been added to your <a href="/pages/watchlist.php">watch list</a>.';
			}
		}
		
		exit;
	
	
	case "preview":
		
		parse_str(base64_decode($_POST['_handle']), $handle);
		if(!$title = formatName($handle['title'])) exit;
		if(!$sessid = formatName($handle['sessid'])) exit;
		
		require $_SERVER['DOCUMENT_ROOT']."/bin/php/class.pages.php";
		$pg = new pg($title);
		$pg->sessid = $sessid;
		$pg->loadData("sessid");
		$pg->preview = true;
		$pg->output();
		exit;
	
	case "clonefield":
		
		if(!$field = $_POST['_field']) return 'No field';
		if(!$_POST['_handle']) return 'No handle';
		parse_str(base64_decode($_POST['_handle']), $handle);
		if(!$title = $handle['title']) return 'No title';
		$title = formatName($title);
		if(!$sessid = $handle['sessid']) return 'No session id';
		
		require $_SERVER['DOCUMENT_ROOT']."/bin/php/class.pages.edit.php";
		$ed = new pgedit($title);
		$ed->sessid = $sessid;
		$ed->loadData("sessid");
		
		echo $ed->fieldInput($field);
		exit;
	
	case "autofill desc":
		
		// Autofill description field
		
		$ret = array();
		
		parse_str($_POST['_editdata'], $enddata);
		if(!$enddata['handle']) $ret['error'].= "No handle data passed\n";
		parse_str(base64_decode($enddata['handle']), $handle);
		if(!$title = $handle['title']) $ret['error'].= "No title given\n";
		
		if($ret['error']) die(json_encode($ret));
		
		$title = formatName($title);
		
		require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.pages.php";
		$pg = new pg($title);
		
		if(!$sessid = $handle['sessid']) $ret['error'] = "No session id given";
		$pg->sessid = $sessid;
		
		try{ $pg->loadData("sessid"); } // populate $pg->data with the current session data
		catch(Exception $e){ $ret['error'] = "Couldn't load data from session file"; }
		
		if($ret['error']) die(json_encode($ret));
		
		$d = '';
		
		if($pg->type == "game"){
			
			//genres
			if(!$pg->data->genres || !$genres = $pg->data->genres->children()){ $d = "A game "; }
			else{
				$d = str_replace("Category:", "", $genres[0]);
				$vowels = array("A", "a", "E", "e", "I", "i", "O", "o", "U", "u");
				if(in_array(substr($d, 0, 1), $vowels)) $d = "An " . $d;
				else $d = "A " . $d;
				
				$g = array("RPG", "Shooter");
				if(!in_array($genres[0], $g)) $d.= " game ";
			}
			
			$pfs = array();
			$result = $pg->data->xpath("//publication");
			while(list( , $node) = each($result)){
				if(count($pfs) >= 3) break;
				if($node['reissue']) continue;
				$pf = (string)$node->platform;
				$pglinks = new pglinks();
				if($pflinks = $pglinks->extractFrom($pf)){
					foreach($pflinks as $link){
						$sh = $pf_shorthand[strtolower($link['tag'])];
						$pf = '[['.$link['tag'].($sh ? '|'.$sh : '').']]';
						if(!in_array($pf, $pfs)) $pfs[] = $pf;
						if(count($pfs) >= 3) break;
					}
				}
			}
			if($pfs){
				$o_pfs = implode(", ", $pfs);
				if($pos = strrpos($o_pfs, ",")) $o_pfs = substr($o_pfs, 0, $pos) . (count($pfs) > 2 ? ',' : '') . " and" . substr($o_pfs, ($pos + 1));
				$d.= "for " . $o_pfs . " ";
			}
			
			//publisher
			$publisher = '';
			$result = $pg->data->xpath("//publication[@primary='primary']/publisher");
			while(list( , $node) = each($result)){
				$publisher = (string)$node;
			}
			if($publisher) $d.= "by ".str_replace("Category:", "", $publisher)." ";
			else {
				//get developer
				$devs = array();
				$result = $pg->data->xpath("//developers/developer");
				while(list( , $node) = each($result)){
					$dev = (string)$node;
					$dev = str_replace("[[Category:", "[[", $dev);
					if(!in_array($dev, $devs)) $devs[] = $dev;
				}
				if($devs){
					$o = implode(", ", $devs);
					if($pos = strrpos($o, ",")) $o = substr($o, 0, $pos) . (count($devs) > 2 ? ',' : '') . " and" . substr($o, ($pos + 1));
					$d.= "by " . $o . " ";
				}
			}
			
			$srss = array();
			$result = $pg->data->xpath("//series/game_series");
			while(list( , $node) = each($result)){
				$srs = (string)$node;
				$srs = str_replace("[[Category:", "[[", $srs);
				if(!in_array($srs, $srss)) $srss[] = $srs;
			}
			if($srss){
				$o = implode(", ", $srss);
				if($pos = strrpos($o, ",")) $o = substr($o, 0, $pos) . (count($srss) > 2 ? ',' : '') . " and" . substr($o, ($pos + 1));
				$d.= "in the " . $o . " ";
			}
			
			$ret['output'] = trim($d);
			
		} elseif($pg->type == "person"){
			
			//get roles
			$roles = array();
			$result = $pg->data->xpath("//roles/role");
			while(list( , $node) = each($result)){
				$role = (string)$node;
				$role = str_replace("[[Category:", "[[", $role);
				$role = strtolower($role);
				if(!in_array($role, $roles)) $roles[] = $role;
			}
			if($roles){
				$o = implode(", ", $roles);
				if($pos = strrpos($o, ",")) $o = substr($o, 0, $pos) . (count($roles) > 2 ? ',' : '') . " and" . substr($o, ($pos + 1));
				$d.= "A " . $o . " ";
			} else {
				$d = "A game developer ";
			}
			
			//get developers
			$devs = array();
			$result = $pg->data->xpath("//developers/developer");
			while(list( , $node) = each($result)){
				$dev = (string)$node;
				$dev = str_replace("[[Category:", "[[", $dev);
				if(!in_array($dev, $devs)) $devs[] = $dev;
			}
			if($devs){
				$o = implode(", ", $devs);
				if($pos = strrpos($o, ",")) $o = substr($o, 0, $pos) . (count($devs) > 2 ? ',' : '') . " and" . substr($o, ($pos + 1));
				$d.= "at " . $o . " ";
			}
			
			$ret['output'] = trim($d);
			
		}
		
		die(json_encode($ret));
	
	case "fetch wikipedia data":
		
		$a = new ajax();
		
		if(!$title = formatName($_GET['_title'])) $a->kill("No page tile received");
		
		$curl = curl_init();
		
		$url = "http://en.wikipedia.org/w/api.php?action=query&prop=info&inprop=url&format=json&titles=".urlencode($title);
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_USERAGENT => 'MRB <hellobirdman@yahoo.com>',
		    CURLOPT_URL => $url,
		));
		$response = json_decode(curl_exec($curl), true);
		foreach($response['query']['pages'] as $page_id => $data){
			$wp = $data;
		}
		
		//$url = "http://en.wikipedia.org/w/api.php?action=query&titles=".urlencode($title)."&prop=revisions&rvprop=content&rvgeneratexml=1&format=json";
		$url = "http://en.wikipedia.org/w/api.php?action=query&prop=revisions&rvprop=content&rvsection=0&format=json&titles=".urlencode($title);
		$curl = curl_init();
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_USERAGENT => 'MRB <hellobirdman@yahoo.com>',
		    CURLOPT_URL => $url,
		));
		
		$response = json_decode(curl_exec($curl), true);
		foreach($response['query']['pages'] as $page_id => $data){
			$scrape = $data['revisions'][0]['*'];//echo $scrape;print_r($data);exit;
		}
		
		if($scrape){
			$lines = explode("\n", $scrape);
			for($i=0; $i < count($lines); $i++){
				$l = trim($lines[$i]);
				/*if($l == "{{Infobox video game") $start_infobox = 1;
				elseif(!$start_infobox) continue;*/
				preg_match("/\| *([a-z]+) *=(.*)/", $l, $matches);
				if($matches[0]){
					//print_r($matches);
					$name = trim($matches[1]);
					$value = trim($matches[2]);
					if(in_array($name, array("developer", "genre", "series"))){
						if($name == "developer"){
							$name = "developers";
						} elseif($name == "genre"){
							$name = "genres";
							$value = str_replace("video game", "game", $value);
						}
						$p = new pglinks();
						foreach($p->extractFrom($value) as $link){
							$tag = $link['link_words'] ? $link['link_words'] : $link['tag'];
							if($name == "series") $tag.= " series";
							$a->ret['field_datalist'][$name][]= htmlspecialchars($tag);
						}
					}
				}
				/*preg_match_all("/\{\{/", $l, $matches);
				$open_tags+= count($matches[0]);
				preg_match_all("/\}\}/", $l, $matches);
				if(count($matches[0])) $open_tags = $open_tags - count($matches[0]);
				echo $open_tags."; ";
				if(!$open_tags) $intro.= $l;
				echo $l."\n";*/
			}
		}
		
		// get Intro paragraphs
		
		$url = "http://en.wikipedia.org/w/api.php?format=json&action=query&export&titles=".urlencode($title)."&prop=extracts&exintro";
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_USERAGENT => 'MRB <hellobirdman@yahoo.com>',
		    CURLOPT_URL => $url,
		));
		$response = json_decode(curl_exec($curl), true);
		
		foreach($response['query']['pages'] as $page_id => $data){
			$intro = $data['extract'];
			$intro = str_replace('<sup>?</sup>', '', $intro);
			$intro = str_replace("\n", "", $intro);
			$intro = str_replace("<p>", "", $intro);
			$intro = str_replace("</p>", "\n\n", $intro);
			$intro = preg_replace(
				array("@\<\/?b\>@",
				      "@\<\/?i\>@",),
				array("**",
				      "*"),
				$intro
			);
			$intro.= "[^]: [".$wp['title']."](".$wp['fullurl']."). In Wikipedia, The Free Encyclopedia. Retrieved ".date("M j, Y")." ([source](".str_replace("action=edit", "oldid=".$wp['lastrevid'], $wp['editurl'])."))";
			$a->ret['intro'] = $intro;
		}
		
		// cover image
		
		/*$url = "http://en.wikipedia.org/w/api.php?action=query&titles=File:Max%20Payne%203%20Cover.jpg&prop=imageinfo&iiprop=size|url";
		*/
		
		curl_close($curl);
		exit;
	
	case "fetch steam appid":
		
		$a = new ajax();
		
		if(!$title = formatName($_GET['_title'])) $a->kill("No page tile received");
		
		$curl = curl_init();
		
		$url = "http://api.steampowered.com/ISteamApps/GetAppList/v0002/?&format=json";
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_USERAGENT => 'MRB <hellobirdman@yahoo.com>',
		    CURLOPT_URL => $url,
		));
		$response = json_decode(curl_exec($curl), true);
		foreach($response['applist']['apps'] as $i){
			if($i['name'] == $title){
				$a->ret['appid'] = $i['appid'];
				exit;
			}
		}
		
		exit;
		
	case "fetch img":
		
		require_once $_SERVER["DOCUMENT_ROOT"]."/pages/class.pages.edit.php";
		use Vgsite\Image;
		
		$a = new ajax();
		
		if(!$field = $_GET['_imgfield']) $a->kill("No image field specified");
		if(!$sessid = $_GET['_sessid']) $a->kill("No session id specified");
		
		switch($field){
			case "rep_image":
				
				if(!$xmlstr = @file_get_contents($_SERVER['DOCUMENT_ROOT']."/pages/xml/drafts/".$sessid.".xml")) $a->kill("Couldn't load data to fetch image.");
				$xmld = simplexml_load_string($xmlstr);
				
				$result = $xmld->xpath("//publication[@primary='primary']");
				if($node = $result[0]){
					if((string)$node->img_name) $a->ret['img'] = (string)$node->img_name;
					elseif((string)$node->img_name_title_screen) $a->ret['img'] = (string)$node->img_name_title_screen;
					elseif((string)$node->img_name_logo) $a->ret['img'] = (string)$node->img_name_logo;
					else $a->kill("No primary publication set, or the image was not in to right format.");
					$img = new img($a->ret['img']);
					$a->ret['img_tn'] = $img->src['sm'];
				}
				exit;
			
			case "gameimgs":
				
				if(!$xmlstr = @file_get_contents($_SERVER['DOCUMENT_ROOT']."/pages/xml/drafts/".$sessid.".xml")) $a->kill("Couldn't load data to fetch image.");
				$xmld = simplexml_load_string($xmlstr);
				
				$result = $xmld->xpath("//title");
				if(!$title = (string)$result[0]) $a->kill("Error reading TITLE from XML file.");
				
				$imgs = array();
				
				$result = $xmld->xpath("//publication[@primary='primary']");
				if($node = $result[0]){
					if((string)$node->img_name_title_screen) $imgs['img_titlescreen'] = (string)$node->img_name_title_screen;
				}
				
				$query = "SELECT img_name, img_category_id FROM images_tags LEFT JOIN images USING (img_id) WHERE `tag` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."' ORDER BY `sort`";
				$res = mysqli_query($GLOBALS['db']['link'], $query);
				while($row = mysqli_fetch_assoc($res)){
					switch($row['img_category_id']){
						case "11":
							if(!$imgs['img_titlescreen']) $imgs['img_titlescreen'] = $row['img_name'];
							break;
						case "1":
							if(!$imgs['img_gameplay_1']){ $imgs['img_gameplay_1'] = $row['img_name']; break; }
							if(!$imgs['img_gameplay_2']){ $imgs['img_gameplay_2'] = $row['img_name']; break; }
							if(!$imgs['img_gameplay_3']){ $imgs['img_gameplay_3'] = $row['img_name']; break; }
							break;
						case "14":
							if(!$imgs['img_gameover']) $imgs['img_gameover'] = $row['img_name'];
							break;
					}
				}
				
				foreach($imgs as $img => $filename){
					$_img = new img($filename);
					$a->ret['img'][$img]['filename'] = $filename;
					$a->ret['img'][$img]['tn'] = $_img->src['tn'];
				}
				
				exit;
				
		}
		
		exit;
		
	case "fetch video":
		
		$a = new ajax();
		
		if(!$url = $_GET['_url']) $a->kill("No URL specified");
		
		$oembed_url = 'http://www.youtube.com/oembed?url='.rawurlencode($url).'&format=json&maxwidth=100';
		if($oembed = curl_get($oembed_url)){
			$json = json_decode($oembed);
			if(!$json->html) $a->kill("[Error fetch] Couldn't fetch video from the given URL. Please make sure it's a valid Youtube URL.");
			$a->ret['video'] = $json;
		} else {
			$a->kill("Couldn't fetch video from the given URL. Please make sure it's a valid Youtube URL.");
		}
		
		exit;
	
	case "fetch link":
		
		$a = new ajax();
		
		if(!$text = $_GET['_text']) $a->kill("No text given");
		
		$pgl = new pglinks();
		foreach($pgl->extractFrom($text) as $link){
			$q = "SELECT subcategory FROM pages WHERE title = '".mysqli_real_escape_string($GLOBALS['db']['link'], $link['tag'])."' LIMIT 1";
			$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
			$pgl->attrs['data-subcategory'] = $row['subcategory'];
			$a->ret['formatted'] = $pgl->outputLink($link['tag'], $link['namespace'], $link['link_words']);
		}
		
		exit;
	
}

/* depreciated 26 feb '11
if($do == "ile_ouptut_field"){
	
	$inp = $_POST['_input'];
	$field = $_POST['_field'];
	if(strstr($field, "cr-")) $field = $_POST['_pgtype']."-credit";
	
	switch($field){
		case 'keywords':
			$ret = $inp;
			break;
		case 'content':
			$ret = parseText($inp);
			$ret = bb2html($ret);
			$ret = nl2p($ret);
			break;
		case 'categories':
			$ret = '<li>'.str_replace("\n", '</li><li>', $inp).'</li><li class="clear"></li>';
			$ret = bb2html($ret);
			break;
		case 'person-credit':
			parse_str($inp);
			foreach($in['credits']['credit'] as $p) {
				$key = (stristr($p['name'], "[[AlbumId:") ? "a" : "g");
				$roles = array();
				foreach($p['roles']['role'] as $k => $v) {
					if(is_array($v)) {
						$roles[] = $v;
					} else {
						$roles[0][$k] = $v;
					}
				}
				$dat = "";
				if($key == "g"){
					$pname = preg_replace('@.*\[\[(.+)\]\].*@', '$1', $p['name']);
					$q = "SELECT * FROM pages_games WHERE `title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $pname)."' LIMIT 1";
					$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
				} elseif($key == "a"){
					$pname = preg_replace('@.*\[\[(.+)\]\].*@', '$1', $p['name']);
					$q = "SELECT datesort FROM albums WHERE `albumid` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $pname)."' LIMIT 1";
					$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
					$dat->release = $dat->datesort;
				}
				$rolesstr = '';
				for($c = 0; $c < count($roles); $c++) {
					if(!$roles[$c]['credited_role']) continue;
					$rolesstr.= '<dd class="role">'.$roles[$c]['credited_role'].'</dd>';
				}
				if($rolesstr == '') $rolesstr = '<div><span class="warn"></span>No roles credited</div>';
				if($p['notes']){
					$p['notes'] = parseText($p['notes']);
					$p['notes'] = nl2br($p['notes']);
				}
				$ret = '
						<dl>
							<dt class="title '.($key == "a" ? 'album' : 'game').'">'.$p['name'].'</dt>
							'.$rolesstr.'
							<dd class="notes">'.$p['notes'].'</dd>
						</dl>
				';
				$ret = bb2html($ret);
			}
			break;
		case "game-credit":
			parse_str($inp);
			foreach($in['credits']['credit'] as $p) {
				$dl.= '<dt>'.$p['name'].'<a class="dnd"></a></dt>';
				$roles = array();
				foreach($p['roles']['role'] as $k => $v) {
					if(is_array($v)) $roles[] = $v;
					else $roles[0][$k] = $v;
				}
				$dd = '';
				foreach($roles as $r) {
					if($r['credited_role'] == '') continue;
					$dd.= '<dd>'.$r['credited_role'].'</dd>';
				}
				if(!$dd) $dd = '<dd><i style="color:#E32828;">No roles attributed</i></dd>';
				$dl.= $dd;
				if($p['notes']) {
					$dl.= '<dd class="notes">'.$p['notes'].'</dd>';
				}
				$dl.= '</dl>';
			}
			$ret = bb2html($dl);
			break;
		default:
			$ret = bb2html($inp);
	}
	
	die($ret);
	
}*/

/* depreciated 26 feb '11
if($do == "output_listitem") {
	
	$key = $_POST['_key'];
	$val = $_POST['_val'];
	$i   = $_POST['_index'];
	
	if($key == "publication"){
		outputPub('', $i);
		return;
	}
	
	if($key == "works_game" || $key == "works_person") {
		?>
		<dd class="tool-item sortable vital">
			<a class="dnd"></a>
			<a href="javascript:void(0);" class="ximg" style="right:15px;" onclick="rmToolItem($(this).closest('dd'));">remove</a>
			<div style="float:left;">
				<input type="text" name="in[credits][credit][<?=$i?>][name]" value="<?=htmlSC($val)?>" tabindex="1" style="display:none; width:226px; height:16px; font-family:monospace;"/>
				<span><?=bb2html($val, "pages_only")?></span> 
				<a href="#editname" title="edit this entry"" onclick="$(this).hide().prev().hide().prev().show();"><img src="/bin/img/icons/edit.gif" border="0" alt="edit"/></a>
			</div>
			<div style="margin:0 0 5px 208px;">
				<label><input type="radio" name="in[credits][credit][<?=$i?>][vital]" value="vital" checked="checked" class="cr-v-toggle"/> <b>Vital Role</b></label> &nbsp; 
				<label><input type="radio" name="in[credits][credit][<?=$i?>][vital]" value="" class="cr-v-toggle"/> Personnel</label>
			</div>
			<div style="float:left;">
				<?
				for($c = 0; $c < 5; $c++) {
					?>
					<div class="credit" style="margin:0 0 5px;<?=($c > 0 ? ' display:none;' : '')?>">
						<input type="text" name="in[credits][credit][<?=$i?>][roles][role][<?=$c?>][credited_role]" value="" tabindex="2" class="inprole" style="width:200px;"/>
					</div>
					<?
				}
				?>
				<a href="javascript:void(0);" onclick="$(this).siblings('.credit:hidden:eq(0)').show();" tabindex="3"><b>+</b> Add another role</a>
			</div>
			<div style="margin:0 50px 0 210px;">
				<textarea name="in[credits][credit][<?=$i?>][notes]" tabindex="4" style="width:100%; height:3em; border-color:#CCC;"><?=$p['notes']?></textarea>
				<div class="notesmsg" style="margin:5px 0 0;">
					<span style="padding:0 0 0 10px; color:#AAA; background:url(/bin/img/arrow-up-point.png) no-repeat left center;">Notes about this credit (BB Code allowed)</span>
				</div>
			</div>
			<br style="clear:both;"/>
		</dd>
		<?
		exit;
	}
	
	if($key == "ile-person-credit" || $key == "ile-game-credit"){
		?>
		<dl id="ile-cr-<?=$i?>" class="serializedreturn" style="position:fixed; width:530px; top:50%; left:50%; margin:-150px 0 0 -265px;">
			<dt>
				<a href="javascript:void(0);" title="Close this editing field (changes will still take effect upon submission)" class="ximg" style="top;12px; right:12px;" onclick="ileclose($(this).closest('dl'));">close</a>
				Credit
			</dt>
			<dd>
				<div style="position:relative; float:left;">
					<input type="text" name="in[credits][credit][<?=$i?>][name]" value="" tabindex="1" class="ile-ac" style="width:310px; font-family:monospace;" onfocus="$(this).next().css('opacity','.8').animate({opacity:.8},2000,function(){ $(this).fadeOut(); });"/>
					<span style="font-style:italic; color:#AAA; position:absolute; top:3px; left:5px;" onclick="$(this).hide().prev().focus();">Start typing to find a <?=($key == "ile-person-credit" ? 'Game or Album' : 'Person')?></span>
				</div>
				<div style="margin-left:345px;">
					<label><input type="radio" name="in[credits][credit][<?=$i?>][vital]" value="vital" checked="checked" class="cr-v-toggle"/> <b>Vital Role</b></label> &nbsp; 
					<label><input type="radio" name="in[credits][credit][<?=$i?>][vital]" value="" class="cr-v-toggle"/> Personnel</label>
				</div>
			</dd>
			<dd>
				<div style="float:left;">
					<?
					for($c = 0; $c < 5; $c++) {
						?>
						<div class="credit" style="margin:0 0 5px;<?=($c > 0 ? ' display:none;' : '')?>">
							<input type="text" name="in[credits][credit][<?=$i?>][roles][role][<?=$c?>][credited_role]" value="" tabindex="2" class="inprole" style="width:200px;"/>
						</div>
						<?
					}
					?>
					<a href="javascript:void(0);" onclick="$(this).siblings('.credit:hidden:eq(0)').show();" tabindex="3"><b>+</b> Add another role</a>
				</div>
				<div style="width:310px; margin:0 0 0 210px;">
					<textarea name="in[credits][credit][<?=$i?>][notes]" tabindex="4" style="width:98%; height:5em; border-color:#CCC;"></textarea>
					<div class="notesmsg" style="margin:5px 0 0;">
						<span style="padding:0 0 0 10px; color:#AAA; background:url(/bin/img/arrow-up-point.png) no-repeat left center;">Notes about this credit (BB Code allowed)</span>
					</div>
				</div>
				<br style="clear:both;"/>
			</dd>
			<dd class="ok">
				<a href="javascript:void(0);" tabindex="6" style="float:right; color:#E21D1D;" onclick="if(confirm('Remove this credit?')) { $(this).closest('dl').remove(); $('#il-cr-<?=$i?>').fadeOut(1200); ileclose(); }">Remove</a>
				<input type="button" value="OK" tabindex="5"/>
			</dd>
		</dl>
		<?
		exit;
	}
	
	die('<dd class="tool-item resetme"><a href="javascript:void(0);" class="ximg" onclick="rmToolItem($(this).closest(\'dd\'));">remove</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.bb2html("$val", "pages_only").' <textarea name="in['.$key.'][]">'.$val.'</textarea></dd>');
	
}*/

/* depreciated 26 feb '11
if($do == "loadpubs") {
	
	// Load and print all publications from games_publications that match a pgid or sessionid
	// @call AJAX
	// @post pgid
	// @post sessid
	
	$pgid = $_POST['pgid'];
	$sessid = $_POST['sessid'];
	
	$query = "SELECT * FROM games_platforms WHERE platform != 'multiple' ORDER BY platform";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$pfs[] = $row;
	}
	
	$regions = array(
		"us" => "North America",
		"jp" => "Japan",
		"eu" => "Europe",
		"au" => "Australia"
	);
	
	$query = "SELECT * FROM games_publications WHERE pgid='".mysqli_real_escape_string($GLOBALS['db']['link'], $pgid)."' OR pages_session_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $sessid)."' ORDER BY release_date ASC";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		?>
		<dd id="pub-<?=$row['id']?>" class="pub">
			<div style="float:right; font-size:12px; color:#CCC;">
				<a href="javascript:void(0);" title="Edit this publication" onclick="$(this).parent().siblings('table.editpubdata').show().next().hide();">Edit</a> | 
				<a href="javascript:void(0);" title="remove this publication" style="color:#D32C2C;" onclick="rmpub('<?=$row['id']?>');">Remove</a>
			</div>
			
			<img src="<?=substr($row['img'], 0, -4)?>-tn.png" style="float:left;"/>
			
			<table border="0" cellpadding="3" cellspacing="0" class="editpubdata" style="display:none; margin-left:95px;">
				<tr>
					<td><textarea name="in[pubs][<?=$row['id']?>][title]" rows="1" cols="50"><?=$row['title']?></textarea></td>
				</tr>
				<tr>
					<td>
						<select name="in[pubs][<?=$row['id']?>][platform_id]">
							<?
							foreach($pfs as $pf) echo '<option value="'.$pf['platform_id'].'"'.($row['platform_id'] == $pf['platform_id'] ? ' selected="selected"' : '').'>'.$pf['platform'].'</option>';
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<select name="in[pubs][<?=$row['id']?>][region]">
							<?
							foreach($regions as $code => $country) {
								echo '<option value="'.$code.'"'.($row['region'] == $code ? ' selected="selected"' : '').'>'.$country.'</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<select name="in[pubs][<?=$row['id']?>][year]">
							<?
							$rd = explode("-", $row['release_date']);
							$msel[$rd[1]] = ' selected="selected"';
							
							for($j = (date('Y') + 2); $j >= 1980; $j--) {
								echo '<option value="'.$j.'"'.($rd[0] == $j ? ' selected="selected"' : '').'>'.$j.'</option>'."\n";
							}
							?>
						</select> 
						<select name="in[pubs][<?=$row['id']?>][month]">
							<option value="00">Month</option>
							<option value="01"<?=$msel['01']?>>January</option>
							<option value="02"<?=$msel['02']?>>February</option>
							<option value="03"<?=$msel['03']?>>March</option>
							<option value="04"<?=$msel['04']?>>April</option>
							<option value="05"<?=$msel['05']?>>May</option>
							<option value="06"<?=$msel['06']?>>June</option>
							<option value="07"<?=$msel['07']?>>July</option>
							<option value="08"<?=$msel['08']?>>August</option>
							<option value="09"<?=$msel['09']?>>September</option>
							<option value="10"<?=$msel['10']?>>October</option>
							<option value="11"<?=$msel['11']?>>November</option>
							<option value="12"<?=$msel['12']?>>December</option>
						</select> 
						<select name="in[pubs][<?=$row['id']?>][day]">
							<option value="00">Day</option>
							<?
							for($j = 1; $j <= 31; $j++) {
								if($j < 10) $j = '0'.$j;
								echo '<option value="'.$j.'"'.($rd[2] == $j ? ' selected="selected"' : '').'>'.$j.'</option>'."\n";
							}
							?>
						</select>
					</td>
				</tr>
			</table>
			<div style="margin-left:95px; line-height:1.5em;">
				<i><?=$row['title']?></i><br/>
				Platform: <b><?=$pfs[$row['platform_id']]['platform']?></b><br/>
				Region: <b><?=$regions[$row['region']]?></b><br/>
				Release: <b><?=$row['release_date']?></b>
				<br style="clear:left;"/>
			</div>
		</dd>
		<?
	}
	
}

function outputPub($pub, $i){
	
	if(!$i) $i = rand(200,999);
	?>
	<dd class="tool-item item-pub">
		<div style="float:right; font-size:12px; color:#CCC;">
			<a href="javascript:void(0);" style="color:#D32C2C;" onclick="rmToolItem($(this).closest('dd'));">Remove</a>
		</div>
		<div class="pubimg">
			<?
			if($pub['filename']) {
				$f = explode("/", $pub['filename']);
				$tn = count($f) - 1;
				$f[$tn] = "sm_".substr($f[$tn], 0, -3)."png";
				$tn = implode("/", $f);
				$imgx = "";
				if(file_exists($_SERVER['DOCUMENT_ROOT'].$pub['filename'])) {
					//check img size
					$img  = @GetImageSize($_SERVER['DOCUMENT_ROOT'].$pub['filename']);
					$imgx = $img[0];
					echo '<a href="'.$pub['filename'].'" class="thickbox"><img src="'.$tn.'" border="0"/></a><input type="hidden" name="in[publications][publication]['.$i.'][filename]" value="'.$pub['filename'].'"/>';
				} else echo '<div style="padding:2px 5px; border:1px dashed #E12B2B;">File not found!</div>';
			} else echo '<div class="pubimg-new"></div>';
			?>
		</div>
		
		<div class="pubdata">
			
			<input type="file" name="boxfile_<?=$i?>" class="inp-pub"/> Upload new box art
			
			<p></p>
			
			<input type="text" name="in[publications][publication][<?=$i?>][title]" value="<?=($pub['title'] ? htmlSC($pub['title']) : htmlSC($_POST['_pgtitle']))?>" size="50" style="font-family:monospace;"/> Title <a href="#help" title="Titles sometimes differ between different regions, rereleases, and special editions" class="tooltip helpinfo"><span>?</span></a>
			
			<p></p>
			
			<input type="text" name="in[publications][publication][<?=$i?>][platform]" value="<?=($pub['platform'] ? htmlSC($pub['platform']) : "Start typing to find a platform...")?>" size="50" class="inp-pub acplatforms<?=($pub['platform'] ? '' : ' resetonfocus')?>" style="font-family:monospace;" onfocus="$(this).removeClass('resetonfocus'); if($(this).val() == 'Start typing to find a platform...') $(this).val('');"/> Platform
			
			<p></p>
			
			<select name="in[publications][publication][<?=$i?>][region]" class="inp-pub">
				<?
				if(!$pub['region']) echo '<option value="">Choose a Region...</option>';
				?>
				<option value="North America"<?=($pub['region'] == "North America" ? ' selected="selected"' : '')?>>North America</option>
				<option value="Europe"<?=($pub['region'] == "Europe" ? ' selected="selected"' : '')?>>Europe</option>
				<option value="Japan"<?=($pub['region'] == "Japan" ? ' selected="selected"' : '')?>>Japan</option>
				<option value="Australia"<?=($pub['region'] == "Australia" ? ' selected="selected"' : '')?>>Australia</option>
			</select> 
			<select name="in[publications][publication][<?=$i?>][release_year]" class="inp-pub" style="background-color:#FFFF80;">
				<?
				for($j = (date('Y') + 2); $j >= 1980; $j--) {
					echo '<option value="'.$j.'"'.($pub['release_year'] == $j ? ' selected="selected"' : '').'>'.$j.'</option>'."\n";
				}
				?>
				?>
			</select> 
			<select name="in[publications][publication][<?=$i?>][release_month]" class="inp-pub">
				<?
				$msel = "";
				$msel[$pub['release_month']] = ' selected="selected"';
				?>
				<option value="00"<?=$msel['00']?>>Month</option>
				<option value="01"<?=$msel['01']?>>1 January</option>
				<option value="02"<?=$msel['02']?>>2 February</option>
				<option value="03"<?=$msel['03']?>>3 March</option>
				<option value="04"<?=$msel['04']?>>4 April</option>
				<option value="05"<?=$msel['05']?>>5 May</option>
				<option value="06"<?=$msel['06']?>>6 June</option>
				<option value="07"<?=$msel['07']?>>7 July</option>
				<option value="08"<?=$msel['08']?>>8 August</option>
				<option value="09"<?=$msel['09']?>>9 September</option>
				<option value="10"<?=$msel['10']?>>10 October</option>
				<option value="11"<?=$msel['11']?>>11 November</option>
				<option value="12"<?=$msel['12']?>>12 December</option>
			</select> 
			<select name="in[publications][publication][<?=$i?>][release_day]" class="inp-pub">
				<option value="00">Day</option>
				<?
				for($day = 1; $day <= 31; $day++) {
					if($day < 10) $day = '0'.$day;
					echo '<option value="'.$day.'"'.($pub['release_day'] == $day ? ' selected="selected"' : '').'>'.$day.'</option>'."\n";
				}
				?>
			</select> 
			Release date
			
			<p></p>
			
			<?
			$primpub = ($pub ? ($pub['primary'] || $pub['@attributes']['primary'] ? TRUE : FALSE) : FALSE);
			?>
			<label><input type="radio" name="primary_publication" value="<?=$i?>"<?=($primpub ? ' checked="checked"' : '')?>/> This is the primary publication</label>
			
		</div><!--.pubdata-->
		
		<br style="clear:left;"/>
		
	</dd>
	<?
	
	
	
	
		if($key == "publication") {
		?>
		<dd class="tool-item item-pub">
			<a href="javascript:void(0);" style="float:right; font-size:12px; color:#D32C2C;" onclick="rmToolItem($(this).closest('dd'));">Remove</a>
			
			<div class="pubimg">
				<div class="pubimg-new"></div>
			</div>
			
			<div class="pubdata">
				
				<div>
					
					<label><input type="radio" name="primary_publication" value="<?=$i?>"/> This is the primary publication</label>
					
					<p></p>
			
					<input type="file" name="boxfile_<?=$i?>" class="inp-pub"/> Upload box art (required)
					
					<p></p>
					
					<?
					$query = "SELECT * FROM games_platforms WHERE platform != 'multiple' ORDER BY platform";
					$res   = mysqli_query($GLOBALS['db']['link'], $query);
					while($row = mysqli_fetch_assoc($res)) {
						$pfs[] = $row;
						if($row['notable']) $pfs_n[] = $row;
					}
					?>
					<select name="in[publications][publication][<?=$i?>][platform_id]" class="inp-pub" onchange="if(this.options[this.selectedIndex].value=='other') { $(this).html($(this).next().html()); }">
						<option value="">Select a platform...</option>
						<?
						foreach($pfs_n as $row) echo '<option value="'.$row['platform_id'].'">'.$row['platform']."</option>\n";
						?>
						<option value="other">other...</option>
					</select>
					<select name="all_pfs" style="display:none">
						<option value="">Select from all platforms...</option>
						<?
						foreach($pfs as $row) echo '<option value="'.$row['platform_id'].'">'.$row['platform']."</option>\n";
						?>
					</select> 
					
					<select name="in[publications][publication][<?=$i?>][region]" class="inp-pub">
						<option value="">Select a region...</option>
						<option value="us">North America</option>
						<option value="jp">Japan</option>
						<option value="eu">Europe</option>
						<option value="au">Australia</option>
					</select>
					
					<p></p>
					
					<select name="in[publications][publication][<?=$i?>][release_year]" class="inp-pub" style="background-color:#FFFF80;">
						<?
						for($year = (date('Y') + 2); $year >= 1980; $year--) {
							echo '<option value="'.$year.'"'.(date("Y") == $year ? ' selected="selected"' : ' style="background-color:white;"').'>'.$year.'</option>'."\n";
						}
						?>
					</select> 
					<select name="in[publications][publication][<?=$i?>][release_month]" class="inp-pub">
						<option value="00">Month</option>
						<option value="01">January</option>
						<option value="02">February</option>
						<option value="03">March</option>
						<option value="04">April</option>
						<option value="05">May</option>
						<option value="06">June</option>
						<option value="07">July</option>
						<option value="08">August</option>
						<option value="09">September</option>
						<option value="10">October</option>
						<option value="11">November</option>
						<option value="12">December</option>
					</select> 
					<select name="in[publications][publication][<?=$i?>][release_day]" class="inp-pub">
						<option value="00">Day</option>
						<?
						for($day = 1; $day <= 31; $day++) {
							if($day < 10) $day = '0'.$day;
							echo '<option value="'.$day.'">'.$day.'</option>'."\n";
						}
						?>
					</select> 
					<b>Release date</b> -- Input all known values (year is required)
					
				</div>
				
			</div><!--.pubdata-->
			
		</dd>
		<?
		exit;
	}
}*/