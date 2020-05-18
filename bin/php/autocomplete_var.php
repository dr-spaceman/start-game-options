<?

// Autocomplete a wide variety of data
// return categorized arrays with only titles and tags (if applicable [ie Album])

use Vgsite\Page;
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.ajax.php";

$q = mysqli_real_escape_string($GLOBALS['db']['link'], $_GET['q']);

$a = new ajax();
$results = array();

$tables = array(
	"categories" => buildIndexQuery('category'),
	"games"      => buildIndexQuery('game'),
	"people"     => buildIndexQuery('person'),
	"topics"     => buildIndexQuery('topic'),
	"characters" => buildIndexQuery('Game character', 'category'),
	"locations"  => buildIndexQuery('Game location', 'category'),
	"genres"     => buildIndexQuery('Game genre', 'category'),
	"publishers" => buildIndexQuery('Game publisher', 'category'),
	"developers" => buildIndexQuery('Game developer', 'category'),
	"series"     => buildIndexQuery('Game series', 'category'),
	"roles"      => buildIndexQuery('Game development role', 'category'),
);

function buildIndexQuery($field, $parent=''){
	global $q;
	if($parent == "category"){
		return "SELECT `title`, title_sort FROM pages_links LEFT JOIN pages ON (pages_links.from_pgid = pages.pgid) WHERE (`to` = '$field') AND `namespace` = 'Category' AND `redirect_to` = '' AND (`title` LIKE '%".$q."%' OR `keywords` LIKE '%".$q."%') ORDER BY `title_sort` LIMIT 100";
	} else {
		return "SELECT `title`, title_sort FROM pages WHERE `type` = '$field' AND `redirect_to` = '' AND (`title` LIKE '%".$q."%' OR `keywords` LIKE '%".$q."%') ORDER BY `title_sort` LIMIT 100";
	}
}

foreach($tables as $table => $query){
	if(stristr($_GET['var'], $table)){
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)){
			$title_sort = strtolower($row['title_sort']);
			$results[$title_sort][] = array("title" => $row['title']);
		}
	}
}

if(stristr($_GET['var'], "platforms")){
	include_once $_SERVER["DOCUMENT_ROOT"]."/pages/include.pages.php";
	foreach(getPlatforms(1) as $pf) $results[strtolower($pf['title_sort'])][] = array("title" => $pf['title']);
}
	
if(stristr($_GET['var'], "albums")){
	$query = "SELECT title, subtitle, albumid FROM albums WHERE (`title` LIKE '%".$q."%' OR `keywords` LIKE '%".$q."%' OR cid='$q') AND `view`='1' LIMIT 100";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)){
		$title_sort = formatName($row['title']." ".$row['subtitle'], "sortable");
		$title_sort = strtolower($title_sort);
		$results[$title_sort][] = array("title" => $row['title'].($row['subtitle'] ? ' - '.$row['subtitle'] : ''), "tag" => 'AlbumID:'.$row['albumid']);
	}
}

if(!$a->ret['num_results'] = count($results)){
	exit;
}

ksort($results);

$res_1=array();
$res_2=array();
$q_len = strlen($q);

//Go through results and put tags that start with the query at the beginning
foreach($results as $title_sort => $arr){
	foreach($arr as $sub_arr){
		if(strtolower(substr($sub_arr['title'], 0, $q_len)) == strtolower($_GET['q'])) $res_1[] = $sub_arr;
		else $res_2[] = $sub_arr;
	}
}

$a->ret['results'] = array_merge($res_1, $res_2);

exit;

?>