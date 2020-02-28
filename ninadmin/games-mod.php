<?
// Jump:
// MAIN DETAILS //
// CONTROLS //
// PUBLICATIONS //
// LOGO //
// TRAILERS //
// LINKS //
// FILES //
// TRIVIA //
// QUOTES //
// GAME GUIDE //
// PREVIEW //

require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.upload.php");
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/contribute.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/admin.php");

$id = $_GET['id'];
if(!$what = $_GET['what']) $what = "main";
$do = $_GET['do'];
$action = $_POST['action'];
$in = $_POST['in'];

// get general gamedata from db
$q = "SELECT * FROM games WHERE games.gid='$id' LIMIT 1";
$res = mysqli_query($GLOBALS['db']['link'], $q);
while($row = mysqli_fetch_assoc($res)) {
	$gdat = $row;
}

if($_GET['justadded']) {
	$results[] = "Success! $gdat[title] added to the database. Edit or input more details below.";
}

$platforms = getPlatforms();
$dir = $platforms[$gdat['platform_id']]['platform_shorthand'];

$page = new page;
$page->title = "Videogam.in Admin ".($gdat[title] ? " / $gdat[title] / $what" : "");
$page->min_rank = 7;
$page->admin = TRUE;

// DELETE //
if($do == "delete_entry" && $id) {
	
	$tables = array(
		"albums_tags" => "",
		"forums_tags" => "tag='gid:$id'",
		"games_collection" => "",
		"games_commerce" => "",
		"games_controls" => "",
		"games_links" => "",
		"games_publications" => "",
		"games_quotes" => "",
		"games_series" => "",
		"games_trailers" => "",
		"games_trivia" => "",
		"groups_tags" => "tag='gid:$id'",
		"media_tags" => "tag='gid:$id'",
		"my_games" => "",
		"people_work" => "",
		"posts_tags" => "tag='gid:$id'",
		"games" => "");
	while(list($table, $x) = each($tables)) {
		$q = "DELETE FROM `$table` WHERE ".($x ? $x : "gid='$id'");
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't delete from `$table` table; ".mysql_error();
		else $results[] = "Delete from `$table`; $q";
	}
	
	unset($id);
	
}

if(!$id) {
	
	///////////
	// INDEX //
	///////////
	
	if(!$orderby = $_GET['orderby']) $orderby = "games.title";
	
	$page->title.= "/ Edit a Game";
	$page->header();
	
	?><h2>Edit Game</h2>
	
	Select a game (order by <?=($orderby == "games.title" ? '<b>title</b>' : '<a href="?orderby=games.title">title</a>')?> or <?=($orderby == "games_platforms.platform" ? '<b>platform</b>' : '<a href="?orderby=games_platforms.platform">platform</a>')?>)<br/>
	
	<form action="games-mod.php" method="get">
		<select name="id" size="20" style="margin-top:5px">
			<?
			$query = "SELECT games.gid, games.title, platform, platform_shorthand, unpublished FROM games 
				LEFT JOIN games_publications ON (games_publications.gid=games.gid AND games_publications.`primary`='1') 
				LEFT JOIN games_platforms ON (games_publications.platform_id=games_platforms.platform_id) ORDER BY $orderby";
			if($orderby == "games_platforms.platform") $query.= ", games.title";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)) {
				if($orderby == "games_platforms.platform") {
					$i = 0;
					if($curr != $row['platform']) {
						$curr = $row['platform'];
						$i++;
						if($i != 1) echo '</optgroup>';
						echo '<optgroup label="'.$row['platform'].'">';
					}
				}
				if(strlen($row['title']) > 60) $row['title'] = substr($row['title'], 0, 59).'&hellip;';
				echo '<option value="'.$row['gid'].'" style="'.($row['unpublished'] ? 'text-decoration:line-through;' : '').(!$row['platform'] ? 'font-weight:bold; color:red;' : '').'">'.$row['title'].($orderby == "games.title" ? ' ('.$row[platform_shorthand].')' : '').(!$row['platform'] ? ' *NO PUBLICATIONS*' : '').'</option>'."\n";
			}
			?>
		</select>
		<div style="margin-top:5px"><input type="submit" value="Submit"></div>
	</form>
	<?
	
	$page->footer();
	exit;
	
}




//gamedata needed for everything else
if(!$gdat) die("No gamedata given");


////////////
// Header //
////////////

$here[$what] = "here";

$mod_header = <<<EOF
	<table border="0" cellpadding="0" cellspacing="0" width="100%" class="heading-tabs">
		<tr>
			<th colspan="11"><h2><a href="/games/$gdat[gid]/">$gdat[title]</a></h2></th>
		</tr>
		<tr>
			<td class="first">&nbsp;</td>
			<td class="$here[main]"><a href="/ninadmin/games-mod.php?id=$id&what=main">General</a></td>
			<td class="$here[files]"><a href="/ninadmin/games-mod.php?id=$id&what=files">Files</a></td>
			<td class="$here[publications]"><a href="/ninadmin/games-mod.php?id=$id&what=publications">Publications</a></td>
			<td class="$here[preview]"><a href="/ninadmin/games-mod.php?id=$id&what=preview">Preview</a></td>
			<td class="$here[guide]"><a href="/ninadmin/games-mod.php?id=$id&what=guide">Game Guide</a></td>
			<td class="last" width="100%">&nbsp;</td>
		</tr>
	</table>
EOF;




if($what == "main") {
	
	//////////////////
	// MAIN DETAILS //
	//////////////////
	
	if(!$gdat) die("No gamedata given");
	
	//check files dir
	if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/games/files/'.$id.'/')) {
		if(mkdir($_SERVER['DOCUMENT_ROOT'].'/games/files/'.$id.'/', 0777)) {
			$results[] = "Files dir didn't exist, though successfully created one.";
		} else {
			$errors[] = "Couldn't create files directory which is neccessary for uploads. Manually create /games/files/".$dir."/".$id."/";
		}
	}
	if(!is_writable($_SERVER['DOCUMENT_ROOT'].'/games/files/'.$id.'/')) {
		if(!chmod($_SERVER['DOCUMENT_ROOT'].'/games/files/'.$id.'/', 0777)) {
			$errors[] = "Couldn't make this game's files dir writable. Manually CHMOD /games/files/".$dir."/".$id."/ to 777.";
		}
	}
	
	if($_POST['submit']) {
		
		if(!$in['title']) $errors[] = "No title input";
		$in['title'] = htmlent($in['title']);
		
		if(!$in['title_url']) {
			$in['title_url'] = $gdat['title_url'];
			if(!$in['title_url']) $errors[] = "No title URL given, please input one.";
		} else {
			if($in['title_url'] != $gdat['title_url'] && $gdat['title_url'] != "") {
				//title url has changed
				$in['title_url'] = makeUrlStr($in['title_url']);
				//exists?
				$q = "SELECT * FROM games WHERE title_url='".$in['title_url']."' AND gid != '$id' LIMIT 1";
				if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) {
					$warnings[] = "The title URL you input already exists; Changed to previously used one";
					$in['title_url'] = $gdat['title_url'];
				} else {
					$query = "INSERT INTO games_old_title_urls (gid, old_title_url, new_title_url) VALUES ('$id', '$gdat[title_url]', '$in[title_url]')";
					if(!mysqli_query($GLOBALS['db']['link'], $query)) {
						$errors[] = "Title URL not changed; Couldn't update db to make title URL mirror: ".mysql_error();
						$in['title_url'] = $gdat['title_url'];
					}
				}
			}
		}
		
		$datetime = date("Y-m-d H:i:s");
		
		if(!$errors) {
			$query = "UPDATE games SET
				`title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['title'])."',
				`title_url` = '$in[title_url]',
				`classic` = '".$in['classic']."',
				`vapid` = '".$in['vapid']."',
				`featured` = '".$in['featured']."',
				`online` = '$in[online]',
				`unpublished` = '$in[unpublished]',
				`modified` = '$datetime'
				WHERE `gid`='$id' LIMIT 1";
			if(!mysqli_query($GLOBALS['db']['link'], $query)) {
				$errors[] = "Couldn't update db: ".mysql_error();
			} else $results[] = "Database succesfully updated";
			
		}
	} else {
		
		//no postdata ($in)
		$in = $gdat;
		
	}
	
	$page->header();
	echo $mod_header;
	?>
	
	<div style="margin-bottom:1em; font-size:11px; color:#666; text-align:right;">#<?=$id?> / Created on <?=$gdat[created]?> by <?=outputUser($gdat[creator], FALSE)?> / Data last updated on <?=$gdat[modified]?></div>
	
	<form action="games-mod.php?id=<?=$id?>&what=main" method="post">
		<table border="0" cellpadding="0" cellspacing="0" class="styled-form">
			<tr>
				<th>Title</th>
				<td><input type="text" name="in[title]" value="<?=str_replace('"', '&quot;', $in['title'])?>" size="50"/></td>
			</tr>
			<tr>
				<th>Title URL</th>
				<td>
					<div id="change-turl-warn" style="display:none; margin-bottom:5px;"><b style="color:red">Warning!</b> Don't wantonly change this! An established URL is necessary for good search results from search engines; Changing a page's URL will decrease its rank if it is already well established.</div>
					http://videogam.in/games/~<input id="title-url" type="text" name="in[title_url]" value="<?=$in[title_url]?>" size="40" disabled="disabled"/> 
					<input type="button" value="Change" onclick="document.getElementById('title-url').disabled=false; document.getElementById('change-turl-warn').style.display='block'; this.style.display='none'"/>
				</td>
			</tr>
			<tr>
				<th>Special Status</th>
				<td>
					<label><input type="checkbox" name="in[classic]" value="1"<?=($in['classic'] ? ' checked="checked"' : '')?>/> Classic</label> &nbsp; 
					<label><input type="checkbox" name="in[vapid]" value="1"<?=($in['vapid'] ? ' checked="checked"' : '')?>/> Vapid</label> &nbsp; 
					<label><input type="checkbox" name="in[featured]" value="1"<?=($in['featured'] ? ' checked="checked"' : '')?>/> Featured</label> &nbsp; 
				</td>
			</tr>
			<tr
			<tr>
				<th>Online Play?</th>
				<td><label><input type="checkbox" name="in[online]" value="1" style="vertical-align:middle"<?=($in[online] ? ' checked="checked"' : '')?>/> This game can be played against others online</label></td>
			</tr>
			<tr>
				<th>Unpublish</th>
				<td><label><input type="checkbox" name="in[unpublished]" value="1" style="vertcal-align:middle"<?=($in[unpublished] ? ' checked="checked"' : '')?>/> Unpublish this gamepage, removing it from all indexes (it can still be found via search though)</label></td>
			</tr>
			<?/*
			if($usrrank == 9) {
				?>
				<tr>
					<th>Contributors</th>
					<td>
						<input type="hidden" name="in[update_contributors]" value="1"/>
						Add a contributor: 
						<select onchange="document.getElementById('input-contr').value=document.getElementById('input-contr').value+','+this.options[this.selectedIndex].value;">
							<option value="">Select a user...</option>
							<?
							$query = "SELECT usrid, username FROM users ORDER BY username";
							$res   = mysqli_query($GLOBALS['db']['link'], $query);
							while($row = mysqli_fetch_assoc($res)) {
								echo '<option value="usrid:'.$row['usrid'].'">'.$row['username'].'</option>'."\n";
							}
							?>
						</select>
							
						<p><textarea name="in[contributors]" rows="2" cols="60" id="input-contr"><?=$in['contributors']?></textarea></p>
					</td>
				</tr>
				<?
			}
			*/?>
			<tr>
				<th>Delete</th>
				<td>
					<a href="#x" id="delete-button" onclick="toggle('confirm-delete', 'delete-button')" style="color:#D14747"><b>Delete</b> this entry and all associated data</a>
					<div id="confirm-delete" style="display:none">
						Permanently delete everything associated with this game? 
						<input type="button" value="Yes" onclick="if(confirm('Are you absolutely, 100% sure???')==true) window.location='?do=delete_entry&id=<?=$id?>';"/> 
						<input type="button" value="No, I changed my mind!" onclick="toggle('delete-button','confirm-delete')"/>
					</div>
				</td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td><input type="submit" name="submit" value="Submit Changes" style="font-size:21px"/></td>
			</tr>
		</table>
	</form>
	
	
	<?
	
	$page->footer();
	
} //main details

if($what == "controls") {
	
	//////////////
	// CONTROLS //
	//////////////
	
	if($_POST['submit_controls']) {
		if($_POST['insert']) {
			$q = "INSERT INTO games_controls (gid, `force`, no_reviews, no_grading, no_forecasting, no_collecting, no_playing, no_playing_online) VALUES 
				('$id', '$in[force]', '$in[no_reviews]', '$in[no_grading]', '$in[no_forecasting]', '$in[no_collecting]', '$in[no_playing]', '$in[no_playing_online]')";
		} else {
			$q = "UPDATE games_controls SET 
				`force` = '$in[force]',
				`no_reviews` = '$in[no_reviews]',
				`no_grading` = '$in[no_grading]',
				`no_forecasting` = '$in[no_forecasting]',
				`no_collecting` = '$in[no_collecting]',
				`no_playing` = '$in[no_playing]',
				`no_playing_online` = '$in[no_playing_online]' 
				WHERE gid='$id' LIMIT 1";
		}
		if(!mysqli_query($GLOBALS['db']['link'], $q)) {
			$errors[] = "Couldn't update db";
		} else {
			$results[] = "Controls updated";
		}
	}
	
	$page->header();
	echo $mod_header;
	
	$q = "SELECT * FROM games_controls WHERE gid='$id' LIMIT 1";
	if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
		$insert = 1;
	}
	
	?>
	<form action="games-mod.php?id=<?=$id?>&what=controls" method="post">
		<input type="hidden" name="insert" value="<?=$insert?>"/>
		<table border="0" cellspacing="0" width="100%" class="styled-form">
			<tr>
				<th>Rating System</th>
				<td><small>Force a specific user rating system. By default, the system is <i>Forecasting</i> until the North American release date has passed, when it will then be <i>Grading</i>.</small>
					<p><select name="in[force]">
						<option value="">default</option>
						<option value="forecasting"<?=($dat->force == "forecasting" ? ' selected="selected"' : '')?>>Forecasting</option>
						<option value="grading"<?=($dat->force == "grading" ? ' selected="selected"' : '')?>>Grading</option>
						<option value="blank"<?=($dat->force == "blank" ? ' selected="selected"' : '')?>>Show nothing</option>
					</select></p>
				</td>
			</tr>
			<tr>
				<th>Autocracy</th>
				<td>
					<label><input type="checkbox" name="in[no_forecasting]" value="1"<?=($dat->no_forecasting ? ' checked="checked"' : '')?>/> No forecasting</label>
					<p><label><input type="checkbox" name="in[no_grading]" value="1"<?=($dat->no_grading ? ' checked="checked"' : '')?>/> No grading</label></p>
					<p><label><input type="checkbox" name="in[no_reviews]" value="1"<?=($dat->no_reviews ? ' checked="checked"' : '')?>/> No reader reviews</label></p>
					<p><label><input type="checkbox" name="in[no_playing]" value="1"<?=($dat->no_playing ? ' checked="checked"' : '')?>/> No playing</label></p>
					<p><label><input type="checkbox" name="in[no_playing_online]" value="1"<?=($dat->no_playing_online ? ' checked="checked"' : '')?>/> No playing online</label></p>
				</td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td><input type="submit" name="submit_controls" value="Set Controls"/></td>
			</tr>
		</table>
	</form>
	<?
	
	$page->footer();
	
}

if($what == "publications") {
	
	//////////////////
	// PUBLICATIONS //
	//////////////////
	
	/*if($_POST['submit_add']) {
		
		// ADD PUB //
		
		if(!$in['platform_id'] && $in['platform_id'] != '0') die("No platform selected");
		if(!$in['year']) die("No release year input");
		$in['title'] = htmlent($in['title']);
		if(!$in['title']) $in['title'] = $gdat['title'];
		if(!$in['region']) {
			if($in['region_other']) $in['region'] = $in['region_other'];
			else die("No region selected");
		}
		
		//get # of current pubs and decide if this should be the primary pub
		$q = "SELECT * FROM games_publications WHERE gid='$id'";
		if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) {
			$primary = '1';
		} else {
			$primary = '0';
			$warnings[] = "Make sure you set the correct primary publication";
		}
		
		//get next id
		$query = mysqli_query($GLOBALS['db']['link'], "SHOW TABLE STATUS LIKE 'games_publications'");
		$row = mysqli_fetch_assoc($query);
		if(!$next_id = $row['Auto_increment']) die("Couldn't get next database ID; ".mysql_error());
		
		$query = "INSERT INTO games_publications (gid,platform_id,title,region,release_date,`primary`,placeholder_img) VALUES 
			('$id', '".$in['platform_id']."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['title'])."', '".$in['region']."', '".$in['year']."-".$in['month']."-".$in['day']."', '$primary', '".$in['placeholder_img']."')";
		if(!mysqli_query($GLOBALS['db']['link'], $query)) {
			$errors[] = "Couldn't add publication to db: ".mysql_error();
		} else {
			$results[] = "Publication successfully added";
			
			if($_FILES['file']['name']) {
				//upload
				$handle = new Upload($_FILES['file']);
		    if ($handle->uploaded) {
		    	
		    	$handle->image_convert          = 'jpg';
					$handle->image_resize           = true;
					$handle->image_ratio_no_zoom_in = true;
					$handle->image_x                = 500;
					$handle->image_y                = 700;
		    	$handle->file_overwrite         = TRUE;
		    	$handle->file_safe_name         = FALSE;
		    	$handle->file_new_name_body     = $id.'-box-'.$next_id;
		    	
		    	$handle->Process($_SERVER['DOCUMENT_ROOT']."/games/files/$id/");
					if ($handle->processed) {
						$results[] = 'Box art uploaded: <a href="/games/files/'.$id.'/'.$handle->file_dst_name.'" target="_blank">'.$handle->file_dst_name.'</a>';
						
						//small img
						$handle->file_overwrite = TRUE;
						$handle->file_safe_name = FALSE;
						$handle->file_new_name_body = $id.'-box-'.$next_id.'-sm';
						$handle->image_convert         = 'png';
						$handle->image_resize          = true;
						$handle->image_ratio_y         = true;
						$handle->image_x               = 140;
						$handle->Process($_SERVER['DOCUMENT_ROOT']."/games/files/$id/");
						if ($handle->processed) $results[] = 'Small image created: <a href="/games/files/'.$id.'/'.$handle->file_dst_name.'" target=_blank">'.$handle->file_dst_name.'</a>';
						else $errors[] = 'Small image couldn\'t be created: ' . $handle->error;
									
						//thumbnail
						$handle->file_overwrite = TRUE;
						$handle->file_safe_name = FALSE;
						$handle->file_new_name_body = $id.'-box-'.$next_id.'-tn';
						$handle->image_convert         = 'png';
						$handle->image_resize          = true;
						$handle->image_ratio_y         = true;
						$handle->image_x               = 80;
						$handle->Process($_SERVER['DOCUMENT_ROOT']."/games/files/$id/");
						if ($handle->processed) {
							$results[] = 'Thumbnail image created: <a href="/games/files/'.$id.'/'.$handle->file_dst_name.'" target=_blank">'.$handle->file_dst_name.'</a>';
							addUserContribution(3, '<a href="/games/link.php?id='.$id.'">'.htmlent($gdat['title']).'</a> box art', '<img src="/games/files/'.$id.'/'.$handle->file_dst_name.'"/>', TRUE, '', "games_publications:".$next_id, 'gid:'.$id);
						} else $errors[] = 'Thumbnail image couldn\'t be created: ' . $handle->error;
		      
		      } else {
		        $errors[] = 'file not uploaded to the wanted location: ' . $handle->error;
		      }
		    } else {
		      $errors[] = 'file not uploaded on the server: ' . $handle->error;
		    }
		  }
		}
	}*/
	
	if($pubid = $_GET['edit']) {
		
		// EDIT //
		
		if($_POST['submit_edit']) {
			
			if(!$in) die("No data given");
			
			if(!$in['title']) $in['title'] = $gdat->title;
			$in['title'] = htmlent($in['title']);
			
			$query = "UPDATE games_publications SET 
				title='".mysqli_real_escape_string($GLOBALS['db']['link'], $in['title'])."',
				region='".$in['region']."',
				release_date='".$in['release_date']."',
				platform_id='".$in['platform_id']."',
				placeholder_img='".$in['placeholder_img']."'
				WHERE id='$pubid' LIMIT 1";
			if(!mysqli_query($GLOBALS['db']['link'], $query)) {
				$errors[] = "Couldn't update publication: ".mysql_error();
			} else {
				$results[] = "Publication successfully edited";
			}
			
			if($in['delete_box'] || $_FILES['file']['name']) {
				
				//delete old box
				@unlink($_SERVER['DOCUMENT_ROOT']."/games/files/$id/$id-box-$pubid.jpg");
				@unlink($_SERVER['DOCUMENT_ROOT']."/games/files/$id/$id-box-$pubid-sm.png");
				@unlink($_SERVER['DOCUMENT_ROOT']."/games/files/$id/$id-box-$pubid-tn.png");
				
			}
			
			if($_FILES['file']['name']) {
				
				//upload
				$handle = new Upload($_FILES['file']);
		    if ($handle->uploaded) {
		    	
		    	$handle->image_convert          = 'jpg';
					$handle->image_resize           = true;
					$handle->image_ratio_no_zoom_in = true;
					$handle->image_x                = 500;
					$handle->image_y                = 700;
		    	$handle->file_overwrite         = TRUE;
		    	$handle->file_safe_name         = FALSE;
		    	$handle->file_new_name_body     = $id.'-box-'.$pubid;
		    	
		    	$handle->Process($_SERVER['DOCUMENT_ROOT']."/games/files/$id/");
					if ($handle->processed) {
						$results[] = 'Box art uploaded: <a href="/games/files/'.$id.'/'.$handle->file_dst_name.'" target="_blank">'.$handle->file_dst_name.'</a>';
						
						//small img
						$handle->file_overwrite = TRUE;
						$handle->file_safe_name = FALSE;
						$handle->file_new_name_body = $id.'-box-'.$pubid.'-sm';
						$handle->image_convert         = 'png';
						$handle->image_resize          = true;
						$handle->image_ratio_y         = true;
						$handle->image_x               = 140;
						$handle->Process($_SERVER['DOCUMENT_ROOT']."/games/files/$id/");
						if ($handle->processed) $results[] = 'Small image created: <a href="/games/files/'.$id.'/'.$handle->file_dst_name.'" target=_blank">'.$handle->file_dst_name.'</a>';
						else $errors[] = 'Small image couldn\'t be created: ' . $handle->error;
									
						//thumbnail
						$handle->file_overwrite = TRUE;
						$handle->file_safe_name = FALSE;
						$handle->file_new_name_body = $id.'-box-'.$pubid.'-tn';
						$handle->image_convert         = 'png';
						$handle->image_resize          = true;
						$handle->image_ratio_y         = true;
						$handle->image_x               = 80;
						$handle->Process($_SERVER['DOCUMENT_ROOT']."/games/files/$id/");
						if ($handle->processed) $results[] = 'Thumbnail image created: <a href="/games/files/'.$id.'/'.$handle->file_dst_name.'" target=_blank">'.$handle->file_dst_name.'</a>';
						else $errors[] = 'Thumbnail image couldn\'t be created: ' . $handle->error;
		        } else {
		            $errors[] = 'file not uploaded to the wanted location: ' . $handle->error;
		        }
		    } else {
		        // if we're here, the upload file failed for some reasons
		        // i.e. the server didn't receive the file
		        $errors[] = 'file not uploaded on the server: ' . $handle->error;
		    }
		  }
			
		} else {
			
			$q = sprintf("SELECT * FROM games_publications WHERE id='%s' LIMIT 1", mysqli_real_escape_string($GLOBALS['db']['link'], $_GET['edit']));
			if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
				die("Couldn't get data for publication id#".$_GET['edit'].": ".mysql_error());
			}
			
			$sel[$dat->region] = ' selected="selected"';
			
			$page->header();
			
			?>
			<h2>Edit Publication</h2>
			
			<form action="games-mod.php?id=<?=$id?>&what=publications&edit=<?=$_GET['edit']?>" method="post" enctype="multipart/form-data">
				<input type="hidden" name="in[primary]" value="<?=$dat->primary?>"/>
				<fieldset>
					<legend>Editing Publication ID#<?=$_GET['edit']?></legend>
					<table border="0" cellpadding="0" cellspacing="0" width="100%" class="styled-form">
						<tr>
							<th>Title<br/><small>Required</small></th>
							<td><input type="text" name="in[title]" value="<?=$dat->title?>" size="50"/> <a href="javascript:void(0)" class="tooltip" title="Input the full title of the publication, for example: &quot;Final Fantasy XII (Collector's Edition)&quot; will differentiate it from regular old Final Fantasy XII"><span class="block">?</span></a></td>
						</tr>
						<tr>
							<th>Platform<br/><small>Required</small></th>
							<td>
								<select name="in[platform_id]">
									<?
									$query = "SELECT * FROM games_platforms WHERE platform != 'multiple' ORDER BY platform";
									$res   = mysqli_query($GLOBALS['db']['link'], $query);
									while($row = mysqli_fetch_assoc($res)) {
										echo '<option value="'.$row['platform_id'].'"'.($row['platform_id'] == $dat->platform_id ? ' selected="selected"' : '').'>'.$row['platform']."</option>\n";
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<th>Region<br/><small>Required</small></th>
							<td>
								<select name="in[region]">
									<?
									require($_SERVER['DOCUMENT_ROOT']."/bin/php/country_codes.php");
									while(list($k, $v) = each($cc)) {
										$k = strtolower($k);
										echo '<option value="'.$k.'"'.($k == $dat->region ? ' selected="selected"' : '').'>'.$v.'</option>';
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<th>Release</th>
							<td><input type="text" name="in[release_date]" value="<?=$dat->release_date?>"/> YYYY-MM-DD</td>
						</tr>
						<tr>
							<th>Box Art</th>
							<td>
								<?
								$box = "/games/files/$id/$id-box-".$dat->id.".jpg";
								$tn = "/games/files/$id/$id-box-".$dat->id."-tn.png";
								if(file_exists($_SERVER['DOCUMENT_ROOT'].$tn)) {
									?>
									<a href="<?=$box?>" class="thickbox"><img src="<?=$tn?>"/></a> <label><input type="checkbox" name="in[delete_box]" value="1"/> Delete this box art</label>
									<?
								}
								else echo 'No box uploaded';
								?>
								<p>Upload new box art: <input type="file" name="file"/></p>
								<p><label><input type="checkbox" name="in[placeholder_img]" value="1"<?=($dat->placeholder_img ? ' checked="checked"' : '')?>/> This is a placeholder image and not the real box cover</label></p>
							</td>
						</tr>
						<tr>
							<th>&nbsp;</th>
							<td colspan="2"><input type="submit" name="submit_edit" value="Edit Publication"/></td>
						</tr>
					</table>
				</fieldset>
			</form>
			<?
			
			$page->footer();
			exit;
			
		}
		
	}
	
	if($_GET['delete']) {
		
		// DELETE //
		
		$query = "DELETE FROM games_publications WHERE id=".$_GET['delete']." LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $query)) {
			$errors[] = "Couldn't delete: ".mysql_error();
		} else {
			$results[] = "Publication deleted";
		}
		
		$dir = $_SERVER['DOCUMENT_ROOT']."/games/files/".$id."/";
		$body = $id."-box-".$_GET['delete'];
		if(file_exists($dir.$body.".jpg")) {
			unlink($dir.$body.".jpg");
			unlink($dir.$body."-sm.png");
			unlink($dir.$body."-tn.png");
		}
	
	}
	
	if($pr = $_GET['set_primary']) {
		
		//SET PRIMARY//
		
		$query = "UPDATE games_publications SET `primary`='0' WHERE gid='$id'";
		if(mysqli_query($GLOBALS['db']['link'], $query)) {
			$query2 = "UPDATE games_publications SET `primary`='1' WHERE id='$pr' LIMIT 1";
			if(!mysqli_query($GLOBALS['db']['link'], $query2)) {
				$errors[] = "Couldn't set primary: ".mysql_error();
			} else {
				$results[] = "Primary set";
			}
		}
	}
	
	//get current pubs
	$query = "SELECT * FROM games_publications LEFT JOIN games_platforms USING (platform_id) WHERE gid='$id' ORDER BY `primary` DESC";
	$pub_res = mysqli_query($GLOBALS['db']['link'], $query);
	$pub_num = @mysqli_num_rows($pub_res);
	
	//is there a primary publication?
	if($pub_num) {
		$query = "SELECT * FROM games_publications WHERE gid='$id' AND `primary`='1'";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		if(!mysqli_num_rows($res)) {
			$warnings[] = "No primary publication is set. Please select one below (or add another publication and then set it as primary)";
		}
	}
	
	$page->freestyle.= '.plain TD { vertical-align:top; }';
	$page->javascript.= <<<EOF
<script type="text/javascript">
	function checkPubForm() {
		if(document.getElementById('pub-platform').value=="") {
			alert("Please select a platform");
			return false;
		}
		if(document.getElementById('pub-region').value=="" && document.getElementById('pub-region-other').value=="") {
			alert("Please select a region");
			return false;
		}
	}
</script>
EOF;
	$page->header();
	echo $mod_header;
	
	?>
	<table border="1" cellpadding="5" cellspacing="0" width="100%" class="plain">
		<tr>
			<th colspan="5" style="background-color:#EEE"><big>Current Publications</big></th>
		</tr>
		<?
		if(!$pub_num) {
			echo '<tr><td colspan="5">None yet :(</td></tr>';
		} else {
			while($row = mysqli_fetch_assoc($pub_res)) {
				?>
				<tr>
					<td>
						<i><?=$row['title']?></i><br/>
						<?=$row['platform']?><br/>
						<?=$row['release_date']?> 
						<img src="/bin/img/flags/<?=$row['region']?>.png" alt="<?=$row['region']?>"/>
					</td>
					<td><?
						$box = "/games/files/$id/$id-box-".$row['id'].".jpg";
						$tn = "/games/files/$id/$id-box-".$row['id']."-tn.png";
						if(file_exists($_SERVER['DOCUMENT_ROOT'].$tn)) echo '<a href="'.$box.'" class="thickbox"><img src="'.$tn.'"/></a>';
						else echo 'No box uploaded';
						?>
					</td>
					<td>
						<?=(!$row['primary'] ? '<input type="button" value="Set as Primary" onclick="document.location=\'games-mod.php?id='.$id.'&what=publications&set_primary='.$row['id'].'\';"/>' : '<b>PRIMARY</b>')?><br/>
						<input type="button" value="Edit" onclick="window.location='?id=<?=$id?>&what=publications&edit=<?=$row[id]?>';"/><br/>
						<input type="button" value="Delete" onclick="if(confirm('Really delete?')) window.location='?id=<?=$id?>&what=publications&delete=<?=$row[id]?>';"/>
					</td>
				</tr>
				<?
			}
		}
		?>
	</table>
	
	<?
	$page->footer();
	
	
} //pubs

if($what == "trailers") {
	
	//////////////
	// TRAILERS //
	//////////////
	
	//upload thumb
	if($in['upload_thumb']) {
		
		$handle = new Upload($_FILES['file']);
    if ($handle->uploaded) {
    	$handle->image_resize = true;
			$handle->image_ratio_crop = 'T';
			$handle->image_y = 100;
			$handle->image_x = 100;
    	$handle->Process($_SERVER['DOCUMENT_ROOT']."/games/files/$id/");
      if ($handle->processed) {
      	$q = "UPDATE games_trailers SET thumbnail='".$handle->file_dst_name."' WHERE datetime='".$in['datetime']."' LIMIT 1";
      	if(!mysqli_query($GLOBALS['db']['link'], $q)) {
      		$errors[] = "The image was successfully uploaded but there was an error applying it to the database. Try reuploading it by editing it below in the Current Trailers box.";
      	} else {
      		$results[] = "Success! You have applied a thumbnail to your trailer.";
      	}
      } else {
      	$errors[] = 'file not uploaded to the wanted location: ' . $handle->error;
      }
    } else {
        // if we're here, the upload file failed for some reasons
        // i.e. the server didn't receive the file
        $errors[] = 'file not uploaded on the server: ' . $handle->error;
    }
		
	}
	
	//new trailer
	if($in['submit_add']) {
		if(!$in['title']) $errors[] = "No title input";
		if(!$in['code'] && !$in['url']) $errors[] = "No code or URL input";
		if(!$errors) {
			if($in['type'] == "code") {
				$in['url'] = "";
				//youtube?
				preg_match("@http://w?w?w?\.?youtube\.com/v/([^&\"]+)@", $in['code'], $match);
				if($yt_id = $match[1]) {
					$suggest_img = '<img src="http://i.ytimg.com/vi/'.$yt_id.'/default.jpg"/>';
				}
			}
			if($in['type'] == "url") {
				$in['code'] = "";
				//youtube?
				preg_match("@http://w?w?w?\.?youtube\.com/watch\?v=([^&]+)@", $in['url'], $match);
				if($yt_id = $match[1]) {
					$suggest_img = '<img src="http://i.ytimg.com/vi/'.$yt_id.'/default.jpg"/>';
				}
			}
			$datetime = date("Y-m-d H:i:s");
			
			$q = sprintf("INSERT INTO games_trailers (gid, title, description, code, url, usrid, datetime) VALUES 
				('".$gdat->gid."', '%s', '%s', '%s', '%s', '$usrid', '$datetime')",
				mysqli_real_escape_string($GLOBALS['db']['link'], $in['title']),
				mysqli_real_escape_string($GLOBALS['db']['link'], $in['description']),
				mysqli_real_escape_string($GLOBALS['db']['link'], $in['code']),
				mysqli_real_escape_string($GLOBALS['db']['link'], $in['url']));
			if(!mysqli_query($GLOBALS['db']['link'], $q)) {
				$errors[] = "Couldn't add trailer";
			} else {
				//is it a youtube?
				$results[] = 'The trailer has been successfully added.<br/><br/>
					<fieldset>
						<legend>Your Trailer</legend>
						'.($in['type'] == "code" ? $in['code'] : '<a href="'.$in['url'].'" target="_blank">'.$in['url'].'</a>').'
					</fieldset>
					<br/>
					<fieldset>
						<legend>Upload a Thumbnail</legend>
						'.($suggest_img ? 'Suggested thumbnail:<br/>'.$suggest_img.'<br/>If this image will suffice, save it to your harddrive then upload it below.<br/><br/>' : '').'
						Please add a thumbnail for this trailer:<br/>
						<form action="games-mod.php?id='.$id.'&what=trailers" method="post" enctype="multipart/form-data">
							<input type="hidden" name="in[datetime]" value"'.$datetime.'"/>
							<input type="file" name="file"> 
							<input type="submit" name="in[upload_thumb]" value="Upload"/>
						</form>
						<br/><br/>
						<b>Methods to capture a thumbnail to upload:</b>
						<ol>
							<li>Play your movie and capture the screen with the <i>PrtSc</i> button if it is an option for your compute. This captures your entire screen and copies it to your clipboard. You\'ll next need to edit and crop it in image editing software.</li>
							<li>Use the free image editing program, <a href="http://www.irfanview.com/">IrfanView</a>, a small, simple, decent program that uses little CPU. To capture a thumbnail, open IrfanView, click Options > Capture/Screenshot, capture your screen, then crop by clicking the top corner of your desired area and dragging the mouse to encompass the whole video window. Next click Edit > Crop Selection and save your image as a high-quality JPEG an upload it below. Easy!</li>
						</ol>
					</fieldset>';
			}
		}
	}
		
	
	$page->header();
	echo $mod_header;
	
	?>
	
	<div class="warn">This feature isn't ready yet</div>
	<fieldset>
		<legend>Current Trailers</legend>
		<?
		$query = "SELECT * FROM games_trailers WHERE gid='$gdat->gid' ORDER BY datetime";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		if(!$trailernum = mysqli_num_rows($res)) {
			echo "None yet :(";
		} else {
			$i = 0;
			while($row = mysqli_fetch_assoc($res)) {
				$i++;
				echo '<b>'.$row['title'].'</b> <small>Posted '.$row['datetime'].' by '.outputUser($row['usrid'], FALSE).'</small> <input type="button" value="Edit/Delete" id="button-'.$i.'" onclick="toggle(\'edit-'.$i.'\', \'button-'.$i.'\')"/><br/>';
				?>
				<form action="games-mod.php?id=<?=$id?>&what=trailers" method="post" id="edit-<?=$i?>">
					<fieldset>
						<legend>Make Changes</legend>
						<table border="0" cellspacing="0" class="styled-form">
							
							<tr>
								<th>&nbsp;</th>
								<td>
									<input type="submit" name="in[submit]" value="Submit Changes" style="font-weight:bold"/> 
									<input type="reset" value="Cancel" onclick="toggle('button-<?=$i?>', 'edit-<?=$i?>')"/>
								</td>
							</tr>
						</table>
					</fieldset>
				</form>
				<?
				if($i > $trailernum) echo '<div class="hr"></div>'."\n\n";
			}
		}
		?>
	</fieldset>
	
	<br/>
	
	<form action="games-mod.php?id=<?=$id?>&what=trailers" method="post">
		<fieldset>
			<legend>New Trailer</legend>
			<table border="0" cellspacing="0" class="styled-form">
				<tr>
					<th>Title</th>
					<td><input type="text" name="in[title]" size="60"/></td>
				</tr>
				<tr>
					<th>Description<br/><small>optional</small></th>
					<td><textarea name="in[description]" rows="3" cols="45"></textarea></td>
				</tr>
				<tr>
					<th>The Trailer</th>
					<td>
						<label><input type="radio" name="in[type]" value="code" checked="checked" id="do-embed" onclick="toggle('input-embed', 'input-link');"/> Embed HTML code</label> 
						<label><input type="radio" name="in[type]" value="url" id="do-link" onclick="toggle('input-link', 'input-embed');"/> Link to URL</label> 
						<p id="input-embed">
							<textarea name="in[code]" rows="6" cols="45"></textarea>
						</p>
						<p id="input-link" style="display:none">
							<input type="text" name="in[url]" size="60" style="color:blue; text-decoration:underline;"/>
						</p>
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td>
						<input type="submit" name="in[submit_add]" value="Add Trailer"/>
					</td>
				</tr>
			</table>
		</fieldset>
	</form>
	<?
	
	$page->footer();
	
}

if($what == "links") {
	
	///////////
	// LINKS //
	///////////
	
	//depreciated (EXCEPT DELETE LINK) -- all links can otherwise be managed via game overview
	
	if($del = $_GET['delete']) {
		
		// delete
		
		if($usrrank < 7) die("Can't do this with rank");
		
		$q = "SELECT * FROM games_links WHERE id='$del' LIMIT 1";
		if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Link id #$del not in db");
		
		$q = "DELETE FROM games_links WHERE id='$del' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) {
			$errors[] = "Couldn't delete";
		} else {
			adminAction("games link: game:$gdat[title] Site:$dat->site_name URL:$dat->url Posted:$dat->datetime by ".outputUser($dat->usrid, FALSE, FALSE), "deleted");
			//notify superuser
			if($dat->usrid && ($dat->usrid != $usrid)) {
				$udat = getUserDat($dat->usrid);
				if($udat->rank == 9) {
					mail($udat->email, "Videogam.in: link deleted", "A link you posted has been deleted by ".outputUser($usrid, FALSE, FALSE).":\n\nSite:".$dat->site_name."\nURL:".$dat->url."\nGame:".$gdat[title]."\nhttp://theVideogam.in.com/games/".$gdat[platform_shorthand]."/".$gdat[title_url]."/");
				}
			}
			$results[] = "Deleted";
		}
		
	}
	
	$page->header();
	echo $mod_header;
	
	?>
	
	<fieldset>
		<legend>Current Links</legend>
		<?
		$query = "SELECT * FROM games_links WHERE gid='$id'";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		if(!mysqli_num_rows($res)) {
			echo "No links";
		} else {
			while($row = mysqli_fetch_assoc($res)) {
				echo '<p>';
				echo '<a href="'.$row[url].'" target="_blank">'.$row[site_name].'</a> ';
				echo '<a href="?id='.$id.'&what=links&delete='.$row[id].'" class="x">X</a>';
				if($row[description]) echo '<br/>'.$row[description];
				echo '<br/><small>Added by '.outputUser($row[usrid], FALSE).' on '.$row[datetime].'</small>';
				echo '</p>';
			}
		}
		?>
	</fieldset>
	
	<?
	
	$page->footer();
	
}

if($what == "files") {
	
	///////////
	// FILES //
	///////////
	
	if($_POST['upload']) {
		
		if($_POST['upload_bgimg']) {
			if($curr = $_POST['currentfile']) {
				$newf = "/bin/deleted-files/".str_replace("/", "--", $curr)."--".rand(10000, 99999);
				rename($_SERVER['DOCUMENT_ROOT'].$curr, $_SERVER['DOCUMENT_ROOT'].$newf);
			}
			if($usrid != 1) @mail($default_email, "[Videogam.in] New game bg img!", "A new image has been uploaded by ".outputUser($usrid, false, false)." to http://videogam.in/games/$id\n\n".($newf ? "Note old img here -> http://videogam.in/$newf\n\n" : ""));
		}
		
		// Upload
		$handle = new Upload($_FILES['file']);
    if ($handle->uploaded) {
    	if($_POST['upload_bgimg']) {
    		$handle->file_new_name_body = 'background_'.$_POST['bgimgalign'];
    		$handle->file_overwrite = true;
    	}
    	$handle->Process($_SERVER['DOCUMENT_ROOT']."/games/files/$id/");
        if ($handle->processed) {
           $results[] = 'File uploaded: <a href="/games/files/'.$id.'/'.$handle->file_dst_name.'" target="_blank">'.$handle->file_dst_name.'</a>';
        } else {
           $errors[] = 'file not uploaded to the wanted location: ' . $handle->error;
        }
    } else {
        // if we're here, the upload file failed for some reasons
        // i.e. the server didn't receive the file
        $errors[] = 'file not uploaded on the server: ' . $handle->error;
    }
    
    if($_POST['upload_bgimg']) {
    	header("Location: /games/$id/");
    	exit;
    }
		
	}
	
	//Delete
	if($del = urldecode($_GET['delete'])) {
		if($usrrank <= 7) die("Invalid user rank");
		$file = $_SERVER['DOCUMENT_ROOT']."/games/files/$id/$del";
		if(!is_file($file)) {
			$errors[] = "Can't delete $handle since it isn't a file";
		} else {
			copy($file, $_SERVER['DOCUMENT_ROOT']."/bin/deleted-files/games--files--$id--$del--".date('YmdHis'));
			if(!unlink($file)) {
				$errors[] = "Couldn't delete $file";
			} else {
				adminAction("/games/files/$id/$del", "delete");
				$results[] = 'File deleted and sent to <a href="/bin/deleted-files/" target="_blank">/bin/deleted-files</a>';
			}
		}
	}
	
	$page->header();
	echo $mod_header;
	
	?>
	
	<div style="margin-bottom:15px; padding:10px; border:1px solid #06C; font-size:21px;">
		Files directory is <a href="/games/files/<?=$id?>/" target="_blank">/games/files/<?=$id?></a>
	</div>
	
	<form action="games-mod.php?id=<?=$id?>&what=files" method="post" ENCTYPE="multipart/form-data">
		<fieldset>
			<legend>Upload a File</legend>
			<input type="file" name="file"> 
			<input type="submit" name="upload" value="Upload"/>
		</fieldset>
	</form>
	<br/>
	
	<fieldset>
		<legend>Current Files</legend>
		<select id="files" size="5">
			<?
			if ($handle = opendir($_SERVER['DOCUMENT_ROOT']."/games/files/$id/")) {
				while (false !== ($file = readdir($handle))) {
					if($file != "." && $file != "..") echo '<option value="'.urlencode($file).'">'.$file."</option>\n";
		    }
		  }
			?>
		</select>
		<p>
			<input type="button" value="View" onclick="window.open('/games/files/<?=$id?>/'+document.getElementById('files').value);"/> 
			<input type="button" value="Delete" onclick="if(confirm('Really delete ?')) document.location='games-mod.php?what=files&id=<?=$id?>&delete='+document.getElementById('files').value;"<?=($usrrank <= 7 ? ' style="display:none"' : '')?>/>
		</p>
	</fieldset>
	<br/>
	
	<?
	
	$page->footer();
	
}

if($what == "trivia") {
	
	////////////
	// TRIVIA //
	////////////
	
	if($_POST['submit_edit']) {
		
		if(!$factid = $_POST['factid']) die("No fact id given");
		
		if($usrrank < 9) {
			$q = "SELECT usrid FROM games_trivia WHERE id='$factid' LIMIT 1";
			$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
			$udat = getUserDat($dat->usrid);
			if($udat->rank > $usrrank) $errors[] = "You don't have permission to edit this item since the item's author outranks you.";
		}
		
		if(!$errors) {
		
			if($in['delete']) {
				
				$q = "DELETE FROM games_trivia WHERE id='".$_POST['factid']."' LIMIT 1";
				if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't delete; ".mysql_error();
				else $results[] = "Factoid deleted";
				
			} else {
				
				$q = sprintf("UPDATE games_trivia SET fact='%s', author='%s', datetime='%s' WHERE id='".$_POST['factid']."' LIMIT 1",
					mysqli_real_escape_string($GLOBALS['db']['link'], $in['fact']),
					mysqli_real_escape_string($GLOBALS['db']['link'], $in['author']),
					mysqli_real_escape_string($GLOBALS['db']['link'], $in['datetime']));
				if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't update db; ".mysql_error();
				else $results[] = "Factoid edited";
				
			}
		}
	}
	
	if($_POST['submit_new']) {
		
		if($in['author_method'] == "select") $author = $in['author_select'];
		elseif($in['author_method'] == "input") $author = $in['author_input'];
		else $author = "";
		
		$subj = "games_trivia:".mysqlNextAutoIncrement("games_trivia");
		$q = sprintf("INSERT INTO games_trivia (gid, fact, author, datetime, usrid) VALUES 
			('$id', '%s', '%s', '%s', '$usrid');",
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['fact']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $author),
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['datetime']));
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't add to db; ".mysql_error();
		else {
			$results[] = "Factoid added";
			addUserContribution(4, 'Trivia for <a href="/games/link.php?id='.$id.'">'.htmlent($gdat['title']).'</a>', $in['fact'], TRUE, '', $subj, 'gid:'.$id);
		}
		
	}
	
	$page->header();
	echo $mod_header;
	
	?>
	
	<input type="button" value="New Factoid" id="add-trivia-button" onclick="toggle('add-trivia-form','add-trivia-button');"/>
	
	<form action="games-mod.php?id=<?=$id?>&what=trivia" method="post" id="add-trivia-form" style="display:none">
		<fieldset>
			<legend>New Factoid</legend>
			<table border="0" cellpadding="0" cellspacing="0" width="100%" class="styled-form">
				<tr>
					<th>The Factoid</th>
					<td>
						<textarea name="in[fact]" rows="10" cols="60" id="input-fact"></textarea>
						<p><?=saveDraftButton("input-fact", $in['title_url']."_factoid")?></p>
					</td>
				</tr>
				<tr>
					<th>Author</th>
					<td>
						<label><input type="radio" name="in[author_method]" value="select" checked="checked"/> A Videogam.in user:</label> 
						<select name="in[author_select]">
							<?
							$query = "SELECT usrid, username FROM users ORDER BY username";
							$res   = mysqli_query($GLOBALS['db']['link'], $query);
							while($row = mysqli_fetch_assoc($res)) {
								echo '<option value="usrid:'.$row['usrid'].'"'.($row['usrid'] == $usrid ? ' selected="selected"' : '').'/>'.$row['username'].'</option>';
							}
							?>
						</select>
						<p>
							<label><input type="radio" name="in[author_method]" value="input"/> Someone else:</label> 
							<input type="text" name="in[author_input]" size="45" id="input-author"/> (use HTML to link)
						</p>
						<p><label><input type="radio" name="in[author_method]" value=""/> Nobody (make it anonymous)</label></p>
					</td>
				</tr>
				<tr>
					<th>Date & time</th>
					<td><input type="text" name="in[datetime]" value="<?=date("Y-m-d H:i:s")?>" maxlength="19" size="16"/> YYYY-MM-DD HH:MM:SS</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><input type="submit" name="submit_new" value="Submit Factoid"/></td>
				</tr>
			</table>
		</fieldset>
	</form>
	
	<br/><br/>
	
	<fieldset>
		<legend>Curent Factoids</legend>
		<?
		$query = "SELECT * FROM games_trivia WHERE gid='$id' ORDER BY datetime DESC";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		if(mysqli_num_rows($res)) {
			while($row = mysqli_fetch_assoc($res)) {
				$row = stripslashesDeep($row);
				
				if($row['author']) {
					$author = $row['author'];
					if(substr($author, 0, 6) == "usrid:") {
						$spaces = strlen($author) - 6;
						$author = outputUser(substr($author, 6, $spaces), FALSE, TRUE);
					}
				} else $author = "";
				
				?>
				<div style="margin:15px">
					<div id="fact-<?=$row['id']?>-words">
						<?=$row['fact']?>
						<div style="margin-top:3px">author: <b><?=$author?></b> | date: <b><?=$row['datetime']?></b> <input type="button" value="Edit" onclick="toggle('fact-<?=$row['id']?>-edit', 'fact-<?=$row['id']?>-words');"/></div>
					</div>
					<div id="fact-<?=$row['id']?>-edit" style="display:none; padding:10px; background-color:#EEE;">
						<form action="games-mod.php?id=<?=$id?>&what=trivia" method="post">
							<input type="hidden" name="factid" value="<?=$row['id']?>"/>
							<textarea name="in[fact]" rows="5" cols="50"><?=$row['fact']?></textarea>
							<p><textarea name="in[author]" rows="1" cols="50"><?=$row['author']?></textarea></p>
							<p><input type="text" name="in[datetime]" value="<?=$row['datetime']?>" maxlength="19" size="16"/> YYYY-MM-DD HH:MM:SS</p>
							<p><label><input type="checkbox" name="in[delete]" value="1"/> Destroy Factoid!!!</label></p>
							<p><input type="submit" name="submit_edit" value="Process Edits"/> <input type="button" value="Cancel" onclick="toggle('fact-<?=$row['id']?>-words', 'fact-<?=$row['id']?>-edit');"/></p>
						</form>
					</div>
				</div>
				<?
			}
		} else {
			echo 'None :(';
		}
		?>
	</fieldset>
	<?
	
	$page->footer();
	
}

if($what == "quotes") {
	
	////////////
	// QUOTES //
	////////////
	
	//submit add
	if($_POST['add_quote']) {
		if(!$in['quote']) die("No quote given");
		$subj = "games_quotes:".mysqlNextAutoIncrement("games_quotes");
		$query = sprintf("INSERT INTO games_quotes (gid, quote, quote_more, quoter, datetime, usrid) VALUES 
			('$id', '%s', '%s', '%s', '".date("Y-m-d H:i:s")."', '$usrid')",
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['quote']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['quote_more']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['quoter']));
		if(!mysqli_query($GLOBALS['db']['link'], $query)) $errors[] = "Couldn't add quote";
		else {
			$results[] = "Quote successfully added";
			addUserContribution(5, 'Quote about <a href="/games/link.php?id='.$id.'">'.htmlent($gdat['title']).'</a> by '.$in['quoter'], '<blockquote>'.$in['quote'].'</blockquote>-'.$in['quoter'], TRUE, '', $subj, 'gid:'.$id);
		}
	}
	
	//edit form
	if($edit = $_GET['edit']) {
		
		$page->header();
		echo $mod_header;
	
		$q = "SELECT * FROM games_quotes WHERE id='$edit' LIMIT 1";
		if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Couldn't get data for id # $edit");
		?>
		<form action="games-mod.php?id=<?=$id?>&what=quotes" method="post">
			<input type="hidden" name="in[id]" value="<?=$edit?>"/>
			<fieldset>
				<legend>Editing Quote # <?=$edit?></legend>
				<table border="0" cellspacing="0" class="styled-form">
					<tr>
						<th>Quote:</th>
						<td>
							<span style="font:normal 50px 'arial black',arial; vertical-align:top;">&ldquo;</span>
							<textarea name="in[quote]" rows="5" cols="45"><?=stripslashes($dat->quote)?></textarea>
							<span style="font:normal 50px 'arial black',arial; vertical-align:top;">&rdquo;</span>
						</td>
					</tr>
					<tr>
						<th>Extended Quote:<br/><small>This text is initially hidden and should be utilized if your quote is longer than 2-3 sentences</small></th>
						<td>
							<span style="font:normal 50px 'arial black',arial; vertical-align:top;">&ldquo;</span>
							<textarea name="in[quote_more]" rows="5" cols="45"><?=stripslashes($dat->quote_more)?></textarea>
							<span style="font:normal 50px 'arial black',arial; vertical-align:top;">&rdquo;</span>
						</td>
					</tr>
					<tr>
						<th>Quoter HTML:<br/><small>Cite the quoter here, using HTML to link back to the source</small></th>
						<td><textarea name="in[quoter]" rows="2" cols="45"><?=stripslashes($dat->quoter)?></textarea></td>
					</tr>
					<tr>
						<th>Date Time:</th>
						<td>
							Quotes are sorted by descending date time
							<p><input type="text" name="in[datetime]" value="<?=$dat->datetime?>" maxlength="19"/> YYYY-MM-DD HH:MM:SS</p>
						</td>
					</tr>
					<tr>
						<th>Delete:</th>
						<td><label><input type="checkbox" name="in[delete]" value="1"/> Delete this quote</label></td>
					<tr>
						<th>&nbsp;</th>
						<td><input type="submit" name="edit_quote" value="Submit Edits"/></td>
					</tr>
				</table>
			</fieldset>
		</form>
		<?
		
		$page->footer();
		exit;
	}
	
	//submit edit
	if($_POST['edit_quote']) {
		
		if(!$in['id']) die("No id given");
		if(!$in['quote']) die("No quote given");
		if(!preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/", $in['datetime'])) {
			$in['datetime'] = "";
			$warnings[] = "The datetime was not in proper format and won't be changed";
		}
		
		if($in['delete']) {
			$q = "DELETE FROM games_quotes WHERE id='".$in['id']."' LIMIT 1";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't delete";
			else $results[] = "Deleted it";
		} else {
			$query = sprintf("UPDATE games_quotes SET 
				quote = '%s', 
				quote_more = '%s', 
				quoter = '%s' 
				".($in['datetime'] ? ", datetime = '".$in['datetime']."'" : "")."
				WHERE id='".$in['id']."' LIMIT 1", 
				mysqli_real_escape_string($GLOBALS['db']['link'], $in['quote']),
				mysqli_real_escape_string($GLOBALS['db']['link'], $in['quote_more']),
				mysqli_real_escape_string($GLOBALS['db']['link'], $in['quoter']));
			if(!mysqli_query($GLOBALS['db']['link'], $query)) $errors[] = "Couldn't edit quote";
			else $results[] = "Quote successfully edited";
		}
	}
	
	$page->header();
	echo $mod_header;
	
	?>
	
	<p>Quotes, also known as "Heresay" are displayed on the game overview page. The purpose is to highlight insightful and/or witty things 
	people have said about this game.</p>
	<br/>
	
	<input type="button" value="Add a Quote" onclick="this.style.display='none'; toggle('add-quote-form', '');"/>
	
	<form action="games-mod.php?id=<?=$id?>&what=quotes" method="post" id="add-quote-form" style="display:none">
		<fieldset>
			<legend>Add a Quote</legend>
			<table border="0" cellspacing="0" class="styled-form">
				<tr>
					<th>Quote:</th>
					<td>
						<span style="font:normal 50px 'arial black',arial; vertical-align:top;">&ldquo;</span>
						<textarea name="in[quote]" rows="5" cols="45"></textarea>
						<span style="font:normal 50px 'arial black',arial; vertical-align:top;">&rdquo;</span>
					</td>
				</tr>
				<tr>
					<th>Extended Quote:<br/><small>This text is initially hidden and should be utilized if your quote is longer than 2-3 sentences</small></th>
					<td>
						<span style="font:normal 50px 'arial black',arial; vertical-align:top;">&ldquo;</span>
						<textarea name="in[quote_more]" rows="5" cols="45"></textarea>
						<span style="font:normal 50px 'arial black',arial; vertical-align:top;">&rdquo;</span>
					</td>
				</tr>
				<tr>
					<th>Quoter HTML:<br/><small>Cite the quoter here, using HTML to link back to the source</small></th>
					<td><textarea name="in[quoter]" rows="2" cols="45"><a href="http://" target="_blank"></a></textarea></td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><input type="submit" name="add_quote" value="Add Quote"/></td>
				</tr>
			</table>
		</fieldset>
	</form>
	
	<br/><br/>
	
	<fieldset>
		<legend>Current Quotes</legend>
		<?
		$query = "SELECT * FROM games_quotes WHERE gid='$id' ORDER BY datetime DESC";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		if(!mysqli_num_rows($res)) {
			echo "None yet :(";
		} else {
			while($row = mysqli_fetch_assoc($res)) {
				?>
				<blockquote><?=$row['quote'].($row['quote_more'] ? '<hr/>'.$row['quote_more'] : '')?></blockquote>
				<p style="text-align:right">
					<?=$row['quoter']?> 
					<small>(submitted <?=$row['datetime']?> by <?=outputUser($row['usrid'], FALSE)?>)</small> 
					<input type="button" value="Edit/Delete" onclick="document.location='games-mod.php?id=<?=$id?>&what=quotes&edit=<?=$row['id']?>'"/>
				</p>
				<hr/><?
			}
		}
		?>
	</fieldset>
	<?
	
	$page->footer();
}

if($what == "guide") {
	
	////////////////
	// GAME GUIDE //
	////////////////
	
	require("include.game_guide.php");
	
}

if($what == "preview") {
	
	/////////////
	// PREVIEW //
	/////////////
	
	require("include.preview.php");

}

?>