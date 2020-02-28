<?php
/**
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/fb/src/config.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/fb/src/facebook.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";

// Create our Application instance
$fb = array(
  'appId'  => FB_APPID,
  'secret' => FB_SECRET
);
$facebook = new Facebook($fb);

// Get User ID
$fbuser = $facebook->getUser();

// We may or may not have this data based on whether the user is logged in.
//
// If we have a $fbuser id here, it means we know the user is logged into
// Facebook, but we don't know if the access token is valid. An access
// token is invalid if the user logged out of Facebook.

if ($fbuser) {
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $fbuser_data = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    error_log($e);
    $fbuser = null;
  }
}

// Login or logout url will be needed depending on current user state.
if(!$fbuser){
  $loginUrl = $facebook->getLoginUrl(array("scope"=>"email,user_birthday,publish_stream"));
	header("Location:".$loginUrl);
} else {
	
	$oauth_token = $facebook->getAccessToken();
  
  // we should have just been authenticated
  // use the given data to check local user data
	
	//debug
	//echo "<pre>Token: $oauth_token \n\n"; print_r($fbuser_data);exit;
	
  //$logoutUrl = $facebook->getLogoutUrl();
  
  // automatically reconcole existing Videogamin user account with Fb account
  if($usrid){
  	
  	//Remove all rows to avoid duplicates and get fresh data incase of re-authorization
  	$q = "DELETE FROM users_oauth WHERE usrid='$usrid' AND oauth_provider='facebook'";
  	mysqli_query($GLOBALS['db']['link'], $q);
  	
  	//fresh data
  	$q = "INSERT INTO users_oauth (usrid, oauth_provider, oauth_usrid, oauth_username, oauth_token) VALUES ('$usrid', 'facebook', '".mysqli_real_escape_string($GLOBALS['db']['link'], $fbuser_data['id'])."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $fbuser_data['username'])."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $oauth_token)."');";
		if(!mysqli_query($GLOBALS['db']['link'], $q)){
			sendBug("login_fb.php Error reconciling Vg.in account with Fb acct [$q]: ".mysqli_error($GLOBALS['db']['link']));
			die("Sorry, there was a database error and we couldn't link your Facebook account to your Videogam.in account. Refresh to try again, or <a href=\"".($_COOKIE['lastpage'] ? $_COOKIE['lastpage'] : "http://videogam.in")."\">go back to Videogam.in</a>");
		}
		header("Location:".($_COOKIE['lastpage'] ? $_COOKIE['lastpage'] : "http://videogam.in/account.php?edit=prefs&fbconnectedsuccess=1"));
  	exit;
  }
  $q = "SELECT * FROM users WHERE email='".mysqli_real_escape_string($GLOBALS['db']['link'], $fbuser_data['email'])."' LIMIT 1";
  if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
  	$q = "SELECT * FROM users_oauth WHERE usrid='$row[usrid]' AND oauth_provider='facebook' LIMIT 1";
  	$res = mysqli_query($GLOBALS['db']['link'], $q);
  	if(!mysqli_num_rows($res)){
  		$q = "INSERT INTO users_oauth (usrid, oauth_provider, oauth_usrid, oauth_username) VALUES ('$row[usrid]', 'facebook', '".mysqli_real_escape_string($GLOBALS['db']['link'], $fbuser_data['id'])."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $fbuser_data['username'])."');";
			if(!mysqli_query($GLOBALS['db']['link'], $q)){
				sendBug("login_fb.php Error reconciling Vg.in account with Fb acct [$q]: ".mysqli_error($GLOBALS['db']['link']));
			}
		}
  	login($row);
  	header("Location:".($_COOKIE['lastpage'] ? $_COOKIE['lastpage'] : "http://videogam.in"));
  	exit;
  }
  
  // login existing Fb user
  $q = "SELECT * FROM users_oauth LEFT JOIN users USING (usrid) WHERE oauth_provider = 'facebook' AND oauth_usrid='".mysqli_real_escape_string($GLOBALS['db']['link'], $fbuser_data['id'])."' LIMIT 1";
  if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
  	login($row);
  	header("Location:".($_COOKIE['lastpage'] ? $_COOKIE['lastpage'] : "http://videogam.in"));
  	exit;
  }
  
  // New user :D
  
  $usrname = $fbuser_data['username'];
  if(!$usrname && $fbuser_data['email']) $usrname = @strstr($fbuser_data['email'], '@', true);
  if(!$usrname) $usrname = "user".mysqlNextAutoIncrement("users", 1);
  $i = 0;
  while(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT username FROM users WHERE username = '".mysqli_real_escape_string($GLOBALS['db']['link'], $usrname)."' LIMIT 1"))){
  	$i++;
  	if($i > 1) $usrname = substr($usrname, 0, -1);
  	$usrname.= $i;
  	if($i == 100) break;
  }
  
  //generate new password
  $new_password = '';
	$possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ$%#@!&^$%#@!&^$%#@!&^";
	$maxlength = strlen($possible);
	for($i=0; $i < 6; $i++) $new_password.= substr($possible, mt_rand(0, $maxlength-1), 1);
	
	$avatar = gravatar(
	
	$tod = date("Y-m-d H:i:s");
	$q = "INSERT INTO users (`username`,`password`,`email`,`registered`,`activity`,`previous_activity`,`gender`,avatar) VALUES 
		('".mysqli_real_escape_string($GLOBALS['db']['link'], $usrname)."', password('$new_password'), '".$fbuser_data['email']."', '$tod', '$tod', '$tod', '".mysqli_real_escape_string($GLOBALS['db']['link'], $fbuser_data['gender'])."', '$avatar');";
	$usrid = mysqlNextAutoIncrement("users");
	if(!mysqli_query($GLOBALS['db']['link'], $q)){
		sendBug("login_fb.php Couldn't register via Fb because of a Mysql error [$q]: ".mysqli_error($GLOBALS['db']['link']));
		die('Sorry, there was a database error and we couldn\'t register you as a new user. Please try <a href="/register.php">our alternate registration form</a>.');
	}
	$q = "INSERT INTO users_oauth (usrid,oauth_provider,oauth_usrid,oauth_username) VALUES ('$usrid', 'facebook', '".mysqli_real_escape_string($GLOBALS['db']['link'], $fbuser_data['id'])."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $fbuser_data['username'])."');";
	if(!mysqli_query($GLOBALS['db']['link'], $q)){
		sendBug("login_fb.php Couldn't register via Fb because of a Mysql error [$q]: ".mysqli_error($GLOBALS['db']['link']));
		die('Sorry, there was a database error and we couldn\'t register you as a new user. Please try <a href="/register.php">our alternate registration form</a>.');
	}
	
	$query = "INSERT INTO `users_prefs` (usrid) VALUES ('$usrid')";
	if(!mysqli_query($GLOBALS['db']['link'], $query)) sendBug("Could not INSERT into `users_prefs` table [$query] (/login_fb.php)");
	
	$dob = '';
	if($fbuser_data['birthday']){
		$dob_stamp = strtotime($fbuser_data['birthday']);
		$dob = date("Y-m-d", $dob_stamp);
	}
	$query = "INSERT INTO users_details (usrid,`name`,`time_zone`,`dob`,homepage) VALUES ('$usrid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $fbuser_data['name'])."', '".$fbuser_data['timezone']."', '$dob', '".$fbuser_data['link']."');";
	if(!mysqli_query($GLOBALS['db']['link'], $query)) sendBug("Could not INSERT into `users_details` table [$query] (/login_fb.php)");
	
	if($fbuser_data['email']){
		$message = file_get_contents($_SERVER['DOCUMENT_ROOT']."/bin/incl/welcome_message.htm");
	  $message = str_replace("%s", $usrname, $message);
	  $message = str_replace("Dear $usrname", "Dear ".$fbuser_data['name'], $message);
	  $message = str_replace("</body>", "<p>P.S. Your new Videogam.in site password is <b>$new_password</b>. You can always log in with Facebook though.</p></body>", $message);
		$headers  = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
		'To: '.$fbuser_data['name'].' <'.$fbuser_data['email'].'>' . "\r\n" . 
		'From: Videogam.in <Luigi@videogam.in>' . "\r\n";
		@mail($fbuser_data['email'], "Welcome to Videogam.in", $message, $headers);
	}
	
	// import avatar
	/*require $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.upload.php";
	$avatar_url = "http://graph.facebook.com/$fbuser/picture?return_ssl_resources=1";
	$avatar_tn_url = "http://graph.facebook.com/$fbuser/picture";
	if($avatar = file_get_contents($avatar_url)){
		$handle = new Upload($avatar);
	  if($handle->uploaded){
	  	$handle->file_auto_rename      = false;
	  	$handle->file_overwrite        = true;
			$handle->file_new_name_body    = $usrid;
			$handle->file_safe_name        = false;
	  	$handle->image_convert         = 'png';
	  	$handle->file_new_name_ext     = 'png';
			$handle->image_resize          = true;
			$handle->image_ratio_crop      = true;
			$handle->image_y               = 150;
			$handle->image_x               = 135;
	  	$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/img/avatars/custom/");
	  	if($handle->processed) mysqli_query($GLOBALS['db']['link'], "UPDATE users SET avatar='custom/".$usrid.".png' WHERE usrid='$usrid' LIMIT 1");
	  }
	}
	if($avatar_tn = file_get_contents($avatar_tn_url)){
		$handle = new Upload($avatar_tn);
	  if($handle->uploaded){
  		$handle->file_auto_rename      = false;
  		$handle->file_overwrite        = true;
			$handle->file_new_name_body    = $usrid;
			$handle->file_safe_name        = false;
			$handle->image_convert         = 'png';
  		$handle->file_new_name_ext     = 'png';
			$handle->image_resize          = true;
			$handle->image_ratio_crop      = true;
			$handle->image_y               = 20;
			$handle->image_x               = 20;
			$handle->image_watermark_y     = 0;
			$handle->image_watermark_x     = 0;
			$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/img/avatars/tn/custom/");
			if($handle->processed) $avatar_tn = $handle->file_dst_name;
		}
	}*/
	
	$usrrank = 2;
	$usrlastlogin = $tod;
	login(array("username" => $usrname, "usrid" => $usrid, "rank" => $usrrank, "previous_activity" => $usrlastlogin));
	
	require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.badges.php";
	$_badges = new badges();
	$_badges->earn(1);
	
	header("Location:".($_COOKIE['lastpage'] ? $_COOKIE['lastpage'] : "http://videogam.in"));
	exit;
  
}

// This call will always work since we are fetching public data.
//$naitik = $facebook->api('/naitik');

?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>php-sdk</title>
    <style>
      body {
        font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
      }
      h1 a {
        text-decoration: none;
        color: #3b5998;
      }
      h1 a:hover {
        text-decoration: underline;
      }
    </style>
  </head>
  <body>
    <h1>php-sdk</h1>

    <?php if ($fbuser): ?>
      <a href="<?php echo $logoutUrl; ?>">Logout</a>
    <?php else: ?>
      <div>
        Login using OAuth 2.0 handled by the PHP SDK:
        <a href="<?php echo $loginUrl; ?>">Login with Facebook</a>
      </div>
    <?php endif ?>

    <h3>PHP Session</h3>
    <pre><?php print_r($_SESSION); ?></pre>

    <?php if ($fbuser): ?>
      <h3>You</h3>
      <img src="https://graph.facebook.com/<?php echo $fbuser; ?>/picture">

      <h3>Your User Object (/me)</h3>
      <pre><?php print_r($fbuser_data); ?></pre>
    <?php else: ?>
      <strong><em>You are not Connected.</em></strong>
    <?php endif ?>

    <h3>Public profile of Naitik</h3>
    <img src="https://graph.facebook.com/naitik/picture">
    <?php echo $naitik['name']; ?>
  </body>
</html>
