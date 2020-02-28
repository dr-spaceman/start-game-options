<?
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";
require_once $_SERVER['DOCUMENT_ROOT']."/pages/class.pages.php";
require_once $_SERVER['DOCUMENT_ROOT']."/pages/class.pages.edit.php";

$num_handle = 35; // number of pages to handle at once

$spIndexes = array_keys($pgsubcategories);

$az = array("0","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");

if(!$type = $_GET['_type']) die("No index type given");

$rebuild_tables = $_GET['_rebuildtables'] ? true : false;

$query = "SELECT title FROM pages WHERE `type` = '$type' AND redirect_to = ''";
$num_rows = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query));

if(isset($_GET['_min'])){
	
	$min = $_GET['_min'];
	
	//making one big index for each type
	/*$file = $_SERVER['DOCUMENT_ROOT']."/pages/xml/index/".$type.".xml";
	
	if($min < 1){
		$in = simplexml_load_string('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><index></index>');
		$in = $in->addChild('index');
		$in->addAttribute("type", $type);
		
		//json index
		$q = "DELETE FROM pages_index_json WHERE `type` = '$type'";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Error on : $q");
		$q = "INSERT INTO pages_index_json (`type`, `json`) VALUES ('$type', '');";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Error on : $q");
		$json_blob = array();
		
	} else {
		$f = file_get_contents($file);
		$in = simplexml_load_string($f);
		
		$q = "SELECT `json` FROM pages_index_json WHERE `type`='$type' LIMIT 1";
		if(!$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) die("Error on : $q");
		$json_blob = json_decode($row['json'], true);
	}*/
	
	if($min < 1){
		
		$current_ia = '';
		
		//json index
		$q = "DELETE FROM pages_index_json WHERE `type` = '$type'";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Error on : $q");
		$q = "INSERT INTO pages_index_json (`type`, `letter`) VALUES ";
		foreach($az as $a) $q.= "('$type', '$a'),";
		$q = substr($q, 0, -1);
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Error on : $q");
		
	}
	
	$query.= " ORDER BY title_sort LIMIT ".($min ? $min - 2 : $min).", $num_handle;";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	if(!mysqli_num_rows($res)){
		die("Fin.".($rebuild_tables ? " Tables rebuilt." : ""));
	}
	while($row = mysqli_fetch_assoc($res)){
		
		$pg = new pgedit($row['title']);
		$pg->loadData();
		
		$title_sort = $pg->row->title_sort ? $pg->row->title_sort : formatName($pg->title, "sortable");
		$title_sort = strtolower($title_sort);
		$ia = substr($title_sort, 0, 1);
		if(!preg_match("/[a-z]/i", $ia)) $ia = "0";
		
		if($ia != $current_ia){
			
		}
		
		// Modify credits structure 2011-03-05
		//include("build_mod_credits.incl.php");
		
		// Categorize publication platform links 2011-03-09
		//include("build_mod_pubpflinks.incl.php");
		
		// Extract data from Description 2011-03-09
		//include("build_mod_extrdata.incl.php");
		
		// Set `subcategory` field 2011-05-31
		//include($_SERVER['DOCUMENT_ROOT']."/bin/php/execute/build_mod_subcategory.incl.php");
		
		// Set title_sort field 2012-03-12
		include($_SERVER['DOCUMENT_ROOT']."/bin/php/execute/build_mod_title_sort.incl.php");
		
		if($rebuild_tables && $pg->type == "game") include "pages_index_buildinclude.games.php"; //handles db tables (games_publications, credits)
		
		list($in, $json) = $pg->buildIndexRow($in);
		
		$json_index[$ia][$pg->title] = $json;
		
		$json_ = $json;
		unset($json_['keywords']);
		unset($json_['description']);
		unset($json_['rep_image']);
		unset($json_['categories']);
		$q = "UPDATE pages SET index_data = '".mysqli_real_escape_string($GLOBALS['db']['link'], json_encode($json_))."' WHERE pgid='".$pg->pgid."' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Couldn't update index_data field on pages database; ".mysql_error());
		
	}
	
	if(count($json_index)){
		foreach($json_index as $ia => $json){
			$q = "SELECT `json` FROM pages_index_json WHERE `type` = '$type' AND `letter` = '$ia' LIMIT 1";
			$json_row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
			$json_ = (array)json_decode($json_row['json']);
			$json_arr = array_merge($json_, $json);
			$json_str = json_encode($json_arr);
			$q = "UPDATE pages_index_json SET `json` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $json_str)."' WHERE `type` = '$type' AND `letter` = '$ia' LIMIT 1";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Error on : $q");
		}
	}
	
	/*$dom = new DOMDocument('1.0', 'UTF-8');
	$dom->xmlStandalone = false;
	$dom->preserveWhiteSpace = false;
	$dom->loadXML($in->asXML());
	$dom->formatOutput = true;
	if(!$dom->save($file)) die("Error saving index file :(");*/
	
	exit;
	
}

echo $html_tag;
?>
<head>
	<script type="text/javascript" src="/bin/script/jquery.js"></script>
	<script type="text/javascript">
	$(document).ready(function(){
		
		var it,
				lm=0,
				min,
				pc;
		
		function handleIndex(){
			min = lm * <?=$num_handle?>;
			lm++;
			pc = (min / <?=$num_rows?>) * 100;
			$("body").html('<div style="width:400px; overflow:hidden; position:relative; margin:0 0 5px; padding:2px 5px; border:1px solid #CCC; background-color:white;"><span style="position:relative; z-index:2;">Building <b><?=$type?></b> index</span><div style="position:absolute; z-index:0; top:0; left:0; width:'+pc+'%; height:100px; background-color:#D5EBFF;"></div></div>');
			$.get(
				"/bin/php/pages_index_build.include.php",
				{ _type:"<?=$type?>",
				  _min:min,
				  _rebuildtables:"<?=$rebuild_tables?>" },
				function(ret){
					if(ret){
						$("body").append('<br/>&gt;&gt; '+ret);
						if(ret == "Fin."){
							<?=($_GET['_onFin'] ? 'parent.'.$_GET['_onFin'].'();' : '')?>
						}
					} else handleIndex();
				}
			)
		}
		
		handleIndex();
		
	})
	</script>
</head>
<body style="margin:0; padding:0; background:transparent; font-size:13px; font-family:arial;">Initializing...</body>
</html>