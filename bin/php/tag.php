<?
if($_POST['_action'] == "load_freetags") {
	require_once("page.php");
	$x = array();
	$query = "SELECT DISTINCT(tag) FROM posts_tags WHERE tag NOT LIKE 'gid:%' AND tag NOT LIKE 'pid:%' ORDER BY tag";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$x[] = $row['tag'];
	}
	die( implode("``", $x) );
} elseif($_POST['_action'] == "load_sellist") {
	require_once("page.php");
	$what = $_POST['_list'];
	if($what == "games") {
		echo '<option value="">Select a game...</option>';
		$query = "SELECT gid, title FROM games ORDER BY title";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)) {
			$q = "SELECT release_date FROM games_publications WHERE gid='$row[gid]' and `primary`='1' LIMIT 1";
			$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
			if(strlen($row['title']) > 50) {
				$row['title'] = substr($row['title'], 0, 39) . "&hellip;" . substr($row['title'], -9);
			}
			echo '<option value="gid:'.$row['gid'].'">'.$row['title'].' ('.substr($dat->release_date, 0, 4).')</option>';
		}
	} elseif($what == "people") {
		echo '<option value="">Select a person...</option>';
		$query = "SELECT pid, name, title FROM people ORDER BY name";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)) {
			if(strlen($row['title']) > 35) $row['title'] = substr($row['title'], 0, 33) . "&hellip;";
			echo '<option value="pid:'.$row['pid'].'">'.$row['name'].($row['title'] ? ' ('.$row['title'].')' : '').'</option>';
		}
	} elseif($what == "albums") {
		echo '<option value="">Select an album...</option>';
		$query = "SELECT albumid, title, subtitle, cid FROM albums ORDER BY title, subtitle";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)) {
			if(strlen($row['title']) > 25) $row['title'] = substr($row['title'], 0, 20) . "&hellip;" . substr($row['title'], -5);
			if(strlen($row['subtitle']) > 25) $row['subtitle'] = substr($row['subtitle'], 0, 14) . "&hellip;" . substr($row['subtitle'], -9);
			echo '<option value="aid:'.$row['albumid'].'">'.$row['title'].($row['subtitle'] ? ' '.$row['subtitle'] : '').' ('.$row['cid'].')</option>';
		}
	}
	exit;
} elseif($_POST['_action'] == "add_post_tag") {
	require_once("page.php");
	if(!$nid = $_POST['_nid']) die("Error: No post id given");
	if(!$tag = $_POST['_tag']) die("Error: No tag given");
	//check if tag already exists
	$q = "SELECT * FROM posts_tags WHERE nid='".mysqli_real_escape_string($GLOBALS['db']['link'], $nid)."' AND tag='".mysqli_real_escape_string($GLOBALS['db']['link'], $tag)."' LIMIT 1";
	if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) die("This item is already tagged with '$tag'.");
	
	//if media, only  game/person allowed
	/*$q = "SELECT type FROM posts WHERE nid='".mysqli_real_escape_string($GLOBALS['db']['link'], $nid)."' LIMIT 1";
	if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Couldn't find data for nid #$nid");
	if($dat->type == "image" || $dat->type == "gallery") {
		$pre = substr($tag, 0, 4);
		if($pre == "gid:" || $pre == "pid:") {
			$q = "SELECT * FROM posts_tags WHERE nid='".mysqli_real_escape_string($GLOBALS['db']['link'], $nid)."' AND tag LIKE '".$pre."%' LIMIT 1";
			if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) die("Media posts can only have one subject. If your media is about more than one game or person, you'll need to split it into multiple posts.");
		}
	}*/
		
	$q = "INSERT INTO posts_tags (nid, tag, usrid, datetime) VALUES ('".mysqli_real_escape_string($GLOBALS['db']['link'], $nid)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $tag)."', '$usrid', '".date("Y-m-d H:i:s")."');";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Error adding tag to database");
	exit;
} elseif($_POST['_action'] == "remove_tag") {
	require_once("page.php");
	$tid = $_POST['_tag_id'];
	$tbl = $_POST['_table'];
	if($tbl == "posts_tags") {
		//check and see if the tag is removable first
		$q = "SELECT usrid, rank FROM posts_tags LEFT JOIN users USING (usrid) WHERE posts_tags.id='$tid' LIMIT 1";
		$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
		if($usrid != $dat->usrid) {
			if($_SESSION['user_rank'] < $dat->rank) die("You can't remove that tag since the person who tagged it is ranked higher than you.");
		}
		$q = "DELETE FROM posts_tags WHERE id='$tid' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Error removing tag from database; ".mysqli_error($GLOBALS['db']['link']));
	}
} else {
	?>
	<div id="tagspace">
		<div class="container1"></div>
		<div class="container2">
			<div class="container">
				<div class="close">
					<a href="javascript:void(0);" class="x">x</a> <a href="#close" class="preventdefault">Close</a>
				</div>
				<div class="nav">
					<div>Tag a&nbsp;&nbsp;</div> 
					<a href="javascript:void(0);"><span>Game</span></a> 
					<a href="javascript:void(0);"><span>Person</span></a> 
					<a href="javascript:void(0);" onclick="loadTagSelectList('albums');"><span>Album</span></a> 
					<a href="javascript:void(0);"><span>Other</span></a>
				</div>
				<div id="tag-loading"><img src="/bin/img/loading-thickbox.gif" alt="loading"/></div>
				<div class="space2">
					Select a category above to tag something.
					<?=$tagmsg_i?>
				</div>
				<div class="space2 tag-space-Game" style="display:none">
					<div style="margin-right:6px;">
						<input type="text" name="" value="Start typing here to find a game..." id="tag-q-game" class="inptag" onfocus="$(this).val('');"/>
					</div>
					<?=$tagmsg_g?>
					<p>Type the game title in the text query field above and select a game from the drop-down list to tag it.</p>
					<p>If you can't find your game, <a href="/games/add.php">add it to the games database</a> and return here afterward to tag it.</p>
					<p>If you're having problems with this form, <a href="#" onclick="loadTagSelectList('games');" class="arrow-right">switch to a select list</a></p>
				</div>
				<div class="space2 tag-space-games-select" style="display:none">
					<select name="" id="tag-games-select" class="inptag" onchange="submitNewTag($(this).val(), '['+$(this).val()+']', '<?=$tag_vars_cont?>', '<?=$tag_vars_id?>');" style="padding:2px;"></select>
				</div>
				<div class="space2 tag-space-Person" style="display:none">
					<div style="margin-right:6px;">
						<input type="text" name="" value="Start typing here to find a name..." id="tag-q-person" class="inptag" onfocus="$(this).val('');"/>
					</div>
					<?=$tagmsg_p?>
					<p>Type the person's name in the text query field above and select from the drop-down list to tag them.</p>
					<!--<p>If you can't find your game, <a href="/games/add.php">add it to the games database</a> and return here afterward to tag it.</p>-->
					<p>If you're having problems with this form, <a href="#" onclick="loadTagSelectList('people');" class="arrow-right">switch to a select list</a></p>
				</div>
				<div class="space2 tag-space-people-select" style="display:none">
					<select name="" id="tag-people-select" class="inptag" onchange="submitNewTag($(this).val(), '['+$(this).val()+']', '<?=$tag_vars_cont?>', '<?=$tag_vars_id?>');" style="padding:2px;"></select>
				</div>
				<div class="space2 tag-space-Album tag-space-albums-select" style="display:none">
					<select name="" id="tag-albums-select" class="inptag" onchange="submitNewTag($(this).val(), '['+$(this).val()+']', '<?=$tag_vars_cont?>', '<?=$tag_vars_id?>');" style="padding:2px;"></select>
				</div>
				<div class="space2 tag-space-Other" style="display:none">
					<div style="margin:0 6px 3px 0;">
						<input type="text" name="" value="" id="tag-free" class="inptag"/>
					</div>
					<input type="button" value="Tag It" onclick="submitNewTag($('#tag-free').val(), $('#tag-free').val(), '<?=$tag_vars_cont?>', '<?=$tag_vars_id?>');"/>
					<?=$tagmsg_free?>
					<p>Tag something else by inputting a tag description in the text box above and submitting the form. Please input one tag at a time.</p>
				</div>
			</div>
		</div>
	</div>
	<?
}
?>