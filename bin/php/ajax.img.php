<?
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.img.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/bbcode.php";

if($_GET['load_img_data']){
	
	header("Content-type: application/json");
	$ret = array();
	
	do if($imgfile = $_GET['load_img_data']){
		
		if($pos = strpos($imgfile, "/")){
			$imgfile = substr($imgfile, 0, $pos);
		}
		$img = new img($imgfile);
		if($img->notfound){
			$ret['errors'][] = "Couldn't retrieve image data for img file '$imgfile'.";
			break;
		}
		
		$img->getSessionData();
		
		if($img->img_description){
			$bb = new bbcode();
			$bb->text = $img->img_description;
			$bb->params['inline_citations'] = true;
			$desc = $bb->bb2html();
			$desc = nl2br($desc);
		}
		
		require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.tags.php";
		$tag_subj = 'images_tags:img_id:'.$img->img_id;
		$tags = new tags($tag_subj);
		$allow_add = $tags->allow_add ? true : false;
		$tags->allow_add = false;
		
		$x = $img->img_width;
		$y = $img->img_height;
		$x_max = $_GET['x_max'] ? $_GET['x_max'] : $img->img_width;//$ret['errors'][] = "X: $x, $x_max";
		$y_max = $_GET['y_max'] ? $_GET['y_max'] : $img->img_height;//$ret['errors'][] = "Y: $y, $y_max";
		if($x > $x_max || $y > $y_max){
			//$scale = ($x > $y) ? $x_max/$x : $y_max/$y;
			$scale = $x_max/$x;
			$x = ceil($x * $scale);
			$y = ceil($y * $scale);
			//$ret['errors'][] = "NEW X: $x";$ret['errors'][] = "NEW Y: $y";
		}
		
		$imgtitle = trim($img->img_title);
		if($imgtitle==''){
			$imgtitle = substr($img->img_name, 0, -4);
			$imgtitle = str_replace("-", " ", $imgtitle);
			$imgtitle = str_replace("_", " ", $imgtitle);
		}
		
		$ret['img_width'] = $img->img_width;
		$ret['img_height'] = $img->img_height;
		$ret['img'] = '<img src="'.$img->src[0].'" alt="'.htmlSC($img->img_title).'" width="'.$x.'" height="'.$y.'" border="0" class="'.($scale ? 'scaled' : '').'" onclick="'.($scale ? 'lightbox.toggleScaled({scaled_x:'.$x.',scaled_y:'.$y.',full_x:'.$img->img_width.',full_y:'.$img->img_height.'});' : '').'"/>';
		//if($scale) $ret['img_full'] = '<img src="'.$img->src[0].'" alt="'.htmlSC($img->img_title).'" width="'.$img->img_width.'" height="'.$img->img_height.'" border="0" class="lightbox-fullimg togglescaled"  onclick="lightbox.toggleScaled(\'scaled\')"/>';
		//if($scale) $ret['img'] = '<a href="'.$img->src[0].'" target="_blank" class="scaled">'.$ret['img'].'</a>';
		$ret['label'] =
			'<ul>'.
				'<li class="h">'.
					'<h5>'.$imgtitle.'</h5>'.
					$desc.
				'</li><li class="uplby">'.
					'Uploaded by '.outputUser($img->usrid, false).'<br/>'.
					'<abbr title="'.$img->img_timestamp.'" style="border-width:0">'.timeSince($img->img_timestamp).' ago</abbr><br/>'.
					//'to <a href="/image/-/session/'.$img->sessid.'">'.$img->session_row['img_session_description'].'</a>'.
				'</li>'.
				'<li class="tags taglist"><h6>Tags</h6>'.$tags->taglist().($allow_add ? '<ul class="taglist"><li class="sugg"><a class="suggtaglink" onclick="lightbox.close(); img.edit(\''.$imgfile.'\');"><b>+</b><u>Add a Tag</u></a></li></ul>' : '').'</li>'.
				'<li class="permalink"><a href="'.$img->src['url'].'" style="text-decoration:none;">&infin; <u>Permalink</u></a></li>'.
				($usrrank >= 4 || $img->usrid == $usrid ? '<li><a onclick="lightbox.close(); img.edit(\''.$imgfile.'\');" class="red">Edit image</a></li>' : '').
			'</ul>';
		
		//+1 view 
		$q = "UPDATE images SET img_views = '".++$img->img_views."' WHERE img_name = '".mysqli_real_escape_string($GLOBALS['db']['link'], $img->img_name)."' LIMIT 1";
		mysqli_query($GLOBALS['db']['link'], $q);
		
	} while(false);
	
	die (json_encode($ret));
	
}

if($_POST['_action'] == "load_ins_form"){
	
	$query = "SELECT * FROM images_sessions WHERE usrid='$usrid' ORDER BY img_session_created DESC";
	$sessres = mysqli_query($GLOBALS['db']['link'], $query);
	$num_sessions = mysqli_num_rows($sessres);
	
	?>
	
	<div class="container">
		<ul class="nav">
			<li><a href="/upload.php" title="Upload images" target="_blank" onclick="img.closeForm()"><span style="background-position:-240px -30px;">Upload images</span></a></li>
			<li><a href="#search" title="Search all uploads"><span style="background-position:-90px -30px;">Search all uploads</span></a></li>
			<li><a href="#closeFrame" title="Close this window" onclick="img.closeForm()"><span style="background-position:-30px -30px;">Close</span></a></li>
		</ul>
		<?
		
		if($_POST['form_key'] == "img_session_id" && $sessid = $_POST['form_val']){
			
			$query = "SELECT * FROM images_sessions WHERE img_session_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $sessid)."' LIMIT 1";
			$sessres = mysqli_query($GLOBALS['db']['link'], $query);
			if(!mysqli_num_rows($sessres)) die("Couldn't find session data fro ID # $sessid</div>");
			$sess = mysqli_fetch_object($sessres);
			$query = "SELECT * FROM images WHERE img_session_id = '".$sessid."'";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			if($num = mysqli_num_rows($res)){
				?>
				<ul class="imginslist imglist">
					<?
					while($row = mysqli_fetch_assoc($res)){
						$img = new img($row['img_name']);
						$src = $img->src;
						?>
						<li>
							<dl title="<?=($row['img_session_description'] ? htmlSC($row['img_session_description']) : $row['img_name'])?>" class="tooltip">
								<dt><?=$row['img_name']?></dt>
								<dd class="img"><img src="<?=$src['tn']?>" border="0"/></dd>
							</dl>
						</li>
						<?
					}
					?>
				</ul>
				<?
			}
			
		} elseif($num_sessions){
			?>
			<ul class="imginslist sessionlist">
				<?
				while($row = mysqli_fetch_assoc($sessres)){
					$file = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], "SELECT img_name FROM images WHERE img_session_id = '".$row['img_session_id']."' LIMIT 1"));
					$img = new img($file->img_name);
					if($img->notfound) continue;
					$src = $img->src;
					?>
					<li onclick="img.loadForm('img_session_id', '<?=$row['img_session_id']?>')">
						<dl>
							<dt><?=$row['img_session_description']?></dt>
							<dd class="img"><img src="<?=$src['tn']?>" border="0"/></dd>
						</dl>
					</li>
					<?
				}
				?>
			</ul>
			<?
		}
		?>
	</div>
	<?
	
	exit;
	
}
?>