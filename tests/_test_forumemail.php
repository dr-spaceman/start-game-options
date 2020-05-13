<?
use Vgsite\Page;
include("forums/action.php");
echo Page::HTML_TAG;
?><head>
<link rel="stylesheet" type="text/css" href="/bin/css/screen.css" media="screen"/>
</head>
<body style="margin:30px;">
<form action="test_forumemail.php" method="post">
<textarea name="q" rows="13" cols="80"><?=$_POST['q']?></textarea>
<p></p>
<input type="submit" value="Test code"/>
</form>
<?

if($_POST['q']){
	$q = parseText($_POST['q']);
	echo '<details open><summary>Result</summary>';
	if($emailed = sendSubscription(array("tid" => "1"), "/forums/?tid=1", "fuuu", $q)){
		echo 'Emailed '.count($emailed).': ' . implode(", ", $emailed) . '<hr/>';
		$bb = new bbcode($q);
		$bb->params['prepend_domain'] = true;
		$bb->params['nl2p'] = true;
		$q = $bb->bb2html();
		$q2 = htmlentities($q);
		echo $q . '<hr/><pre>' . $q2 .'</pre>';
	} else {
		echo "No results received";
	}
	echo '</details>';
}
?>
</body></html>