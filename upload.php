<?
require_once ($_SERVER['DOCUMENT_ROOT']."/bin/php/page.php");
$page = new page;
require_once ($_SERVER['DOCUMENT_ROOT']."/bin/php/class.img.php");

if($sessid = $_GET['sessid']){
	$q = "SELECT * FROM images_sessions WHERE img_session_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $sessid)."' LIMIT 1";
	if($img_sess = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))){
		if($img_sess->usrid != $usrid && $usrrank < 8) die("<span style=\"color:white;\">You don't have access to modify this session.</span>");
	}
	$editsession = true;
} else {
	$sessid = imgMakeSessionId();
}

$page->title = "Videogam.in / Upload";
$page->freestyle.= '
		#uplqbuff { display:none; width:400px; border:1px solid #D0D0D0; background-color:white; }
		#uplq { height:145px; overflow:auto; }
		#numq { display:none; padding:4px 10px; font-weight:bold; background-color:#DDD; }
		button.subm { padding:5px 15px; font-size:15px; font-weight:bold; }
		#uploading { display:none; margin:0 21px 0 163px; padding:0 0 0 30px; line-height:30px; font-size:18px; background:rgba(255, 255, 255, 0.2) url("/bin/img/icons/sprites/goomba.gif") no-repeat 10px center; border:1px inset rgba(0,0,0,.2); border-radius:2px; }
		#uplimgerr { display:none; }
		#uplimgerr ul { margin:0 0 8px; padding:0 5px 0 0; list-style:none; max-height:120px; overflow:auto; }
		#uplimgerr li { margin:3px 0 0; padding:0 0 0 16px; background:url(/bin/img/icons/warn.png) no-repeat left 3px; }
		.uploadifyQueueItem {
			font-size:12px;
			border-bottom:1px solid #D0D0D0;
		}
		.uploadifyError { color:#E81717; }
		.uploadifyQueueItem .cancel { float:right; display:block; padding:5px; }
		.uploadifyQueueItem .cancel a { display:block; background:url("/bin/img/icons/trash.png") no-repeat center center; opacity:.6; }
		.uploadifyQueueItem .cancel a:hover { opacity:1; }
		.uploadifyQueueItem .cancel a img { visibility:hidden; }
		.uploadifyQueueItem .fileName { display:block; padding:5px 10px; float:left; }
		.uploadifyQueueItem .fileSize { display:block; margin:0 0 0 280px; padding:5px; }
		.uploadifyQueueItem .percentage { display:none; }
		.uploadifyQueue .completed {}
		.uploadifyQueue .completed .cancel a { background-image:url("/bin/img/check_15.png"); }
		.uploadifyProgress {
			background-color:#E1F0FF;
			width:100%;
		}
		.uploadifyProgressBar {
			background-color:#39F;
			height:3px;
			width:0;
		}
		.error .cancel a { background-image:url("/bin/img/icons/warn.png") !important }
		.error .uploadifyProgressBar { background-color:#DE1B1B; }
		.errorMessage { margin:0 10px 3px 10px; color:#DE1B1B; }
';

$page->javascripts[] = "/bin/uploadify/swfobject.js";
$page->javascripts[] = "/bin/uploadify/jquery.uploadify.v2.1.4.min.js";
$page->javascript.= <<<eof
	<script type="text/javascript">
		$(document).ready(function(){
			
			$("[name='img_category_id']").change(function(){
				if($(this).val() != ''){
					$('#quickuplsubmit').attr('disabled','')
				} else {
					$('#quickuplsubmit').attr('disabled','disabled')
				}
			})
			
			if($.browser.msie){
				
				$("#topmsg").prepend('<p><span class="warn">So sorry!</span> The advanced attributes of this upload form have been proven to conflict with the Internet Explorer browser. We highly recommend downloading and using <a href="http://google.com/chrome" target="_blank">Chrome</a>, <a href="http://getfirefox.com" target="_blank">Firefox</a>, or pretty much any other browser in existence.</p>');
				$("#uplimgform").append('<input type="submit"/>');
				
			} else {
				
				var numQ = 0;
				
				$('#file_upload').uploadify({
			    'uploader'  : '/bin/uploadify/uploadify.swf',
			    'script'    : '/uploadhandle.php',
			    'cancelImg' : '/bin/uploadify/cancel.png',
			    'auto'      : false,
			    'fileDataName' : 'upl',
			    'fileExt'   : '*.jpg;*.gif;*.png',
	 				'fileDesc'  : 'Image Files (.JPG, .GIF, .PNG)',
			    'multi'     : true,
			    'queueID'   : 'uplq',
			    'queueSizeLimit' : 120,
			    'removeCompleted' : false,
			    'buttonImg' : '/bin/uploadify/browse.png',
			    'scriptData' : {
			    	'action':'submimg',
			    	'handler':'%s'
			    }, 'onSelectOnce' : function(event,data){
			    	$("#uplqbuff").show();
						if(data.fileCount){
							$("#uplimgsubmit").show();
							$("#topmsg").hide();
							var numq = $(".uploadifyQueueItem").length;
							$("#numq").show().html(numq+' file'+(numq > 1 ? 's' : ''));
						}
					}, 'onCancel' : function(event,ID,fileObj,data) {
						if(!data.fileCount){
							$("#uplqnull").slideDown();
							$("#uplimgsubmit").slideUp();
						}
						numQ = data.fileCount;
						$("#numq").html(numQ+' file'+(numQ != 1 ? 's' : ''));
					}, 'onComplete' : function(event, ID, fileObj, response, data) {
						if(response != "ok") $("#file_upload"+ID).addClass("error").find(".uploadifyProgress").before('<div class="errorMessage">'+response+'</div>');//$("#uplimgerr").show().find("ul").prepend('<li>'+response+'</li>');
						numTotal = $("#uplq").children().length;
						numQ = numTotal - data.fileCount
						$("#uploaded").text(numQ+' / '+numTotal+' Uploaded');
					}, 'onAllComplete' : function(event,data){
						if($("#uplimgerr ul").html()){ $("#uplimgerr .msg").show(); return; }
			    	$("#uplimgsubmit .subm").text("Submit Uploads");
			    	$("#uploading").hide();
			    	$("#uplfin").fadeIn();
			    }
			  });
			  
			}
				
		});
	</script>
eof;

$handler = array("sessid" => $sessid, "usrid" => $usrid);
$handler = http_build_query($handler);

$autotag = $_GET['img_tag'];
if($autotag && !is_array($autotag)){
	$autotag[0] = $_GET['img_tag'];
}
for($i = 0; $i < count($autotag); $i++){
	$tag = formatName($autotag[$i]);
	$handler.= '&img_tag[]='.formatNameURL($tag);
	$p_autotags.= '&nbsp;&nbsp;&bull; <b>' . $tag . '</b><br/>';
}

$page->javascript = sprintf($page->javascript, base64_encode($handler));

$page->width = "fixed";
$page->header();

?>

<h1><?=($editsession ? $img_sess->img_session_description : 'New Upload Session')?></h1>

<? if(!$usrid) $page->die_('Please <a href="/login.php">log in</a> to upload.'); ?>

<div id="topmsg">
	<p><?=(!$editsession ? 'You\'re starting a brand new upload session. To upload additional pictures to a previous session, access the <a href="/uploads.php">Upload Manager</a>.' : 'Continue with the below form to upload more images to this session, which currently has <b>'.$img_sess->img_qty.' image'.($img_sess->img_qty != 1 ? 's' : '').'</b>. Otherwise, <a href="/upload.php" class="arrow-right">start a new session</a>')?></p>
	<?=($autotag ? '<p><span class="warn"></span> All your uploaded images for this session will be automatically tagged with the following tags, so make sure all your uploads are related or <a href="/upload.php">remove these tags</a><br/>' . $p_autotags . '</p>' : '')?>
</div>

<div class="buff" style="height:20px"></div>

<div style="width:45%; float:left; padding-right:5%;">
	
	<h2 style="margin:0; padding:0; border:none;">Mass Upload</h2>
	
	<form method="post" action="uploadhandle.php" enctype="multipart/form-data" id="uplimgform">
		<p>Upload up to 50 images in <code>JPG</code>, <code>GIF</code>, or <code>PNG</code> format.<br/>
		<b>Tip</b>: Use the <i>Ctrl</i> and <i>Shift</i> keys to select multiple files.</p>
		<p><input id="file_upload" name="upl" type="file"/></p>
	</form>
	
	<div id="uplqbuff">
		<div id="uplq"></div>
		<div id="numq"></div>
	</div>
	
	<p></p>
	
	<div id="uplimgsubmit" style="display:none; float:left;">
		<button type="button" onclick="$(this).text('Submitting...'); $('#uplfin').hide(); $('#uploading').show(); $('#uploaded').text('Initializing...'); $('#uplimgerr').hide().find('ul').html(''); $('#file_upload').uploadifyUpload();" class="subm">Submit Uploads</button>
	</div>
	
	<div id="uploading">
		<span id="uploaded">Initializing...</span>
	</div>
	
	<div id="uplfin" class="popmsg" style="display:none; width:400px; margin-left:-200px; top:30%; padding:20px;">
		<a href="#rm" class="ximg preventdefault" style="top:10px; right:10px;" onclick="$(this).parent().fadeOut()">Close</a>
		<div style="padding-left:40px; background:url('/bin/img/check_30.png') no-repeat left center; font-size:110%;">
			<big><b>Finished!</b></big><br/>
			Next step: <b><a href="/uploads.php?sessid=<?=$sessid?>" class="arrow-right">Add tags and descriptions</a></b>
		</div>
	</div>
	
	<div id="uplimgerr">
		<div class="hr"></div>
		<ul></ul>
		<div class="msg" style="display:none">I am error. Try again or <a href="/uploads.php?sessid=<?=$sessid?>" class="arrow-right">continue to next step</a></div>
	</div>

</div>

<div style="margin-left:40%; padding-left:10%; background:url('/bin/img/dotline_y.png' repeat-y left;">
	<h2 style="margin:0; padding:0; border:none;">Quick Upload</h2>
	<p></p>
	<form method="post" action="uploadhandle.php" enctype="multipart/form-data" id="quickuploadform">
		<input type="hidden" name="action" value="submimg"/>
		<input type="hidden" name="actionhandle" value="quick upload"/>
		<input type="hidden" name="handler" value="<?=base64_encode($handler)?>"/>
		<input type="file" name="upl" onchange="$('form#quickuploadform').submit(); $(this).attr('disabled','disabled').next().show();"/>
		<span style="display:none; padding-left:20px; background:url('/bin/img/loading_grayarrows_flushbg.gif') no-repeat left center;">Uploading</span>
	</form>
</div>

<br style="clear:both;"/><br/>

<?

$page->footer();

?>