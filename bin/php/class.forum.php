<?
//Navigate:
// INDEX
// SHOW CATEGORY
// NEW POSTS
// SHOW FORUM
// SHOW TOPIC
// TOPIC LIST
// output post

require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/bbcode.php");
require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/htmltoolbox.php");
$page->css[] = "/bin/css/forums.css";
$page->css[] = "/bin/css/posts.css";
$page->javascripts[] = "/bin/script/runonload.js";
$page->javascripts[] = "/bin/script/forum.js";
$page->javascripts[] = "/bin/script/posts.js";
if($_GET['focus_post']) $page->javascripts[] = "/bin/script/scrollto.js";

//if($usrid != "Matt" && $usrid != "Rahul") $GLOBALS['closed'] = "The forums are temporarily closed for mainenance.";
	
//get user prefs (like javascript switch)
if($usrid) {
	$q = "SELECT * FROM users_prefs WHERE usrid='$usrid' LIMIT 1";
	$udat = mysql_fetch_object(mysql_query($q));
}

class forum {
	
	var $posts_per_page = 40;
	var $topics_per_page = 20;
	var $invis_groups = array(
		'nobody',
		'',
		'registered user',
		'',
		'',
		'V.I.P.',
		'non-staff moderator',
		'low-level admin',
		'mid-level admin',
		'high-level admin');
	var $fpost = "";
	var $manual_close = ""; //a value between 1 and 9, being a userrank to limit participation
	
	function getThisLocation() {
		$loc = $_SERVER["REQUEST_URI"];
		if(!preg_match("/\/$|\.[a-z]{3,4}$/i", $loc)) $loc.= "/"; //add trailing slash
		return $loc;
	}
	
	function showIndex() {
		global $db, $usrid, $usrrank, $usrlastlogin, $admns, $datetime;
		
		///////////
		// INDEX //
		///////////
		
		if($GLOBALS['closed']) die($GLOBALS['closed']);
		
		$query = "SELECT * FROM forums_posts";
		$res = mysql_query($query);
		$dat->posts = mysql_num_rows($res);
		
		$query = "SELECT * FROM forums_topics";
		$res = mysql_query($query);
		$dat->topics = mysql_num_rows($res);
		
		//new post topics
		if($usrid) {
			$query = "SELECT * FROM forums_topics WHERE last_post > '$usrlastlogin'";
			$res = mysql_query($query);
			$num_new = mysql_num_rows($res);
		}
		
		?>
		<div id="forum" class="forum-index">
			
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td valign="top">
						
						<big class="heading2"><h2>The Videogam.in <i>Message Forums of DEATH!!!</i></h2> is a place to discuss videogames. So far, <b><?=number_format(mysql_num_rows(mysql_query("SELECT DISTINCT(usrid) FROM forums_posts")))?></b> people have posted <b><?=number_format($dat->posts)?></b> messages in <b><?=number_format($dat->topics)?></b> topics.</big>
			
						<ul class="index-nav">
							<?
							if($usrid) {
								?>
								<li><a href="new-posts/"><b><?=($num_new ? $num_new : 'No ')?> Unread Topic<?=($num_new != 1 ? 's' : '')?></b> since <acronym title="<?=timeSince($usrlastlogin)?> ago">your last visit</acronym></a></li>
								<?
							}
							?>
							<li><a href="top-rated/"><b>Top-rated discussion threads</b></a></li>
							<li><a href="tags/">Tag cloud</a></li>
							<?
							if($usrrank >= 5) {
								?>
								<li>
									<select onchange="window.location=this.options[this.selectedIndex].value">
										<option value="">Admin...</option>
										<option value="/forums/action.php?new_forum=1">make new forum</option>
										<option value="/forums/action.php?do=manage_categories">manage categories</option>
										<option value="/forums/action.php?do=manage_tags">manage tags</option>
										<option value="/forums/action.php?do=update_posts">update counts</option>
									</select>
								</li>
								<?
							}
							?>
						</ul>
						
						<form action="search.php" method="get">
							<fieldset class="search-forums">
								<legend>Search the Forums</legend>
								<table border="0" cellpadding="0" cellspacing="5" width="100%">
									<tr>
										<td colspan="2" style="font-size:12px; color:#666;">
											<label><input type="radio" name="source" value="tp" checked="checked"/>Search topic titles and posts</label> &nbsp; 
											<label style="white-space:nowrap"><input type="radio" name="source" value="t"/>Search only topic titles</label>
										</td>
									</tr>
									<tr>
										<td width="100%">
											<div style="margin-right:6px"><input type="text" name="query" style="width:100%;"/></div>
										</td>
										<td>
											<input type="submit" value="Search"/>
										</td>
									</tr>
								</table>
							</fieldset>
						</form>
						
						<h3>What <acronym title="term used very loosely" style="border-bottom:1px dotted #CCC;">people</acronym> have been talking about lately:</h3>
						<div style="line-height:25px">
							<?
							$query = "SELECT tag FROM forums_tags LEFT JOIN forums_posts USING (tid) 
							WHERE posted > DATE_ADD(CURDATE(), INTERVAL -7 DAY) AND tag != 'General Gaming' AND tag != 'Congenialtalia' AND tag != 'Society' AND tag != 'The Shit Pit' AND tag != 'News'";
							$res = mysql_query($query);
							if($topicnum = mysql_num_rows($res)) {
								while($row = mysql_fetch_assoc($res)) {
									$tags[$row['tag']]++;
								}
								//randomize
								$aux = array();
								$keys = array_keys($tags);
								shuffle($keys);
								foreach($keys as $key) {
									$aux[$key] = $tags[$key];
									unset($tags[$key]);
					    	}
					    	$tags = $aux;
					    	
								$mean = array_sum($tags) / count($tags);
								while(list($tag, $num) = each($tags)) {
									unset($tagwords);
									$fontsize = 7 + ($num / 17 * $mean);
									if($fontsize > 25) $fontsize = 25;
									$tagword = $this->tagWord($tag);
									if($tagword) echo '<a href="/forums/?tag='.urlencode($tag).'" class="forum-tag" style="font-size:'.$fontsize.'pt" title="'.$num.' post'.($num != 1 ? 's' : '').'">'.$tagword.'</a>'."&nbsp;\n";
								}
							} else {
								echo "No topics discussed during this timeframe. ";
							}
							?>
						</div>
						
						<h3>
							Top Posters &nbsp; 
							<a href="#" class="arrow-toggle arrow-toggle-on preventdefault" onclick="if( $(this).hasClass('arrow-toggle-on') ) return; $(this).addClass('arrow-toggle-on').siblings('a').removeClass('arrow-toggle-on'); $('ol.posters').toggle();">This Week</a> &nbsp; 
							<a href="#" class="arrow-toggle preventdefault" onclick="if( $(this).hasClass('arrow-toggle-on') ) return; $(this).addClass('arrow-toggle-on').siblings('a').removeClass('arrow-toggle-on'); $('ol.posters').toggle();">All Time</a>
						</h3>
						<ol class="posters">
							<?
							$query = "SELECT usrid, COUNT(usrid) AS postnum FROM forums_posts WHERE DATE_SUB(CURDATE(),INTERVAL 7 DAY) <= posted GROUP BY usrid ORDER BY postnum DESC LIMIT 5";
							$res   = mysql_query($query);
							while($row = mysql_fetch_assoc($res)) {
								echo '<li style="margin:4px 0;">'.outputUser($row['usrid']).' &nbsp; '.$row['postnum'].' <span style="color:#777;">posts</span></li>';
							}
							?>
						</ol>
						<ol class="posters" style="display:none">
							<?
							$query = "SELECT usrid, COUNT(usrid) AS postnum FROM forums_posts GROUP BY usrid ORDER BY postnum DESC LIMIT 5";
							$res   = mysql_query($query);
							while($row = mysql_fetch_assoc($res)) {
								echo '<li style="margin:4px 0;">'.outputUser($row['usrid']).' &nbsp; '.$row['postnum'].' <span style="color:#777;">posts</span></li>';
							}
							?>
						</ol>
						
					</td>
					<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
					<td valign="top" width="60%">
						
						<table border="0" cellpadding="0" cellspacing="0" width="100%" class="forum-index-list">
						<?
						
						$query2 = "SELECT * FROM `forums_categories` ORDER BY `sort`";
						$res2 = mysql_query($query2);
						while($c = mysql_fetch_assoc($res2)) {
							$c['category'] = stripslashes($c['category']);
							$c['description'] = stripslashes($c['description']);
							?>
							<tr class="category-row">
								<td>&nbsp;</td>
								<td colspan="4"><?=$c['category'].($c['description'] ? '<p>'.$c['description'].'</p>' : '')?></td>
							</tr>
							<?
							
								$query = "SELECT * FROM `forums` WHERE `cid` = '$c[cid]' AND `invisible` < '$usrrank'";
								$res = mysql_query($query);
								while($row = mysql_fetch_assoc($res)) {
									
									$last = $this->getLastForumInfo($row['fid']);
									
									if($usrlastlogin < $last['post']) {
										$lightbulb = '<img src="/bin/img/mascot.png" alt="new posts" border="0"/>';
									} else {
										$lightbulb = '<img src="/bin/img/mascot-off.png" alt="no new posts" border="0"/>';
									}
									echo '
										<tr>
											<td>'.$lightbulb.'</td>
											<td><a href="?fid='.$row[fid].'" class="forum-name">'.$row[title].'</a>'.($row[description] ? '<div class="forum-desc">'.stripslashes($row[description]).'</div>' : '').'</td>
											<td nowrap="nowrap" style="font-size:16px; color:#999; padding-left:15px;">
												<span style="color:black">'.mysql_num_rows(mysql_query("SELECT * FROM forums_tags WHERE tag='".$row['included_tags']."'")).'</span> topics
											</td>
										</tr>';
								}
						}
							?>
						</table>
						
					</td>
				</tr>
			</table>
			
		</div>
		<?
	}
	
	function showCategory($category) {
		global $db, $usrid, $admns;
		
		///////////////////
		// SHOW CATEGORY //
		///////////////////
		
		$query = "SELECT * FROM `forums_categories` WHERE cidd` = '$category' LIMIT 1";
		if(!$cat = mysql_fetch_object(mysql_query($query))) {
			echo '<div class="forum-note">No category called "'.$category.'" found.</div>';
		} else {
			
			$uval = $this->getUserValue($usrid);
			$last_login = $this->getLastLogin();
			
			$query = "SELECT SUM(topics) as topics, SUM(posts) as posts FROM `forums` WHERE cid = '$cat->cid' AND `no_index` != 1";
			$res = mysql_query($query);
			$dat = mysql_fetch_object($res);
			
			?>
			<div id="forum">
			<h2><a href="/forums/">Forums</a> <span>/</span> <?=$cat->category?></h2>
			<div id="forum-body">
				
				<div class="menu">
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td id="posts-cell" class="on"><h3><?=number_format($dat->posts)?> posts in <?=$dat->topics?> topics</h3></td>
					<td class="last-cell" width="100%">&nbsp;</td>
				</tr>
				</table>
				</div>
				
				<table border="0" cellpadding="0" cellspacing="0" width="100%" class="forum-index-list">
				<tr>
					<th>&nbsp;</th>
					<th>Title</th>
					<th nowrap="nowrap">&nbsp; Topics &nbsp;</th>
					<th nowrap="nowrap">&nbsp; Posts &nbsp;&nbsp;&nbsp;</th>
					<th>Last Post</th>
				</tr>
				
				<tr class="category-row"><td>&nbsp;</td><td colspan="4"><?=$cat->category.($cat->description ? "<p>".stripslashes($cat->description)."</p>" : '')?></td></tr>
				<?
					$query = "SELECT * FROM `forums` WHERE `cid` = '$cat->cid' AND `invisible` < '$uval' ORDER BY `title` ASC";
					$res = mysql_query($query);
					while($row = mysql_fetch_assoc($res)) {
						
						if($last_login < $row[last_post])
							$lightbulb = '<img src="/bin/img/mascot.png" alt="new posts" border="0"/>';
						else
							$lightbulb = '<img src="/bin/img/mascot-off.png" alt="no new posts" border="0"/>';
						
						if($row[invisible])
							$acronym = array('<acronym title="invisible to '.$this->invis_groups[$row[invisible]].' and below">', '</acronym>');
						else unset($acronym);
						
						echo '<tr><td>'.$lightbulb.'</td>';
						echo '<td>'.$acronym[0].'<a href=?fid='.$row[fid].'" class="forum-name">'.$row[title].'</a>'.$acronym[1].($row[description] ? "<br/>$row[description]" : '').'</td>';
						echo '<td style="text-align:center;">'.$row[topics].'</td><td style="text-align:center;">'.$row[posts]."</td>";
						echo '<td nowrap="nowrap">'.($row[last_post] ? $this->timeSince($row[last_post],1).' ago by <a href="/user/?'.$row[last_post_author].'">'.$row[last_post_author].'</a>' : '&nbsp;')."</td></tr>\n";
					}
					
				?>
				</table>
				
			</div>
			</div>
			<?
			
		}
	}
	
	function showNewPosts($since='') {
		global $db, $usrid, $usrrank, $usrlastlogin, $admns, $usergroupd;
		
		///////////////
		// NEW POSTS //
		//////////////;
			
		$uval = $usrrank;
		
		if($usrid && !$since) $since = 'last-login';
		elseif(!$usrid && !$since) $since = 1;
		
		$last_login = $usrlastlogin;
		
		if($since == 'last-login') {
			$time_interval = "'$last_login'";
			$words = "Showing new posts since your last login ".$this->timeSince($last_login)." ago";
		} elseif(is_numeric($since)) {
			$time_interval = 'DATE_ADD(CURDATE(), INTERVAL -'.$since.' DAY)';
			$words = "Showing new posts in the past $since day";
			if($since != 1) $words.= "s";
		} else {
			echo "Error: There is an illegal value input for time since last posts ($since).";
			$page->footer();
			exit;
		}
		$query = "SELECT * FROM forums_topics as t WHERE `last_post` > $time_interval AND `invisible` <= '$uval' ORDER BY `last_post` DESC";	
		$new_topic_num = mysql_num_rows(mysql_query($query));
		
		//page navigation
		if($new_topic_num > $this->topics_per_page) {
			if(!$pg = $_GET['pg']) $pg = 1;
			$pgs = ceil($new_topic_num / $this->topics_per_page);
			$pgmin = ($this->topics_per_page * $pg) - $this->topics_per_page;
			$query.= " LIMIT $pgmin, $this->topics_per_page";
			$didnt_show = 0;
			for($i = 1; $i <= $pgs; $i++) {
				$show = FALSE;
				if($i > ($pg - 5) && $i < ($pg + 5)) $show = TRUE;
				if($i == 1 || $i == $pgs) $show = TRUE;
				if($show) {
					if($i == 1) $class = ' first';
					else $class = "";
					$p_pagenav.= ($pg == $i ? '<td class="on'.$class.'">'.$i : '<td class="off'.$class.'"><a href="?since='.$since.'&pg='.$i.'">'.$i.'</a>')."</td>";
					$didnt_show = 0;
				} elseif(!$didnt_show) {
					$p_pagenav.= '<td class="lapse">&bull;&bull;&bull;</td>';
					$didnt_show++;
				}
			}
			$p_pagenav = '
			<table border="0" cellpadding="0" cellspacing="0" class="pagenav">
				<tr>
					<th>Page</th>
					'.$p_pagenav.'
				</tr>
			</table>';
		}
		
		?>
		<div id="forum">
			
		<h2><a href="/forums/">Forums</a> <span>/</span> new posts</h2>
		
		<div id="description"><?=$words?>
			<div style="margin-top:5px">
				<select onchange="window.location='/forums/new-posts/?since='+this.options[this.selectedIndex].value">
					<option value="">Show new posts...</option>
					<?=($usrid ? '<option value="last-login">since your last login</option>' : '')?>
					<option value="1">in the past 24 hours</option>
					<option value="2">in the past 48 hours</option>
					<option value="7">in the past week</option>
					<option value="14">in the past 2 weeks</option>
					<option value="30">in the past month</option>
				</select>
			</div>
		</div>
		
		<div id="forum-body">
			
			<div class="menu">
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td id="posts-cell" class="on"><h3><?=$new_topic_num?> topic<?=($new_topic_num != 1 ? 's' : '')?> with new posts</h3></td>
						<td class="plaintext" width="100%" style="border-right-width:0"><?=$p_pagenav?></td>
					</tr>
				</table>
			</div>
			
			<table border="0" cellpadding="0" cellspacing="0" width="100%" class="forum-index-list">
			<tr>
				<th width="20">&nbsp;</th>
				<th>Title</th>
				<th>Forum</th>
				<th nowrap="nowrap" width="60" style="text-align:center"># Replies</th>
				<th>Last Post</th>
			</tr>
			<?
				$res = mysql_query($query);
				while($row = mysql_fetch_assoc($res)) {
					
					$row['title'] = stripslashes($row['title']);
					
					if($last_login < $row['last_post']) {
						$lightbulb = '<img src="/bin/img/mascot.png" alt="new posts" border="0"/>';
					} else {
						$lightbulb = '<img src="/bin/img/mascot-off.png" alt="no new posts" border="0"/>';
					}
					
					//get forum
					$q = "SELECT title, fid FROM forums_tags 
						LEFT JOIN forums ON (tag = included_tags) 
						WHERE tid='".$row['tid']."' AND tag LIKE 'forum:%' LIMIT 1";
					if($dat = mysql_fetch_object(mysql_query($q))) {
						$p_forum = '<a href="/forums/?fid='.$dat->fid.'">'.$dat->title.'</a>';
					} else $p_forum = "";
					
					$print_forum = "";
					if($forum = $this->topic2forum($row)) {
						if($forum->title) $print_forum = stripslashes($forum->title);
						if($forum->fid) $print_forum = '<a href="/forums/?fid='.$forum->fid.'">'.$print_forum.'</a>';
					}
					
					echo '
					<tr>
						<td>'.$lightbulb.'</td>
						<td>'.($print_forum ? $print_forum.' / ' : '').'<a href="/forums/?tid='.$row['tid'].'&focus_post=unread">'.$row['title'].'</a></td>
						<td>'.$p_forum.'</td>
						<td style="text-align:center">'.($this->numberOfPosts($row['tid']) - 1).'</td>
						<td nowrap="nowrap">'.($row['last_post'] ? $this->timeSince($row['last_post']).' ago by '.outputUser($row['last_post_usrid'], FALSE) : '&nbsp;').'</td>
					</tr>';
				}
				
			?>
			</table>
			<?=($p_pagenav ? '<div style="padding:10px">'.$p_pagenav.'</div>' : '')?>
		</div>
		</div>
		<?
	}
	
	function showForum($fid='') {
		global $db, $usrid, $usrrank, $admns, $depreciate_forum_heading;
		
		////////////////
		// SHOW FORUM //
		////////////////
		
		//Show a forum with a specified FID or $this->associate_tag
		
		if($GLOBALS['closed']) die($GLOBALS['closed']);
		
		$this_loc = $this->getThisLocation();
		$uval = $this->getUserValue($usrid);
		$last_login = $this->getLastLogin();
		
		$query = "SELECT * FROM `forums` where `fid` = '$fid' LIMIT 1";
		if($fid && !$forum = mysql_fetch_object(mysql_query($query))) {
			echo "Couldn't get forum data for FID #".$fid;
		} else {
			
			if(!$forum) {
				
				//get some stuff in place of FID data
				$forum->invisible = 0;
				$forum->closed = 0;
				
				if($this->special_forum) {
					$forum->title = ucwords($this->special_forum);
				}
				
				if($x = explode(":", $this->associate_tag)) {
					$aux_category_tags = array();
					if($x[0] == "gid") {
						$aux_category_tags[] = "forum:1";
						$q = "SELECT title, title_url, text FROM games 
							LEFT JOIN wiki ON (subject_field='gid' AND subject_id=games.gid) 
							WHERE games.gid='$x[1]' ORDER BY wiki.datetime DESC LIMIT 1";
						$dat = mysql_fetch_object(mysql_query($q));
						$forum->title = '<a href="/forums/tags/">Tags</a> <span>/</span> '.$dat->title;
						$dat->text = bb2html($dat->text);
						$dat->text = reformatLinks($dat->text);
						$dat->text = strip_tags($dat->text, '<a>');
						$forum->description = $dat->text.' <a href="/games/~'.$dat->title_url.'" class="arrow-right">'.$dat->title.'</a>';
					} elseif($x[0] == "group") {
						$q = "SELECT name, name_url, about FROM groups WHERE group_id='$x[1]' LIMIT 1";
						$dat = mysql_fetch_object(mysql_query($q));
						$forum->title = '<a href="/forums/tags/">Tags</a> <span>/</span> '.$dat->name;
						$text = bb2html($dat->about);
						$text = reformatLinks($text);
						$text = strip_tags($text, '<a>');
						$forum->description = $text.' <a href="/groups/~'.$dat->name_url.'" class="arrow-right">'.$dat->name.'</a>';
					} elseif($x[0] == "news") {
						$aux_category_tags[] = "forum:8";
					} elseif($x[0] == "forum") {
						$q = "SELECT title FROM forums WHERE included_tags='forum:$x[1]' LIMIT 1";
						if($dat = mysql_fetch_object(mysql_query($q))) {
							$forum->title = '<a href="/forums/tags/">Tags</a> <span>/</span> '.$dat->title;
						}
					}
				}
				
			}
			
			if(!$forum->title && $this->associate_tag) {
				$forum->title = '<a href="/forums/tags/">Tags</a> <span>/</span> '.$this->associate_tag;
			}
			
			//get topic #
			if($this->special_forum == "top-rated") {
				$special_forum_query = "SELECT *, (rating / ratings) AS total_rating FROM forums_topics WHERE ratings > 1 ORDER BY total_rating DESC, ratings DESC LIMIT 20";
				$topic_num = mysql_num_rows(mysql_query($special_forum_query));
			} else {
				$topic_num = $this->numberOfTopics($fid);
			}
			
			//show topic instead of topic list?
			if($this->associate_tag && !strstr($this->associate_tag, "gid:") && $topic_num == 1) {
				$q = "SELECT tid, `type` FROM forums_tags LEFT JOIN forums_topics USING (tid) WHERE tag='".$this->associate_tag."' AND invisible <= '$uval' LIMIT 1";
				$dat = mysql_fetch_object(mysql_query($q));
				if($dat->type == "comments") {
					$this->showTopic($dat->tid);
					return;
				}
			}
			
			//user has access?
			if($uval >= $forum->invisible) {
				
				$forum->title = stripslashes($forum->title);
				
				?>
				<div id="forum">
				<h2<?=($depreciate_forum_heading ? ' style="display:none;"' : '')?>>
					<a href="/forums/">Forums</a> <span>/</span> <?=$forum->title?>
				</h2>
				<?=($forum->description ? '<div id="description">'.stripslashes($forum->description).'</div>' : "")?>
				<div id="forum-body">
				<?
				
				$navtree = $this->makeNavTree();
				
				if(!$usrid) $post_access = 'no-login';
				elseif($uval < $forum->closed) $post_access = 'closed-notice';
				else $post_access = 'forum-form';
				
				//page navigation
				if($topic_num > $this->topics_per_page) {
					if(!$pg = $_GET['pg']) $pg = 1;
					$pgs = ceil($topic_num / $this->topics_per_page);
					$pgmin = ($this->topics_per_page * $pg) - $this->topics_per_page;
					$query.= " LIMIT $pgmin, $this->topics_per_page";
					$didnt_show = 0;
					for($i = 1; $i <= $pgs; $i++) {
						$show = FALSE;
						if($i > ($pg - 3) && $i < ($pg + 3)) $show = TRUE;
						if($i <= 2 || $i > ($pgs - 2)) $show = TRUE;
						if($show) {
							if($i == 1) $class = ' first';
							else $class = "";
							$p_pagenav.= ($pg == $i ? '<th class="on'.$class.'">'.$i.'</th>' : '<td class="off'.$class.'"><a href="/forums/?'.($this->associate_tag ? 'tag='.urlencode($this->associate_tag) : 'fid='.$fid).'&pg='.$i.'">'.$i.'</a></td>');
							$didnt_show = 0;
						} elseif(!$didnt_show) {
							$p_pagenav.= '<th class="lapse">&middot;&middot;&middot;</th>';
							$didnt_show++;
						}
					}
					$p_pagenav = '
					<table border="0" cellpadding="0" cellspacing="0" class="pagenav">
						<tr>
							<th style="background-color:transparent">Page</th>
							'.$p_pagenav.'
						</tr>
					</table>';
				}
				
				?>
				
				<div class="menu">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td id="posts-cell" class="on"><h3><?=($topic_num ? $topic_num : 'No')?> Topic<?=($topic_num == 1 ? '' : 's')?></h3></td>
							<?=(!$this->special_forum ? '<td id="reply-cell"><a href="javascript:void(0)" onclick="$(\'#'.$post_access.'\').slideToggle(); $(this).parent().toggleClass(\'on\').prev().toggleClass(\'on\');">new topic</a></td>' : '')?>
							<?=($p_pagenav ? '<td class="plaintext" style="padding:0">'.$p_pagenav.'</td>' : '')?>
							<?
							if($fid && $usrrank >= 5) {
								echo ('<td class="plaintext"><select onchange="window.location=this.options[this.selectedIndex].value">
								<option value="">Admin...</option>
								<option value="/forums/action.php?do=Edit+Forum+Details&fid='.$fid.'">forum details</option>
								<option value="/forums/action.php?do=close&fid='.$fid.'">close forum</option>
								'.($usrrank >= 8 ? '<option value="/forums/action.php?do=hide&fid='.$fid.'">hide forum</option><option value="/forums/action.php?do=delete&fid='.$fid.'">delete forum</option>' : '').
								'</select></td>');
							}
							if($this->associate_tag && $usrrank >= 5) {
								echo '<td><a href="/forums/action.php?do=manage_tags&edit_tag='.$this->associate_tag.'"><span style="color:black">Admin:</span> <span style="text-decoration:underline">manage this tag</span></a></td>';
							}
							?>
							<td class="last-cell" width="100%"><?=$this->linkBack($forum->location,$forum->title,$forum->title_url,$forum->corresponding_table,$forum->corresponding_id)?></td>
						</tr>
					</table>
				</div>
				
				<div id="no-login" style="display:none;">Please <a href="/login.php">log in</a> to participate in the discussion.</div>
				
				<div id="closed-notice" style="display:none;">Sorry, this forum is locked; no new topics can be made.</div>
				
				<form action="/forums/action.php" method="post" id="forum-form" style="display:none;padding:1em;" onsubmit="return requiredA('<?=$usrid?>');">
					<input type="hidden" name="fid" value="<?=$fid?>"/>
					<input type="hidden" name="tags[]" value="<?=($fid ? $forum->included_tags : $this->associate_tag)?>"/>
					<?
					if($aux_category_tags) {
						foreach($aux_category_tags as $t) {
							echo '<input type="hidden" name="tags[]" value="'.$t.'"/>';
						}
					}
					?>
					
					<div style="margin-bottom:15px; padding:0 0 15px 65px; font-size:14px; line-height:22px; background:url(/bin/img/icons/lightbulb_m.png) no-repeat 0 0; border-bottom:1px solid #DDD;">
						<b style="font-size:15px;">Sharing a link, picture, video, or news article?</b><br/>
						<a href="/posts/manage.php?action=newpost">Post it to the Videogam.in Sblog (News Blog) instead!</a> It has advanced posting tools and 
						people can discuss your post just like in the forums.
					</div>
						
					<label id="inp-title">
						<b>Topic title:</b>&nbsp;&nbsp;
						<input type="text" name="title" size="95" maxlength="120" id="ftitle" tabindex="1"/>
					</label>
					<p>
						No HTML allowed; BB Code is currently in use (see <a class="arrow-link" href="javascript:void(0)" onclick="window.open('/bbcode.htm','markup_guide_window','width=620,height=510,scrollbars=yes');">BB Code syntax guide</a>).
						<?=($usrrank >= 6 ? '<br/>Note: since you\'re an administrator, HTML will not be stripped from your post! However, BB Code is preferred over HTML.' : '')?>
					</p>
					<p><?=outputToolbox("fmessage", array("b","i","big","small","strikethrough","img","a","ul","li","spoiler","blockquote","autotag","emoticon"), "use bb code")?></p>
					<div style="margin-right:12px"><p>
						<textarea name="message" id="fmessage" rows="10" tabindex="2" style="width:100%;" onchange="confirm_exit=true;"></textarea>
					</p></div>
					<div id="reply-opts">
						<ul class="tabbed-nav">
							<li class="on"><a href="#" rel="RO-opts">Options</a></li>
							<li><a href="#" rel="RO-preview" onclick="$(this).html('Refresh Preview');">Preview</a></li>
							<li><a href="#" rel="RO-upload">Upload a Picture</a></li>
							<li><a href="#" rel="RO-attach">Attach a File</a></li>
							<li><a href="#" rel="RO-poll">Create a Poll</a></li>
						</ul><br style="clear:both"/>
						<div id="RO-opts" class="opt">
							<label><input type="checkbox" name="add_reply_mail" value="1" id="reply-mail"/> Subscribe: e-mail me whenever someone posts to this thread</label>
						</div>
						<div id="RO-preview" class="opt message-text" style="display:none"></div>
						<div id="RO-upload" class="opt" style="display:none">
							<iframe src="/forums/action.php?do=upload" name="upload-img" frameborder="0" style="width:800px; height:69px;"></iframe>
						</div>
						<div id="RO-attach" class="opt" style="display:none">coming soon (maybe)</div>
						<div id="RO-poll" class="opt" style="display:none">
							<table border="0" cellpadding="5" cellspacing="0">
								<tr>
									<th>Poll question:</th>
									<td><input type="text" name="poll[question]" size="95" maxlength="255"/></td>
								</tr>
								<tr>
									<th>Answer options:</th>
									<td>
										Put one option per line; 12 options maximum.
										<div style="margin:5px 0 0;"></div>
										<textarea name="poll[opts]" rows="6" cols="70"></textarea>
									</td>
								</tr>
								<tr>
									<th>Answer type:</th>
									<td>
										<label><input type="radio" name="poll[answer]" value="single" checked="checked"/> Single: voter can choose only one option</label><br/>
										<label><input type="radio" name="poll[answer]" value="multiple"/> Multiple: voter can choose multiple options</label>
									</td>
								</tr>
							</table>
						</div>
					</div>
					<p>
						<input type="submit" name="do" value="Post Topic" tabindex="3" <?=(!$usrid ? 'disabled="disabled"' : '')?>/> 
						<input type="button" value="Cancel" onclick="forumToggle('forum-form');return false"/> 
					</p>
				</form>
				
				<?	if(!$topic_num) {
						echo '<div id="no-stuff" style="display:block; margin-bottom:10px;">There are no topics for this forum yet :(</div>';
					} else {
						echo '<div id="no-stuff" style="display:none;"></div>';
				?>
				<table border="0" cellpadding="0" cellspacing="0" width="100%" class="forum-index-list">
					<tr>
						<th>&nbsp;</th>
						<th>Title</th>
						<th><span style="display:none;">Rating</span>&nbsp;</th>
						<th style="text-align:center;">Replies</th>
						<th>Creator</th>
						<th>Last Post</th>
					</tr>
				<?
					// THE QUERY
					if($special_forum_query) $query = $special_forum_query;
					else {
						$query = "SELECT * FROM forums_tags LEFT JOIN forums_topics USING (tid) 
							WHERE tag='".($fid ? $forum->included_tags : $this->associate_tag)."' AND invisible <= '$uval' 
							ORDER BY sticky DESC, last_post DESC LIMIT ".($pgmin ? $pgmin : '0').", ".$this->topics_per_page;
					}
					
					$res = mysql_query($query);
					while($row = mysql_fetch_assoc($res)) {
						if($uval <= $row[closed] || ($row[closed] && $usrrank >= 5))
							$print_closed = ' class="locked"';
						else $print_closed = '';
						
						if($row[ratings]) {
							$total = $row[rating] / $row[ratings];
							if($total >= .5) $thumbs = '<img src="/bin/img/thumbs-up.png" alt="thumbs up"/>';
							else $thumbs = '<img src="/bin/img/thumbs-down.png" alt="thumbs down"/>';
							$thumbs.= '<span class="thumbs-text">'.$row[ratings].'</span>';
						} else $thumbs = '&nbsp;';
						
						if($last_login < $row[last_post]) {
							$lightbulb = '<a href="/forums/?tid='.$row['tid'].'&focus_post=unread"><img src="/bin/img/mascot.png" alt="new posts" border="0"/></a>';
							//$new_words = '<a href="/forums/?tid='.$row[tid].'&page=unread#unread" class="link-to-newest-post"><acronym title="jump to newest unread post">New</acronym></a>';
						} else {
							$lightbulb = '<img src="/bin/img/mascot-off.png" alt="no new posts" border="0"/>';
							$new_words = '';
						}
						
						$postnum = $this->numberOfPosts($row['tid']);
						if($postnum != 0) $postnum = $postnum - 1;
						
						if($row['last_post'] == "0000-00-00 00:00:00") {
							$last_post = "";
						} else {
							$last_post = $this->timeSince($row[last_post]).' ago<br/>by '.($row['last_post_usrid'] ? outputUser($row['last_post_usrid'], FALSE) : $row['last_post_author']);
						}
							
						?>
						<tr<?=($row['sticky'] == 1 ? ' class="sticky"' : '')?>>
							<td><?=$lightbulb?></td>
							<td class="topic-title">
								<a href="/forums/?tid=<?=$row[tid]?>"<?=$print_closed?>><?=stripslashes($row['title'])?></a>
								<?=$new_words.($row['posts'] > $this->posts_per_page ? '<a href="/forums/?tid='.$row['tid'].'&focus_post=last" class="link-to-last-page"><acronym title="jump to last post"><span>&gt;|</span></acronym></a>' : '')?>
							</td>
							<td nowrap="nowrap"><?=$thumbs?></td>
							<td style="text-align:center"><?=$postnum?></td>
							<td><?=($row['usrid'] ? outputUser($row['usrid']) : $row['creator'])?></td>
							<td nowrap="nowrap" class="last-post"><?=$last_post?></td>
						</tr>
						<?
					}
					
				?></table>
				<?
					} // end if(topic_num)
				
				if($p_pagenav) echo '<div style="padding:7px 12px">'.$p_pagenav.'</div>';
				
				?>
				</div>
				
				<div id="forum-foot">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<?
							if($fid) {
								?>
							<td id="forum-details">
								<b>Forum #<?=$fid?></b> <span>|</span> 
								Invisible to <?=$this->invis_groups[$forum->invisible].($forum->invisible > 0 ? 's and below' : '')?> <span>|</span> 
								Closed to <?=$this->invis_groups[$forum->closed].($forum->closed > 0 ? 's and below' : '')?>
							</td>
								<?
							}
							?>	
							<td id="nav-tree"><?=$navtree?></td>
						</tr>
					</table>
				</div>
				
				</div>
				<?
			}
		}
	}
	
	function showTopic($tid='', $pg='') {
		global $db, $usrid, $usrname, $usrrank, $udat, $admns, $forum_suggest, $depreciate_forum_heading;
		
		////////////////
		// SHOW TOPIC //
		////////////////
		
		if($GLOBALS['closed']) die($GLOBALS['closed']);
		
		$uval = $usrrank;
		$this_loc = $this->getThisLocation();
		$last_login = $this->getLastLogin();
		
		if($tid) {
			$query = "SELECT * FROM `forums_topics` where `tid` = '$tid' LIMIT 1";
		} elseif($this->associate_tag) {
			$query = "SELECT * FROM forums_tags LEFT JOIN forums_topics USING(tid) WHERE tag='$this->associate_tag' LIMIT 1";
		} else {
			$query = "SELECT * FROM `forums_topics` where `location` = '$this_loc' LIMIT 1";
		}
		if(!$topic = mysql_fetch_object(mysql_query($query))) {
			$this->newTopicForm();
		} else {
			
			if($this->manual_close) $topic->closed = $this->manual_close;
			
			if(!$tid) $tid = $topic->tid;
			
			//user has access?
			if($uval >= $topic->invisible) {
				
				$topic->title = stripslashes($topic->title);
				
				$group_topic = FALSE;
				$group_member = FALSE;
				$private_group = FALSE;
				
				$tags = array();
				$tagarr = array();
				
				if(!$this->minimal) {
					//get tags
					$query = "SELECT id, tag, usrid, datetime FROM forums_tags WHERE tid='$tid'";
					$res   = mysql_query($query);
					while($row = mysql_fetch_assoc($res)) {
						$tags[$row['id']] = $row['tag'];
						if($row['datetime']) {
							$tagarr[$row['tag']] = array("tag" => $row['tag'], "datetime" => strtotime($row['datetime']), "uid" => $row['usrid']);
						}
						$x = explode(":", $row['tag']);
						if($x[0] == "group") {
							$group_topic = TRUE;
							$group_id = $x[1];
							$q = "SELECT * FROM groups_members WHERE group_id='$group_id' AND usrid='$usrid' LIMIT 1";
							if(mysql_num_rows(mysql_query($q))) $group_member = TRUE;
							$q = "SELECT * FROM groups WHERE group_id='$group_id' LIMIT 1";
							$group_dat = mysql_fetch_object(mysql_query($q));
							if($group_dat->status == "invite") $private_group = TRUE;
						} elseif($x[0] == "news" && strstr($this_loc, "forums/")) {
							// if sblog forum and loc not news article, get heading and append to description
							$newsid = $x[1];
							$q = "SELECT * FROM posts WHERE nid='$newsid' LIMIT 1";
							$n = mysql_fetch_assoc(mysql_query($q));
							include_once($_SERVER['DOCUMENT_ROOT']."/bin/php/class.posts.php");
							$posts = new posts;
							$date = substr($n['datetime'], 0, 10);
							$date = str_replace("-", "/", $date);
							$ndet = 'Posted by '.outputUser($n['usrid'], FALSE, TRUE).' '.timeSince($n['datetime']).' ago &middot; <a href="/posts/'.$date.'/'.$n['permalink'].'" class="arrow-right">Full Article</a>';
							$topic->description = '<div class="news">'.$posts->item($n, "item", TRUE).'<div style="margin:5px 0 0;">'.$ndet.'</div></div>';
						}
					}
					$topic->tags = implode(",", $tags);
				}
				
				if($private_group && !$group_member) dieFullpage("Sorry, this is a private forum.");
				
				// reviews forum desc
				/*if($forum->cid == 9 && $this_loc == "/forums/".$forum->title_url."/") {
					if($forum->corresponding_table == "StaffReview") {
						$q = "SELECT g.`platform`, g.`title_url`, g.`title`, r.`author`, r.`date`, r.`summary` FROM `Games` as g, `StaffReview` as r WHERE r.`indx`='$forum->corresponding_id' AND g.`id`=r.`id` LIMIT 1";
						$rev = mysql_fetch_object(mysql_query($q));
						$topic->description = '<div style="color:#808080;">A <a href="/games/'.specialUrlEncode($rev->platform).'/'.$rev->title_url.'/">'.$rev->title.'</a> review by <a href="/editors/?'.$rev->author.'">'.$rev->author.'</a> on <b>'.formatDate($rev->date, 5).'</b></div>'.$rev->summary.' <a href="/reviews/staff/?subid='.$forum->corresponding_id.'" class="arrow-right">Full Review</a>';
					}
				}*/
				
				$topic_array = array('tags' => $topic->tags);
				$forum = $this->topic2forum($topic_array);
				
				echo '<div id="forum" class="'.($this->size ? "forum-".$this->size : "").'">'."\n";
				
				if(!$this->minimal) {
					?>
					<h2<?=($depreciate_forum_heading ? ' style="display:none;"' : '')?>>
						<a href="/forums/">Forums</a> <span>/</span>
						<?=($forum->title ? $forum->title . ' <span>/</span> ' : '') . $topic->title?>
					</h2>
					<?=($topic->description ? '<div id="description">'.stripslashes($topic->description).'</div>' : "")?>
					<div id="forum-body">
					<?
				}
				
				$d_posts = array();
				$query = "SELECT pid, posted FROM forums_posts WHERE tid='$tid' ORDER BY posted ASC";
				$res   = mysql_query($query);
				$i = 1;
				while($row = mysql_fetch_assoc($res)) {
					$d_posts[$row['pid']] = $i;
					$last_pid = $row['pid'];
					if($row['posted'] > $last_login && !isset($newest_pid)) $newest_pid = $row['pid'];
					$i++;
				}
				$post_num = count($d_posts);
				
				$this->fpost = $_GET['focus_post'];
				if($this->fpost == "last") $this->fpost = $last_pid;
				if($this->fpost == "unread") $this->fpost = $newest_pid;
				
				if($this->minimal) {
					echo '<h4><span>'.($post_num ? $post_num : 'No').' '.($topic->type == "forum" ? 'Post' : 'Comment').($post_num == 1 ? '' : 's').'</span></h4>';
				} else {
					?>
					<div class="menu">
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tr>
								<td id="posts-cell">
									<div>
										<h3><?=($post_num ? $post_num : 'No')?> <?=($topic->type == "forum" ? 'Post' : 'Comment').($post_num == 1 ? '' : 's')?></h3>
									</div>
								</td>
								<?
								if($usrid) {
									$q = "SELECT * FROM forums_mail WHERE `usrid` = '$usrid' AND `tid` = '$tid' LIMIT 1";
									if(mysql_num_rows(mysql_query($q))) {
										$subscribed = TRUE;
										echo '<td><a href="/forums/action.php?unsubscribe='.base64_encode($usrid.';;'.$tid).'">unsubscribe</a></td>';
									} else {
										echo '<td><a href="/forums/action.php?subscribe='.base64_encode($usrid.';;'.$tid).'">subscribe</a></td>';
									}
								}
								
								if($usrrank >= 5) {
								if($topic->sticky) $uns = 'un';
								else $uns = '';
								echo ('<td class="plaintext"><select onchange="window.location=this.options[this.selectedIndex].value">
									<option value="">Admin...</option>
									<option value="/forums/action.php?do=edit_topic_details&tid='.$tid.'">edit topic details</option>
									<option value="/forums/action.php?do='.$uns.'sticky&tid='.$tid.'">make '.$uns.'sticky</option>
									<option value="/forums/action.php?do=close&tid='.$tid.'">close preferences</option>
									'.($usrrank >= 8 ? '<option value="/forums/action.php?do=hide&tid='.$tid.'">access preferences</option><option value="/forums/action.php?do=delete&tid='.$tid.'">delete topic</option>' : '').
									'</select></td>');
								}
								
								if($forum->type != "comments") {
									?><td><iframe src="/forums/rating.php?tid=<?=$tid?>" name="rating-frame" scrolling="no" frameborder="0"></iframe></td>
						  		<td class="plaintext" style="border-right-style:dotted;">Rate this topic:</td><td class="rating" style="border-right-style:dotted;"><a href="/forums/rating.php?tid=<?=$tid?>&rating=1" target="rating-frame"><img src="/bin/img/thumbs-up.png" alt="thumbs up" border="0"/></a></td><td class="rating"><a href="/forums/rating.php?tid=<?=$tid?>&rating=0" target="rating-frame"><img src="/bin/img/thumbs-down.png" alt="thumbs down" border="0"/></a></td>
									<td width="100%" class="last-cell">&nbsp;</td><?
								} else {
									?><td width="100%" class="last-cell"><?=$this->linkBack($forum->location,$forum->title,$forum->title_url,$forum->corresponding_table,$forum->corresponding_id)?></td><?
								}
								?>
							</tr>
						</table>
					</div>
					<?
					
					//Tags
					?>
					<div id="tags">
						<?
						list($tag_words, $taglist) = $this->tagList($tid, $tags);
						?>
						<b>Tags</b> &middot; <?=implode(" &middot; ", $taglist)?><span class="suggest"> &middot; <a href="#x" onclick="suggestTag('<?=$tid?>')" class="arrow-right-gray gray">Suggest a new tag</a></span><span id="put-new-tags"></span>
						<div id="suggest-tag-form"></div>
					</div>
					<?
					
					//poll
					$q = "SELECT * FROM forums_polls WHERE tid='$tid' LIMIT 1";
					if($poll = mysql_fetch_object(mysql_query($q))) {
						
						$q = "SELECT * FROM forums_polls_votes WHERE tid='$tid'";
						$r   = mysql_query($q);
						$total_votes = 0;
						$total_voters = 0;
						$data   = array();
						$voters = array();
						$voted  = array();
						while($row = mysql_fetch_assoc($r)) {
							$data[$row['answer']]++;
							if(!in_array($row['usrid'], $voters)) $voters[] = $row['usrid'];
							if($row['usrid'] == $usrid) $voted[] = $row['answer'];
							$total_votes++;
						}
						$total_voters = count($voters);
						
						if($poll->answer_type == "single") $inptype = "radio";
						else $inptype = "checkbox";
						?>
						<div id="poll">
							<h5><span>Poll Question:</span> <?=$poll->question?></h5>
							<form action="/forums/action.php" method="post"<?=($group_topic && !$group_member ? ' onsubmit="return false;"' : '')?>>
								<input type="hidden" name="tid" value="<?=$tid?>"/>
								<ol>
									<?
									$opts = array();
									$opts = explode("|--|", $poll->options);
									$i = 0;
									foreach($opts as $opt) {
										if($data[$i]) {
											$pc = ($data[$i] / $total_votes);
											$xpos = 813 * $pc + 23 . "px";
											$pc = $pc * 100;
											$pc = round($pc, 1);
											$thisdata = $data[$i].' vote'.($data[$i] != 1 ? 's' : '').' ('.$pc.'%)';
											if(in_array($i, $voted)) $thisdata.= '&nbsp;&nbsp;<span class="yourvote">Your Vote</span>';
										} else {
											$thisdata = "no votes";
											$xpos = "23px";
										}
										echo '<li'.($voted ? ' class="results"' : '').' style="background-position:'.$xpos.' 0;"><label>'.($voted ? '' : '<input type="'.$inptype.'" name="pollopt[]" value="'.$i.'"/> ').$opt.' <span class="polldata" style="'.($voted ? '' : 'display:none').'">&nbsp;&nbsp;'.$thisdata.'</span></label></li>';
										$i++;
									}
									?>
								</ol>
								<?=($group_topic && !$group_member ? '<div style="margin-bottom:5px; padding:5px; border:1px solid #DDD;"><span class="warn">Please join the <a href="/groups/~'.$group_dat->name_url.'">'.$group_dat->name.'</a> group in order to vote.</span></div>' : '')?>
								<?=($voted ? '' : '<input type="submit" name="submit_poll" value="Vote"'.($group_topic && !$group_member ? ' disabled="disabled"' : '').'/> ')?>
								<input type="button" value="<?=($voted ? 'Hide' : 'Show')?> Results" onclick="if($(this).val()=='Show Results') {$(this).val('Hide Results');} else {$(this).val('Show Results');}; $('#poll .polldata').toggle(); $('#poll li').toggleClass('results');"/> 
								<span class="polldata"><?=$total_votes?> total vote<?=($total_votes != 1 ? 's' : '')?> from <?=$total_voters?> unique voter<?=($total_voters != 1 ? 's' : '')?></span>
							</form>
						</div>
						<?
						
					}
				}
				
				if($post_num) {
					?>
					<div id="forum-posts">
						<div id="forum-posts-space">
						<?
						if($topic->usrid == $usrid && $post_num == 1 && !$this->minimal) {
							$adj = array("shiny", "effulgent", "incandescent", "brilliant", "resplendent", "fantastical", "auspicious", "lascivious");
							$rand = rand(0, (count($adj) - 1));
							?>
							<div style="margin:0 2em 15px; padding:0 0 0 4em; background:url(/bin/img/discuss.png) no-repeat 0 50%;">
								<b><big>Expose your <?=$adj[$rand]?> new topic</big></b>
								<div style="margin-top:5px">
									You made a new topic. Nicely done. Now give it more exposure within the site by tagging it with a related subject.<br/>
									Simply suggest a new tag with the above form.
								</div>
							</div>
							<?
						}
						
						//always output first post
						$query = "SELECT * FROM forums_posts WHERE tid = '$tid' ORDER BY posted ASC LIMIT 0, 1";
						$res   = mysql_query($query);
						while($row = mysql_fetch_assoc($res)) {
							$this->outputPost($row, $d_posts[$row['pid']]);
							$dt = strtotime($row['posted']);
						}
						
						//postnav
						if($this->fpost) {
							$min = $d_posts[$this->fpost] - ceil($this->posts_per_page / 2);
							$max = $min - 1 + $this->posts_per_page;
							if($min < 1) $min = 1;
							if($max > $post_num) $max = $post_num;
							if($min > 2)  {
								echo '
								<div class="postnav">
									<table border="0" cellpadding="0" cellspacing="0">
										<tr>
											<th class="down">Showing Posts '.($min + 1).' - '.$max.'</th>
											<td><a href="javascript:void(0)" onclick="outputPosts(\''.$tid.'\');">Show All '.$post_num.' Posts</a></td>
											<th style="display:none; border-right-width:0;"><img src="/bin/img/loading-thickbox.gif" alt="loading"/></th>
										</tr>
									</table>
								</div>
								';
							}
							if(($min + $this->posts_per_page) < $post_num)  {
								$p_pagenav = '
								<div class="postnav">
									<table border="0" cellpadding="0" cellspacing="0">
										<tr>
											<th class="up">Showing Posts '.($min != 1 ? $min + 1 : 1).' - '.$max.'</th>
											<td><a href="javascript:void(0)" onclick="outputPosts(\''.$tid.'\');">Show All '.$post_num.' Posts</a></td>
											<th style="display:none; border-right-width:0;"><img src="/bin/img/loading-thickbox.gif" alt="loading"/></th>
										</tr>
									</table>
								</div>
								';
							}
						} else {
							$min = 1;
							if(($post_num + 2) > $this->posts_per_page) {
								$p_pagenav = '
								<div class="postnav">
									<table border="0" cellpadding="0" cellspacing="0">
										<tr>
											<th class="up">Showing Posts 1 - '.$this->posts_per_page.'</th>
											<td><a href="javascript:void(0)" onclick="outputPosts(\''.$tid.'\');">Show All '.$post_num.' Posts</a></td>
											<th style="display:none; border-right-width:0;"><img src="/bin/img/loading-thickbox.gif" alt="loading"/></th>
										</tr>
									</table>
								</div>
								';
							}
						}
						
						$query = "SELECT * FROM forums_posts WHERE tid = '$tid' ORDER BY posted ASC LIMIT $min, ".($this->posts_per_page - 1);
						$res   = mysql_query($query);
						while($row = mysql_fetch_assoc($res)) {
							$_dt = strtotime($row['posted']);
							$tags_here = array();
							foreach($tagarr as $t) {
								if($t['datetime'] > $dt && $t['datetime'] < $_dt) {
									$tags_here[$t['uid']][] = '<a href="/forums/?tag='.$t['tag'].'">'.$tag_words[$t['tag']].'</a>';
									unset($tagarr[$t['tag']]);
								}
							}
							if($tags_here) {
								foreach(array_keys($tags_here) as $uid) echo '<p class="tagged">'.outputUser($uid, FALSE, TRUE).' tagged '.implode(" &middot ", $tags_here[$uid]).'</p>';
							}
							$this->outputPost($row, $d_posts[$row['pid']]);
							$dt = $row['datetime'];
						}
						
						$tags_here = array();
						foreach($tagarr as $t) {
							if($t['datetime'] > $dt) {
								$tags_here[$t['uid']][] = '<a href="/forums/?tag='.$t['tag'].'">'.$tag_words[$t['tag']].'</a>';
							}
						}
						if($tags_here) {
							foreach(array_keys($tags_here) as $uid) echo '<p class="tagged">'.outputUser($uid, FALSE, TRUE).' tagged '.implode(" &middot ", $tags_here[$uid]).'</p>';
						}
						
						echo $p_pagenav;
						echo "</div>"; // #forum-posts-space
						
						// Reply Form //
						if($user->rank < 5) $status = "regular-user";
						elseif($user->rank == 5) $status = "vip";
						else $status = "staff";
						
						if(!$usrid) {
							$av_tn_style = 'background-image:url(/bin/img/avatars/tn/unknown.png);';
							$av_tn_class = "user";
						} else {
							$query2 = "SELECT username,rank,registered,avatar,handle FROM users LEFT JOIN users_details USING (usrid) WHERE users.usrid = '$usrid' LIMIT 1";
							$user = mysql_fetch_object(mysql_query($query2));
							$av_tn_style = "";
							$av_tn_class = "noavtn";
							if($user->avatar && file_exists($_SERVER['DOCUMENT_ROOT'].'/bin/img/avatars/tn/'.$user->avatar)) {
								$av_tn_style = 'background-image:url(/bin/img/avatars/tn/'.$user->avatar.');';
								$av_tn_class = "yesavtn";
							}
							if($user->avatar && file_exists($_SERVER['DOCUMENT_ROOT'].'/bin/img/avatars/'.$user->avatar)) {
								$p_avatar = '<div class="avatar"><img src="/bin/img/avatars/'.$user->avatar.'" alt="'.$user->username.'" border="1"/></div>';
							} else $p_avatar = "";
						}
						
						$n = $post_num + 1;
						
						if($this->size == "contracted") {
							$cols = 54;
							$rows = 8;
						} else {
							$cols = 100;
							$rows = 15;
						}
						
						$tools = array("b","i","a","spoiler","autotag","emoticon");
						if($this->size != "contracted") array_push($tools,"big","small","strikethrough","blockquote","img","ul","ol");
						
						?>
						<table border="0" cellpadding="0" cellspacing="0" width="100%" id="jump-response">
							<tr>
								<td>
									<div class="message">
										<div class="speechpoint"></div>
										<div class="container" style="overflow:hidden !important;">
											<a href="#jump-response" id="forum-initiate-reply">Your reply here...</a>
											<div id="reply-form" class="message-text" style="display:none">
												<?
												if($group_topic && !$group_member) {
													?>
													<span class="warn">This group topic is open to members of <a href="/groups/~<?=$group_dat->name_url?>"><?=$group_dat->name?></a>. Please join the group in order to participate in the discussion.</span>
													<?
												} else {
													?>
													<div id="quote-inserted" style="display:none">
														<div>
															<a href="javascript:void(0)" class="x" style="float:right" onclick="$('#quote-inserted').hide();">x</a>
															The quoted text has been inserted into the reply field below
														</div>
													</div>
													<?
													
													if($uval < $topic->closed || ($topic->closed && $usrrank < 5)) {
														?>
														<div id="closed-notice">Sorry, this forum is locked; no new posts can be made.</div>
														<?
													} else {
														?>
														<form action="<?=($udat->no_javascript ? '/forums/action.php' : '')?>" method="post" id="forum-form" onsubmit="confirm_exit=false;">
															<div class="switch">
																<input type="hidden" name="tid" value="<?=$tid?>" id="tid"/>
																<input type="hidden" name="is_last_page" value="<?=($pg == $pagenum ? '1' : '')?>" id="is_last_page"/>
																<input type="hidden" name="no_js" value="<?=$udat->no_javascript?>"/>
																<input type="hidden" name="do" value="post_reply"/>
																
																<?
																if(!$usrid) {
																	$n1 = rand(0, 4);
																	$n2 = rand(1, 5);
																	$n = $n1 + $n2 - 1;
																	?>
																	<input type="hidden" name="ajaxregkey" value="<?=$n?>" id="ajaxregkey"/>
																	<fieldset class="identify">
																		<legend>Please identify yourself in order to post your reply</legend>
																		<a href="javascript:void(0);" id="reply-id-old" class="arrow-toggle arrow-toggle-on">Returning User</a> &nbsp; 
																		<a href="javascript:void(0);" id="reply-id-new" class="arrow-toggle">New User</a> 
																		<table border="0" cellpadding="0" cellspacing="0">
																			<tr>
																				<th><label for="identify-un">username:</label></th>
																				<td><input type="text" name="username" size="25" maxlength="30" id="identify-un"/></td>
																			</tr>
																			<tr>
																				<th><label for="identify-pw">password:</label></th>
																				<td><input type="password" name="password" size="25" maxlength="30" id="identify-pw"/> <a href="/retrieve-pass.php">forgot?</a></td>
																			</tr>
																		</table>
																		<table border="0" cellpadding="0" cellspacing="0" style="display:none">
																			<tr>
																				<th><label for="identify-email">e-mail:</label></th>
																				<td>
																					<input type="text" name="email" size="25" maxlength="30" id="identify-email"/> 
																					<span class="msg">Required in order to validate your comment</span>
																				</td>
																			</tr>
																			<tr>
																				<th><label for="identify-name">name:</label></th>
																				<td>
																					<input type="text" name="name" size="25" maxlength="30" id="identify-name"/> 
																					<span class="msg">Your name as it will appear on your post<br/>Include only letters, numbers, -, and _, please</span>
																				</td>
																			</tr>
																			<tr>
																				<td colspan="2">
																					Please validate this form by completing the following simple mathematical equation: 
																					<div style="margin-top:2px; font-weight:bold; font-size:15px; color:#555;">
																						<?=$n1?> + <?=$n2?> = <input type="text" name="ajaxregkeyinp" maxlength="1" size="1" id="ajaxregkeyinp"/>
																					</div>
																				</td>
																			</tr>
																			<tr>
																				<td colspan="2">
																					When you input an e-mail address, we'll send you a link to confirm your post. In addition, you'll receive a password that you 
																					can use to log in or post any more comments. <b>Your e-mail address is never displayed here, nor is it shared with others; 
																					It's only used to validate your post.</b>
																				</td>
																			</tr>
																		</table>
																	</fieldset>
																	<?
																}
																?>
																
																<?=outputToolbox("fmessage", $tools, "use bb code")?>
																
																<div style="margin-right:12px;">
																	<textarea name="message" rows="<?=$rows?>" tabindex="1" id="fmessage" class="styled" style="width:100%" onchange="confirm_exit=true;"></textarea>
																</div>
																
																<div style="text-align:right;">
																	Formatting: No HTML allowed; <a class="arrow-link" href="javascript:void(0)" onclick="window.open('/bbcode.htm','markup_guide_window','width=620,height=510,scrollbars=yes');">BB Code syntax guide</a>
																</div>
																
																<?
																if($this->size != "contracted") {
																	?>
																	<div id="reply-opts" style="margin-top:-5px;">
																		<ul class="tabbed-nav">
																			<li class="on"><a href="#" rel="RO-opts">Options</a></li>
																			<li><a href="#" rel="RO-upload">Upload a Picture</a></li>
																			<li><a href="#" rel="RO-attach">Attach a File</a></li>
																		</ul><br style="clear:both"/>
																		<div id="RO-opts" class="opt">
																			<label><input type="checkbox" name="add_reply_mail" value="1"/> Subscribe: e-mail me whenever someone posts to this thread</label>
																			<p><label><input type="checkbox" name="disable_emoticons" value="1"/> Disable emoticons, smiles, and icons</label></p>
																			<?=($group_topic ? '<p><label><input type="checkbox" name="dont_mail" value="1"/> Don\'t e-mail group members about this post</label></p>' : '')?>
																		</div>
																		<div id="RO-upload" class="opt" style="display:none">
																			<iframe src="/forums/action.php?do=upload" name="upload-img" frameborder="0" style="width:630px; height:69px;"></iframe>
																		</div>
																		<div id="RO-attach" class="opt" style="display:none">coming soon (maybe)</div>
																	</div>
																	<?
																}
																?>
															</div>
															<div id="preview-space" class="switch" style="display:none"></div>
															
															<div id="submit-field">
																<input type="button" value="Preview" tabindex="2" id="preview-reply-button"/> 
																<input type="button" tabindex="3" id="reply-button" onclick="confirm_exit=false; return postReply();" value="Post <?=($forum->type == "comments" ? "Comment" : "Reply")?>"/> 
																<img src="/bin/img/loading-thickbox.gif" alt="loading" id="reply-loading" style="display:none"/>
															</div>
														</form>
													<?
													}
												}
												?>
											</div>
										</div>
									</div>
								</td>
								<td class="user-details <?=$status?>">
									<?
									if($usrid) {
										?>
										<a href="/~<?=$usrname?>">
											<span class="username <?=$av_tn_class?>" style="<?=$av_tn_style?>"><?=$user->username?></span>
											<?=($user->handle ? '<div class="user-title">'.$user->handle.'</div>' : '')?>
											<?=$p_avatar?>
											<!--<div class="joined">Joined <?=timeSince($user->registered)?> ago</div>-->
										</a>
										<?
									} else {
										?>
										<span class="username <?=$av_tn_class?>" style="<?=$av_tn_style?>">You</span>
										<?
									}
									?>
								</td>
							</tr>
						</table>
						
					</div><!--#forum-posts-->
					<?
					
				} else {
					?>
					<div id="forum-posts" style="border:0 !important; margin:5px 0 !important;">
						<table border="0" cellpadding="0" cellspacing="0" width="100%"></table>
					</div>
					<?
				}
				
				if(!$this->minimal) {
					?>
					</div><!--#forum-body-->
					<div id="forum-foot">
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tr>
								<td id="forum-details" nowrap="nowrap">
									<b>Topic #<?=$tid?></b> <span>&middot;</span> 
									<?
									if($private_group) echo "Visible to Group Members";
									else echo "Invisible to ".$this->invis_groups[$topic->invisible].($topic->invisible > 0 ? 's and below' : '');
									?> <span>&middot;</span> 
									<?
									if($group_topic) echo "Open to Group Members";
									else echo "Closed to ".$this->invis_groups[$topic->closed].($topic->closed > 0 ? 's and below' : '');
									?>
								</td>
								<td width="100%" style="text-align:center; color:#999;">
									<?
									$q = "SELECT tid, title FROM forums_topics WHERE last_post < '$topic->last_post' ORDER BY last_post DESC LIMIT 1";
									if($dat = mysql_fetch_object(mysql_query($q))) {
										echo '<a href="?tid='.$dat->tid.'&focus_post=unread" title="'.htmlSC($dat->title).'" class="arrow-left tooltip">older</a>';
									} else {
										echo '<span class="arrow-left">older</span>';
									}
									echo ' &middot; ';
									$q = "SELECT tid, title FROM forums_topics WHERE last_post > '$topic->last_post' ORDER BY last_post ASC LIMIT 1";
									if($dat = mysql_fetch_object(mysql_query($q))) {
										echo '<a href="?tid='.$dat->tid.'&focus_post=unread" title="'.htmlSC($dat->title).'" class="arrow-right tooltip">newer</a>';
									} else {
										echo '<span class="arrow-right" style="background-image:url(/bin/img/arrow-small-gray-right.png);">newer</span>';
									}
									?>
								</td>
								<td id="nav-tree"><?=$this->makeNavTree()?></td>
							</tr>
						</table>
					</div>
					<?
				}
				
				?>
				</div><!--#forum-->
				<?
			} // invisible
		}
	}
	
	////////////////
	// TOPIC LIST //
	////////////////
	
	function showTopicList($limit='6') {
		global $db, $forum_suggest, $usrrank;
		
		if(!$this->associate_tag) {
			echo "Error: Can't compile forum list; No tag given.";
		} else {
			
			?><div id="forum"><?
			
			$query = "SELECT tid, title FROM forums_tags LEFT JOIN forums_topics USING (tid) 
				WHERE tag='".$this->associate_tag."' AND invisible <= '$usrrank' 
				ORDER BY sticky DESC, last_post DESC LIMIT 0, $limit";
			$res = mysql_query($query);
			if(!mysql_num_rows($res)) {
				?>There are no related topics yet. Be the first to <a href="/forums/?tag=<?=urlencode($this->associate_tag)?>">post one</a>.<?
			} else {
				?>
				<ul class="forum-topic-list">
					<?
					while($row = mysql_fetch_assoc($res)) {
						echo '<li><a href="/forums/?tid='.$row['tid'].'">'.stripslashes($row['title']).'</a></li>'."\n";
					}
					?>
					<li class="last"><a href="/forums/?tag=<?=urlencode($this->associate_tag)?>"><span>See all topics</span> or <span>start new one</span></a></li>
				</ul>
				<?
			}
			
			?></div><?
			
		}
	}
	
	// OUTPUT POST //
	
	function outputPost($row, $n) {
		
		global $usrid, $usrrank, $unread, $this_loc;
		
		//disable emoticons?
		$disemote = FALSE;
		if(strstr($row['message'], '<!--disable_emoticons-->')) {
			$disemote = TRUE;
			$row['message'] = str_replace('<!--disable_emoticons-->', '', $row['message']);
		}
		
		$row['message'] = stripslashes($row['message']);
		$premessage = $row['message'];
		if(!$disemote) $row['message'] = emote($premessage);
		$row['message'] = bb2html($row['message']);
		$row['message'] = nl2br($row['message']);
			
		//get user data
		if($row['usrid']) {
			$query2 = "SELECT username,rank,registered,avatar,handle,time_zone FROM users LEFT JOIN users_details USING (usrid) WHERE users.usrid = '".$row['usrid']."' LIMIT 1";
			$user = mysql_fetch_object(mysql_query($query2));
			$av_tn_style = "";
			$av_tn_class = "noavtn";
			if($user->avatar && file_exists($_SERVER['DOCUMENT_ROOT'].'/bin/img/avatars/tn/'.$user->avatar)) {
				$av_tn_style = 'background-image:url(/bin/img/avatars/tn/'.$user->avatar.');';
				$av_tn_class = "yesavtn";
			}
			$p_user = '<span class="username '.$av_tn_class.'" style="'.$av_tn_style.'">'.$user->username.'</span>';
			$user_link = '/~'.$user->username;
			$p_joined = "";
			if(!$p_joined = $users[$row['usrid']]['joined']) {
				$users[$row['usrid']]['joined'] = timeSince($user->registered);
				$p_joined = $users[$row['usrid']]['joined'];
			}
			if($p_joined) $p_joined = '<div class="joined">Joined '.$p_joined.' ago</div>';
		} else {
			// no user data; resort to to poster name
			unset($user);
			$user->rank = 0;
			$row['poster'] = substr($row['poster'], 1, -1);
			$p_user = '<span class="username" style="color:black">'.$row['poster'].'</span>';
			$p_joined = '<div class="joined">unregistered user</div>';
			$user_link = "";
		}
			
		if($user->handle) {
			$p_title = '<div class="user-title">'.$user->handle.'</div>';
		} else $p_title = "";
			
		if($user->avatar && file_exists($_SERVER['DOCUMENT_ROOT'].'/bin/img/avatars/'.$user->avatar)) {
			$p_avatar = '<div class="avatar"><img src="/bin/img/avatars/'.$user->avatar.'" alt="'.$user->username.'" border="1"/></div>';
		} else $p_avatar = "";
			
		$new = FALSE;
		if($this->getLastLogin() < $row['posted']) {
			$new = TRUE;
			$unread++;
		}
			
		if($user->rank < 5) $status = "regular-user";
		elseif($user->rank == 5) $status = "vip";
		else $status = "staff";
		
		if($this->fpost == $row['pid']) echo '<div id="focuspoint"></div>';
		
		$output_dt = convertTimeZone($row['posted'], $user->time_zone);
		$output_dt = formatDate($output_dt, 10);
		
		?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td>
					<div class="message fmss">
						<div class="speechpoint"></div>
						<div class="container">
							<div id="message-<?=$row['pid']?>" class="message-text">
								<?=$row['message']?>
							</div>
							<?
							if($usrrank >= 5 || $row['usrid'] == $usrid) {
								?>
								<div id="editpost-<?=$row['pid']?>" style="display:none">
									<form action="" method="">
										<div style="margin:-6px -7px 0 -7px;">
											<div style="margin-right:12px">
												<textarea name="message" rows="10" id="edit-text-<?=$row['pid']?>" style="width:100%; background-color:#F5F5F5;"><?=$premessage?></textarea>
											</div>
										</div>
										<p style="margin:3px 0 0;">
											<img src="/bin/img/loading-arrows-small.gif" alt="loading" style="display:none"/> 
											<input type="button" value="Submit Changes" class="submit-edited-post" onclick="submitEditedForumPost('<?=$row['pid']?>');"/> 
											<input type="reset" class="cancel-edit-forum-post" value="Cancel" onclick="$('#editpost-<?=$row['pid']?>').hide(); $('#message-<?=$row['pid']?>').show();"/> 
											<?=($usrrank >= 8 ? '<label><input type="checkbox" name="no_track" value="1"/> leave no trace of this edit</label> &nbsp;&nbsp; ' : '')?>
											<label><input type="checkbox" name="disable_emoticons" value="1"<?=($disemote ? ' checked="checked"' : '')?>/> disable emoticons</label>
										</p>
									</form>
								</div>
								<?
							} else {
								?>
								<textarea id="edit-text-<?=$row['pid']?>" style="display:none"><?=$premessage?></textarea>
								<?
							}
							?>
							<ul class="message-opts">
								<li><a href="?<?=($_GET['tid'] ? 'tid='.$row['tid'].'&' : '')?>focus_post=<?=$row['pid']?>" title="permalink to post # <?=sprintf("%03s", $n)?>" class="tooltip postnum<?=($new ? ' postnum-new' : '')?>"><?=sprintf("%03s", $n)?></a></li>
								<li><a class="datetime tooltip" title="<?=$output_dt?>"><?=timeSince($row['posted'])?> ago</a></li><?
								if($usrid) { //quote
									?><li><a href="javascript:void(0)" onclick="postQuote('<?=$row['pid']?>','jump-response','<?=$user->username?> [url=?tid=<?=$row['tid'].($pg > 1 ? '&page='.$pg : '')?>#p<?=$row['pid']?>]said[/url]:'); return false;">Quote</a></li><?
								}
						   	if($usrrank >= 5 || $row['usrid'] == $usrid) { //edit
									?><li><a href="javascript:void(0)" class="edit" onclick="$('#editpost-<?=$row['pid']?>').toggle(); $('#message-<?=$row['pid']?>').toggle();">Edit</a></li><?
								}
								if($usrrank >= 5) { //del
									?><li><a href="javascript:void(0)" onclick="confirmDelete('<?=$row['pid']?>')">Delete</a></li>
									<li><a href="javascript:void(0)" title="<?=$row['ip']?>" class="tooltip">IP</a></li><?
								}
								echo ($row['edited'] ? '<li class="edited">Last edited by '.$row['editor'].' on '.formatDate($row['edited']).'</li>' : '');
								?>
							</ul>
						</div>
					</div>
				</td>
				<td id="p<?=$row['pid']?>" class="user-details <?=$status?>">
					<a href="<?=$user_link?>">
						<?=$p_user?>
						<?=$p_title?>
						<?=$p_avatar?>
						<!--<?=$p_joined?>-->
					</a>
				</td>
			</tr>
		</table>
		<?
		
	}
	
	function newTopicForm() {
		global $usrid;
		
		if($GLOBALS['closed']) die($GLOBALS['closed']);
		
		/* $this->suggest ARRAY VALUES
		$this->associate_tag can be used in place of some of these values
		$[type] = "forum" (default) or "comments"
		$[title] = forum title
		$[tags] = tags (array)
		$[description] = description
		$[form_size] = "x-small", "small", or "large" (default)
		$[legend]
		$[include_location] = if true, insert page location into DB table (useful if forum topic is associated by location rather than tag)
		*/
		
		if(!$this->suggest['type'] || ($this->suggest['type'] != "forum" && $this->suggest['type'] != "comments")) $this->suggest['type'] = "forum";
		
		$tags = array();
		if($this->suggest['tags']) {
			foreach($this->suggest['tags'] as $tag) {
				$tags[] = $tag;
			}
		}
		if($this->associate_tag) $tags[] = $this->associate_tag;
		$o_tags = implode(",", $tags);
		
		if($this->size == "contracted") {
			$cols = 54;
			$rows = 8;
		} else {
			$cols = 100;
			$rows = 15;
		}
		if(!$this->suggest['legend']) {
			$this->suggest['legend'] = ($this->suggest['type'] == "comments" ? "Start the Discussion" : "Start a New Topic");
		}
		
		?>
		<div id="forum" class="<?=($this->size ? "forum-".$this->size : "")?>">
			<div id="forum-posts" style="<?=$this->suggest['fieldset_style']?>">
				<h4><span><?=$this->suggest['legend']?></span></h4>
				<div id="forum-container">
					<?
					if(!$usrid) echo 'Please <a href="/login.php">register or login</a> to comment';
					else {
						
						if($user->rank < 5) $status = "regular-user";
						elseif($user->rank == 5) $status = "vip";
						else $status = "staff";
						
						if(!$usrid) {
							$av_tn_style = 'background-image:url(/bin/img/avatars/tn/unknown.png);';
							$av_tn_class = "user";
						} else {
							$query2 = "SELECT username,rank,registered,avatar,handle FROM users LEFT JOIN users_details USING (usrid) WHERE users.usrid = '$usrid' LIMIT 1";
							$user = mysql_fetch_object(mysql_query($query2));
							$av_tn_style = "";
							$av_tn_class = "noavtn";
							if($user->avatar && file_exists($_SERVER['DOCUMENT_ROOT'].'/bin/img/avatars/tn/'.$user->avatar)) {
								$av_tn_style = 'background-image:url(/bin/img/avatars/tn/'.$user->avatar.');';
								$av_tn_class = "yesavtn";
							}
							if($user->avatar && file_exists($_SERVER['DOCUMENT_ROOT'].'/bin/img/avatars/'.$user->avatar)) {
								$p_avatar = '<div class="avatar"><img src="/bin/img/avatars/'.$user->avatar.'" alt="'.$user->username.'" border="1"/></div>';
							} else $p_avatar = "";
						}
						
						$n = $post_num + 1;
						
						if($this->size == "contracted") {
							$cols = 54;
							$rows = 8;
						} else {
							$cols = 100;
							$rows = 15;
						}
						
						$tools = array("b","i","a","spoiler","autotag");
						if($this->size != "contracted") array_push($tools,"big","small","strikethrough","blockquote","img","ul","ol");
						
						?>
						<table border="0" cellpadding="0" cellspacing="0" width="100%" id="jump-response">
							<tr>
								<td>
									<div class="message">
										<div class="speechpoint"></div>
										<div class="container" style="overflow:hidden !important;">
											<a href="#jump-response" id="forum-initiate-reply">Your reply here...</a>
											<div id="reply-form" class="message-text" style="display:none">
												<?
												if($group_topic && !$group_member) {
													?>
													<span class="warn">This group topic is open to members of <a href="/groups/~<?=$group_dat->name_url?>"><?=$group_dat->name?></a>. Please join the group in order to participate in the discussion.</span>
													<?
												} else {
													?>
													<div id="quote-inserted" style="display:none">
														<div>
															<a href="javascript:void(0)" class="x" style="float:right" onclick="$('#quote-inserted').slideUp();">x</a>
															The quoted text has been inserted into the reply field below
														</div>
													</div>
													<?
													
													if(!$usrid) {
														?>
														<div id="no-login"><div id="no-stuff" style="display:none;"></div>Please <a href="/login.php">log in</a> to participate in the discussion.</div>
														<?
													} elseif($uval < $topic->closed || ($topic->closed && $usrrank < 5)) {
														?>
														<div id="closed-notice">Sorry, this forum is locked; no new topics can be made.</div>
														<?
													} else {
														?>
														<form action="<?=($udat->no_javascript ? '/forums/action.php' : '')?>" method="post" id="forum-form" onsubmit="confirm_exit=false;">
															<div class="switch">
																
																<input type="hidden" name="type" value="<?=$this->suggest['type']?>"/>
																<input type="hidden" name="title" value="<?=htmlSC($this->suggest['title'])?>"/>
																<input type="hidden" name="tags" value="<?=$o_tags?>"/>
																
																<?=outputToolbox("fmessage", $tools, "use bb code")?>
																
																<div style="margin-right:12px">
																	<textarea name="message" rows="<?=$rows?>" tabindex="1" id="fmessage" class="styled" style="width:100%;" onchange="confirm_exit=true;"></textarea>
																</div>
																
																<div style="text-align:right;">
																	Formatting: No HTML allowed; <a class="arrow-link" href="javascript:void(0)" onclick="window.open('/bbcode.htm','markup_guide_window','width=620,height=510,scrollbars=yes');">BB Code syntax guide</a>
																</div>
																
																<?
																if($this->size != "contracted") {
																	?>
																	<div id="reply-opts" style="margin-top:-5px;">
																		<ul class="tabbed-nav">
																			<li class="on"><a href="#" rel="RO-opts">Options</a></li>
																			<li><a href="#" rel="RO-upload">Upload a Picture</a></li>
																			<li><a href="#" rel="RO-attach">Attach a File</a></li>
																		</ul><br style="clear:both"/>
																		<div id="RO-opts" class="opt">
																			<label><input type="checkbox" name="add_reply_mail" value="1"/> Subscribe: e-mail me whenever someone posts to this thread</label>
																			<?=($group_topic ? '<p><label><input type="checkbox" name="dont_mail" value="1"/> Don\'t e-mail group members about this post</label></p>' : '')?>
																		</div>
																		<div id="RO-upload" class="opt" style="display:none">
																			<iframe src="/forums/action.php?do=upload" name="upload-img" frameborder="0" style="width:630px; height:69px;"></iframe>
																		</div>
																		<div id="RO-attach" class="opt" style="display:none">coming soon (maybe)</div>
																	</div>
																	<?
																}
																?>
															</div>
															<div class="switch" style="display:none">
																<div id="preview-space"></div>
															</div>
															
															<div id="submit-field">
																<input type="button" value="Preview" tabindex="2" id="preview-reply-button"/> 
																<input type="button" tabindex="3" id="reply-button" onclick="confirm_exit=false; return postNewTopic();" value="Post <?=($this->type == "comments" ? "Comment" : "Reply")?>"<?=(!$usrid ? ' disabled="disabled"' : '')?>/> 
																<img src="/bin/img/loading-thickbox.gif" alt="loading" id="reply-loading" style="display:none"/>
															</div>
														</form>
													<?
													}
												}
												?>
											</div>
										</div>
									</div>
								</td>
								<td class="user-details <?=$status?>">
									<a href="/~<?=$usrname?>">
										<span class="username <?=$av_tn_class?>" style="<?=$av_tn_style?>"><?=$user->username?></span>
										<?=($user->handle ? '<div class="user-title">'.$user->handle.'</div>' : '')?>
										<?=$p_avatar?>
										<?=($this->size != "contracted" ? '<div class="joined">Joined '.timeSince($user->registered).' ago</div>' : '')?>
									</a>
								</td>
							</tr>
						</table>
						<?
					}
					?>
				</div>
			</div>
		</div>
		<?
	}
	
	/*function showTopicList() {
		global $usrrank;
		
		if(!$this->associate_tag) {
			echo "Error: Can't compile forum list; No tag given.";
		} else {
		
			$uval = $usrrank;
			$this_loc = $this->getThisLocation();
				
			?>
			<div id="forum" class="forum-topic-list">
				<?
			
			if(!$tnum = $this->numberOfTopics($fid)) {
				echo '<div style="margin:5px 0;">No topics yet :(</div>';
				$suggest[tags] = $this->associate_tag . $this->append_tag;
				$suggest[form_size] = "x-small";
				$suggest[hide] = TRUE;
				echo $this->formToCreate($suggest);
			} else {
				
				?><ul><?
				$query = "SELECT * FROM forums_tags LEFT JOIN forums_topics USING (tid) WHERE tag = '".$this->associate_tag."' AND invisible <= '$uval' ORDER BY sticky DESC, last_post DESC LIMIT 5";
				$res = mysql_query($query);
				while($row = mysql_fetch_assoc($res)) {
					echo '<li><a href="/forums/?tid='.$row[tid].'">'.stripslashes($row[title])."</a></li>\n";
				}
				?><li style="background-image:url(/bin/img/arrow-right.gif) !important;"><a href="/forums/?tag=<?=$this->associate_tag?>">Start a new topic</a></li>
				</ul><?
			
			}
				
			?></div><?
		}
	}*/
	
	function getUserValue($user='') {
		global $db, $usrid, $usrrank;
		if($user == $usrid || !$user) return $usrrank; //given value is logged-in user
		else {
			$query = "SELECT rank FROM users WHERE usrid='$user' LIMIT 1";
			$dat = mysql_fetch_object(mysql_query($query));
			return $dat->rank;
		}
	}
	
	function getLastLogin($user='') {
		global $db, $usrid, $usrlastlogin;
		if(!$usrlastlogin) $usrlastlogin = date("Y-m-d H:i:s");
		if($user == $usrid || !$user) return $usrlastlogin; //given value is logged-in user
		else {
			$query = "SELECT previous_activity FROM users WHERE usrid='$user' LIMIT 1";
			$dat = mysql_fetch_object(mysql_query($query));
			if($dat->previous_activity) return $dat->previous_activity;
			else return date("Y-m-d H:i:s");
		}
	}
	
	function getForumURL($fid='', $tid='') {
		if($fid) $ret.= '?fid='.$fid;
		elseif($tid) $ret.= '?tid='.$tid;
		return $ret;
	}
	
	function isModerator($user='') {
		global $usrid;
		if(!$user) $user = $usrid;
		$query = "SELECT * FROM `forums_admins` WHERE `user` = '$user' AND `authority` != '' LIMIT 1"; // tru for mod or admin
		if(mysql_num_rows(mysql_query($query))) return TRUE;
		else return FALSE;
	}
	
	function isAdmin($user='') {
		global $usrid;
		if(!$user) $user = $usrid;
		$query = "SELECT * FROM `forums_admins` WHERE `user` = '$user' AND `authority` = 'admin' LIMIT 1";
		if(mysql_num_rows(mysql_query($query))) return TRUE;
		else return FALSE;
	}
	
	function numberOfTopics($fid='') {
		global $uval;
		if(!$uval) $uval = $this->getUserValue();
		if($fid) {
			$q = "SELECT included_tags FROM forums WHERE fid='$fid'";
			$dat = mysql_fetch_object(mysql_query($q));
			$query = "SELECT * FROM forums_tags LEFT JOIN forums_topics USING (tid) WHERE tag='".$dat->included_tags."' AND invisible < '$uval'";
		} elseif($this->associate_tag) {
			$query = "SELECT * FROM forums_tags LEFT JOIN forums_topics USING (tid) WHERE tag='".$this->associate_tag."' AND invisible < '$uval'";
		} else {
			$query = "SELECT * FROM `forums_topics` WHERE `location`='".$this->getThisLocation()."' AND `invisible` < '$uval'";
		}
		$ret = mysql_num_rows(mysql_query($query));
		return $ret;
	}
	
	function numberOfPosts($tid='', $pg='') {
		if($tid) {
			$query = "SELECT * FROM `forums_posts` WHERE `tid` = '$tid' AND `invisible` < ".$this->getUserValue();
			$res = mysql_query($query);
			return mysql_num_rows($res);
		} elseif($this->associate_tag) {
			$query = "SELECT * FROM forums_tags LEFT JOIN forums_topics USING (tid) WHERE tag='".$this->associate_tag."' AND invisible < ".$this->getUserValue();
			if($dat = mysql_fetch_object(mysql_query($query))) {
				$q = "SELECT * FROM forums_posts WHERE `tid`='".$dat->tid."' AND `invisible` < ".$this->getUserValue();
				return mysql_num_rows(mysql_query($q));
			}
		} else {
			$query = "SELECT posts FROM forums_topics WHERE `location` = '".$this->getThisLocation()."' LIMIT 1";
			$dat = mysql_fetch_object(mysql_query($query));
			return $dat->posts;
		}
	}
	
	function newPosts() {
		global $last_login, $uval;
		if(!$last_login) $last_login = $this->getLastLogin();
		if(!$uval) $uval = $this->getUserValue();
		if($this->associate_tag) {
			$query = "SELECT * FROM forums_tags LEFT JOIN forums_topics USING (tid) WHERE tag='".$this->associate_tag."' AND last_post > '$last_login' AND invisible < '$uval' LIMIT 1";
			if(mysql_num_rows(mysql_query($query))) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}
	
	function updatePosts($topic_id='') {
		// just for numerical display purposes, since these figures don't reflect the actual number of posts & topics
		// since it only counts those p&t that are visible to registered users and above
		// for accurate post count, make a db query
		global $db;
		$query = "SELECT * FROM `forums_topics`".($topic_id ? " WHERE `tid` = '$topic_id'" : '');
		$res = mysql_query($query);
		while($row = mysql_fetch_assoc($res)) {
			$t[$row[tid]] = 0;
			$date[$row[tid]] = '0000-00-00 00:00:00';
			$query2 = "SELECT * FROM `forums_posts` WHERE tid='$row[tid]' ORDER BY `posted` DESC";
			$res2 = mysql_query($query2);
			while($row2 = mysql_fetch_assoc($res2)) {
				$t[$row[tid]]++;
				if($t[$row[tid]] == 1) {
					$date[$row[tid]] = $row2[posted];
					$usrid[$row[tid]] = $row2[usrid];
				}
			}
		}
		foreach(array_keys($t) as $tid) {
			$query = "UPDATE forums_topics SET `posts` = '$t[$tid]', `last_post` = '$date[$tid]', `last_post_usrid` = '$usrid[$tid]' WHERE `tid` = '$tid'";
			if(!mysql_query($query)) echo "Error: couldn't update forum post count<br/>";
		}
		
	}
	
	function linkBack($backto='',$ftitle='',$ftitle_url='',$c_table='',$c_id='') {
		global $db;
		if($backto == $this->getThisLocation()) return '<a href="/forums/'.$ftitle_url.'/">expand forum contents &#187;</a>';
		elseif($c_table == "Games") {
			$q = "SELECT `platform`, `title`, `title_url` FROM `Games` WHERE `indexid` = '$c_id' LIMIT 1";
			if($dat = mysql_fetch_object(mysql_query($q)))
				return '<a href="/games/'.specialUrlEncode($dat->platform).'/'.$dat->title_url.'/"><span class="arrow-right">'.$dat->title.' coverage</span></a>';
		} elseif(strstr($backto, '/people/')) {
			return '<a href="'.$backto.'">'.$ftitle.' profile &#187;</a>';
		} elseif(strstr($backto, '/news/')) {
			return '<a href="'.$backto.'">see full news article &#187;</a>';
		}
		else return '&nbsp;';
	}
	
	function timeSince($original, $short = false) {
		$original = strtotime($original);

		// array of time period chunks
		$chunks = array(
			array(60 * 60 * 24 * 365 , 'year'),
			array(60 * 60 * 24 * 30 , 'month'),
			array(60 * 60 * 24 * 7, 'week'),
			array(60 * 60 * 24 , 'day'),
			array(60 * 60 , 'hour'),
			array(60 , 'minute'),
		);
		
		$today = time(); /* Current unix time  */
		$since = $today - $original;
		
		// $j saves performing the count function each time around the loop
		for ($i = 0, $j = count($chunks); $i < $j; $i++) {
			
			$seconds = $chunks[$i][0];
			$short ? ($name = substr($chunks[$i][1],0,1)) : ($name = $chunks[$i][1]);
			
			// finding the biggest chunk (if the chunk fits, break)
			if (($count = floor($since / $seconds)) != 0) {
				// DEBUG print "<!-- It's $name -->\n";
				break;
			}
		}
		
		$print = ($count == 1) ? '1 '.$name : "$count {$name}" . ($short ? "" : "s");
		
		if ($i + 1 < $j) {
			// now getting the second item
			$seconds2 = $chunks[$i + 1][0];
			$short ? ($name2 = substr($chunks[$i + 1][1],0,1)) : ($name2 = $chunks[$i + 1][1]);
			
			// add second item if it's greater than 0
			if (($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0) {
				$print .= ($count2 == 1) ? ', 1 '.$name2 : ", $count2 {$name2}" . ($short ? "" : "s");
			}
		}
		if($short) {
			$print=str_replace(" ","",$print);
			$print=str_replace(","," ",$print);
		}
		return $print;
	}
	
	function longWordWrap($string, $lm = 75) {
	    $string = str_replace("\n", "\n ", $string); // add a space after newline characters, so that 2 words only seperated by \n are not considered as 1 word
	    $words = explode(" ", $string); // now split by space
	    foreach ($words as $word) {
	    	$outstring .= chunk_split($word, $lm, " ");
	    }
	    return $outstring;
	}
	
	function makeNavTree() {
		global $db, $usrrank;
		
		$navtree = '<select onchange="window.location=\'/forums/\'+this.options[this.selectedIndex].value">
			<optgroup label="">
				<option value="">Navigate to...</option>
				<option value="">forum index</option>
				<option value="new-posts">new posts</option>';
		$q = "SELECT * FROM forums_categories ORDER BY `sort` ASC";
		$res = mysql_query($q);
		while($row = mysql_fetch_assoc($res)) {
			$navtree.= '</optgroup><optgroup label="'.$row[category].'">';
			$q2 = "SELECT * FROM forums WHERE cid='$row[cid]' AND `invisible` <= ".$usrrank;
			$res2 = mysql_query($q2);
			while($row2 = mysql_fetch_assoc($res2)) {
				$navtree.= '<option value="?fid='.$row2[fid].'">'.$row2[title]."</option>\n";
			}
		}
		$navtree.= '</optgroup></select>';
		return $navtree;
	}
	
	function getLastForumInfo($fid) {
		$q = "SELECT * FROM forums WHERE fid='$fid'";
		$dat = mysql_fetch_object(mysql_query($q));
		$q2 = "SELECT * FROM forums_tags LEFT JOIN forums_topics USING (tid) WHERE tag LIKE '".$dat->included_tags."' ORDER BY last_post DESC LIMIT 1";
		$dat2 = mysql_fetch_object(mysql_query($q2));
		$ret[post] = $dat2->last_post;
		$ret[poster] = $dat2->last_post_usrid;
		return $ret;
	}
	
	function tagList($tid, $tags) {
		global $usrrank, $tag_convert;
		
		$taglist = array();
		while(list($i, $tag) = each($tags)) {
			$words = "";
			
			if($tag_convert) {
				foreach($tag_convert as $t) {
					while(list($k, $v) = each($t)) {
						if($tag == $k) $words = $v;
					}
				}
			}
			if(!$words) {
				if(preg_match("/^gid:([0-9]+)/", $tag, $f)) { //game
					$q = "SELECT title FROM games WHERE gid='$f[1]' LIMIT 1";
					$dat = mysql_fetch_object(mysql_query($q));
					$words = $dat->title;
				} elseif(preg_match("/^preview:([0-9]+)/", $tag, $f)) { //preview
					if($usrrank >= 5) {
						$words = '<abbr title="Hidden from site users"><del>'.$tag.'</del></abbr>';
					}
				} elseif(preg_match("/^news:([0-9]+)/", $tag, $f)) { //news
					if($usrrank >= 5) {
						$words = '<abbr title="Hidden from site users"><del>News #'.$f[1].'</del></abbr>';
					}
				} elseif(preg_match("/^blurb:([0-9]+)/", $tag, $f)) { //blurb
					if($usrrank >= 5) {
						$words = '<abbr title="Hidden from site users"><del>Blurb #'.$f[1].'</del></abbr>';
					}
				} elseif(preg_match("/^staff_review:([0-9]+)/", $tag, $f)) { //staff review
					$words = '<abbr title="Staff review #'.$f[1].'">Review comments</abbr>';
				} elseif(preg_match("/^reader_review:([0-9]+)/", $tag, $f)) { //reader review
					$words = '<abbr title="Reader review #'.$f[1].'">Review comments</abbr>';
				} elseif(preg_match("/^group:([0-9]+)/", $tag, $f)) { //game
					$q = "SELECT * FROM groups WHERE group_id='$f[1]' LIMIT 1";
					$dat = mysql_fetch_object(mysql_query($q));
					$words = $dat->name;
				} else {
					$words = $tag;
				}
			}
			if($words) {
				if($usrrank >= 5) {
					$app[0] = ' onmouseover="$(this).children(\'.x\').show();" onmouseout="$(this).children(\'.x\').hide();"';
					$app[1] = ' <a href="javascript:void(0)" onclick="deleteTag(\''.$i.'\'); $(this).parent().hide();" title="remove tag" class="x" style="display:none">X</a>';
				} else unset($app);
				$taglist[] = '<span'.$app[0].'><a href="/forums/?tag='.urlencode($tag).'">'.$words.'</a>'.$app[1]."</span>";
				$tagarr[$tag] = $words;
			}
		}
		return array($tagarr, $taglist);
		
	}
	
	function topic2forum($topic) {
		global $tag_convert;
		if(preg_match_all("/forum:([^,]+)/m", $topic[tags], $matches)) {
			$q = "SELECT title FROM forums WHERE included_tags='forum:".$matches[1][0]."' LIMIT 1";
			if($dat = mysql_fetch_object(mysql_query($q))) {
				$forum->title = '<a href="/forums/?fid='.$matches[1][0].'">'.$dat->title.'</a>';
				if(!$tag_convert) {
					$tag_convert = array();
				}
				$tag_convert[] = array("forum:".$matches[1][0] => $dat->title);
			}
		} else $forum = "";
		return $forum;
	}
	
	function tagWord($tag) {
		if(strstr($tag, "gid:")) {
			$x = explode(":", $tag);
			$q = "SELECT title FROM games WHERE gid='$x[1]' LIMIT 1";
			$dat = mysql_fetch_object(mysql_query($q));
			$tagword = stripslashes($dat->title);
		} elseif(!strstr($tag, ":")) $tagword = $tag;
		return $tagword;
	}
	
	function addReplyMail($tid) {
		global $usrid;
		
		$q = "SELECT * FROM `forums_mail` WHERE `usrid` = '$usrid' AND `tid` = '$tid' LIMIT 1";
		if(!mysql_num_rows(mysql_query($q))) {
			$q2 = "INSERT INTO `forums_mail` (`usrid`, `tid`) VALUES ('$usrid', '$tid')";
			$res = mysql_query($q2);
			if(!$res) return FALSE;
			else return TRUE;
		} else return TRUE;
	}
	
	function parseForForumPost($message, $tid) {
		global $usrrank;
		/*
		preg_match("/<code>(.|\n|\r)+\<\/code>/", $message, $html);
		if($html[0]) {
			$message = str_replace($html[0], "<code>", $message);
		}
		$message = strip_tags($message, '<b><i><a><strike><big><small><blockquote><del><img><code>');
		if($html[0]) $message = str_replace("<code>", $html[0], $message);
		*/
		if($usrrank < 4) $message = preg_replace("@<([a-z0-9/]+.*?)>@is", "&lt;$1&gt;", $message);
		$message = trim($message);
		
		$message = str_replace("[AMP]", "&", $message);
		$message = str_replace("[PLUS]", "+", $message);
		
		//extract autoTags
		preg_match_all('@\[(game|person)=?(.*?)\](.*?)\[/(game|person)\]@ise', $message, $matches, PREG_SET_ORDER);
		if($matches) {
			foreach($matches as $m) {
				$type = $m[1];
				$subj = ($m[2] ? $m[2] : $m[3]);
				$txt = $m[3];
				$x = formatName($subj);
				$subj = $x[0];
				
				unset($tag);
				if($type == "game") {
					$q = "SELECT gid FROM games WHERE title='".mysql_real_escape_string($subj)."' LIMIT 1";
					if($gdat = mysql_fetch_object(mysql_query($q))) {
						$tag = "gid:".$gdat->gid;
					}
				} else $tag = $subj;
				
				if($tag) {
					//check if tag already exists for this topic
					$q = "SELECT * FROM forums_tags WHERE tid='$tid' AND tag='".mysql_real_escape_string($tag)."' LIMIT 1";
					if(!mysql_num_rows(mysql_query($q))) {
						$q = "INSERT INTO forums_tags (tid, tag) VALUES ('$tid', '".mysql_real_escape_string($tag)."');";
						//mysql_query($q);
					}
				}
				
			}
		}
		
		return $message;
	}
	
	function extractTags($txt, $tid='') {
		
		$tags = array();
		preg_match_all('@\[(game|person)=?(.*?)\](.*?)\[/(game|person)\]@ise', $txt, $matches, PREG_SET_ORDER);
		if($matches) {
			foreach($matches as $m) {
				$type = $m[1];
				$subj = ($m[2] ? $m[2] : $m[3]);
				if($tag = $this->convertTag($subj)) {
					if($tid) {
						//check if tag already exists for this topic
						$q = "SELECT * FROM forums_tags WHERE tid='$tid' AND tag='".mysql_real_escape_string($tag)."' LIMIT 1";
						if(!mysql_num_rows(mysql_query($q))) {
							$tags[] = $tag;
						}
					} else $tags[] = $tag;
				}
			}
			return $tags;
		}
		
	}
	
	function convertTag($tag) {
		
		$x = formatName($tag);
		$qg = "SELECT gid FROM games WHERE title='".mysql_real_escape_string($x[0])."' LIMIT 1";
		$qp = "SELECT pid FROM people WHERE name='".mysql_real_escape_string($x[0])."' LIMIT 1";
		if($gdat = mysql_fetch_object(mysql_query($qg))) {
			$tag = "gid:".$gdat->gid;
		} elseif($pdat = mysql_fetch_object(mysql_query($qp))) {
			$tag = "pid:".$pdat->pid;
		}
		return $tag;
		
	}
	
}