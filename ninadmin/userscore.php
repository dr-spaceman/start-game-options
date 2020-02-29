<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
$page = new page;

if($_GET['init']){
	
	$lm = $_GET['lm'];
	if(!$lm) $lm = 0;
	$max = 300;
	
	//record current scores and counts
	$q = "SELECT usrid FROM users LIMIT $lm, $max";
	$res = mysqli_query($GLOBALS['db']['link'], $q);
	if(!mysqli_num_rows($res)) die("Finished");
	while($row = mysqli_fetch_assoc($res)){
		
		$usr = $row['usrid'];
		$ins = array();
		$userdat = getUserDat($usr);
		$ins['num_forumposts'] = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM forums_posts WHERE usrid = '$usr'"));
		$ins['num_pageedits'] =  mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM `pages_edit` WHERE `usrid` = '$usr' AND published='1'"));
		$ins['num_ps'] = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM `pages` WHERE `contributors` = '$usr' OR `contributors` LIKE '$usr|%'"));
		$ins['num_sblogposts'] = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM `posts` WHERE `usrid` = '$usr' AND unpublished='0' AND pending='0'"));
		$ins['contribution_score'] = $userdat->contribution_score;
		$ins['forum_rating'] = $userdat->forum_rating;
		$ins['sblog_rating'] = $userdat->sblog_rating;
		
		$score = UserCalcScore($usr, $ins);
		
		if($score['total'] >= 1 && !mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM users_data WHERE usrid = '".$usr."' AND `date` = '".date("Y-m-d")."' LIMIT 1"))){
			$q2 = "INSERT INTO users_data (usrid, `date`, ".implode(", ", array_keys($ins)).", score_forums, score_pages, score_sblogs, score_total) VALUES 
				('$usr', '".date("Y-m-d")."', '".implode("', '", array_values($ins))."', '".$score['forums']."', '".$score['pages']."', '".$score['sblogs']."', '".$score['total']."');";
			if(!mysqli_query($GLOBALS['db']['link'], $q2)) $err = "Error on Query: $q2; ".mysqli_error($GLOBALS['db']['link']);
		}
		
		$q2 = "UPDATE users SET 
			score_forums = '".$score['forums']."',
			score_pages = '".$score['pages']."',
			score_sblogs = '".$score['sblogs']."',
			score_total = '".$score['total']."'
			WHERE usrid = '$usr' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q2)) $err = "Error on Query: $q2; ".mysqli_error($GLOBALS['db']['link']);
		
	}
	
	?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
	<?=(!$err ? '<meta http-equiv="REFRESH" content="5;url=userscore.php?init=1&lm='.($lm + $max).'">' : '')?>
	</head>
	<body>
		<?=$err?>
		<?=$lm?>-<?=($lm + $max)?> Fin.<br/>
		<?=(!$err ? 'Loading' : '')?> <a href="?init=1&lm=<?=($lm + $max)?>">next</a> set...
	</body>
	</html>
	<?
	exit;
	
}

$page->title = "Recalculate user scores";
$page->header();

?>
<h1>Recalculate all user scores</h1>
<p>Only necessary when the mathematical formula changes.</p>
<input type="button" value="Initiate Recalculation" onclick="$(this).hide(); $('#recfram').show().attr('src', '/ninadmin/userscore.php?init=1');" disabled="true"/>

<p></p>

<iframe name="" src="" frameborder="0" id="recfram" style="display:none;"></iframe>
	
<?
$page->footer();
?>
