<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
$page = new page;
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.pages.edit.php");

do if($_POST){
	
	if(!$_POST['handle']){ $errors[] = "No handle data passed"; break; }
	
	parse_str(base64_decode($_POST['handle']), $handle);
	
	if(!$title = $handle['title']){ $errors[] =  "No title given"; break; }
	$title = formatName($title);
	$title = ucfirst($title);
	
	$ed = new pgedit($title);
	try{ $ed->loadData(); } // populates $ed->data
	catch(Exception $e){ $errors[] = 'There was an error loading data from the current version of this page: <code>' . $e->getMessage() . '</code>'; break; }
	
	$len = filesize($_SERVER['DOCUMENT_ROOT']."/pages/xml/".$ed->pgid.".xml");
	
	$dt = date("Y-m-d H:i:s");
	
	$q = "INSERT INTO pages_edit (pgid, `title`, session_id, usrid, old_len, new_len, edit_summary, `datetime`) VALUES ('".$ed->pgid."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."', '$ed->sessid', '$usrid', '$len', '$len', '".mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['edit_summary'])."', '$dt')";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't record edit session in the database";
	
	if($_POST['rmpg']){
		
		// remove pg //
		
		$q = "UPDATE pages_edit SET removed = '1', `new_len` = '0' WHERE session_id='$ed->sessid' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't record edit summary [ERROR RMUPD03]";
		
		$q = "DELETE FROM pages WHERE title='".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't remove page because of a database error: ".$q;
		
		@mail(getenv('NOTIFICATION_EMAIL'), "[Videogam.in] Remove page alert", "$usrname has deleted $title (http://videogam.in".$ed->url.").");
		
	} else {
		
		$title_sort = $_POST['title_sort'] ? $_POST['title_sort'] : formatName($ed->title, "sortable");
		$title_sort = strtolower($title_sort);
		if($ed->row->title_sort != $title_sort){
			$q = "UPDATE pages SET title_sort = '".mysqli_real_escape_string($GLOBALS['db']['link'], $title_sort)."' WHERE pgid='$ed->pgid' LIMIT 1";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = 'Couldn\'t update Sort Title';
		}
		
		if(!in_array($_POST['pgtype'], array_keys($GLOBALS['pgtypes']))) $errors[] = 'Invalid page type given ('.$_POST['pgtype'].')';
		elseif($_POST['pgtype'] != $ed->type){
			
			// new page type //
			
			// rm pg from old index
			
			$xmlf = $_SERVER['DOCUMENT_ROOT']."/pages/xml/index/".$ed->type.".xml";
			$index = simplexml_load_file($xmlf);
			
			$index_dom = new DOMDocument('1.0', 'UTF-8');
			$index_dom->xmlStandalone = false;
			$index_dom->preserveWhiteSpace = false;
			$index_dom->formatOutput = true;
			$index_dom->loadXML($index->asXML());
			
			$xpath = new DOMXpath($index_dom);
			$nodei = 0;
			foreach($xpath->query($ed->type.'[@pgid="'.$ed->pgid.'"]') as $node){
				$node->parentNode->removeChild($node);
			}
			
			if(!$index_dom->save($xmlf)) $error[] = "Error saving index XML file; This page was not removed from the old index ($ed->type)";
			
			$q = "SELECT `json` FROM pages_index_json WHERE `type`='".$ed->type."' LIMIT 1";
			if(!$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) $errors[] = "Error selecting JSON index; This page was not removed from the old index ($ed->type)";
			else {
				$json_blob = json_decode($row['json'], true);
				unset($json_blob[$ed->title]);
				$json_str = json_encode($json_blob);
				$q = "UPDATE pages_index_json SET `json` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $json_str)."' WHERE `type` = '".$ed->type."' LIMIT 1";
				if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Error updating JSON index; This page was not removed from the old index ($ed->type)";
			}
			
			// update new index
			
			$ed->type = $_POST['pgtype'];
			$ed->data['type'] = $_POST['pgtype'];
			
			$q = "UPDATE pages SET `type` = '".$_POST['pgtype']."' WHERE `title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $title)."' LIMIT 1";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't update database with new page type";
			
			//...we'll rebuild the new index below
			
		}
	}
	
	try{ $ed->save(false, true); }
	catch(Exception $e){ $errors[] = "Couldn't save base data file (".$e->getMessage().")\n"; }
	
	$q = "UPDATE pages_edit SET `published` = '1' WHERE session_id='$ed->sessid' LIMIT 1";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't record edit (Your changes were probably saved though) [ERROR SETP01]";
	
	//rebuild index and then go to the page overview
	echo $html_tag;
	$pgurl = pageURL($title, $ed->type, true);
	?>
	<script type="text/javascript">
		function goToPg(){
			window.location = '<?=$pgurl?>';
		}
	</script>
	<iframe src="/bin/php/pages_index_build.include.php?_type=<?=$ed->type?>&_onFin=<?=(!$errors ? 'goToPg' : '')?>" frameborder="0" style="width:500px; height:500px;"></iframe>
	<?
	exit;
	
} while(false);

$title    = formatName($_GET['title']);
$titleurl = formatNameURL($title);

$page->title = htmlSC($title) . " [CONFIG] -- Videogam.in";

$page->javascript.= <<<eof
<script type="text/javascript">
	function checkConfigForm(){
		if($("#edit_summary").val() == ''){ alert("Please include an Edit Summary in the field provided with a short note about your configuration changes."); return false; }
	}
</script>
eof;

try {
	
	$ed = new pgedit($title);
	$ed->header();

	if(!$usrid) $page->die_('<big style="font-size:150%;">Please <a href="/login.php" class="prompt">Log In</a> to continue.</big><br/><br/>Don\'t have an account? <a href="/register.php">Register</a> in about a minute.');
	
	if($usrrank < 4) $page->die("Access denied");
	
	if(!$ed->row){
		
		$page->die_('This page hasn\'t been started yet.');
	
	} else {
		
		// Fresh edit of an established page
		// Use current version as the basis
		
		try{ $ed->loadData(); } // populates $ed->data
		catch(Exception $e){ $page->die_('There was an error loading data from the current version of this page: <code>' . $e->getMessage() . '</code>'); }
		
	}
	
	?>
	<div id="pgconfig" class="pgedbg" style="padding:20px 40px;">
		
		<form action="configure.php?title=<?=$titleurl?>" method="post" name="pgConfigs" onsubmit="return checkConfigForm()">
			
			<?=$ed->formHandle()?>
		
			<dl>
				<dt>Page Type</dt>
				<dd>
					<select name="pgtype">
						<option value="game"<?=($ed->type == "game" ? ' selected="selected"' : '')?>>Game</option>
						<option value="person"<?=($ed->type == "person" ? ' selected="selected"' : '')?>>Person</option>
						<option value="category"<?=($ed->type == "category" ? ' selected="selected"' : '')?>>Category</option>
						<option value="topic"<?=($ed->type == "topic" ? ' selected="selected"' : '')?>>Topic</option>
					</select>
				</dd>
				
				<dt>Sort Title</dt>
				<dd class="help">Set it to blank to automatically generate a sort title</dd>
				<dd>
					<textarea name="title_sort" rows="1" cols="50"><?=$ed->row->title_sort?></textarea>
				</dd>
				
				<dt>Remove</dt>
				<dd>
					<a href="#rmpg" class="red preventdefault" onclick="$(this).hide().next().show()">Remove this page</a>
					<div style="display:none">Please confirm that you would like to remove this page by checking this box: <input type="checkbox" name="rmpg" value="1"/></div>
				</dd>
			</dl>
			
			<fieldset id="editsummary" style="margin:20px 0 0; padding:8px 15px 15px;">
				<legend>Edit Summary</legend>
				Please briefly summarize your changes, making clear your intention and purpose for editing. This will help keep better records and allow the editors and future contributors to better understand your contributions.
				<div style="margin-top:5px; margin-right:5px;">
					<textarea name="edit_summary" rows="2" id="edit_summary" style="width:100%; background-color:#F5F5F5;"><?=$_POST['edit_summary']?></textarea>
				</div>
			</fieldset>
			
			<div style="margin:20px 0 0;"></div>
			
			<input type="submit" value="Submit" style="font-weight:bold;"/>
			
		</form>
		
	</div><!--#pgconfig-->
	
	<?
	
	$ed->footer();
	
} catch(Exception $e){
  echo 'Error :'.$e->getMessage();
  $page->footer();
  exit;
}
