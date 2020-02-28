<?
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/page.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.posts.php";
require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/bbcode2markdown2.php";

$min = $_GET['min'];
if(isset($min)){

	$max = 30;
	$nextmin = ($min + $max);
	
	require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/class.ajax.php";
	
	$a = new ajax();
	
	$result = "Processing $min &ndash; $nextmin ...";
	
	$q = "SELECT * FROM posts LIMIT $min, $max";
	$r = mysqli_query($GLOBALS['db']['link'], $q);
	if(!mysqli_num_rows($r)){
		$a->ret['result'] = "Fin.";
		exit;
	}
	while($row = mysqli_fetch_assoc($r)){
		
		$post = new post($row);
		$content = $post->content;
		
		$text = $content['text'];
		$text_intro = $text;
		$more_tag = '<!--more-->';
		$more_tag_custom = '';
		
		$result.= '<dl><dt><a href="'.$post->url.'" target="_blank">'.$post->description.'</a></dt><dd style="white-space:pre-wrap">'.htmlentities($content['text_intro']);
		
		//$result.= $content['text'].'</dd></dl>';continue;
		
		if(strstr($text, "<!--more")){
			preg_match("/<!--more(.*?)-->/", $text, $matches);
			$more_tag = $matches[0];
			if($matches[1]){
				$more_tag_custom = trim(htmlspecialchars($matches[1]));
				$more_tag_custom = substr($more_tag_custom, 0, 30);
			}
			$tarr = array();
			$tarr = explode($more_tag, $text);
			
			//Find any citation references defined after <!--more--> that would be cut off
			$refs = postExtractCitations($text, $tarr[0]);
			if($refs) $refs = "\n" . $refs;
			
			$text_intro = $tarr[0] . $more_tag . $refs;
		}
		
		$intro_maxlen = 3500;
		if(strlen($text_intro) > $intro_maxlen){
			$text_intro = substr($text, 0, $intro_maxlen) . "&hellip;" . $more_tag;
		}
		
		//convert to formatted HTML
		//Markdown only -- special Videogam.in code ([spoiler] etc) and [[page links]] are parsed in realtime when fetched
		$bb = new bbcode($text_intro);
		$bb->params['inline_citations'] = true;
		$text_intro = $bb->markdown(true);
		
		$content['text_intro'] = trim($text_intro);
		
		$cont_str = json_encode($content);
		
		//$result.= htmlentities($cont_str).'</dd></dl>';continue;
		$q = "UPDATE posts SET `content` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $cont_str)."' WHERE nid='$row[nid]' LIMIT 1";
		//$result.= htmlentities($q).'</dd></dl>';continue;
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Critical error: Couldn't update db table [".htmlentities($q)."] ".mysqli_error($GLOBALS['db']['link']);
		
		$q = "INSERT INTO posts_edits (nid, usrid, `comments`, `content`) VALUES ('$row[nid]', 4651, '[BOT] Converting to new markup and Sblog 2.0 formatting (3rd round)', '".mysqli_real_escape_string($GLOBALS['db']['link'], $cont_str)."');";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't update pages_edits db table [".htmlentities($q)."] ".mysqli_error($GLOBALS['db']['link']);
		
		$result.= '<hr/>'.htmlentities($text_intro).'</dd></dl>';
		
	}
	
	//$errors[] = "fuuu";
	
	if(count($errors)){
		$a->ret['haserrors'] = 1;
		$result.= '<b>Errors</b><ol><li>' . implode('</li><li>', $errors) . '</li></ol>';
	} else {
		$result.= 'Success.';
	}
	
	$a->ret['result'] = $result . ' <a onclick="sendform('.$nextmin.')">Next</a>';
	$a->ret['nextmin'] = $nextmin;
	
	exit;
	
}

$page = new page();
$page->title = "Reformat Sblog Texts";
$page->javascript.='
<script type="text/javascript">
$(document).ready(function(){
	$("#forminit").click(function(){
		$(this).hide();
		$("#formresult").show();
		sendform(0);
	});
});
function sendform(min){
	$.get(
		"posts-reformat.php",
		{ min: min },
		function(res){
			$("#formresultspace").html(res.result);
			if(res.nextmin && !res.haserrors) sendform(res.nextmin);
		}
	);
}
</script>
';
$page->header();

?>
<h1><?=$page->title?></h1>

<button id="forminit">Start</button>

<fieldset id="formresult" style="display:none">
	<legend>Result</legend>
	<div id="formresultspace">Initializing...</div>
</fieldset>
<?

$page->footer();




function postExtractCitations($text, $text_piece){
	$regex = '{
				^[ ]{0,3}\[\^(.+?)\][ ]?:	# note_id = $1
				  [ ]*
				  \n?					# maybe *one* newline
				(						# text = $2 (no blank lines allowed)
					(?:					
						.+				# actual text
					|
						\n				# newlines but 
						(?!\[\^.+?\]:\s)# negative lookahead for footnote marker.
						(?!\n+[ ]{0,3}\S)# ensure line is not blank and followed 
										# by non-indented content
					)*
				)		
				}xm';
			preg_match_all($regex, $text, $matches_citations, PREG_SET_ORDER);
			if(count($matches_citations)){
				//print_r($matches_citations);
				foreach($matches_citations as $c){
					//only return cited references
					$r = '{\[\^'.$c[1].'\]}';
					if(preg_match($r, $text_piece)) $ret[] = trim($c[0]);
				}
			}
	if($ret) return implode("\n", $ret);
}