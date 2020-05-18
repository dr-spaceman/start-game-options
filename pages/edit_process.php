<?
use Vgsite\Page;
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.pages.edit.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.pglinks.php";
use Vgsite\Image;

function userErrorHandler($errno, $errstr, $errfile, $errline){
	$errortype = array (E_ERROR=>'Error',E_WARNING=>'Warning',E_PARSE=>'Parsing Error',E_NOTICE=>"Notice",E_CORE_ERROR=>'Core Error',E_CORE_WARNING=>'Core Warning',E_COMPILE_ERROR=>'Compile Error',E_COMPILE_WARNING=>'Compile Warning',E_USER_ERROR=>'User Error',E_USER_WARNING=>'User Warning',E_USER_NOTICE=>'User Notice',E_STRICT=>'Runtime Notice',E_RECOVERABLE_ERROR=>'Catchable Fatal Error');
	if($errortype[$errno] == "Notice") return true; //Don't show notices
	$GLOBALS['ret']['error'].= $errstr . ' [ERROR '.$errline.'] ['.$errfile.']' . "\n";
	$GLOBALS['ret']['error_vars'] = $vars;
}
$old_error_handler = set_error_handler("userErrorHandler");


$user = new user($usrid);

$ret = array();

if(!$_POST['_editdata']) trigger_error("No data passed", E_USER_ERROR);
parse_str($_POST['_editdata'], $enddata);
if(!$enddata['handle']) trigger_error("No handle data passed", E_USER_ERROR);
parse_str(base64_decode($enddata['handle']), $handle);
if(!$title = $handle['title']) trigger_error("No title given", E_USER_ERROR);

if(!$field = $_POST['_field']) trigger_error("No field name given", E_USER_ERROR);

if($ret['error']) die(json_encode($ret));

$title = formatName($title);
$title = ucfirst($title);
$ed = new pgedit($title);

//check session
if(!$sessid = $handle['sessid']) trigger_error("No session id given", E_USER_ERROR);
$ed->sessid = $sessid;
try{ $ed->checkSession(); }
catch(Exception $e){ trigger_error($e->getMessage(), E_USER_ERROR); }
if($ed->sessionest && $ed->sessionrow->usrid != $usrid) trigger_error("Session users don't match; No access to edit session", E_USER_ERROR);

try{ $ed->loadData("sessid"); } // populate $ed->data with the current session data
catch(Exception $e){ trigger_error("Couldn't load data from session file", E_USER_ERROR); die(json_encode($ret)); }

parse_str($_POST['_input'], $in);

$dt = date("Y-m-d H:i:s");

switch($field){
	
	// set $this->data to the given input
	// Score modifiers
	// return output preview
	
	case "publish":
		break;
	
	case "genres":
	case "developers":
	case "series":
	case "categories":
	case "credits_list":
	case "roles":
		$ch = array("genres" => "genre", "developers" => "developer", "series" => "game_series", "categories" => "category", "credits_list" => "credit", "roles" => "role");
		unset($ed->data->{$field});
		$xml = $ed->data->addChild($field);
		if($in[$field] && is_string($in[$field])) trigger_error("Error on $field: An array of data was not found.");
		elseif(is_array($in[$field])) {
			$field_str = implode("", $in[$field]);
			$links = new pglinks;
			foreach($links->extractFrom($field_str, ($field=="credits_list" ? true : false), ($field=="credits_list" ? true : false)) as $link){
				$c = $xml->addChild($ch[$field], htmlspecialchars($link['original']));
				if($in['categories_parent'] && in_array($link['tag'], $in['categories_parent'])) $c->addAttribute("ancestor", "parent");//$ret['errors'][] = $c->asXML();
			}
		}
		$ret['view'] = $ed->fieldView($field);
		break;
	
	case "publications":
	
		unset($ed->data->publications);
		$publications = $ed->data->addChild("publications");
		
		if($in['publications']){
			
			$prim_pub = (string)$in['publications_primary'];
			
			//sort by date
			$pubsort = array();
			foreach($in['publications'] as $key => $val){
				if(preg_match("/^(19|20|21)\d\d$/", $in['publications'][$key]['release'])) $in['publications'][$key]['release_tentative'] = 1;
				if($in['publications'][$key]['release_tentative']){
					if(!preg_match("/(19|20|21)\d\d/", $in['publications'][$key]['release'], $matches)) trigger_error("Please input a valid year for all given Tentative publication release dates. We couldn't find a year for the given date [".$in['publications'][$key]['release']."]");
					$in['publications'][$key]['release_year'] = $matches[0];
					$pubsort[$key] = $matches[0] . '99990';
					continue;
				}
				if(($release_timestamp = strtotime($in['publications'][$key]['release'])) === false){
					trigger_error("Couldn't convert the given release date [".$in['publications'][$key]['release']."] into a real date. Input a real date, or mark this release as 'tentative'.");
					$release_timestamp = '';
				} else {
					$region_key = 0;
					for($i=0; $i < count($pf_regions_expanded); $i++){
						if($pf_regions_expanded[$i]['region'] == $in['publications'][$key]['region']) $region_key = $i;
					}
					$pubsort[$key] = date('Ymd', $release_timestamp).$region_key;
					$in['publications'][$key]['release_year'] = date('Y', $release_timestamp);
					$in['publications'][$key]['release_month'] = date('m', $release_timestamp);
					$in['publications'][$key]['release_day'] = date('d', $release_timestamp);
				}
			}
			asort($pubsort);
			
			$i = 0;
			foreach(array_keys($pubsort) as $key){
				
				$i++;
				
				$pub = $publications->addChild("publication");
				$row = $in['publications'][$key];
				
				if($prim_pub == $key) $pub->addAttribute('primary', 'primary');
				elseif($prim_pub == '' && $row['region'] == "North America"){
					$prim_pub = $key;
					$in['publications_primary'] = $prim_pub;
					$pub->addAttribute('primary', 'primary');
				}
				
				if($row['reissue']) $pub->addAttribute("reissue", "reissue");
				
				$pub->addChild('img_name', htmlspecialchars($row['img_name']));
				if($row['img_name_title_screen']) $pub->addChild('img_name_title_screen', htmlspecialchars($row['img_name_title_screen']));
				if($row['img_name_logo']) $pub->addChild('img_name_logo', htmlspecialchars($row['img_name_logo']));
				
				$row['title'] = trim($row['title']);
				if($row['title'] == '') $row['title'] = $ed->title;
				$pub->addChild('title', htmlspecialchars($row['title']));
				
				$row['platform'] = trim($row['platform']);
				if($row['platform'] == '') trigger_error('Please input a platform for <a href="#?field=publications" onclick="$(\'#alert\').hide()">'.$row['title'].'</a>', E_USER_ERROR);
				//Categorize platform links
				$pglinks = new pglinks();
				if($pflinks = $pglinks->extractFrom($row['platform'])){
					foreach($pflinks as $link){
						//dont categorize this platform if its a digital reissue
						$category = ($pub['reissue'] && $row['distribution'] == "digital" ? "" : "Category:");
						$row['platform'] = str_replace($link['original'], '[['.$category.$link['tag'].($link['link_words'] ? '|'.$link['link_words'] : '').']]', $row['platform']);
					}
				}
				//$row['platform'] = str_replace("[[Category:", "[[", $row['platform']);
				//if(substr($row['platform'], 0, 2) == "[[") $row['platform'] = "[[Category:" . substr($row['platform'], 2);
				$pub->addChild('platform', htmlspecialchars($row['platform']));
				
				if(!$row['region']) trigger_error('Please input a region for <a href="#?field=publications" onclick="$(\'#alert\').hide()">'.$row['title'].'</a>', E_USER_ERROR);
				$pub->addChild('region', htmlspecialchars($row['region']));
				
				$row['publisher'] = trim($row['publisher']);
				$pglinks = new pglinks();
				if($pflinks = $pglinks->extractFrom($row['publisher'])){
					foreach($pflinks as $link){
						$row['publisher'] = str_replace($link['original'], '[[Category:'.$link['tag'].($link['link_words'] ? '|'.$link['link_words'] : '').']]', $row['publisher']);
					}
				}
				$pub->addChild('publisher', htmlspecialchars($row['publisher']));
				
				if($row['release_tentative']){
					$pub->addChild('release_tentative', htmlspecialchars($row['release']));
					$pub->addChild('release_year', htmlspecialchars($row['release_year']));
				} else {
					$pub->addChild('release_year', htmlspecialchars($row['release_year']));
					$pub->addChild('release_month', htmlspecialchars($row['release_month']));
					$pub->addChild('release_day', htmlspecialchars($row['release_day']));
				}
				
				if($row['distribution'] == "digital" && !$row['media_distribution']){
					trigger_error('Please input a Distribution Platform for all publications marked [Digital] <a href="#?field=publications" onclick="$(\'#alert\').hide()">'.$row['title'].'</a>', E_USER_ERROR);
				} elseif($row['media_distribution']) {
					$pglinks = new pglinks();
					if($pflinks = $pglinks->extractFrom($row['media_distribution'])){
						foreach($pflinks as $link){
							$row['media_distribution'] = str_replace($link['original'], '[[Category:'.$link['tag'].($link['link_words'] ? '|'.$link['link_words'] : '').']]', $row['media_distribution']);
						}
					}
					$row['media_distribution'] = trim($row['media_distribution']);
					$pub->addChild('media_distribution', htmlspecialchars($row['media_distribution']));
					if($row['distribution'] == "digital") $pub->addChild("distribution", "digital");
				}
				
				if($row['notes'] = trim($row['notes'])){
					$pub->addChild('notes', htmlspecialchars($row['notes']));
				}
				
			}
			
			if(!$prim_pub){
				$result = $ed->data->xpath("//publication");
				if($node = $result[0]){
					$in['publications_primary'] = 1;
					if(!$node['primary']) $node->addAttribute('primary', 'primary');
				}
			}
		}
		$ret['view'] = $ed->fieldView($field);
		break;
		
	case "img":
		
		/*if(!$in['rep_image'] && $in['img_main_auto']){
			//automatically get primary pub box art for the main (rep) img
			$result = $ed->data->xpath("//publication[@primary='primary']");
			if($node = $result[0]){
				if((string)$node->img_name){
					$in['rep_image'] = "img:".(string)$node->img_name;
				} else trigger_error("No primary publication set");
			}
		}*/
		
		foreach(array("rep_image", "heading_image", "background_image") as $img){
			$ed->data->{$img} = $in[$img];
			if($attr = $in['imgattr'][$img]){
				foreach($attr as $name => $val){
					if(is_array($val)) $val = implode("", $val);
					if((string)$ed->data->{$img}->attributes()->{$name}) $ed->data->{$img}->attributes()->{$name} = htmlspecialchars($val);
					else $ed->data->{$img}->addAttribute($name, htmlspecialchars($val));
				}
			}
		}
		
		if($ed->type == "game"){
			foreach(array("img_titlescreen", "img_gameplay_1", "img_gameplay_2", "img_gameplay_3", "img_gameover", "video_trailer") as $img){
				unset($ed->data->{$img});
				if($in[$img]) $ed->data->{$img} = $in[$img];
			}
		}
		
		break;
	
	case "online":
		unset($ed->data->online);
		if(count($in['online_network'])){
			$online = $ed->data->addChild("online");
			foreach($in['online_network'] as $network) $online->addChild("network", htmlspecialchars($network));
		}
		$ret['view'] = $ed->fieldView($field);
		break;
	
	case "apis":
		unset($ed->data->wikipedia_title);
		$ed->data->wikipedia_title = parseText($in['wikipedia_title']);
		unset($ed->data->twitter_id);
		$ed->data->twitter_id = trim($in['twitter_id']) ? $in['twitter_type'] . parseText($in['twitter_id']) : '';
		unset($ed->data->steam_appid);
		if($in['steam_appid']) $ed->data->steam_appid = parseText($in['steam_appid']);
		unset($ed->data->amazon_asin);
		if($in['amazon_asin']) $ed->data->amazon_asin = parseText($in['amazon_asin']);
		$ret['view'] = $ed->fieldView($field);
		break;
	
	case "credits":
		$in[$field] = trim($in[$field]);
		unset($ed->data->credits);
		$ed->data->credits = parseText($in[$field]);
		$ret['view'] = $ed->fieldView($field);
		$ed->data->credits->addAttribute("source", $in['credits_source']);
		$ed->data->credits->addAttribute("completion", $in['credits_completion']);
		break;
	
	case "characters":
	case "locations":
		unset($ed->data->{$field});
		if($in['inputtype'][$field] == "open"){
			if(trim($in[$field]['open']) == "") break;
			$ed->data->{$field} = parseText($in[$field]['open']);
			$ed->data->{$field}->addAttribute("inputtype", "open");
			$ret['view'] = $ed->fieldView($field);
		} else {
			unset($in[$field]['open']);
			$xml = $ed->data->addChild($field);
			$pglinks = new pglinks();
			if($in[$field] && is_string($in[$field])) trigger_error("Error on $field: An array of data was not found.");
			elseif(is_array($in[$field])) {
				foreach($in[$field] as $link){
					$xml->addChild(substr($field, 0, -1), htmlspecialchars($link));
				}
			}
			$ret['view'] = $ed->fieldView($field);
		}
		break;
	
	case "personal":
		$ed->data->dob = '';
		$dob = implode("-", $in['dob']);
		if($dob == '0000-00-00') $dob = '';
		else $ed->data->dob = $dob;
		$ed->data->nationality = $in['nationality'];
		$ret['view'] = $ed->fieldView($field);
		break;
		
	case "keywords":
		if($in['keywords'] == $ed->title) $in['keywords'] = '';
		
	default:
		$in[$field] = trim($in[$field]);
		$ed->data->{$field} = parseText($in[$field]);
		$ret['view'] = $ed->fieldView($field);
}

if($ret['error']) die(json_encode($ret));

try{ $ed->save("draft"); }
catch(Exception $e){ trigger_error("Couldn't save draft data file (".$e->getMessage().")", E_USER_ERROR); die(json_encode($ret)); }

if(!$ed->sessionest){
	
	//get fsize of source file, be it an old revision or the current version
	if($enddata['editsource']){
		$old_len = filesize($_SERVER['DOCUMENT_ROOT']."/pages/xml/drafts/".$enddata['editsource'].".xml");
		//echo 'old_len source '.$_SERVER['DOCUMENT_ROOT']."/pages/xml/drafts/".$enddata['editsource'].".xml";
	} elseif($ed->pgid){
		$old_len = filesize($_SERVER['DOCUMENT_ROOT']."/pages/xml/".$ed->pgid.".xml");
		//echo 'old_len source '.$_SERVER['DOCUMENT_ROOT']."/pages/xml/".$ed->pgid.".xml";
	} else {
		$old_len = 0;
	}
	if(!$old_len) $old_len = 0;
	//die(" = ".$old_len);
	
	$q = "INSERT INTO pages_edit (pgid, `title`, session_id, usrid, source_session_id, old_len) VALUES 
	('".$handle['pgid']."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."', '$ed->sessid', '$usrid', '".$enddata['editsource']."', '$old_len')";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) trigger_error("Couldn't record edit session in the database", E_USER_ERROR);

}

$q = "UPDATE pages_edit SET 
	edit_summary = '".mysqli_real_escape_string($GLOBALS['db']['link'], $enddata['edit_summary'])."', 
	`minor_edit` = '".$enddata['minoredit']."', 
	`datetime`   = '$dt', 
	`new_len`    = '$ed->length',
	`score`      = '".$sc."'
	WHERE session_id='$ed->sessid' LIMIT 1";
if(!mysqli_query($GLOBALS['db']['link'], $q)) trigger_error("Couldn't record edit summary", E_USER_ERROR);

if(!$ret['error']) $ret['saved'] = 1;

$ret['pagecompletion'] = $ed->calculateCompletion();
$ret['pagecompletion_needed'] = count($ed->completion_needed) ? "Still needed: ".implode(", ", $ed->completion_needed) : '';

if($field == "publish"){
	
	// PUBLISH //
	
	//redirection
	preg_match("@^#REDIRECT ?\[\[(.*?)\]\]@is", $ed->data->content, $matches);
	$redirect_to = $matches[1];
	$redirect_to = trim($redirect_to);
	if($redirect_to != "") {
		$redirect_to = formatName($redirect_to);
		if($redirect_to && $redirect_to != $dbdat->redirect_to && !$enddata['edit_summary']) {
			trigger_error('Please give a reason for redirecting in the <a href="#editsummary" onclick="$(\'#alert\').hide()">EDIT SUMMARY</a>', E_USER_ERROR);
			die(json_encode($ret));
		}
	} elseif(strstr($ed->data->description, "#REDIRECT")) trigger_error('In order to redirect this page, please move the redirect code from the <a href="#?field=description" onclick="$(\'#alert\').hide()">Description</a> field to the .incyclopedia (article) field', E_USER_ERROR);
	
	if(!$redirect_to){
		//make sure required fields are given
		if(trim($ed->data->description) == "" && ($ed->type == "game" || $ed->type == "person")) trigger_error('Please supply a <a href="#?field=description" onclick="$(\'#alert\').hide();">Description</a>.', E_USER_ERROR);
		if(trim($ed->data->description) == "" && trim($ed->data->content) == "") trigger_error('Please give a <a href="#?field=description" onclick="$(\'#alert\').hide();">Description</a> and/or <a href="#?field=content" onclick="$(\'#alert\').hide();">Page Content</a>.', E_USER_ERROR);
	}
	
	if($ret['error']) die(json_encode($ret));
	
	$pgexists = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM pages WHERE `title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $ed->title)."' LIMIT 1"));
	if(!$ed->pgid || !$pgexists){
		$q = "SELECT * FROM pages WHERE `title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $ed->title)."' LIMIT 1";
		if($dbdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))){
			$ed->pgid = $dbdat->pgid;
		} else {
			$title_sort = formatName($ed->title, "sortable");
			$title_sort = strtolower($title_sort);
			$ed->pgid = mysqlNextAutoIncrement("pages");
			$q = "INSERT INTO pages (`type`, `title`, `title_sort`, `creator`, `created`) VALUES ('".$ed->type."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $ed->title)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $title_sort)."', '$usrid', '$dt');";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) {
				trigger_error("There was a critical database error when trying to insert page; This page hasn't been created, but your draft has been saved.", E_USER_ERROR);
				die(json_encode($ret));
			}
			$justcreated = 1;
		}
	}
	
	$q = "UPDATE pages SET 
		`subcategory` = '',
		`keywords`    = '".mysqli_real_escape_string($GLOBALS['db']['link'], $ed->data->keywords)."',
		`description` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $ed->data->description)."',
		`rep_image`   = '".mysqli_real_escape_string($GLOBALS['db']['link'], $ed->data->rep_image)."',
		`background_image` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $ed->data->background_image)."',
		`redirect_to` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $redirect_to)."',
		`modifier`    = '$usrid',
		`modified`    = '$dt'
		WHERE pgid='".$ed->pgid."' LIMIT 1";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) trigger_error("Couldn't update database values for alternate titles and keywords; ".mysqli_error($GLOBALS['db']['link']), E_USER_ERROR);
	
	try{ $ed->save(false, true); }
	catch(Exception $e){ trigger_error("Couldn't save base data file (".$e->getMessage().")", E_USER_ERROR); die(json_encode($ret)); }
	
	if($ret['error']) die(json_encode($ret));
	
	
	// calculate score
	
	if($enddata['withholdpts']) $sc = 0;
	else {
		//calculate contribution score
		
		//get lengths
		$q = "SELECT old_len, new_len FROM pages_edit WHERE session_id='$ed->sessid' LIMIT 1";
		$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
		
		$sc = 1; //base point per edit
		$sz = $row['new_len'] - $row['old_len'];
		if($sz > 0) $sc = $sc + ($sz / 500); // 1 point per 500 bytes
		//foreach($score_mult as $m) $sc = $sc * $m;
		//foreach($score_add  as $m) $sc = $sc + $m;
		if($sc < 1) $sc = 1;
	}
		
	$q = "UPDATE pages_edit SET 
		`published` = '1',
		`score`     = '$sc'
		WHERE session_id='$ed->sessid' LIMIT 1";
	mysqli_query($GLOBALS['db']['link'], $q);
	
	//Remove from pagecount_requestfail, the database that collects hits from pages not yet started
	$q = "DELETE FROM pagecount_requestfail WHERE title = '".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."'";
	mysqli_query($GLOBALS['db']['link'], $q);
	
	//Page links
	
	$old_parents = array();
	$parents = array();
	$q = "SELECT * FROM pages_links WHERE from_pgid = '$ed->pgid' AND ancestor = 'parent'";
	$r = mysqli_query($GLOBALS['db']['link'], $q);
	while($row = mysqli_fetch_assoc($r)){
		$old_parents[] = $row['to'];
	}
	
	$q = "DELETE FROM pages_links WHERE from_pgid = '".$ed->pgid."'";
	mysqli_query($GLOBALS['db']['link'], $q);
	$ulinks    = array();
	$ulinks_ns = array();
	$exlinks   = array();
	$pglinks = new pglinks();
	if($exlinks = $pglinks->extractFrom($ed->data->asXML(), false)){
		
		foreach($exlinks as $link){
			
			if(!in_array($link['tag'], $ulinks)) $ulinks[] = $link['tag'];
			if($link['namespace']){
				$key = array_search($link['tag'], $ulinks);
				$ulinks_ns[$key] = $link['namespace'];
			}
			
			//subcategory
			if($link['namespace'] == "Category" && in_array($link['tag'], array_keys($pgsubcategories))){
				$q = "UPDATE pages SET subcategory = '".mysqli_real_escape_string($GLOBALS['db']['link'], $link['tag'])."' WHERE pgid='".$ed->pgid."' LIMIT 1";
				if(!mysqli_query($GLOBALS['db']['link'], $q)) trigger_error("Couldn't set page subcategory to '".$link['tag']."'", E_USER_ERROR);
			}
			
		}
		
		//find parent category
		$result = $ed->data->xpath("//category[@ancestor='parent']");
		foreach($result as $node){
			$link = parseLink((string)$node[0]);
			$parents[] = $link['tag'];
		}
		
		$q = "INSERT INTO pages_links (`from_pgid`, `to`, `namespace`, `is_redirect`, `ancestor`) VALUES ";
		foreach($ulinks as $i => $link){
			$is_redirect = ($link == $redirect_to ? 1 : 0);
			$q.= " ('".$ed->pgid."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $link)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $ulinks_ns[$i])."', '$is_redirect', '".(in_array($link, $parents) ? "parent" : "")."'),";
		}
		$q = substr($q, 0, -1);
		if(!mysqli_query($GLOBALS['db']['link'], $q)) trigger_error("Couldn't record page links; ".mysqli_error($GLOBALS['db']['link']), E_USER_ERROR);
	}
	
	//$GLOBALS['debug']=true;
	categoryTreeTemplate($title);
	$rm_parents = array_diff($old_parents, $parents); //removed parents
	foreach($rm_parents as $parent) categoryTreeTemplate($parent);
	
	//update indexes
	
	if(!$redirect_to){
		
		list($index, $json_) = $ed->buildIndexRow($index);
		
		$title_sort = $ed->row['title_sort'] ? $ed->row['title_sort'] : formatName($ed->title, "sortable");
		$title_sort = strtolower($title_sort);
		$ia = substr($title_sort, 0, 1);
		if(!preg_match("/[a-z]/i", $ia)) $ia = "0";
		
		$q = "SELECT `json` FROM pages_index_json WHERE `type`='".$ed->type."' AND `letter` = '$ia' LIMIT 1";
		if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
			$json_blob = json_decode($row['json'], true);
		} else {
			mysqli_query($GLOBALS['db']['link'], "INSERT INTO pages_index_json (`type`, `letter`) VALUES ('".$ed->type."', '$ia')");
			$json_blob = array();
		}
		
		if($json_blob[$ed->title]){
			$json_blob[$ed->title] = $json_;
		} else {
			// Not yet on this index;
			// Fetch all sort names and insert this into the correct spot
			
			
			
			
			
			
			
			
			
			
		}
		
		$json_str = json_encode($json_blob);
		$q = "UPDATE pages_index_json SET `json` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $json_str)."' WHERE `type` = '".$ed->type."' AND `letter` = '$ia' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) trigger_error("Error updating JSON index: $q ", E_USER_ERROR);
		
		unset($json_['keywords']);
		unset($json_['description']);
		unset($json_['rep_image']);
		unset($json_['categories']);
		$q = "UPDATE pages SET index_data = '".mysqli_real_escape_string($GLOBALS['db']['link'], json_encode($json_))."' WHERE pgid='".$ed->pgid."' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) trigger_error("Couldn't update index_data field on pages database; ".mysqli_error($GLOBALS['db']['link']), E_USER_ERROR);
		
		//credits index
		if($ed->type == "person"){
			$q = "DELETE FROM credits WHERE person = '".mysqli_real_escape_string($GLOBALS['db']['link'], $ed->title)."' AND source_person = 1 AND source_game = 0 AND source_album = 0;";
			mysqli_query($GLOBALS['db']['link'], $q);
			$q = "UPDATE credits SET source_person = 0 WHERE person = '".mysqli_real_escape_string($GLOBALS['db']['link'], $ed->title)."';";
			mysqli_query($GLOBALS['db']['link'], $q);
			if($ed->data->credits_list->credit[0]){
				$queries = array();
				$pglinks = new pglinks();
				$exlinks = $pglinks->extractFrom($ed->data->credits_list->asXML());
				foreach($exlinks as $link){
					$work = $link['namespace'] == "AlbumID" ? "AlbumID:".$link['tag'] : $link['tag'];
					$q = "SELECT * FROM credits WHERE person = '".mysqli_real_escape_string($GLOBALS['db']['link'], $ed->title)."' AND work = '".mysqli_real_escape_string($GLOBALS['db']['link'], $work)."' LIMIT 1";
					if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))){
						$q = "UPDATE credits SET source_person = 1 WHERE person = '".mysqli_real_escape_string($GLOBALS['db']['link'], $ed->title)."' AND work = '".mysqli_real_escape_string($GLOBALS['db']['link'], $work)."'";
						mysqli_query($GLOBALS['db']['link'], $q);
					} else {
						$queries[] = "('".mysqli_real_escape_string($GLOBALS['db']['link'], $ed->title)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $work)."', '1')";
					}
				}
				if($queries){
					$query = "INSERT INTO credits (person, work, source_person) VALUES ".implode(",", $queries);
					mysqli_query($GLOBALS['db']['link'], $query);
				}
			}
		}
		if($ed->type == "game"){
			
			$pg = $ed;
			include $_SERVER['DOCUMENT_ROOT']."/bin/php/pages_index_buildinclude.games.php";
			
		}
		
	} // end index actions
	
	//former PS
	$q = "SELECT * FROM pages WHERE `title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."' LIMIT 1";
	$pgdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
	$contr = array();
	$contr = json_decode($pgdat->contributors);
	$former_ps = $contr[0];
	
	//recalculate contributions and get new PS
	$ps = $ed->recalculatePageContr();
	
	$has_new_ps = $former_ps && $former_ps != $ps ? true : false;
	$is_new_ps = $has_new_ps && $ps == $usrid ? true : false;
	
	//update user total score
	$q = "SELECT SUM( `score` ) AS `sum_score` FROM pages_edit WHERE usrid = '$usrid' AND `published` = '1';";
	$row_score = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
	$q = "UPDATE users SET contribution_score='".$row_score->sum_score."' WHERE usrid='$usrid' LIMIT 1;";
	mysqli_query($GLOBALS['db']['link'], $q);
	
	//notify users watching that this page has been edited
	$query = "SELECT usrid FROM pages_watch WHERE `title`='".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."' AND usrid != '".$usrid."';";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($_SERVER['HTTP_HOST'] != "localhost" && $row = mysqli_fetch_assoc($res)){
		$q = "SELECT `email`, `username` FROM users WHERE usrid = '$row[usrid]' LIMIT 1";
		do if($user_row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
			$q2 = "SELECT * FROM users_prefs WHERE usrid = '$row[usrid]' LIMIT 1";
			if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q2))){
				$prefs = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q2));
				if($prefs['watchlist_notify'] != 1) continue;
				if($enddata['minoredit'] && $prefs['watchlist_minor_no_notify'] == 1) continue;
			}
			$mail = array();
			$mail['headers'] = 'From: noreply@videogam.in' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
			$mail['subj'] = "[Videogam.in] $title page changed";
			$mail['message'] = $user_row['username'].",\n".$usrname." has changed the Videogam.in page for $title. Since you've edited this page before and it's on your watch list we thought you might like to know!\n\nSee the newly changed page at http://videogamin.squarehaven.com".$ed->url."\n\n".($enddata['edit_summary'] ? 'Edit summary: '.$enddata['edit_summary'] : "$usrname did not leave an edit summary of these changes. Please review the changes to make sure they are up to standards.")."\n\nSincerely,\nThe Videogam.in Page Change Notification Robot";
			if(!@mail($user_row['email'], $mail['subj'], $mail['message'], $mail['headers'])) sendBug('Couldnt e-mail page watcher. Details:'.implode('; ', $mail));
		} while(false);
	}
	
	// New Patron Saint
	do if($has_new_ps){
		
		//track for stream
		$q = "INSERT INTO stream (`action`, `action_type`, `usrid`) VALUES ('[[User:".$usrname."]] became the Patron Saint of [[".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."]].', 'page edit', '$usrid');";
		mysqli_query($GLOBALS['db']['link'], $q);
		
		//notify old ps
		if($_SERVER['HTTP_HOST'] == "localhost") break;
		try{
			$user_former_ps = new user($former_ps);
			$mail = array();
			$mail['headers'] = 'From: noreply@videogam.in' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
			$mail['subj'] = "[Videogam.in] You're no longer the Patron Saint of $title";
			$mail['message'] = $user_former_ps->username.",\n".$usrname." has changed the Videogam.in page for $title, making them its newly revered Patron Saint. Do not have pity on yourself, as ".$usrname." is not forever enshrined as such...\n\nIf you seek to avenge this egregious dishonor to your name, go forth! Contribute more to this page by navigating to http://videogamin.squarehaven.com".$ed->url." with the utmost haste.\n\nSincerely,\nThe Videogam.in Notification Robot";
			if(!@mail($user_former_ps->email, $mail['subj'], $mail['message'], $mail['headers'])) sendBug('Couldnt e-mail old Patron Saint. Details:'.implode('; ', $mail));
		}	catch(Exception $e){}
		
	} while(false);
	
	//watch
	$q = "SELECT * FROM pages_watch WHERE `title`='".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."' AND usrid='".$usrid."' LIMIT 1";
	$watching = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q));
	if($enddata['watch'] && !$watching){
		$q = "INSERT INTO pages_watch (`title`, usrid) VALUES ('".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."', '$usrid');";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) trigger_error("Couldn't add to your watch list", E_USER_ERROR);
	}
	
	// BADGES //
	
	foreach($ulinks as $i => $link) {
		if($ulinks_ns[$i] == "Category" && $link == "Mega Man series") Badge::getById(35)->earn($user);
		if($ulinks_ns[$i] == "Category" && $link == "Final Fantasy series"){
			Badge::getById(42)->earn($user);
			if($ps == $usrid) Badge::getById(43)->earn($user);
		}
		if($ulinks_ns[$i] == "Category" && $link == "Puzzle") Badge::getById(44)->earn($user);
		if($ulinks_ns[$i] == "Category" && $link == "Game Boy"){
			$query = "SELECT `title` FROM pages_links LEFT JOIN pages ON (pgid = from_pgid) WHERE `to` = 'Game Boy' AND is_redirect != '1'";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)){
				$q = "SELECT * FROM pages_edit WHERE `title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $row['title'])."' AND published = '1' LIMIT 1";
				if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $numGB++;
				if($numGB >= 5){
					Badge::getById(45)->earn($user);
					break;
				}
			}
		}
		if($ulinks_ns[$i] == "Category" && $link == "PlayStation"){
			$query = "SELECT `title` FROM pages_links LEFT JOIN pages ON (pgid = from_pgid) WHERE `to` = 'PlayStation' AND is_redirect != '1'";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)){
				$q = "SELECT * FROM pages_edit WHERE `title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $row['title'])."' AND published = '1' LIMIT 1";
				if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $numPS++;
				if($numPS >= 3){
					Badge::getById(58)->earn($user);
					break;
				}
			}
		}
		if($ulinks_ns[$i] == "Category" && ($link == "Namco" || $link == "Namco Bandai") && $ps == $usrid) Badge::getById(59)->earn($user); //Sexy Yellow Circle
		if($ed->type == "person"){
			$q = "SELECT DISTINCT(pgid) FROM pages_edit LEFT JOIN pages USING(pgid) WHERE `type` = 'person' AND usrid = '$usrid'";
			if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q)) >= 75) Badge::getById(52)->earn($user);
		}
	}
	
	//Treasure Hunter
	if($is_new_ps){
		
		$q = "UPDATE pages_edit SET new_ps = '1' WHERE session_id='$ed->sessid' LIMIT 1";
		mysqli_query($GLOBALS['db']['link'], $q);
		
		$userscore = new UserScore($user)->calculateScore();
		$num_ps_stolen = (int) $userscore['num_ps_stolen'];
		if($num_ps_stolen >= 5) Badge::getById(55)->earn($user);
		
	}
	
	$ret['goto'] = $ed->url . ($is_new_ps ? '#isNewPs' : '');
	
}

die(json_encode($ret));

?>