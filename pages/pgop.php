<?
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.ajax.php";
include_once $_SERVER['DOCUMENT_ROOT']."/pages/include.pages.php";

$a = new ajax();

if(!$usrid) $a->kill("No user session registered; Please log in.");

$act = $_GET['action'];
if($act != "add" && $act != "rm" && $act != "edit") $a->kill("There was a form error");
$op = $_GET['op'];
if($op != "love" && $op != "hate" && $op != "collection") $a->kill("There was a form error [op]");
if(!$title = formatName($_GET['title'])) $a->kill("There was a form error [title]");
$remarks = trim($_GET['remarks']);

//check if title is a page
if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM pages WHERE `title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."' LIMIT 1"))){
	$a->kill('<i>'.$title.'</i> is not yet in the games database.<br/><b><a href="/content/Special:new?title='.formatNameUrl($title).'">Add it to the database</a></b>');
}

if($op == "collection"){
	
	require $_SERVER["DOCUMENT_ROOT"]."/bin/php/collection.php";
	
	$q = "SELECT * FROM collection WHERE usrid='$usrid' AND title='".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."' LIMIT 1";
	$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
	
	$coll = new collection();
	if(!$a->ret['formatted'] = $coll->form($title, $row)) $a->kill("Couldn't fetch collection form :( [error location: PGOP]");
	
	exit;
	
}

if($act == "add") $q = "INSERT INTO pages_fan (usrid, op, `title`) VALUES ('$usrid', '$op', '".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."');";
elseif($act == "rm") $q = "DELETE FROM pages_fan WHERE usrid='$usrid' AND op='$op' AND `title`='".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."';";
elseif($act == "edit") $q = "UPDATE pages_fan SET `remarks`='".mysqli_real_escape_string($GLOBALS['db']['link'], $remarks)."' WHERE usrid='$usrid' AND op='$op' AND `title`='".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."';";
if(!mysqli_query($GLOBALS['db']['link'], $q)) $a->ret['errors'][] = "There was a database error!".($usrrank > 6 ? " ".mysqli_error($GLOBALS['db']['link']) : '');
else $a->ret['success'] = '1';

if(($act == "edit" || $act == "add") && $_SESSION['fb_142628175764082_access_token']){
	
	//post to fb
	
	$page = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM pages WHERE `title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."' LIMIT 1"));
	
	if($page['type'] == "game"){
		$fb_fan_obj = "game";
		$fb_fan_url = "http://videogam.in/games/".formatNameUrl($title);
	} elseif($page['type'] == "person"){
		$fb_fan_obj = "person";
		$fb_fan_url = "http://videogam.in/people/".formatNameUrl($title);
	} else {
		$fb_fan_obj = "page";
		$fb_fan_url = "http://videogam.in/".($page['type'] ? $pgtypes[$page['type']] : 'content').'/'.formatNameUrl($title);
	}
	
	require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/fb/src/facebook.php";
	$fb = array(
	  'appId'  => '142628175764082',
	  'secret' => '5913f988087cecedd1965a3ed6e91eb1'
	);
	$facebook = new Facebook($fb);
	if($fbuser = $facebook->getUser()){
		$handle = "/me/videogamin:".$op;
		$handle_get = $handle."?".$fb_fan_obj."=".$fb_fan_url;
		$handle_query = array($fb_fan_obj => $fb_fan_url, "remarks" => $remarks, "access_token" => $_SESSION['fb_142628175764082_access_token']);
		$handle_post = $handle . "?" . http_build_query($handle_query);
	  try {
	    $rows = $facebook->api($handle_get);
	    foreach($rows['data'] as $row){
	    	if($row['from']['id'] == $fbuser && $row['data'][$fb_fan_obj]['title'] == $title){
	    		// the user already marked this -- delete the old one
	    		$facebook->api("/".$row['id']."?access_token=".$_SESSION['fb_142628175764082_access_token'], "DELETE");
	    	}
	    }
	    $res = $facebook->api($handle, "POST", $handle_query);
	    if($res['id']){
	    	$a->ret['fb_post_id'] = $res['id'];
	    } else {
	    	$a->ret['errors'][] = "There was an error posting to Facebook!".($usrrank > 5 ? stripslashes("[$handle_post]") : '');
	    }
	  } catch (FacebookApiException $e) {
	    if($e) $a->ret['errors'][] = $e;
	  }
  	/*if(remarks){
  		// Post to wall
  		handle = "/me/feed?message="+remarks+"&title='.htmlSC($ptitle).'&description='.htmlSC($pdesc).'&link='.$page->meta['og:url'].'&picture='.$page->meta['og:image'].'&access_token='.$_SESSION['fb_142628175764082_access_token'].'";
  	}*/
	}
}

exit;

?>