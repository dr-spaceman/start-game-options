<?
require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");

if($_POST) {
	
	if(!$gid = $_POST['gid']) die("Error: no game ID given.");
	$gid2 = $_POST['gid2'];
	
	if(!$gid2) {
		
		//inital game list
		
		$query = "SELECT DISTINCT(pid) FROM people_work WHERE gid='$gid'";
		$res   = mysql_query($query);
		$pids = array();
		while($row = mysql_fetch_assoc($res)) {
			$pids[] = $row['pid'];
		}
		
		$gids = array();
		foreach($pids as $pid) {
			$query = "SELECT DISTINCT(gid) FROM people_work WHERE pid='$pid' AND gid != '' AND gid != '$gid'";
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
					$out.= '<tr><th><a href="/games/~'.$d->title_url.'#developers">'.$d->title.'</a></th><td class="date">'.$rel.'</td><td class="switch"><a href="javascript:void(0)" onclick="showCollabs('.$g.'); $(this).toggleClass(\'on\');">'.$count.' collaborations</a></td></tr><tr><td colspan="3" id="collist-'.$g.'" class="list"></td></tr>';
				}
			}
			if(!$out) echo "There are no collaborations between this game's staff and another.";
			else {
				?>
				Instances where the people above worked together again on different games.
				<p style="margin:3px 0 0;">Click the game title to go to the full developer list, or else see those who collaborated together by clicking "N collaborations."</p>
				<table border="0" cellpadding="0" cellspacing="0" id="people-collab-list"><?=$out?></table>
				<?
			}
		}
		
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
						$p_roles.= "\n".($r['notes'] ? '<dd class="notes'.($vital ? ' vital' : ' notvital').'">'.reformatLinks($r['notes']).'</dd>'."\n" : '');
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
	
}

?>