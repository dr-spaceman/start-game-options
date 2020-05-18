<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/pages/edit_ajax.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/htmltoolbox.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");

$pgid      = $row['pgid'];
if(!$title && !$pgid) die('No page id or title given.');

$sessid    = $_POST['sessid'] ? $_POST['sessid'] : date("YmdHis").sprintf("%07d", $usrid);

$filedir   = "/pages/files/".preg_replace("/[^a-z0-9_-]/i", "", $titleurl)."/";
$pgtype    = $row['pgtype'];
if(!$pgtype) $page->kill("No page type detected");

$_pg->ile  = true;
$dbdat     = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $masterq));

$in        = $_POST['in'];
if($in)      require("edit_process.php");
if($in)      $row = $in;
else $in   = $row;

//check for previous sessions
if(!$_POST){
	$query = "SELECT * FROM pages_edit WHERE title='".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."' AND usrid='$usrid' AND published='0' AND session_id != '$sessid' ORDER BY `datetime` DESC";
	$res   = mysqli_query($GLOBALS['db']['link'], $query);
	if(mysqli_num_rows($res)){
		$sess = 'You have unpublished sessions for this page title.</b> You can build upon your previous session or continue with the form below to start from scratch.<ul>';
		while($row_ = mysqli_fetch_assoc($res)) {
			$sess.= '<li><a href="history.php?view_version='.$row_['session_id'].'">'.formatDate($row_['datetime'], 2).'</a> <span style="color:#AAA;">[<a href="edit.php?title='.$titleurl.'&editsource='.$row_['session_id'].'" title="build upon these changes">build</a>|<a href="edit.php?destroysession='.$row_['session_id'].'" title="permanently delete these changes" style="color:#E21D1D">destroy!</a>]</span> '.$row_['edit_summary'].'</li>';
		}
		$sess.= '</ul>';
		$warnings[] = $sess;
	}
}

$page->header();

if(!$_COOKIE['iledit'] || !$usrid){

?>
<div style="position:fixed; z-index:9; top:20%; left:50%; width:420px; margin:0 0 0 -210px; padding:2em 2em 2em 100px; font-size:110%; color:white; line-height:1.5em; background:black url(/bin/img/icons/sprites/digdug_huge.png) no-repeat -32px 10px; opacity:.95; border-radius:1em; -moz-border-radius:1em; -webkit-border-radius:1em;">
	<a href="#close" title="hide this message and don't show it to me again" class="ximg preventdefault" style="top:10px; right:10px;" onclick="$(this).parent().fadeOut(); $.cookie('iledit', '1', {expires:30, path:'/'});">close</a>
	<b style="font-size:130%;">Welcome to the Videgam.in Page Editor.</b>
	<p></p>
	Before editing your first page, please read the <a href="/s1823" target="_blank">Page Editing Guide & F.A.Q.</a>. You should also be familiar with the <a href="/bbcode.htm" target="_blank">BB Code Guide</a> for the special formatting used on this site.
	<?=(!$usrid ? '<p></p><b>Please note!</b> You can mess around, but your changes won\'t be saved until you <a href="/login.php">Log in</a>.' : '')?>
</div>
<?

}

?>

<iframe src="" name="ileframe" style="display:none;"></iframe>

<form action="/pages/handle.php?title=<?=$titleurl?>&piece=edit" method="post" name="ile" id="ile" enctype="multipart/form-data" target="_top">
	
	<input type="hidden" name="pgid" value="<?=$pgid?>"/>
	<input type="hidden" name="sessid" value="<?=$sessid?>"/>
	<input type="hidden" name="_action" value="publish" id="formaction"/>
	<input type="hidden" name="ile_return" value=""/><!--set to TRUE to return a preview-->

	<div id="ile-msg">
		<div class="container">
			
			<div id="ile-subm" style="display:none; margin:-10px -10px 10px; background-color:white;">
				<table border="0" cellpadding="10" cellspacing="0" width="100%">
					<tr>
						<td valign="top" width="65%">
							<fieldset id="editsummary">
								<legend>Edit Summary <a href="#help" class="tooltip" title="Please briefly summarize edits, making clear your intention and purpose for editing. This will help keep better records and allow the editors and future contributors to better understand your contributions.">?</a></legend>
								<textarea name="edit_summary" tabindex="1" style="width:98%; height:3em;"><?=$_POST['edit_summary']?></textarea>
							</fieldset>
						</td>
						<td valign="top" nowrap="nowrap">
							<div style="margin:10px 0 0;">
								<?
								$watch = array();
								$q = "SELECT * FROM pages_watch WHERE `title`='".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."' AND usrid='".$usrid."' LIMIT 1";
								$watch = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
								if(!$watch){
									?>
									<label><input type="checkbox" name="watch[watch]" value="1"<?=($watch ? ' checked="checked"' : '')?> onclick="if( $(this).is(':checked') ) $('#ch-watch-email').show(); else $('#ch-watch-email').hide();"/> Watch this page</label> 
									<a title="Easily track any additions or changes to this page from your Watch List" class="tooltip" href="#help">?</a>
									<br/>
									<?
								}
								if($_SESSION['user_rank'] > 6){
									?><label><input type="checkbox" name="withholdpts" value="1"<?=($_POST['withholdpts'] ? ' checked="checked"' : '')?>/> Withhold my points for this edit</label><br/><?
								}
								?><label><input type="checkbox" name="minoredit" value="1"<?=($_POST['minoredit'] ? ' checked="checked"' : '')?>/> This is a minor edit</label> <a href="#help" class="tooltip" title="Mark this edit as minor if it only corrects spelling or formatting, performs minor rearrangements of text, or tweaks only a few words or inconsequential attributes.">?</a><?
								?>
							</div>
						</td>
						<td valign="top">
							<div class="ilebuttons">
								<input type="button" value="Save Draft" tabindex="2" class="editpgsubmit" onclick="$('#formaction').val('draft');"/>
								<p></p>
								<input type="button" value="Submit Changes" tabindex="3" class="editpgsubmit" style="font-weight:bold;" onclick="$('#formaction').val('publish');"/>
								<p></p>
								<a href="/pages/edit.php?destroysession=<?=$sessid?>&returnonfail=<?=$titleurl?>">Cancel Edits</a>
							</div>
						</td>
					</tr>
				</table>
			</div>
			
			<div style="margin:0 0 0 -20px; padding:0 0 0 20px; background:url(/bin/img/big_edit.png) no-repeat left center;">
				
				<span style="float:right; white-space:nowrap; background:url(/bin/img/arrow-up-point.png) no-repeat left center; padding:0 0 0 15px;">
					<a href="#submission" class="preventdefault" onclick="$('#ile-subm').slideToggle();">Submission Options</a>
				</span>
				You are in Edit Mode &ndash; Click anything <b class="il" style="background-color:#BBB;">editable</b> to make changes &middot; 
				<a href="/s1823" target="_blank" title="Page Editing Guide and FAQ">Help</a> &middot;
				<a href="/pages/history.php?view_version=<?=$sessid?>" target="_blank" title="Permanent link to view changes made during this edit session">Permanent Link</a> &middot; 
				<a href="/pages/edit.php?title=<?=$titleurl?>&editsource=<?=$sessid?>" title="Use a form to edit this page">Switch to Form-style Editing</a>
			</div>
			
			<div class="message"><img src="/bin/img/black_point.png"/>Click me when you're done</div>
		</div>
	</div>
	
	<!-- description -->
	<dl id="ile-description">
		<dt>Description</dt>
		<?
		$rules['desc']['game'] = '
			<dd class="help">	
				Common format: <code>A (GENRES) game for (PLATFORMS) by (DEVELOPERS) in the (SERIES) series</code><br/>
				For example: <code>A [[Category:Platform genre|Platform]] game for [[Category:Super Nintendo]] and [[Category:Game Boy Advance]] by [[Category:Nintendo]] in the [[Category:Mario series|Mario]] and [[Category:Yoshi series]]</code>
			</dd>
		';
		$rules['desc']['person'] = '
			<dd class="help">
				Common format: <code>A (PROFESSION) for (COMPANY/DEVELOPMENT GROUP)</code><br/>
				For example: <a href="#help" class="tooltip" title="&bull; \'Music Composer\' and \'Game Designer\' are not good category names since they\'re a name of a profession rather than a listable category&lt;br/&gt;&bull; \'Music Composers\' and \'Game Designers\' are better category names since they can be a list of people with this noted profession AS WELL AS a page that details the profession">?</a>
				<ul>
					<li><code>A [[Category:Music Composers|Music composer]] for [[Category:Nintendo]]</code></li>
					<li><code>A [[Category:Game Designers|Game Designer]] for [[Category:Mistwalker]] and (formerly) [[Category:Square Enix]]</code></li>
				</ul>
			</dd>
		';
		?>
		<dd class="help">
			A single-sentence description of this <?=($pgtype == "other" ? "page" : $pgtype)?>.
		</dd>
		<dd class="help">	
			Tip: You can use the <code>Category</code> namespace to atomatically categorize this page. <a href="/sblog/1823/page-editing-guide#Categories" target="_blank" class="arrow-link">More about categories</a></a>
		</dd>
		<?=$rules['desc'][$pgtype]?>
		<dd class="form">
			<?=outputToolbox("inp-description", array("b", "i", "links"), "bbcode")?>
			<textarea name="in[description]" rows="3" cols="80" tabindex="1" id="inp-description" class="ilereturn"><?=$in['description']?></textarea>
		</dd>
	</dl>
	
	<!--page cont-->
	<dl id="ile-content" style="width:900px; max-width:900px;">
		<dt>Page Content</dt>
		<dd class="help">
			At least one paragraph that supplies general information, story/synopsis/biography, facts, and trivia about the subject. The article could also be a publisher's description, storyline, tagline, or official biography, but all referenced materials should be <a href="/sblog/1823/page-editing-guide#Citing_Sources">cited properly</a>. <b><a href="/sblog/1823/page-editing-guide#Page_Content" target="_blank" class="arrow-link">More about this field</a></b>
		</dd>
		<dd class="form fw">
			<?=outputToolbox("inp-content", array("b", "i", "a", "big", "small", "links", "h5", "h6", "img", "cite", "br"), "bbcode")?>
			<textarea name="in[content]" rows="22" cols="" tabindex="1" id="inp-content" class="ilereturn"><?=$in['content']?></textarea>
		</dd>
	</dl>
	
	<!--categories-->
	<dl id="ile-categories" style="margin-top:-30px;">
		<dt>Parent Categories</dt>
		<dd class="help">Add parent categories to relate this page with others. <a href="/sblog/1823/page-editing-guide#Categories" target="_blank" class="arrow-link">More about categories</a></dd>
		<dd class="help">Enclose Category names in double square brackets (ie: <code>[[Game concept]]</code>).</dd>
		<dd class="help">Category names are preferred to be singular nouns (ie: <code>[[Game console]]</code> rather than <code>[[Game consoles]]</code>)</dd>
		<dd class="form">
			<?
			if(!$in['categories']['str'] && is_array($in['categories']['category'])) $in['categories']['str'] = implode("\n", $in['categories']['category'])
			?>
			<textarea name="in[categories][str]" rows="5" cols="60" tabindex="10" class="ilereturn"><?=$in['categories']['str']?></textarea></dd>
		</dd>
	</dl>
	
	<?
	if($pgtype == "game") {
		
		// GAME //
		
		?>
		<!-- publications -->
		<dl id="ile-publications" class="nodrag" style="position:fixed; width:800px; height:70%; top:0; left:50%; margin:20px 0 0 -400px; overflow:auto;">
			<dt>Publications</dt>
			<dd class="tool" style="" onclick="$(this).hide().siblings().show(); document.location='#edpg-publications';">
				<input type="button" value="Add a new Publication" style="padding-top:3px; padding-bottom:3px;"/>
			</dd>
			<dd class="help noslide" style="display:none; line-height:1.5em;" class="">
				
				<div style="padding:10px 60px; background:url(/bin/img/navi.png) no-repeat 10px 10px;">
					<b>Hey, Listen!</b> The absolute best source for quality cover art is GameFAQs. Before submitting your box art, please check their site to see if they have a nicer picture. Visit the following URL to go straight to search results. Handy!<br/>
					<a href="http://www.gamefaqs.com/search/index.html?game=<?=urlencode($title)?>" target="_blank" class="arrow-link" style="font-size:17px">http://www.gamefaqs.com/search/index.html?game=<?=urlencode($title)?></a>
				</div>
				
				<br/>
				
				<big class="warn" style="font-weight:bold;">Before continuing, please make sure your box art meets these standards</big>
				<ul>
					<li style="list-style:none; padding:5px 25px 0; background:url(/bin/img/check_15.png) no-repeat 0 7px;">Your image must be JPG, GIF, or PNG format</li>
					<li style="list-style:none; padding:5px 25px 0; background:url(/bin/img/check_15.png) no-repeat 0 7px;">at least 200 pixels in width</li>
					<li style="list-style:none; padding:5px 25px 0; background:url(/bin/img/check_15.png) no-repeat 0 7px;">an unblurred, clear, quality image without watermarks or site logos</li>
					<li style="list-style:none; padding:5px 25px 0; background:url(/bin/img/check_15.png) no-repeat 0 7px;">a flat image that is not scaled, rotated, or has any borders or whitespace around the perimeter<br/>
						<b>TIP!</b> use <a href="http://www.google.com/search?q=online+image+editor" target="_blank" class="arrow-link">an online image editor</a> like <a href="http://www.pixlr.com" target="_blank" class="arrow-link">Pixlr</a> to quickly and easily crop any whitespace or borders from an image.</li>
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
			<dd class="tool" style="display:none; font-size:17px; background-color:">
				
				<div>
					<label>
						<input type="checkbox" name="" value="" id="" onclick="if($(this).is(':checked')) $('#addpubbutton').show(); else $('#addpubbutton').hide();"/> 
						<b>Yes, my box art meets the above standards</b>
					</label> 
					and I have compared my box art with the images at <a href="http://www.gamefaqs.com/search/index.html?game=<?=urlencode($title)?>" target="_blank" class="arrow-link">GameFAQs</a>.
				</div>
				
				<div id="addpubbutton" style="display:none; margin:8px 0 0; font-size:14px;">
					<input type="button" value="Add a new Publication" onclick="addPub();" style="padding-top:3px; padding-bottom:3px;"/>
				</div>
				
			</dd>
			<dd id="newpubsafterme" class="help">
				Publications will be sorted by date after clicking <i>Submit Changes</i>. To upload and preview an image before submitting, click <i>Save Draft</i>.
			</dd>
			<?
			if(count($row['publications']['publication'])) {
				$i = 0;
				foreach($row['publications']['publication'] as $indxval => $pub) outputPub($pub, ++$i);
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
			<dd class="form">
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
			<dd class="form">
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
		<input type="hidden" name="crsort" value="" id="crsort"/>
		<dl id="credits">
			<dt>Credits</dt>
			<?
			$rules['credits']['game'] = '
				<dd class="help">
					<ul>
						<li>If a person was credited as someone else than their actual name (an alias, for example), you can use the following standard Wiki linking format:<br/>&nbsp;&nbsp;&nbsp;<code>REAL NAME|CREDITED NAME</code>. For example: <code>Kenji Inafune|Inafking</code>, <code>Hiroshi Yamauchi|Old Man Oochi</code></li>
						<li>Each person must be assigned <b>at least one role</b>. If this person had multiple roles, input them individually by clicking <code>+ Add another role</code>.</li>
						<li>Input a generalized role rather than a specific role to credit this person with work on this game. <a href="#help" class="tooltip" title="For example, \'Voice Actor\' instead of \'Voice of Solid Snake\'. Details can be specified in the notes field that appears after the role field.">?</a></li>
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
		</dl>
		<?
		if(count($in['credits']['credit'])) {
			$i = 0;
			foreach($in['credits']['credit'] as $indxval => $p) {
				$i++;
				$vital = ($_POST ? $p['vital'] : $p['@attributes']['vital']);
				?>
				<dl id="ile-cr-<?=$i?>" class="serializedreturn" style="position:fixed; width:530px; top:50%; left:50%; margin:-150px 0 0 -265px;">
					<dt>Credit</dt>
					<dd>
						<input type="text" name="in[credits][credit][<?=$i?>][name]" value="<?=htmlSC($p['name'])?>" tabindex="1" class="ile-ac" style="width:310px; float:left; font-family:monospace;"/>
						<div style="margin:0 0 20px 345px;">
							<label><input type="radio" name="in[credits][credit][<?=$i?>][vital]" value="vital"<?=($vital ? ' checked="checked"' : '')?> class="cr-v-toggle"/> <b>Vital Role</b></label> &nbsp; 
							<label><input type="radio" name="in[credits][credit][<?=$i?>][vital]" value=""<?=(!$vital ? ' checked="checked"' : '')?> class="cr-v-toggle"/> Personnel</label>
						</div>
						
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tr>
								<td valign="top">
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
											<input type="text" name="in[credits][credit][<?=$i?>][roles][role][<?=$c?>][credited_role]" value="<?=htmlSC($cr['credited_role'])?>" tabindex="2" class="inprole" style="width:200px;"/>
										</div>
										<?
									}
									?>
									<a href="#edit" onclick="$(this).siblings('.credit:hidden:eq(0)').show().find('input').focus();" tabindex="3"><b>+</b> Add another role</a>
								</td>
								<td>&nbsp;&nbsp;&nbsp;</td>
								<td width="310" valign="top">
									<textarea name="in[credits][credit][<?=$i?>][notes]" tabindex="4" style="width:98%; height:5em;"><?=$p['notes']?></textarea>
									<div class="notesmsg" style="margin:5px 0 0;">
										<span style="padding:0 0 0 10px; color:#888; background:url(/bin/img/arrow-up-point.png) no-repeat left center;">Notes about this credit (BB Code allowed)</span>
									</div>
								</td>
							</tr>
						</table>
					</dd>
					<dd class="ok">
						<a href="#edit" style="float:right; color:#E21D1D;" onclick="if(confirm('Remove this credit?')) { $(this).closest('dl').remove(); $('#il-cr-<?=$i?>').fadeOut(1200); ileclose(); }">Remove</a>
						<input type="button" value="OK" tabindex="5"/>
					</dd>
				</dl>
				<?
			}
		}
	}
	
	?>
	<dl id="ile-title">
		<dt>Page Title</dt>
		<dd class="help">
			Since renaming a page can be a drastic change, it has its own form to handle the change.
		</dd>
		<dd>
			<big><a href="/pages/move.php?title=<?=$titleurl?>">Rename <i><?=$title?></i></a></big>
		</dd>
	</dl>
	
	<!-- keywords -->
	<dl id="ile-keywords">
		<dt><?=($pgtype == "person" ? 'Aliases, Nicknames, Foreign Characters' : 'Title Keywords, Alternate Spellings')?></dt>
		<?
		$rules['kw']['game'] = '
			<dd class="help">
				Include keywords, alternate spellings, acronyms and unofficial titles. This field is meant to facilitate searching. For example:
				<ul>
					<li><code>SoulCalibur II, SoulCalibur 2, Soul Calibur II, Soul Calibur 2</code></li>
					<li><code>Br&uuml;tal Legend, Brutal Legend</code></li>
					<li><code>Final Fantasy XIV, Final Fantasy 14, FFXIV, FF14</code></li>
				</ul>
			</dd>
		';
		$rules['kw']['category'] = '
			<dd class="help">
				Include keywords, alternate spellings, acronyms, and unofficial titles. This field is meant to facilitate searching. For example:
				<ul>
					<li><code>PlayStation, Sony Playstation, PS, PSOne, PS One, PS1</code></li>
					<li><code>RPG genre, Role-playing, Role playing</code></li>
					<li><code>Resident Evil series, Biohazard series</code></li>
				</ul>
			</dd>
		';
		echo $rules['kw'][$pgtype];
		?>
		<dd class="form">
			<textarea name="in[keywords]" rows="2" cols="80" tabindex="10" class="ilereturn"><?=(!$in['keywords'] ? $title : $in['keywords'])?></textarea>
		</dd>
	</dl>
	
	<dl id="ile-pgtype">
		<dt>Page Type</dt>
		<dd class="help">
			<div class="warn" style="margin:0 0 3px;font-size:15px;">
				Changing the page type may result in a loss of certain data.
			</div>
		</dd>
		<dd class="form">
			<select name="in[pgtype]">
				<option value="game"<?=($in['pgtype'] == "game" ? ' selected="selected"' : '')?>>Game</option>
				<option value="person"<?=($in['pgtype'] == "person" ? ' selected="selected"' : '')?>>Person</option>
				<option value="category"<?=($in['pgtype'] == "category" ? ' selected="selected"' : '')?>>Category</option>
				<option value="topic"<?=($in['pgtype'] == "topic" ? ' selected="selected"' : '')?>>Topic</option>
			</select>
		</dd>
	</dl>
	
	<?		
	//rep img
	if($in['rep_image']){
		$x = explode("/", $in['rep_image']);
		$repimg = $x[(count($x) - 1)];
	}
	?>
	<dl id="ile-rep_image">
		<dt>Representative Image</dt>
		<dd class="help">An image that best represents this <?=$in['pgtype']?></dd>
		<?=($in['pgtype'] == "game" ? '<dd class="help">The box art for the primary publication will be automatically set as the Representative Image unless you upload a different image below. The only instance when this image shouldn\'t be box art is when there is none available, in which case a logo or artwork will suffice (but a temporary or preliminary box image is much preferred)</dd>' : '')?>
		<dd class="help">
			<a href="http://images.google.com/images?q=<?=urlencode($title)?>" target="_blank" class="arrow-link">Google image search for <i><?=$title?></i></a>
		</dd>
		<dd class="help">
			<span class="warn"></span>Please upload a picture that:
			<ul>
				<li>is at least 150 pixels in width &ndash; The bigger the better</li>
				<li>is in JPG, PNG, or GIF format</li>
				<?=($in['pgtype'] == "person" ? '<li>is a picture of this person; it isn\'t a company logo and preferably doesn\'t have other people in it</li><li>is closely cropped around the person\'s face (tip: use <a href="http://pixlr.com" target="_blank" class="arrow-link">Pixlr</a> to crop pictures quickly)</li>' : '')?>
			</ul>
		</dd>
		<dd>
			<input type="file" name="rep_img"/> 
			<input type="button" value="Upload" class="editpgsubmit" onclick="$('#formaction').val('draft');"/>
			<input type="hidden" name="in[rep_image]" value="<?=$in['rep_image']?>"/>
		</dd>
		<dd>
			<label><input type="checkbox" name="in[rep_image_hide]" value="1"<?=($in['rep_image_hide'] ? ' checked="checked"' : '')?>/>Hide image</label> &nbsp; 
			<?=($in['rep_image'] ? '<label><input type="checkbox" name="rm_rep_image" value="1"'.($_POST['rm_rep_image'] ? ' checked="checked"' : '').'/>Remove current image '.($pgtype == "game" ? ' <a href="#help" class="tooltip" title="Selecting this option will remove the current image and replace it with the box of the primary publication">?</a>' : '').'</label>' : '')?>
		</dd>
	</dl>
	<?
				
	//heading img
	if($in['heading_image']){
		$x = explode("/", $in['heading_image']);
		$hdimg = $x[(count($x) - 1)];
	}
	?>
	<dl id="ile-heading_image">
		<dt>Heading Image</dt>
		<dd>
			<input type="file" name="hd_img"/> 
			<input type="button" value="Upload" class="editpgsubmit" onclick="$('#formaction').val('draft');"/>
			<input type="hidden" name="in[heading_image]" value="<?=$in['heading_image']?>"/>
		</dd>
		<?=($in['heading_image'] ? '<dd><label><input type="checkbox" name="rm_heading_image" value="1"'.($_POST['rm_heading_image'] ? ' checked="checked"' : '').'/>Remove</label> <a href="'.$in['heading_image'].'" rel="shadowbox">current image</a></dd>' : '')?>
	</dl>
	<?
				
	//bg img
	if($in['background_image']){
		$x = explode("/", $in['background_image']);
		$bgimg = $x[(count($x) - 1)];
	}
	?>
	<dl id="ile-background_image">
		<dt>Background Image</dt>
		<dd class="help">
			<span class="warn"></span>Your image must must flush with the background. Therefore, image manipulation (IE Photoshop) is required. A PNG-24 with transparency is recommended.
			<p></p>If you don't have the resources or skill to create a background image but have an image that you think would work well, upload it here and save a draft, then petition someone for help in this pages's discussion secion.
		</dd>
		<dd>
			<input type="file" name="bg_img"/> 
			<input type="button" value="Upload" class="editpgsubmit" onclick="$('#formaction').val('draft');"/>
			<input type="hidden" name="in[background_image]" value="<?=$in['background_image']?>"/>
		</dd>
		<?=($in['background_image'] ? '<dd><label><input type="checkbox" name="rm_background_image" value="1"'.($_POST['rm_background_image'] ? ' checked="checked"' : '').'/>Remove</label> <a href="'.$in['background_image'].'" rel="shadowbox">current image</a></dd>' : '')?>
	</dl>
	
	<dl id="ile-rmpg" style="width:200px;">
		<dt>Remove</dt>
		<dd>
			<label><input type="checkbox" name="rmpg" value="1" <?=($_POST['rmpg'] ? 'checked="checked"' : '')?> onclick="if(!confirm('Are you sure?')) $(this).attr('checked', false);"/>Remove this page</label>
		</dd>
		<?=($_SESSION['user_rank'] >= 8 ? '<dd><label><input type="checkbox" name="rmpgscores" value="1" '.($_POST['rmpgscores'] ? 'checked="checked"' : '').'/>Also nullify all scores given to previous editors of this page</label></dd>' : '')?>
	</dl>
	
</form>
<?