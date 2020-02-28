<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
$page = new page;
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.pages.edit.php");

$to = formatName($_GET['to']);

$page->title = "Links to ".htmlSC($to)." -- Videogam.in";

$ed = new pgedit($to);
$ed->header();

if(!$to){ echo "No page specified"; $ed->footer(); exit; }

?>
<div id="pged-links" class="pgedbg" style="padding:30px 40px;">
<h3 style="margin-top:0;">Pages that link to <a href="<?=pageURL($to)?>"><?=$to?></a></h3>
<?

$query = "SELECT * FROM pages_links LEFT JOIN pages ON (pgid = from_pgid) WHERE `to` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $to)."';";
$res   = mysqli_query($GLOBALS['db']['link'], $query);
while($row = mysqli_fetch_assoc($res)){
	if(!$row['title']) continue;
	if($row['namespace'] == "Category") $links_ctg[] = $row;
	else $links[] = $row;
}
if(!$links && !$links_ctg) {
	echo "No pages link to this page.";
} else {
	if($links_ctg){
		?>
		<h4><?=$to?> (Category)</h4>
		<ul class="tree">
			<?
			foreach($links_ctg as $link){
				?>
				<li class="branch"><a href="<?=pageURL($link['title']).($link['is_redirect'] || $link['redirect_to'] ? '/?redirect=no' : '')?>"><?=$link['title']?></a></li>
				<?
			}
			?>
		</ul>
		<div class="hr"></div>
		<?
	}
	if($links){
		echo '<ul>';
		foreach($links as $link) {
			?>
			<li>
				<a href="<?=pageURL($link['title']).($link['is_redirect'] || $link['redirect_to'] ? '/?redirect=no' : '')?>"><?=$link['title']?></a>
				<?=($link['namespace'] ? ' ('.$link['namespace'].')' : '')?>
				<?
				if($link['is_redirect']) {
					echo ' (redirection)';
					//check for pages that link to the page that redirects here
					$query = "SELECT `title` FROM pages_links LEFT JOIN pages ON (pgid = from_pgid) WHERE `to` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $link['title'])."';";
					$res   = mysqli_query($GLOBALS['db']['link'], $query);
					$sublinks = array();
					while($row = mysqli_fetch_assoc($res)) {
						$sublinks[] = $row['title'];
					}
					if(count($sublinks)){
						echo '<ul class="tree">';
						foreach($sublinks as $slink){
							echo '<li><a href="'.pageURL($slink).'">'.$slink.'</a></li>';
						}
						echo '</ul>';
					}
				}
				?>
			</li>
			<?
		}
		echo '</ul>';
	}
}

?>
</div><!--#pged-links-->
<?

$ed->footer();
?>