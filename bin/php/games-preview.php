<?

$page->title = $gdat->title." preview -- Videogam.in";
$page->header();

?>
<div id="preview">
	<h2><?=$gdat->title?> preview</h2>
	
	<?
	$q = "SELECT * FROM games_previews WHERE gid='".$gdat->gid."' AND `datetime` > 0 ORDER BY `datetime` DESC LIMIT 1";
	if(!$preview = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
		echo "No preview yet published for this game.";
	} else {
		$words = stripslashes($preview->words);
		$words = reformatLinks($words);
		preg_match_all("/\<h3\>(.+)\<\/h3\>/i", $words, $headings);
		if($headings[1]) {
			echo '<div class="submenu"><ul>';
			foreach($headings[1] as $h) {
				echo '<li><a href="#'.$h.'">'.$h.'</a></li>';
			}
			echo "</ul></div>\n\n";
			$words = preg_replace("/\<h3\>(.+)\<\/h3\>/i", "<h3 id=\"$1\">$1</h3>", $words);
		}
		
		//amazon asin
		/*$amzquery = "SELECT * FROM `ASIN` WHERE `id` = '$id' LIMIT 1";
		$amzres = mysqli_query($GLOBALS['db']['link'], $amzquery);
		$amazon = mysqli_fetch_object($amzres);
		if($amazon->asin) {
			$p_amazon = '<p class="commerce-link"><a href="http://www.amazon.com/gp/product/'.$amazon->asin.'?ie=UTF8&tag=squarehaven&linkCode=as2&camp=1789&creative=9325&creativeASIN='.$amazon->asin.'" target="_blank">Preorder <i>'.$dat[title].'</i> from <i class="amazon">Amazon.com</i></a></p>'."\n\n";
		}*/
		
		echo '
		<div id="preview-words">
			'.$words.'
			<br style="clear:both"/>
			'.$p_amazon.'
		</div>
		';
	
		// comments
		require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.forum.php");
		$forum = new forum;
		$forum->associate_tag = "preview:".$gdat->gid;
		if($forum->numberOfPosts()) {
			$depreciate_forum_heading = TRUE;
			$forum->showForum();
		} else {
			?><br/><br/><?
			$suggest[title] = $gdat->title." preview";
			$suggest[type] = "comments";
			$suggest[tags] = $forum->associate_tag . ",game:".$gdat->gid.",forum:1";
			echo $forum->formToCreate($suggest);
		}
		
		//get some stuff for footer
		//contributors
		$query = "SELECT DISTINCT usrid FROM games_previews WHERE gid='".$gdat->gid."'";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)) {
			$preview_contributors[] = outputUser($row['usrid'], FALSE);
		}
		$query = "SELECT DISTINCT contributor FROM games_previews WHERE gid='".$gdat->gid."' AND datetime>0";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)) {
			$row['contributor'] = trim($row['contributor']);
			if($row['contributor'] != "") $preview_contributors[] = $row['contributor'];
		}
		//created date
		$q = "SELECT datetime FROM games_previews WHERE datetime>0 ORDER BY datetime ASC LIMIT 1";
		$x = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
		$preview_created = timeSince($x->datetime);
		//modified date
		$q = "SELECT datetime FROM games_previews WHERE datetime>0 ORDER BY datetime DESC LIMIT 1";
		$x = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
		$preview_modified = timeSince($x->datetime);
		
		?>
		<div id="game-footer" style="margin:15px 0; padding:0; background-image:none;"><div class="container">
			<div class="links">
				<h5><a href="/games/~<?=$gdat->title_url?>"><?=$gdat->title?></a></h5> <span>&middot;</span> 
				<?=($num_news ? '<a href="/games/~'.$gdat->title_url.'/news/">News</a>' : 'News')?> <span>&middot;</span> 
				<?=($num_media ? '<a href="/games/~'.$gdat->title_url.'/media/">Media</a>' : 'Media')?> <span>&middot;</span> 
				<?
				if($game_footer_medias) {
					foreach($game_footer_medias as $x) echo $x.' <span>&middot;</span> '."\n";
				}
				?>
				<a href="/games/~<?=$gdat->title_url?>/preview/">Preview</a> <span>&middot;</span> 
				<?=($has_guide ? '<a href="/games/guides/'.$gdat->title_url.'/">Game Guide</a>' : 'Game Guide')?> <span>&middot;</span> 
				<?=($num_people ? '<a href="/games/~'.$gdat->title_url.'#people">Developers</a>' : 'Developers')?> <span>&middot;</span> 
				<a href="/forums/?tag=gid:<?=$gdat->gid?>">Forum Discussion</a> <span>&middot;</span> 
				<?=($num_fans ? '<a href="/games/~'.$gdat->title_url.'/fans/">Fans</a>' : 'Fans')?> <span>&middot;</span> 
				<?=($num_links ? '<a href="/games/~'.$gdat->title_url.'#links">Links</a>' : 'Links')?> <span>&middot;</span> 
				<?=($num_albums ? '<a href="/games/~'.$gdat->title_url.'#albums">Music</a>' : 'Music')?>
			</div>
			<div style="margin:3px 0;">
				<b>This Page</b> <span>&middot;</span> 
				Created <?=$preview_created?> ago <span>&middot;</span> 
				Last updated <?=$preview_modified?> ago <span>&middot;</span> 
				Viewed <?=addPageView($preview->id, 'gamepreview:'.$gdat->gid, TRUE)?> times
			</div>
			<div>
				<b>Contributors</b> <span>&middot;</span> 
				<?=implode(' <span>&middot;</span> ', $preview_contributors)?> <span>&middot;</span> 
				<a href="javascript:void(0)" class="arrow-right">Contribute something</a>
			</div>
		</div></div>
		<?
		
	}
	
?>
</div>
<?

$page->footer();

?>