<?
require "page.php";

?><?=$html_tag?>
<head>
	<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
	<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>
	<style type="text/css">
		ul { margin:0; padding:0; list-style:none; }
		li { margin:10px 0 -8px 10px; padding:0; }
	</style>
</head>
<body style="margin:0; padding:0;">
<?

if($url = $_GET['url']){
$tinyurl = $_GET['tinyurl'];
$desc = str_replace('"', "'", $_GET['desc']);
?>
<ul>
<li><iframe src="http://www.facebook.com/plugins/like.php?app_id=142628175764082&amp;href=<?=urlencode($url)?>&amp;send=false&amp;layout=button_count&amp;width=90&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font=arial&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:90px; height:21px;" allowTransparency="true"></iframe></li>
<li><g:plusone size="medium" href="<?=$url?>"></g:plusone></li>
<li><a href="http://twitter.com/share?url=<?=urlencode($url)?>&text=<?=$desc?>&related=videogamin" class="twitter-share-button">Tweet</a>
</ul>
<?
}

?>
</body>
</html>