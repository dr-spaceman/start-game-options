<?

$page->title = "Videogam.in / Sblog Archive";
$page->first_section = array("id" => "posts", "class" => "posts");
$page->header();

$posts = new posts();

$params = array("user", "category", "archive", "tags", "privacy", "unpublished", "pending", "date", "sort", "page");
foreach($params as $param){
	if($_GET[$param]) $posts->query_params[$param] = $_GET[$param];
}
$posts->parseParams();
$posts->buildQuery();

?>
<h1>News & Blogs Archive</h1>
<?

echo $posts->postsList();

$page->footer();
?>