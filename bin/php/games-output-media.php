<?
$page->title = $gdat->title." media -- Videogam.in";
$page->javascript.= '
	<script type="text/javascript">
	function toggleMediaCat(cat,swi) {
		var group = document.getElementsByName("cat-"+cat);
		for (var i = 0; i < group.length; i++) {
			group[i].style.display=swi;
		}
	}
	</script>';
$page->freestyle.= '
	#game-media {}
	#media-filter {
		border-left: 1px solid #C0C0C0; }
	#media-filter TD {
		padding: 5px;
		font-weight: bold;
		font-size: 15px; 
		border-width: 1px 1px 1px 0;
		border-style: solid;
		border-color: #C0C0C0;
		background-color: #EEE; }
	
	#media-list {}
	#media-list .li {
		padding: 7px 0;
		border-bottom: 1px solid #DDD; }
	#media-list BIG {
		font-size: 16px; }
	#media-list .date {
		padding-top: 3px;
		float: right; }
	#media-list .source {
		padding-top: 5px;
		font-size: 12px; }
	';
$page->header();

$query = "SELECT m.*, c.`category`
	FROM media m, media_tags t, media_categories as c 
	WHERE t.tag='gid:".$gdat->gid."' AND m.media_id=t.media_id AND c.category_id=m.category_id AND m.unpublished != '1' 
	ORDER BY m.datetime DESC";
$res   = mysql_query($query);
if(!mysql_num_rows($res)) {
	echo "No media uploaded yet :(<br/><br/><br/><br/>";
	$page->footer();
	exit;
}
while($row = mysql_fetch_assoc($res)) {
	$categories[$row['category_id']] = $row['category'];
	$media[] = $row;
}

?>
<div id="game-media" class="conts">
	<table border="0" cellpadding="0" cellspacing="0" width="100%" id="media-filter">
		<tr>
			<?
			while(list($k, $v) = each($categories)) {
				$ch = ' checked="checked"';
				echo '<td nowrap="nowrap"><label><input type="checkbox"'.$ch.' onclick="if(this.checked) toggleMediaCat(\''.$k.'\',\'block\'); else toggleMediaCat(\''.$k.'\',\'none\');" /> '.$v."</label>\n";
			}
			?>
		<td width="100%">&nbsp;</td>
		</tr>
	</table>
	
	<div id="media-list">
		<?
		foreach($media as $row) {
		
			if ($row['gallery']) {
				$row['href'] = "/media.php?mid=".$row['media_id'];
			} else {
				$row['href'] = $row['directory'];
			}
			?>
			<div name="cat-<?=$row['category_id']?>" class="li">
				<div class="date"><?=($row['quantity'] ? '<b>'.$row['quantity'].'</b> ' : '').$row['category']?></div>
				<big><a href="<?=$row['href']?>"><?=stripslashes($row['description'])?></a></big>
				<div class="source">
					Uploaded by <?=outputUser($row['usrid'], FALSE)?> on <?=formatDate($row['datetime'], 7)?>
					<?=($row['source'] ? ' <span style="color:#888">&middot;</span> Source: '.$row['source'] : '')?>
				</div>
			</div>
			<?
		}
		?>
	</div>
</div>

<?
$page->footer();
?>