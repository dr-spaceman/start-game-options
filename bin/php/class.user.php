<?

/**
 * New class
 */

class User extends user_old {

	private $logged_in = false;

	const GUEST = 0;
	const RESTRICTED = 1;
	const MEMBER = 2;
	const VIP = 3;
	const TRUSTED = 4;
	const MODERATOR = 5;
	const ADMIN = 6;
	const MIDADMIN = 7;
	const HIGHADMIN = 8;
	const SUPERADMIN = 9;

	function __construct($params) {
		parent::__construct($params);
	}

	function isLoggedIn() {
		return $this->logged_in;
	}
}

class Admin extends User {

}

// Old method is below, preserved for backward compatibility
class user_old {
	
	public $notfound; //set to TRUE if after __construct user is not found
	public $id;
	public $username;
	public $rank;
	public $region;
	public $email;
	public $gender;
	public $registered;
	public $activity;
	public $previous_activity;
	public $url;
	public $data = array(); // user contribution data; call getScore()
	public $score;       // score data; call calcScore()
	//public $avatar; //comment out so it will be picked up by __set()
	public $avatar_src;
	public $preferences = array();
	
	function __construct($params){
		
		// @param $params array qualifying user [usrid, username] or string [usrid]
		
		$this->id = is_array($params) ? $params['usrid'] : (string)$params;
		
		$base_query = "SELECT usrid, username, email, gender, rank, region, avatar, registered, activity, previous_activity FROM users ";
		
		if(!$this->id && $params['username']){
			$q = $base_query . "WHERE username='".mysqli_real_escape_string($GLOBALS['db']['link'], $params['username'])."'";
			if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $q = $base_query . "WHERE username_old = '".mysqli_real_escape_string($GLOBALS['db']['link'], $params['username'])."' LIMIT 1";
			if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
				foreach($row as $k => $v){
					if($k == "usrid") $k = "id";
					$this->{$k} = $v;
				}
				$this->url = "/~".$this->username;
				return; // return now so we don't have to revalidate
			}
		}
		
		if($this->id){
			// validate & get username
			$q = $base_query . "WHERE usrid = '".mysqli_real_escape_string($GLOBALS['db']['link'], $this->id)."' LIMIT 1";
			if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
				foreach($row as $k => $v){
					if($k == "usrid") $k = "id";
					$this->{$k} = $v;
				}
				$this->url = "/~".$this->username;
			} else {
				$this->id = '';
			}
		}
		
		if(!$this->id) return $this->emptyUser();
		
	}
	
	function __set($name, $value){
		if($name == "avatar"){
			$this->setAvatar($value);
		} else $this->{$name} = $value;
	}
	
	function emptyUser(){
		$this->notfound = true;
		$this->id = 0;
		$this->username = "???";
		$this->email = "noreply@videogam.in";
		$this->rank = 0;
		$this->url = "/~".$this->username;
		$this->avatar = "unknown.png";
	}
	
	function setAvatar($value=''){
		if(!$value) $value = $this->avatar;
		$this->avatar = $value;
		$this->avatar_src['big']  = "/bin/img/avatars/".($value ? $value : 'unknown.png');
		$this->avatar_src['icon'] = "/bin/img/avatars/icon/".($value ? $value : 'unknown.png');
		
		// Check for Fb connection and get Fb avatar
		/*$res_oauth_fb = mysqli_query($GLOBALS['db']['link'], "SELECT * FROM users_oauth WHERE usrid='$this->id' AND oauth_provider = 'facebook' LIMIT 1");
		if(!$file && mysqli_num_rows($res_oauth_fb)){
			$row_oauth = mysqli_fetch_assoc($res_oauth_fb);
			$this->avatar_src['icon'] = "http://graph.facebook.com/".$row_oauth['oauth_username']."/picture";
			$this->avatar_src['big'] = $this->avatar_src['icon']."?size=large";
		}*/
	}
	
	function avatar($size="icon"){
		
		//output an avatar
		
		if($size == "big") $src = $this->avatar_src['big'];
		else $src = $this->avatar_src['icon'];
		return '<span class="useravatar '.$size.'"><img src="'.$src.'"/></span>';
		
	}
	
	function output($avatar=true, $link=true, $style=''){
		
		if(!$this->id) return '<span class="user">???</span>';
		
		$ret = '';
		if($link) $ret.= '<a href="/~'.$this->username.'" style="'.$style.'" title="'.$this->username.'\'s profile">';
		if($avatar) $ret.= $this->avatar("thumbnail");
		$ret.= '<span class="username">'.$this->username.'</span>';
		if($link) $ret.= '</a>';
		$ret = '<span class="user">'.$ret.'</span>';
		
		return $ret;
			
	}
	
	function getDetails(){
		
		// Get details from USERS_DETAILS table and set it to $this
		
		if(!$this->id) return false;
		
		$q = "SELECT * FROM users_details WHERE usrid='$this->id' LIMIT 1";
		if(!$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
			return false;
		} else {
			foreach($row as $k => $v){
				$this->{$k} = $v;
			}
			return $row;
		}
	}
	
	function getPreferences(){
		
		// Fetch user preferences into $this->preferences [array]
		
		if(!$this->id) return false;
		
		$q = "SELECT * FROM users_prefs WHERE usrid='$this->id' LIMIT 1";
		if(!$this->preferences = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
			return false;
		}
		
		return $this->preferences;
		
	}
	
	function getScore($calculate=true){
		
		//var $calculate return calculated score
		
		if(!$this->id) return false;
		
		$q = "SELECT * FROM users_data WHERE usrid = '$this->id' ORDER BY `date` DESC LIMIT 1";
		if($data_row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
			$this->data = $data_row;
			if($calculate) return $this->calculateScore($data_row);
		}
		
	}
	
	function calcScore($vars=''){
		$this->calculateScore($vars);
	}
	
	function calculateScore($vars=array(), $single_var=''){
		
		// Calculate user score
		
		// Create $score [array] [total, forums, sblogs, pages, [vars]]
		// $score[vars] [array] meta data (ie from users_data table)
		
		// @var $vars [array] given vars (so we don't need to do an exhaustive recalculation)
		// @var $single_var just calculate and return the score of a single data item
		
		if(!$this->id) return false;
		
		$this->getScore(false);
		
		$vars_queries = array(
			'num_forumposts' => "SELECT * FROM forums_posts WHERE usrid = '$this->id'",
			'num_pageedits' => "SELECT * FROM `pages_edit` WHERE `usrid` = '$this->id' AND published='1'",
			'num_ps' => "SELECT * FROM `pages` WHERE redirect_to = '' AND (`contributors` = '[$this->id]' OR `contributors` LIKE '[$this->id,%')",
			'num_ps_stolen' => "SELECT * FROM `pages_edit` WHERE redirect_to = '' AND new_ps='1'",
			'num_sblogposts' => "SELECT * FROM `posts` WHERE `usrid` = '$this->id' AND category != 'draft' AND pending != '1'",
		);
		
		if($single_var && in_array($single_var, array_keys($vars_queries))) return mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $vars_queries[$single_var]));
		
		if(!$vars){
			$vars = array();
			foreach($vars_queries as $var => $query) $vars[$var] = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query));
			$vars['contribution_score'] = $this->data['contribution_score'];
			$vars['forum_rating'] = $this->data['forum_rating'];
			$vars['sblog_rating'] = $this->data['sblog_rating'];
		}
		
		$sc = array();
		
		if($vars['num_forumposts']){
			$sc['forums'] = $vars['num_forumposts'] * .1 * ($vars['forum_rating'] > 10 ? $vars['forum_rating'] * .1 : 1);
		}
		
		if($vars['num_sblogposts']){
			$sc['sblog_mult'] =
				1 + (($vars['sblog_rating'] / $vars['num_sblogposts']) / 21); // where 1 is no ratings and 2 is all perfect ratings
			$sc['sblogs'] = $vars['num_sblogposts'] * $sc['sblog_mult'];
		}
		
		if($vars['num_pageedits']) $sc['pages'] = $vars['contribution_score'] * .1 + ($vars['num_ps'] * 2);
		
		$sc['total'] = $sc['forums'] + $sc['sblogs'] + $sc['pages'];
		$sc['vars'] = $vars;
		
		$this->score = $sc;
		return $this->score;
		
	}
	
}

function outputUser($uid = "", $avatar = TRUE, $link = TRUE, $substr = ''){
	
	$user = new user($uid);
	return $user->output($avatar, $link);
	
	//old method:
	
	if(!$uid) return "???";
	elseif(!is_numeric($uid)) return '<acronym title="ID # '.$uid.'">???</acronym>';
	else {
		$q = "SELECT * FROM users WHERE usrid='$uid' LIMIT 1";
		if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
			
			if($avatar){
				if(!$dat->avatar && $dat->oauth_provider=="facebook" && $dat->oauth_username) $o_avatar='<span class="avatar"><img src="http://graph.facebook.com/'.$dat->oauth_username.'/picture" width="20" height="20"/></span>';
				else $o_avatar = '<span class="avatar" style="background-image:url(/bin/img/avatars/tn/'.($dat->avatar ? $dat->avatar : 'unknown.png').');"></span>';
			}
			
			if($link) $ret = '<a href="/~'.$dat->username.'"'.($avatar ? ' class="user"' : '').' style="'.$style.'" title="'.$dat->username.'\'s profile">'.$o_avatar;
			
			if(is_numeric($substr) && strlen($dat->username) > $substr){
				$substr = $substr - 2;
				$ret.= substr($dat->username, 0, $substr)."&hellip;";
			} else {
				$ret.= $dat->username;
			}
			if($link) $ret.= '</a>';
			return $ret;
		} else {
			return '<acronym title="Couldn\'t get data for ID#'.$uid.'">???</acronym>';
		}
	}
	
}

function gravatar($email, $size=144, $usrid=''){
	
	//if $usrid is supplied, copy the gravar here, process, and assign it to the user
	
	$grav_url = "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=404&s=".$size;
	
	if(!$usrid) return $grav_url;
	
	$copied_file = $_SERVER['DOCUMENT_ROOT']."/bin/img/avatars/temp/".$usrid.time().".png";
	if(@copy($grav_url, $copied_file)){
		$upload_avatar_result = uploadAvatar($copied_file, "", customAvatarDir($usrid));
		if($upload_avatar_result['filename']){
			$avatar = "c/".$usrid."/".$upload_avatar_result['filename'];
			$q = "UPDATE users SET avatar='".$avatar."' WHERE usrid='$usrid' LIMIT 1";
			if(mysqli_query($GLOBALS['db']['link'], $q)) return "/bin/img/avatars/".$avatar;
		}
	} else return false;
	
}


function customAvatarDir($usrid=''){
	if(!$usrid) return '';
	return "c/".$usrid;
}

function uploadAvatar($file, $filename_body="", $dir="", $u_fullsize=true, $u_icon=true){
	
	//process an avatar image
	//@return array [error, filename]
	
	require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.upload.php";
	
	// If it's a local file, check if it exists
	// Otherwie it's an uploaded file -- check that it's a for real image
	if(is_string($file)){
		if(!file_exists($file)) return array("error" => "Couldn't find file [$file]");
	} elseif($file['name']){
		if(!$imagesize = getimagesize($file['tmp_name'])) return array("error" => "Couldn't process uploaded file (not an image?)");
	} else {
		return array("error" => "An unknown file handler error occurred.");
	}
	
	if($file['name']){
		$handle = new Upload($file);
	  if($handle->uploaded){
	  	
	  	if(!$filename_body){
	  		$filename_body = $handle->file_src_name_body;
	  	}
	  	
	  	$filename_body = substr($filename_body, 0, 40);
	  	
	  	if($u_fullsize){
	  	
		  	$handle->file_auto_rename      = false;
		  	$handle->file_overwrite        = true;
				$handle->file_new_name_body    = $filename_body;
				$handle->file_safe_name        = true;
		  	$handle->image_convert         = 'png';
		  	$handle->file_new_name_ext     = 'png';
				$handle->image_resize          = true;
				$handle->image_ratio_crop      = true;
				$handle->image_y               = 144;
				$handle->image_x               = 144;
		  	$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/img/avatars/$dir");
		    
		    if(!$handle->processed) return array("error" => 'Couldn\t upload image: ' . $handle->error);
		    else $success = true;
	    
	    }
	    
	    if($u_icon){
	    	
		  	$handle->file_auto_rename      = false;
		  	$handle->file_overwrite        = true;
				$handle->file_new_name_body    = $filename_body;
				$handle->file_safe_name        = true;
		  	$handle->image_convert         = 'png';
		  	$handle->file_new_name_ext     = 'png';
				$handle->image_resize          = true;
				$handle->image_ratio_crop      = true;
				$handle->image_y               = 48;
				$handle->image_x               = 48;
				$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/img/avatars/icon/$dir");
			
				if(!$handle->processed) return array("error" => 'Thumbnail couldn\'t be created: ' . $handle->error);
				else $success = true;
				
			}
			
			if($success) return array("filename" => $handle->file_dst_name);
			
			return array("error" => 'An upload error occurred.');
			
		} else {
			return array("error" => 'file not uploaded to the wanted location: ' . $handle->error);
		}
	}
	
	return array("error" => 'An unknown error occurred.');
	
}