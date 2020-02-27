<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
$page = new page;
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.pages.php");
$_pg = new pages;
require_once ($_SERVER["DOCUMENT_ROOT"]."/pages/edit_ajax.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/htmltoolbox.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");
//require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/xml_handler.php");
//$xmlh = new XMLh;

if($sessid = $_GET['destroysession']){
	
	//DESTROY AND EDIT SESSION
	
	$q = "SELECT * FROM pages_edit WHERE session_id='".mysql_real_escape_string($sessid)."' LIMIT 1";
	if(!$pe = mysql_fetch_object(mysql_query($q))) {
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
		<h1>Destroy a Revision</h1>
		<fieldset>
			<legend>Session details</legend>
			<ul>
				<li><i><?=$pe->title?></i></li>
				<li>ID# <a href="/pages/history.php?view_version=<?=$sessid?>"><?=$sessid?></a></li>
				<li><?=outputUser($pe->usrid)?></li>
				<li><?=$pe->datetime?></li>
			</ul>
		</fieldset>
		<br/>
		<br/>
		Delete this revision?
		<ul style="line-height:2em;">
			<li><a href="<?=pageURL($pe->title)?>">Nevermind, don't delete it</a></li>
			<li><big style="font-size:18px;"><a href="edit.php?destroysession=<?=$sessid?>&sure=yes">Yes, permanently <b style="color:#DF3737">delete</b> this edit session</a></big></li>
		</ul>
		<?
		$page->footer();
		exit;
	}
	
	$q = "DELETE FROM pages_edit WHERE session_id='".mysql_real_escape_string($sessid)."' LIMIT 1";
	if(!mysql_query($q)) die("Error removing session from database");
	
	recalculatePageContr($pe->title);
	
	header("Location: ".pageURL($pe->title));
	die("Success!");
	
}

/*$access = (!in_array($usrname, $betatesters) ? FALSE : TRUE);
if(!$access && $_SERVER['HTTP_HOST'] != "localhost") {
	include("../404.php");
	exit;
}*/

$dbdat = ""; //obj data from db row
$row   = ""; //arr deconstructed data from xml file
$in    = ""; //arr received form data (initally cloned from $row)

$pgid = trim($_GET['pgid']);
$title = formatName($_GET['title']);
if(!$title && !$pgid) die('No page id or title given. <a href="/pages/">back</a>');

$sessid = $_POST['sessid'] ? $_POST['sessid'] : date("YmdHis").sprintf("%07d", $usrid);

$q = "SELECT * FROM pages WHERE ".($pgid ? "pgid='".mysql_real_escape_string($pgid)."'" : "`title`='".mysql_real_escape_string($title)."'")." LIMIT 1";
$dbdat = mysql_fetch_object(mysql_query($q));
$pgid = $dbdat->pgid;

if($_GET['editsource'] || $_POST['editsource']){
	$source = ($_POST['editsource'] ? $_POST['editsource'] : $_GET['editsource']);
	if(!$row = $_pg->deconstruct("xml/drafts/".$source.".xml")){
		$warnings[] = "Couldn't get sourcefile data for the requested edit session";
		unset($source);
	} elseif(!$_POST) $warnings[] = "You are using an old source file as the basis of your edits. By submitting this form, the current source file will be replaced (though will still be accessible from the history archives) by your submission.";
} 
if(!$row && $pgid) {
	$row = $_pg->deconstruct("xml/".$dbdat->pgid.".xml") OR $warnings[] = "Couldn't get sourcefile data";
}

if(!$pgid)  $pgid = $dbdat->pgid;
if(!$title) $title = $dbdat->title;
$title_url = formatNameURL($title);
$filedir   = "/pages/files/".preg_replace("/[^a-z0-9_-]/i", "", $title_url)."/";
$pgtype    = ($row['pgtype'] ? $row['pgtype'] : $_POST['in']['pgtype']);

if($dbdat) $_pg->data = mysql_fetch_assoc(mysql_query($q));
else $_pg->data = array("title" => $title, "type" => $pgtype);

$in = $_POST['in'];
if($in) require("edit_process.php");
else $in = $row;

if(!$in) $in['title'] = $title;

$page->title = "Videogam.in / Edit ".htmlSC($title);
$page->javascripts[] = "/bin/script/jquery-ui-1.js";
$page->javascripts[] = "/pages/edit.js";
$page->css[] = "/pages/pages_subsid.css";
$page->width = "fixed";

$page->header();

$_pg->editHeader();

if(!$usrid) $page->die_('<big style="font-size:150%;">Please <a href="/login.php">Log In</a> in order to edit this page.</big>');

//check for previous sessions
	$query = "SELECT * FROM pages_edit WHERE title='".mysql_real_escape_string($title)."' AND usrid='$usrid' AND published='0' AND session_id != '$sessid' ".($dbdat ? "AND `datetime` > DATE_ADD('".$dbdat->modified."', INTERVAL -1 DAY)" : "")." ORDER BY `datetime` DESC";
	$res   = mysql_query($query);
	if(mysql_num_rows($res)){
		echo '<div style="margin:0 0 5px; padding:7px 10px; background-color:#FFB;"><b>You have unpublished sessions for this page title.</b> You can build upon your previous session or continue with the form below to start from scratch.<ul>';
		while($row = mysql_fetch_assoc($res)) {
			echo '<li><a href="history.php?view_version='.$row['session_id'].'">'.formatDate($row['datetime'], 2).'</a> <span style="color:#AAA;">[<a href="edit.php?title='.$title_url.'&editsource='.$row['session_id'].'">build</a>]</span> '.$row['edit_summary'].'</li>';
		}
		echo '</ul></div>';
	}

if(!$pgtype) {
	
	// START A PAGE //
	
	//check the title for matches
	$query = "SELECT `title`, MATCH (`title`, `keywords`) AGAINST ('".mysql_real_escape_string($title)."') AS `score` FROM pages WHERE redirect_to='' AND MATCH (`title`, `keywords`) AGAINST ('".mysql_real_escape_string($title)."') ORDER BY `score` DESC LIMIT 20";
	$res   = mysql_query($query);
	$matches = array();
	while($row = mysql_fetch_assoc($res)) {
		if($row['score'] > 3) $matches[] = $row['title'];
	}
	
	?>
	<form action="edit.php?title=<?=$title_url?>" method="post" id="startpgform" style="font-size:14px; line-height:1.5em;">
		
		<input type="hidden" name="_action" value="start"/>
		<input type="hidden" name="watch[watch]" value="1"/>
		<textarea name="title" style="display:none;"><?=$title?></textarea>
		
		<p><b>You're starting the page for <i><?=$title?></i>.</b> Before starting your first page, please read the <a href="/posts/2010/04/16/page-editing-guide" target="_blank" class="arrow-link">Page Creating Guide & F.A.Q.</a> and <a href="/bbcode.htm" target="_blank" class="arrow-link">BB Code Guide</a>.</p>
		
		<div style="display:none">Unless this page will be a <a href="faq.php#redirect">redirect</a>, you will be required to:
		<ol style="margin:0 0 15px;">
			<li>Write a one-sentence, sussinct <b>description</b> of this subject.</li>
			<li>Write an <b>article</b> that includes at least one paragraph that supplies general information, story/synopsis/biography, facts, and trivia about the subject. The article could also be a publisher's official description, storyline, or tagline, but <b>all referenced materials must be <a href="/posts/2010/04/16/page-editing-guide#Citing_Sources">cited properly</a>.</b></li>
		</ol>
		</div>
		
		<?
		if($matches) {
			$o_matches = '<ul><li>[['.implode(']]</li><li>[[', $matches).']]</li></ul>';
			?>
			<div style="padding:7px 10px; background-color:#FFB;">
				<b class="warn" style="font-size:16px;">Possible matches already exist</b>
				<?=bb2html($o_matches, "pages_only")?>
			</div>
			<p></p>
			<?
		}
		?>
		
		<fieldset style="line-height:1.5em; font-size:17px;">
			<legend>Select a Page Type</legend>
			<ul style="list-style:none; margin:0; padding:0; color:#666;">
				<li><label style="color:black;"><input type="radio" name="in[pgtype]" value="game"/>Game</label> &ndash; A specific game with a unique development team</li>
				<li><label style="color:black;"><input type="radio" name="in[pgtype]" value="person"/>Person</label> &ndash; A person involved in the creation of videogames <a href="#help" class="tooltip helpinfo" title="If the person isn't a game developer (for example: Michael Atkinson, Jack Thompson, Hulk Hogan), make it a TOPIC instead"><span>?</span></a></li>
				<li><label style="color:black;"><input type="radio" name="in[pgtype]" value="category"/>Category</label> &ndash; A console, company, series, genre, character, concept; anything that can list games or people <a href="#help" class="tooltip helpinfo" title="For example: Square Enix, Mario series, Mustaches, Fangames, Indie games, Games made into movies"><span>?</span></a> <a href="/posts/2010/04/16/page-editing-guide#Categories" target="_blank" class="arrow-link">more</a></li>
				<li><label style="color:black;"><input type="radio" name="in[pgtype]" value="topic"/>Topic</label> &ndash; A news or discussion topic that is none of the above <a href="#help" class="tooltip helpinfo" title="For example: Videogame violence, Hulk Hogan, Amalgamerotic lesbian action"><span>?</span></a></li>
			</ul>
		</fieldset>
		
		<p></p>
		
		<div id="start-sw" style="padding:10px; background-color:#EEE; font-size:14px;">
			<div id="sw-game" class="sw" style="display:none">
				As a rule of thumb, the game title should be the one listed at <a href="http://en.wikipedia.org/wiki/Lists_of_video_games" target="_blank" title="Wikipedia list of videogames" class="arrow-link">Wikipedia</a>.
			</div>
			<div id="sw-person" class="sw" style="display:none">
				As a rule of thumb, the page title should be the person's commonly credited English name. For example: <code>Alexander O. Smith</code>.<p></p>
			</div>
			<div id="sw-category" class="sw" style="display:none">
				Examples of category names:
				<ul>
					<li><code>Square Enix</code> -- A category that could include company information but also could easily list games and people (much more fitting than "Square Enix games" or "Square Enix developers").</li>
					<li><code>Final Fantasy series</code> -- A page that could easily (and automatically!) list all games and notable personnel.</li>
					<li><code>Mustaches</code> -- Mario, Luigi, Solid Snake, Nobuo Uematsu...</li>
					<li><code>Fangames</code>, <code>Indie games</code>, <code>Games made into movies</code>, <code>Pussies</code> (games about cats, of course)</li>
				</ul>
				<p><b>Category name format:</b> besides the first letter of the first word, only proper nouns should be capitalized. <a href="/posts/2010/04/16/page-editing-guide#Categories" target="_blank" class="arrow-link">More about categories</a></p>
			</div>
			<div id="sw-topic" class="sw" style="display:none">
				Some examples of topics: <code>Videogame violence</code>, <code>Hulk Hogan</code>, <code>Amalgamerotic lesbian action</code><p></p>
			</div>
			
			<input type="submit" value="Start" disabled="disabled" id="submitstart" style="font:normal 20px Arial;"/> 
			<big style="font-size:20px; color:#333;">&nbsp;<?=$title?></big>
		</div>
		
	</form>
	<?
	
	$page->footer();
	exit;
	
}

?>

<div style="display:block; text-align:right; font-size:16px; color:#666;">
	<a href="#help" class="tooltip helpinfo" title="Read both before editing your first page!"><span>?</span></a> 
	Help, tips, and tools: <b><a href="/posts/2010/04/16/page-editing-guide" target="_blank">Page Editing Guide & F.A.Q.</a></b> | <b><a href="/bbcode.htm" target="_blank">BB Code Guide</a></b>
	</div>

<?=($dbdat->redirect_to ? '<div id="redirectnotice" class="warn-box"><span class="warn">This page is being redirected to <b>'.bb2html('[['.$dbdat->redirect_to.']]').'</b>.</span></div>' : '')?>

<form action="edit.php?title=<?=$title_url?>" method="post" name="editpg" id="editpg" class="pgtype-<?=$pgtype?>" enctype="multipart/form-data">
	<input type="hidden" name="pgid" value="<?=$dbdat->pgid?>"/>
	<input type="hidden" name="sessid" value="<?=$sessid?>"/>
	<?=($source ? '<input type="hidden" name="editsource" value="'.$source.'"/>' : '')?>
	
	<?
	if($pgtype == "game") {
		// alternate titles
		/*?>
		<dl>
			<dt>Alternate Titles</dt>
			<dd class="help">
				Input one alternate title per line. 
			</dd>
			<dd class="help" style="display:none;">
				Optionally include a description in parentheses, for example:
				<ul>
					<li><code>Takarajima Z Barbaros no Hih&#333; (&#23453;&#23798;Z &#12496;&#12523;&#12496;&#12525;&#12473;&#12398;&#31192;&#23453;?, lit. Treasure Island Z: Barbaros' Secret Treasure)</code></li>
				</ul>
				Only include <b>Official</b> alternate titles, including:
				<ul>
					<li>Regional or foreign titles, including title changes and foreign characters</li>
					<li>Re-releases with different names</li>
				</ul>
			</dd>
			<dd>
				<?
				if(is_array($in['title_alt']['alt'])) {
					$in['title_alt'] = implode("\r\n", $in['title_alt']['alt']);
				}
				?>
				<textarea name="in[title_alt]" rows="2" cols="80"><?=$in['title_alt']?></textarea>
			</dd>
		</dl>
		<?*/
	}
	
	include("edit_fields.php");
	$fields = simplexml_load_string($xmlstr);
		foreach($fields->field as $f){
			if(!strstr((string)$f['for'], $pgtype)) continue;
			?>
			<dl>
				<?
				foreach($f->children() as $n => $row){
					if((string)$row['for'] && !strstr((string)$row['for'], $pgtype)) continue;
					$str = $row->asXML();
					$str = sprintf($str, $in[(string)$f['name']]);
					$str = str_replace("[PGTYPE]", $pgtype, $str);
					echo $str;
				}
				?>
			</dl>
			<?
		}
		
	?>
	<dl>
		<?
		/*
		$rules['kw']['game'] = '
			<dd class="help" style="display:none;">
				Include keywords, alternate spellings, acronyms and unofficial titles. For example:
				<ul>
					<li><code>SoulCalibur II, SoulCalibur 2, Soul Calibur II, Soul Calibur 2</code></li>
					<li><code>Br&uuml;tal Legend, Brutal Legend</code></li>
					<li><code>Final Fantasy XIV, Final Fantasy 14, FFXIV, FF14</code></li>
				</ul>
			</dd>
		';
		$rules['kw']['category'] = '
			<dd class="help" style="display:none;">
				Include keywords, alternate spellings, acronyms, and unofficial titles.  For example:
				<ul>
					<li><code>PlayStation, Sony Playstation, PS, PSOne, PS One, PS1</code></li>
					<li><code>RPG genre, Role-playing, Role playing</code></li>
					<li><code>Resident Evil series, Biohazard series</code></li>
				</ul>
			</dd>
		';
		?>
		
		<dt><?=($pgtype == "person" ? 'Aliases, Nicknames, Foreign Characters' : 'Title Keywords, Alternate Spellings')?></dt>
		<dd class="help">Input alternate spellings to facilitate better searching for this page.</dd>
		<?=$rules['kw'][$pgtype]?>
		<dd>
			<textarea name="in[keywords]" rows="2" cols="80"><?=(!$in['keywords'] ? $title : $in['keywords'])?></textarea>
		</dd>
		
	</dl>
	<?
	if($pgtype == "game" || $pgtype == "person") {
		?>
		<dl id="edpg-description">
			<?
			$rules['desc']['game'] = '
				<dd class="help" style="display:none;">	
					Common format: <code>A (GENRES) game for (PLATFORMS) by (DEVELOPERS) in the (SERIES) series</code><br/>
					For example: <code>A [[Category:Platform genre|Platform]] game for [[Category:Super Nintendo]] and [[Category:Game Boy Advance]] by [[Category:Nintendo]] in the [[Category:Mario series|Mario]] and [[Category:Yoshi series]]</code>
				</dd>
			';
			$rules['desc']['person'] = '
				<dd class="help" style="display:none;">
					Common format: <code>A (PROFESSION) for (COMPANY/DEVELOPMENT GROUP)</code><br/>
					For example: <a href="#help" class="tooltip helpinfo" title="&bull; \'Music Composer\' and \'Game Designer\' are not good category names since they\'re a name of a profession rather than a listable category&lt;br/&gt;&bull; \'Music Composers\' and \'Game Designers\' are better category names since they can be a list of people with this noted profession AS WELL AS a page that details the profession"><span>?</span></a>
					<ul>
						<li><code>A [[Category:Music Composers|Music composer]] for [[Category:Nintendo]]</code></li>
						<li><code>A [[Category:Game Designers|Game Designer]] for [[Category:Mistwalker]] and (formerly) [[Category:Square Enix]]</code></li>
					</ul>
				</dd>
			';
			?>
				
			<dt>Description</dt>
			<dd class="help">
				A single-sentence description of this <?=($pgtype == "other" ? "page" : $pgtype)?>.
			</dd>
			<dd class="help" style="display:none;">	
				Tip: You can use the <code>Category</code> namespace to atomatically categorize this page. <a href="/posts/2010/04/16/page-editing-guide#Categories" target="_blank" class="arrow-link">More about categories</a></a>
			</dd>
			<?=$rules['desc'][$pgtype]?>
			<dd class="fw">
				<?=outputToolbox("inp-description", array("b", "i", "a", "links"), "bbcode")?>
				<textarea name="in[description]" rows="2" id="inp-description"><?=$in['description']?></textarea>
			</dd>
		</dl>
		<?
	}
	?>
	
	<!--page cont-->
	<dl id="edpg-content">
		<dt>Page Content</dt>
		<dd class="help">
			At least one paragraph that supplies general information, story/synopsis/biography, facts, and trivia about the subject. The article could also be a publisher's description, storyline, tagline, or official biography, but all referenced materials should be <a href="/posts/2010/04/16/page-editing-guide#Citing_Sources">cited properly</a>. <b><a href="/posts/2010/04/16/page-editing-guide#Page_Content" target="_blank" class="arrow-link">More about this field</a></b>
		</dd>
		<dd class="fw">
			<?=outputToolbox("inp-content", array("b", "i", "a", "big", "small", "links", "h5", "h6", "img", "cite", "ol", "li"), "bbcode")?>
			<textarea name="in[content]" rows="25" id="inp-content"><?=$in['content']?></textarea>
		</dd>
	</dl>
	
	<!--categories-->
	<dl>
		<dt>Parent Categories</dt>
		<dd class="help">
			Add categories to relate this page with others. <a href="/posts/2010/04/16/page-editing-guide#Categories" target="_blank" class="arrow-link">More about categories</a>
		</dd>
		<dd class="tool">
			<input type="text" name="categories-category" value="Start typing to find a category" class="resetonfocus" style="width:280px;"/> 
			<input type="button" value="Add" class="tool-list"/> &nbsp; 
			<span class="arrow-left" style="font-size:12px; color:#666;">Add categories one at a time</span>
		</dd>
		<dd class="null"<?=(count($in['categories']['category']) ? ' style="display:none;"' : '')?>>No categories currently recorded</dd>
		<?
		if(count($in['categories']['category'])) {
			foreach($in['categories']['category'] as $indxval => $cat) {
				echo '<dd class="tool-item"><a href="javascript:void(0);" class="ximg" onclick="rmToolItem($(this).closest(\'dd\'));">remove</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.bb2html($cat, "pages_only").' <textarea name="in[categories][category][]">'.$cat.'</textarea></dd>';
			}
		}
		?>
	</dl>
	
	<?*/
	if($pgtype == "game") {
		
		// GAME //
		?>
		
		<!--related games-->
		<?/*<dl>
			<dt>Related Games</dt>
			<dd class="tool">
				<input type="text" name="related_games-game" value="Start typing to find a game" class="resetonfocus" style="width:250px;"/> 
				<input type="button" value="Add" class="tool-list"/> &nbsp; 
				<span class="arrow-left" style="font-size:12px; color:#666;">Input a game title and click "Add"; If it's not listed you can still add it</span>
			</dd>
			<dd class="null"<?=(count($in['related_games']['game']) ? ' style="display:none;"' : '')?>>No related games currently recorded</dd>
			<?
			if(count($in['related_games']['game'])) {
				foreach($in['related_games']['game'] as $rel) {
					echo '<dd class="tool-item"><a href="javascript:void(0);" class="ximg" onclick="rmToolItem($(this).closest(\'dd\'));">remove</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.bb2html($rel, "pages_only").' <textarea name="in[related_games][game][]">'.$rel.'</textarea></dd>';
				}
			}
			?>
		</dl>*/?>
		
		<!--publications-->
		<dl id="edpg-publications">
			<dt>Release Dates & Box Art</dt>
			<dd class="help noslide" style="display:none;" class="">
				<big class="warn" style="font:bold 15px arial;">Before continuing, please make sure your box art meets these standards</big>
				
				<p></p>
				
				<div style="padding:0 0 0 50px; background:url(/bin/img/navi.png) no-repeat 0 10px;">
					<b>Hey, Listen!</b> The absolute best source for quality cover art is GameFAQs. Before submitting your box art, please check their site to see if they have a nicer picture. Visit the following URL to go straight to search results. Handy!<br/>
					<a href="http://www.gamefaqs.com/search/index.html?game=<?=urlencode($in['title'])?>" target="_blank" class="arrow-link" style="font-size:17px">http://www.gamefaqs.com/search/index.html?game=<?=urlencode($in['title'])?></a>
				</div>
				
				<p></p>
				
				Please make sure your box art is:
				<ul>
					<li>JPG, GIF, or PNG format</li>
					<li>At least 200 pixels in width</li>
					<li>An unblurred, clear, quality image without watermarks or site logos</li>
					<li>A flat image that is not scaled, rotated, or has any borders or whitespace around the perimeter (tip: use <a href="http://www.google.com/search?q=online+image+editor" target="_blank" class="arrow-link">an online image editor</a> like <a href="http://www.pixlr.com" target="_blank" class="arrow-link">Pixlr</a> to quickly and easily crop any whitespace or borders from an image)</li>
				</ul>
				<br/>
				
				<table border="0" cellpadding="10" cellspacing="1" width="533">
					<tr>
						<th colspan="3" style="text-align:center; font-size:16px; background-color:#F5F5F5; border:1px solid #DDD;">Some examples of bad box art</th>
					</tr>
					<tr>
						<td style="background-color:black;"><img src="/gamedata/bad_boxes/3d.jpg"/></td>
						<td style="background-color:black;"><img src="/gamedata/bad_boxes/watermark.jpg"/></td>
						<td style="background-color:black;"><img src="/gamedata/bad_boxes/whitespace.jpg"/></td>
					</tr>
					<tr>
						<td style="text-align:center;">Side perspective</td>
						<td style="text-align:center;">Watermark from a<br/>douchebag site</td>
						<td style="text-align:center;">Whitespace around permiter<br/>(even as little as a few pixels of whitespace will conflict with how we use and display box images)</td>
					</tr>
				</table>
			</dd>
			<dd class="tool" style="font-size:15px;" onclick="$(this).hide().siblings('.tool, .help').css('display', 'block');">
				<input type="button" value="Add a new box art image"/>
			</dd>
			<dd class="tool" style="display:none; font-size:17px;">
				
				<label>
					<input type="checkbox" name="" value="" id="boxst" onclick="if($(this).is(':checked')) $('#addpubbutton').show(); else $('#addpubbutton').hide();"/> 
					<span>
						<b>Yes, my box art meets the above standards</b> and I have compared my box art with the images at <a href="http://www.gamefaqs.com/search/index.html?game=<?=urlencode($in['title'])?>" target="_blank" class="arrow-link">GameFAQs</a>.
					</span>
				</label>
				
				<div id="addpubbutton" style="display:none; margin:8px 0 0;">
					<input type="button" value="Add a new game box" onclick="addPub();"/>
				</div>
				
			</dd>
			<dd class="null"<?=(count($in['publications']['publication']) ? ' style="display:none;"' : '')?>>No Publications currently recorded</dd>
			<?
			if(count($in['publications']['publication'])) {
				$i = 0;
				foreach($in['publications']['publication'] as $indxval => $pub) outputPub($pub, ++$i);
			}	
			?>
		
		</dl>
		<?
	}
	
	if($pgtype == "person") {
		?>
		
		<!--personal details-->
		<dl>
			<dt>Personal Details</dt>
			<dd>
				Birthdate: 
				<select name="in[dob][0]">
					<option value="0000">year</option>
					<?
					if($in['dob'] && !is_array($in['dob'])) $in['dob'] = explode("-", $in['dob']);
					for($y = (date('Y') - 10); $y >= 1900; $y--) {
						echo '<option value="'.$y.'"'.($in['dob'][0] == $y ? ' selected="selected"' : '').'>'.$y.'</option>';
					}
					?>
				</select> 
				<select name="in[dob][1]">
					<option value="00">month</option>
					<option value="01"<?=($in['dob'][1] == "01" ? ' selected="selected"' : '')?>>1 January</option>
					<option value="02"<?=($in['dob'][1] == "02" ? ' selected="selected"' : '')?>>2 February</option>
					<option value="03"<?=($in['dob'][1] == "03" ? ' selected="selected"' : '')?>>3 March</option>
					<option value="04"<?=($in['dob'][1] == "04" ? ' selected="selected"' : '')?>>4 April</option>
					<option value="05"<?=($in['dob'][1] == "05" ? ' selected="selected"' : '')?>>5 May</option>
					<option value="06"<?=($in['dob'][1] == "06" ? ' selected="selected"' : '')?>>6 June</option>
					<option value="07"<?=($in['dob'][1] == "07" ? ' selected="selected"' : '')?>>7 July</option>
					<option value="08"<?=($in['dob'][1] == "08" ? ' selected="selected"' : '')?>>8 August</option>
					<option value="09"<?=($in['dob'][1] == "09" ? ' selected="selected"' : '')?>>9 September</option>
					<option value="10"<?=($in['dob'][1] == "10" ? ' selected="selected"' : '')?>>10 October</option>
					<option value="11"<?=($in['dob'][1] == "11" ? ' selected="selected"' : '')?>>11 November</option>
					<option value="12"<?=($in['dob'][1] == "12" ? ' selected="selected"' : '')?>>12 December</option>
				</select> 
				<select name="in[dob][2]">
					<option value="00">day</option>
					<?
					for($d = 1; $d <= 31; $d++) {
						if($d < 10) $d = '0'.$d;
						echo '<option value="'.$d.'"'.($in['dob'][2] == $d ? ' selected="selected"' : '').'>'.$d.'</option>'."\n";
						}
					?>
				</select>
			</dd>
			<dd>
				Nationality: 
				<select name="in[nationality]">
					<option value="">none/unknown</option>
					<?
					require($_SERVER['DOCUMENT_ROOT']."/bin/php/country_codes.php");
					while(list($k, $v) = each($cc)) {
						echo '<option value="'.$k.'"'.($k == $in['nationality'] ? ' selected="selected"' : '').'>'.$v.'</option>';
					}
					?>
				</select>
			</dd>
		</dl>
		<?
	}
	
	if($pgtype == "game" || $pgtype == "person") {
		?>
		
		<!--credits-->
		<dl class="credits">
			<dt>Credits</dt>
			<?
			$rules['credits']['game'] = '
				<dd class="help">
					<ul>
						<li>If a person was credited as someone else than their actual name (an alias, for example), you can use the following standard Wiki linking format:<br/>&nbsp;&nbsp;&nbsp;<code>REAL NAME|CREDITED NAME</code>. For example: <code>Kenji Inafune|Inafking</code>, <code>Hiroshi Yamauchi|Old Man Oochi</code></li>
						<li>Each person must be assigned <b>at least one role</b>. If this person had multiple roles, input them individually by clicking <code>+ Add another role</code>.</li>
					</ul>
				</dd>
			';
			$rules['credits']['person'] = '
				<dd class="help">
					<ul>
						<li>Credit this person with roles in games and soundtracks.</li>
						<li>Each work must be assigned <b>at least one role</b>. If this person had multiple roles, input them individually by clicking <code>+ Add another role</code>.</li>
						<li>Input a generalized role rather than a specific role to credit this person. <a href="#help" class="tooltip" title="For example, \'Voice Actor\' instead of \'Voice of Solid Snake\'. Details can be specified in the notes field that appears after the role field.">?</a></li>
					</ul>
				</dd>
			';
			echo $rules['credits'][$pgtype];
			?>
			<dd class="tool">
				<input type="text" name="works_<?=$pgtype?>" value="Start typing to find a work..." class="resetonfocus" style="width:250px;"/> 
				<input type="button" value="Add" class="tool-list"/> &nbsp; 
				<span class="arrow-left" style="font-size:12px; color:#666;">Add one work at a time</span>
			</dd>
			<dd class="null"<?=(count($in['credits']['credit']) ? ' style="display:none;"' : '')?>>No works recorded</dd>
			<?
			if(count($in['credits']['credit'])) {
				$i = 0;
				foreach($in['credits']['credit'] as $indxval => $p) {
					$i++;
					$vital = ($_POST ? $p['vital'] : $p['@attributes']['vital']);
					?>
					<dd class="tool-item sortable <?=$vital?>">
						<a class="dnd"></a>
						<a href="javascript:void(0);" class="ximg" style="right:15px;" onclick="rmToolItem($(this).closest('dd'));">remove</a>
						<div style="float:left;">
							<input type="text" name="in[credits][credit][<?=$i?>][name]" value="<?=htmlSC($p['name'])?>" style="display:none; width:226px; height:16px; font-family:monospace;"/>
							<span><?=bb2html($p['name'], "pages_only")?></span> 
							<a href="#editname" title="edit this entry"" onclick="$(this).hide().prev().hide().prev().show();"><img src="/bin/img/icons/edit.gif" border="0" alt="edit"/></a>
						</div>
						<div style="margin:0 0 5px 208px;">
							<label><input type="radio" name="in[credits][credit][<?=$i?>][vital]" value="vital"<?=($vital ? ' checked="checked"' : '')?> class="cr-v-toggle"/> <b>Vital Role</b></label> &nbsp; 
							<label><input type="radio" name="in[credits][credit][<?=$i?>][vital]" value=""<?=(!$vital ? ' checked="checked"' : '')?> class="cr-v-toggle"/> Personnel</label>
						</div>
						<div style="float:left;">
							<?
							$roles = array();
							foreach($p['roles']['role'] as $k => $v) {
								if(is_array($v)) $roles[] = $v;
								else $roles[0][$k] = $v;
							}
							for($c = 0; $c < 5; $c++) {
								
								//converting notes from individual role to core array value
								if(!$p[notes] && $cr['notes']) $p[notes] = $cr['notes'];
								
								$cr = $roles[$c];
								?>
								<div class="credit" style="margin:0 0 5px;<?=(!$cr['credited_role'] ? ' display:none;' : '')?>">
									<input type="text" name="in[credits][credit][<?=$i?>][roles][role][<?=$c?>][credited_role]" value="<?=htmlSC($cr['credited_role'])?>" class="inprole" style="width:200px;"/>
								</div>
								<?
							}
							?>
							<a href="javascript:void(0);" onclick="$(this).siblings('.credit:hidden:eq(0)').show();" style=""><b>+</b> Add another role</a>
						</div>
						<div style="margin:0 50px 0 210px;">
							<textarea name="in[credits][credit][<?=$i?>][notes]" style="width:100%; height:3em; border-color:#CCC;"><?=$p['notes']?></textarea>
							<div class="notesmsg" style="margin:5px 0 0;">
								<span style="padding:0 0 0 10px; color:#AAA; background:url(/bin/img/arrow-up-point.png) no-repeat left center;">Notes about this credit (BB Code allowed)</span>
							</div>
						</div>
						<br style="clear:both;"/>
					</dd>
					<?
				}
			}
			?>
		</dl>
		<?
		
	}
	
	?>
	
	<dl>
		<dt>Page Attributes</dt>
		<dd>
			<ul style="line-height:1.5em; font-size:14px; color:#666;">
				<li>Title: <b style="color:black"><?=$in['title']?></b> <a href="#changetitle" class="preventdefault arrow-toggle" onclick="$(this).toggleClass('arrow-toggle-on').next().toggle();" style="<?=(!$pgid ? 'display:none;' : '')?>">change</a>
					<div class="pgattrch">
						Since renaming a page can be a drastic change, it has its own form to handle the change: <a href="/pages/move.php?title=<?=$title_url?>" target="_blank">Rename <i><?=$title?></i></a>
					</div>
				</li>
				<li>Page Type: <b style="color:black"><?=$in['pgtype']?></b> <a href="#changetype" class="preventdefault arrow-toggle" onclick="$(this).toggleClass('arrow-toggle-on').next().toggle();" style="<?=(!$pgid ? 'display:none;' : '')?>">change</a>
					<div class="pgattrch">
						<div class="warn" style="margin:0 0 3px;font-size:15px;">
							Changing the page type may result in a loss of certain data.
						</div>
						<select name="in[pgtype]">
							<option value="game"<?=($in['pgtype'] == "game" ? ' selected="selected"' : '')?>>Game</option>
							<option value="person"<?=($in['pgtype'] == "person" ? ' selected="selected"' : '')?>>Person</option>
							<option value="category"<?=($in['pgtype'] == "category" ? ' selected="selected"' : '')?>>Category</option>
							<option value="topic"<?=($in['pgtype'] == "topic" ? ' selected="selected"' : '')?>>Topic</option>
						</select>
					</div>
				</li>
				<?
				
				//rep img
				if($in['rep_image']){
					$x = explode("/", $in['rep_image']);
					$repimg = $x[(count($x) - 1)];
				}
				?>
				<li>Representative Image: <b style="color:black"><?=($repimg ? '<a href="'.$in['rep_image'].'" target="_blank">'.$repimg.'</a>' : 'none')?></b> <a href="#changetype" class="preventdefault arrow-toggle" onclick="$(this).toggleClass('arrow-toggle-on').next().toggle();">change</a> &ndash; An image that best represents this <?=$in['pgtype']?>
					<div class="pgattrch">
						<?=($in['pgtype'] == "game" ? 'The box art for the primary publication will be automatically set as the Representative Image unless you upload a different image below. The only instance when this image shouldn\'t be box art is when there is none, in which case a logo or artwork will suffice (but a temporary or preliminary box image is much preferred)<br/>' : '')?>
						<a href="http://images.google.com/images?q=<?=urlencode($title)?>" target="_blank" class="arrow-link">Google image search for <i><?=$title?></i></a><br/>
						<span class="warn"></span>Please upload a picture that:
						<ul>
							<li>is at least 150 pixels in width &ndash; The bigger the better</li>
							<li>is in JPG, PNG, or GIF format</li>
							<?=($in['pgtype'] == "person" ? '<li>is a picture of this person; it isn\'t a company logo and preferably doesn\'t have other people in it</li><li>is closely cropped around the person\'s face (tip: use <a href="http://pixlr.com" target="_blank" class="arrow-link">Pixlr</a> to crop pictures quickly)</li>' : '')?>
						</ul>
						<input type="file" name="rep_img"/> &nbsp; 
						<label><input type="checkbox" name="in[rep_image_hide]" value="1"<?=($in['rep_image_hide'] ? ' checked="checked"' : '')?>/>Hide image</label> &nbsp; 
						<?=($in['rep_image'] ? '<label><input type="checkbox" name="rm_rep_image" value="1"'.($_POST['rm_rep_image'] ? ' checked="checked"' : '').'/>Remove current image '.($pgtype == "game" ? ' <a href="#help" class="tooltip" title="Selecting this option will remove the current image and replace it with the box of the primary publication">?</a>' : '').'</label>' : '')?>
					</div>
					<input type="hidden" name="in[rep_image]" value="<?=$in['rep_image']?>"/>
				</li>
				
				<?
				//heading img
				if($in['heading_image']){
					$x = explode("/", $in['heading_image']);
					$hdimg = $x[(count($x) - 1)];
				}
				?>
				<li>
					Heading Image: <b style="color:black"><?=($hdimg ? '<a href="'.$in['heading_image'].'" target="_blank">'.$hdimg.'</a>' : 'none')?></b> <a href="#changetype" class="preventdefault arrow-toggle" onclick="$(this).toggleClass('arrow-toggle-on').next().toggle();">change</a>
					<div class="pgattrch">
						<input type="file" name="hd_img"/> &nbsp; 
						<?=($in['heading_image'] ? '<label><input type="checkbox" name="rm_heading_image" value="1"'.($_POST['rm_heading_image'] ? ' checked="checked"' : '').'/>Remove current image</label>' : '')?>
					</div>
					<input type="hidden" name="in[heading_image]" value="<?=$in['heading_image']?>"/>
				</li>
				
				<?
				//bg img
				if($in['background_image']){
					$x = explode("/", $in['background_image']);
					$bgimg = $x[(count($x) - 1)];
				}
				?>
				<li>Background Image: <b style="color:black"><?=($bgimg ? '<a href="'.$in['background_image'].'" target="_blank">'.$bgimg.'</a>' : 'none')?></b> <a href="#changetype" class="preventdefault arrow-toggle" onclick="$(this).toggleClass('arrow-toggle-on').next().toggle();">change</a>
					<div class="pgattrch">
						<span class="warn"></span>Your image must must flush with the background. Therefore, image manipulation (IE Photoshop) is required; A PNG-24 with transparency is recommended.<br/>
						<input type="file" name="bg_img"/> &nbsp; 
						<?=($in['background_image'] ? '<label><input type="checkbox" name="rm_background_image" value="1"'.($_POST['rm_background_image'] ? ' checked="checked"' : '').'/>Remove current image</label>' : '')?>
					</div>
					<input type="hidden" name="in[background_image]" value="<?=$in['background_image']?>"/>
				</li>
			</ul>
		</dd>
	</dl>
	
	<fieldset id="editsummary" style="margin:30px 0 0;">
		<legend>Edit Summary</legend>
		Please briefly summarize edits, making clear your intention and purpose for editing. 
		This will help keep better records and allow the editors and future contributors to better understand your contributions.
		<div class="fw" style="margin-top:5px;">
			<textarea name="edit_summary" rows="2"><?=$_POST['edit_summary']?></textarea>
		</div>
	</fieldset>
	<p></p>
	
	<?
	$watch = array();
	$q = "SELECT * FROM pages_watch WHERE `title`='".mysql_real_escape_string($title)."' AND usrid='".$usrid."' LIMIT 1";
	$watch = mysql_fetch_assoc(mysql_query($q));
	
	?>
	<fieldset style="padding:10px;">
		<?
		if(!$watch){
		?>
			<label>
				<input type="checkbox" name="watch[watch]" value="1"<?=($watch ? ' checked="checked"' : '')?> onclick="if( $(this).is(':checked') ) $('#ch-watch-email').show(); else $('#ch-watch-email').hide();"/> 
				Watch this page <a title="Easily track any additions or changes to this page from your Watch List" class="tooltip" href="#help">?</a>
			</label><p style="margin:5px 0 0;"></p>
			<?
		}
		?>
		<label><input type="checkbox" name="minoredit" value="1"<?=($_POST['minoredit'] ? ' checked="checked"' : '')?>/> This is a minor edit <a href="#help" class="tooltip" title="Mark this edit as minor if it only corrects spelling or formatting, performs minor rearrangements of text, or tweaks only a few words or inconsequential attributes.">?</a></label>
		<?
		if($usrrank > 6){
			?><p style="margin:5px 0 0;"></p><label><input type="checkbox" name="withholdpts" value="1"<?=($_POST['withholdpts'] ? ' checked="checked"' : '')?>/> Withhold my points for this edit</label><?
		}
		?>
	</fieldset>
	<p></p>
	
	<div class="buttons">
		
		<div class="rmpg" style="float:right;">
			<label><input type="checkbox" name="rmpg" value="1" <?=($_POST['rmpg'] ? 'checked="checked"' : '')?> onclick="if(!confirm('Are you sure?')) $(this).attr('checked', false);"/>Remove this page</label>
		</div>
	
		<input type="hidden" name="_action" value="publish"/>
		<input type="button" name="" value="Save Draft / Preview" class="editpgsubmit" style="font-size:14px; padding-top:4px; padding-bottom:4px;" onclick="$(this).prev().val('draft');"/> 
		<input type="button" name="" value="Submit & Publish" class="editpgsubmit" style="font-weight:bold; font-size:14px; padding-top:4px; padding-bottom:4px;"/> 
	
	</div>
	
</form>
<?

$page->footer();