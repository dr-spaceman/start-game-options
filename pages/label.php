<?

/* Output a label for a given page */

require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";

if($pglabel['title']) $title = $pglabel['title'];
elseif($_GET['title']) $title = $_GET['title'];
elseif($_POST['title']) $title = $_POST['title'];

echo pglabel($title);

?>