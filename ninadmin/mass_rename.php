<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
$page = new page;
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.pages.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");

if($usrrank < 8) { include("../404.php"); exit; }

$page->title = "Page Management / Mass Rename Link";
$page->header();

?>
<h1>Mass Rename a Link</h1>
<?

do if ($_POST){
	
	$title = formatName($_POST['title']);
	if($title == "") break;
	
	$newtitle = formatName($_POST['newtitle']);
	if($newtitle == "") break;
	
	$query = "SELECT title, pgid FROM pages_links LEFT JOIN pages ON (pgid = from_pgid) WHERE `to` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."'";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)){
		
		if($row['title'] == "") continue;
		
		$resl.= "<dt>Attempting [[".$row['title']."]]</dt>";
		
		$pgid = $row['pgid'];
		$file = $_SERVER['DOCUMENT_ROOT']."/pages/xml/".$pgid.".xml";
		
		if(!$str = @file_get_contents($file)) continue;
		$str = preg_replace('@\[\[(.*?)\]\]@ise', "replaceLink('\\1')", $str);
		if(!@file_put_contents($file, $str)) continue;
		
		$q = "UPDATE pages_links SET `to` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $newtitle)."' WHERE from_pgid = '$pgid' AND `to` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."'";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) continue;
		
		$resl.= '<dd><b>Success</b></dd>';
		
	}
	
	?>
	<fieldset style="margin:1em 0 2em;">
		<legend>Updated pages</legend>
		<dl>
			<?=bb2html($resl, "pages_only")?>
		</dl>
	</fieldset>
	<?
	
} while (false);

?>
Mass-rename a <code>[[Page Link]]</code> on all content pages. This is only necessary for <b>a well-established category page</b>.

<p></p>

For example: <code class="arrow-right">Super Nintendo</code> <code>Super NES</code>

<p></p>

<form action="mass_rename.php" method="post">
	<input type="text" name="title" value=""/>
	<span class="arrow-right"></span>&nbsp;&nbsp;
	<input type="text" name="newtitle" value=""/>&nbsp;&nbsp;
	<input type="submit" value="Rename"/>
</form>
<?

$page->footer();

function replaceLink($l){
	
	global $title, $newtitle, $pgnamespaces;
	
	$ch = $l;
	
	$thisns = '';
	foreach($pgnamespaces as $ns){
		if(strpos($l, $ns.":") === 0){
			$thisns = $ns.":";
			$ch = str_replace($thisns, "", $ch);
		}
	}
	
	if(strstr($ch, "|")) {
		list($ch, $thistext) = explode("|", $ch);
		$thistext = "|".trim($thistext);
	}
	
	$ch = strtolower($ch);
	$title = strtolower($title);
	
	if($ch == $title) return "[[".$thisns.$newtitle.$thistext."]]";
	
	return "[[$l]]";
	
}

?>