<?
require($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");
	
$page = new page;
$page->title = "Videogam.in / Music / Index by Series";
$page->css[] = "/music/style.css";
$page->freestyle.= '
TABLE.plain, .plain TD { border-color:#C0C0C0 !important; }
#page H1 { color:#425C84; font:bold 17pt Arial; letter-spacing:-1px; }
#page H1 SPAN { color:#AAA; }';
$page->width = "fixed";

//get series list
$query = "SELECT series, COUNT(series) AS count FROM albums WHERE series != '' GROUP BY series";
$res   = mysqli_query($GLOBALS['db']['link'], $query);
while($row = mysqli_fetch_assoc($res)) {
	$serieslist[] = $row;
}

if(!$name = urldecode($_GET['name'])) {
	$name = $serieslist[0]['series'];
}
$name2 = urlencode($name);

$page->header();

include("nav.php");

?>
<h2>Music by Series <span>/</span> <?=$name?></h2>

<div style="border:1px solid #C0C0C0; background-color:#EEE; margin:0 0 15px 0; padding:5px;">
	<span class="arrow-right"><b>Select a series</b></span> &nbsp;
	<select onchange="window.location='by_series.php?name='+this.options[this.selectedIndex].value">
		<?
		foreach($serieslist as $row) {
			echo '<option value="'.urlencode($row['series']).'"'.($row['series'] == $name ? ' selected="selected"' : '').'>'.$row['series'].' ('.$row['count'].' album'.($row['count'] != 1 ? 's' : '').')</option>'."\n";
		}
		?>
	</select>
</div>

<table border="0" cellpadding="7" cellspacing="0" width="100%" class="plain">
	<?
	$aids = array();
	$query = "SELECT * FROM albums LEFT JOIN albums_samples USING (albumid) WHERE series='$name' ORDER BY title";
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
