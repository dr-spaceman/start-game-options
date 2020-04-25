<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");

$page = new page;
$page->title = "Videogam.in / Register";
$page->css[] = "/bin/css/register.css";

if($_POST['do'] == "ajaxreg") {
	
    // 2020-03-29 What's happening here ????????????????????????????????????????????
	// REGISTRATION VIA AJAX (ie, forum post)
	
	$un = trim($_POST['un']);
	$pw = trim($_POST['pw']);
	$em = trim($_POST['em']);
	
	if (!$un) die("Error: No username given");
	if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL($em)) die("Error: e-mail address ($em) is not valid");
	
	//valid username?echo "blah";
 // if(preg_match('/[^a-zA-Z0-9-_]/', $un)) die("Error: Illegal characters in username (only letters, numbers, -, and _)");
  
  //valid pass?
  //if($pw && preg_match('/[^a-zA-Z0-9]/', $pw)) die("Illegal characters in password (only letters and numbers)");
  
  //Check if username is already registered
  $Query = "SELECT username FROM users WHERE username = '".mysqli_real_escape_string($GLOBALS['db']['link'], $un)."'"; 
  $Result = mysqli_query($GLOBALS['db']['link'], $Query);
  if(mysqli_num_rows($Result)) die("Error: the username '".$un."' has already been registered. Please choose a different username.");
  
  //Check if email address is already registered
  $Query = "SELECT email FROM users WHERE email = '".mysqli_real_escape_string($GLOBALS['db']['link'], $em)."'"; 
  $Result = mysqli_query($GLOBALS['db']['link'], $Query);
	if(mysqli_num_rows($Result)) die("Error: The email address '".$em."' is already registered. Please log in using your username/password combination.");
	
	// REGISTER HERE //
	
	die("ok");
	
}

if($_GET['verify']) {
	
	$page->title = "Videogam.in / Verify Email";
	$page->header();
	$user = base64_decode($_GET['verify']);
	$query = "UPDATE `users` SET `verified` = '1' WHERE `username` = '$user' LIMIT 1";
	if(!mysqli_query($GLOBALS['db']['link'], $query)) echo "Error! Couldn't verify user '$user'";
	else echo "Verifying e-mail address...<br/><br/>Success! You have been verified.";
	$page->footer();
	exit;
	
}

if($_GET['do'] == "send_verification_email") {
	
	$page->title = "Videogam.in / Send Verification E-mail";
	$page->header();
	if(!$usrid) echo "Error: Couldn't send verification e-mail since you're not logged in";
	elseif(sendVerificationEmail($usrid)) {
		$query = "SELECT `email` FROM `users` WHERE `id` = '$usrid' LIMIT 1";
		$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query));
		echo "A verification e-mail has been sent to $dat->email. If you can't access this e-mail account, please <a href=\"account.php\">update your account details</a> and try again.";
	} else {
		echo "Error sending verification e-mail! The error has been sent to the staff for review";
		sendBug("Couldn't send verification e-mail to someone");
	}
	$page->footer();
	exit;
	
}

if ($_POST['do'] == "Submit Registration") {
    try {
        $sub = $_POST['sub'];

        $fields = array ('username', 'password', 'password_match', 'email');
        foreach ($fields as $field) {
        if ($sub[$field] == "") {
          $errors[] = "You missed the <i>$field</i> field.";
        }
        }

        //Authentication
        require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.authenticate.php";
        $a_real = $auth_forms[base64_decode($_POST['auth_form_num'])]['a'];
        $a_given = trim(strtolower($_POST['auth_form_input']));
        $auth_passed = $a_real == $a_given ? true : false;
        if(!$auth_passed) $errors[] = "Couldn't authenticate input. Are you a real person?";

        $sub['username'] = trim($sub['username']);
        $sub['password'] = trim($sub['password']);
        $sub['password_match'] = trim($sub['password_match']);
        $sub['email'] = trim($sub['email']);

        //valid username?
        if(preg_match('/[^a-zA-Z0-9-_]/', $sub['username'])) {
        	$errors[] = "Illegal characters in username (only letters, numbers, -, and _)";
        }

        //valid pass?
        /*if(preg_match('/[^a-zA-Z0-9]/', $sub[pass])) {
        	$errors[] = "Illegal characters in password (only letters and numbers)";
        }*/

        //matching passwords?
        if($sub['password'] != $sub['password_match']) {
        $errors[] = "your passwords don't match.";
        }

        if(!filter_var($sub['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "The e-mail address <i>".$sub['email']."</i> does not appear to be valid.";
        }
        
        $tod = date("Y-m-d H:i:s");
	  
        //Insert into database
        $Query = "INSERT INTO users (`username`, `password`, `email`, `registered`, `activity`, `previous_activity`, `gender`) 
        	VALUES ('".mysqli_real_escape_string($GLOBALS['db']['link'], $sub['username'])."', password('".mysqli_real_escape_string($GLOBALS['db']['link'], $sub['password'])."'), '".$sub['email']."', '$tod', '$tod', '$tod', '".mysqli_real_escape_string($GLOBALS['db']['link'], $sub['gender'])."')";
        if (!mysqli_query($GLOBALS['db']['link'], $Query)) {
            $errors[] = "There was an error and the account could not be registered.";
            sendBug("User couldn't register. Table error?");
        }
        	
    	// retrieve new user data for login
    	$query = "SELECT * FROM `users` WHERE `username` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $sub['username'])."' LIMIT 1";
    	if(!$user = @mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $query))) {
    		$errors[] = "You were registered successfully, but there was an error and the process could not continue. However, you should be able to log in to your account.";
    		sendBug("Registration error selecting inserted user data. [$query]");
    		break;
    	}
    	
    	if(!login($user) && !$usrid){
    		$errors[] = 'Your account was registered but there was an unexpected login error. Please try <a href="/login.php">logging in manually</a>.</body></html>';
    		break;
    	}
    	
    	//update user_prefs table
    	$query = "INSERT INTO `users_prefs` (`usrid`) VALUES ('$usrid')";
    	if(!mysqli_query($GLOBALS['db']['link'], $query)) sendBug("Could not INSERT into `users_prefs` table (/register.php) [$query]");
    	
    	//send welcome email
    	$message = file_get_contents($_SERVER['DOCUMENT_ROOT']."/bin/incl/welcome_message.htm");
    	$message = str_replace("%s", $usrname, $message);
    	$headers  = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
    	'To: '.$usrname.' <'.$user['email'].'>' . "\r\n" . 
    	'From: Videogam.in <Luigi@videogam.in>' . "\r\n";
    	@mail($user['email'], "Welcome to Videogam.in", $message, $headers);
    	
    	//check for Gravatar
    	gravatar($user['email'], '', $usrid);
    	
    	//encripted oauth data
    	if($str = $_POST['udata']){
    		
    		$str = base64_decode($str);
    		parse_str($str, $oauth);
    		if($oauth['oauth_provider'] == "steam"){
    			
    			define("STEAM_CONDENSER_PATH", $_SERVER['DOCUMENT_ROOT']."/bin/php/steam-condenser/lib/");
    			require_once STEAM_CONDENSER_PATH."steam/community/SteamId.php";
    			
    	    $steamuser = new SteamId($oauth['oauth_usrid']);
    	    	
        	$q = "INSERT INTO users_oauth (usrid,oauth_provider,oauth_usrid,oauth_username) VALUES ('$usrid', 'steam', '".mysqli_real_escape_string($GLOBALS['db']['link'], $oauth['oauth_usrid'])."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $oauth['oauth_username'])."');";
    			if(!mysqli_query($GLOBALS['db']['link'], $q)){
    				sendBug("register.php Couldn't record Steam user data to users_oauth table because of a Mysql error [$q]: ".mysqli_error($GLOBALS['db']['link']));
    				die('Sorry, there was a database error and we couldn\'t record your Steam details. Your account has been registered and you have been logged in though! <a href="/">Continue</a>');
    			}
    			
    			$query = "INSERT INTO users_details (usrid,`name`,`location`,`homepage`) VALUES ('$usrid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $steamuser->realName)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $steamuser->location)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $steamuser->links[0])."');";
    			if(!mysqli_query($GLOBALS['db']['link'], $query)) sendBug("Could not INSERT into `users_details` table [$query] (/register.php)");
    			
    			// import avatar
    			if($avatar_url = $steamuser->getFullAvatarUrl()){
    				
    				$avatar = $_SERVER['DOCUMENT_ROOT']."/bin/temp/".$oauth['usrid'].time().".jpg";
    				if(copy($avatar_url, $avatar)){
    					
    					$upload_avatar_result = uploadAvatar($avatar, "", customAvatarDir($usrid));
    					if($upload_avatar_result['filename']){
    						mysqli_query($GLOBALS['db']['link'], "UPDATE users SET avatar='".customAvatarDir($usrid)."/".$upload_avatar_result['filename']."' WHERE usrid='$usrid' LIMIT 1");
    					}
    					
    				}
    			}
    		}
    	}
     	
     	header("Location: /account.php?edit=details&justregistered=1");
    } catch (PDOException $e) {
       if ($e->errorInfo[1] == 1062) {
          // duplicate username or email
       } else {
          // an error other than duplicate entry occurred
       }
    }

}

// "you've been registered" display moved to account.php
if ($_GET['registered']){
	header("Location: /account.php?edit=details&justregistered=1");
	exit;
}

$page->minimalist = true;
$page->header();

if(!$_POST){
?>
<div style="width:225px; margin:40px auto 0; text-align:center;">
	<a href="/login_fb.php" style="display:block;" onclick="$.cookie('lastpage', '<?=$_SERVER['HTTP_REFERER']?>', {expires:1, path:'/'})"><img src="/bin/img/fbconnect_225.png" width="225" height="33" alt="Login with Facebook"/></a>
	<p>Register automagically with your Facebook account.</p>
	<p><a onclick="$(this).closest('div').hide().next().show()">I don't have Facebook</a></p>
</div>
<? } ?>
<div style="width:300px; margin:0 auto;<?=(!$_POST ? "display:none;" : "")?>"><? include("register_form.incl.php") ?></div>

<div id="whyreg" style="display:none">
	<h3>Why Register?</h3>
	<ol>
		<li>
			<strong>Contribute</strong><br/>
			Use your expertise to contribute to the Videogam.incyclopedia, our database of games, game creators, consoles, companies, characters, and videogame concepts.
		</li>
		<li>
			<strong>Collect</strong><br/>
			List games you're currently playing and the game music you're listening to. Share your collection with friends.
		</li>
		<li>
			<strong>Discuss</strong><br/>
			Participate in forum discussions and comment on news.
		</li>
		<li>
			<strong>Join</strong><br/>
			Join groups and clans to find like-minded individuals and friends to play with.
		</li>
		<li>
			... and more!
		</li>
	</ol>
</div>

<br style="clear:both;"/>

<?

$page->footer();



function sendVerificationEmail($user) {
	global $db, $default_email;
	
	$query = "SELECT * FROM `users` WHERE `username` = '$user' LIMIT 1";
	if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query))) die("Couldn't get user data for '$user'");
	
	$to = $dat->email;
  $subject = 'Videogam.in verification e-mail';
  $body = "Dearest $username,\nVerify your Videogam.in user account by visiting the following URL:\n\nhttp://thevideogam.in/register.php?verify=".base64_encode($username)."\n\nThanks!\n-The happy Videogam.in user validation robot\n";
  $headers = 'From: ' . $default_email . "\r\n" .
    'Reply-To: ' . $default_email . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
  
  if(mail($to, $subject, $body, $headers)) return TRUE;
  else return FALSE;
  	
}

?>