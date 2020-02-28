<? 
require($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");
$page = new page;
require($_SERVER['DOCUMENT_ROOT']."/bin/php/class.posts.php");
require($_SERVER['DOCUMENT_ROOT']."/bin/php/class.img.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.badges.php");
$_badges = new badges;

$user = mysqli_real_escape_string($GLOBALS['db']['link'], $_GET['username']);

$page->title = "Videogam.in Users / $user";
$page->css[] = "/bin/css/account.css";
$page->width = "fixed";
$page->fb    = true;
$page->header();

if(!$user) {
	?>
	<h2>User Profiles</h2>
	<input type="text" name="fuser" id="fuser"/>
	<input type="button" value="Find User" onclick="document.location='/~'+document.getElementById('fuser').value;"/>
	<?
	$page->footer();
	exit;
}

$query = "SELECT * FROM users LEFT JOIN users_details USING (usrid) WHERE username = '".mysqli_real_escape_string($GLOBALS['db']['link'], $user)."' LIMIT 1";
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

if($dat->rank >= 3) $status = "vip";
if($dat->rank >= 7) $status = "staff";

//prefs
$query = "SELECT * FROM `users_prefs` WHERE `usrid` = '".$dat->usrid."' LIMIT 1";
$res   = mysqli_query($GLOBALS['db']['link'], $query);
$prefs = mysqli_fetch_object($res);

$statuses = array("staff" => "Videogam.in Staff", "vip" => "Videogam.in V.I.P.");
$stcolors = array("staff" => "#D12929", "vip" => "#1878E2");

?>

<div id="user-profile">
	
	<div id="avatar" style="float:left;">
		<?
		if($dat->avatar && file_exists($_SERVER['DOCUMENT_ROOT']."/bin/img/avatars/".$dat->avatar)) $p_av = $dat->avatar;
		else $p_av = "unknown.png";
		?>
		<img src="/bin/img/avatars/<?=$p_av?>" alt="<?=$dat->username?>'s avatar"/>
	</div>
	
	<div style="float:right;">
		<div class="rolodex">
			<dl>
				<dt class="top">Name</dt>
				<dd class="top"><?=($dat->name ? $dat->name : '<i>unknown</i>')?></dd>
				
				<dt>Location</dt>
				<dd><?=($dat->location ? $dat->location : '<i>unknown</i>')?></dd>
				
				<dt>Gender</dt>
				<dd><?=($dat->gender ? ($dat->gender == "asexual" ? 'Asexual or Robot' : ucwords($dat->gender)) : '<i>unknown</i>')?></dd>
				
				<dt>Birthday</dt>
				<dd><? if($bday = formatDate($dat->dob, 9)) echo $bday; else echo '<i>unknown</i>'; ?></dd>
				
				<?
				if($dat->im) {
					$i = 0;
					$ims = explode("|||", $dat->im);
					foreach($ims as $im) {
						list($client, $un) = explode(":::", $im);
						if($client && $un) {
							$i++;
							if($i == 1) echo '<dt><acronym title="instant message handle(s)">IM</acronym></dt>'."\n";
							echo '<dd'.($i != 1 ? ' class="top"' : '').'>'.$client.': <i>'.$un.'</i></dd>'."\n";
						}
					}
				}
				
				if($dat->homepage) {
					echo '<dt>Web</dt>'."\n".'<dd><a href="'.$dat->homepage.'" target="_blank">'.(strlen($dat->homepage) > 35 ? substr($dat->homepage, 0, 33).'&hellip;' : $dat->homepage).'</a></dd>'."\n";
				}
				?>
				
			</dl>
		</div>
	</div>
	
	<div id="details" style="margin:0 370px 0 170px;">
		<h1 title="#<?=$dat->usrid?>">
			<?=$dat->username?>
			<?=($status ? '<span class="status"><img src="/bin/img/forum_status_'.$status.'_left.png" alt="'.$status.'"/></span>' : '')?>
		</h1>
		<ul id="since">
			<?=($dat->handle ? '<li class="handle">'.$dat->handle.'</li>' : '')?>
			<li class="since">
				Member for <b title="<?=$dat->registered?>" style="border-bottom:1px dotted #CCC;"><?=timeSince($dat->registered)?></b> <span>/</span> 
				Active <b title="<?=$dat->activity?>" style="border-bottom:1px dotted #CCC;"><?=timeSince($dat->activity)?></b> ago
			</li>
			<li class="buttons">
				<a href="/~<?=$dat->username?>/blog" class="button" style="background-image:url(/bin/img/icons/emoticons/_star.png)"><?=$dat->username?>'s Blog</a> 
				<?=($usrrank >= 8 ? '<a href="mailto:'.$dat->email.'" class="button" style="background-image:url(/bin/img/user-send_email.png)">Send E-mail</a> ' : '')?>
				<a href="/contact-user.php?user=<?=$dat->username?>&method=pm" class="button" style="background-image:url(/bin/img/user-send_pm.png)">Send PM</a> 
				<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script><fb:like layout="button_count" show_faces="false" width="90" font="arial"></fb:like>
			</li>
		</ul>
		
	</div>
	
	<br style="clear:both;"/>
	
	<!-- about me -->
	<?=($dat->interests ? '<div id="bio"><span><img src="/bin/img/blockquote.png" alt="quote"/></span><blockquote>'.$dat->interests.'</blockquote><br style="clear:left;"/></div>' : '')?>
	
	<br style="clear:left;"/>
	
	<!-- score -->
	<?
	
	$score = UserCalcScore($dat->usrid);
	
	if($dat->usrid != 1 || $usrid == 1){
		//Don't show Matt's amazingly awesome, intimidating scores/badges etc.
		?>
		<div class="score" style="margin:35px 0 0;">
			<dl>
				<dt style="background-color:#39F;"><?=ceil($score['forums'])?></dt>
				<dd>Forum Score</dd>
				
				<dt style="background-color:#693;"><?=ceil($score['sblogs'])?></dt>
				<dd>Sblog Score</dd>
				
				<dt style="background-color:#FC3;"><?=ceil($score['pages'])?></dt>
				<dd>Page Score</dd>
				
				<dt style="background-color:#E11E1E;"><?=ceil($score['total'])?></dt>
				<dd><b>Total Score</b></dd>
			</dl>
		</div>
		
		<br style="clear:left;"/>
		
		<!-- badges -->
		<div style="<?=($dat->usrid == 1 ? 'display:;' : '')?>">
			<h3 style="margin-top:1em;">Badges</h3>
			<?=$_badges->collection($dat->usrid, $dat->username)?>
		</div>
		<?
	}
	
	?>
	
	<div style="margin:50px 0 0; height:0;"></div>
	
	<div style="float:right; width:250px;" id="pslist">
		<span class="halo"></span>
		<?
		//patron saint
		//SELECT pages.`title`, SUM(score) FROM pages LEFT JOIN pages_edit USING (`title`) WHERE (contributors='1' OR contributors LIKE '1|%') AND pages.redirect_to='' GROUP BY `title` ORDER BY SUM(score) DESC LIMIT 0, 25
		$query = "SELECT `title` FROM pages WHERE (contributors='$dat->usrid' OR contributors LIKE '$dat->usrid|%') AND redirect_to='' ORDER BY `title`";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		if(!$num_ps = mysqli_num_rows($res)){
			echo '<h5>'.$dat->username.' is not yet the Patron Saint of anything <span class="frowney"></span></h5>';
		} else {
			echo '<h5>Patron Saint of <b style="color:black">'.$num_ps.'</b> pages</h5><ul>';
			$i = 0;
			while($row = mysqli_fetch_assoc($res)){
				$i++;
				if($i == 20) echo '<li style="background:none;" onclick="$(this).hide().siblings().show();"><b><a href="#moreps" class="preventdefault arrow-toggle arrow-toggle-on" style="padding:0 10px 0 0; background-position:right center;">Show more</a></b></li>';
				echo '<li style="'.($i >= 20 ? 'display:none;' : '').'"><a href="/pages/handle.php?title='.formatNameURL($row['title']).'">'.$row['title'].'</a></li>';
			}
			echo '</ul>';
		}
		?>
	</div>
	
	<div style="width:620px;">
	
		<h3>Latest News & Blog posts</h3>
		<?
		// Posts
		
		if(!$score['vars']['num_sblogposts']){
			echo '<span class="none">'.$dat->username." hasn't yet published any news or blogs.</span>";
		} else {
			
			$posts = new posts();
			
			$query = "SELECT * FROM posts WHERE unpublished != '1' AND pending != '1' AND usrid = '$dat->usrid' ORDER BY datetime DESC LIMIT 5";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)) $rows[] = $row;
			$posts->shortlist($rows);
			echo '<div class="more"><b><a href="/posts/?username='.$dat->username.'" class="arrow-right">See all '.$score['vars']['num_sblogposts'].' News & Blog posts by '.$dat->username.'</a></b></div>';
			
			//reviews
			echo '<h3>Reviews</h3>';
			$query = "SELECT * FROM posts WHERE unpublished != '1' AND pending != '1' AND usrid = '$dat->usrid' and type2 = 'review' ORDER BY datetime DESC";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)) $reviews[] = $row;
			if(!$reviews) echo '<span class="none">No reviews yet.</span>';
			else $posts->shortlist($reviews);
			
		}
		
		// ALBUMS
		$query = "SELECT DISTINCT(albumid), `title`, subtitle FROM albums_collection LEFT JOIN albums USING (albumid) WHERE usrid='$dat->usrid' and `view`='1' ORDER BY datetime DESC";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		if(mysqli_num_rows($res)){
			echo '<h3>Music Collection</h3><ul class="albumcoll">';
			$i = 0;
			while($row = mysqli_fetch_assoc($res)){
				$img = "/music/media/cover/standard/".$row['albumid'].".png";
				$i++;
				if($i == 8) echo '<li class="more"><a href="#morealbums" class="preventdefault" onclick="$(this).parent().hide().siblings().show();"><span>Show All Albums</span></a></li>';
				echo '<li style="'.($i > 7 ? 'display:none;' : '').'">';
				echo '<a href="/music/?id='.$row['albumid'].'" title="'.htmlSC($row['title']).($row['subtitle'] ? ' <i>'.htmlSC($row['subtitle']).'</i>' : '').'" class="tooltip">';
				echo '<img src="'.(file_exists($_SERVER['DOCUMENT_ROOT'].$img) ? $img : '/music/graphics/none.png').'" alt="'.$row['albumid'].'" border="0"/>';
				echo '</a>';
				echo '</li>';
			}
			echo '</ul><div style="clear:both;"></div>';
		}
		
		// Uploads
		$query = "SELECT img_name FROM images_tags LEFT JOIN images USING (img_id) WHERE (`tag` = 'User:$user' OR `tag` LIKE 'User:$user|%')";
		if($num_imgs = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query))){
			?>
			<h3>
				<span style="float:right; font-size:13px;"><a href="/image/-/tag/User:<?=$user?>">See all <?=$num_imgs?> uploads</a></span>
				Uploads
			</h3>
			<?
			$query.= " ORDER BY RAND() LIMIT 5";
			$res = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)){
				$img = new img($row['img_name']);
				echo '<a href="'.$img->src['url'].'"><img src="'.$img->src['tn'].'" width="100" height="100" alt="'.htmlSC($img->img_title).'" border="0"/></a> ';
			}
		}
		?>
	
	</div>
	
	<?
	
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
