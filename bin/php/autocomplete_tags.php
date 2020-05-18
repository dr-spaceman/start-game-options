<?
use Vgsite\Page;
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.ajax.php";

# var q string query
# var noincludetags boolean if true don't include recent tags (via cookies)

$q = mysqli_real_escape_string($GLOBALS['db']['link'],  $_GET['q'] );

$a = new ajax();

$results = array();

//recent tags (cookies)
if(!$_GET['noincludetags']){
	$rec_tags = array();
	if($_COOKIE['recent_tags']){
		$rec_tags = explode("|", $_COOKIE['recent_tags']);
		foreach($rec_tags as $rec_tag){
			if($rec_tag != ''){
				if($q == '' || stristr($rec_tag, $q)) $results[strtolower($rec_tag)][] = array("tag" => $rec_tag, "category" => "Tag");
			}
		}
	}
}

//If query is blank, recent tags were requested, so don't make db queries

if($q != ''){
	
	//pages
	$query = "SELECT `title`, `title_sort`, `type`, `subcategory` FROM pages WHERE `redirect_to` = '' AND (`title` LIKE '%$q%' OR `keywords` LIKE '%$q%') ORDER BY `title` LIMIT 50";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)){
		$category = $row['subcategory'] ? str_replace('Game ', '', $row['subcategory']) : $row['type'];
		$results[strtolower($row['title_sort'])][] = array("tag" => $row['title'], "category" => $category);
	}
	
	//albums
	$query = "SELECT title, subtitle, albumid FROM albums WHERE (`title` LIKE '%$q%' OR `keywords` LIKE '%$q%' OR cid='$q') AND `view`='1' LIMIT 20";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)){
		$title_sort = formatName($row['title']." ".$row['subtitle'], "sortable");
		$title_sort = strtolower($title_sort);
		$title = $row['title'].($row['subtitle'] ? ' - '.$row['subtitle'] : '');
		$label = $row['title'].($row['subtitle'] ? ' <i>'.$row['subtitle'].'</i>' : '');
		$results[$title_sort][] = array("label" => $label, "tag" => 'AlbumID:'.$row['albumid'], "category" => "album");
	}

}

ksort($results);

$res_1=array();
$res_2=array();
$q_len = strlen($q);

//Go through results and put tags that start with the query at the beginning
foreach($results as $title_sort => $arr){
	foreach($arr as $sub_arr){
		if(strtolower(substr($sub_arr['tag'], 0, $q_len)) == strtolower($_GET['q'])) $res_1[] = $sub_arr;
		else $res_2[] = $sub_arr;
	}
}

$a->ret['results'] = array_merge($res_1, $res_2);
$a->ret['num_results'] = count($a->ret['results']);

exit;





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
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)){
			$ret[] = $row['title'];
		}
	}
}

if(stristr($_GET['_var'], "platforms")){
	$query = "SELECT `title` FROM pages_links LEFT JOIN pages ON (pages_links.from_pgid = pages.pgid) WHERE (`to` = 'Game console' OR `to` = 'Game platform') AND `namespace` = 'Category' AND `redirect_to` = '' AND (`title` LIKE '%".$q."%' OR `keywords` LIKE '%".$q."%') ORDER BY `title`";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)){
		if($row['title'] == "Game console") continue;
		if($row['title'] == "Handheld game console") continue;
		$ret[] = $row['title'];
	}
}
	
if(stristr($_GET['_var'], "albums")){
	$q = mysqli_real_escape_string($GLOBALS['db']['link'], $_GET['q']);
	$query = "SELECT title, subtitle, albumid FROM albums WHERE (`title` LIKE '%".$q."%' OR `keywords` LIKE '%".$q."%' OR cid='$q') AND `view`='1' LIMIT 100";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)){
		$ret[] = $row['title'].($row['subtitle'] ? ' - '.$row['subtitle'] : '').'|AlbumID:'.$row['albumid'];
	}
}

echo implode("\n", $ret);

?>