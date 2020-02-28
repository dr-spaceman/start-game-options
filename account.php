<?
require $_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php";
$page = new page;

if($_GET['mlist']){
	
	$page->title = "Videogam.in mailing list";
	$page->header();
	$email = base64_decode($_GET['mlist']);
	$q = "SELECT * FROM users WHERE email='".mysqli_real_escape_string($GLOBALS['db']['link'], $email)."' LIMIT 1";
	if(!$usr = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) $page->kill('Sorry, but we couldn\'t find that user account. Please log in and unsubscribe manually from your <a href="http://videogam.in/account.php?edit=prefs">account preferences</a> page. Sorry for the inconvenience.');
	$q = "SELECT * FROM users_prefs WHERE usrid='$usr[usrid]' LIMIT 1";
	if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))){
		$q = "INSERT INTO users_prefs (usrid) VALUES ('$usr[usrid]');";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $page->kill('There was a database error! Please log in and unsubscribe manually from your <a href="http://videogam.in/account.php?edit=prefs">account preferences</a> page. Sorry for the inconvenience.');
	}
	$q = "UPDATE users_prefs SET mail_from_admins = '0' WHERE usrid = '$usr[usrid]'";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) $page->kill('There was a database error! Please log in and unsubscribe manually from your <a href="http://videogam.in/account.php?edit=prefs">account preferences</a> page. Sorry for the inconvenience.');
	
	?>
	You have been successfully unsubscribed.
	<?
	
	$page->kill();
	
}

require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.badges.php";

$page->title = "Videogam.in / Your Account";
$page->css[] = "/bin/css/account.css";
$page->first_section = array("id" => "account");

$edit = $_GET['edit'];
$in = $_POST['in'];

if(!$usrid){
	$page->header();
	?>
	<h1>Please log in</h1>
	<a href="/login.php" title="Log into your account." class="prompt">Log in</a> to access your account.
	<?
	$page->footer();
	exit;
}

$user = new user($usrid);
if($user->notfound) $page->kill("Couldn't find user data for [#".$usrid."]");

$user->getDetails();

$pgsec1 = array("class"=>"pgsec-black", "style"=>"padding-bottom:0;");
$pgsec2 = array("class"=>"pgsec-white", "style"=>"border-width:0;");

($_GET['fbconnectedsuccess'] ? '<script>$.jGrowl("Facebook connection get!")</script>' : '').
($_GET['twconnectedsuccess'] ? '<script>$.jGrowl("Twitter connection get!")</script>' : '').
($_GET['steamconnectedsuccess'] ? '<script>$.jGrowl("Steam connection get!")</script>' : '');

if($edit == 'details') {
	
	// EDIT DETAILS //

  if ($_POST) {
  	
    $fields = array('email');
		foreach ($fields as $field) {
			if ($in[$field] == "") {
				$errors[] = "The <em>$field</em> field is required.";
			}
		}
		
		if(!$in['password2']) unset($in['password1']);
		if ($in['password1'] != $in['password2']) {
			unset($in['password1']);
			$warnings[] = "Passwords didn't match; Your password will remain unchanged";
		}
		
		if($in['email'] != $user->email) {
  		//Check if email address is already registered
		  $q = "SELECT email from users where email = '".$in['email']."' and `usrid` != '$usrid'"; 
		  if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) {
		  	$warnings[] = "The e-mail address <i>".$in['email']."</i> is already registered; Your e-mail address will remain unchanged (".$user->email.")";
		  	$in['email'] = $user->email;
		  } else {
			  //de-validate email
			  $query = "UPDATE `users` SET `verified` = 0 WHERE `usrid` = '$usrid' LIMIT 1";
				if(!$errors) {
					if(!mysqli_query($GLOBALS['db']['link'], $query)) sendBug("Couldn't de-validate user $usrid who changed e-mail address (/account.php)");
				}
			}
		}
		
		if(!$errors) {
			
			//check new usaername
			do if($new_username = $_POST['new_username']){
				$new_username = formatName($new_username);
				if($new_username == ''){
					unset($new_username);
					break;
				}
				$q = "SELECT * FROM users WHERE username = '".mysqli_real_escape_string($GLOBALS['db']['link'], $new_username)."' OR username_old = '".mysqli_real_escape_string($GLOBALS['db']['link'], $new_username)."' LIMIT 1"; 
			  if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))){
					$errors[] = "The  username '$new_username' is already taken.";
			  	unset($new_username);
					break;
			  }
			  if(strlen($new_username) > 15){
			  	$errors[] = "The new username given is too long (> 15 characters)";
			  	unset($new_username);
			  	break;
			  }
			} while(false);
			
			$in['name'] = htmlSC($in['name']);
			$in['location'] = htmlSC($in['location']);
			if ($in['homepage'] == 'http://') $in['homepage'] = '';
			$in['handle'] = htmlSC($in['handle']);
			$in['dob'] = $in['year']."-".$in['month']."-".$in['day'];
			
			if(strlen($in['interests']) > 100){
				$in['interests'] = substr($in['interests'], 0, 100).'&hellip;';
			}
			
			if($in[im][un][0] == "BillyBob64") {
				unset($in[im][cl][0]);
				unset($in[im][un][0]);
			}
			$ims = array();
			for($i = 0; $i <= 6; $i++) {
				if($in[im][cl][$i] != "" && $in[im][un][$i] != "") $ims[] = $in[im][cl][$i].":::".$in[im][un][$i];
			}
			if($ims) $im_str = implode("|||", $ims);
			$im_str = htmlSC($im_str);
			$in[im] = $im_str;
			
			//users main
			$Query = sprintf("UPDATE users SET "
				.($new_username ? "username='".mysqli_real_escape_string($GLOBALS['db']['link'], $new_username)."', username_old='".mysqli_real_escape_string($GLOBALS['db']['link'], $usrname)."'," : "").
		  	($in['password1'] ? "password = password('$in[password1]')," : "")."
	   		email = '%s',
			  region = '".$in['region']."',
				`gender` = '$in[gender]' 
	   		WHERE usrid = '$usrid' LIMIT 1",
	   			mysqli_real_escape_string($GLOBALS['db']['link'], $in[email]));
	   	
	   	//users_details
	   	if($Result = mysqli_query($GLOBALS['db']['link'], "SELECT * FROM users_details WHERE usrid='$usrid' LIMIT 1")) {
				if(mysqli_num_rows($Result)) {
					$Query2 = sprintf("UPDATE users_details SET 
			      name = '%s',
			   		location = '%s',
			   		interests = '%s',
			   		homepage = '%s',
			   		`im` = '%s',
						`handle` = '%s',
						`dob` = '$in[dob]',
						`time_zone` = '$in[time_zone]',
						last_profile_update = now()
			   		WHERE usrid = '$usrid' LIMIT 1",
			   			mysqli_real_escape_string($GLOBALS['db']['link'], $in[name]),
			   			mysqli_real_escape_string($GLOBALS['db']['link'], $in[location]),
			   			mysqli_real_escape_string($GLOBALS['db']['link'], $in[interests]),
			   			mysqli_real_escape_string($GLOBALS['db']['link'], $in[homepage]),
			   			mysqli_real_escape_string($GLOBALS['db']['link'], $im_str),
			   			mysqli_real_escape_string($GLOBALS['db']['link'], $in[handle]));
					if(!mysqli_query($GLOBALS['db']['link'], $Query2)) {
						$errors[] = "Couldn't update details" . ($usrrank >= 8 ? " " . mysqli_error($GLOBALS['db']['link']) : "");
					}
				} else {
					$Query2 = sprintf("INSERT INTO users_details 
						(`usrid`,  `name`, `location`, `time_zone`,      `interests`, `homepage`, `dob`,                           `im`, `handle`, `last_profile_update`) VALUES 
			      ('$usrid', '%s',   '%s',       '$in[time_zone]', '%s',        '%s',       '$in[year]-$in[month]-$in[day]', '%s', '%s',     now())",
			   			mysqli_real_escape_string($GLOBALS['db']['link'], $in[name]),
			   			mysqli_real_escape_string($GLOBALS['db']['link'], $in[location]),
			   			mysqli_real_escape_string($GLOBALS['db']['link'], $in[interests]),
			   			mysqli_real_escape_string($GLOBALS['db']['link'], $in[homepage]),
			   			mysqli_real_escape_string($GLOBALS['db']['link'], $im_str),
			   			mysqli_real_escape_string($GLOBALS['db']['link'], $in[handle]));
					if(!mysqli_query($GLOBALS['db']['link'], $Query2)) {
						$errors[] = "Couldn't update (insert) details";
					}
				}
			}
			
			if (!$errors) {
				//$Query for `users`
	  		if (!mysqli_query($GLOBALS['db']['link'], $Query)) {
	  			$errors[] = "Couldn't update profile because of a database error " . ($usrrank >= 6 ? " [$Query] ".mysqli_error($GLOBALS['db']['link']) : '');
	  		} else {
	  			
	  			$results[] = "Successfully updated your details";
	  			
	  			if($new_username){
	  				$results[] = "Your username was changed from '$usrname' to '$new_username'.";
	  				$in['username_old'] = $usrname;
	  				$usrname = $new_username;
	  				$_SESSION['usrname'] = $new_username;
	  			}
	  			
	  			//badge for profile info
	  			if($in['name'] && $in['location'] && $in['interests'] && $in['handle'] && ($in['month'] > 0) && ($in['day'] > 0)){
	  				$_badges = new badges;
						if($_badges->earn(20)) $page->badges[] = 20;
					}
					
	  		}
			}
			
		}
		
	} else {
		
	  $Query = "SELECT * FROM users LEFT JOIN users_details ON users_details.usrid=users.usrid WHERE users.usrid = '$usrid' LIMIT 1";
	  $Result = mysqli_query($GLOBALS['db']['link'], $Query);
	  while ($row = mysqli_fetch_assoc($Result)) {
	    $in = $row;
	  }
	  
	  if(!$in) {
	  	die("Fatal error: could not get user data.");
	  }
	  
	}
	
	$page->freestyle.= '
		TR.off TD {
			padding: 0 !important;
			border-width: 0 !important; }
		TR.off TD INPUT {
			display: none; }
	';
	
	$page->javascript.= '
	<script type="text/javascript">
		$(document).ready(function(){
			$("#interests").keyup(function(){
				var maxLength     = 100;
				var currentLength = $(this).val().length;
				if(currentLength >= maxLength) $("#interestslen").css("color", "#E41B1B");
	      else $("#interestslen").css("color", "black");
				$("#interestslen").html(maxLength - currentLength);
	    });
		});
	</script>';
	   
	
	if($in[im]) {
		$ims = explode("|||", $in[im]);
		foreach($ims as $i) {
			list($cl, $un) = explode(":::", $i);
			if($cl != "" && $un != "") {
				$im[cl][] = $cl;
				$im[un][] = $un;
			}
		}
	}
	$page->javascript.='<script type="text/javascript">var imnum='.count($im[cl]).';</script>';
	$page->header();
	
	echo accountHeader();
  
	$in = stripslashesDeep($in);
	
	if (!$in[homepage]) $in[homepage] = 'http://';
	
	// register notice
	if ($_GET['justregistered'] == 1){
		
		//badge
		$_badges = new badges;
		if($_badges->earn(1)) $_badges->showEarned(1);
		
		?>
		<div style="margin:0 20px 20px; padding:20px; border-width:0 1px 1px 0; border-style:solid; border-color:#CCC; background-color:white;">
			<h1 style="margin:0"><span style="padding-right:22px; background:url('/bin/img/mascot.png') no-repeat right 70%;">Welcome to Videogam.in</span></h1>
			<p style="font-size:110%; line-height:140%;">Get started by setting up your profile below so other people can get to know you. (Hint: you may earn your <b>second badge</b> if you give enough information about yourself.)<p></p>
		</div>
		<?
		
	}
	
	?>
	
	<form action="account.php?edit=details" method="post" id="edit-details">
		
		<div style="float:left; width:46%;">
			<h2>General Details</h2>
			
			<dl class="col">
			  <dt><label for="name">Name</label></dt>
			  <dd><input type="text" name="in[name]" value="<?=$in['name']?>" id="name" size="35" maxlength="30"/></dd>
			
			  <dt><label for="handle">Handle <a href="" class="tooltip helpinfo" title="A title, nickname, or label that is sometimes displayed below your username (such as in the forums)"><span>?</span></a></label></dt>
			  <dd><input type="text" name="in[handle]" value="<?=$in['handle']?>" id="handle" size="35"<?=($in[handle_lock] ? ' disabled="disabled"' : '')?> maxlength="55"/></dd>
			
			  <dt><label for="homepage">Homepage</label></dt>
			  <dd><input type="text" name="in[homepage]" value="<?=$in[homepage]?>" id="homepage" size="35" maxlength="100" style="color:#06C; text-decoration:underline;"/></dd>
			
			  <dt><label for="location">Location</label></dt>
			  <dd><input type="text" name="in[location]" value="<?=$in[location]?>" id="location" size="35" maxlength="30"/></dd>
			
			  <dt><label for="location">Region <a href="" class="tooltip helpinfo" title="The region where you buy games"><span>?</span></a></label></dt>
			  <dd>
			  	<select name="in[region]">
			  		<?
			  		require_once $_SERVER['DOCUMENT_ROOT']."/pages/include.pages.php";
			  		foreach($pf_regions as $region => $acr){
			  			echo '<option value="'.$acr.'" '.($in['region'] == $acr ? "selected" : "").'>'.$region.'</option>';
			  		}
			  		?>
			  	</select>
			  </dd>
			
			  <dt><label for="interests">About You</label></dt>
			  <dd class="text">
			  	<span style="float:right; color:#666;"><b id="interestslen"><?=(100 - strlen($in['interests']))?></b></span>
			  		In 100 characters or less
			  </dd>
			  <dd>
			  	<textarea name="in[interests]" rows="2" cols="" id="interests"><?=$in[interests]?></textarea>
			  </dd>
			
			  <dt>Gender</dt>
			  <dd>
			  	<select name="in[gender]"><option value="">Not sure</option>
			  		<option value="male"<?=($in[gender] == "male" ? ' selected="selected"' : '')?>>Male</option>
			  		<option value="female"<?=($in[gender] == "female" ? ' selected="selected"' : '')?>>Female</option>
			  		<option value="asexual"<?=($in[gender] == "asexual" ? ' selected="selected"' : '')?>>Asexual or Robot</option>
			  	</select>
			  </dd>
			
			  <dt>Birthdate</dt>
			  <?	list($year, $month, $day) = explode("-", $in[dob]);
			  
			  ?><dd><select name="in[year]"><option value="0000">year</option>
			  		<?	for($y=date('Y'); $y >= 1900; $y--) echo '<option value="'.$y.'" '.($year == $y ? 'selected="selected"' : '').'>'.$y."</option>\n";
			  		?></select> <select name="in[month]"><option value="00">month</option>
			  			<option value="01" <?=($month == '01' ? 'selected="selected"' : '')?>>Jan</option>
			  			<option value="02" <?=($month == '02' ? 'selected="selected"' : '')?>>Feb</option>
			  			<option value="03" <?=($month == '03' ? 'selected="selected"' : '')?>>March</option>
			  			<option value="04" <?=($month == '04' ? 'selected="selected"' : '')?>>April</option>
			  			<option value="05" <?=($month == '05' ? 'selected="selected"' : '')?>>May</option>
			  			<option value="06" <?=($month == '06' ? 'selected="selected"' : '')?>>June</option>
			  			<option value="07" <?=($month == '07' ? 'selected="selected"' : '')?>>July</option>
			  			<option value="08" <?=($month == '08' ? 'selected="selected"' : '')?>>Aug</option>
			  			<option value="09" <?=($month == '09' ? 'selected="selected"' : '')?>>Sept</option>
			  			<option value="10" <?=($month == '10' ? 'selected="selected"' : '')?>>Oct</option>
			  			<option value="11" <?=($month == '11' ? 'selected="selected"' : '')?>>Nov</option>
			  			<option value="12" <?=($month == '12' ? 'selected="selected"' : '')?>>Dec</option>
			  		</select> <select name="in[day]"><option value="00">day</option>
			  		<?	for($d=1; $d <= 31; $d++) {
			  				if($d < 10) $d = '0' . $d;
			  				echo '<option value="'.$d.'" '.($day == $d ? 'selected="selected"' : '').'>'.$d."</option>\n";
			  			}
			  		?></select></dd>
			  		
			  <dt>Time Zone</dt>
				<dd>
					<select name="in[time_zone]" id="time_zone">
						<option value="-5.0">Select Time Zone...</option>
				    <?
				    $tzones = array(
            "0.0" => "GMT +00:00 Britain, Ireland, W. Africa",
            "0.5" => "GMT +00:30",
            "1.0" => "GMT +01:00 Western Europe, C. Africa",
            "1.5" => "GMT +01:30",
            "2.0" => "GMT +02:00 Eastern Europe, E. Africa",
            "2.5" => "GMT +02:30",
            "3.0" => "GMT +03:00 Russia, Saudi Arabia",
            "3.5" => "GMT +03:30",
            "4.0" => "GMT +04:00 Arabian",
            "4.5" => "GMT +04:30",
            "5.0" => "GMT +05:00 West Asia, Pakistan",
            "5.5" => "GMT +05:30 India",
            "6.0" => "GMT +06:00 Central Asia",
            "6.5" => "GMT +06:30",
            "7.0" => "GMT +07:00 Bangkok, Hanoi, Jakarta",
            "7.5" => "GMT +07:30",
            "8.0" => "GMT +08:00 China, Singapore, Taiwan",
            "8.5" => "GMT +08:30",
            "9.0" => "GMT +09:00 Korea, Japan",
            "9.5" => "GMT +09:30 Central Australia",
            "10.0" => "GMT +10:00 Eastern Australia",
            "10.5" => "GMT +10:30",
            "11.0" => "GMT +11:00 Central Pacific",
            "11.5" => "GMT +11:30",
            "12.0" => "GMT +12:00 Fiji, New Zealand",
            "-12.0" => "GMT -12:00 Dateline",
            "-11.5" => "GMT -11:30",
            "-11.0" => "GMT -11:00 Samoa",
            "-10.5" => "GMT -10:30",
            "-10.0" => "GMT -10:00 Hawaiian",
            "-9.5" => "GMT -09:30",
            "-9.0" => "GMT -09:00 Alaska/Pitcairn Isl.",
            "-8.5" => "GMT -08:30",
            "-8.0" => "GMT -08:00 US/Canada/Pacific",
            "-7.5" => "GMT -07:30",
            "-7.0" => "GMT -07:00 US/Canada/Mountain",
            "-6.5" => "GMT -06:30",
            "-6.0" => "GMT -06:00 US/Canada/Central",
            "-5.5" => "GMT -05:30",
            "-5.0" => "GMT -05:00 US/Canada/Eastern, Colombia",
            "-4.5" => "GMT -04:30",
            "-4.0" => "GMT -04:00 W. Brazil, Chile, Atlantic",
            "-3.5" => "GMT -03:30 Newfoundland",
            "-3.0" => "GMT -03:00 Argentina, E. Brazil, Greenland",
            "-2.5" => "GMT -02:30",
            "-2.0" => "GMT -02:00 Mid-Atlantic",
            "-1.5" => "GMT -01:30",
            "-1.0" => "GMT -01:00 Azores/E. Atlantic",
            "-0.5" => "GMT -00:30");
            while(list($key, $val) = each($tzones)) {
            	echo '<option value="'.$key.'"'.($key == $in[time_zone] ? ' selected="selected"' : '').'>'.$val."</option>\n";
            }
            ?>
					</select>
				</dd>
				
				<? if(!$in['username_old']){ ?>
				<dt>Username</dt>
				<dd class="text">
					<span><?=$usrname?> &nbsp; <a onclick="$(this).parent().hide().next().show().closest('dd').next().show();">change</a></span>
					<span class="warn" style="display:none">You can only change your username once</span>
				</dd>
				<dd style="display:none"><input type="text" name="new_username" placeholder="New username"/></dd>
				<? } ?>
			</dl>
			
		</div>
		
		<div style="margin-left:54%;">
			
			<h2>Contact Details</h2>
			<dl>
				<dd><input type="text" name="in[email]" value="<?=$in[email]?>" id="email" placeholder="E-mail address" style="width:192px"/></dd>
				<dd>Your e-mail address is strictly confidential, never shared with a third party, and never shown to any site visitors, <b>ever</b>.</dd>
			</dl>
			
			<div class="hr"></div>
			
			<h2>Password</h2>
			<dl>
				<dd>
					<div style="width:48%; float:left;"><input type="password" name="in[password1]" value="" id="pass1" placeholder="Input a new password" style="width:192px"/></div>
					<div style="margin-left:52%"><input type="password" name="in[password2]" value="" id="pass2" placeholder="Confirm new password" style="width:192px"/></div>
				</dd>
			</dl>
			
			<div class="hr"></div>
			
			<button type="submit" style="font-size:15px; font-weight:bold; padding:5px 12px;">Submit Changes</button>
		
		</div>

	</form>
	<?

  $page->footer();
  exit;
} //end if(edit details)

if($edit == "avatar") {
	
	// AVATAR //
	
	if($_FILES['uploadavatar']['name']){
		// handle upload
		require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/upload_avatar.php";
		
		$upload_avatar_result = uploadAvatar($_FILES['uploadavatar'], "", "c/".$usrid);
		if($upload_avatar_result['filename']){
			$avatar = "c/".$usrid."/".$upload_avatar_result['filename'];
			$q = "UPDATE users SET avatar='$avatar' WHERE usrid='$user->id' LIMIT 1";
			if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Could not record new avatar in database";
			$user->setAvatar($avatar);
		} else {
			$errors[] = $upload_avatar_result['error'];
		}
		
	}
	
	$avtype = substr($user->avatar, 0, 7) == "c/" ? "custom" : "preset";
	
	$page->title.= " / Avatar";
	$page->javascripts[] = "/bin/script/jquery.isotope.js";
	$page->javascript.='
		<script type="text/javascript">
			$(document).ready(function(){
				
				var $avsel = $("#select-avatar");
			
				$avsel.isotope({itemSelector : "li"});
				
				$("#select-avatar li").click(function(){
					$(this).addClass("on").siblings().removeClass("on");
					$avsel.isotope("reLayout");
					$.post(
						"/account.ajax.php",
						{ submit_avatar:1, avatar:$(this).data("file") },
						function(res){
							if(res.errors){ handleErrors(res.errors); }
							if(res.formatted){
								//$.jGrowl("Avatar set.");
								$("#account header .useravatar").remove();
								$("#account header").prepend(res.formatted);
							}
						}
					);
				});
				
			});
		</script>
	';
	$page->header();
	
	echo accountHeader();
	
	?>
	
	<div id="setav">
		
		<form action="account.php?edit=avatar" method="post" enctype="multipart/form-data" id="uploadavatarform">
			<input type="file" name="uploadavatar" id="uploadavatar" style="width:1px; visibility:hidden;" onchange="$(this).parent().submit()"/>
			<button type="button" onclick="$(this).prev().click();">Upload a custom avatar</button>
		</form>
		
		<ul id="select-avatar">
			<?
			//custom avatars
			if ($handle = opendir($_SERVER['DOCUMENT_ROOT']."/bin/img/avatars/c/".$usrid)){
				while (false !== ($file = readdir($handle))) {
					if (substr($file, -3) == "png") {
						$avs_c[] = $file;
					}
				}
			}
			foreach($avs_c as $file) {
				$file = "c/".$usrid."/".$file;
				?>
				<li class="<?=($user->avatar == $file ? 'on' : '')?>" data-file="<?=$file?>">
					<a title="Custom avatar" rel="avatar">
						<img src="/bin/img/avatars/icon/<?=$file?>" alt="Custom avatar" width="48" height="48" class="icon"/>
						<big><img src="/bin/img/avatars/<?=$file?>" alt="Custom avatar"/></big>
					</a>
				</li>
				<?
			}
			
			//preset avatars
			if ($handle = opendir($_SERVER['DOCUMENT_ROOT']."/bin/img/avatars/")){
				while (false !== ($file = readdir($handle))) { 
					if (substr($file, -3) == "png") {
						$avs[] = $file;
					}
				}
			}
			
			//# of uses
			$query = "SELECT * FROM users WHERE avatar != ''";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($row = mysqli_fetch_assoc($res)) {
				$uses[$row['avatar']]++;
			}
			
			natcasesort($avs);
			reset($avs);
			foreach($avs as $file) {
				$filen=ucwords(str_replace(".png","",$file));
				$title = $filen." - ".($uses[$file] ? "Used by ".$uses[$file].($uses[$file] == 1 ? ' user' : ' users') : "Not in use");
				?>
				<li class="<?=($user->avatar == $file ? 'on' : '')?>" data-file="<?=$file?>">
					<a title="<?=$title?>" rel="avatar">
						<img src="/bin/img/avatars/icon/<?=$file?>" alt="<?=$title?>" width="48" height="48" class="icon"/>
						<big><img src="/bin/img/avatars/<?=$file?>" alt="<?=$title?>"/></big>
					</a>
				</li>
				<?
			}
			?>
		</ul>
		
	</div>
	<?
	
	$page->footer();
	exit;
	
} // avatar

if($edit == "prefs") {
	
	// PREFERENCES //
	
	if($_POST) {
		
		//get column names
		$result = mysqli_query($GLOBALS['db']['link'], "SHOW COLUMNS FROM users_prefs");
    while($row = mysqli_fetch_assoc($result)){
			if($row['Field'] != "usrid") $users_prefs_fields[] = $row['Field'];
    }
		
		$q = "DELETE FROM users_prefs WHERE usrid='$usrid'";
		mysqli_query($GLOBALS['db']['link'], $q);
		
		$q = "INSERT INTO `users_prefs` (`usrid`,`".implode("`,`", $users_prefs_fields)."`) VALUES ('$usrid'";
		foreach($users_prefs_fields as $p){
			$q.= ",'".mysqli_real_escape_string($GLOBALS['db']['link'], $_POST['pref'][$p])."'";
		}
		$q.= ");";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't save preferences because of a database error";
		else $results[] = "Preferences saved";
		
	}
	
	$page->freestyle.= '
		#acctprefs fieldset { border:1px solid #CCC; padding:10px 15px; }
		#acctprefs dl { margin:10px; }
		#acctprefs dl dt { margin:0; }
		#acctprefs dl dt + dt { margin-top:5px; }
		#acctprefs dl dd { margin-top:5px; }
	';
	$page->header();
	
	echo accountHeader();
	
	$q = "SELECT * FROM users_prefs WHERE usrid='$usrid' LIMIT 1";
	$pref = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
	
	?>
	<form action="account.php?edit=prefs" method="post" id="acctprefs">
		
		<fieldset style="float:left;">
			<legend>E-mail Notifications</legend>
			Your e-mail address: <code style="font-weight:bold;"><?=$user->email?></code> (<a href="account.php?edit=details">change</a>)
			<dl>
				<dt>
					<label><input type="checkbox" name="pref[mail_from_admins]" value="1" <?=($pref[mail_from_admins] != "0" ? 'checked="checked"' : "")?>/> Receive e-mail messages from Videogam.in administrators</dt>
				<dt>
					<label><input type="checkbox" name="pref[mail_from_users]" value="1" <?=($pref[mail_from_users] != "0" ? 'checked="checked"' : "")?>/> Allow other Videogam.in users to send me e-mail messages (your e-mail address is always kept secret)</label>
				</dt>
				<dt>
					<label><input type="checkbox" name="pref[pm_notify]" value="1" <?=($pref[pm_notify] != "0" ? 'checked="checked"' : "")?>/> Notify me when someone sends me a private message</label>
				</dt>
				<dt>
					<label><input type="checkbox" name="pref[subscribe_sblog]" value="1" <?=($pref[subscribe_sblog] != "0" ? 'checked="checked"' : "")?>/> Notify me when someone comments on my News & Blog posts</label>
				</dt>
				<dt>
					<label><input type="checkbox" name="pref[subscribe_reply]" value="1" <?=($pref[subscribe_reply] != "0" ? 'checked="checked"' : "")?>/> Notify me when someone directly replies to my forum and comment posts</label>
				</dt>
				<dt>
					<label><input type="checkbox" name="pref[watchlist_notify]" value="1" <?=($pref[watchlist_notify] != "0" ? 'checked="checked"' : "")?>/> Notify me when someone edits one of my Watch List pages</label>
				</dt>
				<dd>
					<label><input type="checkbox" name="pref[watchlist_minor_no_notify]" value="1" <?=($pref[watchlist_minor_no_notify] != "0" ? 'checked="checked"' : "")?>/> Don't notify me when someone makes a minor edit</label>
				</dd>
			</dl>
		</fieldset>
		
		<br style="clear:left;"/>
		<br/>
		
		<fieldset style="float:left;">
			<legend>Game Collection</legend>
			<dl>
				<dt>When adding new games...
				<dd><label><input type="radio" name="pref[collection_prepend]" value="0" <?=($pref[collection_prepend] != "1" ? 'checked="checked"' : "")?>/> place them at the <b>bottom</b> (end) of my shelf</dd>
				<dd><label><input type="radio" name="pref[collection_prepend]" value="1" <?=($pref[collection_prepend] == "1" ? 'checked="checked"' : "")?>/> place them at the <b>top</b> (beginning) of my shelf</dd>
			</dl>
			<p><b>Protip</b>: change the default box art you see when adding games by adjusting your Region selection on your <a href="/account.php?edit=details">account details</a> page.</p>
		</fieldset>
		
		<br style="clear:left;"/>
		<br/>
		
		<fieldset style="float:left;">
			<legend>Facebook Connection</legend>
			<?
			$q = "SELECT * FROM users_oauth WHERE usrid='$usrid' AND oauth_provider='facebook' LIMIT 1";
			if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
				?>
				You are connected to Facebook (<a href="http://facebook.com/<?=$row['oauth_username']?>" target="_blank"><?=$row['oauth_username']?></a>) [<a href="/login_fb.php">Reauthorize</a>]
				<dl>
					<dt>Post on my wall...</dt>
					<dd>
						<label><input type="checkbox" name="pref[fb_fan]" value="1" <?=($pref[fb_fan] != "0" ? 'checked="checked"' : "")?>/> Games, people, and other things I Love and Hate</label>
					</dd>
					<dd>
						<label><input type="checkbox" name="pref[fb_collection]" value="1" <?=($pref[fb_collection] != "0" ? 'checked="checked"' : "")?>/> My Game Collection</label>
					</dd>
					<dd>
						<label><input type="checkbox" name="pref[fb_play]" value="1" <?=($pref[fb_play] != "0" ? 'checked="checked"' : "")?>/> Games I'm playing</label>
					</dd>
					<dd>
						<label><input type="checkbox" name="pref[fb_sblog_auth]" value="1" <?=($pref[fb_sblog_auth] != "0" ? 'checked="checked"' : "")?>/> Sblog posts I write</label>
					</dd>
					<dd>
						<label><input type="checkbox" name="pref[fb_sblog_like]" value="1" <?=($pref[fb_sblog_like] != "0" ? 'checked="checked"' : "")?>/> Sblog posts I like</label>
					</dd>
				</dl>
				<?
			} else {
				echo '<a href="/login_fb.php"><img src="/bin/img/fb_connect_160.png" border="0" alt="Connect with Facebook"/></a>';
			}
			?>
		</fieldset>
		
		<br style="clear:left;"/>
		<br/>
		
		<fieldset style="float:left;">
			<legend>Twitter Connection</legend>
			<?
			$q = "SELECT * FROM users_oauth WHERE usrid='$usrid' AND oauth_provider='twitter' LIMIT 1";
			if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
				?>
				You are connected to Twitter (<a href="https://twitter.com/#!/<?=$row['oauth_username']?>" target="_blank">@<?=$row['oauth_username']?></a>) [<a href="/bin/php/twitter/connect.php">Reauthorize</a>]
				<dl style="display:none">
					<dt>Post tweets...</dt>
					<dd>
						<label><input type="checkbox" name="pref[twitter_fan]" value="1" <?=($pref[twitter_fan] != "0" ? 'checked="checked"' : "")?>/> Games, people, and other things I Love and Hate (only when you include remarks)</label>
					</dd>
					<dd>
						<label><input type="checkbox" name="pref[twitter_play]" value="1" <?=($pref[twitter_play] != "0" ? 'checked="checked"' : "")?>/> Games I'm playing (only when you include remarks)</label>
					</dd>
					<dd>
						<label><input type="checkbox" name="pref[twitter_sblog_auth]" value="1" <?=($pref[twitter_sblog_auth] != "0" ? 'checked="checked"' : "")?>/> Sblog posts I write</label>
					</dd>
				</dl>
				<?
			} else {
				echo '<a href="/bin/php/twitter/connect.php"><img src="/bin/img/twitter_connect_160.png" border="0" alt="Connect with Twitter"/></a>';
			}
			?>
		</fieldset>
		
		<br style="clear:left;"/>
		<br/>
		
		<fieldset style="float:left;">
			<legend>Steam Connection</legend>
			<?
			$q = "SELECT * FROM users_oauth WHERE usrid='$usrid' AND oauth_provider='steam' LIMIT 1";
			if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
				?>
				You are connected to Steam (<a href="https://steamcommunity.com/id/<?=$row['oauth_username']?>" target="_blank"><?=$row['oauth_username']?></a>).
				<?
			} else {
				echo '<a href="/login_steam.php"><img src="/bin/img/steam_connect.png" border="0" alt="Connect with Steam"/></a>';
			}
			?>
		</fieldset>
		
		<br style="clear:left;"/>
		<br/>
		
		<input type="submit" name="submit" value="Save Preferences" style="font-size:15px; font-weight:bold;"/>
	</form>
	<?
	
	$page->footer();
	exit;
	
}

if($edit == "games") {
	
	///////////
	// GAMES //
	///////////
	
	require($_SERVER['DOCUMENT_ROOT']."/bin/php/games-collection.php");
	
	$page->javascript.= '<script type="text/javascript" src="/bin/script/games-collection.js"></script>'."\n";
	$page->title.= " / Games";
	$page->header();
	
	echo accountHeader();
	
	?>
	<p class="warn">This feature is coming soon!</p>
	
	<?
	$page->footer();
	exit;
	?>
	
	<table border="0" cellpadding="10" cellspacing="0" style="border:1px solid #CCC;">
		<tr>
			<td style="background-color:#F5F5F5; color:#666;"><b>Settings</b></dd>
			<td style="border-width:0 1px; border-style:solid; border-color:#CCC;">
				Theme: <span id="g-theme-1"><b id="g-theme">Mantle</b> <span style="color:#AAA">[<a href="javascript:void(0)" onclick="toggle('g-theme-2','g-theme-1','inline');">change</a>]</span></span>
				<span id="g-theme-2" style="display:none">
					<select id="select-theme" onchange="document.getElementById('gamebox').className='theme-'+this.options[this.selectedIndex].value.replace(/ /g, '');">
						<option value="Mantle">Mantle (default)</option>
						<option value="Wood Shelf">Wood Shelf</option>
						<option value="Plywood">Plywood</option>
					</select> 
					<input type="button" value="Select" onclick="toggle('g-theme-1','g-theme-2','inline'); document.getElementById('g-theme').innerHTML=document.getElementById('select-theme').value;"/>
				</span>
			</dd>
			<td>
				On your profile page, display: <span id="g-display-1"><b id="g-display">Five random games</b> <span style="color:#AAA">[<a href="javascript:void(0)" onclick="toggle('g-display-2','g-display-1','inline');">change</a>]</span></span>
				<span id="g-display-2" style="display:none">
					<select id="select-display">
						<option value="Five random games">Five random games (default)</option>
						<option value="Most recently added">Most recently added</option>
						<option value="Loved games">Loved games</option>
						<option value="Choose">Choose</option>
					</select> 
					<input type="button" value="Select" onclick="toggle('g-display-1','g-display-2','inline'); document.getElementById('g-display').innerHTML=document.getElementById('select-display').value;"/>
				</span>
			</dd>
		</tr>
	</table>
	
	<div style="clear:both;height:15px;">&nbsp;</div>
	
	<div id="add-game-input">
		<div id="add-game-button"><a href="javascript:void(0)" onclick="toggle('add-game-form','add-game-button');" class="add-button">Add Games</a></div>
		<div id="add-game-form" style="display:none">
			<form action="javascript:void(0)" onsubmit="findGames()">
				<div class="input"><input type="text" value="Game title..." size="40" id="ag-query" class="off" onclick="if(this.value=='Game title...') { this.value=''; this.className=''; }"/></div><a href="javascript:void(0)" onclick="findGames()" class="submit"><img src="/bin/img/search-square.png" alt="search" border="0"/></a>
			</form>
		</div>
	</div>
	<div id="add-game-output" style="display:none">
		<table border="0" cellpadding="0" cellspacing="0" id="ag-title-house">
			<tr>
				<td><div id="add-game-title"></div></dd>
				<td><a href="javascript:void(0)" class="x" onclick="toggle('add-game-input','add-game-output')">X</a></dd>
			</tr>
		</table>
		<div id="add-game-results"><div id="add-game-results-loading"><div id="add-game-results-container"></div></div></div>
	</div>
	
	<div style="clear:both;height:15px;">&nbsp;</div>
	
	<div id="gamebox">
		
		<?
		$query = "SELECT my.*, g.title_url FROM my_games my LEFT JOIN games g USING (gid) WHERE usrid='$user->id' ORDER BY added DESC LIMIT 5";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		if(!$colnum = mysqli_num_rows($res)) {
			echo '<div id="gamebox-space"><div style="padding:15px">'.$user->username.' hasn\'t put any games in '.$genderref[$user->gender].' box yet.</div></div><div id="gamebox-default"></div>';
		} else {
			
			// PROFILE GAMES //
			
			?>
			<div id="gamebox-default" class="row" style="display:none;border-bottom:3px solid black;" onmouseover="toggle('ag-labelof-1','','inline')" onmouseout="toggle('','ag-labelof-1')">
				<div class="hlabel">
					<b>Profile Preview</b>
					<span id="ag-labelof-1" style="display:none"> - what your collection will probably look like on your profile</span>
				</div>
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<?
						while($row = mysqli_fetch_assoc($res)) {
							
							if($row['publication_id']) {
								$img = "/games/files/".$row['gid']."/".$row['gid']."-box-".$row['publication_id']."-sm.png";
								$q = "SELECT * FROM games_publications LEFT JOIN games_platforms USING (platform_id) WHERE id='".$row['publication_id']."' LIMIT 1";
								$x = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
								$row['title'] = $x->title;
								$row['platform'] = $x->platform;
							} elseif($row['platform_id']) {
								$img = "/bin/uploads/user_boxart/".$row['id']."_sm.png";
								$q = "SELECT * FROM games_platforms WHERE platform_id='".$row['platform_id']."' LIMIT 1";
								$x = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
								$row['platform'] = $x->platform;
							} else {
								$row['platform'] = "Unknown platform";
								$img = "";
							}
							if(!$img || !file_exists($_SERVER['DOCUMENT_ROOT'].$img)) {
								$img = "/bin/img/no_box-140.png";
							}
							
							$labels = "";
							if($row['rating'] == 2) {
								$labels.= '<img src="/bin/img/game-rate-love-label.png" alt="love" title="I love this game" border="0" class="tooltip"/>';
							} elseif($row['rating'] == '0') {
								$labels.= '<img src="/bin/img/game-rate-hate-label.png" alt="hate" title="I hate this game" border="0" class="tooltip"/>';
							}
							
							unset($playtitle);
							unset($stuff);
							$stuff = array();
							if($row['own']) $stuff[] = "<b>owns</b>";
							if($row['play']) {
								$playtitle = "I am currently playing this game";
								$stuff[] = "<b>plays</b>";
							}
							if($row['play_online']) {
								if($playtitle) $playtitle.= " and ";
								$playtitle.= "I play this game online";
								if($row['online_id']) $addid = ' ('.$genderref[$user->gender].' online ID is \''.$row['online_id'].'\' if you want to play with '.$genderref2[$user->gender].')';
								else $addid = "";
								$stuff[] = "<b>plays this game online</b>".$addid;
								$push = "";
							} else $push = " this game";
							if($num = count($stuff)) {
								$dostuff = $user->username." ";
								if($num == 1) $dostuff.= $stuff[0];
								elseif($num == 2) $dostuff.= implode(" and ", $stuff);
								elseif($num == 3) $dostuff.= "<b>owns</b>, <b>plays</b>, and <b>plays this game online</b>".$addid;
								$dostuff.= $push.".";
							} else $dostuff = "";
							if($playtitle) {
								$labels.= '<img src="/bin/img/gamebox-label-play.png" alt="play" title="'.$playtitle.'" border="0" class="tooltip"/>';
							}
							
							?>
							<td class="small<?=$ratingclass?>">
								<div class="container">
									<a href="/games/~<?=$row['title_url']?>" title="<i><?=$row['title']?></i><br/><?=$row['platform']?><br/><?=$dostuff?>" class="tooltip"><div class="gamecover"><img src="<?=$img?>" border="0"/></div></a>
									<?=($labels ? '<div class="labels">'.$labels.'</div>' : '')?>
								</div>
							</dd>
							<?
							
						}
						if($colnum < 5) echo '<td width="100%" style="background:url(/bin/img/gamebox-spotlight-na.png) repeat-x 0 0;">&nbsp;</dd>';
						?>
					</tr>
				</table>
			</div>
			<?
			
			// ALL GAMES //
			
			?>
			<div onmouseover="toggle('ag-labelof-2','')" onmouseout="toggle('','ag-labelof-2')">
				<div class="hlabel">
					<b style="float:left">All Games</b>
					<table border="0" cellpadding="0" cellspacing="0" id="ag-labelof-2" style="float:left; display:none;">
						<tr>
							<td><input type="checkbox" checked="checked" id="" style="margin:0 4px 0 10px;"/></dd>
							<td><label>Love</label></dd>
							<td><input type="checkbox" checked="checked" id="" style="margin:0 4px 0 10px;"/></dd>
							<td><label>Hate</label></dd>
							<td><input type="checkbox" checked="checked" id="" style="margin:0 4px 0 10px;"/></dd>
							<td><label>Own</label></dd>
							<td><input type="checkbox" checked="checked" id="" style="margin:0 4px 0 10px;"/></dd>
							<td><label>Play</label></dd>
							<td><input type="checkbox" checked="checked" id="" style="margin:0 4px 0 10px;"/></dd>
							<td><label>Play Online</label></dd>
						</tr>
					</table>
				</div>
				
				<?
				$query = "SELECT my.*, g.title_url FROM my_games my LEFT JOIN games g USING (gid) WHERE usrid='$usrid' ORDER BY added DESC";
				$res   = mysqli_query($GLOBALS['db']['link'], $query);
				$num = mysqli_num_rows($res);
				$height = $num / 7;
				$height = ceil($height) * 165;
				?>
				<div class="list" style="min-height:<?=$height?>px">
					<div id="my-game-edit-space"></div>
					<div id="add-game-loading" class="item" style="display:none">
						<div class="container" style="padding:0 0 30px 45px;"><img src="/bin/img/loading-box.gif" alt="loading"/></div>
					</div>
					<div id="game-additions"></div>
					<?
					
					while($row = mysqli_fetch_assoc($res)) {
						
						if($row['publication_id']) {
							$img = "/games/files/".$row['gid']."/".$row['gid']."-box-".$row['publication_id']."-tn.png";
							$q = "SELECT * FROM games_publications LEFT JOIN games_platforms USING (platform_id) WHERE id='".$row['publication_id']."' LIMIT 1";
							$x = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
							$row['title'] = $x->title;
							$row['platform'] = $x->platform;
						} elseif($row['platform_id']) {
							$img = "/bin/uploads/user_boxart/".$row['id']."_tn.png";
							$q = "SELECT * FROM games_platforms WHERE platform_id='".$row['platform_id']."' LIMIT 1";
							$x = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q));
							$row['platform'] = $x->platform;
						} else {
							$row['platform'] = "Unknown platform";
							$img = "";
						}
						if(!$img || !file_exists($_SERVER['DOCUMENT_ROOT'].$img)) {
							$img = "/bin/img/no_box.png";
						}
						
						$labels = "";
						if($row['rating'] == 2) {
							$labels.= '<img src="/bin/img/game-rate-love-label.png" alt="love" title="I love this game" border="0" class="tooltip"/>';
						} elseif($row['rating'] == '0') {
							$labels.= '<img src="/bin/img/game-rate-hate-label.png" alt="hate" title="I hate this game" border="0" class="tooltip"/>';
						}
						
						unset($playtitle);
						unset($stuff);
						$stuff = array();
						if($row['own']) {
							//$labels.= '<img src="/bin/img/gamebox-label-own.png" alt="own" title="I own this game" border="0" class="tooltip"/>';
							$stuff[] = "<b>owns</b>";
						}
						if($row['play']) {
							$playtitle = "I am currently playing this game";
							$stuff[] = "<b>plays</b>";
						}
						if($row['play_online']) {
							if($playtitle) $playtitle.= " and ";
							$playtitle.= "I play this game online";
							if($row['online_id']) $addid = ' ('.$genderref[$dat->gender].' online ID is \''.$row['online_id'].'\' if you want to play with '.$genderref2[$dat->gender].')';
							else $addid = "";
							$stuff[] = "<b>plays this game online</b>".$addid;
							$push = "";
						} else $push = " this game";
						if($num = count($stuff)) {
							$dostuff = $dat->username." ";
							if($num == 1) $dostuff.= $stuff[0];
							elseif($num == 2) $dostuff.= implode(" and ", $stuff);
							elseif($num == 3) $dostuff.= "<b>owns</b>, <b>plays</b>, and <b>plays this game online</b>".$addid;
							$dostuff.= $push.".";
						} else $dostuff = "";
						if($playtitle) {
							$labels.= '<img src="/bin/img/gamebox-label-play.png" alt="play" title="'.$playtitle.'" border="0" class="tooltip"/>';
						}
						
						?>
						<div class="item">
							<div class="container">
								<div id="my-game-edit-<?=$row['id']?>" class="my-game-edit" title="<?=$row['id']?>" style="display:none">
									<img src="<?=$img?>" alt="<?=htmlSC($row['title'])?>" class="coverimg"/>
									<big><?=$row['title']?></big>
									<p class="platform"><?=$row['platform']?> <span style="color:#333">&middot;</span> <a href="/games/~<?=$row['title_url']?>" class="arrow-right">Game overview</a></p>
									<p><img src="/bin/img/loading-thickbox.gif" alt="loading"/></p>
								</div>
								<div class="shadow">
									<a href="#"><div class="gamecover"><img src="<?=$img?>" border="0"/></div></a>
									<?=($labels ? '<div class="labels">'.$labels.'</div>' : '')?>
								</div>
							</div>
						</div>
						<?
						
					}
					?>
					<br style="clear:both"/>
				</div>
			</div>
			<?
			
		}
		?>
		
	</div>
	
	<?
	
	$page->footer();
	exit;
	
}

///////////
// INDEX //
///////////

$user->getScore();

$page->header();
echo accountHeader();

?>
<div id="acctindex">
	
	<span class="arrow-right"></span>&nbsp;<big>Member since <b><?=formatDate($user->registered)?></b> (<?=timeSince($user->registered)?>)</big>
	<br/><br/>
	
	<div class="score">
		<dl style="">
			<dt style="background-color:#39F;"><?=ceil($user->score['forums'])?></dt>
			<dd>Forum score</dd>
			
			<dt style="background-color:#693;"><?=ceil($user->score['sblogs'])?></dt>
			<dd>Sblog score</dd>
			
			<dt style="background-color:#E11E1E;"><?=ceil($user->score['pages'])?></dt>
			<dd>Page score</dd>
			
			<dt style="background-color:black;"><?=ceil($user->score['total'])?></dt>
			<dd><b>Total score</b></dd>
			
		</dl>
	</div>
	<br/><br/>
	
	<div style="font-size:110%">
		<a href="#calc" class="arrow-toggle" onclick="$(this).toggleClass('arrow-toggle-on').next().slideToggle();">How your score is calculated</a>
		<div style="display:none; line-height:1.5em;">
			<p>Your <b>Forum Score</b> is calculated by the number of posts you make to the <a href="/forums/">forums</a> and comments made to <a href="/posts/">Sblog posts</a>. In addition, there is a multiplier that is calculated based on other users' rating of your posts.</p>
			<p>Similarly, your <b>Sblog score</b> is calculated by the number of posts you make to both the public Sblog and your private blog, with a multiplier calculated from user ratings. The user ratings are generally much higher for public posts than private blogs, since they receive more exposure.</p>
			<p>Your <b>Page Score</b> is calculate based on the quantity, quality, and depth of your edits to <a href="/games/">Games</a>, <a href="/people/">People</a>, <a href="/categories/">Categories</a>, and <a href="/topics/">Topic</a> pages. There is also a multiplier that is based on the number of subjects in which you are "Patron Saint", the user who contributed the most information about that subject.</p>
			
		<?  ?>
	
			<ul>
				<li><b><?=$user->score['vars']['num_forumposts']?></b> Forum & Comment posts</li>
				<li><b><?=$user->score['vars']['forum_rating']?></b> Forum & Comment rating (by other users, sum)</li>
				<li><b><?=$user->score['vars']['num_sblogposts']?></b> Sblog posts</li>
				<li><b><?=$user->score['vars']['sblog_rating']?></b> Sblog post rating (by other users, sum)</li>
				<li><b><?=$user->score['vars']['num_pageedits']?></b> Page Edits</li>
				<li><b><?=$user->score['vars']['num_ps']?></b> Patron Saint pages</li>
				<li><b><?=$user->score['vars']['contribution_score']?></b> Contribution Score (quantity, quality & depth of page edits)</li>
			</ul>
		</div>
	</div>

</div>
<?

$page->footer();


function accountHeader() {
	global $user, $edit;
	return '
<header>
	'.$user->avatar().'
	<h1>'.$user->username.'</h1>
	<p><a href="/~'.$user->username.'" style="font-size:17px;">http://videogam.in/</span>~'.$user->username.'</a></p>
	<nav>
		<ul>
			<li'.($edit == "" ? ' class="on"' : '').'><a href="account.php">You</a></li>
			<li'.($edit == "details" ? ' class="on"' : '').'><a href="account.php?edit=details">Details</a></li>
			<li'.($edit == "avatar" ? ' class="on"' : '').'><a href="account.php?edit=avatar">Avatar</a></li>
			<li'.($edit == "prefs" ? ' class="on"' : '').'><a href="account.php?edit=prefs">Preferences</a></li>
			<li'.($edit == "games" ? ' class="on"' : '').'><a href="/~'.$user->username.'#/collection">Game Collection</a></li>
			<li><a href="/posts/manage.php">News, Blogs & Content posts</a></li>
			<li><a href="~'.$user->username.'">View Your Profile</a></li>
		</ul>
	</nav>
</header>';
}

?>