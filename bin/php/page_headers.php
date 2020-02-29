<?
ini_set("error_reporting", 6135);
require "db.php";
require "page_functions.php";
require "bbcode.php";
require "class.user.php";

$default_email = "mat.berti@gmail.com";

//$betatesters = array("Matt", "Matt2", "Andrew", "Alex", "Nels", "Kanji");

$errors   = array();
$warnings = array();
$results  = array();

$html_tag = '<!DOCTYPE html>
<html dir="ltr" lang="en-US" xmlns:fb="http://www.facebook.com/2008/fbml">';
$root = $_SERVER['DOCUMENT_ROOT'];

session_set_cookie_params(6000);
session_start();

$usrid   = null;
$usrname = null;
$usrrank = 0;

//set login vars
if(isset($_SESSION['usrname'])){
	
	$usrname = $_SESSION['usrname'];
	$usrid = $_SESSION['usrid'];
	$usrrank = base64_decode($_SESSION['usrkey']);
	$usrlastlogin = $_SESSION['usrlastlogin'];

} else {
	
	if(isset($_COOKIE['usrsession'])){
		
		//login user from remembered cookie
		
		$usrsession = base64_decode($_COOKIE['usrsession']);
		list($usrid_, $password_) = explode("```", $usrsession);
		$q = sprintf(
			"SELECT * FROM users WHERE usrid='%s' AND password=PASSWORD('%s') LIMIT 1",
			mysqli_real_escape_string($GLOBALS['db']['link'], $usrid_),
			mysqli_real_escape_string($GLOBALS['db']['link'], $password_)
		);
		if($res = mysqli_query($GLOBALS['db']['link'], $q)) {
			$userdat = mysqli_fetch_assoc($GLOBALS['db']['link'], $res);
			login($userdat);
		}
	
	}
}

//login
if(isset($_POST['do']) && $_POST['do'] == "login" && isset($_POST['username'])) {
	
	if(strstr($_POST['username'], "@")){
		//an email address was given
		$query = sprintf(
			"SELECT * FROM `users` WHERE `email` = '%s' AND `password` = password('%s') LIMIT 1",
			mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['username']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['password'])
		);
	} else {
		//a username was given
		$query = sprintf(
			"SELECT * FROM `users` WHERE `username` = '%s' AND `password` = password('%s') LIMIT 1",
			mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['username']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['password'])
		);
	}
	
	if($userdat = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $query))) {
		
		login($userdat);
		
		//remember?
		if($_POST['remember']) {
			// time()+60*60*24*100 = 100 days
			$usrsession = $_SESSION['usrid']."```".$_POST['password'];
			$usrsession = base64_encode($usrsession);
			setcookie("usrsession", $usrsession, time()+60*60*24*100, "/");
		}
		
		if($_POST['ajax_login']) {
			die("ok");
		} else {
			?><?=$html_tag?>
		  <head>
		    <title>Logging in <?=$_SESSION['usrname']?></title>
		    <link rel="stylesheet" type="text/css" href="/bin/css/screen.css"/>
		    <? if(!$GLOBALS['no_oauth']){ ?>
		    <meta http-equiv="REFRESH" content="0; url=<?=$_SERVER['REQUEST_URI']?>" />
		  	<? } else { ?>
		    <script type="text/javascript" src="/bin/script/jquery.js"></script>
				<script>
					$(['/bin/img/fb_connect_160.png', '/bin/img/twitter_connect_160.png', '/bin/img/steam_connect.png']).each(function(){ $('<img/>')[0].src = this });
					$(document).ready(function(){
						$("a").click(function(){
							window.location = '<?=$_SERVER['REQUEST_URI']?>';
						});
					});
				</script>
				<? } ?>
		  </head>
			<body>
			<div id="body-message"><?
				if(!$GLOBALS['no_oauth']) echo '<big style="font-size:21px; padding-right:30px; background:url(/bin/img/icons/emoticons/_goomba.gif) no-repeat right center;">Logging in</big>';
				else {
					?>
					<div class="container" style="text-align:center; padding:3em 2em; font-size:14px;">
						Connect to your social networks to share your Videogam.in activity!<br/><br/>
						<a href="/login_fb.php" target="_blank"><img src="/bin/img/fb_connect_160.png" border="0" alt="connect with Facebook"/></a><br/><br/>
						<a href="/login_steam.php" target="_blank"><img src="/bin/img/steam_connect.png" border="0" alt="connect with Steam"/></a><br/><br/>
						<a href="/bin/php/twitter/connect.php" target="_blank"><img src="/bin/img/twitter_connect_160.png" border="0" alt="connect with Twiter"/></a><br/><br/>
						<a>No thanks.</a>
					</div>
					<?
				}
				?></div>
			</body>
		  </html>
		  <?
			exit;
		}
		
	} else {
		
		// failed login
		
		setcookie(session_name(), '', time()-42000, '/');
		setcookie("usrsession", "", time()-60*60*24*100, "/");
		unset($_SESSION['usrname']);
		unset($_SESSION['usrid']);
		unset($_SESSION['usrrank']);
		unset($_SESSION['usrlastlogin']);
		session_destroy();
		
		if($_POST['ajax_login']) {
			die("error");
		} else {
			?><?=$html_tag?>
			<head>
				<title>Error logging in</title>
				<link rel="stylesheet" type="text/css" href="/bin/css/screen.css" />
			</head>
			<body>
			<div id="body-message"><b>Error logging in!</b> Please try again:<br />
				<form method="post" action="<?=$_SERVER['REQUEST_URI']?>">
			    <input type="hidden" name="do" value="login">
			    <table border="0" cellpadding="5" cellspacing="0">
			    	<tr>
			    		<td>Username:</td>
			    		<td><input type="text" name="username" size="13" maxlength="25"/></td>
			    	</tr>
			    	<tr>
			    		<td>Password:</td>
			    		<td><input type="password" name="password" size="13" maxlength="16"/></td>
			    	</tr>
			    	<tr>
			    		<td colspan="2"><input type="submit" name="login" value="Login"/> &nbsp; <a href="/retrieve-pass.php">Retrieve password</a> | <a href="/register.php">Register</a></td>
			    	</tr>
			    </table>
		  	</form>
		  </div>
			</body>
			</html><?
			exit;
		}
	}
}

//logout
if(isset($_GET['do']) && $_GET['do'] == "logout") {
	setcookie(session_name(), '', time()-42000, '/');
	setcookie("usrsession", "", time()-60*60*24*100, "/");
	unset($_SESSION['usrname']);
	unset($_SESSION['usrid']);
	unset($_SESSION['usrrank']);
	unset($_SESSION['usrlastlogin']);
	session_destroy();
	?><?=$html_tag?>
    <head>
    <title>Redirecting....</title>
    <link rel="stylesheet" type="text/css" href="/bin/css/screen.css" />
    <meta http-equiv="REFRESH" content="1; url=.">
    </head>
		<body>
			<div id="body-message">
				<big style="font-size:21px; padding-right:30px; background:url(/bin/img/icons/emoticons/_goomba.gif) no-repeat 100% 50%;">Logging Out</big>
			</div>
		</body>
    </html>
  <?
	exit();
}

function login($userdat){
	
	global $usrname, $usrid, $usrrank, $usrlastlogin, $fbuser, $errors;
	
	if(!$_SESSION['usrname'] = $userdat['username']) $errors[] = "Couldn't set session variable 'usrname'.";
	if(!$_SESSION['usrid'] = $userdat['usrid']) $errors[] = "Couldn't set session variable 'usrid'.";
	if(!$_SESSION['usrkey'] = base64_encode($userdat['rank'])) $errors[] = "Couldn't set user rank variable.";
	$_SESSION['usrlastlogin'] = $userdat['previous_activity'];
	
	if($errors) return false;
	
	$usrname = $_SESSION['usrname'];
	$usrid = $_SESSION['usrid'];
	$usrrank = base64_decode($_SESSION['usrkey']);
	$usrlastlogin = $_SESSION['usrlastlogin'];
	
	$_SESSION['newbadges'] = newBadges();
	
	updateActivity();
	
	//fb login
	$check_1 = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM users_oauth WHERE usrid='$usrid' AND oauth_provider='facebook' LIMIT 1"));
	$check_2 = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM users_oauth WHERE usrid='$usrid' LIMIT 1"));
	if(!$fbuser && $check_1) {
		require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/fb/src/facebook.php";
		$fb = array(
		  'appId'  => '142628175764082',
		  'secret' => '5913f988087cecedd1965a3ed6e91eb1'
		);
		$facebook = new Facebook($fb);
		$fbuser = $facebook->getUser();
		if ($fbuser) {
		  try {
		    // Proceed knowing you have a logged in user who's authenticated.
		    $fbuser_data = $facebook->api('/me');
		  } catch (FacebookApiException $e) {
		    error_log($e);
		    $fbuser = null;
		  }
		}
	/*} elseif(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q)) && !mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q2)) && $_COOKIE['no_oauth'] != "ignore" && $userdat['registered'] != $userdat['activity']){
		setcookie("no_oauth", "1", time()+60*60*24*100, "/");*/
	} elseif(!$check_1 && !$check_2 && $userdat['registered'] != $userdat['activity']){
		$GLOBALS['no_oauth'] = true;
	}
	
	return true;
	
}

function updateActivity(){
	
	global $usrid;
	
	$u = new user($usrid);
	$u->getDetails(); //$dob for birthday badge
	
	//update activity
	$query = "UPDATE users SET activity='".date("Y-m-d H:i:s")."', previous_activity='".$u->activity."' WHERE usrid='".$usrid."' LIMIT 1";
	mysqli_query($GLOBALS['db']['link'], $query);
	
	//record current scores and counts
	$query = "SELECT * FROM users_data WHERE usrid = '".$usrid."' AND `date` = '".date("Y-m-d")."' LIMIT 1";
	if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query))) {
		
		$u->calculateScore(); //recalculate score
		
		if($u->score['total'] >= 1){
			$q2 = "INSERT INTO users_data (usrid, `date`, ".implode(", ", array_keys($u->score['vars'])).", score_forums, score_pages, score_sblogs, score_total) VALUES 
				('$usrid', '".date("Y-m-d")."', '".implode("', '", array_values($u->score['vars']))."', '".$u->score['forums']."', '".$u->score['pages']."', '".$u->score['sblogs']."', '".$u->score['total']."');";
			mysqli_query($GLOBALS['db']['link'], $q2);
		}
		
		$q2 = "UPDATE users SET 
			score_forums = '".$u->score['forums']."',
			score_pages = '".$u->score['pages']."',
			score_sblogs = '".$u->score['sblogs']."',
			score_total = '".$u->score['total']."'
			WHERE usrid = '$usrid' LIMIT 1";
		mysqli_query($GLOBALS['db']['link'], $q2);
		
	}
	
	//badges
	//check birthday
	$dob = str_replace("-", "", $u->dob);
	$dob = substr($dob, 4);
	if($dob == date("md")){
		require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.badges.php";
		$_badges = new badges();
		$_badges->earn(37);
	}
	
}

function newBadges(){
	
	// check for new badges earned since last login
	// return array badge IDs
	
	$new = array();
	
	$query = "SELECT * FROM badges_earned WHERE usrid = '".$GLOBALS['usrid']."' AND `new` = '1';";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	while ($row = mysqli_fetch_assoc($res)) {
		$new[] = $row['bid'];
	}
	
	return $new;
	
}
	
?>