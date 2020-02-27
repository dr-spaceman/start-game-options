<?
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";

$name = "Bob"; $email = "Bob@bob.com";
$message = "Line 1\nLine 2\nLine 3";
$message = wordwrap($message, 70);

// Send
$headers = 'From: '.$name.'<'.$email.'>'."\r\n" . 'X-Mailer: PHP/' . phpversion();
if (mail("$default_email, mat.berti@gmail.com, hellobirdman@yahoo.com, lance111000@gmail.com", "Videogam.in Message", "The following message is from ".$name.":\n\n".$message.($frompage ? "\n\nPage: http://videogam.in".$frompage : "\n\nThis message was sent via contact form from ".$_SERVER['SCRIPT_NAME']), $headers)) die("Sent.");

echo "?";
exit;


require_once $_SERVER['DOCUMENT_ROOT']."/pages/class.pages.edit.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/bbcode2markdown2.php";

$min = $_GET['min'];
if(!$min)	$min = 0;

$max = 1;

$q = "SELECT title, pgid, description FROM pages LIMIT $min, $max";
$r = mysql_query($q);
if(!mysql_num_rows($r)) die("Fin.");
while($row = mysql_fetch_assoc($r)){
	
	$pgid=$row[pgid];
	$dt = date("Y-m-d H:i:s");
	
	$ed = new pgedit($row['title']);
	$ed->loadData();
	
	echo '<dl><dt><a href='.$ed->url.'>'.$row['title'].'</a></dt>';
	
	/*$newd = bbcode2markdown($row['description']);
	$q2 = "update pages set description = '".mysql_real_escape_string($newd)."' WHERE pgid='$pgid' limit 1";
	if(!mysql_query($q2)){ $errors = true; echo '<dd>Error '.$q.' '.mysql_error().'</dd>'; }*/
	
	$parameters = array("description", 'content', 'credits', 'characters', 'locations');
	
	$ch=false;
	foreach($parameters as $p){
		if($ed->data->{$p} && !count($ed->data->{$p}->children())){
			$newp = bbcode2markdown($ed->data->{$p});
			if(strlen($newp) == strlen($ed->data->{$p})) continue;
			$ch=true;
			echo
				'<dd>'.
				'<hr/><pre style="white-space:pre-wrap">'.htmlspecialchars($ed->data->{$p}).'</pre>'.
				'<hr/><pre style="white-space:pre-wrap">'.htmlspecialchars($newp).'</pre>'.
				'</dd>';
			$ed->data->{$p} = $newp;
		}
	}
	
	if(!$ch){ echo '</dl>'; continue; }
	
	try{ $ed->save("draft"); echo "<dd><a href=\"/pages/xml/drafts/".$ed->sessid.".xml\">Saved draft</a></dd>"; }
	catch(Exception $e){ $errors=true; echo "<dd>Couldn't save draft (".$e->getMessage().")" . '</dd>'; }
	
	try{ $ed->save(false, true); echo "<dd><a href=\"/pages/xml/".$pgid.".xml\">Saved data file</a></dd>"; }
	catch(Exception $e){ $errors=true; echo "<dd>Couldn't save base data file (".$e->getMessage().")" . '</dd>'; }
	
	$q = "INSERT INTO pages_edit (pgid, `title`, session_id, usrid, edit_summary, `datetime`) VALUES 
	('".$pgid."', '".mysql_real_escape_string($row['title'])."', '$ed->sessid', '4651', '[BOT] Converting to new markup (2nd round)', '$dt')";
	if(!mysql_query($q)){ $errors=true; echo"<dd>Error Couldn't record edit session in the database ".$q.mysql_error(); }
	
	echo "</dl>";$errors=true;
	
}

if($errors) echo '<p>'.$errors.' ERRORS</p><p><a href="test.php?min='.($min + $max).'">NEXT</a></p>';
else echo '<script>document.location="test.php?min='.($min + $max).'";</script>';
/* ?>
<p></p><button type="submit">Submit</button>
</form>
<? */