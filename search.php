<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
$page = new page();
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.posts.php");
$posts = new posts();
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.forums.php");
$forum = new forum();
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.img.php");

$q = trim($_GET['q']);//die($q);
$q_url = urlencode($q);
$what = trim($_GET['what']);
if(!in_array($what, array("content","posts","albums","forums","groups"))) $what = "all";
$min  = ($_GET['min'] ? $_GET['min'] : '0'); //starting point for db SELECT if $what!='all'
if($what == "all") $min = '0';
$min *= 1;
$max = 20; //maximum rows to fetch from each db on individual searches (ie, not ALL RESULTS page)

$page->title = $q." - Videogam.in search";

$page->width = "fixed";
$page->freestyle.= '
	h1 { display:block; height:1px; overflow:hidden; text-indent:-500px; }
	h2 { margin:30px 0 0; padding:0; border-width:0; font-weight:bold; font-size:30px; text-shadow:1px 1px 0 #AAA; height:1px; overflow:hidden; }
	.searchsubj-all h2 { height:auto; }
	#searchres { margin:0 0 20px; }
	#searchres .label {
		margin:0 2px; padding:4px 9px;
		font-size:14px;
		background-color:white; border-radius:2px; -moz-border-radius:2px; -webkit-border-radius:2px;
		box-shadow:0 0 4px #D0D0D0; -moz-box-shadow:0 0 4px #D0D0D0; -webkit-box-shadow:0 0 4px #D0D0D0;
	}
	
	#searchres .nav { background:url(/bin/img/bg_ccc_50.png);
		height:46px;
		border-radius:4px; -moz-border-radius:4px; -webkit-border-radius:4px;
	}
	#searchres .nav form { display:block; float:left; margin:0; padding:0 10px; line-height:46px; border-right:1px solid #CCC; }
	#searchres .nav ul { margin:0; padding:0 0 0 15px; list-style:none; }
	#searchres .nav li { float:left; margin:0; padding:0; }
	#searchres .nav a { display:block; padding:0 13px; font-size:14px; border-right:1px solid #CCC; line-height:46px; }
	#searchres .nav .on { background:url(/bin/img/textured_bg.jpg); }
	#searchres .nav .on a { font-weight:bold; text-decoration:none; color:#444; }
	
	.searchres > DL > DT { margin:12px 0 0; padding:0; }
	.searchres > DL > DD { margin:3px 0 0; padding:0; font-size:12px; color:#888; }
	.searchres > DL > DD > A { color:#777 !important; }
	.searchres > .more { float:right; font-size:14px; }
	.searchres > .more a {}
	#exactmatch { margin:15px 0; font-size:120%; }
	#exactmatch big { font-weight:bold; font-size:20px; }
	.res-albums { padding:0; }
	.res-albums dl { margin:0 0 10px 160px; padding:0; }
	.res-albums dt { position:relative; margin:0; padding:0; }
	.res-albums dt a { text-decoration:none; }
	.res-albums big b { text-decoration:underline; }
	.res-albums .imgcontainer {
		position:absolute; top:0; left:-160px;
		border-radius:4px; -moz-border-radius:4px; -webkit-border-radius:4px;
		box-shadow:1px 1px 4px #BBB; -moz-box-shadow:1px 1px 4px #BBB; -webkit-box-shadow:1px 1px 4px #BBB;
		background-color:#333 !important;
	}
	.res-albums .imgcontainer img { visibility:hidden; }
	.res-albums dt a:hover .imgcontainer {
		box-shadow:1px 1px 4px #888; -moz-box-shadow:1px 1px 4px #888; -webkit-box-shadow:1px 1px 4px #888;
	}
	#posts { margin:40px 0; }
	.res-posts h2 { display:none; }
	#forum { margin:0 0 30px; }
	.pglabel { margin:10px 0; }
';
$page->header();

$page->openSection(array("id"=>"searchres", "class"=>"searchsubj-".$what));

?>
<h1>Search: <?=$q?></h1>

<div class="nav">
	<form action="/search.php" method="get">
		<input type="text" name="q" value="<?=htmlSC($q)?>" style="width:240px; font-size:130%;"/>
		<input type="submit" value="Search" style="font-size:130%;"/>
		<input type="hidden" name="what" value="<?=$what?>"/>
	</form>
	<ul>
		<li class="<?=($what=="all"?"on":"")?>"><a href="/search.php?what=all&q=<?=urlencode($q)?>">Everything</a></li>
		<li class="<?=($what=="content"?"on":"")?>"><a href="/search.php?what=content&q=<?=urlencode($q)?>">Pages</a></li>
		<li class="<?=($what=="posts"?"on":"")?>"><a href="/#/posts/?query=<?=urlencode($q)?>">News & Blogs</a></li>
		<li class="<?=($what=="albums"?"on":"")?>"><a href="/search.php?what=albums&q=<?=urlencode($q)?>">Albums</a></li>
		<li class="<?=($what=="forums"?"on":"")?>"><a href="/search.php?what=forums&q=<?=urlencode($q)?>">Forums</a></li>
		<li class="<?=($what=="groups"?"on":"")?>"><a href="/search.php?what=groups&q=<?=urlencode($q)?>">Groups</a></li>
		<li><a href="/image/-/term/<?=$q_url?>">Images</a></li>
	</ul>
</div>

<?

if(!$q) $page->kill("<p></p><big>Please input a search term</big>");
//$page->kill($q);

$matches = array();
$num = array();

//pages
if($what=="all" || $what=="content"){
	
	$types['content'] = "Content Pages";
	
	$q_formatted = formatName($q);
	
	/*$query = "SELECT `title`, `description` FROM pages WHERE redirect_to='' AND `title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."' LIMIT 1";
	if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $query))) $matches['content'] = '<fieldset id="exactmatch"><legend>Exact Match</legend><big>'.bb2html('[['.$row['title'].']]</big>'.($row['description'] ? '<div style="margin-top:3px">'.$row['description'].'</div>' : ''), "pages_only").'</fieldset>';
	else $matches['content'] = '<fieldset id="exactmatch"><legend>Create This Page!</legend>There is no page named <i>'.$q.'</i> yet. <b><a href="/pages/edit.php?title='.formatNameURL($q).'" class="arrow-right">Start the <i>'.$q.'</i> page</a></b></fieldset>';*/
	
	$query = "SELECT `title`, `description`, `type`, MATCH (`title`, `keywords`) AGAINST ('".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."') AS `score`
		FROM pages WHERE redirect_to='' AND MATCH (`title`, `keywords`) AGAINST ('".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."')
		ORDER BY `score` DESC";
	if($num['content'] = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query))){
		$res = mysqli_query($GLOBALS['db']['link'], $query.($what!="content" ? " LIMIT 8" : " LIMIT $min, $max"));
		$o = '';
		while($row = mysqli_fetch_assoc($res)) {
			if(strtolower($row['title']) == strtolower($q_formatted)) continue; // we'll output a pglabel below that will capture an exact match
			$o.= '<dt><big>[['.$row['title'].']] ('.ucfirst($row['type']).')</big></dt>'.($row['description'] ? '<dd>'.$row['description'].'</dd>' : '');
		}
		$matches['content'].= '<dl>'.links($o).'</dl>';
	}
}

//sblogs
if($what=="all" || $what=="posts"){
	$types['posts'] = "News & Blogs";
	if($what=="all") $posts->max = 10;
	$posts->query_params['query'] = $q;
	$posts->buildQuery();
	$matches['posts'] = $posts->postsList("open_archived");
}

//Albums
if($what=="all" || $what=="albums"){
	$types['albums'] = "Game Music";
	$query = "SELECT `albumid`, `title`, `subtitle`, `cid`, `release`, MATCH (`title`, `subtitle`, `keywords`) AGAINST ('".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."') AS `score`
		FROM albums WHERE `view` = '1' AND MATCH (`title`, `subtitle`, `keywords`) AGAINST ('".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."') 
		ORDER BY `score` DESC";
	if($num['albums'] = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query))){
		$res = mysqli_query($GLOBALS['db']['link'], $query.($what!="albums" ? " LIMIT 5" : " LIMIT $min, $max"));
		while($row = mysqli_fetch_assoc($res)) {
			$img['src'] = "/music/media/cover/standard/".$row['albumid'].".png";
			if(file_exists($_SERVER['DOCUMENT_ROOT'].$img['src'])){
				list($img['width'], $img['height'], $img['type'], $img['attr']) = getimagesize($_SERVER['DOCUMENT_ROOT'].$img['src']);
			} else {
				unset($img);
			}
			$matches['albums'].= '<dl style="'.($img['height'] ? 'height:'.$img['height'].'px;' : '').'"><dt><a href="/music/?id='.$row['albumid'].'" title="'.htmlSC($row['title'].' '.$row['subtitle']).' game music album overview" class="albumlink">'.($img['src'] ? '<div class="imgcontainer" style="background:url('.$img['src'].') no-repeat 0 0;"><img src="'.$img['src'].'" alt="'.htmlSC($row['title'].' '.$row['subtitle']).'" border="0" width="140"/></div>' : '').'<big>'.$row['title'].($row['subtitle'] ? ' &ndash; <b>'.$row['subtitle'].'</b>' : '').'</big></a></dt><dd>'.$row['cid'].'</dd><dd>'.$row['release'].'</dd></dl>';
		}
	}
}

//forums
while($what=="all" || $what=="forums"){
	
	$types['forums'] = "Forum Discussions";
	$max = $forum->topics_per_page;
	
	//first, search topic titles
	$query = "SELECT tid, MATCH (`title`) AGAINST ('".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."') AS `score` 
		FROM forums_topics WHERE MATCH (`title`) AGAINST ('".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."') AND `invisible` <= '$usrrank';";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$tids[$row['tid']] = $row['score'];
	}
	//next, search post messages
	$query = "SELECT tid, MATCH (`message`) AGAINST ('".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."') AS `score` 
		FROM forums_posts WHERE MATCH (`message`) AGAINST ('".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."');";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$tids[$row['tid']] = $tids[$row['tid']] + $row['score'];
	}
	
	if(!count($tids)) break;
	
	$num['forums'] = count($tids);
	
	arsort($tids);
	$i=0;
	while(list($tid, $score) = each($tids)){
		if(++$i < $min) continue;
		elseif($i > $min + ($what!="forums" ? 5 : $max)) break;
		$matches['forums'][] = $tid;
	}
	
	break;
	
}

//groups
while($what=="all" || $what=="groups"){
	
	break;
	
}

//images
if($what == "all"){
	
	$types['images'] = "Images";
	$moreurl['images'] = "/image/-/term/".$q_url;
	
	$imgs = array();
	
	//get images by tag
	$query = "SELECT DISTINCT(img_name) FROM images_tags LEFT JOIN images USING (img_id) WHERE tag = '".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."' OR `tag` LIKE '".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."|%'";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)) $imgs[] = $row['img_name'];
	
	//get images by description
	$query = "SELECT img_name FROM images WHERE img_title LIKE '%".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."%' OR img_description LIKE '%".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."%'";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)){
		if(!in_array($row['img_name'], $imgs)) $imgs[] = $row['img_name'];
	}
	
	if($num['images'] = count($imgs)){
		$img_max = $num['images'] < 10 ? $num['images'] : 10;
		$gallery = new gallery();
		$gallery->files = array_slice($imgs, 0, $img_max);
		$gallery->size = "sm";
		$matches['images'] = $gallery->htmlEncode();
	}
	
}

if(!count($matches)){
	echo '<big style="display:block;margin:30px 0 0;">No results for <i>'.$q.'</i>.</big>';
} else {
	foreach($matches as $type => $cont){
		?>
		<div class="searchres res-<?=$type?>">
			<?=($what=="all" && $num[$type] > 10 ? '<div class="more label"><a href="'.($moreurl[$type] ? $moreurl[$type] : '/search.php?what='.$type.'&q='.urlencode($q)).'" class="arrow-right">Show all <b>'.$num[$type].'</b> results in <b>'.$types[$type].'</b></a></div>' : '')?>
			<h2><?=$types[$type]?></h2>
			<?
			
			//nav
			if($num[$type] && $what != "all"){
				$pgnum = ceil($num[$type] / $max);
				$pgthis = floor(($min / $max) + 1);
				?>
				<div class="pgnav" style="float:left; background-color:white;">
					<ul>
						<li><b><?=$num[$type]?> Results</b></li>
					</ul>
				</div>
				<?
				$pgnav = '
				<div class="pgnav" style="float:right;">
					<ul>
						'.($pgthis > 1 ? '<li><b><a href="/search.php?what='.$type.'&min='.($min - $max).'&q='.urlencode($q).'" class="arrow-left">Previous</a></b></li>' : '<li class="off"><span><span class="arrow-left">Previous</span></span></li>');
						for($i=1; $i<=$pgnum; $i++){
							if($i==1 || $i==$pgnum || $pgthis == $i) true; //first, last, this pg
							elseif($i > ($pgthis - 3) && $i < ($pgthis + 3)) true; //thispg +- 3
							elseif($i == ($pgthis - 3) || $i == ($pgthis +3)){
								if(($i + 1) < $pgnum){
									$pgnav.= '<li class="hellip off"><span>&hellip;</span></li>';
									continue;
								}
							}
							else continue;
							$pgnav.= '<li class="'.($pgthis == $i ? 'on' : '').'"><a href="/search.php?what='.$type.'&min='.(($i - 1) * $max).'&q='.urlencode($q).'">'.($i == 1 ? 'Page ' : '').$i.'</a></li>';
						}
						$pgnav.= 
						($pgthis != $pgnum ? '<li><b><a href="/search?what='.$type.'&min='.($min + $max).'&q='.urlencode($q).'" class="arrow-right">Next</a></b></li>' : '<li class="off"><span><span class="arrow-right">Next</span></span></li>').'
					</ul>
				</div>
				<br style="clear:both;"/>';
				echo $pgnav;
			}
			
			if($type == "content"){
				echo pglabel($q_formatted);
				echo $cont;
			} elseif($type == "posts"){
				
				?><div id="posts" class="posts"><?=$cont?></div><?
				
			} elseif($type == "forums"){
				
				?>
				<div id="forum">
					<div id="forum-body">
						
						<table border="0" cellpadding="0" cellspacing="0" width="100%" class="topic-list">
						<tr>
							<th width="20">&nbsp;</th>
							<th>Title</th>
							<th>Forum</th>
							<th nowrap="nowrap" style="text-align:center"># Replies</th>
							<th>Last Post</th>
						</tr>
						<?
						foreach($cont as $tid) {
							
							$query = "SELECT * FROM forums_topics WHERE tid='$tid' LIMIT 1";
							$res = mysqli_query($GLOBALS['db']['link'], $query);
							while($row = mysqli_fetch_assoc($res)) {
								
								$row['title'] = stripslashes($row['title']);
								
								if($usrlastlogin < $row['last_post']) $lightbulb = '<img src="/bin/img/mascot.png" alt="new posts" border="0"/>';
								else $lightbulb = '<img src="/bin/img/mascot-off.png" alt="no new posts" border="0"/>';
								
								//get forum
								$query2 = "SELECT title, fid FROM forums WHERE fid='".$row['fid']."' LIMIT 1";
								if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query2))) {
									$p_forum = '<a href="/forums/?fid='.$dat->fid.'">'.$dat->title.'</a>';
								} else $p_forum = "";
								
								echo '
								<tr>
									<td>'.$lightbulb.'</td>
									<td>'.($print_forum ? $print_forum.' / ' : '').'<a href="/forums/?tid='.$row['tid'].'">'.$row['title'].'</a></td>
									<td nowrap="nowrap">'.$p_forum.'</td>
									<td style="text-align:center">'.($forum->numberOfPosts($row['tid']) - 1).'</td>
									<td nowrap="nowrap">'.($row['last_post'] ? timeSince($row['last_post']).' ago by '.outputUser($row['last_post_usrid'], FALSE) : '&nbsp;').'</td>
								</tr>';
							}
						}
						?>
						</table>
						
						<?=($p_pagenav ? '<div class="menu"><ul><li>'.$p_pagenav.'</li></ul></div>' : '')?>
						
					</div>
				</div><!--#forum-->
				<?
				
			} else {
				echo $cont;
			}
			
			echo $pgnav;
			
			?>
		</div>
		<?
	}
}

$page->footer();
?>