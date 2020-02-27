<?

function makeUrlStr($str) {
	$str = trim($str);
	$str = strtolower($str);
	$str = str_replace(" ", "-", $str);
	$str = preg_replace("/[^a-z0-9-_\.]/", "", $str);
	$str = preg_replace("/-+/", "-", $str); //replace more than 1 consecutive -
	return $str;
}

function adminAction($subj="", $note="") {
	global $db, $usrid;
	$q = sprintf("INSERT INTO admin_changelog (usrid, `subject`, `notes`, datetime) VALUES ('$usrid', '%s', '%s', NOW())",
		mysql_real_escape_string($subj),
		mysql_real_escape_string($note));
	if(!mysql_query($q)) $errors[] = "Couldn't record to changelog";
}

function deleteDirectory($dir, $backup=FALSE) {
  if (substr($dir,-1) != "/") $dir .= "/";
  if (!is_dir($dir)) return false;
  if (($dh = opendir($dir)) !== false) {
   while (($entry = readdir($dh)) !== false) {
     if ($entry != "." && $entry != "..") {
       if (is_file($dir . $entry) || is_link($dir . $entry)) {
       	 if($backup) {
       	 	 $bu = str_replace($_SERVER['DOCUMENT_ROOT'], "", $dir);
       	 	 $bu = $_SERVER['DOCUMENT_ROOT']."/bin/deleted-files".$bu;
       	 	 if(!is_dir($bu)) mkdir($bu, 0777);
         	 copy($dir.$entry, $bu.$entry);
         }
         unlink($dir.$entry);
       }
       else if (is_dir($dir.$entry)) deleteDirectory($dir.$entry);
     }
   }
   closedir($dh);
   rmdir($dir);

   return true;
  }
  return false;
}

function saveDraftButton($field, $filename='') {
	global $draft_occ;
	$draft_occ++;
	if(!$filename) $filename = "untitled_".date("Y-m-d_His");
	$ret = '<img src="/bin/img/icons/folder_add.png" alt="drafts"/> 
	<input type="button" value="Save a copy to your drafts" id="save-draft-button-'.$draft_occ.'" onclick="saveDraft(\''.$draft_occ.'\', \''.$field.'\', \''.$filename.'\', \''.date("D j M Y H:i:s T").'\');"/> 
	<span id="draft-last-saved-'.$draft_occ.'"></span>';
	return $ret;
}