<?
use Vgsite\Page;
$page = new Page();
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/htmltoolbox.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/contribute.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.forum.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");

if($aact = $_POST['aact']) { //AJAX ACTION
	if($aact == "preview") {
		$x = $_POST['text'];
		$x = str_replace("[AMP]", "&", $x);
		$x = reformatLinks($x);
		if($_SESSION['user_rank'] <= 6) $x = strip_tags($x);
		$x = bb2html($x);
		$x = nl2br($x);
		die($x);
	}
	exit;
}

$page->title = "Videogam.in / Wiki";
$page->style[] = "/bin/css/forums.css";
$page->freestyle = <<<EOF
H3 { margin:0; padding:15px 0 0; border-width:0; }
H3 DIV { margin:0; padding: 10px 15px; background-color:#EEE; font-size:21px; }
UL.nav { height:20px; margin:0; padding:0 0 0 10px; list-style:none; border-bottom:1px solid #CCC; background-color:#EEE; }
.nav LI { margin:0 0 0 5px; padding:0; float:left; }
.nav A { display:block; padding: 2px 8px; border-width:1px 1px 0 1px; border-style:solid; border-color:#DDD; }
.nav LI.on A { text-decoration:none; color:black; background-color:white; border-color:#CCC; }
.notes { clear:both; margin:10px 0; padding:10px; border:1px dashed #CCC; }
.notes UL { margin:0; padding-left:15px; list-style-type:square; }
.notes LI { margin:5px 0 0 0; }
FORM P { margin: 3px 0 0 0; }
#wiki-history { margin-top:3px; border-top:1px solid #CCC; }
#wiki-history TD { padding: 5px 15px 5px 0; border-bottom:1px solid #CCC; }
#forum-body { margin: 0 !important; }
EOF;
$page->javascript.= '<script type="text/javascript" src="/bin/script/wiki.js"></script>';

if($_GET['view_version']) {
	//view an id
	$q = "SELECT * FROM wiki WHERE id='".mysqli_real_escape_string($GLOBALS['db']['link'], $_GET['view_version'])."' LIMIT 1";
	if(!$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) {
		die("Error: Couldn't get wiki data.");
	} else {
		?>
		<?=Page::HTML_TAG?>
		<head>
			<title>Videogam.in Wiki</title>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
			<script type="text/javascript" src="/bin/script/global.js"></script>
			<script type="text/javascript" src="/bin/script/jquery.js"></script>
			<script type="text/javascript" src="/bin/script/thickbox.js"></script>
			<script type="text/javascript" src="/bin/script/tooltip.js"></script>
			<link rel="stylesheet" type="text/css" href="/bin/css/screen.css" media="screen"/>
			<link rel="stylesheet" type="text/css" href="/bin/css/thickbox.css" media="screen"/>
		</head>
		<body style="background-color:white; padding:15px;">
		<?
		$dat->text = bb2html($dat->text);
		$dat->text = reformatLinks($dat->text);
		$dat->text = nl2br($dat->text);
		echo $dat->text;
		?>
		</body>
		</html>
		<?
		exit;
	}
}

if($_GET['compare']) {
	//compare two versions
	$v = explode(",", $_GET['compare']);
	?>
	<?=Page::HTML_TAG?>
	<head>
		<title>Videogam.in Wiki</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
		<script type="text/javascript" src="/bin/script/global.js"></script>
		<script type="text/javascript" src="/bin/script/jquery.js"></script>
		<script type="text/javascript" src="/bin/script/thickbox.js"></script>
		<script type="text/javascript" src="/bin/script/tooltip.js"></script>
		<link rel="stylesheet" type="text/css" href="/bin/css/thickbox.css" media="screen"/>
		<style type="text/css">
			.texts { padding: 30px; }
			.texts DEL { background-color:#FAE4E4; }
			.texts INS { text-decoration:none; background-color:#D5FFD5; }
		</style>
	</head>
	<body style="background-color:white;">
	
	<?
	$q = "SELECT * FROM wiki WHERE id='".mysqli_real_escape_string($GLOBALS['db']['link'], $v[0])."' LIMIT 1";
	if(!$t1 = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Error: Couldn't get wiki data for id # ".$v[0]);
	
	$q = "SELECT * FROM wiki WHERE id='".mysqli_real_escape_string($GLOBALS['db']['link'], $v[1])."' LIMIT 1";
	if(!$t2 = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Error: Couldn't get wiki data for id # ".$v[1]);
	
	require_once $_SERVER['DOCUMENT_ROOT'].'/bin/php/Text_Diff/Diff.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/bin/php/Text_Diff/Diff/Renderer/inline.php';
	
	$lines1 = explode("\n", $t2->text);
	$lines2 = explode("\n", $t1->text);
	
	$diff     = new Text_Diff('auto', array($lines1, $lines2));
	$renderer = new Text_Diff_Renderer_inline();
	$comp = $renderer->render($diff);
	$comp = nl2p($comp);
	$comp2 = bb2html($comp);
	
	?>
	
	<div style="margin:0 0 10px 0; padding:5px 8px; font:normal 18px arial; color:#888; background-color:#EEE; border-bottom:1px solid #AAA;">
		Comparing <?=formatDate($t1->datetime, 10)?> (<?=outputUser($t1->usrid, FALSE)?>) to <?=formatDate($t2->datetime, 10)?> by <?=outputUser($t2->usrid, FALSE)?>
	</div>
	
	<input type="button" value="Toggle formatting" onclick="$('.texts').toggle();"/>
	
	<div class="texts"><?=$comp2?></div>
	<div class="texts" style="display:none; font:normal 13px monospace;"><?=$comp?></div>
	
	</body>
	</html>
	<?
	exit;
}

if(!$subj = urldecode($_GET['subj'])) dieFullpage("Error: No subject given; Can't edit without one.", TRUE);
if(!$usrid) dieFullpage("Please log in to edit this wiki", TRUE);
if(!$pg = $_GET['pg']) $pg = "edit";

list($subj_field, $subj_id, $field) = explode("/", $subj);

//get desc
if($subj_field == "gid" && $field == "synopsis") {
	//game synopsis
	$q = "SELECT title, title_url, unpublished, creator FROM games WHERE gid='$subj_id' LIMIT 1";
	$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
	$link = '/games/'.$subj_id.'/'.$dat->title_url;
	$desc = '[gid='.$subj_id.'/] synopsis';
	$type_id = 2;
	$toolincl = array("b", "i", "big", "small", "strikethrough", "a", "cite", "links");
	$use_bbcode = TRUE;
	$about['cont'] = 'A short (one paragraph) story synopsis or genral description of this game. Please refrain from spoilers, facts, and trivia; the latter two can be submitted as "Trivia" on the game page.';
	$about['format'] = 'The following BB Code tags are allowed: <code>[b] [i] [big] [small] [strike] [url] [cite] [game] [person]</code>.';
	if($dat->unpublished && $dat->creator == $usrid) $auto_pub = TRUE; //auto-publish if game page creator & game's unpublished
} elseif($subj_field == "association") {
	//association
	$link = '/associations/'.urlencode($subj_id);
	$desc = '<a href="'.$link.'">'.$subj_id.'</a> description';
	$type_id = 16;
	$toolincl = array("b", "i", "big", "small", "strikethrough", "a", "cite", "links");
	$use_bbcode = TRUE;
	$about['cont'] = 'General information and a simplified history of the game company or group. Please make sure to link to any people, games, or game series mentioned in your text! Using present- or future-tense language is advised against (for example, don\'t say "Company A is is currently working on Game X.").';
	$about['format'] = 'The following BB Code tags are allowed: <code>[b] [i] [big] [small] [strike] [url] [cite] [game] [person]</code>.';
} elseif($subj_field == "pid" && $field == "biography") {
	//person bio
	$q = "SELECT name, name_url FROM people WHERE pid='$subj_id' LIMIT 1";
	$dat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
	$link = '/people/~'.$dat->name_url;
	$desc = '[pid='.$subj_id.'/] biography';
	$type_id = 13;
	$toolincl = array("b", "i", "big", "small", "strikethrough", "a", "cite", "links");
	$use_bbcode = TRUE;
	$about['cont'] = 'A description of this person\'s experience and influence within the games industry. Keep to a strict definition of "biography", refraining from including current activities and projects; using present- or future-tense language is advised against (for example, don\'t say "Hikuku Yablato is currently working on Game X.")';
	$about['format'] = 'The following BB Code tags are allowed: <code>[b] [i] [big] [small] [strike] [url] [cite] [game] [person]</code>.';
}
	

//submit edit
if($_POST['submit']) {
	$in = $_POST['in'];
	if($in['text']) {
		
		$in['text'] = codedBB($in['text']);
		
		//stip tags for non-staff
		if($_SESSION['user_rank'] <= 5) $in['text'] = strip_tags($in['text']);
		
		$contr = new contribution;
		$contr->type = $type_id;
		$contr->desc = $desc;
		$contr->ssubj = $subj_field.":".$subj_id;
		$contr->data = '{field:}'.$field.'|--|{subject_field:}'.$subj_field.'|--|{subject_id:}'.$subj_id.'|--|{text:}'.$in['text'].'|--|{notes:}'.$in['notes'];
		
		if(!$auto_pub && $_SESSION['user_rank'] <= 3) {
			
			$contr->status = "pend";
			$results[] = 'Your new text has been submitted to the editors for review. <a href="'.$link.'">Go back to the text page</a> or begin a new version below.';
		
		} else {
			
			$contr->status = "publish";
			$contr->subj = "wiki:id:".mysqlNextAutoIncrement("wiki").":";
			$q = sprintf("INSERT INTO wiki (`field`, subject_field, subject_id, `text`, `notes`, usrid, `datetime`) VALUES 
				('%s', '%s', '%s', '%s', '%s', '$usrid', '".date("Y-m-d H:i:s")."');",
				mysqli_real_escape_string($GLOBALS['db']['link'], $field),
				mysqli_real_escape_string($GLOBALS['db']['link'], $subj_field),
				mysqli_real_escape_string($GLOBALS['db']['link'], $subj_id),
				mysqli_real_escape_string($GLOBALS['db']['link'], $in['text']),
				mysqli_real_escape_string($GLOBALS['db']['link'], $in['notes']));
			if(!mysqli_query($GLOBALS['db']['link'], $q)) {
				$errors[] = "Couldn't update wiki database; ".mysqli_error($GLOBALS['db']['link']);
			} else {
				$results[] = 'Your new text has been posted. <a href="'.$link.'">Go back to the text page</a> to see it or begin a new version below.';
			}
			
		}
		
		//give contr points?
		$q = sprintf("SELECT * FROM wiki WHERE usrid='$usrid' AND `field`='%s' AND subject_field='%s' AND subject_id='%s' LIMIT 1",
			mysqli_real_escape_string($GLOBALS['db']['link'], $field),
			mysqli_real_escape_string($GLOBALS['db']['link'], $subj_field),
			mysqli_real_escape_string($GLOBALS['db']['link'], $subj_id));
		if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $contr->no_points = TRUE;
		
		$contr_res = $contr->submitNew();
		
	}
}

$page->header();

if($toolincl) {
	$arr = array();
	$arr = $toolincl;
	$arr = array_diff($arr, array("links"));
	$htmltags = '&lt;'.implode('&gt; &lt;', $arr).'&gt;';
} else {
	$htmltags = 'none!!!';
}

?>
<h3><div><span style="color:#666">Wiki</span> <span style="color:#999">/</span> <?=bb2html($desc)?></div></h3>
<ul class="nav">
	<li<?=($pg == "edit" ? ' class="on"' : '')?>><a href="?subj=<?=$_GET['subj']?>">Edit</a></li>
	<li<?=($pg == "history" ? ' class="on"' : '')?>><a href="?subj=<?=$_GET['subj']?>&pg=history">History</a></li>
	<li<?=($pg == "discussion" ? ' class="on"' : '')?>><a href="?subj=<?=$_GET['subj']?>&pg=discussion">Discussion</a></li>
</ul>
<?

switch($pg) {

case "edit":
	
	if($in['text']) $row[0] = $in['text'];
	else {
		$q = sprintf("SELECT `text` FROM wiki WHERE `field`='%s' AND `subject_field`='%s' AND `subject_id`='%s' ORDER BY `datetime` DESC LIMIT 1",
			mysqli_real_escape_string($GLOBALS['db']['link'], $field),
			mysqli_real_escape_string($GLOBALS['db']['link'], $subj_field),
			mysqli_real_escape_string($GLOBALS['db']['link'], $subj_id));
		$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
	}

	?>
	<div class="notes">
		<big style="font-size:18px">Please review the following rules, tips, and notes:</big>
		<ul>
			<li><b>Writing perspective:</b> Write in third-person and with a neutral, unbiased point-of view.</li>
			<li><b>Copyright:</b> Don't plagiarize or copy anyone else's words unless they are in the public realm (such as Wikipedia or other <a href="http://en.wikipedia.org/wiki/GNU_Free_Documentation_License" target="_blank">GNUFDL</a> source; If you copy from a source like this, you still have to cite it). Your submitted work is copyrighted to you; You are free to use it elsewhere.</li>
			<?=($use_bbcode ? '<li><b>Citing sources:</b> In addition to copied words, any facts taken from another source that are not generally known should be cited. More details on citing are instructed in the <a class="arrow-link" href="javascript:void(0)" onclick="window.open(\'/bbcode.htm\',\'markup_guide_window\',\'width=620,height=510,scrollbars=yes\');">BB Code Guide</a>.</li><li>This form will only accept BB Code; All HTML will be stripped after the form is submitted. See the <a class="arrow-link" href="javascript:void(0)" onclick="window.open(\'/bbcode.htm\',\'markup_guide_window\',\'width=620,height=510,scrollbars=yes\');">BB Code Guide</a> for syntax.'.($_SESSION['user_rank'] >=6 ? '<ul><li>Since you\'re an admin, your text won\'t be stripped of HTML; Use it freely, though BB code is preferred above HTML.</li></ul>' : '').'</li>' : '')?>
		</ul>
		<?
		if($about) {
			?>
			<div>&nbsp;</div><big style="font-size:18px">About this field:</big>
			<ul>
				<?=($about['cont'] ? '<li><b>Appropriate content:</b> '.$about['cont'].'</li>' : '')?>
				<?=($about['format'] ? '<li><b>Formatting allowed:</b> '.$about['format'].'</li>' : '')?>
			</ul>
			<?
		}
		?>
	</div>
	
	<form action="wiki.php?subj=<?=$subj?>" method="post" name="htmlform">
		<input type="hidden" name="in[allow_tags]" value="<?=str_replace(" ", "", $htmltags)?>"/>
		<div id="wiki-edit">
			<?=outputToolbox("wiki-text", $toolincl, $use_bbcode)?>
			<div style="margin-right:6px;"><textarea name="in[text]" rows="20" id="wiki-text" onchange="confirm_exit=true;" style="width:100%"><?=readableBB($row[0])?></textarea></div>
		</div>
		<div id="wiki-preview" style="display:none">
			<fieldset>
				<legend>Preview</legend>
				<div id="wiki-preview-space"><img src="/bin/img/loading-thickbox.gif" alt="loading"/></div>
			</fieldset>
		</div>
		<fieldset style="margin-top:10px;">
			<legend>Edit Summary</legend>
			Please include a summary or notes regarding your update (no HTML):
			<p style="margin-right:6px;"><textarea name="in[notes]" style="width:100%; height:2.6em;"></textarea></p>
		</fieldset>
		<p style="margin-top:10px;">
			<input type="button" value="Preview" id="wiki-button-preview" onclick="wikiPreview(); toggle('wiki-button-edit','wiki-button-preview','inline');"/> 
			<input type="button" value="Edit" id="wiki-button-edit" style="display:none" onclick="toggle('wiki-edit','wiki-preview'); toggle('wiki-button-preview','wiki-button-edit','inline');"/> 
			<input type="submit" name="submit" value="Submit Changes" style="font-weight:bold" onclick="confirm_exit=false;"/>
		</p>
	</form>
	<?

break;

case "history":
	
	?><br/><?
	
	$query = sprintf("SELECT * FROM wiki WHERE `field`='%s' AND `subject_field`='%s' AND `subject_id`='%s' ORDER BY `datetime` DESC",
		mysqli_real_escape_string($GLOBALS['db']['link'], $field),
		mysqli_real_escape_string($GLOBALS['db']['link'], $subj_field),
		mysqli_real_escape_string($GLOBALS['db']['link'], $subj_id));
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	if(!$versions = mysqli_num_rows($res)) {
		echo "No one has submitted anything yet for this wiki.";
	} else {
		if($versions > 1) {
			?><input type="button" value="Compare selected versions" onclick="window.open('/wiki.php?compare='+document.getElementById('v1').value+','+document.getElementById('v2').value,'compare_wiki_window','width=930,height=600');"/><?
		}
		?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%" id="wiki-history">
		<?
		$i = 0;
		while($row = mysqli_fetch_assoc($res)) {
			$v = $versions - $i;
			$i++;
			if($i == 1) {
				$ch1 = ' checked="checked"';
				$field1 = '<input type="hidden" id="v1" value="'.$row['id'].'"/>';
			} else $ch1 = '';
			if($i == 2) {
				$ch2 = ' checked="checked"';
				$field2 = '<input type="hidden" id="v2" value="'.$row['id'].'"/>';
			} else $ch2 = '';
			?>
			<tr>
				<td nowrap="nowrap"><a href="javascript:void(0)" onclick="window.open('/wiki.php?view_version=<?=$row['id']?>','view_wiki_window','width=930,height=600');">Version <?=$v?></a></td>
				<td<?=($versions < 2 ? ' style="display:none"' : '')?>><input type="radio" name="v1" value="<?=$row['id']?>"<?=$ch1?> onclick="document.getElementById('v1').value='<?=$row['id']?>';"/></td>
				<td<?=($versions < 2 ? ' style="display:none"' : '')?>><input type="radio" name="v2" value="<?=$row['id']?>"<?=$ch2?> onclick="document.getElementById('v2').value='<?=$row['id']?>';"/></td>
				<td nowrap="nowrap"><?=formatDate($row['datetime'], 10)?></td>
				<td nowrap="nowrap"><?=outputUser($row['usrid'], FALSE)?></td>
				<td width="100%"><?=$row['notes']?></td>
			</tr>
			<?
		}
		?>
		</table>
		<?=$field1.$field2?>
		<?
	}

break;

case "discussion":
	
	?><br/><?
	$forum = new forum;
	$forum->associate_tag = "wiki:$subj";
	if(!$forum->numberOfPosts()) {
		$suggest = array( "type" => "comments", "title" => "Wiki: ".strip_tags($desc) );
		echo $forum->formToCreate($suggest);
	} else {
		$depreciate_forum_heading = TRUE;
		$forum->showForum();
	}

break;

}

$page->footer();
?>