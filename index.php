<?
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.posts.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.img.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php";

$page = new page();

$page->fb = true;
$page->title = "Videogam.in, a site about videogames";

$page->freestyle.= '
#portal { position:relative; height:250px; }
#portal table.nav { position:absolute; z-index:2; bottom:0; height:30px; box-shadow:0 5px 12px -5px #CCC; }
#portal .nav a {
	display:block; padding:0 15px; text-align:center; line-height:30px; font-size:12px; color:#666; text-decoration:none;
	border-width:1px 1px 0 0; border-style:solid; border-color:RGBA(0,0,0,.12) RGBA(0,0,0,.06);
	background: -moz-linear-gradient(top, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0) 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(0,0,0,0.1)), color-stop(100%,rgba(0,0,0,0))); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top, rgba(0,0,0,0.1) 0%,rgba(0,0,0,0) 100%); /* Chrome10+,Safari5.1+ */
	background: linear-gradient(top, rgba(0,0,0,0.1) 0%,rgba(0,0,0,0) 100%); /* W3C */
}
#portal .nav tr td:first-child a { border-left-width:1px; }
#portal .nav .on a { background:none; border-top-color:transparent; border-right-color:transparent; color:black; box-shadow: 0 3px 3px #CCCCCC; }
#portal .homeSection { display:none; height:220px; overflow:hidden; }
#portal ul.styled { margin:0; padding:0; list-style:none; }
#portal ul.styled li { margin:0 0 4px; padding:0 0 0 14px; color:#666; background:url(/bin/img/bullet-square.png) no-repeat 4px 6px; }
#hs-newContent { overflow:auto !important; }
#hs-newContent dl { margin:0 0 15px; padding:0; font-size:14px; }
#hs-newContent dl dt { margin:0; padding:0; float:left; font-size:12px; color:#777; }
#hs-newContent dl dd { margin:0 0 4px 55px; padding:0; }
#hs-newContent .imgs a { display:inline-block; margin:4px 5px 0 0; }
.shelf-item { margin-left:0 !important; margin-right:0 !important; }
.shelf-headings { top:7px; }
.shelf #selregion {
	display:none;
	position:absolute; z-index:6; left:0; bottom:0;
}
.shelf #selregion div {}
.shelf #selregion div b { display:block; padding:0 15px 0 25px; font-size:11px; line-height:23px; color:white; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.7); }
.shelf #selregion div .arrow { width:10px; height:6px; position:absolute; top:50%; right:5px; margin:-4px 0 0; background:url("/bin/img/sprites_arrows.png") no-repeat scroll -18px -7px transparent; }
.shelf #selregion div .flag { position:absolute; top:50%; left:4px; margin:-6px 0 0; box-shadow:0 0 2px rgba(0, 0, 0, 0.7); }
.shelf #selregion ul { margin:0; padding:0; list-style:none; }
.shelf #selregion li { float:left; }
.shelf #selregion li.off { display:none; }
.shelf #selregion li a { display:block; margin:0; padding:6px 10px; bacground-color:RGBA(0,0,0,.6); }
ul.gameops { margin:0; padding:0; list-style:none; }
ul.gameops li { padding-left:18px; position:relative; }
ul.gameops li .icon { position:absolute; top:50%; left:0; width:14px; height:14px; margin-top:-7px; background:url("/bin/img/pgop_sprites.png") no-repeat 0 -30px; }
';

$page->javascript.= '
<script type="text/javascript">
	$(document).ready(function(){
		var hs;
		$("#portal .nav a").click(function(){
			if($(this).parent().hasClass("on")) return;
			$(this).parent().addClass("on").siblings().removeClass("on");
			$("#hs-"+$(this).attr("href").replace("#", "")).show().siblings(".homeSection").hide();
			if($(this).attr("href")=="#siteCounts") $.get("/index_ajax.php", {"action":"load_vgin_stats"}, function(ret){ $("#hs-siteCounts").html(ret) });
		});
		var gameshelf = {
			toggleRegion: function(){ $("#selregion").fadeToggle("fast") },
			toggleRegionSel: function(){}
		}
		$(".shelf").hoverIntent(gameshelf.toggleRegion, gameshelf.toggleRegion);
	})
</script>';

$page->header();

/*if(!$_COOKIE['colophon']){

	$page->closeSection();
	$page->openSection(array("class"=>"pgsec-white", "style"=>"margin:0 0 40px; padding:0 !important; border-bottom:2px solid #BBB;"));
	
	?>
	<div id="colophon" style="position:relative; padding:20px 0 20px 50px; font-size:15px;" onclick="$.cookie('colophon2', '1', {expires:365, path:'/'});">
		
		<div style="position:absolute; left:0; bottom:0; width:42px; height:83px; background:url('/bin/img/icons/sprites/sword_big.png');"></div>
	
		<h1 style="font-size:20px; text-shadow:none;">IT'S A SECRET TO EVERYBODY</h1>
		
		Welcome to Videogam.in, a site about videogames! 
		<b><a href="/about.php">Read more</a></b> about this site or else <a href="#close" title="hide this message and don't show it to me again" onclick="$(this).closest('.pgsec').hide();">pay me for the door repair charge</a>.
		
	</div>
	<?

}*/

/*if(!$_COOKIE['homesblogbaddude']){
	
	$page->closeSection();
	$page->openSection(array("class"=>"homesblogbaddude", "style"=>"margin:0;"));
	?>
	<div style="position:relative; margin:30px 0 0; text-align:center;">
		<a href="#close" title="hide this message and don't show it to me again" class="ximg" style="top:0; right:190px;" onclick="$(this).parent().hide(); $.cookie('homesblogbaddude', '1', {expires:365, path:'/'});">close</a>
		<img src="/bin/img/promo/sblog_baddude.png" alt="Are you a bad enough dude...?" border="0"/>
	</div>
	<?
	$page->closeSection();
	$page->openSection(array("class"=>"homesblogbaddude pgsec-black", "style"=>"margin:0 0 50px;"));
	?>
	<div style="margin:-30px 190px; font-size:30px; color:#DDD; text-align:justify;">
			
			<p>The Videogam.in Sblog is a collection of News, Blogs and Content posted by users like you.</p>
			
			<p style="text-align:center"><img src="/bin/img/promo/sblog_is.png" alt="News + Blog + You = SBLOG"/></p>
			
			<p>You can post almost anything, as long as it's interesting and related to videogames.</p>
			
			<p><img src="/bin/img/promo/sblog_types.png" alt="There are many post options"/></p>
			
			<p>Contribute to the communal Public Sblog or create your own Blog.</p>
			
			<p style="text-align:center"><img src="/bin/img/promo/sblog_publicprivate.png" alt="Public or Private Sblogs"/></p>
			
			<p>Use it to review games, game soundtracks, or pretty much anything.</p>
			
			<p><img src="/bin/img/promo/sblog_review.png" alt="Use it for reviews"/></p>
			
			<p>Rate Sblog posts based on your opinion of quality, interest, and relevance.</p>
			
			<p style="text-align:center;"><img src="/bin/img/promo/sblog_hearts.png" alt="Heart rating"/></p>
			
			<p><a href="/posts/manage.php?action=newpost" style="color:white; font-weight:bold;" onclick="$.cookie('homesblogbaddude', '1', {expires:365, path:'/'});">Check out the Sblog post form</a> to see what it can do, or read more about Sblogging in the <a href="/posts/2010/03/30/videogamin-sblog-faq" style="color:white; font-weight:bold;" onclick="$.cookie('homesblogbaddude', '1', {expires:365, path:'/'});">Sblog Posting Guide</a>.</p>
			
			<p><a href="#head" title="hide this message and don't show it to me again" style="font-size:18px;" onclick="$('.homesblogbaddude').hide(); $.cookie('homesblogbaddude', '1', {expires:365, path:'/'});">Close this message</a></p>
	
	</div>
	<?

}*/

?>

<div class="c1r">
	
	<?=printAd("300x250")?>
	
	<!--<div style="width:300px; margin:20px 0 0; background-color:white;">
		<fb:like-box profile_id="113351978705186" width="300" stream="false" header="false" style="margin-bottom:-1px;"></fb:like-box>
		<fb:activity site="http://videogam.in" height="265" header="false" recommendations="true"></fb:activity>
	</div>-->
	
</div>

<div id="portal" class="c2">
	
	<table border="0" cellpadding="0" cellspacing="0" width="100%" class="nav">
		<tr>
			<td class="on"><a href="#upcoming">Upcoming & Recent Releases</a></td>
			<td><a href="#newContent">Recent Activity</a></td>
			<td><a href="#users">Videogam.in Users</a></td>
			<td><a href="#siteCounts">By the Numbers</a></td>
		</tr>
	</table>
	
	<!--upcoming & recent releases-->
	<div id="hs-upcoming" class="homeSection shelf gameshelf horizontal mouseposscroll" style="display:block"><?
		$_GET['action'] = "load_shelf";
		include("index_ajax.php"); ?>
	</div>
	
	<div id="hs-siteCounts" class="homeSection" style="font-size:14px; line-height:18px; position:relative;">
		<div id="load-vgin-stats" style="padding:10px; background:RGBA(255,255,255,.8); border-radius:100%; position:absolute; left:45%; top:35%;"><img src="/bin/img/loading-arrows-small.gif" width="16" height="16" alt="loading"/></div>
	</div>
	
	<div id="hs-newContent" class="homeSection">
		<dl>
			<?
			$li = array();
			
			//stream
			$query = "SELECT * FROM `stream` WHERE DATE_SUB(CURDATE(),INTERVAL 7 DAY) <= `datetime`";
			$res   = mysql_query($query);
			while($row = mysql_fetch_assoc($res)){
				$li[$row['datetime']] = '<dd>'.$row['action'].'</dd>';
			}
			
			//new pages
			$query = "SELECT creator, `title`, `type`, `subcategory`, `created` FROM pages WHERE redirect_to='' ORDER BY `created` DESC LIMIT 8";
			$res   = mysql_query($query);
			while($row = mysql_fetch_assoc($res)){
				$o_user[$row['creator']] = $o_user[$row['creator']] ? $o_user[$row['creator']] : outputUser($row['creator'], false);
				$li[$row['created']] = '<dd>'.$o_user[$row['creator']].' started the <b>[['.$row['title'].']]</b> '.($row['subcategory'] ? str_replace('Game ', '', $row['subcategory']) : $row['type']).' page</dd>';
			}
			
			//uploads
			$query = "SELECT * FROM images_sessions ORDER BY img_session_created DESC LIMIT 3";
			$res   = mysql_query($query);
			while($row = mysql_fetch_assoc($res)){
				$o_user[$row['usrid']] = $o_user[$row['usrid']] ? $o_user[$row['usrid']] : outputUser($row['usrid'], false);
				$o = '<dd>'.$o_user[$row['usrid']].' uploaded '.$row['img_qty'].' image'.($row['img_qty'] != 1 ? 's' : '').' to the group <a href="/image/-/session/'.$row['img_session_id'].'" title="'.htmlSC($row['img_session_description']).'">'.$row['img_session_description'].'</a><div class="imgs">';
				$query2 = mysql_query("SELECT img_name FROM images WHERE img_session_id = '".$row['img_session_id']."' ORDER BY `sort` ASC LIMIT 3");
				while($file = mysql_fetch_assoc($query2)){
					$img = new img($file['img_name']);
					$o.= '<a href="'.$img->src['url'].'" class="imgupl" rel="portal-'.$row['img_session_id'].'"><img src="'.$img->src['tn'].'" width="50" height="50" border="0" alt="'.htmlSC($img->img_title).'"/></a>';
				}
				$o.= '</div></dd>';
				$li[$row['img_session_created']] = $o;
			}
			
			//albums
			$query = "SELECT `datetime`, usrid, albumid, `title`, `subtitle` FROM albums_changelog LEFT JOIN albums ON (albums_changelog.album=albums.albumid) WHERE type='new' AND view='1' ORDER BY albums_changelog.datetime DESC limit 3";
			$res   = mysql_query($query);
			while($row = mysql_fetch_assoc($res)){
				$o_user[$row['usrid']] = $o_user[$row['usrid']] ? $o_user[$row['usrid']] : outputUser($row['usrid'], false);
				$li[$row['datetime']] = '<dd>'.$o_user[$row['usrid']].' started the <b><a href="/music/?id='.$row['albumid'].'">'.$row['title'].' <i>'.$row['subtitle'].'</i></a></b> album page</dd>';
			}
			
			//love/hate
			$query = "SELECT * FROM pages_fan ORDER BY `datetime` DESC";
			$res   = mysql_query($query);
			while($row = mysql_fetch_assoc($res)){
				if(++$num_op_user[$row['usrid']] > 2) continue;
				if(++$num_op > 10) break;
				$o_user[$row['usrid']] = $o_user[$row['usrid']] ? $o_user[$row['usrid']] : outputUser($row['usrid'], false);
				$li[$row['datetime']] = '<dd>'.$o_user[$row['usrid']].' '.$row['op'].'s [['.$row['title'].']]</dd>';
			}
			
			krsort($li);
			$dl = '';
			foreach($li as $datetime => $dd){
				$dl.= '<dt>'.timeSince($datetime, true).'</dt>' . $dd;
			}
			
			echo links($dl);
			?>
		</dl>
	</div>
	
	<?/*
	<div id="hs-newUploads" class="homeSection">
		<a href="/upload.php" title="Upload images" class="add"><b>+</b> Upload Images</a>
		<ol style="margin:0 110px 0 -5px; padding:0; list-style:none;">
			<?
			$query = "SELECT * FROM images_sessions ORDER BY img_session_created DESC LIMIT 6";
			$res   = mysql_query($query);
			while($row = mysql_fetch_assoc($res)){
				$file = mysql_fetch_object(mysql_query("SELECT img_name FROM images WHERE img_session_id = '".$row['img_session_id']."' ORDER BY `sort` ASC LIMIT 1"));
				$img = new img($file->img_name);
				echo '<li style="float:left; margin:0; padding:0;"><a href="/image/-/session/'.$row['img_session_id'].'" title="'.htmlSC($row['img_session_description']).'" style="display:block; margin:0 0 5px 5px;"><img src="'.$img->src['ss'].'" height="100" border="0" alt="'.htmlSC($img->img_title).'"/></a></li>';
			}
			?>
		</ol>
	</div>
	
	<div id="hs-newAlbums" class="homeSection">
		<ul style="margin:0; padding:0; list-style:none;">
			<?
			$query = "SELECT * FROM albums_changelog LEFT JOIN albums ON (albums_changelog.album=albums.albumid) WHERE type='new' AND view='1' ORDER BY albums_changelog.datetime DESC limit 5";
			$res   = mysql_query($query);
			while($row = mysql_fetch_assoc($res)) {
				echo '<li style="margin:0 0 3px; color:#666;">'.outputUser($row['usrid'], false).' created <b><a href="/music/?id='.$row['albumid'].'">'.$row['title'].' <i>'.$row['subtitle'].'</i></a></b></li>';
			}
			?>
		</ul>
	</div>*/?>
	
	<div id="hs-users" class="homeSection">
		<div style="float:left; width:38%;">
			<b>Top Rated<span class="tooltip" title="based on points earned from creating and editing content pages, forum posts, Sblog posts, and user ratings" style="color:#777;">*</span> Contributing Users</b>
			<ol style="margin:5px 0 0; line-height:25px; font-size:110%;">
				<?
				$noshow = array("Matt", "Jeriaska", "Rahul", "Ziyad", "Videogamin", "Xavi");
				$query = "SELECT usrid, username, avatar FROM `users` ORDER BY score_total DESC limit 20";
				$res   = mysql_query($query);
				$i = 0;
				while($row = mysql_fetch_assoc($res)) {
					if(in_array($row['username'], $noshow)) continue;
					if(++$i > 7) break;
					echo '<li><a href="/~'.$row['username'].'" class="user"><span class="avatar" style="background-image:url(\'/bin/img/avatars/tn/'.$row['avatar'].'\');"></span>'.$row['username'].'</a></li>';
				}
				?>
			</ol>
		</div>
		<div style="margin-left:40%;">
			<b>Say Hello</b>
			<ul style="margin:5px 0 0; padding:0; line-height:25px; font-size:110%; color:#888; list-style:none;">
				<?
				$query = "SELECT usrid, registered FROM users ORDER BY `registered` DESC limit 7";
				$res   = mysql_query($query);
				while($row = mysql_fetch_assoc($res)) {
					echo '<li>'.outputUser($row['usrid']).' registered '.timeSince($row['registered']).' ago</li>';
				}
				?>
			</ul>
		</div>
	</div>
	
</div>
<?

$page->closeSection();

$sec = array("id" => "posts", "class" => "posts");
$page->openSection($sec);

$posts = new posts();
$posts->query_params = array('archive' => false);
$posts->buildQuery();
echo $posts->postsList();

$page->closeSection();

$page->footer();

?>