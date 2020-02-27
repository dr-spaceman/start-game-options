<?
$page->title = $gdat->title." fans -- Videogam.in";
$page->header();

?>
<div id="game-fans" class="conts">
<?

if(!$orderby = $_GET['orderby']) $orderby = "u.username";
if($orderby == "u.username") $orderdir = "ASC";
else $orderdir = "DESC";

$query = "SELECT mg.*, u.username FROM my_games mg, users u WHERE mg.gid='$gdat->gid' AND u.usrid=mg.usrid ORDER BY $orderby $orderdir".($orderby != "u.username" ? ", u.username" : "");
$res   = mysql_query($query);
if(!mysql_num_rows($res)) {
	echo "There are no fans of this game yet.<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>";
} else {
	?>
	<table border="0" cellpadding="0" cellspacing="0" width="100%" id="fanslist">
		<tr>
			<th><a href="?sort=u.username">User</a></th>
			<th><a href="?sort=mg.rating">Affection</a></th>
			<th><a href="?sort=mg.own">Own</a></th>
			<th><a href="?sort=mg.play">Play</a></th>
			<th><a href="?sort=mg.play_online">Play Online</a></th>
		</tr>
		<?
		while($row = mysql_fetch_assoc($res)) {
			?>
			<tr>
				<td class="username"><?=outputUser($row['usrid'])?></td>
				<td><?=$row['rating']?></td>
				<td><?=$row['own']?></td>
				<td><?=$row['play']?></td>
				<td><?=$row['play_online']?></td>
			</tr>
			<?
		}
		?>
	</table>Fancier fan list coming soon
	<?
}

?>
</div>
<?

$page->footer();

?>