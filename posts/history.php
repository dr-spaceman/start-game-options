<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
$page = new page();
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.posts.php");

if(!$nid = $_GET['nid']) $page->kill('No post ID given. <a href="manage.php">Sblog Manager</a>');


try { $post = new post($nid); }
catch(Exception $e){ $page->kill($e->getMessage()); }

$page->title = "Videogam.in Sblog History - ".$post->description;
//$page->javascripts[] = "/posts/posts_form.js";
//$page->javascripts[] = "/bin/script/jquery.textareaautosize.js";
//$page->css[] = "/posts/posts_form.css";
$page->header();

?><h1>Sblog History</h1>

<h2><a href="<?=$post->url?>">#<?=$nid?></a> <?=$post->description?></h2>

<?
$query = "SELECT * FROM posts_edits WHERE nid='$nid' ORDER BY datetime ASC";
$res = mysqli_query($GLOBALS['db']['link'], $query);
if(!$num_edits = mysqli_num_rows($res)){
	$page->kill("No edits recorded.");
}

echo '<p>'.$num_edits.' edits recorded</p>';

while($row = mysqli_fetch_assoc($res)){
	echo '<dl><dt>'.$row['datetime'].' '.outputUser($row['usrid']).'</dt>';
	echo '<dd><dl><dt>Edit Comments</dt><dd>'.($row['comments'] ? $row['comments'] : 'None').'&nbsp;</dd>';
	if(substr($row['content'], 0, 2) == '{"'){
		$content = json_decode($row['content']);
		foreach($content as $k => $v){
			echo '<dt>'.$k.'</dt><dd style="white-space:pre-wrap; word-break:break-all;">';
			if(is_string($v)) echo htmlspecialchars($v);
			elseif(is_array($v)) print_r($v);
			echo '</dd>';
		}
	} else echo '<dt>Content (old format)</dt><dd style="white-space:pre-wrap; word-break:break-all;">'.$row['content'].'</dd>';
	echo '</dl></dd></dl>';
}

$page->footer();
		