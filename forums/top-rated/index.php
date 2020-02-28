<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.forum.php");

$page = new page;
$page->title = "Videogam.in forums / top rated topics";
$page->style[] = "/bin/css/forums.css";
$page->header();
$forum = new forum();
$forum->special_forum = "top-rated";
$forum->showForum();
$page->footer();

?>