<?
use Vgsite\Page;

$page = new Page();
$page->title = "Videogam.in -- Formatting Guide";

$page->freestyle.= '
.section { line-height:20px; position:relative; margin-left:-32px; padding-left:32px; }
.section .permalink { display:none; position:absolute; top:0; left:0; width:32px; height:32px; font:20px/17px Arial; text-decoration:none; text-align:center; }
.section:hover .permalink { display:block; }
.section:target h2 { padding:8px 10px; background-color:black; color:white; }
h2 { margin-top:2em; }
pre { margin-bottom:10px; padding:10px;
background-color:#CCC;
overflow:auto;
width:auto;
max-height:600px;
color:#666;
}
.section.expandable:not(.expanded){ cursor:pointer; }
.section.expanded .expand { display:none; }
pre b { color:black; font-weight:normal; }
code { padding:1px 5px; background-color:#CCC; }
.whitespace { background-color:#FFFFB0; }
.detail { display:none; }
';

$page->javascript.= '
<script>
$(document).ready(function(){
	$(".section.expandable").click(function(){
		if($(this).hasClass("expanded")) return;
		$(this).addClass("expanded").find(".detail").slideDown();
	});
	$(".section.expandable .contract").click(function(event){
		$(this).closest(".detail").slideUp();
		$(this).closest(".section").removeClass("expanded");
		event.stopPropagation();
	});
	$(".section").each(function(){
		$(this).append("<a class=\"permalink\" href=\"#"+$(this).attr("id")+"\" title=\"permanent link to this section\">&sect;</a>");
	});
});
</script>';

$page->header();

?>
<h1>Formatting Guide</h1>

<p>Videogamin uses <a href="http://daringfireball.net/projects/markdown/">Markdown</a>, some <a href="#html">HTML</a>, and other unique markup to help you style your Sblog, Forum, and .Incyclopedia input.</p>

<div id="ItalicsBold" class="section">
	<h2>Italics and Bold</h2>
	<pre>
<b>*</b>This is italicized<b>*</b>, and so is <b>_</b>this<b>_</b>.
<b>**</b>This is bold<b>**</b>, and so is <b>__</b>this<b>__</b>.
Use <b>***</b>italics and bold together<b>***</b> if you <b>___</b>have to<b>___</b>.
</pre>
</div>


<div id="Links" class="section expandable">
	<h2>Links</h2>
	<pre>
Here's an inline link to <b>[Google](http://www.google.com/)</b>.
Here's a reference-style link to <b>[Google][1]</b>.
Here's a very readable link to <b>[Yahoo!][yahoo]</b>.

  <b>[1]:</b> http://www.google.com/
  <b>[yahoo]:</b> http://www.yahoo.com/
</pre>
	<div class="detail">
		<p>
			The link definitions can appear anywhere in the document -- before or after the
			place where you use them. The link definition names <code>[1]</code> and <code>[yahoo]</code>
			can be any unique string, and are case-insensitive; <code>[yahoo]</code> is the
			same as <code>[YAHOO]</code>.
		</p>
    <h3 id="link-advanced-links">Advanced Links</h3>
    <p>
        Links can have a title attribute, which will show up on hover. Title attributes
        can also be added; they are helpful if the link itself is not descriptive enough
        to tell users where they're going.</p>
    <pre>
Here's a <b>[poorly-named link](http://www.google.com/ "Google")</b>.  
Visit <b>[us][web]</b>.

  <b>[web]:</b> http://ourwebsite.com/ "Our Website"
</pre>
		<p>And the result of the above code:</p>
		<pre>
<b>Here's a <a href="http://www.google.com/" title="Google">poorly-named link</a>.
Visit <a href="http://ourwebsite.com/" title="Our Website">us</a>.</b>
</pre>
    <p>
        You can also use standard HTML hyperlink syntax.</p>
    <pre>
<b>&lt;a href="http://example.com" title="example"&gt;example&lt;/a&gt;</b>
</pre>
	<p>URLs enclosed in angle backets will be converted to links, others won't.</p>
	<pre>
<b>The URL I mentioned is &lt;http://www.ratemypoo.com&gt;</b>
</pre>
	<a class="contract">Show less</a>
	</div>
	<a class="expand">Show more</a>
</div>


<div id="Tags" class="section expandable">
	<h2>Tags / Page Links</h2>
	Use double square brackets around any game title, person, company, development group, or topic to link to it's content page at Videogam.in. Optionally, include a vertical bar followed by alternate link words.
	<pre>
<b>[[The Legend of Zelda]]</b> . . . . . . . . . . . . . <a href="/games/The_Legend_of_Zelda">The Legend of Zelda</a>
<b>[[Final Fantasy X-2|a game with hotpants]]</b>  . . . <a href="/games/Final_Fantasy_X-2">a game with hotpants</a>
<b>[[Shigeru Miyamoto]]</b>  . . . . . . . . . . . . . . <a href="/people/Shigeru_Miyamoto">Shigeru Miyamoto</a>
<b>[[Alexander O. Smith|translator extraordinaire]]</b>  <a href="/people/Alexander_O._Smith">translator extraordinaire</a>
<b>[[PlayStation Portable|PSP]]</b>  . . . . . . . . . . <a href="/pages/PlayStation_Portable">PSP</a>
<b>[[Sonic series]]</b>  . . . . . . . . . . . . . . . . <a href="/pages/Sonic_series">Sonic series</a>
<b>[[mustaches]]</b> . . . . . . . . . . . . . . . . . . <a href="/pages/Mustaches">mustaches</a>
</b></pre>
	<p><span class="warn"></b>Page links should be used often and liberally to tag a relevant topic when mentioned in a forum reply, news post, or content page in order to create links and cohesion within the site, and to give readers a reference for more information.</p>
	<div class="detail">
		<p>Videogam.in tags are case-insensitive, so <code>[[Arc the Lad]]</code> and <code>[[Arc The Lad]]</code> link to the same page.</p>
		<p>Sometimes, you can include a special <b>Namespace</b> in your tag to perform a special action. For example: <code>[[Category:Super Nintendo]]</code>, <code>[[Category:Diablo series|a series of RPG games]]</code>, <code>[[Tag:Super Mario Bros.]]</code>, <code>[[Tag:Videogame violence]]</code></p>
		<a class="contract">Show less</a>
	</div>
	<a class="expand">Show more</a>
</div>


<div id="Headings" class="section expandable">
	<h2>Headings</h2>
    <p>
        Underline text to make the two <code>&lt;h1&gt;</code> <code>&lt;h2&gt;</code> top-level
        headings:</p>
    <pre>
Header 1
<b>========</b>

Header 2
<b>--------</b>
</pre>
<div class="detail">
    <p>
        The number of = or - signs doesn't matter; one or two will work. But using enough to underline
        the text makes your titles look better in plain text.</p>
    <p>
        You can also use hash marks:</p>
    <pre>
<b>#</b> Header 1 <b>#</b>
<b>##</b> Header 2 <b>##</b>
</pre>
  <p>Headings can be used to organize an expecially detailed text input into various sections and subsections. You can also use the <code>&lt;!--toc--&gt;</code> tag to output a Table of Contents based on your headings.</p>
  <pre style="white-space:pre-wrap">
<b>In an alternate universe, the villains and heroes of [[Final Fantasy series|Final Fantasy]] wage war in a battle between darkness and light. [[Zidane Tribal|Zidane]] and [[Kuja]], [[Kefka]] and the [[Warrior of Light]], [[Cosmos (Final Fantasy)|Cosmos]] and [[Chaos (Final Fantasy)|Chaos]] fight to determine which is the greater power, creation or destruction.

&lt;!--toc--&gt;

Characters
==========

There are many characters in this game!

Heroes
------

* Warrior of Light ([[Final Fantasy]])
* [[Squall Leonhart]] ([[Final Fantasy VIII]])

Villains
--------

* [[Garland]] (Final Fantasy)
* Kefka ([[Final Fantasy VI]])

Story
=====

The story is really interesting!

Conclusion
==========

This game is going to be great!
</b></pre>
		<a class="contract">Show less</a>
	</div>
	<a class="expand">Show more</a>
</div>


<div id="Lists" class="section expandable">
	<h2>Lists</h2>
	<pre>
<b>-</b> Use a minus sign for a bullet
<b>+</b> Or plus sign
<b>*</b> Or an asterisk
</pre>
    <pre>
<b>1.</b> Numbered lists are easy
<b>2.</b> Markdown keeps track of the numbers for you
<b>7.</b> So this will be item 3.
</pre>
	<div class="detail">
		<p>
			Indent four spaces for each nesting level:
		</p>
    <pre>
<b>-</b> Hyrule
<b>-</b> Mushroom Kingdom
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;</b><b>-</b> Donut Plains
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;</b><b>-</b> Dinosaur Land
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><b>-</b> Yoshi's Island
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;</b><b>-</b> Dry Dry Desert
<b>-</b> Donkey Kong Island
</pre>
    <pre>
<b>1.</b><b class="whitespace">&nbsp;</b>Lists in a list item:
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;</b><b>-</b><b class="whitespace">&nbsp;</b>Indented four spaces.
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><b>*</b><b class="whitespace">&nbsp;</b>indented eight spaces.
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;</b><b>-</b><b class="whitespace">&nbsp;</b>Four spaces again.
<b>2.</b><b class="whitespace">&nbsp;&nbsp;</b>Multiple paragraphs in a list items:
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;</b>It's best to indent the paragraphs four spaces
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;</b>You can get away with three, but it can get
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;</b>confusing when you nest other things.
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;</b>Stick to four.
<b class="whitespace">&nbsp;</b>
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;</b>We indented the first line an extra space to align
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;</b>it with these paragraphs.  In real use, we might do
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;</b>that to the entire list so that all items line up.
<b class="whitespace">&nbsp;</b>
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;</b>This paragraph is still part of the list item, but it looks messy to humans.  So it's a good idea to wrap your nested paragraphs manually, as we did with the first two.
<b class="whitespace">&nbsp;</b>
<b>3.</b><b class="whitespace">&nbsp;</b>Blockquotes in a list item:
<b class="whitespace"> </b>
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;</b><b>&gt;</b> Skip a line and
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;</b><b>&gt;</b> indent the &gt;'s four spaces.
<b class="whitespace">&nbsp;</b>
<b>4.</b><b class="whitespace">&nbsp;</b>Preformatted text in a list item:
<b class="whitespace">&nbsp;</b>
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>Skip a line and indent eight spaces.
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>That's four spaces for the list
<b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>and four to trigger the code block.
</pre>
		<p>
			<span class="warn"></span>Lists must have blank lines before and after them, and there must be a space followed by the list label for each list item:
		</p>
		<pre>
Blah blah blah
1. This list won't work
2. because it doesnt have a blank line before it

-This list also won't work
-Because there's no space after the bullet (-)

<b>*<b class="whitespace">&nbsp;</b>This list
*<b class="whitespace">&nbsp;</b>will work
*<b class="whitespace">&nbsp;</b>hooray!</b>
</pre>
		<a class="contract">Show less</a>
	</div>
	<a class="expand">Show more</a>
</div>


<div id="Definitionlists" class="section">
	<h2>Definition Lists</h2>
	<p>Create a list of terms and defitintions like so:</p>
	<pre>
<b>;</b> Full name <b>::</b> Luigi Mario
<b>;</b> First appearance <b>::</b> [[Mario Bros.]] (1983)
<b>;</b> Affiliations
<b>::</b> [[Mario]] (brother)
<b>::</b> [[Princess Daisy]] (girlfriend)
<b>::</b> [[Waluigi]] (arch-nemesis)
</pre>
</div>


<div id="Blockquotes" class="section expandable">
	<h2>Blockquotes</h2>
	<p>
		Add a <code>&gt;</code> to the beginning of any line to create a <code>&lt;blockquote&gt;</code>.
	</p>
	<pre>
<b>&gt;</b> It's dangerous to go
<b>&gt;</b> alone! Take this.

<b>&gt;</b> Look at you, hacker. A pathetic creature of meat and bone, panting 
and sweating as you run through my corridors. How can you challenge a 
perfect, immortal machine?
</pre>
	<div class="detail">
    <p>
        Blockquotes within a blockquote:
    </p>
    <pre>
<b>&gt;</b> Breaching the battleaxe and dashing into the next room, I was hoping to reach my goal, but instead I heard...
<b>&gt;</b> <b>&gt;</b> Thank you Mario! But our princess is in another castle.
</pre>
    <p>
        Lists in a blockquote:</p>
    <pre>
<b>&gt;</b><b class="whitespace">&nbsp;</b><b>-</b><b class="whitespace">&nbsp;</b>A list in a blockquote
<b>&gt;</b><b class="whitespace">&nbsp;</b><b>-</b><b class="whitespace">&nbsp;</b>With a &gt; and space in front of it
<b>&gt;</b><b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;</b><b>*</b><b class="whitespace">&nbsp;</b>A sublist
</pre>
    <p>
        Preformatted text in a blockquote:</p>
    <pre>
<b>&gt;</b><b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>Indent five spaces total.  The first
<b>&gt;</b><b class="whitespace">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>one is part of the blockquote designator.
</pre>
		<a class="contract">Show less</a>
	</div>
	<a class="expand">Show more</a>
</div>


<div id="Images" class="section expandable">
	<h2>Images</h2>
	<p>
		If you upload your image to Videogam.in, you can add styles, captions, select sizes, and more. To upload an image, click <span class="wmd-button wmd-button-image">image</span> on the toolbar.
	</p>
	<p>
		Display an uploaded image like so: <code>{img:FILENAME|OPTIONS}</code>, where <code>FILENAME</code> is the name of the uploaded file, and <code>OPTIONS</code> are optional attributes separated by the <code>|</code> delimiter.
	</p>
	<pre>
<b>{img:pretzels.jpg|center|caption=These pretzels are making me thirsty}</b>
<b>{img:example.jpg|200px}</b>

Just push <b>{img:Playstation-Button-X.png|inline|caption=X Button}</b> to jump.

<b>{img:example.png|caption=My wonderful screenshot|thumbnail|right}</b>
In this example, our image will align to the right of this text and this text will wrap around the image.
</pre>
	<div class="detail">
		<p>Image options:</p>
		<ul>
			<li><code>caption=CAPTION</code></li>
			<li>Size: <code>thumbnail</code> <code>screenshot</code> <code>small</code> <code>medium</code> <code>large</code> <code>optimal</code> <code>original</code><br/>
			You can also specify a variable size in pixels, ie: <code>100px</code> (620px is the maximum [780px inside an <a href="#Aside">aside</a>]) or percent, ie: <code>50%</code>.</li>
			<li>Position:
				<ul>
					<li><code>left</code> <code>right</code> allow text wrapping on either side of the image</li>
					<li><code>center</code> no text wrapping</li>
					<li><code>inline</code> aligns the image with the text (only useful with tiny images less than 20px in size).</li>
				</ul>
			</li>
		</ul>
		<p>
			To display any image, including one hosted on an external website:</p>
    	<pre>
<b>![My picture](http://i.imgur.com/9nTbl.jpg)</b>
</pre>
			<p>
				We recommend uploading your image to Videogam.in for the added display functionality as well as stability, since sometimes images hosted on external websites are deleted, and use up bandwidth from that site.
			</p>
		<a class="contract">Show less</a>
	</div>
	<a class="expand">Show more</a>
</div>


<div id="Videos" class="section expandable">
	<h2>Videos</h2>
	<p>
		Embed videos with the following code: <code>{video:VIDEO_URL|OPTIONS}</code> where <code>VIDEO_URL</code> is the video page URL (<span class="warn"></span>Currently only <b>YouTube</b> is supported.), and <code>OPTIONS</code> are optional attributes separated by the <code>|</code> delimiter.
	</p>
	<pre>
<b>{video:http://www.youtube.com/watch?v=uI3rO3PbYOo}
{video:http://youtu.be/vejqGtiHx88|500px|left}
{video:https://www.youtube.com/watch?v=FuX5_OWObA0|center|caption=It is a place that you go to when you die.}</b>
</pre>
	<div class="detail">
		<ul>
			<li><code>caption=CAPTION</code></li>
			<li>Size: <code>small</code> <code>medium</code> <code>large</code><br/>
			You can also specify a variable size in pixels, ie: <code>500px</code> (620px is the maximum [780px inside an <a href="#Aside">aside</a>]) or percent, ie: <code>50%</code>.</li>
			<li>Position: <code>left</code> <code>right</code> <code>center</code></li>
		</ul>
		<a class="contract">Hide options</a>
	</div>
	<a class="expand">Show options</a>
</div>


<div id="Audio" class="section expandable">
	<h2>Audio</h2>
	<p>
		Embed audio uploaded via <a href="/posts/manage.php?action=newpost">Sblog</a> with the following code: <code>{audio:FILENAME|OPTIONS}</code> where <code>FILENAME</code> is the uploaded MP3 file or the Sblog ID and <code>OPTIONS</code> are optional attributes separated by the <code>|</code> delimiter.
	</p>
	<pre>
<b>{audio:01ChopChopMasterOnion.mp3}
{audio:03_Vamo_Alla_Flamenco.mp3|200px|left}
{audio:2605|center|caption=Seiken Densetsu 2 - Into the Thick of It composed by [[Hiroki Kikuta]]}</b>
</pre>
	<div class="detail">
		<ul>
			<li><code>caption=CAPTION</code></li>
			<li>Size: <code>small</code> <code>medium</code> <code>large</code><br/>
			You can also specify a variable size in pixels, ie: <code>500px</code> (620px is the maximum) or percent, ie: <code>50%</code>.</li>
			<li>Position: <code>left</code> <code>right</code> <code>center</code></li>
		</ul>
		<a class="contract">Hide options</a>
	</div>
	<a class="expand">Show options</a>
</div>


<div id="Footnotes" class="section expandable">
	<h2>Footnotes and Citations</h2>
	Create a footnote like so <code>[^label]</code>, then add reference details at the end of the text.
	<pre>Here is some unsubstantiated information.<b>[^1]</b> Here is some other information.

Blah blah blah... ...

<b>[^1]: http://www.source.com/example/ Source Name</b>
</pre>
	<div class="detail">
		<p>
			Give reference details in any format, including the given format of <code>URL SOURCE_NAME</code>. Your reference details tag (in this case <code>[^1]: </code>) must be in this exact format, including a colon and one space following the tag.
		</p>
		<p>
			<span class="warn"></span>Any words, facts, and information taken from another source that are not generally known should be cited.
		</p>
		<p>
			The citation label can include multiple letters and numbers (but not spaces or special characters). Here is another example:
		</p>
		<pre style="white-space:pre-wrap">
Shigeru Miyamoto once donned a walrus mustache.<b>[^1]</b> After shaving it off, he used its spent powers to create [[Donkey Kong]], [[Super Mario Bros.]], and [[The Legend of Zelda]]. Some say its powers still linger, long after being shorn.<b>[^ChoudhauryQuote]</b> However, Miyamoto himself denies any connection between his once luxurious mustache and his games.<b>[^1][^2]</b> It is known from contemporary sources that former Nintendo president Hiroshi Yamauchi once used the powers of wispy a knee-length beard to conjure up immense powers, saving the videogame industry with the [[Nintendo Entertainment System]] after the videogame crash of 1983.<b>[^99]</b>

<b>[^1]: http://en.wikipedia.com/Shigeru_Miyamoto Wikipedia: "Shigeru Miyamoto"
[^ChoudhuryQuote]: Videogame historian [Rahula P. Choudhaury](http://www.rahulachoudhaury.com) said in her book, [*Shigeru Miyamoto: The Untold Story*](http://Amazon.com/asin/BS00123), "I believe that Miyamoto's mustache powers are clearly still at work. How can one man go on to create Star Fox, Pikmin, and the like if the powers of the mustache had been drained? I might even suggest that Masahiro Sakurai has tapped into the mustache power from time to time, likely when heading development for Super Smash Bros. Brawl."
[^2]: [Super Mario Wiki: Shigeru Miyamoto](http://www.mariowiki.com/Shigeru_Miyamoto#Mustache)
[^99]: A footnote can include several paragraphs, including this one here. Right after this sentence, we'll start a new paragraph, but **beware**, when you start a new paragraph in a footnote, you must indent it no less than four spaces.

    Can you see the indention to the left? Yes, that's necessary, otherwise this information will be cut off into the general body of the text.</b>
</pre>
		<p>You can also cite general sources used with an empty citation label <code>[^]</code> at the bottom of the text:</p>
		<pre>
<b>[^]: Robert Cheese: "A God Amongst Us: The Biography of Shigeru Miyamoto"
[^]: http://cheeseburger.com/ I Can Has Cheeseburger?
[^]: http://ign.com/wiiu/article/123872 *Wii U specs revealed* (IGN)</b>
</pre>
		<a class="contract">Show less</a>
	</div>
	<a class="expand">More details and examples</a>
</div>


<div id="Linebreaks" class="section">
	<h2>Line breaks</h2>
	<p>
		End a line with two spaces to add a <code>&lt;br/&gt;</code> linebreak:
	</p>
	<pre>
How do I love thee?<span class="whitespace">&nbsp;&nbsp;</span>  
Let me count the ways
</pre>
</div>


<div id="Spoilers" class="section">
	<h2>Spoilers</h2>
	<p>
		Hide spoilers with the <code>[spoiler]</code> tag.
	</p>
	<pre>
In Final Scifi VII <b>[spoiler]Thaeris dies[/spoiler]</b>.
</pre>
</div>


<div id="Aside" class="section expandable">
	<h2>Aside</h2>
	<p>
		An <i>aside</i> contains content that is tangentially related to the article, and could be considered separate from that content. Such sections are often represented as sidebars in printed typography.
	</p>
	<p>
		Create <i>aside</i> content with the following code: <code>[aside|WIDTH]CONTENT[/aside]</code>.
	</p>
	<pre style="white-space:pre-wrap">
<b>[aside|short]See the [Pokedex](/pokedex) for more information on [**Jigglypuff**](/pokedex/Jigglypuff)[/aside]</b> **Jigglypuff** is a Normal-type Pokemon. It evolves from Igglybuff when leveled up with high friendship and evolves into Wigglytuff when exposed to a Moon Stone. Jigglypuff appear to be round, pink balls with small, cat-like ears and very large eyes.
</pre>
	<div class="detail">
		<ul>
			<li><code>WIDTH</code> options:
				<ul>
					<li><code>short</code> a small aside that floats to the left of the article</li>
					<li><code>long</code> a full-length aside that extends from the natural width of 620px to a maximum of 780px</li>
					<li>You can also specify a variable size in pixels <code>500px</code> (780px is the maximum) or percent <code>50%</code></li>
				</ul>
			</li>
		</ul>
		<pre style="white-space:pre-wrap">
<b>[aside|300px]
> &lt;big&gt;We would definitely like to consider the possibility of a sequel. Since it is not possible to get right into development at this moment, please give us a little more time.&lt;/big&gt;
[/aside]</b>
Director [[Tatsuya Kando]] and character designer [[Tetsuya Nomura]] have previously stated they would like to make a sequel, especially due to the reception in the United States. [[The World Ends with You]] sold 410,000 copies in the United States, compared to 210,000 in Japan.

<b>[aside|long]{video:http://www.youtube.com/watch?v=uI3rO3PbYOo|780px|caption=HD video}[/aside]</b>

<b>[aside]{img:Secret_of_mana.jpg|40%}[/aside]</b> **[[Secret of Mana]]** is among the most beloved of [[Square]]'s games for the 16-bit [[Super NES]], featuring colorful graphics, fun gameplay, an enthralling storyline, and unforgettable music. The second title in the [[Mana series]], it set standards for future titles.
</pre>
		<a class="contract">Show less</a>
	</div>
	<a class="expand">More examples and options</a>
</div>


<div id="html" class="section">
	<h2>HTML</h2>
	Allowed tags: 
		<pre style="white-space:pre-wrap"><b>&lt;a&gt;&lt;abbr&gt;&lt;acronym&gt;&lt;aside&gt;&lt;b&gt;&lt;big&gt;&lt;blockquote&gt;&lt;br&gt;&lt;cite&gt;&lt;code&gt;&lt;del&gt;&lt;dl&gt;&lt;dt&gt;&lt;dd&gt;&lt;em&gt;&lt;fieldset&gt;&lt;i&gt;&lt;legend&gt;&lt;li&gt;&lt;ol&gt;&lt;q&gt;&lt;s&gt;&lt;small&gt;&lt;strike&gt;&lt;sub&gt;&lt;sup&gt;&lt;s&gt;&lt;strong&gt;&lt;table&gt;&lt;tbody&gt;&lt;thead&gt;&lt;tfoot&gt;&lt;tr&gt;&lt;td&gt;&lt;th&gt;&lt;ul&gt;</b></pre>
	Allowed attributes: 
		<pre><b>alt href src start title rel width height</b></pre>
	For example, you can use <code>&lt;a href="http://google.com" title="Google"&gt;Google&lt;/a&gt;</code>, but not <code>&lt;big style="color:red;font-size:500px;"&gt;BIG OBNOXIOUS TEXT&lt;/big&gt;</code> since <code>style</code> is not an allowable attribute.
</div>
<?

$page->footer();