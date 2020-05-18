<?

use Vgsite\Page;
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.pages.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php";

$az = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","0");

if(!$index && $_GET['index']) $index = trim($_GET['index']);
if(!$letter && $_GET['letter']) $letter = substr(trim($_GET['letter']), 0, 1);
if(!$letter) $letter = "0";

if(!$index || !in_array($index, array_keys($pgtypes))) exit;

$query = sprintf(
	"SELECT `json` FROM pages_index_json WHERE `type` = '%s' AND `letter` = '$letter' LIMIT 1",
	mysqli_real_escape_string($GLOBALS['db']['link'], $index)
);
$res = mysqli_query($GLOBALS['db']['link'], $query);
if(!$row = mysqli_fetch_assoc($res)) exit;
$rows = (array) json_decode($row['json']);

if(!count($rows)){
	die('<i class="null">No results.</i>');
}

foreach($rows as $title => $row){
	
	/*$tn = '';
	if($row->rep_image){
		$pos = strrpos($row->rep_image, "/");
		$tn = substr($row->rep_image, 0, $pos) . "/" . ($index == "person" ? "profile_" : "md_") . substr($row->rep_image, ($pos + 1), -3) . "png";
		$tn = '&lt;img src=&quot;'.$tn.'&quot; width=&quot;100&quot; style=&quot;margin:-2px -5px&quot;/&gt;';
	}
	if(!$tn) $tn = htmlSC($title);*/
	
	switch($index){
		case "game":
			
			$o_pf = '&nbsp;';
			if($row->platforms){
				for($i = 0; $i < count($row->platforms); $i++){
					$pf_acr = $pf_acronyms[strtolower($row->platforms[$i])];
					if($pf_acr) $row->platforms[$i] = $pf_acr;
				}
				$o_pf = implode(", ", $row->platforms);
			}
			
			$outp.= '
				<tr>
					<td><a href="'.pageURL($title, "game").'">'.$title.'</a></td>
					<td>'.$o_pf.'</td>
					<td nowrap="nowrap">'.($row->first_release ? str_replace("-00", "", $row->first_release) : '&nbsp;').'</td>
					<td>'.($row->genres ? implode(', ', $row->genres) : '&nbsp;').'</td>
				</tr>
			';
			
			break;
			
		default:
			
			$outp.= '<li><a href="'.pageURL($title, $index).'" title="'.$tn.'" class="tooltip">'.$title.'</a></li>';
			
	}
}

if($index == "game"){
	?>
	<div style="padding:3px; background-color:white;">
		<table border="0" cellpadding="0" cellspacing="0" class="data" style="width:100%;">
			<tr>
				<th class="sortable">Title</th>
				<th class="sortable">Platform</th>
				<th class="sortable">Release</th>
				<th class="sortable">Genre</th>
			</tr>
			<?=links($outp)?>
		</table>
	</div>
	<?
} else {
	?>
	<ul>
		<?=$outp?>
	</ul>
	<?
}

/* ?><pre><? print_r($in); ?></pre><? */
?>