
function getReplyMsgTxt() {
	return $("#wmd-input").val();
}

var messagefocused = false;

$(document).ready(function() {
	
	$("#fpostslist .postitem").live("mouseover", function(){
		$(this).addClass("hov");
	}).live("mouseout", function(){
		$(this).removeClass("hov");
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
	
	$(":input[name='message']").focus(function(){
		messagefocused = true;
	});
	
	//keynav
	var thisn = -1;
	var isCtrl = false;
	var uposts = $("#unreadposts");
	if(uposts.length){
		
		uposts = $(uposts).val().split(",");
		
		$(document).keyup(function (e) {
			if(e.which == 17) isCtrl = false;
		}).keydown(function (e) {
			if(e.ctrlKey) {
				isCtrl=true;
			} else return;
			if(e.which == 38) { //up
				if(!isCtrl) return;
				thisn--;
			} else if(e.which == 40) { //down
				if(!isCtrl) return;
				thisn++;
			} else return;
			
			//e.preventDefault();
			
			if(thisn < 1) thisn = 0;
			if(thisn >= uposts.length) thisn--;
			
			var loc = window.location.href;
			var h = loc.indexOf("#");
			if(h) loc = loc.substr(0, h);
			window.location = loc+'#p'+uposts[thisn];
			$('#p'+uposts[thisn]).focus();
			
		});
	}
	
	/*$(document).keydown(function(Ev) {
		if(messagefocused) return;
		if(!$("#unreadposts").length) return;
		var uposts = $("#unreadposts").val().split(",");
		if(!uposts.length) return;
		
		var k = Ev.keyCode;
		if(k == 38) {//up
			thisn--;
		} else if(k == 40) {//down
			thisn++;
		} else return;
		
		Ev.preventDefault();
		if(thisn < 1) thisn = 0;
		if(thisn >= uposts.length) thisn--;
		
		var loc = window.location.href;
		var h = loc.indexOf("#");
		if(h) loc = loc.substr(0, h);
		window.location = loc+'#p'+uposts[thisn];
		$('#p'+uposts[thisn]).focus();
	});*/
	
	var replyInsert = '[@](#p[PID])[USER] ',
	    replyInsertWhole = '';
	
	$("#forum-posts").on("click", ".message-op", function(){
		var pid = $(this).data("pid");
		switch($(this).data("op")){
			case "reply":
				$(this).closest(".postitem").nextUntil("#topic-reply").addClass("closedforreply").slideUp(100);
				replyInsertWhole = ($("#wmd-input").val() ? $("#wmd-input").val() + "\n\n" : '') + replyInsert.replace('[PID]', pid).replace('[USER]', $(this).data("user"));
				$("#wmd-input").val(replyInsertWhole).focus().closest(".postitem").addClass("reply");
				$("#input-replyto").val(pid);
				$("#label-replyto").html('<a href="#p'+pid+'" title="You\'re replying to '+$(this).data("user")+'" class="message-reply">'+$(this).data("user")+'</a> &nbsp; <button type="reset" class="cancel" onclick="cancelReply()">Cancel Reply</button><div class="spacer" style="height:10px"></div>');
				break;
			case "edit":
				$("#message-"+pid).addClass("loading");
				$("#edit-"+pid).load("/forums/action.php", { load_edit_form:pid }, function(res){
					$("#message-"+pid).removeClass("loading").hide();
					$("#edit-"+pid).show();
				});
				break;
			case "delete":
				deletePost($(this).data("pid"));
				break;
		}
	}).on("reset", ".message-edit form", function(){
		$(this).closest(".message-edit").html('').siblings(".message").show();
	}).on("submit", "#topic-reply form", function(ev){
		
		// Submit New Topic / New Reply form
		
		confirm_exit=false;
		
		if($(this).data("formaction") == "newtopic") return true;
		
		loading.on();
		
		var $form = $(this),
		    finput = $form.serialize();
		
		$form.find(":input").prop("disabled", true);
		
		$.post(
			"/forums/action.php",
			{ _do:"post_reply", _in:finput }, 
			function(res){
				$form.find(":input").prop("disabled", false);
				if(res.errors){
					loading.off();
					handleErrors(res.errors);
				}
				if(res.pid){
					loading.off();
					//get new post and load it into the postlist
					$.post(
						"/forums/action.php",
						{ "load_message":res.pid },
						function(ret){
							$("#topic-reply").hide().before(ret);
						}
					);
				} else {
					loading.off();
				}
			}
		);
		
		return false;
	}).on("submit", ".message-edit form", function(ev){
		
		// Submit edit message
		
		ev.preventDefault();
		loading.on();
		
		var $form = $(this),
		    finput = $form.serialize();
		
		$form.find(":input").prop("disabled", true);
		
		$.post(
			"/forums/action.php",
			{ _do:"edit_post", _in:finput }, 
			function(res){
				$form.find(":input").prop("disabled", false);
				if(res.errors){
					loading.off();
					handleErrors(res.errors);
				}
				if(res.success){
					loading.off();
					$form.closest(".message-edit").html('').siblings(".message").show().html("Loading new message...").load("/forums/action.php .message > *", {"load_message":res.pid});
				} else {
					loading.off();
				}
			}
		);
		return false;
	});
	
});


function togglePreview(i){
	if( $("#fmsg-"+i+"-prevbutton").text() == "Preview" ) {
		$("#fmsg-"+i+"-form :input").attr("disabled", "disabled");
		loading.on();
		$.post(
			"/forums/action.php", 
			{ previewtxt: $("#wmd-input").val()	},
			function(t) {
				$("#fmsg-"+i+"-prevbutton").text("Edit");
				$("#fmsg-"+i+"-form .message-preview").html(t).show().prev().hide();
				$("#fmsg-"+i+"-form :input").removeAttr("disabled");
				loading.off();
			}
		);
	} else {
		$("#fmsg-"+i+"-prevbutton").text("Preview");
		$("#fmsg-"+i+"-form .message-preview").hide().prev().show();
	}
}


function deletePost(pid) {
	if( confirm("Permanently delete this post?") ){
		$("#p"+pid).addClass("loading");
		$.post(
			"/forums/action.php",
			{ delete_post: pid },
			function(res){
				if(res.error){
					alert("Error: "+res.error);
					$("#p"+pid).removeClass("loading");
				} else {
					$("#p"+pid).fadeOut();
				}
			}, "json"
		);
	}
}

function forumSubscription(subj, id, elem){
	if( !$("#usrid").val() ) {
		login.init();
		return;
	}
	$(elem).addClass("loading");
	$.post(
		"/forums/action.php",
		{ _do: "manage_subscription",
			_subj: subj,
			_id: id
		}, function(res){
			if(res) alert("Error: "+res);
			$(elem).animate({opacity:1}, 500, function(){ $(this).removeClass("loading"); });
		}
	);
}

function requiredA() {
	if(!$("#NT-title").val()) { alert("Please input a TOPIC TITLE"); return false; }
	if(!$("#wmd-input").val()) { alert("Please input a MESSAGE"); return false; }
	confirm_exit=false;
	return true;
}

function loadPosts(tid) {
	$(".postnav .loading img").show();
	$.post(
		"/forums/action.php", 
		{ load_posts: tid },
		function(t) {
			$(".postnav").hide();
			$("#fpostslist .postitem").remove();
			$("#fpostslist").addClass("loaded").prepend(t);
			tooltip();
		}
	);
}

function fRatePost(pid, r, el){
	if( !$("#usrid").val() ) {
		login.init();
		return;
	}
	$(el).addClass("loading");
	$.post(
		"/forums/action.php",
		{ rate_post:pid,
			rating:r
		}, function(res){
			if(res.error) alert("Error: "+res.error);
			$(el).removeClass("loading").find(".rating").attr("title", res.title).html(res.outp);
		}, "json"
	);
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
	$('#wmd-input').focus().val( $('#wmd-input').val() + '[quote]' + who + $('#edit-text-'+what).html() + "[/quote]\n" );
	$("#quote-inserted").show();
	window.location='#'+jump;
}

function postNewTopic() {

	var $message = $('#wmd-input').val().replace(/&/g, '[AMP]').replace(/\+/g, '[PLUS]');
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

function cancelReply(){
	$("#forum .postitem.closedforreply").show();
	$("#wmd-input").closest(".postitem").removeClass("reply");
	$("#input-replyto").val('');
	$("#label-replyto").html('');
}