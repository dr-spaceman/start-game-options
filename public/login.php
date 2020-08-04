<?php

require_once dirname(__FILE__) . '/../config/bootstrap_app.php';

use Monolog\Logger;
use Vgsite\Exceptions\LoginException;
use Vgsite\User;
use Vgsite\UserMapper;
use Vgsite\UserScore;
use Vgsite\Badge;

$logger_login = new Logger('login');
// Register a handler -- file loc and minimum error level to record
$logger_login->pushHandler(new Monolog\Handler\StreamHandler(LOGS_DIR.'/login.log', (getenv('ENVIRONMENT') == "development" ? Logger::DEBUG : Logger::INFO)));
$logger_login->pushProcessor(new Monolog\Processor\IntrospectionProcessor(Logger::DEBUG));

if (isset($_POST['login'])) {

	if ($ajax_request === true) {
		//TODO
	}

	try {
		$user = login();

		header($_SERVER['SERVER_PROTOCOL'].' 200 OK', true, 200);
        header('Location: /');
        exit;

		?><html>
			<head>
				<title>Logging in <?=$user->getUsername()?></title>
			    <link rel="stylesheet" type="text/css" href="/bin/css/screen.css"/>
			    <? if($GLOBALS['no_oauth']){ ?>
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
					if(!$GLOBALS['no_oauth']) {
						echo '<big style="font-size:21px; padding-right:30px; background:url(/bin/img/icons/emoticons/_goomba.gif) no-repeat right center;">Logging in</big>';
					} else {
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
	} catch (LoginException | Exception $e) {
	    switch ($e->getCode()) {
	        case 401:
	            header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized", true, 401);
	            break;
	        case 400:
	            header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request", true, 400);
	            break;
	        default:
	        	header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
	    }

	    $login_error = 'Error logging in: '.$e->getMessage();

        $logger_login->warning($e, ['username' => $_POST['username']]);
    }
}

echo $template->render('login.html', ['login_error' => $login_error]);

//logout
if (isset($_GET['do']) && $_GET['do'] == "logout") {
	setcookie(session_name(), '', time()-42000, '/');
	unset($_SESSION['username'], $_SESSION['user_id'], $_SESSION['user_rank'], $_SESSION['logged_in']);
	session_destroy();
	?><!DOCTYPE html>
    <html>
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
	exit;
}

function login()
{
	if (!isset($_POST['login'])) {
        throw new LoginException('No login credentials found', 400);
    }

    $username = filter_input(INPUT_POST, "username");
    if (!$username) {
        throw new LoginException('Username or email is required to login', 400);
    }

    $email = '';
    if(strstr($username, "@")) {
        $email = filter_var($username, FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new LoginException("The e-mail address '$email' couldn't be validated. Please try again!", 400);
        }
    }

    $password = filter_input(INPUT_POST, "password");
    if (!$password) {
        throw new LoginException('Password is required.', 400);
    }

    $user_mapper = new UserMapper();
    
    try {
        $user = !empty($email) ? $user_mapper->findByEmail($email) : $user_mapper->findByUsername($username);
    } catch (\OutOfBoundsException $e) {
        throw new LoginException('Invalid username', 401);
    }

    if (!password_verify($password, $user->getPassword())) {
        throw new LoginException('Invalid password', 401);
    }

    // Re-hash password if necessary
    $currentHashAlgorithm = PASSWORD_DEFAULT;
    $passwordNeedsRehash = password_needs_rehash($user->getPassword(), $currentHashAlgorithm);
    if ($passwordNeedsRehash === true) {
        // Save new password hash
        $user->setPassword($password, true);
        $user_mapper->save($user);
    }

    $_SESSION['user_id'] = $user->getId();
    $_SESSION['user_rank'] = $user->getRank();
    $_SESSION['username'] = $user->getUsername();
    $_SESSION['logged_in'] = 'true';

    /**
     * Login complete; Post-login business below
     */

    // TODO
    $user_details = [];//$user_mapper->getAllDetails($user);

    // check for new badges earned since last login
    // return array badge IDs
    $sql = "SELECT badge_id FROM badges_earned WHERE user_id=? AND `new`=1";
    $statement = $GLOBALS['pdo']->prepare($sql);
    $statement->execute([$user->getId()]);
    if ($rows = $statement->fetchAll(PDO::FETCH_COLUMN)) {
        $_SESSION['newbadges'] = $rows;
    }

    //check birthday badge
    if (substr($user_details['dob'], 5) == date("m-d")) {
        Badge::findById(37)->earn($user);
    }

    // Update activity
    $sql = sprintf(
        "UPDATE users SET activity='%s', previous_activity='%s' WHERE user_id=%d LIMIT 1", 
        date("Y-m-d H:i:s"), 
        $user->getLastLogin()->format('Y-m-d H:i:s'), 
        $user->getId()
    );
    $GLOBALS['pdo']->query($sql);

    //record current scores and counts
    $score = new UserScore($user);
    $score->save();

    //fb login
    // $check_1 = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM users_oauth WHERE user_id=".$user->getId()." AND oauth_provider='facebook' LIMIT 1"));
    // $check_2 = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM users_oauth WHERE user_id=".$user->getId()." LIMIT 1"));
    // if (!$fbuser && $check_1) {
    //     require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/fb/src/facebook.php";
    //     $fb = array(
    //       'appId'  => '142628175764082',
    //       'secret' => '5913f988087cecedd1965a3ed6e91eb1'
    //     );
    //     $facebook = new Facebook($fb);
    //     $fbuser = $facebook->getUser();
    //     if ($fbuser) {
    //       try {
    //         // Proceed knowing you have a logged in user who's authenticated.
    //         $fbuser_data = $facebook->api('/me');
    //       } catch (FacebookApiException $e) {
    //         error_log($e);
    //         $fbuser = null;
    //       }
    //     }
    // /*} elseif(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q)) && !mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q2)) && $_COOKIE['no_oauth'] != "ignore" && $userdat['registered'] != $userdat['activity']){
    //     setcookie("no_oauth", "1", time()+60*60*24*100, "/");*/
    // } elseif (!$check_1 && !$check_2 && $user_details['registered'] != $user_details['activity']) {
    //     $GLOBALS['no_oauth'] = true;
    // }

    if($_POST['remember']) {
        //TODO
    }

    return $user;
}
