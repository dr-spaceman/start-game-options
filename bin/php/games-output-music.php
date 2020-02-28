<?

$page->title = $gdat->title." music -- Videogam.in";
$page->style[] = "/music/style.css";
$page->freestyle.= '
#conts-music {  }
H4 { margin:0 0 3px; padding:0 0 3px; border-bottom:1px solid #CCC; font-size:21px; font-weight:normal; color:#555; }
#CMpeople {}
#CMpeople OL { margin:0; padding:0; list-style:none; }
#CMpeople LI { margin:0; padding:0; }
#CMpeople LI A { display:block; width:200px; float:left; margin:0 15px 0 0; padding:10px 0; text-decoration:none; }
#CMpeople LI:first-child A { border-width:0; padding-top:7px; }
#CMpeople LI A .img { float:left; margin:0 10px 5px 0; padding:2px; border:1px solid #DDD; }
#CMpeople LI A:HOVER .img { border-color:#39F; }
#CMpeople H5 { margin:0 0 3px; padding:0; font-size:14px; text-decoration:underline; }
#CMpeople .title { color:#777; font-size:12px; }
#CMalbums {}
.music-data-table { float:left; margin:0 20px 10px 0; }
.music-data-table H5 {
	margin: 10px 0 0 0;
	padding: 5px;
	font-size: 12px;
	border-width: 0 0 1px 0;
	border-style: solid;
	border-color: #CCC;
	background: transparent url(/bin/img/gradient-b2t-eee.png) repeat-x scroll 0pt -110px; }
.music-data-table H5 A {
	text-decoration: none; }
.music-data-table H5 A EM {
	text-decoration: underline; }
';
$page->header();

?>
<div id="conts-music" class="conts">
	<table border="0" cellpadding="0" cellspacing="0"><tr><td>
		
		<div id="CMpeople">
			<h4>Musicians</h4>
			<?
			$query = "SELECT DISTINCT(people.pid), `name`, name_url, `title` 
				FROM albums_tags 
				LEFT JOIN people_work USING(albumid) 
				LEFT JOIN people ON (people.pid=people_work.pid) 
				WHERE albums_tags.gid='$gdat->gid' 
				ORDER BY `people`.`name` ASC";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			if(!mysqli_num_rows($res)) echo "No people have credited for the music of this game.";
			else {
				?><ol><?
				while($row = mysqli_fetch_assoc($res)) {
					$nopic = TRUE;
					$src = "/bin/img/people/nopicture-tn.png";
					if(file_exists($root."/bin/img/people/".$row['pid']."-tn.png")) {
						$src = "/bin/img/people/".$row['pid']."-tn.png";
						$nopic = FALSE;
					}
					if($row['pid']) echo '<li><a href="/people/~'.$row['name_url'].'"><div class="img"><img src="'.$src.'" border="0" alt="'.($nopic ? "No picture" : htmlSC($row['name'])).'"/></div><h5>'.$row['name'].'</h5><div class="title">'.$row['title'].'</div></a></li>';
				}
				?></ol><?
			}
			?>
		</div>
		
		<br style="clear:both"/>
		
		<div id="CMalbums">
			<h4 style="margin-right:20px">Music Albums</h4>
			<?
			foreach($gamepg->albumdata as $dat) {
				?>
				<div class="music-data-table">
					<h5><a href="/music/?id=<?=$dat['albumid']?>" class="ntlink"><?=$dat['title']?> <em><?=$dat['subtitle']?></em></a></h5>
					<table border="0" width="395" cellpadding="0" cellspacing="0">
						<tr>
							<td rowspan="6" width="160" class="newsidebar">
								<a href="/music/?id=<?=$dat['albumid']?>"><img src="<?=(file_exists($_SERVER['DOCUMENT_ROOT']."/music/media/cover/standard/".$dat['albumid'].".png") ? '/music/media/cover/standard/'.$dat['albumid'].'.png" alt="'.$dat['title'].' '.$dat['subtitle'].'"' : 'graphics/none.png" alt="no cover image available"')?> border="0"/></a>
							</td>
							<td width="235" colspan="2" class="newsubtitle">Album Data</td>
						</tr>
						<tr>
							<td width="100" class="newentry">Publisher:</td>
							<td width="135" class="newentry2"><?=$dat['publisher']?></td>
						</tr>
						<tr>
							<td width="100" class="newentry">Catalog ID:</td>
							<td width="135" class="newentry2"><?=$dat['cid']?></td>
						</tr>
						<tr>
							<td width="100" class="newentry">Release Date:</td>
							<td width="135" class="newentry2"><?=$dat['release']?></td>
						</tr>
						<tr>
							<td width="100" class="newentry">Price (retail):</td>
							<td width="135" class="newentry2"><?=$dat['price']?></td>
						</tr>
						<tr>
							<td width="100" class="newentry">Composition:</td>
							<td width="135" class="newentry2"><?
								
								unset($a);
								unset($v);
								unset($r);
								$a = array();
								$v = array();
								$r = array();
								
								$q = "SELECT name, name_url, vital FROM people_work LEFT JOIN people USING (pid) WHERE people_work.albumid='".$dat['albumid']."' AND role LIKE '%compos%'";
								$res = mysqli_query($GLOBALS['db']['link'], $q);
								while($row = mysqli_fetch_assoc($res)) {
									$x = '<a href="/people/~'.$row['name_url'].'">'.$row['name'].'</a>';
									if($row['vital']) $v[] = $x;
									else $r[] = $x;
								}
								
								$q = "SELECT name, vital FROM albums_other_people WHERE albumid='".$dat['albumid']."' AND role LIKE '%compos%'";
								$res = mysqli_query($GLOBALS['db']['link'], $q);
								while($row = mysqli_fetch_assoc($res)) {
									if($row['vital']) $v[] = $row['name'];
									else $r[] = $row['name'];
								}
								
								$a = array_merge($v, $r);
								$_people = array();
								for($i = 0; $i <= 2; $i++) {
									if($a[$i]) $_people[] = $a[$i];
								}
								if(count($_people) == 3 && count($a) > 3) $_people[] = "et al.";
								echo implode(", ", $_people);
								?>
							</td>
						</tr>
					</table>
				</div>
				<?
			}
			?>
		</div>
	</td></tr></table>
</div>
<?

$page->footer();

?>