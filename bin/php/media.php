<?

// Media Gallery       //
// Created 13 Jan 2008 //
// by Matt Berti       //

use Vgsite\Page;

$mid = $_GET['mid']; //show gallery via database (media.media_id)
$dir = $_GET['dir']; //show gallery via directory
$file = $_GET['file']; //individual file
$subid = $_GET['subid']; //navigational id

//get media data
if($mid) {
	$q = "SELECT * FROM media LEFT JOIN media_categories USING (category_id) WHERE media_id='$mid' LIMIT 1";
}

$page = new Page();
$page->title = "Videogam.in";
$page->style[] = "";
$page->style[] = "";
$page->meta_description = "";
$page->meta_keywords = "";

$page->header();

?>

<h2>Heading</h2>

content goes here

<?

$page->footer();

?>