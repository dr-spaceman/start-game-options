<?
require_once "page.php";
require_once "bbcode.php";

if($_POST['_action'] == "add_tag") addTag($_POST);

function addTag($arr){
	
	// @param $arr an array of data:
	// [_subject] tag subject
	// [_tag] tag name
	// [_return] return type [ print (default, outputs json string), return (returns array of data) ]
	
	global $usrid;
	
	if($arr['_return'] != "return") header("Content-type: application/json");
	$ret = array();
	
	if(!$usrid) $ret['error'] = 'Please log in to suggest a tag';
	
	$subj = trim($arr['_subject']);
	if(!$subj) $ret['error'] = "No db subject given";
	
	$tag = $arr['_tag'];
	if($barpos = strrpos($tag , '|')){
		$tag_link_words = substr($tag, $barpos);
		$tag = substr($tag, 0, $barpos);
	}
	$tag = formatName($tag, '', false);
	if(!$tag) $ret['error'] = "No tag given";
	
	$handle = array();
	$handle = explode(":", $subj); //ie posts_tags:nid:123
	
	//already tagged with this?
	$q = sprintf("SELECT * FROM `%s` WHERE `%s`='%s' AND `tag`='%s' LIMIT 1", 
		mysqli_real_escape_string($GLOBALS['db']['link'], $handle[0]),
		mysqli_real_escape_string($GLOBALS['db']['link'], $handle[1]),
		mysqli_real_escape_string($GLOBALS['db']['link'], $handle[2]),
		mysqli_real_escape_string($GLOBALS['db']['link'], $tag));
	if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $ret['error'] = "This item is already tagged with '$tag'.";
	
	//if the subject's a forum post, get thread id to match for double tagging
	if($handle[0] == "forums_tags" && $handle[1] == "pid"){
		$q = "SELECT tid FROM forums_posts WHERE pid = '".mysqli_real_escape_string($GLOBALS['db']['link'], $handle[2])."' LIMIT 1";
		if(!$fpost = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) $ret['error'] = "Couldn't get forum thread data";
	}
	
	//check forum topic for double tagging
	if($fpost['tid']){
		$q = "SELECT * FROM forums_tags WHERE tid = '".$fpost['tid']."' AND `tag`='".mysqli_real_escape_string($GLOBALS['db']['link'], $tag)."' LIMIT 1";
		if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $ret['error'] = "This forum thread is already tagged with '$tag'.";
	}
	
	if(!$ret['error']) {
		
		// BADGES
		
		require_once("class.badges.php");
		$_badges = new badges;
		
		if(stristr($tag, 'ninja')) $_badges->earn(36);
		
		$sodapop = array('soda','sodapop','beer','booze','alcohol');
		foreach($sodapop as $needle) {
			if(stristr($tag, $needle)) $_badges->earn(56);
		}
		
		$q = sprintf("INSERT INTO `%s` (`%s`, `tag`, `usrid`) VALUES ('%s', '%s', '$usrid');", 
			mysqli_real_escape_string($GLOBALS['db']['link'], $handle[0]),
			mysqli_real_escape_string($GLOBALS['db']['link'], $handle[1]),
			mysqli_real_escape_string($GLOBALS['db']['link'], $handle[2]),
			mysqli_real_escape_string($GLOBALS['db']['link'], $tag));
		$ret['tagid'] = mysqlNextAutoIncrement($handle[0]);
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $ret['error'] = "Error adding tag to database: ".mysqli_error($GLOBALS['db']['link']);
		else{
			$pglinks = new pglinks();
			$pglinks->attr['class'] = "tag-link";
			$ret['newtag'] = $pglinks->parse("[[$tag]]");
		}
		
		$ret['tagrmid'] = base64_encode($handle[0].':id:'.$ret['tagid']);
		
		//if the subject's a forum post, also tag the topic
		if($fpost['tid']){
			$q = "UPDATE forums_tags SET tid = '".$fpost['tid']."' WHERE id = '".$ret['tagid']."' LIMIT 1";
			mysqli_query($GLOBALS['db']['link'], $q);
		}
		
		// save this tag as a cookie for quick access
		$c = "|".$_COOKIE['recent_tags'];
		$c = str_replace("|$tag|", "|", $c); // prepend the tag
		setcookie("recent_tags", $tag.$c, time()+60*60, "/");
		
	}
	
	if($arr['_return'] == "return") return $ret;
	
	echo json_encode($ret);
	exit;
	
}

function extractTags($txt) {
	
	require_once "class.pglinks.php";
	$links = new pglinks();
	return $links->extractFrom($txt);
	
	// return array of tags
	// @inp str ie: [[Final Fantasy]], [[Xbox|a game console from Microsoft]], [[Category:Mustaches]]
	// @ret array [ tag , namespace , link_words ]
	
	/*$tags = array();
	$added_tags = array();
	preg_match_all('@\[\[(Category:|Tag:)?(.*?)\]\]@ise', $txt, $matches, PREG_SET_ORDER);
	if($matches) {
		foreach($matches as $m) {
			$ns = ($m[1] ? substr($m[1], 0, -1) : '');
			$tag = $m[2];
			$link_words = "";
			if(strstr($tag, "|")) {
				list($tag, $link_words) = explode("|", $tag);
			}
			$tag = formatName($tag);
			if($tag != "" && !in_array($tag, $added_tags)) $tags[] = array("tag" => $tag, "namespace" => $ns, "link_words" => $link_words);
			$added_tags[] = $tag;
		}
	}
	return $tags;*/
	
}

if($_POST['_action'] == "rm_tag") {
	
	if(!$usrid) die('Please log in to remove');
	$tag = trim($_POST['_tag']);
	$tag = base64_decode($tag);
	if(!$tag) die("No tag given");
	
	$handle = array();
	$handle = explode(":", $tag); //ie posts_tags:id:123
	
	$q = sprintf("DELETE FROM `%s` WHERE `%s`='%s' LIMIT 1;", 
		mysqli_real_escape_string($GLOBALS['db']['link'], $handle[0]),
		mysqli_real_escape_string($GLOBALS['db']['link'], $handle[1]),
		mysqli_real_escape_string($GLOBALS['db']['link'], $handle[2]));
	if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Error removing tag from database: ".mysqli_error($GLOBALS['db']['link']));
	
	exit;
	
}

if($_GET['q'] && $_GET['timestamp']) {
	
	//autocomplete query
	
	$q = $_GET['q'];
	//$query = "SELECT title, type FROM pages WHERE MATCH(`title`,`keywords`) AGAINST('".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."') AND redirect_to='' AND title_unpublished='' LIMIT 100";
	$query = "SELECT title, type FROM pages WHERE (`title` LIKE '%".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."%' OR `keywords` LIKE '%".mysqli_real_escape_string($GLOBALS['db']['link'], $q)."%') AND redirect_to='' AND title_unpublished='' LIMIT 100";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)){
		echo $row['title'].'|'.$row['type']."\n";
	}
	
	exit;
	
}
	

class tags {
	
	public $subj;              // format: 'TABLE:FIELD:VAL', ie: posts_tags:nid:987, forums_tags:tid:1298
														 // required: establish this on class contruct
	public $allow_add;         // allow adding to the list if true
	public $allow_rm;          // allow tag to be removed if true; optionally, value can be "creator", which is limited to the original tagger and admins
	public $i;                 // iteration for form ID, automatically generated as a random digit
	public $list_style;        //replace the default list style with another: [ plain ]
	public $suggestedTags;
	public $tag_len;           // maximum tag length
	
	function __construct($subj){
		$this->subj = $subj;
		$this->i = mt_rand(); //taggroupid
		$this->list_style = "default";
		$this->allow_add = ($GLOBALS['usrrank'] >= 4 ? true : false );
		$this->allow_rm = $this->allow_add;
	}
	
	/*public function __set($name, $value){
		echo "Setting $name : $value ; ";
	}*/
	
	function numTags($subj=''){
		
		//@return str number of tags counted by subject
		
		if($this->num_tags) return $this->num_tags;
		
		if(!$subj) $subj = $this->subj;
		
		$handle = explode(":", $subj);
		$query = "SELECT * FROM `".mysqli_real_escape_string($GLOBALS['db']['link'], $handle[0])."` WHERE `".mysqli_real_escape_string($GLOBALS['db']['link'], $handle[1])."`='".mysqli_real_escape_string($GLOBALS['db']['link'], $handle[2])."'";
		$this->num_tags = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query));
		return $this->num_tags;
		
	}
	
	function taglist($limit=0, $show=8){
		
		// output a tag list based on $this->subj
		// @var $limit int 0 = no limit
		
		global $usrid, $usrrank;
		
		$handle = explode(":", $this->subj);
		$query = sprintf("SELECT * FROM `%s` WHERE `%s`='%s'".($limit ? " LIMIT $limit" : ""),
			mysqli_real_escape_string($GLOBALS['db']['link'], $handle[0]),
			mysqli_real_escape_string($GLOBALS['db']['link'], $handle[1]),
			mysqli_real_escape_string($GLOBALS['db']['link'], $handle[2]));
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		
		if($show === 0) $show = mysqli_num_rows($res);
		
		$i = 0;
		$o_tags = '';
		while($row = mysqli_fetch_assoc($res)){
			$tagitem = $this->tagItem($row);
			if(++$i > $show) $tagitem = str_replace('style="', 'style="display:none;', $tagitem);
			$o_tags.= $tagitem;
		}
		
		$o_tags_sugg = '';
		if($this->suggestedTags){
			foreach($this->suggestedTags as $a){
				$o_tags_sugg.= '<li class="tagitem suggtagitem">'.$a.'</li>';
			}
		}
		
		if($this->allow_add) $addTag = '<li class="sugg">'.($usrid ? '<a href="#suggest_new_tag" class="suggtaglink preventdefault" onclick="$(this).parent().hide(); $(\'#t'.$this->i.'-tagspace\').show().find(\'.inptag\').focus();"><u>Add a Tag</u></a>' : '<a href="/login.php" class="suggtaglink"><u>Add a Tag</u></a>').'</li>';
		else $addTag = '<li class="sugg"></li>';
		
		$ret = '<ul id="t'.$this->i.'-newtagshere" class="taglist nohov '.$this->list_style.'" data-taggroupid="'.$this->i.'">';
		if(!$o_tags) $ret.= '<li class="notags null">No tags yet</li>';
		else {
			require_once "class.pglinks.php";
			$pglinks = new pglinks();
			$pglinks->attr['class'] = "tag-link";
			$ret.= $pglinks->parse($o_tags);
		}
		$ret.= 
			$o_tags_sugg.
			($i > $show ? '<li class="moretags"><a href="#moretags" class="preventdefault arrow-right" onclick="$(this).parent().hide().siblings(\'.tagitem\').show();"><span>Show more tags</span></a></li>' : '').
			$addTag.
			'</ul>';
		
		return $ret;
	
	}
	
	function tagItem($row){
		
		//@param $row dbrow [ id, usrid, tag ]
		
		$handle = explode(":", $this->subj);
		if($this->allow_rm === true || ($this->allow_rm == "creator" && $GLOBALS['usrid'] == $row['usrid']) || $GLOBALS['usrrank'] >= 8) {
			$rm = '<a title="Remove this tag" id="'.base64_encode($handle[0].':id:'.$row['id']).'" class="rm">x</a>';
		}
		$tag = $row['tag'];
		if($this->tag_len && strlen($tag) > $this->tag_len){
			$tag = $row['tag'] . '|' . substr($row['tag'], 0, ($this->tag_len - 8)) . '&hellip;' . substr($row['tag'], -6);
		}
		return '<li id="t'.$this->i.'-tag-'.$row['id'].'" class="tagitem'.($rm ? ' rmable' : '').'" style=""><span class="tag-wrap">[['.$tag.']]'.$rm.'</span><input type="hidden" name="tag-ref['.$handle[0].':'.$row['id'].']" value="t'.$this->i.'-tag-'.$row['id'].'"/></li>';
		
	}
	
	function tagarr($subj='') {
		
		//@return array tags related to a subject
		
		if(!$subj) $subj = $this->subj;
		
		$handle = explode(":", $subj);
		$query = "SELECT * FROM `$handle[0]` WHERE `$handle[1]`='".mysqli_real_escape_string($GLOBALS['db']['link'], $handle[2])."'";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		$tags = array();
		while($row = mysqli_fetch_assoc($res)) {
			$tags[] = $row['tag'];
		}
		
		return $tags;
		
	}
	
	function suggestForm(){
		
		if(!$this->allow_add) return;
		
		global $usrid;
		
		$ret = 
		'<div id="t'.$this->i.'-tagspace" class="tagspace">
			<div class="container">
				';
				if($usrid) {
					$ret.=
					'<form action="#" method="" class="fftt" onsubmit="return submitNewTagForm($(this));">
						<input type="text" name="inptag" id="t'.$this->i.'-inptag" class="inptag ff"/>
						<label for="t'.$this->i.'-inptag" class="tt">Start typing to find a tag...</label>
						<input type="submit" value="+"/>
						<input type="hidden" name="tagsubj" value="'.$this->subj.'"/>
						<input type="hidden" name="taggroupid" value="'.$this->i.'"/>
					</form>
					';
				}
				$ret.= '
			</div>
		</div>
		<input type="hidden" name="tagsubj" value="'.$subj.'" id="t'.$this->i.'-tagsubj"/>
		';
		
		return $ret;
		
	}
	
	function suggest($txt, $match_against=array()) {
		
		//populates $this->suggestedTags with an array of checkbox-accompanied tags
		//this list is automatically included in the tag list if you call this function before taglist()
		//otherwise they can be manually output somewhere else
		
		// @var $txt text to extract tags FROM
		// @var $match_against array of tags to exclude
		
		if($e_tags = extractTags($txt)) {
			
			if($this->subj){
				//get current tags and add to $match_against
				$handle = explode(":", $this->subj);
				$query = "SELECT * FROM `$handle[0]` WHERE `$handle[1]`='".mysqli_real_escape_string($GLOBALS['db']['link'], $handle[2])."'";
				$res   = mysqli_query($GLOBALS['db']['link'], $query);
				while($row = mysqli_fetch_assoc($res)) {
					$match_against[] = $row['tag'];
				}
			}
			
			$tags = array();
			foreach($e_tags as $t){
				if(!in_array($t['tag'], $match_against)) $tags[] = $t;
			}
			
			if(count($tags)){
				$i = 0;
				foreach($tags as $t){
					$tag = $t['tag'];
					$this->suggestedTags[] = '<input type="checkbox" name="tag[]" value="" data-subject="'.$this->subj.'" data-taggroupid="'.$this->i.'" id="tag-'.++$i.'" class="fauxcheckbox suggestedtag" onclick="newTag.init($(this))"/><label for="tag-'.$i.'" title="click me to tag!">'.$tag.'</label>';
				}
			}
		}
		
	}
	
	function autoTag($txt) {
		
		//autotag [[Tag:foo]]
		
		if(!$this->subj) return;
		
		if($tags = extractTags($txt)){
			
			$handle = explode(":", $this->subj);
			$ctags = array();
			$query = "SELECT * FROM `$handle[0]` WHERE `$handle[1]`='".mysqli_real_escape_string($GLOBALS['db']['link'], $handle[2])."'";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)){
				$ctags[] = $row['tag'];
			}
			
			$q = "";
			foreach($tags as $t){
				if($t['namespace'] == "Tag" && !in_array($t['tag'], $ctags)){
					$q.= "('".mysqli_real_escape_string($GLOBALS['db']['link'], $handle[2])."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $t['tag'])."', '$usrid'),";
				}
			}
			if($q){
				if(!mysqli_query($GLOBALS['db']['link'], "INSERT INTO `$handle[0]` (`$handle[1]`, tag, usrid) VALUES ".substr($q, 0, -1))) return FALSE;
			}
			
		}
		
		return;
			
	}
	
}

?>