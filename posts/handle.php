<?
use Vgsite\Page;
$page = new Page();
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.posts.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.forums.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.tags.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");

//list($y, $m, $d, $desc_url, $xvar) = explode("/", $_GET['path']);

if($nid = $_GET['nid']){
	require("item.php");
	exit;
}

// ARCHIVE //

$page->title = "Videogam.in / Sblog Archive";
$page->first_section = array("id" => "posts", "class" => "posts");
$page->header();

$posts = new posts();

$params = array("user", "category", "post_type", "attachment", "archive", "tags", "privacy", "unpublished", "pending", "date", "sort", "page");
foreach($params as $param){
	if(isset($_GET[$param])) $posts->query_params[$param] = $_GET[$param];
}
$posts->parseParams();
$posts->buildQuery();

?>
<h1>News & Blogs Archive</h1>
<?

echo $posts->postsList();

$page->footer();

?>