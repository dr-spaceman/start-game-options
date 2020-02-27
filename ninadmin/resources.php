<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/admin.php");

$page = new page;
$page->title = "Nintendosite Admin / External Resources";
$page->min_rank = 6;
$page->admin = TRUE;
$page->freestyle.= '#external-resources DT { font-weight:bold; }
#external-resources DD { margin-left:10px; font-size:11px; }
#external-resources DD SMALL { color:#808080; }';

$in = $_POST['in'];

// delete
if($del = $_GET['delete']) {
	$q = "SELECT * FROM admin_resources WHERE id='$del' LIMIT 1";
	$dat = mysql_fetch_object(mysql_query($q));
	if($usrrank == 9 || ($usrrank == 8 && $dat->usrid != 9) || ($usrid == $dat->usrid)) {
		$q = "DELETE FROM admin_resources WHERE id='$del' LIMIT 1";
		if(mysql_query($q)) $results[] = "Deleted";
		else $errors[] = "Couldn't delete from db";
	} else {
		$errors[] = "No access to delete that";
	}
}

//add
if($_POST['add_link']) {
	$in['category'] = preg_replace("/[^a-zA-Z0-9-_ ]/", "", $in['category']);
	$q = sprintf("INSERT INTO admin_resources (category, url, `title`, description, usrid) VALUES ('%s', '%s', '%s', '%s', '$usrid')",
		mysql_real_escape_string($in['category']),
		mysql_real_escape_string($in['url']),
		mysql_real_escape_string($in['title']),
		mysql_real_escape_string($in['description']));
	if(!mysql_query($q)) $errors[] = "Couldn't add to db";
	else {
		$page->javascript.= '<script type="text/javascript">
				if(document.onload) alert("xxx");
			</script>';
		$results[] = "Link added";
	}
}

$page->header();

?><h2>External Webmaster Resources</h2><?

if($usrrank >= 7) {
	?>
	<input type="button" value="Add a Link" onclick="document.getElementById('add-form').style.display='block'; this.style.display='none';"/>
	
	<form action="resources.php" method="post" id="add-form" style="display:none">
		<fieldset>
			<legend>Add a Link</legend>
			<table border="0" cellspacing="0" class="styled-form">
				<tr>
					<th>Category</th>
					<td>
						<select id="cats" onchange="document.getElementById('inp-category').value = this.options[this.selectedIndex].value;">
							<option value="">Select previously used</option>
							<?
							$query = "SELECT DISTINCT(category) AS category FROM admin_resources WHERE category != '' ORDER BY category";
							$res   = mysql_query($query);
							while($row = mysql_fetch_assoc($res)) {
								echo '<option value="'.$row['category'].'">'.$row['category'].'</option>'."\n";
							}
							?>
						</select>
						<p><input type="text" name="in[category]" size="30" id="inp-category"/></p>
					</td>
				</tr>
				<tr>
					<th>URL</th>
					<td><input type="text" name="in[url]" size="50" style="text-decoration:underline; color:blue;"/></td>
				</tr>
				<tr>
					<th>Site Name/Page Title</th>
					<td><input type="text" name="in[title]" size="30"/></td>
				</tr>
				<tr>
					<th>Description<br/><small>optional</small></th>
					<td><textarea name="in[description]" cols="30" rows="2"></textarea></td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><input type="submit" name="add_link" value="Add Resource"/></td>
				</tr>
			</table>
		</fieldset>
	</form>
	<?
}

?><h3>Uncategorized</h3>
<div id="external-resources">
<dl>
<?

$query = "SELECT * FROM admin_resources ORDER BY category";
$res   = mysql_query($query);
while($row = mysql_fetch_assoc($res)) {
	if($curr_cat != $row['category']) {
		$curr_cat = $row['category'];
		echo '</dl><h3>'.$row['category']."</h3>\n<dl>\n";
	}
	if($usrrank == 9 || ($usrrank == 8 && $row['usrid'] != 9) || ($usrid == $row['usrid'])) {
		$del_link = ' <a href="?delete='.$row['id'].'" class="x">X</a>';
	} else {
		$del_link = "";
	}
	echo '<dt><a href="'.$row['url'].'">'.$row['title'].'</a>'.$del_link.'</dt>';
	if($row['description']) echo '<dd>'.$row['description'].'</dd>';
	echo '<dd><small>Suggested by '.outputUser($row['usrid'], FALSE).'</small></dd>'."\n";
}

?></div><?

$page->footer();