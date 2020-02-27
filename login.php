<?
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