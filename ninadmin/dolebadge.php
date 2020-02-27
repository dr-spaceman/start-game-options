<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
$page = new page;

require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.badges.php");
$_badges = new badges;

if($_GET['init']){
	
	$lm = $_GET['lm'];
	if(!$lm) $lm = 0;
	$max = 300;
	
	switch($_GET['bid']){
		case 1:
			$q = "SELECT usrid FROM users LIMIT $lm, $max";
			$res = mysql_query($q);
			if($num = mysql_num_rows($res)){
				while($row = mysql_fetch_assoc($res)){
					$_badges->earn(1, $row[usrid]);
				}
			} else $err = "<b>All done!</b>";
			break;
			
		case '10':
			$q = "SELECT DISTINCT(usrid) FROM posts where unpublished != '1' LIMIT $lm, $max";
			$res = mysql_query($q);
			if($num = mysql_num_rows($res)){
				while($row = mysql_fetch_assoc($res)){
					$_badges->earn(10, $row[usrid]);
				}
			} else $err = "<b>All done!</b>";
			break;
			
		case 11:
		case 12:
		case 13:
		case 14:
		case 38:
			$q = "SELECT DISTINCT(usrid) FROM posts LIMIT $lm, $max";
			$res = mysql_query($q);
			if($num = mysql_num_rows($res)){
				while($row_ = mysql_fetch_assoc($res)){
					$query = "SELECT type, type2, rating, ratings, rating_weighted, usrid FROM posts WHERE usrid='$row_[usrid]' AND unpublished != '1' AND `pending` != '1'";
					$res   = mysql_query($query);
					if(mysql_num_rows($res)){
						
						$goodposts = 0;
						$picposts = 0;
						$postedtypes = array();
						
						while($row = mysql_fetch_assoc($res)){
						
							if($in['type2'] == "review") $_badges->earn(13, $row_[usrid]); //review
							
							if($row['ratings'] > 4 && $row['rating'] >= 80) $goodposts++;
							
							if($row['type'] == "gallery") $row['type'] = "image";
							if($row['type'] == "image") $picposts++;
							if(!in_array($row['type'], $postedtypes)) $postedtypes[] = $row['type'];
						
						}
					
						if($goodposts >= 24){
							$_badges->earn(11, $row_[usrid]); //welterweight
						}
						if($goodposts >= 99){
							$_badges->earn(12, $row_[usrid]); //heavyweight
						}
						if(count($postedtypes) == 6){
							$_badges->earn(14, $row_[usrid]); //kungfu master (one of every type)
						}
						if($picposts >= 5){
							$_badges->earn(38, $row_[usrid]); //photo man
						}
					}
				}
			} else $err = "<b>All done!</b>";
			break;
			
		default:
			$err = "No badge data";
			
		}
	
	?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
	<?=(!$err ? '<meta http-equiv="REFRESH" content="5;url=?init=1&lm='.($lm + $max).'&bid='.$_GET['bid'].'">' : '')?>
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

if($bid = $_POST['bid']){
	
	//manually dole
	
	$uname = trim($_POST['username']);
	$q = "SELECT usrid FROM users WHERE username = '".mysql_real_escape_string($uname)."' LIMIT 1";
	$dat = mysql_fetch_object(mysql_query($q));
	if(!$dat->usrid) $errors[] = "Couldn't find username '$uname'.";
	else {
		$_badges->earn($bid, $dat->usrid);
		$results[] = "Badge successfully awarded";
	}
	
}

$page->title = "Dole out badges";
$page->header();

if($usrrank < 8) $page->die_("No access");

?>
<h1>Dole out badges</h1>

<form action="dolebadge.php" method="post" style="float:right; width:50%; font-size:15px;">
	Award 
	<select name="bid">
		<option value="">Select a badge...</option>
		<?
		foreach($_badges->badges as $b){
			echo '<option value="'.$b['bid'].'">'.$b['name'].'</option>';
		}
		?>
	</select> 
	to 
	<input type="text" name="username" value="username" class="resetonfocus"/> 
	<input type="submit" value="Dole"/>
</form>

<select onchange="$('#recfram').show().attr('src', '?init=1&bid='+$(this).val());">
	<option value="">Select a badge to automatically dole out...</option>
	<?
	foreach($_badges->badges as $b){
		echo '<option value="'.$b['bid'].'">'.$b['name'].'</option>';
	}
	?>
</select>

<p></p>

<iframe name="" src="" frameborder="0" id="recfram" style="display:none;"></iframe>
	
<?
$page->footer();
?>
