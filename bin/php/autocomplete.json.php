<?

//Load one or more list elements and return one JSON element

require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");

$components = array();
$components = explode(",", $_GET['components']);
if(!count($components)) exit;

$ret = array();

$xpaths = array(
	"genres" => array("game", '//genre'),
	"series" => array("game", '//game_series'),
	"developers" => array("game", '//developer')
);

foreach($components as $c){
	
	if(in_array($c, array_keys($xpaths))){
		$ret[$c] = array();
		$index = simplexml_load_file($_SERVER['DOCUMENT_ROOT']."/pages/xml/index/".$xpaths[$c][0].".xml");
		$result = $index->xpath($xpaths[$c][1]);
		while(list( , $node) = each($result)){
			$row = (string)$node;
			if(strstr($row, "|")) $row = substr($row, 0, strpos($row, "|"));
			if(!in_array($row, $ret[$c])) $ret[$c][] = $row;
		}
		asort($ret[$c]);
		$ret[$c] = array_values($ret[$c]);
	
	} else {
		switch($c){
			case "platforms":
				include_once $_SERVER["DOCUMENT_ROOT"]."/pages/include.pages.php";
				$ret['platforms'] = getPlatforms();
				break;
			case "images_tags":break;
		}
	}
	
}

die(json_encode($ret));




if(strstr($what, "roles")) {
	$q = "SELECT DISTINCT(role) FROM people_work ORDER BY role";
	$r = mysql_query($q);
	while($row = mysql_fetch_assoc($r)) $rows[] = $row['role'];
}
if(strstr($what, "developers")) {
	$q = "SELECT DISTINCT(developer) FROM games_developers ORDER BY developer";
	$r = mysql_query($q);
	while($row = mysql_fetch_assoc($r)) $rows[] = $row['developer'];
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
	$result = $xml->xpath();
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
	$r = mysql_query($q);
	while($row = mysql_fetch_assoc($r)) $rows[] = $row['title']."`".$row['gid']."`/games/".$row['gid']."/".$row['title_url']."`gid:".$row['gid'];
}
if(strstr($what, "people")) { //name`pid`url`tag`title`prolific
	$q = "SELECT pid, name, name_url, title, prolific FROM people ORDER BY name";
	$r = mysql_query($q);
	while($row = mysql_fetch_assoc($r)) $rows[] = $row['name']."`".$row['pid']."`/people/".$row['pid']."/".$row['name_url']."`pid:".$row['pid']."`".htmlSC($row['title'])."`".$row['prolific'];
}
if(strstr($what, "pages_titles")) {
	$q = "SELECT `title`,`keywords` FROM `pages` WHERE redirect_to = '' ORDER BY `title` $limit";
	$r = mysql_query($q);
	while($row = mysql_fetch_assoc($r)) $rows[] = $row['title']."`".$row['keywords'];
}
if(strstr($what, "pages_game_titles")) {
	$q = "SELECT `title`,`keywords` FROM `pages` WHERE `type` = 'game' AND redirect_to = '' ORDER BY `title` $limit";
	$r = mysql_query($q);
	while($row = mysql_fetch_assoc($r)) $rows[] = $row['title']."`".$row['keywords'];
}
if(strstr($what, "pages_person_titles")) {
	$q = "SELECT `title` FROM `pages` WHERE `type` = 'person' AND redirect_to = '' ORDER BY `title` $limit";
	$r = mysql_query($q);
	while($row = mysql_fetch_assoc($r)) $rows[] = $row['title'];
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
	$r = mysql_query($q);
	while($row = mysql_fetch_assoc($r)) $rows[] = $row['title'];*/
}
if(strstr($what, "platforms")) {
	$q = "SELECT `title`,`keywords` FROM pages_links LEFT JOIN `pages` ON (from_pgid = pgid) WHERE (`to` = 'Game console' || `to` = 'Game platform') AND namespace = 'Category' AND redirect_to = '' ORDER BY `title` $limit";
	$r = mysql_query($q);
	while($row = mysql_fetch_assoc($r)) $rows[] = $row['title'].'`'.$row['keywords'];
}

echo implode("`", $rows);

?>