<?
error_reporting(0);
function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars){
	$errortype = array (E_ERROR=>'Error',E_WARNING=>'Warning',E_PARSE=>'Parsing Error',E_NOTICE=>'Notice',E_CORE_ERROR=>'Core Error',E_CORE_WARNING=>'Core Warning',E_COMPILE_ERROR=>'Compile Error',E_COMPILE_WARNING=>'Compile Warning',E_USER_ERROR=>'User Error',E_USER_WARNING=>'User Warning',E_USER_NOTICE=>'User Notice',E_STRICT=>'Runtime Notice',E_RECOVERABLE_ERROR=>'Catchable Fatal Error');
	if($errortype[$errno] == "Notice") return; //Don't show notices
	$GLOBALS['errors'][] = $errmsg . ' [ERROR '.$linenum.'] ['.$filename.']' . "\n";
	$GLOBALS['ret']['error_vars'] = $vars; //?
}
$old_error_handler = set_error_handler("userErrorHandler");

use Vgsite\Page;
use Verot\Upload;
use Vgsite\Badge;
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.posts.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/class.ajax.php";
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.tags.php");

if($_POST['submit_post_action'] && $_POST['ajaxforminput']){
	
	////////////
	// SUBMIT //
	////////////
	
	$submit_action = $_POST['submit_post_action'];
	
	parse_str($_POST['ajaxforminput'], $ajaxforminput);
	
	if($ajaxforminput['in'])   $in   = $ajaxforminput['in'];
	if($ajaxforminput['cont']) $cont = $ajaxforminput['cont'];
	
	$a = new ajax();
	
	//there has to be a session id
	if($in['session_id']) $session_id = $in['session_id'];
	else $a->kill("Critical error: no session id registered");
	
	//user has to be logged in
	if(!$usrid) $a->kill("No user session registered. Please log in to post this.");
	
	$is_preview_draft = $submit_action == "submit" ? false : true;
	
	//JSON content here
	$content = array("heading"=>"", "heading_formatted" => "", "text"=>"", "text_intro"=>"");
	
	//get db data and check access
	$q = "SELECT * FROM posts WHERE session_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $session_id)."' LIMIT 1";
	if($in_db = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))){
		$postdat = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
		$postdat['options'] = $postdat['options'] ? json_decode($postdat['options'], 1) : array();
		//user has access?
		if($_SESSION['user_rank'] <= 7 && $postdat['usrid'] != $usrid) $a->kill("You don't have access to edit this item.");
		if($postdat['options']['access'] && $_SESSION['user_rank'] < $postdat['options']['access']) $a->kill("This item is locked and can't be edited.");
	} else {
		$postdat = array();
	}
	
	//required fields
	if(!in_array($in['category'], array("draft", "blog", "public"))) $in['category'] = '';
	if(!$in['category'] && !$is_preview_draft) $errors[] = 'Please choose an appropriate <a href="#?field=postsection">Post Section</a>';
	if(!$in['post_type'] && !$is_preview_draft) $errors[] = 'Please choose a <a href="#?field=ttype">post type</a>';
	
	$unpublished = $in['category'] && $in['category'] != "draft" ? false : true;
	
	//begin parsing the data!
	//hooray!
	
	if($heading = parseText($cont['heading'])){
		$bb = new bbcode($heading);
		$heading = $bb->bb2html();
		$heading = strip_tags($heading, "<i><em><del><ins>");
		$content['heading_formatted'] = $heading;
		$heading = $bb->html2bb($heading);
	}
	
	$description = $in['description'] ? $in['description'] : strip_tags($content['heading_formatted']);
	
	$text = parseText($cont['text']);
	
	$autotags = array();
	if($ajaxforminput['autotags']) $autotags = $ajaxforminput['autotags'];
	
	if($in['subject']){
		$in['subject'] = formatName($in['subject'], '', false);
		$autotags[] = $in['subject'];
	}
	
	// Check to make sure there's no more than 1 <!--more-->
	$more_regex = "/<!--more(.*?)-->/";
	preg_match($more_regex, $text, $matches_more);//print_r($matches_more);
	if(count($matches_more)){
		//Set more tag stuff for later
		$more_tag = $matches_more[0];
		if($matches_more[1]){
			$more_tag_custom = trim(htmlspecialchars($matches_more[1]));
			$more_tag_custom = substr($more_tag_custom, 0, 30);
			if(strlen($more_tag_custom) < 4) unset($more_tag_custom);
		}
	}
	
	//review
	if($in['post_type'] == "review"){
		if($cont['rating'] == "scale"){
			$content['rating']['scale'] = $cont['scale_rating'];
		} elseif($cont['rating'] == "custom"){
			$content['rating']['custom'] = trim($cont['custom_rating']);
			$content['rating']['custom'] = htmlentities($content['rating']['custom'], ENT_QUOTES);
			if($content['rating']['custom'] == ""){
				unset($content['rating']['custom']);
				$content['rating']['fixed'] = "";
			}
		} else $content['rating']['fixed'] = $cont['rating'];
		if(!$description) $description = $in['subject'] . " review";
		if(!$text) $errors[] = "Text body is required for a Review";
	}
	
	//preview
	if($in['post_type'] == "preview"){
		if(!$description) $description = $in['subject'] . " preview";
		if(!$text) $errors[] = "Text body is required for a Preview";
	}
	
	//playlog
	if($in['post_type'] == "playlog"){
		if(!$description) $description = $in['subject'] . " play log";
		if(!$text) $errors[] = "Text body is required for a Play Log";
	}
	
	//news
	if($in['post_type'] == "news"){
		if(!$heading) $errors[] = "A Headline is required for a News post";
	}
	
	//quote
	if($in['post_type'] == "quote"){
		$content['quote_source'] = parseText($cont['quote_source']);
		if(!$text || !$content['quote_source']) $errors[] = "Please input both a quote and source";
		
		if(strlen($content['quote_source']) > 1000) $errors[] = "Quote source is too long (1000 character limit)";
		
		//Quote is limited to 500 chars
		//convert to pure text to get an accurate measurement
		$bb = new bbcode($text);
		$quote = $bb->bb2html();
		$quote = strip_tags($quote);
		$content['quote_length'] = strlen($quote);
		if($content['quote_length'] > 500) $errors[] = "Quotes are limited to 500 characters (currently ".$content['quote_length'].")";
		if(!$description) $description = '"'.($content['quote_length'] > 115 ? substr($quote, 0, 110)."..." : $quote).'"';
	}
	
	//link
	do if($in['attachment'] == "link"){
		$url = trim($cont['link_url']);
		if($url == "http://") unset($url);
		if(!$url){
			break;
		} else {
			$url = preg_replace("@http://(www.)?videogam.in/?@", "/", $url);
			if(!preg_match("@^/|(https?://)@", $url)) $errors[] = "Invalid URL. It should be either a http:// link or an internal videogam.in link beginning with the '/' character.";
		}
		if(!$heading){
			// get the link destination page title
			$html = file_get_contents_curl($url);
			$doc = new DOMDocument();
			@$doc->loadHTML($html);
			$nodes = $doc->getElementsByTagName('title');
			$heading = $nodes->item(0)->nodeValue;
		}
		$content['link_url'] = $url;
	} while(false);
	if($in['attachment'] == "link" && !$content['link_url']) $in['attachment'] = "";
	
	//image
	do if($in['attachment'] == "image" && count($cont['img_names'])){
		$num_imgs=0;
		foreach($cont['img_names'] as $img_name){
			$num_imgs++;
			if($img_name == "%s" || $img_name == "") continue;
			$content['img_names'][] = $img_name;
			if($num_imgs >= 10) break;
		}
		if(!$num_imgs) break;
		$content['img_num'] = $num_imgs;
		if($num_imgs > 1 && $cont['img_layout']){
			$cells = array();
			$cells = explode("x", $cont['img_layout']);
			foreach($cells as $cellnum) $cells_total = $cells_total + $cellnum;
			if($cells_total == $num_imgs) $content['img_layout'] = $cont['img_layout'];
			//else $errors[] = "layout doesn't match number of cells";
		}
		if($num_imgs > 1 && !$content['img_layout']){
			$content['img_layout'] = $GLOBALS['image_default_layouts'][$num_imgs];
		}
	} while(false);
	if($in['attachment'] == "image" && !$content['img_num']) $in['attachment'] = "";
	
	//video
	do if($in['attachment'] == "video"){
		$video_input = trim($in['video_input']);//$content['vi'] = $video_input;
		if($video_input == "") break;
		if(substr($video_input, 0, 4) == "http") $content['video_url'] = $video_input;
		else $content['video_embedcode'] = $video_input;
		if($content['video_url'] && $video = getVideo($content['video_url'])){
			if($video->html) $content['video_embedcode'] = html_entity_decode((string)$video->html);
			$content['video_width'] = (string)$video->width;
			$content['video_height'] = (string)$video->height;
			if(!$description && $video->title) $description = html_entity_decode((string)$video->title);
		}
		if(!$content['video_embedcode']) $errors[] = "Couldn't fetch video embed code from the given URL. Please input the embed code instead.";
	} while(false);
	if($in['attachment'] == "video" && !$content['video_embedcode']) $in['attachment'] = "";
	
	//audio
	do if($in['attachment'] == "audio"){
		if(!$content['audio_file'] = $cont['audio_file']) break;
		if($cont['audio_trackids']){
			$content['audio_trackids'] = array();
			foreach($cont['audio_trackids'] as $trackid){
				if(count($content['audio_trackids']) >=3) break;
				$q = "SELECT * FROM albums_tracks WHERE `id` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $trackid)."' LIMIT 1";
				if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $content['audio_trackids'][] = $trackid;
			}
		}
	} while(false);
	if($in['attachment'] == "audio" && !$content['audio_file']) $in['attachment'] = "";
	
	//tweet
	do if($in['attachment'] == "tweet"){
		$url = trim($cont['tweet_url']);
		if($url == "http://") unset($url);
		if(!$url) break;
    $json = getTweet($url);
		if(!$json['html']) $errors[] = "Couldn't get the tweet from the given URL.";
		else $content['tweet'] = $json;
	} while(false);
	if($in['attachment'] == "tweet" && !$content['tweet']) $in['attachment'] = "";
	
	if($heading) $content['heading'] = $heading;
	if($text){
		$content['text'] = $text;
		$content['text_intro'] = $text;
		$preview_text = $text;
		
		// Create intro text for posts lists
		
		#
		# Any changes here should also be made to /ninadmin/posts-reformat.php, the script that re-formats all post intros
		#
		
		if($more_tag){
			$tarr = array();
			$tarr = explode($more_tag, $text);
			
			//Find any citation references defined after <!--more--> that would be cut off
			$refs = postExtractCitations($text, $tarr[0]);
			if($refs) $refs = "\n" . $refs;
			
			$content['text_intro'] = $tarr[0] . $more_tag . $refs;
			$preview_text = $tarr[0] . $more_tag;
			unset($tarr[0]);
			$preview_text.= implode("", $tarr);
			
		}
		
		$intro_maxlen = 3500;
		if(strlen($content['text_intro']) > $intro_maxlen){
			$insert_chunk = substr($content['text_intro'], 0, $intro_maxlen);
			$cutoff_line = strrchr($insert_chunk, " ") . substr($content['text_intro'], $intro_maxlen, 55) . '...';
			$errors[] = "Your article is too long. Please add the following code <code>&lt;!--more--&gt;</code> somewhere before the following line:<br/><code>".$cutoff_line."</code>";
			$content['text_intro'] = substr($text, 0, $intro_maxlen) . "&hellip;" . $more_tag;
			$preview_text = $content['text_intro'] . substr($text, $intro_maxlen);
		} elseif(strlen($content['text_intro']) > 1200){
			$a->ret['warnings'][] = "Your article introduction is a bit long. Consider adding the following code<br/><code>&lt;!--more--&gt;</code> somewhere within the first 1000 characters to create an introduction section of text.";
		}
		
		//cut the intro if it's too long
		/*if(strlen($content['text_intro']) > 1000){
			$t = substr($content['text_intro'], 0, 1000);
			$more_pos = 1000;
			//dont cut off any html or [[pagelinks]]
			if(strstr($t, "<")){
				$pos = strrpos($t, "<");
				if(strrpos($t, "</") < $pos) $more_pos = $pos;
			}
			if(strstr($t, "[[")){
				$pos = strrpos($t, "[[");
				if(strrpos($t, "]]") < $pos) $more_pos = $pos;
			}
			$content['text_intro'] = substr($text, 0, $more_pos) . "&hellip;<!--more-->";
			$preview_text = $content['text_intro'] . substr($text, $more_pos);
		}*/
		
		//convert to formatted HTML
		//Markdown only -- special Videogam.in code ([spoiler] etc) and [[page links]] are parsed in realtime when fetched
		$bb = new bbcode($content['text_intro']);
		$bb->params['inline_citations'] = true;
		$content['text_intro'] = $bb->markdown(true);
		
		//check for illegal content in the intro
		//preg_match("@\[|\{|\<(video|audio|img)@i", $content['text_intro'], $matches);
		//print_r($matches);
		
	}//echo $content['text_intro'];exit;
	
	if(!$content['heading'] && !$content['text']) $errors[] = "You need either a Headline or a Text entry.";
	
	$cont_str = json_encode($content);
	
	$content_preview = $content;
	$more_words = $more_tag_custom ? $more_tag_custom : ($in['post_type'] == "review" ? 'Full Review' : 'Read on');
	$content_preview['text'] = str_replace($more_tag, ' <a class="arrow-right" style="white-space:nowrap;">'.$more_words.'</a><div class="morebreak" title="Only the above content is seen on lists of posts (all content is seen on the article page)"><span>Page Break</span></div>', $preview_text);
	$cont_str_preview = json_encode($content_preview);
	
	//debug:
	//print_r($in);print_r($cont);
	//$a->ret['formatted'] = htmlspecialchars($cont_str_preview); $a->ret['errors'] = $errors; exit;
	//$a->ret['formatted'] = htmlspecialchars($cont_str); $a->ret['errors'] = $errors; exit;
	//$a->ret['errors'][] = $description . strlen($description);
	
	//Process the data & insert/update db
	
	$datetime = date("Y-m-d H:i:s");
	
	$description = trim($description);
	if(!$description){
		if($postdat['description']) $description = $postdat['description'];
		elseif($heading) $description = $heading;
		elseif($text) $description = $text;
	}
	$bb = new bbcode($description);
	$bb->params['strip_citations'] = true;
	$description = $bb->bb2html();
	$description = strip_tags($description);
	$description_status = strlen($description) > 115 ? substr($description, 0, 112)."..." : $description;
	if(strlen($description) > 58) $description = substr($description, 0, 55)."...";
	$in['description'] = $description ? $description : "Sblog";
	$a->ret['description'] = $in['description'];
	
	// check/make permalink
	$in['permalink'] = trim($in['permalink']);
	//if(!$in['permalink']) $in['permalink'] = $postdat['permalink'];
	if(!$in['permalink']) $in['permalink'] = $in['description'];
	$in['permalink'] = makePermalink($in['permalink']);
	$a->ret['permalink'] = $in['permalink'];
	
	//We will set the category to draft in case the user is still editing
	//Track the real intention in case he navigates away and comes back later to publish
	if($is_preview_draft) $in['options']['on_comeback_category'] = $in['category'];
	
	if($in_db){
		$q = "UPDATE posts SET 
			`description` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['description'])."',
			`permalink` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['permalink'])."',
			`content` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $cont_str)."', 
			`post_type` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['post_type'])."', 
			`subject` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['subject'])."',
			`attachment` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $in['attachment'])."', 
			`category` = 'draft',
			`privacy` = '".($in['privacy'] ? mysqli_real_escape_string($GLOBALS['db']['link'], $in['privacy']) : "public")."',
			`archive` = '".(int)$in['archive']."',
			`options` = '".mysqli_real_escape_string($GLOBALS['db']['link'], opts_str($in['options']))."'
			WHERE session_id='$session_id' LIMIT 1";
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Critical error: Couldn't update entry on posts database.".($_SESSION['user_rank'] == 9 ? " [$q] ".mysqli_error($GLOBALS['db']['link']) : "");
	} else {
		$q = sprintf("INSERT INTO posts 
			(`session_id`, `description`, `permalink`, `content`, `usrid`, `post_type`, `subject`, `attachment`, `category`, `privacy`, `archive`, `options`, `datetime`) VALUES 
			('$session_id', '%s', '%s', '%s', '$usrid', '".$in['post_type']."', '%s', '".$in['attachment']."', 'draft', 'public', '".(int)$in['archive']."', '%s', '$datetime');",
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['description']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['permalink']),
			mysqli_real_escape_string($GLOBALS['db']['link'], $cont_str),
			mysqli_real_escape_string($GLOBALS['db']['link'], $in['subject']),
			mysqli_real_escape_string($GLOBALS['db']['link'], opts_str($in['options'])));
		if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Critical error: Couldn't add entry to posts database.".($_SESSION['user_rank'] == 9 ? " [$q] ".mysqli_error($GLOBALS['db']['link']) : "");
	}
	
	$q = "SELECT * FROM posts WHERE session_id = '$session_id' LIMIT 1";
	$postdat = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
	$nid = $postdat['nid'];
	
	//generate status update text
	$status_url = "http://videogam.in/s".$nid;
	if($in['attachment'] == "video") $status_url = $content['video_url'];
	if($in['attachment'] == "link") $status_url = $content['link_url'];
	$status_text = $description_status . " " . $status_url;
	$a->ret['status_text'] = $status_text;
	
	//preview
	if($is_preview_draft){
		
		$in['nid'] = $nid;
		$a->ret['nid'] = $nid;
		$in['content'] = $cont_str_preview;
		
		//debug
		//$a->ret['formatted'] = htmlspecialchars($cont_str); $a->ret['errors'] = $errors; exit;
		
		$post = new post($in);
		
		$a->ret['formatted'] = '<div class="posts"><article class="post-item">'.$post->output().'</article><div class="clear"></div></div>';
		if($errors && $submit_action != "draft") $a->ret['errors'] = $errors;// ignore errors if just saving a draft
		
		exit;
	
	}
	
	//album samples
	$q = "DELETE FROM albums_samples WHERE nid='$nid'";
	mysqli_query($GLOBALS['db']['link'], $q);
	if(!$unpublished && $content['audio_trackids']){
		foreach($content['audio_trackids'] as $trackid){
			$q = "SELECT * FROM albums_samples LEFT JOIN albums_tracks ON (albums_samples.track_id = albums_tracks.id) WHERE albums_samples.track_id='$trackid' LIMIT 1";
			if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
				$errors[] = "There's already a sample for the track [".$row['track_name']."] (".$row['albumid']."); Please remove this track association in order to continue!";
			} else {
				$q = "SELECT albumid, track_name FROM albums_tracks WHERE id='$trackid' LIMIT 1";
				if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
					$albumid = $row['albumid'];
					$autotags[] = "AlbumID:".$albumid;
					$file = str_replace("/bin/uploads/audio/", "", $content['audio_file']);
					$q = "INSERT INTO albums_samples (`albumid`,`track_id`,`file`,`usrid`,`nid`) VALUES ('$albumid', '$trackid', '$file', '$usrid', '$nid');";
					if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't add [".$row['track_name']."] (".$row['albumid'].") into albums samples database";
				}
			}
		}
	}
	
	if($errors){
		$a->ret['errors'] = $errors;
		exit;
	}
	
	//FINAL PROCESSING
	
	//share
	if($ajaxforminput['share']){
		if(!$in['status_text']) $in['status_text'] = $status_text;
		$query = "SELECT * FROM users_oauth WHERE usrid='$usrid'";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		while($row=mysqli_fetch_assoc($res)) $oauth[$row['oauth_provider']] = $row;
		
		//facebook
		do if($ajaxforminput['share']['facebook']){
			if(!$oauth['facebook']['oauth_token']){ $errors[] = 'Couldn\'t Post to Facebook wall -- Authorization data not found. Try reauthorizing from your <a href="/account.php?edit=prefs">account</a> page.'; break; }
			require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/fb/src/facebook.php";
			require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/fb/src/config.php";
			$fb = array('appId' => FB_APPID, 'secret' => FB_SECRET);
			$facebook = new Facebook($fb);
			if($fbuser = $facebook->getUser()){
				$post['message'] = $in['status_text'];
				if(strstr($in['status_text'], $status_url)){
					$post['message'] = str_replace($status_url, "", $post['message']);
					$post['link'] = $status_url;
				}
				try { $res = $facebook->api('/me/feed', 'POST', $post); }
				catch(FacebookApiException $e) { $errors[] = "Facebook error: " . $e->getMessage(); break; }
				if($res->id) $in['options']['facebook_post_id'] = $status->id;
			} else {
				$errors[] = 'Couldn\'t Post to Facebook wall -- Error authorizing user. Try reauthorizing from your <a href="/account.php?edit=prefs">account</a> page.';
			}
    } while(false);
    
		if($ajaxforminput['share']['twitter']){
			$parameters = array('status' => $in['status_text']);
			//get access token
			if(!$oauth['twitter']['oauth_token']) $a->kill = 'Couldn\'t Tweet (@you) this post -- Authorization data not found. Try reauthorizing from your <a href="/account.php?edit=prefs">account</a> page.';
			require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/twitter/config.php";
			if (CONSUMER_KEY === '' || CONSUMER_SECRET === '') $a->kill = 'Couldn\'t Tweet (@'.$oauth['twitter']['oauth_username'].') this post because the credentials have been lost.';
			session_start();
			require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/twitter/twitteroauth/twitteroauth.php";
			$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $oauth['twitter']['oauth_token'], $oauth['twitter']['oauth_token_secret']);
			$status = $connection->post('statuses/update', $parameters);
			if($status->error) $errors[] = "There was an error Tweeting (@".$oauth['twitter']['oauth_username'].") this [".$status->error."]";
			elseif($status->id){
				$in['options']['tweet_id'] = $status->id;
			}
		}
		if($ajaxforminput['share']['twitter_site']){
			$parameters = array('status' => $in['status_text']);
			//get Videogamin access token
			$q = "SELECT * FROM users_oauth WHERE usrid='4651' AND oauth_provider='twitter' LIMIT 1";
			if(!$oauth = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) $a->kill = 'Couldn\'t Tweet (@Videogamin) this post because the database information couldn\'t be retrieved.';
			require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/twitter/config.php";
			if (CONSUMER_KEY === '' || CONSUMER_SECRET === '') $a->kill = 'Couldn\'t Tweet (@Videogamin) this post because the credentials have been lost.';
			session_start();
			require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/twitter/twitteroauth/twitteroauth.php";
			$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $oauth['oauth_token'], $oauth['oauth_token_secret']);
			$status = $connection->post('statuses/update', $parameters);
			if($status->error) $errors[] = "There was an error Tweeting (@Videogamin) this [".$status->error."]";
			elseif($status->id){
				$in['options']['tweet_id_site'] = $status->id;
			}
		}
	}
	
	if(!$postdat['datetime_first_published'] && !$unpublished){
		
		// it was never published,
		// so set the post date to now
		
		$first_publish = true;
		
		$tm = ", `datetime` = '$datetime', datetime_first_published = '$datetime'";
		
	}
	
	$q = "UPDATE posts SET 
		`category` = '".$in['category']."',
		`pending` = '$pending',
		`options` = '".opts_str($in['options'])."',
		`minimize` = '".((int)$in['minimize'])."',
		datetime_last_edited = '$datetime'
		".$tm." 
		WHERE session_id='$session_id' LIMIT 1";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't update posts database;".mysqli_error($GLOBALS['db']['link']);
	else $success = true;
	
	//autotags
	$_tags = new tags("posts_tags:nid:".$nid);
	$_tags->autoTag($cont_str);
	
	//autotag img upload tags
	if($in['attachment'] == "image"){
		foreach($cont['img_names'] as $img_name){
			$query = "SELECT tag FROM images LEFT JOIN images_tags USING (img_id) WHERE img_name = '".mysqli_real_escape_string($GLOBALS['db']['link'], $img_name)."'";
			$res   = mysqli_query($GLOBALS['db']['link'], $query);
			while($images_tags = mysqli_fetch_assoc($res)){
				if(in_array($images_tags['tag'], $autotags)) continue;
				$autotags[] = $images_tags['tag'];
			}
		}
	}
	
	if(count($autotags)){
		foreach($autotags as $tag) {
			$tag = formatName($tag, '', false);
			if(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM posts_tags WHERE nid='$nid' AND tag='".mysqli_real_escape_string($GLOBALS['db']['link'], $tag)."' LIMIT 1"))){
				$q = "INSERT INTO posts_tags (nid, tag, usrid) VALUES ('$nid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $tag)."', '$usrid');";
				mysqli_query($GLOBALS['db']['link'], $q);
			}
		}
	}
	
	//record edit
	$pe = $ajaxforminput['pe'];
	$q = "INSERT INTO posts_edits (nid, usrid, comments, content) VALUES ('$nid', '$usrid', '".mysqli_real_escape_string($GLOBALS['db']['link'], $pe['comments'])."', '".mysqli_real_escape_string($GLOBALS['db']['link'], $cont_str)."');";
	if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = 'Couldn\'t record this edit record into posts_edits database; All other data was saved, unless otherwise noted. <a href="/sblog/'.$nid.'">Continue to your post</a>';
	
	if($pe['email']){
		$usrdat  = getUserDat($pe['email']);
		if($usrdat->usrid != $usrid){
			//get old post info
			$q = "SELECT * FROM posts_edits WHERE nid = '$nid' AND usrid = '$usrdat->usrid' ORDER BY `datetime` DESC LIMIT 1";
			if($oldpost = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) $oldpostmessage = "\n\nSee your old post as you last edited it (".$oldpost['datetime'].") here -> http://videogam.in/sblog/$nid?history=".$oldpost['id'];
		}
		$to      = $usrdat->email;
		$subject = 'Your Videogam.in post has been moderated';
		$message = $usrdat->username.",\nYour Videogam.in post, \"".$in['description'].",\" has been edited by $usrname.".($pe['comments'] ? "\n\nComments:\n".$pe['comments'] : "")."\n\nSee the new post here -> http://videogam.in/posts/?id=".$nid.$oldpostmessage."\n\nSincerely,\nThe Friendly Videogam.in Post Edit Notfying Robot\n";
		$headers = 'From: noreply@videogam.in' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
		mail($to, $subject, $message, $headers);
	}
	
	//poll
	$poll = $ajaxforminput['poll'];
	$q = "SELECT * FROM posts_polls WHERE nid='$nid' LIMIT 1";
	if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) {
		if(!mysqli_query($GLOBALS['db']['link'], "UPDATE posts_polls SET `closed` = '".$poll['closed']."' WHERE nid='$nid' LIMIT 1")) $errors[] = "Couldn't update poll closed status";
	} elseif(trim($poll['question'])) {
		$bb = new bbcode($poll['question']);
		$poll['question'] = $bb->bb2html();
		$poll['question'] = strip_tags($poll['question'], '<i><b><del>');
		if($poll['question'] != "") {
			$poll['opts'] = trim($poll['opts']);
			if(!$poll['opts']) $errors[] = "No poll options given";
			else {
				$fopts = array();
				$opts = array();
				$opts = explode("\r\n", $poll['opts']);
				foreach($opts as $opt) {
					$opt = trim($opt);
					if($opt != "") $fopts[] = $opt;
				}
				if(count($fopts) > 1) {
					$foptsins = implode("|--|", $fopts);
					$bb->text = $foptsins;
					$foptsins = $bb->bb2html();
					$foptsins = strip_tags($foptsins, '<i>');
					$q = sprintf("INSERT INTO posts_polls (nid, question, options, answer_type) VALUES ('$nid', '%s', '%s', '%s');",
						mysqli_real_escape_string($GLOBALS['db']['link'], $poll['question']),
						mysqli_real_escape_string($GLOBALS['db']['link'], $foptsins),
						mysqli_real_escape_string($GLOBALS['db']['link'], $poll['answer_type'])
					);
					if(!mysqli_query($GLOBALS['db']['link'], $q)) $errors[] = "Couldn't insert poll to DB; ".mysqli_error($GLOBALS['db']['link']);
				} else $errors[] = "Your poll needs more than one option";
			}
		}
	}
	
	//badges
	if($first_publish){
		$_badges = new badges;
		
		if($in['post_type'] == "review"){
			$_badges->earn(13, $postdat['usrid']); //budding critic
		}
		
		$query = "SELECT post_type, attachment, rating, ratings, rating_weighted FROM posts WHERE usrid='$postdat[usrid]' AND category != 'draft' AND `pending` != '1'";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		if(mysqli_num_rows($res)){
			
			$_badges->earn(10, $postdat['usrid']); //lightweight
			
			$goodposts = 0;
			$picposts = 0;
			$postedtypes = array();
			while($row = mysqli_fetch_assoc($res)){
				
				if($row['ratings'] > 4 && $row['rating'] >= 80) $goodposts++;
				
				if($row['attachment'] == "image") $picposts++;
				if(!in_array($row['post_type'], $postedtypes)) $postedtypes[] = $row['post_type'];
			}
			if($goodposts >= 24){
				$_badges->earn(11, $postdat['usrid']); //welterweight
			}
			if($goodposts >= 99){
				$_badges->earn(12, $postdat['usrid']); //heavyweight
			}
			if(count($postedtypes) == 6){
				$_badges->earn(14, $postdat['usrid']); //kungfu master (one of every type)
			}
			if($picposts >= 5){
				$_badges->earn(38, $postdat['usrid']); //photo man
			}
		}
	}
	
	$date = substr($dt, 0, 10);
	$date = str_replace("-", "/", $date);
	
	$loc = "/sblog/".$nid."/".$in['permalink'];
	if($_SERVER['HTTP_HOST'] == "localhost") $loc = "/posts/handle.php?nid=".$nid;
	
	if($errors){
		$a->ret['errors'] = $errors;
		if($success) $a->ret['success'] = 'Post saved (but with errors). <a href="'.$loc.'">Go to your post</a>';
	} else {
		$a->ret['goto'] = $loc;
		$a->ret['success'] = 'Post saved. Redirecting to <a href="'.$loc.'">your post</a>';
	}
	
	exit;

}

function opts_str($in){
	foreach($in as $key => $val) if($val == '') unset($in[$key]);
	if($in) return json_encode($in);
}

function makePermalink($p) {
	
	$p = trim($p);
	$p = strtolower($p);
	$p = str_replace(" ", "-", $p);
	$p = preg_replace("/\-+/", "-", $p);
	$p = preg_replace("/[^a-z0-9_-]+/i", "", $p);
	$p = substr($p, 0, 99);
	if($p == "") $p = "post";
	return $p;
	
}

function file_get_contents_curl($url){
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;

$html = file_get_contents_curl("http://example.com/");

//parsing begins here:
$doc = new DOMDocument();
@$doc->loadHTML($html);
$nodes = $doc->getElementsByTagName('title');

//get and display what you need:
$title = $nodes->item(0)->nodeValue;

$metas = $doc->getElementsByTagName('meta');

for ($i = 0; $i < $metas->length; $i++)
{
    $meta = $metas->item($i);
    if($meta->getAttribute('name') == 'description')
        $description = $meta->getAttribute('content');
    if($meta->getAttribute('name') == 'keywords')
        $keywords = $meta->getAttribute('content');
}

echo "Title: $title". '<br/><br/>';
echo "Description: $description". '<br/><br/>';
echo "Keywords: $keywords";

}

function postExtractCitations($text, $text_piece){
	$regex = '{
				^[ ]{0,3}\[\^(.+?)\][ ]?:	# note_id = $1
				  [ ]*
				  \n?					# maybe *one* newline
				(						# text = $2 (no blank lines allowed)
					(?:					
						.+				# actual text
					|
						\n				# newlines but 
						(?!\[\^.+?\]:\s)# negative lookahead for footnote marker.
						(?!\n+[ ]{0,3}\S)# ensure line is not blank and followed 
										# by non-indented content
					)*
				)		
				}xm';
			preg_match_all($regex, $text, $matches_citations, PREG_SET_ORDER);
			if(count($matches_citations)){
				//print_r($matches_citations);
				foreach($matches_citations as $c){
					//only return cited references
					$r = '{\[\^'.$c[1].'\]}';
					if(preg_match($r, $text_piece)) $ret[] = trim($c[0]);
				}
			}
	if($ret) return implode("\n", $ret);
}

/*function uploadVideoThumb($f) {
	
	//@var $f location of a file to handle
	//@ret final tn URL filename (ie /bin/img/uploads/posts/abc.jpg)
	
	$dir = "/bin/uploads/posts/";
	
	$handle = new Upload($f);
	if($handle->uploaded) {
		$handle->image_resize           = true;
		$handle->image_ratio_crop       = true;
		$handle->image_x                = 200;
		$handle->image_y                = 130;
		$handle->Process($_SERVER['DOCUMENT_ROOT'].$dir);
		if ($handle->processed) {
			
			return $dir.$handle->file_dst_name;
			
		} else return FALSE;
	} else return FALSE;

}*/

?>