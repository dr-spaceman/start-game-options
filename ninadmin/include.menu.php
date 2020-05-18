<h5>Videogam.in Admin</h5>
<div class="hr-gray"></div>
<dl>
			
	<dt>General Admin.</dt>
	<dd><a href="/ninadmin/albums.php">Albums Database</a></dd>
	<dd><a href="/ninadmin/people.php">People Database</a></dd>
	<?=($_SESSION['user_rank'] >= 7 ? '<dd><a href="/ninadmin/avatars.php">Avatar Management</a></dd>' : '')?>
		<?
	if($_SESSION['user_rank'] >= 8) {
		echo '<dd><a href="/ninadmin/user-contributions.php">User Contributions</a>';
		$pend = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM pending"));
		if($pend) echo ' ('.$pend.')';
		echo '</dd>';
		}
		?>
			
	<dt>Games</dt>
	<dd><a href="/games/add.php">New Game</a></dd>
	<dd><a href="/ninadmin/games-mod.php">Edit a Game</a></dd>
	<?=($_SESSION['user_rank'] >= 8 ? '
	<dd><a href="/ninadmin/games-misc.php?what=platforms">Platform Management</a></dd>
	<dd><a href="/ninadmin/games-prune.php">Prune Database</a></dd>
	' : '')?>
			
	<dt>News</dt>
			
	<dt>Media</dt>
	<dd><a href="/ninadmin/media.php">Media Manager</a></dd>
			
	<dt>Documentation & Help</dt>
	<dd><a href="/ninadmin/drafts.php">Your Drafts & Docs</a></dd>
	<dd><a href="/ninadmin/resources.php">External Resources</a></dd>
	<dd><a href="/ninadmin/templates.php">Templates</a></dd>
			
</dl>