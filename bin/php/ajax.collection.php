<?
use Vgsite\Page;
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.ajax.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.collection.php";

$a = new ajax();

switch($_GET['_action']){
	case "fetch":
		if(!$collection_usrid = $_GET['_usrid']) $collection_usrid = $usrid;
		if(!$collection_usrid) $a->kill("No user session found");
		$query = "SELECT * FROM collection WHERE usrid='".mysqli_real_escape_string($GLOBALS['db']['link'], $collection_usrid)."'";
		if($filter = $_GET['_filter']){
			$filter = json_decode($filter);
			foreach($filter as $key => $val) $query.=" AND `".mysqli_real_escape_string($GLOBALS['db']['link'], $key)."`='".mysqli_real_escape_string($GLOBALS['db']['link'], $val)."'";
		} else {
			unset($query);
		}
		if($query){
			if($_GET['_orderby']){
				$_GET['_orderby'] = str_replace("`", "", $_GET['_orderby']);
				if(strstr($_GET['_orderby'], " ")){
					$orderby_arr = explode(" ", $_GET['_orderby']);
					$orderby = " ORDER BY `".mysqli_real_escape_string($GLOBALS['db']['link'], $orderby_arr[0])."` ".$orderby_arr[1];
				} else {
					$orderby = " ORDER BY `".mysqli_real_escape_string($GLOBALS['db']['link'], $_GET['_orderby'])."`";
				}
			}
			$query.= $orderby . " LIMIT 0, 100";
			$res = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)){
				$a->ret['collection'][] = $row;
			}
		}
		exit;
		
}

exit;

?>