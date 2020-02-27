<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.ajax.php");

$q = $_GET['q'];

$a = new ajax();

//outp format: [0]sortable val [1]real val [2]description [3]url [4]bbcode link

//games
$query = "SELECT gid, `title`, title_url FROM games WHERE `title` LIKE '%$q%' OR `keywords` LIKE '%$q%' limit $limit";
$res = mysql_query($query);
while($row = mysql_fetch_assoc($res)) {
	$x = array();
	$x['title_sort'] = $row['title']." 0";
	$x['title'] = $row['title'];
	$query2 = "SELECT platform, release_date FROM games_publications LEFT JOIN games_platforms USING (platform_id) WHERE gid='".$row['gid']."' ORDER BY `primary` DESC LIMIT 3";
	$res2   = mysql_query($query2);
	if(mysql_num_rows($res2)) {
		$i = 0;
		while($row2 = mysql_fetch_assoc($res2)){
			$i++;
			if($i == 1) {
				if($row2['release_date']) $x['release_date'] = $row2['release_date'];
			}
			if($i < 3) {
				$x['platform'] = $row2['platform'];
			}
		}
	}
	$ret[] = $x.'</small>|/games/~'.$row['title_url'].'|[['.$row['title'].']]';
}

//game series
$query = "SELECT DISTINCT(`series`), COUNT(`series`) AS `count` FROM games_series WHERE `series` LIKE '%$q%' GROUP BY `series` ORDER BY `series` DESC";
$res   = mysql_query($query);
while($row = mysql_fetch_assoc($res)) {
	$ret[] = $row['series']." 1|".$row['series']."|<small>Game Series &middot; ".$row['count']." games</small>|/games/series/".urlencode($row['series'])."|[[".$row['series']." series]]";
}

//people
$query = "SELECT `name`, name_url, `title` FROM people WHERE `name` LIKE '%$q%' OR `alias` LIKE '%$q%' limit $limit";
$res = mysql_query($query);
while($row = mysql_fetch_assoc($res)) {
	$ret[] = $row['name']." 2|".$row['name']."|<small>".$row['title']."</small>|/people/~".$row['name_url']."|[[".$row['name']."]]";
}

//assoc
$query = "SELECT DISTINCT(`developer`) FROM games_developers WHERE `developer` LIKE '%$q%' limit $limit";
$res   = mysql_query($query);
while($row = mysql_fetch_assoc($res)) {
	$x = $row['developer']." 3|".$row['developer']."|<small>Development Group";
	$query2 = "SELECT * FROM games_developers WHERE developer = '".$row['developer']."'";
	$num = "";
	if($num = mysql_num_rows(mysql_query($query2))) $x.= " &middot; $num game".($num != 1 ? "s" : "");
	$query2 = "SELECT * FROM people WHERE assoc_co LIKE '`".$row['developer']."`'";
	$num = "";
	if($num = mysql_num_rows(mysql_query($query2))) $x.= " &middot; $num ".($num != 1 ? "people" : "person");
	$ret[] = $x."</small>|/associations/".urlencode($row['developer'])."|[[".$row['developer']."]]";
}

//music
$query = "SELECT `title`, subtitle, cid, albumid FROM albums WHERE title LIKE '%$q%' OR keywords LIKE '%$q%' limit $limit";
$res = mysql_query($query);
while($row = mysql_fetch_assoc($res)) {
	$p_title = $row['title'].($row['subtitle'] ? ' <i>'.$row['subtitle'].'</i>' : '');
	$ret[] = $row['title'].($row['subtitle'] ? ' '.$row['subtitle'] : '4').'|<span class="music">'.$p_title.'</span>||/music/?id='.$row['albumid'].'|[url=/music/?id='.$row['albumid'].']'.strip_tags($p_title).'[/url]';
	//if(strlen($p_title) > 50) $p_title = substr($p_title, 0, 40)."&hellip;".substr($p_title, -8);
}

sort($ret);
foreach($ret as $r) echo $r."\n";