<?

/*class Stream
{
	$this->activities = array();

	function __construct($truncate_news_articles = false, $distance = "1 WEEK")
	{
		$this->truncate_news_articles = $truncate_news_articles;
		$this->distance = $distance;

		$this->addActivity(new ActivityItem());
	}

	function output()
	{
	}

	function addActivity()
	{
	}
}

class ActivityItem
{
	function __construct($query, $datetime, $type, $actor, $data)
	{
		$this->query = $query;
		$this->results = mysqli_query($GLOBALS['db']['link'], $query);
		$this->datetime = $datetime;
		$this->type = $type;
		$this->actor = $actor;
		$this->data = $data;

		while ($row = mysqli_fetch_assoc($results))
		{
			$this->dateYmd = date("Y-m-d", strtotime($row['datetime']));
			$this->time = strtotime($row['datetime']);
		}
	}
}*/

function latestActivity($truncate_news_articles = false, $distance = "1 WEEK"){

	// Rahul, 6/23/07
	// Create a "river" of sitewide activity by our userbase.
	// Return an array keyed by day and sorted in reverse, containing arrays keyed by timestamp and sorted in reverse.
	// Each entry contains various info but MUST contain: type, actor, time, data.
	// Data is specific to each type.
	// Each entry is "designed" to output the following, in general:
	//	 User performed Action on Target at Time

	$activities = array();
	//$distance = "1 WEEK";
	$s = $truncate_news_articles;
	
	// User gave Game a X
	$userRatings = mysqli_query($GLOBALS['db']['link'], "SELECT gg.*, g.* FROM games_grades gg, games g WHERE g.gid = gg.gid AND datetime > DATE_SUB(NOW(), INTERVAL $distance) ORDER BY datetime DESC");
	while ($row = mysqli_fetch_assoc($userRatings))
		$activities[date("Y-m-d", strtotime($row['datetime']))][strtotime($row['datetime'])] = array(
			"type"		=> "rating",
			"actor"		=> $row['usrid'],
			"time"		=> $row['datetime'],
			"data"		=> array(
				"game"	=> stripslashes($row['title']),
				"id"	=> $row['gid'],
				"grade" => $row['grade'],
				"url" => $row['title_url'] //'/games/'.specialUrlEncode($row['platform']).'/'.$row['title_url'].'/'
			)
		);

	// User thinks/says/asserts/etc Game is X
	$userHype = mysqli_query($GLOBALS['db']['link'], "SELECT gf.*, g.* FROM games_forecasts gf, games g WHERE g.gid = gf.gid AND datetime > DATE_SUB(NOW(), INTERVAL $distance) ORDER BY datetime DESC");
	while ($row = mysqli_fetch_assoc($userHype))
		$activities[date("Y-m-d", strtotime($row['datetime']))][strtotime($row['datetime'])] = array(
			"type"		=> "forecast",
			"actor"		=> $row['usrid'],
			"time"		=> $row['datetime'],
			"data"		=> array(
				"game"	=> stripslashes($row['title']),
				"id"	=> $row['gid'],
				"forecast" => GetForecast($row['rating']),
				"url" => $row['title_url'] //'/games/'.specialUrlEncode($row['platform']).'/'.$row['title_url'].'/'
			)
		);

	// User posted Article entitled X
	//$news = mysqli_query($GLOBALS['db']['link'], "SELECT id, created, headline, subheading, author FROM news WHERE published = 1 AND date > //DATE_SUB(NOW(), INTERVAL $distance) ORDER BY date DESC");
	//while ($row = mysqli_fetch_assoc($news))
	//{
	//	$c = $row['heading'];
	//	if (!$s)
	//	{
	//		$c = strip_tags($c);
	//		$c = strlen($c) > 75 ? substr($c, 0, 72)."..." : $c;
	//	}
	//	if (!$s) $a = strlen($a) > 75 ? substr($a, 0, 72)."..." : $a;
	//	$activities[date("Y-m-d", strtotime($row['date']))][strtotime($row['date'])] = array(
	//		"type"		=> "news",
	//		"actor"		=> $row['author'],
	//		"time"		=> $row['date'],
	//		"data"		=> array(
	//			"title"	=> $row['headline'],
	//			"heading"=> stripslashes($c),
	//			"id"	=> $row['id']				
	//		)
	//	);
	//}

	// User commented on Article
//	$comments = mysqli_query($GLOBALS['db']['link'], "SELECT c.*, n.headline FROM comments c, news n WHERE n.id = c.nid AND n.published = 1 AND c.datetime > DATE_SUB(NOW(), INTERVAL $distance) ORDER BY c.datetime DESC");
//	while ($row = mysqli_fetch_assoc($comments))
//	{
//		$c = strip_tags($row['comment']);
//		$c = strlen($c) > 75 ? substr($c, 0, 72)."..." : $c;
//		$activities[date("Y-m-d", strtotime($row['datetime']))][strtotime($row['datetime'])] = array(
//			"type"		=> "newscomment",
//			"actor"		=> $row['user'],
//			"time"		=> $row['datetime'],
//			"data"		=> array(
//				"title"	=>	$row['headline'],
//				"comment"	=> stripslashes($c),
//				"id"	=> $row['nid']
//			)
//		);
//	}

	// User commented on A vs B
//	$rotwcomments = mysqli_query($GLOBALS['db']['link'], "SELECT rc.*, r.sel1, r.sel2 FROM sqhav_main2.rotwcomments rc, sqhav_main2.rotw r WHERE r.id = rc.id AND rc.datetime > DATE_SUB(NOW(), INTERVAL $distance) ORDER BY rc.datetime DESC");
//	while ($row = mysqli_fetch_assoc($rotwcomments))
//	{
//		$c = $row['comment'];
//		if (!$s)
//		{
//			$c = strip_tags($c);
//			$c = strlen($c) > 75 ? substr($c, 0, 72)."..." : $c;
//		}
//		$activities[date("Y-m-d", strtotime($row['datetime']))][strtotime($row['datetime'])] = array(
//			"type"		=> "rotwcomment",
//			"actor"		=> $row['user'],
//			"time"		=> $row['datetime'],
//			"data"		=> array(
//				"rotw"	=> $row['sel1']." vs ".$row['sel2'],
//				"comment"	=> stripslashes($c),
//				"id"	=> $row['id']
//			)
//		);
//	}

	// User added Game to their collection
	$gamescoll = mysqli_query($GLOBALS['db']['link'], "SELECT gc.*, g.title, u.username, u.gender FROM games_collection gc, games g, users u WHERE g.gid = gc.gid AND datetime > DATE_SUB(NOW(), INTERVAL $distance) AND gc.usrid = u.usrid ORDER BY datetime DESC");
	while ($row = mysqli_fetch_assoc($gamescoll))
	{
		$activities[date("Y-m-d", strtotime($row['datetime']))][strtotime($row['datetime'])] = array(
			"type"		=> "collection-game",
			"actor"		=> $row['username'],
			"time"		=> $row['datetime'],
			"data"		=> array(
				"title"	=> stripslashes($row['title']),
				"id"	=> $row['gid'],
				"url" => $row['title_url'] //'/games/'.specialUrlEncode($row['platform']).'/'.$row['title_url'].'/'
			),
			"gender"	=> $row['gender']
		);
	}

	// User is now playing Game
	$playing = mysqli_query($GLOBALS['db']['link'], "SELECT mg.*, u.* FROM my_games mg, games g, users u WHERE g.gid = mg.gid AND u.usrid = mg.usrid AND play_start > DATE_SUB(NOW(), INTERVAL $distance) ORDER BY play_start DESC");
	while ($row = mysqli_fetch_assoc($playing))
	{
		$activities[date("Y-m-d", strtotime($row['play_start']))][strtotime($row['play_start'])] = array(
			"type"		=> "playing",
			"actor"		=> $row['usrid'],
			"time"		=> $row['play_start'],
			"data"		=> array(
				"title"	=> stripslashes($row['title']),
				"id"	=> $row['gid'],
				"url" => $row['title_url'] //'/games/'.specialUrlEncode($row['platform']).'/'.$row['title_url'].'/'
			)
		);
	}

	// User added Album to their collection
	$albumcoll = mysqli_query($GLOBALS['db']['link'], "SELECT ac.*, a.title, a.subtitle, u.username, u.gender FROM albums_collection ac, albums a, users u WHERE a.albumid = ac.albumid AND ac.datetime > DATE_SUB(NOW(), INTERVAL $distance) AND ac.usrid = u.usrid ORDER BY ac.datetime DESC");
	while ($row = mysqli_fetch_assoc($albumcoll))
	{
		$activities[date("Y-m-d", strtotime($row['datetime']))][strtotime($row['datetime'])] = array(
			"type"		=> "collection-album",
			"actor"		=> $row['username'],
			"time"		=> $row['datetime'],
			"data"		=> array(
				"title"	=> $row['title'] . " " . $row['subtitle'],
				"id"	=> $row['aid']
			),
			"gender"	=> $row['gender']
		);
	}

	// User is now listening to Album
//	$listening = mysqli_query($GLOBALS['db']['link'], "SELECT ul.*, a.title, a.subtitle FROM sqhav_main.user_listening ul, sqhav_main2.album_list a WHERE a.albumid = ul.aid AND ul.started > DATE_SUB(NOW(), INTERVAL $distance) ORDER BY ul.started DESC");
//	while ($row = mysqli_fetch_assoc($listening))
//	{
//		$activities[date("Y-m-d", strtotime($row['started']))][strtotime($row['started'])] = array(
//			"type"		=> "listening",
//			"actor"		=> $row['user'],
//			"time"		=> $row['started'],
//			"data"		=> array(
//				"title"	=> $row['title'] . " " . $row['subtitle'],
//				"id"	=> $row['aid']
//			)
//		);
//	}

	// User wrote a site update
//	$siteupdate = mysqli_query($GLOBALS['db']['link'], "SELECT * FROM siteupdate WHERE date > DATE_SUB(NOW(), INTERVAL $distance) ORDER BY date DESC");
//	while ($row = mysqli_fetch_assoc($siteupdate))
//	{
//		$c = $row['words'];
//		$activities[date("Y-m-d", strtotime($row['date']))][strtotime($row['date'])] = array(
//			"type"		=> "siteupdate",
//			"actor"		=> $row['author'],
//			"time"		=> $row['date'],
//			"data"		=> array(
//				"text"	=> stripslashes($c)
//			)
//		);
//	}

	// Game was released for Platform in Territory
	$gamereleases = mysql_query($GLOBALS['db']['link'], "SELECT gp.release_date, gp.title AS publication, pt.platform AS platformname, games.* FROM games_publications gp, games, games_platforms as pt WHERE gp.release_date >= DATE_SUB(NOW(), INTERVAL $distance) AND gp.release_date <= NOW() AND games.gid=gp.gid");
	while ($row = mysqli_fetch_assoc($gamereleases))
	{
		$activities[date("Y-m-d", strtotime($row['release_date']))][strtotime($row['release_date'])] = array(
			"type"		=> "release-game",
			"time" => $row['release_date'],
			"actor"		=> stripslashes($row['title']),
			"url" => $row['title_url'], //"/games/".specialUrlEncode($row['platform'])."/".$row['title_url']."/",
			"loc"	=> $row['publication'],
			"platform" => $row['platformname'],
			"ext" => $row['ext']
		);
	}

	// Album was released
	$albumreleases = mysqli_query($GLOBALS['db']['link'], "SELECT * FROM albums a WHERE datesort > DATE_SUB(NOW(), INTERVAL $distance) AND datesort <= NOW() ORDER BY datesort DESC");
	while ($row = mysqli_fetch_assoc($albumreleases))
	{
		$activities[date("Y-m-d", strtotime($row['datesort']))][strtotime($row['datesort'])] = array(
			"type"		=> "release-album",
			"actor"		=> $row['title']." ".$row['subtitle'],
			"time"		=> $row['datesort'],
			"data"		=> array(
				"id"	=> $row['id']
			)
		);
	}

	// Profile updates
	$profileupdates = mysqli_query($GLOBALS['db']['link'], "SELECT u.username, ud.last_profile_update AS date, u.gender FROM users u, users_details ud WHERE ud.last_profile_update > DATE_SUB(NOW(), INTERVAL $distance) ORDER BY ud.last_profile_update DESC");
	while ($row = mysqli_fetch_assoc($profileupdates))
	{
		$activities[date("Y-m-d", strtotime($row['date']))][strtotime($row['date'])] = array(
			"type"		=> "profile-update",
			"actor"		=> $row['username'],
			"time"		=> $row['date'],
			"data"		=> array(
			),
			"gender"	=> $row['gender']
		);
	}

	// Forum posts
//	$forumposts = mysqli_query($GLOBALS['db']['link'], "
//		SELECT fp.poster, fp.posted, fp.message, ft.tid, ft.title 
//		FROM `forums_posts` fp, forums_topics ft 
//		WHERE fp.tid = ft.tid AND ft.invisible = 0 AND fp.posted > NOW() - INTERVAL $distance ORDER BY fp.posted DESC");
//	while ($row = mysqli_fetch_assoc($forumposts))
//	{
//		$c = $row['message'];
//		$c = strip_tags($c);
//		$c = strlen($c) > 75 ? substr($c, 0, 72)."..." : $c;
//		$activities[date("Y-m-d", strtotime($row['posted']))][strtotime($row['posted'])] = array(
//			"type"		=> "forum-post",
//			"actor"		=> $row['poster'],
//			"time"		=> $row['posted'],
//			"data"		=> array(
//				"title"	=> stripslashes($row['title']),
//				"msg"	=> stripslashes(stripslashes($c)),
//				"tid"	=> $row['tid']
//			)
//		);
//	}

	// Modified people
	$peoplechanges = mysqli_query($GLOBALS['db']['link'], "SELECT name, modified FROM people WHERE modified > NOW() - INTERVAL $distance ORDER BY modified DESC");
	while ($row = mysqli_fetch_assoc($peoplechanges))
	{
		$activities[date("Y-m-d", strtotime($row['modified']))][strtotime($row['modified'])] = array(
			"type"		=> "person-update",
			"actor"		=> $row['name'],
			"time"		=> $row['modified'],
			"data"		=> array(
			)
		);
	}

	// Modified games
	$gamechanges = mysqli_query($GLOBALS['db']['link'], "SELECT * FROM games WHERE modified > NOW() - INTERVAL $distance ORDER BY modified DESC");
	while ($row = mysqli_fetch_assoc($gamechanges))
	{
		$activities[date("Y-m-d", strtotime($row['modified']))][strtotime($row['modified'])] = array(
			"type"		=> "game-update",
			"actor"		=> $row['title'],
			"time"		=> $row['modified'],
			"data"		=> array(
				"url"	=> $row['title_url'] //specialUrlEncode($row['platform']) . "/" . $row['title_url'],
			)
		);
	}

	// games previews
	$previewchanges = mysqli_query($GLOBALS['db']['link'], "SELECT *, games.title, games.title_url FROM games_previews gp LEFT JOIN games ON (gp.gid=games.gid) WHERE gp.datetime > NOW() - INTERVAL $distance ORDER BY gp.datetime DESC");
	while ($row = mysqli_fetch_assoc($previewchanges))
	{
		$activities[date("Y-m-d", strtotime($row['datetime']))][strtotime($row['datetime'])] = array(
			"type"		=> "preview-update",
			"actor"		=> $row['title'],
			"time"		=> $row['datetime'],
			"data"		=> array(
				"url"	=> $row['title_url'] //specialUrlEncode($row['platform']) . "/" . $row['title_url'] . "/preview/",
			)
		);
	}





	// sort activities within a day
	foreach ($activities as $key => $a)
	{
		krsort($a);
		$activities[$key] = $a;
	}

	// sort activities by day
	krsort($activities);

	return $activities;
}

// Write the stream contained in $latestActivity to the browser
function output_stream($latestActivity){
	foreach ($latestActivity as $date => $group){
		$formattedDate = date("F jS", strtotime($date));
		if ($formattedDate == date("F jS")) echo '<li class="header first">Today</li>';
		elseif ($formattedDate == date("F jS", strtotime("yesterday"))) echo '<li class="header">Yesterday</li>';
		else echo '<li class="header">'.$formattedDate.'</li>';
	$i = 0;
	foreach ($group as $a){

		$i++;

		// show 1 in 10 collection or forum post updates
		// temporary code to reduce collection/forum spam. in future we should determine frequency and adjust accordingly.
		if ($i % 10 != 0 && ($a['type'] == "collection-game" || $a['type'] == "collection-album" || $a['type'] == "forum-post")) continue;

		switch ($a['gender']){
			case "male":
				$his = "his";
				break;
			case "female":
				$his = "her";
				break;
			default:
				$his = "its";
				break;
		}
		$date = '&nbsp;&nbsp;<span class="date">'.date("g:ia", strtotime($a['time']))."</span>";
	?>
	<li class="<?=$a['type']?>">
	<?switch ($a['type']){
		case "rating":?>
			<span class="actor username"><a href="/user/?<?=$a['actor']?>"><?=$a['actor']?></a></span> <span class="action">gave</span> <a href="<?=$a['data']['url']?>" class="target"><?=$a['data']['game']?></a> a <span class="modifier"><?=$a['data']['grade']?></span>.
			<?=$date?>
			<?break;
		case "forecast":?>
			<span class="actor username"><a href="/user/?<?=$a['actor']?>"><?=$a['actor']?></a></span> <span class="action"><?=sprintf($a['data']['forecast'], '<a class="target" href="'.$a['data']['url'].'">' . $a['data']['game'] . '</a>')?></span>.
			<?=$date?>
			<?break;
		case "news":?>
			<span class="actor username"><a href="/user/?<?=$a['actor']?>"><?=$a['actor']?></a></span> <span class="action">wrote an article entitled</span> <a class="target" href="/news/?id=<?=$a['data']['id']?>"><?=$a['data']['title']?></a>.
			<?=$date?>
			<blockquote class="article"><?=$a['data']['heading']?></blockquote>
			<?break;
		case "newscomment":?>
			<span class="actor username"><a href="/user/?<?=$a['actor']?>"><?=$a['actor']?></a></span> <span class="action">commented on</span> <a class="target" href="/news/?id=<?=$a['data']['id']?>"><?=$a['data']['title']?></a>.
			<?=$date?>
			<blockquote class="comment"><?=$a['data']['comment']?></blockquote>
			<?break;
		case "rotwcomment":?>
			<span class="actor username"><a href="/user/?<?=$a['actor']?>"><?=$a['actor']?></a></span> <span class="action">commented on</span> <a class="target" href="/rotw/rotw.php?page=comments&amp;id=<?=$a['data']['id']?>"><?=$a['data']['rotw']?></a>.
			<?=$date?>
			<blockquote class="comment"><?=$a['data']['comment']?></blockquote>
			<?break;
		case "collection-game":?>
			<span class="actor username"><a href="/user/?<?=$a['actor']?>"><?=$a['actor']?></a></span> <span class="action">added</span> <a href="<?=$a['data']['url']?>" class="target"><?=$a['data']['title']?></a> <span class="action">to <?=$his?> collection</span>.
			<?=$date?>
			<?break;
		case "collection-album":?>
			<span class="actor username"><a href="/user/?<?=$a['actor']?>"><?=$a['actor']?></a></span> <span class="action">added</span> <a href="/features/albums/?id=<?=$a['data']['id']?>" class="target"><?=$a['data']['title']?></a> <span class="action">to <?=$his?> collection</span>.
			<?=$date?>
			<?break;
		case "playing":?>
			<span class="actor username"><a href="/user/?<?=$a['actor']?>"><?=$a['actor']?></a></span> <span class="action">is now playing</span> <a href="<?=$a['data']['url']?>" class="target"><?=$a['data']['title']?></a>.
			<?=$date?>
			<?break;
		case "listening":?>
			<span class="actor username"><a href="/user/?<?=$a['actor']?>"><?=$a['actor']?></a></span> <span class="action">is now listening to</span> <a href="/features/albums/?id=<?=$a['data']['id']?>" class="target"><?=$a['data']['title']?></a>.
			<?=$date?>
			<?break;
		case "siteupdate":?>
			<span class="actor username"><a href="/user/?<?=$a['actor']?>"><?=$a['actor']?></a></span> <span class="action">posted a</span> <a href="/" class="target">site update</a>.
			<?=$date?>
			<blockquote class="article"><?=$a['data']['text']?></blockquote>			
			<?break;
		case "release-game":?>
			<span class="actor game"><a href="<?=$a['url']?>"><?=$a['actor']?></a><?=($a['ext'] ? ' ('.$a['ext'].')' : '')?> <span class="action">is out on</span> <?=$a['platform']?> <span class="action">in</span> <?=$a['loc']?>
			<?break;
		case "release-album":?>
			<span class="actor album"><a href="/features/albums/?id=<?=$a['data']['id']?>"><?=$a['actor']?></a></span> <span class="action">is out</span>!
			<?=$date?>
			<?break;
		case "profile-update":?>
			<span class="actor profile"><a href="/user/?<?=$a['actor']?>"><?=$a['actor']?></a></span> <span class="action">updated <?=$his?> profile</span>.
			<?=$date?>
			<?break;
		case "forum-post":?>
			<span class="actor forumpost"><a href="/user/?<?=$a['actor']?>"><?=$a['actor']?></a></span> <span class="action">posted in the forum thread</a> <a href="/forum/?tid=<?=$a['data']['tid']?>"><?=$a['data']['title']?></a>.
			<?=$date?>
			<blockquote class="article"><?=$a['data']['msg']?></blockquote>
			<?break;
		case "person-update":?>
			<span class="actor person"><a href="/people/<?=str_replace(" ", "-", $a['actor'])?>/"><?=$a['actor']?></a>'s page</a> <span class="action">was updated</span>.
			<?=$date?>
			<div class="image"><img src="/people/pictures/<?=str_replace(" ", "-", $a['actor'])?>-tn.png" alt="<?=$a['actor']?>'s picture" /></div>
			<?break;
		case "game-update":?>
			<span class="actor game"><a href="/games/<?=$a['data']['url']?>/"><?=$a['actor']?></a>'s page</a> <span class="action">was updated</span>.
			<?break;
		case "preview-update":?>
			<span class="actor game"><a href="/games/<?=$a['data']['url']?>/"><?=$a['actor']?> preview</a></a> <span class="action">was updated</span>.
			<?break;
		default:
			break;
	}?>
	</li>
	<?
	}
}
}

function GetForecast($rating){
	switch ($rating){
		case 3:
			return "wants to marry %s";
			break;
		case 2:
			return "thinks %s is noteworthy";
			break;
		case 1:
			return "says %s is totally gay";
			break;
		case 0:
			return "believes that %s will be a monumental turd";
			break;
	}
}

?>