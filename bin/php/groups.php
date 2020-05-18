<?
use Vgsite\Page;
$page = new Page();

require ($_SERVER['DOCUMENT_ROOT']."/bin/php/class.groups.php");
$groups = new groups;

$path = $_GET['path'];
if($path != "") {
	$groups->groupPage($path);
	exit;
}

// Groups Index //

$page->title = "Videogam.in / Groups";
$page->header();

$groups->header();

if($find = $_GET['find']) $findclause = " AND name LIKE '%".mysqli_real_escape_string($GLOBALS['db']['link'], $find)."%'";

if(!$orderby = $_GET['orderby']) $orderby = "name";
if($orderby != "name" && $orderby != "created" && $orderby != "members") $orderby = "name";
if($orderby == "name" || $orderby == "created") {
	$query = "SELECT g.*, COUNT(gm.group_id) AS members FROM groups_members gm, groups g WHERE g.group_id=gm.group_id AND g.`status` != 'invite'$findclause GROUP BY gm.group_id ORDER BY $orderby ".($orderby == "created" ? " DESC" : "ASC");
} else {
	$query = "SELECT g.*, COUNT(gm.group_id) AS members FROM groups_members gm, groups g WHERE g.group_id=gm.group_id AND g.`status` != 'invite'$findclause GROUP BY gm.group_id ORDER BY members, name DESC";
}
$groupnum = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query));

$max = 28;
if(!$pg = $_GET['pg']) $pg = 1;
if($pg > 1) {
	$min = ($pg - 1) * $max;
	$query.= " LIMIT $min, $max";
} else $query.= " LIMIT 0, $max";

?>
<div style="margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid #DDD;">
	<b><?=$groupnum?> Public Group<?=($groupnum != 1 ? 's' : '').($find ? ' found' : '')?></b> &middot; Sort by 
	<?=($orderby == "name" ? '<b>Name</b>' : '<a href="'.($find ? '?find='.$find : '.').'">Name</a>')?> &middot; 
	<?=($orderby == "created" ? '<b>Creation Date</b>' : '<a href="?orderby=created'.($find ? '&find='.$find : '').'">Creation Date</a>')?> &middot; 
	<?=($orderby == "members" ? '<b># of Members</b>' : '<a href="?orderby=members'.($find ? '&find='.$find : '').'"># of Members</a>')?>
</div>

<ol id="groupslist">
<?
$res = mysqli_query($GLOBALS['db']['link'], $query);
$i = 0;
while($row = mysqli_fetch_assoc($res)) {
	$img = "no";
	if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/img/groups/".$row['group_id']."_icon.png")) $img = $row['group_id'];
	if(strlen($row['name']) > 36) $p_name = substr($row['name'], 0, 35)."&hellip;";
	else $p_name = $row['name'];
	$half = substr($p_name, 0, 13);
	if(!strstr($half, " ")) $p_name = $half."-".substr($p_name, 13, 36);
	?>
	<li<?=($i % 4 == "0" ? ' style="clear:left"' : '')?>>
		<a href="~<?=$row['name_url']?>" title="<?=htmlSC($row['name'])?>">
			<div class="img"><img src="/bin/img/groups/<?=$img?>_icon.png" alt="<?=htmlSC($row['name'])?>" border="0"/></div>
			<div class="name"><?=$p_name?></div>
		</a>
		<?=$row['members']?> member<?=($row['members'] > 1 ? 's' : '')?>
	</li>
	<?
	$i++;
}
?>
</ol>
<?

if($groupnum > $max) {
	?><div id="pagenav"><?
	$pgs = ceil($groupnum / $max);
	for($i = 1; $i <= $pgs; $i++) {
		if($i == $pg) echo ' <b>'.($i == 1 ? 'Page ' : '').$i.'</b>';
		else echo ' <a href="?orderby='.$orderby.'&pg='.$i.($find ? '&find='.$find : '').'">'.($i == 1 ? 'Page ' : '').$i.'</a>';
	}
	?></div><?
}

$page->footer();

?>