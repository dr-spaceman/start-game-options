<?
session_start();

require($_SERVER['DOCUMENT_ROOT']."/ninadmin/album_rules.php");


	require("page.php");
	$page =  new Page();
	checkpermissions('albums.php');

$menu = "<a href=\"?action=new\">CREATE</a> (restart) | <a href=\"?action=edit\">EDIT</a> (restart)<br/><br/>\n";

echo <<<END
$menu\n
END;
	
echo $trackrule;

$page->footer();

?>