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
	try {
		$user = api_request();

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
