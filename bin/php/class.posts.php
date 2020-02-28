<?
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php";

$image_default_layouts = array("1"=>"1", "2"=>"1x1", "3"=>"1x2", "4"=>"2x2", "5"=>"1x2x2", "6"=>"3x3", "7"=>"1x3x3", "8"=>"2x3x3", "9"=>"3x3x3", "10"=>"1x3x3x3");

class posts {
	
	var $max = 20; 								// maximum posts per page
	var $pg  = 1;
	var $rating_threashhold = -7; // a post shouldn't be displayed if its weighted rating is less than this figure
	
	// Start a new session by defining $this->query with a db table query
	
	public function __set($name, $value){
		if($name == "query"){
			$this->q = $value;
			$this->num_posts = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $this->q));
		}
		else $this->{$name} = $value;
	}
	
	function parseParams(){
		
		if($this->query_params['datetime']) unset($this->query_params['datetime']);
		if(ctype_digit($this->query_params['date']['y']) && strlen($this->query_params['date']['y']) == 4){
			$this->query_params['datetime'] = $this->query_params['date']['y'] . "-".
			($this->query_params['date']['m'] ? $this->query_params['date']['m']."-".
			($this->query_params['date']['d'] ? $this->query_params['date']['d'] : '') : '');
		}
		
		if(ctype_digit($this->query_params['max']) && $this->query_params['max'] <= 20) $this->max = $this->query_params['max'];
		if($this->query_params['page']) $this->pg = $this->query_params['page'];
		
	}
	
	function buildQuery(){
		if($this->query_params['privacy'] != "private") $qs_privacy = "public";
		$q = "SELECT * FROM ";
		if(is_array($this->query_params['tags'])){
			$q.= "posts_tags LEFT JOIN posts USING (nid) WHERE (";
			foreach($this->query_params['tags'] as $tag) $q.= "`tag` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $tag)."' OR ";
			$q = substr($q, 0, -4).") AND ";
		} else $q.= "posts WHERE ";
		if($this->query_params['query'] = trim($this->query_params['query'])) $q.= "(`description` LIKE '%".mysqli_real_escape_string($GLOBALS['db']['link'], $this->query_params['query'])."%' OR `content` LIKE '%".mysqli_real_escape_string($GLOBALS['db']['link'], $this->query_params['query'])."%') AND ";
		$q.= "`privacy` = '".$qs_privacy."' AND `pending` " . (!$this->query_params['pending'] ? "!= '1'" : "= 1");
		if(isset($this->query_params['archive'])) $q.= " AND `archive` ".($this->query_params['archive'] ? "= 1" : "!= 1");
		if($this->query_params['category'] == "blog" OR $this->query_params['category'] == "public") $q.= " AND `category` = '".$this->query_params['category']."'";
		else $q.= " AND `category` != 'draft'";
		if($this->query_params['user']){
			$query = "SELECT usrid FROM users WHERE username = '".mysqli_real_escape_string($GLOBALS['db']['link'], $this->query_params['user'])."' LIMIT 1";
			if($userdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $query))) $q.= " AND usrid = '".$userdat->usrid."'";
		}
		if($this->query_params['datetime']){
			if(preg_match("/\d{4}-\d*-?\d*/", $this->query_params['datetime'])) $q.= " AND `posts`.`datetime` LIKE '".$this->query_params['datetime']."%'";
		}
		if(is_array($this->query_params['post_type_not'])){
			foreach($this->query_params['post_type_not'] as $type_not) $q.= " AND `post_type` != '".mysqli_real_escape_string($GLOBALS['db']['link'], $type_not)."'";
		}
		if(is_array($this->query_params['attachment_not'])){
			foreach($this->query_params['attachment_not'] as $att_not) $q.= " AND `attachment` != '".mysqli_real_escape_string($GLOBALS['db']['link'], $att_not)."'";
		}
		$orders_by = array("date_asc" => "`posts`.`datetime` ASC", "rating" => "rating_weighted DESC");
		$order_by = $orders_by[$this->query_params['sort']];
		if(!$order_by) $order_by = "`posts`.`datetime` DESC";
		$q.= " ORDER BY ".$order_by;
		$this->query = $q;//echo $q;
	}
	
	function postsList($listattr=''){
		
		// @param $listattr str directions and options for this postslist [ open_archived ]
		
		global $usrid;
		
		/* Navigation */
		
		$pg = $this->pg ? $this->pg : 1;
		if(!is_numeric($this->max) || $this->max < 5 || $this->max > 20) $this->max = 20;
		$pgs = ceil($this->num_posts / $this->max);
		
		if($pgs > 1){
			
			$qp = $this->query_params;
			unset($qp['max']);
			unset($qp['tags']);
			$qp['page'] = $pg;
			$qs = http_build_query($qp);
			
			$pgnav =
				'<nav>
					<h6>Page <b>'.$pg.'</b> of <b>'.$pgs.'</b></h6>
					<ul>'.
						'<li class="prev">'.($pg > 1 ? '<a href="/posts/handle.php?'.str_replace("page=$pg", "page=".($pg - 1), $qs).'" title="Previous page" class="postsnavlink arrow-left"><span>Previous</span></a>' : '<span class="arrow-left"><span>Previous</span></span>').'</li>'.
						'<li class="next">'.($pg != $pgs ? '<a href="/posts/handle.php?'.str_replace("page=$pg", "page=".($pg + 1), $qs).'" title="Next page" class="postsnavlink arrow-right"><span>Next</span></a>' : '<span class="arrow-right"><span>Next</span></span>').'</li>'.
					'</ul>
				</nav>';
		}
		
		$catg = $this->category;
		if($catg == "blogs") $catg = "blog";
		
		$postname = "Sblog Post";
		if($catg == "news") $postname = "Article";
		elseif($catg == "blog") $postname = "Blog";
		$catgs = array(
			"posts" => "All Posts",
			"news" => "News",
			"blog" => "Blogs"
		);
		
		if(is_array($this->query_params['tags'])){
			foreach($this->query_params['tags'] as $tag) $form_tags.= '<input type="hidden" name="tags[]" value="'.mysqli_real_escape_string($GLOBALS['db']['link'], $tag).'"class="dontget"/>';
		}
		for($n = 1999; $n <= date("Y"); $n++) $form_year.= '<option value="'.$n.'"'.(substr($this->query_params['datetime'], 0, 4) == $n ? ' selected="selected"' : '').'>'.$n.'</option>';
		$months = array('01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December');
		$sel_month = $this->query_params['datetime'] ? substr($this->query_params['datetime'], 5, 2) : '';
		for($n = 1; $n <= 12; $n++) $form_month.= '<option value="'.sprintf("%02d", $n).'"'.($sel_month == sprintf("%02d", $n) ? ' selected="selected"' : '').'>'.$months[sprintf("%02d", $n)].'</option>';
		for($n = 1; $n <= 31; $n++) $form_day.= '<option value="'.sprintf("%02d", $n).'"'.(substr($this->query_params['datetime'], 8, 2) == $n ? ' selected="selected"' : '').'>'.$n.'</option>';
		
		$types = array("general"=>"Articles", "news"=>"Gaming News", "preview"=>"Previews/Impressions", "review"=>"Reviews", "blog"=>"Blogs", "playlog"=>"Play Logs", "quote"=>"Quotes");
		if(!$this->query_params['post_type_not']) $this->query_params['post_type_not'] = array();
		foreach($types as $type => $f_type){
			$form_posttype.= '<dd><label class="togglechecks"><input type="checkbox"'.(!in_array($type, $this->query_params['post_type_not']) ? ' checked="checked"' : '').'/> '.$f_type.'</label><input type="checkbox" name="post_type_not[]" value="'.$type.'"'.(in_array($type, $this->query_params['post_type_not']) ? ' checked="checked"' : '').' style="display:none"/></dd>';
		}
		
		$attachments = array("image"=>"Pictures", "video"=>"Videos", "audio"=>"Music", "link"=>"Links", "tweet"=>"Tweets");
		if(!$this->query_params['attachment_not']) $this->query_params['attachment_not'] = array();
		foreach($attachments as $type => $f_type){
			$form_attachment.= '<dd><label class="togglechecks"><input type="checkbox"'.(!in_array($type, $this->query_params['attachment_not']) ? ' checked="checked"' : '').'/> '.$f_type.'</label><input type="checkbox" name="attachment_not[]" value="'.$type.'"'.(in_array($type, $this->query_params['attachment_not']) ? ' checked="checked"' : '').' style="display:none"/></dd>';
		}
		
		$this->nav = '<aside>'.$pgnav.'</aside>';
		$this->nav_with_legend =
			'<aside>
				'.$pgnav.'
				<h5>'.number_format($this->num_posts).' '.$postname.''.($this->num_posts != 1 ? 's' : '').'</h5>
				<div class="opts">
					<a onclick="$(\'#postsqueryparams\').slideToggle()">Filter Results</a> | 
					<a href="/posts/manage.php?action=newpost" title="Make a new News, Blog, or Content post" class="addpost"><b style="font-size:14px;">+</b> <u>New Post</u></a>
					<form id="postsqueryparams" onsubmit="return filterPosts()" style="display:none">
						
						<dl>
							<dt>Post types</dt>
							<dd>
								<select name="category">
									<option value="">All</option>
									<option value="blog"'.($this->query_params['category'] == "blog" ? ' selected="selected"' : '').'>Blogs</option>
									<option value="public"'.($this->query_params['category'] == "public" ? ' selected="selected"' : '').'>News, Articles, and Content</option>
								</select>
							</dd>
							'.$form_posttype.'
							<dt>Media</dt>
							'.$form_attachment.'
							<dt>Date</dt>
							<dd>
								<select name="date[y]">
									<option value="">'.($this->query_params['datetime'] ? 'Any Year' : 'Year...').'</option>
									'.$form_year.'
								</select> 
								<select name="date[m]">
									<option value="">'.($this->query_params['datetime'] ? 'Any Month' : 'Month...').'</option>
									'.$form_month.'
								</select> 
								<select name="date[d]">
									<option value="">'.($this->query_params['datetime'] ? 'Any Day' : 'Day...').'</option>
									'.$form_day.'
								</select> 
							</dd>
							<dt>Author</dt>
							<dd><input type="text" name="user" value="'.htmlSC($this->query_params['user']).'" placeholder="Input a user name" style="width:227px;"/></dd>
							<dt>Sort</dt>
							<dd>
								<select name="sort" style="width:227px;">
									<option value="">Date posted (newest first)</option>
									<option value="date_asc"'.($this->query_params['sort'] == "date_asc" ? ' selected="selected"' : '').'>Date posted (oldest first)</option>
									<option value="rating"'.($this->query_params['sort'] == "rating" ? ' selected="selected"' : '').'>Rating</option>
								</select>
							</dd>	
							<dt>Search Term</dt>
							<dd><input type="text" name="query" value="'.htmlSC($this->query_params['query']).'" style="width:227px;"/></dd>
							<dt>Other</dt>
							<dd><label class="togglechecks"><input type="checkbox"'.($this->query_params['archive'] != '0' ? ' checked="checked"' : '').'/> Include archived posts</label><input type="checkbox" name="archive" value="0"'.($this->query_params['archive'] == '0' ? ' checked="checked"' : '').' style="display:none"/></dd>
							
							<input type="hidden" name="max" value="'.$this->max.'" class="dontget"/>
							'.$form_tags.'
							
							<dd><input type="submit" value="Apply Filter"/></dd>
						</dl>
					</form>
				</div>
			</aside>';
		
		/* LIST */
		
		if(!$rows){
			$rows_nids = array();
			$this->q.= " LIMIT ".(($this->pg - 1) * $this->max).", ".$this->max.";";//echo $this->q;
			$res = mysqli_query($GLOBALS['db']['link'], $this->q);
			while($row = mysqli_fetch_assoc($res)){
				if(in_array($row['nid'], $rows_nids)) continue;
				$rows_nids[] = $row['nid'];
				$rows[] = $row;
			}
		}
		
		if(!count($rows)) return $this->nav_with_legend . '<div class="postslist"><i class="postslistempty">No posts to display</i></div>';
		
		$ret = 
		'<div class="postslist">';
			$i = 0;
			foreach($rows as $row) {
				
				$i++;
				
				$post = new post($row);
				
				$date = substr($row['datetime'], 0, 10);
				$date = str_replace("-", "/", $date);
				
			/*$rootlink = "posts";
				$postedto = "Content Archive";
				if($row['category'] == "public" && !$row['archive']) {
					if($_GET['category'] == "news") $rootlink = "news";
					$postedto = "";
				} elseif($row['category'] == "blog") {
					$rootlink = "~".$row['usrname']."/blog";
					$postedto = $row['usrname']."'s Blog";
				}*/
				
				/*$typeimg = $row['type'];
				if($typeimg == "gallery") $typeimg = "image";
				if($row['type2'] == "review") $typeimg = "review";*/
				
				/*$q = "SELECT `question` FROM posts_polls WHERE nid='".$row['nid']."' LIMIT 1";
				if($poll = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
					if(strlen($poll['question']) > 100) $poll['question'] = substr($poll['question'], 0, 96)."...";
				}'.($poll['question'] ? '<li class="poll">Poll: <a href="'.$post->url.'#poll">'.$poll['question'].'</a></li>' : '').'*/
				
				if(!$row['rating_weighted']) $row['rating_weighted'] = 0;
				
				$closed = "";
				$class = "";
				if($row['rating_weighted'] < $this->rating_threashhold){
					$closed = "closed";
					$class = "belowth";
				} else $class = 'aboveth';
				if($row['archive'] && !strstr($listattr, "open_archived")){
					$closed = "closed";
				}
				if($row['minimize']) $closed = "closed";
				if(!$closed) $class.= " open toggle";
				else $class.= " closed";
				
				$style = "";
				if($row['rating_weighted'] < -5) $style.= "opacity:.3;";
				elseif($row['rating_weighted'] < 0) $style.= "opacity:.6;";
				
				//my rating
				/*if($usrid){
					$q = "SELECT rating FROM posts_ratings WHERE nid='$row[nid]' AND usrid='$usrid' LIMIT 1";
					$myrating = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
				}*/
				
				$ret.= 
					'<article id="nid-item-'.$row['nid'].'" class="post-item compact nohov '.$class.'" style="'.$style.'" data-posttype="'.$row['post_type'].'">
						<div class="description">
							<time datetime="'.$row['datetime'].'" title="'.date("T").' GMT '.date("P").'">'.formatDate($row['datetime']).'</time><a href="'.$post->url.'"><b>'.$row['description'].'</b></a>
						</div>
						'.$post->output("compact").'
						'.$post->outputMeta();
						
						/*include_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.tags.php";
						$tags = new tags("posts_tags:nid:".$post->nid);
						if($tags->numTags()){
							$tags->allow_add = false;
							$tags->allow_rm  = false;
							$tags->tag_len = 30;
							$o_tags = $tags->tagList(6);
							//if($tags->num_tags > 6) $o_tags = str_replace('</ul>', '<li>&hellip;</li>', $o_tags);
							$ret.= '<dd class="tags">'.$o_tags.'</dd>';
						}*/
						
					$ret.= '
					<div class="clear"></div>
				</article>';
			}
			$ret.= '<div class="clear"></div>
		</div>';
		
		/*				<a href="'.$post->url.'" title="'.ucwords($typeimg).': '.htmlsc($row['description']).'" style="background-image:url(/bin/img/icons/news/'.$typeimg.'_sm.png);">
							<time datetime="'.$row['datetime'].'">'.formatDate($row['datetime'], "MM/DD/YY").'</time>
							<span class="description">'.$row['description'].'</span>
						</a>
					';*/
		
		return $this->nav_with_legend . $ret . $this->nav;
		
	}
	
	function shortlist($rows) {
		
		if(!$rows) return;
		
		?>
		<ul class="posts-shortlist">
			<?
			foreach($rows as $row) {
				
				$post = new post($row);
				
				$typeimg = $row['type'];
				if($typeimg == "gallery") $typeimg = "image";
				if($row['type2'] == "review") $typeimg = "review";
				
				echo '<li style="background-image:url(/bin/img/icons/news/'.$typeimg.'_sm.png);"><a href="'.$post->url.'">'.$row['description'].'</a> <span class="date">'.timeSince($row['datetime'], "short").' ago</span></li>';
				
			}
			?>
		</ul>
		<?
		
	}

}

class post {
	
	public $nid; // est $nid when the class is called (ie: new post($nid);) or identify it after calling the class (ie: $post->nid = 123;)
	public $url; // automatically constructed if nid is given on class call
	public $content; // split content
	
	function __construct($in){
		// @var $in an NID or a post array (a db row, for example)
		if(is_array($in)){
			foreach($in as $key => $val) $this->{$key} = $val;
			$this->url = $_SERVER['HTTP_HOST'] == "localhost" ? "/posts/handle.php?nid=".$this->nid : '/sblog/'.$this->nid.'/'.$in['permalink'];
			$this->content = $this->splitData();
		} elseif($in != ''){
			$q = "SELECT * FROM posts WHERE nid='".mysqli_real_escape_string($GLOBALS['db']['link'], $in)."' LIMIT 1";
			if($in = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
				foreach($in as $key => $val) $this->{$key} = $val;
				$this->url = $_SERVER['HTTP_HOST'] == "localhost" ? "/posts/handle.php?nid=".$this->nid : '/sblog/'.$this->nid.'/'.$this->permalink;
				$this->content = $this->splitData();
			} else {
				throw new Exception("Error: Couldn't find post #".$in);
			}
		} else {
			throw new Exception("Error: Couldn't construct post.");
		}
	}
	
	function splitData($attachment='', $content='', $post_type=''){
		
		if(!$content) $content = $this->content;
		
		return json_decode($content, true);
		
	}
	
	function output($disp="full"){
		
		// @var $disp str [compact, full (default)]
		
		global $usrid;
		
		if($disp != "compact") $disp = "full";
		
		/*$date = substr($n['datetime'], 0, 10);
		$date = str_replace("-", "/", $date);
		$rtlink = $date."/".$n['permalink'];
		if($n['category'] == "blog") {
			$q = "SELECT username FROM users WHERE usrid='".$n['usrid']."' LIMIT 1";
			$udat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
			$rtlink = "/~".$udat->username."/blog/".$rtlink;
		} else {
			$rtlink = "/posts/".$rtlink;
		}*/
		
		$d = !is_array($this->content) ? $this->splitData() : $this->content;
		
		//heading image
		/*$himg = "";
		$allowedtypes = array("text", "quote", "link", "audio");
		if(in_array($n['type'], $allowedtypes) && $n['img']){
			if(substr($n['img'], 0, 2) == "ls"){
				$himgtype = "ls";
				$himg = '<a class="himg" href="'.$this->url.'" title="'.htmlSC($n['description']).'"><img src="/posts/img/'.$n['img'].'" border="0" alt="'.htmlSC($n['description']).'"/><span class="pgfold"></span></a>';
			} else {
				$himgtype = "tn";
				$himg = '<a class="himgtn" href="'.$this->url.'" title="'.htmlSC($n['description']).'"><img src="/posts/img/'.$n['img'].'" width="140" height="91"/><span class="pgfold"></span></a>';
			}
		}*/
		
		//heading
		if($d['heading'] && !$d['heading_formatted']){
			$bb = new bbcode($d['heading']);
			$bb->params['minimal'] = true;
			$d['heading_formatted'] = $bb->bb2html();
			$d['heading_formatted'] = strip_tags($d['heading_formatted'], "<i><em><del><ins>");
		}
		$h = ($disp == "full" ? "h1" : "h4");
		$ret_heading    = ($d['heading_formatted'] ? '<'.$h.' class="heading"><a href="'.$this->url.'" title="Article: '.htmlSC($this->description).'">'.$d['heading_formatted'].'</a></'.$h.'>' : '');
		
		//review
		if($this->post_type == "review"){
			if($d['rating']['scale']){
				//scale (star) rating
				$pos = $d['rating']['scale'] * 16;
				$text_tag = '<span class="star-rating" style="background-position:0 -'.$pos.'px;">'.$d['rating']['scale'].'/5</span>';
			} elseif($d['rating']['custom']){
				//custom input
				$text_tag = '<strong>'.$d['rating']['custom'].'</strong>';
			} elseif($d['rating']['fixed']){
				$text_tag = '<span class="img" style="background-image:url(\'/bin/img/icons/emoticons/_'.$d['rating']['fixed'].'.png\');">'.str_replace("_", " ", $d['rating']['fixed']).'</span>';
			} else {
				$text_tag = '<strong>Review</strong>';
			}
		}
		
		//text
		$text = '';
		if($d['text'] && $this->post_type != "quote"){
			
			//format $text for publication
			
			$text_tag = ($text_tag ? '<span class="text-tag">'.$text_tag.'</span>' : '');
			if($text_tag == '' && $this->category == "blog") $text_tag = '<span class="text-tag">Blog</span>';
			//if($text_tag == '' && $n['type'] == "link") $text_tag = '<span class="text-tag"><a href="'.$d['link_url'].'" target="_blank">Link</a></span>';
			
			if($disp == "full"){
				
				$bb = new bbcode($d['text']);
				$bb->headings_offset = 1;
				$d['text'] = $bb->bb2html();
				$text = $d['text'];
				
				if($text_tag){
					$pos = strpos($text, '<p>');
					$pos = $pos !== false ? ($pos + 3) : 0;
					if($pos) $text = substr($text, 0, $pos) . $text_tag . substr($text, $pos);
					else $text = $text_tag . $text;
				}
			
			} else {
				
				//create intro text for postslists
				//minimize to bare essentials
				//this should already be formatted from Markdown -> HTML
				
				$text = $d['text_intro'];
				if(strstr($text, "<!--more")){
					preg_match("/<!--more(.*?)-->/", $text, $matches);
					$more_tag = $matches[0];
					if($matches[1]){
						$more_tag_custom = trim(htmlspecialchars($matches[1]));
						$more_tag_custom = substr($more_tag_custom, 0, 30);
					}
					$more_words = $more_tag_custom ? $more_tag_custom : ($d['text_type'] == "review" ? 'Full Review' : 'Read on');
					$text = str_replace($more_tag, ' <a href="'.$this->url.'" class="arrow-right" style="white-space:nowrap;">'.$more_words.'</a>', $text);
				}
				
				$bb = new bbcode($text);
				$bb->headings_offset = 4;
				$bb->params['inline_citations'] = true;
				$bb->params['markdown'] = false;
				$text = $bb->bb2html();
				
				if($text_tag){
					$pos = strpos($text, '<p>');
					$pos = $pos !== false ? ($pos + 3) : 0;
					if($pos) $text = substr($text, 0, $pos) . $text_tag . substr($text, $pos);
					else $text = $text_tag . $text;
				}
				
				//poll
				$q = "SELECT `question` FROM posts_polls WHERE nid='".$this->nid."' LIMIT 1";
				if($poll = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
					if(strlen($poll['question']) > 100) $poll['question'] = substr($poll['question'], 0, 96)."...";
					$text.= '<div class="poll"><p>Poll: <a href="'.$this->url.'#poll">'.$poll['question'].'</a></p></div>';
				}
			}
		}
		$ret_body = $text;
		
		//quote
		if($this->post_type == "quote"){
			
			$ret_heading = '';
			
			$quote_text = $d['text']."\n\n".'<!--__footer__-->'.$d['quote_source'];
			
			$bb = new bbcode($quote_text);
			$bb->params['inline_citations'] = true;
			$bb->params['footnotes_noappend'] = true;
			$bb->headings_offset = 4;
			$quote_text = $bb->bb2html();
			
			if($d['quote_length'] < 90) $qcl = "shortquote";
			elseif($d['quote_length'] < 140) $qcl = "medquote";
			elseif($d['quote_length'] < 190) $qcl = "";
			else $qcl = "longquote";
			
			$ret_body = '<blockquote class="att '.$qcl.'">'.str_replace("<!--__footer__-->", "<footer>", $quote_text).'</footer></blockquote>' . $bb->outputFootnotes(true);
			
		}
		
		//attachment
		$ret_attachment = '';
		switch($this->attachment){
			case "link":
				$ret_heading = '';
				$ret_attachment = '<a href="'.$d['link_url'].'" target="_blank"><span>'.$d['heading_formatted'].'</span></a>';
				break;
			
			case "image":
				require_once "class.img.php";
				$num_image = $d['img_num'] ? $d['img_num'] : count($d['img_names']);
				if(!$d['img_layout']) $d['img_layout'] = $GLOBALS['image_default_layouts'][$num_image];
				$cells = array();
				$cells = explode("x", $d['img_layout']);
				$i = 0;
				foreach($cells as $cell){
					$row = '';
					$imgs = array();
					$row_minheight = 0;
					$col_maxwidth = 620;
					if($cell == 2) $col_maxwidth = 305;
					elseif($cell == 3) $col_maxwidth = 200;
					for($c=0; $c < $cell; $c++){
						try { $imgs[$c] = new img($d['img_names'][$i]); }
						catch(Exception $e){ $imgs[$c] = new img("unknown.png"); }
						//calculate proportional dimensions
						$prop = $imgs[$c]->img_width > $col_maxwidth ? ($col_maxwidth / $imgs[$c]->img_width) : 1;
						$prop_height = ceil($imgs[$c]->img_height * $prop);
						//track shortest row cell
						$row_minheight = ($c==0 || $prop_height < $row_minheight) ? $prop_height : $row_minheight;
						$imgs[$c]->prop_height = $prop_height;
						$i++;
					}
					//decide optimal image size
					$img_size = $cell == 1 ? "op" : "md";
					//now that we have min row height, loop back through and output the images
					for($c=0; $c < $cell; $c++){
						$img = $imgs[$c];
						//output the original if GIF incase animated
						if($img->img_minor_mime == "gif") $img_size = "0";
						//offset image if it's taller than the row height
						$y_offset = ($row_minheight - $img->prop_height) / 2;
						$row.= $img->output($img_size, $this->nid, "margin-top:".$y_offset."px;");
					}
						
					$ret_attachment.= '<div class="row width-'.$cell.'" style="height:'.$row_minheight.'px;">'.$row.'</div>';
				}
				break;
				
			case "video":
				if($d['video_embedcode']) $ret_attachment = $d['video_embedcode'];
				break;
				
			case "audio":
				$ret_attachment = embedAudio($d['audio_file']);
				//old method'<script type="text/javascript" src="https://media.dreamhost.com/ufo.js"></script><p id="'.$d['audio_file'].'"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.</p><script type="text/javascript">var FO = {movie:"https://media.dreamhost.com/mediaplayer.swf",width:"320",height:"20",majorversion:"7",build:"0",bgcolor:"#FFFFFF",flashvars:"file='.$d['audio_file'].'&showdigits=true&autostart=false" }; UFO.create(FO,"'.$d['audio_file'].'");</script>';
			  break;
			
			case "tweet":
				$ret_attachment = $d['tweet']['html'] ? $d['tweet']['html'] : '(<a href="'.$d['tweet']['url'].'">Tweet</a>)';
				break;
		}
		
		$ret = $ret_heading.($ret_attachment ? '<figure class="attachment '.$this->attachment.'">'.$ret_attachment.'</figure>' : '').'<div class="body">'.$ret_body.'</div>';
		
		return $ret;
		
	}
	
	function outputMeta(){
		
		// Comment count
		if($num = $this->numComments()){
			$numtitle = "$num comments about this post";
			$num = "$num Comment".($num != 1 ? "s" : "");//.' comment'.($num != 1 ? 's' : '');
		} else {
			$num = "Discuss";
			$numtitle = "Discuss this post";
		}
		
		//User data
		if(!$GLOBALS['post_user'][$this->usrid]){
			$GLOBALS['post_user'][$this->usrid] = new user($this->usrid);
		}
		$u = $GLOBALS['post_user'][$this->usrid];
		
		$meta = '
		<div class="meta meta-side">
			<ul>
				<li class="posted">
					<a href="'.$this->url.'" title="Permanent link to this post"><time datetime="'.$this->datetime.'" title="[Permanent link] Posted '.substr($this->datetime, 11, 5).' '.date("T").' GMT '.date("P").'">'.date("D F j, Y", strtotime($this->datetime)).'</time> <span class="sym">&infin;</span></a>
				</li>
				<li class="poster">
					Posted by <a href="'.$u->url.'">'.$u->avatar().$u->username.'</a>
				</li>
				<li class="discuss">
					<a href="'.$this->url.'#forum" title="'.$numtitle.'">'.$num.'<span></span></a>
				</li>
			</ul>
			
			<div class="hrate">'.$this->outputHeartRating().'</div>
			<div class="share" id="share-'.$this->nid.'"><a>Share</a><div class="shareconsole"></div></div>
		</div>
		';
		
		return $meta;
		
	}
	
	function numComments($nid='') {
		
		if($this->num_comments) return $this->num_comments;
		
		if(!$nid) $nid = $this->nid;
		
		$q = "SELECT posts FROM forums_topics WHERE location='post:$nid'";
		if(!$dat = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) $this->num_comments = 0;
		else $this->num_comments = $dat['posts'];
		return $this->num_comments;
		
	}
	
	function heartRating($r=''){
		if($r < 1 || $r === '') $r = '-e-e-e';
		elseif($r < 5) $r = '-h-e-e';
		elseif($r < 10) $r = '-f-e-e';
		elseif($r < 15) $r = '-f-h-e';
		elseif($r < 20) $r = '-f-f-e';
		elseif($r < 25) $r = '-f-f-h';
		else $r = '-f-f-f';
		/*if($r < -7 || $r === '') $r = '-e-e-e';
		elseif($r < -5) $r = '-h-e-e';
		elseif($r < 0) $r = '-f-e-e';
		elseif($r < 1) $r = '-f-h-e';
		elseif($r < 5) $r = '-f-f-e';
		elseif($r < 21) $r = '-f-f-h';
		else $r = '-f-f-f';*/
		$r = str_replace('-f', '<img src="/bin/img/heart.png"/>', $r);
		$r = str_replace('-h', '<img src="/bin/img/heart.5.png"/>', $r);
		$r = str_replace('-e', '<img src="/bin/img/heart.0.png"/>', $r);
		return $r;
	}
	
	function outputHeartRating(){
		global $usrid;
		
		if(!$this->nid) return;
		
		//my rating
		if($usrid){
			$q = "SELECT rating FROM posts_ratings WHERE nid='$this->nid' AND usrid='$usrid' LIMIT 1";
			$myrating = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
		}
		
		return 
		'<div id="rate-nid-'.$this->nid.'">'.
			'<a href="#rate_post" title="I don\'t like this" class="tooltip hrate-minus" data-nid="'.$this->nid.'" data-rating="0">&minus;</a>'.
			'<span class="rating" title="'.($this->ratings ? "Link quality is ".$this->rating."% based on ".$this->ratings." rating".($row['ratings'] != 1 ? 's' : '')." [".$this->rating_weighted."]" : 'Not yet rated').'">'.($this->ratings ? $this->heartRating($this->rating_weighted) : $this->heartRating('')).'</span>'.
			'<a href="#rate_post" title="I like this" class="tooltip hrate-plus" data-nid="'.$this->nid.'" data-rating="1">+</a>'.
			'<div class="loading"></div>'.
		'</div>';
	}

}

?>