<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/admin.php");

$page = new page;
$page->title = "Videogam.in Admin / Add a Game";
$page->min_rank = 7;
$page->admin = TRUE;
$page->freestyle.= '
H3 {
	padding-bottom: 0;
	border-bottom-width: 0; }
FORM.styled, FIELDSET.styled {
	padding: 15px;
	font-size: 16px;
	border: 5px solid #DCDCDC;
	background-color:#F5F5F5; }
TABLE.submenu {
	margin:5px 0 15px; border:1px solid #CCC; }
TABLE.submenu TH {
	padding-left:10px; font-weight:normal; border-bottom:1px solid #CCC; }
TABLE.submenu TD {
	padding: 5px 10px; border-right:1px solid #CCC; color:#888; }
TABLE.submenu TD.on {
	font-size:16px; color:black; background-color:#FFFFA4; }
DL#publicationlist {
	margin: -10px 0 0 0; }
#publicationlist DT {
	margin: 10px 0 0 0; font-weight: bold; }
#publicationlist DD {
	margin: 3px 0 0 20px; }
INPUT.unfocused {
	color: #AAA; }
P.warn {
	padding: 10px 10px 10px 30px !important;
	background-color: white;
	background-position: 10px 15px !important;
	border: 1px solid #CCC; }
';
$page->javascript.= '<script type="text/javascript" src="/bin/script/add-game.js"></script>'."\n";
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

if(!$_POST) {
	
	// STEP 1 //
	
	$page->header();
	
	?>
	<h2 style="margin-bottom:10px">New Game Wizard</h2>
	
	<form action="games-add.php" method="post" name="submittitleform" class="styled" onsubmit="return checkTitleForm();">
		<input type="hidden" name="step" value="0"/>
		<input type="text" name="in[title]" value="Input game title" id="input-title" class="unfocused" size="45" style="font-family:arial; font-size:175%;" onfocus="if(this.value=='Input game title') this.value=''; this.className='';"/>
		<div id="space"><p><input type="button" value="Check" onclick="checkTitle(document.getElementById('input-title').value);" style="font-family:arial; font-size:175%;"/></p></div>
	</form>
	<?
	
	$page->footer();
	exit;

} else {
	
	require("games-add-submit.php");
	
	$page->header();
	?><h2 style="margin-bottom:10px">New Game Wizard</h2><?
	
	if($step >= 1) {
		?>
		<form action="games-add.php" method="post" name="navform">
			<input type="hidden" name="editid" value="<?=$editid?>"/>
			<input type="hidden" name="step" value="" id="input-step"/>
			<table border="0" cellpadding="10" cellspacing="0" class="submenu">
				<tr>
					<th colspan="6"><big style="font-size:21px"><?=$in['title']?></big></th>
				</tr>
				<tr>
					<?
					if($step == 1) {
						?><td class="on"><b>Step 1</b><br/>Synopsis</td><?
					} else {
						?><td><a href="javascript:void(0)" onclick="document.getElementById('input-step').value='1'; document.navform.submit();"><b>Step 1</b><br/>Synopsis</a></td><?
					}
					if($step == 2) {
						?><td class="on"><b>Step 2</b><br/>General Details</td><?
					} else {
						?><td><a href="javascript:void(0)" onclick="document.getElementById('input-step').value='2'; document.navform.submit();"><b>Step 2</b><br/>General Details</a></td><?
					}
					if($step == 3) {
						?><td class="on"><b>Step 3</b><br/>Publications</td><?
					} else {
						?><td><a href="javascript:void(0)" onclick="document.getElementById('input-step').value='3'; document.navform.submit();"><b>Step 3</b><br/>Publications</a></td><?
					}
					if($step == 4) {
						?><td class="on"><b>Step 4</b><br/>People</td><?
					} else {
						?><td><a href="javascript:void(0)" onclick="document.getElementById('input-step').value='4'; document.navform.submit();"><b>Step 4</b><br/>People</a></td><?
					}
					if($step == 5) {
						?><td class="on"><b>Step 5</b><br/>Screenshots</td><?
					} else {
						?><td><a href="javascript:void(0)" onclick="document.getElementById('input-step').value='5'; document.navform.submit();"><b>Step 5</b><br/>Screenshots</a></td><?
					}
					?>
					<td style="border-right-width:0;"><a href="games-mod.php?id=<?=$editid?>">Switch to Advanced Editor</a></td>
				</tr>
			</table>
		</form>
		<?
	}
	
	switch($step) {
	
	case "1": // STEP 1 //
		
		?>
		<form action="games-add.php" method="post" class="styled">
			<input type="hidden" name="step" value="1"/>
			<input type="hidden" name="in[title]" value="<?=$in['title']?>"/>
			<input type="hidden" name="editid" value="<?=$editid?>"/>
			Add a short description or story synopsis of the game.
			<p>Use <code>&lt;big&gt;&lt;/big&gt;</code> for <big><b>big text</b></big></p>
			<p><textarea name="in[synopsis]" rows="7" cols="73" id="synopsis"><?=stripslashes($in['synopsis'])?></textarea></p>
			<p><?=saveDraftButton("synopsis", $in['title_url']."_synopsis")?></p>
			<p><input type="submit" name="submitform" value="Save & Continue &gt;" style="font-size:21px"/></p>
		</form>
		<?
		
	break;
	
	case "2": // STEP 2 //
		
		?>
		<form action="games-add.php" method="post" name="step2form">
			<input type="hidden" name="step" value="2"/>
			<input type="hidden" name="in[title]" value="<?=$in['title']?>"/>
			<input type="hidden" name="editid" value="<?=$editid?>" id="editid"/>
			<input type="hidden" name="submitform" value="1"/>
			
			<table border="0" cellpadding="0" cellspacing="0" class="styled-form styled-form-alt">
				<tr>
					<td colspan="2">
						Some good external resources for this data include <a href="http://en.wikipedia.org" target="_blank">Wikipedia</a>, <a href="http://mobygames.com" target="_blank">MobyGames</a>, and <a href="http://gamefaqs.com" target="_blank">GameFAQs</a>.
					</td>
				</tr>
				<tr>
					<th>Development Group(s) <a href="javascript:void(0)" class="tooltip" title="The company or group(s) that produced this game"><span class="block">?</span></a></th>
					<td>
						Separate multiple developers with / (IE: <i>Nintendo/HAL Labs</i>)
						<p><input type="text" name="in[developer]" value="<?=$in[developer]?>" size="50"/>
					</td>
				</tr>
				<tr>
					<th>Genres</th>
					<td>
						Separate each genre with a comma. Feel free to be creative, as you can input an infinite number of genres. 
						However, please note that only the first genre will be displayed on the game page header, while the rest 
						will be used for other applications (so it's important that you input all the applicable genre descriptions).
						<p>
							<select onchange="document.getElementById('input-genre').innerHTML+=this.options[this.selectedIndex].value; this.value='';">
								<option value="">Insert a common genre...</option>
								<?
								$query = "SELECT `genre`, COUNT(`genre`) AS `count` FROM `games_genres` GROUP BY `genre` ORDER BY `genre`";
								$res   = mysql_query($query);
								while($row = mysql_fetch_assoc($res)) {
									if($row['count'] > 3) echo '<option value=", '.str_replace('"', '&quot;', $row['genre']).'">'.$row['genre'].'</option>';
								}
								?>
							</select>
						</p>
						<p><textarea name="in[genre]" rows="2" cols="60" id="input-genre"><?
							$genres = array();
							$res = mysql_query("SELECT * FROM games_genres WHERE gid='$editid'");
							while($row = mysql_fetch_assoc($res)) $genres[] = $row['genre'];
							echo implode(",", $genres);
						?></textarea></p>
					</td>
				</tr>
				<tr>
					<th>
						Series 
						<a href="javascript:void(0)" class="tooltip" title="Some games can be included in multiple series.<br/>For example, Final Fantasy Tactics: War of the Lions is part of Final Fantasy, Final Fantasy Tactics, and the Ivalice Alliance series." style="font-size:13px"><span class="block">?</span></a>
					</th>
					<td><select id="series-put-select" onchange="insertSeries()">
						<option value=""<?=(!$in['series'] ? ' selected="selected"' : '')?>>Select a common series...</option>
						<?
							$query = "SELECT DISTINCT(series) AS series FROM games_series ORDER BY series";
							$res   = mysql_query($query);
							while($row = mysql_fetch_assoc($res)) {
								echo '<option value="'.htmlentities($row['series'], ENT_QUOTES).'">'.$row['series']."</option>\n";
							}
						?>
						</select>
						<p>
							or input: <input type="text" name="in[series_other]" size="25" id="series-put-input"/> 
							<input type="button" value="Insert" onclick="insertSeries()">
						</p>
						<p><fieldset>
							<legend>Currently inserted series</legend>
							<div id="series-field">
							<?
							//$query = "SELECT series, COUNT(*) AS count FROM games_series GROUP BY series ORDER BY series";
							$query = "SELECT * FROM games_series WHERE gid='$editid'";
							$res   = mysql_query($query);
							if(mysql_num_rows($res)) {
								$i = 100;
								while($row = mysql_fetch_assoc($res)) {
									$i++;
									?>
									<div id="series-<?=$i?>">
										<span id="series-words-<?=$i?>"><?=$row['series']?></span> 
										<a href="javascript:void(0)" onclick="removeSeries('<?=$i?>');" class="x">X</a>
									</div>
									<?
								}
								echo '</div><span id="none"></span>';
							} else {
								echo '</div><span id="none">None</span>';
							}
							?>
						</fieldset></p>
					</td>
				</tr>
				<tr>
					<th>Online Play?</th>
					<td><label><input type="checkbox" name="in[online]" value="1" style="vertical-align:middle"<?=($in['online'] ? ' checked="checked"' : '')?>/> This game can be played against others online</label></td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><input type="button" value="Submit" onclick="document.step2form.submit()" style="font-size:21px"/></td>
				</tr>
			</table>
		</form>
		
	<?
	
	break;
	
	case "3": // STEP 3 //
		
		?>
			<table border="0" cellpadding="0" cellspacing="0" width="100%" class="styled-form styled-form-alt">
				<tr>
					<td colspan="2">
						<form action="games-add.php" method="post">
							<input type="hidden" name="editid" value="<?=$editid?>"/>
							<input type="hidden" name="step" value="4" id="input-step"/>
							<div style="font-size:16px">Please add at least one publication of the game (submitting box art will allow users to add it to their game collection).</div>
							<input type="submit" value="Done adding publications" style="margin-top:5px; font-weight:bold; font-size:16px;"/>
						</form>
					</td>
				</tr>
				<form action="games-add.php" method="post" enctype="multipart/form-data" onsubmit="return formRequire('pub-region,pub-platform');">
				<input type="hidden" name="step" value="3"/>
				<input type="hidden" name="in[title]" value="<?=$in['title']?>"/>
				<input type="hidden" name="editid" value="<?=$editid?>"/>
				<tr>
					<th>Platform<br/><small>Required</small></th>
					<td>
						<select name="in[platform_id]" id="pub-platform">
							<option value="">Select a platform...</option>
							<?
							$query = "SELECT * FROM games_platforms WHERE platform != 'multiple' ORDER BY platform";
							$res   = mysql_query($query);
							while($row = mysql_fetch_assoc($res)) {
								echo '<option value="'.$row['platform_id'].'">'.$row['platform']."</option>\n";
							}
							?>
						</select> If it's not here, <a href="games-misc.php?what=platforms" target="_blank">add it</a> then refresh this page
					</td>
				</tr>
				<tr>
					<th>Region<br/><small>Required</small></th>
					<td>
						<select name="in[region]" id="pub-region">
							<option value="">Select a region...</option>
							<option value="us">North America</option>
							<option value="jp">Japan</option>
							<option value="eu">Europe</option>
							<option value="au">Australia</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>Release date<br/><small>Year is required</small></th>
					<td>
						Be as specific as possible, but only input known values.
						<p>
							<select name="in[year]" style="background-color:#FFFF80;">
								<?
								for($i = (date('Y') + 2); $i >= 1980; $i--) {
									echo '<option value="'.$i.'"'.(date("Y") == $i ? ' selected="selected"' : ' style="background-color:white;"').'>'.$i.'</option>'."\n";
								}
								?>
							</select> 
							<select name="in[month]">
								<option value="00">Month</option>
								<option value="01">January</option>
								<option value="02">February</option>
								<option value="03">March</option>
								<option value="04">April</option>
								<option value="05">May</option>
								<option value="06">June</option>
								<option value="07">July</option>
								<option value="08">August</option>
								<option value="09">September</option>
								<option value="10">October</option>
								<option value="11">November</option>
								<option value="12">December</option>
							</select> 
							<select name="in[day]">
								<option value="00">Day</option>
								<?
								for($i = 1; $i <= 31; $i++) {
									if($i < 10) $i = '0'.$i;
									echo '<option value="'.$i.'">'.$i.'</option>'."\n";
								}
								?>
							</select>
						</p>
					</td>
				</tr>
				<tr>
					<th>Upload Box Art</th>
					<td>
						<b>Please upload only cover images that meet these standards:</b>
						<ul style="margin:0 0 0 15px; padding:0; list-style-type:square;">
							<li>JPG, GIF, or PNG format</li>
							<li>At least 200 pixels in width</li>
							<li>Unblurred, clear, quality images without watermarks or site logos</li>
							<li>Flat images that are not scaled, rotated, have a 3D perspective, or have any borders or whitespace around the perimiter (tip: use <a href="http://www.wiredness.com/" target="_blank">Wiredness</a> to quickly crop any whitespace or borders from an image)</li>
						</ul>
						<p><input type="file" name="file"/></p>
					</td>
				</tr>
				<tr>
					<th>Publication Title <a href="javascript:void(0)" class="tooltip" title="Input the full title of the publication, for example: &quot;Final Fantasy XII (Collector's Edition)&quot; will differentiate it from regular old Final Fantasy XII"><span class="block">?</span></a></th>
					<td><input type="text" name="in[pub_title]" value="<?=$in['title']?>" size="50"/></td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td colspan="2">
						<input type="submit" name="submitform" value="Add Publication" style="font-size:21px"/>
					</td>
				</tr>
			</table>
		</fieldset>
	</form>
	<?
		
	break;
	
	case "4": // STEP 4 //
		
		?>
			<table border="0" cellpadding="0" cellspacing="0" width="100%" class="styled-form styled-form-alt">
				<tr>
					<td colspan="2">
						<form action="games-add.php" method="post">
							<input type="hidden" name="editid" value="<?=$editid?>"/>
							<input type="hidden" name="step" value="5" id="input-step"/>
							<div style="font-size:16px">Credit to those who worked on this game by adding their name to the credits list.</div>
							<input type="submit" value="Done adding people" style="margin-top:5px; font-weight:bold; font-size:16px;"/>
						</form>
					</td>
				</tr>
				<form action="games-add.php" method="post" enctype="multipart/form-data">
				<input type="hidden" name="step" value="4"/>
				<input type="hidden" name="in[title]" value="<?=$in['title']?>"/>
				<input type="hidden" name="editid" value="<?=$editid?>"/>
				<tr>
					<th>Who?</th>
					<td>
						<select name="in[pid]" id="pid">
							<option value="">Select an existing person...</option>
							<?
							$query = "SELECT pid, name, prolific FROM people WHERE not_creator != '1' ORDER BY name";
							$res   = mysql_query($query);
							while($row = mysql_fetch_assoc($res)) {
								echo '<option value="'.$row['pid'].'"'.($in['pid'] == $row['pid'] ? ' selected="selected"' : '').($row['prolific'] ? ' style="font-weight:bold"' : '').'>'.$row['name'].'</option>'."\n";
							}
							?>
						</select>
						<p>Or <input type="button" value="Add a new person" onclick="window.open('people.php','newpersonwindow','left=20,top=20,scrollbars=1');"/><br/>
							<small>(A new window will open in which you can add the person to the database, add some details, and add them to this game's credit list)</small></p>
					</td>
				</tr>
				<tr>
					<th>
						Role 
						<a href="javascript:void(0)" class="tooltip" title="The person's role in the creation of the game, or how they are credited; For example: Producer, Director, Character Designer, Programmer, etc."><span class="block">?</span></a>
					</th>
					<td>
						<input type="text" name="in[role]" value="<?=$in['role']?>" id="role" size="30"/>
						<p><label><input type="checkbox" name="in[vital]" value="1"<?=($in['vital'] ? ' checked="checked"' : '')?> id="vital"/> This person's role was relatively vital to the creation of this game</label></p>
					</td>
				</tr>
				<tr>
					<th>
						Notes <a href="javascript:void(0)" class="tooltip" title="Any notes or clarifications about this person's role in the game"><span class="block">?</span></a>
					</th>
					<td><textarea name="in[notes]" rows="3" cols="60" id="notes"><?=$in['notes']?></textarea></td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><input type="submit" name="submitform" value="Add Person" style="font-size:21px"/></td>
				</tr>
			</table>
		</form>
		<?
		
		break;
		
		case "5": // STEP 5 //
		
			//already has screens?
			$q = "SELECT * FROM media_tags WHERE tag='gid:$editid' LIMIT 1";
			if(mysql_num_rows(mysql_query($q))) {
				?>There are already screenshots for this game. Nicely done!<?
			} else {
		
				?>
				<form action="games-add.php" method="post" enctype="multipart/form-data" class="styled">
					<input type="hidden" name="step" value="5"/>
					<input type="hidden" name="in[title]" value="<?=$in['title']?>"/>
					<input type="hidden" name="editid" value="<?=$editid?>"/>
					
					Upload a few <b>screenshots</b> to show everyone how nice your game looks.
					
					<p><input type="file" name="screen[0]"/> <label><input type="text" name="caption[0]" size="40"/> caption <a href="javascript:void(0)" class="tooltip" title="A description of the image (optional)"><span class="block">?</span></a></label></p>
					<p><input type="file" name="screen[1]"/> <input type="text" name="caption[1]" size="40"/></p>
					<p><input type="file" name="screen[2]"/> <input type="text" name="caption[2]" size="40"/></p>
					<p><input type="file" name="screen[3]"/> <input type="text" name="caption[3]" size="40"/></p>
					<p><input type="file" name="screen[4]"/> <input type="text" name="caption[4]" size="40"/></p>
					<p>(Upload more screens after this submission)</p>
					
					<p>
						<input type="submit" name="submitform" value="Upload & Continue" style="font-weight:bold"/> 
						<input type="submit" name="skipsubmit" value="Skip upload"/>
					</p>
				</form>
				<?
				
			}
		
		break;
		
		case "6": // STEP 6 //
		
			?>
			<span style="font-size:50px">All done!</span>
			<ul>
				<?
				$q = "SELECT * FROM media_tags LEFT JOIN media USING (media_id) WHERE tag='gid:$editid' LIMIT 1";
				if($mdat = mysql_fetch_object(mysql_query($q))) {
					echo '<li><a href="media.php?uploaddirectory='.str_replace("/media/", "", $mdat->directory).'">Upload more screenshots</a> or <a href="media.php">upload a new batch of media</a></li>'."\n";
				}
				?>
				<li><a href="/games/link.php?id=<?=$editid?>">See your game's page</a> that you created</li>
				<li><a href="games-mod.php?id=<?=$editid?>">Go to this game's advanced editing form</a> to review and edit all data associated with this game.</li>
			</ul>
			<?
			
		break;
		
	}
	
	$page->footer();
	exit;
	
}

?>