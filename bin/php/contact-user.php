<? 
require ($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");

$page = new page;

$page->title  = "Videogam.in / Contact User";

//$_GET vars
if($_GET['user']) $user = $_GET['user'];
elseif($_POST['user']) $user = $_POST['user'];
if($_GET['method']) $method = $_GET['method'];
elseif($_POST['method']) $method = $_POST['method'];
if(!$method) $method = "email";
if($_GET['subject']) $subject = $_GET['subject'];
elseif($_POST['subject']) $subject = $_POST['subject'];
$subject = htmlentities($subject, ENT_QUOTES);
$message = htmlentities($_POST['message'], ENT_QUOTES);
$reply_to_id = $_POST['reply_to_id'];
$to = filter_input(INPUT_POST, 'to'); // a usrid

if($user && $method == "pm" && !$usrid) {
	$page->header();
	?>
	<h2>Contact a User</h2>
	Please <a href="#x" onclick="toggle('login');">login</a> to send a PM.
	<?
	$page->footer();
	exit;
}

if($_POST['send']) {
	
	if(!$user) die("No user id given");
	
	if($_POST['math'] != $_POST['math1'] + $_POST['math2']) $errors[] = "Your math is wrong. Are you a human?";
	
	if(!$subject || !$message) $errors[] = "You must have a subject and message";
	
	if($method == "email") {
		
		//get $to
		$usr = User::getById($to);
		if(!$usr->data['email']) $errors[] = "Couldn't get user's e-mail address from the database! Try <a href=\"?user=$user&method=pm\">sending a private message</a> instead (make sure to copy your message below).";
		
		$sdat = getUserDat($usrid);
		
		if(!$errors) {
			
			$message = "You have received a message from a Videogam.in user:\n\nFrom: ".$_POST['name']."\nSubject: ".strip_tags($subject)."\nMessage:\n".$message;
			$message = wordwrap($message, 70);
			if($usrid) $message.= "\n\nSee ".$usrname."'s profile here -> http://videogam.in/~".$usrname."\n";
			$message.= "\n\nThis message was sent via contact form from ".$_SERVER['SCRIPT_URI'];
			$headers = 'From: ' . $_POST['email'] . "\r\n" .
			    'Reply-To: ' . $_POST['email'] . "\r\n" .
			    'X-Mailer: PHP/' . phpversion();
			
			if(mail($usr->data['email'], "Message from a Videogam.in user: ".strip_tags($subject), $message, $headers)) {
				$page->header();
				?>
				<h2>Message Sent</h2>
				Your e-mail message has been successfully sent to <?=$user?>.
				<?
				$page->footer();
				exit;
			} else {
				$errors[] = "There was a problem with the mail server and your e-mail couldn't be sent! Try <a href=\"?user=$user&method=pm\">sending a private message</a> instead (make sure to copy your message below).";
			}
			
		}
		
	} elseif($method == "pm" && !$errors) {
		
		$subject = strip_tags($subject);
		$subject = addslashes($subject);
		$message = strip_tags($message);
		$message = addslashes($message);
		
		$query = sprintf("INSERT INTO `pm` (`to`, `from`, `date`, `subject`, `message`, `reply_to_id`) VALUES 
			('$to', '$usrid', '".date('Y-m-d H:i:s')."', '%s', '%s', '$reply_to_id')",
			mysqli_real_escape_string($GLOBALS['db']['link'], $subject),
			mysqli_real_escape_string($GLOBALS['db']['link'], $message));
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		if(!$res) $errors[] = "Couldn't process PM because of a database error!";
		else {
			
			//e-mail recipient if s/he allows e-mails
			$dat = getUserDat($to);
			if($dat->pm_notify) {
				$message = $user.",\nYou have received a private message from ".$usrname."! Huzzah!\nLog into your Videogam.in account to retrieve it.\n---> http://videogam.in\n\nSincerely,\nThe Vapid Videogam.in Private Messaging Robot\n\nP.S.: You can turn off these notifications via your account page\n-> http://videogam.in/account.php";
				$headers = 'From: noreply@videogam.in'."\r\n" .
				    'Reply-To: noreply@videogam.in'."\r\n" .
				    'X-Mailer: PHP/' . phpversion();
				
				if(!@mail($dat->email, "Private Message from a Videogam.in user", $message, $headers)) {
					sendBug("Couldn't notify user of PM");
				}
			}
			
			$page->header();
			?>
			<h2>Message Sent</h2>
			Your PM has been sent! <a href="/messages.php" class="arrow-right">Your inbox</a>
			<?
			$page->footer();
			exit;
			
		}
	}
}

$page->header();

?>
<h2>Contact User</h2>
<?

if(!$user) {
	
	?>
	<form action="contact-user.php" method="get">
		Contact <input type="text" name="user"/> via 
		<select name="method">
			<option value="email">e-mail</option>
			<option value="pm">private message</option>
		</select> 
		<input type="submit" value="Go to contact form &gt;" />
	</form>
	<?

} else {
	
	$usr = User::getByUsername($user);
	$usr_prefs = $usr->getPreferences();
	
	if($method == "email") {
		//does user allow emails?
		if(!$usr_prefs['mail_from_users'] && $usrrank <= 8) {
			echo 'Sorry, '.$usr->getUsername().' doesn\'t allow mail from other users. You may of course send them a <a href="?user='.$usr->getUsername().'&method=pm">private message</a> though!';
			$page->footer();
			exit;
		}
	}
	
	//reply stuff
	if($reply_to_id = $_GET['reply_to_id']) {
		$query = "SELECT * FROM `pm` WHERE `id` = '$reply_to_id' LIMIT 1";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		$reply_dat = mysqli_fetch_object($res);
		$subject = $reply_dat->subject;
		$reply_message = "\n\n\n------ Original Message ------\nFrom: ".outputUser($reply_dat->from, FALSE, FALSE)."\nTo: ".outputUser($reply_dat->to, FALSE, FALSE)."\nDate: ".$reply_dat->date."\nSubject: ".stripslashes($reply_dat->subject)."\n\n".stripslashes($reply_dat->message);
	}
	
	//randomize validation
	$rand1 = rand(0,4);
	$rand2 = rand(1,9);
	
	?>
	<?=($usrid ? '' : 'Since you are not <a href="/login.php?loc=contact-user.php&locs=user:'.$user.',method=pm">logged in</a>, your identity will not be supplied, so please identify yourself.<br />')?>
	<form action="contact-user.php" method="post">
		
		<input type="hidden" name="to" value="<?=$usr->usrid?>"/>
		<input type="hidden" name="user" value="<?=$user?>"/>
		<input type="hidden" name="method" value="<?=$method?>"/>
		<input type="hidden" name="reply_to_id" value="<?=$reply_to_id?>"/>
		<input type="hidden" name="math1" value="<?=$rand1?>"/>
		<input type="hidden" name="math2" value="<?=$rand2?>"/>
		
		<fieldset style="margin-top:1em; background-color:#F5F5F5; border:1px dotted #C0C0C0;">
			<legend>Contact <big><a href="/~<?=$usr->username?>"><?=$usr->username?></a></big> via <big><?=$method?></big></legend>
			<table border="0" cellpadding="0" cellspacing="10">
				<tr<?=($method == "pm" ? ' style="display:none;"' : '')?>>
					<td>Your name:</td>
					<td><input type="text" name="name" value="<?=($_POST['name'] ? $_POST['name'] : $usrname)?>" size="18" maxlength="40"/></td>
					<td>
						<?
						$dat = getUserDat($usrid);
						?>
						Your e-mail address: <input type="text" name="email" value="<?=($_POST['email'] ? $_POST['email'] : $dat->email)?>" size="35" maxlength="100"/>
					</td>
				</tr>
				<?=($method == "pm" ? '<tr><td colspan=2">'.$usr->username.' will'.($usr->pm_notify ? '' : ' not').' be notified via e-mail about this PM.</td></tr>' : '')?>
				<tr>
					<td>Subject:</td>
					<td colspan="2"><input type="text" name="subject" value="<?=$subject?>" size="80" maxlength="50" /></td>
				</tr>
				<tr>
					<td valign="top">Message:<?=($method == "pm" ? "<br /><small style=\"color:#808080;\">No HTML permitted</small>" : "")?></td>
					<td colspan="2"><textarea name="message" rows="10" cols="90" maxlength="5000"><?=($_POST['message'] ? $_POST['message'] : $reply_message)?></textarea></td>
				</tr>
				<tr>
					<td valign="top"><img src="/bin/img/numbers/<?=$rand1?>.png" alt="random number" /> + <img src="/bin/img/numbers/<?=$rand2?>.png" alt="random number" /> =</td>
					<td colspan="2"><input type="text" name="math" size="1" maxlength="2" /> <small style="font-size:12px;color:#808080;">(just to prove you are human and not an evil spamming robot)</small></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td colspan="2"><input type="submit" name="send" value="Send Message" /></td>
				</tr>
			</table>
		</fieldset>
	</form>
	<?

}
	

$page->footer();?>