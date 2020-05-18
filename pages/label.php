<?

/* Output a label for a given page */

use Vgsite\Page;

if($pglabel['title']) $title = $pglabel['title'];
elseif($_GET['title']) $title = $_GET['title'];
elseif($_POST['title']) $title = $_POST['title'];

echo pglabel($title);

?>