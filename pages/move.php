<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
$page = new page;
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.pages.edit.php");

$title = formatName($_GET['title']);
$titleurl = formatNameURL($title);

do if($_POST){
	
	if(!$oldpgid = $_POST['pgid']) break;
	$q = "SELECT `title` FROM pages WHERE pgid = '".mysql_real_escape_string($oldpgid)."' LIMIT 1";
	if(!$row = mysql_fetch_assoc(mysql_query($q))){ $errors[] = "Couldn't find page data for pade ID #".$oldpgid; break; }
	
	$pg = new pgedit($row['title']);
	$pg->loadData();
	
	$in = $_POST['in'];
	$new_title = formatName($in['title']);
	$old_title = $pg->title;
	$move = false;
	$movetoempty = true;
	$dt = date("Y-m-d H:i:s");
	
	//get new page info
	if($new_title == "") {
		$in['title'] = $old_title;
		$errors[] = "No new title was given; reverting to old title";
		break;
	}
	
	if($new_title === $old_title) {
		$errors[] = "No title change detected";
		break;
	}
	
	if(strtolower($new_title) == strtolower($old_title)){
		//no change or just changed case
		$move = false;
	} else {
		
		$move = true;
		
		$res = mysql_query("SELECT * FROM pages WHERE `title`='".mysql_real_escape_string($new_title)."' LIMIT 1");
		if($dat = mysql_fetch_object($res)){
			
			// a page exists in the destination
			
			$movetoempty = FALSE;
			
			if(!$dat->redirect_to && !$in['replace']) {
				
				//there's already a page named that and it's NOT a redirect and the user has not selected REPLACE
				$errors[] = bb2html('There is already a page named [['.$new_title.']] in the database. Remove it manually or mark this form to replace existing content.');
				break;
			
			}
		}
		
		if(!$_POST['edit_summary']) {
			$errors[] = "Since you are renaming this page, you must supply an edit summary (reason for the action) in the given field.";
			break;
		}
		
	}
	
	if($usrrank <= 4 && $move) {
		
		//send an email request since user is less than trusted VIP (lv 5)
		
		$q = "SELECT * FROM users WHERE usrid='$usrid' LIMIT 1";
		$usr = mysql_fetch_object(mysql_query($q));
		
		$headers = 'From: '.$usrname.'<'.$usr->email.'>'."\r\n" .
    'X-Mailer: PHP/' . phpversion();
		if (mail($default_email, "Videogam.in Move Request", "$usrname has requested that $old_title be renamed to $new_title. Reason:\n\n".$_POST['edit_summary']."\n\nTo follow through with this request: http://videogam.in/pages/move.php?title=".urlencode($titleurl)."&to=".urlencode($new_title)."&reason=".urlencode($_POST['edit_summary'])."\n\n", $headers)) {
	 		$results[] = "Your move request has been sent. Thanks for helping keep things tidy around here.";
		} else {
	 		$errors[] = "There was an error and your request could not be sent. Please email $default_email to request this move and mention the error.";
		}
		
		break;
		
	}
	
	if($move && $movetoempty){
		
		// no page exists to where we're moving
		// make a new page that redirects pg from where pg moved from
		
		$newpgid = mysqlNextAutoIncrement("pages");
		$q = "INSERT INTO pages (type, title, redirect_to, creator, created, modified) VALUES 
			('".$pg->type."', '".mysql_real_escape_string($old_title)."', '".mysql_real_escape_string($new_title)."', '$usrid', '$dt', '$dt');";
		if(!mysql_query($q)) $errors[] = "Couldn't add redirect page to database; ".mysql_error();
		
		//rename page history entries
		$q = "UPDATE pages_edit SET `title` = '".mysql_real_escape_string($new_title)."' WHERE `title` = '".mysql_real_escape_string($old_title)."';";
		if(!mysql_query($q)) $errors[] = "Couldn't update page history because of a database error";
		
	} elseif($move && !$movetoempty){
		
		// we're about to rename this page to the new title
		// delete page content at the destination so there's no duplicate
		
		$dat = "";
		$q = "SELECT * FROM pages WHERE `title` = '".mysql_real_escape_string($new_title)."' LIMIT 1";
		$dat = mysql_fetch_object(mysql_query($q));
		$newpgid = $dat->pgid;
		
		$q = "UPDATE pages SET `title` = '".mysql_real_escape_string($old_title)."', redirect_to = '".mysql_real_escape_string($new_title)."' WHERE pgid = '$newpgid' LIMIT 1";
		if(!mysql_query($q)) $errors[] = "Couldn't update moving page with new attributes; ".mysql_error();
		
		//rename page history entries
		$q = "UPDATE pages_edit SET `title` = '".mysql_real_escape_string($new_title)."' WHERE `title` = '".mysql_real_escape_string($old_title)."';";
		if(!mysql_query($q)) $errors[] = "Couldn't update page history because of a database error";
		
	}
	
	if($move && $newpgid){
		
		//create the redirect page XML
		//only if the page title is not a capitalization change
		
		$newpg = new pgedit($old_title);
		$newpg->type = $pg->type;
		$newpg->pgid = $newpgid;
		$newpg->data = $newpg->template();
		$newpg->data->content = "#REDIRECT [[$new_title]]";//echo '<pre>'.htmlspecialchars($newpg->data->asXML());
		
		try{ $newpg->save(false, true); }
		catch(Exception $e){ $errors[] = "Error creating redirect page to $new_title; " . $e->getMessage(); }
		
		$q = "INSERT INTO pages_links (`from_pgid`, `to`, `is_redirect`) VALUES ('$newpgid', '".mysql_real_escape_string($new_title)."', '1');";
		if(!mysql_query($q)) $errors[] = "Couldn't record redirect link; ".mysql_error();
		
	}
	
	//update stuff
	$upd = array();
	if($in['upd']['watchlist']){
		$upd['pages_watch'] = 'title';
	}
	if($in['upd']['tag']){
		$upd = array_merge($upd, $pg_tags_tables);
	}
	
	foreach($upd as $table => $field){
		$q = "UPDATE `$table` SET `$field` = '".mysql_real_escape_string($new_title)."' WHERE `$field` = '".mysql_real_escape_string($old_title)."';";
		if(!mysql_query($q)) $errors[] = "Couldn't update DB table `$table` with new page title; ".mysql_error();
	}
	
	// UPDATE INDEXES //
	
	$xmlf = $_SERVER['DOCUMENT_ROOT']."/pages/xml/index/".$pg->type.".xml";
	$index = simplexml_load_file($xmlf);
	
	list($index, $json_) = $pg->buildIndexRow($index);
	
	$index_dom = new DOMDocument('1.0', 'UTF-8');
	$index_dom->xmlStandalone = false;
	$index_dom->preserveWhiteSpace = false;
	$index_dom->formatOutput = true;
	$index_dom->loadXML($index->asXML());
	
	$xpath = new DOMXpath($index_dom);
	$nodei = 0;
	foreach($xpath->query($pg->type.'[@pgid="'.$pg->pgid.'"][1]') as $node){
		$node->parentNode->removeChild($node);
	}
	
	if(!$index_dom->save($xmlf)) $ret['error'].= "Error saving index file :( \n";
	
	$q = "SELECT `json` FROM pages_index_json WHERE `type`='".$pg->type."' LIMIT 1";
	if(!$row = mysql_fetch_assoc(mysql_query($q))) die("Error selecting JSON index: $q");
	$json_blob = json_decode($row['json'], true);
	$json_blob[$pg->title] = $json_;
	$json_str = json_encode($json_blob);
	$q = "UPDATE pages_index_json SET `json` = '".mysql_real_escape_string($json_str)."' WHERE `type` = '".$pg->type."' LIMIT 1";
	if(!mysql_query($q)) $ret['error'].= "Error updating JSON index: $q \n";
	
	if($errors) break;
	
	$pg->title = $new_title;
	$pg->data->title = $new_title;
	
	try{ $pg->save(true, true); }
	catch(Exception $e){ $errors[] = "Couldn't save base XML document for this page. Changes will not be reflected. More information: " . $e->getMessage(); break; }
	
	//update dbs
	$q = "UPDATE pages SET `title` = '".mysql_real_escape_string($new_title)."', `modified` = '$dt' WHERE pgid='".$pg->pgid."' LIMIT 1";
	if(!mysql_query($q)) $errors[] = "Couldn't update database pages database; ".mysql_error();
	
	$q = "INSERT INTO pages_edit (pgid, `title`, session_id, usrid, `rename`, `published`, edit_summary) VALUES (
		'".$pg->pgid."', 
		'".mysql_real_escape_string($new_title)."', 
		'".$pg->sessid."',
		'$usrid',
		'[[".mysql_real_escape_string($old_title)."|".mysql_real_escape_string($old_title)."]] to [[".mysql_real_escape_string($new_title)."|".mysql_real_escape_string($new_title)."]]',
		'1',
		'".mysql_real_escape_string($_POST['edit_summary'])."')";
	if(!mysql_query($q)) $errors[] = "Couldn't record edit session";
	
	if($move && $newpgid){
		
		//add'l edit session for old page
	
		$q = "INSERT INTO pages_edit (pgid, `title`, session_id, usrid, `rename`, `published`, edit_summary) VALUES (
			'$newpgid', 
			'".mysql_real_escape_string($old_title)."', 
			'".$newpg->sessid."',
			'$usrid',
			'[[".mysql_real_escape_string($old_title)."|".mysql_real_escape_string($old_title)."]] to [[".mysql_real_escape_string($new_title)."|".mysql_real_escape_string($new_title)."]]',
			'1',
			'".mysql_real_escape_string($_POST['edit_summary'])."')";
		if(!mysql_query($q)) $errors[] = "Couldn't record edit session 2";
		
	}
	
	if(!$errors) {
		header("Location: ".pageURL($new_title, $pgrow['type']));
		exit;
	}
	
} while(false);

$page->title = "Rename/Move " . htmlSC($title) . " -- Videogam.in";

$pg = new pgedit($title);
$pg->header();

if(!$pg->row){
	echo "Error: The page '$title' doesn't exist!";
	$pg->footer();
	exit;
} elseif(!$usrid){
	echo 'Please <a href="/login.php" class="prompt">log in</a> to continue.';
	$pg->footer();
	exit;
} elseif($pg->redirect_to){
	echo "This page can't be renamed since it's a redirect.";
	$pg->footer();
	exit;
}
?>
<div id="pgmove" class="pgedbg" style="padding:30px 40px;">
	
		<p style="margin-top:0;"><span class="warn"></span>Renaming this page will change the page location and create a redirection link in the current URL. However, <b>links and tags to the old page may not be updated.</b></p>
		<p>This can be a drastic and unexpected change for a popular page; Established pages should only be renamed when absolutely necessary or when contrary to <a href="/posts/2010/04/16/page-editing-guide#Naming_Conventions">naming conventions</a>.</p>
	
	<div class="hr"></div>
	
	<form action="move.php?title=<?=$titleurl?>" method="post">
		
		<input type="hidden" name="pgid" value="<?=$pg->pgid?>"/>
		
		Rename to:<br/>
		<textarea name="in[title]" cols="50" rows="2"><?=($_GET['to'] ? urldecode($_GET['to']) : $pg->title)?></textarea>
		
		<?
		if($usrrank > 4) {
		//adv opts for admins
		?>
		
		<p></p>
		<label><input type="checkbox" name="in[replace]" value="1"/> Replace any existing page content that might exist in the new page location</label>
		
		<p></p>
		<label><input type="checkbox" name="in[upd][watchlist]" value="1" checked="checked"/> Update watch lists with new title</label>
		
		<p></p>
		<label><input type="checkbox" name="in[upd][tag]" value="1"/> Update Sblog, Forum, etc., tags with new title <a href="#help" class="tooltip" title="This is only necessary when renaming a page that has been been referred to by a temporary name before (IE, 'New Zelda'), when a game title has been changed for domestic release (IE, changing 'Dragon Quest IX' to 'Dragon Quest IX: Sentinels of the Starry Skies'), etc.">?</a></label>
		
		<p></p>
		&nbsp;&nbsp;&nbsp;<span class="arrow-right"></span> 
		<?
		
		foreach($pg_tags_tables as $table => $field){
			$q = "SELECT * FROM `$table` WHERE `$field` = '".mysql_real_escape_string($title)."';";
			$num_tags = $num_tags + mysql_num_rows(mysql_query($q));
		}
		
		if($num_tags) echo '<i>There are <b>'.$num_tags.'</b> Sblogs, Forums, Groups, Albums, and/or Images tagged "'.$title.'"</i>';
		else echo '<i>There is nothing tagged "'.$title.'"</i>';
		
		} //end adv opts for admins
		
		?>
		
		<p></p>
		
		Reason: (Required)<br/>
		<textarea name="edit_summary" cols="50" rows="2"><?=($_GET['reason'] ? urldecode($_GET['reason']) : $_POST['edit_summary'])?></textarea>
		
		<p></p>
		
		<?
		if($usrrank <= 4){
			?>
			Upon submitting this form, a move request will be submitted to the administrators for review. The request then may or may not be carried out, but will be carefully considered based on your reason given.
			<p></p>
			<?
		}
		?>
		
		<input type="submit" value="Submit"/>
		
	</form>
</div>
<?

$pg->footer();
?>