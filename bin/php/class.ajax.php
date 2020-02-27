<?

class ajax {
	
	public $ret = array();
	
	function __construct(){
		header("Content-type: application/json; charset=utf-8");
	}
	
	function __destruct(){
		die(json_encode($this->ret));
	}
	
	function kill($msg=''){
		if($msg != '') $this->ret['errors'][] = $msg;
		exit();
	}
	
	function error($msg='Error!'){
		$this->ret['errors'][] = $msg;
	}
	
}