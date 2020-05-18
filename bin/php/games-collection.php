<?
use Vgsite\Page;

$do = $_GET['do'];
$action = $_POST['action'];
$gid = $_POST['gid'];

if($action == "output_form") {
	?>
	Sorry, this feature is under contruction. <a href="javascript:void(0)" onclick="toggle('','add-game')">Close this window</a>
	<?
	exit;
	$q = "SELECT * FROM games WHERE gid='$gid' LIMIT 1";
	$gdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
	
	//check for existing
	$query = "SELECT * FROM my_games WHERE gid='$gid' AND usrid='$usrid'";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res) && $_POST['bypass'] != '1') {
		
		?>
		<a href="javascript:void(0)" onclick="toggle('','add-game')" class="x">X</a>
		
		This game is already on your list. 
		Choose a publication below to edit it, or <a href="javascript:void(0)" onclick="addGame('<?=$gid?>','1')">add another publication of this game</a>.
		<ul>
			<?
			while($row = mysqli_fetch_assoc($res)) {
				?><li><a href="javascript:void(0)" onclick="addGame('<?=$gid?>', '1', '<?=$row['id']?>')"><?
				if($row['publication_id']) {
					$q = "SELECT * FROM games_publications LEFT JOIN games_platforms USING (platform_id) WHERE id='".$row['publication_id']."' LIMIT 1";
					if($pdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) echo $pdat->title."</a> (".$pdat->platform.")";
					else echo "???</a>";
				} else {
					$q = "SELECT platform FROM games_platforms WHERE platform_id='".$row['platform_id']."' LIMIT 1";
					$x = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
					echo $row['title']."</a> (".$x->platform.")";
				}
				?></li><?
			}
			?>
		</ul>
		<?
		
	} else {
		
		$editid = $_POST['editid'];
		if(is_numeric($editid)) {
			$q = "SELECT * FROM my_games WHERE id='$editid' LIMIT 1";
			if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
				die("Error: Couldn't get data");
			}
		} else $editid = "";
		
		//generate session id in case of upload
		$rand = "";
		$possible = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	  $i = 0;
		while ($i < 10) {
			$rand .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
			$i++;
		}
		$gcsessid = $gdat->gid."_".$usrid."_".$rand;
	
		?>
		<input type="hidden" name="dbaction" value="<?=($editid ? $editid : 'insert')?>" id="dbaction"/>
		
		<a href="javascript:void(0)" onclick="toggle('','add-game')" class="x">X</a>
		
		<table border="0" cellpadding="0" cellspacing="0" id="add-game-form">
			<tr>
				<th nowrap="nowrap">
					<div id="selbox-heading-select">
						Select a cover:
						<?=(!$dat ? '<small>or <a href="javascript:void(0)" onclick="toggle(\'selbox-heading-upload\', \'selbox-heading-select\'); toggle(\'selbox-upload\', \'selbox-select\'); GCsessid=\''.$gcsessid.'\';">upload your own</a></small>' : '')?>
					</div>
					<div id="selbox-heading-upload" style="display:none">
						Upload a cover:
						<small>or <a href="javascript:void(0)" onclick="if(confirm('End this upload session (and deselect any uploaded covers)?')) { toggle('selbox-heading-select', 'selbox-heading-upload'); toggle('selbox-select', 'selbox-upload'); GCsessid=''; }">select a cover</a></small>
					</div>
				</th>
				<td style="padding-right:10px;">
					
					<div id="selbox-select">
						<?
						if($dat && !$dat->publication_id) {
							//editing an uploaded box
							$thispf = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], "SELECT platform FROM games_platforms WHERE platform_id = '$dat->platform_id' LIMIT 1"));
							$pf_imgs[] = '<a href="javascript:void(0)" onclick="selectBoxCover(\'x\');" class="tooltip" title="<i>'.$dat->title.'</i> for '.$thispf->platform.' (your uploaded cover)"><img src="/bin/uploads/user_boxart/'.$dat->id.'_tn.png" id="box-x" class="on"/></a>'."\n";
							echo '<input type="hidden" id="AGselpubid" value="x"/>';
						}
						$query = "SELECT * FROM games_publications pub LEFT JOIN games_platforms pf USING (platform_id) WHERE pub.gid='$gid' ORDER BY `primary` DESC";
						$res   = mysqli_query($GLOBALS['db']['link'], $query);
						$pf_num = mysqli_num_rows($res);
						while($row = mysqli_fetch_assoc($res)) {
							$img = "/games/files/".$gdat->gid."/".$gdat->gid."-box-".$row['id']."-tn.png";
							if(file_exists($_SERVER['DOCUMENT_ROOT'].$img)) {
								$p_img = $img;
								$imgsz = getimagesize($_SERVER['DOCUMENT_ROOT'].$img);
								if($imgsz[1] > $c_height) $c_height = $imgsz[1];
							} else {
								$p_img = "/bin/img/no_box.png";
								if($c_height < 80) $c_height = 80;
							}
							if($dat) {
								$pf_imgs[] = '<a href="javascript:void(0)" onclick="selectBoxCover('.$row['id'].');" class="tooltip" title="<i>'.$row['title'].'</i> for '.$row['platform'].'"><img src="'.$p_img.'" id="box-'.$row['id'].'" class="'.($row['id'] == $dat->publication_id ? 'on' : 'off').'"/></a>'."\n";
								if($row['id'] == $dat->publication_id) echo '<input type="hidden" name="publication_id" value="'.$row['id'].'" id="AGselpubid"/>';
							} else {
								$pf_imgs[] = '<a href="javascript:void(0)" onclick="selectBoxCover('.$row['id'].');" class="tooltip" title="<i>'.$row['title'].'</i> for '.$row['platform'].'"><img src="'.$p_img.'" id="box-'.$row['id'].'" class="'.($row['primary'] ? 'on' : 'off').'"/></a>'."\n";
								if($row['primary']) echo '<input type="hidden" name="publication_id" value="'.$row['id'].'" id="AGselpubid"/>';
							}
							//$pfs[] = '<li><label><input type="checkbox" name="platform_id" value="'.$row['platform_id'].'" id="platform_id"> '.$row['platform'].'</label></li>'."\n";
						}
						
						if($pf_imgs) {
							$boxnum = count($pf_imgs);
							?>
							<div id="boxes" style="<?=($c_height ? 'height:'.($c_height + ($boxnum > 4 ? 31 : 11)).'px;' : '').($boxnum > 4 ? 'width:224px;' : '')?>">
								<?
								if($boxnum > 4) {
									$max = ceil($boxnum / 4);
									?>
									<div id="add-game-boxnav">
										<div style="float:right;">
											<a href="javascript:void(0)" class="arrow-left off" id="agboxnav-prev" onclick="AGboxnavigate('prev');">Prev</a> &nbsp;
											<a href="javascript:void(0)" class="arrow-right" id="agboxnav-next" onclick="AGboxnavigate('next', '<?=$max?>');">Next</a>
										</div>
										<?=$boxnum?> covers 
									</div>
									<?
								}
								$i = 0;
								$j = 0;
								foreach($pf_imgs as $pf_img) {
									$i++;
									if($i == 1) {
										$j++;
										echo '<div id="boxes-pg-'.$j.'"'.($j > 1 ? ' style="display:none"' : '').'>';
									}
									echo $pf_img;
									if($i == 4) {
										echo '</div>';
										$i = 0;
									}
								} echo '</div>';
								?>
							</div>
							<?
						} else {
							?>
							<div id="boxes" style="margin:0; font-size:13px;">
								Sorry, there aren't any publications for this game in our database. However, you can still add this game to your gamelist by uploading your own cover image.
								<input type="hidden" value="" id="AGselpubid"/>
							</div>
							<?
						}
						?>
					</div>
					
					<!-- upload -->
					<div id="selbox-upload" style="display:none">
						
						<div id="ag-upload-1" style="font-size:12px">
							<b>Cover images that work best</b> are at least 200 pixels in width; are unblurred, clear, quality images without 
							watermarks or site logos; and are flat images that are not scaled, rotated, have a 3D perspective, or have any borders 
							or whitespace around the perimiter.
							<p>Please only upload images in JPG, GIF, or PNG format.</p>
							<form action="/bin/php/games-collection.php?do=user_upload" method="post" enctype="multipart/form-data" target="aguploadframe">
								<input type="hidden" name="gcsessid" value="<?=$gcsessid?>"/>
								<p>
									<input type="file" name="file" size="11" id="gc-inp-file"/> 
									<input type="submit" name="submit" value="Upload" onclick="if(!document.getElementById('gc-inp-file').value) return false; else { toggle('ag-upload-2','ag-upload-1'); return true; }"/>
								</p>
							</form>
						</div>
						
						<div id="ag-upload-2" style="display:none">
							<div style="float:left; margin-right:5px;"><iframe src="/bin/php/games-collection.php?do=user_upload&gcsessid=<?=$gcsessid?>" name="aguploadframe" id="aguploadframe" frameborder="0" scrolling="no" style="width:80px; height:135px;"></iframe></div>
							<div style="float:left;">
								<input type="button" value="Use it" onclick="toggle('ag-upload-3','ag-upload-2');"/><br/>
	    					<input type="button" value="Try again" onclick="aguploadframe.location='games-collection.php?do=user_upload&delete_file=<?=$gcsessid?>.jpg'; toggle('ag-upload-1','ag-upload-2');" style="margin-top:5px"/>
	    				</div>
	    			</div>
						
						<div id="ag-upload-3" style="display:none">
							<label><input type="checkbox" id="ag-uplinp-share"> Make this cover image public so other people can use it, too.</label>
							
							<p>
								<input type="text" value="<?=htmlent($gdat->title)?>" size="30" id="ag-uplinp-title"/> 
								<a href="javascript:void(0)" class="tooltip" title="Input the full title of the publication, for example: &quot;Final Fantasy XII Collector's Edition&quot; will differentiate it from regular old Final Fantasy XII"><span class="block">?</span></a>
							</p>
							
							<p><select id="ag-uplinp-platform">
								<option value="">Select a platform...</option>
								<?
								$query = "SELECT * FROM games_platforms WHERE platform != 'multiple' ORDER BY platform";
								$res   = mysqli_query($GLOBALS['db']['link'], $query);
								while($row = mysqli_fetch_assoc($res)) {
									echo '<option value="'.$row['platform_id'].'">'.$row['platform']."</option>\n";
								}
								?>
							</select></p>
							
							<p id="ag-selregionp"><select id="ag-uplinp-region" onchange="if(this.options[this.selectedIndex].value=='other') { this.options[this.selectedIndex].value=''; toggle('ag-uplinp-regionother','ag-uplinp-region'); document.getElementById('ag-selregionp').style.marginBottom='20'; }">
								<option value="">Select a region...</option>
								<option value="us">North America</option>
								<option value="jp">Japan</option>
								<option value="eu">Europe</option>
								<option value="au">Australia</option>
								<option value="other">elsewhere</option>
							</select>
							<select id="ag-uplinp-regionother" style="display:none">
								<option value="">Select from more options...</option>
								<?
								require($_SERVER['DOCUMENT_ROOT']."/bin/php/country_codes.php");
								while(list($k, $v) = each($cc)) {
									echo '<option value="'.strtolower($k).'">'.$v.'</option>';
								}
								?>
							</select></p>
						</div>
					
					</div>
					
				</td>
				<td nowrap="nowrap" class="buttons"<?=($gdat->online ? ' style="height:82px"' : '')?>>
					
					<input type="hidden" name="own" value="<?=($dat->own ? '1' : '')?>" id="action-own"/>
					<input type="hidden" name="play" value="<?=($dat->play ? '1' : '')?>" id="action-play"/>
					<input type="hidden" name="play_online" value="<?=($dat->play_online ? '1' : '')?>" id="action-play_online"/>
					
					<table border="0" cellpadding="0" cellspacing="5">
						<tr>
							<td><a href="javascript:void(0)" id="action-own-button" class="tooltip <?=($dat->own ? 'on' : '')?>" title="I own this game" onclick="setGameAction('own')"><span>Own</span></a></td>
							<td><a href="javascript:void(0)" id="action-play-button" class="tooltip <?=($dat->play ? 'on' : '')?>" title="I am currently playing this game" onclick="setGameAction('play')"><span>Play</span></a></td>
						</tr>
						<?
						if($gdat->online) {
							?>
							<tr>
								<td colspan="2"><a href="javascript:void(0)" id="action-play_online-button" class="<?=($dat->play_online ? 'on' : '')?>" onclick="setGameAction('play_online')"><span>Play online</span></a></td>
							</tr>
							<tr>
								<td colspan="2">
									<div id="playonline-input" class="tooltip" title="Your ID is optional and will allow your friends to connect with you"<?=(!$dat->play_online ? ' style="display:none"' : '')?>>
									<?
									if(!$dat->online_id) {
										?><input type="text" name="online_id" value="Your online ID" id="online_id" onfocus="if(this.value=='Your online ID') { this.value=''; this.className='focused'; }" onblur="if(this.value=='') { this.value='Your online ID'; this.className=''; }"/><?
									} else {
										?><input type="text" name="online_id" value="<?=$dat->online_id?>" id="online_id" class="focused"/><?
									}
									?>
									</div></td>
							</tr>
							<?
						} else {
							?><input type="hidden" name="online_id" value="" id="online_id"/><?
						}
						?>
					</table>
				</td>
				<td nowrap="nowrap" width="84" class="rating">
					<input type="hidden" name="game-rating" id="game-rating" value="<?=($dat->rating ? $dat->rating : '1')?>"/>
					<ul>
						<li><a href="javascript:void(0)" class="tooltip<?=($dat->rating == 2 ? ' on' : '')?>" title="Love it" onclick="setGameRating('2')" id="game-rating-2" style="background-image:url(/bin/img/game-rate-love.png);"><span>Love it</span></a></li>
						<li><a href="javascript:void(0)" class="tooltip<?=($dat->rating == '0' ? ' on' : '')?>" title="Hate it" onclick="setGameRating('0')" id="game-rating-0" style="background-image:url(/bin/img/game-rate-hate.png);"><span>Hate it</span></a></li>
					</ul>
				</td>
				<td style="border-width:0; padding-right:0;">
					<div id="add-game-submit-buttons">
						<a href="javascript:void(0)" onclick="submitAddGame('<?=$gid?>')" class="styled-button" style="margin:0 5px 0 0; font-weight:bold; font-size:17px;"><span>Save</span></a>
						<?=($dat ? '<a href="javascript:void(0)" onclick="deleteFromMyGames(\''.$dat->id.'\');" class="styled-button" style="font-size:17px;" onmouseover="toggle(\'remove-from-mygames-msg\',\'\');" onmouseout="toggle(\'\',\'remove-from-mygames-msg\');"><span>Remove</span></a><div id="remove-from-mygames-msg" style="display:none; clear:both; padding-top:5px; font-size:13px; color:#888;">Remove from My Games</div>' : '')?>
					</div>
					<div id="add-game-results"></div>
				</td>
			</tr>
		</table>
		<?
		
	}
	
}

if($action == "submit_add_game") {
	
	if(!$gid = $_POST['gid']) die("Error: no game id");
	$pid = $_POST['publication_id'];
	if($_POST['online_id'] == "Your online ID") $_POST['online_id'] = '';
	$_POST['title'] = str_replace("[AMP]", "&", $_POST['title']);
	
	if($sid = $_POST['gcsessid']) {
		//it's an upload
		if(!$_POST['title']) {
			$gdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], "SELECT title FROM games WHERE gid='$gid' LIMIT 1"));
			$_POST['title'] = $gdat->title;
		}
	}
	
	if($_POST['dbaction'] == "insert") {
		
		//insert
	
		$now = date("Y-m-d H:i:s");
		if($play = $_POST['action-play']) $play_start = "'$now'";
		else $play_start = "NULL";
		if($play_online = $_POST['action-play_online']) $play_online_start = "'$now'";
		else $play_online_start = "NULL";
		$_POST['online_id'] = htmlentities($_POST['online_id'], ENT_QUOTES);
		
		if(!$next_id = mysqlNextAutoIncrement('my_games')) die("Error: Couldn't get next database ID; ".mysqli_error($GLOBALS['db']['link']));
		
		if($sid) {
			
			//it's an upload
			
			unset($pid);
			
			$file = $_SERVER['DOCUMENT_ROOT']."/bin/temp/".$sid.".jpg";
			if(!file_exists($file)) die('Error: Your uploaded file seems to have become lost somewhere... Please <a href="javascript:void(0)" onclick="addGame(\''.$gid.'\');">try uploading again</a>.');
			$newfile = $_SERVER['DOCUMENT_ROOT']."/bin/uploads/user_boxart/".$next_id.".jpg";
			if(!copy($file, $newfile)) die("Coudn't copy uploaded image out of the temporary folder");
			if(!copy(substr($file, 0, -4)."_sm.png", substr($newfile, 0, -4)."_sm.png")) die("Coudn't copy small image out of the temporary folder");
			if(!copy(substr($file, 0, -4)."_tn.png", substr($newfile, 0, -4)."_tn.png")) die("Coudn't copy thumbnail image out of the temporary folder");
		
		} else {
			unset($_POST['title']);
			unset($_POST['platform_id']);
			unset($_POST['region']);
		}
		
		if($_POST['share_upload']) {
			
			require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/games-contribute.php"); //gets $gid & $gdat (& more)
			
			$description = '<a href="/games/link.php?id='.$gid.'">'.htmlentities($gdat->title, ENT_QUOTES).'</a> box art';
			
			//pend it or post automatically?
			if($_SESSION['user_rank'] >= 4) {
				
				$nextid = mysqlNextAutoIncrement("games_publications");
				$subj = "games_publications:".$nextid;
				
				//get # of current pubs and decide if this should be the primary pub
				$q = "SELECT * FROM games_publications WHERE gid='$gid'";
				if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $primary = '1';
				else $primary = '0';
				
				$q = "INSERT INTO games_publications (gid, platform_id, title, region, `primary`) VALUES 
				('$gid', '".$_POST['platform_id']."', '".htmlentities($_POST['title'], ENT_QUOTES)."', '".$_POST['region']."', '$primary')";
				if(!mysqli_query($GLOBALS['db']['link'], $q)) {
					sendBug("Error adding a user-submitted publication via + My Games\n\ngid: $gid (http://videogam.in/games/link.php?id=$gid)\nuser: $usrname (http://videogam.in/~$usrname)\ndb query: ".$q."\nerror: ".mysqli_error($GLOBALS['db']['link']));
				}
				
				$file = $sid.".jpg";
				$new_body = $gid."-box-".$nextid;
				$new_dir = "/games/files/".$gid."/";
				if(!is_dir($_SERVER['DOCUMENT_ROOT'].$new_dir)) mkdir($_SERVER['DOCUMENT_ROOT'].$new_dir, 0777);
				if(!copy($_SERVER['DOCUMENT_ROOT']."/bin/temp/".$file, $_SERVER['DOCUMENT_ROOT'].$new_dir.$new_body.".jpg")) die("Couldn't copy uploaded file");
				if(!copy($_SERVER['DOCUMENT_ROOT']."/bin/temp/".substr($file, 0, -4)."_sm.png", $_SERVER['DOCUMENT_ROOT'].$new_dir.$new_body."-sm.png")) die("Couldn't copy uploaded small file");
				if(!copy($_SERVER['DOCUMENT_ROOT']."/bin/temp/".substr($file, 0, -4)."_tn.png", $_SERVER['DOCUMENT_ROOT'].$new_dir.$new_body."-tn.png")) die("Couldn't copy uploaded thumbnail");
				
				contributeToGame();
				
				$pid = $nextid;
				unset($_POST['title']);
				unset($_POST['platform_id']);
				unset($_POST['region']);
				
			} else {
				
				$subj = "";
				$pendid = mysqlNextAutoIncrement('pending');
				
				$q = "INSERT INTO pending (`table`, usrid, `datetime`) VALUES 
				('pending_games_publications', '$usrid', '".date('Y-m-d H:i:s')."');";
				if(!mysqli_query($GLOBALS['db']['link'], $q)) sendBug("Error adding a [temporary] user-submitted publication via + MY GAMES\n\ngid: $gid (http://videogam.in/games/link.php?id=$gid)\nuser: $usrname (http://videogam.in/~$usrname)\ndb query: ".$q."\nerror: ".mysqli_error($GLOBALS['db']['link']));
				
				$q = "INSERT INTO pending_games_publications (pend_id, gid, platform_id, title, region, `file`) VALUES 
				('$pendid', '$gid', '".$_POST['platform_id']."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['title'])."', '".$_POST['region']."', '".$sid.".jpg')";
				if(!mysqli_query($GLOBALS['db']['link'], $q)) {
					sendBug("Error adding a [temporary] user-submitted publication via + MY GAMES\n\ngid: $gid (http://videogam.in/games/link.php?id=$gid)\nuser: $usrname (http://videogam.in/~$usrname)\ndb query: ".$q."\nerror: ".mysqli_error($GLOBALS['db']['link']));
					die("Error saving to database; ".mysqli_error($GLOBALS['db']['link']));
				}
				
			}
			
			addUserContribution(3, $description, '', ($_SESSION['user_rank'] <= 7 ? TRUE : FALSE), $pendid, $subj);
			
		}
		
		$q = "INSERT INTO my_games (usrid, gid, publication_id, play, play_start, play_online, play_online_start, online_id, own, rating, added, title, platform_id, region) VALUES
			('$usrid', '$gid', '$pid', '$play', $play_start, '$play_online', $play_online_start, '".mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['online_id'])."', '".$_POST['action-own']."', '".$_POST['game-rating']."', '$now', '".htmlentities($_POST['title'], ENT_QUOTES)."', '".$_POST['platform_id']."', '".$_POST['region']."');";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Error: couldn't save to database ".($_SESSION['user_rank'] > 5 ? mysqli_error($GLOBALS['db']['link']) : ''));
		else die($next_id);
		
	} else {
		
		$id = $_POST['dbaction'];
		
		$q = "SELECT * FROM my_games WHERE id='$id' LIMIT 1";
		if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Error: Couldn't get DB data to update");
		
		$now = date("Y-m-d H:i:s");
		if($play = $_POST['action-play'] && !$dat->play) {
			$play_start = "play_start = '$now',";
		}
		if($play_online = $_POST['action-play_online'] && !$dat->play_online) {
			$play_online_start = "play_online_start = '$now',";
		}
		$_POST['online_id'] = htmlentities($_POST['online_id'], ENT_QUOTES);
		
		$q = "UPDATE my_games SET 
			publication_id = '$pid', 
			play = '$play', 
			$play_start 
			play_online = '$play_online', 
			$play_online_start 
			online_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['online_id'])."', 
			own = '".$_POST['action-own']."', 
			rating = '".$_POST['game-rating']."' 
			WHERE id='$id' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Error: couldn't save to database ".($_SESSION['user_rank'] > 5 ? mysqli_error($GLOBALS['db']['link']) : ''));
		else die($q);die($id);
		
	}
	
	exit;
	
}

if($action == "delete") {
	
	if(!$id = $_POST['id']) die("Error: No ide given");
	
	$q = "DELETE FROM my_games WHERE id='$id' LIMIT 1";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Error: couldn't delete from database");
	else echo "ok";
	exit;

}

if($do == "user_upload") {
	
	if($file = $_GET['delete_file']) {
		@unlink($_SERVER['DOCUMENT_ROOT']."/bin/temp/$file");
	  @unlink($_SERVER['DOCUMENT_ROOT']."/bin/temp/".substr($file, 0, -4)."_sm.png");
	  @unlink($_SERVER['DOCUMENT_ROOT']."/bin/temp/".substr($file, 0, -4)."_tn.png");
	}
	
	echo Page::HTML_TAG;
	?>
	<head>
		<link rel="stylesheet" type="text/css" href="/bin/css/screen.css" media="screen"/>
		<link rel="stylesheet" type="text/css" href="/bin/css/games.css" media="screen"/>
		<style style="text/css">
			P { margin: 3px 0 0 0; }
		</style>
		<script type="text/javascript" src="/bin/script/global.js"></script>
		<script type="text/javascript" src="/bin/script/tooltip.js"></script>
	</head>
	<body style="margin:0; padding:0; background-color:#F5F5F5 !important; font-size:12px !important;" id="user-upload-form">
	<?
	
	if(!$_POST) {
		
		?><img src="/bin/img/loading-gray-arrows.gif" alt="loading"/><?
		exit;
		
	} else {
		
		if(!$_FILES['file']['name']) {
			die("No file submitted</body></html>");
		} else {
			
			require($_SERVER['DOCUMENT_ROOT']."/bin/php/class.upload.php");
			$handle = new Upload($_FILES['file']);
	    if ($handle->uploaded) {
	    	
	    	$handle->image_convert          = 'jpg';
				$handle->image_resize           = true;
				$handle->image_ratio_no_zoom_in = true;
				$handle->image_x                = 500;
				$handle->image_y                = 700;
	    	$handle->file_new_name_body = $_POST['gcsessid'];
	    	$handle->file_overwrite = TRUE;
	    	
	    	$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/temp/");
				if ($handle->processed) {
					$reg = $handle->file_dst_name;
					
					//small img
					$handle->file_new_name_body = $_POST['gcsessid']."_sm";
	    		$handle->file_overwrite        = TRUE;
					$handle->image_convert         = 'png';
					$handle->image_resize          = true;
					$handle->image_ratio_y         = true;
					$handle->image_x               = 140;
					$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/temp/");
					if ($handle->processed) $sm = $handle->file_dst_name;
					else $error = 'Small image couldn\'t be created: ' . $handle->error;
								
					//thumbnail
					$handle->file_new_name_body = $_POST['gcsessid']."_tn";
	    		$handle->file_overwrite        = TRUE;
					$handle->image_convert         = 'png';
					$handle->image_resize          = true;
					$handle->image_ratio_y         = true;
					$handle->image_x               = 80;
					$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/temp/");
					if ($handle->processed) $tn = $handle->file_dst_name;
					else $error = 'Thumbnail image couldn\'t be created: ' . $handle->error;
	      } else {
					$error = 'file not uploaded to the wanted location: ' . $handle->error;
				}
	    } else {
	        // if we're here, the upload file failed for some reasons
	        // i.e. the server didn't receive the file
	        $error = 'file not uploaded on the server: ' . $handle->error;
	    }
	    
	    if($error) {
	    	@unlink($_SERVER['DOCUMENT_ROOT']."/bin/temp/$reg");
	    	@unlink($_SERVER['DOCUMENT_ROOT']."/bin/temp/$sm");
	    	@unlink($_SERVER['DOCUMENT_ROOT']."/bin/temp/$tn");
	    	echo 'Upload process couldn\' continue; '.$error.'</body></html>';
	    } else {
	    	
	    	?>
	    	<img src="/bin/temp/<?=$tn?>" alt="your uploaded box"/>
	    	</body></html>
	    	<?
	    }
			
		}
		
	}
	
}

if($action == "show my games") {
	
	$uid = filter_input(INPUT_POST, 'uid', FILTER_SANITIZE_NUMBER_INT);
	$whichones = $_POST['whichones'];
	
	$dat = User::getById($uid)['data'];
	$genderref = array("male" => "his", "female" => "her", "asexual" => "its", "" => "their");
	$genderref2 = array("male" => "him", "female" => "her", "asexual" => "it", "" => "them");
	
	switch($whichones) {
	case "all": $query = "SELECT my.*, g.title_url FROM my_games my LEFT JOIN games g USING (gid) WHERE usrid='$uid' ORDER BY added ASC";
	break;
	case "own": $query = "SELECT my.*, g.title_url FROM my_games my LEFT JOIN games g USING (gid) WHERE usrid='$uid' AND my.own='1' ORDER BY added ASC";
	break;
	case "play": $query = "SELECT my.*, g.title_url FROM my_games my LEFT JOIN games g USING (gid) WHERE usrid='$uid' AND my.play='1' ORDER BY added ASC";
	break;
	case "play_online": $query = "SELECT my.*, g.title_url FROM my_games my LEFT JOIN games g USING (gid) WHERE usrid='$uid' AND my.play_online='1' ORDER BY added ASC";
	break;
	case "love": $query = "SELECT my.*, g.title_url FROM my_games my LEFT JOIN games g USING (gid) WHERE usrid='$uid' AND my.rating='2' ORDER BY added ASC";
	break;
	case "hate": $query = "SELECT my.*, g.title_url FROM my_games my LEFT JOIN games g USING (gid) WHERE usrid='$uid' AND my.rating='0' ORDER BY added ASC";
	break;
	}
	
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	if(!mysqli_num_rows($res)) {
		echo '<div style="padding:15px">'.$dat['username'].' hasn\'t put any of those games in '.$genderref[$dat['gender']].' box yet.</div>';
	} else {
		
		$i = 0;
		while($row = mysqli_fetch_assoc($res)) {
			
			if($i == 0) $rowstyle = "border-top-width:0;";
			else $rowstyle = "";
			
			$i++;
			if($i > 7) {
				?>
				</tr></table>
				</div>
				<?
				$i = 1;
			}
			if($i == 1) {
				?>
				<div class="row" style="background-position:-<?=rand(1,600)?>px 100%;<?=$rowstyle?>">
				<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr>
				<?
			}
			
			if($row['publication_id']) {
				$img = "/games/files/".$row['gid']."/".$row['gid']."-box-".$row['publication_id']."-tn.png";
				$q = "SELECT * FROM games_publications LEFT JOIN games_platforms USING (platform_id) WHERE id='".$row['publication_id']."' LIMIT 1";
				$x = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
				$row['title'] = $x->title;
				$row['platform'] = $x->platform;
			} elseif($row['platform_id']) {
				$img = "/bin/uploads/user_boxart/".$row['id']."_tn.png";
				$q = "SELECT * FROM games_platforms WHERE platform_id='".$row['platform_id']."' LIMIT 1";
				$x = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
				$row['platform'] = $x->platform;
			} else {
				$row['platform'] = "Unknown platform";
				$img = "";
			}
			if(!$img || !file_exists($_SERVER['DOCUMENT_ROOT'].$img)) {
				$img = "/bin/img/no_box.png";
			}
			
			$labels = "";
			if($row['rating'] == 2) {
				$labels.= '<img src="/bin/img/game-rate-love-label.png" alt="love" title="I love this game" border="0" class="tooltip"/>';
			} elseif($row['rating'] == '0') {
				$labels.= '<img src="/bin/img/game-rate-hate-label.png" alt="hate" title="I hate this game" border="0" class="tooltip"/>';
			}
			
			unset($playtitle);
			unset($stuff);
			$stuff = array();
			if($row['own']) {
				//$labels.= '<img src="/bin/img/gamebox-label-own.png" alt="own" title="I own this game" border="0" class="tooltip"/>';
				$stuff[] = "<b>owns</b>";
			}
			if($row['play']) {
				$playtitle = "I am currently playing this game";
				$stuff[] = "<b>plays</b>";
			}
			if($row['play_online']) {
				if($playtitle) $playtitle.= " and ";
				$playtitle.= "I play this game online";
				if($row['online_id']) $addid = ' ('.$genderref[$dat['gender']].' online ID is \''.$row['online_id'].'\' if you want to play with '.$genderref2[$dat['gender']].')';
				else $addid = "";
				$stuff[] = "<b>plays this game online</b>".$addid;
				$push = "";
			} else $push = " this game";
			if($num = count($stuff)) {
				$dostuff = $dat['username']." ";
				if($num == 1) $dostuff.= $stuff[0];
				elseif($num == 2) $dostuff.= implode(" and ", $stuff);
				elseif($num == 3) $dostuff.= "<b>owns</b>, <b>plays</b>, and <b>plays this game online</b>".$addid;
				$dostuff.= $push.".";
			} else $dostuff = "";
			if($playtitle) {
				$labels.= '<img src="/bin/img/gamebox-label-play.png" alt="play" title="'.$playtitle.'" border="0" class="tooltip"/>';
			}
			
			?>
			<td class="<?=$ratingclass?>">
				<div align="center">
					<div class="container">
						<a href="/games/~<?=$row['title_url']?>" title="<i><?=$row['title']?></i><br/><?=$row['platform']?><br/><?=$dostuff?>" class="tooltip"><div class="gamecover"><img src="<?=$img?>" border="0"/></div></a>
						<?=($labels ? '<div class="labels">'.$labels.'</div>' : '')?>
					</div>
				</div>
			</td>
			<?
			
		}
		
		if($i <= 7) {
			?>
			<td colspan="<?=(7 - $i)?>" width="100%" style="background:url(/bin/img/gamebox-spotlight-na.png) repeat-x 0 0 !important;">&nbsp;</td>
			</tr>
			</table>
			</div>
			<?
		}
		
	}
	
}

if($action == "find games") {
	
	$q = str_replace("[AMP]", "&", $_POST['query']);
	$q = htmlent($q); //cuz tites are saved to the db after going through this function
	if(!$pg = $_POST['pg']) $pg = 1;
	$max = 8;
	$min = ($pg - 1) * $max;
	$query = "SELECT gp.id, g.title_url, gp.gid, gp.title, gp.platform_id FROM games g, games_publications gp WHERE (g.title LIKE '%$q%' OR gp.title LIKE '%$q%') AND g.gid=gp.gid";
	$num = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query));
	if(!$num) {
		echo "No games found";
	} else {
		?>
		<div class="header">
			<div style="float:left">Found <?=$num?> games
			<?
			if($num > $max) {
				//pages
				$pgs = ceil($num / $max);
				echo '<span style="color:#808080">&middot;</span> Page '.$pg.' of '.$pgs.'</div>';
				if($pg == 1) echo '<span class="pg pgdn"><span>&lt;</span></span>';
				else echo '<a href="javascript:void(0)" class="pg pgdn" onclick="findGames('.($pg - 1).')"><span>&lt;</span></a>';
				if($pg == $pgs) echo '<span class="pg pgup"><span>&gt;</span></span>';
				else echo '<a href="javascript:void(0)" class="pg pgup" onclick="findGames('.($pg + 1).')"><span>&gt;</span></a>';
			} else echo '</div>';
			?>
		</div>
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<?
				
				$pfs = getPlatforms();
				
				$query.= " LIMIT $min, $max";
				$res = mysqli_query($GLOBALS['db']['link'], $query);
				while($row = mysqli_fetch_assoc($res)) {
					$defimg = "/games/files/".$row['gid']."/".$row['gid']."-box-".$row['id']."-tn.png";
					if(file_exists($_SERVER['DOCUMENT_ROOT'].$defimg)) $img = $defimg;
					else $img = "/bin/img/no_box.png";
					
					//in collection already?
					$q = "SELECT * FROM my_games WHERE publication_id='".$row['id']."' LIMIT 1";
					if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $has = TRUE;
					else $has = FALSE;
					
					?>
					<td>
						<?=($has ? '<div class="in-collection">in your collection<img src="/bin/img/black_point.png" alt="point"/></div>' : '')?>
						<div id="ag-row-<?=$row['id']?>" onmouseover="this.className='on';" onmouseout="this.className='';">
							<a href="javascript:void(0)" title="<i><?=htmlent($row['title'])?></i><br/><?=$pfs[$row['platform_id']]['platform']?>" class="tooltip" onclick="addFoundGame('<?=$row['id']."','".$row['gid']?>');"><img src="<?=$img?>" border="0" class="coverimg"/></a>
							<div id="tabs-<?=$row['id']?>" class="tabs">
								<a href="javascript:void(0)" title="Add to My Games" id="ag-tab-<?=$row['id']?>-add" class="tooltip" onclick="addFoundGame('<?=$row['id']."','".$row['gid']?>');"><img src="/bin/img/add_game_tab-plus.png" border="0"/></a>
								<a href="/games/~<?=$row['title_url']?>" title="View game overview" class="tooltip off" onmouseover="document.getElementById('ag-tab-<?=$row['id']?>-add').className='tooltip off'; this.className='tooltip';" onmouseout="document.getElementById('ag-tab-<?=$row['id']?>-add').className='tooltip'; this.className='tooltip off';"><img src="/bin/img/add_game_tab-look.png" border="0"/></a>
							</div>
						</div>
					</td>
					<?
				}
	}
	
}

if($action == "add found game") {
	
	if(!$pubid = $_POST['pubid']) exit;
	if(!$gid = $_POST['gid']) exit;
	$q = "INSERT INTO my_games (usrid, gid, publication_id, added) VALUES ('$usrid', '$gid', '$pubid', '".date("Y-m-d H:i:s")."');";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) die(mysqli_error($GLOBALS['db']['link']));
	else {
		$q = "SELECT title_url, gp.title, platform FROM games_publications gp LEFT JOIN games USING (gid) LEFT JOIN games_platforms USING (platform_id) WHERE gp.id='$pubid' LIMIT 1";
		$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
		
		$defimg = "/games/files/".$gid."/".$gid."-box-".$pubid."-tn.png";
		if(file_exists($_SERVER['DOCUMENT_ROOT'].$defimg)) $img = $defimg;
		else $img = "/bin/img/no_box.png";
		?>
		<div class="item">
			<div class="container">
				<div class="shadow">
					<a href="/games/~<?=$row['title_url']?>" title="<i><?=$row['title']?></i><br/><?=$row['platform']?>" class="tooltip"><div class="gamecover"><img src="<?=$img?>" border="0"/></div></a>
				</div>
			</div>
		</div>
		<?
	}
	
}

if($action == "edit my game form") {
	
	//$q = "SELECT pub.title, pub. FROM my_games mg LEFT JOIN games_publications pub ON (mg.publication_id=pub.id) LEFT JOIN games g USING (gid) LEFT JOIN games_platforms pf USING (platform_id) WHERE gp.id='".$_POST['id']."' LIMIT 1";
	$q = "SELECT mg.*, g.title_url, g.online, pf.platform FROM my_games mg LEFT JOIN games g USING (gid) LEFT JOIN games_platforms pf USING (platform_id) WHERE mg.id='".$_POST['id']."' LIMIT 1";
	if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
		die("Error: Couldn't get data for  publication id ".$_POST['id']." ".mysqli_error($GLOBALS['db']['link']));
	} else {
		
		if($dat->publication_id) {
			$img = "/games/files/".$dat->gid."/".$dat->gid."-box-".$dat->publication_id."-tn.png";
			$q = "SELECT * FROM games_publications LEFT JOIN games_platforms USING (platform_id) WHERE id='".$dat->publication_id."' LIMIT 1";
			$x = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
			$dat->title = $x->title;
			$dat->platform = $x->platform;
		} elseif($dat->platform_id) {
			$img = "/bin/uploads/user_boxart/".$dat->id."_tn.png";
		}
		
		?>
		<div class="header">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td class="cover-left"><img src="<?=$img?>" alt="<?=htmlent($dat->title)?>" class="coverimg"/></td>
					<td width="100%">
						<big><?=$dat->title?></big>
						<p class="platform">
							<?=$dat->platform?> <span style="color:#999">&middot;</span> 
							<span style="color:#8AC5FF">[<a href="/games/~<?=$dat->title_url?>">Game overview</a>]</span>
						</p>
						<p class="upload"><a href="javascript:void(0)">Upload a new cover image</a></p>
					</td>
					<td class="cover-right"><img src="<?=$img?>" alt="<?=htmlent($dat->title)?>" class="coverimg"/></td>
				</tr>
			</table>
		</div>
		
		<div class="leftcol">
			
			<div class="buttons">
				<input type="hidden" id="action-own" value="<?=$dat->own?>"/>
				<input type="hidden" id="action-play" value="<?=$dat->play?>"/>
				<input type="hidden" id="action-play_online" value="<?=$dat->play_online?>"/>
				<input type="hidden" id="online_id" value="<?=$dat->online_id?>"/>
				<table border="0" cellpadding="0" cellspacing="5" width="100">
					<tr>
						<td><a href="javascript:void(0)"<?=($dat->own ? ' class="on"' : '')?> id="action-own-button" onclick="setGameAction('own')">Own</a></td>
						<td><a href="javascript:void(0)"<?=($dat->play ? ' class="on"' : '')?> id="action-play-button" onclick="setGameAction('play')">Play</a></td>
					</tr>
					<?
					if($dat->online) {
						?>
						<tr>
							<td colspan="2"><a href="javascript:void(0)" id="action-play_online-button" class="<?=($dat->play_online ? 'on' : '')?>" onclick="setGameAction('play_online')">Play online</a></td>
						</tr>
						<?
					}
					?>
				</table>
			</div>
			
			<div class="ratings">
				<input type="hidden" id="game-rating" value="<?=$dat->rating?>"/>
				<a href="javascript:void(0)" id="game-rating-2" class="tooltip<?=($dat->rating == 2 ? ' on' : '')?>" title="Love it" onclick="setGameRating('2')" style="background-image:url(/bin/img/game-rate-love.png);"><span>Love it</span></a>
				<a href="javascript:void(0)" id="game-rating-0" class="tooltip<?=($dat->rating == '0' ? ' on' : '')?>" title="Hate it" onclick="setGameRating('0')" style="background-image:url(/bin/img/game-rate-hate.png);"><span>Hate it</span></a>
			</div>
			
		</div><!-- #leftcol -->
		
		<div class="rightcol">
			
			Tags: <br/>
			<?
			$tags = array();
			$query = "SELECT tag FROM my_games_tags WHERE my_games_id='$dat->id'";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)) {
				$tags[] = $row['tag'];
			}
			?>
			<textarea cols="30" rows="2"><?=implode(", ", $tags)?></textarea>
			
			<p>
				Notes:<br/>
				<textarea cols="30" rows="6"><?=$dat->notes?></textarea>
			</p>
			
		</div>
		
		<div class="submit">
			<a href="javascript:void(0)" class="submit"><span>Submit Changes</span></a>
			<a href="javascript:void(0)" class="delete"><span>Delete from My Games</span></a>
		</div>
		<?
	}
	
}