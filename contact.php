<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
require_once ($_SERVER['DOCUMENT_ROOT']."/bin/php/class.authenticate.php");

$page = new page();
$page->title = "Videogam.in / Contact Us";

do if($_POST){
	
	// If the hidden formfield was filled in, it's a bot
	if($_POST['name'] != "") $page->kill("Your contact form was sent...");
	
	if($_POST['_ajax']){
		
		require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.ajax.php");
		$a = new ajax();
		
		$inp = $_POST['_input'];
		parse_str($inp);
		
	} else {
		
		$inp     = $_POST['inp'];
		$email   = $inp['email'];
		$name    = $inp['name'];
		$message = trim($inp['message']);
		if($message == ""){
			$errors[] = "No message input found";
			break;
		}
		
	}
	
	if($usrid){
		$user = new user($usrid);
		if(!$email) $email = $user->email;
		if(!$name) $name = $user->username;
	}
	
	if(!$name) $name = "A Videogam.in user";
	if(!$email) $email = "noreply@videogam.in";
	
	$mail_headers = "From: Videogam.in <noreply@videogam.in>\r\n" . 
	                "Reply-To: " . $email . "\r\n" .
	                "X-Mailer: PHP/" . phpversion();
	$mail_to      = $default_email;
	$mail_subject = "Videogam.in Message";
	$mail_message = "The following message is from ".$name." <".$email.">:\n\n".$message.($frompage ? "\n\nPage: http://videogamin.squarehaven.com".$frompage : "\n\nThis message was sent via contact form from ".$_SERVER['SCRIPT_NAME']);
	$mail_result  = mail($mail_to, $mail_subject, $mail_message, $mail_headers);
	
	if($mail_result){
 		$results[] = "<b>Success!</b> Your message has been sent.".($frompage ? ' <b><a href="'.$frompage.'" class="arrow-left">Back to where you came from</a></b>' : '');
 		$ret['success'] = 1;
	} else {
 		$errors[1] = 'There was an error and your message could not be sent. Please email <a href="mailto:'.$default_email.'">'.$default_email.'</a>.';
 		$ret['error'] = "There was an error and your message could not be sent. Please email <$default_email>.";
	}
	
	if($_POST['_ajax']){
		$a->ret = $ret;
		exit;
	}
	
	if(!$errors){
		
		//Form should have been sent 
		
		$page->header();
		
		?>
		<div style="background-color:black; padding:5px;">
			<div style="position:relative; background:url('/bin/img/promo/toadstool_letter_bg.png') repeat 0 0; padding:20px;">
				<div style="background:white; padding:50px 90px 50px 90px; white-space:pre-wrap;"><?=$message?></div>
				<div style="background:url('/bin/img/promo/toadstool_letter_sprites.png') no-repeat 0 -36px; width:44px; height:70px; position:absolute; bottom:40px; left:40px;"></div>
			</div>
		</div>
		<br/><br/>
		Your message has been sent!
		<?
	
		if($usrrank >= 9) echo "<p>[" . htmlentities($mail_headers) . "]</p>";
		
		$page->footer(); exit;
		
	}
	
} while(false);

$page->freestyle = '
.contactform .yourname {display:block; height:10px; width:1px; margin:-10px 0 0; overflow:hidden; }
';

$page->header();

?>
<h1>Contact Us</h1>

<form action="contact.php" method="post" class="contactform">
	<input type="hidden" name="usrid" value="<?=$usrid?>"/>
	<label class="yourname"> <input type="text" name="name">Your name</label>
	<table border="0" cellpadding="10" cellspacing="0" width="100%">
		<tr>
			<th>Your Name:</th>
			<td><input type="text" name="inp[name]" value="<?=htmlsc($inp['name'])?>" size="50"/></td>
		</tr>
		<tr>
			<th nowrap="nowrap">E-mail Address: &nbsp;&nbsp;&nbsp;</th>
			<td width="100%"><input type="text" name="inp[email]" value="<?=htmlsc($inp['email'])?>" size="50"/></td>
		</tr>
		<tr>
			<th>Message:</th>
			<td>
				<div class="inpfw">
					<textarea name="inp[message]" rows="10"><?=$inp['message']?></textarea>
				</div>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="submit"/></td>
		</tr>
	</table>
</form>

<?

$page->footer();

?>