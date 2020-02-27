<?
require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/bbcode.php");
if($_POST) {
	
	// ajax request for collabs
	
	require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");
	
	if(!$gid = $_POST['gid']) die("Error: no game ID given.");
	$gid2 = $_POST['gid2'];
	
	if(!$gid2) {
		
		
		
	} else {
		
		//show the collaborations on the 2nd game
		
		$query = "SELECT DISTINCT(pid) FROM people_work WHERE gid='$gid'";
		$res   = mysql_query($query);
		$pids1 = array();
		while($row = mysql_fetch_assoc($res)) {
			$pids1[] = $row['pid'];
		}
		
		$query = "SELECT DISTINCT(pid) FROM people_work WHERE gid='$gid2'";
		$res   = mysql_query($query);
		$pids = array();
		while($row = mysql_fetch_assoc($res)) {
			if(in_array($row['pid'], $pids1)) $pids[] = $row['pid'];
		}
		
		?>
		<div>
			<dl>
				<?
				$toprow = "";
				$num_vpeople = 0;
				foreach($pids as $pid) {
					
					$query = "SELECT pid, name, name_url, `alias`, role, vital, notes FROM people_work LEFT JOIN people USING (pid) WHERE people_work.pid='$pid' AND people_work.gid='$gid2'";
					$res   = mysql_query($query);
					$arr = array();
					$roles = array();
					while($row = mysql_fetch_assoc($res)) {
						$row = stripslashesDeep($row);
						$arr = array(
							"name" => $row['name'],
							"name_url" => $row['name_url'],
							"alias" => $row['alias']
						);
						$roles[] = array(
							"role" => $row['role'],
							"vital" => $row['vital'],
							"notes" => $row['notes']
						);
					}
					
					$i = 0;
					$p_roles = "";
					$vital = FALSE;
					foreach($roles as $r) {
						$i++;
						if($r['vital']) {
							$num_vpeople++;
							$vital = TRUE;
							if(!$toprow) {
								$c_toprow = " toprow";
								$toprow = 1;
							}
						}
						$p_roles.= '<dd class="role'.($i == 1 ? ' first' : ' notfirst').($vital ? ' vital' : ' notvital').$c_toprow.'">'.$r['role'].'</dd>';
						$p_roles.= "\n".($r['notes'] ? '<dd class="notes'.($vital ? ' vital' : ' notvital').'">'.bb2html($r['notes']).'</dd>'."\n" : '');
					}
					?>
					<dt class="<?=($vital ? 'vital' : 'notvital').$c_toprow?>"><a href="/people/~<?=$arr['name_url']?>" title="<?=($arr['alias'] ? 'AKA '.htmlSC($arr['alias']) : '')?>"><?=$arr['name']?></a></dt>
					<?=$p_roles?>
					<?
					$c_toprow = "";
				}
				?>
			</dl>
		</div>
		<?
		
	}
	
	exit;
	
}

$page->title = $gdat->title." developers -- Videogam.in";
$page->javascript.= '
<script type="text/javascript">
	function showCollabs(n) {
		var td = $("#collist-"+n);
		if( $(td).html() ) {
			$(td).html(\'\');
		} else {
			$(td).html(\'<div><img src="/bin/img/loading-thickbox.gif" alt="loading"/></div>\');
			$.post(
				"/bin/php/games-output-people.php",
				{ gid: "'.$gdat->gid.'",
					gid2: n
				},
				function(t) {
					$(td).html(t);
				}
			);
		}
	}
</script>';
$page->freestyle.= '
	#devlist {
		width: 49%;
	}
	#conts-people DL {
		margin: 0;
		padding: 0;
	}
	#conts-people DT {
		width: 48%;
		float: left;
		text-align: right;
		margin: 0 2% 0 0;
		padding: 10px 0 0; }
	#conts-people DT.vital {
		font-weight: bold; }
	#conts-people DD {
		margin: 0 0 0 50%;
		padding: 3px 0 0; }
	#conts-people DD.first {
		padding-top: 10px; }
	#conts-people DD.notes {
		padding-top: 0;
		font-size: 12px;
		color: #999; }
	#conts-people DD.notes A {
		color: #888; }
	#conts-people DD.notes A:HOVER {
		color: #999; }
	#people-collab-list {
		float: right;
		width: 50%;
	}
	#people-collab-list > .container {
		margin-left: 15px;
	}
	#people-collab-list H4 {
		margin: 0 0 3px;
		padding: 0 0 3px;
		font-size: 18px;
		font-weight: normal;
		color: #444;
		border-bottom: 1px solid #DDD;
	}
	#people-collab-list TABLE {
		margin: 5px 0 0;
		border-top: 1px solid #DDD;
		background-color: white; }
	#people-collab-list TH {
		padding: 4px 15px 4px 0;
		border-bottom: 1px solid #DDD; }
	#people-collab-list TD.date {
	 	padding: 4px 15px 4px 0;
	 	color: #555;
		border-bottom: 1px solid #DDD; }
	#people-collab-list TD.switch {
	 white-space: nowrap;
	 	padding: 4px 0;
		border-bottom: 1px solid #DDD; }
	#people-collab-list TD.switch A {
		padding-left: 9px;
		background: url(/bin/img/arrow-left-point.png) no-repeat 0 50%; }
	#people-collab-list TD.switch A.on {
		background-image: url(/bin/img/arrow-down-point.png); }
	#people-collab-list TD.list DIV {
		padding: 4px 0 14px 0;
		border-bottom: 1px solid #DDD;
		background-color: #F5F5F5; }
';
$page->header();

if(!$gamepg->num_people) {
	echo "No developers have been credited yet.<br/><br/><br/><br/><br/><br/>";
	$page->footer();
	exit;
}

$query = "SELECT pid, name, name_url, `alias`, role, vital, notes FROM people_work LEFT JOIN people USING (pid) WHERE people_work.gid='".$gdat->gid."' ORDER BY name";
$res   = mysql_query($query);
$pids = array();
$roles = array();
$p = array();
while($row = mysql_fetch_assoc($res)) {
	$row = stripslashesDeep($row);
	if(!in_array($row['pid'], $pids)) $pids[] = $row['pid'];
	$arr[$row['pid']] = array(
		"name" => $row['name'],
		"name_url" => $row['name_url'],
		"alias" => $row['alias']
	);
	$roles[$row['pid']][] = array(
		"role" => $row['role'],
		"vital" => $row['vital'],
		"notes" => $row['notes']
	);
}

?>
<div id="conts-people" class="conts">
	
	<?
	//collab list
	$query = "SELECT DISTINCT(pid) FROM people_work WHERE gid='$gdat->gid'";
	$res   = mysql_query($query);
	$pids = array();
	while($row = mysql_fetch_assoc($res)) {
		$pids[] = $row['pid'];
	}
	
	$gids = array();
	foreach($pids as $pid) {
		$query = "SELECT DISTINCT(gid) FROM people_work WHERE pid='$pid' AND gid != '' AND gid != '$gdat->gid'";
		$res   = mysql_query($query);
		while($row = mysql_fetch_assoc($res)) {
			$gids[$row['gid']]++;
		}
	}
	
	if(!$gids) {
		echo "There are no collaborations between this game's staff and another.";
	} else {
		arsort($gids);
		$out = "";
		while(list($g, $count) = each($gids)) {
			if($count > 1) {
				$q = "SELECT title, title_url FROM games WHERE gid='$g' LIMIT 1";
				$d = mysql_fetch_object(mysql_query($q));
				$q2 = "SELECT release_date FROM games_publications WHERE gid='$g' ORDER BY release_date ASC LIMIT 1";
				if($d2 = mysql_fetch_object(mysql_query($q2))) {
					$rel = substr($d2->release_date, 0, 4);
					if($rel == "0000") $rel = "&nbsp;";
				} else $rel = "&nbsp;";
				$out.= '<tr><th width="100%"><a href="/games/~'.$d->title_url.'/developers">'.$d->title.'</a></th><td class="date">'.$rel.'</td><td class="switch"><a href="javascript:void(0)" onclick="showCollabs('.$g.'); $(this).toggleClass(\'on\');">'.$count.' collaborations</a></td></tr><tr><td colspan="3" id="collist-'.$g.'" class="list"></td></tr>';
			}
		}
		if(!$out) echo "There are no collaborations between this game's staff and another.";
		else {
			?>
			<div id="people-collab-list">
				<div class="container">
					<h4>Collaborations</h4>
					Instances where the people credited in this game worked together on different games.
					<p style="margin:3px 0 0;">Click the game title to go to the full developer list, or else see those who collaborated together by clicking "N collaborations."</p>
					<table border="0" cellpadding="0" cellspacing="0"><?=$out?></table>
				</div>
			</div>
			<?
		}
	}
	?>
	
	<div id="devlist">
		<dl>
			<?
			$toprow = "";
			$num_vpeople = 0;
			foreach($pids as $pid) {
				$i = 0;
				$p_roles = "";
				$vital = FALSE;
				if(!$roles[$pid]) {
					$p_roles = '<dd class="role first">&nbsp;</dd>';
				} else {
					foreach($roles[$pid] as $r) {
						$i++;
						if($r['vital']) {
							$num_vpeople++;
							$vital = TRUE;
							if(!$toprow) {
								$c_toprow = " toprow";
								$toprow = 1;
							}
						}
						$p_roles.= '<dd class="role'.($i == 1 ? ' first' : ' notfirst').($vital ? ' vital' : ' notvital').$c_toprow.'">'.($r['role'] ? $r['role'] : '&nbsp;').'</dd>';
						$p_roles.= "\n".($r['notes'] ? '<dd class="notes'.($vital ? ' vital' : ' notvital').'">'.bb2html($r['notes']).'</dd>'."\n" : '');
					}
				}
				?>
				<dt class="<?=($vital ? 'vital' : 'notvital').$c_toprow?>"><a href="/people/~<?=$arr[$pid]['name_url']?>" title="<?=($arr[$pid]['alias'] ? 'AKA '.htmlSC($arr[$pid]['alias']) : '')?>"><?=$arr[$pid]['name']?></a></dt>
				<?=$p_roles?>
				<?
				$c_toprow = "";
			}
			?>
		</dl>
	</div>
	<div style="clear:right">&nbsp;</div>
</div>
<?

$page->footer();

?>