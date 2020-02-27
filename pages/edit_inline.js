
var pgid    = '';
var pgtitle = '';
var pgtype  = '';
var sessid  = '';
var field_index = 500;
var ile_confirm_exit_msg = "All unsaved changes will be lost."

//var gtitle_data = $.ajax({ url:"/bin/php/autocomplete_load.php", data:"what=pages_game_titles", async:false }).responseText.split("|");

$(document).ready(function(){
	
	loadAcData();
	
	pgid    = $(":input[name='pgid']").val();
	pgtitle = $(":input[name='pgtitle']").val();
	pgtype  = $(":input[name='in[pgtype]']").val()
	sessid  = $(":input[name='sessid']").val();
	
	//var mover = false;
	$("body").css("margin-bottom","45px").removeClass("viewmode").addClass("editmode");
	$("#ile-msg").animate({ opacity:1 }, 2000, function(){
		$(this).animate({ bottom:"0" }, 1000);
	});/*.hover(function(){
		mover=true;
		$(this).find(".toggle").animate({opacity:1}, 120, function(){ if(mover) $(this).slideDown(); });
		$(this).find(".message").fadeOut();
	}, function(){
		mover=false;
		$(this).find(".toggle").animate({opacity:1}, 300, function(){ if(!mover) $(this).slideUp(); });
	});*/
	
	$(".il").
		live("mouseover", function(){ $(this).addClass("il-hover"); }).
		live("mouseout", function(){ $(this).removeClass("il-hover"); }).
		attr("title","Edit this").find("a").live("click", function(v){ v.preventDefault(); });
	
	$(".il-hidden").css("display","inline");
	$(".il-hidden-block").css("display","block");
	
	$("#ile :input").change(function(){
		confirm_exit = true;
		confirm_exit_msg = ile_confirm_exit_msg;
		$.cookie('unsavedSess', '1', {path:'/'}); //record for Resetti badge
	});
	
	//add X and buttons to each field
	$("#ile dl").each(function(){
		
		if( !$(this).hasClass("nodrag") ) $(this).draggable();
		
		$(this).children("dt").prepend('<a href="#edit" title="Close this editing field (changes will still take effect upon submission)" class="ximg" style="top;12px; right:20px;" onclick="ileclose($(this).closest(\'dl\'));" tabindex="12">close</a>');
		if( !$(this).children('.ok').length ) $(this).append('<dd class="ok"><input type="button" value="OK" tabindex="11"/></dd>');
	
	});
	
	$(".editmode .il").live("click", function(){
		var what = $(this).attr("id").replace("il-", '');
		if(!what) return;
		iledit(what);
	});
	
	
	//event that sets the master form
	$("#ile .ok :input[type='button']").live("click", function() {
		
		var what = $(this).closest("dl").attr("id").replace("ile-", "");
		
		var setit = true;
		$("#ile-"+what+" :input.required").each(function() {
			if( $(this).val() == "" ) {
				alert("Error: This element can't be sent for changes since a required field is blank.");
				setit = false;
			}
		});
		if(!setit) return;
		
		$("#ile").append('<input type="hidden" name="changes[]" value="'+what+'"/>');
		
		//format text in input.ilereturn fields and return it via AJAX
		var ret = $("#ile-"+what+" :input.ilereturn");
		if( $(ret).length ){
			
			$(this).attr("disabled", "disabled").val("Formatting...").animate({opacity:1}, 750, function(){ $(this).removeAttr("disabled").val("OK").closest("dl").fadeOut(); });
			
			$.post(
				"/pages/edit_ajax.php",
				{ _do:"ile_ouptut_field",
					_field:what,
					_input:$(ret).val() 
				}, function(res){
					if(!res || res == '<p></p>') res = '<i>NULL</i>';
					$("#il-"+what).html(res).find("a").removeAttr("href");
				}
			);
			
		} else if( $("#ile-"+what).hasClass("serializedreturn") ){
			
			$(this).attr("disabled", "disabled").val("Formatting...").animate({opacity:1}, 1000, function(){ $(this).removeAttr("disabled").val("OK").closest("dl").fadeOut(); });
			
			var formfields = $("#ile #ile-"+what+" :input").serialize();
			
			$.post(
				"/pages/edit_ajax.php",
				{ _do:"ile_ouptut_field",
					_field:what,
					_input:formfields,
					_pgtype:pgtype
				}, function(res){
					if(!res) res = '<i>NULL</i>';
					$("#il-"+what).html(res).find("a").removeAttr("href");
				}
			);
			
		} else $(this).closest("dl").fadeOut();
		
		$("#il-"+what).siblings(".null").hide();
		
		$("#ile-submit:hidden").fadeIn();
		$("#ile-msg .message:hidden").fadeIn(500).animate({bottom:"43px"}, {queue:false, duration:500}).animate({opacity:1}, 2200, function(){
			$(this).fadeOut(500).animate({bottom:"63px"}, {queue:false, duration:500});
		});
		
	});
	
	$(".editpgsubmit").click(function(){
			
		//submit
		
		if( $(":input[name='edit_summary']").val() == "" && $("#formaction").val() != "draft" ) {
			if(!confirm("Submit these changes without leaving an edit summary?")) return false;
		}
		$(".editpgsubmit").attr("disabled", "disabled");
		confirm_exit = false;
		$.cookie('unsavedSess', null, {path:'/'});
		document.ile.submit();
		
	});
	
	
	//add credit
	var addingcred = false;
	$(".addcredit").click(function(v){
		
		v.preventDefault();
		
		if(addingcred) return;
		addingcred = true;
		
		$("#credits .null").hide();
		
		field_index++;
		
		//get and insert an empty form field via AJAX
		$.post(
			"edit_ajax.php",
			{ _do: 'output_listitem', 
				_key: 'ile-'+pgtype+"-credit",
				_index: field_index
			}, function(res) {
				
				addingcred = false;
				
				$("#ile").append(res);
				
				var newil = $("#il-cr-clone").val().replace("INDEXID", field_index);
				if(pgtype == "person") $("#il-cr-cloned > dt").after(newil);
				else $("#creditscontainer").append(newil);
				iledit("cr-"+field_index);
				
				loadAcData();
				tooltip();
				
			}
		);
	});
	
	if(pgtype == "game"){
		var crsort = '';
		$("#creditscontainer").sortable({
			items: 'dl',
			placeholder: 'placeholder',
			update: function(event, ui) {
				crsort = $(this).sortable('toArray').toString();
				$("#crsort").val(crsort);
			}
		});
		$("#creditscontainer").disableSelection();
	}
	
	$(".cr-v-toggle").live("click", function(){
		var what = $(this).closest("dl").attr("id").replace("ile-", "");
		if( $(this).val() == "vital" ) $("#il-"+what).removeClass("toggle").addClass("vital");
			else $("#il-"+what).removeClass("vital").addClass("toggle");
		});
	
});

function iledit(what){
	
	if(!what) {
		alert("That element has no attribute specified and can't be edited.");
		return;
	}
	if( !$("#ile-"+what).length ) {
		alert("That element has no form field and can't be edited.");
		return;
	}
	
	if($("#ile-"+what).css("position")!="fixed"){
		var pos = $("#il-"+what).offset();
		if( $("#ile-"+what).css("left") == '0px' ) $("#ile-"+what).css("left",pos.left+"px");
		if( $("#ile-"+what).css("top") == '0px' )  $("#ile-"+what).css("top",pos.top+"px");
	}

	$("#ile-"+what).fadeIn(300);
	
	var focusf = $("#ile-"+what+" :input[tabindex='1']");
	if( !$(focusf).length ) focusf = $("#ile-"+what+" :input:visible:enabled:first");
	$(focusf).focus();
	
}

function saveDraft(form_target){
	
	$("#ile").attr("target", "_top");
	document.ile.submit();
	
}

function loadAcData(){
	
	//load some data for autocomplete fields
	
	$(".ile-ac").autocomplete(
		"/bin/php/autocomplete.php",
		{ minChars:3,
			max:30,
			selectFirst:true,
			formatItem:function(row) {
				return '<small>'+row[1]+"</small>"+row[0];
			}
		}
	).result(function(event, data, formatted) {
		$(this).val('[['+(data[3] ? data[3] : formatted)+']]');
	});
	
}

function ileclose(elem){
	
	if(elem) $(elem).fadeOut();
	
}

function addPub() {
	
	var trig = $("#addpubbutton :input[type='button']");
	var sp   = $(trig).closest("dl");
	$(trig).val("Adding...").attr("disabled", "disabled");
	$.post(
		"edit_ajax.php",
		{ _do: 'output_listitem', 
			_key: "publication",
			_index: field_index++,
			_pgtitle: pgtitle
		}, function(res) {
			$("#newpubsafterme").after(res);
			acPlatforms();
			tooltip();
			$(trig).val("Added!").removeAttr("disabled").animate({opacity:100}, 2000, function(){ $(this).val("Add a Publication"); });
		}
	);
	
}

function acPlatforms(){
	if(!pf_data) var pf_data = $.ajax({ url:"/bin/php/autocomplete_load.php", data:"what=platforms", async:false }).responseText.split("|");
	$(":input.acplatforms").autocomplete(
		pf_data, 
		{ minChars:0, matchContains:true, selectFirst:true, 
			formatItem:function(row) {
				var dat = row[0].split("`");
				return dat[0]+'<div style="display:none;">'+dat[1]+'</div>';
			}, formatResult:function(row){
				var dat = row[0].split("`");
				return '[['+dat[0]+']]';
			}
		}
	);
}

function rmToolItem(El) {
	
	if(!confirm("Remove this row?")) return;
	
	if( !$(El).siblings(".tool-item").length ) { $(El).siblings(".null").show(); }
	$(El).remove();
	
}