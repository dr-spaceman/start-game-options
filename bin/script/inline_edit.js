
var ILnosubmit = true; //only submit the form if the SUBMIT button is pressed (rather than hitting Enter key)

$(document).ready(function(){
	
	//alert if IE
	if($.browser.msie) {
		$("#notify").append('<dl><dt class="war">Warning</dt><dd>Although we make efforts for the best possible cross-browser compatibility, this form has been known to experience some unsurmountable problems with Microsoft Internet Explorer, which we\'ve detected as the browser you\'re currently using. We highly recommend using <a href="http://getfirefox.com" target="_blank">Firefox</a>, <a href="http://google.com/chrome" target="_blank">Google Chrome</a>, or pretty much any other browser.</dd></dt>');
		showNotifications();
	}
	
	$("body").css("margin-bottom","45px").addClass("editmode");
	$("#edit-mode-msg .container").html('This function has been depreciated. The new editing system will be online soon!');
	$("#edit-mode-msg").animate({ opacity:1 }, 2000, function(){
		$(this).animate({ bottom:"0" }, 1000);
	});
	
	$(".editable").hover(
		function(){ $(this).addClass("editable-hover"); },
		function(){ $(this).removeClass("editable-hover"); }
	).removeClass("editable-hidden").attr("title","Edit this").removeAttr("href").find("a").removeAttr("href").attr("title","Edit this");
	
	$(".editable-hidden").css("display","inline");
	$(".editable-hidden-block").css("display","block");
	
	$(".ILform .form H5").each(function(){
		var histField = $(this).closest(".ILform").attr("id").replace("IL-", "");
		$(this).prepend('<span class="opts"><a href="javascript:void(0);" title="Show the edit history of this field" onclick="ILfieldhistory(\''+histField+'\');">history</a> | <a href="javascript:void(0);" title="Close this editing field" onclick="$(this).closest(\'.ILform\').fadeOut(300);" style="color:#D83D3D;">close</a></span>');
		$(this).after('<div class="hist-space"></div>');
	});
	
	$(".editable").click(function(){
		return;
		var what = $(this).attr("id").replace(/ILedit-/, "");
		if(!what) {
			alert("That element has no attribute specified and can't be edited.");
			return;
		}
		if( !$("#IL-"+what).length ) {
			alert("That element has no form field and can't be edited.");
			return;
		}
		
		var pos = findPos(this);
		if( $("#IL-"+what).css("left") == '0px' ) {
			$("#IL-"+what).css("left",pos[0]+"px");
		}
		$("#IL-"+what).css("top",pos[1]+"px").show();
		$("#IL-"+what+" :input:visible:enabled:first").focus();
		
		//check for cookie, display standards
		if(what.substr(0, 14) == "upload-pub-pic") {
			if(!$.cookie('note_pubStandards')) {
				AGboxstandardsoverlay();
				$.cookie('note_pubStandards', 'noted');
			}
		}
		
	});
	
	//events that set the master form
	$(".ILform .submit").click(function() {
		return;
		var what = $(this).closest('.ILform').fadeOut(300).attr("id").replace(/IL-/, "");
		ILsetField(what);
	});
	$(".ILform :input").change(function(){
		return;
		if( $(this).hasClass("required") && $(this).val() == "" ) {
			alert("That field must have a value input into it");
			return;
		}
		$(this).closest(".ILform").addClass("ILchanged");
		if( $(this).is("select") && $(this).hasClass("focusonme") ) $(this).blur(); //blur select box onchange (if it's the appointed focused field)
	});
	$(".ILform .focusonme").blur(function() {
		return;
		var what = $(this).closest('.ILform').fadeOut(300).attr("id").replace(/IL-/, "");
		
		//if field is unchanged, don't do anything
		if( !$(this).closest('.ILform').hasClass("ILchanged") ) {
			$("#ILedit-"+what).removeClass("editable-loading");
			return;
		}
		
		ILsetField(what);
	});
	
	//autocomplete roles for people form
	if( $(".IL-input-role").length) {
		var _data = $.ajax({ url:"/bin/php/autocomplete_load.php", data:"what=roles", async:false }).responseText.split("|");
		$(".IL-input-role").autocomplete(_data, {max:20});
	}
	
	//contr notes
	$(".contribution-notes").change(function(){
		var cid = $(this).attr("name").replace("cid_", "");
		$("#submit-"+cid).val("Saving...").attr("disabled", "disabled");
		$.post(
			"/bin/php/inline_edit.php",
			{ _action: "update_contr_notes",
				_cid: cid,
				_notes: $(this).val()
			}, function(res){
				if(res) alert(res);
				$("#submit-"+cid).val("Saved").animate({opacity:1}, 1000, function(){ $(this).val('Save').removeAttr("disabled"); });
			}
		);
	});
	
});

function ILsetField(what) {
	
	if( !$("#IL-"+what).hasClass("ILchanged") ) return;
	$("#IL-"+what).removeClass("ILchanged");
	
	var setit = true;
	$("#IL-"+what+" :input.required").each(function() {
		if( $(this).val() == "" ) {
			alert("Error: This element can't be sent for changes since a required field is blank.");
			setit = false;
		}
	});
	if(!setit) return;
	
	var what2 = what;
	var outp = $("#IL-"+what+" .output").val(); //the current value in the form
	outp = $.trim(outp);
	if( $("#IL-"+what).hasClass("manual-output") ) { //get a special output value instead
		
		var vars = "";
		var outpset = false;
		
		//people
		if(what == "personpic") {
			outp = "new upload ready";
			what2 = "";
		}
		if(what == "persondob") {
			var dob = $("select[name='contr[persondob][year]']").val()+"-"+$("select[name='contr[persondob][month]']").val()+"-"+$("select[name='contr[persondob][day]']").val();
			outp = dob;
			what2 = "";
		}
		if(what == "assoc_co" || what == "assoc_other") {
			$("#"+what+"-space").html( outp.replace(/\n/g, " &middot; ") );
			what2 = "";
			outpset = true;
		}
		
		//games
		if(what == "genre" || what == "developers" || what == "series") what2 = "gengamedata";
		if(what.substr(0, 12) == "pub-platform") what2 = "pub-platform";
		if(what.substr(0, 4) == "pub_") {
			$("#ILedit-"+what).removeClass("editable-loading");
			$("#ILedit-"+what).html("changes made...");
			what2 = "";
		}
		if(what.substr(0, 7) == "devrole") {
			if( $("#IL-"+what+" .vital-check:checked").length ) $("#devrole-"+what.substr(8)).addClass("role-vital");
			else $("#devrole-"+what.substr(8)).removeClass("role-vital");
			what2 = "";
		}
		if(what.substr(0, 7) == "trivia_") {
			what2 = "bb2html";
			if( outp == "" ) what2 = "";
		}
		
		if(what2) {
			$("#ILedit-"+what).addClass("editable-loading");
			if(!vars) vars = { _action:"output", _what:what2, _text:outp };
			$.post(
				"/bin/php/inline_edit.php",
				vars,
				function(res) {
					$("#ILedit-"+what).removeClass("editable-loading");
					$("#ILedit-"+what).html( res );
				}
			);
		} else if(!outpset) {
			$("#ILedit-"+what).html( outp )
		}
	} else if( outp ) {
		$("#ILedit-"+what).html( outp )
	}
	
	if( $("#ILedit-"+what).html() == "" ) $("#ILedit-"+what).html('<i>NULL</i>');
	
	$("#ILmaster-form").append('<input type="hidden" name="changes[]" value="'+what+'"/>');
	
	$("#IL-master-submit-button:hidden").fadeIn();
	$("#edit-mode-msg .message:hidden").slideDown(function(){
		$(this).animate({ bottom:"43px" }).animate({opacity: 1.0}, 3000).animate({ bottom:"48px" }, function(){ 
			$(this).slideUp();
		})
	});
	
	confirm_exit = true;
	
}

function ILfieldhistory(f) {
	var sp = $("#IL-"+f+" .form .hist-space");
	if( $(sp).html() ) {
		$(sp).hide().html('');
		return;
	}
	var subj = $("input[name='contr["+f+"][subj]']").val();
	$(sp).html('<div class="msg">Loading history...</div>').show().load("/bin/php/inline_edit.php", { load_field_history: subj });
}

function watchList(ssubj) {
	
	$.post(
		"/bin/php/inline_edit.php",
		{ watchlist: ssubj },
		function(res){
			if(res != "") {
				alert(res);
				$(".chbox-loading").toggleClass("chbox-checked")
			}
			$(".chbox-loading").animate({opacity:1}, 300, function(){ $(this).removeClass("chbox-loading") });
		}
	);
	
}

function forfeitPoints(cid) {
	
	$.post(
		"/bin/php/inline_edit.php",
		{ forfeitpoints: cid },
		function(res){
			if(res != "") {
				alert(res);
				$(".chbox-loading").toggleClass("chbox-checked")
			}
			$(".chbox-loading").animate({opacity:1}, 300, function(){ $(this).removeClass("chbox-loading") });
		}
	);
	
}





function ILsubmit(f) {
	
	$(f).attr("disabled","disabled");
	$(f).before('<img src="/bin/img/loading-arrows-small.gif" alt="loading" class="loading"/> ');
	var d = "";
	$(f).parents(".ILeditor").find("textarea,input,select").each(function(){
		var n;
		var v;
		if( n = $(this).attr("name") ) {
			v = $(this).val().replace(/&/g, '[AMP]');
			d += (d ? "&" : "")+n+"="+v;
		}
	});
	
	var th = $(f);
	$.ajax({
		type: "POST",
		url: "/bin/php/inline_edit.php",
		data: d,
		success: function(t){
			if(t.slice(0, 7) == "Error: ") {
				$(th).removeAttr("disabled").parents(".ILeditor").find(".loading").remove();
				alert(t);
			} else {
				if(!t) t = "Add something";
				$(th).removeAttr("disabled").parents(".ILeditor").hide().prev().html(t);
			}
		}
	});
	
}

