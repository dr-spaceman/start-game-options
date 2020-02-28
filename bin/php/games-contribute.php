<?
// FACTOID
// LINK
// PUBLICATION
// SCREENS
// PERSON
// QUOTE
die("This function has been depreciated. The new editing system will be online soon!");

require_once ($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");
require_once ($_SERVER['DOCUMENT_ROOT']."/bin/php/contribute.php");
require_once ($_SERVER['DOCUMENT_ROOT']."/bin/php/htmltoolbox.php");

$do = $_GET['do'];
$action = ($_POST['_action'] ? $_POST['_action'] : $_POST['action']);
$gid = ($_POST['_gid'] ? $_POST['_gid'] : $_POST['gid']);
$submitform = $_POST['submitform'];

if(!$usrid && $action != "preview_link" && $action != "print_pub_standards") die('Please log in to contribute.');

if($action == "set message cookie") {
	setcookie("dont_show_contribute_message", "1", time()+60*60*24*30, "/"); //30 days
	exit;
}

if($_POST && $gid) {
	$q = "SELECT * FROM games WHERE gid='$gid' LIMIT 1";
	if(!$gdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Couldn't get game data for gid # $gid");
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

// FACTOID //

if($action == "trivia_form") {
		
	?>
	<form action="javascript:void(0);" onsubmit="GCsubmittrivia()">
		<big style="color:#666">Share an interesting (and true!) fact or bit of trivia about this game.</big>
		<p>Appropriate submissions include uncommon knowledge, development information, easter eggs, etc. Please only submit <i>relevant</i> and <i>interesting</i> 
			information, citing the source (if applicable) to corroborate your submission. <b>Please don't include game hints, tips, tricks, etc. (those go into the 
			game guide).</b>
		</p>
		<p><?=outputToolbox("gc-input-trivia", array("b", "i", "a", "blockquote", "spoiler", "links"), "bbcode")?></p>
		<p style="margin-right:6px"><textarea name="" rows="5" id="gc-input-trivia" onchange="has_trivia=true;" style="width:100%; background-color:#F5F5F5;"></textarea></p>
		
		<p>
			<fieldset>
				<legend>Source</legend>
				You will be credited as the contributor of this factoid, however, please cite the original source if applicable.
				<p><input type="text" name="" value="" size="40" id="gc-input-author" style="background-color:#F5F5F5;"/> Website or Author's name</p>
				<p><input type="text" name="" value="" size="40" id="gc-input-authorlink" style="text-decoration:underline; color:#06C; background-color:#F5F5F5;"/> http:// link back to source text</p>
			</fieldset>
		</p>
		<p><input type="submit" value="Submit" id="gc-trivia-button"/></p>
	</form>
	<?

}
if($action == "submit_trivia") {
	
	$fact = str_replace("[AMP]", "&", $_POST['fact']);
	$fact = str_replace("[PLUS]", "+", $_POST['fact']);
	$author = str_replace("[AMP]", "&", $_POST['author']);
	$author = str_replace("[PLUS]", "+", $_POST['author']);
	$author_link = str_replace("[AMP]", "&", $_POST['author_link']);
	$author_link = str_replace("[PLUS]", "+", $_POST['author_link']);
	if($author || $author_link) {
		$cite = " [cite".($author_link ? "=".$author_link : '')."]".(!$author ? $author_link : $author)."[/cite]";
		$fact.= $cite;
	}
	$fact = strip_tags($fact);
	
	$contr = new contribution;
	$contr->type = 4;
	$contr->desc = 'Trivia for [gid='.$gdat->gid.'/]';
	$contr->ssubj = "gid:".$gid;
	$contr->data = "{fact:}".$fact;
	if($usrrank >= 4 || $gdat->unpublished) {
		$contr->status = "publish";
		$contr->subj = "games_trivia:id:".mysqlNextAutoIncrement("games_trivia").":";
		$q = "INSERT INTO games_trivia (gid, fact, datetime, usrid) VALUES 
		('$gid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $fact)."', '".date("Y-m-d H:i:s")."', '$usrid')";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Couldn't update games_trivia database; ".mysqli_error($GLOBALS['db']['link']));
	} else $contr->status = "pend";
	$cres = $contr->submitNew();
	
	?>
	<div class="smiley">
		Your trivia has been <?=(!$cres['published'] ? 'submitted to the editors for review' : 'successfully posted (refresh this page to see)')?>. 
		Thanks for contributing! You can see all your great contributions at your <a href="/user-contributions.php?usrid=<?=$usrid?>">contributions page</a>.
	</div>
	<?
	exit;
	
}
	

// LINK //

if($action == "preview_link") {

	$tags = @get_meta_tags($_POST['_url']);
	$desc = $tags['description'];
  
  if ($fp = @fopen( $_POST['_url'], 'r' )) {
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
		<input type="hidden" name="in[url]" value="<?=$_POST['_url']?>"/>
		<?=$auth->hidden?>
		<table cellpadding="0" cellspacing="0" width="100%" class="styled-form">
			<tr>
				<th>Site Name</th>
				<td><div style="margin-right:6px"><input type="text" name="in[site_name]" value="<?=$title?>" maxlength="75" style="width:100%"/></div></td>
			</tr>
			<tr>
				<th>Description<br/><small>optional</small></th>
				<td><div style="margin-right:6px"><textarea name="in[description]" maxlength="255" style="width:100%; height:4em;"><?=$desc?></textarea></div></td>
			</tr>
			<?
			if($usrrank >= 6) {
				?>
				<tr>
					<th>Request link exchange</th>
					<td>
						<label><input type="checkbox" name="" value=""/> Send the following link request to:</label> 
						<input type="text" name="in[to]" value="email address" class="resetonfocus" onfocus="$(this).removeClass('resetonfocus').val('');"/>
						<p style="margin-right:6px"><textarea name="in[message]" style="width:100%; height:4em;">Hello, it's <?=$usrname?> from over at Videogam.in (http://Videogam.in). We noticed your site and have deemed it a respectable establishment, so we thought it apt to notify our users about you. We posted a link to your site on our <?=$gdat->title?> overview (located at http://videogam.in/games/~<?=$gdat->title_url?>) and wonder if you would care to have a link exchange with us. We have some buttons and HTML-ready text links at http://videogam.in/links.php#linkback. Thanks!
- <?=$usrname?></textarea></p>
						<p>(or just copy the message to send in a form)</p>
					</td>
				</tr>
				<?
			}
			?>
			<tr>
				<th>Authenticate</th>
				<td><?=$auth->label?><?=$auth->input?> ?</td>
			</tr>
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

if($action == "print_pub_standards") {
	
	?>
	<div style="line-height:1.5em; font-size:13px; padding:25px; background:#EEE url(/bin/img/gradient-eee-120.png) repeat-x 0 0;">
		<big style="font-size:23px; color:#AAA;">Before continuing, please make sure your box art meets these standards</big>
		<p>Beacuse of how we use and display box art, it has to meet certain requirements before can be displayed.</p>
		<p>Please make sure your box art is:
			<ul>
				<li>JPG, GIF, or PNG format</li>
				<li>At least 200 pixels in width</li>
				<li>An unblurred, clear, quality image without watermarks or site logos</li>
				<li>A flat image that is not scaled, rotated, has a 3D perspective, or has any borders or whitespace around the perimeter (tip: use <a href="http://www.google.com/search?q=online+image+editor" target="_blank" class="arrow-link">an online image editor</a> like <a href="http://www.pixlr.com" target="_blank" class="arrow-link">Pixlr</a> to quickly and easily crop any whitespace or borders from an image)</li>
			</ul>
		</p>
		<br/>
		<table border="0" cellpadding="10" cellspacing="0" class="plain" style="background-color:#DDD;">
			<tr>
				<th colspan="3" style="text-align:center;">Some examples of bad box art</th>
			</tr>
			<tr>
				<td><img src="/games/bad_boxes/3d.jpg"/></td>
				<td><img src="/games/bad_boxes/watermark.jpg"/></td>
				<td><img src="/games/bad_boxes/whitespace.jpg"/></td>
			</tr>
			<tr>
				<td style="text-align:center;">3d perspective</td>
				<td style="text-align:center;">watermark from a<br/>douchebag site</td>
				<td style="text-align:center;">whitespace around permiter<br/>(even <i>a little</i> is bad!)</td>
			</tr>
		</table>
		<div style="margin:25px -25px -25px -25px; padding:25px; border-top:1px solid #888; background-color:#FFFFB0;">
			<label style="display:block; margin-bottom:5px;"><button onclick="$('#TB_overlay').fadeOut(); $('#TB_window').slideUp(GCrmoverlay);" style="font-weight:bold;">Continue</button> <big style="font-size:18px">my box art meets these standards</big></label>
			<label><button onclick="$('#TB_overlay').fadeOut(); $('#TB_window').slideUp(GCrmoverlay); $('#contribution-panel').hide();">Cancel</button> my box art doesn't meet these stringent requirements</label>
		</div>
	</div>
	<?
	
}

if($action == "pub_form") {
	
	?>
	
	<form action="/bin/php/games-contribute.php" method="post" target="gc-pub-upload" enctype="multipart/form-data" id="gc-pub-input">
		
		The absolute best source we've found for quality cover art is GameFAQs. Before submitting your box art, please check their site to see if they have a nicer picture. Visit the following URL to go straight to search results. Handy!<br/>
		<a href="http://www.gamefaqs.com/search/index.html?game=<?=urlencode($gdat->title)?>" target="_blank" class="arrow-link" style="font-size:17px">http://www.gamefaqs.com/search/index.html?game=<?=urlencode($gdat->title)?></a>
		<br/><br/>
		
		<input type="hidden" name="gid" value="<?=$gid?>"/>
		<input type="hidden" name="action" value="submit pub"/>
		<table border="0" cellpadding="0" cellspacing="0" width="100%" class="styled-form">
			<tr>
				<th>Upload Box Art</th>
				<td>
					Please see <a href="javascript:void(0);" class="arrow-link" onclick="AGboxstandardsoverlay();">box art standards</a>
					<p><input type="file" name="file" id="gc-pub-file"/></p>
					<p><label><input type="checkbox" name="in[placeholder_img]" value="1"/> This is a placeholder image and not the real cover image <a href="javascript:void(0)" class="tooltip tooltip-block" title="If the release has no box art (such as is the case with an upcoming or downloadable game), please upload a representative image to serve as a placeholder.">?</a></p>
				</td>
			</tr>
			<tr>
				<th>Title <a href="javascript:void(0)" class="tooltip tooltip-block" title="Input the full title of the publication, for example: &quot;Final Fantasy XII Collector's Edition&quot; will differentiate it from regular old Final Fantasy XII">?</a></th>
				<td><input type="text" name="in[title]" value="<?=$gdat->title?>" size="50"/></td>
			</tr>
			<tr>
				<th>Platform</th>
				<td>
					<?
					$query = "SELECT * FROM games_platforms WHERE platform != 'multiple' ORDER BY platform";
					$res   = mysqli_query($GLOBALS['db']['link'], $query);
					while($row = mysqli_fetch_assoc($res)) {
						$pfs[] = $row;
						if($row['notable']) $pfs_n[] = $row;
					}
					?>
					<select name="in[platform_id]" id="gc-pub-platform" onchange="if(this.options[this.selectedIndex].value=='other') { $(this).html($(this).next().html()); }">
						<option value="">Select a platform...</option>
						<?
						foreach($pfs_n as $row) echo '<option value="'.$row['platform_id'].'">'.$row['platform']."</option>\n";
						?>
						<option value="other">other...</option>
					</select>
					<select name="all_pfs" style="display:none">
						<option value="">Select from all platforms...</option>
						<?
						foreach($pfs as $row) echo '<option value="'.$row['platform_id'].'">'.$row['platform']."</option>\n";
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
						<option value="other">elsewhere...</option>
					</select>
					<select name="in[region_other]" id="gc-pub-region-other" style="display:none">
						<option value="">Select from all regions...</option>
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
			<tr style="display:none">
				<th>Publisher<br/><small>optional</small></th>
				<td>(If different from developer)
					<p><input type="text" name="in[publisher]" size="50" maxlength="100"/></p>
				</td>
			</tr>
			<tr style="display:none">
				<th>Release Info<br/><small>optional</small></th>
				<td>Notes or facts about this particular publication, including differences between this and another publication, additional stages, characters, modes, etc.
			<tr>
				<th>&nbsp;</th>
				<td colspan="2"><input type="submit" value="Submit" onclick="return GCcheckpub();"/></td>
			</tr>
		</table>
	</form>
	<div id="gc-pub-upload" style="display:none">
		Your submission is uploading to the box below.<br/>
		When it's finished you can <a href="javascript:void(0)" class="arrow-right" onclick="GCtoggle('pub', 'reload');">add another publication</a>
		<br/><br/>
		<fieldset>
			<legend>Result</legend>
			<iframe src="/bin/php/games-contribute.php?do=load" name="gc-pub-upload" frameborder="0" style="width:100%; height:35px;"></iframe>
		</fieldset>
	</div>
	<?
	
}

if($action == "submit pub") {
	
	echo $html_tag;
	?>
	<head>
		<title>New publication</title>
		<link rel="stylesheet" type="text/css" href="/bin/css/screen.css" media="screen"/>
		<style type="text/css">BODY, HTML { margin:0; padding:0; background-color:white !important; min-width:1px !important; }</style>
	</head>
	<body>
	<?
	if(!$_FILES['file']['name']) echo "No file detected";
	else {
		
		$in = $_POST['in'];
		if(!$in['platform_id'] && $in['platform_id'] != '0') die("No platform selected");
		if(!$in['year']) die("No release year input");
		if(!$in['title']) $in['title'] = $gdat->title;
		$in['title'] = htmlSC($in['title']);
		if(!$in['region']) {
			if($in['region_other']) $in['region'] = $in['region_other'];
			else die("No region selected");
		}
		$rd = $in['year']."-".$in['month']."-".$in['day'];
		//get # of current pubs and decide if this should be the primary pub
		$q = "SELECT * FROM games_publications WHERE gid='$gid' LIMIT 1";
		if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $primary = '1';
		else $primary = '0';
		
		$boxfiles = processBoxes($_FILES['file'], $gid);
		
    $contr = new contribution;
    $contr->upload = $_FILES['file'];
		$contr->type = 3;
		$contr->desc = '[gid='.$gdat->gid.'/] cover art/publication';
		$contr->ssubj = "gid:".$gid;
		$contr->data = "{title:}".$in['title']."|--|{release_date:}".$rd."|--|{platform_id:}".$in['platform_id']."|--|{region:}".$in['region']."|--|{placeholder_img:}".$in['placeholder_img']."|--|{primary:}".$primary.'|--|{*upload:}<img src="/bin/uploads/contributions/[CID].png"/>';
		
		if($usrrank >= 4 || $gdat->unpublished) {
			$contr->status = "publish";
			$nextid = mysqlNextAutoIncrement("games_publications");
			$contr->subj = "games_publications:id:".$nextid.":";
			$q = sprintf(
				"INSERT INTO games_publications (gid, platform_id, title, region, release_date, `primary`, `placeholder_img`) VALUES 
				('$gid', '%s', '%s', '%s', '$rd', '$primary', '%s')",
				mysqli_real_escape_string($GLOBALS['db']['link'], $in['platform_id']),
				mysqli_real_escape_string($GLOBALS['db']['link'], $in['title']),
				mysqli_real_escape_string($GLOBALS['db']['link'], $in['region']),
				mysqli_real_escape_string($GLOBALS['db']['link'], $in['placeholder_img'])
			);
			if(!mysqli_query($GLOBALS['db']['link'], $q)) {
				sendBug("Error adding a user-submitted publication (box art) to a gamepage\n\ngid: $gid (http://videogam.in/games/$gid)\nuser: $usrname (http://videogam.in/~$usrname)\ndb query: ".$q."\nerror: ".mysqli_error($GLOBALS['db']['link']));
				die("Error saving to database; ".mysqli_error($GLOBALS['db']['link']));
			}
			
			$new_body = $gid."-box-".$nextid;
			$new_dir = "/games/files/".$gid."/";
			processBoxesDirs($boxfiles, $new_body, $new_dir);
			
		} else $contr->status = "pend";
		
		$cres = $contr->submitNew();
		
		?>
		<div class="smiley">
			Your box art has been <?=(!$cres['published'] ? 'submitted to the editors for review' : 'successfully posted (refresh this page to see)')?>. 
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

/*
screens are now managed via the Sblog Content Mgr 2009-Oct-21
if($action == "screens_form") {
	die('Please post screens via the <a href="/posts/manage.php?action=newpost&type=image">Content Manager</a>. Upon tagging your post with this game, they\'ll show up here.');
	?>
	<div id="gc-ss-forms">
		
		<?
		if($usrrank >= 7) {
			echo '<div style="margin-bottom:5px; padding:5px; border:1px solid #DDD;">This is a simple form designed for regular users to upload a few screens. Since you\'re an admin, you have access to the much more powerful <a href="/ninadmin/media.php">media uploader</a>.</div>';
		}
		
		//user already uploaded some screens?
		$dir = "/media/".$gdat->title_url."-".$gdat->gid."-screens-uid".$usrid;
		$q = "SELECT * FROM media WHERE directory='$dir' LIMIT 1";
		if($x = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
			$has_note = ' (you have uploaded '.$x->quantity.' so far)';
			$desc = htmlsc($x->description);
		} else {
			$desc = htmlSC($gdat->title)." screenshots";
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
		
		<fieldset style="margin:0 0 10px; border:1px solid #CCC;">
			<legend style="font-weight:normal; font-size:14px; color:#666;">Description of this set of screenshots</legend>
			<input type="text" value="<?=$desc?>" size="75" id="inp-ss-desc"/>
		</fieldset>
		
		<?
		for($i = 1; $i <= 20; $i++) {
			?>
			<div id="gc-ss-<?=$i?>" style="<?=($i > 1 ? 'display:none; margin-top:10px;' : '')?>">
				<div id="gc-ss-<?=$i?>-input" class="gc-ss-form">
					<form action="/bin/php/games-contribute.php" method="post" target="gc-ss-<?=$i?>-upload" enctype="multipart/form-data">
						<input type="hidden" name="gid" value="<?=$gid?>"/>
						<input type="hidden" name="action" value="upload screen"/>
						<input type="hidden" name="description" value="" class="inp-ss-desc"/>
						<input type="file" name="screen" id="gc-ss-<?=$i?>-file"/>
						<label>
							caption: 
							<a href="javascript:void(0)" class="tooltip" title="A description of the image (optional)"><span class="block" style="font-size:12px">?</span></a> 
							<input type="text" name="caption" size="50"/>
						</label>
						<input type="submit" value="Upload" onclick="$(this).siblings('.inp-ss-desc').val( $('#inp-ss-desc').val() ); return GCcheckss('<?=$i?>');"/>
					</form>
				</div>
				<iframe src="/bin/php/games-contribute.php?do=load" name="gc-ss-<?=$i?>-upload" id="gc-ss-<?=$i?>-upload" frameborder="0" style="display:none; width:532px; height:28px;"></iframe>
			</div>
			<?
		}
		?>
		<div id="gc-ss-21" style="display:none; margin-top:10px;">You have reached the maximum of 20 screens. <a href="#contribute" onclick="has_screens=false; GCtoggle('screens',1); GCtoggle('screens',1);">Reload this frame</a> if you want to upload more.</div>
		
		<div id="gc-ss-finished" style="display:none; margin:10px 0 5px; padding-top:10px; border-top:1px solid #DDD; color:#666;">
			<input type="button" value="Finished Uploading" style="float:right;" onclick="GCtoggle('screens',1);"/>
			<a href="javascript:void(0)" class="arrow-toggle" onclick="$(this).toggleClass('arrow-toggle-on').next().toggle();">Cite screenshot source</a> if you didn't capture the screens
			<div style="display:none;">
				<p><input type="text" name="source_name" id="gc-ss-source" size="35"/> Source name</p>
				<p><input type="text" name="source_url" id="gc-ss-sourceurl" value="http://" size="35" style="text-decoration:underline; color:blue;"/> Source URL</p>
				<p>
					<input type="button" value="Submit" id="gc-ss-source-button" onclick="GCsubmitSsSource('<?=$gdat->title_url?>-<?=$gid?>-screens-uid<?=$usrid?>');"/> 
					<span id="gc-ss-source-space">(submitting citation won't interrupt upload process)</span>
				</p>
			</div>
		</div>
		
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
			
			$contr = new contribution;
    	$contr->upload = $_FILES['screen'];
    	$contr->status = ($usrrank >= 4 || $gdat->unpublished ? "publish" : "pend");
			
			//check file type
			$ext = substr($_FILES['screen']['name'], -3);
			$ext = strtolower($ext);
			$allowed_exts = array("jpg", "gif", "png");
			if(!in_array($ext, $allowed_exts)) die("Error uploading '".$_FILES['screen']['name']."': Please only upload JPG, GIF, or PNG files.");
			
			$dir = "/media/".$gdat->title_url."-".$gdat->gid."-screens-uid".$usrid;
			
			$desc = mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['description']);
			$desc = trim($desc);
			if(!$desc) $desc = htmlSC($gdat->title)." screenshots";
			
			$media_query = "SELECT * FROM media WHERE directory='$dir' LIMIT 1";
			$media_res = mysqli_query($GLOBALS['db']['link'], $media_query);
			if(!mysqli_num_rows($media_res)) {
			
				//make dir
				$subj = $_SERVER['DOCUMENT_ROOT'].$dir;
				if(is_dir($subj) && (!@mkdir($subj, 0777) || !@mkdir($subj."/thumbs", 0777))) {
					die("Couldn't make directories ($subj)");
				}
				
				$nextid = mysqlNextAutoIncrement("media");
				$media_id = $nextid;
				
				$q = "INSERT INTO media (directory, category_id, description, gallery, datetime, usrid, quantity, unpublished) VALUES 
				('$dir', '1', '$desc', '1', '".date("Y-m-d H:i:s")."', '$usrid', '1', '".($contr->status == "pend" ? '1' : '')."')";
				if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Couldn't add to db: ".mysqli_error($GLOBALS['db']['link']));
				
				$q = "INSERT INTO media_tags (media_id, tag) VALUES ('$nextid', 'gid:".$gdat->gid."')";
				if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Couldn't add to tag db: ".mysqli_error($GLOBALS['db']['link']));
				
			} else {
				
				//qty + 1
				$dat = mysqli_fetch_object($media_res);
				$q = "UPDATE media SET `quantity`='".($dat->quantity + 1)."', `description`='$desc' WHERE media_id='".$dat->media_id."' LIMIT 1";
				mysqli_query($GLOBALS['db']['link'], $q);
				
				$media_id = $dat->media_id;
				
				//if big time difference, notify editor
				$diff = time() - strtotime($dat->datetime);
				if($diff > 3700) $contr->notify = TRUE; //about 1 hour
				
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
						$capt = htmlSC($capt);
						$q = sprintf("INSERT INTO media_captions (media_id, `file`, `caption`) VALUES ('$media_id', '".$file."', '%s')",
							mysqli_real_escape_string($GLOBALS['db']['link'], $capt));
						if(!mysqli_query($GLOBALS['db']['link'], $q)) echo "Error! Couldn't add caption. ";
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
					$contr->type = 9;
					$contr->desc = '[gid='.$gdat->gid.'/] <a href="/media.php?mid='.$media_id.'">screenshots</a>';
					$contr->data = '{media_id:}'.$media_id.'|--|{caption:}'.$capt;
					$contr->subj = "media:".$media_id;
					$contr->ssubj = 'gid:'.$gid;
					$contr_res = $contr->submitNew();
					
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

if($action == "ss source") {
	
	if(!$dir = $_POST['dir']) die("Error: no directory given");
	$source = trim($_POST['sname']);
	if($_POST['surl'] != "http://" && $_POST['surl'] != "") $source = '<a href="'.htmlSC($_POST['surl']).'" target="_blank">'.$source.'</a>';
	if($source) {
		$q = "UPDATE media SET `source`='".mysqli_real_escape_string($GLOBALS['db']['link'], $source)."' WHERE `directory`='/media/$dir' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Couldn't update database: ".mysqli_error($GLOBALS['db']['link']));
		else die("Source input saved.");
	}
	
}*/



// VIDEO //

if($action == "video") {
	
	?>
	<form action="/posts/manage.php" method="get">
		<input type="hidden" name="action" value="newpost"/>
		<input type="hidden" name="type" value="video"/>
		<input type="hidden" name="autotag" value="gid:<?=$gid?>"/>
		
		Input the source URL of the video: &nbsp; <span style="color:#888;">For example: <i>http://www.youtube.com/watch?v=jiS6gtClrqk</i></span>
		
		<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top:10px;">
			<tr>
				<td width="100%" style="padding-right:10px;">
					<input type="text" name="video_url" value="http://" onfocus="$(this).select();" style="width:100%; font:normal 16px arial; text-decoration:underline; color:blue;"/>
				</td>
				<td>
					<input type="submit" value="Next" style="font-size:16px;"/>
				</td>
			</tr>
		</table>
	</form>
	<?
	
}

// PERSON //

if($action == "check_name") {
	
	if(!$name = $_POST['_name']) die("No name given [Error #002ACTCHNAME]");
	
	//check if the person exists and what roles he already has listed for this game
	list($checkname, $name_url) = formatName($name);
	$checkname = mysqli_real_escape_string($GLOBALS['db']['link'], $checkname);
	$res = mysqli_query($GLOBALS['db']['link'], "SELECT * FROM people WHERE name='$checkname' LIMIT 1");
	$roles = array();
	if($pdat = mysqli_fetch_object($res)) {
		$res2 = mysqli_query($GLOBALS['db']['link'], "SELECT role FROM people_work WHERE gid='$gid' AND pid='".$pdat->pid."'");
		if(mysqli_num_rows($res2)) {
			while($row = mysqli_fetch_assoc($res2)) {
				$roles[] = $row['role'];
			}
		}
	}
	
	if(!$pdat) {
		
		//search for possible matches
		
		$matches = array();
		
		$query = "SELECT `name`, `alias`, pid, `title`, MATCH (`name`, `alias`) AGAINST ('".mysqli_real_escape_string($GLOBALS['db']['link'], $checkname)."') AS `score` 
			FROM people 
			WHERE MATCH (`name`, `alias`) AGAINST ('".mysqli_real_escape_string($GLOBALS['db']['link'], $checkname)."') 
			ORDER BY `score` DESC LIMIT 10";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		while($row = mysqli_fetch_assoc($res)) {
			if($row['score'] > 1) $matches[] = $row;
		}
		
		?>
		<div id="gc-name-matches">
			<big>"<?=$checkname?>" isn't listed in the database yet.</big>
			
			<?
			if(count($matches)) {
				?>
				<p>Possible matches:</p>
				<ul style="margin:3px; padding:0;">
					<?
					$i = 0;
					foreach($matches as $row) {
						$i++;
						echo '<li style="margin:0 0 3px 0; padding:0; list-style:none;"><input type="radio" name="person_name" value="'.htmlSC($row['name']).'"'.($i == 1 ? ' checked="checked"' : '').' class="gcselname"/> <a href="/people/'.$row['pid'].'/" target="_blank" class="arrow-link">'.$row['name'].'</a> '.$row['title'].'</li>';
					}
					?>
				</ul>
				<div style="margin:7px 0 10px; padding-left:27px; background:url(/bin/img/arrow-down-right.png) no-repeat 10px center;">
					<input type="button" value="Use the selected name" onclick="GCsuggestname( $('.gcselname:checked').val() );"/>
				</div>
				<p>If none of the people match, you can add this person to the database by continuing with this form. 
					Upon adding this person, you should follow up by contributing some more information to this person's profile.</p>
				<?
			} else {
				?>
				<p>There's no record of this person yet, but you can add them to the database by continuing with this form. 
					Upon adding this person, you should follow up by contributing some more information to this person's profile.</p>
				<?
			}
			
			?>
			<p>
				<input type="button" value="Cancel" onclick="GCtoggle('person', 'reload')" style="float:right;"/>
				<input type="button" value="Add" onclick="$('#gc-name-matches').hide().next().show();"/> "<?=$checkname?>" to the database.
			</p>
		</div>
		<?
		
	}
	
	?>
	<div style="<?=(!$pdat ? 'display:none;' : '')?>">
		<input type="hidden" value="<?=$pdat->pid?>" id="gc-pid"/>
		<input type="hidden" value="<?=htmlSC($name)?>" id="gc-name"/>
		
		<div style="margin-bottom:10px; padding:8px; border:1px solid #CCC;">
			
			<input type="button" value="Cancel" onclick="GCtoggle('person', 'reload')" style="float:right; margin:-4px;"/>
			
			Adding <?=($pdat ? '<a href="/people/~'.$pdat->name_url.'" target="_blank" class="arrow-link">'.$name.'</a>' : "<b>$name</b>")?> as a developer...
			<?
			if(count($roles)) {
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
			if(!$pdat) {
				?>
				<p>Upon inputting a role below, a new database entry and profile page will be created for this person. Please assign this person a title:</p>
				<p><b><?=$name?></b> is a <input type="text" name="title" size="20" maxlength="30" id="gc-newperson-title"/></p>
				<p>The title should be as succunct as possible while describing this person's specific career or role in the games industry.</p>
				<p>Some examples: <i>Game Designer</i>, <i>Music Composer</i>, <i>Programmer</i>, <i>Executve</i>, <i>Conceptual Designer</i>, etc.</p>
				<?
			}
			?>
		</div>
		
		<br/>
		
		<form action="javascript:void(0);" onsubmit="GCsubmitperson();">
			<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td nowrap="nowrap" style="padding-bottom:3px; font-size:14px; border-bottom:1px solid #DDD;">
						<b>Role in this game:</b>&nbsp;&nbsp;
					</td>
					<td width="100%" style="padding:0 9px 3px 0; border-bottom:1px solid #DDD;">
						<input type="text" name="role" id="gc-input-role" value="Start typing to find a common role" class="resetonfocus" style="width:100%; background-color:#F5F5F5;" onfocus="if( $(this).val() == 'Start typing to find a common role') { $(this).val('').removeClass('resetonfocus'); }"/>
					</td>
					<td nowrap="nowrap" style="padding:0 6px 3px 3px; font-size:14px; border-width:1px 1px 0; border-style:solid; border-color:#DDD; background-color:#FFC;">
						<label><input type="radio" name="vital" value="1" id="role-vital"/><b>Vital Role</b></label> &nbsp; 
						<label><input type="radio" name="vital" value="" id="role-nonvital"/>Personnel</label> &nbsp; 
					</td>
				</tr>
				<tr>
					<td colspan="3" style="padding:3px 6px; border-width:0 1px 1px; border-style:solid; border-color:#DDD;">
						<b>Please input only one role.</b> If this person had multiple roles, input them individually unless the roles are closely related, 
							for example: "Composer, Arranger"; "Illustrator, Character Designer".
						<p>Input a <b>generalized role</b> to credit this person with work on this game. For example, "Voice Actor" instead of "Voice of Ashe". 
							Include the specific role credited in the <b>notes</b> field below.</p>
						<p>Mark this person's role as <b>vital</b> if they made a major contribution to the creation of this game.</p>
					</td>
				</tr>
			</table>
			
			<br/>
			
			<p>Include any notes, clarfications, or interesting facts about this person's role in this game:</p>
			<p><span style="font-size:11px; color:#888;">This field is only for short notes. For anything extensive, add a trivia entry or write an article.</span></p>
			<p style="margin-right:6px;"><textarea rows="2" id="gc-role-notes" style="width:100%; background-color:#F5F5F5;"></textarea></p>
			<p><input type="submit" value="Submit" id="gc-person-submit-button"/></p>
		</form>
	</div>
	<?
	
}

if($action == "submit_person") {
	
	$_POST['role'] = str_replace("[AMP]", "&", $_POST['role']);
	$_POST['role'] = str_replace("[PLUS]", "+", $_POST['role']);
	$_POST['notes'] = str_replace("[AMP]", "&", $_POST['notes']);
	$_POST['notes'] = str_replace("[PLUS]", "+", $_POST['notes']);
	$now = date('Y-m-d H:i:s');
	$name = trim($_POST['_name']);
	if($_POST['vital']) $vital = "1";
	
	$contr = new contribution;
	$contr->status = "pend";
	
	if($pid = $_POST['pid']) {
		
		// existing person //
		
		$contr->type = 6;
		$contr->ssubj = "pid:".$pid;
		$contr->desc = '[pid='.$pid.'/] role in [gid='.$gid.'/] ('.$_POST['role'].')';
		$contr->data = '{pid:}'.$pid.'|--|{gid:}'.$gid.'|--|{role:}'.$_POST['role'].'|--|{notes:}'.$_POST['notes'].'|--|{vital:}'.$vital;
		
		$res = mysqli_query($GLOBALS['db']['link'], "SELECT * FROM people WHERE pid='$pid' LIMIT 1");
		if(!$pdat = mysqli_fetch_object($res)) die("Couldn't get data for person ID # $pid;".mysqli_error($GLOBALS['db']['link']));
		
		//post automatically?
		if($usrrank >= 4) {
			
			$contr->status = "publish";
			$contr->subj = "people_work:id:".mysqlNextAutoIncrement("people_work").":";
			
			$q = "INSERT INTO people_work (pid, gid, role, notes, vital) VALUES 
			('".mysqli_real_escape_string($GLOBALS['db']['link'], $pid)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $gid)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['role'])."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['notes'])."', '$vital')";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Couldn't update people work database; ".mysqli_error($GLOBALS['db']['link']));
			
		} else {
			
			$contr->status = "pend";
			
		}
		
		$conr_res = $contr->submitNew();
		if($contr_res['errors']) die("Error: ".implode("; ", $contr_res1['errors']));
		
		?>
		<div class="smiley">
			Your contribution has been <?=(!$conr_res['published'] ? 'submitted to the editors for review' : 'successfully posted (<a href="./developers">go to Developer Page</a>)')?>. 
			Thanks for contributing! You can see all your great contributions at your <a href="/user-contributions.php?usrid=<?=$usrid?>">contributions page</a>.
			<p><a href="javascript:void(0)" class="arrow-right" onclick="GCtoggle('person', 'reload');">Add another developer</a></p>
		</div>
		<?
		exit;
	
	} else {
		
		// NEW PERSON //
		
		if(!$name) die("Error: No name given. [Error #003NEWP]");
		
		list($name, $name_url) = formatName($name);
		
		$title = trim($_POST['_title']);
		$title = ucwords($title);
		
		$contr->type = 12;
		$contr->data = '{name:}'.$name.'|--|{title:}'.$title;
		$contr->process_data = FALSE;
		
		//pend it or post automatically?
		if($usrrank >= 4) {
			
			$pid = mysqlNextAutoIncrement("people");
			
			$contr->desc = 'New person: [pid='.$pid.'/]';
			$contr->subj = 'people:pid:'.$pid.':';
			$contr->ssubj = 'pid:'.$pid;
			$contr->status = "publish";
			
			$q = sprintf("INSERT INTO people (name, name_url, title, created, modified, contributors) VALUES 
			('%s', '%s', '%s', '$now', '$now', 'usrid:$usrid');",
			mysqli_real_escape_string($GLOBALS['db']['link'], $name),
			mysqli_real_escape_string($GLOBALS['db']['link'], $name_url),
			mysqli_real_escape_string($GLOBALS['db']['link'], $title));
			if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Couldn't add $name to people database; ".mysqli_error($GLOBALS['db']['link']));
			else {
				$contr_res1 = $contr->submitNew();
				if($contr_res1['errors']) die("Error: ".implode("; ", $contr_res1['errors']));
			}
			
			unset($contr);
			$contr = new contribution;
			$contr->status = "publish";
			$contr->type = 6;
			$contr->ssubj = "pid:".$pid;
			$contr->desc = '[pid='.$pid.'/] role in [gid='.$gid.'/] ('.$_POST['role'].')';
			$contr->data = '{pid:}'.$pid.'|--|{gid:}'.$gid.'|--|{role:}'.$_POST['role'].'|--|{notes:}'.$_POST['notes'].'|--|{vital:}'.$vital;
			
			$workid = mysqlNextAutoIncrement("people_work");
			
			$contr->subj = 'people_work:id:'.$workid.':';
			
			$q = "INSERT INTO people_work (pid, gid, role, notes, vital) VALUES 
			('".mysqli_real_escape_string($GLOBALS['db']['link'], $pid)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $gid)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['role'])."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['notes'])."', '$vital')";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Couldn't update people work database; ".mysqli_error($GLOBALS['db']['link']));
			
			$contr_res2 = $contr->submitNew();
			
		} else {
			
			list($name, $name_url) = formatName($name);
			$temp_pid = $name_url.rand(100000, 999999);
			
			$contr->desc = 'New Person: '.$name;
			$contr->data.= '|--|{*temp_pid:}'.$temp_pid;
			$contr->ssubj = 'pid:'.$temp_pid;
			$contr->subj = 'pid:'.$temp_pid.':title:';
			$contr->status = "pend";
			
			$contr_res1 = $contr->submitNew();
			if($contr_res1['errors']) die("Error: ".implode("; ", $contr_res1['errors']));
				
			unset($contr);
			$contr = new contribution;
			$contr->status = "pend";
			$contr->type = 6;
			$contr->ssubj = "pid:".$temp_pid;
			$contr->desc = $name.' role in [gid='.$gid.'/] ('.$_POST['role'].')';
			$contr->data = '{pid:}'.$temp_pid.'|--|{gid:}'.$gid.'|--|{role:}'.$_POST['role'].'|--|{notes:}'.$_POST['notes'].'|--|{vital:}'.$vital;
			
			$contr_res2 = $contr->submitNew();
			
		}
		
		?>
		<div class="smiley">
			Your contribution has been <?=(!$contr_res2['published'] ? 'submitted to the editors for review' : 'successfully posted (<a href="./developers">go to Developer Page</a>)')?>. 
			Thanks for contributing! You can see all your great contributions at your <a href="/user-contributions.php?usrid=<?=$usrid?>">contributions page</a>.
			<p><a href="javascript:void(0)" class="arrow-right" onclick="GCtoggle('person', 'reload');">Add another developer</a></p>
			<?=($contr_res2['published'] ? '<p><big style="font-size:14px;">You added <b>'.$name.'</b> to the people database! Please <a href="/people/'.$pid.'/'.$name_url.'/edit">contribute more information</a> to this person\'s lonely, empty profile.</big></p>' : '')?>
		</div>
		<?
		exit;
		
	}
}

if($action == "select_pid") {
	
	if(!$pid = $_POST['pid']) die("No pid given");
	
	$res = mysqli_query($GLOBALS['db']['link'], "SELECT * FROM people WHERE pid='$pid' LIMIT 1");
	if(!$pdat = mysqli_fetch_object($res)) die("Couldn't get data for person ID # $pid;".mysqli_error($GLOBALS['db']['link']));
	
	$res = mysqli_query($GLOBALS['db']['link'], "SELECT role FROM people_work WHERE gid='$gid' AND pid='$pid'");
	if(mysqli_num_rows($res)) {
		while($row = mysqli_fetch_assoc($res)) {
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
					$result = mysqli_query($GLOBALS['db']['link'], "SELECT role, COUNT(role) AS count FROM `people_work` WHERE role != '' GROUP BY role ORDER BY role");
					while ($row = mysqli_fetch_assoc($result)) {
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

// QUOTE //

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
	
	if(!$quote = str_replace("[AMP]", "&", $_POST['quote'])) die("Error: No quote given.");
	if(!$quoter = str_replace("[AMP]", "&", $_POST['quoter'])) $quoter = "Anonymous";
	$quote_link = str_replace("[AMP]", "&", $_POST['quote_link']);
	if($quote_link == "http://") unset($quote_link);
	if($quote_link) $quoter = '<a href="'.$quote_link.'" target="_blank">'.$quoter.'</a>';
	
	$now = date('Y-m-d H:i:s');
	
	$description = 'Quote about <a href="/games/link.php?id='.$gid.'">'.htmlSC($gdat->title).'</a> by '.$quoter;
	
	//pend it or post automatically?
	if($usrrank >= 4 || $gdat->unpublished) {
		
		$subj = "games_quotes:".mysqlNextAutoIncrement("games_quotes");
		$q = sprintf("INSERT INTO games_quotes (gid, quote, cut_off, quoter, usrid, datetime) VALUES 
		('$gid', '%s', '1', '%s', '$usrid', '$now');",
		mysqli_real_escape_string($GLOBALS['db']['link'], $quote),
		mysqli_real_escape_string($GLOBALS['db']['link'], $quoter));
		if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Couldn't add quote to database; ".mysqli_error($GLOBALS['db']['link']));
		
	} else {
		
		$pend_subm = $quote."|--|".$quoter;
		
	}
	
	addUserContribution(5, $description, '<blockquote>'.$_POST['quote'].'</blockquote>-'.$quoter, ($usrrank <= 8 ? TRUE : FALSE), $pend_subm, $subj, "gid:".$gid);
	
	?>
	<div class="smiley">
		Your quote has been <?=($pending ? 'submitted to the editors for review' : 'successfully posted (refresh this page to see)')?>. 
		Thanks for contributing! You can see all your great contributions at your <a href="/user-contributions.php?usrid='.$usrid.'">contributions page</a>.
	</div>
	<?
	exit;
	
}