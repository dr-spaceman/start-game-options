<?
use Vgsite\Page;

$page = new Page();
$page->title = "Page Management / Rebuild Category Trees";

$page->header();

if($title=$_POST['title']){
	require_once $_SERVER['DOCUMENT_ROOT']."/pages/class.pages.edit.php";
	$GLOBALS['debug'] = 1;
	categoryTreeTemplate($title);
}

?>
<h1>Rebuild Category Trees</h1>

<p>Input a page title to rebuild (or eliminate) its Category Tree. All ancestor pages listed in the tree will also reflect the changes.</p>

<form action="build_cattrees.php" method="post">
<input type="text" name="title" placeholder="Page title"/>
<button type="submit">Commence rebuilding</button>
</form>
<?

$page->footer();
		

?>