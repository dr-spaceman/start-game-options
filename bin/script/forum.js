
function getReplyMsgTxt() {
	return $("#fmessage").val();
}

$(document).ready(function() {
	
	resizeAvatars();
	
	if($("#focuspoint").length) $(window).load(function(){ $.scrollTo("#focuspoint", 1500)});
	
	$("#reply-form .identify a").click(function() {
		if( $(this).hasClass("arrow-toggle-on") ) return;
		$("#reply-form .identify a").toggleClass("arrow-toggle-on");
		$("#reply-form .identify table").toggle();
	});
	
	$(".cancel-edit-forum-post").click(function() {
		$(this).siblings(".submit-edited-forum-post").removeAttr("disabled");
		$(this).siblings("img").hide();
		$(this).parents(".message").siblings(".message-opts").children(".edit").removeClass("on");
	});
	
	$("#preview-reply-button").click(function(){
		if($(this).val() == "Preview") {
			$("#reply-loading").css('display','inline');
			$.post(
				"/forums/action.php", 
				{ previewtxt: getReplyMsgTxt(),
					disable_emoticons: ( $("input[name='disable_emoticons']:checked").length ? '1' : '' )
				},
				function(t) {
					$("#preview-space").html(t);
					$("#forum-form .switch").toggle();
					$("#reply-loading").hide();
					$("#preview-reply-button").val("Edit");
				}
			);
		} else {
			$("#forum-form .switch").toggle();
			$(this).val("Preview");
		}
	});
	
	$("#forum-initiate-reply").hover(
		function(){
			$(this).append(' <span style="color:#06C">Click to reply</span>');
		},
		function() {
			$(this).html('Your reply here...');
		}
	).click(function() {
		initiateReply();
	});
	
	$("#reply-opts .tabbed-nav li a").click(function(e) {
		e.preventDefault();
		$(this).parent("li").attr("class","on").siblings("li").removeClass("on");
		var $subj = $(this).attr("rel");
		$("#"+$subj).show().siblings(".opt").hide();
		if($subj == "RO-preview") {
			$("#RO-preview").html('<img src="/bin/img/loading-thickbox.gif" alt="loading"/>');
			$.post(
				"/forums/action.php",
				{ previewtxt: getReplyMsgTxt() },
				function(t){
					$("#RO-preview").html(t);
				}
			);
		}
	});
	
});

function resizeAvatars() {
	// get the height of each speech bubble and hide avatar if it's not high enough
	$("#forum .message").each(function(){
		var $y = $(this).height();
		var $img = $(this).children(".container").children("img").height();
		if(!$img && $y < 200) {
			$(this).parent("td").siblings(".user-details").children("a").children(".avatar").hide().siblings(".yesavtn").addClass("user").removeClass("yesavtn");
		}
	});
}

function forumToggle(what,jump) {
	$("#"+what).toggle();
	$("#no-stuff").toggle();
	$("#reply-cell").toggleClass('on');
	$("#posts-cell").toggleClass('on');
	if(jump) window.location='#'+jump;
}

function postQuote(what,jump,who) {
	initiateReply();
	if(who) who="[b]"+who+"[/b] \n";
	$('#fmessage').focus().val( $('#fmessage').val() + '[quote]' + who + $('#edit-text-'+what).html() + "[/quote]\n" );
	$("#quote-inserted").show();
	window.location='#'+jump;
}

function confirmDelete(pid) {
	var agree=confirm("Really delete this post?");
	if (agree) window.location='/forums/action.php?do=Delete+Post&pid='+pid;
}

function requiredA(usrid) {
	if(!usrid) return false;
	if(document.getElementById('ftitle').value=='') {
		alert("Missing the title field");
		return false;
	}
	if(document.getElementById('fmessage').value=='') {
		alert("Missing the message field");
		return false;
	}
	confirm_exit=false;
	return true;
}

function initiateReply() {
	$("#forum-initiate-reply").hide().next().show();
}

function postReply() {
	
	$("#jump-response").addClass("rm-on-fullpage");

	var $message = $('#fmessage').val().replace(/&/g, '[AMP]').replace(/\+/g, '[PLUS]');
	if($message == '' || $message == "Your reply here...") {
		alert("Missing the message field");
		return;
	}
	var $emailReplies = $("input[name='add_reply_mail']:checked").length;
	var $dontMail = $("input[name='dont_mail']:checked").length;
	var $tid = $('#tid').val();
	
	$('#reply-button').attr("disabled","disabled");
	$('#reply-loading').show().siblings().hide();
	$('#reply-cell').css("text-decoration","line-through");
	
	var pdata = {
		_do: "post_reply",
		message: $message,
		add_reply_mail: $emailReplies,
		dont_send_mail: $dontMail,
		tid: $tid,
		disable_emoticons: ( $("input[name='disable_emoticons']:checked").length ? '1' : '' )
	};
	
	if( $("#reply-id-old").hasClass("arrow-toggle-on") ) {
		//login
		$.post(
			"/bin/php/page.php",
			{ _do: "login",
				ajax_login: "1",
				username: $("#identify-un").val(),
				password: $("#identify-pw").val()
			},
			function(t) {
				if(t == "error") {
					alert("Error: wrong username/password combination. Please try again.");
					$('#reply-button').removeAttr("disabled");
					$('#reply-loading').hide().siblings().show();
					return;
				} else {
					processReply(pdata);
				}
			}
		);
	} else if( $("#reply-id-new").hasClass("arrow-toggle-on") ) {
		//REGISTER
		//validate
		var arki = $("#ajaxregkeyinp").val();
		arki = parseInt(arki);
		var ark = $("#ajaxregkey").val();
		ark = parseInt(ark) + 1;
		if(ark != arki) {
			alert("Error: Invalid math input. Please validate the form with the correct mathematical equation.");
			$('#reply-button').removeAttr("disabled");
			$('#reply-loading').hide().siblings().show();
			return;
		}
		
		var _em = $("#identify-email").val();
		var _un = $("#identify-name").val();
		$.post(
			"/register.php",
			{ _do: "ajaxreg",
				em: _em,
				un: _un
			},
			function(t) {
				if( t == "ok" ) {
					processReply(pdata);
				} else {
					alert(t);
					$('#reply-button').removeAttr("disabled");
					$('#reply-loading').hide().siblings().show();
					return;
				}
			}
		);
	} else {
		processReply(pdata);
	}
	
}

function processReply(pdata) {
	$.post(
		"/forums/action.php",
		pdata,
		function(t){
			var $arr = t.split("|--|");
			if($arr[2] != "") alert($arr[2]);
			if($arr[0]) $("#reply-form").html($arr[0])
			if($("#reply-form").height() < 200) {
				$("#jump-response .avatar").hide().siblings(".yesavtn").addClass("user").removeClass("yesavtn");
			};
			if($arr[1]) $("#reply-permalink").slideDown().attr("href", $arr[1]);
		}
	);
}

function submitEditedForumPost(pid) {
	$f1 = $("#editpost-"+pid);
	$f2 = $("#editpost-"+pid).siblings(".message-text");
	$f3 = $("#editpost-"+pid+" .submit-edited-post");
	$f4 = $($f3).siblings("img").css("display","inline");
	$f3.attr({ disabled:"disabled" });
	var poster  = $("#editpost-"+pid+" input[name=poster]").val();
	var txt     = $("#editpost-"+pid+" textarea[name=message]").val();
	$.post(
		"/forums/action.php",
		{ _do:               "Edit Post",
			_ajax:             "1",
			message:           txt,
			pid:               pid,
			poster:            poster,
			no_track:          ( $("#editpost-"+pid+" input[name='no_track']:checked").length ? '1' : '' ),
			disable_emoticons: ( $("#editpost-"+pid+" input[name='disable_emoticons']:checked").length ? '1' : '' )
		}, function(msg) {
			$f1.hide();
			$f2.html(msg).show();
			$f3.removeAttr("disabled");
			$f4.hide();
		}
	);
}

function postNewTopic() {

	var $message = $('#fmessage').val().replace(/&/g, '[AMP]').replace(/\+/g, '[PLUS]');
	if($message == "") {
		alert("Missing the message field");
		return;
	}
	var $title = $("#forum-form input[name='title']").val().replace(/&/g, '[AMP]').replace(/\+/g, '[PLUS]');
	var $tags = $("#forum-form input[name='tags']").val().replace(/&/g, '[AMP]').replace(/\+/g, '[PLUS]');
	var $type = $("#forum-form input[name='type']").val();
	
	$('#reply-button').attr("disabled","disabled");
	$('#reply-loading').show().siblings().hide();
	
	$.ajax({
		type: "POST",
		url: "/forums/action.php",
		data: "do=Post Topic&ajax=1&message="+$message+"&tags="+$tags+"&title="+$title+"&type="+$type,
		success: function(t){
			var $arr = t.split("|--|");
			if($arr[0] != "") alert($arr[2]);
			if($arr[1]) $("#reply-permalink").slideDown().attr("href", $arr[1]);
			if($arr[2]) $("#reply-form").html($arr[2]);
		}
	});
	
}

function suggestTag(tid) {
	
	$('#suggest-tag-form').html('<img src="/bin/img/loading-thickbox.gif" border="0" alt="loading" style="margin-top:5px"/>');
	
	$.ajax({
		type: "POST",
		url: "/forums/action.php",
		data: "outputSuggestTag=1&tid="+tid,
		success: function(t){
			$('#suggest-tag-form').html(t);
		}
	});

}

function showTagField(field) {
	$("#suggest-tag-fields").show().children("li").hide();
	$("#tag-field-"+field).show();
}

function submitTag(tag, tid) {
	
	if(!tag) return;
	$(".suggest-tag-button").val("Suggesting...").attr("disabled","disabled");
	
	$.post(
		"/forums/action.php",
		{ submit_tag_suggestion: 1,
			tid: tid,
			tag: tag
		},
		function(t) {
			$("#tags .suggest").hide();
			if(t.substr(0, 6) == "Error:") alert(t);
			else $("#put-new-tags").append(t);
			$(".suggest-tag-button").val("Suggest").removeAttr("disabled");
			$("#suggest-tag-fields").hide();
		}
	);
	
}

function deleteTag(tagid) {
	if(!confirm("Really delete this tag?")) return;
	$.ajax({
		type: "POST",
		url: "/forums/action.php",
		data: "delete_tag="+tagid,
		success: function(t) {
			if(t != "") alert(t);
		}
	});
}

function outputPosts(tid) {
	$(".postnav th").show();
	$.post(
		"/forums/action.php", 
		{ output_posts: tid },
		function(t) {
			$(".rm-on-fullpage").hide();
			$("#forum-posts-space").html(t);
			resizeAvatars();
			tooltip();
		}
	);
}