<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Videogam.in, a website about videogames (COMING SOON)</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
	<meta name="keywords" content="Nintendo Wii, Nintendo DS, Xbox, Playstation, PSP, NES, Super NES, SUper Nintendo, Game boy, videogames, cheats, game guide, guides, music, game music, Mario, Zelda, Square Enix, Final Fantasy, Kingdom Hearts, Dragon Quest, Nobuo Uematsu, Hironobu Sakaguchi, Tetsuya Nomura, Yoshitaka Amano"/>
	<meta name="description" content="A website about videogames"/>
	<meta name="DC.title" content="Videogam.in"/>
	<style>
		HTML, BODY { height:100%; }
		BODY { display:block; background:#537ca6 url(/splash-bg.png) no-repeat center center; margin:0; padding:0; font:normal 16px arial; color:white; }
		A { color:white; }
		TD B { font-size: 13px; }
		INPUT { padding-top:3px; padding-bottom:3px; font:normal 14px arial; border-style:solid; -moz-border-radius:3px; -webkit-border-radius:3px; }
		INPUT[type=text], INPUT[type=password] { padding-left:3px; border-width:1px 0 0 1px; border-color:#314a63 #8aadcf #8aadcf #314a63; background-color:white; }
		INPUT[type=submit] { border-width: 0 1px 1px 0; padding-top:2px; padding-bottom:2px; font-weight:bold; color:#444; background:#DDD url(/bin/img/styled-button-bg.png) repeat-x 0 -1px;  border-color:#999 #666 #666 #999; }
		FIELDSET { float: left; margin:20px 0 3px; border:1px inset #769abe; font-size:13px; }
		LEGEND { font:bold 15px arial; }
		A.login { 
			display:block; padding:8px 0; text-decoration:none; font-weight:bold; text-align:center;
			background: url("/bin/img/gradient-b2t.png") repeat-x scroll 0 -62px;
			-moz-border-radius:20px; -webkit-border-radius:20px; font-size:14px;
			box-shadow: 0 0 2px 1px #537CA6; -moz-box-shadow:0 0 2px 1px #537CA6; -webkit-box-shadow:0 0 2px 1px #537CA6;
		}
	</style>
	<script type="text/javascript" src="/bin/script/jquery-1.4.1.js"></script>
</head>
<body>

<div style="height:100%; min-height:100%;">

<h1 style="visibility:hidden; margin:0;">Videogam.in, a website about videogames</h1>

<div style="width:220px; margin:5% auto;">
	<a href="#login" class="login" style="" onclick="$(this).parent().hide().next().show();">Registered Users Log In</a>
</div>

<div id="login" style="display:none; width:220px; margin:0 auto;">
	<fieldset id="form-login">
		<legend><b><big>Log in to Videogam.in</big></b></legend>
	  <form method="post" action="/" style="margin-top:5px">
	    <input type="hidden" name="do" value="login"/>
	    <table border="0" cellpadding="2" cellspacing="0">
	    	<tr>
	    		<td>Username</td>
	    		<td><input type="text" name="username" id="login-username" size="13" maxlength="25"/></td>
	    	</tr>
	    	<tr>
	    		<td>Password</td>
	    		<td><input type="password" name="password" id="login-password" size="13" maxlength="16"/></td>
	    	</tr>
	    	<tr>
	    		<td nowrap="nowrap"><label><input type="checkbox" name="remember" value="1"/>Remember</label></td>
	    		<td style="text-align:right;"><input type="submit" name="login" value="Log in" style="font-weight:bold"/></td>
	    	</tr>
	    	<tr>
	    		<td colspan="2" style="text-align:right;"></td>
	    	</tr>
	    </table>
	  </form>
	</fieldset>
	<div style="text-align:center; font-size:12px;"><a href="/retrieve-pass.php" id="forgot-info">Retrieve forgotten password</a> &middot; <a href="/register.php">Register</a></div>
</div>

<div style="position:absolute; bottom:22%; width:100%; text-align:center; font-size:15px; color:#1b2d3f; line-height:1.5em;">
	<?
	$_POST['email'] = trim($_POST['email']);
	
	if($_POST['email'] && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		echo "The e-mail address <i>".$_POST['email']."</i> doesn't appear to be valid.<br/>";
		unset($_POST['email']);
	}
	
	if($_POST['email']) {
		$q = "SELECT * FROM mailing_list WHERE `email` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['email'])."' LIMIT 1";
		if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) echo "You are already subscribed!";
		else {
			$q = "INSERT INTO mailing_list (email, `datetime`) VALUES ('".$_POST['email']."', '".date("Y-m-d H:i:s")."');";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) echo "There was an error and the e-mail couldn't be added to the list.";
			else echo "<b>Thanks!</b> We'll e-mail you with any pertinent updates and let you know when we're open.";
		}
	} else {
		?>
		<form action="/index.php" method="post" style="margin:0;">
			Enter your e-mail address below to receive updates.<br/>
			<input type="text" name="email" size="35" maxlength="55"/> 
			<input type="submit" value="Submit"/>
		</form>
		<?
	}
	?>
</div>

<div style="position:absolute; bottom:5%; width:100%; text-align:center; line-height:1.5em;">
	Videogam.in is currently deep in development, but we expect to open sometime in Spring, 2010<br/>
	<a href="http://tinyurl.com/y9twoqa">Contact Us</a> | 
	<a href="http://tinyurl.com/y9twoqa">Jobs</a> | 
	<a href="http://twitter.com/videogamin" style="padding-right:22px; background:url(/bin/img/twitter_tiny_white.png) no-repeat right center;">Follow us on Twitter</a>
</div>

</div>

</body>
</html>