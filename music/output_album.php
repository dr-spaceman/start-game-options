<?
require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/class.posts.php");
$_posts = new posts();
require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/bbcode.php");

if(!$id = $_GET['id']) die("No album id given");

$Query = "SELECT * FROM albums WHERE albumid = '$id' LIMIT 1";
$Result = mysqli_query($GLOBALS['db']['link'], $Query);
$dat = mysqli_fetch_assoc($Result);

// checking alternative editions
$Querya = "SELECT * from albums_edition where album = '$id' limit 1";
$Resulta = mysqli_query($GLOBALS['db']['link'], $Querya);
if($row = mysqli_fetch_assoc($Resulta)) {
	if($duchk = $row['group']) {
		$Queryb = "SELECT e.album, e.group, e.revset, l.albumid, l.id from albums_edition as e, albums as l where e.group = '$duchk' and e.album = l.albumid and e.revset = 1 limit 1";
		$Resultb = mysqli_query($GLOBALS['db']['link'], $Queryb);
		while ($row = mysqli_fetch_assoc($Resultb)) {
			if ($row['album'] == $dat['albumid']) {
				$reportcard = $dat['albumid'];
			} else {
				$reportcard = $row['album'];
				$edappend = " (<a href=\"?id=".$row['albumid']."\">".$row['album']."</a> edition)";
				$synact = 1;
			}
	
		}
	}
} else {
	$reportcard = $dat['albumid'];
}
$album = $reportcard;

$page->title.= " / ".$dat['title']." ".$dat['subtitle'];
$page->javascripts[] = "album_profile.js";
$page->width = "fixed";

$page->first_section = array("id"=>"album-profile");

$page->header();

$name = strlen($dat['title']);
$name2 = strlen($dat['subtitle']);
$name3 = $name + $name2;

include("nav.php");

?>

<div id="side">
<?
	
//////////
// Side //
//////////

//admin controls
if($_SESSION['user_rank'] >= 6) {
	?>
	<table border="0" cellpadding="3" cellspacing="0" width="100%" class="plain" style="margin:0 0 15px 0;" id="pgadmin">
		<tr>
			<td style="text-align:center; background-color:#EEE;"><b>Admin</b></td>
			<td style="text-align:center"><a href="/ninadmin/albums.php?step=1&action=edit&editid=<?=$id?>">Edit Page</a></td>
			<td style="text-align:center"><a href="/ninadmin/albums.php?action=edit&editid=<?=$id?>&step=9">Update Commerce</a></td>
			<td style="text-align:center"><a href="#" onclick="document.getElementById('pgadmin').style.display='none';" class="x" title="hide this bar">X</a></td>
		</tr>
	</table>
	<?
}

//rating & collection
if($usrid) {
	
	//avg
	$q = "SELECT COUNT(albumid) AS count, AVG(rating) AS avg FROM `albums_ratings` WHERE albumid='$id'";
	if(!$grouprtg = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
		$grouprtg->count = '0';
		$grouprtg->avg = '0';
	}
	$grouprtg->avg = round($grouprtg->avg);
	
	//user
	$q = "SELECT * FROM albums_ratings WHERE albumid='$id' AND usrid='$usrid' LIMIT 1";
	if($rtg = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
		?>
		<script type="text/javascript">var user_album_rating = '<?=$rtg->rating?>';</script>
		<?
	}
	
	//collection
	$query = "SELECT * FROM albums_collection WHERE albumid='$id' AND usrid='$usrid'";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$coll[$row['action']] = TRUE;
	}
	
	$love = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM pages_fan WHERE `title` = 'AlbumId:$id' AND usrid='$usrid' LIMIT 1"));
	
}
?>
<div id="collection">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<th colspan="2" class="top">Average Rating <span style="font-weight:normal !important; color:#AAA;">(<span style="color:#777"><?=$grouprtg->count?></span>)</span></th>
			<td colspan="2" class="top rating">
				<span class="rating-value-<?=$grouprtg->avg?>" style="width:78px !important;"><img src="/bin/img/pixel.png" width="78" height="14" alt="star rating spacer"/></span>
			</td>
		</tr>
		<tr>
			<th colspan="2">Your Rating</th>
			<td colspan="2" class="rating">
				<span id="star-rating-bg"<?=($rtg ? ' class="rating-value-'.$rtg->rating.'"' : '')?>><img src="/bin/img/pixel.png" width="93" height="14" border="0" alt="star rating spacer" usemap="#star_map"/><img src="/bin/img/loading-green-arrows.gif" alt="loading" border="0" id="loading-rating" style="display:none"/></span>
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox"<?=($coll['collecting'] ? ' checked="checked"' : '')?> id="check-collecting" onclick="setCollection('collecting', '<?=$id?>', '<?=$usrid?>')"/><img src="/bin/img/loading-gray-arrows.gif" alt="loading" border="0" id="loading-collecting" style="display:none"/></td>
			<td class="label"><label for="check-collecting" style="background-image:url(/music/graphics/add-collection.png);">In your collection</label></td>
			<td class="input"><input type="checkbox"<?=($love ? ' checked="checked"' : '')?> id="check-listening" onclick="setCollection('listening', '<?=$id?>', '<?=$usrid?>')"/><img src="/bin/img/loading-gray-arrows.gif" alt="loading" border="0" id="loading-listening" style="display:none"/></td>
			<td class="label"><label for="check-listening" style="background-image:url(/music/graphics/add-playlist.png);">I love this album</label></td>
		</tr>
		<tr>
			<td colspan="4" style="text-align:center" id="fans-link"><b><a href="#x" class="arrow-right" onclick="toggleFans('<?=$id?>');">See all fans and collectors of this album</a></b></td>
		</tr>
		<tr>
			<td colspan="4" id="fans" class="nostyle">
				<div style="padding-bottom:5px; border-bottom:1px solid #BBB;"><b>Fans of this album</b> <span style="color:#808080">(<a href="#x" onclick="toggleFans();">hide</a>)</span></div>
				<div id="loading-fans" style="text-align:center"><img src="/bin/img/loading_bar.gif" alt="loading"/></div>
				<div id="fans-list"></div>
			</td>
		</tr>
	</table>
</div>
<map name="star_map" onmouseout="revertToRating();">
	<area shape="rect" alt="Remove your rating" title="Remove your rating" coords="77,0,93,14" href="#x" onmouseover="document.getElementById('star-rating-bg').className='rating-value-5';" onclick="rateAlbum('<?=$id?>', '0', '<?=$usrid?>');"/>
	<area shape="rect" alt="Rate 5 stars" title="Rate 5 stars" coords="62,0,76,14" href="#x" onmouseover="document.getElementById('star-rating-bg').className='rating-value-5';" onclick="rateAlbum('<?=$id?>', 5, '<?=$usrid?>');"/>
	<area shape="rect" alt="Rate 4 stars" title="Rate 4 stars" coords="47,0,61,14" href="#x" onmouseover="document.getElementById('star-rating-bg').className='rating-value-4';" onclick="rateAlbum('<?=$id?>', 4, '<?=$usrid?>');"/>
	<area shape="rect" alt="Rate 3 stars" title="Rate 3 stars" coords="32,0,46,14" href="#x" onmouseover="document.getElementById('star-rating-bg').className='rating-value-3';" onclick="rateAlbum('<?=$id?>', 3, '<?=$usrid?>');"/>
	<area shape="rect" alt="Rate 2 stars" title="Rate 2 stars" coords="17,0,31,14" href="#x" onmouseover="document.getElementById('star-rating-bg').className='rating-value-2';" onclick="rateAlbum('<?=$id?>', 2, '<?=$usrid?>');"/>
	<area shape="rect" alt="Rate 1 star" title="Rate 1 star" coords="0,0,16,14" href="#x" onmouseover="document.getElementById('star-rating-bg').className='rating-value-1';" onclick="rateAlbum('<?=$id?>', 1, '<?=$usrid?>');"/>
</map>

<?

//buy
?>
<div id="buy">
	<h3>Available at</h3>
	<ul>
		<?
		//array for images
		$retail = array(
			"AnimeNation" => "animenation",
			"GameMusic.com" => "gmc",
			"Amazon.com" => "amazon",
			"Play-Asia.com" => "playasia");
		$Querya = "SELECT * FROM albums_buy WHERE album = '".$dat['albumid']."' ORDER BY price ASC";
		$Resulta = mysqli_query($GLOBALS['db']['link'], $Querya);
		while ($row = mysqli_fetch_assoc($Resulta)) {
			if($row['vendor'] == "GameMusic.com") continue;
			if($row['vendor'] == "Play-Asia.com") $pa_incl = TRUE;
			if($retail[$row['vendor']]) {
		  	if(!$dat['no_commerce']) echo '<li class="retail"><a href="'.$row['code'].'"><span class="price">'.$row['price'].'</span><img src="/music/graphics/'.$retail[$row['vendor']].'.png" alt="'.$row['vendor'].'" border="0"/></a></li>'."\n";
		  } else {
		  	echo '<li><a href="'.$row['code'].'">'.$row['vendor'].'</a></li>';
		  }
		}
		if(!$dat['no_commerce']) {
			//play asia & ebay links
			if(!$pa_incl) echo '<li><a href="http://www.play-asia.com/SOap-23-83-3swa-49-en.html"><img src="/music/graphics/playasia.png" width="87" height="10" alt="Play-Asia.com" border="0"/></a></li>'."\n";
			?><li><a href="http://rover.ebay.com/rover/1/711-1751-2978-71/1?AID=5463217&PID=676401&mpre=http%3A%2F%2Fsearch.ebay.com%2Fsearch%2Fsearch.dll%3Fsatitle%3D<?=urlencode($dat['title'].' '.$dat['subtitle'])?>"><span class="price">New & Used</span><img src="/music/graphics/ebay.png" alt="eBay" border="0" style="padding-top:0 !important; margin-top:-1px;"/></a></li><?
		}
		?>
	</ul>
</div>
<?


// CREDITS

unset($people);
$people['vital'] = array();
$people['reg'] = array();
$Query3 = "SELECT pid, name, name_url, role, vital, notes FROM people_work LEFT JOIN people USING (pid) WHERE people_work.albumid='".$dat['albumid']."' ORDER BY name";
$Result3 = mysqli_query($GLOBALS['db']['link'], $Query3);
while($row = mysqli_fetch_assoc($Result3)) {
	if($row['vital']) $people['vital'][] = $row;
	else $people['reg'][] = $row;
}
$Query3 = "SELECT * FROM albums_other_people WHERE albumid='".$dat['albumid']."' ORDER BY name";
$Result3 = mysqli_query($GLOBALS['db']['link'], $Query3);
while($row = mysqli_fetch_assoc($Result3)) {
	if($row['vital']) $people['vital'][] = $row;
	else $people['reg'][] = $row;
}
krsort($people);

if($people) {
	
	?>
	<div id="credits">
		<h3>Artists & Credits</h3>
		<table border="0" cellpadding="2" cellspacing="0" width="100%">
			<?
			$t_cont = '';
			foreach($people['vital'] as $p) {
				if($p['notes']) {
					$notes = $p['notes'];
					$notes = '<tr><td colspan="2" class="notes">'.$notes.'</td></tr>'."\n";
					$class = " rowwithnotes";
				} else {
					$notes = "";
					$class = "";
				}
				$t_cont.= '<tr class="vital'.$class.'"><th>[['.$p['name'].']]</th>'."\n".
					'<td>'.($p['role'] ? $p['role'] : '&nbsp;').'</td></tr>'."\n".
					$notes;
			}
			foreach($people['reg'] as $p) {
				if($p['notes']) {
					$notes = $p['notes'];
					$notes = '<tr><td colspan="2" class="notes">'.$notes.'</td></tr>'."\n";
					$class = "rowwithnotes";
				} else {
					$notes = "";
					$class = "";
				}
				$t_cont.= '<tr class="nonvital '.$class.'"><th>[['.$p['name'].']]</th>'."\n".
					'<td>'.($p['role'] ? $p['role'] : '&nbsp;').'</td></tr>'."\n".
					$notes;
			}
			$bb = new bbcode($t_cont);
			echo $bb->bb2html();
			?>
		</table>
	</div>
	<?
	
}

//Related Albums
?>
<div id="related">
	<?
	$r = array();
	$Querya = "SELECT r.type, r.related, r.album, l.id, l.albumid, l.release, l.title, l.datesort, l.subtitle, l.view from albums_related as r, albums as l where r.album = '".$dat['albumid']."' and l.albumid = r.related ORDER BY l.datesort DESC";
	$Resulta = mysqli_query($GLOBALS['db']['link'], $Querya);
	if(mysqli_num_rows($Resulta)) {
		?>
		<h3>Related Albums</h3>
		<?
		while ($row = mysqli_fetch_assoc($Resulta)) {
			$i++;
			
			/*if(file_exists($_SERVER['DOCUMENT_ROOT']."/music/media/cover/thumb/".$row['albumid'].".png")) {
				$pic = '<img src="/music/media/cover/thumb/'.$row['albumid'].'.png" border="1" alt="Cover"/>';
			} else {
				$pic = '<img src="/graphics/none_sm.png" border="1" alt="No cover image available"/>';
			}*/
			
			if ($row['type'] == 5) {
				if ($row['view'] == 1) {
					$r['r5'][] = '<li><a href="?id='.$row['albumid'].'">'.$pic.$row['albumid'].' ('.$row['release'].')</a></li>'."\n";
				} else {
					$r['r5'][] = '<li>'.$pic.$row['albumid'].' ('.$row['release'].')</li>'."\n";
				}
			} else { // 1 2 3 4
				$t = "r".$row['type'];
				if ($row['view'] == 1) {
					$r[$t][] = '<li><a href="?id='.$row['albumid'].'">'.$pic.$row['title'].' '.$row['subtitle'].'</a></li>'."\n";
				} else {
					$r[$t][] = '<li>'.$pic.$row['title'].' '.$row['subtitle'].'</li>'."\n";
				}
				unset($pic);
			}
		}
		
		if ($r['r5']) {
			?>
			<h4>Other editions</h4>
			<ul>
				<?
				foreach ($r['r5'] as $related) {
					echo $related;
				}
				?>
			</ul>
			<?
		}
		if ($r['r1']) {
			?>
			<h4>Style/format</h4>
			<ul>
				<?
				foreach ($r['r1'] as $related) {
					echo $related;
				}
				?>
			</ul>
			<?
		}
		if ($r['r2']) {
			?>
			<h4><?=$dat['title']?></h4>
			<ul>
				<?
				foreach ($r['r2'] as $related) {
					echo $related;
				}
				?>
			</ul>
			<?
		}
		if ($r['r4']) {
			?>
			<h4><?=bb2html('[['.$dat['series'].' series]]', 'pages_only')?></h4>
			<ul>
				<?
				foreach ($r['r4'] as $related) {
					echo $related;
				}
				?>
			</ul>
			<?
		}
		if ($r['r3']) {
			?>
			<h4>By Composer</h4>
			<ul>
				<?
				foreach ($r['r3'] as $related) {
					echo $related;
				}
				?>
			</ul>
			<?
		}
	}
	
	// RELATED GAMES //
	$query = "SELECT `tag` FROM albums_tags WHERE albumid='$album'";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)) {
		?>
		<h3>Related Games</h3>
		<ul style="margin-top:5px">
			<?
			while($row = mysqli_fetch_assoc($res)) {
				$ul.= '<li>[['.$row['tag'].']]</li>'."\n";
			}
			echo bb2html($ul, "pages_only");
			?>
		</ul>
		<?
	}

?>
</div><!-- #related -->

<?

//eBay ad
//echo '<script language="JavaScript" src="http://lapi.ebay.com/ws/eBayISAPI.dll?EKServer&ai=duynuh%29lsv&bdrcolor=c0c0c0&cid=0&eksize=1&encode=ISO-8859-1&endcolor=FF0000&endtime=n&fbgcolor=f5f5f5&fntcolor=000000&fs=3&hdrcolor=556987&hdrimage=1&hdrsrch=n&img=n&lnkcolor=006699&logo=3&num=4&numbid=n&paypal=n&popup=n&prvd=1&query='.urlencode($dat['title'].' '.$dat['subtitle']).'&r0=3&shipcost=n&sid=album-'.$id.'&siteid=0&sort=MetaNewSort&sortby=endtime&sortdir=desc&srchdesc=n&tbgcolor=f5f5f5&tlecolor=f5f5f5&tlefs=0&tlfcolor=000000&track=676401&width=152"></script>';

?>
</div><!-- #side -->

<?
//////////
// MAIN //
//////////
?>

<div id="main">

<div id="main-left">
	<?
	if(file_exists($_SERVER['DOCUMENT_ROOT']."/music/media/cover/standard/".$dat['albumid'].".png")) {
		if(file_exists($_SERVER['DOCUMENT_ROOT']."/music/media/cover/".$dat['albumid'].".jpg")) {
			echo '<a href="/music/media/cover/'.$dat['albumid'].'.jpg" title="'.$dat['title']." ".$dat['subtitle'].' box cover" class="" rel="shadowbox">';
			$a = "</a>";
		} else $a = "";
		echo '<span class="albumimg" style="background:url(/music/media/cover/standard/'.$dat['albumid'].'.png);"><img src="/music/media/cover/standard/'.$dat['albumid'].'.png" border="0" alt="'.$dat['title']." ".$dat['subtitle'].' box cover"/></span>'.$a;
	} else $nocover = TRUE;
	
	?>
	<dl<?=($nocover ? ' style="margin-top:0 !important;"' : '')?>>
		<?
		if ($dat['publisher']) {
			?>
			<dt>Publisher</dt>
			<dd><?=$dat['publisher']?></dd>
			<?
		}
		if ($dat['cid']) {
			?>
			<dt>Catalog ID</dt>
			<dd><?=$dat['cid']?></dd>
			<?
		}
		if ($dat['datesort']) {
			?>
			<dt>Release Date</dt>
			<dd><?=formatDate($dat['datesort'])?></dd>
			<?
		}
		if ($dat['price']) {
			?>
			<dt>Retail Price</dt>
			<dd><?=$dat['price']?></dd>
			<?
		}
		if ($dat['jp'] == 1) {
			?>
			<dt><a href="jp/<?=$dat['albumid']?>.txt">Original track list</a></dt>
			<dd>(S-JIS)</dd>
			<?
		}
		?>
	</dl>
</div>

<h2><?=$dat['title'].($dat['subtitle'] ? ' <i>'.$dat['subtitle'].'</i>' : '')?></h2>

<div id="synopsis">
	<?
	$postlist = array();
	$query = "SELECT * FROM posts WHERE `subject` = 'AlbumID:$album' AND `category` != 'drafts' AND rating_weighted >= '".$_posts->rating_threashhold."' ORDER BY rating_weighted DESC";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)) {
		while($row = mysqli_fetch_assoc($res)) {
			
			if($row['post_type'] != "summary"){
				$postlist[] = $row;
				continue;
			}
			
			$post = new post($row);
			
			$pdate = formatDate($row['datetime']);
			$date = substr($row['datetime'], 0, 10);
			$date = str_replace("-", "/", $date);
			
			echo '<article class="">'.$post->output("compact").$post->outputMeta().'</article>';
			
			$has_synopsis = true;
			
		}
	
	}
	
	if(!$has_synopsis){
		?>
		No synopsis is currently available. <a href="/posts/manage.php?action=newpost&autotag=AlbumID:<?=$album?>&instruct=albumsynopsis" class="arrow-right">Write a summary or review</a><br /></span>
		<?
	}
	?>
</div>
<?

if($postlist){
	
	?>
	<div id="sbloglist">
		<h3>Related News & Blogs</h3>
		<?
		$_posts->shortlist($postlist);
		?>
	</div>
	<?
	
}

?>
<br style="clear:left;"/>
<?

//////////////
// FACTOIDS //
//////////////

?>
<div id="factoids">
	<fieldset>
		<legend>Notes & Factoids</legend>
		<ul>
			<?
			$q = "SELECT * FROM albums_trivia WHERE album = '$album'";
			$r = mysqli_query($GLOBALS['db']['link'], $q);
			while($row = mysqli_fetch_assoc($r)) {
				if($row['author']) {
					$auth = $row['author'];
					if($row['link']) $auth = '<a href="'.$row['link'].'" target="_blank">'.$auth.'</a>';
					$auth = ' <span style="color:#999;">('.$auth.')</span>';
				} else $auth = "";
				echo '<li>'.bb2html($row['fact']).$auth.'</li>'."\n";
			}
			?>
			<li><a href="/contact.php" class="arrow-right">Contribute an interesting (and true!) factoid</a></li>
		</ul>
	</fieldset>
</div>

<?


////////////
// TRACKS //
////////////

$Queryn = "SELECT * FROM albums_tracks LEFT JOIN albums_samples ON (albums_tracks.id=albums_samples.track_id) WHERE albums_tracks.albumid = '$album' ORDER BY disc, track_number ASC";
$Resultm = mysqli_query($GLOBALS['db']['link'], $Queryn);
if(mysqli_num_rows($Resultm)) {
	
	//samples?
	$q_samples = "SELECT * FROM albums_samples WHERE albumid = '$album' LIMIT 1";
	if($sample_num = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q_samples))) {
		$sample_message = '<div style="margin-top:3px; padding-top:3px; border-top:1px solid #EEE;"><b>This Album has media samples.</b> Click the <span style="padding-left:16px; background:url(/music/graphics/playtrack.png) no-repeat left 50%;">sample icon</span> to listen.</div>';
	}
	

	?>
	<div id="tracklist">
		<h3><?=$dat['title']?> <?=$dat['subtitle']?> Track List</h3>
		<?
		
		$Query = "SELECT * FROM albums_credits WHERE albumid = '$album' AND conttype = 'track'";
		$Result = mysqli_query($GLOBALS['db']['link'], $Query);
		if(mysqli_num_rows($Result)) {
			?>
			<div id="tracklist-source">
				<b>Track list translations:</b> 
				<?
				while ($row = mysqli_fetch_assoc($Result)) {
					$track_sources[] = ($row['address'] ? '<a href="'.$row['address'].'" target="_blank">' : '').$row['source'].($row['address'] ? '</a>' : '');
				}
				echo implode(", ", $track_sources);
				echo $sample_message;
				?>
			</div>
			<?
		} else {
			?>
			<div id="tracklist-source">
				All track list translations were done by Videogam.in staff and contributors unless otherwise stated here.
				<?=$sample_message?>
			</div>
			<?
		}
		
		?>
		<table border="0" cellpadding="2" cellspacing="0" width="100%" class="track-table">
			<?
		
		//check column vals in order to depreciate columns with no data
		$q = "SELECT DISTINCT `artist`, `type`, `location`, `time` FROM `albums_tracks` WHERE `albumid`='$album'";
		$r = mysqli_query($GLOBALS['db']['link'], $q);
		while($row = mysqli_fetch_assoc($r)) {
			if($row['artist']) $cols['artist'] = 1;
			if($row['time']) $cols['time'] = 1;
		}
		$colspan = 3;
		if($cols) {
			foreach($cols as $c) {
				$colspan++;
			}
		}
		
		$discnum = 0;
		$cdisc = "";
		while ($row = mysqli_fetch_assoc($Resultm)) {
			
			if($row['disc'] != $cdisc) {
				$discnum++;
				$cdisc = $row['disc'];
				
				?>
				<tr>
					<td colspan="<?=$colspan?>" class="heading"><h4 id="disc-<?=$discnum?>"><?=$row['disc']?></h4></td>
				</tr>
				<tr>
					<th><span style="display:none">track number</span>&nbsp;</th>
					<th>Name</th>
					<th><span style="display:none">extended information</span>&nbsp;</th>
					<?=($cols['artist'] ? '<th>Artist</th>' : '')?>
					<?=($cols['time'] ? '<th style="text-align:center">Time</th>' : '')?>
				</tr>
				<?
			}
			
			$x = 1; //alternate for TD class
			$y++;
			
			if($row['file'] || $row['type'] || $row['location']) $ext = TRUE;
			else $ext = FALSE;
			
			?>
				<tr>
					<td class="tracklistn" id="row-<?=$y?>-tracknum"<?=($ext ? ' rowspan="2"' : '')?>><?=$row['track_number']?></td>
					<td class="tracklist2"><?=$row['track_name']?></td>
					<td class="tracklist2 extended-links"><?
						if($ext) {
							if($row['file']) {
								echo '<a href="#x" title="Listen to a sample of this track" onclick="toggleTrackRow(\''.$y.'\'); outputTrackSample(\''.$y.'\', \''.$row['file'].'\');"><img src="/music/graphics/playtrack.png" alt="Play a sample of this track" border="0"/></a>';
							}
							if($row['type'] || $row['location']) {
								echo '<a href="#x" title="View more information about this track" onclick="toggleTrackRow(\''.$y.'\')"><img src="/music/graphics/trackinfo.png" alt="Extended information about this track" border="0"/></a>';
							}
						} else echo '&nbsp;';
					?></td>
					<?=($cols['artist'] ? '<td class="tracklist'.($x++ % 2).'">'.($row['artist'] ? $row['artist'] : '&nbsp;').'</td>' : '')?>
					<?=($cols['time'] ? '<td class="tracklist'.($x++ % 2).'" style="text-align:center">'.($row['time'] ? $row['time'] : '&nbsp;').'</td>' : '')?>
				</tr>
			<?
			
			if($ext) {
				?>
				<tr class="extended" id="row-<?=$y?>-ext">
					<td colspan="<?=($colspan - 1)?>" class="nostyle" id="row-<?=$y?>-ext-td"><div id="row-<?=$y?>-ext-div" style="display:none">
						<a href="#x" onclick="toggleTrackRow('<?=$y?>')" title="hide this row" style="float:right;"><img src="/music/graphics/close.png" alt="close" border="0"/></a>
						<?=($row['type'] ? '<div><b>Track Type:</b> '.$row['type'].'</div>' : '').
						   ($row['location'] ? '<div><b>In-game Location:</b> '.$row['location'].'</div>' : '').
						   ($row['file'] ? '<div id="row-'.$y.'-sample"></div>' : '')?>
					</div></td>
				</tr>
				<?
			}
		}
		
		?>
		</table>
	</div>
	
	<?

} // END TRACKLIST


?>

</div><!-- main -->



<div style="clear:both; height:15px;"><br style="clear:both"/></div>

<div id="album-profile-footer">
	<b>Page Data:</b> 
	<?
	$conts = array();
	$i = 0;
	$q = "SELECT * FROM albums_changelog WHERE album='$id' ORDER BY datetime DESC";
	$r = mysqli_query($GLOBALS['db']['link'], $q);
	while($row = mysqli_fetch_assoc($r)) {
		$i++;
		if($i == 1) $mod_dt = $row['datetime'];
		if($row['type'] == "new") $cr_dt = $row['datetime'];
		if(!in_array($row['usrid'], $conts)) $conts[] = $row['usrid'];
	}
	for($i = 0; $i < count($conts); $i++) {
		$conts[$i] = outputUser($conts[$i], FALSE);
	}
	$p_conts = implode(", ", $conts);
	if($cr_dt) $pgv[] = 'Created '.formatDate($cr_dt);
	if($mod_dt) $pgv[] = 'Last updated '.timeSince($mod_dt).' ago';
	if($p_conts) $pgv[] = 'Contributors: '.$p_conts;
	echo implode(' <span>|</span> ', $pgv);
	?>
</div>
<?

$page->footer();

?>