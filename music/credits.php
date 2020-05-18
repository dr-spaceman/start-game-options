<?
use Vgsite\Page;
	
$page = new Page();
$page->title = "Videogam.in / Music / Database Project Credits";
$page->style[] = "/music/style.css";
$page->freestyle.= 'H3 { border-width:0; padding:0; }';
$page->meta_description = "";
$page->meta_keywords = "";

$page->header();

include ("nav.php");

echo <<<END

<h2>Albums Database Credits</h2>

The albums database would not be possible without the help of the following people and other sources, voluntary or not.<br><br>

<h3>Data</h3>

<div class="credits">
<a href="http://kosiro.hp.infoseek.co.jp/" target="_blank">Japan Game Music Library (J.G.M.L.)</a><br>
The impressive J.G.M.L. has most of the basic album information anyone really needs.  (At the very least, it verifies the existence of that All Star Pro-Wrestling II soundtrack you just <em>knew</em> existed.) The J.G.M.L. has data on over 5000 game music titles since 1984.<br></div>

<h3>Translations</h3>

<div class="credits">
Other than the people and sources mentioned, Square Haven staff did all the translations for the track lists (with considerable help from <a href="http://www.dgs.monash.edu.au/~jwb/wwwjdic.html" target="_blank">Jim Breen's WWWJDIC</a>).  The point is not for us to make a claim on the final word on translations, but to show that they should not be passed off as immutable.  In fact, enthusiasts should cast a critical eye on them and perhaps come up with their own translations as they see fit.  That is why we have included the original Japanese track listings courtesy of the <a href="http://kosiro.hp.infoseek.co.jp/" target="_blank">J.G.M.L.</a><br></div>

<h3>Lyrics</h3>

<div class="credits">
(One thing at a time...)<br></div>

<h3>Reviews</h3>

<div class="credits">
(None yet.  <a href="mailto:paul@square-haven.com">Care to submit a brief review-as-synopsis</a>?)<br></div>

<h3>Media</h3>
<div class="credits">
<a href="http://www.animenation.com/" target="_blank">AnimeNation</a><br>
"Provider" of virtually all of the pretty, high-quality cover scans.  Peruse <a href="http://squarehaven.com/features/albums/by_retailer.php">AnimeNation's Square CD list</a> and buy something because you feel guilty.<br></div>

<h3>Trivia</h3>
<div class="credits">
(None yet.  <a href="mailto:paul@square-haven.com">Know something interesting</a>?)<br></div>

<h3>Inspiration (and more)</h3>

<div class="credits">
<strong>Rahul of <a href="http://www.gamingredients.com/" target="_blank">Gamingredients</a><br></strong>
Without his nitpicky criticisms about "overbearing gray" and jarring text choice/placement/spacing and expanded track listings, the design would have been butt-ugly crude.  Also the contributor of more useless facts about game music than we ever wanted to know.<br></div>

<div class="credits">
<a href="http://www.altpop.com/stc/" target="_blank">SoundtrackCentral.com</a><br>
The original and the only informed game soundtrack resource of its scale (that we're aware of).<br></div>

<div class="credits">
<a href="http://www.ffmusic.info/" target="_blank">Daryl's Library</a><br>
Highly specific detail on the actual contents of FF soundtracks (including liner notes for many).  Has the English names for tracks that most Japanese sources inexplicably omit.  Lifesaver!<br></div>

<div class="credits">
<a href="http://www.avians.net/rkc/reichu/reichu-vgm.html" target="_blank">Reichu's VGM</a><br>
Incredibly useful resource for alternative (alternative with respect to the sometimes-bizarre translations out there) track list translations (with detailed translation notes!), especially considering the lack of nuance in <s>ours</s> <a href="mailto:paul@square-haven.com">mine</a>.<br></div>

<p>- Paul Le, 2003.</p>

END;

$page->footer();

?>