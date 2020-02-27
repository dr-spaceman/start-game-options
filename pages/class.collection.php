<?

require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";
require_once $_SERVER['DOCUMENT_ROOT']."/pages/class.pages.php";

class collection {
	
	function form($title, $in=array()){
		
		global $usrid;
		
		if(!$usrid) return false;
		
		if(!$in){
			$q = "SELECT * FROM collection WHERE `title` = '".mysql_real_escape_string($title)."' AND usrid = '$usrid' LIMIT 1";
			$in = mysql_fetch_assoc(mysql_query($q));
		}
		
		$pg = new pg($title);
		
		if($pg->id){
			$query = "SELECT * FROM games_publications WHERE pgid='$pg->id' ORDER BY release_date";
			$res   = mysql_query($query);
			while($row = mysql_fetch_assoc($res)){
				
			}
		}
		
		$ret = '
		<form class="collection-form" onsubmit="collection.update(); return false;">
			<h6><a href="'.$pg->url.'" target="_blank">'.$title.'</a></h6>
			<div class="shelf>'.$shelf.'</div>
			<div class="form-main">
				<div class="field" id="field-platform">
					<select name="platform" onchange="if($(this).val()==\'\'){ $(this).hide().next().show().focus(); }">';
						foreach($pg->row->index_data['platforms'] as $pf){
							$ret.= '<option value="'.htmlsc($pf).'">'.$pf.'</option>';
						}
						$ret.= '<option value="">&mdash;</option>';
						foreach(getPlatforms() as $pf){
							$ret.= '<option value="'.htmlsc($pf).'">'.$pf.'</option>';
						}
						$ret.= '
						<option value="">&mdash;</option>
						<option value="">other...</option>
					</select>
					<input type="text" name="" value="" onchange="" style="display:none"/>
				</div>
				<div class="field" id="field-ownership">
					<select name="ownership">
						<option value="want">I want it</option>
						<option value="play">I\'ve played it</option>
						<option value="own">I own it</option>
						<option value="own-digital">I own a digital copy</option>
					</select>
				</div>
				<div class="field" id="field-notes">
					<textarea name="notes" placeholder="Notes"></textarea>
				</div>
				<div class="field" id="field-completion">
					<input type="range" onchange="" onmouseover="" onmouseup="" class="slider" id="slider" min="0" max="100" step="1" value="50"></input>
				</div>
				<div class="options"></div>
			</div>
			<button type="submit">'.($in ? 'Submit changes' : 'Add to my collection').'</button>
		</form>';
		
		return $ret;
		
	}
	
	function add($subj){
		
		// var $subj arr what to add to the collection 
		
		global $usrid;
		
	}
}
?>