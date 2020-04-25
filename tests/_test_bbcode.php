<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
?><!DOCTYPE html>
<html dir="ltr" lang="en-US" xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="/bin/css/screen.css" media="screen"/>
        <script type="text/javascript" src="/bin/script/Markdown.Converter.js"></script>
        <script type="text/javascript" src="/bin/script/Markdown.Sanitizer.js"></script>
        <script type="text/javascript" src="/bin/script/Markdown.Editor.custom.js"></script>
</head>
<body>
<section>
<?

if($_POST['q']){
	$t = $_POST['parse'] ? parseText($_POST['q']) : $_POST['q'];
	if($_POST['bbcode2markdown']){
		require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode2markdown.php");
		$t = bbcode2markdown($t);
		$md = $t;
	}
	$bb = new bbcode();
	$bb->text = $t;
	$bb->headings_offset = 1;
	
	if($_POST['markdown_only']){
		$t = Markdown($t);
	} elseif($_POST['html2markdown']){
		$t = $bb->html2bb();
	} else {
		$t = $bb->bb2html();
	}
}
?>
<form action="test_bbcode.php" method="post">
<div id="fuu-toolbar"></div>
<textarea name="q" rows="13" class="wmd-input" id="fuu" style="width:100%"><?
if($_POST['q']) echo $_POST['q'];
else echo '*foo* **bar**';
?></textarea>
<p></p>
<input type="submit" value="Test code"/> 
<label><input type="checkbox" name="parse" value="1" <?=($_POST['parse'] ? 'checked="checked"' : '')?>/> Parse</label> &nbsp; 
<label><input type="checkbox" name="bbcode2markdown" value="1" <?=($_POST['bbcode2markdown'] ? 'checked="checked"' : '')?>/> bbcode2markdown</label> &nbsp; 
<label><input type="checkbox" name="html2markdown" value="1" <?=($_POST['html2markdown'] ? 'checked="checked"' : '')?>/> HTML2markdown</label> &nbsp; 
<label><input type="checkbox" name="markdown_only" value="1" <?=($_POST['markdown_only'] ? 'checked="checked"' : '')?>/> Markdown only</label> &nbsp; 
<input type="checkbox" name="tidyHtml" value="1" <?=($_POST['tidyHtml'] ? 'checked="checked"' : '')?>/> tidyHtml</label> &nbsp; 
</form>
<p></p>
<fieldset><legend>Live Preview (Markdown only)</legend><div id="fuu-preview"></div></fieldset>
	<script>
		      var elId = "fuu",
	    	      converter = new Markdown.Converter(),
	            help = function () { window.open('http://videogam.in/formatting-help'); },
	            editor = new Markdown.Editor(converter, elId, { handler: help });
	
	    editor.run();
	</script>
<p></p>
<?=($_POST['bbcode2markdown'] ? '<fieldset><legend>bbcode2markdown</legend><pre style="white-space:pre-wrap">'.htmlentities($md).'</pre></fieldset><p></p>' : '')?>
<fieldset><legend>Formatted Result:</legend>
<?=$t?>
</fieldset>
<p></p>
<fieldset><legend>Code Result:</legend>
<pre style="white-space:pre-wrap"><?=htmlspecialchars($t)?></pre>
</fieldset>
<?=($_POST['tidyHtml'] ? '<fieldset><legend>tidyHtml</legend>'.htmlspecialchars(tidyHtml($_POST['q'])).'</fieldset>' : '')?>

</section></body></html>