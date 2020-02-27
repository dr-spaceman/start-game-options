<?
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";
require_once $_SERVER['DOCUMENT_ROOT']."/pages/class.pages.php";

class collection {
	public $usrid;
	
	function __construct(){
		$this->usrid = $GLOBALS['usrid'];
	}
}
?>