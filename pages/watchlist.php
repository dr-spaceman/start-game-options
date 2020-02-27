<?
require $_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php";
$page = new page;
require $_SERVER['DOCUMENT_ROOT']."/bin/php/class.pages.php";
unset($page->css);
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/bbcode.php";

if($_POST['rmpages']) {
	foreach($_POST['rmpages'] as $pg_) {
		$q = "DELETE FROM pages_watch WHERE usrid='$usrid' AND `title`='".mysql_real_escape_string($pg_)."';";
		if(!mysql_query($q)) $errors[] = "Couldn't remove $pg_";
		else $removedpgs++;
	}
	if($removedpgs) $results[] = "Sucessfully removed $removedpgs page".($removedpgs != 1 ? 's' : '')." from your watch list.";
}

$chlisttype = "watchlist";
include("changelist.php");

$page->title = "Your Watch List -- Videogam.in";

$page->header();

if(!$usrid) $page->die_('<big style="font-size:150%;">Please <a href="/login.php">Log In</a> to view your watch list.</big>');

?>
<h1>Your Watch List</h1>

<div style="line-height:20px; font-size:14px;">
	<img src="/bin/img/icons/question_block.png" alt="?" width="16" style="float:left; margin:5px 10px 5px 20px;"/>
	Your watch list is a list of pages you would like to closely watch for changes by users.<br/>
	Watching a page gives you better notice of changes as well as more credits toward becoming that page's <b>Patron Saint</b>.
</div>

<?
if(!$numwatching) $page->die_("You aren't watching any pages yet. To watch a page, navigate to that page and click the checkbox at the bottom of the page.");
?>

<div style="float:right; width:35%;">
	<h2>Manage Your List</h2>
	You are watching <?=$numwatching?> pages:
	<form action="watchlist.php" method="post">
		<ul style="list-style:none; margin:0; padding:0;">
			<?
			foreach($watching as $row) {
				$title_url = formatNameURL($row['title']);
				$ul.= '<li style="margin:5px 0 0; padding:0;"><div style="float:right; color:#CCC;">&nbsp;&nbsp;<a href="history.php?title='.$title_url.'">history</a> | <a href="#">discussion</a></div><input type="checkbox" name="rmpages[]" value="'.htmlsc($row['title']).'"/> [['.$row['title'].']]</li>';
			}
			echo bb2html($ul, "pages_only");
			?>
		</ul>
		<div style="margin-top:10px; padding:5px; background-color:#f5f5f5; font-size:14px;">
			<input type="submit" name="rmwatchedpages" value="Remove"/> selected items from your watch list
		</div>
	</form>
</div>

<div style="margin-right:40%;">
	<h2>Changes</h2>
	<div id="chswitch" style="margin:0 0 5px; font-size:14px;">
		<span style="color:#888;">Show changes since</span> &nbsp;
		<a href="?since=lastlogin" class="arrow-toggle<?=(!$since || $since == "lastlogin" ? ' arrow-toggle-on' : '')?>"><acronym title="<?=timeSince($usrlastlogin)?> ago">your last login</acronym></a> &nbsp;
		<a href="?since=3" class="arrow-toggle<?=($since == 3 ? ' arrow-toggle-on' : '')?>">3 days</a> &nbsp;
		<a href="?since=7" class="arrow-toggle<?=($since == 7 ? ' arrow-toggle-on' : '')?>">7 days</a> &nbsp;
		<a href="?since=14" class="arrow-toggle<?=($since == 14 ? ' arrow-toggle-on' : '')?>">14 days</a> &nbsp;
		<a href="?since=30" class="arrow-toggle<?=($since == 30 ? ' arrow-toggle-on' : '')?>">30 days</a>
	</div>
	<?
	
	if(!$pr_changes) {
		echo 'No changes made '.(!$since || $since=="lastlogin" ? 'since your last login '.timeSince($usrlastlogin).' ago.' : 'in the past '.$since.' days.');
	} else {
		?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%" id="wiki-history"><?=bb2html($pr_changes)?></table>
		<div style="padding:5px 0; border-top:2px solid #AAA;">
			<b><?=$i?> changes</b>
			<?
			if($i > $numchanges) {
				$numpages = $i / $limit;
				$numpages = ceil($numpages);
				?>
				&middot; Page <?=$pgnum?> of <?=$numpages?> &nbsp;&nbsp; 
				<span style="color:#BBB;">
					<?=($pgnum > 1 ? '<a href="?since='.$_GET['since'].'&page='.($pgnum - 1).'" class="arrow-left">Previous</a>' : '<span class="arrow-left">Previous</span>')?> &nbsp; 
					<?=($pgnum < $numpages ? '<a href="?since='.$_GET['since'].'&page='.($pgnum + 1).'" class="arrow-right">Next</a>' : '<span class="arrow-right">Next</span>')?>
				</span>
				<?
			}
			?>
			&nbsp;
		</div>
		<?
	}
	?>
</div>

<br style="clear:both;"/>
<?

//$page->openSection(array("class"=>"pgsec pgsec-white"));

$page->footer();
?>