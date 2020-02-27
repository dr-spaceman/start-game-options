<?
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php";

$ret = array();

$q = mysql_real_escape_string($_GET['q']);

$query = "SELECT pgid, `title`, `type`, `subcategory` FROM pages WHERE `redirect_to` = '' AND (`title` LIKE '%".$q."%' OR `keywords` LIKE '%".$q."%') ORDER BY `title` LIMIT 50";
$res = mysql_query($query);
while($row = mysql_fetch_assoc($res)){
	if($row['subcategory']) $row['type'] = $row['subcategory'];
	$ret[] = $row;
}

header("Content-type: application/json");
die(json_encode($ret));
		
$tables = array(
	"categories" => "SELECT `title` FROM pages WHERE `type` = 'category' AND `redirect_to` = '' AND (`title` LIKE '%".$q."%' OR `keywords` LIKE '%".$q."%') ORDER BY `title` LIMIT 100",
	"games" => "SELECT `title` FROM pages WHERE `type` = 'game' AND `redirect_to` = '' AND (`title` LIKE '%".$q."%' OR `keywords` LIKE '%".$q."%') ORDER BY `title` LIMIT 100",
	"characters" => "SELECT `title` FROM pages_links LEFT JOIN pages ON (pages_links.from_pgid = pages.pgid) WHERE (`to` = 'Game character') AND `namespace` = 'Category' AND `redirect_to` = '' AND (`title` LIKE '%".$q."%' OR `keywords` LIKE '%".$q."%') ORDER BY `title`",
	"locations" => "SELECT `title` FROM pages_links LEFT JOIN pages ON (pages_links.from_pgid = pages.pgid) WHERE (`to` = 'Game location') AND `namespace` = 'Category' AND `redirect_to` = '' AND (`title` LIKE '%".$q."%' OR `keywords` LIKE '%".$q."%') ORDER BY `title`",
	"publishers" => "SELECT `title` FROM pages_links LEFT JOIN pages ON (pages_links.from_pgid = pages.pgid) WHERE (`to` = 'Game publisher') AND `namespace` = 'Category' AND `redirect_to` = '' AND (`title` LIKE '%".$q."%' OR `keywords` LIKE '%".$q."%') ORDER BY `title`"
);

foreach($tables as $table => $query){
	if(stristr($_GET['_var'], $table)){
		$res = mysql_query($query);
		while($row = mysql_fetch_assoc($res)){
			$ret[] = $row['title'];
		}
	}
}

if(stristr($_GET['_var'], "platforms")){
	$query = "SELECT `title` FROM pages_links LEFT JOIN pages ON (pages_links.from_pgid = pages.pgid) WHERE (`to` = 'Game console' OR `to` = 'Game platform') AND `namespace` = 'Category' AND `redirect_to` = '' AND (`title` LIKE '%".$q."%' OR `keywords` LIKE '%".$q."%') ORDER BY `title`";
	$res = mysql_query($query);
	while($row = mysql_fetch_assoc($res)){
		if($row['title'] == "Game console") continue;
		if($row['title'] == "Handheld game console") continue;
		$ret[] = $row['title'];
	}
}
	
if(stristr($_GET['_var'], "albums")){
	$q = mysql_real_escape_string($_GET['q']);
	$query = "SELECT title, subtitle, albumid FROM albums WHERE (`title` LIKE '%".$q."%' OR `keywords` LIKE '%".$q."%' OR cid='$q') AND `view`='1' LIMIT 100";
	$res   = mysql_query($query);
	while($row = mysql_fetch_assoc($res)){
		$ret[] = $row['title'].($row['subtitle'] ? ' - '.$row['subtitle'] : '').'|AlbumID:'.$row['albumid'];
	}
}

echo implode("\n", $ret);

?>