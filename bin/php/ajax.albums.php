<?
require $_SERVER['DOCUMENT_ROOT']."/bin/php/class.ajax.php";

$ajax = new ajax();

if(!$albumid = $_POST['albumid']) $ajax->kill("No album id");

if($_POST['do'] == "get_fans") {
	
	//fans list
	
	$query = "SELECT * FROM albums_ratings WHERE albumid='".mysqli_real_escape_string($GLOBALS['db']['link'], $albumid)."'";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$fans[$row['usrid']]['rating'] = $row['rating'];
	}
	$query = "SELECT * FROM albums_collection WHERE albumid='".mysqli_real_escape_string($GLOBALS['db']['link'], $albumid)."'";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$fans[$row['usrid']][$row['action']] = TRUE;
	}
	
	if(!$fans){
		$ajax->ret['formatted'] = '<tr><td>This album has no fans at all!</td></tr>';
		exit();
	} else {
		ksort($fans);
		foreach(array_keys($fans) as $f){
			$ajax->ret['formatted'].= '
			<tr>
				<td>'.outputUser($f).'</td>
				<td>'.($fans[$f]['rating'] ? '<span class="rating"><span class="rating-value-'.$fans[$f]['rating'].'" style="width:78px !important;"><img src="/bin/img/pixel.png" width="78" height="14" alt="star rating spacer"/></span></span>' : '&nbsp;').'</td>
				<td>'.($fans[$f]['collecting'] ? '<img src="/music/graphics/add-collection.png" alt="in collection"/>' : '&nbsp;').'</td>
				<td width="15%">'.($fans[$f]['listening'] ? '<img src="/music/graphics/add-playlist.png" alt="on playlist"/>' : '&nbsp;').'</td>
			</tr>
			';
		}
	}
	
	exit();
	
}

if($_POST['do'] == "rate"){
	
	//rating
	
	if(!$rating = $_POST['rating']) $rating = 0;
	
	$q = "DELETE FROM albums_ratings WHERE albumid='".mysqli_real_escape_string($GLOBALS['db']['link'], $albumid)."' AND usrid='$usrid' LIMIT 1";
	mysqli_query($GLOBALS['db']['link'], $q);
	
	if($rating == '0') {
		$ajax->ret['success'] = true;
	} else {
		$q = "INSERT INTO albums_ratings (albumid, rating, usrid, datetime) VALUES ('".mysqli_real_escape_string($GLOBALS['db']['link'], $albumid)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $rating)."', '$usrid', '".date("Y-m-d H:i:s")."')";
		if(mysqli_query($GLOBALS['db']['link'], $q)) $ajax->ret['success'] = true;
		else $ajax->kill("There was a database error and the requested action couldn't be performed. [ERROR 54IUD]");
	}
	
	exit();
	
}

if($_POST['do'] == "set_collection"){
	
	//collection
	
	if(!$what = $_POST['what']) $ajax->kill("No list action given");
	if(!$set = $_POST['set']) $ajax->kill();
	if(!$usrid) $ajax->kill("No user session registered; please log in.");
	
	if($what == "listening"){
		$q = $set == "true" ? "INSERT INTO pages_fan (usrid, op, `title`) VALUES ('$usrid', 'love', 'AlbumId:$albumid');" : "DELETE FROM pages_fan WHERE usrid='$usrid' AND op='love' AND `title`='AlbumId:$albumid';";
	} else {
		if($set == "true"){
			$q = "INSERT INTO albums_collection (action, albumid, usrid, datetime) VALUES ('".mysqli_real_escape_string($GLOBALS['db']['link'], $what)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $albumid)."', '$usrid', '".date("Y-m-d H:i:s")."')";
		} else {
			$q = "DELETE FROM albums_collection WHERE action='".mysqli_real_escape_string($GLOBALS['db']['link'], $what)."' AND albumid='".mysqli_real_escape_string($GLOBALS['db']['link'], $albumid)."' AND usrid='$usrid' LIMIT 1";
		}
	}
	
	if(!mysqli_query($GLOBALS['db']['link'], $q)) $ajax->kill("There was a database error and the requested action couldn't be performed. [ERROR 71JYTY]");
	
	$ajax->ret['success'] = true;
	
	exit();
	
}