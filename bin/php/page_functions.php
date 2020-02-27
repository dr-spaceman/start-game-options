<?

function formatName($name='', $mode='', $rmNamespaces=true) {
	
	//format name and make name url
	//game titles, people names, categories, topics, etc.
	//always format before inserting into DB or searching
	// @var $mode string [sortable] format into a sortable name (ie, The Legend of Zelda => Legend of Zelda, Final Fantasy VI => Final Fantasy 6)
	// @var $rmNamespaces boolean false to not remove any namespaces
	
	if($name == '') return '';
	$name = stripslashes($name);
	$name = html_entity_decode($name, ENT_QUOTES, "UTF-8");
	$name = htmlSCDecode($name);
	$name = urldecode($name);
	//$name = str_replace('"', "'", $name);
	$name = str_replace("\n", "", $name);
	$name = str_replace("\r", "", $name);
	$name = str_replace("\t", "", $name);
	$name = str_replace("_", " ", $name);
	$name = preg_replace("/ +/", " ", $name);
	if($rmNamespaces) $name = preg_replace("/^(Category:)|(Tag:)|(User:)|(AlbumID:)|(Special:)/i", "", $name); // remove special namespaces
	$name = preg_replace("/\<|\>|\[|\]|\||\{|\}|`/", "", $name); // < > [ ] { } | `
	//$name = ucfirst($name);
	$name = trim($name);
	
	do if($mode == "sortable"){
		$name = preg_replace("/^(A |An |The )/", "", $name);
		$name = preg_replace("/[^a-z0-9 ]*/i", "", $name);
		$name = preg_replace("/ +/", " " , $name);
		// Change numbers to leading zeros
		preg_match("/( |^)([1-9]{1})( |$)/", $name, $numer);
		if($numer[0]){
			$name = str_replace($numer[0], $numer[1].sprintf("%02d",$numer[2]).$numer[3], $name);
		}
		// Don't treat the "I" in a name beginning with "I" as a roman (ie "I Wanna Be The Guy")
		if(substr($name, 0, 2) == "I ") break;
		// Change Roman numerals to numbers w/ leading zeros
		$romans_arr = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
		preg_match("/( |^)(D?C{0,3}|C[DM])(L?X{0,3}|X[LC])(V?I{0,3}|I[VX])( |$)/", $name, $romans);
		if($romans[0]){
			$roman = trim($romans[0]); $result = 0;
			foreach ($romans_arr as $key => $value) {
			    while (strpos($roman, $key) === 0) {
			        $result += $value;
			        $roman = substr($roman, strlen($key));
			    }
			}
			$name = str_replace($romans[0], $romans[1].sprintf("%02d",$result).$romans[5], $name);
		}
	} while(false);
	
	return $name;
	
}

function formatNameURL_SC($name, $enc='') {
	if($enc) $name = rawurlencode($name);
	else $name = str_replace("&", "%26", $name);
	//$name = str_replace('/', '%2F', $name);
	return ($name);
}

function formatNameURL($name, $enc='') {
	
	$name = str_replace(" ", "_", $name);
	//$name = str_replace('"', '&#34;', $name);
	
	/*$namespaces = array("/^Category:/");
	$name = preg_replace($namespaces, "", $name);*/
	
	return formatNameURL_SC($name, $enc);
	
}

function pageURL($title, $pgtype='', $encode=1){
	
	$pgtypes = array(
		"game"     => "games",
		"person"   => "people",
		"category" => "categories",
		"topic"    => "topics"
	);
	$pgsubcategories = array(
		"Game character" => "characters",
		"Game developer" => "developers",
		"Game series"    => "series", 
		"Game console"   => "consoles", 
		"Game concept"   => "concepts"
	);
	
	if($pgtype) $index = $pgtypes[$pgtype];
	if(!$pgtype || $pgtype == "category"){
		$title = formatName($title);
		$q = "SELECT `type`, `subcategory` FROM pages WHERE `title` = '".mysql_real_escape_string($title)."' LIMIT 1";
		if($dat = mysql_fetch_object(mysql_query($q))){
			if($dat->subcategory) $index = $pgsubcategories[$dat->subcategory];
			$index = $index ? strtolower($index) : $pgtypes[$dat->type];
		}
	}
	if(!$index) $index = "content";
	
	if($_SERVER['HTTP_HOST'] == "localhost") return "/pages/handle.php?index=".$index."&title=".formatNameURL($title, $encode);
	else return "/".$index."/".formatNameURL($title, $encode);
	
}

function sendBug($desc) { global $default_email; $desc = wordwrap($desc, 70); @mail($default_email, "Videogam.in Auto-Bug Report", $desc); }

function validateEmail($email){
	if(!eregi("^[^@]+@.+\.[a-z]{2,6}$", $email)) return FALSE;
	return TRUE;
}

/*function getPlatforms() {
	$query = "SELECT * FROM games_platforms";
	$res = mysql_query($query);
	while($row = mysql_fetch_assoc($res)) {
		$ret[$row[platform_id]][platform] = $row[platform];
		$ret[$row[platform_id]][platform_shorthand] = $row[platform_shorthand];
	}
	return $ret;
}*/

function getUserDat($params=''){
	// @param $params array or string
	// @return obj
	
	if(!$params) $params = $GLOBALS['usrid'];
	$uid = is_string($params) ? $params : $params['usrid'];
	if($uid)                    $q = "SELECT * FROM users LEFT JOIN users_details USING (usrid) LEFT JOIN users_prefs USING (usrid) WHERE usrid='".mysql_real_escape_string($uid)."' LIMIT 1";
	elseif($params['username']) $q = "SELECT * FROM users LEFT JOIN users_details USING (usrid) LEFT JOIN users_prefs USING (usrid) WHERE username='".mysql_real_escape_string($params['username'])."' LIMIT 1";
	else return false;
	if(!$dat = mysql_fetch_object(mysql_query($q))) return false;
	
	$dat->avatar_src = "/bin/img/avatars/".($dat->avatar ? $dat->avatar : 'unknown.png');
	$dat->avatar_tn_src = "/bin/img/avatars/tn/".($dat->avatar ? $dat->avatar : 'unknown.png');
	
	return $dat;
}

function formatDate ($date, $form = 1, $convert = FALSE) {
	global $usrid;
	
	if($convert && $usrid) {
		$q = "SELECT time_zone FROM users_details WHERE usrid='$usrid' LIMIT 1";
		if($dat = mysql_fetch_object(mysql_query($q))) {
			$date = convertTimeZone($date, $dat->time_zone);
		}
	}
	
  $months = array
   ('01' => 'January',
    '02' => 'February',
    '03' => 'March',
    '04' => 'April',
    '05' => 'May',
    '06' => 'June',
    '07' => 'July',
    '08' => 'August',
    '09' => 'September',
    '10' => 'October',
    '11' => 'November',
    '12' => 'December');
  
  $short_months = array
   ('01' => 'Jan',
    '02' => 'Feb',
    '03' => 'March',
    '04' => 'April',
    '05' => 'May',
    '06' => 'June',
    '07' => 'July',
    '08' => 'Aug',
    '09' => 'Sept',
    '10' => 'Oct',
    '11' => 'Nov',
    '12' => 'Dec');
  
  list($dt,$tm) = explode(" ",$date);
  $tm = substr($tm,0,5);
  list($y,$m,$d) = explode("-", $dt);
  if ($form == 1) {
    $m = $months[$m];
    if($m == '00' || !$m) {	unset($m); unset($d); }
    if($d == '00') unset($d);
    else $d = " ".$d;
    if($m && $y) $comma = ", ";
    $ret = $m.$d.$comma.$y;
  } elseif ($form == 2 || $form == "MM/DD/YY TIME") {
  	list($y,$m,$d) = explode("-", $dt);
  	$y = substr($y,2,3);
    $ret = $m."/".$d."/".$y." ".$tm;
  } elseif ($form == 3 || $form == "MM/DD") {
    $ret = $m."/".$d;
  } elseif ($form == 4) {
    $m = $months[$m];
    $m = substr($m,0,3);
    $ret = $m." ".$d;
  } elseif ($form == 5 || $form == "MM/DD/YY") {
    $y = substr($y,2,3);
    $ret = $m."/".$d."/".$y;
  } elseif ($form == 6) {
		if(!$m || $m == '00') {
			unset($m);
			unset($d);
		} else $m = $short_months[$m]." ";
		if($d == '00') unset($d);
		else $d.= " ";
		if($y == '0000') unset($y);
	    $ret = $d.$m.$y;
  } elseif ($form == 7) {
  	if(substr($d, 0, 1) == "0") $d = substr($d, 1, 1);
    $ret = ($m ? $short_months[$m]." " : '').($d ? $d.", " : '').$y;
  } elseif($form == 8) {
  	$ret = $y." ".$short_months[$m]." ".$d." ".$tm;
  } elseif($form == 9) {
  	$ret = ($months[$m] ? $months[$m].($d != '00' ? " ".number_format($d) : '') : '');
  } elseif ($form == 10) {
    $ret = "$short_months[$m] $d, $y $tm";
  }
  return $ret;
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
		
		if($print == "0 minutes") $print = "a few seconds";
		
		return $print;
}

function convertTimeZone($dt, $tz) {
	return $dt;
}

function addPageView($pg='', $id='') {
	global $db;
	if(!$pg) $pg = preg_replace("/(index.php)$/", "", $_SERVER['SCRIPT_NAME']);
	$query = "SELECT * FROM `pagecount` WHERE `title` = '".mysql_real_escape_string($pg)."' LIMIT 1;";
	$res = mysql_query($query);
	if(!$row = mysql_fetch_object($res)) {
		$query = "INSERT INTO `pagecount` (`title`, `count`, `corresponding_id`) VALUES ('".mysql_real_escape_string($pg)."', 1, '$id')";
		mysql_query($query);
		return '1';
	} elseif($_SERVER['REMOTE_ADDR']) {
		$query = "UPDATE `pagecount` SET `count` = '".++$row->count."'".($id ? ", `corresponding_id` = '$id'" : "")." WHERE `title` = '".mysql_real_escape_string($pg)."'";
		$res = mysql_query($query);
	}
	return $row->count;
}

function printAd($size) {
	if($size == "300x250") {
		$ads = array(
			//'<a href="http://www.play-asia.com/SOap-23-83-3swa.html"><img src="http://www.play-asia.com/paOS-32-74-v.html" border=0 alt="Play-Asia.com - Buy Video Games for Consoles and PC - From Japan, Korea and other Regions" width="300" height="250"/></a>',
			'<a href="/groups/34/Videogam.in_Development"><img src="/bin/img/commerce/ads/videogam.in_dev_group.png" alt="Join the Videogam.in Site Development Group!" border="0"/></a>',
			'<a href="/music"><img src="/bin/img/commerce/ads/videogam.in_gmd.png" alt="Videogam.in Game Music Database" border="0"/></a>',
			//'<a href="http://affiliate.buy.com/fs-bin/click?id=6L8WGGotccI&offerid=245038.10000079&type=4&subid=0"><IMG alt="" border="0" src="http://ak.buy.com/buy_assets/affiliate/01/300x250_xbox360pricedrop_08.jpg"></a><IMG border="0" width="1" height="1" src="http://ad.linksynergy.com/fs-bin/show?id=6L8WGGotccI&bids=245038.10000079&type=4&subid=0">' //buy.com
		);
		echo '<div class="ad" style="width:300px; height:250px; background-color:#f7f5d1;">'.$ads[mt_rand(0,(count($ads) - 1))].'</div>';
	}
}

function mysqlNextAutoIncrement($table, $dontdie='') {
	
	$q = "SHOW TABLE STATUS LIKE '$table'";
	$r 	= mysql_query($q) or die ( "Query failed: " . mysql_error() );
	$row = mysql_fetch_assoc($r);
	if($row['Auto_increment']) return $row['Auto_increment'];
	elseif(!$dontdie) die("Couldn't get incremental ID for `$table`");
	
}

function htmlSC($x, $opts='') {
	
	// < > ' "
	$x = str_replace('"', '&quot;', $x);
	$x = str_replace("'", "&#039;", $x);
	$x = str_replace("<", "&lt;", $x);
	$x = str_replace(">", "&gt;", $x);
	if(strstr($opts, "bbcode")){
		$x = str_replace("[", "&#91;", $x);
		$x = str_replace("]", "&#93;", $x);
		$x = str_replace("{", "&#123;", $x);
	}
	return $x;
	
}

function htmlSCDecode($x, $opts='') {
	// Decode characters not decoded by html_entity_decode()
	$r = array(
		"&#039;" => "'"
	);
	$x = str_replace(array_keys($r), array_values($r), $x);
	if(strstr($opts, "bbcode")){
		$x = str_replace("&#91;", "[", $x);
		$x = str_replace("&#93;", "]", $x);
		$x = str_replace("&#123;", "{", $x);
	}
	return $x;
}

function nl2p($t) {return $t;
	$t = trim($t);
	$t = (string)str_replace(array("\r", "\r\n", "\n"), '[NL]', $t);
	$t = str_replace("[NL][NL][NL]", "</p>\n\n<p>", $t);
	$t = str_replace("[NL]", "", $t);
	$t = str_replace("<p></p>", "", $t);
	//$t = preg_replace('@<p><(h5|h6|ul|ol|dl|div)(.*?)</h([1-6])></p>@is', "<h$1>$2</h$1>", $t);
	$tags = array(
		array("<p><h5", "<p><h6", "<p><ul", "<p><ol", "<p><dl", "<p><div", "</h5></p>", "</h6></p>", "</ul></p>", "</ol></p>", "</dl></p>", "</div></p>"),
		array("<h5",    "<h6", "<ul", "<ol", "<dl", "<div", "</h5>", "</h6>", "</ul>", "</ol>", "</dl>", "</div>")
	);
	$t = str_replace($tags[0], $tags[1], $t);
	return $t;
}

function stripslashesDeep($value) {
	$value = is_array($value) ?
		array_map('stripslashesDeep', $value) :
		stripslashes($value);
	return $value;
}

function UserCalcScore($uid = '', $vars = ''){
	
	// depreciated
	
	try{ $u = new user(($uid ? $uid : $GLOBALS['usrid'])); }
	catch(Exception $e){ return false; }
	return $u->calcScore();
	
}

function object2array($mixed) {
    if(is_object($mixed)) $mixed = (array) $mixed;
    if(is_array($mixed)) {
        $new = array();
        foreach($mixed as $key => $val) {
            $key = preg_replace("/^\\0(.*)\\0/","",$key);
            $new[$key] = object2array($val);
        }
    }
    else $new = $mixed;
    return $new;       
}

function str_replace_once($search, $replace, $subject) {
    $firstChar = strpos($subject, $search);
    if($firstChar !== false) {
        $beforeStr = substr($subject,0,$firstChar);
        $afterStr = substr($subject, $firstChar + strlen($search));
        return $beforeStr.$replace.$afterStr;
    } else {
        return $subject;
    }
}

function pglabel($title){
	
	require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.img.php";
	
	$ret = '';
	
	if($pglabel['title']) $title = $pglabel['title'];
	elseif($_GET['title']) $title = $_GET['title'];
	elseif($_POST['title']) $title = $_POST['title'];
	
	//check if its an album
	if(strtolower(substr($title, 0, 8)) == "albumid:"){
		require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.albums.php";
		$albumid = substr($title, 8);
		try{ $album = new album($albumid); }
		catch(Exception $e){ unset($album); echo $e; }
		if($album){
			$ret.= '
			<img src="'.($album->coverimg ? $album->coverimg : "/images/0000001/sm/unknown.png").'"/>
			<strong><cite>'.$album->link.'</cite></strong>
			<p>'.$album->cid.'</p>
			<p>'.$album->release.'</p>
			<div style="clear:left"></div>
			';
		}
	} else {
		require_once $_SERVER['DOCUMENT_ROOT']."/pages/class.pages.php";
		if(!$title = formatName($title)) echo 'No title given';
		else {
			try{ $page = new pg($title); }
			catch(Exception $e){ unset($page); }
			$desc='';
			if($page->row['description']) $desc = links($page->row['description']);
			elseif($page->pgid) $desc = "A ".$page->type;
			else $desc = 'Not in the database. <a href="'.$page->edit_url.'" target="_blank" class="arrow-link">Add <i>'.$title.'</i></a>';
			$img='';
			if(!$page->row['rep_image']) $img = "unknown.png";
			elseif(substr($page->row['rep_image'], 0, 4) == "img:") $img = substr($page->row['rep_image'], 4);
			else $imgtag = '<img src="'.$page->row['rep_image'].'"/>';
			if($img){
				try{ $img = new img($img); }
				catch(Exception $e){ unset($img); $img = new img("unknown.png"); }
				$imgtag = '<img src="'.$img->src['box'].'"/>';
			}
			$ret.= '
			'.$imgtag.'
			<strong><cite>'.$page->link.'</cite></strong>
			'.($page->row['index_data']['release'] ? '<p>'.substr($page->row['index_data']['release'], 0, 4).'</p>' : '').'
			<p>'.$desc.'</p>
			<div style="clear:left"></div>
			';
		}
	}
	
	$ret = '<div class="pglabel">'.$ret.'</div>';
	return $ret;
}

?>