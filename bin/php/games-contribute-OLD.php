<?
require_once ($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");
require_once ($_SERVER['DOCUMENT_ROOT']."/bin/php/contribute.php");

$do = $_GET['do'];
$action = $_POST['action'];
$gid = $_POST['gid'];
$submitform = $_POST['submitform'];

//if(!$usrid) die('Please <a href="javascript:void(0)" onclick="toggle(\'login\')">log in</a> to contribute.');

if($action == "set message cookie") {
	setcookie("dont_show_contribute_message", "1", time()+60*60*24*30, "/"); //30 days
	exit;
}

if($_POST) {
	$q = "SELECT * FROM games WHERE gid='$gid' LIMIT 1";
	if(!$gdat = mysql_fetch_object(mysql_query($q))) die("Couldn't get game data for gid # $gid");
}

if($do == "load") {
	
	echo $html_tag;
	?>
	<head>
		<title>Page loading</title>
		<style>
			BODY { margin:0; padding:0; font:normal 13px arial; }
		</style>
	</head>
	<body>
	<div style="padding:5px; border:1px solid #CCC;"><img src="/bin/img/loading-thickbox.gif" alt="loading"/></div>
	</body>
	</html>
	<?
	
	exit;
	
}

// SYNOPSIS //

if($action == "synopsis_form") {
	
	if($gdat->synopsis && $usrrank >= 7) {
		die('Actually, there\'s aleady a synopsis for this game!<br/>Since your an admin, you can probably <a href="/ninadmin/games-mod.php?id='.$gid.'">edit it</a>.');
	}
	
	?>
	Add a short (one paragraph) story synopsis or genral description of this game. Please refrain from spoilers, facts, and trivia; the latter two can be submitted under "Trivia" above.
	<p><textarea name="" rows="4" cols="80" id="gc-input-synopsis" onchange="has_synopsis=true;"></textarea></p>
	<p><label><input type="checkbox" name="" value="" checked="checked" id="user_is_author" onclick="if(this.checked==false) { toggle('new-synopsis-author',''); this.value=''; } else { toggle('','new-synopsis-author'); this.value='1'; }"/> I am the author of this synopsis</label></p>
	<div id="new-synopsis-author" style="display:none">
		<p><input type="text" name="" value="" size="40" id="gc-input-author"/> Author's name</p>
		<p><input type="text" name="" value="" size="40" id="gc-input-authorlink" style="text-decoration:underline; color:#06C;"/> Author's link (optional)</p>
	</div>
	<p>
		<input type="button" value="Submit" id="gc-submit-synopsis-button" onclick="GCsubmitsynopsis();"/> 
		<img src="/bin/img/loading-thickbox.gif" alt="loading" id="gc-synopsis-loading" style="display:none"/>
	</p>
	<?
	
}

if($action == "submit_synopsis") {
	
	$_POST['synopsis'] = str_replace("[AMP]", "&", $_POST['synopsis']);
	$_POST['author'] = str_replace("[AMP]", "&", $_POST['author']);
	$_POST['author_link'] = str_replace("[AMP]", "&", $_POST['author_link']);
	
	$submission = $_POST['synopsis'];
	if($_POST['author']) {
		$source = $_POST['author'];
		if($_POST['author_link']) $source = '<a href="'.$_POST['author_link'].'">'.$source.'</a>';
		$source = ' <small>('.$source.')</small>';
		$submission.= $source;
	}
	
	//desc
	$description = 'Synopsis for <a href="/games/link.php?id='.$gid.'">'.htmlentities($gdat->title, ENT_QUOTES).'</a>';
	
	//pend it or post automatically?
	if($usrrank >= 4 && !$gdat->synopsis) {
		
		$q = "UPDATE games SET synopsis='".mysql_real_escape_string($submission)."' WHERE gid='$gid' LIMIT 1";
		if(!mysql_query($q)) die("Couldn't update games database; ".mysql_error());
		
		contributeToGame();
		
	} else {
		
		$pendid = mysqlNextAutoIncrement('pending');
		
		$q = "INSERT INTO pending (`table`, usrid, `datetime`) VALUES 
		('pending_games_synopsis', '$usrid', '".date('Y-m-d H:i:s')."');";
		if(!mysql_query($q)) die("Error saving to `pending`; ".mysql_error());
		
		$q = sprintf("INSERT INTO pending_games_synopsis (`pend_id`, `gid`, `synopsis`, `user_is_author`, `author`, `author_link`) VALUES 
		('$pendid', '$gid', '%s', '".$_POST['user_is_author']."', '%s', '%s');",
		mysql_real_escape_string($_POST['synopsis']),
		mysql_real_escape_string($_POST['author']),
		mysql_real_escape_string($_POST['author_link']));
		if(!mysql_query($q)) {
			sendBug("Error adding a user-submitted synopsis to a gamepage\n\ngid: $gid (http://videogam.in/games/link.php?id=$gid)\nuser: $usrname (http://videogam.in/~$usrname)\nsynopsis: ".$_POST['synopsis']);
			die("Error saving to database; ".mysql_error());
		}
		
	}
	
	addUserContribution(2, $description, $submission, ($usrrank <= 7 ? TRUE : FALSE), $pendid, 'games:'.$gid, 'gid:'.$gid);
	
	?>
	<div class="smiley">
		Your synopsis has been <?=($pendid ? 'submitted to the editors for review' : 'successfully posted (refresh this page to see)')?>. 
		Thanks for contributing! You can see all your great contributions at your <a href="/user-contributions.php?usrid=<?=$usrid?>">contributions page</a>.
	</div>
	<?
	exit;
	
}

// FACTOID //

if($action == "trivia_form") {
		
	?>
	<textarea name="" rows="4" cols="80" id="gc-input-trivia" onchange="has_trivia=true;"></textarea>
	<p><label><input type="checkbox" checked="checked" id="gc-trivia-user-is-author" onclick="if(this.checked==false) toggle('new-trivia-author',''); else toggle('', 'new-trivia-author');"/> I am the author of this interesting (and true!) factoid</label></p>
	<div id="new-trivia-author" style="display:none">
		<p><input type="text" name="" value="" size="40" id="gc-input-author"/> Source or Author's name</p>
		<p><input type="text" name="" value="" size="40" id="gc-input-authorlink" style="text-decoration:underline; color:#06C;"/> Source or Author's link (optional)</p>
	</div>
	<p>
		<input type="button" value="Submit" id="gc-trivia-button" onclick="GCsubmittrivia();"/> 
		<img src="/bin/img/loading-thickbox.gif" alt="loading" id="gc-trivia-loading" style="display:none"/>
	</p>
	<?

}
if($action == "submit_trivia") {
	
	$fact = str_replace("[AMP]", "&amp;", $_POST['fact']);
	$author = str_replace("[AMP]", "&amp;", $_POST['author']);
	
	$description = 'Trivia for <a href="/games/link.php?id='.$gid.'">'.htmlentities($gdat->title, ENT_QUOTES).'</a>';
	
	//pend it or post automatically?
	if($usrrank >= 4) {
		
		$subj = "games_trivia:".mysqlNextAutoIncrement("games_trivia");
		
		$q = "INSERT INTO games_trivia (gid, fact, author, datetime, usrid) VALUES 
		('$gid', '".mysql_real_escape_string($fact)."', '$author', '".date("Y-m-d H:i:s")."', '$usrid')";
		if(!mysql_query($q)) die("Couldn't update games database; ".mysql_error());
		
		contributeToGame();
		
	} else {
		
		$pendid = mysqlNextAutoIncrement('pending');
		$subj = "";
		
		$q = "INSERT INTO pending (`table`, usrid, `datetime`) VALUES 
		('pending_games_trivia', '$usrid', '".date('Y-m-d H:i:s')."');";
		if(!mysql_query($q)) die("Error saving to `pending`; ".mysql_error());
		
		$q = "INSERT INTO pending_games_trivia (`pend_id`, `gid`, `fact`, `author`) VALUES 
		('$pendid', '$gid', '".mysql_real_escape_string($fact)."', '".mysql_real_escape_string($author)."');";
		if(!mysql_query($q)) {
			sendBug("Error adding a user-submitted trivia to a gamepage\n\ngid: $gid (http://videogam.in/games/link.php?id=$gid)\nuser: $usrname (http://videogam.in/~$usrname)\nfact: ".$fact);
			die("Error saving to database; ".mysql_error());
		}
		
	}
	
	addUserContribution(4, $description, $fact, ($usrrank <= 7 ? TRUE : FALSE), $pendid, $subj, 'gid:'.$gid);
	
	?>
	<div class="smiley">
		Your trivia has been <?=($pendid ? 'submitted to the editors for review' : 'successfully posted (refresh this page to see)')?>. 
		Thanks for contributing! You can see all your great contributions at your <a href="/user-contributions.php?usrid=<?=$usrid?>">contributions page</a>.
	</div>
	<?
	exit;
	
}
	

// LINK //

if($action == "preview_link") {

	$tags = @get_meta_tags($_POST['url']);
	$desc = $tags['description'];
  
  if ($fp = @fopen( $_POST['url'], 'r' )) {
    $cont = "";
    while( !feof( $fp ) ) {
       $buf = trim(fgets( $fp, 4096 )) ;
       $cont .= $buf;
    }
    @preg_match( "/<title>([^<]*)<\/title>/si", $cont, $match );
    $title = strip_tags(@$match[1]);
	}
	
	$rand1 = rand(0,4);
	$rand2 = rand(1,9);
	$auth->math1 = $rand1;
	$auth->math2 = $rand2;
	$auth->hidden = '
	<input type="hidden" name="math1" value="'.$rand1.'" id="addlinkmath1"/>
	<input type="hidden" name="math2" value="'.$rand2.'" id="addlinkmath2"/>';
	$auth->label = '<label for="inp-math"><img src="/bin/img/numbers/'.$rand1.'.png" alt="random number"/> + <img src="/bin/img/numbers/'.$rand2.'.png" alt="random number"/> = </label>';
	$auth->input = '<input type="text" name="math" maxlength="2" size="5" id="addlinkinpmath"/>';
	
	?>
	<div style="margin-bottom:10px">
		<b>Please note:</b> only links that are strongly related to this game should be posted here!<br/>
		All other links can be posted on the <a href="/links.php">general links</a> page.
	</div>
	<form action="/games/~<?=$gdat->title_url?>" method="post" name="addlinkform">
		<input type="hidden" name="in[url]" value="<?=$_POST['url']?>"/>
		<?=$auth->hidden?>
		<table cellpadding="0" cellspacing="0" width="100%" class="styled-form">
			<tr>
				<th>Site Name</th>
				<td><input type="text" name="in[site_name]" value="<?=$title?>" size="52" maxlength="75"/></td>
			</tr>
			<tr>
				<th>Description<br/><small>optional</small></th>
				<td><textarea name="in[description]" rows="2" cols="50" maxlength="255"><?=$desc?></textarea></td>
			</tr>
			<tr>
				<th><?=$auth->label?></th>
				<td><?=$auth->input?> ?</td>
			</tr>
			<?
			if($usrrank >= 6) {
				?>
				<tr>
					<th>Request link exchange</th>
					<td>
						<label><input type="checkbox" name="" value=""/> Send the following link request to:</label> 
						<input type="text" name="in[to]" value="email address" onclick="this.value='';"/>
						<p><textarea name="in[message]" cols="50" rows="4">Hello, it's <?=$usrname?> from over at Videogam.in (http://Videogam.in). We noticed your site and have deemed it a respectable establishment, so we thought it apt to notify our users about you. We posted a link to your site on our <?=$gdat->title?> overview (located at http://videogam.in/games/~<?=$gdat->title_url?>) and wonder if you would care to have a link exchange with us. We have some buttons and HTML-ready text links at http://videogam.in/links.php#linkback. Thanks!
- <?=$usrname?></textarea></p>
						<p>(or just copy the message to send in a form)</p>
					</td>
				</tr>
				<?
			}
			?>
			<tr>
				<th>&nbsp;</th>
				<td>
					<input type="submit" name="submit" value="Add Link" style="font-weight:bold" onclick="return GCcheckLinkSubmission();"/> 
					<input type="button" value="Cancel" onclick="GCtoggle('link')"/>
				</td>
			</tr>
		</table>
	</form>
	<?
  
}

// PUBLICATION //

if($action == "pub_form") {
	
	?>
	<form action="/bin/php/games-contribute.php" method="post" target="gc-pub-upload" enctype="multipart/form-data" id="gc-pub-input">
		<input type="hidden" name="gid" value="<?=$gid?>"/>
		<input type="hidden" name="action" value="submit pub"/>
		<table border="0" cellpadding="0" cellspacing="0" width="100%" class="styled-form">
			<tr>
				<th>Upload Box Art</th>
				<td>
					<b>Please upload only cover images that meet these standards:</b>
					<ul style="margin:0 0 0 15px; padding:0; list-style-type:square;">
						<li>JPG, GIF, or PNG format</li>
						<li>At least 200 pixels in width</li>
						<li>Unblurred, clear, quality images without watermarks or site logos</li>
						<li>Flat images that are not scaled, rotated, have a 3D perspective, or have any borders or whitespace around the perimiter (tip: use <a href="http://www.wiredness.com/" target="_blank">Wiredness</a> to quickly and easily crop any whitespace or borders from an image)</li>
					</ul>
					<p><input type="file" name="file" id="gc-pub-file"/></p>
				</td>
			</tr>
			<tr>
				<th>Title</th>
				<td><input type="text" name="in[title]" value="<?=$gdat->title?>" size="50"/> <a href="javascript:void(0)" class="tooltip" title="Input the full title of the publication, for example: &quot;Final Fantasy XII Collector's Edition&quot; will differentiate it from regular old Final Fantasy XII"><span class="block">?</span></a></td>
			</tr>
			<tr>
				<th>Platform</th>
				<td>
					<select name="in[platform_id]" id="gc-pub-platform">
						<option value="">Select a platform...</option>
						<?
						$query = "SELECT * FROM games_platforms WHERE platform != 'multiple' ORDER BY platform";
						$res   = mysql_query($query);
						while($row = mysql_fetch_assoc($res)) {
							echo '<option value="'.$row['platform_id'].'">'.$row['platform']."</option>\n";
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th>Region</th>
				<td>
					<select name="in[region]" id="gc-pub-region" onchange="if(this.options[this.selectedIndex].value=='other') { this.options[this.selectedIndex].value=''; toggle('gc-pub-region-other','gc-pub-region'); }">
						<option value="">Select a region...</option>
						<option value="us">North America</option>
						<option value="jp">Japan</option>
						<option value="eu">Europe</option>
						<option value="au">Australia</option>
						<option value="other" onselect="alert('hi')">elsewhere</option>
					</select>
					<select name="in[region_other]" id="gc-pub-region-other" style="display:none">
						<option value="">Select from more options...</option>
						<?
						require($_SERVER['DOCUMENT_ROOT']."/bin/php/country_codes.php");
						while(list($k, $v) = each($cc)) {
							echo '<option value="'.strtolower($k).'">'.$v.'</option>';
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th>Release date</th>
				<td>
					Input all known values (year is the only required field).
					<p>
						<select name="in[year]" style="background-color:#FFFF80;">
							<?
							for($i = (date('Y') + 2); $i >= 1980; $i--) {
								echo '<option value="'.$i.'"'.(date("Y") == $i ? ' selected="selected"' : ' style="background-color:white;"').'>'.$i.'</option>'."\n";
							}
							?>
						</select> 
						<select name="in[month]">
							<option value="00">Month</option>
							<option value="01">January</option>
							<option value="02">February</option>
							<option value="03">March</option>
							<option value="04">April</option>
							<option value="05">May</option>
							<option value="06">June</option>
							<option value="07">July</option>
							<option value="08">August</option>
							<option value="09">September</option>
							<option value="10">October</option>
							<option value="11">November</option>
							<option value="12">December</option>
						</select> 
						<select name="in[day]">
							<option value="00">Day</option>
							<?
							for($i = 1; $i <= 31; $i++) {
								if($i < 10) $i = '0'.$i;
								echo '<option value="'.$i.'">'.$i.'</option>'."\n";
							}
							?>
						</select>
					</p>
				</td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td colspan="2"><input type="submit" value="Submit" onclick="return GCcheckpub();"/></td>
			</tr>
		</table>
	</form>
	<iframe src="/bin/php/games-contribute.php?do=load" name="gc-pub-upload" id="gc-pub-upload" frameborder="0" style="display:none; width:532px; height:32px;"></iframe>
	<?
	
}

if($action == "submit pub") {
	
	require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/class.upload.php");
	
	echo $html_tag;
	?>
	<head>
		<title>New publication</title>
		<link rel="stylesheet" type="text/css" href="/bin/css/screen.css" media="screen"/>
	</head>
	<body style="margin:0; padding:0; background-color:white !important;">
	<?
	if(!$_FILES['file']['name']) echo "No file detected";
	else {
		
		if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/games/files/'.$gid.'/')) @mkdir($_SERVER['DOCUMENT_ROOT'].'/games/files/'.$gid.'/', 0777);
		
		$in = $_POST['in'];
		if(!$in['platform_id'] && $in['platform_id'] != '0') die("No platform selected");
		if(!$in['year']) die("No release year input");
		if(!$in['title']) $in['title'] = $gdat->title;
		if(!$in['region']) {
			if($in['region_other']) $in['region'] = $in['region_other'];
			else die("No region selected");
		}
			
		//upload
		$handle = new Upload($_FILES['file']);
    if ($handle->uploaded) {
    	$handle->file_new_name_body     = 'box_'.$gid.'_'.rand(0,99999);
			$handle->image_convert          = 'jpg';
			$handle->image_resize           = true;
			$handle->image_ratio_no_zoom_in = true;
			$handle->image_x                = 500;
			$handle->image_y                = 700;
    	$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/temp/");
			if ($handle->processed) {
				
				$file = $handle->file_dst_name;
				$file_body = substr($file, 0, -4);
				
				//small img
				$handle->file_new_name_body    = $file_body.'_sm';
				$handle->image_convert         = 'png';
				$handle->image_resize          = true;
				$handle->image_ratio_y         = true;
				$handle->image_x               = 140;
				$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/temp/");
				if(!$handle->processed) die('Small image couldn\'t be created: ' . $handle->error);
				$file2 = $handle->file_dst_name;
				
				//thumbnail
				$handle->file_new_name_body    = $file_body.'_tn';
				$handle->image_convert         = 'png';
				$handle->image_resize          = true;
				$handle->image_ratio_y         = true;
				$handle->image_x               = 80;
				$handle->Process($_SERVER['DOCUMENT_ROOT']."/bin/temp/");
				if (!$handle->processed) die('Thumbnail image couldn\'t be created: ' . $handle->error);
				$file3 = $handle->file_dst_name;
				
			}
				
    } else {
			// if we're here, the upload file failed for some reasons
			// i.e. the server didn't receive the file
			die('file not uploaded on the server: ' . $handle->error);
    }
    
    //desc
		$description = '<a href="/games/link.php?id='.$gid.'">'.htmlentities($gdat->title, ENT_QUOTES).'</a> box art';
		
		//pend it or post automatically?
		if($usrrank >= 4) {
			
			//get # of current pubs and decide if this should be the primary pub
			$q = "SELECT * FROM games_publications WHERE gid='$gid'";
			if(!mysql_num_rows(mysql_query($q))) $primary = '1';
			else $primary = '0';
			
			$nextid = mysqlNextAutoIncrement("games_publications");
			$subj = "games_publications:".$nextid;
			
			$q = "INSERT INTO games_publications (gid, platform_id, title, region, release_date, `primary`) VALUES 
			('$gid', '".$in['platform_id']."', '".htmlentities($in['title'], ENT_QUOTES)."', '".$in['region']."', '".$in['year']."-".$in['month']."-".$in['day']."', '$primary')";
			if(!mysql_query($q)) {
				sendBug("Error adding a user-submitted publication (box art) to a gamepage\n\ngid: $gid (http://videogam.in/games/link.php?id=$gid)\nuser: $usrname (http://videogam.in/~$usrname)\ndb query: ".$q."\nerror: ".mysql_error());
				die("Error saving to database; ".mysql_error());
			}
			
			$new_body = $gid."-box-".$nextid;
			$new_dir = "/games/files/".$gid."/";
			if(!rename($_SERVER['DOCUMENT_ROOT']."/bin/temp/".$file, $_SERVER['DOCUMENT_ROOT'].$new_dir.$new_body.".jpg")) die("Couldn't move uploaded file");
			if(!rename($_SERVER['DOCUMENT_ROOT']."/bin/temp/".$file2, $_SERVER['DOCUMENT_ROOT'].$new_dir.$new_body."-sm.png")) die("Couldn't move uploaded small file");
			if(!rename($_SERVER['DOCUMENT_ROOT']."/bin/temp/".$file3, $_SERVER['DOCUMENT_ROOT'].$new_dir.$new_body."-tn.png")) die("Couldn't move uploaded thumbnail");
			
			contributeToGame();
			
		} else {
			
			$subj = "";
			$pendid = mysqlNextAutoIncrement("pending");
			
			$q = "INSERT INTO pending (`table`, usrid, `datetime`) VALUES 
			('pending_games_publications', '$usrid', '".date('Y-m-d H:i:s')."');";
			if(!mysql_query($q)) die("Error saving to `pending`; ".mysql_error());
			
			$q = "INSERT INTO pending_games_publications (pend_id, gid, platform_id, title, region, release_date, `file`) VALUES 
			('$pendid', '$gid', '".$in['platform_id']."', '".mysql_real_escape_string($in['title'])."', '".$in['region']."', '".$in['year']."-".$in['month']."-".$in['day']."', '$file')";
			if(!mysql_query($q)) {
				sendBug("Error adding a [temporary] user-submitted publication (box art) to a gamepage\n\ngid: $gid (http://videogam.in/games/link.php?id=$gid)\nuser: $usrname (http://videogam.in/~$usrname)\ndb query: ".$q."\nerror: ".mysql_error());
				die("Error saving to database; ".mysql_error());
			}
			
		}
		
		addUserContribution(3, $description, '', ($usrrank <= 7 ? TRUE : FALSE), $pendid, $subj, 'gid:'.$gid);
		
		?>
		<div class="smiley">
			Your box art has been <?=($pending ? 'submitted to the editors for review' : 'successfully posted')?>. 
			Thanks for contributing! You can see all your great contributions at your <a href="/user-contributions.php?usrid=<?=$usrid?>" target="_top">contributions page</a>.
		</div>
		<?
		exit;
    
	}
	?>
	</body>
	</html>
	<?
	
	exit;

}
	

// SCREENS //

if($action == "screens_form") {
	
	?>
	<div id="gc-ss-forms">
		
		<?
		if($usrrank >= 7) {
			echo '<div style="margin-bottom:5px; padding:5px; border:1px solid #DDD;">This is a simple form designed for regular users to upload a few screens. Since you\'re an admin, you have access to the much more powerful <a href="/ninadmin/media.php">media uploader</a>.</div>';
		}
		
		//user already uploaded some screens?
		$dir = substr($gdat->title_url, 0, 15);
		$dir = str_replace("-", "_", $dir);
		$dir = "/media/".$dir."-".$gdat->gid."-screens-uid".$usrid;
		$q = "SELECT * FROM media WHERE directory='$dir' LIMIT 1";
		if($x = mysql_fetch_object(mysql_query($q))) {
			$has_note = ' (you have uploaded '.$x->quantity.' so far)';
		}
		?>
		
		<div style="margin-bottom:10px">
			Upload a few screenshots  of this game below.<br/>
			<ul style="margin:0 0 0 15px; padding:0; list-style-type:square;">
				<li>At least five are recommended <?=$has_note?>.</li>
				<li>Images must be in JPG, GIF, or PNG format.</li>
				<li>Upload only images without watermarks or site logos.</li>
			</ul>
		</div>
		
		<?
		for($i = 1; $i <= 20; $i++) {
			?>
			<div id="gc-ss-<?=$i?>" style="<?=($i > 1 ? 'display:none; margin-top:10px;' : '')?>">
				<div id="gc-ss-<?=$i?>-input" class="gc-ss-form">
					<form action="/bin/php/games-contribute.php" method="post" target="gc-ss-<?=$i?>-upload" enctype="multipart/form-data">
						<input type="hidden" name="gid" value="<?=$gid?>"/>
						<input type="hidden" name="action" value="upload screen"/>
						<input type="file" name="screen" id="gc-ss-<?=$i?>-file"/> 
						<label>
							caption: <a href="javascript:void(0)" class="tooltip" title="A description of the image (optional)"><span class="block" style="font-size:12px">?</span></a> 
							<input type="text" name="caption" size="32"/>
						</label> 
						<input type="submit" value="Upload" onclick="return GCcheckss('<?=$i?>');"/>
					</form>
				</div>
				<iframe src="/bin/php/games-contribute.php?do=load" name="gc-ss-<?=$i?>-upload" id="gc-ss-<?=$i?>-upload" frameborder="0" style="display:none; width:532px; height:28px;"></iframe>
			</div>
			<?
		}
		?>
		<div id="gc-ss-21" style="display:none; margin-top:10px;">You have reached the maximum of 20 screens. <a href="#contribute" onclick="has_screens=false; GCtoggle('screens',1); GCtoggle('screens',1);">Reload this frame</a> if you want to upload more.</div>
		
		<input type="button" value="Finished Uploading" id="gc-ss-finished" style="display:none; margin-top:5px;" onclick="GCtoggle('screens',1);"/>
		
	</div>
	
	<div id="gc-ss-results" class="smiley" style="display:none">
		<?=($usrrank >= 4 ? 'Your screenshots have been successfully posted! They should show up on this page if you refresh your browser. You can see this and your other contributions at your <a href="/user-contributions.php?usrid='.$usrid.'">user contributions page</a>.' : 'Your screenshots have been posted and are waiting for editor review before being published. Thanks for your contribution! You can see this and your other contributions at your <a href="/user-contributions.php?usrid='.$usrid.'">user contributions page</a>.')?>
	</div>
	<?
	
}

if($action == "upload screen") {
	
	require_once($_SERVER['DOCUMENT_ROOT']."/bin/php/class.upload.php");
	
	echo $html_tag;
	?>
	<head>
		<title>Upload game screenshots</title>
		<style>
			BODY { margin:0; padding:0; font:normal 13px arial; }
			A { color:#06C; }
			</style>
	</head>
	<body>
	<div style="padding:5px; border:1px solid #CCC;">
	<?
	if(!$_POST) {
		echo '<img src="/bin/img/loading-thickbox.gif" alt="loading"/>';
	} else {
		if(!$_FILES['screen']['name']) echo "No file detected";
		else {
			
			//check file type
			$ext = substr($_FILES['screen']['name'], -3);
			$ext = strtolower($ext);
			$allowed_exts = array("jpg", "gif", "png");
			if(!in_array($ext, $allowed_exts)) die("Error uploading '".$_FILES['screen']['name']."': Please only upload JPG, GIF, or PNG files.");
			
			$dir = substr($gdat->title_url, 0, 15);
			$dir = str_replace("-", "_", $dir);
			$dir = "/media/".$dir."-".$gdat->gid."-screens-uid".$usrid;
			
			$media_query = "SELECT * FROM media WHERE directory='$dir' LIMIT 1";
			$media_res = mysql_query($media_query);
			if(!mysql_num_rows($media_res)) {
				
				//pend it or post automatically?
				if($usrrank >= 4) $pending = FALSE;
				else $pending = TRUE;
			
				//make dir
				$subj = $_SERVER['DOCUMENT_ROOT'].$dir;
				if(is_dir($subj) && (!@mkdir($subj, 0777) || !@mkdir($subj."/thumbs", 0777))) {
					die("Couldn't make directories ($subj)");
				}
				
				$nextid = mysqlNextAutoIncrement("media");
				$media_id = $nextid;
				
				$q = "INSERT INTO media (directory, category_id, description, gallery, datetime, usrid, quantity, unpublished) VALUES 
				('$dir', '1', '<i>".addslashes($gdat->title)."</i> screenshots', '1', '".date("Y-m-d H:i:s")."', '$usrid', '1', '".($pending ? '1' : '')."')";
				if(!mysql_query($q)) die("Couldn't add to db: ".mysql_error());
				
				$q = "INSERT INTO media_tags (media_id, tag) VALUES ('$nextid', 'gid:".$gdat->gid."')";
				if(!mysql_query($q)) die("Couldn't add to tag db: ".mysql_error());
				
				if(!$pending) contributeToGame();
				if($usrrank <= 8) $notify = TRUE;
				
			} else {
				
				//qty + 1
				$dat = mysql_fetch_object($media_res);
				$q = "UPDATE media SET `quantity`='".($dat->quantity + 1)."' WHERE media_id='".$dat->media_id."' LIMIT 1";
				mysql_query($q);
				
				$media_id = $dat->media_id;
				$notify = FALSE;
				
			}
		  
		  $handle = new Upload($_FILES['screen']);
			if($handle->uploaded) {
				$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir);
				if ($handle->processed) {
					
					$file = $handle->file_dst_name;
					
					echo '<a href="'.$dir.'/'.$file.'" target="_blank">'.$file.'</a> uploaded. ';
					
					//caption
					if($capt = $_POST['caption']) {
						$capt = strip_tags($capt);
						$capt = htmlentities($capt, ENT_QUOTES);
						$q = sprintf("INSERT INTO media_captions (media_id, `file`, `caption`) VALUES ('$media_id', '".$file."', '%s')",
							mysql_real_escape_string($capt));
						if(!mysql_query($q)) echo "Error! Couldn't add caption. ";
					}
					
					//thumb
					$handle->image_convert = 'jpg';
		      $handle->image_resize = TRUE;
		      $handle->image_ratio_crop = TRUE;
					$handle->image_x = 100;
					$handle->image_y = 100;
					$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir."/thumbs/");
					if (!$handle->processed) echo "Error! Couldn't make thumbnail. ";
					$thumb = $handle->file_dst_name;
					
					$desc = '<a href="/games/link.php?id='.$gdat->gid.'">'.$gdat->title.'</a> <a href="/media.php?mid='.$media_id.'">screenshots</a>';
					$subm = '<a href="'.$dir."/".$file.'" class="thickbox" title="'.$capt.'"><img src="'.$dir."/thumbs/".$thumb.'"/></a>';
					addUserContribution(9, $desc, $subm, $notify, '', "media:".$media_id, 'gid:'.$gid);
					
				} else {
					echo 'file not uploaded! Error: ' . $handle->error;
				}
	        
			}
		}
	}
	?>
	</div>
	</body>
	<?
	
	exit;
	
}

// PERSON //

if($action == "person_form") {
	
	?>
	<div id="gc-select-person">
		Credit sombody with the development of this game (a person, not a development group)
		<p>
			<input type="button" value="Next &gt;" onclick="GCsuggestname();" style="font-size:21px; float:right;"/>
			<input type="text" name="name" value="Name of person" size="42" id="gc-suggest-name" class="off" style="font-size:21px" onclick="if(this.value=='Name of person') { this.value=''; this.className=''; };"/>
		</p>
	</div>
	<?
	
}

if($action == "check_name") {
	
	if(!$name = $_POST['name']) die("No name given");
	
	//check if the person exists and what roles he already has listed for this game
	list($checkname, $name_url) = formatName($name);
	$res = mysql_query("SELECT * FROM people WHERE name='".mysql_real_escape_string($checkname)."' LIMIT 1");
	if($pdat = mysql_fetch_object($res)) {
		$res2 = mysql_query("SELECT role FROM people_work WHERE gid='$gid' AND pid='".$pdat->pid."'");
		if(mysql_num_rows($res2)) {
			while($row = mysql_fetch_assoc($res2)) {
				$roles[] = $row['role'];
			}
		}
	}
	
	?>
	<input type="hidden" value="<?=$pdat->pid?>" id="gc-pid"/>
	<input type="hidden" value="<?=htmlspecialchars($name, ENT_QUOTES)?>" id="gc-name"/>
	
	<div style="margin-bottom:10px; padding:8px; border:1px solid #CCC;">
		<input type="button" value="Cancel" onclick="GCtoggle('person', true)" style="float:right; margin:-3px;"/>
		Adding <?=($pdat ? '<a href="/people/~'.$pdat->name_url.'">'.$name.'</a>' : $name)?> as a developer...
		<?
		if($roles) {
			?>
			<p>
				<b>Please note:</b> <?=$pdat->name?> is already credited as a developer of this game in the following role<?=(count($roles) > 1 ? 's' : '')?>:
				<ul style="margin:5px 0 5px 20px; padding:0; list-style-type:square;">
					<?
					foreach($roles as $r) {
						echo '<li>'.$r.'</li>';
					}
					?>
				</ul>
				You may continue with this form if you're crediting this person with a different role.
			</p>
			<?
		}
		?>
	</div>
	
	<table border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td valign="top" style="padding-top:3px"><b>Role:</b> &nbsp; </td>
			<td>
				<select name="" id="gc-select-role">
					<option value="">Select a common role...</option>
					<?
					$result = mysql_query("SELECT role, COUNT(role) AS count FROM `people_work` WHERE role != '' GROUP BY role ORDER BY role");
					while ($row = mysql_fetch_assoc($result)) {
						if($row['count'] >= 4) echo '<option value="'.$row['role'].'">'.$row['role'].'</option>';
					}
					?>
				</select> 
				<input type="text" name="role" value="...or input role here" size="25" id="gc-input-role" class="off" onclick="if(this.value=='...or input role here') { this.value=''; this.className=''; }"/>
				
				<p><label><input type="checkbox" name="" value="" id="gc-person-vital"/> This person's role was relatively vital to the creation of this game</label></p>
			</td>
		</tr>
	</table>
	
	<p>Include any notes, clarfications, or interesting facts about this person's role in this game:</p>
	<p style="margin-top:2px !important;"><textarea rows="2" cols="80" id="gc-role-notes"></textarea></p>
	
	<div id="gc-bio-link"<?=($pdat->bio ? ' style="display:none"' : '')?>><p><?=$name?> has no biography yet! <a href="javascript:void(0)" onclick="toggle('gc-bio-form', 'gc-bio-link');" class="arrow-right">Write one</a></p></div>
	<div id="gc-bio-form" style="display:none">
		<p>Biography: (HTML is permitted)</p>
		<p style="margin-top:2px !important;"><textarea rows="8" cols="80" id="gc-person-bio"></textarea></p>
	</div>
	
	<p>
		<input type="button" value="Submit" id="gc-person-submit-button" onclick="GCsubmitperson();"/> 
		<img src="/bin/img/loading-thickbox.gif" alt="loading" id="gc-person-loading" style="display:none"/>
	</p>
	
	<?
	
}

if($action == "submit_person") {
	
	$_POST['role'] = str_replace("[AMP]", "&", $_POST['role']);
	$_POST['notes'] = str_replace("[AMP]", "&amp;", $_POST['notes']);
	$_POST['bio'] = str_replace("[AMP]", "&amp;", $_POST['bio']);
	$now = date('Y-m-d H:i:s');
	$name = $_POST['name'];
	if($pid = $_POST['pid']) {
		
		// existing person //
		
		$res = mysql_query("SELECT * FROM people WHERE pid='$pid' LIMIT 1");
		if(!$pdat = mysql_fetch_object($res)) die("Couldn't get data for person ID # $pid;".mysql_error());
		
		$description = '<a href="/games/link.php?id='.$gid.'">'.htmlent($gdat->title).'</a> developer: <a href="/people/~'.$pdat->name_url.'">'.$pdat->name.'</a> ('.$_POST['role'].')';
		
		//pend it or post automatically?
		if($usrrank >= 4) {
			
			$subj = "people_work:".mysqlNextAutoIncrement("people_work");
			
			$q = "INSERT INTO people_work (pid, gid, role, notes, vital) VALUES 
			('$pid', '$gid', '".mysql_real_escape_string($_POST['role'])."', '".mysql_real_escape_string($_POST['notes'])."', '".$_POST['vital']."')";
			if(!mysql_query($q)) die("Couldn't update people work database; ".mysql_error());
			
			addUserContribution(6, $description, $_POST['notes'], ($usrrank <= 7 ? TRUE : FALSE), '', $subj, "pid:".$pid);
			
			if($_POST['bio']) {
				$q = "UPDATE people SET bio='".$_POST['bio']."' WHERE pid='$pid' LIMIT 1";
				mysql_query($q);
				addUserContribution(12, '<a href="/people/~'.$pdat->name_url.'">'.$pdat->name.'</a> biography', $_POST['bio'], ($usrrank <= 7 ? TRUE : FALSE), '', 'people:'.$pid, 'pid:'.$pid);
			}
			
			contributeToGame();
			
		} else {
			
			$pendid = mysqlNextAutoIncrement('pending');
			
			$q = "INSERT INTO pending (`what`, `table`, usrid, `datetime`) VALUES 
			('$description', 'pending_people_work', '$usrid', '".date('Y-m-d H:i:s')."');";
			if(!mysql_query($q)) die("Error saving to `pending`; ".mysql_error());
			
			$q = "INSERT INTO pending_people_work (pend_id, pid, gid, role, notes, vital) VALUES 
			('$nextid', '$pid', '$gid', '".mysql_real_escape_string($_POST['role'])."', '".mysql_real_escape_string($_POST['notes'])."', '".$_POST['vital']."')";
			if(!mysql_query($q)) {
				sendBug("Error adding a user-submitted developer to a gamepage\n\ngid: $gid (http://videogam.in/games/link.php?id=$gid)\nuser: $usrname (http://videogam.in/~$usrname)\ndb query: ".$q."\nerror: ".mysql_error());
				die("Error saving to database; ".mysql_error());
			}
			
			addUserContribution(6, $description, $_POST['notes'], ($usrrank <= 7 ? TRUE : FALSE), $pendid, '', "pid:".$pid);
			
			//just e-mail the bio
			if($_POST['bio']) addUserContribution(12, '<a href="/people/~'.$pdat->name_url.'">'.$pdat->name.'</a> biography', $_POST['bio'], TRUE);
			
		}
		
		?>
		<div class="smiley">
			Your contribution has been <?=($pending ? 'submitted to the editors for review' : 'successfully posted (refresh this page to see)')?>. 
			Thanks for contributing! You can see all your great contributions at your <a href="/user-contributions.php?usrid=<?=$usrid?>">contributions page</a>.
			<p><a href="javascript:void(0)" class="arrow-right" onclick="GCtoggle('person', true);GCtoggle('person', true);">Add another developer</a></p>
		</div>
		<?
		exit;
	
	} else {
		
		// NEW PERSON //
		
		if(!$name) die("Error: No name given.");
		$description = '<a href="/games/link.php?id='.$gid.'">'.htmlent($gdat->title).'</a> developer: '.$name.' ('.$_POST['role'].') [Also added '.$name.' to the people database]';
		
		//pend it or post automatically?
		if($usrrank >= 4) {
			
			list($name, $name_url) = formatName($name);
			
			$pid = mysqlNextAutoIncrement("people");
			$q = sprintf("INSERT INTO people (name, name_url, bio, created, modified, contributors) VALUES 
			('%s', '%s', '%s', '$now', '$now', 'usrid:$usrid');",
			mysql_real_escape_string($name),
			mysql_real_escape_string($name_url),
			mysql_real_escape_string($_POST['bio']));
			if(!mysql_query($q)) die("Couldn't add $name to people database; ".mysql_error());
			
			addUserContribution(12, 'New creator: <a href="/people/~'.$name_url.'">'.$name.'</a>', $_POST['bio'], ($usrrank <= 7 ? TRUE : FALSE), '', "people:".$pid, "pid:".$pid);
			
			$workid = mysqlNextAutoIncrement("people_work");
			$q = "INSERT INTO people_work (pid, gid, role, notes, vital) VALUES 
			('$pid', '$gid', '".htmlentities($_POST['role'], ENT_QUOTES)."', '".mysql_real_escape_string($_POST['notes'])."', '".$_POST['vital']."')";
			if(!mysql_query($q)) die("Couldn't update people work database; ".mysql_error());
			
			addUserContribution(6, $description, $_POST['notes'], ($usrrank <= 7 ? TRUE : FALSE), '', "people_work:".$workid, "pid:".$pid);
			
			contributeToGame();
			
		} else {
			
			$pendid = mysqlNextAutoIncrement("pending");
			$q = "INSERT INTO pending (`what`, `table`, usrid, `datetime`) VALUES 
			('".mysql_real_escape_string("New person: $name [w/ game role]")."', 'pending_people', '$usrid', '$now');";
			if(!mysql_query($q)) die("Error saving to `pending`; ".mysql_error());
			
			$q = sprintf("INSERT INTO pending_people (pend_id, name, bio, gid, role, notes, vital) VALUES 
			('$pendid', '%s', '%s', '$gid', '%s', '%s', '".$_POST['vital']."');",
			mysql_real_escape_string($name),
			mysql_real_escape_string($_POST['bio']),
			mysql_real_escape_string($_POST['role']),
			mysql_real_escape_string($_POST['notes']));
			if(!mysql_query($q)) {
				sendBug("Error adding a user-submitted person to people db via gamepage\n\ngid: $gid (http://videogam.in/games/link.php?id=$gid)\nuser: $usrname (http://videogam.in/~$usrname)\ndb query: ".$q."\nerror: ".mysql_error());
				die("Error saving to database; ".mysql_error());
			}
			
		}
		
		addUserContribution(12, $description, '<dl><dt>Notes:</dt><dd>'.$_POST['notes'].'</dd><dt>'.$name.' biography:</dt><dd>'.$_POST['bio'].'</dd></dl>', ($usrrank <= 7 ? TRUE : FALSE), $pendid);
		
		?>
		<div class="smiley">
			Your contribution has been <?=($pending ? 'submitted to the editors for review' : 'successfully posted (refresh this page to see)')?>. 
			Thanks for contributing! You can see all your great contributions at your <a href="/user-contributions.php?usrid=<?=$usrid?>">contributions page</a>.
			<p><a href="javascript:void(0)" class="arrow-right" onclick="GCtoggle('person', true);GCtoggle('person', true);">Add another developer</a></p>
		</div>
		<?
		exit;
		
	}
}

if($action == "select_pid") {
	
	if(!$pid = $_POST['pid']) die("No pid given");
	
	$res = mysql_query("SELECT * FROM people WHERE pid='$pid' LIMIT 1");
	if(!$pdat = mysql_fetch_object($res)) die("Couldn't get data for person ID # $pid;".mysql_error());
	
	$res = mysql_query("SELECT role FROM people_work WHERE gid='$gid' AND pid='$pid'");
	if(mysql_num_rows($res)) {
		while($row = mysql_fetch_assoc($res)) {
			$roles[] = $row['role'];
		}
	}
	
	?>
	<div style="margin-bottom:10px; padding:8px; border:1px solid #CCC;">
		<input type="button" value="Cancel" onclick="GCtoggle('person', true)" style="float:right; margin:-3px;"/>
		Adding <a href="/people/~<?=$pdat->name_url?>" target="_blank"><?=$pdat->name?></a> as a developer...
		<?
		if($roles) {
			?>
			<p>
				<b>Please note:</b> <?=$pdat->name?> is already credited as a developer of this game in the following role<?=(count($roles) > 1 ? 's' : '')?>:
				<ul style="list-style-type:square;">
					<?
					foreach($roles as $r) {
						echo '<li>'.$r.'</li>';
					}
					?>
				</ul>
				You may continue with this form if you're crediting this person with a different role.
			</p>
			<?
		}
		?>
	</div>
		
	<table border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td valign="top" style="padding-top:3px"><b>Role:</b> &nbsp; </td>
			<td>
				<select name="" id="gc-select-role">
					<option value="">Select a common role...</option>
					<?
					$result = mysql_query("SELECT role, COUNT(role) AS count FROM `people_work` WHERE role != '' GROUP BY role ORDER BY role");
					while ($row = mysql_fetch_assoc($result)) {
						if($row['count'] >= 4) echo '<option value="'.$row['role'].'">'.$row['role'].'</option>';
					}
					?>
				</select> 
				<input type="text" name="" value="...or input role here" size="25" id="gc-input-role" class="off" onclick="if(this.value=='...or input role here') { this.value=''; this.className=''; }"/>
				
				<p><label><input type="checkbox" name="" value="" id="gc-person-vital"/> This person's role was relatively vital to the creation of this game</label></p>
			</td>
		</tr>
	</table>
	
	<p>Include any notes, clarfications, or interesting facts about this person's role in this game:</p>
	<p style="margin-top:2px !important;"><textarea rows="2" cols="80" id="gc-role-notes"></textarea></p>
	
	<p>
		<input type="button" value="Submit" id="gc-person-submit-button" onclick="GCsubmitperson();"/> 
		<img src="/bin/img/loading-thickbox.gif" alt="loading" id="gc-person-loading" style="display:none"/>
	</p>
	<?
	
}

if($action == "quote_form") {
	
	?>
	<div style="margin:-5px -5px 0 -5px">
		<table border="0" cellpadding="0" cellspacing="5">
			<tr>
				<td valign="top"><span style="font:normal 50px 'arial black',arial; vertical-align:top; line-height:45px;">&ldquo;</span></td>
				<td>
					<textarea name="in[quote]" rows="7" cols="68" id="gc-quote"></textarea>
					<span style="font:normal 50px 'arial black',arial; vertical-align:top; line-height:45px;">&rdquo;</span>
				</td>
			</tr>
			<tr>
				<td style="text-align:center"><span style="font:normal 20px 'arial black',arial;">-</span></td>
				<td><input type="text" id="gc-quoter" size="35"/> Quoter</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="text" value="http://" size="35" id="gc-quote-link" onfocus="if(this.value=='http://') this.value='';" style="text-decoration:underline; color:#06C;"/> Quoter's link or location of source quote</td>
			</tr>
		</table>
	</div>
	<p><input type="button" value="Submit" onclick="GCsubmitquote();"/></p>
	<?
	
}

if($action == "submit_quote") {
	
	if(!$quote = str_replace("[AMP]", "&amp;", $_POST['quote'])) die("Error: No quote given.");
	$quoter = str_replace("[AMP]", "&amp;", $_POST['quoter']);
	$quote_link = str_replace("[AMP]", "&", $_POST['quote_link']);
	if($quote_link == "http://") unset($quote_link);
	if($quote_link) {
		$quoter = '<a href="'.$quote_link.'" target="_blank">'.$quoter.'</a>';
	}
	
	$now = date('Y-m-d H:i:s');
	
	$description = '<a href="/games/link.php?id='.$gid.'">'.htmlentities($gdat->title, ENT_QUOTES).'</a> quote by '.$name;
	
	//pend it or post automatically?
	if($usrrank >= 4) {
		
		$pending = FALSE;
		
		$q = sprintf("INSERT INTO games_quotes (gid, quote, cut_off, quoter, usrid, datetime) VALUES 
		('$gid', '%s', '1', '%s', '$usrid', '$now');",
		mysql_real_escape_string($quote),
		mysql_real_escape_string($quoter));
		if(!mysql_query($q)) die("Couldn't add quote to database; ".mysql_error());
		
		contributeToGame();
		
	} else {
		
		$pending = TRUE;
		
		if(!$nextid = mysqlNextAutoIncrement("pending")) die("Couldn't get next database id");
		
		$q = "INSERT INTO pending (`what`, `table`, usrid, `datetime`) VALUES 
		('$description', 'pending_games_quotes', '$usrid', '$now');";
		if(!mysql_query($q)) die("Error saving to `pending`; ".mysql_error());
		
		$q = sprintf("INSERT INTO pending_games_quotes (pend_id, gid, quote, quoter) VALUES 
		('$nextid', '$gid', '%s', '%s');",
		mysql_real_escape_string($quote),
		mysql_real_escape_string($quoter));
		if(!mysql_query($q)) {
			sendBug("Error adding a user-submitted game quote\n\ngid: $gid (http://videogam.in/games/link.php?id=$gid)\nuser: $usrname (http://videogam.in/~$usrname)\ndb query: ".$q."\nerror: ".mysql_error());
			die("Error saving to database; ".mysql_error());
		}
		
	}
	
	addUserContribution($description, '<blockquote>'.$_POST['quote'].'</blockquote>-'.$quoter, ($usrrank <= 7 ? TRUE : FALSE), $pending);
	
	?>
	<div class="smiley">
		Your quote has been <?=($pending ? 'submitted to the editors for review' : 'successfully posted (refresh this page to see)')?>. 
		Thanks for contributing! You can see all your great contributions at your <a href="/user-contributions.php?usrid='.$usrid.'">contributions page</a>.
		<p><a href="javascript:void(0)" class="arrow-right" onclick="GCtoggle('quote', true);GCtoggle('quote', true);">Add another quote</a></p>
	</div>
	<?
	exit;
	
}