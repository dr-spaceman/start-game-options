<?

require_once("page.php");

if(!$credits){
	

$outp = "";
foreach($credits as $p) {
	
	$roles = array();
	$rowspan = 0;
	foreach($p['roles']['role'] as $k => $v) {
		if(is_array($v)) {
			$rowspan++;
			$roles[] = $v;
		} else {
			$roles[0][$k] = $v;
			if($k == "credited_role") $rowspan++;
		}
	}
	$pname = str_replace("[[", "", $p['name']);
	$pname = str_replace("]]", "", $pname);
	$q = "SELECT * FROM pages_games WHERE `title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $pname)."' LIMIT 1";
	$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
	$dat->platform = str_replace("[[", "", $dat->platform);
	$dat->platform = str_replace("]]", "", $dat->platform);
	
	$o = '';
	$o.= '
		<tr>
			<td class="cr-title" rowspan="'.$rowspan.'" valign="top">'.$p['name'].'</td>
			<td class="cr-pf" rowspan="'.$rowspan.'" valign="top">'.($dat->platform ? $dat->platform : '&nbsp;').'</td>
			<td class="cr-release altbg" rowspan="'.$rowspan.'" valign="top">'.($dat->release ? substr($dat->release, 0, 4) : '&nbsp;').'</td>
			<td class="cr-role">'.$roles[0]['credited_role'].'</td>
			<td class="cr-notes altbg">'.($roles[0]['notes'] ? $roles[0]['notes'] : '&nbsp;').'</td>
		</tr>
	';
	for($c = 1; $c < count($roles); $c++) {
		$o.= '
			<tr>
				<td class="cr-role">'.$roles[$c]['credited_role'].'</td>
				<td class="cr-notes altbg">'.($roles[$c]['notes'] ? $roles[$c]['notes'] : '&nbsp;').'</td>
			</tr>
		';
	}
	
	if(stristr($p['name'], "[[AlbumId:")) $outp_albums.= $o;
	else $outp_games.= $o;
	
}

?>
<div class="liquid2r">
	<?
	if($outp_games){
		?>
		<table border="0" cellpadding="0" cellspacing="0" class="data games">
			<tr>
				<th class="sortable">Games</th>
				<th><span style="visibility:hidden;">Platform</span></th>
				<th class="sortable">Release</th>
				<th>Role</th>
				<th>Notes</th>
			</tr>
			<?=bb2html($outp_games, "inline_citations")?>
		<?=(!$outp_albums ? '</table>' : '')?>
		<?
	}
	if($outp_albums){
		?>
		<?=(!$outp_games ? '<table border="0" cellpadding="0" cellspacing="0" class="data albums">' : '<tr><td colspan="5">&nbsp;</td></tr>')?>
			<tr>
				<th class="sortable">Albums</th>
				<th><span style="visibility:hidden;">Platform</span></th>
				<th class="sortable">Release</th>
				<th>Role</th>
				<th>Notes</th>
			</tr>
			<?=bb2html($outp_albums, "inline_citations")?>
		</table>
		<?
	}
	?>
</div>
<?