<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
$page = new page;
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.img.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.tags.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");

if(!$img_name = $_GET['img_name']){
	$path = $_SERVER['SCRIPT_URL'];
	if(substr($path, 0, 1) == "/") $path = substr($path, 1);
	$patharr = explode("/", $path);
	$img_name = $patharr[1];
	$settype = $patharr[2];
	//string together last two pieces in case of / in the tag name
	// ie Kingdom_Hearts:_358/2_Days
	$setid = $patharr[3].($patharr[4] ? "/".$patharr[4] : '');
}

if($settype && $img_name == "-"){
	require("imagegallery.incl.php");
	exit;
}

$img = new img($img_name);
if($img->notfound){
	if(isset($_GET['showimage'])){
		header("Content-Type: image/png");
		readfile($_SERVER['DOCUMENT_ROOT']."/bin/img/mascot.png");
	}
	else $page->kill(404);
	exit;
}

if(isset($_GET['showimage'])){
	header("Content-Type: image/".$img->img_minor_mime);
	$src = $img->src[0];
	if($_GET['size'] && in_array($_GET['size'], array_keys($img->src))) $src = $img->src[$_GET['size']];
	readfile($_SERVER['DOCUMENT_ROOT'].$src);
	exit;
}

if(isset($_GET['download'])){

  // Must be fresh start 
  if( headers_sent() ) 
    die('Headers Sent'); 

  // Required for some browsers 
  if(ini_get('zlib.output_compression')) 
    ini_set('zlib.output_compression', 'Off');
    
  // Parse Info / Get Extension 
  $fsize = filesize($fullPath); 
  $path_parts = pathinfo($fullPath); 
  $ext = strtolower($path_parts["extension"]); 

  header("Pragma: public"); // required 
  header("Expires: 0"); 
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
  header("Cache-Control: private",false); // required for certain browsers 
  header("Content-Type: image/".$img->img_minor_mime);
  header("Content-Disposition: attachment; filename=\"".basename($img->src[0])."\";" ); 
  header("Content-Transfer-Encoding: binary"); 
  header("Content-Length: ".$img->img_size); 
  ob_clean(); 
  flush(); 
  readfile($_SERVER['DOCUMENT_ROOT'].$img->src[0]);
  
  exit;

}

$img_title = ($img->img_title ? $img->img_title : substr(substr($img->img_name, 0, -4), 0, 50));

$page->title = htmlSC($img_title)." / Videogam.in Media";
$page->width = "fixed";
$page->freestyle.= '
	#img { float:left; }
	#img a { display:block; }
	#sidecont { width:300px; margin:0 0 0 665px; }
	#sidecont #imgdesc { margin:0 0 20px; padding:0 0 20px; background:url("/bin/img/hr_inset.png") repeat-x 0 100%; font-size:14px; }
	#sidecont big { font-size:14px; color:#666; }
	#sidecont big b { font-weight:normal; color:black; }
	#sidecont dt, #sidecont h6 { margin:20px 0 0; padding:7px 0 0; background:url("/bin/img/hr_inset.png") repeat-x 0 0; font-weight:bold; font-size:15px; }
	#sidecont dd { margin:5px 0 0; padding:0; }
	#sidecont .dispcode { border-width:1px; border-style:solid; border-color:#CCC #F5F5F5 #F5F5F5 #CCC; width:298px; height:19px; overflow:hidden; }
	#sidecont textarea { width:2000px; padding:2px; font-size:11px; white-space:nowrap; overflow:hidden; border-width:0; }
	#sidecont .tag { color:#BBB; }
	#imgnav h6 { margin-bottom:5px; }
	#imgnav ul { margin:0; padding:0; list-style:none; height:94px; }
	#imgnav li { margin:0; padding:0; float:left; }
	#imgnav li.next { float:right; }
	#imgnav li img { width:145px; height:94px; }
';

$page->header();

?>
<h1><?=$img_title?></h1>

<div id="img">
	<?
	if($img->optimized){
		?>
		<a href="<?=$img->src[0]?>" title="<?=htmlsc($img_title)?>" rel="shadowbox"><img src="<?=$img->src['op']?>" alt="<?=htmlsc($img_title)?>" border="0"/></a>
		<?
	} else {
		?>
		<img src="<?=$img->src[0]?>" alt="<?=htmlsc($img_title)?>"/>
		<?
	}
	?>
</div>

<div id="sidecont">
	<h6 style="margin-bottom:5px"><?=$img->categoryName()?></h6>
	<?=($img->img_description ? '<div id="imgdesc">'.bb2html($img->img_description).'</div>' : '')?>
	<big>Uploaded by <b><?=outputUser($img->usrid)?></b> on <b><?=formatDate($img->img_timestamp, 7)?></b></big>
	
	<div id="imgnav">
		<?
		//photonav
		$imgnav = array();
		if($settype != "session" && $settype != "tag") $settype = '';
		if(!$settype) $settype = "session";
		do if($settype == "session"){
			$q = "SELECT * FROM images_sessions WHERE img_session_id = '".$img->sessid."' LIMIT 1";
			if(!$sess = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) break;
			?>
			<h6><a href="/image/-/session/<?=$img->sessid?>"><?=$sess['img_session_description']?></a></h6>
			<?
			$query = "SELECT * FROM images WHERE img_session_id = '".$img->sessid."' ORDER BY `sort`";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			$i = 0;
			while($row=mysqli_fetch_assoc($res)){
				$imgnav[$i] = $row;
				if($row['img_name'] == $img_name) $thisi = $i;
				$i++;
			}
		} while(false);
		
		do if($settype == "tag"){
			$query = "SELECT * FROM images_tags LEFT JOIN images USING (img_id) WHERE tag = '".mysqli_real_escape_string($GLOBALS['db']['link'], $setid)."' OR tag LIKE '".mysqli_real_escape_string($GLOBALS['db']['link'], $setid)."|%' ORDER BY img_timestamp";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			if(!mysqli_num_rows($res)) break;
			
			echo '<h6><a href="/image/-/tag/'.$setid.'">'.$setid.'</a></h6>';
			
			$i = 0;
			while($row = mysqli_fetch_assoc($res)){
				$imgnav[$i] = $row;
				if($row['img_name'] == $img_name) $thisi = $i;
				$i++;
			}
		} while(false);
		
		if($imgnav){
			?>
			<ul>
				<?
				$i = -1;
				foreach($imgnav as $row){
					$i++;
					if($i > ($thisi + 1)) break;
					if($i < ($thisi - 1)) continue;
					if($i == $thisi) continue;
					$img_s = new img($row['img_name']);
					echo '<li class="'.($i < $thisi ? "prev" : "next").'"><a href="'.$img_s->src['url'].'"><img src="'.$img_s->src['ss'].'"/></a></li>';
				}
				?>
			</ul>
			<?
		}
		?>
	</div><!--#imgnav-->
	
	<dl>
		<dt>Tags</dt>
		<div class="tags taglist">
			<?
			$tags = new tags("images_tags:img_id:".$img->img_id);
			echo $tags->taglist(0, 0);
			echo $tags->suggestForm();
			?>
		</div>
		<?
		/*$query = "SELECT * FROM images_tags WHERE img_id = '".$img->img_id."'";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)){
			$tagurl = formatNameURL($row['tag']);
			if($pos = strpos($tagurl, "|")) $tagurl = substr($tagurl, 0, $pos);
			$o_tags.= '<dd class="tag">[['.$row['tag'].']] [<a href="/image/-/tag/'.$tagurl.'">images</a>]</dd>';
		}
		if($o_tags) echo bb2html($o_tags, "pages_only");
		else echo '<dd>No tags</dd>';*/
		
		$capt = ($img->img_title ? str_replace("|", "/", $img->img_title) : '');
		
		?>
		<dt>Embed Code (Videogam.in only)</dt>
		<dd class="dispcode"><textarea onclick="$(this).select()">{img:<?=$img->img_name?>}</textarea></dd>
		<dd class="dispcode"><textarea onclick="$(this).select()">{img:<?=$img->img_name?>|thumb|right|caption=<?=$capt?>}</textarea></dd>
		<dt>Permanent Link</dt>
		<dd class="dispcode"><textarea onclick="$(this).select()">http://videogam.in<?=$img->src['url']?></textarea></dd>
		<dt>HTML</dt>
		<dd class="dispcode"><textarea onclick="$(this).select()"><a href="http://videogam.in<?=$img->src['url']?>"><img src="http://videogam.in<?=$img->src['optimized']?>" alt="<?=htmlSC($img->img_title)?>" border="0"/></a></textarea></dd>
		<?=($img->usrid == $usrid || $usrrank >= 4 ? '<dt>Edit</dt><dd><a href="javascript:img.edit(\''.$img->img_name.'\')">Edit this image</a></dd><dd><a href="/uploads.php?sessid='.$img->sessid.'#img-'.$img->img_id.'">Edit this image session</a></dd>' : '')?>
	</dl>
</div>

<br style="clear:both"/>

<?

// ++ view
$q = "UPDATE images SET img_views = '".++$img->img_views."' WHERE img_name = '".mysqli_real_escape_string($GLOBALS['db']['link'], $img->img_name)."' LIMIT 1";
mysqli_query($GLOBALS['db']['link'], $q);

$page->footer();

?>