<?

use Vgsite\Page;
$page->css[] = "/bin/css/news.css";
$page->javascript.= '<script src="/bin/script/news.js" type="text/javascript"></script>'."\n";

class news {

function newsNav($query = "SELECT * FROM news WHERE unpublished != '1' ORDER BY `datetime` DESC") {
	
	$max = ($this->max ? $this->max : 20);
	$pg = (ctype_digit($_GET['pg']) ? $_GET['pg'] : 1);
	
	if(!$newsnum = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query))) {
		echo "No news found.";
	} else {
		
		$pgs = ceil($newsnum / $max);
		if($pgs > 1) {
			for($i = 1; $i <= $pgs; $i++) {
				$show = FALSE;
				if($i > ($pg - 6) && $i < ($pg + 6)) $show = TRUE;
				if($i <= 2 || $i > ($pgs - 2)) $show = TRUE;
				if($show) {
					if($i == $pg) $pgnav.= '<b>'.($i == 1 ? 'Page 1' : $i).'</b> ';
					else $pgnav.= '<a href="?pg='.$i.'&'.$gstr.'">'.($i == 1 ? 'Page 1' : $i).'</a> ';
				} else {
					$didnt_show[$i] = TRUE;
					if(!$didnt_show[$i - 1]) $pgnav.= '&middot;&middot;&middot; ';
				}
			}
			$min = ($pg - 1) * $max;
			$query.= " LIMIT $min, $max";
		} 
		
		?>
		<div class="newsnav">
			<a href="/news/new.php" class="addnews">Post something new</a>
			<div class="pagenav">
				<big><?=$newsnum?> Article<?=($newsnum != 1 ? 's' : '')?></big> 
				<?=$pgnav?>
			</div>
			<?
			
			$tags = array();
			$res = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)) {
				$rows[] = $row;
				
				$query2 = "SELECT tag FROM news_tags WHERE nid='".$row['nid']."'";
				$res2   = mysqli_query($GLOBALS['db']['link'], $query2);
				while($row2 = mysqli_fetch_assoc($res2)) {
					$tags[$row2['tag']]++;
				}
			}
			
			?>
			<div style="width:100%; float:left;">
				<?
				$this->newslist($rows);
				?>
				<div class="pagenav" style="margin:-25px 0 0;"><?=$pgnav?>&nbsp;</div>
			</div>
			<br style="clear:both;"/>
			
		</div>
		<?
		
	}
}

function newsList($rows) {
	global $usrid;
	
	$months = array("", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
	?>
	<div class="news newslist">
		<dl>
			<?
			$i = 0;
			foreach($rows as $row) {
				$i++;
				if($num = $this->numComments($row['nid'])) {
					$num = $num.' comment'.($num != 1 ? 's' : '');
					$numtitle = "$num comments about this post";
				} else {
					$num = "Discuss";
					$numtitle = "Discuss this post";
				}
				$date = substr($row['datetime'], 0, 10);
				$date = str_replace("-", "/", $date);
				list($year, $month, $day) = explode("/", $date);
				//$year = substr($year, 2);
				if(substr($month, 0, 1) == "0") $month = substr($month, 1);
				$month = $months[$month];
				list($raw, $rating, $num_ratings, $disp_rating) = getNewsRating($row['nid']);
				
				//my rating
				unset($sel); $sel = array();
				if($usrid) {
					$q = "SELECT * FROM news_ratings WHERE usrid='$usrid' AND nid='$row[nid]' LIMIT 1";
					if($urdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) $sel[$urdat->rating] = "rate-on";
				}
				?>
				<dt class="head-<?=$row['type']?>">
					<ul id="nid-item-<?=$row['nid']?>" class="nrate">
						<li class="gbrate rating">
							<a href="/news/<?=$date?>/<?=$row['description_url']?>#ratings" title="<?=$rating?>%, <?=$num_ratings?> rating<?=($num_ratings != 1 ? 's' : '')?>"><?=$disp_rating?></a>
						</li>
						<li class="gbrate gbrate-b"><a href="#" title="I hate this!" rel="0" class="rate <?=$sel[0]?>"><span>I hate this!</span></a></li>
						<li class="gbrate gbrate-g"><a href="#" title="I love this!" rel="1" class="rate <?=$sel[1]?>"><span>I love this!</span></a></li>
						<li class="date">
							<a href="/news/<?=$date?>/<?=$row['description_url']?>" title="Permanent link to this post">
								<span class="day"><?=$day?></span> 
								<span class="month"><?=$month?></span> 
								<span class="year"><?=$year?></span>
							</a>
						</li>
						<li class="author">Posted by <?=outputUser($row['usrid'], FALSE, TRUE)?></li>
						<li class="comments"><a href="/news/<?=$date?>/<?=$row['description_url']?>" title="<?=$numtitle?>"><?=$num?></a></li>
					</ul>
					<?/*
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td class="date">
								<a href="/news/<?=$date?>/<?=$row['description_url']?>" title="Permanent link to this post">
									<span class="day"><?=$day?></span> 
									<span class="month"><?=$month?></span> 
									<span class="year"><?=$year?></span>
								</a>
							</td>
							<td class="author">Posted by <?=outputUser($row['usrid'], FALSE, TRUE)?></td>
							<td class="comments"><a href="/news/<?=$date?>/<?=$row['description_url']?>" title="<?=$numtitle?>"><?=$num?></a></td>
							<td class="gbrate gbrate-g"><a href="#" title="I love this!" rel="1" class="rate <?=$sel[1]?>"><span>I love this!</span></a></td>
							<td class="gbrate gbrate-b"><a href="#" title="I hate this!" rel="0" class="rate <?=$sel[0]?>"><span>I hate this!</span></a></td>
							<td class="gbrate rating">
								<a href="/news/<?=$date?>/<?=$row['description_url']?>#ratings" title="<?=$rating?>%, <?=$num_ratings?> rating<?=($num_ratings != 1 ? 's' : '')?>"><?=$disp_rating?></a>
							</td>
						</tr>
					</table>
					*/?>
				</dt>
				<dd class="listitem"><?=$this->item($row, "item")?></dd>
				<?
			}
			?>
		</dl>
		<div class="clear">&nbsp;</div>
	</div>
	<?
}

function item($n, $disp="item") { //$n is a row of db headings and values; $disp = article (single full article) || item (compact single item)
	global $usrid;
	
	$date = substr($n['datetime'], 0, 10);
	$date = str_replace("-", "/", $date);
	
	if(file_exists($_SERVER['DOCUMENT_ROOT']."/bin/img/news/headingimg_".$n['nid'].".png")) {
		$alt = ($n['headline_img_alt'] ? $n['headline_img_alt'] : htmlSC($n['description']));
		$himg = '<div class="headingimg"><img src="/bin/img/news/headingimg_'.$n['nid'].'.png" alt="'.$alt.'" border="0"/></div>';
	} elseif($n['headline_img_squarehaven']) {
		$alt = ($n['headline_img_alt'] ? $n['headline_img_alt'] : htmlSC($n['description']));
		$himg = '<div class="headingimg"><img src="http://squarehaven.com/news/images/'.$n['headline_img_squarehaven'].'" alt="'.$alt.'" border="0"/></div>';
	}
	
	$n['content'] = str_replace("|NULL|", "||", $n['content']);
	
	if($n['type'] == "text") {
		
		list($heading, $txt1, $txt2) = explode("|--|", $n['content']);
		$heading = bb2html($heading, 'minimal');
		$heading = strip_tags($heading, "<i><del>");
		$txt1 = bb2html($txt1, "inline_citations");
		$txt1 = strip_tags($txt1, "<b><i><del><a><blockquote><span>");
		$txt1 = nl2br($txt1);
		if($txt2) {
			if($disp == "item") $txt1.= ' <a href="/news/'.$date.'/'.$n['description_url'].'#full-article" class="arrow-right" style="white-space:nowrap;">Read on</a>';
			else {
				$txt2 = bb2html($txt2);
				$txt2 = nl2p($txt2);
			}
		}
		$ret = $himg.'
		<h4>'.$heading.'</h4>
		<div class="subheading">'.$txt1.'<br style="clear:both"/></div>
		'.($txt2 && $disp=="article" ? '<div id="full-article" class="expanded">'.$txt2.'</div>' : '');
		
	}
	
	if($n['type'] == "quote") {
		
		list($x1, $x2, $quote, $quoter) = explode("|--|", $n['content']);
		$quote = bb2html($quote, "inline_citations");
		$quote = nl2br($quote);
		$quoter = bb2html($quoter, "inline_citations");
		$quoter = nl2br($quoter);
		$ret = '<div class="quote-container"><blockquote class="quote"><span>'.$quote.'</span></blockquote><div class="corner corner-tl"></div><div class="corner corner-tr"></div><div class="corner corner-br"></div><div class="corner quote-point"></div></div><div class="quoter">'.$quoter.'</div>';
		
	}
	
	if($n['type'] == "link") {
		
		list($txt1, $txt2, $url) = explode("|--|", $n['content']);
		$txt1 = bb2html($txt1);
		$txt1 = strip_tags($txt1, "<i><del>");
		if($txt2) {
			$txt2 = bb2html($txt2, "inline_citations");
			$txt2 = strip_tags($txt2, "<b><i><del><a><blockquote><span>");
			$txt2 = nl2br($txt2);
		}
		$ret = '
		<table border="0" cellpadding="0" cellspacing="0"><tr><td><h4 class="link"><a href="'.$url.'" target="_blank"><span>'.$txt1.'</span></a></h4></td></tr></table>
		'.($txt2 ? '<div class="subheading">'.$himg.$txt2.($himg ? '<br style="clear:both"/>' : '').'</div>' : '');
		
	}
	
	if($n['type'] == "image") {
		
		// IMAGE //
		
		list($heading, $text, $file, $caption) = explode("|--|", $n['content']);
		if($heading) {
			$heading = bb2html($heading);
			$heading = strip_tags($heading, "<i><del>");
		}
		if($text) {
			$text = bb2html($text, "inline_citations");
			$text = nl2br($text);
		}
		if(file_exists($_SERVER['DOCUMENT_ROOT'].substr($file, 0, -4)."_561x".substr($file, -4))) {
			$a = '<a href="'.$file.'" class="thickbox" title="'.htmlSC($caption).'">';
			$file = substr($file, 0, -4)."_561x".substr($file, -4);
		}
		$ret = '
		'.($heading ? '<h4>'.$heading.'</h4>' : '').
		'<div class="img"><table border="0" cellpadding="0" cellspacing="0"><tr><td'.(!$a ? ' class="nolink"' : '').'>'.$a.'<img src="'.$file.'" alt="'.$caption.'" border="0"/>'.($a ? '</a>' : '').'</td></tr></table></div>'.
		($text ? '<div class="subheading">'.$text.'</div>' : '');
		
	}
	
	if($n['type'] == "gallery") {
		
		// GALLERY //
		
		list($heading, $text, $dir, $thumb_num, $link_to) = explode("|--|", $n['content']);
		
		if(is_numeric($dir)) {
			$mid = $dir;
			$q = "SELECT * FROM media WHERE media_id='$mid' LIMIT 1";
			$mdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
			$dir = $mdat->directory;
			//captions
			$query = "SELECT * FROM media_captions WHERE media_id='$mid'";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)) {
				$capt[$row['file']] = htmlSC($row['caption']);
			}
		}
		
		if(substr($dir, 0, 1) != "/") $dir = "/".$dir; //add slash at beginning
		if(substr($dir, -1) != "/") $dir.= "/"; //add slash at end
		if(!$thumb_num) $thumb_num = 1;
		if($mdat->quantity && $thumb_num > $mdat->quantity) $thumb_num = $mdat->quantity;
		if($heading) {
			$heading = bb2html($heading);
			$heading = strip_tags($heading, "<i><del>");
		}
		if($text) {
			$text = bb2html($text, "inline_citations");
			$text = nl2br($text);
		}
		
		//exists?
		if(!is_dir($_SERVER['DOCUMENT_ROOT'].$dir)) {
			echo "The given directory (<a href=\"$dir\">$dir</a>) cannot be found.";
		} elseif(!is_dir($_SERVER['DOCUMENT_ROOT'].$dir."/thumbs/")) {
			echo "The given directory (<a href=\"$dir\">$dir</a>) has no thumbnail directory.";
		} else {
			
			//get imgs
			$s_dir = $_SERVER['DOCUMENT_ROOT'].$dir;
			if ($dh = opendir($s_dir)) {
				while (($file = readdir($dh)) !== false) {
					if(filetype($s_dir.$file) !== "dir") {
						$imgs[] = $file;
					}
				}
				closedir($dh);
			}
			$i_num = count($imgs);
			if ($dh = opendir($s_dir."thumbs/")) {
				while (($file = readdir($dh)) !== false) {
					if(filetype($s_dir."thumbs/".$file) !== "dir") {
						$thumbs[] = $file;
					}
				}
				closedir($dh);
			}
			$t_num = count($thumbs);
			
			if($i_num != $t_num) {
				$outp = "Error: The number of images (".$i_num.") is not equal to the number of thumbs (".$t_num.").";
			} else {
				$outp = '<ul class="news-gallery">';
				for($i = 0; $i < $thumb_num; $i++) {
					$outp.= '<li><a href="'.$dir.$imgs[$i].'" rel="news-gallery-'.$n['nid'].'" class="thickbox"'.($capt[$imgs[$i]] ? ' title="'.$capt[$imgs[$i]].'"' : '').'><img src="'.$dir.'thumbs/'.$thumbs[$i].'" alt="'.($capt[$imgs[$i]] ? ' title="'.$capt[$imgs[$i]].'"' : $thumbs[$i]).'" border="0" width="100" height="100"/></a></li>';
				}
				if($link_to) $outp.= '<li class="gallery-link"><a href="/media.php?dir='.$dir.'"><div>More</div></a></li>';
				$outp.= '</ul><div style="clear:both; height:1px;">&nbsp;</div>';
			}
		}
		
		$ret = 
		($heading ? '<h4>'.$heading.'</h4>' : '').
		($text ? '<div class="subheading">'.$text.'</div>' : '').
		$outp;
		
	}
	
	if($n['type'] == "video") {
		
		// VIDEO //
		
		list($heading, $text, $code) = explode("|--|", $n['content']);
		if($heading) {
			$heading = bb2html($heading);
			$heading = strip_tags($heading, "<i><del>");
		}
		if($text) {
			$text = bb2html($text, "inline_citations");
			$text = nl2br($text);
		}
		if($usrid == 1 && strstr($code, "youtube")) $code = "[[blocked for your protection]]<br/><code>".htmlentities($code)."</code>";
		$ret = '
		'.($heading ? '<h4>'.$heading.'</h4>' : '').'
		'.($text ? '<div class="subheading">'.$text.'</div>' : '').
		'<div class="video-code">'.$code.'</div>';
		
	}
	
	if($n['type'] == "audio") {
		
		// AUDIO //
		
		list($heading, $text, $file) = explode("|--|", $n['content']);
		if($heading) {
			$heading = bb2html($heading);
			$heading = strip_tags($heading, "<i><del>");
		}
		if($text) {
			$text = bb2html($text, "inline_citations");
			$text = nl2br($text);
		}
		$ret = 
		($heading ? '<h4>'.$heading.'</h4>' : '').
		($text ? '<div class="subheading">'.$himg.$text.($himg ? '<br style="clear:both"/>' : '').'</div>' : '').
		'<script type="text/javascript" src="https://media.dreamhost.com/ufo.js"></script>
		<p id="'.$file.'"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.</p>
		<script type="text/javascript">
		  var FO = { movie:"https://media.dreamhost.com/mediaplayer.swf",width:"320",height:"20",majorversion:"7",build:"0",bgcolor:"#FFFFFF",
		             flashvars:"file='.$file.'&showdigits=true&autostart=false" };
		  UFO.create(FO,"'.$file.'");
		</script>';
		
	}
	
	if($disp == "article") {
		$ret = '<div class="item article type-'.$n['type'].'">'.$ret.'</div>';
	} elseif($disp == "item") {
		$ret = '<div class="item type-'.$n['type'].'">'.$ret.'</div>';
	}
	
	return $ret;
	
}

function extractTags($txt) {
	
	$tags = array();
	preg_match_all('@\[(game|person)=?(.*?)\](.*?)\[/(game|person)\]@ise', $txt, $matches, PREG_SET_ORDER);
	if($matches) {
		foreach($matches as $m) {
			$type = $m[1];
			$subj = ($m[2] ? $m[2] : $m[3]);
			if($tag = $this->convertTag($subj)) $tags[] = $tag;
		}
		return $tags;
	}
	
}

function convertTag($tag) {
	
	$x = formatName($tag);
	$qg = "SELECT gid FROM games WHERE title='".mysqli_real_escape_string($GLOBALS['db']['link'], $x[0])."' LIMIT 1";
	$qp = "SELECT pid FROM people WHERE name='".mysqli_real_escape_string($GLOBALS['db']['link'], $x[0])."' LIMIT 1";
	if($gdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $qg))) {
		$tag = "gid:".$gdat->gid;
	} elseif($pdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $qp))) {
		$tag = "pid:".$pdat->pid;
	}
	return $tag;
	
}

function numComments($nid) {
	
	$q = "SELECT tid FROM forums_tags LEFT JOIN forums_posts USING(tid) WHERE tag='news:$nid'";
	if(!$num = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $num = '0';
	return $num;
	
}

}

function getNewsRating($nid) {
	
	$q = "SELECT avg(`rating`) as `rating`, count(`rating`) as `num` FROM `news_ratings` WHERE nid='$nid'";
	$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
	$raw = $dat->rating;
	$num = $dat->num;
	
	$rating = ceil($raw * 100);
	if($rating == 100) $pr = "FFF";
	elseif($rating > 83) $pr = "FFH";
	elseif($rating > 66) $pr = "FFE";
	elseif($rating > 49) $pr = "FHE";
	elseif($rating > 32) $pr = "FEE";
	elseif($rating > 15) $pr = "HEE";
	else $pr = "EEE";
	$pr = str_replace("F", '<img src="/bin/img/heart.png" alt="full heart" width="14" height="14"/>', $pr);
	$pr = str_replace("H", '<img src="/bin/img/heart.5.png" alt="half heart" width="14" height="14"/>', $pr);
	$pr = str_replace("E", '<img src="/bin/img/heart.0.png" alt="emty heart" width="14" height="14"/>', $pr);
	
	return array($raw, $rating, $num, $pr);
	
}

if( isset($_POST['set_rating']) ) {
	
	if(!$usrid) die("Error: no user id registered");
	if(!$nid = $_POST['nid']) die("Error: no news id given");
	$r = $_POST['set_rating'];
	if($r != '0' && $r != '1' && $r != 'null') die("Error: invalid rating given");
	
	$q = "DELETE FROM news_ratings WHERE usrid='$usrid' AND nid='$nid' LIMIT 1";
	mysqli_query($GLOBALS['db']['link'], $q);
	
	if($r != "null") {
		$q = sprintf("INSERT INTO news_ratings (nid, usrid, rating, ip_address) VALUES ('%s', '%s', '$r', '".$_SERVER['REMOTE_ADDR']."');", 
			mysqli_real_escape_string($GLOBALS['db']['link'], $nid),
			mysqli_real_escape_string($GLOBALS['db']['link'], $usrid)
		);
		mysqli_query($GLOBALS['db']['link'], $q);
	}
	
	die( implode("|", getNewsRating($nid)) );
	
}
	

?>