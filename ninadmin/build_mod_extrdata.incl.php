<?

// Extract data from Description

if((string)$pg->data->description){
	if($pg->type == "game"){
		$d = array();
		$d = preg_split('/( for | by | in the)/', $pg->data->description, -1, PREG_SPLIT_OFFSET_CAPTURE);
		
		//genres
		if($d[0][0]){
			unset($pg->data->genres);
			$genres = $pg->data->addChild("genres");
			$links = array();
			$links = extractLinks($d[0][0]);
			foreach($links as $link){
				$i = '[[Category:' . $link['title'] . ']]';
				$genres->addChild("genre", htmlspecialchars($i));
			}
		}
		
		//developers
		if($d[2][0]){
			unset($pg->data->developers);
			$developers = $pg->data->addChild("developers");
			$links = array();
			$links = extractLinks($d[2][0]);
			foreach($links as $link){
				$i = '[[Category:' . $link['title'] . ']]';
				$developers->addChild("developer", htmlspecialchars($i));
			}
		}
		
		//series
		if($d[3][0]){
			unset($pg->data->series);
			$series = $pg->data->addChild("series");
			$links = array();
			$links = extractLinks($d[3][0]);
			foreach($links as $link){
				$i = '[[Category:' . $link['title'] . ']]';
				$series->addChild("game_series", htmlspecialchars($i));
			}
		}
		
		//tweak the links in Description field
		$d = (string)$pg->data->description;
		$d = str_replace('[[[[', '[[', $d); $d = str_replace(']]]]', ']]', $d);
		$links = extractLinks($d);
		foreach($links as $link){
			if($link['title'] === $link['linkwords']) $link['linkwords'] = '';
			$link['namespace'] = '';
			$i = '[[' . $link['title'] . ($link['linkwords'] ? '|'.$link['linkwords'] : '') . ']]';
			$d = str_replace('[['.$link['original'].']]', $i, $d);
		}
		$pg->data->description = htmlspecialchars($d);
		
		/*?><pre><? print_r($pg->data->description); print_r($pg->data->genres); print_r($pg->data->developers); print_r($pg->data->series);*/
	}
	try{ $pg->save(false, true); }
	catch(Exception $e){ die(" Error saving XML file for page '".$pg->title."' ID#".$pg->pgid."; ".$e->getMessage().'<pre>'.htmlspecialchars($pg->data->asXML()).'</pre>'); }
}
?>