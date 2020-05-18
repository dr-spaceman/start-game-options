<?
// NAVIGATE: //
// --------- //
// NEW
// EDIT
// PEOPLE
// TRACKS
// RELATED GAMES
// RELATED ALBUMS
// SYNOPSIS
// MEDIA
// FACTOIDS & RETAILERS
// SAMPLES

use Vgsite\Page;
$page = new Page();
require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/admin.php");
use Verot\Upload;
require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.tags.php");

$rootpath = $_SERVER['DOCUMENT_ROOT'];
$albumpath = "music";

if($_SESSION['user_rank'] < 6) { include("../404.php"); exit; }

$page->javascripts[] = "/bin/script/albums-mgt.js";
$page->freestyle.= '
TABLE.styled-form {}
.styled-form FIELDSET { border:1px solid #CCC; }
.styled-form > THEAD > TH {
	padding-left:5px;
	color:#696969; }
.styled-form TR.selected > TD, .styled-form TR.selected > TH {
	background-color:#FFD2D2 !important; }
.styled-form > TR > TH, .styled-form > TBODY > TR > TH {
	width:11%;
	padding:10px 5px 10px 0;
	font-weight:bold;
	text-align:left;
	vertical-align:top;
	border-top:1px solid #CCC; }
.styled-form-alt > TR > TH, .styled-form-alt > TBODY > TR > TH {
	padding:15px 5px 15px 15px !important; }
.styled-form TH SMALL {
	font-weight:normal ! important;
	font-size:10px;
	color:#777; }
.styled-form > TR > TD, .styled-form > TBODY > TR > TD {
	padding:10px;
	border-top:1px solid #CCC; }
.styled-form P {
	margin:3px 0 0 0; }
.styled-form SMALL {
	font-size:10px;
	color:#808080; }
.styled-form input[type=text], .styled-form SELECT, .styled-form TEXTAREA {
	background-color:white !important;
	font-family:Arial;
	font-size:13px;
}
';

//get array of people -- $people
/*$query = "SELECT pid, name FROM people ORDER BY name";
$res   = mysqli_query($GLOBALS['db']['link'], $query);
while($row = mysqli_fetch_assoc($res)) {
	$people[] = array('pid' => $row['pid'], 'name' => $row['name']);
}*/

// $_GET & $_POST vars
if(!$action = $_GET['action']) $action = $_POST['action'];
if(!$step = $_GET['step']) $step = $_POST['step'];
if(!$editid = $_GET['editid']) $editid = $_POST['editid'];
$process = $_POST['process'];
$dbupdate = $_POST['dbupdate'];
$album1 = $_POST['album1'];
$alt = $_POST['alt'];
$substep = $_POST['substep'];
if(!$factedit = $_POST['factedit']) $factedit = $_GET['factedit'];
$yrsort = $_POST['yrsort'];
$mosort = $_POST['mosort'];
$daysort = $_POST['daysort'];

$page->title = "Videogam.in Admin / Albums";

require_once($_SERVER["DOCUMENT_ROOT"]."/ninadmin/album_rules.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/ninadmin/albums_edit.php");

if ($action == "new") {
	
	/////////
	// NEW //
	/////////
	
	if ($step != "1") $step = 1;
	
	if ($step == "1") {
		
		// STEP 1 //
		
		if ($albumerror) $errors[] = $albumerror;
		
		$page->title.= " / New Album";
		$page->header();
		
		unset($album1);
		
		$page->openSection();
		
		?>
		<h2>New Album Entry</h2>
		
		<form action="albums.php" method="post">
			<input type="hidden" name="action" value="<?=$action?>"/>
			<input type="hidden" name="step" value="1"/>
			<input type="hidden" name="process" value="1"/>
			<input type="hidden" name="dbupdate" value="1"/>
			
			<table border="0" cellpadding="0" cellspacing="0" class="styled-form">
				<tr>
					<th>Album title</th>
					<td>
						<input type="text" name="album1[0][title]" maxlength="255" id="input-title" style="width:250px"/></p>
					</td>
				</tr>
				<tr>
					<th>Album subtitle</th>
					<td><input type="text" name="album1[0][subtitle]" value="Original Soundtrack" maxlength="255" style="width:250px; font-style:italic;" onfocus="$(this).select();"/></td>
				</tr>
				<tr>
					<th>Catalog ID</th>
					<td><input type="text" name="album1[0][cid]" maxlength="255" onchange="document.getElementById('input-albumid').value=this.value;"/></td>
				</tr>
				<tr>
					<th>Album ID</th>
					<td>
						This value is <strong>required</strong> for database management. 
						<strong>It must be a truncated form of the catalog ID</strong>. 
						For example, if the catalog ID were <i>SSCX-10101~2</i>, then <i>SSCX-10101</i> should be entered for the album ID.
						<p><input type="text" name="album1[0][albumid]" maxlength="255" id="input-albumid"/></p>
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td style="font-size:15px;">
						<div style="display:none; margin:0 0 10px;">
							<select name="clone">
								<option value="">Clone an album...</option>
								<?
								$Query = "SELECT l.id, l.albumid, l.title, l.subtitle, l.datesort from albums as l order by title ASC, subtitle ASC";
								$Result = mysqli_query($GLOBALS['db']['link'], $Query);
								while ($Array = mysqli_fetch_assoc($Result)) {
									echo '<option value="'.$Array['albumid'].'">'.$Array['title'].' '.$Array['subtitle'].' ('.$Array['albumid'].'); '.$Array['datesort'].'</option>';
								}
								?>
							</select> &nbsp; 
							<a href="#" onclick="$(this).prev().val('').parent().hide();">cancel</a>
						</div>
						<input type="submit" name="create" value="Create the Entry" style="font:bold 15px;"/> 
						or <a href="#" onclick="$(this).siblings(':hidden').show();">clone an existing one</a>
					</td>
				</tr>
			</table>
		</form>
<?
	
	}
	
} elseif ($action == "edit") {
	
	//////////
	// EDIT //
	//////////

	$Query2 = "SELECT * from albums as l where albumid = '$editid' limit 1";
	$Result2 = mysqli_query($GLOBALS['db']['link'], $Query2);
	unset($album1);
	while ($Array2 = mysqli_fetch_assoc($Result2)) {
		$album1[] = $Array2;
	}
	
	$albumtitle = $album1[0][title];
	$albumsubtitle = $album1[0][subtitle];

	$page->title.= " / Edit / ".$albumtitle." ".$albumsubtitle;
	$page->header();
	
	$page->openSection();
	
	include("albums_nav.php");
	
	if ($dbupdate == "1" && $substep != "1") {
		if ($type == "new") {
			$results[] = "Album entry created; Please check and input general details below.";
		}
		elseif($updatemsg) {
			$results[] = $updatemsg;
		}
	}
	
	if ($step == "1") {
		
		// STEP 1 //
		
		unset($album1);
		$Query2 = "SELECT * from albums as l where albumid = '$editid'";
		$Result2 = mysqli_query($GLOBALS['db']['link'], $Query2);
		
		while ($Array2 = mysqli_fetch_assoc($Result2)) {
		$album1[] = $Array2;
		
		}
		
		if ($album1[0][coverimg] == 1) {
		$cimg1 = " selected";
		}
		else {
		$cimg2 = " selected";
		}
		
		if ($album1[0][jp] == 1) {
		$jp1 = " selected";
		}
		else {
		$jp2 = " selected";
		}
		
		if ($album1[0][media] == 1) {
		$media1 = " selected";
		}
		else {
		$media2 = " selected";
		}
		
		if ($album1[0][path] == 1) {
		$path1 = " selected";
		}
		else {
		$path2 = " selected";
		}
		
		if ($album1[0]["new"] == 1) {
		$new1 = " selected";
		}
		else {
		$new2 = " selected";
		}
		
		$comp1 = explode("|", $album1[0][compose]);
		$arng1 = explode("|", $album1[0][arrange]);
		$pfrm1 = explode("|", $album1[0][perform]);
		
		
		unset($editid);
		$editid = $album1[0][albumid];
		
		$date1 = explode("-", $album1[0][datesort]);
		
		$yrmax = date("Y");
		$yrmax = $yrmax + 2;
		
		for ($i=1980; $i < $yrmax; $i++) {
		if ($i == $date1[0]) {
		$yrlist[] = "<option value=\"$i\" selected=\"selected\">$i</option>\n";
		}
		else {
		$yrlist[] = "<option value=\"$i\">$i</option>\n";
		}
		}
		
		for ($i=1; $i < 32; $i++) {
		$j=$i;
		if ($i < 10) {
		$i = "0$i";
		}
		if ($i == "$date1[2]") {
		$daylist[] = "<option value=\"$i\" selected=\"selected\">$i</option>\n";
		}
		else {
		$daylist[] = "<option value=\"$i\">$i</option>\n";
		}
		$i=$j;
		}
		
		for ($i=1; $i < 13; $i++) {
		$j=$i;
		if ($i < 10) {
		$i = "0$i";
		}
		if ($i == $date1[1]) {
		$molist[] = "<option value=\"$i\" selected=\"selected\">$i</option>\n";
		}
		else {
		$molist[] = "<option value=\"$i\">$i</option>\n";
		}
		$i=$j;
		}
		
		$dateprnt = $date1[1]."/".$date1[2]."/".$date1[0];
		
		//ger create/edit times
		$i = 0;
		$q = "SELECT * FROM albums_changelog WHERE album='$editid' ORDER BY datetime DESC";
		$r = mysqli_query($GLOBALS['db']['link'], $q);
		while($row = mysqli_fetch_assoc($r)) {
			$i++;
			if($i == 1) {
				$mod_user = $row['usrid'];
				$mod_dt = $row['datetime'];
			}
			if($row['type'] == "new") {
				$cr_user = $row['usrid'];
				$cr_dt = $row['datetime'];
			}
		}
		
		?>
		
		<form action="albums.php" method="post">
			
			<input type="hidden" name="editid" value="<?=$editid?>"/>
			<input type="hidden" name="action" value="<?=$action?>"/>
			<input type="hidden" name="step" value="1"/>
			<input type="hidden" name="process" value="1"/>
			<input type="hidden" name="dbupdate" value="1"/>
			<input type="hidden" name="release" value="<?=$dateprnt?>"/>
			<input type="hidden" name="album1[0][id]" value="<?=$album1[0][id]?>"/>
			<input type="hidden" name="fin1" value="1"/>
			<input type="hidden" name="album1[0][albumid]" value="<?=$album1[0][albumid]?>"/>
			<input type="hidden" name="album1[0][release]" value="<?=$dateprnt?>"/>
			
			<div style="margin:0 0 5px 0; font-size:11px; text-align:right;">
				#<?=$album1[0]['id']?> / 
				Created on <?=$cr_dt?> by <?=outputUser($cr_user, FALSE)?> / 
				Edited <?=$i?> times, last on <?=$mod_dt?> by <?=outputUser($mod_user, FALSE)?>
			</div>
			<table border="0" cellpadding="0" cellspacing="0" class="styled-form">
				<tr>
					<th>Game title</th>
					<td>
						<input type="text" name="album1[0][title]" value="<?=$album1[0][title]?>" size="45" maxlength="255"/>
					</td>
				</tr>
				<tr>
					<th>Album subtitle</th>
					<td><input type="text" name="album1[0][subtitle]" value="<?=$album1[0][subtitle]?>" size="45" maxlength="255"/></td>
				</tr>
				<tr>
					<th>Keywords</th>
					<td>
						Include any keywords and phrases to facilitate a search query for this album.
						<p><textarea name="album1[0][keywords]" rows="2" cols="50"><?=$album1[0][keywords]?></textarea></p>
					</td>
				</tr>
				<tr>
					<th>Catalog ID</th>
					<td><input type="text" name="album1[0][cid]" value="<?=$album1[0][cid]?>" size="30" maxlength="255"/></td>
				</tr>
				<tr>
					<th>Album ID</th>
					<td>
						This value is <strong>required</strong> for database management. 
						<strong>It must be a truncated form of the catalog ID</strong>. 
						For example, if the catalog ID were <i>SSCX-10101~2</i>, then <i>SSCX-10101</i> should be entered for the album ID.
						<p><input type="text" name="album1[0][albumid]" value="<?=$album1[0][albumid]?>" maxlength="255" disabled="disabled" style="background-color:#EEE; color:#808080;"/></p>
					</td>
				</tr>
				<tr>
					<th>Publisher</th>
					<td><input type="text" name="album1[0][publisher]" value="<?=$album1[0][publisher]?>" size="30" maxlength="255"/></td>
				</tr>
				<tr>
					<th>Release Date</th>
					<td>
						Year:<select name="yrsort">
						<?
						arsort($yrlist);
						foreach ($yrlist as $a) {
							echo "$a";
						}
						?>
						</select> 
						Month:<select name="mosort">
							<option value="00">--</option>
							<?
						foreach ($molist as $a) {
							echo "$a";
						}
						?>
						</select> 
						Day:<select name="daysort">
							<option value="00">--</option>
							<?
						foreach ($daylist as $a) {
							echo "$a";
						}
						echo "</select>";
						?>
					</td>
				</tr>
				<tr>
					<th>Price</th>
					<td>
						<input type="text" name="album1[0][price]" value="<?=$album1[0][price]?>" id="input-price" maxlength="255"/> 
						<input type="button" value="Insert &yen; character" onclick="document.getElementById('input-price').value='&yen;'+document.getElementById('input-price').value;"/>
					</td>
				</tr>
				<tr>
					<th>Game Series</th>
					<td><input type="text" name="album1[0][series]" value="<?=$album1[0][series]?>" size="30" id="input-series"/></td>
				</tr>
				<tr>
					<th>Publish?</th>
					<td><label><input type="checkbox" name="album1[0][view]" value="1"<?=($album1[0][view] ? ' checked="checked"' : '')?>/> Yes, publish it and make it viewable</label></td>
				</tr>
				<tr>
					<th>Display...</th>
					<td>
						<p><label><input type="checkbox" name="album1[0][jp]" value="1"<?=($album1[0][jp] ? ' checked="checked"' : '')?>/> Japanese track list</label></p>
						<p><label><input type="checkbox" name="album1[0][media]" value="1"<?=($album1[0][media] ? ' checked="checked"' : '')?>/> Media</label></p>
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td>
						<input type="submit" name="pro" value="Save Changes" style="font-weight:bold;"/> &nbsp; 
						<?=($_SESSION['user_rank'] >= 8 || $cr_user == $usrid ? '<span class="redborder"><input type="button" value="Delete this Album" onclick="if(confirm(\'Permanently delete this album?\')) document.location=\'albums.php?delete_album='.$editid.'\';"/></span>' : '')?>
					</td>
				</tr>
			</table>
			
			<?
		
	}
	
	elseif ($step == "2") {
		
		////////////
		// PEOPLE //
		////////////
		
		if($select_work = $_POST['select_work']) {
			
			//a selection of work has been submitted
			
			if($_POST['edit_people_work']) {
				
				//EDIT FORM
				?>
				<fieldset>
					<legend>Editing People Associations</legend>
					
					<form action="albums.php" method="post">
						<input type="hidden" name="editid" value="<?=$editid?>"/>
						<input type="hidden" name="action" value="<?=$action?>"/>
						<input type="hidden" name="step" value="2"/>
						<input type="hidden" name="process" value="2"/>
						<input type="hidden" name="dbupdate" value="1"/>
						
						<table border="0" cellpadding="0" cellspacing="0" width="100%" id="album-people-list">
							<?
							
							foreach($select_work as $s) {
								list($table, $id) = explode("-", $s);
								$query = "SELECT * FROM ".($table == "people_work" ? 'people_work LEFT JOIN people USING (pid)' : $table)." WHERE id='$id' LIMIT 1";
								$res   = mysqli_query($GLOBALS['db']['link'], $query);
								while($p = mysqli_fetch_assoc($res)) {
									?>
									<input type="hidden" name="ids[]" value="<?=$s?>"/>
									<tr>
										<td colspan="4" width="100%">
											<?
											if($p['name_url']) {
												echo '<b><big><a href="/people/~'.$p['name_url'].'">'.$p['name'].'</a></big></b>';
											} else {
												?>
												<div id="manual-<?=$s?>">
													<input type="text" name="in[<?=$s?>][name]" value="<?=str_replace('"', '&quot;', $p['name'])?>" size="30"/>
												</div>
												
												<?
											}
											?>
										</td>
									</tr>
									<tr>
										<td class="last" valign="top"><b>Role:</b></td>
										<td class="last" valign="top">
											<input type="text" name="in[<?=$s?>][role]" value="<?=str_replace('"', '&quot;', $p['role'])?>" size="30"/>
											<p><label><input type="checkbox" name="in[<?=$s?>][vital]" value="1" <?=($p['vital'] ? ' checked="checked"' : '')?>/> Vital contributor to this album</label></p>
										</td>
										<td class="last" valign="top"><b>Notes:</b></td>
										<td class="last" valign="top">
											<textarea name="in[<?=$s?>][notes]" rows="2" cols="35"><?=str_replace('"', '&quot;', $p['notes'])?></textarea>
										</td>
									</tr>
									<?
								}
							}
						
						?>
						</table>
						<input type="submit" name="submit_edit_people_work" value="Submit Changes" style="margin-top:5px"/>
					</form>
				</fieldset>
				<?
				
				$page->footer();
				exit;
				
			} 
			
		}
		
		?>
		<form action="albums.php" method="post">
			<input type="hidden" name="editid" value="<?=$editid?>"/>
			<input type="hidden" name="action" value="<?=$action?>"/>
			<input type="hidden" name="step" value="2"/>
			<input type="hidden" name="process" value="2"/>
			<input type="hidden" name="dbupdate" value="1"/>
			<fieldset>
				<legend>Add a Name</legend>
				<table border="0" cellpadding="0" cellspacing="0" width="100%" class="styled-form">
					<tr>
						<th>Who?</th>
						<td>
							<input type="text" name="name" placeholder="Start typing to find a name..." id="name" class="autocomplete-name resetonfocus" style="width:250px"/>
							<!--
							<label><input type="radio" name="select-who" value="pid" checked="checked" onclick="toggle('who-db','who-manual'); document.getElementById('name').value='';"/> A person from the <a href="people.php">People Database</a></label>
							<div id="who-db"><p>
								<select name="pid" id="pid">
									<option value="">Select a person...</option>
									<?
									$res = mysqli_query($GLOBALS['db']['link'], "SELECT pid, name, title, prolific FROM people ORDER BY name");
									while($row = mysqli_fetch_assoc($res)) {
										echo '<option value="'.$row['pid'].'"'.($row['prolific'] ? ' style="font-weight:bold"' : '').'>'.$row['name'].' ('.$row['title'].')</option>'."\n";
									}
									?>
								</select>
							</p></div>
							<p><label><input type="radio" name="select-who" value="name" onclick="toggle('who-manual','who-db'); document.getElementById('pid').value='';"/> Manually input a name or group not in the People Database</label></p>
							<div id="who-manual" style="display:none"><p>
								<label>Name: </label>
								<p><label><input type="checkbox" name="add_person_to_db" value="1"/> Add this person to the People Database (recommended if the person is even <i>somewhat</i> known)</label></p>
							</p></div>
							-->
						</td>
					</tr>
					<tr>
						<th>Role</th>
						<td>
							The person's or group's role in the creation of the album or how they are credited
							<p><input type="text" name="role" id="role" style="width:250px"/></p>
							<p><label><input type="checkbox" name="vital" value="1" id="vital"/> This person's role was relatively vital to the creation of this album</label></p>
						</td>
					</tr>
					<tr>
						<th>Notes</th>
						<td>
							Any notes or clarifications about this person's role in the album
							<p><textarea name="notes" id="notes" rows="2" cols="65"></textarea></p>
						</td>
					</tr>
					<tr>
						<th>&nbsp;</th>
						<td><input type="submit" name="add_people_work" value="Add Name"/></td>
					</tr>
				</table>
			</fieldset>
		</form>
		
		<br/>
		
		<fieldset>
			<legend>Current Names</legend>
			
			<?
			unset($people);
			
			$Query3 = "SELECT * FROM people_work LEFT JOIN people USING (pid) WHERE people_work.albumid='$editid'";
			$Result3 = mysqli_query($GLOBALS['db']['link'], $Query3);
			while($row = mysqli_fetch_assoc($Result3)) {
				$people[] = $row;
			}
			
			$Query3 = "SELECT * FROM albums_other_people WHERE albumid='$editid'";
			$Result3 = mysqli_query($GLOBALS['db']['link'], $Query3);
			while($row = mysqli_fetch_assoc($Result3)) {
				$people[] = $row;
			}
			
			if(!$people) {
				echo "No people have been credited or associated with this album yet.";
			} else {
				?>
				The following people or groups are associated with this album already:
				
				<form action="albums.php" method="post" name="editpeopleform">
					<input type="hidden" name="editid" value="<?=$editid?>"/>
					<input type="hidden" name="action" value="<?=$action?>"/>
					<input type="hidden" name="step" value="2"/>
					<input type="hidden" name="process" value="2"/>
					<input type="hidden" name="dbupdate" value="1"/>
					
					<table border="0" cellpadding="3" cellspacing="0" width="100%" id="album-people-list">
						<?
						foreach($people as $p) {
							if($p['pid']) $row_id = 'people_work-'.$p['id'];
							else $row_id = 'albums_other_people-'.$p['id'];
							?>
							<tbody id="<?=$row_id?>">
								<tr>
									<td rowspan="2" valign="top">
										<input type="checkbox" name="select_work[]" value="<?=$row_id?>" onclick="if(this.checked == true) document.getElementById('<?=$row_id?>').className='selected'; else document.getElementById('<?=$row_id?>').className='';"/>
									</td>
									<td width="100%">
										<b><a href="/people/<?=formatNameURL($p['name'])?>"><?=$p['name']?></a></b> <?=($p['vital'] ? '<i>vital</i>' : '')?> (<?=$p['role']?>)
									</td>
								</tr>
								<tr>
									<td><b>Notes:</b> <?=bb2html($p['notes'])?></td>
								</tr>
							</tbody>
							<?
						}
						?>
						<tr>
							<td style="text-align:center"><img src="/bin/img/arrow-down-right.png" alt="arrow"/></td>
							<td colspan="5">
								With selected: 
								<input type="submit" name="edit_people_work" value="Edit details"/>
								<input type="submit" name="delete_people_work" value="Remove association with album"/>
							</td>
						</tr>
					</table>
				</form>
				<?
			}
			
			?>
		</fieldset>
	<?
	}
	
	elseif ($step == "3") {
		
		////////////
		// TRACKS //
		////////////
		
		$query = "SELECT * FROM albums_tracks WHERE albumid='$editid'";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		if($total_tracknum = mysqli_num_rows($res)) {
			$query2 = "SELECT DISTINCT(disc) FROM albums_tracks WHERE albumid='$editid' ORDER BY disc";
			$res2   = mysqli_query($GLOBALS['db']['link'], $query2);
			while($row = mysqli_fetch_assoc($res2)) {
				$discs[] = $row['disc'];
			}
		}
		
		if($discs) {
			foreach($discs as $disc) {
				$q = "SELECT * FROM albums_tracks WHERE albumid='$editid' AND disc='$disc'";
				$tracknum[$disc] = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q));
			}
		}
		
		if(!$do = $_GET['do']) {
			if(!$total_tracknum) $do = "add";
			else $do = "edit";
		}
		
		?>
		<ul class="tabbed-nav">
			<li<?=($do == "add" ? ' class="on"' : '')?>><a href="albums.php?step=3&action=edit&editid=<?=$editid?>&do=add"><b>Add Tracks</b></a></li>
			<li<?=($do == "edit" ? ' class="on"' : '')?>><a href="albums.php?step=3&action=edit&editid=<?=$editid?>&do=edit"><b>Edit Discs & Tracks</b></a></li>
			<li<?=($do == "samples" ? ' class="on"' : '')?>><a href="albums.php?step=3&action=edit&editid=<?=$editid?>&do=samples"><b>Samples</b></a></li>
			<li<?=($do == "credits" ? ' class="on"' : '')?>><a href="albums.php?step=3&action=edit&editid=<?=$editid?>&do=credits"><b>Credits</b></a></li>
		</ul>
		
		<br/><br/>
		
		<div id="rules-link" style="margin:15px 0; padding:5px; border:1px dotted #CCC;">Please see <a href="#x" onclick="toggle('rules-show', 'rules-link');" class="arrow-right">track list rules</a></div>
		
		<fieldset id="rules-show" style="display:none; margin:15px 0;">
			<legend>Track List Rules <small style="font-weight:normal">(<a href="#x" onclick="toggle('rules-link', 'rules-show');">hide</a>)</small></legend>
			If something is hard to classify, please leave it blank.<br/><br/>
			
			<strong>ARTIST<br/></strong>
			<div style="padding: 0px 0px 0px 20px;">
			The point of this is to give credit to individual tracks.<br/><br/>
			
			If the <strong>set of arrangers</strong> is the same as the <strong>set of composers</strong>, put down just the composer with 
			the assumption that the composer and arranger are the same.<br/><br/>
			
			If the <strong>set of arrangers</strong> is <strong>NOT</strong> the same as the <strong>set of composers</strong>, we have a 
			sticky situation.  Put the original composer <strong>in SQUARE brackets in the TRACK NAME field</strong>.  Put the arranger in 
			the ARTIST field.  <strong>LAST NAMES only, please</strong>.  This is especially important when you have two or more names in 
			this field.<br/><br/>
			
			<strong>All vocal performers go in parentheses</strong> after arrangers and composers.<br/><br/>
			
			Here's <a href="/music/?id=DPCX-5019" target="_blank">an example</a> of how it should be.<br/><br/></div>
			
			<strong>TYPE<br/></strong>
			<div style="padding: 0px 0px 0px 20px;">Enter a <strong>GENERIC</strong> description.  We want to give an idea, not write 
				commentary.  These standard descriptions are <strong>HIGHLY RECOMMENDED</strong> (read: do it or die!):<br/><br/>
			
			<strong>Field </strong> => Location<br/>
			<strong>Event</strong> => (sequence, opening movie, whatever)<br/>
			<strong>Theme</strong> => Theme (any songs with lyrics, opening themes, game themes that <strong>transcend</strong> game events, meaning they represent the game itself...  For example, "Tidus and Yuna swimming in the lake" CG is NOT an in-game event, but an excuse to play Suteki da ne!)<br/>
			<strong>Character</strong> => Character (character theme)<br/>
			<strong>Battle</strong> => Regular battle<br/>
			<strong>Boss battle</strong> => Boss battle<br/>
			<strong>Fanfare</strong> => (victory! or "game over," which is technically not a fanfare, but whatever, etc.)<br/>
			<strong>Ending</strong> => Anything ending-related<br/><br/>
			<strong>Medley</strong> => A medley of different tracks.  The medley itself is not played in the original game.<br/><br/>
			
			<strong>If TYPE doesn't really apply, especially to bonus tracks</strong>, put the appropriate description in parentheses. 
			(Bonus track) for bonus track, etc., (Unused track) for a track not used in the game, etc....<br/><br/>
			
			If the album features <strong>music from several games</strong>, you should include the game name here as well, preferably 
			abbreviated (i.e., FFVII instead of Final Fantasy VII) in parentheses after the type description.<br/><br/>
			
			Do not make meaningless specifications.  Most people can figure out that "boss battle" music is more "intense" than the 
			regular music.  But "Story boss battle" or "major boss battle" doesn't mean anything if you don't include anything for...<br/><br/></div>
			
			<strong>IN-GAME LOCATION<br/></strong>
			<div style="padding: 0px 0px 0px 20px;">This specifies where exactly the music is played <strong>in the game in question</strong> 
				(if played a lot, where most frequently or what kind of SPECIFIC situation).  Of course, if it is even necessary to ask that 
				question, you need to <strong>put the game title in the TYPE field</strong> (read above for more).  If it's a "game over" 
				theme, put in "Game over."  Attack on Dollet?  "Attack on Dollet."  Capitalize the first word, not all of it. Don't put 
				anything in for regular battle themes that are played throughout the game (unless it varies based on where you are in the game). 
				 Ask if you need more clarification.<br/><br/>
			
			If you can't put something in specific (like a location name), yet want to put in SOMETHING, enclose a general description in 
			parentheses.  Use your own discretion.<br/><br/>
			
			I don't think we have to be too specific about it...<br/><br/></div>
			
			<strong>PLEASE DO NOT CAPITALIZE THESE KEYWORDS</strong>.  It looks dorky.<br/><br/>
			
			<span style="color:#808080">&lt; <a href="#x" onclick="toggle('rules-link', 'rules-show');">Hide Rules</a></span>
		</fieldset>
		<?
		
		if($do == "add") {
			
			// ADD TRACKS //
			
			// the form will be submitted for real when tracks are added en masse
			// individual tracks don't use form submission -- they use javascript
			?>
			<form action="albums.php" method="post" name="addtracksform">
				<input type="hidden" name="editid" value="<?=$editid?>"/>
				<input type="hidden" name="action" value="<?=$action?>"/>
				<input type="hidden" name="step" value="3"/>
				<input type="hidden" name="dbupdate" value="1"/>
				<input type="hidden" name="do" value="mass_add_tracks"/>
				<fieldset>
					<legend>Set Parameters</legend>
					<table border="0" cellpadding="0" cellspacing="0" width="100%" class="styled-form">
						<tr>
							<th>Disc Name</th>
							<td>
								<?
								if($discs) {
									?>
									<select onchange="document.getElementById('input-disc').value=this.options[this.selectedIndex].value" style="margin-bottom:5px">
										<option value="">Select from a currently listed disc...</option>
										<?
										foreach($discs as $disc) {
											echo '<option value="'.$disc.'">'.$disc.' ('.($tracknum[$disc] ? $tracknum[$disc] : '0').' tracks listed)</option>';
										}
										?>
									</select><br/>
									or input manually: 
									<?
								}
								?>
								<input type="text" name="disc"<?=(!$discs ? ' value="Disc 1"' : '')?> id="input-disc"/>
							</td>
						</tr>
						<tr>
							<th>Input Type</th>
							<td>
								<label><input type="radio" name="input_type" value="individual" onclick="toggle('input-individual', 'input-mass');"/> individual tracks</label>
								<p><label><input type="radio" name="input_type" value="mass" onclick="toggle('input-mass', 'input-individual');"/> en masse (copy/paste)</label></p>
							</td>
						</tr>
					</table>
				</fieldset>
				
				<br/>
				
				<fieldset id="input-individual" style="display:none">
					<legend>Add Individual Tracks</legend>
					<table border="0" cellpadding="0" cellspacing="0" class="styled-form">
						<tr>
							<th>Track Name</th>
							<td><input type="text" name="track_name" size="40" id="input-track_name"/></td>
						</tr>
						<tr>
							<th>Track Number<br/><small>This track name's track number (not total tracks)</small></th>
							<td>
								<?=($discs ? '<div style="margin-bottom:5px">This disc currently has <b id="num-disc-tracks"></b> tracks.</div>' : '')?>
								<input type="text" name="track_number" value="1" size="3" id="input-track_number"/>
							</td>
						</tr>
						<tr>
							<th>Artist</th>
							<td>
								<a href="#x" id="help-artist-click" onclick="toggle('help-artist', 'help-artist-click')" class="arrow-right">about this field</a>
								<div id="help-artist" style="display:none">
									The point of this is to give credit to individual tracks.<br/><br/>
									If the <strong>set of arrangers</strong> is the same as the <strong>set of composers</strong>, put down just the composer with the assumption that the composer and arranger are the same.<br/><br/>
									If the <strong>set of arrangers</strong> is <strong>NOT</strong> the same as the <strong>set of composers</strong>, we have a sticky situation.  Put the original composer <strong>in SQUARE brackets in the TRACK NAME field</strong>.  Put the arranger in the ARTIST field.  <strong>LAST NAMES only, please</strong>.  This is especially important when you have two or more names in this field.<br/><br/>
									<strong>All vocal performers go in parentheses</strong> after arrangers and composers.<br/><br/>
									Here's <a href="/music/?id=DPCX-5019" target="_blank">an example</a> of how it should be.<br/>
									<a href="#x" onclick="toggle('help-artist-click', 'help-artist', 'inline')" class="arrow-left">hide</a>
								</div>
								<?
								$Query3 = "SELECT name FROM people_work LEFT JOIN people USING (pid) WHERE people_work.albumid='$editid'";
								$Result3 = mysqli_query($GLOBALS['db']['link'], $Query3);
								while($row = mysqli_fetch_assoc($Result3)) {
									$names[] = $row['name'];
								}
								$Query3 = "SELECT name FROM albums_other_people WHERE albumid='$editid'";
								$Result3 = mysqli_query($GLOBALS['db']['link'], $Query3);
								while($row = mysqli_fetch_assoc($Result3)) {
									$names[] = $row['name'];
								}
								if($names) {
									?>
									<p><select id="select-artist" onchange="document.getElementById('input-artist').value=this.options[this.selectedIndex].value">
										<option value="">Select a person associated with this album...</option>
										<?
										foreach($names as $p) {
											echo '<option value="'.$p.'">'.$p.'</option>'."\n";
										}
										?>
									</select></p>
									<?
									$inp_words = "or input: ";
								}
								?><p><?=$inp_words?><input type="text" name="artist" size="30" id="input-artist"/></p>
							</td>
						</tr>
						<tr>
							<th>Track Type</th>
							<td>
								<a href="#x" id="help-type-click" onclick="toggle('help-type', 'help-type-click')" class="arrow-right">about this field</a>
								<p id="help-type" style="display:none">
									Enter a <strong>GENERIC</strong> description.  We want to give an idea, not write commentary.  These standard descriptions are <strong>HIGHLY RECOMMENDED</strong> (read: do it or die!):<br/><br/>
									<strong>Field </strong> => Location<br/>
									<strong>Event</strong> => (sequence, opening movie, whatever)<br/>
									<strong>Theme</strong> => Theme (any songs with lyrics, opening themes, game themes that <strong>transcend</strong> game events, meaning they represent the game itself...  For example, "Tidus and Yuna swimming in the lake" CG is NOT an in-game event, but an excuse to play Suteki da ne!)<br/>
									<strong>Character</strong> => Character (character theme)<br/>
									<strong>Battle</strong> => Regular battle<br/>
									<strong>Boss battle</strong> => Boss battle<br/>
									<strong>Fanfare</strong> => (victory! or "game over," which is technically not a fanfare, but whatever, etc.)<br/>
									<strong>Ending</strong> => Anything ending-related<br/>
									<strong>Medley</strong> => A medley of different tracks.  The medley itself is not played in the original game.<br/><br/>
									<strong>If TYPE doesn't really apply, especially to bonus tracks</strong>, put the appropriate description in parentheses. (Bonus track) for bonus track, etc., (Unused track) for a track not used in the game, etc....<br/><br/>
									If the album features <strong>music from several games</strong>, you should include the game name here as well, preferably abbreviated (i.e., FFVII instead of Final Fantasy VII) in parentheses after the type description.<br/><br/>
									Do not make meaningless specifications.  Most people can figure out that "boss battle" music is more "intense" than the regular music.  But "Story boss battle" or "major boss battle" doesn't mean anything if you don't include anything for...<br/>
									<a href="#x" onclick="toggle('help-type-click', 'help-type', 'inline')" class="arrow-left">hide</a>
								</p>
								<p><select id="select-type" onchange="document.getElementById('input-type').value=this.options[this.selectedIndex].value">
									<option value="">Select a standard description...</option>
									<option value="Field">Field </option>
									<option value="Event">Event</option>
									<option value="Theme">Theme</option>
									<option value="Character">Character</option>
									<option value="Battle">Battle</option>
									<option value="Boss Battle">Boss battle</option>
									<option value="Fanfare">Fanfare</option>
									<option value="Ending">Ending</option>
									<option value="Medley">Medley</option>
								</select></p>
								<p>or input: <input type="text" name="type" size="30" id="input-type"/></p>
							</td>
						</tr>
						<tr>
							<th>In-Game Location</th>
							<td><a href="#x" id="help-loc-click" onclick="toggle('help-loc', 'help-loc-click')" class="arrow-right">about this field</a>
								<p id="help-loc" style="display:none">
									This specifies where exactly the music is played <strong>in the game in question</strong> (if played a lot, where most frequently or what kind of SPECIFIC situation).  Of course, if it is even necessary to ask that question, you need to <strong>put the game title in the TYPE field</strong> (read above for more).  If it's a "game over" theme, put in "Game over."  Attack on Dollet?  "Attack on Dollet."  Capitalize the first word, not all of it. Don't put anything in for regular battle themes that are played throughout the game (unless it varies based on where you are in the game).<br/>
									<a href="#x" onclick="toggle('help-loc-click', 'help-loc', 'inline')" class="arrow-left">hide</a>
								</p>
								<p><input type="text" name="location" size="30" id="input-location"/></td>
						</tr>
						<tr>
							<th>Time</th>
							<td><input type="text" name="time" size="4" id="input-time"/></td>
						</tr>
						<tr>
							<th>&nbsp;</th>
							<td>
								<input type="hidden" name="albumid" value="<?=$editid?>" id="albumid"/>
								<input type="button" value="Add Track" id="add-track-submit-button" onclick="addTrack()" style="font-weight:bold"/> 
								<img src="/bin/img/loading-arrows-small.gif" alt="loading" id="add-track-loading" style="display:none"/> 
								<span id="add-track-result"></span>
							</td>
						</tr>
					</table>
				</fieldset>
				
				<fieldset id="input-mass" style="display:none">
					<legend>Mass-Add Tracks</legend>
					Use this field to paste a complete list of tracks.
					<p>Include a track name on each corresponding line. The track name on line one will be set as track #1, and so on.
					You can optionally include the track time after the track name by separating it with at least one space or tab-space 
					(it can include multiple of either and will be reformatted automatically after submission). Track numbers in the beginning 
					of the title will <b>probably</b> be depreciated. Here is an example of the preferred input:<br/>
					<blockquote><code>The Moon and the Prince 5:30</code></blockquote><p>
					<p class="warn"><b><i>This field can't be used if there are already tracks listed for a disc.</b></i> 
						To use this field for an existing disc, delete all tracks for that disc.</p>
					<p></p>
					<div style="margin-right:6px;"><textarea name="mass_tracklist" rows="25" style="width:100%;"></textarea>
					<p><input type="button" name="submit_mass" value="Submit Track List" onclick="document.addtracksform.submit()" style="font-weight:bold"/></p>
				</fieldset>
				
			</form>
			<?
			
		} elseif($do == "edit") {
			
			// EDIT TRACKS //
			
			if(!$total_tracknum) {
				?>No tracks added yet.<?
			} else {
				?>
				<form action="albums.php" method="post">
					<input type="hidden" name="editid" value="<?=$editid?>"/>
					<input type="hidden" name="action" value="<?=$action?>"/>
					<input type="hidden" name="step" value="3"/>
					<input type="hidden" name="dbupdate" value="1"/>
					<input type="hidden" name="do" value="edit_tracks"/>
					<?
					$query = "SELECT * FROM albums_tracks LEFT JOIN albums_samples ON (albums_tracks.id=albums_samples.track_id) WHERE albums_tracks.albumid='$editid' ORDER BY disc, track_number";
					$res   = mysqli_query($GLOBALS['db']['link'], $query);
					$i = 0;
					while($row = mysqli_fetch_assoc($res)) {
						$this_disc = $row['disc'];
						if($c_disc != $this_disc) {
							$c_disc = $this_disc;
							if($i >= 1) echo "</table><br/>\n\n";
							?>
							<table cellpadding="5" cellspacing="0" border="0" class="albums-track-list">
								<tr>
									<td colspan="7">
										<input type="hidden" name="old_disc_names[]" value="<?=$row['disc']?>"/>
										<input type="text" name="new_disc_names[]" value="<?=$row['disc']?>" style="font-size:150%;"/>
									</td>
								</tr>
								<tr>
									<th>&nbsp;</th>
									<th>Track Name</th>
									<th>Artist</th>
									<th>Type</th>
									<th>Location</th>
									<th>Time</th>
									<th><label><input type="checkbox" name="" value="" onclick="if($(this).is(':checked')){ $('.chtrack<?=($i + 1)?>').attr('checked', true); $(this).closest('tr').siblings().addClass('selected'); } else { $('.chtrack<?=($i + 1)?>').attr('checked', false); $(this).closest('tr').siblings().removeClass('selected'); }"/> All</label></th>
								</tr>
								<?
							$i++;
						}
						$j++;
						if($j % 2) $class = "even";
						else $class = "";
						?>
						<tr class="<?=$class?>" id="row-<?=$j?>">
							<td><?=$row['track_number']?></td>
							<td>
								<input type="hidden" name="in[<?=$row['id']?>][id]" value="<?=$row['id']?>"/>
								<input type="text" name="in[<?=$row['id']?>][track_name]" value="<?=$row['track_name']?>" size="27"/>
								<input type="hidden" name="in[<?=$row['id']?>][has_sample]" value="<?=($row['file'] ? '1' : '')?>"/>
							</td>
							<td><input type="text" name="in[<?=$row['id']?>][artist]" value="<?=$row['artist']?>" size="18"/></td>
							<td><input type="text" name="in[<?=$row['id']?>][type]" value="<?=$row['type']?>" size="9"/></td>
							<td><input type="text" name="in[<?=$row['id']?>][location]" value="<?=$row['location']?>" size="13"/></td>
							<td><input type="text" name="in[<?=$row['id']?>][time]" value="<?=$row['time']?>" size="5"/></td>
							<td align="center">
								<label>
								<input type="checkbox" name="delete[]" value="<?=$row['id']?>" class="chtrack<?=$i?>" onclick="$('#row-<?=$j?>').toggleClass('selected');
									<?=($row['file'] ? 'if(this.checked == true) alert(\'Warning: Deleting this track will also delete the uploaded music sample\');' : '')?>"/> 
									delete
								</label>
							</td>
						</tr>
						<?
					}
				
				?>
				</table><br/>
				
				<input type="submit" name="submit_edits" value="Submit All Changes" style="font-size:150%;"/>
			</form>
			<?
				
			}
			
		} elseif($do == "samples") {
			
			// SAMPLES //
			
			unset($tracklist);
			$query = "SELECT * FROM albums_tracks LEFT JOIN albums_samples ON (albums_tracks.id=albums_samples.track_id) WHERE albums_tracks.albumid='$editid' ORDER BY disc, track_number";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)) {
				$tracklist[] = $row;
				if($row['file']) $samplenum++;
			}
			
			if(!$tracklist) {
				?>No tracks added yet.<?
			} else {
				
				?>
				Samples can be easily uploaded and tagged via <a href="/posts/manage.php?action=newpost">Sblog</a>.
				<?
				
			}
		
		} elseif($do == "credits") {
			
			// CREDITS SOURCES //
			
			?>
			Only use these fields if you don't feel like submitting your own translations. 
			It's much better (for credibility) to use a bunch of sources than to take crappy translations from only one source, so please cite everyone you use.
			<br/><br/>
			
			<fieldset>
					<legend>Current Sources</legend>
			<?
			
			$query = "SELECT * FROM albums_credits WHERE albumid='$editid' AND conttype='track'";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			if(mysqli_num_rows($res)) {
				?>
				<ul>
					<?
					while($row = mysqli_fetch_assoc($res)) {
						echo '<li>'.$row['source'];
						if($row['address']) echo ' <span style="color:#808080;">&lt;</span>'.$row['address'].'<span style="color:#808080;">&gt;</span>';
						echo ' <a href="?step=3&action=edit&editid='.$editid.'&do=credits&dbupdate=1&delete_credit='.$row['id'].'" class="x" title="delete">X</a></li>'."\n";
					}
					?>
				</ul>
				<?
			} else echo "No sources credited for this album.";
			
			?>
			</fieldset>
			
			<br/>
			
			<form action="albums.php?do=credits" method="post">
				<input type="hidden" name="editid" value="<?=$editid?>"/>
				<input type="hidden" name="action" value="<?=$action?>"/>
				<input type="hidden" name="step" value="3"/>
				<input type="hidden" name="dbupdate" value="1"/>
				
				<fieldset>
					<legend>Add a Source</legend>
					<table border="0" cellspacing="0" class="styled-form">
						<tr>
							<th>Name of Source/Person</th>
							<td><input type="text" name="source" size="30"/></td>
						</tr>
						<tr>
							<th>URL or E-mail Address</th>
							<td>
								Please input either a URL or valid e-mail address only
								<p><input type="text" name="address" size="30"/></p>
							</td>
						</tr>
						<tr>
							<th>&nbsp;</th>
							<td><input type="submit" name="add_track_source" value="Add Source"/></td>
						</tr>
					</table>
				</fieldset>
			</form>
			<?
			
		}
		
	} elseif ($step == "4") {
		
		///////////////////
		// RELATED GAMES //
		///////////////////
		
		$_tags = new tags("albums_tags:albumid:".$editid);
		$_tags->allow_add = TRUE;
		$_tags->allow_rm = TRUE;
		
		?>
		
		<big>To add a game, click <b>+ Add a Tag</b> below. Please select only the games directly related to this album.<br/></big>
		<span style="color:#888;"><span class="arrow-right"></span>&nbsp; For example, for <a href="/music/?id=PSCN-5006">Final Fantasy <i>Vocal Collections</i></a>, a collection of Final Fantasy music, select all the corresponding Final Fantasys whose music appears on the album.</span>
		
		<p></p>
			
		<div class="taglist" style="font-size:15px;">
			<?
			echo $_tags->taglist();
			echo $_tags->suggestForm();
			?>
		</div>
		<?
		
	} elseif ($step == "5") {
		
		////////////////////
		// RELATED ALBUMS //
		////////////////////
		
		if ($substep == "1" && $album5[0][related]) {
		
			for ($i=0; $i < count($album5); $i++) {
				if ($i==0) {
				$qstring = "where l.albumid = '{$album5[$i][related]}'";
				}
				else {
				$qstring .= " or l.albumid = '{$album5[$i][related]}'";
				}
			}
		
			$Query = "SELECT l.id, l.albumid, l.title, l.subtitle, l.datesort from albums as l $qstring order by title ASC, subtitle ASC";
			$Result = mysqli_query($GLOBALS['db']['link'], $Query);
			$cgames = mysqli_num_rows($Result);
		
			$j=0;
			while ($Array = mysqli_fetch_assoc($Result)) {
				$glist[$j] = "<tr>\n
				<td width=\"70%\" style=\"font-size: 8pt; font-family: Verdana;\"$bglist[$j]>$Array[title] <em>$Array[subtitle]</em>	<br/>($Array[albumid]); $Array[datesort]<input type=\"hidden\" name=\"album5[$j][related]\" value=\"$Array[albumid]\">	</td>\n
				<td width=\"30%\" style=\"font-size: 8pt; font-family: Verdana;\"$bglist[$j]>\n
				<select name=\"album5[$j][type]\">\n
				<option value=\"1\">General similar</option>\n
				<option value=\"2\">Same game</option>\n
				<option value=\"3\">Same composer(s)</option>\n
				<option value=\"4\">Same series</option>\n
				<option value=\"5\">Other album editions</option>\n
				</select></td>\n
				</tr>\n\n";
				$j++;
			}
		
			// array end
			
			?>
			
			<?=$step5msg?>
			
			<form action="albums.php" method="post">
			<input type="hidden" name="editid" value="<?=$editid?>">
			<input type="hidden" name="action" value="<?=$action?>">
			<input type="hidden" name="step" value="5">
			<input type="hidden" name="process" value="5">
			<input type="hidden" name="dbupdate" value="1">
			<table border="0" width="100%">
			<?
			
			
			foreach ($glist as $a) {
			echo "$a";
			}
			
			
			?>
			</table>
			<br/>
			<input type="submit" name="pro" value="Save"/></form>
			<?
			unset($album5);
		
		} else {
		
			$Query = "SELECT l.id, l.albumid, l.title, l.subtitle, l.datesort from albums as l where albumid != '$editid' order by title ASC, subtitle ASC";
			$Result = mysqli_query($GLOBALS['db']['link'], $Query);
			$cgames = mysqli_num_rows($Result);
			
			$Query2 = "SELECT r.type, r.album, r.related from albums_related as r where r.album = '$editid'";
			$Result2 = mysqli_query($GLOBALS['db']['link'], $Query2);
			$cgs = 0;
			$sel = array();
			while($row = mysqli_fetch_assoc($Result2)) {
				$cgs++;
				$sel[$row['related']] = $row['type'];
			}
			
			$j=0;
			while ($Array2 = mysqli_fetch_assoc($Result2)) {
			$CompareArray[$j] = $Array2[related];
			$j++;
			}
		
			$j=0;
			$glist = array();
			while ($Array = mysqli_fetch_assoc($Result)) {
				
				$this_sel = FALSE;
				if($sel[$Array['albumid']]) $this_sel = TRUE;
		
				$row = '
				<tr'.($this_sel ? ' class="sel-relalbum-on"' : '').'>
					<td style="vertical-align:top; padding:4px 0; border-bottom:1px solid #EEE;">
						<input type="checkbox" name="albumrel[related][]" value="'.$Array['albumid'].'" '.($this_sel ? 'checked="checked"' : '').' id="ch-'.$Array['albumid'].'" class="ch-relalbum"/>
					</td>
					<td style="padding:5px 7px; border-bottom:1px solid #EEE;">
						<label for="ch-'.$Array['albumid'].'" style="display:block; cursor:pointer;">
							'.$Array['title'].' <em>'.$Array['subtitle'].'</em>
							<span style="display:block; margin-top:2px; color:#666;">('.$Array['albumid'].') '.$Array['datesort'].'</span>
						</label>
						<div id="step2-ch-'.$Array['albumid'].'" style="margin-top:3px;'.(!$this_sel ? 'display:none;' : '').'">
							<select name="albumrel['.$Array['albumid'].'][type]">
								<option value="1">General similar</option>
								<option value="2"'.($sel[$Array['albumid']] == "2" ? ' selected="selected"' : '').'>Same game</option>
								<option value="3"'.($sel[$Array['albumid']] == "3" ? ' selected="selected"' : '').'>Same composer(s)</option>
								<option value="4"'.($sel[$Array['albumid']] == "4" ? ' selected="selected"' : '').'>Same series</option>
								<option value="5"'.($sel[$Array['albumid']] == "5" ? ' selected="selected"' : '').'>Other album editions</option>
							</select>
						</div>
					</td>
				</tr>
				';
				if($this_sel) array_unshift($glist, $row);
				else $glist[] = $row;
			}
			
			// array end
			
			?>
			
			<?=$step5msg?>
			
			<p><?=($cgs ? '<b>'.$cgs.'</b> albums currently marked related' : 'No related albums marked yet')?></p>
			
			<form action="albums.php" method="post">
				<input type="hidden" name="editid" value="<?=$editid?>">
				<input type="hidden" name="action" value="<?=$action?>">
				<input type="hidden" name="substep" value="1">
				<input type="hidden" name="step" value="5">
				<input type="hidden" name="process" value="5">
				<input type="hidden" name="dbupdate" value="1">
				<table border="0" cellpadding="0" cellspacing="0">
				<?
				
				foreach ($glist as $a) {
					echo "$a";
				}
				
				?>
				</table>
				<br/>
				<input type="submit" name="pro" value="Next step">
			</form>
			<?
			unset($album5);
			unset($CompareArray);
			unset($sglist);
			
		}
	
	} elseif ($step == "6") {
		
		//////////////
		// SYNOPSIS //
		//////////////
		
		?>
		This form has been depreciated. To add a synopsis, use the <b><a href="/posts/manage.php?action=newpost&autotag=AlbumID:<?=$editid?>">Sblog post form</a></b> and use the following attributes:
		<ul>
			<li>Post object: Text</li>
			<li>Post content: any</li>
			<li>Headline: choose an appropriate title (ie: Final Fantasy XX Soundtrack summary)</li>
			<li>Post category: Public Post; check <b>Archive Post</b></li>
			<li>After publishing, add the following tag: <code>AlbumID:<?=$editid?></code> (If you follow the above link it should be automatically tagged for you)</li>
		</ul>
		<?
		/*
		unset($album6);
		
		// check alternative edition
		
		$Querya = "SELECT * from albums_edition where album = '$editid' limit 1";
		$Resulta = mysqli_query($GLOBALS['db']['link'], $Querya);
		if ($row = mysqli_fetch_assoc($Resulta)) {
		$duchk = $row[group];
		}
		
		if ($duchk) {
		$Queryb = "SELECT e.album, e.group, e.revset, l.albumid, l.id from albums_edition as e, albums as l where e.group = '$duchk' and e.album = l.albumid and e.revset = 1 limit 1";
		$Resultb = mysqli_query($GLOBALS['db']['link'], $Queryb);
		
		while ($row = mysqli_fetch_assoc($Resultb)) {
		$newer = $row[album];
		}
		if ($newer != $editid) {
		$alt = $newer;
		?>
		<hr/>
		<strong>WARNING:</strong> This album's synopsis has been linked to that of a <a href="/<?=$albumpath?>/?id=<?=$alt?>" target="_blank">a different, newer version of this album</a>.  You are editing this other album's synopsis instead.<br/><br/>
		<?
		}
		else {
		$alt = $editid;
		}
		}
		else {
		$alt = $editid;
		}
		
		// end check
		
		
		$Query = "SELECT * from albums_synopsis as d where d.album = '$alt'";
		$Result = mysqli_query($GLOBALS['db']['link'], $Query);
		
		while ($Array = mysqli_fetch_assoc($Result)) {
		$album6[] = $Array;
		
		}
		
		$date1 = explode("-", $album6[0][date]);
		
		$yrcurrent = date("Y");
		$daycurrent = date("d");
		$mocurrent = date("m");
		$yrmax = $yrcurrent + 1;
		
		for ($i=2001; $i < $yrmax; $i++) {
		if ($i == $date1[0]) {
		$yrlist[] = "<option value=\"$i\" selected>$i</option>\n";
		}
		elseif ($i == $yrcurrent && !$date1[0]) {
		$yrlist[] = "<option value=\"$i\" selected>$i</option>\n";
		}
		else {
		$yrlist[] = "<option value=\"$i\">$i</option>\n";
		}
		}
		
		for ($i=1; $i < 32; $i++) {
		$j=$i;
		if ($i < 10) {
		$i = "0$i";
		}
		if ($i == "$date1[2]") {
		$daylist[] = "<option value=\"$i\" selected>$i</option>\n";
		}
		elseif ($i == $daycurrent && !$date1[2]) {
		$daylist[] = "<option value=\"$i\" selected>$i</option>\n";
		}
		else {
		$daylist[] = "<option value=\"$i\">$i</option>\n";
		}
		$i=$j;
		}
		
		for ($i=1; $i < 13; $i++) {
		$j=$i;
		if ($i < 10) {
		$i = "0$i";
		}
		if ($i == $date1[1]) {
		$molist[] = "<option value=\"$i\" selected>$i</option>\n";
		}
		elseif ($i == $mocurrent && !$date1[1]) {
		$molist[] = "<option value=\"$i\" selected>$i</option>\n";
		}
		else {
		$molist[] = "<option value=\"$i\">$i</option>\n";
		}
		$i=$j;
		}
		
		$album6[0][synopsis] = stripslashes($album6[0][synopsis]);
		
		?>
		<?=$altwarn?>
		
		<form action="albums.php" method="post">
		<input type="hidden" name="editid" value="<?=$editid?>"/>
		<input type="hidden" name="alt" value="<?=$alt?>"/>
		<input type="hidden" name="action" value="<?=$action?>"/>
		<input type="hidden" name="step" value="6"/>
		<input type="hidden" name="process" value="6"/>
		<input type="hidden" name="album6[0][album]" value="<?=$album6[0][album]?>"/>
		<input type="hidden" name="dbupdate" value="1"/>
		
		<table cellspacing="0" class="styled-form">
			<tr>
				<th>Author's name</th>
				<td><input type="text" value="<?=$album6[0][author]?>" name="album6[0][author]" maxlength="255"/></td>
			</tr>
			<tr>
				<th>Author's link</th>
				<td>http:// link or relative link preferred over e-mail
					<p><input type="text" value="<?=$album6[0]['link']?>" name="album6[0][link]" maxlength="255" size="50"/></p>
				</td>
			</tr>
			<tr>
				<th>Publish date</th>
				<td>
					<select name="yrsort">
						<?
						
						arsort($yrlist);
						foreach ($yrlist as $a) {
						echo "$a";
						}
						echo "</select>";
						
						echo "<select name=\"mosort\">\n";
						
						foreach ($molist as $a) {
						echo "$a";
						}
						echo "</select>";
						
						echo "<select name=\"daysort\">\n";
						
						foreach ($daylist as $a) {
						echo "$a";
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th>Words</th>
				<td>
					
					<p><textarea rows="20" cols="66" name="album6[0][synopsis]" id="input-synopsis"><?=$album6[0][synopsis]?></textarea></p>
					<p><?=saveDraftButton("input-synopsis", str_replace(" ", "_", $albumtitle)."-".str_replace(" ", "_", $albumsubtitle)."-synopsis")?></p>
				</td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td><input type="submit" name="pro" value="Submit" style="font-weight:bold"/></td>
			</tr>
		</table>
		</form>
		<?*/
		
	} elseif ($step == "7") {
		
		///////////
		// MEDIA //
		///////////
		
		$path = "$rootpath/$albumpath/media/cover/";
		
		//full-sized img?
		//if(file_exists($path.$editid.".png")) $fs_ext = "png";
		if(file_exists($path.$editid.".jpg")) $fs_ext = "jpg";
		//if(file_exists($path.$editid.".gif")) $fs_ext = "gif";
		
		?>
		<fieldset>
			<legend>Current Cover Images</legend>
			<table cellspacing="0" class="styled-form">
				<tr>
					<th>Full-sized Image</th>
					<td><?=($fs_ext ? '<img src="/'.$albumpath.'/media/cover/'.$editid.'.'.$fs_ext.'" alt="full-sized image"/>' : 'None uploaded')?></td>
				</tr>
				<tr>
					<th>Standard Display Image</th>
					<td><?=(file_exists($path."standard/".$editid.".png") ? '<img src="/'.$albumpath.'/media/cover/standard/'.$editid.'.png" alt="standard image"/>' : 'None uploaded')?></td>
				</tr>
				<tr>
					<th>Thumbnail Image</th>
					<td><?=(file_exists($path."thumb/".$editid.".png") ? '<img src="/'.$albumpath.'/media/cover/thumb/'.$editid.'.png" alt="full-sized image"/>' : 'None uploaded')?></td>
				</tr>
			</table>
		</fieldset>
		
		<br/>
		
		<form action="albums.php" method="post" ENCTYPE="multipart/form-data">
			<input type="hidden" name="editid" value="<?=$editid?>"/>
			<input type="hidden" name="action" value="<?=$action?>"/>
			<input type="hidden" name="step" value="7"/>
			<input type="hidden" name="process" value="7"/>
			<input type="hidden" name="dbupdate" value="1"/>
			
			<fieldset>
				<legend>Upload a New Cover Image</legend>
				<div class="warn">Please only upload an image that is a PNG, JPG, or GIF</div>
				<p><input type="file" name="file"/></p>
				<p><input type="submit" name="upload" value="Upload"/></p>
			</fieldset>
			
		</form>
		<?
	
	} elseif ($step == "9") {
		
		//////////////////////////
		// FACTOIDS & RETAILERS //
		//////////////////////////
		
		unset($album9a);
		
		// check alternative edition
		
		$Querya = "SELECT * from albums_edition where album = '$editid' limit 1";
		$Resulta = mysqli_query($GLOBALS['db']['link'], $Querya);
		if ($row = mysqli_fetch_assoc($Resulta)) {
			$duchk = $row[group];
		}
		
		if ($duchk) {
			$Queryb = "SELECT e.album, e.group, e.revset, l.albumid, l.id from albums_edition as e, albums as l where e.group = '$duchk' and e.album = l.albumid and e.revset = 1 limit 1";
			$Resultb = mysqli_query($GLOBALS['db']['link'], $Queryb);
			
			while ($row = mysqli_fetch_assoc($Resultb)) {
			$newer = $row[album];
			}
			if ($newer != $editid) {
			$alt = $newer;
			$altwarn = '
			<hr/>
			<strong>WARNING:</strong> This album\'s factoids have been linked to that of a <a href="/'.$albumpath.'/?id='.$alt.'" target="_blank">a different, newer version of this album</a>.  You are editing this other album\'s factoids instead.<br/><br/>
			';
			}
			else {
			$alt = $editid;
			}
		}
		else {
			$alt = $editid;
		}
		
		// end check
		
		$Query = "SELECT * from albums_trivia as t where t.album = '$alt' and t.indexid = '$factedit' limit 1";
		$Result = mysqli_query($GLOBALS['db']['link'], $Query);
		$factcheck = mysqli_num_rows($Result);
		
		if ($factedit == "new") {
		
			$yrcurrent = date("Y");
			$daycurrent = date("d");
			$mocurrent = date("m");
			$yrmax = $yrcurrent + 1;
			
			for ($i=2001; $i < $yrmax; $i++) {
			if ($i == $date1[0]) {
			$yrlist[] = "<option value=\"$i\" selected>$i</option>\n";
			}
			elseif ($i == $yrcurrent && !$date1[0]) {
			$yrlist[] = "<option value=\"$i\" selected>$i</option>\n";
			}
			else {
			$yrlist[] = "<option value=\"$i\">$i</option>\n";
			}
			}
			
			for ($i=1; $i < 32; $i++) {
			$j=$i;
			if ($i < 10) {
			$i = "0$i";
			}
			if ($i == "$date1[2]") {
			$daylist[] = "<option value=\"$i\" selected>$i</option>\n";
			}
			elseif ($i == $daycurrent && !$date1[2]) {
			$daylist[] = "<option value=\"$i\" selected>$i</option>\n";
			}
			else {
			$daylist[] = "<option value=\"$i\">$i</option>\n";
			}
			$i=$j;
			}
			
			for ($i=1; $i < 13; $i++) {
			$j=$i;
			if ($i < 10) {
			$i = "0$i";
			}
			if ($i == $date1[1]) {
			$molist[] = "<option value=\"$i\" selected>$i</option>\n";
			}
			elseif ($i == $mocurrent && !$date1[1]) {
			$molist[] = "<option value=\"$i\" selected>$i</option>\n";
			}
			else {
			$molist[] = "<option value=\"$i\">$i</option>\n";
			}
			$i=$j;
			}
			
			?>
			<?=$altwarn?>
			
			<form action="albums.php" method="post">
				<input type="hidden" name="editid" value="<?=$editid?>"/>
				<input type="hidden" name="alt" value="<?=$alt?>"/>
				<input type="hidden" name="action" value="<?=$action?>"/>
				<input type="hidden" name="step" value="9"/>
				<input type="hidden" name="process" value="9"/>
				<input type="hidden" name="album6[0][album]" value="<?=$album6[0][album]?>"/>
				<input type="hidden" name="dbupdate" value="1"/>
				<input type="hidden" name="factedit" value="<?=$factedit?>"/>
				
				Author's name: <input type="text" value="<?=$album6[0][author]?>" name="album6[0][author]" maxlength="255"/><br/><br/>
				
				Author's link (http:// link or relative link preferred over e-mail):<br/><input type="text" value="<?=$album6[0]['link']?>" name="album6[0][link]" maxlength="255" size="50"><br/><br/>
				
				
				Publish date (YYYY-MM-DD):
				<select name="yrsort">
				<?
				
				arsort($yrlist);
				foreach ($yrlist as $a) {
				echo "$a";
				}
				echo "</select>";
				
				echo "<select name=\"mosort\">\n";
				
				foreach ($molist as $a) {
				echo "$a";
				}
				echo "</select>";
				
				echo "<select name=\"daysort\">\n";
				
				foreach ($daylist as $a) {
				echo "$a";
				}
				echo "</select>";
				
				
				?>
				<br/><br/>
				<textarea rows="30" cols="80" name="album6[0][fact]"><?=$album6[0][fact]?></textarea><br/><br/>
				<input type="submit" name="pro" value="Save">
			</form>
			
			<?
		
		} elseif ($factcheck == "1") {
		
			while ($Array = mysqli_fetch_assoc($Result)) {
			$album6[] = $Array;
			
			}
			
			$date1 = explode("-", $album6[0][date]);
			
			$yrcurrent = date("Y");
			$daycurrent = date("d");
			$mocurrent = date("m");
			$yrmax = $yrcurrent + 1;
			
			for ($i=2001; $i < $yrmax; $i++) {
			if ($i == $date1[0]) {
			$yrlist[] = "<option value=\"$i\" selected>$i</option>\n";
			}
			elseif ($i == $yrcurrent && !$date1[0]) {
			$yrlist[] = "<option value=\"$i\" selected>$i</option>\n";
			}
			else {
			$yrlist[] = "<option value=\"$i\">$i</option>\n";
			}
			}
			
			for ($i=1; $i < 32; $i++) {
			$j=$i;
			if ($i < 10) {
			$i = "0$i";
			}
			if ($i == "$date1[2]") {
			$daylist[] = "<option value=\"$i\" selected>$i</option>\n";
			}
			elseif ($i == $daycurrent && !$date1[2]) {
			$daylist[] = "<option value=\"$i\" selected>$i</option>\n";
			}
			else {
			$daylist[] = "<option value=\"$i\">$i</option>\n";
			}
			$i=$j;
			}
			
			for ($i=1; $i < 13; $i++) {
			$j=$i;
			if ($i < 10) {
			$i = "0$i";
			}
			if ($i == $date1[1]) {
			$molist[] = "<option value=\"$i\" selected>$i</option>\n";
			}
			elseif ($i == $mocurrent && !$date1[1]) {
			$molist[] = "<option value=\"$i\" selected>$i</option>\n";
			}
			else {
			$molist[] = "<option value=\"$i\">$i</option>\n";
			}
			$i=$j;
			}
			
			?>
			<?=$altwarn?>
			
			<form action="albums.php" method="post">
			<input type="hidden" name="editid" value="<?=$editid?>">
			<input type="hidden" name="alt" value="<?=$alt?>">
			<input type="hidden" name="action" value="<?=$action?>">
			<input type="hidden" name="step" value="9">
			<input type="hidden" name="process" value="9">
			<input type="hidden" name="album6[0][album]" value="<?=$album6[0][album]?>">
			<input type="hidden" name="dbupdate" value="1">
			<input type="hidden" name="factedit" value="<?=$factedit?>">
			
			Author's name: <input type="text" value="<?=$album6[0][author]?>" name="album6[0][author]" maxlength="255"><br/><br/>
			
			Author's link (http:// link or relative link preferred over e-mail):<br/><input type="text" value="<?=$album6[0]['link']?>" name="album6[0][link]" maxlength="255" size="50"><br/><br/>
			
			
			Publish date (YYYY-MM-DD):
			<select name="yrsort">
			<?
			
			arsort($yrlist);
			foreach ($yrlist as $a) {
			echo "$a";
			}
			echo "</select>";
			
			echo "<select name=\"mosort\">\n";
			
			foreach ($molist as $a) {
			echo "$a";
			}
			echo "</select>";
			
			echo "<select name=\"daysort\">\n";
			
			foreach ($daylist as $a) {
			echo "$a";
			}
			echo "</select>";
			
			
			?>
			<br/><br/>
			<textarea rows="30" cols="80" name="album6[0][fact]"><?=$album6[0][fact]?></textarea><br/><br/>
			
			<label><input type="checkbox" name="factdelete" value="1"/> Destroy factoid!!1</label><br/><br/>
			
			<input type="submit" name="pro" value="Save Changes" style="font-size:150%;"/>
			
			</form>
			
			<?
		
		} else {
			
			// ANIMENATION
			unset($stock);
			unset($vlnk);
			$Query = "SELECT * from albums_buy as b where b.album = '$editid' and b.vendor = 'AnimeNation'";
			$Result = mysqli_query($GLOBALS['db']['link'], $Query);
			$linkcount = mysqli_num_rows($Result);
			
			if ($linkcount == 1) {
			while ($Array = mysqli_fetch_assoc($Result)) {
			$album9a[0] = $Array;
			
			}
			if ($album9a[0][stock] == "0") {
			$stock = " checked";
			}
			
			}
			
			if (!$album9a[0][code]) {
			$vlnk = "http://www.animenation.com/";
			}
			else {
			$vlnk = $album9a[0][code];
			}
			
			$vendor[0] = '
			<a href="'.$vlnk.'" target="_blank"><img src="/'.$albumpath.'/graphics/animenation.png" width="86" height="10" ALT="AnimeNation" border="0"></a><br/>
			<strong>Example:</strong> http://store.yahoo.com/cgi-bin/clink?animenation+ytCEmV+<strong>sscx-10030.html</strong><br/>
			<table border="0" width="100%" cellspacing="2" cellpadding="0">
			<tr>
			<td width="65%">Direct link</td>
			<td width="20%">Price ($ also)</td>
			<td width="15%">No stock?</td>
			</tr>
			<tr>
			<td width="65%"><input type="text" value="'.$album9a[0][code].'" name="album9a[0][code]" maxlength="255" style="width:95%;"></td>
			<td width="20%"><input type="text" value="'.$album9a[0][price].'" name="album9a[0][price]" maxlength="6" size="6"></td>
			<td width="15%"><input type="checkbox" value="0" name="album9a[0][stock]"'.$stock.'></td>
			</tr>
			</table><br/>
			<input type="hidden" name="album9a[0][vendor]" value="AnimeNation">
			';
			
			
			// GAMEMUSIC.COM
			unset($stock);
			unset($vlnk);
			$Query = "SELECT * from albums_buy as b where b.album = '$editid' and b.vendor = 'GameMusic.com'";
			$Result = mysqli_query($GLOBALS['db']['link'], $Query);
			$linkcount = mysqli_num_rows($Result);
			
			if ($linkcount == 1) {
			while ($Array = mysqli_fetch_assoc($Result)) {
			$album9a[1] = $Array;
			
			}
			if ($album9a[1][stock] == "0") {
			$stock = " checked";
			}
			
			}
			
			if (!$album9a[1][code]) {
			$vlnk = "http://www.gamemusic.com/";
			}
			else {
			$vlnk = $album9a[1][code];
			}
			
			$vendor[1] = "
			<a href=\"$vlnk\" target=\"_blank\"><img src=\"/$albumpath/graphics/gmc.png\" width=\"84\" height=\"10\" ALT=\"GameMusic.com\" border=\"0\"></a><br/>
			<strong>Example:</strong> http://www.gamemusic.com/associates/referral.asp?asid=sqrhvn&iid=<strong>1525</strong><br/>
			<table border=\"0\" width=\"100%\" cellspacing=\"2\" cellpadding=\"0\">
			<tr>
			<td width=\"65%\">Direct link</td>
			<td width=\"20%\">Price ($ also)</td>
			<td width=\"15%\">No stock?</td>
			</tr>
			<tr>
			<td width=\"65%\"><input type=\"text\" value=\"{$album9a[1][code]}\" name=\"album9a[1][code]\" maxlength=\"255\" style=\"width:95%;\"></td>
			<td width=\"20%\"><input type=\"text\" value=\"{$album9a[1][price]}\" name=\"album9a[1][price]\" maxlength=\"6\" size=\"6\"></td>
			<td width=\"15%\"><input type=\"checkbox\" value=\"0\" name=\"album9a[1][stock]\"$stock></td>
			</tr>
			</table><br/>
			<input type=\"hidden\" name=\"album9a[1][vendor]\" value=\"GameMusic.com\">
			";
			
			// AMAZON.COM
			unset($stock);
			unset($vlnk);
			$Query = "SELECT * from albums_buy as b where b.album = '$editid' and b.vendor = 'Amazon.com'";
			$Result = mysqli_query($GLOBALS['db']['link'], $Query);
			$linkcount = mysqli_num_rows($Result);
			
			if ($linkcount == 1) {
			while ($Array = mysqli_fetch_assoc($Result)) {
			$album9a[2] = $Array;
			
			}
			if ($album9a[2][stock] == "0") {
			$stock = " checked";
			}
			
			}
			
			if (!$album9a[2][code]) {
			$vlnk = "http://www.amazon.com/";
			}
			else {
			$vlnk = $album9a[2][code];
			}
			
			$vendor[2] = "
			<a href=\"$vlnk\" target=\"_blank\"><img src=\"/$albumpath/graphics/amazon.png\" width=\"84\" height=\"10\" ALT=\"Amazon.com\" border=\"0\"></a><br/>
			<strong>Example:</strong> http://www.amazon.com/exec/obidos/ASIN/<strong>B000058AAY</strong>/squarehaven<br/>
			<table border=\"0\" width=\"100%\" cellspacing=\"2\" cellpadding=\"0\">
			<tr>
			<td width=\"65%\">Direct link</td>
			<td width=\"20%\">Price ($ also)</td>
			<td width=\"15%\">No stock?</td>
			</tr>
			<tr>
			<td width=\"65%\"><input type=\"text\" value=\"{$album9a[2][code]}\" name=\"album9a[2][code]\" maxlength=\"255\" style=\"width:95%;\"></td>
			<td width=\"20%\"><input type=\"text\" value=\"{$album9a[2][price]}\" name=\"album9a[2][price]\" maxlength=\"6\" size=\"6\"></td>
			<td width=\"15%\"><input type=\"checkbox\" value=\"0\" name=\"album9a[2][stock]\"$stock></td>
			</tr>
			</table><br/>
			<input type=\"hidden\" name=\"album9a[2][vendor]\" value=\"Amazon.com\">
			";
			
			// Play-Asia.com
			unset($stock);
			unset($vlnk);
			$Query = "SELECT * from albums_buy as b where b.album = '$editid' and b.vendor = 'Play-Asia.com'";
			$Result = mysqli_query($GLOBALS['db']['link'], $Query);
			$linkcount = mysqli_num_rows($Result);
			
			if ($linkcount == 1) {
			while ($Array = mysqli_fetch_assoc($Result)) {
			$album9a[3] = $Array;
			
			}
			if ($album9a[3][stock] == "0") {
			$stock = " checked";
			}
			
			}
			
			if (!$album9a[3][code]) {
			$vlnk = "http://www.Play-Asia.com/";
			}
			else {
			$vlnk = $album9a[3][code];
			}
			
			$vendor[3] = "
			<a href=\"$vlnk\" target=\"_blank\"><img src=\"/$albumpath/graphics/playasia.png\" ALT=\"Play-Asia.com\" border=\"0\"></a><br/>
			Requires specially rendered code to link directly to a specific page. If you don't have the code, please input the price and default homepage link: <em>http://www.play-asia.com/SOap-23-83-3swa-49-en.html</em>
			<table border=\"0\" width=\"100%\" cellspacing=\"2\" cellpadding=\"0\">
			<tr>
			<td width=\"65%\">Direct link [<a href=\"#insertlink\" class=\"preventdefault\" onclick=\"$('#inpPAlink').val('http://www.play-asia.com/SOap-23-83-3swa-49-en.html');\">insert default link</a>]</td>
			<td width=\"20%\">Price ($ also)</td>
			<td width=\"15%\">No stock?</td>
			</tr>
			<tr>
			<td width=\"65%\"><input type=\"text\" value=\"{$album9a[3][code]}\" name=\"album9a[3][code]\" maxlength=\"255\" style=\"width:95%;\" id=\"inpPAlink\"></td>
			<td width=\"20%\"><input type=\"text\" value=\"{$album9a[3][price]}\" name=\"album9a[3][price]\" maxlength=\"6\" size=\"6\"></td>
			<td width=\"15%\"><input type=\"checkbox\" value=\"0\" name=\"album9a[3][stock]\"$stock></td>
			</tr>
			</table><br/>
			<input type=\"hidden\" name=\"album9a[3][vendor]\" value=\"Play-Asia.com\">
			";
			
			
			$Query = "SELECT * from albums_trivia as t where t.album = '$editid'";
			$Result = mysqli_query($GLOBALS['db']['link'], $Query);
			$blah = mysqli_num_rows($Result);
			
			
			?>
			<h3>FACTOIDS</h3>
			<a href="albums.php?step=<?=$step?>&action=edit&editid=<?=$editid?>&factedit=new" class="arrow-right">Make a new factoid</a><br/><br/>
			<?
			
			while ($Array = mysqli_fetch_assoc($Result)) {
			?>
			<div style="background-color: #EDEDED; border-style: solid; border-width: 0px 0px 1px 0px; border-color: #CCCCCC; padding: 10px;">
			<span style="font-family: Arial; font-size: 12pt;"><a href="albums.php?step=<?=$step?>&action=edit&editid=<?=$editid?>&factedit=<?=$Array[indexid]?>">Edit this factoid</a><br/></span>
			<?=$Array[fact]?>
			<?
			
			if ($Array[date]) {
			$date = "$Array[date]";
			}
			else {
			$date = "no date";
			}
			
			if ($Array[author]) {
			if ($Array[link]) {
			echo " (<a href=\"$Array[link]\">$Array[author]</a>, $date)";
			}
			else {
			echo " (<strong>$Array[author]</strong>, $date)";
			}
			}
			else {
			echo " (<strong>NO AUTHOR</strong>, $date)";
			}
			
			echo "</div>\n";
			
			}
			?>
			<h3>Retailers & Links</h3>
			
			<?
			$q = "SELECT * from albums where albumid = '$editid' limit 1";
			if(!$adat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) $errors[] = "Couldn't get album data";
			?>
			
			<form action="albums.php" method="post">
				
				<div style="margin:15px 0; padding:6px 10px; background-color:#EEE;">
					By default, a link to eBay and PlayAsia will be supplied on the album page.
					<div style="margin-top:3px;"><label style="font-size:15px;"><input type="checkbox" name="no_commerce" value="1"<?=($adat->no_commerce ? ' checked="checked"' : '')?>/>Don't show any retail links</label></div>
				</div>
				
				<strong>Note:</strong> Prices should still be included even if the album in question is not in stock; it is still useful piece of information to determine "market" price.  
				<strong>Click on the retailer graphic to go directly to the site's album page</strong> (new window).  
				To delete links, do not enter a price <strong>or</strong> link.<br/><br/>
				
				<input type="hidden" name="editid" value="<?=$editid?>">
				<input type="hidden" name="action" value="<?=$action?>">
				<input type="hidden" name="step" value="9">
				<input type="hidden" name="process" value="9">
				<input type="hidden" name="dbupdate" value="1">
				
				<?=$vendor[0]?>
				<p></p>
				<?=$vendor[1]?>
				<p></p>
				<?=$vendor[2]?>
				<p></p>
				<?=$vendor[3]?>
				
				<p></p>
				
				<fieldset style="background-color:#EEE;">
					<legend style="font-size:15px;">Other Links</legend>
					
					Supply links to direct the reader to a web page where this album is available (to buy or download).
					
					<br/><br/>
					
					<input type="text" name="link_name" size="55"/> Site Name
					<p style="margin:2px 0;"></p>
					<input type="text" name="link_url" value="http://" size="55" onfocus="$(this).select();" style="color:blue; text-decoration:underline;"/> URL
					<p style="margin:2px 0;"></p>
					<input type="button" value="Add Link" onclick="addLink();"/>
					
					<br/><br/>
					
					<div id="linkshere">
						<?
						$query = "SELECT * FROM albums_buy WHERE album='$editid' AND not_commerce = '1'";
						$res   = mysqli_query($GLOBALS['db']['link'], $query);
						if(!mysqli_num_rows($res)) echo '<b id="nolinks">No links yet!</b>';
						else {
							while($row = mysqli_fetch_assoc($res)) {
								?>
								<div style="margin:5px 0; font-size:15px;">
									<?=$row['vendor']?> <span style="color:#888;">&lt;</span> <?=$row['code']?> <span style="color:#888;">&gt;</span> 
									<a href="javascript:void(0);" style="color:#D22D2D;" onclick="$(this).parent().remove();">remove</a>
									<input type="hidden" name="in[links][name][]" value="<?=htmlSC($row['vendor'])?>"/>
									<input type="hidden" name="in[links][url][]" value="<?=$row['code']?>"/>
								</div>
								<?
							}
						}
						?>
					</div>
					
				</fieldset>
				
				<br/><br/>
				
				<input type="submit" name="pro" value="Save" style="font-size:15px;">
				
			</form>
			<?
		}
		
	}

} // $action==edit

else {
	
	if($aid = $_GET['delete_album']) {
		
		// DELETE //
		
		if($_SESSION['user_rank'] < 8) {
			$q = "SELECT * FROM albums_changelog WHERE album='$aid' AND type='new' AND usrid='$usrid' LIMIT 1";
			if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) die("Can't delete because you aren't the creator");
		}
		
		$tables = array(
			"albums_credits" => "albumid",
			"albums_edition" => "album",
			"albums_other_people" => "albumid",
			"albums_related" => "album",
			"albums_related" => "related",
			"albums_related" => "album",
			"albums_synopsis" => "album",
			"albums_tags" => "albumid",
			"albums_tracks" => "albumid",
			"albums_trivia" => "album",
			"albums" => "albumid"
		);
		
		while(list($table, $field) = each($tables)) {
			$q = "DELETE FROM `$table` WHERE `$field` = '$aid'";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't delete from $table table: $q";
			else $results[] = "Deleted from $table table";
		}
		
		//changelog
		$Query = "INSERT into albums_changelog (album, usrname, usrid, datetime, type) values ('$aid', '$usrname', '$usrid', '".date("Y-m-d H:i:s")."', 'delete')";
		if(!mysqli_query($GLOBALS['db']['link'], $Query)) $errors[] = "Couldn't update changelog. Deletion not recorded.";
		
	}
	
	/////////////
	// DEFAULT //
	/////////////
	
	$page->header();
	
	$page->openSection();
	
	?>
	<h2>Album Management</h2>
	
	<big><a href="?action=new" class="arrow-right">Create a new album entry</a></big>
	
	<br/><br/>
	
	<?
	$Query = "SELECT l.id, l.albumid, l.title, l.subtitle, l.datesort from albums as l order by title ASC, subtitle ASC";
	$Result = mysqli_query($GLOBALS['db']['link'], $Query);
	while ($Array = mysqli_fetch_assoc($Result)) {
		$list[] = "<option value=\"$Array[albumid]\">$Array[title] $Array[subtitle] ($Array[albumid]); $Array[datesort]</option>\n";
	}
	
	?>
	<form action="albums.php" method="post" name="editalbum">
		<input type="hidden" name="action" value="edit">
		<input type="hidden" name="step" value="1">
		<table border="0" cellpadding="0" cellspacing="0"><tr><td>
			<span style="float:right; color:#999;">Double click & go </span>or <b>select an album to edit</b>:<br/>
			<select name="editid" size="18" ondblclick="document.editalbum.submit();" style="margin:5px 0;">
				<?
				foreach ($list as $a) {
					echo "$a";
				}
				?>
			</select><br/>
		</td></tr></table>
		<input type="submit" name="edit" value="Access album data"/>
	</form>
	<?
	
}

$page->footer();

?>