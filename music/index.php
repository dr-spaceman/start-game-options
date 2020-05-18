<?
use Vgsite\Page;
$page = new Page();

$page->title = "Videogam.in / Music";
$page->css[] = "/music/style.css";
$page->width = "fixed";

if($_GET['id']) {
	include("output_album.php");
	exit;
}

$page->header();

$Query   = "SELECT * FROM albums WHERE `new`='1' AND `view`='1' ORDER BY `id` DESC";
$Query2  = "SELECT * FROM albums_changelog LEFT JOIN albums ON (albums_changelog.album=albums.albumid) WHERE type='new' AND view='1' ORDER BY albums_changelog.datetime DESC limit 4";
$Result  = mysqli_query($GLOBALS['db']['link'], $Query2);
$Result2 = mysqli_query($GLOBALS['db']['link'], $Query);

include("nav.php");

$sec = array("id"=>"music-index");
$page->openSection($sec);

?>
<table border="0" width="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td width="395" valign="top">
			<h3>Newest Albums</h3>
<?

while ($dat = mysqli_fetch_assoc($Result)) {
	
	?>
			<div class="music-data-table">
				<h4><a href="./?id=<?=$dat['albumid']?>" class="ntlink"><?=$dat['title']?> <em><?=$dat['subtitle']?></em></a></h4>
				<table border="0" width="395" cellpadding="0" cellspacing="0">
					<tr>
						<td rowspan="6" width="160" class="newsidebar">
							<a href="./?id=<?=$dat['albumid']?>"><img src="<?=(file_exists($_SERVER['DOCUMENT_ROOT']."/music/media/cover/standard/".$dat['albumid'].".png") ? 'media/cover/standard/'.$dat['albumid'].'.png" alt="'.$dat['title'].' '.$dat['subtitle'].'"' : 'graphics/none.png" alt="no cover image available"')?> border="0"/></a>
						</td>
						<td width="235" colspan="2" class="newsubtitle">Album Data</td>
					</tr>
					<tr>
						<td width="100" class="newentry">Publisher:</td>
						<td width="135" class="newentry2"><?=$dat['publisher']?></td>
					</tr>
					<tr>
						<td width="100" class="newentry">Catalog ID:</td>
						<td width="135" class="newentry2"><?=$dat['cid']?></td>
					</tr>
					<tr>
						<td width="100" class="newentry">Release Date:</td>
						<td width="135" class="newentry2"><?=$dat['release']?></td>
					</tr>
					<tr>
						<td width="100" class="newentry">Price (retail):</td>
						<td width="135" class="newentry2"><?=$dat['price']?></td>
					</tr>
					<tr>
						<td width="100" class="newentry">Composition:</td>
						<td width="135" class="newentry2"><?
							
							unset($a);
							unset($v);
							unset($r);
							$a = array();
							$v = array();
							$r = array();
							
							$q = "SELECT name, name_url, vital FROM people_work LEFT JOIN people USING (pid) WHERE people_work.albumid='".$dat['albumid']."' AND role LIKE '%compos%'";
							$res = mysqli_query($GLOBALS['db']['link'], $q);
							while($row = mysqli_fetch_assoc($res)) {
								$x = '[['.$row['name'].']]';
								if($row['vital']) $v[] = $x;
								else $r[] = $x;
							}
							
							$q = "SELECT name, vital FROM albums_other_people WHERE albumid='".$dat['albumid']."' AND role LIKE '%compos%'";
							$res = mysqli_query($GLOBALS['db']['link'], $q);
							while($row = mysqli_fetch_assoc($res)) {
								if($row['vital']) $v[] = $row['name'];
								else $r[] = $row['name'];
							}
							
							$a = array_merge($v, $r);
							if (count($a) > 3) {
								for($i = 0; $i < 3; $i++) {
									$z[] = $a[$i];
								}
								$z[] = "et al.";
								$a = $z;
							}
							
							$opp = implode(", ", $a);
							echo bb2html($opp, "pages_only");
							?>
						</td>
					</tr>
				</table>
			</div>
			<?

}
?>
		</td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td width="" valign="top">
			<h3>Upcoming & Recent</h3>
			<ul class="music-index-list">
				<?
				
				$Query = "SELECT * FROM albums WHERE view='1' ORDER BY datesort DESC LIMIT 6";
				$Result = mysqli_query($GLOBALS['db']['link'], $Query);
				while ($Array = mysqli_fetch_assoc($Result)) {
					if (file_exists($_SERVER['DOCUMENT_ROOT']."/music/media/cover/thumb/".$Array['albumid'].".png")) {
						$image = '<img src="media/cover/thumb/'.$Array['albumid'].'.png" alt="'.$Array['title'].' '.$Array['subtitle'].'" border="0"/>';
					} else {
						$image = '<img src="/bin/img/pixel.png" alt="no cover image available" border="0" width="23" height="23"/>';
					}
					
					?>
					<li>
						<a href="?id=<?=$Array['albumid']?>">
							<span class="right"><?=$Array['release']?></span>
							<?=$image?>
							<span class="title"><?=$Array['title']?> <i><?=$Array['subtitle']?></i></span>
							<br style="clear:left;"/>
						</a>
					</li>
					<?
				}
				?>
			</ul>
			
			<br/><br/>
			
			<h3>Newest MP3 Tracks</h3>
			<ul class="music-index-list">
				<?
				
				$Query = "SELECT albums.albumid, track_name, title, subtitle FROM albums_samples LEFT JOIN albums USING (albumid) LEFT JOIN albums_tracks ON (albums_samples.track_id=albums_tracks.id) ORDER BY albums_samples.datetime DESC LIMIT 6";
				$Result = mysqli_query($GLOBALS['db']['link'], $Query);
				while ($Array = mysqli_fetch_assoc($Result)) {
					?>
					<li>
						<a href="?id=<?=$Array['albumid']?>" style="padding-left:17px; background:url(/music/graphics/playtrack.png) no-repeat 0 6px;">
							<span style="color:black">"<?=$Array['track_name']?>", </span>
							<span class="title"><?=$Array['title']?> <i><?=$Array['subtitle']?></i></span>
						</a>
					</li>
					<?
				}
				?>
			</ul>
			
			<br/><br/>
			
			<h3>Highest Rated</h3>
			<ul class="music-index-list">
				<?
				
				//get rating counts
				$query = "SELECT a.albumid, title, subtitle, COUNT(r.albumid) AS count FROM albums_ratings AS r LEFT JOIN albums AS a ON (r.albumid=a.albumid AND a.view=1) GROUP BY a.albumid";
				$res   = mysqli_query($GLOBALS['db']['link'], $query);
				while($row = mysqli_fetch_assoc($res)) {
					if($row['count'] > 5) {
						$counts[$row['albumid']] = $row['count'];
						$data[$row['albumid']]['title'] = $row['title'];
						$data[$row['albumid']]['subtitle'] = $row['subtitle'];
					}
				}
				//get ratings
				$query = "SELECT albumid, AVG(rating) AS rating FROM albums_ratings GROUP BY albumid ORDER by rating DESC";
				$res   = mysqli_query($GLOBALS['db']['link'], $query);
				while($row = mysqli_fetch_assoc($res)) {
					$ratings[$row['albumid']] = $row['rating'];
				}
				arsort($ratings);
				
				$i = 0;
				foreach(array_keys($ratings) as $a) {
					if($counts[$a]) {
						$top[] = array("albumid" => $a, "count" => $counts[$a], "rating" => $ratings[$a]);
						$i++;
						if($i == 6) break;
					}
				}
				
				foreach($top as $Array) {
					
					$Array['rating'] = round($Array['rating'], 1);
					
					if (file_exists($_SERVER['DOCUMENT_ROOT']."/music/media/cover/thumb/".$Array['albumid'].".png")) {
						$image = '<img src="media/cover/thumb/'.$Array['albumid'].'.png" alt="'.$data[$Array['albumid']]['title'].' '.$data[$Array['albumid']]['subtitle'].'" border="0"/>';
					} else {
						$image = '<img src="graphics/none_sm.png" alt="no cover image available" border="0"/>';
					}
					
					?>
					<li>
						<a href="?id=<?=$Array['albumid']?>">
							<?=$image?>
							<span class="title"><?=$data[$Array['albumid']]['title']?> <i><?=$data[$Array['albumid']]['subtitle']?></i></span><br style="clear:left;"/>
						</a>
					</li>
					<?
				}
				
				?>
			</ul>
		</td>
	</tr>
</table>
<?

$page->closeSection();

$page->footer();

?>