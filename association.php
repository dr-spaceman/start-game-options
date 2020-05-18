<?
use Vgsite\Page;
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");

$assoc = ($_GET['association'] ? $_GET['association'] : "Square Enix");

$page = new Page();
$page->title = "Videogam.in / Association / $assoc";
$page->freestyle = <<<EOF
#association-index .col {
	width: 48%; 
	float:left; }
#assoc-games {
	margin-left: 4%; }
#association-index H3 {
	margin-bottom: 0; }
#association-index .col UL {
	margin: 0;
	padding: 0;
	list-style: none; }
#association-index .col LI {
	margin: 0;
	padding: 5px 0;
	border-bottom: 1px solid #CCC; }
#association-index .col LI SPAN {
	float: right;
	color: #333; }
.no-desc { padding:5px; border:1px solid #CCC; }
EOF;

$page->header();

?>
<div id="association-index">
<h2><?=$assoc?></h2>

<div id="desc">
<?
$q = "SELECT text FROM wiki WHERE `field`='description' AND subject_field='association' AND subject_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $assoc)."' ORDER BY `datetime` DESC LIMIT 1";
if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
	$dat->text = bb2html($dat->text);
	$dat->text = reformatLinks($dat->text);
	$dat->text = nl2br($dat->text);
	?>
	<div style="position:relative; line-height:150%;" onmouseover="toggle('desc-edit','')" onmouseout="toggle('','desc-edit')">
		<?=$dat->text?>
		<div id="desc-edit" style="display:none; position:absolute; right:0; color:#AAA; padding:2px 6px; background-color:#FFFFAE;">
			<a href="/wiki.php?subj=association/<?=urlencode($assoc)?>/description" style="padding-left:15px; background:url(/bin/img/icons/edit.gif) no-repeat 0 50%;">edit</a> &middot; 
			<a href="/wiki.php?subj=association/<?=urlencode($assoc)?>/description&pg=history">history</a>
		</div>
	</div>
	<?
} else {
	?>
	<div class="no-desc">
		There is no description for this association yet. <a href="/wiki.php?subj=association/<?=urlencode($assoc)?>/description" class="arrow-right">Contribute one</a>
	</div>
	<?
}
?>
</div>

<div id="assoc-people" class="col">
	<h3>People</h3>
	<?
	$query = "SELECT pid, name, name_url, title, prolific FROM people WHERE assoc_co LIKE '%$assoc%' OR assoc_other LIKE '%$assoc%' ORDER BY `name`";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)) {
		?><ul><?
		while($row = mysqli_fetch_assoc($res)) {
			echo '<li><span>'.$row['title'].'</span><a href="/people/~'.$row['name_url'].'"'.($row['prolific'] ? ' style="font-weight:bold"' : '').'>'.$row['name'].'</a></li>';
		}
		?></ul><?
	}
	?>
</div>

<div id="assoc-games" class="col">
	<h3>Games</h3>
	<?
	
	$query = "SELECT games.title, title_url, platform, unpublished FROM games_developers 
		LEFT JOIN games USING (gid) 
		LEFT JOIN games_publications ON (games.gid=games_publications.gid AND games_publications.`primary`=1) 
		LEFT JOIN games_platforms ON (games_platforms.platform_id=games_publications.platform_id) 
		WHERE games_developers.developer LIKE '%$assoc%' 
		".($_SESSION['user_rank'] <= 6 ? "AND unpublished != '1'" : "")."
		ORDER BY games.title";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)) {
		?><ul><?
		while($row = mysqli_fetch_assoc($res)) {
			echo '<li><span>'.$row['platform'].'</span><a href="/games/~'.$row['title_url'].'"'.($row['unpublished'] ? ' style="text-decoration:line-through;"' : '').'>'.$row['title'].'</a></li>';
		}
		?></ul><?
	}
	
	?>
</div>
<?

?>
<br style="clear:both"/>
</div>
<?

$page->footer();
?>