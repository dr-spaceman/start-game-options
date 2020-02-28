<?
require ($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");

if(!$albumid = $_POST['albumid']) exit;

if($_POST['do'] == "get_fans") {
	
	//fans list
	
	$query = "SELECT * FROM albums_ratings WHERE albumid='$albumid'";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$fans[$row['usrid']]['rating'] = $row['rating'];
	}
	$query = "SELECT * FROM albums_collection WHERE albumid='$albumid'";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$fans[$row['usrid']][$row['action']] = TRUE;
	}
	
	if(!$fans) echo '<tr><td>This album has no fans at all!</td></tr>';
	else {
		ksort($fans);
		foreach(array_keys($fans) as $f) {
			?>
			<tr>
				<td><?=outputUser($f)?></td>
				<td><?=($fans[$f]['rating'] ? '<span class="rating"><span class="rating-value-'.$fans[$f]['rating'].'" style="width:78px !important;"><img src="/bin/img/pixel.png" width="78" height="14" alt="star rating spacer"/></span></span>' : '&nbsp;')?></td>
				<td><?=($fans[$f]['collecting'] ? '<img src="/music/graphics/add-collection.png" alt="in collection"/>' : '&nbsp;')?></td>
				<td width="15%"><?=($fans[$f]['listening'] ? '<img src="/music/graphics/add-playlist.png" alt="on playlist"/>' : '&nbsp;')?></td>
			</tr>
			<?
		}
	}
	
} elseif($_POST['do'] == "rate") {
	
	//rating
	
	if(!$rating = $_POST['rating']) $rating = 0;
	
	$q = "DELETE FROM albums_ratings WHERE albumid='$albumid' AND usrid='$usrid' LIMIT 1";
	mysqli_query($GLOBALS['db']['link'], $q);
	
	if($rating == '0') {
		echo '1';
	} else {
		$q = "INSERT INTO albums_ratings (albumid, rating, usrid, datetime) VALUES ('$albumid', '$rating', '$usrid', '".date("Y-m-d H:i:s")."')";
		if(mysqli_query($GLOBALS['db']['link'], $q)) echo "1";
		else sendBug("User couldn't rate album. query: $q");
	}
	
} else {
	
	//collection
	
	if(!$what = $_POST['what']) exit;
	if(!$set = $_POST['set']) exit;
	if(!$usrid) exit;
	
	if($set == "true") {
		$q = "INSERT INTO albums_collection (action, albumid, usrid, datetime) VALUES ('$what', '$albumid', '$usrid', '".date("Y-m-d H:i:s")."')";
	} else {
		$q = "DELETE FROM albums_collection WHERE action='$what' AND albumid='$albumid' AND usrid='$usrid' LIMIT 1";
	}
	
	if(mysqli_query($GLOBALS['db']['link'], $q)) {
		echo "1";
	} else {
		sendBug("User couldn't update albums_collection db table. query: $q");
	}
	
}