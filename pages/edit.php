<?
require $_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php";
$page = new page();
require_once $_SERVER["DOCUMENT_ROOT"]."/pages/class.pages.edit.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.img.php";

$img_sessid = imgMakeSessionId();

if($sessid = $_GET['destroysession']){
	
	//DESTROY A SESSION
	
	$q = "SELECT * FROM pages_edit WHERE session_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $sessid)."' LIMIT 1";
	if(!$pe = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
		if($_GET['returnonfail']) {
			header("Location: ".pageURL($_GET['returnonfail']));
			die("Session not yet recorded");
		} else {
			die("Error: Couldn't get page data for id # ".$sessid);
		}
	}
	if($usrid != $pe->usrid && $usrrank < 8) die("You don't have access to this edit session.");
	if(!$_GET['sure']){
		
		$page->header();
		?>
		<fieldset style="float:left;">
			<legend>Session details</legend>
			<dl>
				<dt>Title</dt><dd><i><?=$pe->title?></i></dd>
				<dt>ID#</dt><dd><a href="/pages/history.php?view_version=<?=$sessid?>"><?=$sessid?></a></dd>
				<dt>Editor</dt><dd><?=outputUser($pe->usrid)?></dd>
				<dt>Edited</dt><dd><?=$pe->datetime?></dd>
			</dl>
		</fieldset>
		<br style="clear:left;"/>
		<br/>
		<div style="font-size:14px; float:left; border:1px solid #CCC; padding:10px;">
			<b>Permanently delete this revision?</b> 
			<a href="<?=pageURL($pe->title)?>" style="color:#DA2121">No, nevermind</a> | 
			<a href="edit.php?destroysession=<?=$sessid?>&sure=yes" style="color:#269F35">Yes, delete it for ever and ever</a>
		</div>
		<br style="clear:both;"/>
		<?
		$page->footer();
		exit;
	}
	
	$q = "DELETE FROM pages_edit WHERE session_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $sessid)."' LIMIT 1";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) die("Error removing session from database");
	
	$ed = new pgedit($pe->title);
	$ed->recalculatePageContr();
	
	header("Location: ".pageURL($pe->title));
	die("Success!");
	
}

if($_POST['pgtype']=="template"){
	$_GET['title'] = str_replace("Template:", "", $_GET['title']);
	$_GET['title'] = "Template:".$_GET['title'];
}

$title    = formatName($_GET['title']);
$titleurl = formatNameURL($title, 1);
$titlef   = htmlSC($title);

$page->title = $titlef . " [EDIT] -- Videogam.in";

$page->javascripts[] = "/pages/edit.js";

//$page->kill("The database is being updated to new formatting... be back soon.");

try {
	
	$ed = new pgedit($title);
	$ed->header();
	
	$ed->juststarted = false;

	if(!$usrid) $page->die_('<big style="font-size:150%;">Please <a href="/login.php" class="prompt">Log In</a> to continue.</big><br/><br/>Don\'t have an account? <a href="/register.php">Register</a> in about a minute.');
	
	// Check for unpublished edit session in past few days
	if($_POST['_action'] != "start" && !$_GET['editsource']){
		$query = "SELECT * FROM pages_edit WHERE title='".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."' AND usrid='$usrid' AND published='0' AND `datetime` > DATE_ADD('".date("Y-m-d H:i:s")."', INTERVAL -3 DAY) ORDER BY `datetime` DESC";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		if(mysqli_num_rows($res)){
			?>
			<div id="recentsessions" class="pgedbg" style="margin-bottom:20px; padding:20px 40px; font-size:14px; line-height:1.5em;">
				<b>You have unpublished edits for this page.</b> You can build upon a previous session or continue with the form below to start from scratch.
				<ul>
					<?
					while($row = mysqli_fetch_assoc($res)) {
						echo '<li><a href="history.php?view_version='.$row['session_id'].'">'.formatDate($row['datetime'], 2).'</a> <span style="color:#AAA;">[<a href="edit.php?title='.$titleurl.'&editsource='.$row['session_id'].'">build</a>]</span> '.$row['edit_summary'].'</li>';
					}
					?>
				</ul>
			</div>
			<?
		}
	}
	
	if($_POST['_action'] == "start"){
		
		// We're just starting a new page
		
		$ed->juststarted = true;
		
		$ed->type = $_POST['pgtype'];
		if(!in_array($ed->type, array_keys($pgtypes))) $page->die_("Error: Unknown page type given '$ed->type'");
		
		if($_POST[$ed->type]['subcategory']) $ed->subcategory = $_POST[$ed->type]['subcategory'];
		
		try{ $ed->data = $ed->template(); }
		catch(Exception $e){ $page->die_('Error creating base template data: <code>'.$e->getMessage().'</code>'); }
		
		if($_POST['redirect_to']){
			$reto = formatName($_POST['redirect_to']);
			$ed->data->content = '#REDIRECT [['.$reto.']]';
			$ed->sessionrow->edit_summary = 'Redirect to [['.$reto.']]';
			unset($ed->data->wikipedia_title);
			$ed->new_redirect = 1;
			echo '<script>$.jGrowl("A redirect template has been loaded into the edit form")</script>';
		}
		
	} elseif($_GET['editsource']){
		
		// Use an old revision as the basis
		
		$ed->editsource = $_GET['editsource'];
		$ed->sessid = $ed->editsource;
		
		try{ $ed->checkSession(); }
		catch(Exception $e){ $page->die_('There was an error loading session data from the session ID #'.$ed->sessid.': <code>' . $e->getMessage() . '</code> Try starting a fresh edit instead.'); }
		
		try{ $ed->loadData("sessid"); } // populates $ed->data
		catch(Exception $e){ $page->die_('There was an error loading data from the current version of this page: <code>' . $e->getMessage() . '</code>'); }
		
		$warnings[] = "You have loaded an <b>old revision file</b> into the form on this page. If you choose to submit & publish this form, the current version of this page willbe overwritten with your changes.";
		
		//get a new session ID so we don't overwrite the edit source
		if($ed->editsource) $ed->sessionStart();
		
	} elseif(!$ed->row){
		
		// START A PAGE //
		
		//check the title for matches
		$query = "SELECT `title`, MATCH (`title`, `keywords`) AGAINST ('".mysqli_real_escape_string($GLOBALS['db']['link'], $ed->title)."') AS `score` FROM pages WHERE redirect_to='' AND MATCH (`title`, `keywords`) AGAINST ('".mysqli_real_escape_string($GLOBALS['db']['link'], $ed->title)."') ORDER BY `score` DESC LIMIT 20";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		$matches = array();
		while($row = mysqli_fetch_assoc($res)) {
			if($row['score'] > 3) $matches[] = $row['title'];
		}
		
		?>
		<div id="pgedstart" class="pgedbg" style="padding:30px 40px; font-size:14px; line-height:1.5em;">
			<form action="edit.php?title=<?=$titleurl?>" method="post" id="startpgform">
				
				<input type="hidden" name="_action" value="start"/>
				<input type="hidden" name="watch[watch]" value="1"/>
				<textarea name="title" style="display:none;"><?=$ed->title?></textarea>
				
				<p style="margin-top:0;"><b>You're starting the <i><?=$ed->title?></i> page.</b> If this is your first time editing a Videogam.in content page, make sure to read the <a href="/sblog/1823/page-editing-guide" target="_blank" class="arrow-link">Page Creating Guide & F.A.Q.</a> and <a href="/formatting-help" target="_blank" class="arrow-link">Formatting Guide</a>.</p>
				
				<?
				if($matches) {
					?>
					<div class="hr"></div>
					<div style="">
						<big class="warn" style="font-weight:bold;">Possible matches already in the database</big>
						<ul style="list-style:none; margin:5px 0 0; padding-left:7px;">
							<?
							foreach($matches as $m){
								?>
								<li style="margin:0; padding:0 0 0 10px; background:url(/bin/img/bullet.png) no-repeat left center;" onmouseover="$(this).children('label.hideme').show()" onmouseout="$(this).children('label.hideme').hide()">
									<a href="<?=pageURL($m)?>"><?=$m?></a> 
									<label title="Redirect all traffic from <?=$titlef?> to <?=htmlSC($m)?>" style="display:none; color:#777;" class="arrow-left hideme">create redirect <input type="checkbox" name="redirect_to" value="<?=htmlSC($m)?>" class="redirectcheckbox" style="margin:0; padding:0; vertical-align:middle;" onclick="$('input:checkbox.redirectcheckbox').not($(this)).attr('checked', false).parent().hide().addClass('hideme'); if($(this).is(':checked')){$(this).parent().removeClass('hideme');$('#pgtype-redirect').slideDown().find(':input').click();}else{$(this).parent().addClass('hideme')};"/></span>
								</li>
								<?
							}
							?>
						</ul>
					</div>
					<div class="hr"></div>
					<?
				}
				?>
				
				<div style="line-height:2em;">
					<span style="font-size:13px; color:#555;"><b style="color:black;">Step 1</b>. What is <i><?=$ed->title?></i> ?</span>
					<ul id="pgtype-list" style="list-style:none; margin:0; padding:0; font-size:14px; color:#555;">
						<li><label style="color:black;"><input type="radio" name="pgtype" value="game"/>A Game</label></li>
						<li><label style="color:black;"><input type="radio" name="pgtype" value="person"/>A Person</label> involved in the creation of videogames <a href="#help" class="tooltip helpinfo" title="If the person isn't a game developer (for example: Michael Atkinson, Jack Thompson, Hulk Hogan), make it a TOPIC instead"><span>?</span></a></li>
						<li>
							<label style="color:black;"><input type="radio" name="pgtype" value="category"/>A Category</label> such as a console, company, series, genre, character, concept -- anything that can list games or people <a href="#help" class="tooltip helpinfo" title="For example: Square Enix, Mario, Halo series, Mustache, Indie game, PlayStation 2, NES Zapper..."><span>?</span></a> <a href="/sblog/1823/page-editing-guide#Categories" target="_blank" class="arrow-link">more</a>
							<div class="subsection" style="display:none; margin:0 0 10px 20px; font-size:12px;">
								Choose a subcategory to automatically pre-load a template and other data:<br/>
								<select name="category[subcategory]">
									<option value="">General</option>
									<?
									foreach($pgsubcategories as $catname => $catname_pl){
										$catvalue = $catname;
										$catname = ucwords($catname);
										if($catname=="Game Developer") $catname = "Developer (company or group)";
										elseif($catname=="Game Concept") {}
										elseif($catname=="Game Location") {}
										else $catname = str_replace("Game ", "", $catname);
										echo '<option value="'.$catvalue.'">'.$catname.'</option>';
									}
									?>
								</select>
							</div>
						</li>
						<li><label style="color:black;"><input type="radio" name="pgtype" value="topic"/>A Topic</label> like <a href="/topics/Censorship">censorship</a> or <a href="/topics/Cosplay">cosplay</a> that is none of the above <a href="#help" class="tooltip helpinfo" title="Topics are generally defined as 'other'. They're also stuff we discuss that aren't directly related to videogames."><span>?</span></a></li>
						<li><label style="color:black;"><input type="radio" name="pgtype" value="template"/>A Template</label> page created to be included in other pages <a href="#help" class="tooltip helpinfo" title="Templates usually contain repetitive material that might need to show up on any number of articles or pages."><span>?</span></a></li>
						<li id="pgtype-redirect" style="display:none"><label style="color:black;"><input type="radio" name="pgtype" value="category"/>Redirect page</label></li>
					</ul>
				</div>
				
				<p></p>
				
				<input type="submit" value="Continue" disabled="disabled" id="submitstart" style="font:bold 14px Arial; padding:3px 12px;"/>&nbsp;&nbsp;
				<span class="arrow-right" style="color:#555;">to the next step</span>
				
			</form>
		</div>
		<?
		
		$page->footer();
		exit;
	
	} else {
		
		// Fresh edit of an established page
		// Use current version as the basis
		
		try{ $ed->loadData(); } // populates $ed->data
		catch(Exception $e){ $page->die_('There was an error loading data from the current version of this page: <code>' . $e->getMessage() . '</code>'); }
		
	}
	
	// Save a draft to build from
	try{ $ed->save("draft"); }
	catch(Exception $e){
	  $errors[] = 'There was an error starting a new edit session: <code>'.$e->getMessage().'</code>';
	  $page->footer();
	  exit;
	}
		
	
	// EDIT A PAGE //
	
	// secondary fields
	// not shown initially
	$secf = array();
	if($ed->type == "game") $secf = array("online" => "Online", "official_description" => "Official description", "tagline" => "Tagline");
	
	if(!$_COOKIE['page_editor_message']){
		?>
		<div id="pgedpopmsg" class="popmsg" style="z-index:101; bottom:92px; width:420px; margin:0 0 0 -500px; padding:25px 25px 25px 100px; font-size:110%; color:white; line-height:1.5em; background:black url(/bin/img/icons/sprites/digdug_huge.png) no-repeat -32px 10px; background-color:RGBA(0,0,0,.97); opacity:0;">
			<b style="font-size:22px;">Welcome to the Videgam.in Page Editor.</b>
			<p></p>
			Take a glance at the <a href="/s1823" target="_blank">Page Editing Guide & F.A.Q.</a> before editing your first page. You'll also find the <a href="/formatting-help" target="_blank">Formatting cheat sheet</a> helpful for the special formatting used on this site.
			<p></p>
			As you're making changes to this page, this panel will keep track of your changes and allow you to preview your work by switching between the editor and <b>preview mode</b> -- just flip the switch.<br/><b>Try it out now!</b>
			<div style="position:absolute; bottom:-15px; left:56px; width:40px; height:15px; background:url('/bin/img/speech_point_black.png') no-repeat center bottom; opacity:.97;"></div>
		</div>
		<?
	}
	
	?>
	
	<div style="float:right; margin:-5px 0 0; padding:5px 0 5px 40px; font-size:14px; color:#666; background:url('/bin/img/icons/key_tab.png') no-repeat 3px center;">
		Traverse fields
	</div>
	
	<div style="float:left; margin:0; font-size:14px; color:#666;">
		<a href="#help" class="tooltip helpinfo" title="Read both before editing your first page!"><span>?</span></a> 
		Help, tips, and tools: <b><a href="/sblog/1823/page-editing-guide" target="_blank">Page Editing Guide & F.A.Q.</a></b> | <b><a href="/formatting-help" target="_blank">Formatting Guide</a></b>
	</div>
	
	<div style="clear:both; height:20px;"></div>
	
	<?=($ed->redirect_to ? '<div id="redirectnotice" class="warn-box"><span class="warn">This page is being redirected to <b>'.bb2html('[['.$ed->redirect_to.']]').'</b>.</span></div>' : '')?>
	
	<div id="pged">
		
		<div id="fields">
			
			<? if($ed->type != "template"){ ?>
			
			<!--APIs-->
			<dl id="pged-apis" name="apis">
				<dt><a href="#apis" tabindex="1">APIs</a></dt>
				<dd class="view"><?=$ed->fieldView("apis")?></dd>
				<dd class="help">External resources with which to link this <?=$ed->type?>. These can help fill out the content below, or link it to special resources or tools.</dd>
				<dd class="input">
					<?=$ed->fieldInput("apis")?>
				</dd>
			</dl>
			
			<!--keywords-->
			<dl id="pged-keywords" name="keywords">
				<dt><a href="#keywords"><?=($ed->type == "person" ? "Names & Aliases" : "Keywords")?></a></dt>
				<dd class="view"><?=$ed->fieldView("keywords")?></dd>
				<?
				$rules['kw']['game'] = '
					<dd class="help">
						Include keywords, alternate spellings, acronyms and unofficial titles. For example:
						<ul>
							<li><code>SoulCalibur II, SoulCalibur 2, Soul Calibur II, Soul Calibur 2</code></li>
							<li><code>Br&uuml;tal Legend, Brutal Legend</code></li>
							<li><code>Final Fantasy XIV, Final Fantasy 14, FFXIV, FF14</code></li>
						</ul>
					</dd>
				';
				$rules['kw']['category'] = '
					<dd class="help">
						Include keywords, alternate spellings, acronyms, and unofficial titles.  For example:
						<ul>
							<li><code>PlayStation, Sony Playstation, PS, PSOne, PS One, PS1</code></li>
							<li><code>RPG genre, Role-playing, Role playing, RPG game, Role-playing game</code></li>
							<li><code>Resident Evil series, Biohazard series</code></li>
						</ul>
					</dd>
				';
				$rules['kw']['person'] = '<dd class="help">Input alternate names, aliases, etc.</dd>';
				echo $rules['kw'][$ed->type];
				?>
				<dd class="input">
					<?=$ed->fieldInput("keywords")?>
				</dd>
			</dl>
			
			<? } ?>
			<?
			if($ed->type == "game"){
				
				?>
				<!--genres-->
				<dl id="pged-genres" name="genres">
					<dt><a href="#genres">Genres</a></dt>
					<dd class="view"><?=$ed->fieldView("genres")?></dd>
					<dd class="tool acload">
						<form action="" onsubmit="return insToolItem('genres', $('#findgen').val(), 'Category:');" class="fftt">
							<input type="text" name="genres" value="" id="findgen" class="ff focusonme autocomplete-var" data-var="genres" data-namespace="Category:"/>
							<label for="findgen" class="tt" style="margin:1px 0 0 3px; font-size:13px;">Start typing to find a genre</label>
							<button type="submit">Add</button>
							<div class="submittool faux-button blue">+</div>
							<div class="acresults"></div>
						</form>
					</dd>
					<dd class="input"><?=$ed->fieldInput("genres")?></dd>
				</dl>
				
				<!--developers-->
				<dl id="pged-developers" name="developers">
					<dt><a href="#developers">Developers</a></dt>
					<dd class="view"><?=$ed->fieldView("developers")?></dd>
					<dd class="help">
						<ul>
							<li>The first developer listed below will be the primary developer (used in indexes and other references)</li>
						</ul>
					</dd>
					<dd class="tool acload">
						<form action="" onsubmit="return insToolItem('developers', $('#finddev').val(), 'Category:');" class="fftt">
							<input type="text" name="developers" value="" id="finddev" class="ff focusonme autocomplete-var" data-var="developers" data-namespace="Category:"/>
							<label for="finddev" class="tt" style="margin:1px 0 0 3px; font-size:13px;">Start typing to find a developer</label>
							<button type="submit">Add</button>
							<div class="submittool faux-button blue">+</div>
							<div class="acresults"></div>
						</form>
					</dd>
					<dd class="input">
						<?=$ed->fieldInput("developers")?>
					</dd>
				</dl>
				
				<!--series-->
				<dl id="pged-series" name="series">
					<dt><a href="#series">Series</a></dt>
					<dd class="view"><?=$ed->fieldView("series")?></dd>
					<dd class="tool acload">
						<form action="" onsubmit="return insToolItem('series', $('#findser').val(), 'Category:');" class="fftt">
							<input type="text" name="series" value="" id="findser" class="ff focusonme autocomplete-var" data-var="series" data-namespace="Category:"/> 
							<label for="findser" class="tt" style="margin:1px 0 0 3px; font-size:13px;">Start typing to find a series</label>
							<button type="submit">Add</button>
							<div class="submittool faux-button blue">+</div>
							<div class="acresults"></div>
						</form>
					</dd>
					<dd class="input">
						<?=$ed->fieldInput("series")?>
					</dd>
				</dl>
				
				<!--publications-->
				<?
				$pubs = ($ed->data->publications ? $ed->data->publications->children() : array());
				$num_pubs = count($pubs);
				?>
				<dl id="pged-publications" name="publications">
					<dt><a href="#publications">Publications</a></dt>
					<dd class="view"><?=$ed->fieldView("publications")?></dd>
					<dd class="help">
						<ul>
							<li>See <a href="#boxArtStandards" class="arrow-link preventdefault" onclick="pub.uplmessagepop()">our standards</a> for uploading box art</li>
							<li>Check <b>GameFAQs</b> for the best quality box art <a href="http://www.gamefaqs.com/search/index.html?game=<?=urlencode($ed->title)?>" target="_blank" class="arrow-link">Gamefaqs.com/search/?game=<?=urlencode($ed->title)?></a></li>
						</ul>
					</dd>
					<dd class="input">
						<?=$ed->fieldInput("publications")?>
					</dd>
				</dl>
				<div id="addPubForm" style="display:none">
					<?=$ed->fieldInput("publication", '')?>
				</div>
				<div id="boxartstandardsmsg" class="bodyoverlay <?=(!$_COOKIE['boxartstandardsmsg'] ? "prompt" : '')?>" style="">
					<div class="container" style="width:800px; margin:20px auto; padding:20px 30px; background-color:white; max-height:90%; overflow:auto;">
						<a href="#close" class="ximg" style="position:relative; float:right;" onclick="$('#boxartstandardsmsg').removeClass('prompt').fadeOut(); $.cookie('boxartstandardsmsg', '1', {expires:30, path:'/'});">x</a>
						<div style="padding:0 0 0 50px; background:url(/bin/img/navi.png) no-repeat 0 10px;">
							<b>Hey, Listen!</b> We recommend GameFAQS as the best source for quality box scans.<br/>
							<a href="http://www.gamefaqs.com/search/index.html?game=<?=urlencode($ed->title)?>" target="_blank" class="arrow-link" style="font-size:17px">Gamefaqs.com/search/?game=<?=urlencode($ed->title)?></a>
						</div>
						<p></p>
						<h5>Please make sure your box art is:</h5>
						<ul style="line-height:20px; list-style:none;">
							<li style="background:url(/bin/img/check_15.png) no-repeat 0 2px; padding-left:20px;">JPG, GIF, or PNG format</li>
							<li style="background:url(/bin/img/check_15.png) no-repeat 0 2px; padding-left:20px;">At least 200 pixels in width</li>
							<li style="background:url(/bin/img/check_15.png) no-repeat 0 2px; padding-left:20px;">An unblurred, clear, quality image without watermarks or site logos</li>
							<li style="background:url(/bin/img/check_15.png) no-repeat 0 1px; padding-left:20px;">A flat image that is not scaled, rotated, or has any borders or whitespace around the perimeter (tip: use <a href="http://www.google.com/search?q=online+image+editor" target="_blank" class="arrow-link">an online image editor</a> like <a href="http://www.pixlr.com" target="_blank" class="arrow-link">Pixlr</a> to quickly and easily crop any whitespace or borders from an image)</li>
						</ul>
						<br/>
						<table border="0" cellpadding="10" cellspacing="1" width="100%">
							<tr>
								<td colspan="3" style="font-size:14px; background-color:#F5F5F5; border:1px solid #DDD;"><b>Bad box art!</b> The following images don't meet our standards for quality box art:</td>
							</tr>
							<tr>
								<td style="background-color:black;" width="33%" align="center"><img src="/bin/img/promo/bad_boxes/3d.jpg"/></td>
								<td style="background-color:black;" width="33%"><img src="/bin/img/promo/bad_boxes/watermark.jpg"/></td>
								<td style="background-color:black;" width="33%"><img src="/bin/img/promo/bad_boxes/whitespace.jpg"/></td>
							</tr>
							<tr>
								<td style="text-align:center;">Side perspective</td>
								<td style="text-align:center;">Watermark from a<br/>douchebag site</td>
								<td style="text-align:center;">Whitespace around permiter<br/>(even as little as a few pixels of whitespace will conflict with how we use and display box images)</td>
							</tr>
						</table>
					</div>
				</div>
				
				<!--online-->
				<?
				$hidden = !$ed->data->online || !$ed->data->online->count() ? true : false;
				if(!$hidden) unset($secf['online']);
				?>
				<dl id="pged-online" name="online" class="secondaryfield<?=($hidden ? ' hide' : '')?>">
					<dt><a href="#online">Online</a></dt>
					<dd class="view"><?=$ed->fieldView("online")?></dd>
					<dd class="help">
						<ul>
							<li>Mark any applicable <a href="/category/Online_gaming_network" target="_blank" class="arrow-link">online gaming networks</a> that this game can be played on.</li>
							<li>Only mark those networks that include online multiplayer or interactive features like <a href="/category/Achievements" target="_blank" class="arrow-link">achievements</a>.</li>
							<li><a onclick="$(this).hide().next().show()">How to add items or update this list</a><span style="display:none">The default list below is generated automagically from those pages categorized as <code>Online gaming network</code>. To add a network to this list, it must first have a coverage page. After <a href="/content/Special:new">adding a new page</a>, add the category <code>Online gaming network</code> and it should apprear in the blow list thereafter.</span></li>
						</ul>
					</dd>
					<dd class="input">
						<?=$ed->fieldInput("online")?>
					</dd>
				</dl>
				
				<!--official description-->
				<?
				$hidden = !(string)$ed->data->official_description ? true : false;
				if(!$hidden) unset($secf['official_description']);
				?>
				<dl id="pged-official_description" name="official_description" class="secondaryfield<?=($hidden ? ' hide' : '')?>">
					<dt><a href="#official_description">Official Desr.</a></dt>
					<dd class="view"><?=$ed->fieldView("official_description")?></dd>
					<dd class="input"><?=$ed->fieldInput("official_description")?></dd>
				</dl>
				
				<!--tagline-->
				<?
				$hidden = !(string)$ed->data->tagline ? true : false;
				if(!$hidden) unset($secf['tagline']);
				?>
				<dl id="pged-tagline" name="tagline" class="secondaryfield<?=($hidden ? ' hide' : '')?>">
					<dt><a href="#tagline">Tagline</a></dt>
					<dd class="view"><?=$ed->fieldView("tagline")?></dd>
					<dd class="input"><?=$ed->fieldInput("tagline")?></dd>
				</dl>
				
				<!--credits-->
				<dl id="pged-credits" name="credits">
					<dt><a href="#credits">Credits</a></dt>
					<dd class="view"><?=$ed->fieldView("credits")?></dd>
					<dd class="help">
						<ul>
							<li>The best way to input credits is with a <b>definition list</b> (ie <code>; ROLE :: NAME :: NAME</code>) with one role-name group per line.</li>
							<li class="toggle" style="display:none">On most occasions you will want to utilize <code>[[page links]]</code> to link a name with that person's content page. <b>Only by using links can the game and people pages connect and share this information.</b></li>
							<li class="toggle" style="display:none">If a person was credited as something other than their real name (an alias, for example), you can use the following standard page linking format: <code>[[REAL NAME|CREDITED NAME]]</code><br/>&nbsp;&nbsp;&nbsp;&nbsp;For example: <code>[[Kenji Inafune|Inafking]]</code>, <code>[[Hiroshi Yamauchi|Old Man Oochi]]</code></li>
							<li class="toggle" style="display:none">Optionally, separate credits into list groups. You can do this with <code style="border-bottom-style:double">HEADINGS</code> and <code style="border-bottom-style:solid">SUBHEADINGS</code>. This is useful when there are multiple studios that have worked on a single game, if the game is a remake, or if the official credits list itself separates credits into groups.</li>
							<li class="toggle" style="display:none">An example from <i>Metal Gear Solid</i> (partial list):
								<code class="block">Cast<br/>====<br/>; [[Solid Snake]] :: [[David Hayter]]<br/>; [[Liquid Snake]] :: [[Cam Clarke]]<br/>; [[Meryl Silverburgh]] :: Debi Mae West<br/><br/>Staff<br/>=====<br/>; Planning/Original game design :: [[Hideo Kojima]]<br/>; Character/Mechanical design :: [[Yoji Shinkawa]]<br/>; Written by :: [[Hideo Kojima]] :: [[Tomokazu Fukushima]]<br/>; Setting design :: [[Hideo Kojima]] :: [[TomokazuFukushima]]<br/>; Image board artist :: [[Yoji Shinkawa]]</code>
							</li>
							<li><a href="#toggleCreditsInfo" class="preventdefault" onclick="$(this).children().toggle(); $(this).parent().siblings('.toggle').toggle();"><span>More information and examples...</span><span style="display:none">Less information</span></a></li>
						</ul>
						
					</dd>
					<dd class="input">
						<?=$ed->fieldInput("credits")?>
					</dd>
				</dl>
				
				<?
				
			} elseif($ed->type == "person"){
				
				?>
				<!--personal-->
				<dl id="pged-personal" name="personal">
					<dt><a href="#personal">Personal Details</a></dt>
					<dd class="view"><?=$ed->fieldView("personal")?></dd>
					<dd class="input">
						<?=$ed->fieldInput("personal")?>
					</dd>
				</dl>
				
				<!--roles-->
				<dl id="pged-roles" name="roles">
					<dt><a href="#roles">Professions</a></dt>
					<dd class="view"><?=$ed->fieldView("roles")?></dd>
					<dd class="help">
						<ul>
							<li><a href="/categories/Game_development_role" target="_blank" class="arrow-link">Game development roles</a> or competencies</li>
						</ul>
					</dd>
					<dd class="tool">
						<form action="" onsubmit="return insToolItem('roles', $('#findrole').val(), 'Category:');" class="fftt">
							<input type="text" name="roles" value="" id="findrole" class="ff focusonme autocomplete-var" data-var="roles" data-namespace="Category:"/>
							<label for="findrole" class="tt" style="margin:1px 0 0 3px; font-size:13px;">Start typing to find a common role</label>
							<button type="submit">Add</button>
							<div class="submittool faux-button blue">+</div>
							<div class="acresults"></div>
						</form>
					</dd>
					<dd class="input">
						<?=$ed->fieldInput("roles")?>
					</dd>
				</dl>
				
				<!--developers-->
				<dl id="pged-developers" name="developers">
					<dt><a href="#developers">Developers</a></dt>
					<dd class="view"><?=$ed->fieldView("developers")?></dd>
					<dd class="help">
						<ul>
							<li>Add companies and development groups this person has worked for.</li>
						</ul>
					</dd>
					<dd class="tool acload">
						<form action="" onsubmit="return insToolItem('developers', $('#finddev').val(), 'Category:');" class="fftt">
							<input type="text" name="developers" value="" id="finddev" class="ff focusonme autocomplete-var" data-var="developers" data-namespace="Category:"/>
							<label for="finddev" class="tt" style="margin:1px 0 0 3px; font-size:13px;">Start typing to find a developer</label>
							<button type="submit">Add</button>
							<div class="submittool faux-button blue">+</div>
							<div class="acresults"></div>
						</form>
					</dd>
					<dd class="input">
						<?=$ed->fieldInput("developers")?>
					</dd>
				</dl>
				<?
				
			}
			
			if($ed->type != "template"){
			?>
			
			<!-- description -->
			<dl id="pged-description" name="description">
				<dt><a href="#description">Description</a></dt>
				<dd class="view">
					<?=$ed->fieldView("description")?>
				</dd>
				<dd class="help">
					A succinct single sentence with <code>[[page links]]</code> to describe this <?=($ed->type == "other" ? "page" : $ed->type)?>.
				</dd>
				<dd class="help">
					<span class="warn"></span> Descriptions should be as short and as succinct as possible. See <a href="http://videogam.in/forums/?tid=2635">this forum post</a> for more information and tips on shortening your description.
				</dd>
				<?
				$rules['desc']['game'] = '
					<dd class="help">	
						<b>Common format:</b><br/>
						<code>A (GENRE) game for (ORIGINAL PLATFORM) by (PUBLISHER) in the (GAME SERIES) series</code>
					</dd>
					<dd class="help">
						<b>An example description from <i>Super Metroid</i>:</b><br/>
						<code>An [[Action-Adventure]] game for [[Super NES]] by [[Nintendo]] in the [[Metroid series]]</code>
					</dd>
				';
				$rules['desc']['person'] = '
					<dd class="help">
						<b>Common format</b>: <code>A (PROFESSION) at (COMPANY/DEVELOPMENT GROUP)</code><br/>
						<b>For example</b>:
						<ul>
							<li><code>A [[Music Composer]] at [[Nintendo]]</code></li>
						</ul>
					</dd>
				';
				echo $rules['desc'][$ed->type];
				if($ed->type == "game" || $ed->type == "person"){
					?>
					<dd class="help">
						<button type="button" id="desc-autofill">Auto-fill</button>&nbsp;&nbsp; <span class="arrow-left"></span>Click to automatically fetch a description based on the given data
					</dd>
					<?
				}
				?>
				<dd class="input">
					<?=$ed->fieldInput("description")?>
				</dd>
			</dl>
			<? } ?>
			
			<!--page cont-->
			<dl id="pged-content" name="content">
				<dt><a href="#content"><?=($ed->type == "template" ? "Content" : ".incyclopedia")?></a></dt>
				<dd class="view">
					<?=$ed->fieldView("content")?>
				</dd>
				<dd class="help">
					<? if($ed->type != "template"){ ?>
					<ul>
						<li>At least one paragraph that supplies general information, story, synopsis, biography, facts, and trivia about the subject.</li>
						<li>Could also be a publisher's description, storyline, tagline, or official biography, with <b>facts referenced materials <a href="/sblog/1823/page-editing-guide#Citing_Sources" target="_blank">cited</a></b>.</li>
						<li><b><a href="/sblog/1823/page-editing-guide#Page_Content" target="_blank" class="arrow-link">More about this field</a></b></li>
					</ul>
					<? } ?>
				</dd>
				<dd class="input">
					<?=$ed->fieldInput("content")?>
				</dd>
			</dl>
			
			<?
			if($ed->type == "person"){
				
				// credits list
				
				//check for discrepancies
				$cr_d = array();
				$query = "SELECT work FROM credits WHERE person = '".mysqli_real_escape_string($GLOBALS['db']['link'], $ed->title)."' AND source_person = '0'";
				$res   = mysqli_query($GLOBALS['db']['link'], $query);
				while($row = mysqli_fetch_assoc($res)){
					if(substr($row['work'], 0, 8) == "AlbumID:"){
						$albumid = substr($row['work'], 8);
						$q = "SELECT title, subtitle FROM albums WHERE albumid = '".mysqli_real_escape_string($GLOBALS['db']['link'], $albumid)."' LIMIT 1";
						if(!$album = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) continue;
						$row['work'].= "|".$album['title'].($album['subtitle'] ? " ".$album['subtitle'] : '');
					}
					$cr_d[] = $row['work'];
				}
				
				?>
				<!--credits_list-->
				<dl id="pged-credits_list" name="credits_list">
					<dt><a href="#credits_list">Credits</a></dt>
					<dd class="view"><?=$ed->fieldView("credits_list")?></dd>
					<?
					if($cr_d){
						?>
						<dd id="credlistsugg">
							<div class="container">
								<dl>
									<dt>Suggested additions</dt>
									<?
									$o = '';
									foreach($cr_d as $cr) $o.= '<dd><input type="checkbox" name="suggcred" value=""/><label>[['.$cr.']]</label><textarea style="display:none">'.$cr.'</textarea></dd>';
									$pglinks = new pglinks();
									$pglinks->attr['target'] = "_blank";
									$pglinks->attr['class'] = "arrow-link";
									echo $pglinks->parse($o);
									?>
								</dl>
								<div style="padding-left:25px; background:url('/bin/img/arrow-down-right.png') no-repeat 7px 50%;">
									<a href="#addSel" class="preventdefault" onclick="addSuggCreds()">Add Selected</a> | <a href="#addAll" class="preventdefault" onclick="addSuggCreds('all')">Add All</a>
								</div>
							</div>
						</dd>
						<?
					}
					?>
					<dd class="tool">
						<form action="" onsubmit="return insToolItem('credits_list', $('#findcredwork').val(), '', '', 'top');" class="fftt">
							<input type="text" name="credits_list" value="" id="findcredwork" class="ff focusonme autocomplete-var" data-var="games albums" data-inspos="top"/>
							<label for="findcredwork" class="tt" style="margin:1px 0 0 3px; font-size:13px;">Start typing to find a game or sountrack...</label>
							<button type="submit">Add</button>
							<div class="submittool faux-button blue">+</div>
							<div class="acresults"></div>
						</form>
					</dd>
					<dd class="input">
						<?=$ed->fieldInput("credits_list")?>
					</dd>
				</dl>
				<?
				
			}
			
			if($ed->type == "game"){
				// Characters & Locations
				foreach(array("characters", "locations") as $f){
					
					$s = substr($f, 0, -1);
					
					$inputtype = $ed->data->{$f}['inputtype'];
					$has_data = false;
					if($inputtype == "open" && (string)$ed->data->{$f} != "") $has_data = true;
					elseif($inputtype != "open" && $ed->data->{$f}->{$s}[0]) $has_data = true;
					
					?>
					<dl class="charloc" id="pged-<?=$f?>" name="<?=$f?>">
						<dt><a href="#<?=$f?>"><?=ucwords($f)?></a></dt>
						<dd class="view">
							<?=$ed->fieldView($f)?>
						</dd>
						<dd class="help">
							List recurring or important <?=$f?> here. Include both specific <?=$f?> like <?=($f=="characters" ? '<a href="/characters/GLaDOS" target="_blank">GLaDOS</a>' : '<a href="/locations/Aperture_Science" target="_blank">Aperture Science Laboratories</a>')?> and also general/achetypical <?=$f?> like <?=($f=="characters" ? '<a href="/characters/Robot" target="_blank">Robot</a>' : '<a href="/locations/Evil_research_facility" target="_blank">Evil research facility</a>')?>.
						</dd>
						<dd class="help <?=($has_data ? "hidden" : "")?>">
							Input type: 
							<label><input type="radio" name="inputtype[<?=$f?>]" value="list" class="changeinputtype" data-field="<?=$f?>" <?=($inputtype != "open" ? 'checked="checked"' : '')?>/> List</label> &nbsp; 
							<label><input type="radio" name="inputtype[<?=$f?>]" value="open" class="changeinputtype" data-field="<?=$f?>" <?=($inputtype == "open" ? 'checked="checked"' : '')?>> Open Field</label>
						</dd>
						<dd class="tool inputtype list <?=($inputtype != "open" ? "" : "hidden")?>">
							<form action="" onsubmit="return insToolItem('<?=$f?>', $('#find<?=$f?>').val(), 'Category:');" class="fftt">
								<input type="text" name="<?=$f?>" value="" id="find<?=$f?>" class="ff focusonme autocomplete-var" data-var="<?=$f?>" data-namespace="Category:"/>
								<label for="find<?=$f?>" class="tt" style="margin:1px 0 0 3px; font-size:13px;">Start typing to find <?=$f?>...</label>
								<button type="submit">Add</button>
								<div class="submittool faux-button blue">+</div>
								<div class="acresults"></div>
							</form>
						</dd>
						<dd class="help inputtype open <?=($inputtype != "open" ? "hidden" : "")?>">
							The formatting of this field is completely open, but we recommend you input the <?=$f?> in a list. For example:<br/>
							<code class="block"><?=($f == "characters" ? '* [[Category:Parappa]]<br/>* [[Category:Chop Chop Master Onion]]<br/>* [[Category:PJ Berry]]' : '* [[Category:Zebes]]<br/>* [[Category:Brinstar]]<br/>* [[Category:Outer space]]')?></code>
							<p></p>
							<a href="#more" class="arrow-toggle preventdefault" onclick="$(this).toggleClass('arrow-toggle-on').next().toggle()">More about this field</a>
							<div style="display:none; border-bottom:1px solid #CCC;">
								<?=($f == "characters" ? '<p>Character pages are category pages. As long as you <b>manually categorize</b> all the links to character pages here, this game will be automatically listed on that character\'s page. Categorize links by adding the <code>Category:</code> namespace to them (ie <code>[[Category:Mario]]</code>).</p>
								<p>You can write up a short article about the characters in this field, or merely list them. Feel free to include <code style="border-bottom-style:double">Headings</code> and <code style="border-bottom-style:solid">Subheadings</code> too.</p>' : '<p>Location pages are category pages. As long as you <b>manually categorize</b> all the links to location pages here, this game will be automatically listed on that location page. Categorize links by adding the <code>Category:</code> namespace to them (ie <code>[[Category:Hyrule]]</code>).</p>
								<p>You can write up a short article about the locations in this field, or merely list them. Feel free to include <code style="border-bottom-style:double">Headings</code> and <code style="border-bottom-style:single">Subheadings</code> too.</p>')?>
							</div>
						</dd>
						<dd class="input fw">
							<?=$ed->fieldInput($f)?>
					  </dd>
					</dl>
					<?
				}
			}
			
			?>
			
			<!--categories-->
			<dl id="pged-categories" name="categories" style="<?=($ed->type == "game" ? 'min-height:45px;' : '')?>">
				<dt><a href="#categories">Categories<?=($ed->type == "game" ? ' & Concepts' : '')?></a></dt>
				<dd class="view">
					<?=$ed->fieldView("categories")?>
				</dd>
				<dd class="help">
					Add categories to relate this page with others. <a href="/sblog/1823/page-editing-guide#Categories" target="_blank" class="arrow-link">More about categories</a>
				</dd>
				<dd class="tool">
					<form action="" onsubmit="return insToolItem('categories', $('#findctg').val(), 'Category:');" class="fftt">
						<label for="findctg" class="tt" style="margin:1px 0 0 3px; font-size:13px;">Start typing to find a category...</label>
						<input type="text" name="categories" value="" id="findctg" class="ff focusonme autocomplete-var" data-var="categories" data-namespace="Category:"/> 
						<button type="submit">Add</button>
						<div class="submittool faux-button blue">+</div>
						<div class="acresults"></div>
					</form>
				</dd>
				<dd class="input fw">
					<?=$ed->fieldInput("categories")?>
				</dd>
			</dl>
			
			<div id="help-parentcategory" class="alert" style="display:none; top:20%; bottom:20%;">
				<a class="closealert ximg" onclick="$(this).parent().fadeOut()">close</a>
				<dl>
					<dt>Immediate Parent Category</dt>
					<dd style="line-height:1.5em">
						<p>By marking one or more Immediate Parent categories (and in turn marking this page as an Immediate Parent of another), you can create a dynamic content tree of similarly categorized pages that closely relate to this one. For example:</p>
						<ul>
							<li>Mushroom World
								<ul>
									<li>Mushroom Kingdom
										<ul>
											<li>Toad Town
												<ul>
													<li>Princess Peach's Castle</li>
												</ul>
											</li>
											<li>Dry Dry Desert
												<ul>
													<li>Dry Dry Outpost</li>
												</ul>
											</li>
										</ul>
									<li>Dark Land
										<ul>
											<li>Bowser's Castle</li>
										</ul>
									</li>
									<li>Dinosaur Land
										<ul>
											<li>Yoshi's Island</li>
											<li>Donut Plains</li>
											<li>Vanilla Dome</li>
										</ul>
									</li>
									<li>Beanbean Kingdom</li>
									<li>Sarasaland</li>
								</ul>
							</li>
						</ul>&hellip;
						<p>In the above example, the <code>Princess Peach's Castle</code> page has <code>Toad Town</code> as its Immediate Parent. <code>Toad Town</code> in turn has <code>Mushroom Kingdom</code> as its parent, and <code>Mushroom Kingdom</code>, <code>Dark Land</code>, <code>Dinosaur Land</code>, <code>Beanbean Kingdom</code>, and <code>Sarasaraland</code> all have <code>Mushroom World</code> as their parent.</p>
						<p>The same thing can be done with other categories:</p>
						<ul>
							<li>Action
								<ul>
									<li>Action-adventure</li>
									<li>Beat 'em Up</li>
									<li>Hack and Slash</li>
									<li>Shooter
										<ul><li>First-Person Shooter</li><li>Third-Person Shooter</li><li>Bullet Hell</li><li>Light Gun Shooter</li><li>Rail shooter</li><li>Scrolling shooter</li></ul>
									</li>
								</ul>
							</li>
						</ul>
						<br/>
						<ul>
							<li>Mario series
								<ul><li>Super Mario series<ul><li>Super Mario Land series</li><li>Super Mario World series</li></ul></li></ul>
								<ul><li>Mario Kart series</li></ul>
								<ul><li>Mario Sports series</li></ul>
							</li>
						</ul>
								
						<p>Note that an Immediate Parent must be the same Subcategory. Therefore, if the page doesn't exist, it must be created and categorized before it can be marked as a parent.</p>
						<p><b>Don't mark any ancestor categories as immediate parents!</b> Meaning in the above example, the <code>Princess Peach's Castle</code> page <i>only</i> has <code>Toad Town</code> checked as a parent, and <i>not</i> <code>Mushroom Kingdom</code> or <code>Mushroom World</code>.</p>
						<p><a onclick="$('#help-parentcategory').fadeOut()">Close this message</a></p>
					</dd>
				</dl>
			</div>
			
		</div><!--#fields-->
		
		<?
		if(count($secf)){?>
			<!-- secondary fields -->
			<div id="secondaryfields">
				<b>Add more fields:</b>
				<ul>
					<?
					foreach($secf as $s => $s_formatted){
						echo '<li id="secf-'.$s.'" data-field="'.$s.'">'.$s_formatted.'</li>';
					}
					?>
				</ul>
			</div>
			<div style="clear:both"></div>
			<?
		}
		
		if($ed->type != "template"){?>
		<!--img-->
		<div id="pged-img">
			<form id="pgedin-img" class="pgedfield">
				
				<fieldset>
					<legend>Page Images</legend>
					
					<dl>
						<dt>Main Picture</dt>
						<?
						if($rep_img = (string)$ed->data->rep_image){
							if(substr($rep_img, 0, 4) == "img:"){
								$img = new img(substr($rep_img, 4));
								$rep_img_tn = $img->src['sm'];
							} else {
								$pos = strrpos($rep_img, "/");
								$rep_img_tn = substr($rep_img, 0, $pos) . "/" . ($ed->type == "person" ? "profile_" : "md_") . substr($rep_img, ($pos + 1), -3) . "png";
							}
						}
						?>
						<dd class="img">
							<input type="hidden" name="rep_image" value="<?=(string)$ed->data->rep_image?>" id="repimg-filename"/>
							<img src="<?=$rep_img_tn?>" border="0" alt="" id="repimg"/>
							<ul class="container">
								<?=($ed->type == "game" ? '<li><a title="Fetch the Primary publication box art" id="fetch-box">Fetch</a></li>' : '')?>
								<li><a href="#uplImg" title="Upload a new image" onclick="upl.init('repimg', 'rep_image')">Upload</a></li>
								<li class="rm"><a href="#rmImg" rel="repimg" title="Remove the current image">Remove</a></li>
							</ul>
						</dd>
					</dl>
					
					<dl>
						<dt>Heading Image</dt>
						<dd class="img">
							<?
							$hd_img = (string)$ed->data->heading_image ? (string)$ed->data->heading_image : '';
							?>
							<input type="hidden" name="heading_image" value="<?=(string)$ed->data->heading_image?>" id="headimg-filename"/>
							<img src="<?=$hd_img?>" border="0" alt="" width="200" id="headimg"/>
							<ul class="container">
								<li><a href="#uplImg" title="Upload a new image" onclick="upl.init('headimg', 'heading_image')">Upload</a></li>
								<li class="rm"><a href="#rmImg" rel="headimg" title="Remove the current image">Remove</a></li>
							</ul>
						</dd>
					</dl>
					
					<dl>
						<dt>Background Image</dt>
						<dd class="img">
							<?
							$bg_img = (string)$ed->data->background_image ? (string)$ed->data->background_image : '';
							if($bg_img_style_str = (string)$ed->data->background_image['style']){
								$sa = explode(";", $bg_img_style_str);
								foreach($sa as $si){
									list($k, $v) = explode(":", $si);
									if($k == "") continue;
									$bg_img_style[$k] = $v;
								}
							}
							?>
							<input type="hidden" name="background_image" value="<?=(string)$ed->data->background_image?>" id="bgimg-filename"/>
							<img src="<?=$bg_img?>" border="0" alt="" width="200" id="bgimg"/>
							<ul class="container">
								<li><a href="#uplImg" title="Upload a new image" onclick="upl.init('bgimg', 'background_image')">Upload</a></li>
								<li class="rm"><a href="#rmImg" rel="bgimg" title="Remove the current image">Remove</a></li>
								<li class="sett"><a title="Background settings" onclick="$('#bgimg-settings').fadeIn()">Settings</a></li>
							</ul>
						</dd>
					</dl>
					
					<div id="headimg-msg" class="popmsg pged-img-msg">
						<div class="container">
							<ul>
								<li>An image that's at least 945 pixels in width</li>
								<li>A JPG, PNG, or GIF image</li>
							</ul>
						</div>
					</div>
					<div id="bgimg-msg" class="popmsg pged-img-msg">
						<div class="container">
							<span class="warn"></span>Your image must must flush with the background. Therefore, <b>image manipulation (i.e. Photoshop) is required</b>. A PNG-24 with transparency is recommended.<br/>
						</div>
					</div>
					<div id="bgimg-settings" class="popmsg white" style="display:none; top:20%; width:280px; margin-left:-140px;">
						<div class="container" style="padding:40px">
							<select name="imgattr[background_image][style][]">
								<option value="background-position:right top;" <?=($bg_img_style['background-position'] == "right top" ? 'selected' : '')?>>Right</option>
								<option value="background-position:center top;" <?=($bg_img_style['background-position'] == "left top" ? 'selected' : '')?>>Center</option>
								<option value="background-position:left top;" <?=($bg_img_style['background-position'] == "left top" ? 'selected' : '')?>>Left</option>
							</select>
							<div class="hr"></div>
							<select name="imgattr[background_image][style][]">
								<option value="background-repeat:no-repeat;" <?=($bg_img_style['background-repeat'] == "no-repeat" ? 'selected' : '')?>>No repeat</option>
								<option value="background-repeat:repeat;" <?=($bg_img_style['background-repeat'] == "repeat" ? 'selected' : '')?>>Repeat</option>
								<option value="background-repeat:repeat-x;" <?=($bg_img_style['background-repeat'] == "repeat-x" ? 'selected' : '')?>>Repeat horizontal</option>
								<option value="background-repeat:repeat-y;" <?=($bg_img_style['background-repeat'] == "repeat-y" ? 'selected' : '')?>>Repeat vertical</option>
							</select>
							<div class="hr"></div>
							<button onclick="upl.saveSettings(); $('#bgimg-settings').fadeOut(); return false;" style="font-weight:bold">Save settings</button>&nbsp;
							<button onclick="$('#bgimg-settings').fadeOut(); return false;">Cancel</button>
						</div>
					</div>
					
					<div style="clear:both;"></div>
					
				</fieldset>
				
				<div style="clear:left;"></div>
				
				<?
				if($ed->type == "game"){
					?>
					
					<!-- GAME IMAGES -->
					<fieldset id="gameimgs">
						<legend>Game Images</legend>
						
						<?
						$gm_imgs = array(
							"Title Screen" => array("fieldname" => "titlescreen", "img_category_id" => 11),
							"Gameplay 1" => array("fieldname" => "gameplay_1", "img_category_id" => 1),
							"Gameplay 2" => array("fieldname" => "gameplay_2", "img_category_id" => 1),
							"Gameplay 3" => array("fieldname" => "gameplay_3", "img_category_id" => 1),
							"Game Over" => array("fieldname" => "gameover", "img_category_id" => 14)
						);
						
						foreach($gm_imgs as $name => $var){
							$obj = "img_".$var['fieldname'];
							unset($img);
							if((string)$ed->data->{$obj}) $img = new img((string)$ed->data->{$obj});
							?>
							<dl>
								<dt><?=$name?></dt>
								<dd class="img">
									<input type="hidden" name="<?=$obj?>" value="<?=(string)$ed->data->{$obj}?>" class="gameimg-inp" id="img-<?=$var['fieldname']?>-filename"/>
									<img src="<?=($img ? $img->src['tn'] : '')?>" border="0" alt="" id="img-<?=$var['fieldname']?>"/>
									<ul class="container">
										<li class="off"><a onclick="img.init({fieldId:'img-<?=$var['fieldname']?>-filename', fieldSrc:$('#img-<?=$var['fieldname']?>'), action:'upload', 'onSelect':selectGameImg, uploadVars:{img_category_id:<?=$var['img_category_id']?>, img_tag:$('#pgtitle').val(), overwrite:'true', handler:'<?=base64_encode("sessid=".$GLOBALS['imgs']->sessid)?>'}, nav:'upload'})">Upload</a></li>
										<li class="on rm"><a href="#rmImg" rel="" title="Remove the current image">Remove</a></li>
									</ul>
								</dd>
							</dl>
							<?
						}
						?>
						
						<br style="clear:left;"/>
						<div id="fetch-gameimgs" style="display:none; margin:15px 0 0; text-align:center;">
							<button type="button" id="fetch-gameimgs" style="font-weight:bold; padding-left:20px; padding-right:20px;">Fetch</button> 
							&nbsp;<span class="arrow-left"></span> Try and fetch images already uploaded, classified, and tagged.<sup class="a tooltip" title="Existing images that are uploaded to Videogam.in, tagged with <cite><?=$title?></cite>, and classified appropriately could be fetched automatically.">?</sup>
						</div>
						
					</fieldset>
					
					<!-- VIDEO/TRAILER -->
					<fieldset id="gamevideo">
						<legend>Video</legend>
						
						<dl id="gametrailer">
							<dt>Trailer</dt>
							<dd class="img">
								<input type="hidden" name="video_trailer" value="<?=(string)$ed->data->video_trailer?>" id="trailerurl-inp"/>
								<img src="" border="0" alt=""/>
								<ul class="container">
									<li class="off"><a onclick="promtTrailer()">Add</a></li>
									<li class="on preview"><a href="<?=(string)$ed->data->video_trailer?>" target="_blank" id="trailerurl-link">Preview</a></li>
									<li class="on rm"><a title="Remove the current trainer" onclick="$('#trailerurl-inp').val('').siblings('img').attr('src', ''); changes.save('img');">Remove</a></li>
								</ul>
							</dd>
						</dl>
						<?=((string)$ed->data->video_trailer ? '<script>fetchYoutubeVideo("trailerurl");</script>' : '')?>
						
					</fieldset>
					
					<?
					
				}
				?>
				
				<div style="clear:left;"></div>
				
			</form><!--#pgedin-img-->'
		</div><!--#pged-img-->
		
		<? } ?>
		
		<?
		if($usrrank >= 8){
			?>
			<fieldset class="off" style="margin:20px 0 0;">
			<legend><a href="#data" class="arrow-toggle" onclick="$(this).toggleClass('arrow-toggle-on').closest('fieldset').toggleClass('off').children('.cont').toggle();">Raw data</a></legend>
				<pre class="cont" style="display:none; white-space:pre-wrap;"><? print_r($ed); ?></pre>
			</fieldset>
			<?
		}
		?>
		
		<input type="hidden" name="pgtitle" value="<?=htmlSC($ed->title)?>" id="pgtitle"/>
		<input type="hidden" name="pgtype" value="<?=htmlSC($ed->type)?>" id="pgtype"/>
		<input type="hidden" name="sessid" value="<?=htmlSC($ed->sessid)?>" id="sessid"/>
		
		<div id="pged-upl" class="popmsg" style="z-index:9;">
			<div class="loading"></div>
			<a href="#close" class="ximg preventdefault" style="top:10px; right:10px;" onclick="upl.close()">x</a>
			<div class="container">
				<input type="hidden" id="uplqstr" name="uplqstr" value="upload_handle.php?component=form&imgtype=_IMGTYPE_&fdir=<?=$titleurl?>&retelid=_RETELID_&sessid=<?=$img_sessid?>"/>
				<iframe src="" frameborder="0" id="upliframe" style=""></iframe>
			</div>
		</div>
		
	</div><!--#pged-->
	
	<?
	//if((string)$ed->row->background_image) echo '<div id="bodybgimg" class="pgsection" style="left:auto; background-image:url(\''.(string)$ed->row->background_image.'\'); '.(string)$ed->row->background_image['style'].'"></div>';
	
	$ed->footer("incl_console");
	
} catch(Exception $e){
  echo 'Error :'.$e->getMessage();
  $page->footer();
  exit;
}
