<?

// Rebuild Credits structure
// ie: old structure: <credits><credit><roles><role><credited_role/><notes/></role></roles></credit></credits> (there were notes for each role)
//     new structure: <credits><credit><roles><role/></roles><notes/></credit></credits> (one note for each person/game)
//     newer structure:
//       game credits: ; Game Design :: [[Shigeru Miyamoto]] :: [[Gunpei Yokoi]]
//       person credits: [[Donkey Kong]] [[Super Mario Bros.]] [[AlbumID:SVWC-7191]]

$creditsArr = array();

if((string)$pg->data->credits->credit[0]){
/*?><table border><tr><td valign="top"><pre><? print_r($pg->data->credits); echo htmlentities($pg->data->credits->asXML()); ?></pre></td><?*/
foreach($pg->data->credits->credit as $c){
	
	$cname = str_replace("[[", "", (string)$c->name);
	$cname = str_replace("]]", "", $cname);
	$cname = trim($cname);
	if($pos = strpos($cname, "|")) $cname = substr($cname, 0, $pos);
	
	if($pg->type == "person"){
		
		$q = "SELECT * FROM credits WHERE person = '".mysqli_real_escape_string($GLOBALS['db']['link'], $pg->title)."' AND work = '".mysqli_real_escape_string($GLOBALS['db']['link'], $cname)."' LIMIT 1";
		if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))){
			$q = "UPDATE credits SET source_person = '1' WHERE person = '".mysqli_real_escape_string($GLOBALS['db']['link'], $pg->title)."' AND work = '".mysqli_real_escape_string($GLOBALS['db']['link'], $cname)."' LIMIT 1";
		} else {
			$q = "INSERT INTO credits (person, work, source_person) values ('".mysqli_real_escape_string($GLOBALS['db']['link'], $pg->title)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $cname)."', '1');";
		}
		mysqli_query($GLOBALS['db']['link'], $q);
		
		if(substr($cname, 0, 8) == "AlbumID:"){
			$albumid = str_replace("AlbumID:", "", $cname);
			$query = "SELECT title, subtitle FROM albums WHERE albumid = '$albumid' LIMIT 1";
			$res = mysqli_query($GLOBALS['db']['link'], $query);
			if($album = mysqli_fetch_assoc($res)){
				$cname.= "|".$album['title'].($album['subtitle'] ? ' - '.$album['subtitle'] : '');
			}
		}
		
		$creditsArr[] = '[['.$cname.']]';
		
	} elseif($pg->type == "game"){
		
		$q = "SELECT * FROM credits WHERE person = '".mysqli_real_escape_string($GLOBALS['db']['link'], $cname)."' AND work = '".mysqli_real_escape_string($GLOBALS['db']['link'], $pg->title)."' LIMIT 1";
		if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))){
			$q = "UPDATE credits SET source_game = '1' WHERE person = '".mysqli_real_escape_string($GLOBALS['db']['link'], $cname)."' AND work = '".mysqli_real_escape_string($GLOBALS['db']['link'], $pg->title)."' LIMIT 1";
		} else {
			$q = "INSERT INTO credits (person, work, source_game) values ('".mysqli_real_escape_string($GLOBALS['db']['link'], $cname)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $pg->title)."', '1');";
		}
		mysqli_query($GLOBALS['db']['link'], $q);
		
		foreach($c->roles->role as $r){
			$creditsArr[(string)$r][] = (string)$c->name;
		}
		
	}
	
	/*$notes = '';
	$_roles = array();
	foreach($c->roles->role as $r){
		foreach($r as $k => $v){
			//echo '[K:'.$k.'; V:'.$v.']';
			if($k == "credited_role") $_roles[] = (string)$v;
			if($k == "notes") $notes = (string)$v;
		}
	}*/
	//echo '[ROLES:]'; print_r($_roles);
	/*unset($c->roles);
	$roles = $c->addChild("roles");
	foreach($_roles as $role) $roles->addChild("role", htmlspecialchars((string)$role));
	if($notes && !$c->notes) $c->addChild("notes", htmlspecialchars((string)$notes));*/
}
/*?><td valign="top"><pre><? print_r($pg->data->credits); echo htmlentities($pg->data->credits->asXML()); ?></pre></td></tr></table><?*/

if($pg->type == "person"){
	unset($pg->data->credits);
	$credits_list = $pg->data->addChild("credits_list");
	foreach($creditsArr as $cred){
		$credits_list->addChild("credit", htmlspecialchars($cred));
	}
} elseif($pg->type == "game"){
	$pg->data->credits = '';
	foreach($creditsArr as $role => $names){
		$pg->data->credits.= '; '.$role.' :: ' . implode(" :: ", $names)."\n";
	}
	$pg->data->credits = trim($pg->data->credits);
}

try{ $pg->save(false, true); }
catch(Exception $e){ die(" Error saving XML file for page '".$pg->title."' ID#".$pg->pgid."; ".$e->getMessage().'<pre>'.htmlspecialchars($pg->data->asXML()).'</pre>'); }
}
?>