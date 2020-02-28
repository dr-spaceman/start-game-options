<?
if($f = $_GET['file']) {
?>
<html>
<body style="margin:0; padding:0;">
<script type="text/javascript" src="https://media.dreamhost.com/ufo.js"></script>
<p id="http://videogam.in/bin/uploads/audio/<?=$f?>" style="margin:5px 0;"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.</p>
<script type="text/javascript">
  var FO = { movie:"https://media.dreamhost.com/mediaplayer.swf",width:"400",height:"20",majorversion:"7",build:"0",bgcolor:"#FFFFFF",
             flashvars:"file=http://videogam.in/bin/uploads/audio/<?=$f?>&showdigits=true&autostart=false" };
UFO.create(FO,"http://videogam.in/bin/uploads/audio/<?=$f?>");
</script>
</body>
</html>
<?
} else {
	echo "error: no filename given";
}
?>