<?
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.img.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.shelf.php";

if($_GET['action'] == "load_shelf"){
	
	$arr = array();
	
	$region = "us";
	
	//games
	$query = "SELECT * FROM `games_publications` WHERE `region` = '".mysql_real_escape_string($region)."' AND release_date > DATE_SUB(CURDATE(),INTERVAL 14 DAY) AND `release_date` < DATE_ADD(CURDATE(),INTERVAL 30 DAY) ORDER BY release_date, release_title";
	$res   = mysql_query($query);
	while($row = mysql_fetch_assoc($res)){
		
		if(substr($row['release_date'], 7) == "-00") continue; //get rid of publications with vague release dates (ie 2012-12-00)
		if($row['release_date_tentative']) continue; //get rid of tentative releases
		
		$shelf_img = $row['img_name'] ? $row['img_name'] : $row['img_name_title_screen'];
		if(!$shelf_img) continue;
		
		$shelf = new shelfItem();
		$shelf->type = "game";
		$shelf->img = $shelf_img;
		$row['href'] = "/games/".formatNameURL($row['title']);
		$arr[$row['release_date'].++$i] = $shelf->outputItem($row);
		
	}
	
	//albums
	$query = "SELECT `title`, subtitle, albumid, datesort FROM `albums` WHERE `view` = 1 AND datesort > DATE_SUB(CURDATE(),INTERVAL 14 DAY) AND datesort < DATE_ADD(CURDATE(),INTERVAL 30 DAY)";
	$res   = mysql_query($query);
	while($row = mysql_fetch_assoc($res)){
		$shelf = new shelfItem();
		$shelf->type = "album";
		$arr[$row['datesort'].++$i] = $shelf->outputItem($row);
	}
	
	$num_shelfitems = count($arr);
	//if($num_shelfitems > 3) echo '<a href="#prev" class="trav arrow-left" style="top:33px; left:10px;"></a><a href="#next" class="trav arrow-right" style="top:33px; right:10px;"></a>';
	
	$flags = array("North America" => "us", "Japan" => "jp", "Europe" => "eu", "Australia" => "au");
	
	?>
	<div id="selregion">
		<ul>
			<?
			foreach($flags as $r => $s){
				echo '<li class="'.($r == $region ? 'on' : 'off').'"><a><img src="/bin/img/flags/'.$s.'.png" alt="'.$r.'" class="flag"/></a></li>';
			}
			?>
		</ul>
	</div>
	<div class="shelf-container mouseposscroll-container" style="width:<?=(170 * count($arr))?>px; margin-top:-7px;">
		<?
		ksort($arr);
		foreach($arr as $dt => $row){
			echo $row;
		}
		?>
	</div>
	<?
	return;
}

if($_GET['action'] == "load_vgin_stats"){
	?>
	<dt style="margin:0; padding:0;">
		<dt style="float:left;">&bull;</dt>
		<dd style="margin-left:10px;"><a href="/games/"><b><?=mysql_num_rows(mysql_query("SELECT * FROM pages WHERE `type`='game'"))?></b> Games</a> like 
			<? $game=mysql_fetch_assoc(mysql_query("SELECT `title` FROM `pages` WHERE `type`='game' ORDER BY RAND() LIMIT 1")); ?><a href="/games/<?=formatNameURL($game['title'])?>"><?=$game['title']?></a>
			<ul class="gameops">
				<li><span class="icon"></span>Most loved: <? $most_loved=mysql_fetch_assoc(mysql_query("SELECT `title`, COUNT(`title`) AS `count` FROM `pages_fan` WHERE `op` = 'love' AND `title` NOT LIKE 'AlbumId:%' GROUP BY `title` ORDER BY `count` DESC LIMIT 1")); ?><a href="/games/<?=formatNameURL($most_loved['title'])?>"><?=$most_loved['title']?></a></li>
				<li><span class="icon" style="background-position:-30px -30px"></span>Most hated: <? $most_hated=mysql_fetch_assoc(mysql_query("SELECT `title`, COUNT(`title`) AS `count` FROM `pages_fan` WHERE `op` = 'hate' AND `title` NOT LIKE 'AlbumId:%' GROUP BY `title` ORDER BY `count` DESC LIMIT 1")); ?><a href="/games/<?=formatNameURL($most_hated['title'])?>"><?=$most_hated['title']?></a></li>
				<li><span class="icon" style="background-position:-60px -30px"></span>Most collected: <? $most_collected=mysql_fetch_assoc(mysql_query("SELECT `title`, COUNT(`title`) AS `count` FROM `collection` WHERE `title` NOT LIKE 'AlbumId:%' GROUP BY `title` ORDER BY `count` DESC LIMIT 1")); ?><a href="/games/<?=formatNameURL($most_collected['title'])?>"><?=$most_collected['title']?></a></li>
			</ul>
		</dd>
		<dt style="float:left;">&bull;</dt>
		<dd style="margin-left:10px;">
			<a href="/people/" title="videogame creators, developers, artists, musicians"><b><?=mysql_num_rows(mysql_query("SELECT * FROM pages WHERE `type`='person'"))?></b> People</a> like 
			<? $person=mysql_fetch_assoc(mysql_query("SELECT `title` FROM `pages` WHERE `type`='person' ORDER BY RAND() LIMIT 1")); ?><a href="/people/<?=formatNameURL($person['title'])?>"><?=$person['title']?></a>
		</dd>
		<dt style="float:left;">&bull;</dt>
		<dd style="margin-left:10px;">
			<a href="/characters/" title="videogame characters"><b><?=mysql_num_rows(mysql_query("SELECT * FROM pages WHERE `subcategory`='Game character'"))?></b> Characters</a> like 
			<? $character=mysql_fetch_assoc(mysql_query("SELECT `title` FROM `pages` WHERE `subcategory`='Game character' ORDER BY RAND() LIMIT 1")); ?><a href="/characters/<?=formatNameURL($character['title'])?>"><?=$character['title']?></a>
		</dd>
		<dt style="float:left;">&bull;</dt>
		<dd style="margin-left:10px;">
			<a href="/concepts/" title="videogame concepts"><b><?=mysql_num_rows(mysql_query("SELECT * FROM pages WHERE `subcategory`='Game concept'"))?></b> concepts</a> like 
			<? $concept=mysql_fetch_assoc(mysql_query("SELECT `title` FROM `pages` WHERE `subcategory`='Game concept' ORDER BY RAND() LIMIT 1")); ?><a href="/concepts/<?=formatNameURL($concept['title'])?>"><?=$concept['title']?></a>
		</dd>
		<dt style="float:left;">&bull;</dt>
		<dd style="margin-left:10px;">
			<a href="/developers/" title="videogame developers"><b><?=mysql_num_rows(mysql_query("SELECT * FROM pages WHERE `subcategory`='Game developer'"))?></b> developers</a> like 
			<? $developer=mysql_fetch_assoc(mysql_query("SELECT `title` FROM `pages` WHERE `subcategory`='Game developer' ORDER BY RAND() LIMIT 1")); ?><a href="/developers/<?=formatNameURL($developer['title'])?>"><?=$developer['title']?></a>
		</dd>
		<dt style="float:left;">&bull;</dt>
		<dd style="margin-left:10px;">
			<a href="/series/" title="videogame series"><b><?=mysql_num_rows(mysql_query("SELECT * FROM pages WHERE `subcategory`='Game series'"))?></b> franchises</a> like 
			<? $series=mysql_fetch_assoc(mysql_query("SELECT `title` FROM `pages` WHERE `subcategory`='Game series' ORDER BY RAND() LIMIT 1")); ?><a href="/series/<?=formatNameURL($series['title'])?>"><?=$series['title']?></a>
		</dd>
		<dt style="float:left;">&bull;</dt>
		<dd style="margin-left:10px;">
			<a href="/music/"><b><?=mysql_num_rows(mysql_query("SELECT * FROM albums"))?></b> Soundtracks</a> like 
			<? $album=mysql_fetch_assoc(mysql_query("SELECT `title`, subtitle, albumid FROM albums WHERE `view`=1 ORDER BY RAND() LIMIT 1")); ?><a href="/music/?id=<?=$album['albumid']?>" class="albumlink"><?=$album['title'] . ($album['subtitle'] ? ' <i>'.$album['subtitle'].'</i>' : '')?></a>
		</dd>
		<dt style="float:left;">&bull;</dt>
		<dd style="margin-left:10px;">
			<a href="/groups/"><b><?=mysql_num_rows(mysql_query("SELECT * FROM groups"))?></b> Groups</a> like 
			<? $group=mysql_fetch_assoc(mysql_query("SELECT group_id, `name` FROM `groups` WHERE `status`='open' ORDER BY RAND() LIMIT 1")); ?><a href="/groups/<?=$group['group_id']?>/<?=formatNameURL($group['name'])?>"><?=$group['name']?></a>
		</dd>
		<dt style="float:left;">&bull;</dt>
		<dd style="margin-left:10px;"><a href="/forums/"><b><?=mysql_num_rows(mysql_query("SELECT * FROM forums_topics"))?></b> Forum Topics</a>, <a href="/posts/"><b><?=mysql_num_rows(mysql_query("SELECT * FROM posts"))?></b> Sblog Posts</a></dd>
	</dl>
	<?
	exit;
}