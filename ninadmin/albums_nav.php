<?
if ($step == "1" or $step == "2" or $step == "3" or $step == "4" or $step == "5" or $step == "6" or $step == "7" or $step == "8" or $step == "9") {


if ($editid) {
$albumlink = "Editing ";
}


?>
<table border="0" width="100%" cellspacing="0" cellpadding="5" id="album-header" class="plain">
	<tr>
		<th colspan="5">
			<a href="/music/?id=<?=$editid?>" style="color:black; font-size:21px; font-weight:normal; text-decoration:none;"><?=$albumtitle?> <?=$albumsubtitle?></a>
		</th>
	</tr>
	<tr>
<?


if ($step == "1") {
$step1 = "Step 1<br/>";
$stepact = " style=\"background-color: #CCC;\"";
}
else {
unset($stepact);
$step1 = "<a href=\"?step=1&action=$action&editid=$editid\">Step 1</a><br/>";
}

$step2 = "<a href=\"?step=2&action=$action&editid=$editid\">Step 2</a><br/>";
if ($step == "2") {
$stepact2 = " style=\"background-color: #CCC;\"";
}
else {
unset($stepact2);
}

if ($step == "3") {
$step3 = "Step 3<br/>";
$stepact3 = " style=\"background-color: #CCC;\"";
}
else {
unset($stepact3);
$step3 = "<a href=\"?step=3&action=$action&editid=$editid\">Step 3</a><br/>";
}

if ($step == "4") {
$step4 = "Step 4<br/>";
$stepact4 = " style=\"background-color: #CCC;\"";
}
else {
unset($stepact4);
$step4 = "<a href=\"?step=4&action=$action&editid=$editid\">Step 4</a><br/>";
}

if ($step == "5") {
$step5 = "Step 5<br/>";
$stepact5 = " style=\"background-color: #CCC;\"";
}
else {
unset($stepact5);
$step5 = "<a href=\"?step=5&action=$action&editid=$editid\">Step 5</a><br/>";
}

if ($step == "6") {
$step6 = "Step 6<br/>";
$stepact6 = " style=\"background-color: #CCC;\"";
}
else {
unset($stepact6);
$step6 = "<a href=\"?step=6&action=$action&editid=$editid\">Step 6</a><br/>";
}

if ($step == "7") {
$step7 = "Step 7<br/>";
$stepact7 = " style=\"background-color: #CCC;\"";
}
else {
unset($stepact7);
$step7 = "<a href=\"?step=7&action=$action&editid=$editid\">Step 7</a><br/>";
}

if ($step == "8") {
$step8 = "Step 8<br/>";
$stepact8 = " style=\"background-color: #CCC;\"";
}
else {
unset($stepact8);
$step8 = "Step 8<br/>";
}

if ($step == "9") {
$step9 = "Step 9<br/>";
$stepact9 = " style=\"background-color: #CCC;\"";
}
else {
unset($stepact9);
$step9 = "<a href=\"?step=9&action=$action&editid=$editid\">Step 9</a><br/>";
}

if ($step == "10") {
$step10 = "Step 10<br/>";
$stepact10 = " style=\"background-color: #CCC;\"";
}
else {
unset($stepact10);
$step10 = "<a href=\"?step=3&action=$action&editid=$editid&do=samples\">Step 10</a><br/>";
}

echo <<<END
<td width="20%"$stepact><span class="steplink">$step1</span>Basic data</td>
<td width="20%"$stepact2><span class="steplink">$step2</span>People</td>
<td width="20%"$stepact3><span class="steplink">$step3</span>Track list</td>
<td width="20%"$stepact4><span class="steplink">$step4</span>Related games</td>
<td width="20%"$stepact5><span class="steplink">$step5</span>Related albums</td>\n
END;

echo <<<END
</tr>

<tr>
<td width="20%"$stepact6><span class="steplink">$step6</span>Synopsis, reviews, etc.</td>
<td width="20%"$stepact7><span class="steplink">$step7</span>Media management</td>
<td width="20%"$stepact8><span class="steplink">$step8</span>Lyrics</td>
<td width="20%"$stepact9><span class="steplink">$step9</span>Factoids, retailers, links</td>
<td width="20%"$stepact10><span class="steplink">$step10</span>Samples</td>
</tr>

</table><br/>\n
END;
}

?>