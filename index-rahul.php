<?
require("bin/php/page.php");
require("bin/php/include.stream.php");

$page = new page();
$page->title = "Videogam.in";
$page->header();

$latestActivity = latestActivity();
?>

<h1>Latest activity</h1>
<?=output_stream($latestActivity);?>

<?
$page->footer();
?>