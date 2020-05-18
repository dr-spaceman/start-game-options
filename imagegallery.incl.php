<?
use Vgsite\Page;
use Vgsite\Image;

if($_GET['load']){
	
	header("Content-type: application/json");
	$ret = array();
	
	parse_str($_GET['load'], $in);
	
	if(!$setid = $in['tag']){
		$ret['errors'][] = "Gallery parameters <code>[settype]</code> not set correctly. [E1]";
		die(json_encode($ret));
	}
	
	$urlapnd = "/tag/".formatNameURL($setid);
	$setid = formatName($setid);
	$title = $setid;
	
	$where = '';
	if($cats = $in['cat']){
		foreach($cats as $c){
			//if(!is_int($c)) continue;
			$where.= "img_category_id = '$c' OR ";
		}
		$where = "AND (" . substr($where, 0, -4) . ")";
	}
	$query = "SELECT * FROM images_tags LEFT JOIN images USING (img_id) WHERE (tag = '".mysqli_real_escape_string($GLOBALS['db']['link'], $setid)."' OR tag LIKE '".mysqli_real_escape_string($GLOBALS['db']['link'], $setid)."|%') $where ORDER BY img_timestamp ASC";
	if(!$num_imgs = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $query))){
		$ret['formatted'] = "<i>No images found.</i>";
	}
	
	$pgn = (integer)$in['pgn']; //page number
	if(!$pgn || $pgn < 1) $pgn = 1;
	$min = ($pgn - 1) * 30;
	$max = $min + 30;
	if($max > $num_imgs) $max = $num_imgs;
	
	$ret['nav'] = ($min + 1).' &ndash; '.$max.' of <b>'.$num_imgs.'</b> image'.($num_imgs != 1 ? 's' : '');
	$ret['pagination'] = '<li>'.($pgn == 1 ? '<span class="arrow-left" style="color:#AAA;">Previous</span>' : '<a href="?pgn='.($pgn - 1).'" rel="'.($pgn - 1).'" class="arrow-left pgn igch">Previous</a>').'</li><li>'.($max < $num_imgs ? '<a href="?pgn='.($pgn + 1).'" rel="'.($pgn + 1).'" class="arrow-right pgn igch">Next</a>' : '<span class="arrow-right" style="color:#AAA;">Next</span>').'</li>';
	
	if($num_imgs){
		
		$ret['formatted'] = '<ul>';
		
		$query.= " LIMIT $min, 30";//$ret['errors'][] = $query;die(json_encode($ret));
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		while($image = mysqli_fetch_assoc($res)){
			
			$img = new img($image['img_name']);
			if($img->notfound) continue;
			
			$fsize = round($img->img_size * .0009765625);
			if($fsize > 1024) $fsize = round($fsize * .0009765625, 2).'mb';
			else $fsize.= 'kb';
			$alt = $img->img_name.'<br/>'.$img->img_width.' &times; '.$img->img_height.'<br/>'.$fsize;
			
			$desc = '';
			if($img->img_description){
				$bb = new bbcode($img->img_description);
				$bb->params['inline_citations'] = true;
				$desc = $bb->bb2html();
			}
			
			$ret['formatted'].= '
				<li class="tn">
					<a href="'.$img->src['url'].$urlapnd.'" title="'.$alt.'" class="tooltip imglink imgupl"><img src="'.$img->src['tn'].'" width="100" height="100" border="0" alt="'.htmlSC($img->img_title).'"/></a>
				</li>
				<li class="sm">
					<dl>
						<dt><a href="'.$img->src['url'].$urlapnd.'" title="'.$alt.'" class="tooltip imglink imgupl"><img src="'.$img->src['sm'].'" border="0" alt="'.htmlSC($img->img_title).'"/></a></dt>
						<dd><h5>'.($img->img_title ? $img->img_title : $img->img_name).'</h5></dd>
						'.($img->img_description ? '<dd class="desc">'.$desc.'</dd>' : '').'
						<dd class="info">
							'.outputUser($img->usrid).' &middot; 
							<time datetime="'.$img->img_timestamp.'">'.formatDate($img->img_timestamp, 7).'</time>
						</dd><dd class="info">
							'.($img->img_views ? '<b>'.$img->img_views.'</b>' : 'No').' views &middot; 
							'.($num_comments ? '<a href="/image/'.$img->img_name.'/session/'.$sessid.'">'.$num_comments.' comments</a>' : 'No comments').'
						</dd>
					</dl>
				</li>
			';
		}
		
		$ret['formatted'].= '</ul>';
		
	}
	
	die(json_encode($ret));
	
}

$page->title = "Videogam.in Media";
$page->width = "fixed";
$page->freestyle.= '
	h1 { margin-bottom:30px; }
	#twitter_div { display:none; }
	#imggallerycontainer { position:relative; }
	#imggallerynav { position:absolute; z-index:3; left:0; width:150px; font-size:14px; text-align:right; line-height:18px; }
	#imggallerynav.scroll { padding:20px 0 0; }
	#imggallerynav .viewmode a span { background:url("/bin/img/icons/sprites_img.png") no-repeat 0 0; opacity:.8; }
	#imggallerynav .viewmode.on a span { opacity:1; }
	#imggallerynav label a { text-decoration:none; }
	#imggallerynav h6 { margin:0; padding:0; font-weight:normal; font-size:15px; white-space:nowrap; }
	#imggallerynav ul.pagination { float:right; width:100%; margin:5px 0 0; padding:0; list-style:none; background-color:RGBA(0,0,0,.1); border-radius:3px; text-align:left; }
	#imggallerynav ul.pagination li { display:inline-block; margin:0; padding:0 9px; color:#999; line-height:26px; }
	#imggallerynav ul.pagination li:last-child { float:right; }
	#imggallery { margin:0 0 0 190px; min-height:300px; }
	#imggallery > ul { margin:0 0 0 -30px; padding:0; list-style:none; }
	#imggallery li.sm { margin:0 0 20px 30px; padding:0; display:inline-block; width:345px; vertical-align:top; }
	#imggallery li.tn { display:none; margin:0; padding:0; }
	#imggallery.viewmode-tn .tn { display:inline-block; }
	#imggallery.viewmode-tn .sm { display:none; }
	#imggallery .tn a { display:block; margin:0 15px 15px 0; }
	#imggallery a.imglink:hover {}
	#imggallery dl { margin:0; padding:0; }
	#imggallery dl > * { margin:5px 0 0; padding:0; }
	#imggallery dd.info { color:#666; font-size:12px; }
	#imggallery .sm h5 { margin:0; padding:0; color:black; font-size:16px; font-weight:bold; }
	#imggallery .sm dt {}
	#imggallery.loading ul { opacity:.3; }
	#imggalleryloading { display:none; position:fixed; left:50%; top:50%; margin:-100px 0 0 -75px; padding:20px 20px 20px 40px; font-size:15px; background:url(/bin/img/icons/sprites/goomba.gif) no-repeat 20px center; background-color:white; background-color:RGBA(255,255,255,.6); }
	#imggallery.loading + #imggalleryloading { display:block; }
';
$page->javascripts[] = "/image.js";
$page->meta_data.= '<meta name="fragment" content="!">';

if($settype != "session" && $settype != "tag" && $settype != "term") $settype = '';
if(!$settype || !$setid){
	echo '<h1>Error</h1>Gallery parameters not set correctly.';
	$page->footer();
	exit;
}

$imgs = array();
$img_cats = array();
$sess = '';

if($settype == "session"){
	
	$q = "SELECT * FROM images_sessions WHERE img_session_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $setid)."' LIMIT 1";
	if(!$sess = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
		require($_SERVER['DOCUMENT_ROOT']."/404.php");
		exit;
	}
	
	$title = $sess['img_session_description'];
	
	$query = "SELECT * FROM images WHERE img_session_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $setid)."' ORDER BY `sort`";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	$num_imgs = mysqli_num_rows($res);
	while($row=mysqli_fetch_assoc($res)){
		$imgs[] = $row;
		$img_cats[$row['img_category_id']]++;
	}
	
} elseif($settype == "tag" || $settype == "term"){
	
	$urlapnd = "/tag/".formatNameURL($setid);
	$setid = formatName($setid);
	$title = $setid;
	
	$img_names = array();
	
	$query = "SELECT * FROM images_tags LEFT JOIN images USING (img_id) WHERE tag = '".mysqli_real_escape_string($GLOBALS['db']['link'], $setid)."' OR tag LIKE '".mysqli_real_escape_string($GLOBALS['db']['link'], $setid)."|%' ORDER BY img_timestamp ASC";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	$num_imgs = mysqli_num_rows($res);
	while($row = mysqli_fetch_assoc($res)){
		$imgs[] = $row;
		$img_names[] = $row['img_name'];
		$img_cats[$row['img_category_id']]++;
	}
	
	if($settype == "term"){
		
		$urlapnd = "/term/".formatNameURL($setid);
		
		//get images by description
		$query = "SELECT * FROM images WHERE img_title LIKE '%".mysqli_real_escape_string($GLOBALS['db']['link'], $setid)."%' OR img_description LIKE '%".mysqli_real_escape_string($GLOBALS['db']['link'], $setid)."%'";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)){
			if(in_array($row['img_name'], $img_names)) continue;
			$num_imgs++;
			$imgs[] = $row;
			$img_names[] = $row['img_name'];
			$img_cats[$row['img_category_id']]++;
		}
		
	}
	
} else {
	echo '<h1>Error</h1>Gallery parameters <code>[settype]</code> not set correctly.';
	$page->footer();
	exit;
}

$page->title = $title." - Videogam.in Media";
$page->header();

?>
<h1><?=$title?></h1>
<?

if(!$num_imgs){
	echo "No images found for this set, session, or tag.";
	$page->footer();
	exit;
}

$pgn = (integer) $_GET['pgn']; //page number
if(!$pgn || $pgn < 1) $pgn = 1;
$min = ($pgn - 1) * 30;
$max = $min + 30;
if($max > $num_imgs) $max = $num_imgs;

$cat = $_GET['cat'];

?>
<input type="hidden" name="settype" value="<?=htmlSC($settype)?>" class="hashel" id="settype"/>
<input type="hidden" name="setid" value="<?=htmlSC($setid)?>" class="hashel" id="setid"/>

<div id="imggallerycontainer">
	<form action="<?=$_SERVER['SCRIPT_URL']?>" method="get" name="imggallerynav" id="imggallerynav">
		<div class="pgnav" style="float:right; border:1px solid #CCC;">
			<ul>
				<li class="viewmode sprite on" id="viewmode-sm"><a href="#sm" title="view mode: details"><span style="">Details</span></a></li>
				<li class="viewmode sprite" id="viewmode-tn"><a href="#tn" title="view mode: thumbnails"><span style="background-position:0 -30px;">Thumbnails</span></a></li>
			</ul>
		</div>
		<br style="clear:both;"/><br/>
		
		<h6><?=($min + 1)?> &ndash; <?=$max?> of <b><?=$num_imgs?></b> image<?=($num_imgs != 1 ? 's' : '')?></h6>
		<input type="hidden" name="pgn" value="<?=$pgn?>" id="pgn"/>
		<ul class="pagination">
			<li><?=($pgn == 1 ? '<span class="arrow-left" style="color:#AAA;">Previous</span>' : '<a href="?pgn='.($pgn - 1).'" rel="'.($pgn - 1).'" class="arrow-left pgn igch">Previous</a>')?></li>
			<li><?=($max < $num_imgs ? '<a href="?pgn='.($pgn + 1).'" rel="'.($pgn + 1).'" class="arrow-right pgn igch">Next</a>' : '<span class="arrow-right" style="color:#AAA;">Next</span>')?></li>
		</ul>
		<br style="clear:both"/><br/>
		
		<?
		if(count($img_cats) > 1){
			$images_categories[0] = array("img_category_id" => 0, "img_category" => "Unclassified", "img_category_description" => "");
			$query = "SELECT * FROM images_categories ORDER BY sort";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)){
				$images_categories[$row['img_category_id']] = $row;
			}
			foreach($images_categories as $ic){
				if(!$img_cats[$ic['img_category_id']]) continue;
				$name = str_replace(" ", "", $ic['img_category']);
				$ch = 'checked="checked"';
				if($cat && !in_array($ic['img_category_id'], $cat)) $ch = '';
				echo '<label class="a" style="display:block; clear:both; text-decoration:none; width:220px; margin-left:-70px;"><input type="checkbox" '.$ch.' class="imgcat igch" name="cat[]" value="'.$ic['img_category_id'].'" style="float:right;"/><u>'.$ic['img_category'].'</u> <span style="color:#888;">('.$img_cats[$ic['img_category_id']].')</span></label>';
			}
		}
		?>
	</form>
	<div id="imggallery">
		<ul>
			<?
			$i = 0;
			foreach($imgs as $image){
				
				if($cat && !in_array($image['img_category_id'], $cat)) continue;
				
				$i++;
				if($i <= $min) continue;
				if($i > $max) break;
				
				$img = new img($image['img_name']);
				if($img->notfound) continue;
				
				$fsize = round($img->img_size * .0009765625);
				if($fsize > 1024) $fsize = round($fsize * .0009765625, 2).'mb';
				else $fsize.= 'kb';
				$alt = $img->img_name.'<br/>'.$img->img_width.' &times; '.$img->img_height.'<br/>'.$fsize;
				
				$desc = '';
				if($img->img_description){
					$bb = new bbcode($img->img_description);
					$bb->params['inline_citations'] = true;
					$desc = $bb->bb2html();
				}
				
				?>
				<li class="tn">
					<a href="<?=$img->src['url'].$urlapnd?>" title="<?=$alt?>" class="tooltip imglink imgupl"><img src="<?=$img->src['tn']?>" width="100" height="100" border="0" alt="<?=htmlSC($img->img_title)?>"/></a>
				</li>
				<li class="sm">
					<dl>
						<dt><a href="<?=$img->src['url'].$urlapnd?>" title="<?=$alt?>" class="tooltip imglink imgupl"><img src="<?=$img->src['sm']?>" border="0" alt="<?=htmlSC($img->img_title)?>"/></a></dt>
						<dd><h5><?=($img->img_title ? $img->img_title : $img->img_name)?></h5></dd>
						<?=($img->img_description ? '<dd class="desc">'.$desc.'</dd>' : '')?>
						<dd class="info">
							<?=outputUser($img->usrid)?> &middot; 
							<time datetime="<?=$img->img_timestamp?>"><?=formatDate($img->img_timestamp, 7)?></time>
						</dd><dd class="info">
							<?=($img->img_views ? '<b>'.$img->img_views.'</b>' : 'No')?> views &middot; 
							<?=($num_comments ? '<a href="/image/'.$img->img_name.'/session/'.$sessid.'">'.$num_comments.' comments</a>' : 'No comments')?>
						</dd>
					</dl>
				</li>
				<?
			}
			?>
		</ul>
	</div>
</div><!--#imggallerycontainer-->
<?

$page->footer();