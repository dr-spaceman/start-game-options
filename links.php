<? 
require ($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");

$page = new page;
$page->title = "Videogam.in / External Links";
$page->style[] = "/bin/css/links.css";
$page->meta_description = "Videogame links and resources";
$page->meta_keywords = "games,videogames,game,links,resources,Square Enix,Nintendo,[GAME_TITLES]";

$in = $_POST['in'];

$link_categories = array("Our Affiliates", "Square Enix", "Nintendo", "General Gaming", "Non-Gaming", "Uncategorized");

if($_POST['submit']) {
	if($_POST['name'] && ($_POST['url'] != 'http://' || $_POST['url'] != '')) {
		if($_POST['math'] != $_POST['math1'] + $_POST['math2']) die("Your math is wrong. Are you a human?");
		else {
			if(mail($default_email,"Videogam.in link submission", $_POST['name']."\n".$_POST['url']."\n".$_POST['description']."\n\nhttp://videogam.in/links.php")) {
				$results[] = "Your link has been successfully submited to the editors.";
			} else {
				$errors[] = "There was an error submitting your link. Please make us aware of the error by submitting a <a href=\"/bug.php\">bug report</a>.";
			}
		}
	}
}

if($del_link = $_GET['del_link']) {
	if($usrrank <= 7) die("Not an admin");
	$q = "DELETE FROM external_links WHERE `id` = '$del_link' LIMIT 1";
	if(mysqli_query($GLOBALS['db']['link'], $q)) $results[] = "Link deleted";
	else $errors[] = "ERROR: link couldn't be deleted for some reason";
}

if($in['submit']) {
	$q = sprintf("INSERT INTO external_links (`category`, `url`, `title`, `description`) VALUES ('$in[category]', '$in[url]', '%s', '%s')",
		mysqli_real_escape_string($GLOBALS['db']['link'], $in[title]),
		mysqli_real_escape_string($GLOBALS['db']['link'], $in[description]));
	if(mysqli_query($GLOBALS['db']['link'], $q)) $results[] = "Link added";
	else $errors[] = "ERROR: Link could not be added for some reason";
}

$page->header();
	
?>
<h2>Links</h2>
<a href="#ext">External Links</a> | <a href="#linkback">Links to Square Haven</a> | <a href="#subm">Submit Your Link</a>

<h3 id="ext">External Links</h3>
<?

if($usrrank >= 7) {
	?>
	<div style="float:right; width:200px;">
		<form action="links.php" method="post">
			<fieldset style="background-color:#EEE; border:1px solid #C0C0C0;">
				<legend>Admin: Add a Link</legend>
				<p>
					Category<br/>
					<select name="in[category]">
						<option value="Uncategorized">Uncategorized</option>
						<? foreach($link_categories as $cg) echo '<option value="'.$cg.'">'.$cg."</option>\n"; ?>
					</select>
				</p>
				<p>
					Site Name<br/>
					<input type="text" name="in[title]" size="24"/>
				</p>
				<p>
					URL<br/>
					<input type="text" name="in[url]" size="24"/>
				</p>
				<p>description<br/>
					<textarea name="in[description]" cols="19" rows="3"></textarea>
				</p>
				<p><input type="submit" name="in[submit]" value="Add Link"/></p>
			</fieldset>
		</form>
	</div>
	<?
}

foreach($link_categories as $cg) {
	?><h4><?=$cg?></h4>
	<ul class="links-list"><?
	$query = "SELECT * FROM external_links WHERE `category`='$cg' ORDER BY `title`";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$row = stripslashesDeep($row);
		echo '<li><a href="'.$row['url'].'" target="_blank">'.$row['title'].'</a>'.($row['description'] ? " $row[description]" : "").($usrrank >= 8 ? '&nbsp;<a href="?del_link='.$row[id].'" title="delete this link" class="x">X</a>' : '')."</li>\n";
	}
	?></ul>
	<?
}	

//randomize validation
$auth = authenticate();

?>
<br/><br/>

<div style="float:right; width:180px; padding:0 0 0 1em; border-left:1px solid #C0C0C0;">

	<h3 id="subm" style="margin-top:0;">Submit Your Link</h3>
	
	<form action="links.php" method="post">
	<?=$auth->hidden?>
	<p>
		<label>Site Name:<br />
		<input type="text" name="name" size="24"/></label>
	</p>
	<p>
		<label>URL:<br />
		<input type="text" name="url" size="24" value="http://"/></label>
	</p>
	<p>
		<label>Description:<br />
		<textarea name="description" cols="19" rows="3"></textarea></label>
	</p>
	<p><?=$auth->label?><?=$auth->input?></p>
	<p><input type="submit" name="submit" value="Submit Link"/></p>
	</form>
	
</div>

<div style="width:694px;">
	
	<h3 id="linkback" style="margin-top:0">Link to Videogam.in</h3>
	<div style="display:none">
	<p><img src="banners/squhav1.gif" width="88" height="31" border="0" alt="square haven button" /><br />
	<code>&lt;a href="http://squarehaven.com/">&lt;img src="http://squarehaven.com/banners/squhav1.gif" width="88" height="31" border="0" alt="The venerable Square Enix resource!" />&lt;/a></code></p>
	
	<p><img src="banners/shaven.jpg" width="468" height="60" border="0" alt="square haven banner"><br />
	<code>&lt;a href="http://squarehaven.com/">&lt;img src="http://squarehaven.com/banners/shaven.jpg" width="468" height="60" border="0" alt="The venerable Square Enix resource!" />&lt;/a></code></p>
	</div>
	<p><a href="http://videogam.in/">Videogam.in - A site of vapid gaming debauchery!</a><br />
	<code>&lt;a href="http://videogam.in/">Videogam.in - A site of vapid gaming debauchery!&lt;/a></code></p>
	
</div>

<?
$page->footer();
?>
