<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.upload.php");
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.ajax.php");
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/upload_avatar.php");

if($usrrank < 6) die("Access denied");

$root = $_SERVER['DOCUMENT_ROOT']."/bin/img/avatars";

// AJAX

if($del = $_GET['delete']) {
	$a = new ajax();
	if(substr($del, 0, 7) == "unknown") $a->kill("Can't delete the default avatar");
	if(!rename("$root/$del", "$root/deleted/$del")) $a->kill("Couldn't delete $root/$del");
	if(!rename("$root/icon/$del", "$root/deleted/icon/$del")) $a->kill("Couldn't delete $root/icon/$del");
	$q = "UPDATE users SET avatar='unknown.png' WHERE avatar='$del'";
	mysqli_query($GLOBALS['db']['link'], $q);
	$a->ret['success'] = 1;
	exit;
}

$page = new page();

if($process = $_POST['process']) {
	foreach($process as $x){
		if(!file_exists("$root/temp/$x")){
			$errors[] = "Temp file [$x] not found";
			continue;
		}
		if(!copy("$root/temp/$x", "$root/$x")) {
			$errors[] = "Couldn't process '$x'";
		} else {
			//die ('<img src="/bin/img/avatars/temp/tn/'.$x.'"/>');
			if(!copy("$root/icon/temp/$x", "$root/icon/$x")) {
				$errors[] = "Couldn't process '$x' thumbnail";
			} else {
				$results[] = "[$x] successfully processed";
			}
		}
		unlink("$root/temp/$x");
		unlink("$root/icon/temp/$x");
	}
}

if($del = $_GET['destroy']) {
	//delete temp av
	if(!unlink("$root/temp/$del")) $errors[] = "Couldn't delete [$del]";
	//else $results[] = "Deleted standard img";
	if(!unlink("$root/icon/temp/$del")) $errors[] = "Couldn't delete [$del] icon";
	//else $results[] = "Deleted thumbnail";
}

unset($del);

if($_GET['do'] == "empty_temp_dir") {
	if($handle = opendir($root."/temp/tn/")) {
		while (false !== ($file = readdir($handle))) {
			if($file != "." && $file != "..") unlink($root."/temp/tn/".$file);
		}
	}
	if($handle = opendir($root."/temp/")) {
		while (false !== ($file = readdir($handle))) {
			if($file != "." && $file != ".." && $file != "tn") unlink($root."/temp/".$file);
		}
	}
	$results[] = "Dir emptied (well, probably)";
}

$page->css[] = "/bin/css/account.css";
$page->freestyle.= '
.uploadav { margin:-4px 5px 0 !important; width:295px; height:308px; }
.uploadav .fieldset { height:284px; }
.uploadav dl {} 
.uploadav dt {} 
.uploadav dd { margin:3px 0 0; padding:0; } 
#select-avatar .rm { display:none; top:0; right:0; }
#select-avatar .on:hover .rm { display:block; }
#select-avatar li.on big { position:relative; }
';
$page->javascripts[] = "/bin/script/jquery.isotope.js";
$page->javascript.='
	<script type="text/javascript">
		$(document).ready(function(){
			
			var $avsel = $("#select-avatar");
		
			$avsel.isotope({itemSelector : "li", masonry: {
		    columnWidth: 1,
		    cornerStampSelector: ".uploadav"
		  }});
			
			$("#select-avatar li").click(function(){
				$(this).toggleClass("on");
				$avsel.isotope("reLayout");
			});
			
			$("#select-avatar .rm").click(function(){
				var file = $(this).parent().data("file");
				if(confirm("Permanently remove "+$(this).prev().attr("title")+" ?")){
					$(this).parent().remove();
					$avsel.isotope("reLayout");
					$.get("/ninadmin/avatars.php", {"delete":file}, function(res){
						if(res.errors) handleErrors(res.errors);
					});
				}
			});
			
		});
	</script>
';
$page->header();

?>
<h1>Avatar Management</h1>

<?
if($_FILES['file']['name']) {
	$files[] = $_FILES['file'];
}

if($_FILES['filem']['name'][0]) {
	$m = $_FILES['filem'];
	foreach($m as $key => $filedat){
		for($i=0; $i < count($filedat); $i++){
			$files[$i][$key] = $filedat[$i];
		}
	}
}

if($files){
	echo '<form action="avatars.php" method="post"><fieldset><legend>Upload Result</legend><p>This is just a preview. Process this upload below to make it public.</p>';
	foreach($files as $file){
		echo '<fieldset><legend>'.$file['name'].'</legend>';
		$upload_res = uploadAvatar($file, "", "temp");
		if($upload_res['error']) echo '<p><b>Error:</b> '.$upload_res['error'].'</p>';
		if($st = $upload_res['filename']){
		
			//check for current avatar that would be replaced if processed
			if(file_exists($root."/".$st)) echo '<p><b>Warning</b>: There is already an avatar named <i>'.$st.'</i>. If you process this upload you will replace it with the following avatar:<br/><img src="/bin/img/avatars/'.$st.'"/></p>';
			
			if($_POST['do_icon'] && $_FILES['icon']['name'] && !$_FILES['filem']['name'][0]) $upload_res_icon = uploadAvatar($_FILES['icon'], substr($st, 0, -4), "temp", false, true);
			if($upload_res_icon['error']) echo '<p><b>Error on Thumbnail:</b> '.$upload_res_icon['error'].'</p>';
			
			echo '
					<p>Standard avatar (<a href="/bin/img/avatars/temp/'.$st.'" target="_blank">check for freshness</a>):<br/>
					<img src="/bin/img/avatars/temp/'.$st.'" alt="standard image" width="144" height="144"/></p>
					
					<p>Thumbnail (<a href="/bin/img/avatars/icon/temp/'.$st.'" target="_blank">check for freshness</a>):<br/>
					<img src="/bin/img/avatars/icon/temp/'.$st.'" alt="standard image"/></p>
					
					<label><input type="checkbox" name="process[]" value="'.($st).'" checked /> Process this image</label>
				';
		}
		echo '</fieldset><br/>';
	}
	echo '
				<input type="submit" value="Submit for Processing" style="font-weight:bold"/>
				<input type="button" value="Destroy!" onclick="document.location=\'avatars.php?destroy='.urlencode($st).'\'"/>
		';
	echo '</fieldset></form>';
	
}

?>

<div id="setav">
	
	<ul id="select-avatar">
		<li class="uploadav">
			<fieldset>
				<legend>New Avatar</legend>
				<form action="avatars.php" method="post" ENCTYPE="multipart/form-data">
					<div style="text-align:right" onclick="$(this).siblings('dl').toggle()"><a>Mass Upload</a></div><br/>
					<dl>
						<dt><b>Full-Sized Image</b> (144 x 144)</dt>
						<dd><input type="file" name="file"/></dd>
						<br/>
						<dt><b>Thumbnail</b> (48 x 48)</dt>
						<dd><label><input type="radio" name="do_icon" value="" checked="checked" onclick="$('#upload-thumb').hide();"/> Resize the full-sized source file</label></dd>
						<dd><label><input type="radio" name="do_icon" value="1" onclick="$('#upload-thumb').show().find('input').click()"/> Upload a new image</label></dd>
						<dd id="upload-thumb" style="display:none"><input type="file" name="icon"/></dd>
					</dl>
					<dl style="display:none">
						<dd><input type="file" name="filem[]" multiple/></dd>
					</dl>
					<br/>
					<input type="submit" value="Upload and Review"/>
				</form>
			</fieldset>
		</li>
		<?
		
		if ($handle = opendir($root)){
			while (false !== ($file = readdir($handle))) { 
				if (substr($file, -3) == "png") {
					$avs[] = $file;
				}
			}
		}
		
		//# of uses
		$query = "SELECT * FROM users WHERE avatar != ''";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)) {
			$uses[$row['avatar']]++;
		}
		
		natcasesort($avs);
		reset($avs);
		foreach($avs as $file) {
			$filen=ucwords(str_replace(".png","",$file));
			$title = $filen." - ".($uses[$file] ? "Used by ".$uses[$file].($uses[$file] == 1 ? ' user' : ' users') : "Not in use");
			?>
			<li class="avatar-item <?=($user->avatar == $file ? 'on' : '')?>" data-file="<?=$file?>">
				<a title="<?=$title?>" rel="avatar">
					<img src="/bin/img/avatars/icon/<?=$file?>" alt="<?=$title?>" width="48" height="48" class="icon"/>
					<big><img src="/bin/img/avatars/<?=$file?>" alt="<?=$title?>"/></big>
				</a>
				<a class="rm ximg-small">x</a>
			</li>
			<?
		}
		?>
	</ul>
	
</div>

<?
$page->footer();
?>