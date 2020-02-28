<?
ini_set("error_reporting", 6135);
require("db.php");

$default_email = "matt@videogam.in";

$errors   = array();
$warnings = array();
$results  = array();

$html_tag = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
$root = $_SERVER['DOCUMENT_ROOT'];

session_set_cookie_params(6000);
session_start();
//set login vars
if(isset($_SESSION['usrname'])) {
	
	$usrname = $_SESSION['usrname'];
	$usrid   = $_SESSION['usrid'];
	$usrrank = $_SESSION['usrrank'];
	$usrlastlogin = $_SESSION['usrlastlogin'];

} else {
	
	if(isset($_COOKIE['remember_usrid']) && isset($_COOKIE['remember_usrpass'])) {
		
		//login user from remembered cookie
		
		$pass = base64_decode($_COOKIE['remember_usrpass']);
		$q = "SELECT * FROM users WHERE usrid='".$_COOKIE['remember_usrid']."' AND password=PASSWORD('".base64_decode($_COOKIE['remember_usrpass'])."') LIMIT 1";
		$res = mysqli_query($GLOBALS['db']['link'], $q);
		if($userdat = mysqli_fetch_object($res)) {
			
			if(!$_SESSION['usrname'] = $userdat->username) $errors[] = "Couldn't set session variable 'usrname'.";
			if(!$_SESSION['usrid'] = $userdat->usrid) $errors[] = "Couldn't set session variable 'usrid'.";
			if(!$_SESSION['usrrank'] = $userdat->rank) $errors[] = "Couldn't set user rank variable.";
			$_SESSION['usrlastlogin'] = $userdat->previous_activity;
			$usrname = $_SESSION['usrname'];
			$usrid   = $_SESSION['usrid'];
			$usrrank = $_SESSION['usrrank'];
			$usrlastlogin = $_SESSION['usrlastlogin'];
			
			if(!$errors) {
				//update activity
				$q2 = "UPDATE users SET activity='".date("Y-m-d H:i:s")."', previous_activity='".$userdat->activity."' WHERE usrid='".$_SESSION['usrid']."' LIMIT 1";
				mysqli_query($GLOBALS['db']['link'], $q2);
			}
			
		}
	
	} else {
		//no session & no cookie
		//make sure there are no registration variables passed
		$usrname = "";
		$usrid   = "";
		$usrrank = 0;
		$usrlastlogin = "";
	}
}

//login
if(isset($_POST['do']) && $_POST['do'] == "login" && isset($_POST['username'])) {
	
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	$q = sprintf("SELECT * FROM `users` WHERE `username` = '%s' AND `password` = password('$password') LIMIT 1",
		mysqli_real_escape_string($GLOBALS['db']['link'], $username));
	$res = mysqli_query($GLOBALS['db']['link'], $q);
	if(mysqli_num_rows($res)) {
		
		if(!$userdat = mysqli_fetch_object($res)) die("Error: Couldn't get user data");
		if(!$_SESSION['usrname'] = $userdat->username) die("Couldn't set session variable 'usrname'.");
		if(!$_SESSION['usrid'] = $userdat->usrid) die("Couldn't set session variable 'usrid'.");
		if(!$_SESSION['usrrank'] = $userdat->rank) die("Couldn't set user rank variable.");
		$_SESSION['usrlastlogin'] = $userdat->previous_activity;
		
		//remember?
		if($_POST['remember']) {
			// time()+60*60*24*100 = 100 days
			setcookie("remember_usrid", $_SESSION['usrid'], time()+60*60*24*100, "/");
			setcookie("remember_usrpass", base64_encode($password), time()+60*60*24*100, "/");
		}
		
		//update activity
		$q2 = "UPDATE users SET activity='".date("Y-m-d H:i:s")."', previous_activity='".$userdat->activity."' WHERE usrid='".$_SESSION['usrid']."' LIMIT 1";
		mysqli_query($GLOBALS['db']['link'], $q2);
		
		if($_POST['ajax_login']) {
			die("ok");
		} else {
			?><?=$html_tag?>
		  <head>
		    <title>Logging in <?=$_SESSION['usrname']?></title>
		    <meta http-equiv="REFRESH" content="0; url=<?=$_SERVER['REQUEST_URI']?>">
		    <link rel="stylesheet" type="text/css" href="/bin/css/screen.css"/>
		  </head>
			<body>
			<div id="body-message"><span style="font-size:21px; padding-right:30px; background:url(/bin/img/login-loading.gif) no-repeat 100% 50%;">Logging in</span></div>
			</body>
		  </html>
		  <?
			exit;
		}
	} else {
		
		// failed login
		
		setcookie(session_name(), '', time()-42000, '/');
		setcookie("remember_usrid", "", time()-60*60*24*100, "/");
		setcookie("remember_usrpass", "", time()-60*60*24*100, "/");
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
	    		<td colspan="2"><input type="submit" name="login" value="Login"/> <a href="/retrieve-pass.php">Forgot password?</a></td>
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
	setcookie("remember_usrid", "", time()-60*60*24*100, "/");
	setcookie("remember_usrpass", "", time()-60*60*24*100, "/");
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
			<div id="body-message"><span style="font-size:21px; padding-right:30px; background:url(/bin/img/login-loading.gif) no-repeat 100% 50%;">Logging Out</span></div>
		</body>
    </html>
  <?
	exit();
}

class page {
	var $title;
	var $javascript;
	var $javascripts = array();
	var $style = array();
	var $nocont; //blank page
	
function header() {
	
	////////////
	// HEADER //
	////////////
	
global $db, $login, $usrname, $usrid, $usrrank, $usrlastlogin, $html_tag, $gdat, $gamepg;

$this->close_to = '1'; //close the site to users with this rank and below

//get/set special page vars
if(isset($this->admin)) {
	$this->style[] = "/bin/css/admin.css";
	$this->javascript.= '<script src="/bin/script/html-toolbox.js" type="text/javascript"></script>'."\n".
		'<script src="/bin/script/codepress/codepress.js" type="text/javascript"></script>'."\n".
		'<script src="/bin/script/admin.js" type="text/javascript"></script>'."\n";
}
if(isset($this->error_404)) {
	$this->title.= " [error 404]";
}

//given stylesheets
$print_style = "";
//make sure nothing's calling news.css anymore
if(in_array("/bin/css/news.css", $this->style)) $warnings[] = "WTF this script is still calling news.css!!!";
if(is_array($this->style)) {
	foreach($this->style as $st) $print_style.= '<link rel="stylesheet" href="'.$st.'" type="text/css" media="screen"/>'."\n";
} elseif (!is_array($this->style) && isset($this->style)) {
	$print_style = '<link rel="stylesheet" href="'.$this->style.'" type="text/css" media="screen"/>'."\n";
}
if(isset($this->freestyle)) $print_style.= '<style type="text/css">'.$this->freestyle.'</style>';

//check given javascript srcs and don't repeat one
$print_javascripts = '<script type="text/javascript" src="/bin/script/jquery-1.3.1.js"></script>
	<script type="text/javascript" src="/bin/script/global.js"></script>
	<script type="text/javascript" src="/bin/script/autocomplete.js"></script>
	<script type="text/javascript" src="/bin/script/jquery.cookies.js"></script>
	<script type="text/javascript" src="/bin/script/thickbox.js"></script>
	<script type="text/javascript" src="/bin/script/tooltip.js"></script>
	';
foreach($this->javascripts as $src) {
	if(strpos($print_javascripts, $src) === FALSE) $print_javascripts.= '<script type="text/javascript" src="'.$src.'"></script>'."\n\t";
}

//get here for nav
$scr_url = $_SERVER['SCRIPT_URL'];
if(strstr($scr_url, '/games/')) $here['games'] = 'here';
elseif(strstr($scr_url, '/people/')) $here['people'] = 'here';
elseif(strstr($scr_url, '/music/')) $here['music'] = 'here';
elseif(strstr($scr_url, '/ninadmin/')) $here['admin'] = 'here';
elseif(strstr($scr_url, '/forums/')) $here['forums'] = 'here';
elseif(strstr($scr_url, '/news/')) $here['news'] = 'here';
elseif(strstr($scr_url, '/blogs/')) $here['blogs'] = 'here';
elseif(strstr($scr_url, '/groups/')) $here['groups'] = 'here';
else $here['home'] = 'here';

$meta_replace = array("[GAME_TITLES]", "[GENERIC_KEYWORDS]");
$meta_with    = array("Final Fantasy,Kingdom Hearts,Chrono Trigger,Seiken Densetsu,Secret of Mana,Legend of Zelda,Mario",
                      "games,videogames,Square Enix,Nintendo,PlayStation,discussion,wallpaper,music,MP3,movies,coverage,database,sheet music,artwork,screenshots,screens,files,walkthrough");
if($this->meta_keywords) {
	$this->meta_keywords = str_replace($meta_replace, $meta_with, $this->meta_keywords);
} else {
	$this->meta_keywords = $meta_with[1].','.$meta_with[0];
}
if(!$this->meta_description) {
	$this->meta_description = "Subjective, intelligent coverage of the best games in the universe, especially Square Enix and Nintendo, games from Final Fantasy to Zelda to Mario, with comprehensive game guides, music album coverage, game developer profiles, and more.";
}

?>
<?=$html_tag?>
<head>
	<title><?=($this->title ? strip_tags($this->title) : 'Videogam.in, the site of vapid gaming debauchery')?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="keywords" content="<?=$this->meta_keywords?>"/>
	<meta name="description" content="<?=$this->meta_description?>"/>
	<meta name="DC.title" content="<?=$this->title?>"/>
	<link rel="shortcut icon" href="/favicon.ico"/>
	<link rel="stylesheet" type="text/css" href="/bin/css/screen.css" media="screen"/>
	<link rel="stylesheet" type="text/css" href="/bin/css/thickbox.css" media="screen"/>
	<link rel="stylesheet" type="text/css" href="/bin/css/autocomplete.css" media="screen"/>
	<?=$print_style?>
	<?=$print_javascripts?>
	<?=$this->javascript?>
</head>
<body>
<?

if($this->nocont) return; // End the header here if $nocont is true

$rand = rand(1,5); //generate random # for header img
?>
<div id="htmlbody">
<div id="header" style="background-image:url(/bin/img/headers/header_<?=$rand?>.gif);">
	
	<h1 id="top"><a href="/"><span>Videogam.in</span></a></h1>
	
	<?
	$headers = array(
		1 => array("Pac-Man", "pac-man"),
		2 => array("Earthbound", "earthbound"),
		3 => array("Donkey Kong", "donkey-kong"),
		4 => array("Shadow of the Colossus", "shadow-of-the-colossus"),
		5 => array("Square no Tom Sawyer", "square-no-tom-sawyer")
	);
	?>
	<a href="/games/~<?=$headers[$rand][1]?>" title="<?=htmlSC($headers[$rand][0])?>" id="header-game"><span><?=$headers[$rand][0]?></span></a>
	
	<div id="nav">
		<div id="search">
			<form action="" method="post">
				<table border="0" cellpadding="2" cellspacing="0">
					<tr>
						<td><input type="text" id="search-input" onfocus="$(this).css('width','250px');"/></td>
						<td><input type="image" src="/bin/img/search.png" onclick="this.submit();"/></td>
					</tr>
				</table>
			</form>
		</div>
		
		<ul>
			<li class="<?=$here['home']?>"><a href="/" title="Videogam.in homepage">Home</a></li>
			<li class="<?=$here['games']?>"><a href="/games/" title="Games index"><span class="more">Games</span></a>
				<ul>
					<li><a href="/games/add.php" class="add" title="Add a new game to the database">New Game</a></li>
				</ul>
			</li>
			<li class="<?=$here['people']?>"><a href="/people/" title="A database of people who create videogames">People</a></li>
			<li class="<?=$here['music']?>"><a href="/music/" title="Videogame music, music albums, and music composers">Music</a></li>
			<li class="<?=$here['news']?>"><a href="/news/" title="Videogame News index"><span class="more">News</span></a>
				<ul>
					<li><a href="/news/topics/">Topics</a></li>
					<li><a href="/posts/manage.php?action=newpost" class="add" title="post a new news article">Post a New Article</a></li>
				</ul>
			</li>
			<li class="<?=$here['blogs']?>"><a href="/blogs/" title="Videogame Blogs index"><Span class="more">Blogs</span></a>
				<ul>
					<li><a href="/blogs/topics/">Topics</a></li>
					<?=($usrname ? '<li><a href="/~'.$usrname.'/blog">Your Blog</a></li><li><a href="/posts/manage.php">Blog/Post Management</a></li>' : '')?>
					<li><a href="/posts/manage.php?action=newpost" class="add" title="post a new entry in your Videogam.in blog">New Blog Entry</a></li>
				</ul>
			</li>
			<li class="<?=$here['forums']?>"><a href="/forums/" title="Videogam.in Message Forums of DEATH!!!">Forums</a></li>
			<li class="<?=$here['groups']?>"><a href="/groups/" title="Groups index">Groups</a></li>
			<?=($usrrank >= 5 ? '<li class="'.$here['admin'].'"><a href="/ninadmin/">Admin</a></li>' : '')?>
		</ul>
	</div>
</div>

<div id="page">
<?

//user has access?
if($this->min_rank && $usrrank < $this->min_rank) {
	echo "You don't have access to this page.";
	$this->footer();
	exit;
}

if($this->close_to != '' && $usrrank <= $this->close_to) {
	$access = array("/register.php", "/account.php", "/jobs.php", "/contact.php", "/retrieve-pass.php");
	$p = $_SERVER['SCRIPT_NAME'];
	if(!in_array($p, $access)) {
		$query = "SELECT * FROM users_ranks";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)) {
			$ranks[$row['rank']] = $row['description'];
		}
		echo '<div style="padding-top:15px">Sorry, but your status is <i>'.$ranks[$usrrank].'</i> and this site is currently closed to '.$ranks[$this->close_to].'s '.($this->close_to >= 1 ? 'and below' : '').($usrrank < 1 ? ' (registered users can access the site by <a href="#login">logging in</a>)' : '').'.</div>';
		$this->footer();
		exit;
	}
}

//admin page
if($this->admin) {
	?>
	<div id="admin-menu"><div class="container">
		<? include($_SERVER['DOCUMENT_ROOT']."/ninadmin/include.menu.php"); ?>
	</div></div>
	<div id="admin-content">
	<?
}

//game page
if($gamepg->gid) {
	if(!$gamepg->varsGotten) $gamepg->getVars();
	$gamepg->header();
}

//game guide
if($this->game_guide) {
	if($usrrank >= 7) {
		?><a href="/ninadmin/games-mod.php?what=guide&id=<?=$this->gid?>&edit_file=<?=preg_replace("@/games/.+/.+/guide/@", "", $_SERVER['SCRIPT_NAME'])?>" title="admin: edit this page's contents" class="admin-link">Edit Page</a><?
	}
	$game_guide_menu = file_get_contents($_SERVER['DOCUMENT_ROOT']."/games/".$gdat->platform_shorthand."/".$gdat->title_url."/guide/menu.txt");
	if(strstr($game_guide_menu, 'class="top"')) {
		//menu on top instead of to the right
		echo '<div id="game-guide-menu-top">'.$game_guide_menu."</div>\n";
		$game_guide_menu = "";
	} else {
		echo '<div id="game-guide-menu">'.$game_guide_menu."</div>\n";
	}
	?><div id="game-guide-content"<?=(!$game_guide_menu ? ' style="width:100% !important;"' : '')?>><?
}

//404
if($this->error_404) {
	?><h2>The page you requested is no longer here <small>[error 404]</small></h2>
	If you think this page should be here or you'd just like us to know about how you ventured down this fruitless path, please take a few seconds to <a href="/bug.php">send a Bug Report</a>.
	<p>Otherwise, please utilize the search box above or return to the <a href="/">home page</a>.</p><?
}

}

function footer() {
	
	////////////
	// FOOTER //
	////////////
	
global $db, $usrid, $usrname, $usrrank, $game_guide_menu, $gamepg, $results, $errors, $warnings;

if(!$this->nocont) {
	if($this->admin) {
		?></div><!-- #admin-content -->
		<br style="clear:both"/><br/><?
	}
	
	if($gamepg->header_output) echo '</div><!-- #game-cont --></div><!-- #game-page-2 --><br style="clear:both"/></div><!-- #game-page -->';
	?>
	
	<div id="footer"<?=($game_header_output ? ' style="margin-top:0 !important;"' : '')?>><b>&copy; 2008 Videogam.in</b> | <a href="/contact.php">Contact Us</a> | <a href="/jobs.php">Jobs</a> | <a href="http://twitter.com/videogamin" title="Follow Videogam.in on Twitter">Follow us on Twitter<img src="/bin/img/twitter_sm_white_shadow.png" alt="Twitter follow" width="50" height="50" border="0" style="position:absolute; top:8px; margin-left:3px;"/></a></div>
	
	</div>
	
	<div id="user-details">
		<?
		if ($usrid) {
			?>
			<big>Hello, <?=$usrname?></big>
			<a href="/account.php">you</a> &middot; 
			<a href="/messages.php">messages</a>
			<?
			//new pms?
			$q = "SELECT * FROM pm WHERE `to`='$usrid' AND `read`='0'";
			if($new_pms = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) {
				echo ' ('.$new_pms.')';
			}
			?> &middot; 
			<a href="?do=logout">log out</a>
			<?
		} else {
			?>
			<div id="user-details-container">
				Welcome, Guest
				<big>Please <a href="#login">log in</a></big>
			</div>
			
			<div id="login">
				<div class="switch">
				  
				  <table border="0" cellpadding="0" cellspacing="0">
				  	<tr>
				  		<td id="register-words">
							  New to Videogam.in?
							  <a href="/register.php" id="join-button"><span>Join</span></a>
							  It's 100% free, only takes a minute, and you can do tons of stuff with it.
							</td>
							<td id="form-login">
								<form method="post" action="<?=$_SERVER['REQUEST_URI']?>">
							    <input type="hidden" name="do" value="login"/>
							    <table border="0" cellpadding="0" cellspacing="0">
							    	<tr>
							    		<th><label for="login-username">Username:</label></th>
							    		<td><input type="text" name="username" id="login-username" size="14" maxlength="25"/></td>
							    	</tr>
							    	<tr>
							    		<th><label for="login-password">Password:</label></th>
							    		<td><input type="password" name="password" id="login-password" size="14" maxlength="25"/></td>
							    	</tr>
							    </table>
							    <input type="submit" name="login" value="Log in" style="float:right"/>
							    <label><input type="checkbox" name="remember" value="1"/> Remember Me</label>
							  </form>
							  <a href="/retrieve-pass.php" id="forgot-info">Forgot your password?</a>
							</td>
						</tr>
					</table>
				  
				</div>
				<div id="form-register" style="display:none" class="switch">
					
					<big>Register for an Account</big>
					
					Already registered? <b><a href="javascript:void(0)" class="arrow-right" onclick="$('#login .switch').toggle();">Log in</a></b>
					
					<br/><br/>
					
					<? $auth = authenticate(); ?>
					<div id="invitation-code">
						Registration is presently invite-only. Sign up on the <a href="/">home page</a> to be notified of account openings or input your invitation code:
						<input type="text" name="invitation-code" size="10" id="input-invitation-code"/> 
						<input type="button" value="Submit" onclick="if(document.getElementById('input-invitation-code').value=='IS944A') toggle('registration-form', 'invitation-code'); else alert('Incorrect code');"/>
					</div>
					
					<form action="/register.php" method="post" id="registration-form" style="display:none">
						<input type="hidden" name="loc" value="<?=$_SERVER['SCRIPT_URL']?>"/>
						<input type="hidden" name="math1" id="reg-math1" value="<?=$auth->math1?>"/>
						<input type="hidden" name="math2" id="reg-math2" value="<?=$auth->math2?>"/>
						<table border="0" cellpadding="0" cellspacing="0" id="register-form">
							<tr>
							  <th><label for="user">Username:</label></th>
							  <td><input type="text" name="sub[user]" id="user" size="22" maxlength="15"/></td>
								<td colspan="2" class="note">Use only letters, numbers, dash (-), and underscore (_).</td>
							</tr>
							<tr>
							  <th><label for="email">E-mail Address:</label></th>
							  <td><input type="text" name="sub[email]" id="email" size="22" maxlength="70"/></td>
								<td colspan="2" class="note">For password retrieval; Your information is strictly confidential and will never, ever be shared with a third party.</td>
							</tr>
							<tr>
							  <th><label for="pass">Password:</label></th>
							  <td><input type="password" name="sub[pass]" id="pass" size="22" maxlength="15"/></td>
							  <th>
							  	<label for="pass2">Confirm Password:</label> 
							  	<input type="password" name="sub[pass2]" id="pass2" size="22" maxlength="15"/>
							  </th>
							</tr>
							<tr>
								<th><label for="inp-gender">Gender:</label></th>
								<td>
									<select name="sub[gender]" id="inp-gender">
										<option value="">Not sure</option>
									  <option value="male">Male</option>
									  <option value="female">Female</option>
									  <option value="asexual">Asexual and/or Robot</option>
								  </select>
								</td>
								<td>
									<?=$auth->label?>
							  	<input type="text" name="math" maxlength="2" size="3" id="reg-math"/> 
							  	<input type="submit" name="do" value="Submit Registration" class="forminp" onclick="return validateRegistration()"/>
							  </td>
							</tr>
						</table>
					</form>
				</div>
			</div>
			<?
		}
		?>
	</div>
	
	</div><!--#htmlbody-->
	<?
}

//outp errors, warnings & results
?>
<div id="notify"><?
if($errors) {
	?><dl><dt class="err">Errors</dt><?
	foreach($errors as $err) echo '<dd>'.$err."</dd>\n";
	?></dl><?
}
if($warnings) {
	?><dl><dt class="war">Warnings</dt><?
	foreach($warnings as $w) echo '<dd>'.$w."</dd>\n";
	?></dl><?
}
if($results) {
	?><dl><dt class="res">Results</dt><?
	foreach($results as $res) echo '<dd>'.$res."</dd>\n";
	?></dl><?
}
?></div>

</body>
</html>
<?

//close db connection
mysqli_close($db['link']);

}

} // end class Page

function avert ($loc, $secs='', $msg='') {	
  echo ('<html>
    <head>
    <title>Redirecting....</title>
    <meta http-equiv="REFRESH" content="'.($secs ? $secs : '1').'; url='.$loc.'">
    </head>
    <body>'.$msg.'
    </html>');
  exit;
}

function validateEmail($email, $thorough=FALSE) {
	if(!eregi("^[^@]+@.+\.[a-z]{2,6}$", $email)) {
		return FALSE;
	} else {
		if($thorough) {
			$parts = explode("@", $email);
			$host = $parts[1] .".";
			if (@getmxrr( $host, $mxhosts ) == FALSE && gethostbyname( $host ) == $host )
				return TRUE;
			elseif(checkdnsrr($host,"ANY"))
				return TRUE; 
			else
				return FALSE;
		} else return TRUE;
	}
}

function sendBug($desc) {
	global $default_email;
	$desc = wordwrap($desc, 70);
	@mail($default_email, "Videogam.in Auto-Bug Report", $desc);
}

function stripslashesDeep($value) {
	$value = is_array($value) ?
		array_map('stripslashesDeep', $value) :
		stripslashes($value);
	return $value;
}

function getPlatforms() {
	$query = "SELECT * FROM games_platforms";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$ret[$row[platform_id]][platform] = $row[platform];
		$ret[$row[platform_id]][platform_shorthand] = $row[platform_shorthand];
	}
	return $ret;
}

function getUserDat($uid) {
	$q = "SELECT * FROM users LEFT JOIN users_details USING (usrid) LEFT JOIN users_prefs USING (usrid) WHERE usrid='$uid' LIMIT 1";
	if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
		return $dat;
	}
}

function outputUser($uid = "", $avatar = TRUE, $link = TRUE, $substr = '') {
	if(!$uid) return "???";
	elseif(!is_numeric($uid)) return '<acronym title="ID # '.$uid.'">???</acronym>';
	else {
		$q = "SELECT * FROM users WHERE usrid='$uid' LIMIT 1";
		if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
			if($avatar && $dat->avatar) $style = 'background-image:url(/bin/img/avatars/tn/'.$dat->avatar.');';
			elseif($avatar && !$dat->avatar) $style = 'background-image:url(/bin/img/avatars/tn/unknown.png);';
			if($link) $ret = '<a href="/~'.$dat->username.'"'.($avatar ? ' class="user"' : '').' style="'.$style.'" title="'.$dat->username.'\'s profile">';
			if(is_numeric($substr) && strlen($dat->username) > $substr) {
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

function outputPerson($pid, $linkto=TRUE) {
	
	$q = "SELECT name, name_url FROM people WHERE pid='$pid' LIMIT 1";
	if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
		if($linkto) echo '<a href="/people/~'.$dat->name_url.'">'.$dat->name.'</a>';
		else echo $dat->name;
	} else echo "Person ID#'$pid";
	
}

function dirTree(RecursiveDirectoryIterator $dir)
{
   $tree = array();
   $dirs = array(array($dir, &$tree));
   
   for($i = 0; $i < count($dirs); ++$i)
   {
      $d =& $dirs[$i][0];
      $tier =& $dirs[$i][1];

      for($d->rewind(); $d->valid(); $d->next())
      {
         if ($d->isDir())
         {
            $tier[$d->getFilename()] = array();
            $dirs[] = array($d->getChildren(), &$tier[$d->getFilename()]);
         }
         else
         {
            $tier[$d->getFilename()] = $d->getSize();
         }
      }
   }
   
   return $tree;
}

class DirectoryTreeIterator extends RecursiveIteratorIterator
{
    function __construct($path)
    {
        parent::__construct(
            new RecursiveCachingIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::KEY_AS_FILENAME
                ), 
                CachingIterator::CALL_TOSTRING|CachingIterator::CATCH_GET_CHILD
            ), 
            parent::SELF_FIRST
        );
    }

    function current()
    {
        $tree = '';
        for ($l=0; $l < $this->getDepth(); $l++) {
            $tree .= $this->getSubIterator($l)->hasNext() ? '| ' : '  ';
        }
        return $tree . ($this->getSubIterator($l)->hasNext() ? '|-' : '\-') 
               . $this->getSubIterator($l)->__toString();
    }

    function __call($func, $params)
    {
        return call_user_func_array(array($this->getSubIterator(), $func), $params);
    }
}

function authenticate() {
	$rand1 = rand(0,4);
	$rand2 = rand(1,9);
	$ret->math1 = $rand1;
	$ret->math2 = $rand2;
	$ret->hidden = '
	<input type="hidden" name="math1" value="'.$rand1.'"/>
	<input type="hidden" name="math2" value="'.$rand2.'"/>';
	$ret->label = '<label for="inp-math"><img src="/bin/img/numbers/'.$rand1.'.png" alt="random number"/> + <img src="/bin/img/numbers/'.$rand2.'.png" alt="random number"/> = </label>';
	$ret->input = '<input type="text" name="math" maxlength="2" size="5" id="inp-math"/>';
	return $ret;
}

function formatDate ($date, $form = 1, $convert = FALSE) {
	global $usrid;
	
	if($convert && $usrid) {
		$q = "SELECT time_zone FROM users_details WHERE usrid='$usrid' LIMIT 1";
		if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
			$date = convertTimeZone($date, $dat->time_zone);
		}
	}
	
  $months = array
   ('01' => 'January',
    '02' => 'February',
    '03' => 'March',
    '04' => 'April',
    '05' => 'May',
    '06' => 'June',
    '07' => 'July',
    '08' => 'August',
    '09' => 'September',
    '10' => 'October',
    '11' => 'November',
    '12' => 'December');
  
  $short_months = array
   ('01' => 'Jan',
    '02' => 'Feb',
    '03' => 'March',
    '04' => 'April',
    '05' => 'May',
    '06' => 'June',
    '07' => 'July',
    '08' => 'Aug',
    '09' => 'Sept',
    '10' => 'Oct',
    '11' => 'Nov',
    '12' => 'Dec');
  
  list($dt,$tm) = explode(" ",$date);
  $tm = substr($tm,0,5);
  list($y,$m,$d) = explode("-", $dt);
  if ($form == 1) {
    $m = $months[$m];
    if($m == '00' || !$m) {	unset($m); unset($d); }
    if($d == '00') unset($d);
    else $d = " ".$d;
    if($m && $y) $comma = ", ";
    $ret = $m.$d.$comma.$y;
  } elseif ($form == 2 || $form == "MM/DD/YY TIME") {
  	list($y,$m,$d) = explode("-", $dt);
  	$y = substr($y,2,3);
    $ret = $m."/".$d."/".$y." ".$tm;
  } elseif ($form == 3 || $form == "MM/DD") {
    $ret = $m."/".$d;
  } elseif ($form == 4) {
    $m = $months[$m];
    $m = substr($m,0,3);
    $ret = $m." ".$d;
  } elseif ($form == 5 || $form == "MM/DD/YY") {
    $y = substr($y,2,3);
    $ret = $m."/".$d."/".$y;
  } elseif ($form == 6) {
		if(!$m || $m == '00') {
			unset($m);
			unset($d);
		} else $m = $short_months[$m]." ";
		if($d == '00') unset($d);
		else $d.= " ";
		if($y == '0000') unset($y);
	    $ret = $d.$m.$y;
  } elseif ($form == 7) {
  	if(substr($d, 0, 1) == "0") $d = substr($d, 1, 1);
    $ret = ($m ? $short_months[$m]." " : '').($d ? $d.", " : '').$y;
  } elseif($form == 8) {
  	$ret = $y." ".$short_months[$m]." ".$d." ".$tm;
  } elseif($form == 9) {
  	$ret = ($months[$m] ? $months[$m].($d != '00' ? " ".number_format($d) : '') : '');
  } elseif ($form == 10) {
    $ret = "$short_months[$m] $d, $y $tm";
  }
  return $ret;
}

function timeSince($original, $short = false) {
		$original = strtotime($original);

		// array of time period chunks
		$chunks = array(
			array(60 * 60 * 24 * 365 , 'year'),
			array(60 * 60 * 24 * 30 , 'month'),
			array(60 * 60 * 24 * 7, 'week'),
			array(60 * 60 * 24 , 'day'),
			array(60 * 60 , 'hour'),
			array(60 , 'minute'),
		);
		
		$today = time(); /* Current unix time  */
		$since = $today - $original;
		
		// $j saves performing the count function each time around the loop
		for ($i = 0, $j = count($chunks); $i < $j; $i++) {
			
			$seconds = $chunks[$i][0];
			$short ? ($name = substr($chunks[$i][1],0,1)) : ($name = $chunks[$i][1]);
			
			// finding the biggest chunk (if the chunk fits, break)
			if (($count = floor($since / $seconds)) != 0) {
				// DEBUG print "<!-- It's $name -->\n";
				break;
			}
		}
		
		$print = ($count == 1) ? '1 '.$name : "$count {$name}" . ($short ? "" : "s");
		
		if ($i + 1 < $j) {
			// now getting the second item
			$seconds2 = $chunks[$i + 1][0];
			$short ? ($name2 = substr($chunks[$i + 1][1],0,1)) : ($name2 = $chunks[$i + 1][1]);
			
			// add second item if it's greater than 0
			if (($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0) {
				$print .= ($count2 == 1) ? ', 1 '.$name2 : ", $count2 {$name2}" . ($short ? "" : "s");
			}
		}
		if($short) {
			$print=str_replace(" ","",$print);
			$print=str_replace(","," ",$print);
		}
		
		if($print == "0 minutes") $print = "a few seconds";
		
		return $print;
}

function convertTimeZone ($dt, $tz_int='') {
	
	if(!$tz_int) return $dt;
	
	$adj = $tz_int * (60 * 60);
	$adj = $adj - date("Z"); //server timezone offset
	$ts = strtotime($dt) + $adj;
	
  return date("Y-m-d H:i:s", $ts);

}

function outputTag($t, $loc='', $linkto=FALSE, $linkonly=FALSE, $inclpermalink=FALSE) {
	//$linkto = href to coverage (ie game or person)
	if($loc && (substr($loc, -1) != "/")) $loc.= "/";
	//tage a given tag and turn it into a link
	if(strstr($t, "gid:")) {
		$x = explode(":", $t);
		$q = "SELECT title, title_url FROM games WHERE gid='$x[1]' LIMIT 1";
		$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
		if($loc || $linkto) {
			$ret = '<a href="'.($loc ? $loc.$t.($inclpermalink ? '/'.$dat->title_url : '') : '/games/'.$x[1].'/'.$dat->title_url).'">';
			$endA = '</a>';
		}
		if($linkonly) $ret = '/games/'.$x[1].'/'.$dat->title_url;
		else $ret.= $dat->title.$endA;
	} elseif(strstr($t, "pid:")) {
		$x = explode(":", $t);
		$q = "SELECT name, name_url FROM people WHERE pid='$x[1]' LIMIT 1";
		$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
		if($loc || $linkto) {
			$ret = '<a href="'.($loc ? $loc.$t.($inclpermalink ? '/'.$dat->name_url : '') : '/people/'.$x[1].'/'.$dat->name_url).'">';
			$endA = '</a>';
		}
		if($linkonly) $ret = '/people/'.$x[1].'/'.$dat->name_url;
		else $ret.= $dat->name.$endA;
	} elseif(strstr($t, "aid:")) {
		$albumid = str_replace("aid:", "", $t);
		$q = "SELECT title, subtitle FROM albums WHERE albumid='$albumid' LIMIT 1";
		$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
		if($loc || $linkto) {
			$ret = '<a href="/music/?id='.$albumid.'">';
			$endA = '</a>';
		}
		if($linkonly) $ret = '/music/?id='.$albumid;
		else $ret.= $dat->title.($dat->subtitle ? ' <i>'.$dat->subtitle.'</i>' : '').$endA;
	} elseif(strstr($t, "group:")) {
		list($x, $group_id) = explode(":", $t);
		$q = "SELECT * FROM groups WHERE group_id='$group_id' LIMIT 1";
		$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
		if($loc || $linkto) {
			$ret = '<a href="'.($loc ? $loc.$t : '/groups/~'.$dat->name_url).'">';
			$endA = '</a>';
		}
		$ret.= $dat->name.$endA;
	} else {
		if($loc) $ret = '<a href="'.$loc.urlencode($t).'">';
		$ret.= $t;
		if($loc) $ret.= '</a>';
	}
	return $ret;
}

function outputGallery($dir='', $media_id='', $legend='', $style='', $limit='', $more='') {
	global $gallery_num;
	$gallery_num++;
	
	//data
	if($media_id) $query = "SELECT * FROM media WHERE media_id='$media_id' LIMIT 1";
	else $query = "SELECT * FROM media WHERE directory='$dir' LIMIT 1";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	$dat = mysqli_fetch_object($res);
	$dir = $dat->directory;
	
	//captions
	if($media_id) $query = "SELECT * FROM media_captions WHERE media_id='$media_id'";
	else $query = "SELECT c.* FROM media_captions c, media m WHERE m.directory='$dir' AND c.media_id=m.media_id";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$capts[$row['file']] = $row['caption'];
	}
	
	if(substr($dir, 0, 1) != "/") $dir = "/".$dir;
	if($handle = opendir($_SERVER['DOCUMENT_ROOT'].$dir)) {
		while(false !== ($file = readdir($handle))) {
			if($file != "thumbs" && $file != "." && $file != "..") $imgs[] = $file;
		}
	}
	sort($imgs);
	if($handle = opendir($_SERVER['DOCUMENT_ROOT'].$dir."/thumbs")) {
		while(false !== ($file = readdir($handle))) {
			if($file != "." && $file != "..") $tns[] = $file;
		}
	}
	$img_count = count($imgs);
	sort($tns);
	if($img_count != count($tns)) {
		echo "Error displaying gallery: thumbnail count doesn't match image count";
	} else {
		echo '<fieldset class="gallery" style="'.$style.'">'.
			($legend ? '<legend>'.$legend.'</legend>' : '');
		if(!$limit) $limit = $img_count;
		if($img_count < $limit) $limit = $img_count;
		for($i=0; $i < $limit; $i++) {
			echo '<a href="'.$dir.'/'.$imgs[$i].'" class="thickbox" rel="gallery-'.$gallery_num.'" title="'.$capts[$imgs[$i]].'"><img src="'.$dir.'/thumbs/'.$tns[$i].'" alt="'.$capts[$imgs[$i]].'"/></a>'."\n";
		}
		echo '</fieldset>';
	}		
}

function reformatLinks($text, $prepend_domain = false) {
	return $text;/*
	OLD CODE
	// returnReformattedLink() moved to bbcode.php
	$conditions = array(
		'/\[\[G\|\|([- a-z0-9!#$%&\'*+\/?^_`{}~:\'"?.]+)\|?\|?([- a-z0-9!#$%&\'*+\/?^_`{|}~:\'"?.]+)?\]\]/ise', // [[G||Chrono Trigger||link text]]
		'/\[game([- a-z0-9!#$%&\'*+\/?^_`{}~:\'"?.]+)\|?\|?([- a-z0-9!#$%&\'*+\/?^_`{|}~:\'"?.]+)?\]\]/ise', // [[G||Chrono Trigger||link text]]
		'/\[\[P\|\|([- a-z0-9!#$%&\'*+\/?^_`{}~:\'"]+)\|?\|?([- a-z0-9!#$%&\'*+\/?^_`{|}~:\'"?.]+)?\]\]/ise', // [[P|Yoshitaka Amano||link text]]
		'/\[\[P\|\|([- a-z0-9!#$%&\'*+\/?^_`{}~:\'"]+)\|?\|?([- a-z0-9!#$%&\'*+\/?^_`{|}~:\'"?.]+)?\]\]/ise', // [[P|Yoshitaka Amano||link text]]
		'/\/games\/link\.php\?id=([0-9]{1,4})/ise', // /games/link.php?id=555
		'/\[\[M\|\|([^\|\]]+)\|?\|?([^\]]+)?\]\]/ise', // [[M||/dir||caption]] media dir
		'/\[\[T\|\|([^\|\]]+)\|\|([^\|\]]+)?\|\|([^\|\]]+)?\|?\|?([^\]]+)?\]\]/ise'); // [[T||position||big image||thumbnail||caption?]] thumbnail
	$replacements = array(
		"returnReformattedLink('game', '\\1', '\\2'" . ($prepend_domain ? ",'true'" : "") . ")",
		"returnReformattedLink('game', '\\1', '\\2'" . ($prepend_domain ? ",'true'" : "") . ")",
		"returnReformattedLink('person', '\\1', '\\2'" . ($prepend_domain ? ",'true'" : "") . ")",
		"returnReformattedLink('person', '\\1', '\\2'" . ($prepend_domain ? ",'true'" : "") . ")",
		"returnReformattedLink('game-id', '\\1'" . ($prepend_domain ? ",'true'" : "") . ")",
		"outputMediaDir('\\1', '\\2')",
		"outputThumbnail('\\1', '\\2', '\\3', '\\4')");
	$text = preg_replace($conditions, $replacements, $text);*/
}

function outputMediaDir($dir, $caption="Media") {
	$dir = preg_replace("/\/^/", "", $dir);
	$ret = '<fieldset class="media-selection"><legend>'.$caption.'</legend>';
	if (!$handle = opendir($_SERVER['DOCUMENT_ROOT']."/".$dir."/thumbs")) {
		$ret.= "Couldn't get thumbnails from the specified directory ($dir)</fieldset>";
	} else {
		while (false !== ($file = readdir($handle))) {
			if($file != '.' && $file != '..')
			$ret.= '<a href="'.$dir.$file.'"><img src="'.$dir.'thumbs/'.$file.'" border="0" alt="'.$file.'"/></a> ';
		}
	}
	$ret.= "</fieldset>";
	return $ret;
}

function addPageView ($id='', $pg='', $numformat='') {
	global $db, $usrrank;
	if(!$pg) $pg = preg_replace("/(index.php)$/", "", $_SERVER['SCRIPT_NAME']);
	$query = "SELECT * FROM `pagecount` WHERE `page` = '$pg'";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(!$row = mysqli_fetch_object($res)) {
		$query = "INSERT INTO `pagecount` (`page`, `count`, `corresponding_id`) VALUES ('$pg', 1, '$id')";
		mysqli_query($GLOBALS['db']['link'], $query);
		return '1';
	} else {
		if($usrrank > 6) {
			//dont count admin pageviews
			if($numformat) $row->count = number_format($row->count);
			return $row->count;
		} else {
			$pgcount = $row->count;
			$pgcount++;
			$query = "UPDATE `pagecount` SET `count` = '$pgcount'".($id ? ", `corresponding_id` = '$id'" : "")." WHERE `page` = '$pg'";
			$res = mysqli_query($GLOBALS['db']['link'], $query);
			if($numformat) $pgcount = number_format($pgcount);
			return $pgcount;
		}
	}
}

function printAd($size) {
	if($size == "300x250") {
		echo '<div style="width:300px; height:250px; background-color:#f7f5d1;"><div style="padding:10px;">Advertisement</div></div>';
	}
}

function mysqlNextAutoIncrement($table, $dontdie='') {
	
	$q = "SHOW TABLE STATUS LIKE '$table'";
	$r 	= mysqli_query($GLOBALS['db']['link'], $q) or die ( "Query failed: " . mysqli_error($GLOBALS['db']['link']) );
	$row = mysqli_fetch_assoc($r);
	if($row['Auto_increment']) return $row['Auto_increment'];
	elseif(!$dontdie) die("Couldn't get incremental ID for `$table`");
	
}

function formatName($name) {
	
	//format name and make name url
	//game titles & people names
	//always format before inserting into DB or searching
	
	$name = trim($name);
	$name = urldecode($name);
	$name = str_replace("\n", "", $name);
	$name = str_replace("\r", "", $name);
	$name = str_replace("\t", "", $name);
	$name = str_replace("_", " ", $name);
	$name = preg_replace("/ +/", " ", $name);
	$name = preg_replace("/^(Category:)|(Tag:)/i", "", $name); // remove special namespaces
	$name2 = preg_replace("/\<\>\[\]\|\{\}/", "", $name); // < > [ ] { } |
	if($name2 != $name) return array($name2, "Illegal characters in name; # < > [ ] | { } cannot be used.");
	return array($name, "");
	
}

function formatNameURL($name) {
	
	$name = str_replace(" ", "_", $name);
	$name = str_replace("&", "%26", $name);
	
	/*$namespaces = array("/^Category:/");
	$name = preg_replace($namespaces, "", $name);*/
	
	return $name;
	
}

function htmlent($x) {
	return htmlSC($x);
};
function htmlSC($x) {
	
	// < > ' "
	$x = str_replace('"', '&quot;', $x);
	$x = str_replace("'", "&#039;", $x);
	$x = str_replace("<", "&lt;", $x);
	$x = str_replace(">", "&gt;", $x);
	return $x;
	
}

function dieFullpage($words, $incl_header='') {
	global $page;
	
	if($incl_header) $page->header();
	echo $words;
	$page->footer();
	exit;
	
}

function nl2p($t) {
	$t = nl2br($t);
	$t = str_replace("\r\n", "", $t);
	$t = str_replace("<br /><br />", "</p><p>", $t);
	$t = str_replace("<p></p>", "<br/>", $t);
	return '<p>'.$t.'</p>';
}

function personProfile($pid) {
	$q = "SELECT pid, name, name_url, title, assoc_co FROM people WHERE pid='$pid' LIMIT 1";
	$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
	if(!$dat) return '<div class="personprof">Couldn\'t get person data for ID # \'$pid\'.</div>';
	$pic = "/bin/img/people/".(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/img/people/".$dat->pid."-tn.png") ? $dat->pid."-tn.png" : "nopicture-tn.png");
	$cos = substr($dat->assoc_co, 1, -1);
	if($cos) {
		$arr = array();
		$arr = explode("`", $cos);
		$cos = " for ";
		for($i = 0; $i < count($arr); $i++) {
			if(count($arr) > 2 && $i > 0) $cos.= ', ';
			if(count($arr) > 1 && $i == (count($arr) - 1)) $cos.= ' and ';
			$cos.= '<b>'.$arr[$i].'</b>';
		}
	}
	return '
		<div class="personprof">
			<a href="/people/'.$dat->pid.'/'.$dat->name_url.'">
				<img src="'.$pic.'" alt="'.htmlSC($dat->name).'" border="0"/>
				<big>'.$dat->name.'</big> is a '.($dat->title ? '<b>'.$dat->title.'</b>' : 'game developer').$cos.'
			</a>
		</div>
	';
}

?>