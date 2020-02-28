<? 
require ($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");
$page = new page();
$page->title  = "Preview a Preview";
$page->header();

$platforms = getPlatforms();

$query = "SELECT * FROM games_previews WHERE `id`='$_GET[id]' LIMIT 1";
if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query))) {
	echo "Couldn't get data";
} else {
	$gdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM games WHERE gid='$dat->gid' LIMIT 1"));
	?>
	<h2>Preview</h2>
	<p style="padding:10px; border:1px dotted #BBB;">
		This is just a preview of the preview. The actual preview will be located <a href="/games/~<?=$gdat->title_url?>/preview">here</a> and may be styled a little differently
	</p>
	<?
	$dat->words = stripslashes($dat->words);
	$dat->words = reformatLinks($dat->words);
	preg_match_all("/\<h3\>(.+)\<\/h3\>/i", $dat->words, $headings);
		if($headings[1]) {
			echo '<ul id="preview-menu">';
			foreach($headings[1] as $h) {
				echo '<li><a href="#'.$h.'">'.$h.'</a></li>';
			}
			echo "</ul>\n\n";
			$dat->words = preg_replace("/\<h3\>(.+)\<\/h3\>/i", "<h3 id=\"$1\">$1</h3>", $dat->words);
		}
	echo $dat->words;
}
$page->footer();
?>