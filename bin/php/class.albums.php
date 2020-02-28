<?

require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page_headers.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page_functions.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.pglinks.php";

require_once $_SERVER['DOCUMENT_ROOT']."/music/include.albums.php";

class album {
	
	public $notfound;
	
	function __construct($albumid){
		
		$this->notfound = false;
		
		$q = "SELECT * FROM albums WHERE albumid = '".mysqli_real_escape_string($GLOBALS['db']['link'], $albumid)."' LIMIT 1";
		if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
			$this->row = $row;
			foreach($row as $key => $val) $this->{$key} = $val;
			$this->url = "/music/?id=".$albumid;
			$this->full_title = $this->title . ($this->subtitle ? ' &ndash; '.$this->subtitle : '');
			$this->coverimg = "/music/media/cover/standard/".$albumid.".png";
			if(!file_exists($_SERVER['DOCUMENT_ROOT'].$this->coverimg)) unset($this->coverimg);
			else{
				$this->coverimg_tn = "/music/media/cover/thumb/".$albumid.".png";
				$this->coverimg_lg = "/music/media/cover/".$albumid.".png";
				if(!file_exists($_SERVER['DOCUMENT_ROOT'].$this->coverimg_lg)) unset($this->coverimg_lg);
			}
			$this->link = '<a href="'.$this->url.'" class="pglink albumlink">'.$this->full_title.'</a>';
		} else {
			$this->notfound = true;
		}
	}
	
}