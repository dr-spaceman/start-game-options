<?
use Vgsite\Page;
$page = new Page();
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.pages.edit.php");

$keydown_jscript = '
<script type="text/javascript">
	$(document).ready(function(){
		$(document).keydown(function(Ev) {
			var k = Ev.keyCode;
			if(k == 83) { //S
				$("#togglesource").click();
			}
		});
	});
</script>
';

if($sessid = $_GET['view_version']) {
	
	// view an edit version of a page //
	
	$q = "SELECT * FROM pages_edit WHERE session_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $sessid)."' LIMIT 1";
	if(!$pe = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Error: Couldn't get page data for id # ".$sessid);
	
	$title = $pe->title;
	
	$page->title = htmlSC($title)." -- Videogam.in";
	$page->head = "condensed";
	
	$pg = new pg($title);
	$pg->sessid = $sessid;
	try{ $pg->loadData("sessid"); }
	catch(Exception $e){
	  $errors[] = 'There was an error loading data from this session (sessid #'.$sessid.'): <code>'.$e->getMessage().'</code>';
	  $page->footer();
	  exit;
	}
	
	$pg->header();
	
	$message = 'This is a version or draft of the <i>'.$title.'</i> page, as created by '.outputUser($pe->usrid).' on '.$pe->datetime.'. It may differ from the <b><a href="'.pageURL($title, $row['type']).'">current version</a></b>.';
	$message = addslashes($message);
	if($_SESSION['user_rank'] >= 8) $message2 = 'Admin: <a href="/pages/edit.php?destroysession='.$sessid.'&returnonfail='.$titleurl.'" class="red">Destroy this revision</a>';
	
	?>
	<script>
		$.jGrowl('<?=$message?>', {sticky:true});
		<?=($message2 ? "$.jGrowl('$message2', {sticky:true});" : "")?>
	</script>
	<?
	
	$pg->output();
	
	$pg->footer();
	exit;
	
}

if($comp = trim($_GET['compare'])) {
	
	// compare two versions //
	
	$v = explode(",", $comp);
	?>
	<?=Page::HTML_TAG?>
	<head>
		<title>Videogam.in Wiki</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
		<script type="text/javascript" src="/bin/script/global.js"></script>
		<script type="text/javascript" src="/bin/script/jquery.js"></script>
		<script type="text/javascript" src="/bin/script/thickbox.js"></script>
		<script type="text/javascript" src="/bin/script/tooltip.js"></script>
		<?=$keydown_jscript?>
		<link rel="stylesheet" type="text/css" href="/bin/css/thickbox.css" media="screen"/>
		<style type="text/css">
			A { color:#06C; }
			A:HOVER { color:#39F; }
			.texts { padding:30px; font:normal 13px monospace; white-space:pre-wrap; }
			.texts DEL { background-color:#FAE4E4; text-decoration:none; color:#E98787; }
			.texts DEL A { color:#E36666; }
			.texts INS { text-decoration:none; background-color:#D5FFD5; }
		</style>
	</head>
	<body style="background-color:white;">
	
	<?
	
	$q = "SELECT * FROM pages_edit WHERE session_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $v[1])."' LIMIT 1";
	if(!$t2 = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Error: Couldn't get wiki data for id # ".$v[1]);

	if($v[0] == "previous") {
		$q = "SELECT * FROM pages_edit WHERE pgid = '$t2->pgid' AND `datetime` < '$t2->datetime' ORDER BY `datetime` DESC LIMIT 1";
		if(!$t1 = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Error: Couldn't get wiki data for id # ".$v[0]);
		$v[0] = $t1->session_id;
	} else {
		$q = "SELECT * FROM pages_edit WHERE session_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $v[0])."' LIMIT 1";
		if(!$t1 = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) die("Error: Couldn't get wiki data for id # ".$v[0]);
	}
	
	require_once $_SERVER['DOCUMENT_ROOT'].'/bin/php/Text_Diff/Diff.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/bin/php/Text_Diff/Diff/Renderer/inline.php';
	
	if(!$lines1 = file_get_contents("xml/drafts/".$v[0].".xml")) die("Error: Couldn't get XML data for id # ".$v[0]);
	if(!$lines2 = file_get_contents("xml/drafts/".$v[1].".xml")) die("Error: Couldn't get XML data for id # ".$v[1]);
	$lines1 = explode("\n", $lines1);
	$lines2 = explode("\n", $lines2);
	
	$diff     = new Text_Diff('auto', array($lines1, $lines2));
	$renderer = new Text_Diff_Renderer_inline();
	$comp = $renderer->render($diff);
	$comp = preg_replace("@&lt;(filename|background_image)&gt;(.*?)&lt;/(filename|background_image)&gt;@", '&lt;\1&gt;<a href="\2" target="_blank">\2</a>&lt;/\1&gt;', $comp);
	$comp2 = preg_replace("@&lt;profile_picture&gt;(.*?)&lt;/profile_picture&gt;@", '&lt;profile_picture&gt;<img src="\1"/>&lt;/profile_picture&gt;', $comp);
	$comp2 = bb2html($comp2);
	
	?>
	
	<div style="margin:0 0 10px 0; padding:5px 8px; font:normal 18px arial; color:#888; background-color:#EEE; border-bottom:1px solid #AAA;">
		Comparing <?=formatDate($t2->datetime, 10)?> (<?=outputUser($t2->usrid, FALSE)?>) to <?=formatDate($t1->datetime, 10)?> (<?=outputUser($t1->usrid, FALSE)?>)
	</div>
	
	<input type="button" value="Show Source" id="togglesource" onclick="$('.texts').toggle(); if($(this).val() == 'Show Source') $(this).val('Toggle Source');"/> 
	Press <b>S</b> key
	
	<div class="texts"><?=$comp2?></div>
	<div class="texts" style="display:none;"><?=$comp?></div>
	
	</body>
	</html>
	<?
	exit;
}

if($_GET['title']){
	
	// PAGE HISTORY //
	
	if(!$title = formatName($_GET['title'])) die("An error occurred when trying to decipher page title");
	
	$q = "SELECT * FROM pages WHERE `title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."' LIMIT 1";
	if(!$pgrow = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) $pgrow = array("title" => $title);
	
	if(!$title) $title = $pgrow['title'];
	$titleurl = formatNameURL($title);
	
	$page->title = htmlSC($title)." Edit History -- Videogam.in";
	$page->freestyle = '
		#wiki-history { border-top:1px solid #BBB; }
		#wiki-history td { padding:5px 15px 5px 5px; border-bottom:1px solid #DDD; }
	';
	$page->javascript.= '
		<script type="text/javascript">
		$(document).ready(function(){
			$("#wiki-history :input.histcomp").click(function(){
				$("#"+$(this).attr("name")).val($(this).val());
			});
			
			$("#wiki-history :radio").click(function(){
				var nm = $(this).attr("name");
				var indx = $("#wiki-history :radio[name=\'"+nm+"\']").index(this);
				
				if(nm == "v1") {
					$("#wiki-history :radio[name=\'v2\']").hide();
					$("#wiki-history :radio[name=\'v2\']:lt("+indx+")").show();
				} else if(nm == "v2") {
					$("#wiki-history :radio[name=\'v1\']").hide();
					$("#wiki-history :radio[name=\'v1\']:gt("+indx+")").show();
				}
				
			});
		});
		</script>
	';
	
	$ed = new pgedit($title);
	$ed->header();
	
	?>
	<div id="pgedhistory" class="pgedbg">
		<?
	
		$query = "SELECT * FROM pages_edit WHERE `title`='".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."' ORDER BY `datetime` DESC;";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		if(!$versions = mysqli_num_rows($res)) {
			echo "No edits yet recorded.";
		} else {
			if($versions > 1) {
				?>
				<div style="padding:5px;">
					<input type="button" value="Compare selected revisions" onclick="window.open('history.php?compare='+$('#v1').val()+','+$('#v2').val(),'compare_wiki_window','width=930,height=600,scrollbars=yes');"/>
				</div>
				<?
			}
			?>
			<table border="0" cellpadding="0" cellspacing="0" width="100%" id="wiki-history">
			<?
			$i = 0;
			while($row = mysqli_fetch_assoc($res)) {
				
				//dont show the row if it's a draft and doesn't belong to the viewer
				//if(!$row['published'] && $row['usrid'] != $usrid) continue;
				
				$draft = (!$row['published'] ? '<span class="red">Draft</span> &nbsp; ' : '');
				
				$v = $versions - $i;
				$i++;
				if($i == 2) {
					$ch1 = ' checked="checked"';
					$field1 = '<input type="hidden" id="v1" value="'.$row['session_id'].'"/>';
				} else $ch1 = '';
				if($i == 1) {
					$ch2 = ' checked="checked"';
					$field2 = '<input type="hidden" id="v2" value="'.$row['session_id'].'"/>';
				} else $ch2 = '';
				$bytech = $row['new_len'] - $row['old_len'];
				$bytech_color = "#888";
				if($bytech < 0) $bytech_color = "#E12B2B";
				elseif($bytech > 0) $bytech_color = "#37B93E";
				$bytech_fw = "normal";
				if($bytech > 500 || $bytech < -500) $bytech_fw = "bold";
				
				$rev = "";
				if($row['reverted_from']) {
					$q = "SELECT datetime FROM pages_edit WHERE session_id='".$row['reverted_from']."' LIMIT 1";
					$revdat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
					$rev = '<i style="color:#888;">Reverted from <a href="history.php?view_version='.$row['reverted_from'].'">'.formatDate($revdat->datetime, 10).'</a></i> &nbsp; ';
				}
				
				if($row['redirect_to']) {
					$row['edit_summary'] = '<i style="color:#888;">Redirected to [['.$row['redirect_to'].']].</i> '.$row['edit_summary'];
				}
				
				if($row['rename']) {
					$row['edit_summary'] = '<i style="color:#888;">Renamed [['.($row['rename']).']].</i> '.$row['edit_summary'];
				}
				
				if($row['removed']) {
					$row['edit_summary'] = '<i style="color:#888;">Removed page.</i> '.$row['edit_summary'];
				}
				
				?>
				<tr>
					<td nowrap="nowrap"><a href="history.php?view_version=<?=$row['session_id']?>"><?=formatDate($row['datetime'], 10)?></a></td>
					<?
					if($versions > 1) {
						?>
						<td style="padding:0;" nowrap="nowrap">&nbsp;<input type="radio" name="v1" value="<?=$row['session_id']?>"<?=$ch1?> class="histcomp"<?=($i == 1 ? ' style="display:none;"' : '')?>/>&nbsp;</td>
						<td style="padding:0;" nowrap="nowrap"><input type="radio" name="v2" value="<?=$row['session_id']?>"<?=$ch2?> class="histcomp"<?=($i > 1 ? ' style="display:none;"' : '')?>/>&nbsp;&nbsp;</td>
						<?
					}
					?>
					<td><abbr title="<?=number_format($row['new_len'])?> bytes [<?=$row['score']?>]" style="font-weight:<?=$bytech_fw?>; color:<?=$bytech_color?>;"><?=($bytech > 0 ? "+" : '').$bytech?></abbr></td>
					<td nowrap="nowrap"><?=outputUser($row['usrid'], FALSE)?></td>
					<td><a href="edit.php?title=<?=$titleurl?>&editsource=<?=$row['session_id']?>" title="Begin a new edit session using the data from this version" class="revert tooltip" style="<?=($i == 1 && !$draft ? 'display:none;' : '')?>">revert</a></td>
					<td width="100%"><?=$draft.$rev.links($row['edit_summary'])?></td>
				</tr>
				<?
			}
			?>
			</table>
			<?=$field1.$field2?>
			<?
		}
		?>
		
	</div><!-- #pgedhistory -->
	
	<?
	$ed->footer();
	exit;
	
}

// INDEX //

$chlisttype = "recent";
include("changelist.php");

$page->title = "Recent Changes -- Videogam.in";

unset($page->css); //conflict

$page->header();

?>
<h1>Recent Changes</h1>

<div id="chswitch" style="margin:0 0 5px; font-size:14px;">
	<span style="color:#888;">Show changes since</span> &nbsp;
	<?
	if($usrid) {
		?>
		<a href="?since=lastlogin" class="arrow-toggle<?=($since == "lastlogin" ? ' arrow-toggle-on' : '')?>"><acronym title="<?=timeSince($usrlastlogin)?> ago">your last login</acronym></a> &nbsp;
		<?
	}
	?>
	<a href="?since=3" class="arrow-toggle<?=($since == 3 ? ' arrow-toggle-on' : '')?>">3 days</a> &nbsp;
	<a href="?since=7" class="arrow-toggle<?=($since == 7 ? ' arrow-toggle-on' : '')?>">7 days</a> &nbsp;
	<a href="?since=14" class="arrow-toggle<?=($since == 14 ? ' arrow-toggle-on' : '')?>">14 days</a> &nbsp;
	<a href="?since=30" class="arrow-toggle<?=($since == 30 ? ' arrow-toggle-on' : '')?>">30 days</a>
</div>
<?

if(!$changes) {
	echo 'No changes made '.(!$since || $since=="lastlogin" ? 'since your last login '.timeSince($usrlastlogin).' ago.' : 'in the past '.$since.' days.');
} else {
	?>
	<table border="0" cellpadding="0" cellspacing="0" id="wiki-history">
		<?
		echo links(implode("", $changes));
		//echo count($changes);
		//print_r($changes);
		//$pr_changes = implode("", $changes);
		//echo bb2html($pr_changes);
		?>
		<tr>
			<td colspan="2" style="border-top:2px solid #AAA; padding-right:0;">
				<b><?=$i?> changes</b>
				<?
				if($i > $numchanges) {
					$numpages = $i / $limit;
					$numpages = ceil($numpages);
					?>
					 &middot; Page <?=$pgnum?> of <?=$numpages?> &nbsp;&nbsp; 
					<span style="color:#BBB;">
						<?=($pgnum > 1 ? '<a href="?since='.$_GET['since'].'&page='.($pgnum - 1).'" class="arrow-left">Previous</a>' : '<span class="arrow-left">Previous</span>')?> &nbsp; 
						<?=($pgnum < $numpages ? '<a href="?since='.$_GET['since'].'&page='.($pgnum + 1).'" class="arrow-right">Next</a>' : '<span class="arrow-right">Next</span>')?>
					</span>
					<?
				}
				?>
				&nbsp;
			</td>
		</tr>
	</table>
	<?
}

$page->footer();