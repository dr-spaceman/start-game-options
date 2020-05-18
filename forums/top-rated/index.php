<?
use Vgsite\Page;
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.forum.php");

$page = new Page();
$page->title = "Videogam.in forums / top rated topics";
$page->style[] = "/bin/css/forums.css";
$page->header();
$forum = new forum();
$forum->special_forum = "top-rated";
$forum->showForum();
$page->footer();

?>