<? 
require($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");
$page = new page;
require($_SERVER['DOCUMENT_ROOT']."/bin/php/class.posts.php");
$posts = new posts;

$user = mysqli_real_escape_string($GLOBALS['db']['link'], $_GET['username']);

$page->title = "Videogam.in Users / $user";
$page->style[] = "/bin/css/account.css";
$page->javascript.= '<script type="text/javascript" src="/bin/script/games-collection.js"></script>'."\n";
$page->javascript.= <<<EOF
<script type="text/javascript">
//thumbnail width INCLUDING margin, padding and border
var thumbWidth = 	117;
//total number of thumbnails visible
var thumbsVis = 8;

/* ---------------------- */


var x = 0;
var curPage = 0;

$(document).ready(
	function() {
		var numThumbs = $("#scroll").find("img").size();
		$("#next").click(function() { 
			updateOffset(1,numThumbs);
			$("#scroll").animate({ left: x }, 1000);	
			return false;
		})
		$("#prev").click(function() { 
			updateOffset(0,numThumbs);
			$("#scroll").animate({ left: x }, 1000);
			return false;
		})
		
		var numPages = numThumbs/thumbsVis;
		for (i=0;i<numPages;i++) {
			if (i == 0) {
				pageCopy = '<li id="page-'+i+'" class="on">Page '+i+'</li>';
			} else {
				pageCopy = pageCopy + '<li id="page-'+i+'">Page '+i+'</li>';
			}
		}
		$("#pages").html(pageCopy);
});


	function updateOffset(next,thumbs) {
		if(next == 1) {
			if (x == (0 - ((thumbs - thumbsVis) * thumbWidth))) {
				x = 0;
				curPage = 0;
			} else {
				x = x - (thumbWidth*thumbsVis);
				curPage++;
			}
		}
		if(x <= 0 && next != 1) {
			if (x == 0) {
				x = (0 - ((thumbs - thumbsVis) * thumbWidth));
				curPage = (thumbs/thumbsVis)-1;
			} else {
				x = x + (thumbWidth*thumbsVis);
				curPage--;
			}
		}
		
		$("#pages li").removeClass("on");
		$("#page-"+curPage).addClass("on");
		
		//temporary firefox animation fix - jQuery bug
		foxFix();
	}// end function
	
	
	function foxFix(end) {
		if($.browser.mozilla) {
			if(x == 0) {
				$("#scroll").addClass("fix");
			} else {
				$("#scroll").removeClass("fix");
			}
		}
	}
</script>
EOF;

$page->header();

if (!$user) {
	?>
	<h2>User Profiles</h2>
	<input type="text" name="fuser" id="fuser"/>
	<input type="button" value="Find User" onclick="document.location='/~'+document.getElementById('fuser').value;"/>
	<?
	$page->footer();
	exit;
}

$query = "SELECT * FROM users LEFT JOIN users_details USING (usrid) WHERE username = '$user' LIMIT 1";
$res   = mysqli_query($GLOBALS['db']['link'], $query);
if(!$dat = mysqli_fetch_object($res)) {
	?>
	<h2>User Profiles</h2>
	User '<?=$user?>' not on file.<br/><br/>
	<input type="text" name="fuser" id="fuser"/>
	<input type="button" value="Find User" onclick="document.location='/~'+document.getElementById('fuser').value;"/>
	<?
	$page->footer();
	exit;
}

$genderref = array("male" => "his", "female" => "her", "asexual" => "its", "" => "their");
$genderref2 = array("male" => "him", "female" => "her", "asexual" => "it", "" => "them");

if($dat->rank == 4) $status = "vip";
if($dat->rank >= 6) $status = "staff";

//prefs
$query = "SELECT * FROM `users_prefs` WHERE `usrid` = '".$dat->usrid."' LIMIT 1";
$res   = mysqli_query($GLOBALS['db']['link'], $query);
$prefs = mysqli_fetch_object($res);

?>

<div id="user-profile">
	
	<table border="0" cellpadding="0" cellspacing="0" width="100%" id="head">
		<tr>
			<td id="avatar" width="137">
				<?
				if($dat->avatar && file_exists($_SERVER['DOCUMENT_ROOT']."/bin/img/avatars/".$dat->avatar)) $p_av = $dat->avatar;
				else $p_av = "unknown.png";
				?>
				<img src="/bin/img/avatars/<?=$p_av?>" alt="<?=$dat->username?>'s avatar"/>
			</td>
			<td class="left" nowrap="nowrap">
				
				<h2><span><?=$dat->username?></span></h2>
				<ul id="since">
					<?=($dat->handle ? '<li class="handle">'.$dat->handle.'</li>' : '')?>
					<li class="since">
						<div style="margin-bottom:2px"><?
						$statuses = array("staff" => "Videogam.in Staff", "vip" => "Videogam.in V.I.P.");
						$stcolors = array("staff" => "#D12929", "vip" => "#1878E2");
						if($status) echo '<b style="color:'.$stcolors[$status].'">'.$statuses[$status].'</b> <span>/</span> ';
						
						//forum
						$q = "SELECT * FROM `forums_posts`";
						$posts_all = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q));
						$q = "SELECT * FROM `forums_posts` WHERE `usrid` = '$dat->usrid'";
						if(!$posts_user = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $posts_user = '0';
						echo '<b>'.$posts_user.'</b> forum posts (<b>'.round(($posts_user/$posts_all)*100, 2).' %</b> of all posts)';
						?> <span>/</span> 
						<?
						
						//contributions
						$q = "SELECT * FROM users_contributions WHERE usrid='$dat->usrid' AND published='1' ORDER BY datetime DESC";
						$contrnum = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q));
						?>
						<a href="/user-contributions.php?usrid=<?=$dat->usrid?>"><?=$contrnum?> contributions</a>
						</div>
						Member since <b><?=FormatDate($dat->registered)?></b> <span>/</span> 
						Last activity on <b><?=FormatDate($dat->activity)?></b>
					</li>
					<li class="buttons">
						<a href="/contact-user.php?user=<?=$dat->username?>&method=email" class="button" style="background-image:url(/bin/img/user-send_email.png)">E-mail</a> 
						<a href="/contact-user.php?user=<?=$dat->username?>&method=pm" class="button" style="background-image:url(/bin/img/user-send_pm.png)">Send PM</a>
					</li>
				</ul>
			
			</td>
			<td class="right">
				
				<dl>
					<dt class="top">Name:</dt>
					<dd class="top"><?=($dat->name ? $dat->name : 'unknown')?></dd>
					
					<dt>Location:</dt>
					<dd><?=($dat->location ? $dat->location : 'unknown')?></dd>
					
					<dt>Gender:</dt>
					<dd><?=($dat->gender ? ($dat->gender == "asexual" ? 'Asexual or Robot' : ucwords($dat->gender)) : 'unknown')?></dd>
					
					<dt>Birthday:</dt>
					<dd><? if($bday = formatDate($dat->dob, 9)) echo $bday; else echo 'unknown'; ?></dd>
					
					<?
					if($dat->im) {
						$i = 0;
						$ims = explode("|||", $dat->im);
						foreach($ims as $im) {
							list($client, $un) = explode(":::", $im);
							if($client && $un) {
								$i++;
								if($i == 1) echo '<dt>IM:</dt>'."\n";
								echo '<dd'.($i != 1 ? ' class="top"' : '').'>'.$client.': <i>'.$un.'</i></dd>'."\n";
							}
						}
					}
					
					if($dat->homepage) {
						echo '<dt>Homepage:</dt>'."\n".'<dd><a href="'.$dat->homepage.'" target="_blank">'.(strlen($dat->homepage) > 35 ? substr($dat->homepage, 34).'&hellip;' : $dat->homepage).'</a></dd>'."\n";
					}
					?>
					
				</dl>
			</td>
		</tr>
	</table>
	
	<blockquote id="bio"><?=($dat->interests ? $dat->interests : "I'm speechless")?></blockquote>
	
	<h3 style="margin-bottom:15px; padding:0; border-width:0;">Latest News, Blog, and Content posts</h3>
	<?
	// Posts
	
	$query = "SELECT * FROM posts WHERE unpublished != '1' AND usrid = '$dat->usrid' ORDER BY datetime DESC";
	$postnum = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query));
	if($postnum >  10) {
		$postwords = "Showing <strong>10</strong> of ";
		$query.= " LIMIT 10";
	}
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	$rows  = array();
	while($row = mysqli_fetch_assoc($res)) {
		$rows[] = $row;
	}
	if(!$rows) echo "No posts yet :(";
	else {
		?>
		<div class="newsnav">
			<div class="pagenav">
				<big style="font-weight:normal !important;"><?=$postwords?><strong><?=$postnum?></strong> Post<?=($postnum > 1 ? 's' : '')?></big> 
				<a href="/posts/handle.php?username=<?=$user?>">Show All Posts</a> 
				<a href="/posts/handle.php?username=<?=$user?>&category=news">News</a> 
				<a href="/~<?=$user?>/blog">Blog</a> 
				<a href="/posts/handle.php?username=<?=$user?>&category=content">Content</a>
			</div>
		</div>
		<?
		$posts->postsList($rows);
	}
	
	/*
	<div id="gamebox" style="border-width:0">
		
		<?
		$query = "SELECT my.*, g.title_url FROM my_games my LEFT JOIN games g USING (gid) WHERE usrid='$dat->usrid' ORDER BY `title`";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		if(!$colnum = mysqli_num_rows($res)) {
			echo '<div id="gamebox-space"><div style="padding:15px">'.$dat->username.' hasn\'t put any games in '.$genderref[$dat->gender].' box yet.</div></div><div id="gamebox-default"></div>';
		} else {
			?>
			<div id="slide-box">
				<ul id="pages"></ul>
				<a href="#" id="prev">&laquo;</a>
				<div id="mask"><div id="scroll">
						<?
						while($row = mysqli_fetch_assoc($res)) {
							
							if($row['publication_id']) {
								$img = "/games/files/".$row['gid']."/".$row['gid']."-box-".$row['publication_id']."-sm.png";
								$q = "SELECT * FROM games_publications LEFT JOIN games_platforms USING (platform_id) WHERE id='".$row['publication_id']."' LIMIT 1";
								$x = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
								$row['title'] = $x->title;
								$row['platform'] = $x->platform;
							} elseif($row['platform_id']) {
								$img = "/bin/uploads/user_boxart/".$row['id']."_sm.png";
								$q = "SELECT * FROM games_platforms WHERE platform_id='".$row['platform_id']."' LIMIT 1";
								$x = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
								$row['platform'] = $x->platform;
							} else {
								$row['platform'] = "Unknown platform";
								$img = "";
							}
							if(!$img || !file_exists($_SERVER['DOCUMENT_ROOT'].$img)) {
								$img = "/bin/img/no_box-140.png";
							}
							
							$labels = "";
							if($row['rating'] == 2) {
								$labels.= '<img src="/bin/img/game-rate-love-label.png" alt="love" title="I love this game" border="0" class="tooltip"/>';
							} elseif($row['rating'] == '0') {
								$labels.= '<img src="/bin/img/game-rate-hate-label.png" alt="hate" title="I hate this game" border="0" class="tooltip"/>';
							}
							
							unset($playtitle);
							unset($stuff);
							$stuff = array();
							if($row['own']) $stuff[] = "<b>owns</b>";
							if($row['play']) {
								$playtitle = "I am currently playing this game";
								$stuff[] = "<b>plays</b>";
							}
							if($row['play_online']) {
								if($playtitle) $playtitle.= " and ";
								$playtitle.= "I play this game online";
								if($row['online_id']) $addid = ' ('.$genderref[$dat->gender].' online ID is \''.$row['online_id'].'\' if you want to play with '.$genderref2[$dat->gender].')';
								else $addid = "";
								$stuff[] = "<b>plays this game online</b>".$addid;
								$push = "";
							} else $push = " this game";
							if($num = count($stuff)) {
								$dostuff = $dat->username." ";
								if($num == 1) $dostuff.= $stuff[0];
								elseif($num == 2) $dostuff.= implode(" and ", $stuff);
								elseif($num == 3) $dostuff.= "<b>owns</b>, <b>plays</b>, and <b>plays this game online</b>".$addid;
								$dostuff.= $push.".";
							} else $dostuff = "";
							if($playtitle) {
								$labels.= '<img src="/bin/img/gamebox-label-play.png" alt="play" title="'.$playtitle.'" border="0" class="tooltip"/>';
							}
							
							?>
							<a href="/games/~<?=$row['title_url']?>" title="<i><?=$row['title']?></i><br/><?=$row['platform']?><br/><?=$dostuff?>" class="tooltip"><img src="<?=$img?>" border="0"/></a>
							<?
							
						}
						if($colnum < 5) echo '<td width="100%" style="background:url(/bin/img/gamebox-spotlight-na.png) repeat-x 0 0;">&nbsp;</td>';
						?>
					</div></div>
		<a href="#" id="next">&raquo;</a>			
	</div>
			<?
		}
		?>
		
	</div>
	
	<div id="gamebox" style="display:none">
		
		<table border="0" cellpadding="0" cellspacing="0" width="100%" class="nav">
			<tr>
				<td id="gamebox-nav-default" class="on"><a href="javascript:void(0)" onclick="showMyGames('default');" style="color:black"><?=$dat->username?>'s Game Collection</a></td>
				<td id="gamebox-nav-all"><a href="javascript:void(0)" onclick="showMyGames('all', '<?=$dat->usrid?>');">All</a></td>
				<td id="gamebox-nav-own"><a href="javascript:void(0)" onclick="showMyGames('own', '<?=$dat->usrid?>');">Own</a></td>
				<td id="gamebox-nav-play"><a href="javascript:void(0)" onclick="showMyGames('play', '<?=$dat->usrid?>');">Play</a></td>
				<td id="gamebox-nav-play_online"><a href="javascript:void(0)" onclick="showMyGames('play_online', '<?=$dat->usrid?>');">Play Online</a></td>
				<td id="gamebox-nav-love"><a href="javascript:void(0)" onclick="showMyGames('love', '<?=$dat->usrid?>');">Love</a></td>
				<td id="gamebox-nav-hate"><a href="javascript:void(0)" onclick="showMyGames('hate', '<?=$dat->usrid?>');">Hate</a></td>
				<td width="100%">&nbsp;</td>
			</tr>
		</table>
		
		<?
		$query = "SELECT my.*, g.title_url FROM my_games my LEFT JOIN games g USING (gid) WHERE usrid='$dat->usrid' ORDER BY added DESC LIMIT 5";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		if(!$colnum = mysqli_num_rows($res)) {
			echo '<div id="gamebox-space"><div style="padding:15px">'.$dat->username.' hasn\'t put any games in '.$genderref[$dat->gender].' box yet.</div></div><div id="gamebox-default"></div>';
		} else {
			?>
			<div id="gamebox-space"></div>
			<div id="gamebox-default" class="row" style="border-width:0">
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<?
						while($row = mysqli_fetch_assoc($res)) {
							
							if($row['publication_id']) {
								$img = "/games/files/".$row['gid']."/".$row['gid']."-box-".$row['publication_id']."-sm.png";
								$q = "SELECT * FROM games_publications LEFT JOIN games_platforms USING (platform_id) WHERE id='".$row['publication_id']."' LIMIT 1";
								$x = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
								$row['title'] = $x->title;
								$row['platform'] = $x->platform;
							} elseif($row['platform_id']) {
								$img = "/bin/uploads/user_boxart/".$row['id']."_sm.png";
								$q = "SELECT * FROM games_platforms WHERE platform_id='".$row['platform_id']."' LIMIT 1";
								$x = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
								$row['platform'] = $x->platform;
							} else {
								$row['platform'] = "Unknown platform";
								$img = "";
							}
							if(!$img || !file_exists($_SERVER['DOCUMENT_ROOT'].$img)) {
								$img = "/bin/img/no_box-140.png";
							}
							
							$labels = "";
							if($row['rating'] == 2) {
								$labels.= '<img src="/bin/img/game-rate-love-label.png" alt="love" title="I love this game" border="0" class="tooltip"/>';
							} elseif($row['rating'] == '0') {
								$labels.= '<img src="/bin/img/game-rate-hate-label.png" alt="hate" title="I hate this game" border="0" class="tooltip"/>';
							}
							
							unset($playtitle);
							unset($stuff);
							$stuff = array();
							if($row['own']) $stuff[] = "<b>owns</b>";
							if($row['play']) {
								$playtitle = "I am currently playing this game";
								$stuff[] = "<b>plays</b>";
							}
							if($row['play_online']) {
								if($playtitle) $playtitle.= " and ";
								$playtitle.= "I play this game online";
								if($row['online_id']) $addid = ' ('.$genderref[$dat->gender].' online ID is \''.$row['online_id'].'\' if you want to play with '.$genderref2[$dat->gender].')';
								else $addid = "";
								$stuff[] = "<b>plays this game online</b>".$addid;
								$push = "";
							} else $push = " this game";
							if($num = count($stuff)) {
								$dostuff = $dat->username." ";
								if($num == 1) $dostuff.= $stuff[0];
								elseif($num == 2) $dostuff.= implode(" and ", $stuff);
								elseif($num == 3) $dostuff.= "<b>owns</b>, <b>plays</b>, and <b>plays this game online</b>".$addid;
								$dostuff.= $push.".";
							} else $dostuff = "";
							if($playtitle) {
								$labels.= '<img src="/bin/img/gamebox-label-play.png" alt="play" title="'.$playtitle.'" border="0" class="tooltip"/>';
							}
							
							?>
							<td class="small<?=$ratingclass?>">
								<div class="container">
									<a href="/games/~<?=$row['title_url']?>" title="<i><?=$row['title']?></i><br/><?=$row['platform']?><br/><?=$dostuff?>" class="tooltip"><div class="gamecover"><img src="<?=$img?>" border="0"/></div></a>
									<?=($labels ? '<div class="labels">'.$labels.'</div>' : '')?>
								</div>
							</td>
							<?
							
						}
						if($colnum < 5) echo '<td width="100%" style="background:url(/bin/img/gamebox-spotlight-na.png) repeat-x 0 0;">&nbsp;</td>';
						?>
					</tr>
				</table>
			</div>
			<?
		}
		?>
		
	</div>
	
	<?
	
	// site reviews
	$Query = "SELECT r.indx, r.id, r.author, r.grade, r.date, u.user, r.title, g.id, g.platform from StaffReview as r, Users as u, Games as g where u.user = '$user' and r.author = '$user' and r.id = g.id order by date DESC";
	$Result = mysqli_query($GLOBALS['db']['link'], $Query);
	if (mysqli_num_rows($Result)) {
		$site_reviews = true;
		while ($row = mysqli_fetch_assoc($Result)) {
			$i++;
			$rem = ($i % 2);
			$bgc = "";
			if ($rem)
				$bgc = " class=\"odd\"";
			$row[date] = FormatDate($row[date],2);
			$your_site_reviews .= "<tr".$bgc."><td style=\"vertical-align: top;\">$row[date]</td>\n";
			$your_site_reviews .= "<td><a href=\"/reviews/staff/?subid=$row[indx]\">$row[title]</a></td>\n";
			$your_site_reviews .= "<td>$row[grade]</td>\n";
		}
	}*/

	// reader reviews
	/*$sta = array ('published' => 'green',
				'unpublished' => 'yellow',
				'declined' => 'red');

	$Query = "SELECT * from `ReaderReview` where `user` = '$user' AND `published` = '1'";
	$Result = mysqli_query($GLOBALS['db']['link'], $Query);

	if (mysqli_num_rows($Result)) {
		while ($row = mysqli_fetch_assoc($Result)) {
			$i++;
			$rem = ($i % 2);
			$bgc = "";
			if ($rem) {
				$bgc = " class=\"odd\"";
			}
			
			$your_reader_reviews .= "<tr".$bgc."><td>$row[date]</td>\n";
			$your_reader_reviews .= "<td><a href=\"/reviews/reader/?subid=$row[indx]\">$row[review_title]</a></td>\n";
			$your_reader_reviews .= "<td>$row[grade]</td></tr>\n";
		}
	} else {
		$your_reader_reviews = "<tr class=\"odd\"><td colspan=\"3\">No reader reviews.</td></tr>";
	}
	
	//forum
	$q = "SELECT * FROM `forums_posts`";
	$posts_all = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q));
	$q = "SELECT * FROM `forums_posts` WHERE `usrid` = '$dat->usrid'";
	if(!$posts_user = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $posts_user = '0';
	$forums = "<tr><td><big>$posts_user</big> forum posts (<big>".round(($posts_user/$posts_all)*100, 2)." %</big> of all posts)</td></tr>";
	
	//games coll
	$query = "SELECT action, title, title_url, platform_shorthand 
		FROM games_collection LEFT JOIN games USING (gid) LEFT JOIN games_platforms USING (platform_id) 
		WHERE usrid = '".$dat->usrid."' ORDER BY title";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)) {
		while($row = mysqli_fetch_assoc($res)) {
			$gcoll[$row['action']][] = $row;
		}
	}
	
	//music coll
	$query = "SELECT action, albumid, title, subtitle 
		FROM albums_collection LEFT JOIN albums USING (albumid) 
		WHERE usrid = '".$dat->usrid."' ORDER BY title";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)) {
		while($row = mysqli_fetch_assoc($res)) {
			$acoll[$row['action']][] = $row;
		}
	}

?>


<table cellpadding="0" cellspacing="0" width="100%" id="your-account">
<tr><td valign="top">

<div id="forum-activity">
	<table cellpadding="0" cellspacing="0" width="100%" summary="User's playlist">
	<caption><?=$dat->username?>'s Forum Activity</caption>
	<tbody>
	<?=$forums?>
	</tbody>
	</table>
</div>

<div id="your-playlist">
	<table cellpadding="0" cellspacing="0" width="100%" summary="User's playlist">
	<caption><?=$dat->username?>'s  Playlist</caption>
	<tbody>
		<tr class="odd"><th>Currently playing</th></tr>
		<tr><td><?
			if($gcoll['playing']) {
				?><ul><?
				foreach($gcoll['playing'] as $x) {
					echo '<li><a href="/games/'.$x['platform_shorthand'].'/'.$x['title_url'].'/">'.$x['title'].'</a></li>'."\n";
				}
				?></ul><?
			} else echo "Nothing";
		?></td></tr>
		<tr class="odd"><th>Play online</th></tr>
		<tr><td><?
			if($gcoll['playonline']) {
				?><ul><?
				foreach($gcoll['playonline'] as $x) {
					echo '<li><a href="/games/'.$x['platform_shorthand'].'/'.$x['title_url'].'/">'.$x['title'].'</a></li>'."\n";
				}
				?></ul><?
			} else echo "Nothing";
		?></td></tr>
		<tr class="odd"><th>Currently listening to</th></tr>
		<tr><td><?
			if($acoll['listening']) {
				?><ul><?
				foreach($acoll['listening'] as $x) {
					echo '<li><a href="/music/?id='.$x['albumid'].'">'.$x['title'].($x['subtitle'] ? ' <i>'.$x['subtitle'].'</i>' : '').'</a></li>'."\n";
				}
				?></ul><?
			} else echo "Nothing";
		?></td></tr>
	</tbody>
	</table>
</div>

</td>
<td style="width:15px">&nbsp;</td>
<td valign="top">

<div id="your-collection">
	<table cellpadding="0" cellspacing="0" width="100%" summary="User's game collection">
	<caption><?=$dat->username?>'s Game Collection</caption>
	<tbody>
	<?
	if($gcoll['collecting']) {
		foreach($gcoll['collecting'] as $x) {
			echo '<tr><td><a href="/games/'.$x['platform_shorthand'].'/'.$x['title_url'].'/">'.$x['title'].'</a></td><td>'.$x['platform_shorthand'].'</td></tr>'."\n";
		}
	} else echo '<tr><td>None</td></tr>';
	?>
	</tbody>
	</table>
</div>

<div id="your-collection">
	<table cellpadding="0" cellspacing="0" width="100%" summary="User's album collection">
	<caption><?=$dat->username?>'s Soundtrack Collection</caption>
	<tbody>
	<?
	if($acoll['collecting']) {
		foreach($acoll['collecting'] as $x) {
			echo '<tr><td><a href="/music/?id='.$x['albumid'].'">'.$x['title'].($x['subtitle'] ? ' <i>'.$x['subtitle'].'</i>' : '').'</a></td></tr>'."\n";
		}
	} else echo '<tr><td>None</td></tr>';
	?>
	</tbody>
	</table>
</div>

</td></tr>
</table>*/
?>

</div><!-- #user-profile -->


<?
$page->footer();
?>
