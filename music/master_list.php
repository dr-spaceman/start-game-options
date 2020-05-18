<?
use Vgsite\Page;
	
$page = new Page();
$page->title = "Videogam.in / Music / Index";
$page->css[] = "/music/style.css";
$page->freestyle.= '
	TABLE.plain, .plain TD { border-color:#C0C0C0 !important; }
	.letters TD { width:3%; border-width:0 0 0 1px; border-bottom-width:0 !important; border-color:#CCC !important; text-align:center; font-size:18px; }
	.letters TD A { display:block; }
	TABLE .heading { background-color:#EEE; }
	TABLE .heading H4 { display:inline; margin:0; padding:0; color:#666; }
	TABLE .heading SMALL { font-size:11px; font-weight:normal; }
	A.hassample { padding-right:13px; background:url(/bin/img/musicnote_blue.png) no-repeat right 3px; }
	A.hassample:HOVER { padding-right:13px; background:url(/bin/img/musicnote_blue.png) no-repeat right -85px; }';
$page->meta_description = "";
$page->meta_keywords = "";

$page->header();

include("nav.php");

$aids = array();
$Query = "SELECT * FROM albums LEFT JOIN albums_samples USING (albumid) ORDER BY title, subtitle";
$Result = mysqli_query($GLOBALS['db']['link'], $Query);
while ($row = mysqli_fetch_assoc($Result)) {
	if(!in_array($row['albumid'], $aids)) {
  	$list[] = $row;
  }
  $aids[] = $row['albumid'];
}
$total = count($list);

for ($i = 0; $i < count($list); $i++) {
	$b = $list[$i]['title'];
	$b = strtoupper($b);
	$a[$i] = $b{0};
	unset($b);
}

?>

<table border="0" cellpadding="7" cellspacing="0" width="100%" class="plain">
	<tr>
		<td colspan="3">
			<table border="0" cellpadding="0" cellspacing="0" width="100%" class="letters">
				<tr>
					<?
					$letters = array(
					'0' => '0-9',
					'1' => 'A',
					'2' => 'B',
					'3' => 'C',
					'4' => 'D',
					'5' => 'E',
					'6' => 'F',
					'7' => 'G',
					'8' => 'H',
					'9' => 'I',
					'10' => 'J',
					'11' => 'K',
					'12' => 'L',
					'13' => 'M',
					'14' => 'N',
					'15' => 'O',
					'16' => 'P',
					'17' => 'Q',
					'18' => 'R',
					'19' => 'S',
					'20' => 'T',
					'21' => 'U',
					'22' => 'V',
					'23' => 'W',
					'24' => 'X',
					'25' => 'Y',
					'26' => 'Z');
					
					$letters2 = array_flip($letters);
					
					for ($i = 0; $i < count($list); $i++) {
					
					  $c = $a[$i];
						
					  if ($list[$i]['view']) {
					    $title = '<a href="/music/?id='.$list[$i]['albumid'].'"'.($list[$i]['file'] ? ' title="this album has music samples" class="hassample"' : '').'>'.$list[$i]['title'].' <em>'.$list[$i]['subtitle'].'</em></a>';
					  } else {
					    $title = $list[$i]['title'].' <em>'.$list[$i]['subtitle'].'</em>';
					  }
						
						$strout = '<tr><td>'.$title.'</td><td>'.$list[$i]['release'].'</td><td>'.$list[$i]['cid'].'</td></tr>'."\n";
						
						if (ctype_digit($c)) {
							$e = $letters2['0-9'];
							$d[$e] .= $strout;
						} elseif (ctype_alpha($c)) {
							$e = $letters2[$c];
							$d[$e] .= $strout;
						} else {
							$e = $letters2['0-9'];
							$d[$e] .= $strout;
						}
					
					}
					
					for ($i = 0; $i < 27; $i++) {
						echo '<td style="'.($i == 0 ? 'border-width:0;' : '').'"><a href="#'.$letters[$i].'">'.($letters[$i] == "0-9" ? '#' : $letters[$i
					}
					
					?>
				</tr>
			</table>
		</td>
	</tr>
	<?

	for ($i = 0; $i < 27; $i++) {
		if($d[$i]) {
		?>
		<tr>
			<td colspan="3" class="heading">
				<h4 id="<?=$letters[$i]?>"><?=($letters[$i] == "0-9" ? '#' : $letters[$i])?></h4> &nbsp;
				<small><a href="#top" class="arrow-up">Top</a> </small>
			</td>
		</tr>
		<?=$d[$i]?>
		<?
		}
	}

?>
</table>

<?
$page->footer();
?>
