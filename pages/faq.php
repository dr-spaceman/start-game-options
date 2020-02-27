<?
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php");
$page = new page;
require ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");

$page->width = "fixed";
$page->title = "Page Creating Guide & F.A.Q. -- Videogam.in";
$page->freestyle.='
	#bodywrap { line-height:1.5em; }
	CODE { padding:0 2px; background-color:white; }
';
$page->header();

?>
<h1>Page Creating Guide & F.A.Q.</h1>

This guide will attempt to explain how to edit page content here at Videogam.in.

<h2 id="name">Naming Conventions</h2>
<p>On properly naming a page:
<ul>
	<li><b>Besides the first letter of the first word, only proper nouns should be capitalized.</b> For example: "Super Mario Bros.", "Parappa the Rapper", "Hironobu Sakaguchi", "Double Fine Productions". Examples of proper titles that aren't proper nouns: "Mustaches", "Final Fantasy series", "Music composers", "Games made into movies".</li>
	<li>Never use abbreviations.</li>
	<li>As a rule of thumb, <b>Game pages</b> use the title listed at <a href="http://en.wikipedia.org/wiki/Lists_of_video_games" target="_blank" title="Wikipedia list of videogames" class="arrow-link">Wikipedia</a>.</li>
	<li>A <b>Person page</b> is titled according to their commonly-credited English name, for example: "Ted Woolsey", "Koji Kondo", "Alexander O. Smith".</li>
	<li><b>Category</b> names should always be plural.</li>
</ul></p>

<h2 id="category">Categories</h2>
<p>Categorizing pages relates them with other pages. To be succinct, <b>category pages list games and people</b>. A category could be a company, group, game series, game genre, type (of something), or reocurring concept or theme. For example:
	<ul>
		<li><code>Square Enix</code> -- A category that could include company information but also could easily list games and people (much more fitting than "Square Enix games" or "Square Enix developers").</li>
		<li><code>Final Fantasy series</code> -- A page that could easily (and automatically!) list all games and notable personnel.</li>
		<li><code>Mustaches</code> -- Mario, Luigi, Solid Snake, Nobuo Uematsu...</li>
		<li><code>Fangames</code>, <code>Indie games</code>, <code>Games made into movies</code>, <code>Pussies</code> (games about cats, of course)</li>
	</ul></p>
<p><b>Proper category names</b> are always plural. Additionally, besides the first letter of the first word, only proper nouns should be capitalized.</p>
<p>Category pages are different from <b>Topic Pages</b> in that a category lists games and people as well as discussion topics, whereas a topic only lists realted discussions.</p>
<h3>Designating Categories</h3>
<p>Any page can be categoried, even category pages themselves! For example, "Super Mario 64" should be categoried under "Nintendo EAD", which should be categorized under "Nintendo", which should be categorized under "Videogame companies". To categorize a page, designate Parent Categories in the edit form.</p>
<p>Alternatively, you can designate a parent category anywhere in the edit form with the <code>[[Category:]]</code> namespace shortcut. This is helpful if you're already linking to the parent category. For example, if you're linking to <code>[[Nintendo]]</code> already, use <code>[[Category:Nintendo]]</code> to categorieze that page under Nintendo. That being done, you don't have to designate a category in the Parent Categories field, because you already used the namespace shortcut. Handy!</p>

<h2 id="cite">Citing Sources</h2>

<h2 id="redirect">Redirecting</h2>
<p style="font-size:120%;">Use the following code in the Page Content field: <code>#REDIRECT [[PAGE NAME]]</code></p>
<p>A redirect is a page which has no content itself, but sends the reader to another article or page, usually from an alternative title. 
For example, if you type "Warcraft III" in the search box, or follow the wikilink <?=bb2html('[[Warcraft III]]')?>, you will be taken to the article <?=bb2html('[[Warcraft III: Reign of Chaos]]')?>, with a note at the top of the page: "Redirected from Warcraft III". This is because the "Warcraft III" page has the wikitext <code>#REDIRECT [[Warcraft III: Reign of Chaos]]</code>, which defines it as a redirect page and indicates the target article.</p>
<p>It is also possible to redirect to a specific section of the target page, using the <code>[[Page_name#Section_title]]</code> syntax.</p>
<p>Reasons for redirecting include:
	<ul>
		<li>Alternative or foreign names (for example, <code>[[Biohazard]]</code> redirects to <code>[[Resident Evil]]</code>).</li>
		<li>Less specific forms of names or subtitles (the above <code>[[Warcraft III]]</code> example, for example).</li>
		<li>Alternative spellings, punctuation, or numerals (for example, <code>[[Final Fantasy 7]]</code> should redirect to <code>[[Final Fantasy VII]]</code>.</li>
		<li>Representations using ASCII characters (for example, <code>[[Brutal Legend]]</code> redirects to <code>[[Brütal Legend]]</code>).</li>
	</ul>
</p>
<p><b>Do not redirect</b> because of:
	<ul>
		<li>Likely misspellings</li>
		<li>Foreign characters (for example, don't create a page called <code>[[超兄貴]]</code>).</li>
	</ul>
</p>

<?

$page->footer();