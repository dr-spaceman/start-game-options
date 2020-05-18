<?
use Vgsite\Image;
use Vgsite\Badge;

$genderref = array("male" => "his", "female" => "her", "asexual" => "its", "" => "their");
$genderref2 = array("male" => "him", "female" => "her", "asexual" => "it", "" => "them");
$genderref3 = array("male" => "he", "female" => "she", "asexual" => "it", "" => "they");

if($u->rank >= 3) $status = "vip";
if($u->rank >= 7) $status = "staff";

//prefs
/*$query = "SELECT * FROM `users_prefs` WHERE `usrid` = '".$dat->usrid."' LIMIT 1";
$res   = mysqli_query($GLOBALS['db']['link'], $query);
$prefs = mysqli_fetch_object($res);*/

$stream = array();

function streamItem($s){
	$GLOBALS['stream'][strtotime($s['datetime'])].= 
	'<div class="streamitem nohov '.$s['class'].'" title="'.formatDate($s['datetime']).'">'.
		($s['img'] ? '<div class="img">'.$s['img'].'</div>' : '').
		'<div class="description">'.$s['description'].'<span class="pt"></span></div>'.
		'<div class="overlay"></div>'.
	'</div>';
}

// Badges
$_badges = new badges();
foreach($_badges->badgesEarnedList($u->id) as $row){
	$s = array(
		"class" => "badge",
		"datetime" => $row['datetime'],
		"img" => '<a href="/badges/'.$u->username.'/'.$row['bid'].'/'.formatNameURL($row['name']).'" class="badge"><img src="/bin/img/badges/'.$row['bid'].'.png" width="140" height="140" border="0" title="'.htmlSC($row['name']).'"/></a>',
		"description" => '<big><b>'.$row['name'].'</b></big><blockquote>'.$row['description'].'</blockquote>'
	);
	streamItem($s);
}

// Love & Hate
$query = "SELECT op, remarks, `datetime`, `type`, `title`, `description`, rep_image FROM pages_fan LEFT JOIN pages USING(`title`) WHERE usrid = '$u->id' ORDER BY op, datetime desc";
$res = mysqli_query($GLOBALS['db']['link'], $query);
while($row = mysqli_fetch_assoc($res)){
  $title_sc = htmlSC($row['title']);
  $repimgtn = "";
  if($repimg = $row['rep_image']){
	  if(substr($repimg, 0, 4) == "img:"){
			$img_name = substr($repimg, 4);
			$img = new img($img_name);
			$repimg = $img->src['url'];
			$repimgtn = $img->src['sm'];
		} else {
			$pos = strrpos($repimg, "/");
			$repimgtn = substr($repimg, 0, $pos) . "/md_" . substr($repimg, ($pos + 1), -3) . "png";
		}
		if(!$repimgtn || !file_exists($_SERVER['DOCUMENT_ROOT'].$repimgtn)) $repimgtn = '';
	}
	$url = pageURL($row['title'], $row['type']);
	$s = array(
		"class" => "fan ".$row['op'],
		"datetime" => $row['datetime'],
		"img" => ($repimgtn ? '<a href="'.$url.'" title="'.$title_sc.'"><img src="'.$repimgtn.'" alt="'.$title_sc.'" border="0"/></img></a>' : ''),
		"description" => '<span class="op-sm" title="I '.$row['op'].' this"></span>[['.$row['title'].']]' . ($row['remarks'] ? '<blockquote>'.$row['remarks'].'<span class="pt"></span></blockquote>' : '')
	);
	streamItem($s);
}

// other stream stuff
$query = "SELECT * FROM stream WHERE usrid = '$u->id' AND action_type != 'earn badge'";
$res = mysqli_query($GLOBALS['db']['link'], $query);
while($row = mysqli_fetch_assoc($res)){
	$class = "";
	if(strstr($row['action'], "Patron Saint")){
		$class = "patronsaint";
		$row['action'] = str_replace("[[User:{$u->username}]] became the ", "", $row['action']);
		$row['action'] = '<big>'.ucfirst($row['action']).'</big><span class="halo"></span>';
	}
	$s = array(
		"class" => $class,
		"datetime" => $row['datetime'],
		"img" => "",
		"description" => $row['action']
	);
	streamItem($s);
}

krsort($stream);

$o_stream = '';
$i = 0;
foreach($stream as $row){
	$o_stream.= $row;
	//if(++$i > 20) break;
}
$bb = new bbcode();
$o_stream = $bb->bb2html($o_stream);

$unlen = strlen($u->username);
if($unlen > 14) $h1class = "ultracondensed";
elseif($unlen > 11) $h1class = "condensed";

?>
<script>$.jGrowl("You've accessed the new profile stream. This is a work in progress!")</script>

<div id="user-profile-header">
	
	<h1 title="#<?=$u->id?>" class="<?=$h1class?>"><?=$u->username?></h1>
	
	<nav>
		<ul>
			<li class="on"><a href="/~<?=$u->username?>">Activity</a></li>
			<li><a href="/~<?=$u->username?>/fan">Fan Space</a></li>
			<li><a href="/~<?=$u->username?>/collection">Game Collection</a></li>
			<li><a href="/badges/<?=$u->username?>">Badges</a></li>
			<li><a href="">Page Edits</a></li>
			<li><a href="/~<?=$u->username?>/sblog">Sblog Posts</a></li>
			<li><a href="">Forum Posts</a></li>
			<li><a href="">Reputation</a></li>
		</ul>
	</nav>
	
</div>

<div id="user-profile-container" class="stream">
	
	<div id="userinfo">
		
		<?=($status ? '<span class="userstatus '.$status.'" title="'.$status.'"></span>' : '')?>
		
		<div class="img"><img src="<?=$u->avatar?>" width="135" height="150" alt="<?=$u->username?>'s avatar"/><span class="overlay"></span></div>
		
		<div class="container">
			<dl>
				<?=($u->data['handle'] ? '<dd class="handle">'.$u->data['handle'].'</dd>' : '')?>
				<?=($u->data['interests'] ? '<dd class="bio">'.$u->data['interests'].'</dd>' : '')?>
				<dd class="since">Member for <b title="<?=$u->data['registered']?>"><?=timeSince($u->data['registered'])?></b></dd>
				<dd class="since">Last seen <b title="<?=$u->data['activity']?>"><?=timeSince($u->data['activity'])?></b> ago</dd>
				<dd class="buttons">
					<a href="/~<?=$u->username?>/blog">Blog<span style="width:16px; height:16px; background-position:-40px -40px;"></span></a>
					<a href="/contact-user.php?user=<?=$u->username?>&method=pm">Contact<span style="width:20px; height:16px; background-position:0 -40px;"></span></a>
					<!--<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script><fb:like layout="button_count" show_faces="false" width="90" font="arial"></fb:like>-->
				</dd>
			</dl>
		</div>
		
		<div class="rolodex">
			<dl>
				<dt class="top">Name</dt>
				<dd class="top"><?=($u->data['name'] ? $u->data['name'] : '?')?></dd>
				
				<dt>Location</dt>
				<dd><?=($u->data['location'] ? $u->data['location'] : '?')?></dd>
				
				<dt>Gender</dt>
				<dd><?=($u->data['gender'] ? ($u->data['gender'] == "asexual" ? 'Asexual or Robot' : ucwords($u->data['gender'])) : '?')?></dd>
				
				<dt>Birthday</dt>
				<dd><? $bday = formatDate($u->data['dob'], 9); echo $bday ? $bday : '?'; ?></dd>
				
				<?
				if($u->data['homepage']){
					preg_match('@^(http|https|ftp)://([^/]*)/?.*@i', $u->data['homepage'], $matches);
					$o = str_replace("www.", "", $matches[2]);
					if(strlen($o) > 14) $o = substr($o, 0, 43).'&hellip;';
					if(strlen($o) > 26) $o = substr($o, 0, 25).'<br/>'.substr($o, 25);
					echo '<dt>Web</dt>'."\n".'<dd><a href="'.$u->data['homepage'].'" target="_blank" style="white-space:nowrap">'.$o.'</a></dd>'."\n";
				}
				?>
			</dl>
		</div>
		
		<div class="score" style="margin:15px 0 0;">
			<dl>
				<dt><?=number_format($u->score['vars']['num_forumposts'])?></dt>
				<dd>Forum Posts</dd>
				
				<dt><?=number_format($u->score['vars']['num_sblogposts'])?></dt>
				<dd>Sblog Posts</dd>
				
				<dt><?=number_format($u->score['vars']['num_pageedits'])?></dt>
				<dd>Page Edits</dd>
				
				<dt style="background-color:#E11E1E;"><?=number_format(ceil($u->score['total']))?></dt>
				<dd><b>Reputation</b></dd>
			</dl>
		</div>
		
	</div>
	
	<div class="userstream">
		<?=$o_stream?>
	</div>
	
	<div class="loading">Loading activity stream</div>

</div>
<?

$page->kill();

?>
	
	
	<br style="clear:both;"/>
	
	<!-- about me -->
	
	<br style="clear:left;"/>
	<?
	
	if($dat->usrid != 1 || $usrid == 1){ //Don't show Matt's amazingly awesome, intimidating scores/badges etc.
		?>
		<!-- badges -->
		<div>
			<h3 style="margin-top:1em;">Badges Earned</h3>
			<?=$_badges->collection($dat->usrid, $dat->username)?>
		</div>
		<?
	}
	
	// fan space / hate space //
	
	$query = "SELECT op, remarks, `type`, `title`, `description`, rep_image FROM pages_fan LEFT JOIN pages USING(`title`) WHERE usrid = '$dat->usrid' ORDER BY op, datetime desc";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if($num_fan = mysqli_num_rows($res)){
		while($row = mysqli_fetch_assoc($res)){
			$ops[$row['op']][] = $row;
			$ops_count[$row['op']]++;
		}
	}
	
	?>
	<!-- fan space -->
  <div id="fanspace">
  	<?
  	if(!$num_fan = mysqli_num_rows($res)){
		  echo '<h3>Fan Space</h3>'.$dat->username." doesn't love or hate anything -- ".$genderref3." is 100% neutral about everything.";
		} else {
	  	foreach(array_keys($ops_count) as $op){
	  		?>
		  	<div class="<?=(count($ops_count) == 2 ? 'col' : '')?>">
			    <h3 title="Things <?=$dat->username?> <?=($op == "love" ? 'Loves' : 'Hates')?>"><?=($op == "love" ? 'Fan' : 'Hate')?> Space</h3>
			    <?
			    $num_op = count($ops[$op]);
					$ul = '';
					$num_ul = 0;
					$height = 0;
					$max_height = 1;
					$frame_height = 0;
					//if($num_op > 6) $max_height = 200;
		      foreach($ops[$op] as $row){
		        $title_sc = htmlSC($row['title']);
		        $pos = strrpos($row['rep_image'], "/");
		        $repimgtn = $row['rep_image'] ? substr($row['rep_image'], 0, $pos) . "/" . ($row['type'] == "person" ? "profile_" : "md_") . substr($row['rep_image'], ($pos + 1), -3) . "png" : '';
						if(!$repimgtn || !file_exists($_SERVER['DOCUMENT_ROOT'].$repimgtn)){
							$repimgtn = '';
							$h = 0;
						} else {
							$attr = getimagesize($_SERVER['DOCUMENT_ROOT'].$repimgtn);
							$h = $attr[1] / ($attr[0] / 140);
							$h = floor($h);
						}
						if($height + ($h ? $h : 80) > $max_height){
							$ul.= '</ul>';
							if($height > $frame_height) $frame_height = $height;
							$height = 0;
						}
						if($height == 0){
							$ul.= '<ul>';
							$num_ul++;
						}
						$height+= $h ? $h : 80;
		        $ul.= '<li><figure style="'.($h ? 'height:'.$h.'px' : '').'"><a href="'.pageURL($row['title'], $row['type']).'" title="'.$title_sc.'">'.($repimgtn ? '<span class="tn"><img src="'.$repimgtn.'" alt="'.$title_sc.'"/></span>' : '').'<figcaption><b>'.$row['title'].'</b>'.($row['remarks'] ? '<span class="remarks">'.$row['remarks'].'<span class="pt"></span></span>' : '').'</figcaption><span class="op '.$row['op'].'" title="I '.ucwords($row['op']).' this"></span></a><span class="overlay"></span></figure></li>';
		      }
					$ul.= '</ul>';
					
					?>
					<nav>
						<?
						$col_max = count($ops_count) == 2 ? 3 : 6;
						if($num_ul > $col_max){
							echo '<ol class="pgn">';
							for($i=0; $i <= $num_ul/$col_max; $i++){
								echo '<li class="'.($i == 0 ? 'on' : '').'"><a href="#page'.($i + 1).'">Page '.($i + 1).'</a></li>';
							}
							echo '</ol></nav';
						}
						?>
					</nav>
					
					<div class="frame">
						<div class="container" style="width:<?=($num_ul * 160)?>px">
							<?=$ul?>
							<br style="clear:both"/>
						</div>
					</div>
					
				</div>
				<?
			}
			?>
			<script>
				$("#fanspace .frame").css("height", $("#fanspace .frame .container").height() + 20 + "px");
				$("#fanspace .remarks").each(function(){
					if($(this).width() < 98) $(this).css({'left':'50%', 'margin-left':-8 - $(this).width() / 2 + 'px'});
				})
				$("#fanspace nav ol.pgn").on('click', 'li', function(){
					if($(this).hasClass("on")) return;
					$(this).addClass("on").siblings().removeClass("on");
					var offs = 0 - ($(this).index() * <?=(count($ops_count) == 2 ? '480' : '960')?>); console.log(offs);
					$(this).closest("nav").siblings(".frame").children(".container").animate({"left":offs+"px"}, 500);
				})
			</script>
			<?
		}
		?>
  </div>
	
	<!-- game collection -->
  <?/*
  <div>
    <h3 style="margin-top:1em;">Game Collection</h3>
    <?
    $query = "SELECT op, `type`, `title`, `description`, rep_image FROM pages_fan LEFT JOIN pages USING(`title`) WHERE usrid = '$dat->usrid' ORDER BY datetime desc";
    $res = mysqli_query($GLOBALS['db']['link'], $query);
    if(!mysqli_num_rows($res)){
      echo $dat->username." doesn't love or hate anything -- ".(!$dat->gender || $dat->gender == "asexual" ? "it" : ($dat->gender == "male" ? "he" : "she"))." is 100% neutral about everything.";
    } else {
      use Vgsite\Shelf;
      echo '<div class="shelf" style="height:245px; overflow:auto;"><div class="container">';
      while($row = mysqli_fetch_assoc($res)){
        $shelf = new shelfItem();
        $shelf->type = $row['type'];
        $shelf->op = $row['op'];
        $shelf->img($row['rep_image']);
        $row['href'] = pageURL($row['title'], $row['type']);
        echo $shelf->outputItem($row);
      }
      echo '</div></div>';
    }
    ?>
  </div>*/ ?>
	
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
			echo '<div class="more"><b><a href="/#/posts?user='.$dat->username.'" class="arrow-right">See all '.$score['vars']['num_sblogposts'].' News & Blog posts by '.$dat->username.'</a></b></div>';
			
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
		$query = "SELECT img_name FROM images_tags LEFT JOIN images USING (img_id) WHERE (`tag` = 'User:$username' OR `tag` LIKE 'User:$username|%')";
		if($num_imgs = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query))){
			?>
			<h3>
				<span style="float:right; font-size:13px;"><a href="/image/-/tag/User:<?=$username?>">See all <?=$num_imgs?> uploads</a></span>
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
	$Query = "SELECT r.indx, r.id, r.author, r.grade, r.date, u.user, r.title, g.id, g.platform from StaffReview as r, Users as u, Games as g where u.user = '$username' and r.author = '$username' and r.id = g.id order by date DESC";
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

	$Query = "SELECT * from `ReaderReview` where `user` = '$username' AND `published` = '1'";
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


<?
$page->footer();
?>
