<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");

$what = $_GET['what'];
$rows = array();

if(strstr($what, "roles")) {
	$q = "SELECT DISTINCT(role) FROM people_work ORDER BY role";
	$r = mysqli_query($GLOBALS['db']['link'], $q);
	while($row = mysqli_fetch_assoc($r)) $rows[] = $row['role'];
}
if(strstr($what, "developers")) {
	$q = "SELECT DISTINCT(developer) FROM games_developers ORDER BY developer";
	$r = mysqli_query($GLOBALS['db']['link'], $q);
	while($row = mysqli_fetch_assoc($r)) $rows[] = $row['developer'];
}
if($what == "genres"){
	$xmld = file_get_contents($_SERVER['DOCUMENT_ROOT']."/pages/xml/index/game.xml");
	$xml = simplexml_load_string($xmld);
	$result = $xml->xpath('/index/game/genres/genre');
	while(list( , $node) = each($result)){
		$row = (string)$node;
		if(strstr($row, "|")) $row = substr($row, 0, strpos($row, "|"));
		if(!in_array($row, $rows)) $rows[] = $row;
	}
	asort($rows);
	die(implode("`", $rows));
}
if($what == "series"){
	$xmld = file_get_contents($_SERVER['DOCUMENT_ROOT']."/pages/xml/index/game.xml");
	$xml = simplexml_load_string($xmld);
	$result = $xml->xpath('/index/game/series/series_item');
	while(list( , $node) = each($result)){
		$row = (string)$node;
		if(strstr($row, "|")) $row = substr($row, 0, strpos($row, "|"));
		if(!in_array($row, $rows)) $rows[] = $row;
	}
	asort($rows);
	die(implode("`", $rows));
}
if(strstr($what, "games")) { //title`gid`url`tag
	$q = "SELECT gid, title, title_url FROM games ORDER BY title";
	$r = mysqli_query($GLOBALS['db']['link'], $q);
	while($row = mysqli_fetch_assoc($r)) $rows[] = $row['title']."`".$row['gid']."`/games/".$row['gid']."/".$row['title_url']."`gid:".$row['gid'];
}
if(strstr($what, "people")) { //name`pid`url`tag`title`prolific
	$q = "SELECT pid, name, name_url, title, prolific FROM people ORDER BY name";
	$r = mysqli_query($GLOBALS['db']['link'], $q);
	while($row = mysqli_fetch_assoc($r)) $rows[] = $row['name']."`".$row['pid']."`/people/".$row['pid']."/".$row['name_url']."`pid:".$row['pid']."`".htmlSC($row['title'])."`".$row['prolific'];
}
if(strstr($what, "pages_titles")) {
	$q = "SELECT `title`,`keywords` FROM `pages` WHERE redirect_to = '' ORDER BY `title` $limit";
	$r = mysqli_query($GLOBALS['db']['link'], $q);
	while($row = mysqli_fetch_assoc($r)) $rows[] = $row['title']."`".$row['keywords'];
}
if(strstr($what, "pages_game_titles")) {
	$q = "SELECT `title`,`keywords` FROM `pages` WHERE `type` = 'game' AND redirect_to = '' ORDER BY `title` $limit";
	$r = mysqli_query($GLOBALS['db']['link'], $q);
	while($row = mysqli_fetch_assoc($r)) $rows[] = $row['title']."`".$row['keywords'];
}
if(strstr($what, "pages_person_titles")) {
	$q = "SELECT `title` FROM `pages` WHERE `type` = 'person' AND redirect_to = '' ORDER BY `title` $limit";
	$r = mysqli_query($GLOBALS['db']['link'], $q);
	while($row = mysqli_fetch_assoc($r)) $rows[] = $row['title'];
}
if($what == "categories") {
	$xmld = file_get_contents($_SERVER['DOCUMENT_ROOT']."/pages/xml/index/category.xml");
	$xml = simplexml_load_string($xmld);
	$result = $xml->xpath('/index/category/title');
	while(list( , $node) = each($result)){
		$rows[] = stripslashes($node);
	}
	//die('["'.implode('","', $ret).'"]');
	/*$q = "SELECT `title` FROM `pages` WHERE `type` = 'category' AND redirect_to = '' ORDER BY `title` $limit";
	$r = mysqli_query($GLOBALS['db']['link'], $q);
	while($row = mysqli_fetch_assoc($r)) $rows[] = $row['title'];*/
}
if(strstr($what, "platforms")) {
	$q = "SELECT `title`,`keywords` FROM pages_links LEFT JOIN `pages` ON (from_pgid = pgid) WHERE (`to` = 'Game console' || `to` = 'Game platform') AND namespace = 'Category' AND redirect_to = '' ORDER BY `title` $limit";
	$r = mysqli_query($GLOBALS['db']['link'], $q);
	while($row = mysqli_fetch_assoc($r)) $rows[] = $row['title'].'`'.$row['keywords'];
}

echo implode("`", $rows);

?>