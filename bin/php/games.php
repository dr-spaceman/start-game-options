<?

$page->style[] = "/bin/css/games.css";
$page->javascripts[] = '/bin/script/games.js';

class gamepg {

var $gid;

function getVars() {
	global $gdat;
	
	$this->varsGotten = TRUE;
	
	if(!$gdat) {
		$query = "SELECT * FROM `games` WHERE gid='$this->gid' LIMIT 1";
		if(!$GLOBALS['gdat'] = mysql_fetch_object(mysql_query($query))) {
			die("no data");
		}
	}
	
	$this->desc = "";
	
	//genre output
	$query = "SELECT genre FROM games_genres WHERE gid='$this->gid'";
	$res   = mysql_query($query);
	if($genrenum = mysql_num_rows($res)) {
		$i = 0;
		while($row = mysql_fetch_assoc($res)) {
			$i++;
			if($i == 1) {
				$vowels = array("A", "a", "E", "e", "I", "i", "O", "o", "U", "u");
				if(in_array(substr($row['genre'], 0, 1), $vowels)) $ghead['genres'] = "An ";
				else $ghead['genres'] = "A ";
				$this->desc = $ghead['genres'];
				$ghead['genres'].= '<span class="editable" id="ILedit-genre"><span id="genres">';
			}
			if($this->edit_mode) {
				if($i == 1) $ghead['genres'].= '<a href="/games/genre/'.urlencode($row['genre']).'">'.$row['genre'].'</a> &hellip;';
			} else {
				$ghead['genres'].= '<a href="/games/genre/'.urlencode($row['genre']).'"'.($i == 1 && $genrenum > 1 ? ' class="more"' : '').'>'.$row['genre'].'</a>';
				if($genrenum > $i) $ghead['genres'].= '<span>, </span>';
			}
			if($i == 1) $this->desc.= $row['genre']." game ";
		}
		$ghead['genres'].= '</span><!--#genres--></span><!--#ILedit-genre--> game ';
	} else {
		$ghead['genres'] = "A game ".($this->edit_mode ? '<span class="editable" id="ILedit-genre">[Add genres]</span> ' : '');
		$this->desc = "A game ";
	}
	
	//platform output
	$pf_change = array(
		"Nintendo Entertainment System" => "NES",
		"personal computer" => "PC"
	);
	$query = "SELECT platform, platform_shorthand, games_publications.id FROM games_publications 
	LEFT JOIN games_platforms USING (platform_id) 
	WHERE gid='".$this->gid."' ORDER BY `primary` DESC";
	$res = mysql_query($query);
	$i = 0;
	$pfs = array();
	while($row = mysql_fetch_assoc($res)) {
		if($row['platform_shorthand'] != 'misc') {
			$i++;
			$x = '<a href="/games/'.$row['platform_shorthand'].'/">'.(in_array($row['platform'], array_keys($pf_change)) ? $pf_change[$row['platform']] : $row['platform']).'</a>';
			if(!in_array($x, $pfs)) $pfs[] = $x;
		}
	}
	if($pfnum = count($pfs)) {
		$i = 0;
		$ghead['pf'] = 'for ';
		foreach($pfs as $pf) {
			$i++;
			$ghead['pf'].= $pf;
			if(($pfnum - 1) > $i) $ghead['pf'].= ", ";
			elseif(($pfnum - 1) == $i) $ghead['pf'].= ($pfnum >= 3 ? "," : "")." and ";
		}
		$this->desc.= strip_tags($ghead['pf']);
		$this->platforms = implode(", ", $pfs);
	}
	
	//dev output
	$query = "SELECT developer FROM games_developers WHERE gid='$this->gid'";
	$res   = mysql_query($query);
	if($num_devs = mysql_num_rows($res)) {
		$ghead['dev'] = ' by <span id="ILedit-developers" class="editable">';
		$i = 0;
		while($row = mysql_fetch_assoc($res)) {
			$d = $row['developer'];
			$i++;
			$ghead['dev'].= '<a href="/associations/'.urlencode($d).'">'.trim($d).'</a>';
			if(($num_devs - 1) > $i) $ghead['dev'].= ", ";
			elseif(($num_devs - 1) == $i) $ghead['dev'].= ($num_devs >= 3 ? "," : "")." and ";
		}
		$ghead['dev'].= '</span>';
		$this->desc.= strip_tags($ghead['dev']);
	} elseif($this->edit_mode) {
		$ghead['dev'] = ' <span id="ILedit-developers" class="editable">[add developers]</span>';
	}
	
	//series output
	$query = "SELECT * FROM games_series WHERE gid='$this->gid'";
	$res   = mysql_query($query);
	if(mysql_num_rows($res)) {
		while($row = mysql_fetch_assoc($res)) {
			$series[] = '<a href="/games/series/'.urlencode($row['series']).'">'.$row['series'].'</a>';
		}
		$num_series = count($series);
		$ghead['series'] = ' in the <span id="ILedit-series" class="editable">';
		for($i = 0; $i < $num_series; $i++) {
			$ghead['series'].= $series[$i];
			if(($num_series - 2) > $i) $ghead['series'].= ", ";
			elseif(($num_series - 2) == $i) $ghead['series'].= ($num_series >= 3 ? "," : "")." and ";
		}
		$ghead['series'].= '</span> series';
		$this->desc.= strip_tags($ghead['series']);
	} elseif($this->edit_mode) {
		$ghead['series'] = ' <span id="ILedit-series" class="editable">[add game series]</span>';
	}
	
	$this->ghead = $ghead;
	
}

function header() {
	global $usrrank, $gdat;
	
	if(!$this->varsGotten) echo "Error: gamepage vars not gotten!";
	
	$this->header_output = TRUE;
	
	$this->bgimg = "";
	$this->bgimg_xpos = "right";
	
	if(file_exists($_SERVER['DOCUMENT_ROOT']."/games/files/".$this->gid."/background.jpg")) $this->bgimg = "/games/files/".$this->gid."/background.jpg";
	elseif(file_exists($_SERVER['DOCUMENT_ROOT']."/games/files/".$this->gid."/background.png")) $this->bgimg = "/games/files/".$this->gid."/background.png";
	elseif(file_exists($_SERVER['DOCUMENT_ROOT']."/games/files/".$this->gid."/background.gif")) $this->bgimg = "/games/files/".$this->gid."/background.gif";
	elseif(file_exists($_SERVER['DOCUMENT_ROOT']."/games/files/".$this->gid."/background_right.png")) $this->bgimg = "/games/files/".$this->gid."/background_right.png";
	elseif(file_exists($_SERVER['DOCUMENT_ROOT']."/games/files/".$this->gid."/background_right.gif")) $this->bgimg = "/games/files/".$this->gid."/background_right.gif";
	elseif(file_exists($_SERVER['DOCUMENT_ROOT']."/games/files/".$this->gid."/background_right.jpg")) $this->bgimg = "/games/files/".$this->gid."/background_right.jpg";
	elseif(file_exists($_SERVER['DOCUMENT_ROOT']."/games/files/".$this->gid."/background_left.png")) {
		$this->bgimg = "/games/files/".$this->gid."/background_left.png";
		$this->bgimg_xpos = "left";
	} elseif(file_exists($_SERVER['DOCUMENT_ROOT']."/games/files/".$this->gid."/background_left.jpg")) {
		$this->bgimg = "/games/files/".$this->gid."/background_left.jpg";
		$this->bgimg_xpos = "left";
	} elseif(file_exists($_SERVER['DOCUMENT_ROOT']."/games/files/".$this->gid."/background_left.gif")) {
		$this->bgimg = "/games/files/".$this->gid."/background_left.gif";
		$this->bgimg_xpos = "left";
	}
	
	if($this->edit_mode) {
		?>
		<div style="position:absolute; right:30px; margin-top:15px; border:1px solid #DDD; background-color:white; padding:3px 6px; font-size:12px;">
			<span style="background:url(/bin/img/icons/edit.gif) no-repeat left center; padding-left:12px; color:#999; font-weight:bold;">OTHER</span>
			 &middot; <span id="ILedit-keywords" class="editable">Keywords</span>
			<?=($usrrank >= 8 ? '
			 &middot; <span id="ILedit-bgimg" class="editable">Background Image</span>
			 &middot; <span id="ILedit-status" class="editable">Status</span>
			' : '')?>
		</div>
		<?
	}
	
	?>
	<div id="game-page"><div id="game-page-2" style="<?=($this->bgimg ? 'background:url('.$this->bgimg.') no-repeat '.$this->bgimg_xpos.' top;' : '')?>">
		<div id="game-head">
			<?
		
			//get stuff for navigation
			
			//links
			$q = "SELECT * FROM games_links WHERE gid='$this->gid'";
			$this->num_links = mysql_num_rows(mysql_query($q));
			
			//media
			$query = "SELECT SUM(m.quantity) qty, c.category 
				FROM media m, media_categories c, media_tags t 
				WHERE m.unpublished != '1' AND t.tag='gid:".$this->gid."' AND m.media_id=t.media_id AND c.category_id=m.category_id 
				GROUP BY (c.category)";
			$res   = mysql_query($query);
			$this->num_media = 0;
			while($row = mysql_fetch_assoc($res)) {
				$this->footer_medias[] = '<a href="media">'.$row['category'].'</a>';
				$this->num_media = $this->num_media + $row['qty'];
			}
			
			//guide
			$q = "SELECT * FROM games_guides WHERE gid='$this->gid' AND `published`='1' LIMIT 1";
			if($guide = mysql_fetch_object(mysql_query($q))) {
				$this->has_guide = TRUE;
				$this->guide_phrase = '<b>Hey, listen!</b> Videogam.in\'s <a href="guide/" class="tooltip" title="it is so amazing">Guide to '.$gdat->title.'</a> includes '.
					($guide->characters ? "character data, " : "").
					($guide->walkthrough ? "extensive walkthrough, " : "").
					($guide->secrets ? "secrets & strategies, " : "").
					($guide->data ? "game data, " : "").
					($guide->equipment ? "items & equipment, " : "").
					($guide->bestiary ? "bestiary & monster data, " : "");
				$this->guide_phrase = substr($this->guide_phrase, 0, -2);
				$this->guide_phrase.= " and more.";
			}
			
			//encyclopedia
			$q = "SELECT * FROM wiki WHERE `field`='preview' AND subject_field='gid' AND subject_id='$this->gid' AND `datetime`!='0000-00-00 00:00:00' LIMIT 1";
			$this->has_encyclopedia = mysql_num_rows(mysql_query($q));
			
			//people
			$q = "SELECT * FROM people_work WHERE gid='".$this->gid."'";
			$this->num_people = mysql_num_rows(mysql_query($q));
			
			//music
			$query = "SELECT * FROM albums_tags LEFT JOIN albums USING (albumid) WHERE gid='".$this->gid."' ORDER BY title, subtitle";
			$res = mysql_query($query);
			while($row = mysql_fetch_assoc($res)) {
				$this->albumdata[] = $row;
				$this->num_albums++;
			}
			
			//fans
			$query = "SELECT DISTINCT(usrid) FROM my_games WHERE gid='$this->gid'";
			$res   = mysql_query($query);
			$this->num_fans = mysql_num_rows($res);
			
			//posts
			$query = "SELECT * FROM posts LEFT JOIN posts_tags USING (nid) WHERE tag='gid:".$gdat->gid."' AND unpublished != 1 AND pending != 1 AND privacy = 'public'";
			$this->num_news = mysql_num_rows(mysql_query($query));
			
			if($this->subs[0] == "") $here['overview'] = '<h3>';
			elseif($this->subs[0] == "media") $here['media'] = '<h3>';
			elseif($this->subs[0] == "music") $here['music'] = '<h3>';
			elseif($this->subs[0] == "news") $here['news'] = '<h3>';
			elseif($this->subs[0] == "guide") $here['guide'] = '<h3>';
			elseif($this->subs[0] == "fans") $here['fans'] = '<h3>';
			elseif($this->subs[0] == "developers") $here['developers'] = '<h3>';
			else $here['overview'] = '<h3>';
			
			$link = '/games/'.$gdat->gid.'/'.$gdat->title_url;
		
			?>
			<h2><span class="editable" id="ILedit-title"><?=$gdat->title?></span></h2>
			
			<div id="game-particulars">
				<?
				echo	$this->ghead['genres'] . 
							$this->ghead['pf'] . 
							$this->ghead['dev'] .
							$this->ghead['series'];
				?>
			</div>
			
			<div id="developers"></div>
			<div id="media"></div>
			
			<div id="gnav">
				<ol>
					<li id="gnav-overview">
						<?=$here['overview']?>
						<a href="<?=$link?>" title="<?=htmlSC($gdat->title)?> overview">Overview</a>
						<?=($here['overview'] ? '</h3>' : '')?>
					</li>
					<li>
						<?=$here['news']?>
						<?=($this->num_news ? '<a href="/posts/topics/gid:'.$gdat->gid.'/'.$gdat->title_url.'" title="'.htmlSC($gdat->title).' news & blogs">News & Blogs</a>' : '<a href="/posts/manage.php?action=newpost" class="off" rel="nofollow" title="add a news article, blog post, or other content"><span>News & Blogs</span></a>')?>
						<?=($here['news'] ? '</h3>' : '')?>
					</li>
					<li id="gnav-media">
						<?=$here['media']?>
						<?=($this->num_media ? '<a href="'.$link.'/media" title="'.htmlSC($gdat->title).' media">Media</a>' : '<a href="'.$link.'#contribute-screens" class="off" rel="nofollow" title="Upload some media"><span>Media</span></a>')?>
						<?=($here['media'] ? '</h3>' : '')?>
					</li>
					<li><?=($this->num_reviews ? '<a href="/games/~'.$gdat->title_url.'/reviews/" title="'.htmlSC($gdat->title).' reviews">Reviews</a>' : '<a href="#" class="off" rel="nofollow" title="add your review"><span>Reviews</span></a>')?></li>
					<li<?=$here['encyclopedia']?>><?=($this->has_encyclopedia ? '<a href="/games/~'.$gdat->title_url.'/encyclopedia" title="'.htmlSC($gdat->title).' encyclopedia">Encyclopedia</a>' : '<a href="#" class="off" rel="nofollow" title="Write an expert encyclopedia article"><span>Encyclopedia</span></a>')?></li>
					<li<?=$here['guide']?>><?=($this->has_guide ? '<a href="/games/~'.$gdat->title_url.'/guide" title="'.htmlSC($gdat->title).' secrets & strategies">Strategy Guides</a>' : '<a href="#" class="off" rel="nofollow" title="create a strategy guide"><span>Game Guide</span></a>')?></li>
					<li id="gnav-people">
						<?=$here['developers']?>
						<?=($this->num_people ? '<a href="'.$link.'/developers" title="'.htmlSC($gdat->title).' developer credits">Developers</a>' : '<a href="'.$link.'#contribute-person" class="off" rel="nofollow" title="Credit a game developer"><span>Developers</span></a>')?>
						<?=($here['developers'] ? '</h3>' : '')?>
					</li>
					<li id="gnav-music">
						<?=$here['music']?>
						<?=($this->num_albums ? '<a href="'.$link.'/music" title="'.htmlSC($gdat->title).' music albums">Music</a>' : '<a href="#" class="off" rel="nofollow" title="Contribute music info"><span>Music</span></a>')?>
						<?=($here['music'] ? '</h3>' : '')?>
					</li>
					<li id="gnav-fans">
						<?=$here['fans']?>
						<?=($this->num_fans ? '<a href="'.$link.'/fans" title="'.htmlSC($gdat->title).' fans">Fans</a>' : '<a href="#" class="off" rel="nofollow" title="Become a fan"><span>Fans</span></a>')?>
						<?=($here['fans'] ? '</h3>' : '')?>
					</li>
					<li><a href="/forums/?tag=gid:<?=$this->gid?>" title="<?=htmlSC($gdat->title)?> forums">Forums</a></li>
					<?=($usrrank > 6 ? '<li><a href="/ninadmin/games-mod.php?id='.$this->gid.'" title="Admin: edit this game" rel="nofollow"><span style="padding-left:14px; background:url(/bin/img/icons/edit.gif) no-repeat left center;">Administer</span></a></li>' : '')?>
				</ol>
				
				<dl id="contributors">
					<?
					$conts = array();
					$query = "SELECT usrid, type_id, datetime, points FROM users_contributions LEFT JOIN users_contributions_types USING (type_id) 
						WHERE supersubject='gid:".$this->gid."' AND published='1'";
					$res   = mysql_query($query);
					$num   = mysql_num_rows($res);
					$i = 0;
					$total_points = 0;
					$points = array();
					while($row = mysql_fetch_assoc($res)) {
						$x = "usrid:".$row['usrid'];
						if(!in_array($x, $conts)) $conts[] = $x;
						if($row['type_id'] == "1") $this->creator = "by ".outputUser($row['usrid'], FALSE)." ";
						$i++;
						if($i == $num && $num > 1) {
							$this->modified = $row['datetime'];
							$this->modifier = "by ".outputUser($row['usrid'], FALSE)." ";
							if($this->modifier == $this->creator) unset($this->modifier);
						}
						//$total_points = $total_points + $row['points'];
						$points[$x] = $points[$x] + $row['points'];
					}
					arsort($points);
					$arr = array();
					$arr = explode(",", $gdat->contributors);
					foreach($arr as $a) {
						if(!in_array($a, $conts)) $conts[] = $a;
					}
					$i = 0;
					foreach(array_keys($points) as $c) {
						$i++;
						if(substr($c, 0, 6) == "usrid:") {
							$q = "SELECT username, avatar FROM users WHERE usrid='".str_replace("usrid:", "", $c)."' LIMIT 1";
							$usrdat = mysql_fetch_object(mysql_query($q));
						} else unset($usrdat);
						if($i == 1) {
							echo '<dt>Patron Saint</dt>';
							$avfile = "/bin/img/avatars/sm/".($usrdat->avatar ? $usrdat->avatar : 'unknown.png');
						} elseif($i == 2) echo '<dt>Other contributers</dt>';
						if($usrdat) {
							echo '<dd'.($i == 1 ? ' class="ps" style="background-image:url('.$avfile.');"' : '').'>'.outputUser(str_replace("usrid:", "", $c), FALSE).'</dd>';
						} else {
							echo '<dd>'.$c.'</dd>';
						}
					}
					?>
				</dl>
			</div>
		
		</div><!-- #game-head -->
		<div id="add-game"></div>
		<div id="game-cont">
		<?
}

}
?>