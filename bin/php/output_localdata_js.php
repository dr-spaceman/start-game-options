<?
$ret = '
<script type="text/javascript">

	var LDgames = [
		';
		$query = "SELECT gid, title, title_url FROM games ORDER BY title";
		$res = mysql_query($query);
		while($row = mysql_fetch_assoc($res)) {
			$query2 = "SELECT platform, release_date FROM games_publications LEFT JOIN games_platforms USING (platform_id) WHERE gid='".$row['gid']."' ORDER BY `primary` DESC LIMIT 3";
			$res2   = mysql_query($query2);
			if(mysql_num_rows($res2)) {
				$i = 0;
				while($row2 = mysql_fetch_assoc($res2)) {
					$i++;
					if($i == 1) {
						if(!$row2['release_date']) $x.= "???";
						else $x.= substr($row2['release_date'], 0, 4);
					}
					if($i < 3) {
						$x.= ' &middot; '.$row2['platform'];
					} else {
						$x.= ' &hellip;';
					}
				}
			}
			$ret.= '"'.$row['gid'].'|'.htmlSC($row['title']).'|'.$row['title_url'].'|'.$x.'",';
		}
		$ret.= '
	];
	
	var LDdevs = [
		';
		$query = "SELECT DISTINCT(`developer`) FROM games_developers";
		$res   = mysql_query($query);
		while($row = mysql_fetch_assoc($res)) {
			$query2 = "SELECT * FROM games_developers WHERE developer = '".$row['developer']."'";
			$num = array();
			$num['games'] = mysql_num_rows(mysql_query($query2));
			$query2 = "SELECT * FROM people WHERE assoc_co LIKE '`".$row['developer']."`'";
			$num['people'] = mysql_num_rows(mysql_query($query2));
			$ret.= '"'.htmlSC($row['developer']).'|'.$num['games'].'|'.$num['people'].'",';
		}
		$ret.= '
	];
	
	var LDgenres = [
		';
		$query = "SELECT DISTINCT(`genre`), COUNT(`genre`) AS `count` FROM games_genres GROUP BY `genre` ORDER BY `count` DESC";
		$res   = mysql_query($query);
		while($row = mysql_fetch_assoc($res)) {
			$ret.= '"'.htmlSC($row['genre']).'",';
		}
		$ret.= '
	];
	
	var LDseries = [
		';
		$query = "SELECT DISTINCT(`series`), COUNT(`series`) AS `count` FROM games_series GROUP BY `series` ORDER BY `series` DESC";
		$res   = mysql_query($query);
		while($row = mysql_fetch_assoc($res)) {
			$ret.= '"'.htmlSC($row['series']).'|'.$row['count'].'",';
		}
		$ret.= '
	];

</script>
';

$page->javascript.= $ret;
?>