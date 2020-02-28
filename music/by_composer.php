<?
require($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");
	
$page = new page;
$page->title = "Videogam.in / Music / Index by Composer";
$page->css[] = "/music/style.css";
$page->freestyle.= '
TABLE.plain, .plain TD { border-color:#C0C0C0 !important; }
#page H1 { color:#425C84; font:bold 17pt Arial; letter-spacing:-1px; }
#page H1 SPAN { color:#AAA; }';
$page->width = "fixed";

if(!$name = urldecode($_GET['name'])) {
	$name = "Nobuo Uematsu";
}
$name2 = urlencode($name);

//get series list
$query = "SELECT DISTINCT(pid), name FROM `people_work` LEFT JOIN people USING (pid) WHERE vital=1 AND role LIKE '%compos%' ORDER BY name";
$res   = mysqli_query($GLOBALS['db']['link'], $query);
while($row = mysqli_fetch_assoc($res)) {
	$plist[] = $row;
	if($row['name'] == $name) $pid = $row['pid'];
}

$page->header();

include("nav.php");

?>
<h2>Music by Composer <span>/</span> <?=$name?></h2>

<div style="border:1px solid #C0C0C0; background-color:#EEE; margin:0 0 15px 0; padding:5px;">
	<span class="arrow-right"><b>Select a composer</b></span> &nbsp;
	<select onchange="window.location='by_composer.php?name='+this.options[this.selectedIndex].value">
		<?
		foreach($plist as $row) {
			echo '<option value="'.urlencode($row['name']).'"'.($row['name'] == $name ? ' selected="selected"' : '').'>'.$row['name'].'</option>'."\n";
		}
		?>
	</select>
</div>

<table border="0" cellpadding="7" cellspacing="0" width="100%" class="plain">
	<?
	$aids = array();
	$query = "SELECT albumid, title, subtitle, `view`, `release`, cid, file FROM people_work LEFT JOIN albums USING (albumid) LEFT JOIN albums_samples USING (albumid) WHERE pid='$pid' AND albumid != '' AND role LIKE '%compos%' ORDER BY title";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		if(!in_array($row['albumid'], $aids)) {
		?>
		<tr>
			<td>
				<?=($row['view'] ? '<a href="/music/?id='.$row['albumid'].'"'.($row['file'] ? ' style="padding-right:20px; background:url(/music/graphics/playtrack.png) no-repeat 100% 50%;" title="this album has music samples"' : '').'>' : '').$row['title'].($row['subtitle'] ? ' <em>'.$row['subtitle'].'</em>' : '').($row['view'] ? '</a>' : '')?>
			</td>
			<td><?=$row['release']?></td>
			<td><?=$row['cid']?></td>
		</tr>
		<?
		}
		$aids[] = $row['albumid'];
	}
	?>
</table>
<?

$page->footer();

?>
