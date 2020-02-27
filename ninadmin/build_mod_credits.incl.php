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
		
		$q = "SELECT * FROM credits WHERE person = '".mysql_real_escape_string($pg->title)."' AND work = '".mysql_real_escape_string($cname)."' LIMIT 1";
		if(mysql_num_rows(mysql_query($q))){
			$q = "UPDATE credits SET source_person = '1' WHERE person = '".mysql_real_escape_string($pg->title)."' AND work = '".mysql_real_escape_string($cname)."' LIMIT 1";
		} else {
			$q = "INSERT INTO credits (person, work, source_person) values ('".mysql_real_escape_string($pg->title)."', '".mysql_real_escape_string($cname)."', '1');";
		}
		mysql_query($q);
		
		if(substr($cname, 0, 8) == "AlbumID:"){
			$albumid = str_replace("AlbumID:", "", $cname);
			$query = "SELECT title, subtitle FROM albums WHERE albumid = '$albumid' LIMIT 1";
			$res = mysql_query($query);
			if($album = mysql_fetch_assoc($res)){
				$cname.= "|".$album['title'].($album['subtitle'] ? ' - '.$album['subtitle'] : '');
			}
		}
		
		$creditsArr[] = '[['.$cname.']]';
		
	} elseif($pg->type == "game"){
		
		$q = "SELECT * FROM credits WHERE person = '".mysql_real_escape_string($cname)."' AND work = '".mysql_real_escape_string($pg->title)."' LIMIT 1";
		if(mysql_num_rows(mysql_query($q))){
			$q = "UPDATE credits SET source_game = '1' WHERE person = '".mysql_real_escape_string($cname)."' AND work = '".mysql_real_escape_string($pg->title)."' LIMIT 1";
		} else {
			$q = "INSERT INTO credits (person, work, source_game) values ('".mysql_real_escape_string($cname)."', '".mysql_real_escape_string($pg->title)."', '1');";
		}
		mysql_query($q);
		
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