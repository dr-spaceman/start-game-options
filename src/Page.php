<?php

namespace Vgsite;

class Page {
	static const HTML_TAG = '<!DOCTYPE html><html dir="ltr" lang="en-US" xmlns:fb="http://www.facebook.com/2008/fbml">';

	private $called;
	public $minimalist;      // if true, minimize page bells & whistles
	public $superminimalist; // header fixed to bottom
	public $fullwidth;
	public $title;
	public $dom = array();   // attributes to apply to parts of the page [body, header]
	                      // ie $dom['body']['style'][]='font-size:120%;', $dom['body']['class'][]='className'
	public $css = array(); 	// list of css filenames
	public $freestyle; 			// freestyle css
	public $javascript; 			// freestyle and js via html
	public $javascripts = array(); //list of js filenames
	private $called_sections = array();
	public $meta_data;				// [string] add'l meta tags
	public $meta = array();  // meta data [$property => $content]
	public $fb; 							// connect to facebook
	public $first_section;   // attributes to give to the first page section
	public $badges;

	function __construct()
	{
		$this->called = true;
	}

	function header()
	{
		global $_SESSION['user_rank'];

		$user_id = (int) $_SESSION['user_id'] ?: null;
		if (is_null($user_id) === false) {
			$user = ObjectCache::get(User::class, $user_id);
		}

		if ($this->superminimalist) $this->minimalist = true;

		$this->called_sections['header'] = 1;

		if (!is_array($this->css)) {
			$this->css = array();
		}
		if ($this->css){
			foreach ($this->css as $src) {
				if ($less = substr($src, -5) == ".less") {
					$this->javascripts[] = "/bin/script/less.js";
				}
				if (strpos($print_style, $src) === FALSE) {
					$print_style.= '<link rel="stylesheet'.($less ? '/less' : '').'" type="text/css" href="'.$src.'" media="screen"/>'."\n\t";
				}
			}
		}
		if ($this->freestyle && !strstr($freestyle, '<style')) {
			$print_style.= '<style type="text/css"><!--'.$this->freestyle.'--></style>'."\n\t";
		}

		$meta_replace = array("[GAME_TITLES]", "[GENERIC_KEYWORDS]");
		$meta_with    = array("Final Fantasy,Kingdom Hearts,Chrono Trigger,Seiken Densetsu,Secret of Mana,Legend of Zelda,Mario",
		                      "games,videogames,Square Enix,Nintendo,PlayStation,discussion,wallpaper,music,MP3,movies,coverage,database,sheet music,artwork,screenshots,screens,files,walkthrough");
		if ($this->meta_keywords) {
			$this->meta_keywords = str_replace($meta_replace, $meta_with, $this->meta_keywords);
		} else {
			$this->meta_keywords = $meta_with[1].','.$meta_with[0];
		}
		if(!$this->meta_description) {
			$this->meta_description = "Covering the best games in the universe -- especially Square Enix and Nintendo -- games from Final Fantasy to Zelda to Mario, with comprehensive game guides, music album coverage, game developer profiles, and more.";
		}
		if (is_array($this->meta)) {
			foreach ($this->meta as $property => $content) {
				$this->meta_data.= '<meta property="'.htmlSC($property).'" content="'.htmlSC($content).'"/>'."\n\t";
			}
		}

		if (!$this->title) {
			$this->title = 'Videogam.in, a site about videogames';
		} else {
			$this->title = strip_tags($this->title);
			$this->title = htmlSC($this->title);
		}

		?><?=Page::HTML_TAG?>
		<head<?=$this->head?>>
			<meta charset="UTF-8">
			<title><?=$this->title?></title>
			<meta name="keywords" content="<?=$this->meta_keywords?>"/>
			<meta name="description" content="<?=$this->meta_description?>"/>
			<meta name="DC.title" content="<?=$this->title?>"/>
			<meta property="fb:app_id" content="142628175764082"/>
			<meta property="og:locale" content="en_US"/>
			<?=$this->meta_data?>
			<link rel="shortcut icon" href="/favicon.ico"/>
			<link rel="stylesheet" type="text/css" href="/bin/css/screen.css" media="screen"/>
			<?=$print_style?>
			<script type="text/javascript" src="/bin/script/jquery.1.7.1.js"></script>
			<script type="text/javascript" src="/bin/script/jquery-ui-2.js"></script>
			<script type="text/javascript" src="/bin/script/jquery.cookies.js"></script>
			<script type="text/javascript" src="/bin/script/jquery.address.js"></script>
			<script type="text/javascript" src="/bin/script/jquery.mouseposscroll.js"></script>
			<script type="text/javascript" src="/bin/script/global.js"></script>
			<script type="text/javascript" src="/bin/script/tags.js"></script>
			<?
			if ($this->javascripts) {
				$this->javascripts = array_unique($this->javascripts);
				foreach ($this->javascripts as $src) {
					echo '<script type="text/javascript" src="'.$src.'"></script>'."\n\t";
				}
			}
			?>
			<?=$this->javascript?>
			<script type="text/javascript"><!--Google Analytics-->
			  var _gaq = _gaq || [];
			  _gaq.push(['_setAccount', 'UA-1998327-5']);
			  _gaq.push(['_trackPageview']);
			  (function() {
			    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			  })();
			</script>
		</head>
		<?

		if ($this->minimalist) $this->dom['body']['class'][] = 'min';
		if ($this->superminimalist) $this->dom['body']['class'][] = 'x-min';
		if ($this->fullwidth) $this->dom['body']['class'][] = 'fullwidth';

		$body_class = is_array($this->dom['body']['class']) ? implode(" ", $this->dom['body']['class']) : '';
		$body_style = is_array($this->dom['body']['style']) ? implode(" ", $this->dom['body']['style']) : '';
		$header_class = is_array($this->dom['header']['class']) ? implode(" ", $this->dom['header']['class']) : '';
		$header_style = is_array($this->dom['header']['style']) ? implode(" ", $this->dom['header']['style']) : '';

		$bgn = rand(1,12);
		/*$tweets = array("Son of a submariner!", "What a horrible night to have a curse.", "It's dangerous to go alone! Take this.", "GET THE HECK OUT OF HERE, YOU NERD!", "The cake is a lie", "A Slime draws near! Command?", "You spoony bard!", "I am the reinforcements.", "I FEEL ASLEEP!!!", "Join the Nintendo fun club today! Mac.");
		if($bgn == 6) $tweet = "Come play my lord!";
		elseif($bgn == 1) $tweet = "";
		else $tweet = $tweets[rand(0, (count($tweets) - 1))];*/

		?>
		<body class="<?=$body_class?>" style="<?=$body_style?>">

		<header class="<?=$header_class?>" style="<?=$header_style?>">
			<? if(!$this->superminimalist){ ?>
			<h2><a href="/" title="Videogam.in home page" onmouseover="tweets.init()" onmouseout="$('#twitter_div').hide()">Videogam.in, a site about videogames</a><span></span></h2>
			<div id="twitter_div"><span class="speechpt"></span><span id="tweet"></span></div>
			<div class="bgimg" style="<?=($bgn == 9 || $bgn == 1 ? 'z-index:11;' : '')?>"><img src="/bin/img/headers/<?=$bgn?>.png" alt="" rel="<?=$bgn?>" id="headbgn"/></div>
			<nav id="topnav">
				<dl style="left:0;" class="hovact first-child">
					<dt style="position:relative;">Database<span style="position:absolute; top:6px; width:10px; height:10px; margin-left:5px; background:url('/bin/img/sprites_arrows.png') no-repeat -10px -10px;"></span></dt>
					<dd>
						<ul>
							<li><a href="/games/">Games</a></li>
							<li><a href="/people/">People</a></li>
							<li><a href="/music/">Music</a></li>
						</ul>
					</dd>
					<dd class="more" style="margin-top:16px; padding-top:16px; border-top:1px solid #EEE;">
						<ul>
							<li><a href="/categories/">Categories</a></li>
							<li><a href="/topics/">Topics</a></li>
							<li><a href="/categories/Game_developer">Developers</a></li>
							<li><a href="/categories/Game_series">Franchises</a></li>
							<li><a href="/consoles/">Consoles</a></li>
							<li><a href="/characters/">Characters</a></li>
							<li style="margin-top:16px; padding-top:16px; border-top:1px solid #EEE; white-space:nowrap;"><a href="/content/Special:featured">Featured Pages</a></li>
							<li><a href="/pages/history.php">Recent Edits</a></li>
							<li><a href="/content/Special:most_requested" title="The most requested page content that doesn't yet exist" rel="nofollow" style="white-space:nowrap">Most Requested</a></li>
						</ul>
					</dd>
				</dl>
				<dl style="right:160px;" class="hovact">
					<dt style="position:relative">Site<span style="position:absolute; top:6px; width:10px; height:10px; margin-left:5px; background:url('/bin/img/sprites_arrows.png') no-repeat -10px -10px;"></span></dt>
					<dd>
						<ul>
							<li><a href="/#/posts" title="Videogame News & Blogs">Sblogs</a></li>
							<li><a href="/forums/">Forums</a></li>
							<li><a href="/groups/">Groups</a></li>
						</ul>
					</dd>
					<dd class="more" style="margin-top:16px; padding-top:16px; border-top:1px solid #EEE;">
						<ul>
							<li><a title="post a new news article" href="/posts/manage.php?action=newpost"><b class="plus">+</b> Sblog Post</a></li>
							<li><a title="create a new Videogam.in Game, Person, Category, or Topic page" href="/content/Special:new"><b class="plus">+</b> Content Page</a></li>
							<li style="white-space:nowrap;"><a title="upload images to save and share" href="/upload.php" rel="nofollow"><b class="plus">+</b> Upload Images</a></li>
						</ul>
					</dd>
				</dl>
				<dl style="width:120px; right:0; border-top:19px solid transparent; <?=(!$user_id ? 'background-color:transparent !important; border-color:transparent !important;' : '')?>" class="last-child hovact">
					<?
					if ($user_id) {
						$new_pms = PrivateMessage::checkForNew($user);
						?>
						<dd class="usr"><?=$user->output()?></dd>
						<dd class="more">
							<ul>
								<li><a href="/account.php" rel="nofollow">Account</a></li>
								<li><a href="/~<?=$user->username?>#/collection">Game Collection</a></li>
								<li><a href="/pages/watchlist.php" rel="nofollow">Watch List</a></li>
								<li><a href="/uploads.php" rel="nofollow">Uploads</a></li>
								<li><a href="/posts/manage.php" title="manage your Sblog posts" rel="nofollow">Sblog Manager</a></li>
								<li><a href="/messages.php" rel="nofollow">Messaging</a></li>
								<?=($_SESSION['user_rank'] >= User::ADMIN ? '<li><a href="/ninadmin/" rel="nofollow">Admin Panel</a></li>' : '')?>
								<li><a href="?do=logout" rel="nofollow">Log out</a></li>
							</ul>
						</dd>
						<?
					} else {
						?>
						<dd style="white-space:nowrap; color:RGBA(0,0,0,.15);"><a href="/login.php" style="padding-left:18px; background:url('/bin/img/icons/question_block.png') no-repeat left center;">Log in</a></dd>
						<?
					}
					?>
				</dl>
				<div id="topsearch">
					<span style="display:none; width:100%; position:absolute; top:-40px; font-size:12px;">Input a database search term to navigate<br/>or click enter to search the whole site 
						<a href="#close" onclick="$('#topsearch').removeClass('foc').prevUntil('.first-child').show()" class="ximg" style="top:5px; right:0;">x</a></span>
					<form action="/search.php" method="get" name="topsearch">
						<input type="text" name="q" value="" tabindex="30" id="topsearchin"/>
						<a href="#submit" title="Submit search query" class="preventdefault" tabindex="31" onclick="if($(this).next().val()) document.topsearch.submit()">Search</a>
					</form>
					<div id="topsearch-results"></div>
				</div>
				<?=($new_pms ? '<div id="newpms"><a href="/messages.php"><b>'.$new_pms.'</b> new message'.($new_pms != 1 ? 's' : '').'</a></div>' : '')?>
				<span style="display:block; width:1px; height:105px; position:absolute; left:0; top:0; background:url('/bin/img/dotline_y.png') repeat-y 0 -1px;"></span>
				<span style="display:block; width:1px; height:105px; position:absolute; right:300px; top:0; background:url('/bin/img/dotline_y.png') repeat-y 0 -1px;"></span>
			</nav>
			<? } else { ?>
			<h2 class="first-child"><a href="/" title="Videogam.in home page">Videogam.in, a site about videogames</a><span></span></h2>
			<div id="topsearch">
				<form action="/search.php" method="get" name="topsearch">
					<a href="#submit" title="Submit search query" class="preventdefault" tabindex="31" onclick="if($(this).next().val()) document.topsearch.submit()">Search</a>
					<input type="text" name="q" value="" tabindex="30" id="topsearchin"/>
				</form>
			</div>
			<? } ?>
		</header>
		<?

		if($this->fb){
		?>
		<div id="fb-root"></div>
		<script src="http://connect.facebook.net/en_US/all.js"></script>
		<script>FB.init({ appId:'142628175764082', cookie:true, status:true, xfbml:true, oauth:true });</script>
		<?
		}

		if(!$_COOKIE['colophon']){
			?>
			<div id="colophon" style="position:fixed; z-index:999; right:0; bottom:0; left:0; background-color:black; font-size:15px; color:#BBB; -moz-box-shadow:0 0 10px -5px black;" onclick="$.cookie('colophon', '1', {expires:365, path:'/'});">
				
				<div style="width:960px; padding:30px 0; margin:0 auto; text-align:center;">
					Welcome to Videogam.in, a site about videogames. <b><a href="/about.php" style="color:#fff;">Read more</a></b> about this site or else <a href="#close" title="hide this message and don't show it to me again" class="tooltip" style="color:#fff;" onclick="$('#colophon').hide();">pay me for the door repair charge</a>.
				</div>
				
				<div style="position:absolute; z-index:2; top:10px; left:50%; width:192px; height:16px; margin:0 0 0 -96px; background:url('/bin/img/promo/welcome.png') no-repeat 0 0;"></div>
				<div style="position:absolute; z-index:2; bottom:0; left:50%; width:192px; height:18px; margin:0 0 0 -96px; background:url('/bin/img/promo/welcome.png') no-repeat 0 -16px;"></div>
				<div style="position:absolute; z-index:1; right:0; bottom:0; left:0; width:100%; height:18px; background:url('/bin/img/promo/welcome.png') repeat-x 0 -34px;"></div>
				
			</div>
			<?
		}

		if(!$this->no_first_section) $this->openSection($this->first_section);

	}

function openSection($sec = array(), $contain=false){
	
	// create a new page body section
	// @param $sec array [ id, class, style ] attributes to give the new section
	// @param $contain if true, the section wrapper will not be centered, but the container will
	
	$this->num_open_sections++;
	if($sec['contain']) $contain = true;
	if($contain) $this->contained_sections++;
	if(is_array($sec['data'])){
		foreach($sec['data'] as $key => $val) $data.= ' data-'.$key.'="'.htmlsc($val).'"';
	}
	
	echo '<section id="'.$sec['id'].'" class="'.$sec['class'].($contain?' contained':'').'" style="'.$sec['style'].'"'.$data.'>'.($contain ? '<div class="container">' : '');
	
}

function closeSection($txt=''){
	
	if($this->num_open_sections < 1) return;
	
	$this->num_open_sections--;
	
	echo $txt;
	
	if($this->contained_sections){
		$this->contained_sections--;
		echo '</div><!--section .container-->';
	}
	
	echo '</section>';
	
}

function closeAllSections(){
	while($this->num_open_sections > 0) {
		$this->closeSection();
	}
}
	

function footer() {

global $user_id, $usrname, $results, $errors, $warnings;

$this->called_sections['footer'] = 1;

$i = 0;
while($this->num_open_sections > 0) {
	$txt = (++$i == 1 ? '<div id="pgsecbuff"><div></div></div>' : ''); //give the current open section a padding-bottom
	$this->closeSection($txt);
}

?>
<footer>
	<div class="container">
					
		<div class="feedback c1r">
			<h5>Feedback</h5>
			<form action="" method="" id="footfeedback">
				<div class="inpfw">
					<textarea name="message" rows="" cols="" tabindex="20" placeholder="Send a quick comment, suggestion, question, or report a bug" class="ff inp" onfocus="$(this).css('height','90px'); $('#footfeedback-email').show();"></textarea>
				</div>
				
				<div style="height:5px"></div>
				
				<button type="submit" tabindex="23" id="footfeedbacksend" style="float:right;">Send Query</button>
				
				<div id="footfeedback-email" style="display:none; margin:0 110px 0 0;">
					<input type="text" name="email" value="" tabindex="21" placeholder="E-mail (optional)" class="inp" style="width:100%;"/>
				</div>
				
				<input type="hidden" name="frompage" value="<?=$_SERVER['REQUEST_URI']?>"/>
				<textarea name="errors" style="display:none" id="feedback-errorlog"></textarea>
				
				<input name="name" value="" id="feedback-inp-name"/>
				
				<div style="display:none; margin:3px 0 0;">
					<?
					$rand1 = rand(0,4);
					$rand2 = rand(1,5);
					?>
					<input type="hidden" name="math1" value="<?=$rand1?>"/>
					<input type="hidden" name="math2" value="<?=$rand2?>"/>
					<b><?=$rand1?></b> + <b><?=$rand2?></b> = 
					<input type="text" name="math" size="1" maxlength="1" tabindex="22" class="inp"/>
				</div>
			</form>
		</div>
		
		<div class="c2">
			<h5 style="font-weight:normal;">
				<b>&copy; <?=date("Y")?> Videogam.in</b> a website about videogames
			</h5>
			<ol class="about">
				<li><a href="/terms.php">Terms of Use</a></li>
				<li><a href="/about.php">About Videogam.in</a></li>
				<li><a href="/jobs.php">Join Us</a></li>
				<li><a href="/contact.php">Contact Us</a></li>
				<li><a href="http://twitter.com/videogamin" title="Follow Videogam.in on Twitter" style="padding-left:20px; background:url(/bin/img/icons/twitter_sm.png) no-repeat left center;">Follow Us</a></li>
				<li><a href="http://www.facebook.com/pages/Videogamin/113351978705186" title="Find us on Facebook" style="margin:-2px 0 0; padding:2px 3px 2px 20px; background:#eceef5 url(/bin/img/icons/fb_thumbsup.png) no-repeat left center; font-size:11px; color:#3B5998; border-radius:2px; -moz-border-radius:2px; -webkit-border-radius:2px; text-decoration:none;">Like</a></li>
				<!--<li><a href="#" style="padding-left:20px; background-image:url(/bin/img/icons/rss.png);">Subscribe</a></li>-->
			</ol>
			<br style="clear:left;"/>
			<br/>
			
			<!--<p><b>COLOPHON</b> &middot; Videogam.in is a website about videogames. 
			It's a place where everyone can read and contribute information about <a href="/games/">games</a> and the <a href="/people/">people</a> who make them. 
			It's also a place to follow, review, collect, and show off your favorite (and least favorite) games.</p>
			
			<p>Videogam.in is a site where serious gamers can <a href="/forums">discuss</a> and <a href="/posts/manage.php?action=newpost">write</a> about gaming topics in an unserious manner. 
			Its edge lies in its unique ability to speak vapidly about otherwise serious issues, take videogames only as seriously as its own college drunkenness and inability to graduate within four years, and ultimately: the vapid community of vulgar buffoonery that lurk within its dank confines.</p>-->
			
			<? if(!$this->minimalist){ ?>
			<div class="featuredcont">
			<h5>Featured Content</h5>
				<ul>
					<li><a href="/games/Vagrant_Story">Vagrant Story</a></li>
					<li><a href="/games/Banjo-Kazooie">Banjo-Kazooie</a></li>
					<li><a href="/topics/Cosplay">Cosplay</a></li>
					<li><a href="/categories/Nintendo_3DS">Nintendo 3DS</a></li>
					<li><a href="/topics/Videogame_violence">Videogame violence</a></li>
					<li><a href="/categories/Square_Enix">Square Enix</a></li>
					<li><a href="/people/Akira_Yamaoka">Akira Yamoka</a></li>
					<li><a href="/people/Hironobu_Sakaguchi">Hironobu Sakaguchi</a></li>
					<li><a href="/categories/Peppy_Hare">Peppy Hare</a></li>
				</ul>
				<div style="clear:left; height:0;"></div>
			</div>
			<? } ?>
		</div>
	</div>
	<? if(!$this->minimalist){ ?>
	<div class="diorama">
		<!--<div style="position:absolute; z-index:1; bottom:0; right:0; left:0; width:100; height:10px; background:url(/bin/img/footer_lava.png);"></div>-->
		<div style="position:absolute; z-index:2; bottom:0; left:0; width:19px; height:10px; background:url(/bin/img/footer_stones.png);"></div>
		<div style="position:absolute; z-index:2; bottom:0; right:0; width:19px; height:10px; background:url(/bin/img/footer_stones.png);"></div>
		<div style="position:absolute; z-index:1; bottom:0; left:0; right:0; width:100%; height:10px; background:url(/bin/img/footer_bridge.png);"></div>
		<div style="position:absolute; z-index:1; bottom:10px; right:0;"><img src="/bin/img/footer_switch.png"/></div>
		<div style="position:absolute; z-index:1; bottom:10px; right:32%;"><img src="/bin/img/footer_scene.png"/></div>
		<div style="position:absolute; z-index:0; bottom:0; right:0; left:0; width:100%; height:250px; background:url(/bin/img/gradient_black_250.png);"></div>
	</div>
	<? } ?>
</footer>
<?

if(!$usrid){
	?>
	<div id="login">
		<a href="#cancelLogin" id="login-close">Close</a>
		<div class="fbconnect">
			<a href="/login_fb.php" style="display:block;" onclick="$.cookie('lastpage', '<?=$_SERVER['REQUEST_URI']?>', {expires:1, path:'/'})"><img src="/bin/img/fbconnect_225.png" width="225" height="33" alt="Login with Facebook"/></a>
			<a href="/login_steam.php" style="display:block; margin:12px 0 0 -31px;" onclick="$.cookie('lastpage', '<?=$_SERVER['REQUEST_URI']?>', {expires:1, path:'/'})"><img src="/bin/img/steam_connect_225.png" width="256" height="33" alt="Login with Steam"/></a>
		</div>
		<form method="post" action="<?=$_SERVER['REQUEST_URI']?>">
	    <input type="hidden" name="do" value="login"/>
	    <div>
	    	<input type="text" name="username" placeholder="Videogam.in username or e-mail" id="login-username" maxlength="25"/>
	    </div>
	    <div>
	    	<input type="password" name="password" placeholder="Password" id="login-password" maxlength="25"/>
	    </div>
	    <div style="margin-right:0 !important;">
	    	<label style="display:block; float:right; margin:5px 0 0;"><input type="checkbox" name="remember" value="1"/> Remember Me</label>
	    	<button type="submit" name="login">Log in</button>
	    </div>
	    <div style="height:0;"></div>
	  </form>
		<ul>
			<li><a href="/retrieve-pass.php">Reset password</a></li>
			<li><a href="/register.php">Register a new account</a></li>
		</ul>
	</div>
	<div id="login-overlay" class="bodyoverlay"><img src="/bin/img/promo/pit.png" style="position:fixed; bottom:0; left:50px;"/></div>
	<?
}

$notifications = '';
if($errors) {
	foreach($errors as $err) $notifications.= "$.jGrowl('".addslashes($err)."', {sticky:true});";
}
if($warnings) {
	foreach($warnings as $err) $notifications.= "$.jGrowl('".addslashes($err)."', {sticky:true});";
}
if($results) {
	foreach($results as $err) $notifications.= "$.jGrowl('".addslashes($err)."', {sticky:false});";
}
if($notifications) echo '<script>'.$notifications.'</script>';
?>

<div id="alert" class="alert">
	<a href="#close" class="closealert ximg" onclick="$(this).parent().fadeOut();">close</a>
	<dl></dl>
</div>

<div class="bodyoverlay"></div>

<div id="loading2"></div>

<!--top hr/bg etc-->
<span id="headerbg"></span>

<?

// display new badges

//Resetti badge
if($_COOKIE['unsavedSess']){
	echo '<script type="text/javascript"> $.cookie("unsavedSess", null, {path:"/"}); </script>';
	Badge::findById(54)->earn($user);
}

if($_SESSION['newbadges'] || $this->badges){
	if($_SESSION['newbadges']){
		foreach($_SESSION['newbadges'] as $badgeid) $this->badges[] = $badgeid;
	}
	foreach($this->badges as $badgeid) echo $_badges->showEarned($badgeid);
	unset($_SESSION['newbadges']);
}
?>

<input type="hidden" name="usrid" value="<?=$usrid?>" id="usrid"/>

</body>
</html>
<?

//close db connection
exit;

}

function kill($txt=''){
	if($txt == 404){
		if(!$this->called_sections['header']){ require $_SERVER['DOCUMENT_ROOT']."/404.php"; exit; }
	}
	if(!$this->called_sections['header']) $this->header();
	$this->closeAllSections();
	$this->openSection();
	if($txt == 404) echo 'Error: Page not found :(';
	else echo $txt;
	$this->closeSection();
	$this->footer();
	exit;
	
}

} 