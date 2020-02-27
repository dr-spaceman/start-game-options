<?
define("STEAM_CONDENSER_PATH", $_SERVER['DOCUMENT_ROOT']."/bin/php/steam-condenser/lib/");

require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/openid.php";
require_once STEAM_CONDENSER_PATH."steam/community/SteamId.php";

$page = new page();
$page->title = "Register with Steam -- Videogam.in";
$page->superminimalist = true;

try {
    $openid = new LightOpenID('http://videogam.in');
    if(!$openid->mode) {
    	$openid->identity = "http://steamcommunity.com/openid";
            # The following two lines request email, full name, and a nickname
            # from the provider. Remove them if you don't need that data.
            //$openid->required = array('contact/email');
            //$openid->optional = array('namePerson', 'namePerson/friendly');
      header("Location: ".$openid->authUrl());

    } elseif($openid->mode == 'cancel') {
        echo 'User has canceled authentication!';
    } else {
    	if($openid->validate()){
    		
    		$steam_id = str_replace("http://steamcommunity.com/openid/id/", "", $openid->identity);
    		
    		$steamuser = new SteamId($steam_id);
    		
    		//Debug
    		/*?><h1><?=$steamuser->nickname?></h1><pre><? print_r($steamuser); ?></pre><? exit;*/
    		
    		// automatically reconcole existing Videogamin user account with Steam account
			  if($usrid){
			  	$q = "SELECT * FROM users_oauth WHERE usrid='$usrid' AND oauth_provider='steam' LIMIT 1";
			  	$res = mysql_query($q);
			  	if(!mysql_num_rows($res)){
				  	$q = "INSERT INTO users_oauth (usrid, oauth_provider, oauth_usrid, oauth_username) VALUES ('$usrid', 'steam', '".mysql_real_escape_string($steam_id)."', '".mysql_real_escape_string($steamuser->nickname)."');";
						if(!mysql_query($q)){
							sendBug("login_steam.php Error reconciling Vg.in account with St acct [$q]: ".mysql_error());
							die("Sorry, there was a database error and we couldn't link your steam account to your Videogam.in account. <a href=\"".($_COOKIE['lastpage'] ? $_COOKIE['lastpage'] : "http://videogam.in")."\">Back to Videogam.in</a>");
						}
						header("Location:http://videogam.in/account.php?edit=prefs&steamconnectedsuccess=1");
				  	exit;
				  } else {
				  	//check if it's the same account
				  	$row_oauth = mysql_fetch_assoc($res);
				  	if($row_oauth['oauth_usrid'] != $steam_id) die("There was an account conflict! Your steam account is already connected to a different username.");
			  	}
			  }
			  
			  // login existing steam user
			  $q = "SELECT * FROM users_oauth LEFT JOIN users USING (usrid) WHERE oauth_provider = 'steam' AND oauth_usrid='".mysql_real_escape_string($steam_id)."' LIMIT 1";
			  if($row = mysql_fetch_assoc(mysql_query($q))){
			  	login($row);
			  	header("Location:".($_COOKIE['lastpage'] ? $_COOKIE['lastpage'] : "http://videogam.in"));
			  	exit;
			  }
			  
			  // New user :D
			  
			  $usrname = preg_replace("/[^a-z0-9\-\_]/i", "", $steamuser->nickname);
			  $i = 0;
			  while(mysql_num_rows(mysql_query("SELECT username FROM users WHERE username = '".mysql_real_escape_string($usrname)."' LIMIT 1"))){
			  	$i++;
			  	if($i > 1) $usrname = substr($usrname, 0, -1);
			  	$usrname.= $i;
			  	if($i == 100) break;
			  }
			  
			  // register
			  $sub = array(
			  	"username" => $usrname,
			  	"email" => '',
			  );
			  $udata = array(
			  	"oauth_provider" => "steam",
			  	"oauth_usrid" => $steam_id,
			  	"oauth_username" => $steamuser->customUrl,
			  	"location" => $steamuser->location,
			  	"name" => $steamuser->realName,
			  	"link" => (string)$steamuser->links[0]
			  );
			  $_POST['udata'] = http_build_query($udata);
			  $_POST['udata'] = base64_encode($_POST['udata']);
			  
				$page->css[] = "/bin/css/register.css";
				$page->freestyle.= 'h3 { display:none }';
				$page->first_section['id'] = "steam-reg";
			  $page->header();
			  
			  ?>
			  <h1>Connect with Steam</h1>
			  <p>Just one more step to create your new account.</p>
			  <p>Already have a Videogam.in account? <a href="/login.php">Log in</a> and connect with Steam.</p>
			  <?
			  
			  include("register_form.incl.php");
			  
			  $page->footer();
			  exit;
    		
    	} else {
    		echo "Not valid.";
    	}
    }
} catch(ErrorException $e) {
	$page->header();
	?><h1>Connect with Steam</h1>
	<p><?=$e->getMessage()?></p>
	<ul>
		<li><a href="/register.php" class="arrow-right">Register a new account</a></li>
		<li><a href="/login_steam.php">Try Steam connection again</a></li>
	</ul>
	<?
	$page->footer();
	exit;
	
}

exit;


require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";
require_once STEAM_CONDENSER_PATH."steam/community/SteamId.php";

$apikey = "84B61DCEDFBA6909C33D0DC93D4E51D2";

$id = $_GET['steamid'];
$user = new SteamId($id);
$stats = $user->getGameStats('portal2');
$achievements = $stats->getAchievements();

?>
<!doctype html>
<html>
  <head>
    <title>Steam / <?=$id?></title>
  </head>
  <body>
    <h1>Steam</h1>
    
    <h2><?=$id?></h2>
    <pre><? print_r($id); ?></pre>
    
    <h2>Portal 2 stats</h2>
    <pre><? print_r($stats); ?></pre>
    
    <h2><?=$id?>'s achievments</h2>
    <pre><? print_r($achievements); ?></pre>
  </body>
</html>
