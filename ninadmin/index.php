<?
use Vgsite\Page;
$page = new Page();
$page->title = "Nintendo Site Admin Index";

if($_SESSION['user_rank'] < 6) { include("../404.php"); exit; }

$page->header();

$page->openSection();

?>
<h1>Videogam.in Admin</h1>

<ul>
	<li><a href="/ninadmin/albums.php">Albums Database</a></li>
	<li><a href="/ninadmin/avatars.php">Avatar Management</a></li>
	<?=($_SESSION['user_rank'] >= 8 ? '<li><a href="user_mgt.php">User Management</a></li>' : '')?>
	<?=($_SESSION['user_rank'] >= 8 ? '<li><a href="dolebadge.php">Dole out Badges</a></li>' : '')?>
	<li><a href="/ninadmin/userscore.php">Recalculate User Scores</a></li>
</ul>

<h5>.Incyclopedia</h5>
<ul>
	<?=($_SESSION['user_rank'] >= 8 ? '<li><a href="mass_rename.php">Mass Rename Link</a></li>' : '')?>
	<li><a href="build_index.php">Rebuild Index</a></li>
	<li><a href="build_cattrees.php">Rebuild A Category Tree</a></li>
</ul>

<h5>Sblogs</h5>
<ul>
	<li><a href="posts-reformat.php">Reformat Text</a> - Reformat all text introduction fields (ie text on post lists) (this is necessary if changes are made to bbcodeMarkdown formatting output)</li>
</ul>

<h5>Image Uploads</h5>
<ul>
	<li><a href="images_reprocess.php">Reprocess all image uploads</a> (recreate thumbs, etc.)</li>
</ul>

<h5>Testing</h5>
<ul>
	<li><a href="/test_bbcode.php">BB Code tester</a></li>
</ul>

<?

$page->footer();

?>