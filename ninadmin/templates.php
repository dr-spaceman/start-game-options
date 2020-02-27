<?
require ($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");

$page = new page;
$page->title = "Videogam.in Admin / Templates";
$page->admin = TRUE;

$page->header();

?>

<h2>Templates</h2>

<a href="templates/" target="box">Refresh index</a><br/>
<iframe src="templates/" style="width:100%; height:400px; margin-top:5px;" name="box"></iframe>

<?

$page->footer();

?>