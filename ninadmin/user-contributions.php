<?
use Vgsite\Page;
$page = new Page();
use Verot\Upload;
require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/admin.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/contribute.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bin/php/htmltoolbox.php");

$do = $_GET['do'];
$action = $_POST['action'];
$in = $_POST['in'];
$id = $_GET['id'];

$page->title = "Videogam.in Admin / User Contributions Management";
$page->min_rank = 8;
$page->admin = TRUE;
$page->freestyle.= '
	P { margin:15px 0 0 0 !important; }
	#pendingc TR.on TD { background-color:#FFFFB3; }
	#pendingc TR.hov TD { background-color:#EEE; }
	.comp DEL { background-color:#FAE4E4; }
	.comp INS { text-decoration:none; background-color:#D5FFD5; }
	.comp P { margin:auto !Important; }
';
$page->javascripts[] = "user-contributions.js";

if($_POST) require("user-contributions-process.php");

$page->header();

?>
<h2>User Contributions</h2>
<?

if($id) {
	
	$q = "SELECT * FROM users_contributions uc LEFT JOIN users_contributions_data USING (contribution_id) WHERE uc.contribution_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $id)."' LIMIT 1";
	if(!$x = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) die("Couldn't get data for id # $id: ".mysqli_error($GLOBALS['db']['link']));
	
	if(!$x['pending']) {
		$errors[] = "This contribution is no longer pending (Reviewed by ".outputUser($x['reviewer'], FALSE, FALSE)." on  ".$x['datetime_reviewed'].")";
		$page->footer();
		exit;
	}
	
	$d = makeContrDataArr($x['data']);
	
	if($x['supersubject']) {
		list($subj, $subjid) = explode(":", $x['supersubject']);
		if($subj == "gid") {
			$q = "SELECT * FROM games WHERE gid='".$subjid."' LIMIT 1";
			$g = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
		} elseif($subj == "pid") {
			$q = "SELECT * FROM people WHERE pid='".$subjid."' LIMIT 1";
			$p = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
		}
	}
	
	//get next cid for form
	$q = "SELECT * FROM users_contributions WHERE `datetime` >= '".$x['datetime']."' AND pending = '1' AND contribution_id != '$id' LIMIT 1";
	$nextc = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
	
	?>
	<form action="user-contributions.php<?=($nextc->contribution_id ? '?id='.$nextc->contribution_id : '')?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="cids[]" value="<?=$x['contribution_id']?>"/>
		
		<table border="0" cellpadding="5" cellspacing="0" class="plain" style="border-color:#CCC;">
			<tr>
				<td style="border-color:#CCC">
					<?=bb2html($x['description'])?>
					<?
					if($subj == "gid") {
						?> &nbsp; 
						<span style="color:#AAA; background-color:#EEE;">[<a href="javascript:void(0)" onclick="toggle('change-game','');">Change Game</a>]</span>
						<div style="display:none; margin-top:5px;" id="change-game">
							<select name="in[gid]" onchange="$(this).next().attr('href', '/games/'+$(this).val()+'/');">
								<?
								$query = "SELECT title, gid FROM games ORDER BY title";
								$res   = mysqli_query($GLOBALS['db']['link'], $query);
								while($row = mysqli_fetch_assoc($res)) {
									echo '<option value="'.$row['gid'].'"'.($row['gid'] == $subjid ? ' selected="selected"' : '').'>'.$row['title'].'</option>';
								}
								?>
							</select> 
							<a href="/games" target="_blank" class="arrow-link">go</a>
						</div>
						<?
					}
					?>
				</td>
			</tr>
			<tr>
				<td style="border-color:#CCC">Submitted on <?=formatDate($x['datetime'])?> by <?=outputUser($x['usrid'], FALSE)?></td>
			</tr>
			<?=($x['notes'] ? '<tr><td style="border-color:#CCC; background-color:#FFFFB0;"><b>Author\'s Notes:</b> '.nl2br($x['notes']).'</td></tr>' : '')?>
		</table>
	
		<?
		
		if($x['type_id'] == "2" || $x['type_id'] == "13" || $x['type_id'] == "16") $x['type_id'] = "wiki";
		
		switch($x['type_id']) {
		
		case "wiki":
		
			// WIKI //
			
			if($d['notes']) echo '<p></p><div style="padding:3px 6px; border:1px solid #DDD; background-color:#FFFFB0;"><b>Wiki Author\'s Notes:</b> '.$d['notes'].'</div>';
			
			$q = "SELECT * FROM wiki WHERE field='".$d['field']."' AND subject_field='".$d['subject_field']."' AND subject_id='".$d['subject_id']."' ORDER BY `datetime` DESC LIMIT 1";
			if($row = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
				
				//show comparisons with currently published text
				
				if($row->datetime > $x['datetime']) $warnings[] = "The curently published text is newer than the submission -- publishing this verson may overwrite latter contributions not included here.";
				
				require_once $_SERVER['DOCUMENT_ROOT'].'/bin/php/Text_Diff/Diff.php';
				require_once $_SERVER['DOCUMENT_ROOT'].'/bin/php/Text_Diff/Diff/Renderer/inline.php';
				
				$lines1 = explode("\n", $row->text);
				$lines2 = explode("\n", $d['text']);
				
				$diff     = new Text_Diff('auto', array($lines1, $lines2));
				$renderer = new Text_Diff_Renderer_inline();
				$comp = $renderer->render($diff);
				$comp = nl2p($comp);
				$comp2 = bb2html($comp);
				
				?>
				
				<p></p>
				<div style="border:1px solid black;">
					
					<div class="comp" style="padding:3px 6px; background-color:#EEE; border-bottom:1px solid #CCC; line-height:20px;">
						Comparing currently published text <span style="color:#666;">[<b><?=outputUser($row->usrid, FALSE)?> &ndash; <?=$row->datetime?></b>]</span> 
						to submission text <span style="color:#666;">[<b><?=$x['datetime']?></b>]</span><br/>
						<a href="javascript:void(0);" onclick="$('.compsw').toggle();">Toggle code/HTML formatting</a>
					</div>
					
					<div style="padding:15px;">
						<div class="comp compsw" style="display:none;"><?=$comp?></div>
						<div class="comp compsw"><?=$comp2?></div>
					</div>
					
				</div>
				
				<?/*
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						<th width="50%" style="padding:3px 5px; background-color:#EEE;">Currently Published Text &ndash; <?=$row->datetime?></th>
						<td rowspan="2" style="border-right:1px solid #DDD;">&nbsp;&nbsp;&nbsp;</td>
						<td rowspan="2">&nbsp;&nbsp;&nbsp;</td>
						<th width="50%" style="padding:3px 5px; background-color:#EEE;">Submission Text &ndash; <?=$x['datetime']?></th>
					</tr>
					<tr>
						<td valign="top" style="padding-top:10px;">
							<div class="sw" style="font:normal 12px monospace;"><?=nl2br($row->text)?></div>
							<div class="sw" style="display:none;"><?=bb2html($row->text)?></div>
						</td>
						<td valign="top" style="padding-top:10px;">
							<div class="sw" style="font:normal 12px monospace;"><?=nl2br($d['text'])?></div>
							<div class="sw" style="display:none;"><?=bb2html($d['text'])?></div>
						</td>
					</tr>
				</table>
				*/
				
			}
			
			?>
			
			<p></p>
			<fieldset>
				<legend>Edit text before submitting</legend>
				<div style="display:none;"><?=outputToolbox("wiki-text", array("b", "i", "big", "small", "strikethrough", "a", "cite", "links"), "bb_code")?></div>
				<textarea name="in[text]" rows="5" id="wiki-text" style="width:98%" onfocus="$(this).attr('rows','20').prev().show();"><?=readableBB($d['text'])?></textarea>
			</fieldset>
			<?
			
		break;
		
		case "1":
			
			// NEW GAME //
			
			?>
			<p><big><?=bb2html($x['description'])?></big></p>
			<p><b>Denying this contribution will only leave the new game page unpublished.</b></p>
			
			<p><label><input type="checkbox" name="in[del_game]" value="1" onclick="alert('make sure to Approve instead of Deny');"/> <b>delete this game</b></label> from the database and disassociate any related content (sblog posts, forum tags, people credits, etc); 
				Automatically deny any related contributions pending.</p>
			<?
			
			break;
		
		case "3":
		
			// PUBLICATION //
			
			list($file, $platform_id, $title, $region, $date, $placeholder_img) = explode("|--|", $x['submission']);
			$tn = substr($file, 0, -4)."_sm.png";
			
			?>
			<p><table border="0" cellpadding="0" cellspacing="0" class="styled-form styled-form-alt">
				<tr>
					<th>Title</th>
					<td><input type="text" name="in[title]" value="<?=htmlent($title)?>" size="45"/></td>
				</tr>
				<tr>
					<th>Platform</th>
					<td>
						<select name="in[platform_id]">
							<option value="">Select a platform...</option>
							<?
							$query = "SELECT * FROM games_platforms WHERE platform != 'multiple' ORDER BY platform";
							$res   = mysqli_query($GLOBALS['db']['link'], $query);
							while($row = mysqli_fetch_assoc($res)) {
								echo '<option value="'.$row['platform_id'].'"'.($row['platform_id'] == $platform_id ? ' selected="selected" style="font-weight:bold;"' : '').'>'.$row['platform'].'</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th>Region</th>
					<td>
						<select name="in[region]">
							<?
							require($_SERVER['DOCUMENT_ROOT']."/bin/php/country_codes.php");
							while(list($k, $v) = each($cc)) {
								$k = strtolower($k);
								echo '<option value="'.$k.'"'.($k == $region ? ' selected="selected" style="font-weight:bold;"' : '').'>'.$v.'</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th>Release</th>
					<td><input type="text" name="in[release_date]" value="<?=$date?>" maxlength="10" size="8"/> YYYY-MM-DD</td>
				</tr>
				<tr>
					<th>Uploaded Box Art</th>
					<td>
						<input type="hidden" name="in[file]" value="<?=$file?>"/>
						<div id="upl1">
							<a href="/bin/temp/<?=$file?>" class="thickbox"><img src="/bin/temp/<?=$tn?>" alt="box art"/></a> 
							<p><input type="button" value="Upload a different image" onclick="toggle('upl2','upl1')"/></p>
						</div>
						<div id="upl2" style="display:none">
							<input type="file" name="file"/>
						</div>
						<p><label><input type="checkbox" name="in[placeholder_img]" value="1"<?=($placeholder_img ? ' checked="checked"' : '')?>/> This is a placeholder image and not the real cover image</p>
					</td>
				</tr>
			</table></p>
			<?
		
		break;
		
		case "4":
			
			// NEW GAME TRIVIA (FACTOID) //
			
			$txt = bb2html($d['fact'], "inline_citations");
			$txt = nl2br($txt);
			
			?>
			<p></p>
			<fieldset>
				<legend><a href="#edit" onclick="$('#do-edit').show().prev().hide();">Edit</a>|<a href="#preview" onclick="$('#do-edit').hide().prev().html('loading...').show().load('user-contributions-process.php', {ajax_do:'bb2html', _text:$('#trivia-text').val()});">Preview</a></legend>
				<div id="do-preview"><?=$txt?></div>
				<div id="do-edit" style="display:none;">
					<?=outputToolbox("trivia-text", array("b", "i", "big", "small", "strikethrough", "a", "cite", "links"), "bb_code")?>
					<textarea name="in[fact]" rows="10" id="trivia-text" style="width:99%"><?=readableBB($d['fact'])?></textarea>
				</div>
			</fieldset>
			<?
			
		break;
		
		case "6":
		
			// PERSON WORK //
			
			$pid = $d['pid'];
			$gid = $d['gid'];
			$roles = array();
			
			$q = "SELECT * FROM people WHERE pid='$pid' LIMIT 1";
			if($pdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
				
				//has a role in this game already?
				$q = "SELECT * FROM people_work WHERE gid='$gid' AND pid='$pid'";
				$res = mysqli_query($GLOBALS['db']['link'], $q);
				while($row = mysqli_fetch_assoc($res)) {
					$roles[] = $row;
				}
			
			//in the db?
			} elseif(substr($pid, 0, 9) == "new name:") {
				$name = str_replace("new name:", "", $pid);
				list($name, $name_url) = formatName($name);
				$res = mysqli_query($GLOBALS['db']['link'], "SELECT * FROM people WHERE name='$name' LIMIT 1");
				if(mysqli_num_rows($res)) {
					$pdat = mysqli_fetch_object($res);
					$pid = $pdat->pid;
				} else $not_in_db = TRUE;
			} else {
				echo "<p>Couldn't get data for PID #$pid</p></form>";
				$page->footer();
				exit;
			}
			
			
			if($roles) {
				?>
				<p></p>
				<div style="padding:5px; background-color:#FFB;">
					<b>Please note:</b> <?=$pdat->name?> is already credited as a developer of this game in the following role<?=(count($roles) > 1 ? 's' : '')?>:
					<ul>
						<?
						foreach($roles as $r) {
							echo '<li>'.$r['role'].($r['vital'] ? ' [VITAL]' : '').($r['notes'] ? ' '.$r['notes'] : '').'</li>';
						}
						?>
					</ul>
				</div>
				<?
			}
			
			$q = "SELECT title, title_url FROM games WHERE gid='$gid' LIMIT 1";
			$g = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
			
			?>
			
			<p>Game: <span id="newgidlink"><a href="/games/~<?=$g->title_url?>" target="_blank"><?=$g->title?></a> <span style="color:#AAA; background-color:#EEE;">[<a href="javascript:void(0)" onclick="toggle('newgidselect','newgidlink','inline');">Change</a>]</span></span>
				<span id="newgidselect" style="display:none">
					<select name="in[gid]">
						<?
						$query = "SELECT title, gid FROM games ORDER BY title";
						$res   = mysqli_query($GLOBALS['db']['link'], $query);
						while($row = mysqli_fetch_assoc($res)) {
							echo '<option value="'.$row['gid'].'"'.($row['gid'] == $gid ? ' selected="selected"' : '').'>'.$row['title'].'</option>';
						}
						?>
					</select>
				</span>
			</p>
				
			<p>Role: <input type="text" name="in[role]" value="<?=htmlSC($d['role'])?>" size="35"/></p>
			
			<p><label><input type="checkbox" name="in[vital]" value="1"<?=($d['vital'] ? ' checked="checked"' : '')?>/> This person's role was relatively vital to the creation of this game</label></p>
			
			<p>
				Include any notes, clarfications, or interesting facts about this person's role in this game:
				<div style="margin-top:2px"><textarea name="in[notes]" rows="5" cols="80"><?=$d['notes']?></textarea></div>
			</p>
			<?
			
			if($not_in_db) {
				?>
				<p>
				<fieldset style="border-color:#CCC">
					<legend>New Person?</legend>
					
					The user submitted the name '<?=$name?>' which isn't yet in the database.
					<?
					//check aliases for possible match
					$res = mysqli_query($GLOBALS['db']['link'], "SELECT name, name_url FROM people WHERE alias LIKE '%$name%' OR alias LIKE '%".htmlentities($name, ENT_QUOTES)."%'");
					if(mysqli_num_rows($res)) {
						?><p><b>Found some possible matches:</b><?
						while($row = mysqli_fetch_assoc($res)) {
							echo '<br/>&bull; <a href="/people/~'.$row['name_url'].'" target="_blank" class="arrow-link">'.$row['name'].'</a>';
						}
						?></p><?
					}
					?>
					<div style="margin:5px 0; padding:5px; background-color:#eee;">
					If the person is already in the database (but was submitted under a different or alternate name):<br/>
						<select name="in[pid]" onchange="if(this.options[this.selectedIndex].value=='') { document.getElementById('newpersoninfo').style.display='block'; } else { document.getElementById('newpersoninfo').style.display='none'; }">
							<option value="">This person is actually......</option>
							<?
							$query = "SELECT pid, name FROM people ORDER BY name";
							$res   = mysqli_query($GLOBALS['db']['link'], $query);
							while($row = mysqli_fetch_assoc($res)) {
								echo '<option value="'.$row['pid'].'">'.$row['name'].'</option>';
							}
							?>
						</select>
					</div>
					
					<div id="newpersoninfo">
						If this is a new person, please supply some info/clarifications below.
						<table border="0" cellpadding="5" cellspacing="0">
							<tr>
								<th>Name:</th>
								<td width="100%"><input type="text" name="in[name]" size="40" value="<?=htmlent($name)?>"/></td>
							</tr>
							<tr>
								<th>Title:</th>
								<td width="100%"><input type="text" name="in[title]" size="40"/></td>
							</tr>
							<tr>
								<td colspan="2">
									<label><input type="checkbox" name="in[prolific]" value="1"/> This person is a <b>prolific creator</b> and he/she should be highlighted whenever indexed amongst other people.</label>
									<p><label><input type="checkbox" name="in[not_creator]" value="1"/> This person is <b>not a game creator</b>; he/she has nothing to do with the actual development of videogames. They are just a plain old boring person.</label></p>
								</td>
							</tr>
						</table>
					</div>
				</fieldset>
				</p>
				<?
			} else {
				?><input type="hidden" name="in[pid]" value="<?=$pid?>"/><?
			}
			
		break;
		
		case "15":
			
			// PERSON PIC //
			?>
			<p>
				<dl>
					<?
					list($ssubjid, $pid) = explode(":", $x['supersubject']);
					if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/img/people/".$pid.".png")) {
						?>
						<dt><b>Current picture:</b></dt>
						<dd><img src="/bin/img/people/<?=$pid?>.png" alt="current pic"/></dd>
						<dd>Note: Processing the new picture will replace this one.</dd>
						<?
					}
					list($img, $tn) = explode("|--|", $x['submission']);
					?>
					<dt><b>New upload:</b></dt>
					<dd><img src="/bin/uploads/person_pic/<?=$img?>" alt="new pic"/></dd>
					<dd><img src="/bin/uploads/person_pic/<?=$tn?>" alt="new thumbnail"/></dd>
				</dl>
			</p>
			<?
			
		break;
		
		default:
			
			//a general text field
			
			print_r($x);print_r($d);
			
			if($x['subject']) {
				
				$arr = explode(":", $x['subject']);
				if(count($arr) < 4) die("Subject not complete");
				if($arr[3] == "") die("No subject field given");
				list($t, $k, $v, $f) = $arr;
				
				$inptext = readableBB($d[$f]);
				
				$q = "SELECT * FROM `$t` WHERE `$k` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $v)."' LIMIT 1";
				if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) {
					
					$q = "SELECT * FROM users_contributions WHERE subject = '".$x['subject']."' AND published = '1' ORDER BY `datetime` DESC LIMIT 1";
					$cdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
					
					//show comparisons with currently published text
					
					if($cdat->datetime) {
						if($cdat->datetime > $x['datetime']) $warnings[] = "The curently published text is newer than the submission -- publishing this verson may overwrite latter contributions not included here.";
					}
					
					require_once $_SERVER['DOCUMENT_ROOT'].'/bin/php/Text_Diff/Diff.php';
					require_once $_SERVER['DOCUMENT_ROOT'].'/bin/php/Text_Diff/Diff/Renderer/inline.php';
					
					$lines1 = explode("\n", $row[$f]);
					$lines2 = explode("\n", $d[$f]);
					
					$diff     = new Text_Diff('auto', array($lines1, $lines2));
					$renderer = new Text_Diff_Renderer_inline();
					$comp = $renderer->render($diff);
					$comp = nl2p($comp);
					$comp2 = bb2html($comp);
					
					?>
					
					<p></p>
					<div style="border:1px solid black;">
						
						<div class="comp" style="padding:3px 6px; background-color:#EEE; border-bottom:1px solid #CCC; line-height:20px;">
							Comparing currently published text <span style="color:#666;">[<b><?=outputUser($cdat->usrid, FALSE)?> &ndash; <?=$cdat->datetime?></b>]</span> 
							to submission text <span style="color:#666;">[<b><?=$x['datetime']?></b>]</span><br/>
							<?=($cdat->datetime ? 'Last update <b>'.timeSince($cdat->datetime).'</b> ago<br/>' : '')?>
							<a href="javascript:void(0);" onclick="$('.compsw').toggle();">Toggle code/HTML formatting</a>
						</div>
						
						<div style="padding:15px;">
							<div class="comp compsw" style="display:none;"><?=$comp?></div>
							<div class="comp compsw"><?=$comp2?></div>
						</div>
						
					</div>
					
					<?
					
				}
				
			}
			
			?>
			
			<p></p>
			<fieldset>
				<legend>Edit text before submitting</legend>
				<div style="display:none;"><?=outputToolbox("wiki-text", array("b", "i", "big", "small", "strikethrough", "a", "cite", "links"), "bb_code")?></div>
				<textarea name="in[<?=$f?>]" rows="5" id="wiki-text" style="width:98%" onfocus="$(this).attr('rows','20').prev().show();"><?=$inptext?></textarea>
			</fieldset>
			<?
		
		break;
		
		}
		
		?>
		
		<div style="margin:30px 0 0; padding:10px; background-color:#EEE;">
		
			<div style="display:table; border:1px solid #CCC; padding:5px;">
				<?
				$thisun = outputUser($x['usrid'], FALSE, FALSE);
				?>
				<label><input type="checkbox" name="in[credit_author]" value="1" checked="checked"/> <b>Credit <?=$thisun?></b> as the author this contribution</label><br/>
				<label><input type="checkbox" name="in[email_author]" value="1"/> <b>E-mail <?=$thisun?></b> with submission results and notes</label>
			</div>
			
			<p></p>
			
			<div style="float:left;">
				<div style="width:190px; display:table; border:1px solid #50C84D; padding:5px;">
					<label><input type="radio" name="deny" value="" checked="checked"/><b>Approve</b>; Publish Changes</label>
				</div>
				<div style="width:190px; display:table; margin:10px 0 0; border:1px solid #B61818; padding:5px;">
					<label><input type="radio" name="deny" value="1"/><b>Deny</b>; Remove all Changes</label>
				</div>
			</div>
			<div style="margin:0 6px 0 215px;">
				<label style="float:right;"><input type="checkbox" name="in[anonymous_review]" value="1"/>anonymous review</label>
				Include notes about this review:
				<textarea name="in[review_notes]" style="width:100%; height:49px;"></textarea>
			</div>
			
			<p><input type="submit" name="submitform" value="Submit" style="font:bold 15px arial;"/></p>
			
		</div>
	
	</form>
	<?
	
	$page->footer();
	exit;
	
}

?>
<a href="javascript:void(0)" onclick="toggle('catsandpts','catsandpts-link');" id="catsandpts-link" class="arrow-right">Categories & points</a>
<div id="catsandpts" style="display:none">
	
<a href="javascript:void(0)" onclick="toggle('new-cat-field-input','new-cat-field-link')" id="new-cat-field-link" class="arrow-right">New field</a>
<form action="user-contributions.php" method="post" id="new-cat-field-input" style="display:none">
	<fieldset>
		<legend>New Category Set</legend>
		<input type="hidden" name="action" value="new category"/>
		<input type="text" name="in[category]" value="0" size="3"/> Category
		<p><input type="text" name="in[description]" size="35"/> Description</p>
		<p><input type="text" name="in[points]" value="1" size="3"/> Points</p>
		<p><input type="submit" name="" value="Submit"/></p>
	</fieldset>
</form>

<br/><br/>
		
<form action="user-contributions.php" method="post">
	<input type="hidden" name="action" value="categories"/>
	<table border="0" cellpadding="5" cellspacing="0" class="plain">
		<tr>
			<th style="background-color:#EEE">Category</th>
			<th style="background-color:#EEE">Description</th>
			<th style="background-color:#EEE">Points</th>
		</tr>
		<?
		$query = "SELECT * FROM users_contributions_types ORDER BY category";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)) {
			?>
			<tr>
				<td><input type="hidden" name="in[ids][]" value="<?=$row['type_id']?>"/><input type="text" name="in[<?=$row['type_id']?>][category]" value="<?=$row['category']?>" size="3"/></td>
				<td><input type="text" name="in[<?=$row['type_id']?>][description]" value="<?=htmlent($row['description'])?>" size="35"/></td>
				<td><input type="text" name="in[<?=$row['type_id']?>][points]" value="<?=$row['points']?>" size="3"/></td>
			</tr>
			<?
		}
		?>
		<tr>
			<td colspan="3" style="text-align:right"><input type="submit" name="" value="Submit Changes"/></td>
		</tr>
	</table>
</form>
<br/><br/>
</div>

<h3 style="border-width:0;">Contributions Pending Approval</h3>
<?
$query = "SELECT * FROM users_contributions LEFT JOIN users_contributions_data USING (contribution_id) WHERE pending = '1' ORDER BY datetime ASC";
$res   = mysqli_query($GLOBALS['db']['link'], $query);
$pend_num = mysqli_num_rows($res);
if(!$pend_num) {
	?>Nothing pending right now<?
} else {
	?>
	<form action="user-contributions.php" method="post" name="contrlist" onsubmit="massSubmitContr();">
		<input type="hidden" name="in[credit_author]" value="1"/>
		<table border="0" cellpadding="5" cellspacing="0" width="100%" id="pendingc" class="plain">
			<tr>
				<th>Description</th>
				<th>Submitted By</th>
				<th>Date</th>
				<th>Review</th>
				<th>&nbsp;</th>
			</tr>
			<?
			while($row = mysqli_fetch_assoc($res)) {
				if(strlen($row['data']) > 50){
					$row['data'] = substr($row['data'], 0, 50).'&hellip;';
				}
				?>
				<tr>
					<td><?=bb2html($row['description'])?> <span style="color:#AAA;"><?=$row['data']?></span></td>
					<td><?=outputUser($row['usrid'])?></td>
					<td nowrap="nowrap"><?=FormatDate($row['datetime'])?></td>
					<td><a href="?id=<?=$row['contribution_id']?>" style="display:block; padding:4px; background-color:#CDE4F8; text-align:center;">Review</a></td>
					<td style="padding:0; text-align:center;"><label style="display:block; padding:5px;">
						<input type="checkbox" name="cids[]" value="<?=$row['contribution_id']?>" onclick="$(this).closest('tr').toggleClass('on');"/></label>
					</td>
				</tr>
				<?
			}
			?>
			<tr>
				<td colspan="4" style="background-color:#EEE; text-align:right;">
					<input type="hidden" name="deny" value="1" id="denych"/>
					<input type="hidden" name="enmass" value="1" id="denych"/>
					With selected: 
					<a href="javascript:void(0);" onclick="$('#denych').val(''); massSubmitContr();">Approve</a> | 
					<a href="javascript:void(0);" onclick="$('#denych').val('1'); massSubmitContr();">Deny</a>
				</td>
				<td style="background-color:#EEE; text-align:center;">
					<img src="/bin/img/arrow-small-gray-left.png"/>
				</td>
			</tr>
		</table>
	</form>
	<?
}

$page->footer();

?>