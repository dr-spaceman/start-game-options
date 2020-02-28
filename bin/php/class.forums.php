<?
// SHOW CATEGORY
// NEW POSTS
// SHOW FORUM
// SHOW TOPIC
// TOPIC LIST
// output post

require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/class.tags.php");
require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/bbcode.php");
require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/htmltoolbox.php");
$page->javascripts[] = "/bin/script/forums.js";
	
//get user prefs (like javascript switch)
if($usrid) {
	$q = "SELECT * FROM users_prefs WHERE usrid='$usrid' LIMIT 1";
	$userprefs = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
}

class forum {
	
	var $location; //ie post:997
	var $unique_location; //like $location, but there can't be more than one topic with this given var
	var $posts_per_page = 50;
	var $topics_per_page = 30;
	var $users;
	var $user_groups = array(
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
	var $htmltools = array("b","i","a","spoiler","links","emoticon","big","small","strikethrough","blockquote","img","ul","ol");
	
	function getThisLocation() {
		$loc = $_SERVER["REQUEST_URI"];
		//if(!preg_match("/\/$|\.[a-z]{3,4}$/i", $loc)) $loc.= "/"; //add trailing slash
		return $loc;
	}
	
	function showTopicList($limit='6') {
		
		////////////////
		// TOPIC LIST //
		////////////////
		
		global $db, $usrid, $usrrank, $usrlastlogin;
			
		?>
		<div id="forum">
			<?
			
			if($fid = $this->fid) {
				$query = "SELECT * FROM forums_topics WHERE fid='".$forum->fid."'";
				$link = "?fid=".$fid;
			} elseif($this->location) {
				$query = "SELECT * FROM forums_topics WHERE `location`='".mysqli_real_escape_string($GLOBALS['db']['link'], $this->location)."'";
				$link = "?location=".urlencode($this->location);
			} elseif($this->tag) {
				$this->tag = formatName($this->tag);
				$query = "SELECT * FROM forums_tags LEFT JOIN forums_topics USING (tid) WHERE `tag`='".mysqli_real_escape_string($GLOBALS['db']['link'], $this->tag)."'";
				$link = "?tag=".formatNameURL($this->tag);
			} else {
				echo "No forum id, forum location, or tag provided; Can't fetch forum details.";
				return;
			}
			
			$query.= "ORDER BY sticky DESC, last_post DESC LIMIT 0, $limit";
			$res = mysqli_query($GLOBALS['db']['link'], $query);
			if(!mysqli_num_rows($res)) {
				?>There are no related topics yet. Be the first to <a href="/forums/<?=$link?>">post one</a>.<?
			} else {
				?>
				<ul class="forum-topic-list">
					<?
					while($row = mysqli_fetch_assoc($res)) {
						echo '<li><a href="/forums/?tid='.$row['tid'].'">'.stripslashes($row['title']).'</a></li>'."\n";
					}
					?>
					<li class="last"><a href="/forums/<?=$link?>"><span>See all topics</span> or <span>start new one</span></a></li>
				</ul>
				<?
			}
			
			?>
		</div>
		<?
			
	}
	
	function showForum() {
		
		////////////////
		// SHOW FORUM //
		////////////////
		
		//one of the following is required: $this->fid, $this->location, $this->tag
		
		global $db, $usrid, $usrrank, $usrlastlogin, $page;
		
		$bb = new bbcode();
		
		if($this->fid) {
			
			$fid = $this->fid;
			$forum = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM `forums` where `fid` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $fid)."' LIMIT 1"));
			$query = "SELECT * FROM forums_topics WHERE fid='".$forum->fid."' ORDER BY sticky DESC, last_post DESC";
		
		} elseif($this->location) {
			
			$forum->invisible = 0;
			$forum->closed = 0;
			$query = "SELECT * FROM forums_topics WHERE `location`='".mysqli_real_escape_string($GLOBALS['db']['link'], $this->location)."' ORDER BY sticky DESC, last_post DESC";
			
			//get fid
			$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query." LIMIT 1"));
			$fid = $dat->fid;
			
			// assign some data to $forum
			$forum->invisible = 0;
			$forum->closed = 0;
			if(substr($this->location, 0, 6) == "group:"){
				//group
				$fid = 15;
				$group_id = substr($this->location, 6);
				$q = "SELECT * FROM groups WHERE `group_id` = '$group_id' LIMIT 1";
				if(!$groupdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) $page->die_("There was an error fetching group details [group ID # $group_id]");
				$forum->title = $groupdat->name;
				$forum->description = $groupdat->about;
				//user is a member?
				$q = "SELECT * FROM groups_members WHERE group_id='$group_id' AND usrid='$usrid' LIMIT 1";
				if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $page->die_('<h1>Access Denied</h1>You must be a group member to access this group\'s forums. <a href="/groups/'.$group_id.'/" class="arrow-right">Join this group</a>');
			}
			
		} elseif($this->tag) {
			
			$this->tag = formatName($this->tag);
			// assign some data to $forum
			$forum->title = $this->tag;
			$forum->invisible = 0;
			$forum->closed = 0;
			
			if(strstr($this->tag, "AlbumID:")){
				//get album details
				$albumid = substr($this->tag, 8);
				$q = "SELECT `title`, `subtitle` FROM albums WHERE albumid='".mysqli_real_escape_string($GLOBALS['db']['link'], $albumid)."' LIMIT 1";
				if($album = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) $forum->description = '<a href="/music?id='.$albumid.'">'.$album->title.($album->subtitle ? ' <i>'.$album->subtitle.'</i>' : '').'</a>';
			} else{
				//chech pages DB for details
				$q = "SELECT * FROM pages WHERE `title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $this->tag)."' LIMIT 1";
				$pgdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
				if($pgdat->description) $forum->description = $pgdat->description.' <b class="arrow-right">[['.$this->tag.'|MORE]]</b>';
			}
			
			$query = "SELECT * FROM forums_tags LEFT JOIN forums_topics USING (tid) WHERE `tag`='".mysqli_real_escape_string($GLOBALS['db']['link'], $this->tag)."' ORDER BY sticky DESC, created DESC";
			
		} else {
			echo "No forum id, forum location, or tag provided; Can't fetch forum details.";
			return;
		}
		
		if($forum->cid){
			$q = "SELECT * FROM forums_categories WHERE cid='$forum->cid' LIMIT 1";
			$catg = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
		}
		
		//user has access?
		if($usrrank < $forum->invisible) return;
		
		?>
		<div id="forum">
			<?
			if(!$this->depreciate_heading){
				?>
				<div class="forum-dir">
					<a href="/forums/">Forums</a> / 
					<?=($catg ? '<a href="/forums/?category='.$catg->cid.'">'.$catg->category.'</a> / ' : '')?>
					<?=($this->tag ? '<a href="/forums/tags/">Tags</a> / ' : '')?>
				</div>
				<h1>
					<?=$forum->title?>
				</h1>
				<?=($forum->description ? '<div class="forum-description">'.$bb->bb2html($forum->description).'</div><br style="clear:left;"/>' : '')?>
				<?
			}
			?>
			
				<?
				
				$navtree = $this->makeNavTree();
				
				if(!$usrid) $post_access = 'no-login';
				elseif($usrrank < $forum->closed) $post_access = 'closed-notice';
				else $post_access = 'forum-form';
				
				//page navigation
				$topic_num = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query));
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
							$class = ($i > 1 ? "flush" : '');
							$p_pagenav.= ($pg == $i ? '<li class="'.$class.'"><b>'.($i == 1 ? 'Page ' : '').$i.'</b></li>' : '<li class="'.$class.'"><a href="/forums/?'.($this->associate_tag ? 'tag='.urlencode($this->associate_tag) : 'fid='.$fid).'&pg='.$i.'">'.($i == 1 ? 'Page ' : '').$i.'</a></li>');
							$didnt_show = 0;
						} elseif(!$didnt_show) {
							$p_pagenav.= '<li class="flush"><span>&middot;&middot;&middot;</span></li>';
							$didnt_show++;
						}
					}
				}
				
				
				?>
				<header>
					<div class="menu pgnav">
						<ul>
							<li class="num"><span><?=($topic_num ? $topic_num : 'No')?> Topic<?=($topic_num == 1 ? '' : 's')?></span></li>
							<li class="newpost"><a href="<?=(!$usrid ? '/login.php' : '#new_topic')?>" onclick="$('#new-topic-form').toggle();">New Topic</a></li>
							<?
							if($this->location) {
								if($usrid) {
									$q = "SELECT * FROM forums_mail WHERE `usrid` = '$usrid' AND `location` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $this->location)."' LIMIT 1";
									if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) {
										$subscribed = TRUE;
									}
								}
								?>
								<li><span><input type="checkbox" <?=($subscribed ? 'checked' : '')?> class="fauxcheckbox" id="subscribe-topic" onclick="forumSubscription('location', '<?=$this->location?>', $(this))"/><label for="subscribe-topic" class="tooltip" title="E-mail me whenever a new topic is created in this forum">Subscribe</label></span></li>
								<?
							} elseif($fid) {
								if($usrid) {
									$q = "SELECT * FROM forums_mail WHERE `usrid` = '$usrid' AND `fid` = '$fid' LIMIT 1";
									if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) {
										$subscribed = TRUE;
									}
								}
								?>
								<li><span><input type="checkbox" <?=($subscribed ? 'checked' : '')?> class="fauxcheckbox" id="subscribe-topic" onclick="forumSubscription('fid', '<?=$fid?>', $(this))"/><label for="subscribe-topic" class="tooltip" title="E-mail me whenever a new topic is created in this forum">Subscribe</label></span></li>
								<?
							}
							if($fid && $usrrank >= 5) {
								?>
								<li>
									<span>
										<select onchange="window.location=this.options[this.selectedIndex].value">
											<option value="">Administer&hellip;</option>
											<option value="/forums/action.php?do=Edit+Forum+Details&fid=<?=$fid?>">forum details</option>
											<option value="/forums/action.php?do=close&fid=<?=$fid?>">close forum</option>
											<?=($usrrank >= 8 ? '<option value="/forums/action.php?do=hide&fid='.$fid.'">hide forum</option><option value="/forums/action.php?do=delete&fid='.$fid.'">delete forum</option>' : '')?>
										</select>
									</span>
								</li>
								<?
							}
								if($this->associate_tag && $usrrank >= 5) {
									echo '<li><a href="/forums/action.php?do=manage_tags&edit_tag='.$this->associate_tag.'"><span style="color:black">Admin:</span> <span style="text-decoration:underline">manage this tag</span></a></li>';
								}
								?>
						</ul>
					</div>
					<?=($p_pagenav ? '<nav class="pgnav"><ul>'.$p_pagenav.'</ul></nav>' : '')?>
				</header>
				
				<fieldset id="new-topic-form">
					<?
					if(!$usrid) echo '<big>Please <a href="/login.php">log in</a> to post a topic.</big>';
					elseif($usrrank < $forum->closed) echo 'Sorry, this forum is locked; no new topics can be made.';
					else {
						?>
						<form action="/forums/action.php" method="post" onsubmit="return requiredA();">
							<?=($fid || $this->suggest_fid ? '<input type="hidden" name="fid" value="'.($fid ? $fid : $this->suggest_fid).'"/>' : '')?>
							<?=($this->location ? '<input type="hidden" name="location" value="'.$this->location.'"/>' : '')?>
							<?=($this->tag ? '<input type="hidden" name="tags[]" value="'.htmlSC($this->tag).'"/>' : '')?>
							
							<?
							if(!$fid || $this->suggest_fid){
								?>
								<label for="NT-forum">Forum</label>
								<select name="fid">
									<?
									$query2 = "SELECT * FROM forums WHERE no_index != '1' AND invisible <= '$usrrank' ORDER BY cid, title";
									$res2   = mysqli_query($GLOBALS['db']['link'], $query2);
									while($row2 = mysqli_fetch_assoc($res2)) {
										echo '<option value="'.$row2['fid'].'">'.$row2['title'].'</option>';
									}
									?>
								</select>
								<p></p>
								<?
							}
							?>
							
							<input type="text" name="title" maxlength="120" id="NT-title" tabindex="1" placeholder="Topic title" style="width:100%; max-width:600px;"/>
							
							<p></p>
							<textarea name="description" rows="2" tabindex="2" id="NT-desc" class="tagging" placeholder="Subtitle/Description (optional)" style="width:100%; max-width:600px;"></textarea>
							
							<p></p>
							<div id="wmd-input-toolbar"></div>
							<div style="margin:0 6px 3px 0">
								<textarea name="message" id="wmd-input" class="tagging wmd-input" rows="10" tabindex="3" style="width:100%;" onchange="confirm_exit=true;"></textarea>
							</div>
							<div style="text-align:right;">
								Formatting allowed: <a href="/formatting-help" target="_blank">Markdown</a> and basic HTML with restricted attributes. <a href="/formatting-help" target="_blank" class="arrow-link">More info</a>
							</div>
							
							<p></p>
							<div id="reply-opts">
								<ul class="tabbed-nav" style="margin:0 -20px 20px; padding:0 20px;">
									<li class="on"><a href="#" rel="RO-opts">Options</a></li>
									<li><a href="#" rel="RO-preview" onclick="$(this).html('Refresh Preview');">Preview</a></li>
									<li><a href="#" rel="RO-poll">Create a Poll</a></li>
								</ul><br style="clear:left"/>
								<div id="RO-opts" class="opt">
									<label><input type="checkbox" name="disable_emoticons" value="1"/> Disable emoticons</label><br/>
									Subscribe: 
										<label><input type="checkbox" name="subscribe[tid]" value="1"/> Topic</label> <a href="#help" class="tooltip helpinfo" title="e-mail me whenever someone replies to my new topic"><span>?</span></a> &nbsp; 
										<label><input type="checkbox" name="subscribe[pid]" value="1"/> Direct Reply</label> <a href="#help" class="tooltip helpinfo" title="e-mail me whenever someone directly replies to my message"><span>?</span></a>
									<br/>
								</div>
								<div id="RO-preview" class="opt message-text" style="display:none"></div>
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
							
							<div class="buttons" style="margin:20px -20px -20px; padding:20px; border-top:1px solid #CCC; background-color:rgba(0,0,0,.075);">
								<input type="submit" name="do" value="Post Topic" tabindex="4" style="font-weight:bold;"/> 
								<input type="button" value="Cancel" onclick="$('#new-topic-form').hide(); confirm_exit=false;"/> 
							</div>
						</form>
						<?
					}
					?>
				</fieldset>
				
				<?
				if(!$topic_num){
					?>
					<div id="no-stuff" style="display:block; margin:20px;"><big>There are no topics for this forum yet :(</big></div>
					<?
				} else {
					?>
					
					<table border="0" cellpadding="2" cellspacing="0" width="100%" class="topic-list">
						<tr>
							<th>&nbsp;</th>
							<th>Title</th>
							<th><span style="display:none;">Rating</span>&nbsp;</th>
							<th style="text-align:center;">Replies</th>
							<th>Creator</th>
							<th>Last Post</th>
						</tr>
						<?
						
						$res = mysqli_query($GLOBALS['db']['link'], $query);
						while($row = mysqli_fetch_assoc($res)) {
							
							$print_closed = '';
							if($usrrank < $row['closed'] || ($row['closed'] && $usrrank >= 5)) $print_closed = ' class="locked"';
							
							if($row['ratings']) {
								$total = $row['rating'] / $row['ratings'];
								if($total >= .5) $thumbs = '<img src="/bin/img/thumbs-up.png" alt="thumbs up"/>';
								else $thumbs = '<img src="/bin/img/thumbs-down.png" alt="thumbs down"/>';
								$thumbs.= '<span class="thumbs-text">'.$row['ratings'].'</span>';
							} else $thumbs = '&nbsp;';
							
							if($usrlastlogin < $row['last_post']) {
								$lightbulb = '<a href="/forums/?tid='.$row['tid'].'&focus_post=unread" title="unread posts in this topic"><img src="/bin/img/mascot.png" alt="new posts" border="0"/></a>';
							} else {
								$lightbulb = '<img src="/bin/img/mascot-off.png" alt="no new posts" border="0"/>';
							}
							
							$num_replies = $row['posts'] - 1;
							
							if($row['last_post'] == "0000-00-00 00:00:00") {
								$last_post = "";
							} else {
								$last_post = timeSince($row['last_post']).' ago<br/>by '.($row['last_post_usrid'] ? outputUser($row['last_post_usrid'], FALSE) : $row['last_post_author']);
							}
								
							?>
							<tr<?=($row['sticky'] == 1 ? ' class="sticky"' : '')?>>
								<td><?=$lightbulb?></td>
								<td class="topic-title">
									<?=($row['sticky'] ? "STICKY: " : '')?>
									<a href="<?=$this->topicURL($row['tid'])?>"<?=$print_closed?>><?=stripslashes($row['title'])?></a> 
									<?=($row['description'] ? '<small>'.$bb->bb2html($row['description']).'</small>' : '')?>
								</td>
								<td nowrap="nowrap"><?=$thumbs?></td>
								<td style="text-align:center"><?=$num_replies?></td>
								<td><?=($row['usrid'] ? outputUser($row['usrid']) : $row['creator'])?></td>
								<td nowrap="nowrap" class="last-post"><?=$last_post?></td>
							</tr>
							<?
						}
						
					?>
					</table>
				<?
				
				} // end if(topic_num)
				
				?>
			
			<footer>
			
				<?=($p_pagenav ? '<nav class="pgnav"><ul>'.$p_pagenav.'</ul></nav><div style="clear:both; height:20px;"></div>' : '')?>
				<?
				if($fid) {
					?>
					<div id="forum-details">
						<b>Forum #<?=$fid?></b> <span>|</span> 
						Invisible to <?=$this->user_groups[$forum->invisible].($forum->invisible > 0 ? 's and below' : '')?> <span>|</span> 
						Closed to <?=$this->user_groups[$forum->closed].($forum->closed > 0 ? 's and below' : '')?>
					</div>
					<?
				}
				?>	
				<div id="nav-tree"><?=$navtree?></div>
			</footer>
			
		</div><!--#forum-->
		<?
			
	}
	
	function showTopic($tid='', $pg='') {
		
		global $db, $usrid, $usrname, $usrrank, $userprefs, $usrlastlogin, $page;
		
		////////////////
		// SHOW TOPIC //
		////////////////
		
		$bb = new bbcode();
		
		$this->fpost = $_GET['thread'];
		
		if($tid) {
			$query = "SELECT * FROM `forums_topics` where `tid` = '$tid' LIMIT 1";
		} elseif($this->unique_location) {
			$this->location = $this->unique_location;
			$query = "SELECT * FROM `forums_topics` where `location` = '$this->location' LIMIT 1";
		} else {
			echo "Can't show forum topic; insufficient topic scope given (need `tid` or `unique_location`)";
			return;
		}
		if(!$topic = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query))) {
			$this->newTopicForm();
			return;
		}
		
		if(!$tid) $tid = $topic->tid;
		$this->tid = $tid;
		$cmmnt = ($topic->type == "comments" ? TRUE : FALSE);
		$this->topic_url = $this->topicURL($tid);
		
		if($usrrank < $topic->invisible) return;
		
		$posts_unread = array();
		$d_posts = array();
		$i = 0;
		if($this->fpost) $andwhere = "AND (pid='$this->fpost' OR reply_to='$this->fpost')";
		$query = "SELECT * FROM forums_posts WHERE tid='$tid' $andwhere ORDER BY posted";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)) {
			if($row['reply_to']) {
				$d_posts[$row['reply_to']]['replies'][$row['pid']] = $row;
				$d_posts[$row['reply_to']]['replies'][$row['pid']]['n'] = $i++;
			} else {
				$d_posts[$row['pid']] = $row;
				$d_posts[$row['pid']]['n'] = ++$i;
			}
			if($i == 1) $first_pid = $row['pid'];
			$last_pid = $row['pid'];
			if($row['posted'] > $usrlastlogin) $posts_unread[] = $row['pid'];
		}
		$num_posts = $i;
		$num_unread = count($posts_unread);
		
		$forum = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM forums WHERE fid = '".$topic->fid."' LIMIT 1"));
		
		//additional stuff
		if($topic->location){
			if(substr($topic->location, 0, 6) == "group:"){
				//group
				$group_id = substr($topic->location, 6);
				$q = "SELECT * FROM groups WHERE `group_id` = '$group_id' LIMIT 1";
				if(!$groupdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) $page->die_("There was an error fetching group details [group ID # $group_id]");
				$addltitle = ' / <a href="/forums/?location=group:'.$group_id.'">'.$groupdat->name.'</a>';
				//user is a member?
				$q = "SELECT * FROM groups_members WHERE group_id='$group_id' AND usrid='$usrid' LIMIT 1";
				if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $page->die_('<h1>Access Denied</h1>Sorry, you have to be a group member to access this group\'s forums.  <a href="/groups/'.$group_id.'/" class="arrow-right">Join this group</a>');
			} elseif(substr($topic->location, 0, 5) == "post:"){
				//sblog post
				$nid = substr($topic->location, 5);
				$q = "SELECT * FROM posts WHERE `nid` = '$nid' LIMIT 1";
				$ndat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
				$this->users[$ndat->usrid]['status'] = "author";
			}
		}
		
		?>
		<div id="forum" class="topic <?=$topic->type?>">
			
			<input type="hidden" name="tid" value="<?=$tid?>" id="tid"/>
			<?=($num_unread ? '<input type="hidden" name="unreadposts" value="'.implode(",", $posts_unread).',older" id="unreadposts"/>' : '')?>
			
			<div class="forum-dir"><a href="/forums/">Forums</a> / <?=($forum->title ? '<a href="/forums/?fid='.$forum->fid.'">'.$forum->title.'</a> / ' : '').$addltitle?></div>
			<h1><?=$topic->title?></h1>
			<?=($topic->description ? '<div class="forum-description">'.$bb->bb2html($topic->description).'</div>' : "")?>
			
			<header>
				<div class="menu pgnav">
					<ul>
						<li><span><?=($num_posts ? $num_posts : 'No')?> <?=($topic->type == "forum" ? 'Post' : 'Comment').($num_posts == 1 ? '' : 's')?></span></li>
						<li class="newpost"><a href="#topic-reply">Post a Reply</a></li>
						<?
						if($usrid) {
							$q = "SELECT * FROM forums_mail WHERE `usrid` = '$usrid' AND `tid` = '$tid' LIMIT 1";
							if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) {
								$subscribed = TRUE;
							}
						}
						?>
						<li><span><input type="checkbox" <?=($subscribed ? 'checked' : '')?> class="fauxcheckbox" id="subscribe-topic" onclick="forumSubscription('tid', '<?=$tid?>', $(this))"/><label for="subscribe-topic" class="tooltip" title="E-mail me whenever a new topic is created in this forum">Subscribe</label></span></li>
						<?
						if($usrrank >= 5) {
							if($topic->sticky) $uns = 'un';
							else $uns = '';
							?>
							<li>
								<span>
									<select onchange="window.location=this.options[this.selectedIndex].value">
										<option value="">Administer...</option>
										<option value="/forums/action.php?do=edit_topic_details&tid=<?=$tid?>">edit topic details</option>
										<option value="/forums/action.php?do=<?=$uns?>sticky&tid=<?=$tid?>">make <?=$uns?>sticky</option>
										<option value="/forums/action.php?do=close&tid=<?=$tid?>">close preferences</option>
										<?=($usrrank >= 8 ? '<option value="/forums/action.php?do=hide&tid='.$tid.'">access preferences</option><option value="/forums/action.php?do=delete&tid='.$tid.'">delete topic</option>' : '')?>
										<option value="/forums/action.php?move_topic=<?=$tid?>">move topic</option>
									</select>
								</span>
							</li>
							<?
						}
						?>
					</ul>
				</div>
			</header>
				
				<div class="topictags">
					<h3>Topics</h3>
					<div id="tags" class="tags taglist">
						<?
						$_tags = new tags("forums_tags:tid:".$tid);
						$_tags->allow_add = ($usrrank >= 5 || $topic->usrid == $usrid ? true : false); //allow moderators and the topic creator to add tags
						$_tags->allow_rm = ($usrrank >= 5 ? true : "creator"); //allow moderators to rm all tags, users to remove their own
						if($topic->usrid == $usrid && $num_posts == 1){
							//suggest tags if this topic just started (1 post) and the user is the creator
							$_tags->suggest($d_posts[$first_pid]['message']);
						}
						echo $_tags->taglist(0, 0);
						echo $_tags->suggestForm();
						?>
						<div class="clear" style="height:0;"></div>
					</div>
				</div>
				<div class="spacer"></div>
				<?
				
				//poll
				$q = "SELECT * FROM forums_polls WHERE tid='$tid' LIMIT 1";
				if($poll = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
					
					if($_POST['pollopt']) {
						//record ballot
						if(!$usrid && !$_SERVER['REMOTE_ADDR']) $errors[] = "Couldn't record your vote since you're not registered or have no IP address";
						elseif(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM forums_polls_votes WHERE tid='$tid' AND (".($usrid ? "usrid='$usrid' OR " : "")."ip_address = '".$_SERVER['REMOTE_ADDR']."') LIMIT 1"))) $voted = TRUE;
						else {
							$voted = $_POST['pollopt'];
							$q = "INSERT INTO forums_polls_votes (tid, ip_address, usrid, answer) VALUES ";
							foreach($voted as $vote) {
								$q.= "('$tid', '".$_SERVER['REMOTE_ADDR']."', '$usrid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $vote)."'),";
							}
							if(!mysqli_query($GLOBALS['db']['link'], substr($q, 0, -1))) $errors[] = "Couldn't record your vote to the database";
						}
					}
					
					$query = "SELECT * FROM forums_polls_votes WHERE tid='$tid'";
					$res   = mysqli_query($GLOBALS['db']['link'], $query);
					if($total_votes = mysqli_num_rows($res)) {
						$data   = array();
						$voted  = array();
						while($row = mysqli_fetch_assoc($res)) {
							$data[$row['answer']]++;
							if($row['usrid'] == $usrid || $row['ip_address'] == $_SERVER['REMOTE_ADDR']) $voted[] = $row['answer'];
						}
					}
					if(!count($voted)) $voted = FALSE;
					
					if($poll->answer_type == "single") $inptype = "radio";
					else $inptype = "checkbox";
					
					?>
					<div id="poll" class="<?=(!$voted ? 'hideres' : '')?>">
						<h4>Poll Question: <b><?=$poll->question?></b></h4>
						<form action="<?=$this->topic_url?>" method="post">
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
										$thisdata = '<span class="data">'.$pc.'%</span><span class="data" style="font-weight:bold;">'.$data[$i].'</span>';
									} else {
										$pc = 0;
										$thisdata = "No Votes";
										$xpos = "23px";
									}
									?>
									<li>
										<span id="" class="res poll-data"><?=$thisdata?></span>
										<span id="" class="res poll-bg" style="width:<?=$pc?>%;"></span>
										<label>
											<?=(!$voted ? '<input type="'.$inptype.'" name="pollopt[]" value="'.$i.'"/> ' : '')?>
											<?=$opt?> &nbsp; 
											<?=($voted ? (in_array($i, $voted) ? '<span class="res yourvote">Your Vote</span>' : '') : '')?> &nbsp; 
										</label>
									</li>
									<?
									$i++;
								}
								?>
							</ol>
							<big style="float:right; margin-right:5px; color:#888;"><?=$total_votes?> Vote<?=($total_votes != 1 ? 's' : '')?></big>
							<?=(!$voted ? '<input type="submit" name="submit_poll" value="Vote"/> ' : '')?>
							<button type="button" onclick="$('#poll').toggleClass('hideres');">Toggle Results</button>
						</form>
					</div>
					<div class="spacer"></div>
					<?
					
				}
				
				if($num_posts) {
					?>
					<div id="forum-posts">
						<?
						$justposted = ($topic->usrid == $usrid && $num_posts == 1 ? TRUE : FALSE);
						if($justposted && !$cmmnt) {
							$adj = array("shiny", "effulgent", "incandescent", "brilliant", "resplendent", "fantastical", "auspicious", "lascivious", "salacious");
							$rand = rand(0, (count($adj) - 1));
							?>
							<div style="margin:2em 0; padding:0 0 0 6em; background:url(/bin/img/icons/toad_bow_big_r.gif) no-repeat 1.5em center;">
								<b><big>A new discussion thread is born! All hail, <?=$usrname?>!</big></b>
								<div style="margin-top:2px; font-size:14px; line-height:1.2em; color:#666;">
									Now give it more exposure within the site by tagging it with a related <b>Game</b>, <b>Person</b>, <b>Company</b>, <b>Console</b> or other topic.<br/>
									Suggest a new tag by clicking <span style="color:black;"><b>+</b> Add a Tag</a></span> above.
								</div>
							</div>
							<?
						}
						
						if($this->fpost) {
							
							//thread only
							
							?>
							<div class="postnav pgnav">
								<ul>
									<li><span class="down">Showing a single thread in this topic</span></li>
									<li><a href="<?=$this->topicURL($tid)?>">View Full Topic</a></li>
								</ul>
							</div>
							
							<ol id="fpostslist">
							<?
							
							$this->outputPost($d_posts[$this->fpost]);
							
						} elseif($num_unread) {
							
							//unread post mode
							//show all posts in the topic
							
							?>
							<div class="postnav pgnav">
								<ul>
									<li><span class="down"><?=(!$num_unread ? 'There are no unread posts in this topic' : $num_unread.' unread post'.($num_unread != 1 ? 's' : ''))?></span></li>
										<?
										if($num_unread){
											?>
											<li>
												<span>
													<img src="/bin/img/icons/key_ctrl.png" alt="up key" border="0" title="Use Ctrl + up or down"/>
													&nbsp;<span style="color:#444; font-weight:bold; font-size:16px;">+</span>&nbsp;
													<img src="/bin/img/icons/key_up.png" alt="up key" border="0" title="use Ctrl + UP key to go to the previous newest post"/> 
													<img src="/bin/img/icons/key_down.png" alt="down key" border="0" title="use Ctrl + DOWN key to go to the next newest post"/>
												</span>
											</li>
											<li><span>Jump between unread posts</span></li>
											<?
										}
										?>
								</ul>
							</div>
							
							<ol id="fpostslist">
							<?
							
							while(list($pid, $row) = each($d_posts)) {
								$this->outputPost($row);
							}
							
						} else {
							
							?><ol id="fpostslist"><?
							
							while(list($pid, $row) = each($d_posts)) {
								if(!$this->outputPost($row, TRUE)) break;
							}
							
							if($num_posts > $this->posts_per_page) {
								?>
								<div class="postnav pgnav">
									<ul>
										<li><span class="up">Showing Posts 1 - <?=$this->posts_per_page?></span></li>
										<li><a href="#load_posts" onclick="loadPosts('<?=$tid?>');">Show All <?=$num_posts?> Posts</a></li>
									</ul>
								</div>
								<?
							}
						}
						
						?>
						
						<li id="topic-reply" class="postitem"><?=$this->outputReplyForm(0, TRUE)?></li>
						
						</ol><!--#fpostslist-->
						
					</div><!--#forum-posts-->
					<?
					
				} else {
					?>
					<div id="forum-posts" style="border:0 !important; margin:5px 0 !important;">
						<table border="0" cellpadding="0" cellspacing="0" width="100%"></table>
					</div>
					<?
				}
			
			?>
			<footer>
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td id="forum-details" nowrap="nowrap">
							<b>Topic #<?=$tid?></b> <span>&middot;</span> 
							<?
							if($private_group) echo "Visible to Group Members";
							else echo "Invisible to ".$this->user_groups[$topic->invisible].($topic->invisible > 0 ? 's and below' : '');
							?> <span>&middot;</span> 
							<?
							if($group_topic) echo "Open to Group Members";
							else echo "Closed to ".$this->user_groups[$topic->closed].($topic->closed > 0 ? 's and below' : '');
							?>
						</td>
						<td width="100%" style="text-align:center; color:#BBB;">
							<?
							$query = "SELECT * FROM forums_topics WHERE last_post < '$topic->last_post' AND invisible <= '$usrrank' ORDER BY last_post DESC";
							$res = mysqli_query($GLOBALS['db']['link'], $query);
							if(!mysqli_num_rows($res)) echo '<span id="polder" class="arrow-left">older</span>';
							while($row = mysqli_fetch_assoc($res)) {
								if($row['location']) {
									$handle = explode(":", $row['location']);
									if($handle[0] == "group"){
										if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM groups_members WHERE usrid='$usrid' AND group_id='$handle[1]' LIMIT 1"))) continue;
									}
								}
								echo '<a href="'.$this->topicURL($row['tid']).'" title="'.htmlSC($row['title']).'" id="polder" class="arrow-left tooltip">older</a>';
								break;
							}
							echo " &middot; ";
							
							$query = "SELECT * FROM forums_topics WHERE last_post > '$topic->last_post' AND invisible <= '$usrrank' ORDER BY last_post ASC";
							$res = mysqli_query($GLOBALS['db']['link'], $query);
							if(!mysqli_num_rows($res)) echo '<span class="arrow-right">newer</span>';
							while($row = mysqli_fetch_assoc($res)) {
								if($row['location']) {
									$handle = explode(":", $row['location']);
									if($handle[0] == "group"){
										if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM groups_members WHERE usrid='$usrid' AND group_id='$handle[1]' LIMIT 1"))) continue;
									}
								}
								echo '<a href="'.$this->topicURL($row['tid']).'" title="'.htmlSC($row['title']).'" class="arrow-right tooltip">newer</a>';
								break;
							}
							?>
						</td>
						<td id="nav-tree"><?=$this->makeNavTree()?></td>
					</tr>
				</table>
			</footer>
			
		</div><!--#forum-->
		<?
	}
	
	/////////////////
	// OUTPUT POST //
	/////////////////
	
	function outputPost($row, $ret=FALSE, $justposted=FALSE) {
		
		// output a post, including all threaded replies
		// @var $row array row data from db
		// @var $ret if true, return false when max $posts_per_page is reached
		// @var $justposted just posted via ajax or something
		
		global $usrid, $usrrank, $unread, $this_loc;
		
		if($row['usrid'] != $usrid) $justposted = FALSE;
		
		$this->postnum++;
		if($ret && $this->postnum > $this->posts_per_page) return false;
		
		$row['message'] = stripslashes($row['message']);
		$premessage = $row['message'];
		
		$bb = new bbcode();
		$bb->text = $row['message'];
		$bb->headings_offset = 4;
		$row['message'] = $bb->bb2html();
		
		if(!$row['usrid']) $row['usrid'] = 0;
			
		//get user data
		$this->assignUserDetails($row['usrid']);
		
		if(!$row['rating_weighted']) $row['rating_weighted'] = 0;
			
		$new = FALSE;
		if($this->getLastLogin() < $row['posted']) {
			$new = TRUE;
			$unread++;
		}
		
		$output_dt = convertTimeZone($row['posted'], $user->time_zone);
		$output_dt = formatDate($output_dt, 10);
		
		if(!$row['reply_to']) {
			//a new thread is beginning; output reply space for previous thread
			//if($this->thread && $usrid) echo '<li id="reply-'.$this->thread.'" class="postitem reply" style="display:none;">You\'re replying to <a href="#p'.$this->thread.'">'.$this->threadauthor.'</a></li>';
			$this->thread = $row['pid'];
			$this->threadauthor = $this->users[$row['usrid']]['username'];
		}
		
		$plink = ($this->topic_url ? $this->topic_url : $this->topicURL($row['tid']));
		$plink = str_replace("#forum", "", $plink);
		$plink.= '&thread='.($row['reply_to'] ? $row['reply_to'] : $this->thread).'#p'.$row['pid'];//'/forums/?tid='.$row['tid'].'&thread='.($row['reply_to'] ? $row['reply_to'] : $this->thread).'#p'.$row['pid'];
		
		$class = "";
		$class.= ($row['reply_to'] ? ' reply' : ' thread');
		$class.= ($new ? ' unread' : '');
		$class.= ($justposted ? ' justposted' : '');
		$class.= ($row['rating_weighted'] < -7 ? ' belowth' : ' aboveth');
		
		$style = "";
		if($row['rating_weighted'] < -5) $style.= "opacity:.3;";
		elseif($row['rating_weighted'] < 0) $style.= "opacity:.6;";
		
		?>
		<li id="p<?=$row['pid']?>" class="postitem st-<?=$this->users[$row['usrid']]['status'].$class?>" style="<?=$style?>">
			
			<div class="meta meta-side">
				<ul>
					<li class="poster">
						<a href="<?=$this->users[$row['usrid']]['url']?>">
							<?=$this->users[$row['usrid']]['avatar']?>
							<?=$this->users[$row['usrid']]['username']?>
						</a>
					</li>
					<li class="handle"><?=$this->users[$row['usrid']]['handle']?></li>
					<li class="posted">
						<a href="<?=$plink?>" title="Permanent link to this post"><time datetime="<?=$row['posted']?>" title="[Permanent link] Posted <?=substr($row['posted'], 11, 5).' '.date("T").' GMT '.date("P").'">'.date("D F j, Y", strtotime($row['posted']))?></time></a> <span class="sym">&infin;</span>
					</li>
				</ul>
			</div>
			
			<article class="message message-col" id="message-<?=$row['pid']?>"><?=$row['message']?></article>
			<div class="message-edit message-col" id="edit-<?=$row['pid']?>" style="display:none;"></div>
			
			<?=($row['rating_weighted'] < -7 ? '<div class="belowth-message"><i>This post has been downranked into the pit of despair.</i> <a href="#showatyourperil" onclick="$(this).parent().hide().closest(\'li\').removeClass(\'belowth\');">Show Comment</a></div>' : '')?>
			<?
			/*if($justposted){
				$_tags = new tags("forums_tags:pid:".$row['pid']);
				$_tags->allow_add = TRUE;
				$_tags->allow_rm = TRUE;
				?>
				<div id="t<?=$_tags->i?>-taglist" class="taglist">
					<?=$_tags->suggest($premessage, $_tags->tagarr("forums_tags:tid:".$row['tid']))?>
					<?=$_tags->taglist().$_tags->suggestForm()?>
					<div style="clear:left; height:0;"></div>
				</div>
				<?
			} else {
				$_tags = new tags("forums_tags:pid:".$row['pid']);
				$_tags->allow_rm = "creator";
				if($_tags->numTags()){
					?>
					<div id="t<?=$_tags->i?>-taglist" class="taglist taglist2"><?=$_tags->taglist()?><div class="clear" style="height:0;"></div></div>
					<?
				}
			}*/
			
			?>
			
			<div class="message-ops">
	   		<div class="hrate hearts-rating<?=($usrid == $row['usrid'] ? ' disabled' : '')?>">
   				<a href="#rate_post" title="Rate this post as &lt;b&gt;poor quality&lt;/b&gt; / &lt;b&gt;irrelevant&lt;/b&gt;" class="hrate-minus tooltip preventdefault" onclick="fRatePost(<?=$row['pid']?>,0,$(this).parent());">&minus;</a>
   				<span class="rating" title="<?=($row['ratings'] ? "Post quality is ".$row['rating']."% based on ".$row['ratings']." rating".($row['ratings'] != 1 ? 's' : '')." [".$row['rating_weighted']."]" : 'Not yet rated')?>"><?=($row['ratings'] ? $this->heartRating($row['rating_weighted']) : $this->heartRating(''))?></span>
   				<a href="#rate_post" title="Rate this post as &lt;b&gt;good quality&lt;/b&gt; / &lt;b&gt;relevant&lt;/b&gt;" class="hrate-plus tooltip preventdefault" onclick="fRatePost(<?=$row['pid']?>,1,$(this).parent());">+</a>
	   			<div class="loading"></div>
	   		</div>
				<ul>
					<li class="reply message-reply message-op" title="Reply to this thread" data-op="reply" data-user="<?=$this->users[$row['usrid']]['username']?>" data-pid="<?=$row['pid']?>">Reply</li>
			   	<?=($usrrank >= 5 || $row['usrid'] == $usrid ? '<li class="edit message-op" data-op="edit" data-pid="'.$row['pid'].'">Edit</a></li>' : '')?>
					<?=($usrrank >= 5 ? '<li class="delete message-op" data-op="delete" data-pid="'.$row['pid'].'">Delete</a></li>' : '')?>
			  </ul>
			</div>
			
			<div class="status-ribbon" title="<?=$status?>" style="background-image:url(/bin/img/forum_status_<?=$status?>_left.png);"></div>
			
			<div class="clear"></div>
		</li>
			<?
			
			if($row['replies']){
				foreach($row['replies'] as $pid => $row2){
					$this->outputPost($row2, FALSE);
				}
			}
		
		return true;
		
	}
	
	///////////////////////
	// OUTPUT REPLY FORM //
	///////////////////////
	
	function outputReplyForm($i=0, $hide=FALSE, $textinp='', $newtopic=FALSE) {
		
		//var $i iteration must increase iteration to prevent conflicts
		//var $hide hide the form initally ("click to reply")
		//var $textinp an intial value for the textarea
		//var $newtopic if true, inp a form populated with values from $this->suggest
		
		global $usrid;
		
		if(!$usrid) $usrid = 0;
		
		$this->assignUserDetails($usrid);
		
		if($textinp) {
			preg_match("/PID:([0-9]+)/", $textinp, $matches);
			if($matches){
				$textinp = str_replace($matches[0], "", $textinp);
				if($pid = $matches[1]){
					$q = "SELECT * FROM forums_posts WHERE pid='$pid' LIMIT 1";
					if($row2 = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
						$textinp.= '[quote]'.$row2['message'].'[/quote]';
					}
				}
			}
		}
		
		?>
		<div id="fmsg-<?=$i?>-form">
			<div class="meta meta-side">
				<ul>
					<li class="poster">
						<a href="<?=$this->users[$usrid]['url']?>">
							<?=$this->users[$usrid]['avatar']?>
							<?=$this->users[$usrid]['username']?>
						</a>
					</li>
					<li class="handle"><?=$this->users[$usrid]['handle']?></li>
					<li class="posted"><?=date("D F j, Y")?></li>
				</ul>
			</div>
			<div class="message message-col">
				<?
				if(!$usrid){
					echo '<div style="font-size:14px;"><big><a href="/login.php">Log in</a> to post a reply.</big><p></p>Don\'t have an account? <a href="/register.php">Register</a> in about a minute.</div>';
				} else {
					?>
					<form action="/forums/action.php" method="post" data-formaction="<?=($newtopic ? "newtopic" : "reply")?>">
						<?
						if($newtopic){
							//output a form with given $this->suggest variables
							?>
							<input type="hidden" name="do" value="Post Topic"/>
							<input type="hidden" name="location" value="<?=$this->location?>"/>
							<input type="hidden" name="location_unique" value="<?=($this->unique_location ? '1' : '')?>"/>
							<?
							foreach($this->suggest as $key => $val){
								if(is_array($val)) {
									foreach($val as $v){
										echo '<input type="hidden" name="'.$key.'[]" value="'.htmlSC($v).'"/>';
									}
								} else {
									echo '<input type="hidden" name="'.$key.'" value="'.htmlSC($val).'"/>';
								}
							}
						} else {
							?>
							<input type="hidden" name="_do" value="post_reply"/>
							<input type="hidden" name="tid" value="<?=$this->tid?>"/>
							<input type="hidden" name="reply_to" value="" id="input-replyto"/>
							<div id="label-replyto"></div>
							<?
						}
						?>
						<div class="message-compose">
							<div id="wmd-input-toolbar" style="height:1px; overflow:hidden;"></div>
							<textarea name="message" id="wmd-input" class="tagging wmd-input autosize forum-reply-message" tabindex="1" placeholder="Your reply here" onchange="confirm_exit=true;" onfocus="$('#wmd-input-toolbar').animate({height:'25px'}, 100);"><?=$textinp?></textarea>
						</div>
						<div class="message-preview"></div>
						
						<div class="spacer" style="height:10px"></div>
						
						<div class="opts" style="float:left"><?=($group_topic ? '<label><input type="checkbox" name="" value="1" id="fmsg-'.$i.'-dontemailgroup"/> Don\'t e-mail group members about this post</label>' : '')?></div>
						<div class="buttons" style="text-align:right;">
							<button type="button" tabindex="2" id="fmsg-<?=$i?>-prevbutton" onclick="togglePreview(<?=$i?>);">Preview</button> 
							<button type="submit" class="submit" tabindex="3" onclick="confirm_exit=false;" style="font-weight:bold;"><?=($newtopic ? 'Submit' : 'Submit Reply')?></button>
						</div>
					
					</form>
					<?
				}
				?>
			</div><!--.message-->
		</div>
		<?
	}
	
	function newTopicForm() {
		
		global $usrid;
		
		if(!$this->suggest['type'] || ($this->suggest['type'] != "forum" && $this->suggest['type'] != "comments")) $this->suggest['type'] = "forum";
		
		?>
		<div id="forum" class="topic <?=$this->suggest['type']?>">
			<h2 class="startthediscussion">Start the Discussion</h2>
			<div id="forum-posts">
				<ol id="fpostslist">
					<li class="postitem"><? $this->outputReplyForm(0, TRUE, "", TRUE); ?></li>
				</ol>
			</div>
		</div>
		<?
		
		return;
		
	}
	
	function topicURL($tid, $row='') {
		if(!$row){
			$q = "SELECT * FROM forums_topics WHERE tid='".mysqli_real_escape_string($GLOBALS['db']['link'], $tid)."' LIMIT 1";
			$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
		}
		if($loc = $row['location']){
			if(strstr($loc, "post:")){
				$nid = substr($loc, 5);
				$url = "/sblog/$nid/#forum";
			}
		}
		if(!$url) $url = '/forums/?tid='.$tid;
		return $url;
	}
	
	function getUserValue($user='') {
		global $db, $usrid, $usrrank;
		if($user == $usrid || !$user) return $usrrank; //given value is logged-in user
		else {
			$query = "SELECT rank FROM users WHERE usrid='$user' LIMIT 1";
			$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query));
			return $dat->rank;
		}
	}
	
	function getLastLogin($uid='') {
		global $db, $usrlastlogin;
		if($uid) {
			$query = "SELECT previous_activity FROM users WHERE usrid='$user' LIMIT 1";
			$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query));
			if($dat->previous_activity) return $dat->previous_activity;
			else return date("Y-m-d H:i:s");
		} else return ($usrlastlogin ? $usrlastlogin : date("Y-m-d H:i:s"));
	}
	
	function isModerator($user='') {
		global $usrid;
		if(!$user) $user = $usrid;
		$query = "SELECT * FROM `forums_admins` WHERE `user` = '$user' AND `authority` != '' LIMIT 1"; // tru for mod or admin
		if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query))) return TRUE;
		else return FALSE;
	}
	
	function isAdmin($user='') {
		global $usrid;
		if(!$user) $user = $usrid;
		$query = "SELECT * FROM `forums_admins` WHERE `user` = '$user' AND `authority` = 'admin' LIMIT 1";
		if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query))) return TRUE;
		else return FALSE;
	}
	
	function numberOfTopics($fid='') {
		global $usrrank;
		if($fid) $query = "SELECT * FROM forums_topics WHERE fid='".$fid."' AND invisible <= '$usrrank'";
		else $query = "SELECT * FROM `forums_topics` WHERE `location`='".$this->getThisLocation()."' AND `invisible` < '$uval'";
		return mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query));
	}
	
	function numberOfPosts($tid='', $pg='') {
		if($tid) {
			$query = "SELECT * FROM `forums_posts` WHERE `tid` = '$tid' AND `invisible` < ".$this->getUserValue();
			$res = mysqli_query($GLOBALS['db']['link'], $query);
			return mysqli_num_rows($res);
		} elseif($this->associate_tag) {
			$query = "SELECT * FROM forums_tags LEFT JOIN forums_topics USING (tid) WHERE tag='".$this->associate_tag."' AND invisible < ".$this->getUserValue();
			if($dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query))) {
				$q = "SELECT * FROM forums_posts WHERE `tid`='".$dat->tid."' AND `invisible` < ".$this->getUserValue();
				return mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q));
			}
		} else {
			$query = "SELECT posts FROM forums_topics WHERE `location` = '".$this->getThisLocation()."' LIMIT 1";
			$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query));
			return $dat->posts;
		}
	}
	
	function newPosts() {
		global $usrlastlogin, $uval;
		if(!$usrlastlogin) $usrlastlogin = $this->getLastLogin();
		if(!$uval) $uval = $this->getUserValue();
		if($this->associate_tag) {
			$query = "SELECT * FROM forums_tags LEFT JOIN forums_topics USING (tid) WHERE tag='".$this->associate_tag."' AND last_post > '$usrlastlogin' AND invisible < '$uval' LIMIT 1";
			if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query))) {
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
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)) {
			if($row['fid']) $fid = $row['fid'];
			$t[$row['tid']] = 0;
			$date[$row['tid']] = '0000-00-00 00:00:00';
			$query2 = "SELECT * FROM `forums_posts` WHERE tid='".$row['tid']."' ORDER BY `posted` DESC";
			$res2 = mysqli_query($GLOBALS['db']['link'], $query2);
			while($row2 = mysqli_fetch_assoc($res2)) {
				$t[$row['tid']]++;
				if($t[$row['tid']] == 1) {
					$date[$row['tid']] = $row2['posted'];
					$usrid[$row['tid']] = $row2['usrid'];
				}
			}
		}
		foreach(array_keys($t) as $tid) {
			$query = "UPDATE forums_topics SET `posts` = '$t[$tid]', `last_post` = '$date[$tid]', `last_post_usrid` = '$usrid[$tid]' WHERE `tid` = '$tid'";
			if(!mysqli_query($GLOBALS['db']['link'], $query)) echo "Error: couldn't update forum post count<br/>";
		}
		
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
				<option value="new">new posts</option>';
		$q = "SELECT * FROM forums_categories ORDER BY `sort` ASC";
		$res = mysqli_query($GLOBALS['db']['link'], $q);
		while($row = mysqli_fetch_assoc($res)) {
			$navtree.= '</optgroup><optgroup label="'.$row['category'].'">';
			$q2 = "SELECT * FROM forums WHERE cid='$row['cid']' AND `invisible` <= ".$usrrank;
			$res2 = mysqli_query($GLOBALS['db']['link'], $q2);
			while($row2 = mysqli_fetch_assoc($res2)) {
				$navtree.= '<option value="?fid='.$row2['fid'].'">'.$row2['title']."</option>\n";
			}
		}
		$navtree.= '</optgroup></select>';
		return $navtree;
	}
	
	function getLastForumInfo($fid) {
		$q = "SELECT last_post, last_post_usrid FROM forums_topics WHERE fid='".mysqli_real_escape_string($GLOBALS['db']['link'], $fid)."' ORDER BY last_post DESC LIMIT 1";
		return mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
	}
	
	function subscription($arr, $rm = TRUE) {
		
		//var $arr keys: fid, tid, pid (thread)
		
		global $usrid;
		
		if($fid = $arr['fid']){
			$q = "SELECT * FROM forums_mail WHERE usrid='$usrid' AND fid='".mysqli_real_escape_string($GLOBALS['db']['link'], $fid)."' LIMIT 1";
			if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $q2 = "INSERT INTO forums_mail (usrid, fid) VALUES ('$usrid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $fid)."');";
			elseif($rm) $q2 = "DELETE FROM forums_mail WHERE usrid='$usrid' AND fid='".mysqli_real_escape_string($GLOBALS['db']['link'], $fid)."' LIMIT 1;";
			if($q2) { if(!mysqli_query($GLOBALS['db']['link'], $q2)) return false; else return true; }
		} elseif($tid = $arr['tid']){
			$q = "SELECT * FROM forums_mail WHERE usrid='$usrid' AND tid='".mysqli_real_escape_string($GLOBALS['db']['link'], $tid)."' LIMIT 1";
			if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $q2 = "INSERT INTO forums_mail (usrid, tid) VALUES ('$usrid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $tid)."');";
			elseif($rm) $q2 = "DELETE FROM forums_mail WHERE usrid='$usrid' AND tid='".mysqli_real_escape_string($GLOBALS['db']['link'], $tid)."' LIMIT 1;";
			if($q2){ if(!mysqli_query($GLOBALS['db']['link'], $q2)) return false; else return true; }
		} elseif($pid = $arr['pid']){
			$q = "SELECT * FROM forums_mail WHERE usrid='$usrid' AND pid='".mysqli_real_escape_string($GLOBALS['db']['link'], $pid)."' LIMIT 1";
			if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $q2 = "INSERT INTO forums_mail (usrid, pid) VALUES ('$usrid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $pid)."');";
			elseif($rm) $q2 = "DELETE FROM forums_mail WHERE usrid='$usrid' AND pid='".mysqli_real_escape_string($GLOBALS['db']['link'], $pid)."' LIMIT 1;";
			if($q2){ if(!mysqli_query($GLOBALS['db']['link'], $q2)) return false; else return true; }
		} else return false;
		
	}
	
	function heartRating($r=''){
		if($r < -7 || $r === '') $r = '-e-e-e';
		elseif($r < -5) $r = '-h-e-e';
		elseif($r < 0) $r = '-f-e-e';
		elseif($r < 1) $r = '-f-h-e';
		elseif($r < 5) $r = '-f-f-e';
		elseif($r < 21) $r = '-f-f-h';
		else $r = '-f-f-f';
		$r = str_replace('-f', '<img src="/bin/img/heart.png"/>', $r);
		$r = str_replace('-h', '<img src="/bin/img/heart.5.png"/>', $r);
		$r = str_replace('-e', '<img src="/bin/img/heart.0.png"/>', $r);
		return $r;
	}
	
	function assignUserDetails($usrid=''){
		
		if(!$usrid) $usrid = 0;
		
		// Check if user details already assigned to the class
		if($this->users[$usrid]['username']) return $this->users[$usrid];
		
		$user = new user($usrid);
		$user->getDetails();
		
		$status = '';
		if($user->rank < 4) $status = "regular";
		elseif($user->rank == 4) $status = "vip";
		elseif($user->rank == 5) $status = "mod";
		else $status = "staff";
		
		$this->users[$user->id] = array(
			'username' => $user->username,
			'avatar' => $user->avatar(),
			'handle' => $user->handle,
			'url' => $user->url,
			'status' => $status,
		);
		
		return $this->users[$user->id];
		
	}

}