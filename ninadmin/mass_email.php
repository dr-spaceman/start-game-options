<?
use Vgsite\Page;
require $_SERVER['DOCUMENT_ROOT']."/bin/php/bbcode.php";
require $_SERVER['DOCUMENT_ROOT']."/bin/php/swiftmailer/lib/swift_required.php";

$page =  new Page();
$page->title = "Videogam.in Admin / Mass E-mail Users";

if($_SESSION['user_rank'] < 8){ include("../404.php"); exit; }

$limit = 30; //send # emails at once

$in = $_POST['in'];

if(isset($_POST['min'])){
	
	$min = $_POST['min'];
	$max = $_POST['num'];
  
	$to = array();
	
	$q = $_POST['query'] . " LIMIT $min, $limit";
	$res = mysqli_query($GLOBALS['db']['link'], $q);
	while($row = mysqli_fetch_assoc($res)){
		$q2 = "SELECT * FROM users_prefs WHERE usrid='".$row['usrid']."' AND mail_from_admins = '0' LIMIT 1";
		$res2 = mysqli_query($GLOBALS['db']['link'], $q2);
		if(!$_POST['bypass'] && mysqli_num_rows($res2)){
			$_POST['num']--;
			continue;
		}
		$row['email'] = trim($row['email']);
		if (filter_var($row['email'], FILTER_VALIDATE_EMAIL) $to[$row['email']] = $row['username'];
	}
	
	if(!$_POST['testmail']){
		echo Page::HTML_TAG;
		?>
		<head>
			<script type="text/javascript" src="/bin/script/jquery.js"></script>
			<script>
				$(document).ready(function(){
					$("body").animate({opacity:1}, 1000, function(){
						if($("#massemailform2").length) $("#massemailform2").submit();
					})
				});
			</script>
		</head>
		<body style="margin:0; padding:0; font:normal 13px arial;">
		&gt; Sending <?=($min + 1)?> &ndash; <?=($min + $limit > $max ? $max : $min + $limit)?> of about <?=$max?>... 
		<?
	}
	
	$transport = Swift_MailTransport::newInstance();
	$mailer    = Swift_Mailer::newInstance($transport);
	$message   = Swift_Message::newInstance()
		->setSubject($_POST['subject'] . ($_POST['testmail'] ? ' [TEST]' : ''))
		->setFrom(array('no-reply@videogam.in' => 'Videogam.in'));
	
	if($_POST['testmail']){
		$me = User::getById($usrid);
		$to = array($me->data['email'] => $me->getUsername());
	}
	
	foreach($to as $address => $name){
		$body = $_POST['body'];
		$body = str_replace("##USERNAME##", $name, $body);
		$body.= '<hr/><small>Your account: http://videogam.in/~'.$name.'<br/>Unsubscribe: http://videogam.in/account.php?mlist='.base64_encode($address).'</small>'; 
		$message->setBody($body, 'text/html');
	  $message->setTo(array($address => $name));
	  $numSent += $mailer->send($message, $failedRecipients);
	}
	
	if($_POST['testmail']){
		if($numSent) die("ok");
		else die("nonesent");
	}
	
	echo "Sent $numSent... ";
	
	if($min + $limit > $max) die(" All Fin.</body></html>");
	
	$_POST['min'] = $min + $limit;
	
	?>
	<form action="mass_email.php" method="post" id="massemailform2">
		<input type="hidden" name="min" value="<?=$_POST['min']?>"/>
		<input type="hidden" name="query" value="<?=$_POST['query']?>"/>
		<input type="hidden" name="num" value="<?=$_POST['num']?>"/>
		<input type="hidden" name="bypass" value="<?=$_POST['bypass']?>"/>
		<textarea name="subject" style="display:none"><?=$_POST['subject']?></textarea>
		<textarea name="body" style="display:none"><?=$_POST['body']?></textarea><input type="submit"/>
	</form>
	</body></html>
	<?
	
} else {
	
	if($_POST){
		
		// PREVIEW //
		
		if(!$in['send_to']) $errors[] = "No users selected";
		if(!$in['message'] = trim($in['message'])) $errors[] = "No message input";
		$bb = new bbcode();
		$bb->text = $in['message'];
		$in['message'] = bb2html($in['message']);
		$in['message'] = closeTags($in['message']);
		if(!$in['subject'] = trim($in['subject'])) $errors[] = "No subject input";
		
		$message_formatted = $in['message'];
		//wordwrap($in['message'], 78);
		//$message_formatted = htmlspecialchars($message_formatted);
		
		$q = "SELECT username, usrid, email FROM users WHERE (";
		foreach($in['send_to'] as $s) {
			$q.= "`rank`='$s' OR ";
		}
		$q = substr($q, 0, -4);
		$q.= ") ";
		if(!$in['send_unverified']){
			$q.= "AND `verified`='1' ";
		}
		$q.= "AND `activity` > '2010-01-01'";
		
		if(!$errors){
		
			if(!$num = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) {
				$errors[] = "Couldn't compile e-mail list from database: $q";
			} else {
				
				$header_set = TRUE;
				
				$page->javascript.='
					<script>
						function sendTest(){
							$("#sendtest").text("Sending test...").attr("disabled", true);
							$.post("/ninadmin/mass_email.php", $("#massemailsubmit").serialize() + "&testmail=1", function(ret){
								if(ret == "ok"){
									$("#sendtest").text("Sent");
								} else {
									$("#sendtest").text("Send Test Failed");
								}
								$("#sendtest").animate({opacity:1}, 4000, function(){ $("#sendtest").text("Send Test Mail").removeAttr("disabled") });
							});
						}
					</script>
				';
				
				$page->header();
				
				?><h1>Mass E-mail Users</h1>
				
				<iframe name="massemailframe" id="massemailframe" frameborder="0" style="display:none; width:100%; height:4em;"></iframe>
				
				<fieldset style="width:700px;">
					<legend>Preview Mass E-mail</legend>
				
					<div style="margin:0 0 10px; padding:1em; background-color:white; font-size:13px;">
						<b><?=$in[subject]?></b><br/>
						<?=$message_formatted?>
					</div>
					
					<form action="mass_email.php" method="post" target="massemailframe" id="massemailsubmit">
						
						<input type="hidden" name="min" value="0"/>
						<input type="hidden" name="query" value="<?=$q?>"/>
						<input type="hidden" name="num" value="<?=$num?>"/>
						<input type="hidden" name="bypass" value="<?=$in['bypass']?>"/>
						<textarea name="subject" style="display:none"><?=$in['subject']?></textarea>
						<textarea name="body" style="display:none"><?=$in['message']?></textarea>
						
						<div style="float:right;">
							<button id="sendtest" onclick="sendTest()">Send Test Mail</button> 
							<input type="submit" name="commence" value="Send Mail" style="font-weight:bold;" onclick="$('html, body').animate({scrollTop:0},500); $(this).closest('fieldset').hide(); $('#massemailframe').show(); $('#massemailform').attr('disabled', true);"/>
						</div>
						<span style="line-height:24px">This e-mail will be sent to approximately <b><?=$num?></b> users.</span>
						
					</form>
					
				</fieldset>
				
				<br/>
				<p>Make changes:</p>
				<?
				
			}
		}
	}
	
	if(!$header_set){
		$page->header();
		?><h1>Mass E-mail Users</h1><?
		$in['send_to'] = array(1, 2, 3, 4, 5, 6, 7, 8, 9);
	}
	
	?>
		
	<form action="mass_email.php" method="post" id="massemailform">
		<div class="fftt">
			<input type="text" name="in[subject]" value="<?=str_replace('"', '`', $in['subject'])?>" size="60" class="ff"/>
			<label class="tt">Subject</label>
		</div>
		<br/>
		
		<textarea name="in[message]" cols="90" rows="18"><?=$in['message']?></textarea>
		<br/><br/>
		
		<b>Send to:</b>
		<ul style="list-style:none;">
			<?
			$query = "SELECT * FROM users_ranks WHERE rank > 0 ORDER BY rank";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)) {
				echo '<li><label><input type="checkbox" name="in[send_to][]" value="'.$row['rank'].'"'.(in_array($row['rank'], $in['send_to']) ? ' checked="checked"' : '').'/> '.$row['description'].'s</label></li>';
			}
			?>
		</ul>
		<br/>
		
		<b>Options:</b>
		<ul style="list-style:none">
			<li><label><input type="checkbox" name="in[bypass]" value="1"<?=($in['bypass'] ? ' checked="checked"' : '')?>/> Ignore users who opt out of staff e-mails</label></li>
			<li><label><input type="checkbox" name="in[send_unverified]" value="1"<?=($in['send_unverified'] ? ' checked="checked"' : '')?>/> Send to unverified e-mail addresses</label></li>
		</ul>
		<br/>
		
		<input type="submit" name="submit" value="Submit & Preview"/>
		
	</form>
	<?
	
	$page->footer();
	
}

?>