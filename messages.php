<? 
require $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";

$page = new page();

$page->title  = "Videogam.in / Private Messaging";
$page->css[]  = "/bin/css/messages.css";

$page->javascript.= '
<script type="text/javascript">
<!--
$(document).ready(function(){
	$(".delmsg").click(function(ev){
		if(!confirm("Permantently delete this message?")) ev.preventDefault();
	});
	$("tr.hilite").hover(function(){
		$(this).addClass("hilite-on");
	}, function(){
		$(this).removeClass("hilite-on");
	});
});
-->
</script>
';

//$_GET values
$view = $_GET['view'];
$mark_read = $_GET['mark_read'];
$delete_message = $_GET['delete_message'];

$page->header();

?>
<h1>Private Messaging</h1>
<?

if(!$usrid) $page->kill('Please <a href="/login.php">log in</a> to access messaging services.');

if($mark_read) {
	$query = "SELECT * FROM `pm` WHERE `id` = '$mark_read' LIMIT 1";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	$msg = mysqli_fetch_object($res);
	if($usrid != $msg->to) {
		echo "You can't mark that message as read because it isn't even to you. You are such a moron!";
		$page->footer();
		exit;
	} else {
		$query = "UPDATE `pm` SET `read` = '1' WHERE `id` = '$mark_read'";
		if(!mysqli_query($GLOBALS['db']['link'], $query)) echo "Database Error: Could not mark this message as read. Please <a href=\"/bug.php\">submit a bug report</a> to the staff";
	}
}

if($delete_message) {
	$query = "SELECT * FROM `pm` WHERE `id` = '$delete_message' LIMIT 1";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	$msg = mysqli_fetch_object($res);
	if($usrid == $msg->to) $who = 'to';
	elseif($usrid == $msg->from) $who = 'from';
	if(!$who) {
		$errors[] = "You can't delete that message because it has nothing to do with you.";
	} else {
		$query = "UPDATE `pm` SET `hide_".$who."`='1'".($who == "to" ? ", `read`='1'" : "")." WHERE `id` = '$delete_message'";
		if(!mysqli_query($GLOBALS['db']['link'], $query)) $errors[] = "Database Error: Could not delete. Please <a href=\"/bug.php\">submit a bug report</a> to the staff";
		else $results[] = "Message Deleted";
	}
}

if($view) {
	
	$query = "SELECT * FROM `pm` WHERE `id` = '$view' LIMIT 1";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res))
		$msg = mysqli_fetch_object($res);
	else $page->kill("Database error: Could not get data for id '$view'");
	
	if(!$msg) $page->kill("Error: No data for id '$view'");
	
	if($usrid != $msg->to && $usrid != $msg->from) $page->kill("Shame on you, spying on other people's messages!");
	
	//mark as read
	if($msg->read == 0 && $usrid == $msg->to) {
		$query = "UPDATE `pm` SET `read` = 1 WHERE `id` = '$view' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $query)) $errors[] = "Database Error: Could not mark this message as read.";
	}
	
	$msg->subject = stripslashes($msg->subject);
	$msg->message = stripslashes($msg->message);
	
	$user_from = new user($msg->from);
	
	?>
	<div id="message">
		
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td valign="top" width="35%">
		
					<div class="header">
						<h3><?=$msg->subject?></h3>
						<p>A message from <b><?=$user_from->output()?></b></p>
						<p><b><?=FormatDate($msg->date)?></b></p>
						<p>
							<a href="?delete_message=<?=$view?>" class="styled-button delmsg" style="margin:0 0 0 10px"><span>Delete</span></a>
							<a href="/contact-user.php?user=<?=$user_from->username?>&method=pm&reply_to_id=<?=$view?>" class="styled-button"><span>Reply</span></a>
						</p>
					</div>
					
				</td>
				<td valign="top">
		
					<div id="message-words">
						<?=nl2br($msg->message)?>
					</div>
					
				</td>
			</tr>
		</table>
		
	</div>
	<?
	
	$page->footer();
	exit;
	
}

?>
<div id="messages-overview">

<table border="0" cellpadding="5" cellspacing="0" class="plain">
	<tr>
		<td style="background-color:#FFFFBF"><b>Compose</b></td>
		<td style="background-color:white;"><a href="/contact-user.php" class="arrow-right">Send a message to another Videogam.in user</a></td>
	</tr>
</table>

<table border="0" cellpadding="0" cellspacing="0" width="100%" id="messages">
	<tr>
		<td colspan="4" class="nostyle"><h2>Inbox</h2></td>
	</tr>
	<?
	
	$query = "SELECT * FROM `pm` WHERE `to` = '$usrid' AND `hide_to` = 0 ORDER BY `date` DESC";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)) {
		
		?>
		<tr>
			<th>From</th>
			<th>Date</th>
			<th>Subject</th>
			<th><span style="display: none;">delete</span></th>
		</tr>
		<?
		
		while($row = mysqli_fetch_assoc($res)) {
			$row[subject] = stripslashes($row[subject]);
			if($row[read] == 1) {
				//check if replied
				$q = "SELECT * FROM `pm` WHERE `reply_to_id` = '$row[id]' LIMIT 1";
				if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) {
					$row['status'] = "Replied-to message";
					$row['class'] = "subject-replied";
				} else {
					//just read
					$row['status'] = "Read message";
					$row['class'] = "subject-read";
				}
			} else {
				$row['status'] = "Unread message";
				$row['class'] = "subject";
			}
			
			?>
			<tr class="hilite">
				<td style="white-space:nowrap;"><?=outputUser($row['from'])?></td>
				<td style="white-space:nowrap;"><?=FormatDate($row['date'])?></td>
				<td style="width:80%;"><a href="?view=<?=$row['id']?>" class="tooltip <?=$row['class']?>" title="<?=$row['status']?>"><?=$row['subject']?></a></td>
				<td class="last" style="white-space:nowrap;">
					<div><a href="?delete_message=<?=$row['id']?>" title="delete message" class="ximg delmsg">delete</a></div>
				</td>
			</tr>
			<?
		}
		
	} else {
		echo '<tr><td colspan="4" class="nostyle">Your inbox is empty. Maybe you should make some friends!</td></tr>';
	}
		
	
	?>
	<tr>
		<td colspan="4" class="nostyle"><h2>Sent Messages</h2></td>
	</tr>
	<?
	$query = "SELECT * FROM `pm` WHERE `from` = '$usrid' AND `hide_from` != '1' ORDER BY `date` DESC";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)) {
		?>
		<tr>
			<th>To</th>
			<th>Date</th>
			<th>Subject</th>
			<th><span style="display:none">delete</span></th>
		</tr>
		<?
		
		while($row = mysqli_fetch_assoc($res)) {
			$row['subject'] = stripslashes($row['subject']);
			
			?>
			<tr class="hilite">
				<td style="white-space:nowrap;"><?=outputUser($row['to'])?></td>
				<td style="white-space:nowrap;"><?=FormatDate($row['date'])?></td>
				<td style="width:80%;"><a href="?view=<?=$row['id']?>"><?=$row['subject']?></a></td>
				<td class="last" style="white-space:nowrap;">
					<div><a href="?delete_message=<?=$row['id']?>" title="delete message" class="ximg delmsg">delete</a></div>
				</td>
			</tr>
			<?
		}
	
	} else {
		echo '<tr><td colspan="4" class="nostyle">Your outbox is empty. You should practice your communications skills more often.</td></tr>';
	}
?>
</table>
</div>
<?

$page->footer();
?>