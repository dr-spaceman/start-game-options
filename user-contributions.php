<?
require ($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");
$page = new page;
require_once ($_SERVER['DOCUMENT_ROOT']."/bin/php/contribute.php");
require_once ($_SERVER['DOCUMENT_ROOT']."/bin/php/bbcode.php");

if($id = $_POST['load_contr_data']) {
	$q = "SELECT `data` FROM users_contributions_data WHERE contribution_id='$id' LIMIT 1";
	if(!$dat = mysql_fetch_object(mysql_query($q))) die("<dt>No further data available</dt><dd>&nbsp;</dd>");
	$data = array();
	$data = makeContrDataArr($dat->data);
	while(list($key, $val) = each($data)) {
		echo '<dt><i>'.$key.':</i>&nbsp;</dt><dd>'.str_replace("|,,|", "</dd><dd>", $val).'&nbsp;</dd>';
	}
	exit;
}

if($elem = $_POST['load_all_contr']) {
	
	list($key, $val) = explode(":", $elem);
	if($key == "usrid") {
		
		$query = "SELECT * FROM users_contributions LEFT JOIN users_contributions_data USING (contribution_id) WHERE usrid='$val' ORDER BY datetime DESC";
		$res   = mysql_query($query);
		outputContributions($res, "usrid:".$uid);
		
	} else {
		
		$query = "SELECT * FROM users_contributions LEFT JOIN users_contributions_data USING (contribution_id) WHERE supersubject='$elem' ORDER BY datetime DESC";
		$res   = mysql_query($query);
		outputContributions($res, $elem, FALSE);
			
	}
	
	exit;
	
}

$ssubj = $_GET['supersubject'];
$uid = $_GET['usrid'];
if($uid && !$usr = getUserDat($uid)) {
	$errors[] = "Couldn't get data for user id #$uid";
	unset ($uid);
}

$page->title = "Videogam.in / User Contributions".($uid ? ' / '.$usr->username : '');
$page->freestyle = '
	TD DL { display:none; margin:3px 0 0; padding:0; border-top:1px solid #DDD; }
	TD DL DT { margin:3px 0 0; padding:0 0 0 7px; color:#999; background:url(/bin/img/bullet-point.png) no-repeat left center; float:left; clear:left; }
	TD DL DD { margin:3px 0 0 150px; }
';
$page->javascript.= '
<script type="text/javascript">
	function loadContrData(id) {
		if( $("#space-"+id).is(":hidden") ) return;
		$.post(
			"/user-contributions.php",
			{ load_contr_data: id },
			function(t) {
				$("#space-"+id).hide().after(t);
			}
		);
	}
	
	function loadAllContributions(elem){
		$("#load-all-contr").html(\'<img src="/bin/img/loading-thickbox.gif" alt="loading"/>\');
		$.post(
			"/user-contributions.php", 
			{ load_all_contr: elem },
			function(t){
				$("#contrs").remove();
				$("#contrs-all").html(t);
			}
		);
	}
</script>
';
$page->header();

?>
<h2>User Contributions</h2>
<?

if($ssubj) {
	
	$query = "SELECT * FROM users_contributions 
		LEFT JOIN users_contributions_data USING (contribution_id) 
		WHERE supersubject='$ssubj' 
		ORDER BY datetime DESC";
	$res   = mysql_query($query);
	if(!$count = mysql_num_rows($res)) {
		echo "No contributions yet.";
	} else {
		
		?>
		<p><big><b><?=$count?></b> contributions made for <?=outputTag($ssubj, '', TRUE)?></big></p>
		<?
		
		$query.= " LIMIT 50";
		$res   = mysql_query($query);
		
		outputContributions($res, $ssubj);
		
	}
	
}
	
if($uid) {
	
	$query = "SELECT * FROM users_contributions LEFT JOIN users_contributions_data USING (contribution_id) WHERE usrid='$uid' ORDER BY datetime DESC";
	if(!$count = mysql_num_rows(mysql_query($query))) {
		echo $usr->username." has no contributions yet.";
	} else {
		
		$tpoints = 0;
		$query3 = "SELECT points FROM users_contributions LEFT JOIN users_contributions_types USING (type_id) WHERE usrid='$uid' AND published = '1'";
		$res3   = mysql_query($query3);
		while($row = mysql_fetch_assoc($res3)) {
			$tpoints = $tpoints + $row['points'];
		}
		
		?>
		<p>
			<big>
				<?=outputUser($uid)?> &middot; 
				<b><?=$count?></b> contribution<?=($count != 1 ? 's' : '')?> &middot; 
				<?=($tpoints ? $tpoints : "No")?> points
			</big>
		</p>
		<?
		
		$query.= " LIMIT 50";
		$res   = mysql_query($query);
		
		outputContributions($res, "usrid:".$uid, FALSE);
		
	}
	
}

$page->footer();



function outputContributions($res, $elem, $show_contributor = TRUE) {
	
	$query2 = "SELECT * FROM users_contributions_types";
	$res2   = mysql_query($query2);
	while($row = mysql_fetch_assoc($res2)) {
		$points[$row['type_id']] = $row['points'];
		$ctypes[$row['type_id']] = $row['description'];
	}
	
	?>
	<table border="0" cellpadding="5" cellspacing="0" width="100%" id="contrs" class="plain">
		<tr>
			<th>Date</th>
			<?=($show_contributor ? '<th>Contributor</th>' : '')?>
			<th>Contribution Description</th>
			<!--<th>Points</th>-->
		</tr>
		<?
		$cons = array();
		while($row = mysql_fetch_assoc($res)) {
			$i++;
			
			if($show_contributor) {
				if(!in_array($row['userid'], $cons)) $cons[$row['usrid']] = outputUser($row['usrid'], FALSE);
			}
			
			if($row['published'] && !$row['no_points']) {
				$pts = $points[$row['type_id']];
				$tpts = $tpts + $pts;
			} else $pts = 0;
			
			if($row['published']) $status = "Published";
			elseif($row['pending']) $status = "Pending";
			else $status = "Reviewed".($row['datetime_reviewed'] ? " on ".$row['datetime_reviewed'] : "").($row['reviewer'] ? " by ".outputUser($row['reviewer'], FALSE, FALSE) : "");
			
			if($i == 51) echo '<tr><td colspan="4" style="padding:0; background-color:#EEE;"><div style="height:5px;">&nbsp;</div></td></tr>';
			?>
			<tr>
				<td nowrap="nowrap"><?=formatDate($row['datetime'])?></td>
				<?=($show_contributor ? '<td>'.$cons[$row['usrid']].'</td>' : '')?>
				<td width="100%">
					<a href="javascript:void(0);" class="arrow-toggle" onclick="$(this).toggleClass('arrow-toggle-on'); $(this).siblings(':eq(1)').toggle(); loadContrData('<?=$row['contribution_id']?>');" style="float:right">Details</a>
					<div><?=bb2html($row['description'])?></div>
					<dl>
						<dt><?=$ctypes[$row['type_id']]?></dt>
						<dd>&nbsp;</dd>
						<dt><b>Status</b>:</dt>
						<dd><?=$status?></dd>
						<?=($row['review_notes'] ? '<dt><b>Review Notes</b>:</dt><dd>'.$row['review_notes'].'</dd>' : '')?>
						<?=($row['notes'] ? '<dt><b>Contributor\'s Note</b>:</dt><dd>'.$row['notes'].'</dd>' : '')?>
						<dt id="space-<?=$row['contribution_id']?>">Retrieving data...</dt>
					</dl>
				</td>
				<!--<td style="text-align:center"><?=($pts ? $pts : '0')?></td>-->
			</tr>
			<?
		}
		if($i == 50) {
			?>
			<tr>
				<td colspan="3" id="load-all-contr" style="background-color:#EEE; font-size:15px;">
					<b>Showing the latest 50 contributions</b> &middot; 
					<a href="javascript:void(0);" class="arrow-right" onclick="loadAllContributions('<?=$elem?>')">Show all contributions</a>
				</td>
			</tr>
			<?
		}
		?>
	</table>
	<div id="contrs-all"></div>
	<?
}

?>