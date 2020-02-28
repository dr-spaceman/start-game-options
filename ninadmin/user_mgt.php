<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
$page = new page;

if($usrrank < 8) { include("../404.php"); exit; }

$page->title = "User Mgt";

if($_GET['username']){
	
	$search_un = trim($_GET['username']);
	
	$query = "SELECT * FROM users WHERE username LIKE '%".mysqli_real_escape_string($GLOBALS['db']['link'], $search_un)."%'";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)){
		$found[] = $row;
	}
	
	if(count($found) < 1) $res = "<p>No username like '$search_un' found</p>";
	elseif(count($found) == 1){
		header("Location: user_mgt.php?userid=".$found[0]['usrid']);
		exit;
	} else {
		$res = "<p>Found ".count($found)." users like '".$search_un."':</p><ul>";
		foreach($found as $row){
			$res.= '<li><b>'.$row['username'].'</b> &ndash; <a href="/~'.$row['username'].'">View Profile</a> | <a href="user_mgt.php?userid='.$row['usrid'].'">Manage</a></li>';
		}
		$res.= '</ul><br/>';
	}
	
}

if(!$userid = $_GET['userid']){
	
	$page->header();
	
	?>
	<h1>User Management</h1>
	
	<?=$res?>
	
	<form action="user_mgt.php" method="get">
		<fieldset style="float:left">
			<legend>Find a user:</legend>
			<span class="fftt"><input type="text" name="username" class="ff"/><label class="tt">Username</label></span>&nbsp;
			<span class="fftt"><input type="text" name="userid" class="ff"/><label class="tt">ID #</label></span>&nbsp;
			<input type="submit" name="" value="Search"/>
		</fieldset>
		<br style="clear:left;"/>
	</form>
	<?
	
	$page->footer();
	exit;
	
}

$q = "SELECT * FROM users LEFT JOIN users_details USING (usrid) WHERE usrid='".mysqli_real_escape_string($GLOBALS['db']['link'], $_GET['userid'])."' LIMIT 1";
if(!$userrow = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) die("Couldn't get data for userid # ".$_GET['userid']);

do if($in = $_POST['in']){
	
	if($in['delete']){
		if($usrrank < 8 || $userrow['rank'] > $usrrank){
			$errors[] = "Access level is too low for that action [$usrrank, ".$userrow['rank']."]";
			break;
		}
		
		$q = "SELECT * FROM users WHERE usrid='".mysqli_real_escape_string($GLOBALS['db']['link'], $_GET['userid'])."' LIMIT 1";
		$userrow = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
		
		$q  = "INSERT INTO users_deleted (";
		$q2 = "VALUES (";
		foreach($userrow as $field => $val){
			$q.=  "`$field`,";
			$q2.= "'".mysqli_real_escape_string($GLOBALS['db']['link'], $val)."',";
		}
		$q = substr($q, 0, -1) . ") " . substr($q2, 0, -1) . ");";
		if(!mysqli_query($GLOBALS['db']['link'], $q)){
			$errors[] = "Couldn't create back-up user row because of a database error; $q; ".mysqli_error($GLOBALS['db']['link']);
			break;
		}
		
		$q = "DELETE FROM users WHERE usrid='".mysqli_real_escape_string($GLOBALS['db']['link'], $_GET['userid'])."' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't dete user from the database";
		else{
			$results[] = "This user has been deleted.";
			break;
		}
		
	}
	
	$pw = trim($in['password']);
	if($in['rank'] > $usrrank) unset($in['rank']);
	$q = "UPDATE users SET ".($pw ? "`password` = password('".mysqli_real_escape_string($GLOBALS['db']['link'], $pw)."'), " : "").($in['rank'] ? " `rank` = '".$in['rank']."'" : '')." WHERE usrid = '".$_GET['userid']."' LIMIT 1";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't update USERS table (password, rank)";
	
	$handle = trim($in['handle']);
	$q = "UPDATE users_details SET handle='".mysqli_real_escape_string($GLOBALS['db']['link'], $handle)."', handle_lock='".$in['handle_lock']."' WHERE usrid = '".$_GET['userid']."' LIMIT 1";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't update USERS_DETAILS table (handle)";
	
	$q = "DELETE FROM users_prefs WHERE usrid='".$_GET['userid']."'";
	mysqli_query($GLOBALS['db']['link'], $q);
	
	$query = "SHOW COLUMNS FROM users_prefs";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)){
		if($row['Field'] == "usrid") continue;
		$prefs[$row['Field']] = $in['prefs'][$row['Field']];
	}
	
	$q = "INSERT INTO `users_prefs` (`usrid`,`".implode("`,`", array_keys($prefs))."`) VALUES ('".$_GET['userid']."'";
	foreach($prefs as $p){
		$q.= ",'".mysqli_real_escape_string($GLOBALS['db']['link'], $p)."'";
	}
	$q.= ");";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't update user preferences";
	
	if(!$errors) $results[] = "User updated";

} while(false);

if(!$in) $in = $userrow;

$page->title = "Managing ".$userrow['username'];
$page->freestyle.= '#usermgtform dt { float:left; margin:0 0 15px; padding:0; line-height:24px; } #usermgtform dd { margin:0 0 15px 95px; padding:0; } #usermgtform dd + dd { margin-top:-10px; }';
$page->header();

?>
<div style="color:#CCC; margin-bottom:20px;">
	<a href="user_mgt.php" class="arrow-left">User Management</a> | <b style="color:black"><?=$userrow['username']?></b> | <a href="/~<?=$userrow['username']?>" class="arrow-right"><?=$userrow['username']?>'s Profile</a>
</div>

<?
if($in['rank'] > $usrrank){
	echo "You don't have access to edit this user's profile.";
	$page->footer();
	exit;
}
?>

<form action="user_mgt.php?userid=<?=$_GET['userid']?>" method="post" id="usermgtform">
	<dl id="">
		<dt>New Password</dt>
		<dd><input type="text" name="in[password]" value=""/></dd>
		
		<dt>Rank</dt>
		<dd>
			<select name="in[rank]">
				<?
				$query = "SELECT * FROM `users_ranks` WHERE `rank` >= 1 ORDER BY `rank`";
				$res   = mysqli_query($GLOBALS['db']['link'], $query);
				while($row = mysqli_fetch_assoc($res)){
					echo '<option value="'.$row['rank'].'"'.($row['rank'] == $in['rank'] ? ' selected="selected"' : '').($row['rank'] > $usrrank ? ' disabled="disabled"' : '').'>'.$row['description'].'</option>';
				}
				?>
			</select>
		</dd>
		
		<dt>Handle</dt>
		<dd>
			<input type="text" name="in[handle]" value="<?=htmlSC($in['handle'])?>"/> 
			<label><input type="checkbox" name="in[handle_lock]" value="1"<?=($in['handle_lock'] ? ' checked="checked"' : '')?>/> Lock handle</label>
		</dd>
		<?
		$q = "SELECT * FROM users_prefs WHERE usrid = '".mysqli_real_escape_string($GLOBALS['db']['link'], $_GET['userid'])."' LIMIT 1";
		if(!$prefs = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
			$query = "SHOW COLUMNS FROM users_prefs";
			$res = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)){
				if($row['Field'] == "usrid") continue;
				$prefs[$row['Field']] = $row['Default'];
			}
		}
		?>
		<dt>Preferences</dt>
		<?
		foreach($prefs as $key => $val){
			if($key == "usrid") continue;
			?>
			<dd><label><input type="checkbox" name="in[prefs][<?=$key?>]" value="1"<?=($val ? ' checked="checked"' : '')?>/> <?=$key?></dd>
			<?
		}
		
		if($usrrank >= 8){
		?>
		<dt>Delete</dt>
		<dd><label><input type="checkbox" name="in[delete]" value="1" onclick="if(!confirm('Are you sure?')) return false"/> Delete this user account</label></dd>
		<?
		}
		
		?>
	</dl>
	<div class="hr"></div>
	<dl>
		<dd>
			<input type="submit" value="Submit Changes"/>
		</dd>
	</dl>
</form>
<?

$page->footer();
?>