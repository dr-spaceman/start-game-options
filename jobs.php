<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");

$page = new page;
$page->width = "fixed";
$page->title = "Videogam.in Jobs";
$page->header();

?>
<h1>Jobs at Videogam.in</h1><?

$inp = $_POST['inp'];

if($inp['message']) {
	if($_POST['math'] != $_POST['math1'] + $_POST['math2']) {
		$errors[] = "Sorry but your math appears to be wrong. Please revalidate the form.";
	} else {
		if (mail($default_email,"Videogam.in Job Application","The following message is from $inp[name]:\n\n".$inp[message]."\n\nThis message was sent via contact form from ".$_SERVER['SCRIPT_URI'],"From:$inp[email]")) {
	 		$results[] = "<b>Success!</b> Your message is en route.";
		} else {
	 		$errors[] = "Sorry for the inconvenience, but there was an error and your message could not be sent. Please email $default_email instead.";
		}
	}
}
	
//randomize validation
$rand1 = rand(0,4);
$rand2 = rand(1,9);

?>

<big><p>Videogam.in is a site run by a few amateur enthusiasts with an undying affinity for that which is wondrous in the world of escapism: <i>videogames</i>. We compile data, post news, write reviews, research game guides, and collaborate together on the goal of creating the best games site on the internet.</p><p>If you think you'll fit in such a setting, please don't waste another second and contact us right away.</p>
</big>

<fieldset><legend>Contact Form</legend>
	<form action="jobs.php" method="post">
		<input type="hidden" name="math1" value="<?=$rand1?>" />
		<input type="hidden" name="math2" value="<?=$rand2?>" />
		<table border="0" cellpadding="5" cellspacing="0" width="100%">
			<tr>
				<td><b>Your Name:</b></td>
				<td><input type="text" name="inp[name]" value="<?=$inp['name']?>" size="50"/></td>
			</tr>
			<tr>
				<td nowrap="nowrap"><b>E-mail Address:</b></td>
				<td width="100%"><input type="text" name="inp[email]" value="<?=$inp['email']?>" size="50"/></td>
			</tr>
			<tr>
				<td valign="top"><b>Message:</b><br/>
					<small style="font-size:12px; color:#777;">Please include a little about yourself and your expertise.</small></td>
				<td><div style="margin-right:6px"><textarea name="inp[message]" rows="10" style="width:100%"><?=$inp['message']?></textarea></td>
			</tr>
			<tr>
				<td><img src="http://squarehaven.com/index/graphics/numbers/<?=$rand1?>.png" alt="random number" /> + <img src="http://squarehaven.com/index/graphics/numbers/<?=$rand2?>.png" alt="random number" /> =</td>
				<td><input type="text" name="math" size="5" maxlength="2" /> <small style="font-size:12px;">(just to prove that you are a human rather than an evil spamming robot)</small></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" name="submit_inp" /></td>
			</tr>
		</table>
	</form>
</fieldset>
<?

$page->footer();

?>