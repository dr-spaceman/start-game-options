<?
use Vgsite\Page;
$page = new Page();

if($_GET['init']){
	
	$lm = $_GET['lm'];
	if(!$lm) $lm = 0;
	$max = 300;
	
	//record current scores and counts
	$q = "SELECT usrid FROM users LIMIT $lm, $max";
	$res = mysqli_query($GLOBALS['db']['link'], $q);
	if(!mysqli_num_rows($res)) die("Finished");
	while($row = mysqli_fetch_assoc($res)){
		
		$user_id = $row['usrid'];
		$ins = array();
		$user = User::getById($user_id);
		$ins['num_forumposts'] = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM forums_posts WHERE usrid = '$user_id'"));
		$ins['num_pageedits'] =  mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM `pages_edit` WHERE `usrid` = '$user_id' AND published='1'"));
		$ins['num_ps'] = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM `pages` WHERE `contributors` = '$user_id' OR `contributors` LIKE '$user_id|%'"));
		$ins['num_sblogposts'] = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM `posts` WHERE `usrid` = '$user_id' AND unpublished='0' AND pending='0'"));
		$ins['contribution_score'] = $user->data['contribution_score'];
		$ins['forum_rating'] = $user->data['forum_rating'];
		$ins['sblog_rating'] = $user->data['sblog_rating'];
		
		$score = UserCalcScore($user_id, $ins);
		
		if($score['total'] >= 1 && !mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM users_data WHERE usrid = '".$user_id."' AND `date` = '".date("Y-m-d")."' LIMIT 1"))){
			$q2 = "INSERT INTO users_data (usrid, `date`, ".implode(", ", array_keys($ins)).", score_forums, score_pages, score_sblogs, score_total) VALUES 
				('$user_id', '".date("Y-m-d")."', '".implode("', '", array_values($ins))."', '".$score['forums']."', '".$score['pages']."', '".$score['sblogs']."', '".$score['total']."');";
			if(!mysqli_query($GLOBALS['db']['link'], $q2)) $err = "Error on Query: $q2; ".mysqli_error($GLOBALS['db']['link']);
		}
		
		$q2 = "UPDATE users SET 
			score_forums = '".$score['forums']."',
			score_pages = '".$score['pages']."',
			score_sblogs = '".$score['sblogs']."',
			score_total = '".$score['total']."'
			WHERE usrid = '$user_id' LIMIT 1";
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
