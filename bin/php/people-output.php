<?
require ($_SERVER['DOCUMENT_ROOT']."/index/inc/xhtml_page.php");
require ($_SERVER['DOCUMENT_ROOT']."/index/inc/class.news.php");
require ($_SERVER['DOCUMENT_ROOT']."/index/inc/class.people.php");
require ($_SERVER["DOCUMENT_ROOT"]."/index/inc/class.forum.php");

$pid = $_GET['pid'];
$interviewid = $_GET['interview'];
$collabid = $_GET['collaborate'];
$contribute = $_GET['contribute'];
$sortby = $_GET['sortby'];
$orderby = $_GET['orderby'];
if($sortby == "release") {
	$g_sortby = "release_date";
	$a_sortby = "datesort";
} else {
	$g_sortby = "title";
	$a_sortby = "title";
}
$persondir = str_replace("/people/", "", $_SERVER['REQUEST_URI']);
$persondir = str_replace("/index.php", "", $persondir);
$persondir = str_replace("?".$_SERVER['QUERY_STRING'], "", $persondir);
$persondir = str_replace("/", "", $persondir);
$person = str_replace("-", " ", $persondir);

$title = $person." profile @ The Square Enix People Database -- Square Haven";
$style[] = "/people/people.css";
$style[] = "/index/css/news.css";

//details
$query = "SELECT * FROM `people_index` WHERE `name` = '$person' LIMIT 1";
if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query))) {
	echo "No data found for '$person'.";
	Foot();
	exit;
}

$dat->bio = stripslashes_deep($dat->bio);
$meta_bio = strip_tags($dat->bio);
$meta_bio = substr($meta_bio, 0, 200);

$meta['keywords'] = $person.", Square Enix developers, Final Fantasy developers, Kingdom hearts developers, game developers, creators, composers, musicians, artists";
$meta['description'] = $person." profile at the Square Enix People Database -- ".$meta_bio;

Head();
include ("header_include.php");

//interview
if($interviewid) {
	$query = "SELECT * FROM `people_interviews` WHERE `id` = '$interviewid'";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	$row = mysqli_fetch_object($res);
	$row->title = stripslashes($row->title);
	$row->interview = stripslashes($row->interview);
	$row->source_name = stripslashes($row->source_name);
	$row->interview = reformatLinks($row->interview);
	//$row->interview = str_replace('<p></p>', '', '<p>' . preg_replace('#\n|\r#', '</p>$0<p>', $row->interview) . '</p>');
	?>
	<div id="interview">
	<div id="interview-heading">
		<h1>An interview with<br /><a href="/people/<?=$persondir?>/"><?=$person?></a></h1>
		<p><?=FormatDate($row->date)?></p>
		<?=($row->source_name ? '<p>Source: <a href="'.$row->source_url.'">'.$row->source_name.'</a></p>' : '')?>
		<p>Read <?=addPageView()?> times</p>
	</div>
	<h2><?=$row->title?></h2>
	<hr class="clear-both" />
	<div id="interview-text"><?=nl2br($row->interview)?></div>
	</div>
	<?
	Foot();
	exit;
}

//collaborate
if($collabid) {
	$query = "SELECT * FROM `people_index` WHERE `id` = '$collabid' LIMIT 1";
	if(!$dat2 = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query))) {
		echo "No data found for pid '$collabid'.";
		Foot();
		exit;
	}
		
	//collab games
	$query = "SELECT g.`indexid`, g.`id`, g.`title`, g.`platform`, g.`release_date` FROM `people_work` as p, `Games` as g WHERE (p.`pid` = '$dat->id' OR p.`pid` = '$dat2->id') AND p.`gid` != '' AND p.`gid` = g.`indexid` ORDER BY g.`$g_sortby` $orderby";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)) {
		while($row = mysqli_fetch_assoc($res)) {
			$g[$row[indexid]]++;
			if($g[$row[indexid]] > 1) {
				$row = stripslashes_deep($row);
				$i++;
				if($i % 2) $trclass = ' class="odd"';
				else $trclass = '';
				$games .= '<tr'.$trclass.'><td class="work-title"><a href="/games/'.$platforms[$row[platform]].'/'.$row[id].'/">'.$row[title].'</a></td><td class="work-platform">'.$platforms[$row[platform]].'</td><td class="work-release">'.$row[release_date].'</td></tr>';
			}
			$i = 0;
		}
	}
	
	//collab albums
	$query = "SELECT * FROM `people_work` WHERE (`pid` = '$dat->id' OR `pid` = '$dat2->id') and `albumid` != ''";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)) {
		unset($a);
		while($row = mysqli_fetch_assoc($res)) {
			$a[$row[albumid]]++;
			if($a[$row[albumid]] > 1) {
				$res2 = mysqli_query($GLOBALS['db']['link'], "SELECT `title`, `subtitle` from `albums` WHERE `albumid` = '$row[albumid]' ORDER BY `title`");
				while($row2 = mysqli_fetch_assoc($res2)) {
					$row2 = stripslashes_deep($row2);
					$i++;
					if($i % 2) $trclass = ' class="odd"';
					else $trclass = '';
					$albums .= '<tr'.$trclass.'><td class="work-title" colspan="2"><a href="/features/albums/?id='.$row[albumid].'">'.$row2[title].($row2[subtitle] ? " ".$row2[subtitle] : "").'</a></td><td class="work-release">'.$row2[datesort].'</td></tr>';
				}
				$i = 0;
			}
		}
	}
	
	?>
	<div id="work">
	<h2 style="border:none ! important; margin-top:0 ! important;">Collaborations between <a href="."><?=$person?></a> and <a href="../<?=str_replace(" ", "-", $dat2->name)?>"><?=$dat2->name?></a></h2>
	<?
	
	if($games) {
		?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr><th><h3>Games</h3>&nbsp;<a href="?collaborate=<?=$collabid?>&sortby=title&orderby=asc" title="order credits by ascending title">&uArr;</a><a href="?collaborate=<?=$collabid?>&sortby=title&orderby=desc" title="order credits by descending title">&dArr;</a></th>
			<th>platform</th>
			<th nowrap="nowrap">release <a href="?collaborate=<?=$collabid?>&sortby=release&orderby=asc" title="order credits by ascending release date">&uArr;</a><a href="?collaborate=<?=$collabid?>&sortby=release&orderby=desc" title="order credits by descending release date">&dArr;</a></th>
		</tr><?
		echo $games;
	}
	
	if($albums) {
		if(!$games) echo '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr>';
		else echo '<tr class="putborders">'
		?><th colspan="2"><h3>Albums</h3>&nbsp;<a href="?sortby=title&orderby=asc#credits" title="order credits by ascending title">&uArr;</a><a href="?sortby=title&orderby=desc#credits" title="order credits by descending title">&dArr;</a></th>
			<th nowrap="nowrap">release <a href="?sortby=release&orderby=asc#credits" title="order credits by ascending release date">&uArr;</a><a href="?sortby=release&orderby=desc#credits" title="order credits by descending release date">&dArr;</a></th></tr><?
		echo $albums;
	}
	
	if(!$games && !$albums)
		echo "none.";
	else
		echo '</table>';
	
	?></div><?
	
	Foot();
	exit;
}

//contribute
if($contribute) {
	
	$in = $_POST['in'];
	if($_POST['submit'] && $in) {
		$subject = 'Square Haven: People db contribution';
		$headers = "From: ".($in[email] ? $in[email] : "Square Haven user");
		$message = "The following is a user-submitted People Database contribution:\n\nperson: ".$person."\n";
		while (list($k, $v) = each($in)) {
			$message.= "$k: $v\n";
		}
		if(mail(getenv('NOTIFICATION_EMAIL'), $subject, $message, $headers)) {
			echo "Your contribution has been submitted to the Square Haven webmasters. Thanks!<br /><a href=\".\">Back to ".$person."'s profile</a>";
			Foot();
			exit;
		} else {
			echo 'There was an error and the query couldn`t be submitted. Please <a href="/contact.php">contact us</a> with this problem. Your input is below.<br /><br />';
			echo '<pre>'; print_r($in); echo '</pre>';
			Foot();
			exit;
		}
	}
	
	?>
	<h2 style="border:none ! important; margin-top:0 ! important;">Contribute to <a href="."><?=$person?></a> page</h2>
	
	<form action="?contribute=1" method="POST">
	<fieldset>
		<legend>About You</legend>
		<?	if($_REQUEST["valid"]) {
				?><input type="hidden" name="in[user]" value="<?=$_REQUEST['valid']?>" />
				You are logged in as <em><?=$_REQUEST["valid"]?></em>, so we already have some info about you. Add any other info here regarding yourself or how you would like to be credited:<br />
				<textarea name="in[user_info]" rows="2" cols="70"></textarea><?
			} else {
				?>You are not logged in. <a href="/register.php?loc=/people/<?=$persondir?>/&locs=contribute:1">Resgister</a> or <a href="/login.php?do=Login&loc=/people/<?=$persondir?>/&locs=contribute:1">log in</a> to automatically input your info here, or fill out the form below to be properly credited with your submission.<br />
				<br />
				<table border="0" cellpadding="0" cellspacing="0">
				<tr><td style="width:90px;"><label for="name">name:</label></td>
					<td><input type="text" name="in[name]" id="name" size="40" maxlength="100" /></td></tr>
				<tr><td><label for="email">e-mail:</label></td>
					<td><input type="text" name="in[email]" id="email" size="40" maxlength="100" /></td></tr>
				<tr><td><label for="website">website:</label></td>
					<td><input type="text" name="in[website]" id="website" size="40" maxlength="150" /></td></tr>
				<tr><td><label for="user_info">other info:</label></td>
					<td><textarea name="in[user_info]" id="user_info" rows="2" cols="70"></textarea></td></tr>
				</table><?
			}
			?>
	</fieldset>
	<br />
	<fieldset>
		<legend>Contribution</legend>
		Input the information or data you wish to contribute here:<br />
		<textarea name="in[contribution]" rows="10" cols="70"></textarea>
	</fieldset>
	<br />
	<input type="submit" name="submit" value="Submit Contribution" />
	</form>
	<?
	
	Foot();
	exit;
}


//format bio output
$printbio = trim($dat->bio);
$printbio = nl2br($printbio);
$printbio = reformatLinks($printbio);

?><table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td valign="top"><img src="../pictures/<?=(file_exists($_SERVER['DOCUMENT_ROOT']."/people/pictures/".$persondir.".png") ? $persondir : "nopicture")?>.png" alt="<?=$person?>" id="profile-image" />
	<?	if($dat->dob != "0000-00-00" || $dat->birthplace != "") {
			echo '<dl id="stuff">';
			if($dat->dob != "0000-00-00") echo '<dt>Birthdate:</dt><dd>'.FormatDate($dat->dob)."</dd>\n";
			if($dat->birthplace) echo '<dt>Birthplace:</dt><dd>'.$dat->birthplace."</dd>\n";
			echo "</dl>";
		}
	?></td>
<td width="100%" valign="top">
	<div id="bio">
		<h1><?=$person?><?=($dat->alias ? ' ('.$dat->alias.')' : "")?></h1>
		<?=($dat->title ? '<p id="person-title">'.stripslashes($dat->title).'</p>' : "")?>
		<p><?=$printbio?></p>
	<?	//associations
		if($dat->assoc_co || $dat->assoc_other) {
			?><div id="assoc"><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><?
			if($dat->assoc_co) {
				?><td valign="top"><dl><dt>Companies</dt><?
				$cos = explode("|", $dat->assoc_co);
				if(is_array($cos)) {
					foreach($cos as $co) {
						echo '<dd><a href="/people/?association='.urlencode($co).'">'.$co."</a></dd>\n";
					}
				} else echo $dat->assoc_co;
				?></dl></td><?
			}
			if($dat->assoc_other) {
				?><td valign="top"><dl><dt>Other Associations</dt><?
				$others = explode("|", $dat->assoc_other);
				if(is_array($others)) {
					foreach($others as $co) {
						echo '<dd><a href="/people/?association='.urlencode($co).'">'.$co."</a></dd>\n";
					}
				} else echo $dat->assoc_other;
				?></dl></td><?
			}
			?></tr></table></div><?
		}
		
		//gallery
		if($dat->pic_dir) {
			?><fieldset id="people-gallery">
			<legend><?=$person?> image gallery</legend>
			<? printThumbnailGallery($dat->pic_dir, $person); ?>
			</fieldset><?
		}
		
		 ?>
	</div>
</td></tr>
</table>

<?	$query = "SELECT * FROM `people_work` WHERE `pid` = '$dat->id'";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	$work_num = mysqli_num_rows($res);
?>

<div id="work">
<h2><a name="credits">Credits</a><span>credited in <?=$work_num?> publications</span></h2>

<?	//games
	$query = "SELECT p.*, g.`title`, g.`release_date`, FROM `people_work` as p, `Games` as g WHERE p.`pid` = '$dat->id' AND p.`gid` != '' AND p.`gid` = g.`indexid` ORDER BY g.`$g_sortby` $orderby";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)) {
		?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<th><h3>Games</h3>&nbsp;<a href="?sortby=title&orderby=asc#credits" title="order credits by ascending title">&uArr;</a><a href="?sortby=title&orderby=desc#credits" title="order credits by descending title">&dArr;</a></th>
			<th>Platform</th>
			<th>Release Date <a href="?sortby=release&orderby=asc#credits" title="order credits by ascending release date">&uArr;</a><a href="?sortby=release&orderby=desc#credits" title="order credits by descending release date">&dArr;</a></th>
			<th>Role</th>
		</tr><?
		while($row = mysqli_fetch_assoc($res)) {
			$row = stripslashes_deep($row);
			$row[notes] = reformatLinks($row[notes]);
			$i++;
			
			if($i % 2) $trclass = 'odd';
			else $trclass = 'even';
			
			if($row[release_date] > date("Y-m-d"))
				$row[release_date] = "In development";
			else
				$row[release_date] = FormatDate($row[release_date], 6);
				
			echo '<tr class="'.$trclass.'">';
			echo '<td class="work-title"><a href="/games/'.$platforms[$row[platform]].'/'.$row[id].'/">'.$row[title].'</a> <small>(<a href="../by-game/?gid='.$row[indexid].'">credits</a>)</small></td>';
			echo '<td class="work-platform" nowrap="nowrap">'.$platforms[$row[platform]].'</td>';
			echo '<td class="work-release" nowrap="nowrap">'.$row[release_date].'</td>';
			echo '<td class="work-release-region" nowrap="nowrap"><img src="/games/'.$row[region].'.gif" alt="'.$row[region].'" /></td>';
			echo '<td class="work-role">';
			if(strstr($row[role], ",")) {
				$roles = explode(",", $row[role]);
				foreach($roles as $r) {
					$r = trim($r);
					$p_roles.= '<a href="/people/role/'.$r.'">'.$r.'</a>, ';
				}
				echo substr($p_roles, 0, -2);
				unset($p_roles);
			} elseif(strstr($row[role], "&")) {
				$roles = explode("&", $row[role]);
				foreach($roles as $r) {
					$r = trim($r);
					$p_roles.= '<a href="/people/role/'.$r.'">'.$r.'</a>, ';
				}
				echo substr($p_roles, 0, -2);
				unset($p_roles);
			} else {
				echo '<a href="/people/role/'.$row[role].'">'.$row[role].'</a>';
			}
			echo '</td></tr>';
			echo ($row[notes] ? '<tr class="'.$trclass.'"><td colspan="5" class="work-notes">'.$row[notes].'</td></tr>' : "") . "\n";
		}
		$i = 0;
	} else $nogamework = 1;
	
	//albums
	$query = "SELECT `people_work`.*, `albums`.`title`, `albums`.`subtitle`, `albums`.`datesort` FROM `people_work`, `albums` WHERE `people_work`.`pid` = '$dat->id' and `people_work`.`albumid` != '' AND `people_work`.`albumid` = `albums`.`albumid` ORDER BY `albums`.`$a_sortby` $orderby";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)) {
		if($nogamework) echo '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr>';
		else echo '<tr><td colspan="5">'.pixel(1,10).'</td></tr><tr class="putborders">'
		?><th colspan="2"><h3>Albums</h3>&nbsp;<a href="?sortby=title&orderby=asc#credits" title="order credits by ascending title">&uArr;</a><a href="?sortby=title&orderby=desc#credits" title="order credits by descending title">&dArr;</a></th>
		<th nowrap colspan="2">release <a href="?sortby=release&orderby=asc#credits" title="order credits by ascending release date">&uArr;</a><a href="?sortby=release&orderby=desc#credits" title="order credits by descending release date">&dArr;</a></th>
		<th>role</th></tr>
		<?
		while($row = mysqli_fetch_assoc($res)) {
			$row = stripslashes_deep($row);
			$row[notes] = reformatLinks($row[notes]);
			$i++;
			if($i % 2) $trclass = "odd";
			else $trclass = "even";
			echo '<tr class="'.$trclass.'"><td class="work-title" colspan="2"><a href="/features/albums/?id='.$row[albumid].'">'.$row[title].($row[subtitle] ? " ".$row[subtitle] : "").'</a> <small>(<a href="../by-album/?albumid='.$row[albumid].'">credits</a>)</small></td><td class="work-release" colspan="2">'.FormatDate($row[datesort], 6).'</td><td class="work-role"><a href="/people/role/'.$row[role].'">'.$row[role].'&nbsp;</a></td></tr>';
			echo ($row[notes] ? '<tr class="'.$trclass.'"><td colspan="5" class="work-notes">'.$row[notes].'</td></tr>' : "") . "\n";
		}
		$i = 0;
	} else $noalbumwork = 1;
	
	?></table><?
	
	if($nogamework && $noalbumwork)
		echo pixel(1,4)."<br />none.<br />".pixel(1,5);
?>
</div>

<?	// news
	$query = "SELECT n.* FROM `people_news` as p, `news` as n WHERE p.`pid` = '$dat->id' and n.`id` = p.`nid` ORDER BY n.`date` DESC";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	$news_num = mysqli_num_rows($res);
	if($news_num) {
		?>
		<div id="news">
		<h2>News</h2>
		<?
		$news = new news();
		?><ul class="newslist"><?
		while($row = mysqli_fetch_assoc($res)) {
			echo "<li>" . $news->newsitem($row, "", "people/$persondir") . "</li>\n";
		}
		?></ul></div>
		<div style="clear:both;"><hr class="clear-both" style="margin:1em 0;" /></div><?
	}

	//interviews
	$query = "SELECT * FROM `people_interviews` WHERE `pid` = '$dat->id' ORDER BY `date` DESC";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	$interview_num = mysqli_num_rows($res);
	if($interview_num) {
		?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%" id="interviews"><tr><th colspan="2"><h2>Interviews</h2></th></tr>
		<?
		while($row = mysqli_fetch_assoc($res)) {
			$i++;
			if($i % 2) echo '<tr class="odd">';
			else echo '<tr class="even">';
			echo '<td nowrap="nowrap">'.FormatDate($row[date]).'</td><td width="100%"><a href="?interview='.$row[id].'">'.stripslashes($row[title]).'</a></td></tr>'."\n";
		}
		$i = 0;
		?></table><?
	}
	
	//links
	$query = "SELECT * FROM `people_links` WHERE `pid` = '$dat->id'";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)) {
		?>
		<div id="exlinks">
		<h2>External Links</h2>
		<dl><?
		while($row = mysqli_fetch_assoc($res)) {
			$i++;
			if($i % 2) $trclass = "odd";
			else $trclass = "even";
			echo '<dt class="'.$trclass.'"><a href="'.$row[url].'">'.stripslashes($row[site]).'</a></dt><dd class="'.$trclass.'">'.stripslashes($row[notes])."</dd>\n";
		}
		echo "</dl></div>";
	}
	
	
	//forum
	?><h2 style="border:0 !important; padding:0 !important;">Related Forum Topics</h2><?
	$forum = new forum();
	$forum->associate_tag = $person;
	$depreciate_forum_heading = TRUE;
	$forum->showForum();
	
	?>

<!-- table for separating content & ad -->
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td valign="top" width="100%">

<div id="collab">See where <?=$person?> collaborated with:<br /><select name="collabperson" onChange="javascript:window.location='?collaborate='+this.options[this.selectedIndex].value">
	<option value="" selected>...</option>
<?	$query = "SELECT * FROM `people_index` WHERE `prolific` = '1' and `not_creator` = '0' ORDER BY `name`";
	if($res = mysqli_query($GLOBALS['db']['link'], $query)) {
		echo '<optgroup label="Prolific Creators">';
		while($row = mysqli_fetch_assoc($res)) {
			if($row[name] != $person) echo '<option value="' . $row[id] .'">' . $row[name] . '</option>' . "\n";
		}
		echo "</optgroup>";
	}
	$query = "SELECT * FROM `people_index` WHERE `prolific` = '0' and `not_creator` = '0' ORDER BY `name`";
	if($res = mysqli_query($GLOBALS['db']['link'], $query)) {
		echo '<optgroup label="Other Creators">';
		while($row = mysqli_fetch_assoc($res)) {
			if($row[name] != $person) echo '<option value="' . $row[id] .'">' . $row[name] . '</option>' . "\n";
		}
		echo "</optgroup>";
	}
	$query = "SELECT * FROM `people_index` WHERE `not_creator` = '1' ORDER BY `name`";
	if($res = mysqli_query($GLOBALS['db']['link'], $query)) {
		echo '<optgroup label="Non-creators">';
		while($row = mysqli_fetch_assoc($res)) {
			if($row[name] != $person) echo '<option value="' . $row[id] .'">' . $row[name] . '</option>' . "\n";
		}
		echo "</optgroup>";
	}
?></select></div>

<div id="people-footer">
<h5>Information about this page</h5>
<ul>
	<?=(in_array($_COOKIE[valid], $admns) ? '<li>ADMIN: <a href="/admin/people.php?action=edit+person&name='.urlencode($person).'">edit this page</a></li>' : '')?>
	<li><a href="?contribute=1">Contribute to this page</a></li>
	<li>Contributors:
		<ul>
			<?	$cons = explode(',', $dat->contributors);
				foreach($cons as $c) {
					if(file_exists($_SERVER['DOCUMENT_ROOT'].'/editors/'.$c.'/index.php'))
						echo '<li><a href="/editors/'.$c.'/">'.$c."</a></li>\n";
					elseif(!strstr($c, '</'))
						echo '<li><a href="/user/?'.$c.'">'.$c."</a></li>\n";
					else echo "<li>$c</li>\n";
				} ?>
		</ul></li>
	<li>Created on <?=$dat->created?></li>
	<li>Last edited on <?=$dat->modified?></li>
	<li>Viewed <?=addPageView($dat->id, $_SERVER['SCRIPT_URL'], 1)?> times</li>
	<li><a rel="license"
href="http://creativecommons.org/licenses/by-nc-sa/3.0/">
<img alt="Creative Commons License" style="border-width:0; float:right; margin-left:5px;"
src="http://i.creativecommons.org/l/by-nc-sa/3.0/88x31.png" />
</a>The original 
<span xmlns:dc="http://purl.org/dc/elements/1.1/"
href="http://purl.org/dc/dcmitype/Text" rel="dc:type">work</span> here is
licensed under a <br/>
<a rel="license"
href="http://creativecommons.org/licenses/by-nc-sa/3.0/">Creative
Commons A-NC-SA 3.0
License</a>.</li>
</ul>
</div>

</td>
<td valign="top">
	<div class="people-ad"><?=PrintAd('300x250')?></div>
</td>
</tr>
</table>

<?
$no_foot_ad = 1;
Foot();
?>