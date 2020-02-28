<?
require ($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");
$page = new page;

$page->title = "Videogam.in / Reset Password";
$page->width = "fixed";

if($in = $_POST['in']) {
	if($in['submit']) {
		//find username or email
		$in['username'] = trim($in['username']);
		$in['email'] = trim($in['email']);
		if($in['username']) $q = sprintf("SELECT * FROM users WHERE username='%s' LIMIT 1", mysqli_real_escape_string($GLOBALS['db']['link'], $in['username']));
		elseif($in['email']) $q = sprintf("SELECT * FROM users WHERE email='%s' LIMIT 1", mysqli_real_escape_string($GLOBALS['db']['link'], $in['email']));
		if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
			if($in['username']) $errors[] = "Couldn't find username '".$in['username']."' in the database";
			elseif($in['email']) $errors[] = "Couldn't find e-mail address '".$in['email']."' in the database";
		} else {
			$code = rand(10,99);
			$code.= 'a';
			$code.= rand(100,999);
			$code.= 'n';
			$code.= rand(1,9);
			$q = "INSERT INTO users_temp_pass (usrid, code) VALUES ('".$dat->usrid."', '$code')";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) {
				$errors[] = "Couldn't complete process because of a database error. The staff has been notified of this error.";
				sendBug("Couldn't reset password because of db error: $q");
			} else {
				$p_code = base64_encode($code);
				$p_code = urlencode($p_code);
				$to      = $dat->email;
				$subject = 'Videogam.in password reset instructions';
				$message = "Dear ".$dat->username.",\nYou (or someone else) requested your Videogam.in password to be reset. If it wasn't you ignore this message without any change to your account. Otherwise, to reset your password please navigate to the following URL:\n\nhttp://videogam.in/retrieve-pass.php?do=reset&usrid=".$dat->usrid."&code=$p_code\n(Your username is ".$dat->username.")\n\nSincerely,\nThe Videogam.in Password-changing Robot";
				$headers = 'From: no-reply@videogam.in' . "\r\n" .
				    'Reply-To: no-reply@videogam.in' . "\r\n" .
				    'X-Mailer: PHP/' . phpversion();
				if(!mail($to, $subject, $message, $headers)) {
					$errors[] = "Couldn't send e-mail instructions. The staff has been notified of this error.";
					sendBug("Couldn't send email instructions to change password");
				} else {
					$results[] = "Instructions on resetting your account password have been sent to ".$dat->email;
				}
			}
		}
	}
}

if($_GET['do'] == "reset") {
	if(!$_GET['usrid']) $error[] = "Fatal error: no user id given";
	if(!$code = base64_decode($_GET['code'])) $errors[] = "Fatal error: no code given";
	$q = sprintf("SELECT * FROM users_temp_pass WHERE usrid='%s' AND code='%s' LIMIT 1",
		mysqli_real_escape_string($GLOBALS['db']['link'], $_GET['usrid']),
		mysqli_real_escape_string($GLOBALS['db']['link'], $code));
	if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) {
		$errors[] = "Couldn't reconcile given data with database data. Password reset can't continue.";
	} else {
		
		$page->javascript.= <<<EOF
		<script type="text/javascript">
			function validatePasswordReset() {
				var errormsg = "";
				var npass1 = document.getElementById('newpass1').value;
				var npass2 = document.getElementById('newpass2').value;
				if(!npass1 || !npass2) errormsg = "Password is blank";
				if(npass1 != npass2) errormsg = "Passwords don't match";
				if(errormsg) {
					alert("Error: "+errormsg+". Password not reset. ("+npass1+"|"+npass2+")");
					return false;
				} else {
					return true;
				}
			}
		</script>
EOF;

		$page->header();
		
		?>
		<h1>Reset your password</h1>
		
		Changing the password for username: <b><?=outputUser($_GET['usrid'], FALSE, FALSE)?></b>
		<br/><br/>
		
		<form action="retrieve-pass.php" method="post">
			<input type="hidden" name="usrid" value="<?=$_GET['usrid']?>"/>
			<input type="password" name="pass1" id="newpass1"/> Input a new password
			<p><input type="password" name="pass2" id="newpass2"/> Confirm new password</p>
			<p><input type="submit" name="submit-new-pass" value="Reset Password" onclick="return validatePasswordReset()"/></p>
		</form>
		<?
		$page->footer();
		exit;
	}
}

if($_POST['submit-new-pass']) {
	if(!$p1 = $_POST['pass1']) die("No password 1 given");
	if(!$p2 = $_POST['pass2']) die("No password 2 given");
	if($p1 != $p2) die("Passwords don't match");
	if(!$_POST['usrid']) die("No usrid given");
	$q = "UPDATE users SET password = PASSWORD('$p1') WHERE usrid='".$_POST['usrid']."' LIMIT 1";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) {
		$errors[] = "Couldn't reset password. The staff has been informed of this error.";
		sendBug("Couldn't reset password: $q");
	} else {
		$q = "DELETE FROM users_temp_pass WHERE usrid='".$_POST['usrid']."'";
		@mysqli_query($GLOBALS['db']['link'], $q);
		$results[] = 'Password successfully reset! You may now <a href="#login">login</a> with your fresh new password';
	}
}

if($usrid) header("Location: /");

$page->header();

?>

<h2>Reset Password</h2>

To reset your password, please input either your username or e-mail address.<br/>
You should receive further instruction in your e-mail inbox.<br/><br/>

<form action="retrieve-pass.php" method="post">
	<table border="0" cellpadding="5" cellspacing="0">
		<tr>
			<td><b>Username:</b>&nbsp;&nbsp;</td>
			<td><input type="text" name="in[username]"/></td>
		</tr>
		<tr>
			<td><div align="center">- OR -</div></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><b>E-mail:</b></td>
			<td><input type="text" name="in[email]"/></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="in[submit]" value="Submit"/></td>
		</tr>
	</table>
</form>

<?

$page->footer();

?>