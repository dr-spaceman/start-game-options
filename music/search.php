<?
require($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");
	
$page = new page;
$page->title = "Videogam.in / Music / Search: ".$_GET['find'];
$page->css[] = "/music/style.css";
$page->freestyle.= '
TABLE.plain, .plain TD, .plain TH { border-color:#C0C0C0 !important; }
#page H1 { color:#425C84; font:bold 17pt Arial; letter-spacing:-1px; }
#page H1 SPAN { color:#AAA; }';
$page->width = "fixed";

$find = $_GET['find'];
$by = $_GET['by'];
$dis = str_replace('"','&quot;',$find);
$dis2 = str_replace('"','',$find);
$dis2 = strlen($dis2);
$find = urldecode($find);
$find = htmlentities($find, ENT_QUOTES);
$find2 = urlencode($find);

$Query = "SELECT title, albumid, subtitle, keywords FROM albums WHERE title LIKE '%$find%' OR subtitle LIKE '%$find%' OR keywords LIKE '%$find%' OR cid LIKE '%$find%'";
$Result = mysqli_query($GLOBALS['db']['link'], $Query);
$check = mysqli_num_rows($Result);
while ($Array = mysqli_fetch_assoc($Result)) {
	$id = $Array['albumid'];
}

if ($check == 1) {
	header("Location: /music/?id=$id");
	exit;
}

$page->header();
include ("nav.php");

?>
<h2>Game Music Search</h2>

<div style="border:1px solid #C0C0C0; background-color:#EEE; margin:0 0 15px 0; padding:5px;">
	<?
	if (!$find) {echo "No keywords entered";}
	elseif ($check == 0) {echo "No album entries found for <i>$dis</i>";}
	elseif ($dis2 < 3) {echo "No album entries found for <i>$dis</i>";}
	else {echo "<b>$check</b> album ".($check == 1 ? 'entry' : 'entries')." found for <i>$dis</i>";}
	?>
</div>

<table border="0" cellpadding="7" cellspacing="0" width="100%" class="plain">
	<tr>
	<?
	
	if (!$by) $by = 'title';
	
	switch ($by) {
	case "title":
	  echo ("<th width=\"45%\">Title</th>
	  <th width=\"15%\"><a href=\"?find=$find2&by=release\">Rel. Date</a></th>
	  <th width=\"20%\"><a href=\"?find=$find2&by=cid\">Catalog ID</a></th>");
	  $Query = "SELECT l.title, l.release, l.cid, l.view, l.id, l.albumid, l.subtitle, l.keywords FROM albums as l where l.title like '%$find%' or l.subtitle like '%$find%' or l.keywords like '%$find%' or l.albumid like '%$find%' ORDER BY title, subtitle";
	  break;
	case "release":
	  echo ("<th width=\"45%\"><a href=\"?find=$find2&by=title\">Title</a></th>
	  <th width=\"15%\">Rel. Date</th>
	  <th width=\"20%\"><a href=\"?find=$find2&by=cid\">Catalog ID</a></th>");
	  $Query = "SELECT l.title, l.release, l.cid, l.view, l.id, l.albumid, l.subtitle, l.keywords FROM albums as l where l.title like '%$find%' or l.subtitle like '%$find%' or l.keywords like '%$find%' or l.albumid like '%$find%' ORDER BY datesort";
	  break;
	case "cid":
	  echo ("<th width=\"45%\"><a href=\"?find=$find2&by=title\">Title</a></th>
	  <th width=\"15%\"><a href=\"?find=$find2&by=release\">Rel. Date</a></th>
	  <th width=\"20%\">Catalog ID</th>");
	  $Query = "SELECT l.title, l.release, l.cid, l.view, l.id, l.albumid, l.subtitle, l.keywords FROM albums as l where l.title like '%$find%' or l.subtitle like '%$find%' or l.keywords like '%$find%' or l.albumid like '%$find%' ORDER BY cid";
	  break;
	}
	?>
	</tr>
	<?
	
	$Result = mysqli_query($GLOBALS['db']['link'], $Query);
	
	if (!$find) {
	
	}
	elseif ($dis2 < 3) {
	
	}
	else {
	while ($row = mysqli_fetch_assoc($Result)) {
	  $list[] = $row;
	$check = count($list);
	}
	}
	
	for ($i = 0; $i < count($list); $i++) {
	  $toggle = ($i % 2);
	  $bgc = '';
	  if (!$toggle) {
	    $bgc = " bgcolor=\"#f5f5f5\"";
	  }
	  
	  if ($list[$i][view]) {
	    $title = "<a href=\"/music/?id={$list[$i][albumid]}\">{$list[$i][title]} <em>{$list[$i][subtitle]}</em></a>";
	  } else {
	    $title = '<span style="color:#666">'.$list[$i]['title'].' <em>'.$list[$i]['subtitle'].'</em></span>';
	  }
	
	  echo ("<tr$bgc>
	    <td width=\"45%\">$title</td>
	    <td width=\"15%\">{$list[$i][release]}</td>
	    <td width=\"20%\">{$list[$i][cid]}</td>
	  </tr>");
	
	}
	
	?>
</table>
<?

$page->footer();

?>
