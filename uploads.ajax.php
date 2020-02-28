<?

if($_POST['iledit']){
	
	// inline edit submissions
	
	header("Content-type: application/json");
	$ret = array();
	
	parse_str($_POST['iledit']);
	if(!$img_session_id){ $ret['error'] = "Can't have a blank session title"; break; }
	
	do if($img_session_description){
		
		$d = formatName($img_session_description);
		if($d == ""){ $ret['error'] = "Can't have a blank session title"; break; }
		$q = "UPDATE images_sessions SET img_session_description = '".mysqli_real_escape_string($GLOBALS['db']['link'], $d)."' WHERE img_session_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $img_session_id)."' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $ret['error'] = 'Database error: '.mysqli_error($GLOBALS['db']['link']);
		else $ret['res'] = $d;
		
	} else {
		
		if(!$img_id){ $ret['error'] = "No image ID given"; break; }
		
		$field = array();
		if(isset($img_title)) $field = array("img_title", $img_title);
		if(isset($img_description)) $field = array("img_description", $img_description);
		if(isset($img_category_id)) $field = array("img_category_id", $img_category_id);
		
		if(!$field){ $ret['error'] = "The field name could not be determined."; break; }
		
		$q = "UPDATE images SET `".mysqli_real_escape_string($GLOBALS['db']['link'], $field[0])."` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $field[1])."' WHERE img_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $img_id)."' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $ret['error'] = 'Database error: '.mysqli_error($GLOBALS['db']['link']);
		else{
			if($field[0] == "img_description"){
				$bb = new bbcode();
				$bb->params['inline_citations'] = true;
				$field[1] = $bb->bb2html($field[1]);
			}
			$ret['res'] = $field[1];
		}
	
	} while(false);
	
	if($img_category_id){
		$catg = imgGetCategories();
		$ret['res'] = '<strong>'.$catg[$img_category_id]['img_category'].'</strong>';
	}
	
	die(json_encode($ret));
	
}

if($img_name = $_POST['gen_img_code']){
	
	do if(is_array($img_name)){
		
		$img_names = $img_name;
		
		if(count($img_names) == 1){
			$img_name = $img_names[0];
			continue;
		}
		
		//gallery
		
		$q = "SELECT * FROM images LEFT JOIN images_sessions USING(img_session_id) WHERE img_name='".mysqli_real_escape_string($GLOBALS['db']['link'], $img_names[0])."' LIMIT 1";
		$sess = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
		
		foreach($img_names as $img_name){
			$q = "SELECT * FROM images WHERE img_name='".mysqli_real_escape_string($GLOBALS['db']['link'], $img_name)."' LIMIT 1";
			$img = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
			$o_img_names.= '&nbsp;{img:'.$img_name.($img['img_title'] ? '|caption='.$img['img_title'] : '').'}'."\n";
		}
		?>
		<dt>Display Code</dt>
		<dd>
			<div id="gen-img-code">
				Use these codes to display these images in a forum post, Sblog post, page content, or external site.
				<h6>At Videogam.in</h6>
				<dl>
					<dd><textarea style="height:56px;">[gallery|caption=<?=$sess->img_session_description?>]<?="\n".$o_img_names?>[/gallery]</textarea></dd>
				</dl>
				<h6>At Videogam.in and elsewhere</h6>
				<dl>
					<dt>URL</dt>
					<dd><textarea>http://videogam.in/image/-/session/<?=$sess->img_session_id?></textarea></dd>
					<dt>HTML</dt>
					<dd><textarea>&lt;a href="http://videogam.in/image/-/session/<?=$sess->img_session_id?>"><?=$sess->img_session_description?>&lt;/a></textarea></dd>
				</dl>
			</div>
		</dd>
		<?
		
		exit;
		
	} while(false);
	
	$img = new img($img_name);
	
	$capt = ($img->img_title ? str_replace("|", "/", $img->img_title) : "IMAGE CAPTION");
	
	?>
	<dt>Display Code</dt>
	<dd>
		<div id="gen-img-code">
			Use these codes to display this image in a forum post, Sblog post, page content, or external site.
			<h6>At Videogam.in</h6>
			<dl>
				<dd><textarea>{img:<?=$img_name?>}</textarea></dd>
				<dd><textarea>{img:<?=$img_name?>|thumbnail|left|caption=<?=$capt?>}</textarea></dd>
			</dl>
			<h6>At Videogam.in and elsewhere</h6>
			<dl>
				<dt>URL</dt>
				<dd><textarea>http://videogam.in<?=$img->src['url']?></textarea></dd>
				<dt>Markdown</dt>
				<dd><textarea>[![<?=($img->img_title ? $img->img_title : $img->img_name)?>](http://videogam.in<?=$img->src['optimized']?>)](http://videogam.in<?=$img->src['url']?>)</textarea></dd>
				<dt>HTML</dt>
				<dd><textarea><a href="http://videogam.in<?=$img->src['url']?>"><img src="http://videogam.in<?=$img->src['optimized']?>" alt="<?=htmlSC($img->img_title)?>" border="0"/></a></textarea></dd>
			</dl>
		</div>
	</dd>
	<?
	exit;
	
}

if($_POST['sort']){
	
	$sort = str_replace("img-", "", $_POST['sort']);
	$sort = explode(",", $sort);
	foreach($sort as $img_id){
		$q = "UPDATE images SET `sort` = '".++$i."' WHERE img_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $img_id)."' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Couldn't save sort state because of a database error :(");
	}
	
	die("ok");
	
}

if($_POST['load_tags']){
	
	$str = $_POST['load_tags'];
	$tags = array();
	$imgids = array();
	$imgids = explode(",", $str);
	foreach($imgids as $imgid){
		$imgid = trim($imgid);
		$query = "SELECT tag FROM images_tags WHERE img_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $imgid)."'";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)){
			if(!in_array($row['tag'], $tags)) $tags[] = trim($row['tag']);
		}
	}
	
	if(!$tags) die('<ul><li class="null"><i>None</i></li></ul>');
	
	array_multisort(array_map('strtolower', $tags), $tags);
	
	die('<ul>'.formatImgTaglist($tags).'</ul>');
	
}

if($_POST['mass_add_tag']){
	
	header("Content-type: application/json");
	$ret = array();
	
	$tag = trim($_POST['mass_add_tag']);
	if($tag == '') exit;
	$ret['formatted'] = formatImgTaglist(array($tag));
	
	die(json_encode($ret));
	
}

if($_POST['mass_rm_tag'] && $_POST['ivar']){
	
	header("Content-type: application/json");
	$ret = array();
	
	$tag       = $_POST['mass_rm_tag'];
	$imgidsStr = $_POST['ivar'];
	
	$img_ids = array();
	$img_ids = explode(",", $imgidsStr);
	foreach($img_ids as $img_id){
		$query = "SELECT `id` FROM images_tags WHERE img_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $img_id)."' AND `tag`='".mysqli_real_escape_string($GLOBALS['db']['link'], $tag)."' LIMIT 1";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)){
			$tag_ids[] = $row['id'];
			$q = "DELETE FROM images_tags WHERE img_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $img_id)."' AND `tag`='".mysqli_real_escape_string($GLOBALS['db']['link'], $tag)."'";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) $ret['error'].= "Couldn't delete tag ($tag:$img_id); ";
		}
	}
	
	if($tag_ids) $ret['tag_ids'] = $tag_ids;//implode(",", $tag_ids);
	
	die(json_encode($ret));
	
}

function formatImgTaglist($tags){
	foreach($tags as $tag){
		$num_tags[$tag]++;
		$otags[$tag] = '<li>[['.$tag.']] <a href="#rmTag" class="ximg" title="delete this tag from all selected images" onclick="rmTagSelected(this)">remove</a><textarea name="" style="display:none">'.$tag.'</textarea></li>';
	}
	$tagStr = implode('', $otags);
	$pglinks = new pglinks();
	$pglinks->attr['target'] = "_blank";
	$pglinks->attr['class'] = "imgtag";
	return $pglinks->parse($tagStr);
}

if($_POST['mass_classify'] && $_POST['ivar']){
	
	header("Content-type: application/json");
	$ret = array();
	
	parse_str($_POST['mass_classify']);//$img_category_id_mass
	$imgidsStr = $_POST['ivar'];
	
	$img_ids = array();
	$img_ids = explode(",", $imgidsStr);
	
	$catg = imgGetCategories();
	
	foreach($img_ids as $img_id){
		$q = "UPDATE images SET img_category_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $img_category_id_mass)."' WHERE img_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $img_id)."' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $ret['error'] = "Couldn't classify image ($img_id:$img_category_id_mass); ";
	}
	
	$catname = $catg[$img_category_id_mass]['img_category'];
	if(!$catname) $catname = '<i class="null">None</i>';
	
	if(!$ret['error']) $ret['formatted'] = '<strong>'.$catname.'</strong>';
	
	die(json_encode($ret));
	
}

if($_POST['rm_img_ids']){
	
	header("Content-type: application/json");
	$ret = array();
	
	foreach($_POST['rm_img_ids'] as $rm_img_id){
		$img = new img($rm_img_id);
		if($img->remove() === false) $ret['errors'][] = $img->img_remove_error;
		else $ret['rm_img_ids'][] = $rm_img_id;
	}
	
	die(json_encode($ret));
	
}

?>