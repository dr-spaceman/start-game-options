<? 
require ($_SERVER['DOCUMENT_ROOT']."/index/inc/xhtml_page.php");

$title  = "Square Enix People Database -- Square Haven";
$style  = "/people/people.css";
$meta['keywords'] = "Square Enix developers, Final Fantasy developers, Kingdom hearts developers, game developers, creators, composers, musicians, artists";
$meta['description'] = "Square Enix People Database, a comprehensive collection of information on the most vital people within or near the Square Enix sphere, including game devlopers, creators, music composers, artists, and regular more fallible people dedicated to the creations of those creators.";
mysql_select_db($db[name]);

$query = $_GET['query'];
$association = $_GET['association'];
$role = $_GET['role'];

if($query) {
	//search results
	
	$title.= ": search results";
	Head();
	include_once ("header_include.php");
	
	?><h2>Search Results</h2>
	<div id="index-platform"><table border="0" cellpadding="0" cellspacing="0" width="100%"><?
	$q = "SELECT * FROM `people_index` WHERE `name` LIKE '%$query%' OR `alias` LIKE '%$query%' ORDER BY `name` ASC";
	$r = mysqli_query($GLOBALS['db']['link'], $q);
	if(mysqli_num_rows($r)) {
		?><div class="searchresults"><dl><?
		while($row = mysqli_fetch_assoc($r)) {
			$i++;
			$row = stripslashes_deep($row);
			$link = str_replace(" ", "-", $row[name]);
			if($i == 1) echo "<tr>";
			?>
				<td class="profile-pic"><span><img src="<?=(file_exists("pictures/$link-tn.png") ? 'pictures/'.$link.'-tn.png' : 'pictures/nopicture-tn.png')?>" alt="<?=$row[name]?>" /></span></td>
				<td class="profile-info" width="30%"><a href="<?=$link?>" title="<?=$row[name]?> profile, biography, and credits" class="person-name"><?=$row[name]?></a>
					<dl><dt><?=$row[title]?></dt></dl></td>
			<?
			if($i == 3) {
				echo "</tr>";
				$i = 0;
			}
		}
		if($i == 1) echo "<td></td><td></td></tr>";
		if($i == 2) echo "<td></td></tr>";
		?><tr><td colspan="2" width="33%"></td><td colspan="2" width="33%"></td><td colspan="2" width="33%"></td></tr></table></div><?
	} else {
		?>No results<?
	}
	Foot();
	exit;

} elseif($association) {
	//associations
	$title.= ": Associaitons: ".$association;
	Head();
	include_once ("header_include.php");

	?><h2>Associations: <?=$association?></h2>
	<div id="index-platform"><table border="0" cellpadding="0" cellspacing="0" width="100%"><?
	
	$query = "SELECT * FROM `people_index` WHERE `assoc_co` LIKE '%$association%' OR `assoc_other` LIKE '%$association%' ORDER BY `name` ASC";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res) == 0) {
		echo "<tr><td>No associations found for '$association'.</td></tr>";
		Foot();
		exit;
	}
	while($row = mysqli_fetch_assoc($res)) {
		$i++;
		$row = stripslashes_deep($row);
		$link = str_replace(" ", "-", $row[name]);
		if($i == 1) echo "<tr>";
		?>
			<td class="profile-pic"><span><img src="<?=(file_exists("pictures/$link-tn.png") ? 'pictures/'.$link.'-tn.png' : 'pictures/nopicture-tn.png')?>" alt="<?=$row[name]?>" /></span></td>
			<td class="profile-info" width="30%"><a href="<?=$link?>" title="<?=$row[name]?> profile, biography, and credits" class="person-name"><?=$row[name]?></a>
				<dl><dt><?=$row[title]?></dt></dl></td>
		<?
		if($i == 3) {
			echo "</tr>";
			$i = 0;
		}
	}
	if($i == 1) echo "<td></td><td></td></tr>";
	if($i == 2) echo "<td></td></tr>";
	?><tr><td colspan="2" width="33%"></td><td colspan="2" width="33%"></td><td colspan="2" width="33%"></td></table></div><?




} elseif($role) {
	
	Head();
	include_once ("header_include.php");

	// empty role, we're at /people/role/ -> show role overview
	if ($role == "")
	{?>

		<h1>Roles</h1>

		<p id="note">No role specified.</p>

		<!-- todo: display some stuff here -->

	<?}

	else
	{	
		$role = mysqli_real_escape_string($GLOBALS['db']['link'], $role);
		$results = mysqli_query($GLOBALS['db']['link'], "SELECT p.id AS id, p.name, p.title FROM people_work pw, people_index p WHERE (pw.role LIKE '%$role%' OR p.title LIKE '%$role%') AND pw.pid = p.id GROUP BY p.name ORDER BY p.name ASC");

		?><h1 style="text-transform: capitalize"><?=$role?></h1>
		<p id="matches"><?=mysqli_num_rows($results)?> people found matching the role "<?=$role?>".</p>
		<?if (mysqli_num_rows($results) > 0){?>
		<p id="note"><strong>Note:</strong> this listing shows both specific roles (eg. Executive Producer on a game) and general roles (eg. President).</p>
		<?} else {?>
		<p id="note">According to our records, no one has filled the role "<?=$role?>".</p>
		<?}?>
		<ol id="roleResults">
			<?while ($row = mysqli_fetch_assoc($results)) {
				$safename = str_replace(" ", "-", $row[name]);
			?><li>
				<img src="/people/pictures/<?=(file_exists("pictures/$safename-tn.png") ? $safename.'-tn.png' : 'nopicture-tn.png')?>" alt="<?=$row[name]?>" />
				<strong><a href="/people/<?=$safename?>/"><?=$row['name']?></a></strong>
				<?=$row['title'] != "" ? (" (".$row['title'].")") : ""?><br/>
				<?
				// get the works and specific roles for which this person performed the given role
				$games = mysqli_query($GLOBALS['db']['link'], "SELECT g.indexid, g.title, pw.role FROM people_work pw, Games g WHERE pw.pid = $row[id] AND pw.gid = g.indexid AND pw.role LIKE '%$role%' ORDER BY g.release_date DESC");
				$albums = mysqli_query($GLOBALS['db']['link'], "SELECT a.title, a.subtitle, pw.role, a.albumid FROM sqhav_main2.album_list a, sqhav_main.people_work pw WHERE pw.role LIKE '%$role%' AND pw.pid = $row[id] AND a.albumid = pw.albumid ORDER BY a.datesort DESC");
				$numgames = 0;
				if ($games) $numgames = mysqli_num_rows($games);
				$numalbums = 0;
				if ($albums) $numalbums = mysqli_num_rows($albums);
				$numtotal = $numgames + $numalbums;
				$theRoles = array();
				if ($numgames > 0)
				{
					$total = min($numgames, 3);
					$i = 0;
					while ($row2 = mysqli_fetch_assoc($games)) {
						$currRole = preg_replace("/($role)/i", '<span class="highlight">$1</span>', $row2[role]);
						array_push($theRoles, "as $currRole on <a href=\"/games/link.php?id=$row2[indexid]\">$row2[title]</a>");
						$i++;
						if ($i == $total) break;
					}
				}
				if ($numalbums > 0) {
					$total = min($numalbums, 3);
					$i = 0;
					while ($row3 = mysqli_fetch_assoc($albums)) {
						$currRole = preg_replace("/($role)/i", '<span class="highlight">$1</span>', $row3[role]);
						array_push($theRoles, "as $currRole on <a href=\"/features/albums/?id=$row3[albumid]\">$row3[title] $row3[subtitle]</a>");
						$i++;
						if ($i == $total) break;
					}
				}
				if (count($theRoles) > 0) {
					?><small><em>worked on <?=$numtotal?> relevant publication<?=($numtotal > 1 ? "s" : "")?>, including 	</em><br/>
					<?=join($theRoles, "; ")?></small><?
				}
				else
				{
				?><small>May not have been involved with any related works, or just haven't actually done anything yet.<br/>Which makes you wonder why they're in our database. <a href="/people/<?=$safename?>/?contribute=1">Contribute information &raquo;</a></small><?
				}
				?>
			</li>
			<?}?>
		</ol><?
	}

} elseif($_GET['show'] == "index") {
	//index list
	
	Head();
	include_once ("header_include.php");
	
	//count # of people
	$query_count = "SELECT * FROM `people_index`";
	
	?><div id="index">
	<h2>People Index<span>listing <?=mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query_count))?> people in the database</span></h2>
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr><th>&nbsp;</th>
		<th>name&nbsp;<a href="?show=index&orderby=name&orderdir=asc" title="order index by ascending name">&uArr;</a><a href="?show=index&orderby=name&orderdir=desc" title="order index by descending name">&dArr;</a></th>
		<th>title</th>
		<th>birthdate&nbsp;<a href="?show=index&orderby=dob&orderdir=asc" title="order index by ascending birthdate">&uArr;</a><a href="?show=index&orderby=dob&orderdir=desc" title="order index by descending birthdate">&dArr;</a></th>
		<th><acronym title="Number of games or albums which this person is credited">credits</acronym>&nbsp;<a href="?show=index&orderby=credits&orderdir=asc" title="order index by ascending credits">&uArr;</a><a href="?show=index&orderby=credits&orderdir=desc" title="order index by descending credits">&dArr;</a></th>
	</tr><?
	
	//get # of credits
	$query = "SELECT `pid` FROM `people_work`";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$creds[] = $row[pid];
	}
	$creds = array_count_values($creds);
	arsort($creds);
	
	//decide order & $query
	if(!$orderby = $_GET['orderby'])
		$orderby = "name";
	if(!$orderdir = $_GET['orderdir'])
		$orderdir = "ASC";
	if($orderby == "credits") {
		//order by credits
		if($orderdir == "desc") asort($creds);
		while(list($k, $v) = each($creds)) {
			$query = "SELECT * FROM `people_index` WHERE `id` = '$k'";
			if($res = mysqli_query($GLOBALS['db']['link'], $query)) {
				while($row = mysqli_fetch_assoc($res)) {
					$i++;
					if($i % 2) $trclass = 'odd';
					else $trclass = 'even';
					
					$link = str_replace(" ", "-", $row[name]);
					if($row[title])
						$row[title] = stripslashes($row[title]);
					else
						$row[title] = '&nbsp;';
					?>
					<tr class="<?=$trclass?>"><td><?=(file_exists("pictures/$link-tn.png") ? '<img src="pictures/'.$link.'-tn.png" alt="'.$row[name].'" />' : '')?></td>
						<td><a href="<?=$link?>"<?=($row[prolific] ? ' style="font-size:15px;"' : '')?>><?=$row[name]?></a></td>
						<td><?=$row[title]?></td>
						<td><?=($row[dob] != "0000-00-00" ? FormatDate($row[dob], 6) : "")?></td>
						<td><?=$creds[$row[id]]?></td>
					</tr><?
				}
			}
		}
	} else {
		//
		$query = "SELECT * FROM `people_index` ORDER BY `$orderby` $orderdir";
		if($res = mysqli_query($GLOBALS['db']['link'], $query)) {
			while($row = mysqli_fetch_assoc($res)) {
				$i++;
				if($i % 2) $trclass = 'odd';
				else $trclass = 'even';
				
				$link = str_replace(" ", "-", $row[name]);
				if($row[title])
					$row[title] = stripslashes($row[title]);
				else
					$row[title] = '&nbsp;';
				?>
				<tr class="<?=$trclass?>"><td><?=(file_exists("pictures/$link-tn.png") ? '<img src="pictures/'.$link.'-tn.png" alt="'.$row[name].'" />' : '')?></td>
					<td><a href="<?=$link?>"<?=($row[prolific] ? ' style="font-size:15px; text-decoration:none; border-bottom:1px solid #FF3535;"' : '')?>><?=$row[name]?></a></td>
					<td><?=$row[title]?></td>
					<td><?=($row[dob] != "0000-00-00" ? FormatDate($row[dob], 6) : "")?></td>
					<td><?=$creds[$row[id]]?></td>
				</tr><?
			}
		}
	}
	?></table></div><?
} else {
	
	
	
	
	
	
	//default overview
	
	Head();
	include_once ("header_include.php");
	
	?>
<div id="people-overview">

<h2>People Overview</h2>

<div id="people-info" style="margin:1em 0 0 0; font-size:11px; text-align:justify;"><img src="info-toriyama.png" alt="akira toriyama is giving you informaiton" border="0" align="left" style="margin-right:5px;" />
<strong style="font-size:13px;">The Square Enix People Database</strong> is an attempt to list and compile information on the most vital people within or near the 
Square Enix sphere, including game creators, music composers, and regular more fallible people dedicated to 
the creations of those creators.<br />While most of the data here has been compiled by the <a href="/editors/">Square Haven 
staff</a>, we invite all visitors to make use of the user contributions feature that can be found at the bottom of every individual 
profile.</div>

<div id="prolific">

<h3>Prolific creators</h3>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<?	$query = "SELECT * FROM `people_index` WHERE `prolific` = '1' ORDER BY `name` ASC";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$i++;
		if($i % 2) $rowclass="odd";
		else $rowclass="even";
		$link = str_replace(" ", "-", $row[name]);
		if($row[title])
			$row[title] = stripslashes($row[title]);
		else
			$row[title] = '&nbsp;';
		echo '<tr class="'.$rowclass.'">';
		echo '<td>'.(file_exists("pictures/$link-tn.png") ? '<img src="pictures/'.$link.'-tn.png" alt="'.$row[name].'" />' : '<img src="pictures/nopicture-tn.png" alt="no picture" />').'</td>';
		echo '<td width="100%"><a href="'.$link.'/">'.$row[name].'</a>'.$row[title].'</td></tr>'."\n";
	}
	$i = 0; ?>
</table>
</div>

<div id="recent">
<h3>Recently added/updated</h3>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<?	$query = "SELECT * FROM `people_index` WHERE `restrictions` NOT LIKE '%limited visibility%' ORDER BY `modified` DESC LIMIT 10";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$i++;
		if($i % 2) $rowclass="odd";
		else $rowclass="even";
		$link = str_replace(" ", "-", $row[name]);
		if($row[title])
			$row[title] = stripslashes($row[title]);
		else
			$row[title] = '&nbsp;';
		echo '<tr class="'.$rowclass.'">';
		echo '<td>'.(file_exists("pictures/$link-tn.png") ? '<img src="pictures/'.$link.'-tn.png" alt="'.$row[name].'" />' : '<img src="pictures/nopicture-tn.png" alt="no picture" />').'</td>';
		echo '<td width="100%"><a href="'.$link.'/">'.$row[name].'</a>'.$row[title].'</td></tr>'."\n";
	}
	$i = 0; ?>
</table>
</div>

<div id="popular">
<h3>Most popular people</h3>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<?	$query = "SELECT pp.*, pc.`count` FROM `people_index` as pp, `pagecount` as pc WHERE pp.`restrictions` NOT LIKE '%limited visibility%' AND pc.`page` LIKE '/people/%' AND pc.`corresponding_id` = pp.`id` ORDER BY pc.`count` DESC LIMIT 10";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$i++;
		if($i % 2) $rowclass="odd";
		else $rowclass="even";
		$link = str_replace(" ", "-", $row[name]);
		if($row[title])
			$row[title] = stripslashes($row[title]);
		else
			$row[title] = '&nbsp;';
		echo '<tr class="'.$rowclass.'">';
		echo '<td>'.(file_exists("pictures/$link-tn.png") ? '<img src="pictures/'.$link.'-tn.png" alt="'.$row[name].'" />' : '<img src="pictures/nopicture-tn.png" alt="no picture" />').'</td>';
		echo '<td width="100%"><a href="'.$link.'/">'.$row[name].'</a>'.$row[title].'</td></tr>'."\n";
	}
	$i = 0; ?>
</table>
</div>

</div><?
	
}

Foot();
?>