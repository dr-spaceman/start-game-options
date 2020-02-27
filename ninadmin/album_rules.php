<?

$series = array(
'',
'Breath of Fire',
'Chocobo',
'Chrono',
'Final Fantasy',
'Final Fantasy spinoffs',
'Final Fantasy Tactics',
'Front Mission',
'Ogre Battle',
'Parasite Eve',
'SaGa',
'Seiken Densetsu',
'Star Ocean'
);


$composer = array(
'Nobuo Uematsu',
'Yasunori Mitsuda',
'Yoko Shimomura',
'Hitoshi Sakimoto',
'Kenji Ito',
'Hiroki Kikuta',
'Masaharu Iwata',
'Masashi Hamauzu',
'Junya Nakano',
'Noriko Matsueda',
'Naoshi Mizuta',
'Kumi Tanioka',
'Tsuyoshi Sekito',
'Takahito Eguchi',
'Koichi Sugiyama',
'Motoi Sakuraba',
'Hayato Matsuo',
'Ryuji Sasai');


$trackrule = <<<END
<h3>Track List Rules</h3>
Considering the format for the track lists is severely limited, I have had to write these damn guidelines...<br/><br/>

One thing to remember while doing this: <strong>if something is hard to classify, leave it blank.<br/><br/></strong>

<strong>TRACK NAME<br/></strong>
Come on.  Not that hard!<br/><br/>

<strong>ARTIST<br/></strong>
<div style="padding: 0px 0px 0px 20px;">
The point of this is to give credit to individual tracks.<br/><br/>

If the <strong>set of arrangers</strong> is the same as the <strong>set of composers</strong>, put down just the composer with the assumption that the composer and arranger are the same.<br/><br/>

If the <strong>set of arrangers</strong> is <strong>NOT</strong> the same as the <strong>set of composers</strong>, we have a sticky situation.  Put the original composer <strong>in SQUARE brackets in the TRACK NAME field</strong>.  Put the arranger in the ARTIST field.  <strong>LAST NAMES only, please</strong>.  This is especially important when you have two or more names in this field.<br/><br/>

<strong>All vocal performers go in parentheses</strong> after arrangers and composers.<br/><br/>

Here's <a href="/music/?id=DPCX-5019" target="_blank">an example</a> of how it should be.<br/><br/></div>

<strong>TYPE<br/></strong>
<div style="padding: 0px 0px 0px 20px;">Enter a <strong>GENERIC</strong> description.  We want to give an idea, not write commentary.  These standard descriptions are <strong>HIGHLY RECOMMENDED</strong> (read: do it or die!):<br/><br/>

<strong>Field </strong> => Location<br/>
<strong>Event</strong> => (sequence, opening movie, whatever)<br/>
<strong>Theme</strong> => Theme (any songs with lyrics, opening themes, game themes that <strong>transcend</strong> game events, meaning they represent the game itself...  For example, "Tidus and Yuna swimming in the lake" CG is NOT an in-game event, but an excuse to play Suteki da ne!)<br/>
<strong>Character</strong> => Character (character theme)<br/>
<strong>Battle</strong> => Regular battle<br/>
<strong>Boss battle</strong> => Boss battle<br/>
<strong>Fanfare</strong> => (victory! or "game over," which is technically not a fanfare, but whatever, etc.)<br/>
<strong>Ending</strong> => Anything ending-related<br/><br/>
<strong>Medley</strong> => A medley of different tracks.  The medley itself is not played in the original game.<br/><br/>

<strong>If TYPE doesn't really apply, especially to bonus tracks</strong>, put the appropriate description in parentheses. (Bonus track) for bonus track, etc., (Unused track) for a track not used in the game, etc....<br/><br/>

If the album features <strong>music from several games</strong>, you should include the game name here as well, preferably abbreviated (i.e., FFVII instead of Final Fantasy VII) in parentheses after the type description.<br/><br/>

Do not make meaningless specifications.  Most people can figure out that "boss battle" music is more "intense" than the regular music.  But "Story boss battle" or "major boss battle" doesn't mean anything if you don't include anything for...<br/><br/></div>

<strong>IN-GAME LOCATION<br/></strong>
<div style="padding: 0px 0px 0px 20px;">This specifies where exactly the music is played <strong>in the game in question</strong> (if played a lot, where most frequently or what kind of SPECIFIC situation).  Of course, if it is even necessary to ask that question, you need to <strong>put the game title in the TYPE field</strong> (read above for more).  If it's a "game over" theme, put in "Game over."  Attack on Dollet?  "Attack on Dollet."  Capitalize the first word, not all of it. Don't put anything in for regular battle themes that are played throughout the game (unless it varies based on where you are in the game).  Ask if you need more clarification.<br/><br/>

If you can't put something in specific (like a location name), yet want to put in SOMETHING, enclose a general description in parentheses.  Use your own discretion.<br/><br/>

I don't think we have to be too specific about it...<br/><br/></div>

I must emphasize this: <strong>DO NOT CAPITALIZE THESE KEYWORDS</strong>.  It looks dorky.<br/><br/>
END;

$step4msg = <<<END
Select only the games <strong>directly related</strong> to this album.  For example, for the Final Fantasy vocal collections, you must select all the corresponding Final Fantasies whose music appears on the album.<br/><br/>

If only one game is selected, this album's title bar will link directly to this game.  If more than one is selected, this direct link is deactivated.<br/><br/>
END;

$step5msg = <<<END
Please select only the albums that are <strong>strongly related</strong>.<br/><br/>
END;

$tracksource = <<<END
<div style="border-style: solid; border-width: 1px; padding: 10px;">
<strong>TRACK LIST SOURCES<br/></strong>
Only use these fields if you don't feel like submitting your own translations.  It's much better (for credibility) to use a bunch of sources than to take crappy translations from only one source, so please cite everyone you use.  Thanks.<br/><br/>
<table border="0" cellpadding="0" width="100%" cellspacing="5">
<tr>
<td><strong>Name of source/person</strong></td>
<td><strong>Address/e-mail of source/person</strong></td>
</tr>
<tr>
<td><input type="text" value="$tsrc1[0]" name="tsrc1[0]" maxlength="255"></td>
<td><input type="text" value="$taddr1[0]" name="taddr1[0]" maxlength="255"></td>
</tr>
<tr>
<td><input type="text" value="$tsrc1[1]" name="tsrc1[1]" maxlength="255"></td>
<td><input type="text" value="$taddr1[1]" name="taddr1[1]" maxlength="255"></td>
</tr>
<tr>
<td><input type="text" value="$tsrc1[2]" name="tsrc1[2]" maxlength="255"></td>
<td><input type="text" value="$taddr1[2]" name="taddr1[2]" maxlength="255"></td>
</tr>
<tr>
<td><input type="text" value="$tsrc1[3]" name="tsrc1[3]" maxlength="255"></td>
<td><input type="text" value="$taddr1[3]" name="taddr1[3]" maxlength="255"></td>
</tr>
<tr>
<td><input type="text" value="$tsrc1[4]" name="tsrc1[4]" maxlength="255"></td>
<td><input type="text" value="$taddr1[4]" name="taddr1[4]" maxlength="255"></td>
</tr>
</table><br/></div><br/>
END;


?>