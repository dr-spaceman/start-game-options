<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/admin.php");

if($pf_id = $_POST['setPrimary']) {
	$q = "UPDATE games_platforms SET notable='".$_POST['setTo']."' WHERE platform_id='$pf_id' LIMIT 1";
	if(mysqli_query($GLOBALS['db']['link'], $q)) die("ok");
	else die("Error setting db value");
}

$what = $_GET['what'];
$in = $_POST['in'];

$page = new page;
$page->title = "Nintendosite Admin".($what ? " / $what" : "");
$page->min_rank = 7;
$page->admin = TRUE;
$page->freestyle.= '
TABLE#pfs { border-width: 0 0 1px 1px; border-style:solid; border-color:#CCC; }
#pfs th, #pfs TD { padding:3px 6px; border-width: 1px 1px 0 0; border-style:solid; border-color:#CCC; }
';

if($what == "platforms") {
	
	///////////////
	// PLATFORMS //
	///////////////
	
	if($_POST['submit']) {
		if(!$pf = $in['platform']) $errors[] = "No platform name given";
		if(!$sh = $in['platform_shorthand']) $errors[] = "No shorthang given";
		if(!$errors) {
			$q = "INSERT INTO games_platforms (platform, platform_shorthand, notable) VALUES ('$pf', '$sh', '".$in['notable']."')";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) {
				$errors[] = "Couldn't add to db";
			} else {
				$results[] = "Platform added";
			}
		}
	}
	
	$page->min_rank = 8;
	$page->header();
	print_r($platforms);
	
	?><h2>Platform Management</h2>
	
	
	<table border="0" cellpadding="0" cellspacing="0" id="pfs">
		<tr>
			<th colspan="2" style="background-color:#EEE;"><h3 style="margin:0;padding:0;border-width:0;font-size:16px; font-weight:bold;">Current Platforms</h4></th>
			<th colspan="2" style="text-align:right; background-color:#F5F5F5;">Primary</th>
		</tr>
		<tr>
			<th>ID</th>
			<th>Platform</th>
			<th>Shorthand</th>
			<th style="border-top:none; background-color:#F5F5F5;">&nbsp;</th>
		</tr><?
		$query = "SELECT * FROM games_platforms ORDER BY platform";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)) {
			?>
			<tr>
				<td><?=$row[platform_id]?></td>
				<td><?=$row[platform]?></td>
				<td><?=$row[platform_shorthand]?></td>
				<td style=" background-color:#F5F5F5; text-align:center"">
					<input type="checkbox" value="<?=$row['platform_id']?>" class="set-pf-primary"<?=($row['notable'] ? ' checked="checked"' : '')?>/>
					<img src="/bin/img/loading-box.gif" alt="loading" style="display:none"/>
				</td>
			</tr><?
		}
		?>
	</table><br/>
	
	<form action="games-misc.php?what=platforms" method="post">
		<fieldset>
			<legend>Add a Platform</legend>
			<table border="0" width="100%" class="admin-form">
				<tr>
					<th>Platform name:</th>
					<td><input type="text" name="in[platform]"/></td>
				</tr>
				<tr>
					<th>Shorthand abbreviation:</th>
					<td>
						<small><b>Required</b>; A short abbreviation that consistes of lowercase letters and numbers</small>
						<p><input type="text" name="in[platform_shorthand]" size="5" maxlength="10"/>
					</td>
				</tr>
				<tr>
					<th>Notable?</th>
					<td>
						<label><input type="checkbox" name="in[notable]" value="1"/> This is a notable platform and should be promoted others where applicable.</label>
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><input type="submit" name="submit" value="Add platform"/></td>
				</tr>
			</table>
		</fieldset>
	</form>
	<?
	
	$page->footer();
	
}