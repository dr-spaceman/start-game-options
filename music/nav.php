<div id="albumnav">
	<h1><a href="/music">Game Music Database</a></h1>
	<nav>
		<ul>
			<li><a href="/music/">Overview</a></li>
			<li><a href="/music/master_list.php">Index</a></li>
			<li><a href="/music/by_series.php">By Series</a></li>
			<li><a href="/music/by_composer.php">By Composer</a></li>
			<?=($_SESSION['user_rank'] >= 6 ? '<li><a href="/ninadmin/albums.php?action=new" class="addalbum">+ <span>New Album</span></a></li>' : '')?>
		</ul>
	</nav>
</div>