<?php

require_once (__DIR__.'/../config/bootstrap.php');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Vgsite\User;

//login
if(isset($_POST['login'])) {

	try {
		$username = filter_input(INPUT_POST, "username");
        if (!$username) {
            throw new Exception('Username or email is required to login');
        }

        $email = '';
        if(strstr($username, "@")) {
            $email = filter_var($username, FILTER_VALIDATE_EMAIL);
            if (!$email) {
                throw new Exception("The e-mail address '$email' couldn't be validated. Please try again!");
            }
        }

        $password = filter_input(INPUT_POST, "password");
        if (!$password) {
            throw new Exception('Password is required.');
        }
		
		$user = isset($email) ? User::getByEmail($email) : User::getByUsername($username);

		if (password_verify($password, $user->data['password']) === false) {
			throw new Exception('Invalid password');
		}

		// Re-hash password if necessary
		$currentHashAlgorithm = PASSWORD_DEFAULT;
		$passwordNeedsRehash = password_needs_rehash(
			$user->data['password'],
			$currentHashAlgorithm
		);
		if ($passwordNeedsRehash === true) {
			// Save new password hash
			$user->data['password'] = password_hash(
				$password,
				$currentHashAlgorithm
			);
			$user->save();
		}

		$_SESSION['logged_in'] = 'true';
		$_SESSION['email'] = $email;

		// Everything's ok... do something now
	} catch (Exception $e) {
		
	}

	// the rest is untouched and needs to be looked at

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



// OLD

require $_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php";
$page = new page();

if($usrid) header("Location: /");

$page->title = "Log in to Videogam.in";
$page->freestyle.= '
	#loginform { font-size:14px; }
	#loginform BIG INPUT { padding:7px 0; font-size:14px; text-indent:10px; }
	#loginform INPUT[type=submit] { font-weight:bold; font-size:15px; padding:5px 15px; }
	#loginform LABEL { display:block; margin:10px 0 3px; }
';
$page->minimalist = true;
$page->header();

?>
<div style="width:225px; margin:40px auto 0;">
	
	<h1>Log in</h1>
	
	<div class="hr"></div>
	
	<a href="/login_fb.php" style="display:block;" onclick="$.cookie('lastpage', '<?=$_SERVER['HTTP_REFERER']?>', {expires:1, path:'/'})"><img src="/bin/img/fbconnect_225.png" width="225" height="33" alt="Login with Facebook"/></a>
	
	<div class="hr"></div>
	
	<form method="post" action="<?=$_SERVER['HTTP_REFERER']?>" id="loginform">
		<input type="hidden" name="do" value="login"/>
		
		<label for="login-username" style="height:0; overflow:hidden;">Username:</label>
		<big><input type="text" name="username" placeholder="Videogam.in username or e-mail" id="login-username" maxlength="25" style="width:100%"/></big>
		
		<label for="login-password" style="height:0; overflow:hidden;">Password:</label>
		<big><input type="password" name="password" placeholder="Password" id="login-password" maxlength="25" style="width:100%"/></big>
		
		<label style="float:right"><input type="submit" name="login" value="Log in"/></label>
		
		<label style="line-height:33px"><input type="checkbox" name="remember" value="1"/> Remember me</label>
		
	</form>
	
	<div class="hr"></div>
	
	<ul style="margin:20px 0 0; font-size:110%;">
		<li><a href="/retrieve-pass.php">Reset password</a></li>
		<li><a href="/register.php">Register a new account</a></li>
	</ul>

</div>
<?

$page->footer();

?>