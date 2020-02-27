<?
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.img.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.posts.php";

if(!$page->called) $page = new page();

$pglinks = new pglinks();

$row = $this->data;

$title    = $this->title;
$titleurl = formatNameURL($title);
$title_sc = htmlSC($title);

//Stop if there's a malicious robot crawling for a form
if(strstr($title, ".php")) $page->kill("Invalid page name");

//get box art sizes (if game)
$repimgclassname = "lightbox";
if((string)$row->rep_image){
	$repimg = (string)$row->rep_image;
	if(substr($repimg, 0, 4) == "img:"){
		$img_name = substr($repimg, 4);
		$img = new img($img_name);
		$repimg = $img->src['url'];
		$repimgtn = $img->src['sm'];
		$repimgclassname = "imgupl";
	} else {
		$pos = strrpos($repimg, "/");
		$repimgtn = substr($repimg, 0, $pos) . "/" . ($this->type == "person" ? "profile_" : "md_") . substr($repimg, ($pos + 1), -3) . "png";
	}
	if($this->type != "person"){
		$boxattr = getimagesize($_SERVER['DOCUMENT_ROOT'].$repimgtn);
	}
}

//Sblogs
$posts = new posts();
$posts->max = 5;
// catch redirected page data
// ie [[Warcraft III]] page should catch data credited to [[Warcraft III: Reign of Chaos]]
$tags = $this->catchTags();
array_unshift($tags, $title);
foreach($tags as $tag) $posts->query_params['tags'][] = $tag;
$posts->buildQuery();

//Albums
$query = "SELECT * FROM albums_tags LEFT JOIN albums USING (albumid) WHERE tag = '".mysql_real_escape_string($title)."' AND `view` = '1' ORDER BY datesort";
$res_albums = mysql_query($query);
$num_albums = mysql_num_rows($res_albums);

//Images
$query_img = "SELECT img_name FROM images_tags LEFT JOIN images USING (img_id) WHERE (`tag` = '".mysql_real_escape_string($title)."' OR `tag` LIKE '".mysql_real_escape_string($title)."|%') AND img_category_id != '4'";//dont get box art (ctg 4)
$res_img = mysql_query($query_img);
$num_imgs = mysql_num_rows($res_img);

//resize heading to prevent wrapping
if(strlen($title) > 30){
	$fsize = (89 - strlen($title));
	if($fsize < 25) $fsize = 25;
	$h1style = "font-size:".$fsize."px;";
}

if(!$_COOKIE['contentpg']){

?>
<script type="text/javascript">
	function popmsgclose(){
		$("#messageDrLight").fadeOut();
		$.cookie('contentpg', '1', {expires:365, path:'/'});
	}
</script>
<div id="messageDrLight" class="popmsg" style="z-index:9; top:190px; width:580px; margin-left:-290px;">
	<div class="container" style="padding:20px; font-size:14px;">
		<a href="#close" title="hide this message and don't show it to me again" class="ximg preventdefault" style="top:20px; right:20px;" onclick="popmsgclose()">close</a>
		<img src="/bin/img/icons/sprites/drlight.png" alt="Dr. Light" border="0" width="64" height="64" style="float:left; margin:8px 16px 8px 2px;"/>
		<h6 style="margin:0; padding:0; font-size:32px; font-weight:bold; font-family:monospace;">MESSAGE FROM DR. LIGHT</h6>
		<p></p>
		You've gained access to the <i><?=$title?></i> page in the <b>Videogam.incyclop&aelig;dia</b>, a database dedicated to Games, Game Designers, Consoles, Companies, Characters, Concepts, and more. <a href="#readMore" style="color:white; font-weight:bold; padding-right:9px; background:url('/bin/img/arrow-down-point.png') no-repeat right center;" onclick="$(this).hide().next().slideDown()">Learn more</a>
		<div style="display:none">
			<p></p>
			<div style="float:left; width:14px; height:14px; margin:5px 0 0; background:url(/bin/img/pgop_edit.png);"></div>
			<div style="margin:0 0 0 20px;">
				Every content page here can be edited. If you contribute enough information to a page, you may become its revered and adored <b>Patron Saint</b>.
			</div>
			<p></p>
			<div style="float:left; width:14px; height:14px; margin:3px 0 0; background:url(/bin/img/pgop_heart.png);"></div>
			<div style="margin:0 0 0 20px;">
				You can become a fan of any Game, Person, Company, Console, or anything else.
			</div>
			<p></p>
			<div style="float:left; width:14px; height:14px; margin:3px 0 0; background:url(/bin/img/pgop_ghost.png);"></div>
			<div style="margin:0 0 0 20px;">
				You can also become a hater to express a complicated love/hate relationship.
			</div>
			<p></p>
			<div style="float:left; width:14px; height:14px; margin:4px 0 0; background:url(/bin/img/pgop_add.png);"></div>
			<div style="margin:0 0 0 20px;">
				Add any game to your Game Collection and create lists of titles that you own or would like to own or whatever. You can keep inventory of your games, show off your collection to your friends, or impress a member of the opposite sex.
			</div>
		</div>
		<p></p>
		<a href="#close" class="preventdefault" style="font-size:140%; font-family:monospace; padding-right:13px; background:url('/bin/img/cursor_blink.gif') no-repeat right 5px;" onclick="popmsgclose()"><b>&gt; END TRANSMISSION</b></a>
	</div>
</div>
<?

}

if((string)$row->background_image){
	echo '<div id="bodybgimg" class="pgsection" style="left:auto; background-image:url(\''.(string)$row->background_image.'\'); '.$row->background_image['style'].'"></div>';
}

$sec = array("id" => "pghead", "class" => "off pgsection");
$page->openSection($sec);

$bb = new bbcode();
$bb->params['footnotes_noappend'] = true;
$bb->headings_offset = 4;
	
	?>
	<input type="hidden" name="pgid" value="<?=$this->pgid?>" id="pgid"/>
	<input type="hidden" name="pgtitle" value="<?=$title_sc?>" id="pgtitle"/>
	
	<div class="container <?=((string)$row->heading_image ? 'contained' : 'uncontained')?>">
		<h1 title="<?=htmlSC($title)?>" style="<?=$h1style?>"><span id="il-title" class="il"><?=$title?></span></h1>
		<?=((string)$row->description ? '<h2><span id="il-description" class="il">'.$bb->bb2html($row->description).'</span></h2>' : '')?>
	</div>
	
	<?=((string)$row->heading_image ? '<div id="hdimg"><img src="'.(string)$row->heading_image.'" alt="'.$title_sc.'" border="0"/><img src="/bin/img/pagefold_940.png" alt="shadow" border="0" class="pgfsh"/></div>' : '')?>
	
	<?
	$fan=array();
	$in_collection=false;
	if($usrid){
		$query = "SELECT * FROM pages_fan WHERE `title`='".mysql_real_escape_string($this->title)."' AND usrid='$usrid'";
		$res = mysql_query($query);
		while($row_fan = mysql_fetch_assoc($res)){
			$fan[$row_fan['op']] = $row_fan;
		}
		if($this->type == "game" && mysql_num_rows(mysql_query("SELECT * FROM collection WHERE `title` = '".mysql_real_escape_string($title)."' AND usrid = '$usrid' LIMIT 1"))) $in_collection = true;
		$collection_form = '<div id="pgop-form-collection" class="form"><div class="container"></div><a href="#close" class="ximg preventdefault" onclick="$(this).prev().html(\'\').parent().fadeOut()" style="top:8px; right:8px;">close</a></div>';
	}
	foreach(array("love", "hate") as $op){
		$fan_form[$op] = '<div class="form opform fftt"><textarea class="ff">'.$fan[$op]['remarks'].'</textarea><label class="tt">What do you '.$op.' about '.($this->type == "person" ? $title_sc : "this").'? (optional)</label><span class="spacer"></span><b>You '.$op.' this. <a class="unfan" data-op="'.$op.'">Un'.$op.'</a></b><button data-op="'.$op.'">Save</button></div>';
	}
	if($this->type == "game" || $this->type == "person") $subj = " ".$this->type;
	?>
			
	<div id="pgops">
		<ul>
			<li><a title="I love this<?=$subj?>" class="op tooltip <?=($fan['love'] ? 'on' : '')?>" data-op="love"><span>I love this</span></a><?=$fan_form['love']?></li>
			<li><a title="I hate this<?=$subj?>" class="op tooltip <?=($fan['hate'] ? 'on' : '')?>" data-op="hate"><span>I hate this</span></a><?=$fan_form['hate']?></li>
			<?=($this->type == "game" ? '<li><a title="Add to My Play Collection or Wish List" id="pgop-collection" class="op tooltip '.($in_collection ? 'on' : '').'" data-op="collection"><span>Add to My Game Collection</span></a>'.$collection_form.'</li>' : '')?>
			<li><a href="<?=($this->preview ? '#edit' : '/pages/edit.php?title='.$titleurl.'')?>" rel="nofollow" title="Edit this page" class="tooltip<?=($this->preview ? ' on' : '')?>" data-op="edit"><span>Edit this page</span></span></a></li>
			<!--li [fb:like layout="button_count" action="<?=($this->type == "game" ? "recommend" : "like")?>"][/fb:like]/li-->
		</ul>
	</div>
	<?

$page->closeSection();

$sec = array("class" => "pgsection");
$page->openSection($sec);

	?>

	<?=($this->redirected_from ? '<div class="pgnote"><span style="background:url(/bin/img/arrow-down-right.png) no-repeat left center; padding:0 0 0 12px;">Redirected</span> from <a href="/pages/handle.php?title='.formatNameURL($this->redirected_from).'&redirect=no">'.$this->redirected_from.'</a></div><br style="clear:left;"/>' : '')?>
	
	<?
	
	if(!$this->pgid && !$_GET['view_version'] && !$this->preview){
		
		// NOT YET STARTED //
		
		?>
		<div class="c3">
			<div style="margin:0; padding:15px 80px 15px 15px; border:1px solid #45e082; background:#D1F1DF url(/bin/img/promo/ff_story.png) no-repeat bottom right;">
				<big style="font-size:15px; line-height:1.5em;">
					<div style="font-size:17px;">This page hasn't been started yet.</div>
					<ul style="margin:0; padding:0 0 0 5px; list-style:none; color:#666;">
						<li style="margin:4px 0 3px 0; padding:0 0 0 24px; background:url(/bin/img/big_plus.png) no-repeat left center;"><a href="/pages/edit.php?title=<?=$titleurl?>" rel="nofollow">Start the <i><?=$title?></i> page</a> and be credited as the page creator</li>
						<li style="padding:0 0 0 24px; background:url(/bin/img/arrow-big-blue.png) no-repeat 2px 5px;"><a href="/pages/links.php?to=<?=$titleurl?>">View pages that link to <i><?=$title?></i></a></li>
					</ul>
				</big>
			</div>
		</div>
		<?
		
		//Forum topics
		$topicnum = 0;
		$query = "SELECT tid, title, last_post FROM forums_tags LEFT JOIN forums_topics USING (tid) WHERE tag = '".mysql_real_escape_string($title)."' AND invisible <= '$usrrank' ORDER BY last_post DESC LIMIT 6";
		$topicres   = mysql_query($query);
		$num_topics = mysql_num_rows($topicres);
		if($num_topics){
			?>
			<div id="forumtopics" class="c3">
				<h3>Forum Discussions</h3>
				<ul>
					<?
					while($frow = mysql_fetch_assoc($topicres)){
						if(++$topicnum < 6){
							echo '<li class="'.($frow['last_post'] < $usrlastlogin ? 'unread' : 'read').'"><a href="/forums/?tid='.$frow['tid'].'">'.$frow['title'].'</a></li>';
						} else $fmore = '<div class="more"><a href="forums" title="'.htmlSC($title).' forum discussions">All related Forum Discussions</a></div>';
					}
					?>
				</ul>
				<?=$fmore?>
			</div>
			<?
		}
		
	} else {
			
		// STARTED //
		
		?>
		<div class="c1r">
			<?
		
			if($this->type == "game" && $repimgtn){
				
				//game pic
				?>
				<div id="repimg">
					<a href="<?=$repimg?>" title="<?=$title_sc?>" class="<?=$repimgclassname?>" style="width:<?=$boxattr[0]?>px; height:<?=$boxattr[1]?>px; text-indent:<?=$boxattr[0]?>px; background-image:url(<?=$repimgtn?>);"><img src="<?=$repimgtn?>" alt="<?=$title_sc?> box art" border="0" width="<?=$boxattr[0]?>" height="<?=$boxattr[1]?>"/></a>
				</div>
				<?
				
			} elseif($this->type == "person" && $repimgtn){
				
				//person pic
				?>
				<div id="repimg">
					<div class="profpic"><span></span><img src="<?=$repimgtn?>" alt="<?=$title_sc?>"/></div>
				</div>
				<?
				
			} elseif($repimgtn){
				
				//category/topic pic
				?>
				<div id="repimg">
					<a href="<?=$repimg?>" title="<?=$title_sc?>" class="<?=$repimgclassname?>" style="width:<?=$boxattr[0]?>px; height:<?=$boxattr[1]?>px; background-image:url(<?=$repimgtn?>);"><img src="<?=$repimgtn?>" alt="<?=$title_sc?>" border="0" width="<?=$boxattr[0]?>" height="<?=$boxattr[1]?>"/></a>
				</div>
				<?
				
			}
			
			?>

      <nav id="pgcontnav">
        <ul>
          <?
          if($this->type == "game"){
            ?>
            <li id="pgcontnav-synopsis" class="on"><a href="#synopsis" class="preventdefault" onclick="pgcont.toggle('synopsis')">Synopsis</a></li>
            <?=((string)$row->characters || (string)$row->locations ? '<li id="pgcontnav-charslocs"><a href="#charslocs" class="preventdefault" onclick="pgcont.toggle(\'charslocs\')">Characters & Locations</a></li>' : '')?>
            <li id="pgcontnav-gamedata"><a href="#gamedata" class="preventdefault" onclick="pgcont.toggle('gamedata')">Game Data</a></li>
            <li id="pgcontnav-credits"><a href="#credits" class="preventdefault" onclick="pgcont.toggle('credits')">Credits</a></li>
            <?
          } elseif($this->type == "person"){
            ?>
            <li class="on" id="pgcontnav-synopsis"><a href="#synopsis" class="preventdefault" onclick="pgcont.toggle('synopsis')">Biography</a></li>
            <?=($row->credits_list->credit[0] ? '<li><a href="#credits-list">Credits</a></li>' : '')?>
            <?
          } elseif($this->type == "category"){
            ?>
            <li class="on" id="pgcontnav-synopsis"><a href="#synopsis" class="preventdefault" onclick="pgcont.toggle('synopsis')">Category Description</a></li>
            <li><a href="#catpglist">Related Pages</a></li>
            <?
          }
          if($has_forums = mysql_num_rows(mysql_query("SELECT * FROM forums_tags LEFT JOIN forums_topics USING (tid) WHERE tag = '".mysql_real_escape_string($title)."' AND invisible <= '$usrrank' LIMIT 1"))) echo '<li id="pgcontnav-forumtopics"><a href="#forums" class="preventdefault" onclick="pgcont.toggle(\'forumtopics\')">Forum Discussions</a></li>';
          if($posts->num_posts) echo '<li><a href="#/posts/">News & Blogs</a></li>';
          if($num_albums) echo '<li><a href="#albums">Game Music</a></li>';
          if($num_imgs) echo '<li><a href="#imgs">Images</a></li>';
          ?>
        </ul>
      </nav>
			
		</div><!--.c1r (right-aligned column)-->
		
		<div id="pgcont">
			
			<div id="synopsis" class="toggle">
				<?
				
				if($this->redirect_to) $row->content = str_ireplace("#REDIRECT [[".$this->redirect_to."]]", '<p><span style="padding:0 0 0 25px; font-size:120%; color:#666; background:url(/bin/img/arrow-down-right.png) no-repeat 10px 4px;">This page is assigned to redirect to <b>[['.$this->redirect_to.']]</b></span></p>', $row->content);
				
				if((string)$row->content){
					// .Incyclopedia article //
					?>
					<div id="content" class="editme">
						<?
						echo $bb->bb2html((string)$row->content);
						?>
					</div>
					<?
				} elseif($this->pgid){
					?>
					<div style="padding:30px 0 15px 92px; border-bottom:1px solid #CCC; font-size:15px; line-height:1.5em; background:url(/bin/img/promo/scorpion.png) no-repeat left bottom;">
						<b>Finish it!</b> This page doesn't have a synopsis yet.<br/><a href="/pages/edit.php?title=<?=$titleurl?>">Write an article about <?=$title?></a>
					</div>
					<?
				}
				
				// Categories
				if($row->categories->category[0]){
					
					foreach($row->categories->category as $catg) {
						if($catg['ancestor'] == "parent") $parent_category = (string)$catg;
						$o_syn.= '<li data-ancestor="'.$catg['ancestor'].'">'.(string)$catg.'</li>';
					}
						
					$o_syn = '<div id="categories" class="categories editme">'."\n".'<h1>Categories'.($this->type != "person" ? ' & Concepts' : '').'</h1>'."\n".'<ul>'.$o_syn.'</ul></div>';
					
					$o_syn = $bb->bb2html($o_syn);
					//$o_syn = str_replace('</ul>', '<li class="clear"></li></ul>', $o_syn);
					echo $o_syn;
					
					$file = $_SERVER['DOCUMENT_ROOT']."/pages/xml/categorytrees/".formatNameURL($title, 1).".xml";
					if($this->type == "category" && file_exists($file)){
						$xmld = file_get_contents($file);
						if($tree = @simplexml_load_string($xmld)) echo '<div id="categorytree">'.links($tree->asXML()).'</div><div class="clear"></div>';
					}
					
				}
				
				//echo '<pre>';print_r($bb);
				echo $bb->outputFootnotes(true);
				
				unset($bb);
				
				if($row->twitter_id && substr((string)$row->twitter_id, 0, 1) == "@"){
					$twitter_id = substr((string)$row->twitter_id, 1);
					?>
					<br style="clear:both"/>
					<h5><a href="https://twitter.com/#!/<?=$twitter_id?>">@<?=$twitter_id?></a></h5>
					<a href="https://twitter.com/<?=$twitter_id?>" class="twitter-follow-button" data-show-count="true" data-lang="en" data-show-screen-name="false"></a>
					<?
				}
				
				// Game images
				if($this->type == "game"){
					$imgs = array();
					foreach(array("img_titlescreen", "img_gameplay_1", "img_gameplay_2", "img_gameplay_3", "img_gameover", "_video_trailer") as $img){
						if($img_name = (string)$row->{$img}) $imgs[] = $img_name;
					}
					if(count($imgs)){
						//Check the title screen ratio
						$_i = new img($imgs[0]);
						$ratio = $_i->img_width / $_i->img_height;
						$_gallery = new gallery();
						$_gallery->files = $imgs;
						$_gallery->size = $ratio > 1.3 ? "ss" : "";
						$_gallery->width = $ratio > 1.3 ? 140 : '';
						$_gallery->show = 4;
						$_gallery->parse();
						echo $_gallery->HTMLencode();
					}
				}
				
				?>
			</div><!-- #synopsis -->
			<?
			
			if($this->type == "game"){
				
				?>
				<!-- Game Data -->
				<div id="gamedata" class="toggle editme" style="display:none;">
					<h3>Game Data</h3>
					<dl>
						<dt>Genre<?=($row->genres->genre[1] ? 's' : '')?></dt>
						<?
						if(!(string)$row->genres->genre[0]) echo '<dd>?</dd>';
						else {
							$o_genre = '';
							foreach($row->genres->genre as $genre){
								$o_genre.= '<dd>'.$genre.'</dd>';
							}
							echo $pglinks->parse($o_genre);
						}
						?>
						
						<dt>Developer<?=($row->developers->developer[1] ? 's' : '')?></dt>
						<?
						if(!(string)$row->developers->developer[0]) echo '<dd>?</dd>';
						else {
							$o_dev = '';
							foreach($row->developers->developer as $dev){
								$o_dev.= '<dd>'.$dev.'</dd>';
							}
							echo $pglinks->parse($o_dev);
						}
						
						if((string)$row->series->game_series[0]){
							?>
							<dt>Game Series</dt>
							<?
							$o_series = '';
							foreach($row->series->game_series as $series) $o_series.= '<dd>'.$series.'</dd>';
							echo $pglinks->parse($o_series);
						}
						
						//publications
						$publications = array();
						$publishers = array();
						$publication_images = array("img_name" => "box art", "img_name_title_screen" => "title screen", "img_name_logo" => "logo/icon");
						
						if($row->publications && $publications = $row->publications->children()){
							?>
							<dt>Publications</dt>
							<?
							
							foreach($publications as $pub){
								
								$pub_i++;
								
								$oTitle = htmlSC($pub->title);
								
								$release = $pub->release_tentative ? $pub->release_tentative : $pub->release_year."-".$pub->release_month."-".$pub->release_day;
								$release = str_replace("-00", "-??", $release);
								$o_pf = (string)$pub->platform . ((string)$pub->media_distribution && $pub->distribution == "digital" ? ' ('.(string)$pub->media_distribution.')' : '');
								if((string)$pub->publisher) $publishers[] = (string)$pub->publisher;
								
								?>
								<dd class="publication">
									<ul>
										<li class="title"><?=$pub->title?></li>
										<li class="release" title="<?=$pub->region?>" style="background:url('/bin/img/flags/<?=$GLOBALS['pf_regions'][(string)$pub->region]?>.png') no-repeat left center;"><?=$release?></li>
										<li class="platform"><?=$pglinks->parse($o_pf)?></li>
									</ul>
									<?
									foreach($publication_images as $img_field => $img_description){
										if((string)$pub->{$img_field}){
											$img = new img($pub->{$img_field});
											$alt = $img->img_title ? htmlsc($img->img_title) : $img->img_name;
											echo '<figure><a href="'.$img->src['url'].'" title="'.$alt.'" rel="pub'.$pub_i.'" class="imgupl" data-imgname="'.$img->img_name.'"><img src="'.$img->src['tn'].'" alt="'.$alt.'"/></a><figcaption>'.$img_description.'</figcaption></figure>';
										}
									}
									?>
									<div class="clear"></div>
								</dd>
								<?
								
							}
						}
						
						//publishers
						$publishers = array_unique($publishers);
						if($num_publishers = count($publishers)){
							$o_publishers = implode('</dd><dd>', $publishers);
							$o_publishers = $pglinks->parse($o_publishers);
							?>
							<dt>Publisher<?=($num_publishers > 1 ? 's' : '')?></dt>
							<dd><?=$o_publishers?></dd>
							<?
						}
						
						//official desc
						if((string)$row->official_description){
							?>
							<dt>Official Description</dt>
							<dd><?=nl2br((string)$row->official_description)?></dd>
							<?
						}
						
						//tagline
						if((string)$row->tagline){
							?>
							<dt>Tagline</dt>
							<dd><?=(string)$row->tagline?></dd>
							<?
						}
						
						//online
						if($row->online && $online = $row->online->children()){
							?>
							<dt>Online</dt>
							<?
							$dd='';
							foreach($row->online->children() as $network) $dd.= '<dd>'.$network.'</dd>';
							echo $pglinks->parse($dd);
						}
						?>
					</dl>
				</div><!-- #typedata -->
				
				<!-- game credits -->
				<div id="credits" class="toggle editme" style="display:none;">
					<h3>Credits</h3>
					<?
					if((string)$row->credits){
						$bb = new bbcode();
						$bb->headings_offset = 4;
						$bb->text = (string)$row->credits;
						echo str_replace('</dl>', '</dl><div class="clear"></div>', $bb->bb2html());
					} else echo 'There are no credits listed yet for this game. <a href="/pages/edit.php?title='.$titleurl.'#?field=credits" rel="nofollow" style="text-decoration:none"><b style="font-size:14px">+</b> <u>Add Credits</u></a>';
					?>
				</div>
				
				<!-- characters & locations -->
				<?
				if((string)$row->characters || (string)$row->locations){
					
					?>
					<div id="charslocs" class="toggle" style="display:none">
						<?
						$cl_output = '';
						$bb = new bbcode();
						$bb->headings_offset = 4;
						$bb->params['footnotes_noappend'] = true;
						foreach(array("characters", "locations") as $field){
							$child_name = substr($field, 0, -1);
							$o = '';
							if($row->{$field}['inputtype'] == "open" && (string)$row->{$field}){
								$o = (string)$row->{$field};
								$bb->text = $o;
								$o = $bb->bb2html();
							} elseif($row->{$field}->{$child_name}[0]){
								$o = '<ul>';
								foreach($row->{$field}->children() as $ch) $o.= '<li>'.(string)$ch.'</li>';
								$o.= '</ul>';
								$bb->text = $o;
								$o = $bb->bb2html();
							}
							if($o){
								echo '<h5>'.ucwords($field).'</h5><div id="'.$field.'">'.$o.'</div>';
							}
						}
						echo $bb->outputFootnotes(true);
					?>
					</div>
				<?
				}
			}
			
			if($has_forums){
				// forum topics
				?>
				<div id="forumtopics" class="toggle" style="display:none;">
					<h3>Forum Discussions</h3>
					<ul>
						<?
						$topicnum = 0;
						$query = "SELECT tid, title, last_post FROM forums_tags LEFT JOIN forums_topics USING (tid) WHERE tag = '".mysql_real_escape_string($title)."' AND invisible <= '$usrrank' ORDER BY last_post DESC";
						$res = mysql_query($query);
						while($frow = mysql_fetch_assoc($res)){
							if(++$topicnum < 20){
								echo '<li class="'.($frow['last_post'] < $usrlastlogin ? 'unread' : 'read').'"><a href="/forums/?tid='.$frow['tid'].'">'.$frow['title'].'</a></li>';
							} else $fmore = '<div class="more"><a href="forums" title="'.htmlSC($title).' forum discussions">All related Forum Discussions</a></div>';
						}
						?>
					</ul>
					<?=$fmore?>
				</div>
				<?
			}
			
			?>
		</div><!-- #pgcont -->
		<div class="clear"></div>
		<?		
	}

$page->closeSection();


// pages in this category //

$query = "SELECT DISTINCT(`title`), `type`, `subcategory` 
	FROM pages_links LEFT JOIN pages ON (from_pgid = pgid) 
	WHERE `to` = '".mysql_real_escape_string($title)."' AND `namespace` = 'Category' AND redirect_to = '';";
$res   = mysql_query($query);
$cat_pgs = array('game' => array(), 'person' => array(), 'category' => array(), 'topic' => array());
$cat_list = array();
while($catrow = mysql_fetch_assoc($res)){
	$index = ($catrow['subcategory'] ? $catrow['subcategory'] : $catrow['type']);
	$cat_pgs[$index][] = $catrow['title'];
	$cat_list[] = $catrow['title'];
}

//also get child pages of category pages that redirect here
$query = "SELECT DISTINCT(`title`) FROM pages_links LEFT JOIN pages ON (from_pgid = pgid) WHERE `to` = '".mysql_real_escape_string($title)."' AND is_redirect = '1'";
$res   = mysql_query($query);
while($catrow = mysql_fetch_assoc($res)){
	if(!in_array($catrow['title'], $cat_list)){
		$query2 = "
			SELECT DISTINCT(`title`), `type` 
			FROM pages_links LEFT JOIN pages ON (from_pgid = pgid) 
			WHERE `to` = '".mysql_real_escape_string($catrow['title'])."' AND `namespace` = 'Category' AND redirect_to = '';";
		$res2 = mysql_query($query2);
		while($catrow = mysql_fetch_assoc($res2)) {
			$index = ($catrow['subcategory'] ? $catrow['subcategory'] : $catrow['type']);
			$cat_pgs[$index][] = $catrow['title'];
			$cat_list[] = $catrow['title'];
		}
	}
}

if($num_cat_pgs = count($cat_list)){
	
	$sec = array("id" => "catpglist", "class" => "pgsection");
	$page->openSection($sec);
	
		?>
		<h3>Related Pages</h3>
		<?
		if(!$num_cat_pgs) echo "There are no pages catagorized as '".$title."'.";
		else {
			
			$cat_pgs_other = array_merge($cat_pgs['category'], $cat_pgs['topic']);
			
			if(count($cat_pgs_other)) {
				?>		
				<ul>
					<?
					sort($cat_pgs_other);
					foreach($cat_pgs_other as $p) {
						echo '<li><a href="'.pageURL($p).'">'.$p.'</a></li>';
					}
					?>
				</ul>
				<?
			}
			
			if(count($cat_pgs['game'])){
				?>
				<h4>Games</h4>
				<ul>
					<?
					$outp = "";
					sort($cat_pgs['game']);
					foreach($cat_pgs['game'] as $game) {
						$outp.= '<li>[['.$game.']]</li>'."\n";
					}
					echo $pglinks->parse($outp);
					?>
				</ul>
				<?
			}
			
			if(count($cat_pgs['person'])){
				?>
				<h4>People</h4>
				<ul>
					<?
					$outp = "";
					sort($cat_pgs['person']);
					foreach($cat_pgs['person'] as $person) {
						$outp.= '<li>[['.$person.']]</li>'."\n";
					}
					echo $pglinks->parse($outp);
					?>
				</ul>
				<?
			}
			
			foreach($GLOBALS['pgsubcategories'] as $subcat => $o){
				if($cat_pgs[$subcat]){
					?>
					<h4><?=ucwords($o)?></h4>
					<ul>
						<?
						$outp = "";
						sort($cat_pgs[$subcat]);
						foreach($cat_pgs[$subcat] as $item) {
							$outp.= '<li>[['.$item.']]</li>'."\n";
						}
						echo $pglinks->parse($outp);
						?>
					</ul>
					<?
				}
			}
		}
		
	$page->closeSection();
	
}

// PERSON'S CREDITS //
if($this->type == "person" && $row->credits_list->credit[0]){
	
	$sec = array("id" => "credits-list", "class" => "pgsection");
	$page->openSection($sec);
	
		?>
		<h3>Credits &nbsp;<a href="/pages/edit.php?title=<?=$titleurl?>#?field=credits_list" rel="nofollow" title="Add Credits" class="tooltip addcredit" style="text-decoration:none;" onmouseover="$(this).children('span').show()" onmouseout="$(this).children('span').hide()"><b>+</b><span style="display:none; font-size:14px; font-weight:normal;"> Add Credits</span></a></h3>
		<?
		
		$cr_sort = array();
		$crid = 0;
		
		$pglinks = new pglinks();
		$crStr = $row->credits_list->asXML();
		$crArr = $pglinks->extractFrom($crStr);
		$i = 0;
		
		foreach($crArr as $cr){
			
			$key = $cr['namespace'] == "AlbumID" ? "a" : "g";
			
			$dat = array();
			if($key == "g"){
				$game = new pg($cr['tag'], true);
				$dat = $game->row['index_data'];
				$cr['original'] = $game->link;
			} elseif($key == "a"){
				$q = "SELECT datesort, cid, publisher FROM albums WHERE `albumid` = '".mysql_real_escape_string($cr['tag'])."' LIMIT 1";
				$dat = mysql_fetch_assoc(mysql_query($q));
				$dat['first_release'] = $dat['datesort'];
			}
			
			$p_release = substr($dat['release'], 5);
			$p_release = str_replace("-", "/", $p_release);
			$o = '
				<dd>
					<dl id="il-cr-'.++$crid.'" class="il">
						<dt class="title '.($key == "a" ? 'album' : 'game').'">'.$cr['original'].'</dt>'.
						($dat['platform'] ? '<dd>[['.$dat['platform'].']]</dd>' : '').
						($dat['developers'][0] ? '<dd>[['.$dat['developers'][0].']]</dd>' : '').
						($dat['cid'] ? '<dd>'.$dat['cid'].'</dd>' : '').
						($dat['publisher'] ? '<dd>'.$dat['publisher'].'</dd>' : '').
						'
					</dl>
				</dd>
			';
			
			// Sort by release date
			// put unknown releases (IE pages not yet created) at the end by giving them 9999-99-99 release dates
			$rel = ($dat['first_release'] ? $dat['first_release'] : '9999-99-99') . $i;
			
			$cr_sort[$rel] = $o;
			
			$i++;
			
		}
		
		if($cr_sort){
			
			?>
			<div class="container">
				<?
				
				if($cr_sort){
					$o = '';
					ksort($cr_sort);
					foreach($cr_sort as $date => $outp){
						$i = substr($date, 0, 4);
						$cr_sort2[$i].= $outp;
					}
					foreach($cr_sort2 as $year => $outp){
						$o.= '<dl><dt>'.($year == "9999" ? '&nbsp;' : $year).'</dt>'.$outp.'</dl>';
					}
					$bb = new bbcode();
					$bb->text = $o;
					$bb->params['inline_citations'] = true;
					$bb->params['links_rm_duplicates'] = true;
					echo $bb->bb2html();
				}
				
				?>
			</div>
			<br style="clear:both;"/>
			<?
		
		}
	
	$page->closeSection();
	
} // credits


// BOX ART //

if($this->type == "game" && $row->publications->publication[0]){
	
	require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.shelf.php";
	
	$num_pubs = 0;
	$pubs = array();
	$publications = array();
	if($row->publications) $publications = $row->publications->children();
	foreach($publications as $pub){
		
		if(!$pub->img_name || (string)$pub->img_name == '') continue; // Don't show it if there's no box art
		
		$p = array();
		
		$shelf = new shelfItem();
		$shelf->type = "game";
		$shelf->img = (string)$pub->img_name;
		
		if($shelf->img->img_category_id != 4) continue; //if the image isnt categorized as box art, skip it
		
		$p['href']     = $shelf->img->src['url'];
		$p['platform'] = (string)$pub->platform;
		$p['title']    = (string)$pub->title;
		$p['region']   = (string)$pub->region;
		$p['release_year']  = (string)$pub->release_year;
		$p['release_month'] = (string)$pub->release_month;
		$p['release_day']   = (string)$pub->release_day;
		$num_pubs++;
		
		$o_boxes.= $shelf->outputItem($p);
	
	}
	
	if($num_pubs){
		
		$sec = array("class" => "shelf horizontal gameshelf pgsection", "id" => "boxart");
		$page->openSection($sec);
		
		?>
		<h3>Box Art</h3>
		<?
		
		if($num_pubs > 5){
			?>
			<a href="/js.htm" title="Traverse left" class="trav prev" onclick="shelf.traverse($(this).parent(), -1, 6); return false;"></a>
			<a href="/js.htm" title="Traverse right" class="trav next" onclick="shelf.traverse($(this).parent(), 1, 6); return false;"></a>
			<?
		}
		
		?>
		<div class="shelf-container" style="width:<?=($num_pubs * 198 + 800)?>px;"><?=$o_boxes?></div>
		<?
		
		$page->closeSection();
		
	}
}


// SBLOGS //
if($posts->num_posts){
	
	$sec = array("class" => "posts pgsection");
	$page->openSection($sec, true);
		
		?>
		<h3>News & Blogs</h3>
		<div id="posts" class="posts"><?=$posts->postsList("open_archived")?></div>
		<?
	
	$page->closeSection();
	
}

//Albums
if($num_albums){
	
	$sec = array("id" => "albums", "class" => "pgsection");
	$page->openSection($sec);
		
		?>
		<h3><?=$title?> Game Music Soundtracks</h3>
		<ul class="c2r">
			<?
			$i = 0;
			while($arow = mysql_fetch_assoc($res_albums)){
				$tn['src'] = "/music/media/cover/standard/".$arow['albumid'].".png";
				if(file_exists($_SERVER['DOCUMENT_ROOT'].$tn['src'])){
					list($tn['width'], $tn['height'], $tn['type'], $tn['attr']) = getimagesize($_SERVER['DOCUMENT_ROOT'].$tn['src']);
				} else {
					unset($tn);
				}
				?>
				<?=(++$i == 6 ? '<li><a href="#showalbums" class="preventdefault arrow-toggle" style="display:block;" onclick="$(this).parent().slideUp().siblings().slideDown();">Show all related Game Music</a></li>' : '')?>
				<li style="<?=($i > 5 ? 'display:none;' : '')?>">
					<dl style="<?=($tn['height'] ? 'height:'.$tn['height'].'px;' : '')?>">
						<dt>
							<a href="/music/?id=<?=$arow['albumid']?>" title="<?=htmlSC($arow['title'].' '.$arow['subtitle'])?> game music album overview" class="albumlink">
								<?=($tn['src'] ? '<div class="imgcontainer" style="background:url('.$tn['src'].') no-repeat 0 0;"><img src="'.$tn['src'].'" alt="'.htmlSC($arow['title'].' '.$arow['subtitle']).'" border="0" width="140"/></div>' : '')?>
								<big><?=$arow['title'].($arow['subtitle'] ? ' &ndash; <b>'.$arow['subtitle'].'</b>' : '')?></big>
							</a>
						</dt>
						<dd><?=$arow['cid']?></dd>
						<dd><?=$arow['release']?></dd>
					</dl>
				</li>
				<?
			}
			?>
		</ul>
		<?
	
	$page->closeSection();
	
}


/*
	
	//Forum topics
	$topicnum = 0;
	$query = "SELECT tid, title, last_post FROM forums_tags LEFT JOIN forums_topics USING (tid) WHERE tag = '".mysql_real_escape_string($title)."' AND invisible <= '$usrrank' ORDER BY last_post DESC LIMIT 6";
	$topicres   = mysql_query($query);
	$num_topics = mysql_num_rows($topicres);
	if($num_topics){
		?>
		<div id="forumtopics" style="">
			<h3>Forum Discussions</h3>
			<ul>
				<?
				while($frow = mysql_fetch_assoc($topicres)){
					if(++$topicnum < 6){
						echo '<li class="'.($frow['last_post'] < $usrlastlogin ? 'unread' : 'read').'"><a href="/forums/?tid='.$frow['tid'].'">'.$frow['title'].'</a></li>';
					} else $fmore = '<div class="more"><a href="forums" title="'.htmlSC($title).' forum discussions" class="arrow-right">More related Forum Discussions</a></div>';
				}
				?>
			</ul>
			<?=$fmore?>
		</div>
		<?
	}*/
	
	/*if(count($row['related_games']['game'])) {
		?>
		<div id="related" style="">
			<h3>Related Games</h3>
			<ul>
				<?
				$row['related_games'] = makeAssoc($row['related_games']);
				foreach($row['related_games']['game'] as $game) {
					echo '<li>'.bb2html($game, "pages_only").'</li>';
				}
				?>
			</ul>
		</div>
		<?
	}*/


// PICS //
if($num_imgs){
	
	$sec = array("id" => "imgs", "class" => "pgsection");
	$page->openSection($sec);
	
		?>
		<h3><a href="/image/-/tag/<?=$titleurl?>" title="<?=$title_sc?> images"><b><?=$num_imgs?> Image<?=($num_imgs != 1 ? 's' : '')?></b></a> tagged <i><?=$title?></i></h3>
		<div class="upllink"><a href="/upload.php?autotag=<?=$titleurl?>" title="Upload images"><b>+</b><span> Upload</span></a></div>
		<br style="clear:both;" />
		<?
		
		$imgs = array();
		$query_img.= " AND img_width > 349 ORDER BY RAND() LIMIT 5";
		$res = mysql_query($query_img);
		while($img = mysql_fetch_assoc($res)){
			$imgs[] = $img;
		}
		$outp = '';
		$i = 0;
		?>
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td valign="top" class="imglead">
					<div>
						<?
						$img = new img($imgs[0]['img_name']);
						$img->output('op');
						?>
					</div>
				</td>
				<?
				if($imgs[1]){
					?><td class="imgfollow">
						<div>
							<?
							$img = new img($imgs[1]['img_name']);
							$img->output("sm");
							if(!empty($imgs[2])){
								$img = new img($imgs[2]['img_name']);
								$img->output("sm");
							}
							?>
						</div>
					</td>
					<?
				}
				if($imgs[3]){
					?><td class="imgfollow">
						<div>
							<?
							$img = new img($imgs[3]['img_name']);
							$img->output("sm");
							if(!empty($imgs[4])){
								$img = new img($imgs[4]['img_name']);
								$img->output("sm");
							}
							?>
						</div>
					</td>
					<?
				}
				?>
			</tr>
		</table>
	<?
	
	$page->closeSection();
	
}

// facebook activity //
/*$page->openSection();
?><fb:activity actions="videogamin:love"></fb:activity><?
$page->closeSection();*/

?>

<!--<br/><div class="hr" style="width:600px;"></div>
<fb:comments numposts="10" width="600"></fb:comments>-->
<!--<div id="fb-root"></div>
<script>
  window.fbAsyncInit = function() {
    FB.init({appId: 'your app id', status: true, cookie: true,
             xfbml: true});
  };
  (function() {
    var e = document.createElement('script'); e.async = true;
    e.src = document.location.protocol +
      '//connect.facebook.net/en_US/all.js';
    document.getElementById('fb-root').appendChild(e);
  }());
</script>-->
<?

if(strstr($outp_params, "include_footer")){
	
	$sec = array("id" => "pgft", "class" => "pgsection");
	$page->openSection($sec);
		
		?>
		<div id="pgft-thispage">
			<?
			if($this->pgid){?>
			<span>Created <?=timeSince($this->row['created'])?> ago by <?=outputUser($this->row['creator'], false)?></span> | 
			<span>Edited <?=timeSince($this->row['modified'])?> ago</span> | 
			<? } else { ?>
			<span style="color:#D92626;">Not yet started</span> | 
			<? } ?>
			<span><?=addPageView($title, $this->pgid)?> views</span>
			
			<br/>
			
			<a href="/pages/edit.php?title=<?=$titleurl?>" rel="nofollow"><?=($this->pgid ? 'Edit this page' : 'Start this page')?></a> | 
			<a href="/pages/history.php?title=<?=$titleurl?>">History</a> | 
			<a href="/pages/links.php?to=<?=$titleurl?>">Links</a> | 
			<a href="#">Discussion</a> | 
			<?
			if($usrid) $is_watching = mysql_num_rows(mysql_query("SELECT * FROM pages_watch WHERE `title`='".mysql_real_escape_string($this->title)."' AND usrid='$usrid' LIMIT 1"));
			?>
			
			<span id="watchpages">
				<input type="checkbox" name="watch" value="<?=htmlsc($this->title)?>" <?=($is_watching ? 'checked' : '')?> class="fauxcheckbox watchpage" id="watchpage"/>
				<label for="watchpage" class="tooltip" title="Closely monitor pages you're watching">Watch</label>
			</span>
			
			<br/>
			
			<span>The original work here is licensed under a <a href="http://creativecommons.org/licenses/by-nc-sa/3.0/">Creative Commons A-NC-SA 3.0 License</a></span>
			
		</div>
		<div id="pagecontr">
			<?
			if(is_string($this->row['contributors'])) $contributors = json_decode($this->row['contributors']);
			else $contributors = array();
			if(!count($contributors)) {
				echo '<div id="patronsaint"><a href="#" class="preventdefault">N/A</a><img src="/bin/img/avatars/sm/unknown.png" border="0" alt="N/A"/></dt><dd class="patronsaint">Contribute to this page and your mug can be in this spot.</div>';
			} else {
				$others = array();
				$i = 0;
				foreach($contributors as $c){
					$c_user = new user($c);
					if(++$i == 1){
						?>
						<div id="patronsaint">
							<a href="<?=$c_user->url?>"><?=$c_user->avatar("big")?><b><?=$c_user->username?></b></a>
							<span>is the Patron Saint of this page</span>
						</div>
						<?
					} elseif($i <= 3) $others[] = '<a href="'.$c_user->url.'"><b>'.$c_user->username.'</b></a>';
					elseif($i < 7) $others[] = '<a href="'.$c_user->url.'">'.$c_user->username.'</a>';
					else $contrmore = TRUE;
				}
				if(count($others)) echo '<div class="others">Other contributors: <span>'.implode("</span>, <span>", $others).'</span>'.($contrmore ? ', <a href="/pages/history.php?pgid='.$this->pgid.'" class="arrow-right">more</a>' : '').'</div>';
			}
			?>
		</dl>
		<div class="clear"></div>
		<?
		
	$page->closeSection();
	
}

$page->closeSection();

if($this->row['modifier'] == $usrid){
	
	//if the page was just edited by this user, notify him about his contribution
	
	$sincemod = time() - strtotime($this->row['modified']);
	if($sincemod < 10 && count($contributors)){
		$crev = array_flip($contributors);
		if(!in_array($usrid, $contributors)) $cpos = 3;
		else $cpos = $crev[$usrid];
		if($cpos > 3) $cpos = 3;
		
		?>
		<div id="pgednotice" onclick="$(this).fadeOut();">
			<img src="/bin/img/promo/pit_<?=$cpos?>.png" alt="thank you!"/>
			<div>
				<big>Thank you, <?=$usrname?></big>
				<p></p>
				<?=($cpos == 0 ? "Because of your great efforts and affinity for <i>".$title."</i>, you are this page's <b>Patron Saint</b>, the user who has contributed the most to this subject. Hooray, you!" : "Your contributions to the <i>".$title."</i> page are greatly appreciated. With a bit more contributions here, you might even become its <b>Patron Saint</b>!<p></p>Every time you add to or update this page or when you create a Sblog (News, Blog, or Media) post about <i>".$title."</i>, your contribution score for this topic increases based on the quality, quantity, and size of your contribution.<p></p>Keep up the great work!")?>
				<?=(!$is_watching ? '<p></p>Make sure you add this page to your <b>Watch List</b> so you can keep track of changes other users make.<p></p><span class="chbox watchpage"><span class="inp"></span>Watch this Page</span>' : '')?>
				<p></p>
				<a href="javascript:void(0);" style="font-size:120%;">Close this message</a>
			</div>
		</div>
		<?
	
	}
	
}

if(!$this->pgid && $_SERVER['HTTP_REFERER'] != "http://videogam.in/content/Special:most_requested"){
	// track this page view
	$q = "INSERT INTO pagecount_requestfail (`title`,`url`,`referrer`) VALUES ('".mysql_real_escape_string($title)."', '".$_SERVER['REQUEST_URI']."', '".$_SERVER['HTTP_REFERER']."')";
	mysql_query($q);
}

// Twitter feeds & whatnot
if($row->twitter_id && substr((string)$row->twitter_id, 0, 1) == "@"){
	?>
	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
	<!--<script type="text/javascript" src="http://twitter.com/javascripts/blogger.js"></script>
	<script type="text/javascript" src="http://twitter.com/statuses/user_timeline/<?=$twitter_id?>.json?callback=twitterCallback2&amp;count=5"></script>-->
	<?
}