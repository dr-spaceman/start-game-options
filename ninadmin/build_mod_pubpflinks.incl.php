<?

// Categorize publication platform links

if((string)$pg->data->publications->publication[0]->platform){
foreach($pg->data->publications->publication as $p){
	$pf = (string)$p->platform;
	$pf = str_replace("[[Category:", "[[", $pf);
	if(substr($pf, 0, 2) == "[[") $pf = "[[Category:" . substr($pf, 2);
	$p->platform = htmlspecialchars($pf);
}
try{ $pg->save(false, true); }
catch(Exception $e){ die(" Error saving XML file for page '".$pg->title."' ID#".$pg->pgid."; ".$e->getMessage().'<pre>'.htmlspecialchars($pg->data->asXML()).'</pre>'); }
}
?>