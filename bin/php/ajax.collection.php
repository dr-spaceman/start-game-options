<?
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.ajax.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.collection.php";

$a = new ajax();

switch($_GET['_action']){
	case "fetch":
		if(!$collection_usrid = $_GET['_usrid']) $collection_usrid = $usrid;
		if(!$collection_usrid) $a->kill("No user session found");
		$query = "SELECT * FROM collection WHERE usrid='".mysql_real_escape_string($collection_usrid)."'";
		if($filter = $_GET['_filter']){
			$filter = json_decode($filter);
			foreach($filter as $key => $val) $query.=" AND `".mysql_real_escape_string($key)."`='".mysql_real_escape_string($val)."'";
		} else {
			unset($query);
		}
		if($query){
			if($_GET['_orderby']){
				$_GET['_orderby'] = str_replace("`", "", $_GET['_orderby']);
				if(strstr($_GET['_orderby'], " ")){
					$orderby_arr = explode(" ", $_GET['_orderby']);
					$orderby = " ORDER BY `".mysql_real_escape_string($orderby_arr[0])."` ".$orderby_arr[1];
				} else {
					$orderby = " ORDER BY `".mysql_real_escape_string($_GET['_orderby'])."`";
				}
			}
			$query.= $orderby . " LIMIT 0, 100";
			$res = mysql_query($query);
			while($row = mysql_fetch_assoc($res)){
				$a->ret['collection'][] = $row;
			}
		}
		exit;
		
}

exit;

?>