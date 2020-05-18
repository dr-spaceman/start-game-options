<?
use Vgsite\Page;
$page = new Page();
$page->title = "About Videogam.in";
$page->width = "fixed";
$page->minimalist = true;
$page->freestyle.= '
	#about dl { margin:0; padding:0; }
	#about dl dt { font-weight:bold; font-size:16px; margin:1em 0 0; padding:0 0 0 20px; background:url("/bin/img/heart.png") no-repeat 0 4px; }
	#about dl dd { margin:0; padding:0 0 0 20px; color:#555; }
';
$page->header();

?>
<div id="about" style="margin:0 256px; font-size:110%; line-height:1.5em; position:relative;">
	
	<img src="/bin/img/mascot_huge.png" width="400" height="400" alt="Videogam.in mascot" style="position:absolute; top:0; right:-500px; opacity:.5;"/>
	
	<h1>A website about videogames!</h1>
	
	<big>
		<p><div style="float:left; margin:0 6px 0 0;"><img src="/bin/img/icons/sprites/DK.png" alt="V"/></div><b>IDEOGAM.IN</b> is a place where everyone can <a href="/content/Special:featured" title="featured Videogam.incyclopedia content">read</a> and <a href="/content/Special:new" title="create a new Videogam.incyclopedia article">contribute</a> information about <a href="/games">games</a> and the <a href="/people">people</a> who make them. It's also a place to follow, review, collect, and show off your favorite (and least favorite) games.</p>
		<p>Videogam.in is a site where serious gamers can <a href="/forums" title="Videogam.in Message Forums of Death!!!">discuss</a>, <a href="/posts/" title="Videogam.in Sblog: videogame news & blogs">peruse</a>, <a href="/posts/manage.php?action=newpost" title="Create a new Sblog post">write</a> about <a href="/posts/topics/" title="Videogame topics">gaming topics</a> in an unserious manner. Its edge lies in its unique ability to speak vapidly about otherwise serious issues, take videogames only as earnestly as its own college drunkenness and inability to graduate within four years, and ultimately: the vapid community of vulgar buffoonery that lurk within its dank confines.</p>
		<?=(!$usrid ? '<p>To get started, <a href="/register.php">Register for a Videogam.in account</a> (it only takes about a minute).</p>' : '')?>
	</big>
	
	<div style="margin:20px 0 0 0; padding:10px 12px; font-size:14px; color:#333; background-color:#F5F5F5; border:1px solid #DDD; -moz-border-radius:5px; -webkit-border-radius:5px;">
		Join the <b><a href="/groups/~videogamin-development">Videogam.in Development group</a></b> to receive development updates and contribute to the development process
	</div>
	
	<h2 style="margin:20px 0 0;">Features</h2>
		<dl>
			<dt><a href="/content/Special:featured" title="featured Videogam.incyclopedia content">The Videogam.incyclopedia</a></dt>
			<dd>A growing database of <a href="/games">Games</a>, <a href="/people">Game Creators</a>, and <a href="/categories/Game_console">Consoles</a>, <a href="/categories/Game_developer">Companies</a>, <a href="/categories/Game_character">Characters</a>, <a href="/categories/Game_concept">Concepts</a>, and other <a href="/categories">videogame categories</a>.</dd>
			
			<dt><a href="/posts/">The Sblog</a></dt>
			<dd>A portmanteu of news and blog, the Sblog is a collection of videogame-related content, including news, reviews, quotes, pictures, videos, music, links to other websites, and much more. You can use it to create your own personal blog <?=($_SESSION['logged_in'] ? '(located at <a href="/~'.$current_user->getUsername().'/blog">Videogam.in/~'.$current_user->getUsername().'/blog</a>)' : '')?> or post to the public news roll.</dd>
			
			<dt><a href="/music/">Videogame Music Database</a></dt>
			<dd>450+ game music soundtracks.</li>
			
			<dt><a href="/forums/">The Videogam.in <i>Message Forums of DEATH!!!</i></a></dt>
			<dd>The low brow Mecca of the gaming world.</dd>
			
			<dt><a href="/groups/">Groups and Clans</a></dt>
			<dd>Nouns of number, or multitude, such as Mob, Parliament, Rabble, House of Commons, Regiment, Court of King's Bench, Den of Thieves, Gaming Clan and the like.</dd>
		</dl>

</div>
<?
$page->footer();
?>