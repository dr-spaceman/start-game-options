
var confirm_exit_msg = "Your changes haven't been published yet";

var pgid    = "",
		pgtitle = "",
		pgtype  = "",
		sessid  = "",
		field,
		next_field = 'title',
		tooldata = [];

$(document).ready(function(){
	
	pgid    = $("#pgid").val();
	pgtitle = $("#pgtitle").val();
	sessid  = $("#sessid").val();
	
	$("#head").addClass("condensed");
	
	var EditConsole = {
		o:function(){
			$("#pged-submit-outer").slideDown();
			$('#edit_summary').focus();
		},
		c:function(){
			$("#pged-submit-outer").slideUp();
		}
	}
	$("#pgedconsole").animate({opacity:1}, 1000, function(){
		$(this).animate({bottom:'0'}, 600);
		if( $("#pgedpopmsg").length ) $("#pgedpopmsg").css("bottom", "112px").animate({opacity:1, "bottom":"92px"}, 600);
	}).hoverIntent({over:EditConsole.o, out:EditConsole.c});
	$("#pgedswitchnub").mousedown(function(){
		if( $(this).hasClass("left") ) $.address.value("preview");
		else $.address.value("edit");
	});
	
	$.address.change(function(event){
		
		if(field = $.address.parameter("field")){
			console.log("addr ch ["+field+"]");
			//activate the field specified in the address
			changes.activate(field);
		} else {
			if($.address.value() == "edit"){
				loading.off();
				$(".pgsection").remove();
				$("#pgedwrap").show();
				$("body").removeClass("pg").addClass("pged fixedwidth");
				$("#pgedswitchnub").animate({"left":"33px"}).removeClass("right").addClass("left");
				if(changes.curr_field){
					$.address.value("?field="+changes.curr_field);
					changes.scrollfocus();
				}
			} else if($.address.value() == "preview"){
				$("#pgedswitchnub").animate({"left":"54px"}).removeClass("left").addClass("right");
				loading.on();
				if( $("#fields > dl.changednotsaved").length ){
					changes.saveAll();
					$("body").animate({opacity:1}, 2000, function(){ fetchPreview() });
				} else fetchPreview();
				$('html, body').animate({scrollTop:0 }, 500);
				
				if( $("#pgedpopmsg").length ){
					$("#pgedpopmsg").fadeOut();
					$.cookie('page_editor_message', '1', {expires:365, path:'/'});
				}
			}
		}
	}).strict(false);
	
	function fetchPreview(){
		$.post(
			"/pages/edit_ajax.php",
			{ _do:'preview', _handle:$("#pghandle").val() },
			function(ret){
				if(ret == '') alert("There was an error outputting a preview");
				else{
					$("#pgedwrap").hide().after(ret);
					$("body").removeClass("pged").addClass("pg");
				}
				loading.off();
			}
		);
	}
	
	$(".pgedin-submit").live("click", function(){
		// save edit
		var f = $(this).attr("rel");
		changes.save(f);
		changes.deactivate(f);
		return false; //prevent click bubbling
	});
	$(".pgedin-cancel").live("click", function(){
		// cancel edit
		var f = $(this).attr("rel");
		changes.cancel(f);
		return false;
	});
	
	// focus on a new field
	// ie via Tab or click
	$("#fields > dl").each(function(){
		$(this).addClass("noact nohov");
	}).hover(
		function(){ $(this).addClass("hov").removeClass("nohov"); },
		function(){ $(this).removeClass("hov").addClass("nohov"); }
	).click(function(ev){
		$.address.value('?field='+$(this).attr('name'));
	});
	$("#fields > dl > dt a").focusin(function(){
		var f = this.hash.replace("#", '');
		$.address.value('?field='+f);
	}).click(function(ev){
		ev.preventDefault();
	});
	
	$("#secondaryfields").on("click", "li", function(){
		var field = $(this).data("field");
		$.address.value('?field='+field);
		$(this).fadeOut(function(){ $(this).remove(); if(!$("#secondaryfields li").length) $("#secondaryfields").fadeOut(); });
	});
	
	// Tab off of Categories Cancel button
	$("#pgedin-cancel-categories").keydown(function(ev){
		if(ev.keyCode == '9'){
			$('html, body').animate({scrollTop: ($("#editsummary").offset().top - 250) }, 500);
			$("#editsummary textarea").focus();
			ev.preventDefault(); // Since Tab is pressed, don't jump around
		}
	});
	
	$("#fields form.pgedfield :input").live("change", function(){
		changes.mark($(this).closest("form").data("field"));
	}).live("keydown", function(ev){
		if(ev.keyCode != '9') changes.mark($(this).attr("name"));
	});
	
	$("#fields .view a").live("click", function(ev){
		ev.preventDefault();
	});
	
	// Autofill description field
	$("#desc-autofill").click(function(){
		$(this).attr("disabled", "disabled").text("Fetching data...");
		$.post(
			"edit_ajax.php",
			{ _do:"autofill desc", _editdata:$("#pged-submit").serialize() },
			function(res){
				if(res.error) alert(res.error);
				if(res.output) $("#inp-description").val(res.output);
				$("#desc-autofill").removeAttr("disabled").text("Auto-fill");
				changes.mark( $("#inp-description") );
			}, "json"
		);
	});
	
	// Publications
	if( $("#pged-publications").length ){
		
		//add publication
		$("#addpub button").click(function(ev){
			ev.preventDefault();
			if( $(".pubforms > .pubitem").length > 15 ){ alert("Maximum number of publications reached"); return; }
			pub.addNew($(this).data("distribution"));
			$("#addpub-help").show().next().hide();
		});
		
		$("#addpub-help").click(function(){ $(this).hide().next().show() });
		
		//duplicate
		$("#pged-publications").on("click", ".duplicatepub", function(ev){
			pub.duplicate($(this).closest(".pubitem"));
		})
		.on("click", ".primary", function(){
			$("#pged-publications .primary").each(function(){
				$(this).removeClass("on");
			});
			$(this).addClass("on").find("input[type='radio']").attr("checked", true);
		}).on("click", ".removepub", function(ev){
			ev.preventDefault();
			if(confirm("For real delete this publication?")){
				$(this).closest(".pubitem").fadeOut(function(){ $(this).remove() });
				changes.mark();
			}
		});
		
	}
	
	// Publish button click
	$("#pged-submitbtn").click(function(ev){
		if( $(this).hasClass("off") ) return;
		changes.publish();
	});
	
	$(".autocomplete-var").each(function(){
		$(this).autocomplete({
			minLength:1,
			autoFocus:false,
			source:function(request, response){
				$.ajax({
					url: "/bin/php/autocomplete_var.php",
					data: { 'q':request.term, 'var':this.element.data("var") },
					success: function(data){
						$(".autocomplete-var").removeClass("ui-autocomplete-loading");
						if(data.num_results == 0){
							$(".autocomplete-var").autocomplete("close");
							return false;
						}
						response($.map(data.results, function(item){
							return {
								label: item.title,
								value: item.title,
								tag: item.tag
							}
						}));
					}
				});
			},
			open: function(){ $(this).autocomplete("widget").width(285).css("max-height", "300px") },
			select:function(event, ui){
				console.log(ui);
				insToolItem($(this).attr("name"), (ui.item.tag ? ui.item.tag+"|"+ui.item.value : ui.item.value), ($(this).data("namespace") ? $(this).data("namespace") : ''), '', $(this).data("inspos"));
				$(this).val('');
				return false;
			}
		})
	}).siblings(".submittool").click(function(){
		$(this).closest("form").submit();
	});
	
	/* Images */
	
	$("#pged-img a[href='#rmImg']").click(function(ev){
		ev.preventDefault();
		if(!confirm('Remove the current image?')){ return false };
		$(this).closest("ul").siblings("img").attr("src", "").siblings("input").val("");
		changes.save("img");
	});
	
	$("#gameimgs").hover(function(){ $("#fetch-gameimgs").slideToggle(200); });
	
	var imgFname;
	$("#fetch-gameimgs").click(function(){
		$("#gameimgs").addClass("loading");
		$.getJSON("/pages/edit_ajax.php", { _do:"fetch img", _imgfield:"gameimgs", _sessid:sessid }, function(res){
			$("#gameimgs").removeClass("loading");
			if(res.errors){ handleErrors(res.errors); return; }
			if(res.img){
				$.each(res.img, function(field, val){
					//console.log(field); console.log(val);
					if($("#gameimgs :input[name='"+field+"']").val() == "") $("#gameimgs :input[name='"+field+"']").val(val.filename).siblings("img").attr("src", val.tn);
				});
				changes.save("img");
			}
		});
	});
	
	var pgtype = "";
	$("#pgtype-list").on("click", "label", function(){
		$("#pgtype-list .subsection").hide();
		$("#submitstart").removeAttr("disabled");
		if($(this).children(":input[name='pgtype']").is(':checked')) $(this).siblings('.subsection').show();
	});
	
	//sortable game credits
	if( $(".pgtype-game").length ){
		$(".pgtype-game .credits").sortable({
			placeholder: 'placeholder',
			items: '.sortable'
		});
		$(".pgtype-person .credits").disableSelection();
		
		$(".cr-v-toggle").live("click", function(){
			if( $(this).val() == "vital" ) $(this).closest("dd").addClass("vital");
			else $(this).closest("dd").removeClass("vital");
		});
	}
		  
	$("#wikipedia-title").change(function(){
		fetchWikipediaDescription();
	});
	
	$(['/bin/img/icons/sprites/Char_Duck2_FlyingUpRight.gif']).preload();
	
	$("#api-steam-fetch-appid").click(function(){
		fetchSteamAppid();
	});
	
	$("#fetch-box").click(function(){
		$("#repimg").closest("dl").addClass("loading");
		$.getJSON("/pages/edit_ajax.php", { _do:"fetch img", _imgfield:"rep_image", _sessid:sessid }, function(res){
			$("#repimg").closest("dl").removeClass("loading");
			if(res.errors){ handleErrors(res.errors); return; }
			if(res.img && res.img_tn){
				$("#repimg-filename").val("img:"+res.img);
				$("#repimg").attr("src", res.img_tn);
				changes.save('img');
			}
		});
	});
	
	$(".datalist").
		sortable().
		on("click", ".rm", function(){
			var $el = $(this).closest("li");
			$el.fadeOut(500, function(){ $el.remove() });
			//changes.curr_field = $el.closest("dl").attr("name");
			//changes.mark();
		});
	
	$("#completion").hoverIntent({over:toggleComplNeed, out:toggleComplNeed});
	
	$(".changeinputtype").click(function(){
		var field = $(this).data("field");
		$("#"+field+"-inputtype").val($(this).val());
		$("#pged-"+field+" .inputtype").addClass("hidden").hide();
		$("#pged-"+field+" .inputtype."+$(this).val()).removeClass("hidden").show();
	});
	
});

function toggleComplNeed(){ $("#completion-needed:not(:empty)").fadeToggle() }

function changePubImg(key, val, src){
	// key: pub_i, val: image file name, src: image src
	console.log('init changePubImg: '+key+', '+val+', '+src+';');
	var el = $("#"+key+"-filename");
	if(!$(el).length) return;
	if($(el).val() && val == '') $.jGrowl('Image removed. <a onclick="changePubImg(\''+key+'\', \''+$(el).val()+'\', \''+$("#"+key).attr("src")+'\')">Undo</a>', { life: 6000 });
	$(el).val(val);
	$("#"+key).attr("src", src);
	changePubImgEval();
}
function changePubImgEval(field, imgvars){
	var boxSrc;
	console.log("changePubImgEval");
	$("#pgedin-publications .pubimgfilename").each(function(){
		if($(this).val() || $(this).siblings(".imgupl").children("img").attr("src")){
			boxSrc = $(this).siblings(".imgupl").children("img").attr("src");
			$(this).siblings(".imgupl").attr("href", "/image/"+$(this).val()).children("img").attr("src", boxSrc);
			$(this).siblings(".imgupl").css({display:"block"}).removeClass("off").siblings(".blank").hide();
		} else {
			$(this).siblings(".imgupl").hide().addClass("off").siblings(".blank").show();
		}
	});
	changes.mark();
	$("#pged-upl").fadeOut();
}
function pubImgUplProgress(key, progress){
	console.log("upl progress: "+progress);
}

function selectGameImg(){
	changes.save("img");
}




var changes = {
	num:0,
	curr_field:'',
	activate:function(field){
		
		// Activate a field changes.activate()
		// Only upon address change
		
		if(!field) return;
		console.log("activate ["+field+"]");
		changes.curr_field = field;
		
		if($("#pged-"+field).hasClass("act")) return;
		
		$("#pged-"+field).addClass("act").removeClass("noact hide").children(".view").hide().siblings("dd:not(.hidden)").show();
		$("#pged-"+field+" .focusonme").attr("tabindex", -1).focus();
		
		changes.scrollfocus();
		
		if(field == "apis" && $("#wikipedia-title-description").html() == ""){
			fetchWikipediaDescription();
		}
	
	},
	scrollfocus:function(field){
		$('html, body').animate({scrollTop: ($("#pged-"+(field ? field : changes.curr_field)).offset().top - 30) }, 500);
	},
	deactivate:function(field){
		
		// deactivate the current field
		
		if(!field) field = changes.curr_field;
		if(!field) return;
		console.log("deactivate ["+field+"]");
		$("#pged-"+field).removeClass("act").addClass("noact").children(".view").show().siblings("dd").hide();
		
		if( $("#pged-"+field).hasClass("changednotsaved") ){
			changes.save(field);
		}
		console.log("changes.curr_field "+changes.curr_field);
		if(changes.curr_field == field) $.address.value('?');
		changes.curr_field = '';
		changes.scrollfocus(field);
		
	},
	mark:function(inpfield){
		
		// changes.mark -- mark the current field as changed
		// when to do this: input.change(), manually (ie tool insToolItem(), upload img)
		
		if(inpfield) changes.curr_field = inpfield;
		else if(!changes.curr_field) return;
		
		console.log("changes.mark ["+changes.curr_field+"]");
		
		$("#act-savenow").prop("disabled", "false").text("Save Draft");
		
		if( $("#pged-"+changes.curr_field).hasClass("changednotsaved") ) return;
		$("#pged-"+changes.curr_field).addClass("changed changednotsaved");
		if (inpfield) $(inpfield).addClass("changed");
		confirm_exit = 1;
		
		$.cookie('unsavedSess', '1', {path:'/'}); // record for Resetti badge
		
	},
	cancel:function(field){
		//close this field and (revert to old data [?])
		
		if(!field) return;
		console.log("changes.cancel ["+field+"] request received");
		
		$("#pged-"+field).removeClass("changednotsaved");
		changes.deactivate(field);
	
	},
	save:function(field){
		// send field values via ajax for saving
		// return true if saved
		
		if(!field) return;
		
		console.log("changes.save "+field);
		
		$("#draftsaved").removeClass("saved").addClass("loading");
		$("#pged-submitbtn").addClass("off");
		
		$.post(
			"edit_process.php",
			{ _editdata:$("#pged-submit").serialize(),
				_field:field,
				_input:$("form#pgedin-"+field).serialize()
			}, function(ret){
				
				var res = {};
				if(ret.substr(0,1) == '{'){ res = jQuery.parseJSON(ret) }
				else res.error = ret;
					
				if(res.error){ $("#alert").fadeIn().find("dl").html('<dt>Error</dt><dd>' + res.error.replace(/\n/g, '<br/>') + '</dd>'); }
				$("#pged-"+field).removeClass("loading").find(".view").html(res.view);
				$("#draftsaved").removeClass("loading");
				$("#pged-submitbtn").removeClass("off");
				if(res.pagecompletion){
					if($("#completion-pc").text() != res.pagecompletion+"%"){
						$("#completion-pc").fadeOut(function(){ $("#completion-pc").text(res.pagecompletion+"%").fadeIn() });
						if(res.pagecompletion == "100") $("#completion-victory").animate({bottom:'-51px'}).delay(1300).animate({bottom:'-140px'});
					}
					$("#completion-scale span").animate({width:res.pagecompletion+"%"}, "slow");
				}
				$("#completion-needed").html(res.pagecompletion_needed);
				if(res.saved){
					$("#act-savenow").attr("disabled", "true").text("Draft Saved");
					$("#draftsaved").addClass("saved").text("Draft saved a few seconds ago");
					changes.startTimer();
					$("#pged-"+field).removeClass("changednotsaved").addClass("changedsaved").find("form.pgedfield :input").removeClass("changed");
					if(!res.error){
						if(res.goto){
							console.log("go!");
							confirm_exit = 0;
							window.location = res.goto;
						}
						$("#pged-submitbtn").removeClass("off");
					}
					$("#permalink").show();
					return true;
				} else {
					$("#draftsaved").addClass("saved").text("Error saving draft");
					return false;
				}
			}
		);
		
	},
	saveAll:function(publish){
		
		// Save all changed, unsaved fields
		
		var num_ch   = $("#fields dl.changednotsaved").length;
		var num_succ = 0;
		
		if(num_ch){
			
			if(!confirm("All open unsaved fields will be saved. Continue?")) return;
			
			$("#draftsaved").removeClass("saved").addClass("loading");
			$("#pged-submitbtn").addClass("off");
			
			var ajaxError = false;
			
			$("#fields dl.changednotsaved").each(function(){
				var f = $(this).attr("name");
				$.post("edit_process.php", 
					{ _editdata:$("#pged-submit").serialize(), _field:f, _input:$("#pged-"+f+" form.pgedfield").serialize() },
					function(res){
						$("#pged-submitbtn").removeClass("off");
						$("#pged-"+f).removeClass("loading").find(".view").html(res.view);
						if(res.error){
							ajaxError = true;
							$("#alert").fadeIn().find("dl").html('<dt>Error</dt><dd>' + res.error.replace(/\n/g, '<br/>') + '</dd>');
						}
						if(res.saved){
							
							num_succ++;
							if(num_succ == num_ch && publish) changes.save("publish");
							
							$("#draftsaved").addClass("saved").text("Draft saved a few seconds ago");
							changes.startTimer();
							$("#pged-"+f).removeClass("changednotsaved").addClass("changedsaved").find("form.pgedfield :input").removeClass("changed");
							$("#permalink").show();
							
						} else {
							$("#draftsaved").addClass("saved").text("Error saving " + f);
						}
					}, "json"
				);
			});
			
		} else {
			
			console.log("No changes detected");
			if(publish) changes.save("publish");
			
		}
		
	},
	publish:function(){
		
		//submit changes for publication
		
		// promt for Edit Summary if blank
		if( $(":input[name='edit_summary']").val() == "" ) {
			if(!confirm("Submit these changes without leaving an edit summary?")) return false;
		}
		
		$.cookie('unsavedSess', null, {path:'/'}); // saved session, no Resetti
		changes.saveAll("publish");
		
	},
	timer:'',
	timerElapsed:0,
	startTimer:function(){
		clearInterval(changes.timer);
		changes.timerElapsed = 0;
		changes.timer = setInterval(changes.timerElapse, 60000);
	},
	timerElapse:function(){
		changes.timerElapsed++;
		$("#draftsaved").text("Draft saved " + changes.timerElapsed + " minute" + (changes.timerElapsed != 1 ? "s" : "") + " ago");
	}
}

/* PUBLICATIONS */
var pub = {
	uplmessagepop:function(){
		$("#boxartstandardsmsg").fadeIn();
	},
	addNew:function(val_distribution){
		pub["newId"] = $(".pubforms").children().length + 1;
		$("#addpub").before($("#addPubForm").html().replace(/%s/g, pub["newId"]));
		$("#pub-"+pub["newId"]).animate({opacity:0}, 500, function(){ $("#pub-"+pub["newId"]).animate({opacity:1}, 500) });
		if(val_distribution == "digital") $("#pub-"+pub["newId"]).find(".input-distribution").attr("checked", true);
		bindFields($("#pub-"+pub["newId"]));
		tooltip();
		console.log("New publication: id:"+pub["newId"]+", distribution:"+pub["val_distribution"]);
	},
	duplicate:function(el){
		var new_pub_i = $(".pubforms").children().length + 1;
		var pubf = $("#addPubForm").html().replace(/%s/g, new_pub_i);
		$(el).after(pubf);
		var parArr = $(el).find(":input").serializeArray(),
				parId = (+$(el).data("pubid")),
				pubs = {'North America':'na', 'Japan':'jp', 'Europe':'eu', 'Australia':'au'};
		jQuery.each(parArr, function(i, field){
			//iterate through input fields 
			field.varName = field.name.replace('publications['+parId+'][','').replace(']',''); //get the field name
			if(field.varName == "img_name" || field.varName == "publications_primary") return;
			else if(field.varName == "img_name_title_screen" && field.value) changePubImg("img-titlescreen-"+new_pub_i, field.value, "/image.php?img_name="+field.value+"&showimage");
			else if(field.varName == "img_name_logo" && field.value) changePubImg("img-logo-"+new_pub_i, field.value, "/image.php?img_name="+field.value+"&showimage");
			else if(field.varName == "region") $("#pub-"+new_pub_i+" .pubregion .fauxselect-options [data-value='"+field.value+"']").trigger("click");
			pub.iField = $(":input[name='publications["+new_pub_i+"]["+field.varName+"]']");
			if(field.varName == "release_tentative" || field.varName == "distribution") $(pub.iField).attr("checked", true);
			else $(pub.iField).val(parArr[i].value);
			console.log("publications["+new_pub_i+"]["+field.varName+"] => "+parArr[i].value);
		});
		$("#pub-"+new_pub_i).animate({opacity:0}, 1000, function(){ $("#pub-"+new_pub_i).animate({opacity:1}, 1000) });
		bindFields($("#pub-"+new_pub_i));
		tooltip();
	},
	tooltip:function(i, sw){return;
		var el = $("#pub-"+i+" .pubtooltip");
		if(sw == "on") $(el).css({display:'block', width:'80px'}).animate({width:'100px', left:'-100px'}, 200);
		else $(el).animate({width:'80px', left:'0'}, 200);
	},
	initUpload:function(i){
		if($("#boxartstandardsmsg").hasClass("prompt")) pub.uplmessagepop();
		upl.init('img-box-'+i, 'boxart');
	}
}




/* Upload Images */
var upl = {
	init:function(el, imgtype){
		el = el.replace('img-box-img-box', 'img-box'); //Chrome bug
		var qs = $("#uplqstr").val().replace('_RETELID_', el).replace('_IMGTYPE_', imgtype);
		$("#upliframe").attr("src", qs);
		upl.close();
		$("#pged-upl").addClass("loading").fadeIn().animate({opacity:1}, 1000, function(){ $("#pged-upl").removeClass("loading") });
		if($("#"+el+"-msg").length) $("#"+el+"-msg").fadeIn();
	},
	changeImg:function(_elid, _fname, _src){
	
		// uploaded img in an iframe and received some info to change the img src & field value
		
		changes.mark();
		
		if($("#pgtype").val() == "person") _src = _src.replace("/md_", "/profile_");
		
		$("#"+_elid+"-filename").val(_fname).addClass("changed");
		$("#"+_elid).attr("src", _src);
		
		upl.close();
		
		if(_elid == "repimg" || _elid == "headimg" || _elid == "bgimg"){
			$("."+_elid+"-toggle-on").show();
			$("."+_elid+"-toggle-off").hide();
			changes.save("img");
		}
	},
	close:function(){
		$('#pged-upl, .pged-img-msg').fadeOut();
	},
	saveSettings:function(){
		changes.mark();
		changes.save("img");
	}
}

function handleFileSelect(evt, vars){
		
  var file = evt.dataTransfer.files[0]; // FileList object.
  if(!file.name) return;
  
  console.log("drop "+file.name);
  console.log(vars);
	
  var inpFname,
  		imgPreview = '';
	inpFname = file.name;
  inpFname = inpFname.replace(/_/g, ' ').substr(0, inpFname.lastIndexOf('.'));
  $("#uplimg-editconsole").remove();
  $("body").append('<div id="uplimg-editconsole"><div class="container"><div class="img loading">'+imgPreview+'<div class="loading"></div><div class="tape"></div><div class="pgfold"></div></div><form onsubmit="img.saveUplImgInpData(); return false;" style="margin-left:200px;" id="uplimg-edit-form"><ul style="margin:0; padding:0; list-style:none;"><li><input type="text" name="img_title" value="'+inpFname+'" placeholder="Image name" style="width:100%"/></li><li><textarea name="img_description" placeholder="Description" style="width:100%"></textarea></li><li><select name="img_category_id"><option value="'+vars["img_category_id"]+'">Loading image categories...</option></select></li></ul><button type="submit" disabled="disabled" style="font-weight:bold">Save & Insert</button> <button type="button" onclick="$(\'#uplimg-editconsole\').fadeOut(500).animate({opacity:0},500,function(){$(\'#uplimg-editconsole\').remove()}); window.xhr.abort();">Cancel</button></form><br style="clear:left"/></div></div>');
  
  if(window.FileReader) {
	  var reader = new FileReader();
	  reader.onload = (function(theFile){
	    return function(e){
	      // Render thumbnail
	      changePubImg(vars.parent_key, '', e.target.result);
	      $("#uplimg-editconsole .img").prepend('<img src="'+e.target.result.toString()+'" width="140" alt="your image"/>');
	  	}
	  })(file);
	  reader.readAsDataURL(file);
  } else {
  	//Safari
  	$("#uplimg-editconsole .img").prepend('<img src="" width="140" alt="your image"/>');
  }
  
  var formdata = new FormData();
	formdata.append("upl", file);
	formdata.append("action", "submimg");
	formdata.append("actionhandle", "json");
	formdata.append("img_category_id", vars["img_category_id"]);
	formdata.append("handler", vars["handler"]);
	formdata.append("img_tag[]", pgtitle);
	
	var xhr = new XMLHttpRequest();
	xhr.open("POST", "/uploadhandle.php", true);
	xhr.onload = function(){
		var res = JSON.parse(xhr.responseText);
		if(res.error){
			alert(res.error);
			if(!res.src_box) changePubImg(vars.parent_key, '', '');
		}
		if(res.src_box) changePubImg(vars.parent_key, res.img_name, res.src_box);
		if(res.img_name){
			$("#uplimg-editconsole .img").removeClass("loading").find("img").attr("src", res.src_box);
			$("#uplimg-editconsole form").append('<input type="hidden" name="img_name" value="'+res.img_name+'"/>');
			$("#uplimg-editconsole form button").attr("disabled", false);
			$.post(
				"/bin/php/imginsert.php",
				{ action:'load_img_data', img_name:res.img_name, load_imgcategoryid_options:1 },
				function(res2){
					if(res2.error) alert(res2.error);
					if(res2.tags) $("#uplimg-editconsole form ul").append('<li>'+res2.tags+'</li>');
					if(res2.imgcategoryid_options) $("#uplimg-editconsole form select[name='img_category_id']").html(res2.imgcategoryid_options);
				}, "json"
			)
		}
  }
	xhr.send(formdata);
}
function updateProgress(e){
	//if(e.lengthComputable) console.log((e.loaded / e.total));
}
function handleDragOver(evt) {
  evt.stopPropagation();
  evt.preventDefault();
  evt.dataTransfer.dropEffect = 'copy'; // Explicitly show this is a copy.
}

function addSuggCreds(sel){
	
	if(sel == "all"){
		$(":input[name='suggcred']").each(function(){ $(this).prop("checked", true); });
	}
	
	$(":input[name='suggcred']:checked").each(function(){
		insToolItem("credits_list", $(this).siblings("textarea").val(), '', '', "top");
		$(this).parent().html('Added').animate({opacity:1}, 1000, function(){ $(this).remove() });
	});
	
	if(!$(":input[name='suggcred']").length) $("#credlistsugg").hide();
	
}
	
	
function insToolItem(field, tag, ns, unique, insPos){
	console.log("ins "+ns+tag+" : "+field);
	if(tag == '') return false;
	tag = tag.replace('"', '&quot;');
	if(unique && $("#pgedin-"+field+" .datalist li[data-tag='[["+ns+tag+"]]']").length) return;
	var newCont = '<li data-tag="[['+ns+tag+']]"><a class="faux">'+tag+'</a>'+(field=="categories" ? '<input type="checkbox" name="'+field+'_parent[]" value="'+tag.replace(/\"/g, '&quot;')+'" title="Immediate parent" class="tooltip"/>' : '')+'<a class="rm"></a><textarea name="'+field+'[]">[['+ns+tag+']]</textarea></li>';
	if(insPos == "top") $("#pgedin-"+field+" .datalist").prepend(newCont);
	else $("#pgedin-"+field+" .datalist").append(newCont);
	//tag = $("#inp-"+field).val() + ($("#inp-"+field).val().length ? "\n" : '') + tag;
	//$("#inp-"+field).val(tag).addClass("changed");
	$("#pged-"+field+" .tool input[type='text']").val('').focus(); //reset the search field
	changes.mark();
	$.getJSON("/pages/edit_ajax.php", {"_do":"fetch link", "_text":"[["+tag+"]]"}, function(res){
		if(!res.formatted) return;
		var $el = $("#pgedin-"+field+" .datalist li[data-tag='[["+ns+tag+"]]'] a.faux");
		$el.before(res.formatted);
		$el.remove();
		if(field=="categories") subcatCh();
	});
	return false; //prevent form from submitting
}

function rmToolItem(El) {
	
	if(!confirm("Remove this row?")) return;
	
	if( !$(El).siblings(".tool-item").length ) { $(El).siblings(".null").show(); }
	$(El).remove();
	
}

function acPlatforms(){ return; }

function bindFields($el){
	$el.
		on("click", ".fauxselect", fauxselect).
		on("keyup", ".fauxselect-autocomplete", fauxautocomplete);
}

function fetchWikipediaDescription(){
	var WpTitle = $("#wikipedia-title").val(), WpTitleDesc, WpTitleIsgame;
	pgtype = $("#pgtype").val();
	if(WpTitle == ''){
		$("#wikipedia-title-description").html('');
		return;
	}
	$("#wikipedia-title-description").addClass("loading").html('<img src="/bin/img/icons/sprites/Char_Duck2_FlyingUpRight.gif"> <i style="color:#666">Fetching article</i>');
	$.getJSON(
		"http://en.wikipedia.org/w/api.php?format=json&action=query&export&redirects&titles="+encodeURIComponent(WpTitle)+"&prop=extracts&exintro&exchars=500&callback=?",
		function(res){
			console.log(res);
			$("#wikipedia-title-description").removeClass("loading");
			WpTitleDesc = res.query.pages[Object.keys(res.query.pages)].extract;
			WpTitleIsgame = res.query.export['*'].indexOf("Infobox video game") >= 0 ? true : (res.query.export['*'].indexOf("Infobox VG") >= 0 ? true : false);
			console.log("pgtype: "+pgtype+"; WpTitleIsgame: "+WpTitleIsgame);
			if(!WpTitleDesc){
				$("#wikipedia-title-description").html('');
				$("#wikipedia-title").val('');
				$.jGrowl("Error: the Wikipedia page '"+WpTitle+"' couldn't be found.");
			} else {
				$("#wikipedia-title-description").html('<i>' + WpTitleDesc + '</i> <a href="http://en.wikipedia.org/wiki/'+encodeURIComponent(WpTitle)+'" target="_blank" class="arrow-link">Full article</a><div style="height:10px"></div>' + (pgtype != "game" || (WpTitleIsgame && pgtype == "game") ? 'Use the Wikipedia data from the above article to fill out some of this page: &nbsp;&nbsp; <button type="button" onclick="fetchWikipediaData()">Fetch Wikipedia Data</button>' : '<b>Note:</b> No game data was found in the given article. Search for a better article: <a href="http://en.wikipedia.org/w/index.php?title=Special%3ASearch&profile=default&search='+encodeURIComponent(WpTitle)+'&fulltext=Search" target="_blank" class="arrow-link">Search Wikipedia for <i>'+WpTitle+'</i></a>'));
				if(res.query.redirects && res.query.redirects[0].to) $.jGrowl(WpTitle+" redirects to '"+res.query.redirects[0].to+"'; This new title was plugged in to the input field.");
				if(res.query.pages[Object.keys(res.query.pages)].title) $("#wikipedia-title").val(res.query.pages[Object.keys(res.query.pages)].title); //Put fetched title in the field in case of redirect, etc.
			}
		}
	);
}

function fetchWikipediaData(){
	if(!confirm("Continue with data fetch? This will replace all current input with Wikipedia data.")) return;
	if(loading.loading) return;
	loading.on();
	$.getJSON(
		"/pages/edit_ajax.php", {"_do":"fetch wikipedia data", "_title":$("#wikipedia-title").val()}, 
		function(res){
			loading.off();
			if(res.errors){ for(var i = 0; i < res.errors.length; i++) $.jGrowl(res.errors[i]); $el.removeClass("loading"); return; }
			if(res.field_textarea){
				$.each(res.field_textarea, function(key, val){
					$("#inp-"+key).val(val);
					changes.save(key);
				});
			}
			if(res.field_datalist){
				$.each(res.field_datalist, function(key, vals){
					$.each(vals, function(index, val){
						insToolItem(key, val, "Category:", true);
						changes.save(key);
					});
				});
			}
			if(res.intro && $("#inp-content").val() == ''){
				$("#inp-content").val(res.intro);
				triggerInputChangeEnvent($("#inp-content"));
				changes.save("content");
			} else console.log("Skip insert intro");
		}
	);
}

function fetchSteamAppid(){
	$("#api-steam-fetch-appid").attr("disabled", true).text("Fetching...");
	$.getJSON(
		"/pages/edit_ajax.php", {"_do":"fetch steam appid", "_title":$("#pgtitle").val()}, 
		function(res){
			if(res.appid){
				$("#api-steam-fetch-appid").siblings(":input[name='steam_appid']").val(res.appid).after('<span style="font-size:1.5em; line-height:18px; color:#3EC144;"> &nbsp;&#10003;</span>');
			}
			else $.jGrowl("Fetch App ID FAIL");
			$("#api-steam-fetch-appid").hide();
		}
	);
}

function promtTrailer(){
	var trailerUrl = prompt("Input a URL from Youtube:");
	if(trailerUrl){
		$("#trailerurl-inp").val(trailerUrl);
		$("#trailerurl-link").attr("href", trailerUrl);
		fetchYoutubeVideo("trailerurl", "removeOnErr");
		changes.save("img");
	}
}

function fetchYoutubeVideo(field, command){
	$("#"+field+"-inp").closest("dl").addClass("loading");
	var url = $("#"+field+"-inp").val();
	if(url){
		$.getJSON(
			"/pages/edit_ajax.php", {"_do":"fetch video", "_url":url}, 
			function(res){
				$("#"+field+"-inp").closest("dl").removeClass("loading");
				if(res.errors){
					if(command == "removeOnErr") $("#"+field+"-inp").val('');
					changes.save("img");
					handleErrors(res.errors);
					return;
				}
				if(res.video.thumbnail_url) $("#"+field+"-inp").siblings("img").attr("src", res.video.thumbnail_url);
			}
		);
	} else {
		alert("No URL found");
	}
}

function subcatCh(){
	var sc = $("#inp-subcategory select").val();console.log("subcatCh "+sc);
	$("#pgedin-categories .datalist li :input[type='checkbox']").prop("disabled", true);
	$("#pgedin-categories .datalist li a[data-subcategory='"+sc+"']").siblings(":input[type=checkbox]").prop("disabled", false);
}