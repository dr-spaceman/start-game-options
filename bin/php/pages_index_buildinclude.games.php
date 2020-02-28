<?

//Update games_publications and credits tables with data given from $pg
//ie $pg = new pgedit("Super Mario Bros."); $pg->loadData(); include("pages_index_buildinclude.games.php");

if($pg->type == "game" && $pg->pgid){
	
	$q = "DELETE FROM credits WHERE work = '".mysqli_real_escape_string($GLOBALS['db']['link'], $pg->title)."' AND source_game = 1 AND source_person = 0 AND source_album = 0;";
	mysqli_query($GLOBALS['db']['link'], $q);
	$q = "UPDATE credits SET source_game = 0 WHERE work = '".mysqli_real_escape_string($GLOBALS['db']['link'], $pg->title)."';";
	mysqli_query($GLOBALS['db']['link'], $q);
	if((string)$pg->data->credits){
		$pglinks = new pglinks();
		$pglinks->regex = '@::\s*\[\[('.implode(':|', $GLOBALS['pgnamespaces']).')?:?(.*?)\]\]@ise';
		if($exlinks = $pglinks->extractFrom((string)$pg->data->credits)){
			$queries = array();
			foreach($exlinks as $link){
				$q = "SELECT * FROM credits WHERE work = '".mysqli_real_escape_string($GLOBALS['db']['link'], $pg->title)."' AND person = '".mysqli_real_escape_string($GLOBALS['db']['link'], $link['tag'])."' LIMIT 1";
				if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))){
					$q = "UPDATE credits SET source_game = 1 WHERE person = '".mysqli_real_escape_string($GLOBALS['db']['link'], $link['tag'])."' AND work = '".mysqli_real_escape_string($GLOBALS['db']['link'], $pg->title)."'";
					mysqli_query($GLOBALS['db']['link'], $q);
				} else {
					$queries[] = "('".mysqli_real_escape_string($GLOBALS['db']['link'], $link['tag'])."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $pg->title)."', '1')";
				}
			}
			if($queries){
				$query = "INSERT INTO credits (person, work, source_game) VALUES ".implode(",", $queries);
				mysqli_query($GLOBALS['db']['link'], $query);
			}
		}
	}
	
	$q = "DELETE FROM games_publications WHERE pgid='$pg->pgid'";
	mysqli_query($GLOBALS['db']['link'], $q);
	if($pubs = ($pg->data->publications ? $pg->data->publications->children() : array())){
		
		$q = "INSERT INTO games_publications (`pgid`,`title`,`release_title`,`platform`,`region`,`publisher`,`img_name`,`img_name_title_screen`,`img_name_logo`,`distribution`,`release_date`,`release_date_tentative`,`primary`) VALUES ";
		foreach($pubs as $pub){
			$primary = $pub['primary'] ? '1' : '0';
			$pglinks = new pglinks();
			$pf = (string)$pub->platform;
			if($exlinks = $pglinks->extractFrom((string)$pub->platform)){
				$pf = $exlinks[0]['tag'];
			}
			if((string)$pub->publisher && $exlinks = $pglinks->extractFrom((string)$pub->publisher)){
				$pub->publisher = $exlinks[0]['tag'];
			}
			if((string)$pub->release_tentative){
				$release_date = $pub->release_year."-12-31";
				$release_date_tentative = '1';
			} else {
				$release_date = $pub->release_year."-".$pub->release_month."-".$pub->release_day;
				$release_date_tentative = '0';
			}
			
			$q.= "('$pg->pgid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $pg->title)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $pub->title)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $pf)."', '".$pf_regions[(string)$pub->region]."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $pub->publisher)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $pub->img_name)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $pub->img_name_title_screen)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $pub->img_name_logo)."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $pub->distribution)."', ".($release_date ? "'$release_date'" : "NULL").", '$release_date_tentative', '$primary'),";
		}
		$q = substr($q, 0, -1);
		if(!mysqli_query($GLOBALS['db']['link'], $q)) trigger_error("Couldn't update publications index; ".mysqli_error($GLOBALS['db']['link']), E_USER_ERROR);
	}
	
}

?>