<?

$page->freestyle = '
	#wiki-history { margin-top:3px; }
	#wiki-history TD { padding: 5px 15px 5px 0; border-top:1px solid #CCC; }
	#wiki-history .editsummary { font-size:90%; color:#999; }
	#chswitch A.arrow-toggle-on { text-decoration:none; color:black; }
';

if(!$chlisttype) $chlisttype = "recent";

$wl = FALSE;

if($chlisttype == "watchlist") {
	
	$wl = TRUE;
	
	$watching = array();
	$query = "SELECT * FROM pages_watch WHERE `usrid` = '$usrid' ORDER BY title";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) $watching[$row['title']] = $row;
	
	$numwatching = count($watching);
	
}

$since = $_GET['since'];
if(!$since) $since = ($usrid ? "lastlogin" : 3);
if($since == "lastlogin") {
	//load default changeset --  since last login
	$query = "SELECT * FROM pages_edit WHERE datetime > '$usrlastlogin' ORDER BY datetime DESC";
} else {
	//load a changeset by # days
	if(!is_numeric($since)) {
		$since = 3;
		$errors[] = "Invalid load date; Using 3 days by default.";
	}
	if($since > 30) {
		$since = 30;
		$errors[] = "Can't load more than 30 days.";
	}
	$query = "SELECT * FROM pages_edit WHERE datetime > DATE_ADD(CURDATE(), INTERVAL -".$since." DAY) ORDER BY datetime DESC";
}

$limit = 20;

$pgnum = ceil($_GET['page']);
if(!$pgnum) $pgnum = 1;
$pgmin = ($pgnum * $limit) - $limit;
$pgmax = $pgmin + $limit;

$changes = array();

$users_output = array();

$res   = mysqli_query($GLOBALS['db']['link'], $query);
$i = 0;
while($row = mysqli_fetch_assoc($res)) {
	
	//skip drafts
	if(!$row['published']) continue;
	
	//skip edits not on watchlist
	if($wl && !in_array($row['title'], array_keys($watching))) continue;
	
	$i++;
	if($i < $pgmin) continue;
	if($i > $pgmax) continue;
	$numchanges++;
	
	$titleurl = formatNameURL($row['title']);
	
	if($row['redirect_to']) {
		$row['edit_summary'] = '<i style="color:#888;">Redirected to [['.$row['redirect_to'].']]. Reason:</i> '.$row['edit_summary'];
	}
	
	if($row['rename_to']) {
		$row['edit_summary'] = '<i style="color:#888;">Renamed to [['.$row['rename_to'].']]. Reason:</i> '.$row['edit_summary'];
	}
	
	if($row['reverted_from']) {
		$q = "SELECT datetime FROM pages_edit WHERE session_id='".$row['reverted_from']."' LIMIT 1";
		$revdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
		$row['edit_summary'] = '<i style="color:#888;">Reverted from <a href="javascript:void(0)" onclick="window.open(\'history.php?view_version='.$row['reverted_from'].'\',\'view_wiki_window\',\'width=930,height=600,scrollbars=yes\');">'.formatDate($revdat->datetime, 10).'</a></i> &nbsp; '.$row['edit_summary'];
	}
	
	$bytech = $row['new_len'] - $row['old_len'];
	$bytech_color = "#888";
	if($bytech < 0) $bytech_color = "#E12B2B";
	elseif($bytech > 0) $bytech_color = "#37B93E";
	$bytech_fw = "normal";
	if($bytech > 500 || $bytech < -500) $bytech_fw = "bold";
	
	if(!$users_output[$row['usrid']]) $users_output[$row['usrid']] = outputUser($row['usrid'], FALSE);
	
	$changes[] = '
	<tr>
		<td nowrap="nowrap"><abbr title="'.$row['datetime'].'">'.timeSince($row['datetime'], TRUE).'</abbr></td>
		<td>
			[['.$row['title'].']] &nbsp; 
			<abbr title="'.number_format($row['new_len']).' bytes'.($_SESSION['user_rank'] >= 6 ? ' ['.$row['score'].']' : '').'" style="font-weight:'.$bytech_fw.'; color:'.$bytech_color.';">'.($bytech > 0 ? "+" : '').$bytech.'</abbr> &nbsp; 
			'.($row['minor_edit'] ? '<abbr title="The editor has flagged this as a minor edit" style="font-weight:bold;font-style:italic;">m</abbr> &nbsp; ' : '').'
			<span class="gray">
				(<a href="/pages/history.php?view_version='.$row['session_id'].'" title="permanent link to this edit version">view</a> 
				<a href="javascript:void(0)" onclick="window.open(\'/pages/history.php?compare=previous,'.$row['session_id'].'\',\'view_wiki_window\',\'width=930,height=600,scrollbars=yes\');" title="Compare this change with the previous one">compare</a> 
				<a href="/pages/history.php?title='.$titleurl.'" title="review complete edit history of this page">history</a>)
			</span> &nbsp; 
			'.$users_output[$row['usrid']].' &nbsp; 
			<span class="editsummary gray">'.$row['rev'].$row['edit_summary'].'</span>
		</td>
	</tr>
	';
	
}
?>