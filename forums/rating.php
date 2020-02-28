<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.forum.php");

$forum = new forum();

$tid    = $_GET['tid'];
$rating = $_GET['rating'];
if($rating) {
	if($rating != 1) $rating = '0';
}

if($tid && $rating != '') {
	if(!$usrid) $msg = '<a href="/login.php?loc=/forum/'.$forum->getForumURL('',mysqli_real_escape_string($GLOBALS['db']['link'], $tid)).'" target="_parent">login</a> to rate';
	else {
		$q = sprintf("SELECT * FROM forums_ratings WHERE tid = '%s' and `usrid` = '$usrid' LIMIT 1",
			mysqli_real_escape_string($GLOBALS['db']['link'], $tid));
		$res = mysqli_query($GLOBALS['db']['link'], $q);
		$dat = mysqli_fetch_object($res);
		if($dat && $rating != $dat->rating) { // if user has a rating of this tid already, and he is rating differently
			$q = sprintf("UPDATE forums_ratings SET `rating` = '%s' WHERE tid = '%s' and `usrid` = '$usrid' LIMIT 1",
				mysqli_real_escape_string($GLOBALS['db']['link'], $rating),
				mysqli_real_escape_string($GLOBALS['db']['link'], $tid));
			$res = mysqli_query($GLOBALS['db']['link'], $q);
			if(!$res) die("couldn't update ratings db");
			
			if($rating == 1) $new_rating = '+ 1';
			else $new_rating = '- 1';
			
			$q = sprintf("UPDATE `forums_topics` SET `rating` = `rating` $new_rating WHERE tid = '%s' LIMIT 1",
				mysqli_real_escape_string($GLOBALS['db']['link'], $tid));
			$res = mysqli_query($GLOBALS['db']['link'], $q);
			if(!$res) die("couldn't update topics db");
		} elseif($rating == $dat->rating) {
			//do nothing
		} else {
			$q = sprintf("UPDATE `forums_topics` SET `rating` = `rating` + %s, `ratings` = `ratings` + 1 WHERE tid = '%s' LIMIT 1",
				mysqli_real_escape_string($GLOBALS['db']['link'], $rating),
				mysqli_real_escape_string($GLOBALS['db']['link'], $tid));
			$res = mysqli_query($GLOBALS['db']['link'], $q);
			if(!$res) die('couldnt update db');
			
			$q = sprintf("INSERT INTO `forums_ratings` (`usrid`, `tid`, `rating`) VALUES ('$usrid', '%s', '%s')",
				mysqli_real_escape_string($GLOBALS['db']['link'], $tid),
				mysqli_real_escape_string($GLOBALS['db']['link'], $rating));
			$res = mysqli_query($GLOBALS['db']['link'], $q);
			if(!$res) die('couldnt insert into db');
		}
		
		header("refresh: 0; url=/forums/rating.php?tid=$tid");
		$msg = 'evaluating<span style="text-decoration:blink;">...</span>';
	}
} elseif(!$tid) {
	$msg = "Error: no tid given";
} else {
	$q = sprintf("SELECT `rating`, `ratings` FROM `forums_topics` WHERE tid = '%s' LIMIT 1",
		mysqli_real_escape_string($GLOBALS['db']['link'], $tid));
	$res = mysqli_query($GLOBALS['db']['link'], $q);
	$dat = mysqli_fetch_object($res);
	if($dat->ratings) {
		$total = $dat->rating / $dat->ratings;
		if($total >= .5) $thumb_dir = "up";
		else $thumb_dir = "down";
		$msg = ('
		<table border="0" cellpadding="0" cellspacing="0" align="center">
		<tr><td nowrap="nowrap"><acronym title="total of '.round($total, 2).'/1 based on '.$dat->ratings.' ratings">'.$dat->ratings.' Rating'.($dat->ratings != 1 ? 's' : '').'</acronym>:&nbsp;</td><td><img src="/bin/img/thumbs-'.$thumb_dir.'.png" alt="thumbs '.$thumb_dir.'" border="0" /></td></tr></table>
		');
	} else $msg = "Not yet rated";
}

?><html>
<head>
<style>
BODY { margin: 0; padding: 5px 7px; text-align: center; background-color: #f5f5f5; font: normal 11px verdana; }
TD { font: normal 11px verdana; }
ACRONYM { cursor: help; }
A { color: #369; }
</style>
</head>
<body>
<?=$msg?>
</body>
</html>
<?