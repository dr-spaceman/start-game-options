
var GCon = '';
var has_trivia = false;
var has_screens = false;

$(document).ready(function(){
	
	if(location.hash.substr(0, 12) == "#contribute-") {
		GCtoggle(location.hash.replace(/#contribute-/, ""));
	}
	
	$("#contribution-panel a").click(function(e) {
		e.preventDefault();
		var what = $(this).attr('href').replace(/#/, '');
		if(what == "close") $("#contribution-panel").fadeOut();
		else if(what != "") GCtoggle(what);
	});
	
	$("#contribute-message").hover(function() {
		$(this).addClass("hov");
	}, function() {
		$(this).removeClass("hov");
	}).click(function() {
		if( $("#dontshowcm").is(":checked") ) {
			//make a cookie and don't show the message ever again!!!
			$.post(
				"/bin/php/games-contribute.php",
				{ action:"set message cookie" }
			);
		}
		$(this).animate({ opacity:1 }, 200, function() { $(this).fadeOut(); });
	});
	
});

function openContributionPanel() {
	var w = $("body").width();
	var woffset = (w - 700) / 2;
	$("#contribute .message").fadeOut();
	$("#contribution-panel").css("left",woffset+"px").fadeIn();
}
	
var pdata = ""; //database values loaded

function GCtoggle(what, _reload) {
	
	if($("#contribution-panel").is(":hidden")) openContributionPanel();
	
	var space = $("#contribute-space");
	var loading = $("#contribute .loading");
	
	if(GCon == "trivia" && has_trivia) {
		if(!confirm("By navigating away, your current trivia input will be lost.")) return;
		else has_trivia = false;
	}
	
	//change the menu to show active element
	if(GCon) $("#contribute-nav a").removeClass("on");
	$("#contribute-nav a[href=#"+what+"]").addClass("on");
	
	//end function if the chosen element is already showing
	if(GCon == what && _reload != "reload") return;
	
	//create new space for fading between elements
	$("#GCspace-"+what).remove(); // remove if already present as to not have more than one ID
	$(".GCspace").removeClass("GCspaceOn");
	$(space).append('<div id="GCspace-'+what+'" class="GCspace GCspaceOn" style="display:none"></div>');
	var space2 = $("#GCspace-"+what);
	
	if(what == "trivia") {
		
		GCloading(1);
		
		$.post(
			"/bin/php/games-contribute.php",
			{ action: "trivia_form",
				gid: gid
			},
			function(t) {
				$(space2).html(t);
				GCtransition();
				GCloading(0);
				tooltip();
				TBinit();
			}
		);
		
	}
	
	if(what == "link") {
		$(space2).html('<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td width="100%" style="padding-right:5px;"><input type="text" value="http://" onfocus="if(this.value==\'http://\') this.value=\'\';" id="gc-input-link" style="text-decoration:underline; color:#06C; width:100%; font-size:21px;"/></td><td><input type="button" value="Next &gt;" style="font-size:21px" onclick="GCpreviewlink()"/></td></tr></table>');
		GCtransition();
	}
	
	if(what == "review") {
		$(space2).html("Coming soon.");
		GCtransition();
	}
	
	if(what == "pub") {
		
		GCloading(1);
		
		//check for cookie, display standards
		if(!$.cookie('note_pubStandards')) {
			AGboxstandardsoverlay();
			$.cookie('note_pubStandards', 'noted');
		}
		
		$.post(
			"/bin/php/games-contribute.php",
			{ action: "pub_form",
				gid: gid
			},
			function(t) {
				$(space2).html(t);
				GCtransition();
				GCloading();
				tooltip();
			}
		);
		
	}
	
	if(what == "screens") {
		
		$(space2).html('To post images, including screenshots, artwork, scans, and other media, <a href="/posts/manage.php?action=newpost&type=image&autotag=gid:'+gid+'">begin a new session with the Content Manager</a>. All content tagged with this game will show up somewhere on this page automatically.');
		GCtransition();
		
	}
	
	if(what == "video") {
		
		GCloading(1);
		
		$.post(
			"/bin/php/games-contribute.php",
			{ action: "video",
				gid: gid
			},
			function(t) {
				$(space2).html(t);
				GCtransition();
				GCloading();
				tooltip();
			}
		);
		
	}
	
	if(what == "person") {
		
		var outp = 'Credit sombody with the development of this game (a person, not a development group)<br/><form action="" onsubmit="GCsuggestname(); return false;"><table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top:5px"><tr><td width="100%" style="padding-right:2px"><input type="text" name="name" id="gc-input-name" style="width:100%; font-size:21px;"/></td><td><input type="submit" value="Next &gt;" style="font-size:21px;"/></td></tr></table></form>';
		$(space2).html(outp);
		GCtransition();
		
		if(pdata == "") pdata = $.ajax({ url:"/bin/php/autocomplete_load.php", data:"what=people", async:false }).responseText.split("|");
		
		$("#gc-input-name").autocomplete(
			pdata, {
				matchContains:true, 
				selectFirst:false,
				formatItem:function(row) {
					var dat = row[0].split("`");
					return (dat[5] == '1' ? '<b>' : '')+dat[0]+(dat[5] == '1' ? '</b>' : '')+(dat[4] ? ' <span style="color:#888;">('+dat[4]+')</span>' : '');
				}
			}
		).result(function(_event, _data, formatted) {
			var dat = _data[0].split("`");
			$(this).val(dat[0]);
			GCsuggestname(dat[0]);
			return;
		});
		
	}
	
	if(what == "quote") {
		
		GCloading(1);
		
		$.post(
			"/bin/php/games-contribute.php",
			{ action: "quote_form",
				gid: gid
			},
			function(t) {
				$(space2).html(t);
				GCtransition();
				GCloading();
				tooltip();
			}
		);
		
	}
	
	GCon = what;

}

function GCtransition() {
	var space = $("#contribute-space");
	var space2 = $(".GCspaceOn");
	var h = $(space2).height()+"px";
	$(".GCspace").fadeOut("fast");
	$(space).animate({ height:h }, "normal", function() {
		$(space2).fadeIn();
		$(space).css("height", "auto");
	});
}

function GCloading(on) {
	
	var x = $("#contribute .loading .on").css("left");
	var z = '<img src="/bin/img/big_plus.png" alt="add" border="0" class="off" style="left:-18px;"/>';
	
	if(on) {
		if(x == "8px") return; //it's already on
		$("#contribute .loading .on").css("left","-20px").animate({left:"10px"});
		$("#contribute .loading .off").animate({left:"50px"}).remove();
		$("#contribute .loading a").append(z);
	} else {
		$("#contribute .loading .on").animate({left:"38px"}, function(){
			$("#contribute .loading .off").animate({left:"10px"});
		});
	}
	
}

function GCsubmittrivia() {
	
	var space = $("#GCspace-trivia");
	var fact = $("#gc-input-trivia").val();
	if(!fact) {
		alert("Please input an interesting (and true!) fact into the space provided.");
		return;
	}
	
	GCloading(1);
	$("#gc-input-trivia").attr("disabled","disabled");
	$("#gc-trivia-button").attr("disabled", "disabled");
	has_trivia = false;
	
	$.post(
		"/bin/php/games-contribute.php",
		{ action:"submit_trivia", 
			gid:gid, 
			fact:fact,
			author:$("#gc-input-author").val(),
			author_link:$("#gc-input-authorlink").val()
		},
		function(t) {
			$(space).html(t);
			GCloading();
		}
	);
	
}

function GCpreviewlink() {
	
	var space = $("#GCspace-link");
	var _url = $("#gc-input-link").val();
	if(_url.substr(0,4) != 'http') {
		alert("Please input a http:// link");
		_url = 'http://';
	}
	if(_url && _url != 'http://') {
		
		GCloading(1);
	
		$.post(
			"/bin/php/games-contribute.php",
			{ action:"preview_link",
				gid:gid,
				_url:_url
			},
			function(t) {
				$(space).html(t);
				GCloading();
			}
		);
		
	}
	
}

function GCcheckLinkSubmission() {
	
	var GClinkmath  = document.getElementById('addlinkinpmath').value;
	var GClinkmath1 = document.getElementById('addlinkmath1').value;
	var GClinkmath2 = document.getElementById('addlinkmath2').value;
	
	if(parseInt(GClinkmath) != (parseInt(GClinkmath1) + parseInt(GClinkmath2))) {
		alert('Your math is wrong.');
		return false;
	} else {
		return true;
	}
	
}

function GCcheckpub() {
	
	if(!document.getElementById('gc-pub-file').value) {
		alert('Please select a file to upload');
		return false; 
	}
	if(!document.getElementById('gc-pub-platform').value) {
		alert('Please select a platform');
		return false; 
	}
	if(!document.getElementById('gc-pub-region').value && !document.getElementById('gc-pub-region-other').value) {
		alert('Please select a region');
		return false; 
	}
	toggle('gc-pub-upload','gc-pub-input');
	return true;
	
}

function GCcheckss(i) {
	
	i = parseInt(i);
	j = i + 1;
	
	if(!document.getElementById('gc-ss-'+i+'-file').value) {
		alert('Please select a file to upload');
		return false; 
	} else { 
		if(i == 1) toggle('gc-ss-finished','');
		toggle('gc-ss-'+i+'-upload','gc-ss-'+i+'-input');
		toggle('gc-ss-'+j,'');
		has_screens = true;
		return true; 
	}
	
}

function GCsubmitSsSource(dir) {
	
	$("#gc-ss-source-button").val("Sending...").attr("disabled","disabled");
	$.post(
		"/bin/php/games-contribute.php", 
		{ action:"ss source", gid:gid, dir:dir, sname:$("#gc-ss-source").val(), surl:$("#gc-ss-sourceurl").val() },
		function(t){ 
			$("#gc-ss-source-space").html(t);
			$("#gc-ss-source-button").val("Submit").removeAttr("disabled");
		}
	);
}

var _roledata = "";

function GCsuggestname(x) {
	
	var _name = "";
	if(x) _name = x;
	else _name = $("#gc-input-name").val();
	if(!_name) return;
	var space = $("#GCspace-person");
	
	GCloading(1);
	
	if(!_roledata) _roledata = $.ajax({ url:"/bin/php/autocomplete_load.php", data:"what=roles", async:false }).responseText.split("|");
	
	$.post(
		"/bin/php/games-contribute.php",
		{ _action:"check_name", 
			_gid:gid, 
			_name:_name
		}, function(t) {
			$(space).html(t);
			tooltip();
			$("#gc-input-role").autocomplete(_roledata, {max:20});
			GCloading();
		}
	);
	
}

function GCsubmitperson() {
	
	var space = $("#GCspace-person");
	var _role = $("#gc-input-role").val();
	if(_role == "Start typing to find a common role") _role = "";
	if(!_role) { alert("Please assign this person a role."); return; }
	var _title = "";
	if( $("#gc-newperson-title").length ) {
		_title = $("#gc-newperson-title").val();
		if(_title == "") { alert("Please input a title"); return; }
	}
	var vital = "";
	if( $("#role-vital").is(":checked") || $("#role-nonvital").is(":checked") ) vital = ( $("#role-vital").is(":checked") ? '1' : '');
	else { alert("Please mark this person's role as either vital or personnel"); return; }
	
	GCloading(1);
	
	$("#gc-person-submit-button").attr("disabled","disabled");
	
	$.post(
		"/bin/php/games-contribute.php",
		{ _action:"submit_person",
			gid: gid,
			pid: $("#gc-pid").val(),
			_name: $("#gc-name").val(),
			role: _role,
			notes: $("#gc-role-notes").val(),
			vital: vital,
			_title: _title
		},
		function(t) {
			$(space).html(t);
			GCloading();
		}
	);
	
}

function AGboxstandardsoverlay() {
	var w = $("body").width();
	var h = $("body").height();
	$("body").append('<div id="TB_overlay" style="height:'+h+'px; width:'+w+'px;"></div><div id="TB_window" style="top:80px; left:10%; right:10%; display: block; border:3px solid black;"><div align="center"><img src="/bin/img/loading-thickbox.gif" alt="loading" style="margin-bottom:15px;"/></div></div>');
	$("#TB_window").load("/bin/php/games-contribute.php", { action:"print_pub_standards", gid:gid });
}

function GCrmoverlay() {
	$('#TB_overlay, #TB_window').remove();
}