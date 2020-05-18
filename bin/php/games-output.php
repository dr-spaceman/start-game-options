<?
// JUMP!
// MEDIA //
// PREVIEW //
// OVERVIEW //
// SYNOPSIS //
// SCREENS //
// HERESAY //
// LINKS //
// RATING //
// PUBLICATIONS //

if(isset($_GET['sub'])) {
	$sub = $_GET['sub']; //optional val given via .htaccess symlink -- the value after the title url (/games/gid/title-url/SUBLOCATIONS)
	$subs = array();
	$subs = explode("/", $sub);
	if($subs[0] == "news") {
		$tag = "gid:".$_GET['gid'];
		include($_SERVER["DOCUMENT_ROOT"]."/news/tags.php");
		exit;
	}
}

use Vgsite\Page;
$page = new Page();
require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/games.php");
$gamepg = new gamepg;
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.forum.php");
$forums = new forum;
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.posts.php");
$posts = new posts;
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");

$gamepg->subs = $subs;
if($gamepg->subs[0] == "edit") $gamepg->edit_mode = TRUE;

if($_GET['gid']) {
	$query = "SELECT * FROM `games` WHERE gid='".$_GET['gid']."' LIMIT 1";
	if(!$GLOBALS['gdat'] = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query))) {
		$page->title = "Videogam.in game page not found";
		$page->error_404 = TRUE;
		$page->header();
		$page->footer();
		exit;
	}
} elseif($_GET['title_url']) {
	$query = "SELECT * FROM `games` WHERE title_url='".$_GET['title_url']."' LIMIT 1";
	if(!$GLOBALS['gdat'] = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query))) {
		$page->title = "Videogam.in game page not found";
		$page->error_404 = TRUE;
		$page->header();
		$page->footer();
		exit;
	}
}
$gamepg->gid = $gdat->gid;
$gamepg->getVars();

//redirect if not /games/GID/TITLE
if($_SERVER['HTTP_HOST'] != "localhost" && !$sub && $_SERVER['SCRIPT_URL'] != "/games/".$gdat->gid."/".$gdat->title_url) {
	header("Status: 301");
	header("Location: /games/$gdat->gid/$gdat->title_url");
	exit;
}

//form submit actions
$submit = $_POST['submit'];
$in = $_POST['in'];
if($submit == "Add Link") {
	// ADD LINK
	if($_POST['math'] != ($_POST['math1'] + $_POST['math2'])) {
		$errors[] = "Your math was wrong and the form could not be authenticated and submitted.";
	} else {
		if(!$in['site_name']) $errors[] = "No site name input";
		if(!preg_match("/^http/", $in['url'])) $errors[] = "URL not valid; Make sure it begins with http://";
		if(!$errors) {
			$datetime = date("Y-m-d H:i:s");
			$q = sprintf("INSERT INTO games_links (gid, site_name, url, description, usrid, datetime) VALUES 
				('$gdat->gid', '%s', '%s', '%s', '$usrid', '$datetime')",
				mysqli_real_escape_string($GLOBALS['db']['link'], $in['site_name']), 
				mysqli_real_escape_string($GLOBALS['db']['link'], $in['url']), 
				mysqli_real_escape_string($GLOBALS['db']['link'], $in['description']));
			if(mysqli_query($GLOBALS['db']['link'], $q)) {
				
				$q2 = "SELECT * FROM games_links WHERE datetime='$datetime' LIMIT 1";
				$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q2));
				$user = User::getById($usrid);
				$headers = "From: $usrname <".$user->data['email'].">\r\n" .
    			'Reply-To: ' . $user->data['email'] . "\r\n" .
    			'X-Mailer: PHP/' . phpversion();
				mail(getenv('NOTIFICATION_EMAIL'), "Videogam.in link submission", "The following link has been posted on the $gdat->title overview page:\n\n$in[site_name]\n$in[url]\n$in[description]\n\nTo remove this link: http://videogam.in/ninadmin/games-mod.php?id=$gdat->gid&what=links&delete=$dat->id\n", $headers);
				$results[] = "Your link has been successfully posted. Thanks for your contribution!";
				
				if($in['to'] == "email address") unset($in['to']);
				if($in['to'] && $in['message']) {
					//send link request
					$in['message'] = str_replace("[SITE NAME]", $in['site_name'], $in['message']);
					if(mail($in['to'], "Link exchange request", $in['message'], $headers)) {
						$results[] = "$in[to] has been emailed the link request";
					} else {
						$errors[] = "Couldn't email link request to $in[to]. Your message was:<p>$in[message]</p>";
					}
				}
			} else {
				$errors[] = "Couldn't add link to database $q";
			}
		}
	}
}

// subpages //
if($gamepg->subs[0] == "preview") {
	require($_SERVER['DOCUMENT_ROOT']."/bin/php/games-preview.php");
	exit;
} elseif($gamepg->subs[0] == "fans") {
	require($_SERVER['DOCUMENT_ROOT']."/bin/php/games-output-fans.php");
	exit;
} elseif($gamepg->subs[0] == "media") {
	require($_SERVER['DOCUMENT_ROOT']."/bin/php/games-output-media.php");
	exit;
} elseif($gamepg->subs[0] == "developers") {
	require($_SERVER['DOCUMENT_ROOT']."/bin/php/games-output-people.php");
	exit;
} elseif($gamepg->subs[0] == "music") {
	require($_SERVER['DOCUMENT_ROOT']."/bin/php/games-output-music.php");
	exit;
}

//////////////
// OVERVIEW //
//////////////


$page->title = ($gamepg->edit_mode ? '[EDIT] ' : '').$gdat->title." (".strip_tags($gamepg->platforms).") -- Videogam.in";

if($gamepg->edit_mode) {
	$page->style[] = "/bin/css/inline_edit.css";
	$page->javascript.= '<script type="text/javascript" src="/bin/script/inline_edit.js"></script><script type="text/javascript" src="/bin/script/ajaxfileupload.js"></script>';
	if(!$usrid) dieFullpage("Please log in to make changes.", "incl header");
}

$page->javascript = '<script type="text/javascript" src="/bin/script/jquery.livequery.js"></script>'.$page->javascript;
$page->javascript.= <<<EOF
<script type="text/javascript" src="/bin/script/games-overview.js"></script>
<script type="text/javascript" src="/bin/script/games-contribute.js"></script>
<script type="text/javascript">

var gid='$gdat->gid';

function changeCurrency(x) {
	var foo = x.split("|");
	document.getElementById("value-mark").innerHTML=foo[0];
	document.getElementById("value-avg").innerHTML=foo[1];
	document.getElementById("value-sum").innerHTML=foo[0]+foo[2];
}
function submitValuation() {
	var val = document.getElementById('input-valuation').value.replace(/[^0-9\.]*/g, '');
	if(!val) val = 0;
	if((val * 100) > 10000 || !val.match(/[0-9]+/g)) {
		alert("Please input a value between 0 and 100");
		return false;
	} else return true;
}
</script>
EOF;

if($gdat->creator == $usrid && $gdat->unpublished) {
	setcookie("dont_show_contribute_message", "", time()-60*60*24*100, "/"); //unset the cookie
	unset($_COOKIE['dont_show_contribute_message']);
}

$page->trackback_tag = "gid:".$gdat->gid;
$q = "SELECT text, usrid, datetime FROM wiki WHERE `field`='synopsis' AND subject_field='gid' AND subject_id='$gdat->gid' ORDER BY `datetime` DESC LIMIT 1";
if($synopsis = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
	$synopsis = bb2html($synopsis->text, "inline_citations");
	$synopsis = reformatLinks($synopsis);
	$page->meta_description = $gamepg->desc."; ".strip_tags($synopsis);
	$synopsis = nl2br($synopsis);
}
$page->header();

?><div id="game-overview">

<div id="right-col">

<div id="contribute">
	<?
	$showm = TRUE;
	$showcheckbox = TRUE;
	if($gamepg->edit_mode) $showm = FALSE;
	if($_COOKIE['dont_show_contribute_message']) $showm = FALSE;
	if($gdat->unpublished) {
		$showm = TRUE;
		$showcheckbox = FALSE;
	}
	if($showm) {
		?>
		<div id="contribute-message" class="message">
			<div class="container">
				<div class="container">
					<img src="/bin/img/buy_somethin.png" alt="Contribute somethin' will ya!" style="float:left; margin:0 10px 0 0; padding:2px 0 0 0;"/>
					<big>Contribute somethin' will ya!</big>
					<?
					$def_message = ($gdat->unpublished ? '<p><b>This game page has yet to be published.</b> It might be new and has yet to be reviewed by the editors, or it might have insufficient information that make it unable to yet meet publication standards.</p>' : '').'
						<p>Registered users can <span style="background:url(/bin/img/small_plus.png) no-repeat left center; padding-left:12px;">add new information</span>, 
							<span style="background:url(/bin/img/small_edit.png) no-repeat left center; padding-left:12px;">edit existing information</span>, and use their expertise 
							to <span style="background:url(/bin/img/small_flag.png) no-repeat left center; padding-left:12px;">flag errors for correction</span>.
						</p>
						';
					if($gdat->creator == $usrid) {
						if($_SESSION['user_rank'] < 4 && $gdat->unpublished) echo '<p>You are the creator of this game page! It hasn\'t been published yet, however, as the editors will have to review your contribution before it can become published and visible to other Videogam.in users.</p><p>In order for this game to be published, you must contribute <b>a short (one-paragraph) description or synopsis</b> and <b>at least one selection of box art</b>. We also recommend contributing as much other information as you can.</p><p>Should you navigate away, you can always access this page again by looking through your contributions page, accessible by clicking "you" on the top right.</p>';
						else echo '<p>You are the creator of this game page! Please improve it by adding some more stuff.</p><p>Note that you need to contribute <b>a short (one-paragraph) description or synopsis</b> and <b>at least one selection of box art</b> or your creation may be removed by the editors.</p>' . $def_message;
					} else echo $def_message;
					?>
					<p>
						<a href="javascript:void(0)" class="x" style="vertical-align:middle;">X</a> <a href="javascript:void(0);">Close this message</a> 
						<?=($showcheckbox ? ' &middot; <label style="color:#333"><input type="checkbox" id="dontshowcm"/>Don\'t show this message again</label>' : '')?>
					</p>
					<div class="point">&nbsp;</div>
				</div>
			</div>
		</div>
		<?
	}
	?>
	<div id="cef-nav" class="box">
		<ol>
			<li><a href="#" class="flag"><span>Flag for Removal or Errors</span></a></li>
			<li><a href="/games/<?=$gdat->gid?>/<?=$gdat->title_url?>/edit" rel="nofollow" class="edit"><span>Edit Something</span></a></li>
			<li class="on"><a href="javascript:void(0);" onclick="openContributionPanel()"><span>Contribute Something</span></a></li>
		</ol>
	</div>
	<div id="contribution-panel">
		<div class="container">
			<ol id="contribute-nav">
				<li><a href="#close" title="close contribution panel"><span>&times;</span></a></li>
				<li class="loading">
					<a href="#">
						&nbsp;
						<img src="/bin/img/big_plus.png" alt="add" border="0" class="off"/>
						<img src="/bin/img/loading_box_blue.gif" alt="loading" border="0" class="on"/>
					</a>
				</li>
				<li><a href="#trivia">Trivia</a></li>
				<li><a href="#link">Link</a></li>
				<li><a href="#review">Review</a></li>
				<li><a href="#pub">Box Art</a></li>
				<li><a href="#screens">Images</a></li>
				<li><a href="#video">Video</a></li>
				<li><a href="#person">Developer</a></li>
				<li style="display:none"><a href="#quote">Quote</a></li>
			</ol>
			<div id="contribute-space-wrapper"><div id="contribute-space">
				<div class="GCspace">Contribute something to this page by selecting an element above.<?=(!$usrid ? '<p style="margin:5px 0 0;">Links can be submitted by anyone, but everything else requires a Videogam.in user account. <a href="#login" class="arrow-right">Log in or Register</a></p>' : '')?></div>
			</div></div>
		</div>
	</div>
</div>

<?

// AD //
?>
<div class="box ad"><?=printAd("300x250")?></div>
<?

// PEOPLE //
if($gamepg->num_people) {
	$query = "SELECT id, pid, name, name_url, `alias`, role, vital, notes FROM people_work LEFT JOIN people USING (pid) WHERE people_work.gid='".$gdat->gid."'".(!$gamepg->edit_mode ? " AND vital='1'" : "")." ORDER BY name";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	$pids = array();
	$roles = array();
	$p = array();
	while($row = mysqli_fetch_assoc($res)) {
		$row = stripslashesDeep($row);
		if(!in_array($row['pid'], $pids)) $pids[] = $row['pid'];
		$arr[$row['pid']] = array(
			"name" => $row['name'],
			"name_url" => $row['name_url'],
			"alias" => $row['alias']
		);
		$roles[$row['pid']][] = array(
			"wid" => $row['id'],
			"role" => $row['role'],
			"vital" => $row['vital'],
			"notes" => $row['notes']
		);
	}
}

?>
<div id="people" class="box">
	<div class="plus-button">
		<a href="#top" onclick="GCtoggle('person');">
			<span>
				<span></span>
				<b> Add a Developer </b>
			</span>
		</a>
	</div>
	<h3><span>Developers</span></h3>
	<?
	if($gamepg->num_people) {
		?>
		<dl id="devlist">
			<?
			$toprow = "";
			$num_vpeople = 0;
			foreach($pids as $pid) {
				$i = 0;
				$p_roles = "";
				if(!$roles[$pid]) {
					$p_roles = '<dd class="role first">&nbsp;</dd>';
				} else {
					foreach($roles[$pid] as $r) {
						$i++;
						$num_vpeople++;
						if(!$toprow) {
							$c_toprow = " toprow";
							$toprow = 1;
						}
						$p_roles.= '<dd id="devrole-'.$r['wid'].'" class="role'.($r['vital'] ? ' role-vital' : '').($i == 1 ? '' : ' role-sub').$c_toprow.'">'.($r['role'] ? '<span id="ILedit-devrole_'.$r['wid'].'" class="editable">'.$r['role'].'</span>' : '<span id="ILedit-devrole_'.$r['wid'].'" class="editable editable-hidden">[ADD ROLE]</span>&nbsp;').'</dd>';
						
						$ilforms.= '
							<div id="IL-devrole_'.$r['wid'].'" class="ILform manual-output" style="right:5px; left:auto;">
								<input type="hidden" name="contr[devrole_'.$r['wid'].'][manual_input]" value="person role"/>
								<input type="hidden" name="contr[devrole_'.$r['wid'].'][type]" value="14"/>
								<input type="hidden" name="contr[devrole_'.$r['wid'].'][desc]" value="Update [pid='.$pid.'/]\'s role in [gid='.$gdat->gid.'/]"/>
								<input type="hidden" name="contr[devrole_'.$r['wid'].'][subj]" value="people_work:id:'.$r['wid'].':"/>
								<input type="hidden" name="contr[devrole_'.$r['wid'].'][ssubj]" value="pid:'.$pid.'"/>
								<div class="form" style="width:300px">
									<h5>Game Role</h5>
									<table border="0" cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<td width="100%" style="padding-right:15px;">
												<textarea name="contr[devrole_'.$r['wid'].'][role]" class="output IL-input-role" style="width:100%; height:16px; font:13px arial;">'.$r['role'].'</textarea>
											</td>
											<td nowrap="nowrap">
												<label><input type="checkbox" name="contr[devrole_'.$r['wid'].'][vital]" value="1" '.($r['vital'] ? ' checked="checked"' : '').' class="vital-check"/> Vital Role <a href="javascript:void(0);" class="tooltip tooltip-offset" title="This person\'s role was relatively vital to this game">?</a></label>
											</td>
										</tr>
									</table>
									<p>Include any notes or clarifications about this person\'s role in the game: <small style="color:#999">(No HTML, <a href="/bbcode.htm" target="_blank" class="arrow-link">BB Code</a> ok)</small></p>
									<p style="margin-right:6px"><textarea name="contr[devrole_'.$r['wid'].'][notes]" style="width:100%; height:5.2em;">'.$r['notes'].'</textarea></p>
									<p><input type="button" value="OK" class="submit"/></p>
								</div>
							</div>
						';
					}
				}
				?>
				<dt class="<?=$c_toprow?>"><a href="/people/~<?=$arr[$pid]['name_url']?>" title="<?=($arr[$pid]['alias'] ? 'AKA '.htmlSC($arr[$pid]['alias']) : '')?>"><?=$arr[$pid]['name']?></a></dt>
				<?=$p_roles?>
				<?
				$c_toprow = "";
			}
			?>
		</dl>
		<div class="footer">
			<a href="/games/<?=$gdat->gid?>/<?=$gdat->title_url?>/developers" style="font-weight:bold">Show all <?=$gamepg->num_people?> people</a> with development notes
		</div>
		<?
	} else { 
		echo '<div style="margin:3px 0 0">No developers credited yet.</div>';
	}
	?>
</div>
<?

// ALBUMS //
if($gamepg->num_albums) {
	?>
	<div id="albums" class="box">
		<h3><span>Music</span></h3>
		<ul>
			<?
			foreach($gamepg->albumdata as $row) {
				if (file_exists($_SERVER['DOCUMENT_ROOT']."/music/media/cover/thumb/".$row['albumid'].".png")) {
					$img = '<img src="/music/media/cover/thumb/'.$row['albumid'].'.png" alt="'.$row['title'].' '.$row['subtitle'].'" border="0"/>';
				} else {
					$img = '<img src="/music/graphics/none_sm.png" alt="no cover image available" border="0"/>';
				}
				?>
				<li><a href="/music/?id=<?=$row['albumid']?>"><?=$img?><span class="title"><?=$row['title']?><br/><?=($row['subtitle'] ? '<i>'.$row['subtitle'].'</i></span> ' : '</span>')?><span class="date">(<?=substr($row['datesort'], 0, 4)?>)</span></a></li>
				<?
			}
			?>
		</ul>
	</div>
	<?
}

// FORUMS //
?>
<div id="forums" class="box">
	<h3>Forum Topics</h3>
	<?
	$forums->associate_tag = 'gid:'.$gdat->gid;
	$forums->showTopicList();
	?>
</div>


<div id="groups" class="box">
	<h3>Groups</h3>
	<?
	$query = "SELECT g.*, COUNT(gm.group_id) AS members FROM groups_tags gt, groups_members gm, groups g WHERE gt.tag='gid:".$gdat->gid."' AND g.group_id=gt.group_id and gm.group_id=gt.group_id AND g.`status` != 'invite' GROUP BY gm.group_id ORDER BY members DESC, name DESC";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)) {
		?><ol id="groupslist"><?
		$i = 0;
		while($row = mysqli_fetch_assoc($res)) {
			if($i < 5) {
				$img = "no";
				if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/img/groups/".$row['group_id']."_icon.png")) $img = $row['group_id'];
				$p_name = $row['name'];
				/*if(strlen($row['name']) > 36) $p_name = substr($p_name, 0, 35)."&hellip;";
				else $p_name = $row['name'];
				$half = substr($p_name, 0, 13);
				if(!strstr($half, " ") && strlen($p_name) > 13) $p_name = $half."-".substr($p_name, 13, 36);*/
				?>
				<li>
					<a href="/groups/~<?=$row['name_url']?>" title="<?=htmlSC($row['name'])?>">
						<div class="img"><img src="/bin/img/groups/<?=$img?>_icon.png" alt="<?=htmlSC($row['name'])?>" border="0"/></div>
						<div class="name"><?=$p_name?></div>
					</a>
					<?=$row['members']?> member<?=($row['members'] > 1 ? 's' : '')?>
				</li>
				<?
			}
			$i++;
		}
		?>
		</ol>
		<div style="clear:both"><?
			if($i > 5) {
				?>
				<div style="margin-bottom:5px; padding:5px 0; color:#666;<?=($num_vpeople ? ' border-top:3px solid #CCC;' : '')?>">
					<a href="/groups/?find=gid:<?=$gdat->gid?>" style="font-weight:bold">See all <?=$i?> groups</a>
				</div>
				<?
			}
			?></div>
		<?
	} else {
		?>
		There are no related groups yet. <a href="/groups/create" class="arrow-right">Create one</a>
		<?
	}
	?>
</div>

</div><!-- #right-col -->

<div id="left-col" class="conts">

<?
// BOXES //

$platforms = array();
$query = "SELECT * FROM games_platforms WHERE platform != 'multiple' ORDER BY platform";
$res   = mysqli_query($GLOBALS['db']['link'], $query);
while($row = mysqli_fetch_assoc($res)) {
	$platforms[$row['platform_id']] = $row['platform'];
	$xplatforms[$row['platform']] = $row['platform_id'];
}

$i = 0;
$query = "SELECT * FROM games_publications LEFT JOIN games_platforms USING (platform_id) WHERE gid='$gdat->gid' ORDER BY release_date ASC";
$res   = mysqli_query($GLOBALS['db']['link'], $query);
if(!$pubnum = mysqli_num_rows($res)) {
	$fs_boxes[] = '<li id="fs-'.$row['id'].'" class="on primary nobox"><div>This game has no box art yet<p><a href="javascript:void(0)" class="add" onclick="GCtoggle(\'pub\')">Add some</a></p></div></li>';
} else {
	include_once($_SERVER['DOCUMENT_ROOT']."/bin/php/country_codes.php");
	
	$o_box = '<ol>';
	$i = 0;
	while($row = mysqli_fetch_assoc($res)) {
		$i++;
		if($i == $pubnum && !$has_primary) {
			//there's no primary pub!
			$row['primary'] = 1;
		}
		$row['unparsed_title'] = $row['title'];
		$row['title'] = htmlSC($row['title']);
		$row['country_name'] = strtoupper($row['region']);
		$row['country_name'] = $cc[$row['country_name']];
		if(!$row['release_date'] || $row['release_date'] == "0000-00-00") $row['release'] = "Unknown";
		else $row['release'] = formatDate($row['release_date'], 7);
		$row['alt'] = $row['title'].' for '.htmlSC($row['platform']).' box art ('.$row['country_name'].')';
		$row['o_pf'] = $row['platform'];
		if($row['o_pf'] == "Nintendo Entertainment System") $row['o_pf'] = '<acronym title="Nintendo Entertainment System">NES</acronym>';
		if($row['primary']) $has_primary = TRUE;
		
		//lg box
		if(file_exists($_SERVER['DOCUMENT_ROOT']."/games/files/".$gdat->gid."/".$gdat->gid."-box-".$row['id']."-sm.png")) {
			$href = '/games/files/'.$gdat->gid.'/'.$gdat->gid.'-box-'.$row['id'].'.jpg';
			$src = '/games/files/'.$gdat->gid.'/'.$gdat->gid.'-box-'.$row['id'].'-sm.png';
		} else {
			$href = '#';
			$src = '/bin/img/no_box-140.png';
		}
		$fs_boxes[] = '<li id="fs-'.$row['id'].'" class="gamebox'.($row['primary'] ? ' on primary' : '').'" onmouseover="relOn(\''.$row['id'].'\');"><a href="'.$href.'" class="thickbox" rel="publications" title="'.$row['alt'].'"><img src="'.$src.'" alt="'.$row['alt'].'" border="0"/></a></li>';
		
		//tn
		if(file_exists($_SERVER['DOCUMENT_ROOT']."/games/files/".$gdat->gid."/".$gdat->gid."-box-".$row['id']."-i35.png")) {
			$src = '/games/files/'.$gdat->gid.'/'.$gdat->gid.'-box-'.$row['id'].'-i35.png';
			$tn = '<a href="'.$href.'" title="'.$row['alt'].'" id="tn-'.$row['id'].'"><img src="'.$src.'" alt="'.$row['alt'].'" border="0"/></a>';
		} else {
			$tn = '<a href="#'.$row['id'].'" title="'.$row['alt'].'" id="tn-'.$row['id'].'"><img src="/bin/img/no_box.png" alt="No box art available" border="0" class="nobox"/></a>';
		}
		
		$o_box.= '
		<li id="gamebox-'.$row['id'].'"'.($row['primary'] ? ' class="primary on"' : '').'>
			<ul>
				<li class="tn">'.$tn.'</li>
				<li class="rel">
					<table border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td>
								<div class="container">
									<a href="javascript:void(0);" title="information about this publication" class="rel-info-link" onclick="$(this).siblings(\'.rel-info\').slideToggle();"><span>info</span></a>
									<h4'.(!$gamepg->edit_mode && $row['unparsed_title'] == $gdat->title ? ' class="hide"' : '').'>'.$row['title'].'</h4>
									<p>Release: <b>'.$row['release'].'</b> <img src="/bin/img/flags/'.$row['region'].'.png" alt="'.$row['country_name'].'" title="'.$row['country_name'].'" border="0"/></p>
									<p>Platform: <b>'.$row['o_pf'].'</b></p>';
									//get contr credits
									$query = "SELECT type_id, usrid, `datetime` FROM users_contributions WHERE (`subject` LIKE 'games_publications:id:".$row['id'].":%') AND `published`='1' ORDER BY `datetime`";
									$res2  = mysqli_query($GLOBALS['db']['link'], $query);
									if(mysqli_num_rows($res2)) {
										$userids = array();
										while($row2 = mysqli_fetch_assoc($res2)) {
											if(!in_array($row2['usrid'], $userids)) $userids[] = $row2['usrid'];
										}
										$o_box.= '<p class="rel-info">Contributors: ';
										foreach($userids as $id) $o_box.= '&nbsp;'.outputUser($id).'&nbsp;';
										$o_box.= '</p>';
									} else {
										$o_box.= '<p class="rel-info">No further information available...</p>';
									}
									if($gamepg->edit_mode) $o_box.= '<p style="display:block"><span id="ILedit-pub_'.$row['id'].'" class="editable" style="padding-left:12px; background:url(/bin/img/icons/edit.gif) no-repeat left center;">edit this publication</span></p>';
									$o_box.= '
								</div>
							</td>
						</tr>
					</table>
				</li>
			</ul>
		</li>
		';
		
		$ilforms.= '
			<div id="IL-pub_'.$row['id'].'" class="ILform manual-output" style="width:520px; top:174px !important;">
				<input type="hidden" name="contr[pub_'.$row['id'].'][manual_input]" value="game publication"/>
				<input type="hidden" name="contr[pub_'.$row['id'].'][type]" value="17"/>
				<input type="hidden" name="contr[pub_'.$row['id'].'][desc]" value="Update [gid='.$gdat->gid.'/] [url=/games/'.$gdat->gid.'/'.htmlSC($gdat->title_url).'#publication-'.$row['id'].']publication[/url]"/>
				<input type="hidden" name="contr[pub_'.$row['id'].'][subj]" value="games_publications:id:'.$row['id'].':"/>
				<input type="hidden" name="contr[pub_'.$row['id'].'][ssubj]" value="gid:'.$gdat->gid.'"/>
				<div class="form">
				
					<h5>Publication</h5>
					
					<a href="#" class="xdel" style="float:right" onclick="$(this).hide().next().show(); alert(\'Confirm delete by checking the box and submitting the form\');">Suggest Delete</a>
					<label style="display:none; float:right;"><input type="checkbox" name="contr[pub_'.$row['id'].'][delete]" value="1"/> Suggest DESTROY!!!</label>
					
					<input type="hidden" name="contr[pub_'.$row['id'].'][primary]" value="'.($row['primary'] ? '1' : '').'" class="pub-primary"/>
					<div style="margin-bottom:5px"><label><input type="radio" name="pub-primary" '.($row['primary'] ? 'value="1" checked="checked"' : 'value=""').'/>This is the primary publication</label></div>
					
					<table border="0" cellpadding="0" cellspacing="0" class="styled-form">
						<tr>
							<th>Upload New Box Art</th>
							<td>
								Please see <a href="javascript:void(0);" class="arrow-link" onclick="AGboxstandardsoverlay();">box art standards</a>
								<p><input type="file" name="pub_'.$row['id'].'"/></p>
								<p><label><input type="checkbox" name="contr[pub_'.$row['id'].'][placeholder_img]" value="1"'.($row['placeholder_img'] ? ' checked="checked"' : '').'/> This is a placeholder image and not the real cover image <a href="javascript:void(0)" class="tooltip tooltip-block" title="If the release has no box art (such as is the case with an upcoming or downloadable game), please upload a representative image to serve as a placeholder.">?</a></p>
							</td>
						</tr>
						<tr>
							<th>Title <a href="javascript:void(0)" class="tooltip tooltip-block" title="Input the full title of the publication, for example: &quot;Final Fantasy XII Collector\'s Edition&quot; will differentiate it from regular old Final Fantasy XII">?</a></th>
							<td><div style="margin-right:6px"><input type="text" name="contr[pub_'.$row['id'].'][title]" value="'.htmlSC($row['title']).'" class="required" style="width:100%"/></div></td>
						</tr>
						<tr>
							<th>Platform</th>
							<td>
								<select name="contr[pub_'.$row['id'].'][platform_id]">
									';
									foreach($platforms as $pf) {
										$ilforms.= '<option value="'.$xplatforms[$pf].'"'.($xplatforms[$pf] == $row['platform_id'] ? ' selected="selected"' : '').'>'.$pf."</option>\n";
									}
									$ilforms.= '
								</select>
							</td>
						</tr>
						<tr>
							<th>Region</th>
							<td>
								<select name="contr[pub_'.$row['id'].'][region]">
									<option value="us"'.($row['region'] == "us" ? ' selected="selected"' : '').'>North America</option>
									<option value="jp"'.($row['region'] == "jp" ? ' selected="selected"' : '').'>Japan</option>
									<option value="eu"'.($row['region'] == "eu" ? ' selected="selected"' : '').'>Europe</option>
									<option value="au"'.($row['region'] == "au" ? ' selected="selected"' : '').'>Australia</option>
								</select>
							</td>
						</tr>
						<tr>
							<th nowrap="nowrap">Release date</th>
							<td>
								<select name="contr[pub_'.$row['id'].'][year]">
									';
									$rd = explode("-", $row['release_date']);
									$msel[$rd[1]] = ' selected="selected"';
									
									for($j = (date('Y') + 2); $j >= 1980; $j--) {
										$ilforms.= '<option value="'.$j.'"'.($rd[0] == $j ? ' selected="selected"' : '').'>'.$j.'</option>'."\n";
									}
									$ilforms.= '
								</select> 
								<select name="contr[pub_'.$row['id'].'][month]">
									<option value="00">Month</option>
									<option value="01"'.$msel['01'].'>January</option>
									<option value="02"'.$msel['02'].'>February</option>
									<option value="03"'.$msel['03'].'>March</option>
									<option value="04"'.$msel['04'].'>April</option>
									<option value="05"'.$msel['05'].'>May</option>
									<option value="06"'.$msel['06'].'>June</option>
									<option value="07"'.$msel['07'].'>July</option>
									<option value="08"'.$msel['08'].'>August</option>
									<option value="09"'.$msel['09'].'>September</option>
									<option value="10"'.$msel['10'].'>October</option>
									<option value="11"'.$msel['11'].'>November</option>
									<option value="12"'.$msel['12'].'>December</option>
								</select> 
								<select name="contr[pub_'.$row['id'].'][day]">
									<option value="00">Day</option>
									';
									for($j = 1; $j <= 31; $j++) {
										if($j < 10) $j = '0'.$j;
										$ilforms.= '<option value="'.$j.'"'.($rd[2] == $j ? ' selected="selected"' : '').'>'.$j.'</option>'."\n";
									}
									$ilforms.= '
								</select> 
							</td>
						</tr>
						<tr>
							<th>&nbsp;</th>
							<td>
								<input type="button" value="OK" class="submit"/>
							</td>
						</tr>
					</table>
				</div>
			</div>
		';
	}
	$o_box.= '<li><ul id="add-game-box"><li class="tn"><a href="#top" class="tooltip" title="Add a Game Box"><span><img src="/bin/img/no_box.png" border="0"/></span></a></li></ul></li><li style="clear:left"></li></ol>';
}
?>

<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
		<td valign="top">
			
			<div id="gameboxes">
				<div id="gameboxes-container">
					<ol id="gamebox">
						<?
						foreach($fs_boxes as $fs) echo $fs;
						?>
					</ol>
					<a href="#add-to-collection" id="add-game-button">Add to My Games<span class="point"></span></a>
					
					<?=($pubnum ? '<h3>Publications</h3>'.$o_box : '')?>
					
				</div>
			</div>
		</td>
		<td valign="top" width="100%">
			
			<?
			// SYNOPSIS //
			?>
			<div id="synopsis">
				<h3><?=$gdat->title?> Synopsis</h3>
				<?=($synopsis ? '<div class="text">'.$synopsis.'</div><div id="synopsis-edit"><a href="/wiki.php?subj=gid/'.$gdat->gid.'/synopsis">edit synopsis</a> &middot; <a href="/wiki.php?subj=gid/'.$gdat->gid.'/synopsis&pg=history">history</a></div>' : 'There is no description for '.$gdat->title.' yet :( &nbsp; <a href="/wiki.php?subj=gid/'.$gdat->gid.'/synopsis" class="arrow-right">Write one</a>')?>
			</div>
			<?
			
			// FACTOIDS //
			$query = "SELECT * FROM games_trivia WHERE gid='$gdat->gid' ORDER BY `datetime` ASC";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			?>
			<div id="trivia">
				<h3>Notes & Factoids</h3>
				<ul>
					<?
					while($row = mysqli_fetch_assoc($res)) {
						$row = stripslashesDeep($row);
						$fact = bb2html($row['fact'], "inline_citations");
						$fact = reformatLinks($fact);
						?>
						<li class="item">
							<div class="fact">
								<span id="ILedit-trivia_<?=$row['id']?>" class="editable"><?=$fact?></span>
								<a href="#" class="info" title="show some information about this element"><span>info</span></a>
							</div>
							<ul>
								<?
								$query = "SELECT type_id, `datetime`, usrid FROM users_contributions WHERE (`subject` LIKE 'games_trivia:id:".$row['id'].":%') AND `published`='1' ORDER BY `datetime` ASC";
								$res2  = mysqli_query($GLOBALS['db']['link'], $query);
								if(!mysqli_num_rows($res2)) echo '<li>No further information available</li>';
								else {
									while($row2 = mysqli_fetch_assoc($res2)) {
										$lang = "edited";
										if($row2['type_id'] == "4") $lang = "created";
										echo '<li>'.outputUser($row2['usrid']).' '.$lang.' this on '.formatDate($row2['datetime']).'</li>';
									}
								}
								?>
							</ul>
						</li>
						<?
						$ilforms.= '
							<div id="IL-trivia_'.$row['id'].'" class="ILform manual-output" style="width:40%">
								<input type="hidden" name="contr[trivia_'.$row['id'].'][type]" value="17"/>
								<input type="hidden" name="contr[trivia_'.$row['id'].'][desc]" value="Edit [gid='.$gdat->gid.'/] [url=/games/'.$gdat->gid.'/#trivia-'.$row['id'].']trivia[/url]"/>
								<input type="hidden" name="contr[trivia_'.$row['id'].'][ssubj]" value="gid:'.$gdat->gid.'"/>
								<input type="hidden" name="contr[trivia_'.$row['id'].'][subj][games_trivia:id:'.$row['id'].':fact]"/>
								<input type="hidden" name="contr[trivia_'.$row['id'].'][on_null]" value="delete"/>
								<div class="form">
									<h5>Trivia</h5>
									No HTML allowed; Use <a href="/bbcode.htm" target="_blank" class="arrow-link">BB Code</a> for special formatting
									<p style="margin-right:6px">
										<textarea name="contr[trivia_'.$row['id'].'][field][games_trivia:id:'.$row['id'].':fact]" rows="8" class="output" style="width:100%;">'.readableBB($row['fact']).'</textarea>
									</p>
									<p><input type="button" value="OK" class="submit"/></p>
								</div>
							</div>
						';
					}
					?>
					<li style="background-image:none; padding-left:2px;"><a href="#top" onclick="GCtoggle('trivia')" class="add" style="padding-left:15px;">Contribute an interesting (and true!) factoid</a></li>
				</ul>
			</div>
			<?
			
			// PREVIEW //
			if($gamepg->has_preview) {
			?>
			<div class="coverage">
				<div class="spacer">&nbsp;</div><b>Hey, listen!</b> Check out Videogam.in's <a href="preview/" title="<?=$gdat->title?> preview">Preview of <?=$gdat->title?></a> 
				for detailed editorial information.
			</div>
			<?
			}
			
			// GUIDE //
			if($gamepg->guide_phrase) {
			?>
			<div class="coverage">
				<div class="spacer">&nbsp;</div><?=$gamepg->guide_phrase?>
			</div>
			<?
			}
			?>
			
		</td>
	</tr>
</table>

<?

// SCREENS //
$query = "SELECT * FROM media_tags 
	LEFT JOIN media USING (media_id) 
	WHERE media_tags.tag='gid:$gdat->gid' AND media.category_id='1' AND unpublished != '1' 
	ORDER BY media.datetime DESC 
	LIMIT 1";
$res = mysqli_query($GLOBALS['db']['link'], $query);
if(mysqli_num_rows($res)) {
?>
<div id="screens">
	<h3>Newest <?=$gdat->title?> Screen Shots</h3>
	<?
	while($row = mysqli_fetch_assoc($res)) {
		
		//captions
		$query = "SELECT * FROM media_captions WHERE media_id='".$row['media_id']."'";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		while($row2 = mysqli_fetch_assoc($res)) {
			$capts[$row2['file']] = $row2['caption'];
		}
		
		if($handle = opendir($_SERVER['DOCUMENT_ROOT'].$row['directory'])) {
			while(false !== ($file = readdir($handle))) {
				if($file != "thumbs" && $file != "." && $file != "..") $imgs[] = $file;
			}
		}
		sort($imgs);
		if($handle = opendir($_SERVER['DOCUMENT_ROOT'].$row['directory']."/thumbs/")) {
			while(false !== ($file = readdir($handle))) {
				if($file != "." && $file != "..") $tns[] = $file;
			}
		}
		$img_count = count($imgs);
		sort($tns);
		
		if($img_count != count($tns)) {
			echo "Error displaying gallery: thumbnail count doesn't match image count";
		} else {
			$vis = "";
			$invis = "";
			for($i=0; $i < $img_count; $i++) {
				if($i > 10) $invis.= '<a href="'.$row['directory'].'/'.$imgs[$i].'" class="thickbox" rel="gallery-'.$gallery_num.'" title="'.$capts[$imgs[$i]].'" style="display:none">'.$capts[$imgs[$i]].'</a>'."\n";
				else $vis.= '<td><a href="'.$row['directory'].'/'.$imgs[$i].'" class="thickbox" rel="gallery-'.$gallery_num.'" title="'.$capts[$imgs[$i]].'"><img src="'.$row['directory'].'/thumbs/'.$tns[$i].'" alt="'.$capts[$imgs[$i]].'"/></a></td>'."\n";
			}
			?>
			<div class="gallery">
				<table border="0" cellpadding="0" cellspacing="10">
					<tr>
						<?=$vis?>
					</tr>
				</table>
				<?=$invis?>
			</div>
			<p>
				<a href="#top" onclick="GCtoggle('screens')" class="add">Upload some screenshots</a> &middot; 
				<a href="/games/<?=$gdat->gid?>/<?=$gdat->title_url?>/media" class="arrow-right">more <i><?=$gdat->title?></i> media</a>
			</p>
			<?
		}		
	}
	?>
	<div style="display:none; padding-top:5px;"><a href="media" title="<?=$gdat->title?> media, screenshots, artwork, wallpaper, etc" class="arrow-right">More <i><?=$gdat->title?></i> media</a></div>
</div>
<div style="height:15px;">&nbsp;</div>
<?
}

// NEWS //
?>
<div id="news" style="float:left">
	<div class="plus-button">
		<a href="/posts/manage.php?action=newpost">
			<span>
				<span></span>
				<b> Post Something New </b>
			</span>
		</a>
	</div>
	<h3>Latest News, Blogs, and Content</h3>
	<?
	if(!$gamepg->num_news) {
		echo "No related posts published";
	} else {
		$q = "SELECT * FROM posts_tags LEFT JOIN posts USING (nid) WHERE tag='gid:".$gdat->gid."' AND unpublished != 1 AND pending != 1 AND privacy = 'public' ORDER BY posts.datetime DESC LIMIT 10";
		$r = mysqli_query($GLOBALS['db']['link'], $q);
		while($row = mysqli_fetch_assoc($r)) {
			$rows[] = $row;
		}
		if($gamepg->num_news > 10) {
			echo '<div class="h4">Showing a few of the most recent related posts. <a href="/posts/topics/gid:'.$gdat->gid.'/'.$gdat->title_url.'" class="arrow-right">Show all '.$gamepg->num_news.' posts</a></div>';
		}
		?><br/><?
		$posts->postsList($rows);
	}
	?>
</div>
	<br style="clear:both"/>
<?


// LINKS //
if($gamepg->num_links) {
	$auth = authenticate();
	?>
	<div id="links">
		<h3>
			Links 
			<span class="subheading">
				<a href="#top" onclick="GCtoggle('link')">Add a link</a> <span>|</span> 
				<a href="/links.php" class="arrow-right">More links</a>
			</span>
		</h3>
		<?
		
		$query = "SELECT * FROM games_links WHERE gid='$gdat->gid'";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		if(mysqli_num_rows($res)) {
			?><ul><?
			while($row = mysqli_fetch_assoc($res)) {
				echo '<li>';
				echo '<a href="'.$row['url'].'" target="_blank" class="link">'.$row['site_name'].'</a>';
				if($_SESSION['user_rank'] > 7 || ($_SESSION['user_rank'] == 7 && $row['usrid'] == $usrid)) echo ' <a href="javascript:void(0)" onclick="if(confirm(\'Delete this link?\')) window.location=\'/ninadmin/games-mod.php?id='.$gdat->gid.'&what=links&delete='.$row['id'].'\';" class="x">X</a>';
				if($row['description']) echo '<p>'.$row['description'].'</p>';
				echo "</li>\n";
			}
			?></ul><?
		}
		?>
	</div>
	<?
}

?>

</div><!-- #left-col -->

</div><!-- #game-overview -->

<div id="game-footer">
	<div class="container">
		<div class="links">
			<h5><a href="/games/~<?=$gdat->title_url?>"><?=$gdat->title?></a></h5> <span>&middot;</span> 
			<?=($num_news ? '<a href="news">News</a>' : 'News')?> <span>&middot;</span> 
			<?=($num_media ? '<a href="media">Media</a>' : 'Media')?> <span>&middot;</span> 
			<?
			if($gamepg->footer_medias) {
				foreach($gamepg->footer_medias as $x) echo $x.' <span>&middot;</span> '."\n";
			}
			?>
			<?=($has_preview ? '<a href="preview">Preview</a>' : 'Preview')?> <span>&middot;</span> 
			<?=($has_guide ? '<a href="guide">Game Guide</a>' : 'Game Guide')?> <span>&middot;</span> 
			<?=($gamepg->num_people ? '<a href="developers">Developers</a>' : 'Developers')?> <span>&middot;</span> 
			<a href="/forums/?tag=gid:<?=$gdat->gid?>">Forum Discussions</a> <span>&middot;</span> 
			<?=($gamepg->num_fans ? '<a href="fans">Fans</a>' : 'Fans')?> <span>&middot;</span> 
			<?=($gamepg->num_links ? '<a href="#links">Links</a>' : 'Links')?> <span>&middot;</span> 
			<?=($gamepg->num_albums ? '<a href="music">Music</a>' : 'Music')?>
		</div>
		<div style="margin:3px 0;">
			<b>This Page</b> <span>&middot;</span> 
			Created <?=timeSince($gdat->created)?> ago <?=$gamepg->creator?><span>&middot;</span> 
			<?=($gamepg->modified ? 'Last updated '.timeSince($gamepg->modified).' ago '.$gamepg->modifier.'<span>&middot;</span> ' : '')?>
			Viewed <?=addPageView($gdat->gid, 'gid:'.$gdat->gid, TRUE)?> times
		</div>
		<div>
			<span style="color:#DA4545">[</span> 
			<a href="/user-contributions.php?supersubject=gid:<?=$gdat->gid?>">History</a> <span>&middot;</span> 
			<a href="/user-contributions-faq.php">F.A.Q.</a> <span>&middot;</span> 
			<a href="#contribute-message" onclick="toggle('contribute-message','');">Contribute something</a>
			<span style="color:#DA4545">]</span> 
		</div>
	</div>
</div>

<?
if($gamepg->edit_mode) {
// INLINE EDIT FORMS //
?>

<div id="edit-mode-msg">
	<div class="container">
		You are currently viewing this page in Edit Mode. Click anything <span class="editable-on">editable</span> to make changes. 
		<a href="/game_edit_faq.htm" target="_blank">Help</a> &middot; 
		<a href="." class="arrow-right">End Edit Mode</a>
		<div class="message"><img src="/bin/img/black_point.png"/>Click me when you're done</div>
	</div>
</div>
<form action="/bin/php/inline_edit.php" method="post" enctype="multipart/form-data" id="ILmaster-form" name="ILmasterform" onsubmit="if(ILnosubmit) return false; confirm_exit=false;">
	<input type="button" value="Submit Changes" id="IL-master-submit-button" onclick="ILnosubmit=false; confirm_exit=false; document.ILmasterform.submit();"/>
	<input type="hidden" name="submit_changes" value="1"/>
	<input type="hidden" name="return_url" value="/games/<?=$gdat->gid?>/<?=$gdat->title_url?>"/>
	<input type="hidden" name="return_title" value="<?=htmlSC($gdat->title)?>"/>
	<input type="hidden" name="gid" value="<?=$gdat->gid?>"/>
	<input type="hidden" name="primary_ssubj" value="gid:<?=$gdat->gid?>"/>
	
	<div id="IL-title" class="ILform">
		<input type="hidden" name="contr[title][type]" value="17"/>
		<input type="hidden" name="contr[title][desc]" value="Change [gid=<?=$gdat->gid?>/] title"/>
		<input type="hidden" name="contr[title][subj]" value="games:gid:<?=$gdat->gid?>:title"/>
		<input type="hidden" name="contr[title][ssubj]" value="gid:<?=$gdat->gid?>"/>
		<input type="hidden" name="contr[title][manual_input]" value="game title"/>
		<div class="form">
			<h5>Game Title</h5>
			<p>As a general rule of thumb, input the title listed at <a href="http://en.wikipedia.org/wiki/Lists_of_video_games" target="_blank" class="arrow-link">Wikipedia</a>.</p>
			<p><textarea name="contr[title][field][games:gid:<?=$gdat->gid?>:title]" rows="2" cols="50" class="output"><?=$gdat->title?></textarea>
				<input type="button" class="submit" value="OK"/></p>
			<p>Please also input alternate titles/search terms on the right side of this page.</p>
		</div>
	</div>
	
	<div id="IL-keywords" class="ILform" style="left:auto; right:30px;">
		<input type="hidden" name="contr[keywords][type]" value="17"/>
		<input type="hidden" name="contr[keywords][desc]" value="Change [gid=<?=$gdat->gid?>/] keywords"/>
		<input type="hidden" name="contr[keywords][subj]" value="games:gid:<?=$gdat->gid?>:keywords"/>
		<input type="hidden" name="contr[keywords][ssubj]" value="gid:<?=$gdat->gid?>"/>
		<div class="form">
			<h5>Alternate Titles/Search Terms</h5>
			<p>Input alternate titles to assist searching for this game.</p>
			<p><small style="font-size:11px; color:#666;">For exmaple: soulcalibur II, soulcalibur 2, soul calibur II, soul calibur 2</small></p>
			<p><textarea name="contr[keywords][field][games:gid:<?=$gdat->gid?>:keywords]" rows="2" cols="50"><?=$gdat->keywords?></textarea>
				<input type="button" class="submit" value="OK"/></p>
		</div>
	</div>
	
	<div id="IL-genre" class="ILform manual-output"/>
		<input type="hidden" name="contr[genre][manual_input]" value="game list"/>
		<input type="hidden" name="contr[genre][type]" value="17"/>
		<input type="hidden" name="contr[genre][desc]" value="[gid=<?=$gdat->gid?>/] genres"/>
		<input type="hidden" name="contr[genre][subj]" value="games_genres:gid:<?=$gdat->gid?>:"/>
		<input type="hidden" name="contr[genre][ssubj]" value="gid:<?=($gdat->gid)?>"/>
		<div class="form">
			<h5>Genres</h5>
			Input one genre per line
			<p><textarea name="contr[genre][field]" rows="6" cols="50" class="output"><?
				$query = "SELECT genre FROM games_genres WHERE gid='$gdat->gid'";
				$res   = mysqli_query($GLOBALS['db']['link'], $query);
				while($row = mysqli_fetch_assoc($res)) {
					echo stripslashes($row['genre'])."\n";
				}
			?></textarea>
			<input type="button" class="submit" value="OK"/></p>
		</div>
	</div>
	
	<div id="IL-developers" class="ILform manual-output">
		<input type="hidden" name="contr[developers][manual_input]" value="game list"/>
		<input type="hidden" name="contr[developers][type]" value="17"/>
		<input type="hidden" name="contr[developers][desc]" value="[gid=<?=$gdat->gid?>/] development groups"/>
		<input type="hidden" name="contr[developers][subj]" value="games_developers:gid:<?=$gdat->gid?>:"/>
		<input type="hidden" name="contr[developers][ssubj]" value="gid:<?=($gdat->gid)?>"/>
		<div class="form">
			<h5>Developers</h5>
			Input one developer per line
			<p><textarea name="contr[developers][field]" rows="6" cols="50" class="output"><?
				$query = "SELECT developer FROM games_developers WHERE gid='$gdat->gid'";
				$res   = mysqli_query($GLOBALS['db']['link'], $query);
				while($row = mysqli_fetch_assoc($res)) {
					echo stripslashes($row['developer'])."\n";
				}
			?></textarea>
			<input type="button" class="submit" value="OK"/></p>
		</div>
	</div>
	
	<div id="IL-series" class="ILform manual-output">
		<input type="hidden" name="contr[series][manual_input]" value="game list"/>
		<input type="hidden" name="contr[series][type]" value="17"/>
		<input type="hidden" name="contr[series][desc]" value="[gid=<?=$gdat->gid?>/] series"/>
		<input type="hidden" name="contr[series][subj]" value="games_series:gid:<?=$gdat->gid?>:"/>
		<input type="hidden" name="contr[series][ssubj]" value="gid:<?=($gdat->gid)?>"/>
		<div class="form">
			<h5>Series</h5>
			Input one series per line
			<p><textarea name="contr[series][field]" rows="6" cols="30" class="output"><?
				$query = "SELECT series FROM games_series WHERE gid='$gdat->gid'";
				$res   = mysqli_query($GLOBALS['db']['link'], $query);
				while($row = mysqli_fetch_assoc($res)) {
					echo stripslashes($row['series'])."\n";
				}
			?></textarea>
			<input type="button" class="submit" value="OK"/></p>
		</div>
	</div>
	
	<? if($_SESSION['user_rank'] >= 8) { ?>
		<div id="IL-bgimg" class="ILform" style="left:auto; right:30px;">
			<input type="hidden" name="contr[bgimg][manual_input]" value="game bgimg"/>
			<input type="hidden" name="contr[bgimg][gid]" value="<?=$gdat->gid?>"/>
			<input type="hidden" name="upload_bgimg" value="1"/>
			<input type="hidden" name="currentfile" value="<?=$gamepg->bgimg?>"/>
			<div class="form">
				<h5>Background Image</h5>
				<p><input type="file" name="bgimgfile"/></p>
				<p>Align to the 
					<label><input type="radio" name="bgimgalign" value="left"/>Left</label>&nbsp;&nbsp;
					<label><input type="radio" name="bgimgalign" value="right" checked="checked"/>Right</label>
				</p>
				<p><input type="button" value="OK" class="submit"/></p>
			</div>
		</div>
		<div id="IL-status" class="ILform" style="left:auto; right:30px;">
			<input type="hidden" name="contr[status][manual_input]" value="game status"/>
			<input type="hidden" name="contr[status][gid]" value="<?=$gdat->gid?>"/>
			<div class="form">
				<h5>Status</h5>
				<label><input type="checkbox" name="contr[status][field][unpublished]" value="1"<?=($gdat->unpublished ? ' checked="checked"' : '')?>/>Unpublish, removing from all indexes</label>
				<p><label><input type="checkbox" name="contr[status][field][classic]" value="1"<?=($gdat->classic ? ' checked="checked"' : '')?>/>Classic</label></p>
				<p><label><input type="checkbox" name="contr[status][field][vapid]" value="1"<?=($gdat->vapid ? ' checked="checked"' : '')?>/>Vapid</label></p>
				<p><label><input type="checkbox" name="contr[status][field][featured]" value="1"<?=($gdat->featured ? ' checked="checked"' : '')?>/>Featured</label></p>
				<p><input type="button" value="OK" class="submit"/></p>
			</div>
		</div>
	<? } ?>
	<?=$ilforms?>
</form>

<?
} // end INLINE EDIT FORMS

$page->footer();
?>