<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
$page = new page;
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.forums.php");
$forum = new forum();

$page->title = "Videogam.in forums / Topic Tags";
$page->header();

$since = $_GET['since'];

?>
<div id="forum">
	<h1><small><a href="/forums/">Forums</a> / </small>Tags</h1>
	<div id="forumdesc">
		What people have been talking about since 
		<select onchange="window.location='?since='+this.options[this.selectedIndex].value">
			<option value="">forever</option>
			<option value="365"<?=($since == 365 ? ' selected="selected"' : '')?>>one year ago</option>
			<option value="31"<?=($since == 31 ? ' selected="selected"' : '')?>>one month ago</option>
			<option value="7"<?=($since == 7 ? ' selected="selected"' : '')?>>one week ago</option>
		</select>
		<div class="speechpt"></div>
	</div><br style="clear:left;"/>
	<br/>
	<?
	$query = "SELECT tag FROM forums_tags LEFT JOIN forums_topics USING (tid)";
	if($since) $query.= " WHERE last_post > DATE_ADD(CURDATE(), INTERVAL -".$since." DAY)";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if($topicnum = mysqli_num_rows($res)) {
		?>
		<div class="forum-tag-list">
			<?
			
			while($row = mysqli_fetch_assoc($res)) {
				$tags[$row['tag']]++;
			}
			//randomize
			$aux = array();
			$keys = array_keys($tags);
			shuffle($keys);
			foreach($keys as $key) {
				$aux[$key] = $tags[$key];
				unset($tags[$key]);
    	}
    	$tags = $aux;
    	
			$mean = array_sum($tags) / count($tags);
			while(list($tag, $num) = each($tags)) {
				unset($tagwords);
				$fontsize = 5 + ($num * $mean);
				if($fontsize < 8) $fontsize = 8;
				if($fontsize > 45) $fontsize = 45;
				echo '<a href="/forums/?tag='.formatNameURL($tag).'" style="font-size:'.$fontsize.'pt" title="'.$num.' topic'.($num != 1 ? 's' : '').'">'.$tag.'</a>'."&nbsp;\n";
			}
			?>
		</div>
		<?
	} else {
		echo "No topics discussed during this timeframe.";
	}
	?>
</div>
<?
$page->footer();

?>