
var draftTimer = 0;
var retFormRevClick = false;

$(document).ready(function(){
	
	var albumsPopulated=false;
	chHeadline = function(w){
		console.log("chHeadline "+w);
		$(".headline").hide();
		if($(".hl-"+w).length) $(".hl-"+w).show();
		else $(".hl-default").show();
		if(w == "audio" && !albumsPopulated){
			//populate album select fields
			albumsPopulated = true;
			console.log("Populate albums select");
			$(".form-audio .selectalbum").load("/posts/ajax.php", { _action:"loadalbums" });
		}
	}
	chTtype = function(ttype){
		$("#in-ttype").val(ttype);
		$("#choosettype").hide();
		$(".ttype").hide();
		$(".ttype-"+ttype).show();
		$("#NNform").removeClass().addClass("ttype-"+ttype);
		$("#ttype a[href='#"+ttype+"']").parent().addClass("on").siblings().removeClass("on");
		chHeadline(ttype);
		if(ttype == "blog" || ttype == "playlog"){
			$("#in-category-public").prop("disabled", true).prop("checked", false);
		} else {
			$("#in-category-public").prop("disabled", false);
		}
		if(ttype == "playlog" && $("#inp-subject").val() == ''){
			//fetch currently playing games
			$("#playlog").html('<div class="loading">Loading current play list...</div>');
			$.getJSON("/bin/php/ajax.collection.php", {_action:"fetch", _filter:'{"playing":"1"}', _orderby:"`play_start` desc"}, function(res){
				if(res.errors){ handleErrors(res.errors); }
				if(res.collection){
					$("#playlog").html('<ul></ul>');
					$.each(res.collection, function(i,item){
			      $('<li><a>'+item.title+'</a></li>').appendTo("#playlog ul");
			    });
				}
			});
		}
	}
	if($("#in-ttype").val()) chTtype($("#in-ttype").val());
	
	var $viewspace = { "preview": $(".viewspace.view-preview"), "edit": $(".viewspace.view-edit") }
	$.address.change(function(event){
		console.log(event);
		if($.address.parameter("type") == ""){
			$("#selconttype").show();
			$("#appendcontent").hide();
			$("#in-attachment").val('');
		} else if(ntype = $.address.parameter("type")){
			$("#selconttype").hide();
			$("#appendcontent").show().attr("class", "").addClass(ntype);
			$("#appendcontent .form-"+ntype).show().siblings(".forms").hide();
			chHeadline(ntype);
			$("#in-attachment").val(ntype);
		} else if($.address.value() == "preview"){ //preview
			console.log("preview");
			loading.on();
			$("#editswitcher-nub").animate({"left":"54px"}).removeClass("left").addClass("right");
			$viewspace["edit"].animate({opacity:".5"});
			$("#selconttype").hide();
			$.post(
				"/posts/process.php",
				{ "submit_post_action":'preview', "ajaxforminput":nnFormInput() },
				function(res){
					loading.off();
					nnHandleAjaxRes(res);
					if(res.formatted){
						$viewspace["edit"].hide();
						$viewspace["preview"].show().html(res.formatted);
						$('html, body').animate({scrollTop:210}, 500);
					} else {
						$.address.value("foo");
					}
				}
			);
		} else if($.address.value() == "edit" || $.address.parameter("field")){
			var nfield = $.address.parameter("field");
			loading.off();
			if(nfield)
				$('html, body').animate({
					scrollTop:($("#"+nfield).offset().top - 30)
				}, 500, function(){
					$("#"+nfield).animate({opacity:0}, function(){
						$("#"+nfield).animate({opacity:1})
					})
				});
			else $('html, body').animate({scrollTop:210}, 500);
			$("#editswitcher-nub").animate({"left":"33px"}).removeClass("right").addClass("left");
			$viewspace["preview"].hide();
			$viewspace["edit"].show().css({opacity:1});
			if(!$("#appendcontent").is(":visible")) $("#selconttype").show();
		}
	}).strict(false);
	
	$viewspace["preview"].on("click", "a", function(ev){
		$(this).attr("target", "_blank");
	});
	
	var ntype = $("#in-attachment").val();
	if(ntype){
		console.log("Read attach type: "+ntype);
		$.address.value('?type='+ntype);
	}
	
	$("#new-news :input").change(function() {
		confirm_exit = true;
		$.cookie('unsavedSess', '1', {path:'/'}); //record for Resetti badge
		//start draft timer (if not yet started)
		//auto save draft after 3 minutes, update the timer every minute after that
		if( !draftTimer && $("#savedraftbutton").val() ) {
			draftTimer = 1;
			setInterval("saveDraft('check')", 60000);
		}
	});
	
	//category switches
	$("input[name='in[category]']").click(function() {
		$(this).closest("dt").next("dd").show().siblings("dd").hide();
	});
	
	$(".selection ul > li > a").click(function(){
		$(this).parent().addClass("on").siblings("li").removeClass("on");
	});
	
	$(".selconttype").on("click", "a", function(ev){
		ev.preventDefault();
		var ntype = this.hash.slice(1);//$(this).attr("href").replace("#", "");
		$.address.value('?type='+ntype);
	});
	
	$("#ttype").on("click", "a", function(Ev){
		Ev.preventDefault();
		var ttype = this.hash.slice(1);
		chTtype(ttype);
	});
	
	$("#ttype-review a").click(function(Ev){
		Ev.preventDefault();
		var v = $(this).attr("href").replace("#", "");
		$("#in-rating").val(v);
		if(v == "custom") { $(this).children('input').focus(); }
	});
	
	var starrating = 1;
	var srpos = 0;
	$("#star-rating > span > span").hover(function(){
		starrating = $(this).html();
		starrating = parseInt(starrating);
		srpos = starrating * 16;
		$("#star-rating > span").css("background-position", "0 -"+srpos+"px");
	}, function(){
		//revert to current vale (Set on click)
		currating = $("#in-scaleval").val();
		currating = parseInt(currating);
		srpos = currating * 16;
		$("#star-rating > span").css("background-position", "0 -"+srpos+"px");
	}).click(function(){
		$("#in-scaleval").val(starrating);
	});
	
	$("#new-news .example-link").click(function(e) {
		e.preventDefault();
		$(this).toggleClass("arrow-toggle-on").siblings(".example").slideToggle();
	});
	
	var resizeinput = function(ev){console.log("res");console.log(ev);}
	
	//album list onchange -> populate track list
	$(".selectalbum").change(function(){
		$(this).next().show().children('select').html('<option value="">Loading track list&hellip;</option>');
		var albumid = $(this).val();
		if(!albumid) {
			$(this).next().hide().children('select').html('');
			return;
		}
		$(this).next().children('select').load("/posts/ajax.php", { _action:"loadtracks", _albumid:albumid });
	});
	
	/*$("#getVideoCode").click(function(){
		var _url = $("#inpvidurl").val();
		if(_url == "" || _url == "http://") {
			alert("Please input a valid video URL");
			return;
		}
		$(this).val("Fetching...").attr('disabled', 'disabled');
		$.post(
			"/posts/process.php",
			{ videourl:_url },
			function(res) {
				if(!res) { $("#getVideoCode").val('Failure').removeAttr("disabled"); return; }
				if( !$("input[name='in[heading]']").val() ) $("input[name='in[heading]']").val(res.title).siblings(".tt").hide();
				if( !$("textarea[name='in[text]']").val() ) $("textarea[name='in[text]']").val(res.desc);
				if( !$("textarea[name='in[video_code]']").val() ) $("textarea[name='in[video_code]']").val(res.code);
				$("#in_video_thumbnail").val(res.tn);
				$("#video_thumbnail_src").attr("src", res.tn);
				$("#getVideoCode").val("Fetched!").removeAttr("disabled");
			}, "json"
		);
	});*/
	
	$("#image-items").sortable().on("click", "a", function(){
		if($(this).hasClass("rm")){
			$(this).closest(".image-item").fadeOut(function(){
				$(this).remove();
				if($("#image-items > .image-item").length < 10) $("#image-add").show();
				nnimage.chSelectLayout();
			});
		} else if($(this).data("imagei")){
			var i = $(this).data("imagei");
			img.init({fieldId:'image-'+i+'-filename', fieldSrc:$('#image-'+i+'-img'), action:'select', 'onSelect':nnSelectImage, nav:$(this).data("nav"), uploadVars:{handler:$(this).data("handler")}});
		}
	});
	
	var nnimage = {
		template: $("#image-itemtemplate").html().replace(/%s/g, ""),
		i:$("#image-items > .image-item").length,
		num:0,
		chSelectLayout:function(){
			nnimage.num = $("#image-items > .image-item").length;
			$("#image-selectlayout > a").hide();
			$("#image-selectlayout > a.l"+nnimage.num).css("display", "block");
		}
	}
	nnimage.chSelectLayout();
	$("#image-add").click(function(){
		nnimage.num = $("#image-items > .image-item").length;
		if(nnimage.num > 9){
			$("#image-add").hide();
			return;
		}
		nnimage.i++;
		nnimage.num++;
		$("#image-items").append(nnimage.template.replace(/%I/g, nnimage.i));
		nnimage.chSelectLayout();
	});
	
	$("#image-selectlayout").on("click", "a", function(){
		$(this).addClass("on").siblings().removeClass("on").siblings("input").val($(this).data("layout"));
	});
	
	$("#playlog").on("click", "a", function(){
		$("#inp-subject").val($(this).text()).change();
		$("#playlog").hide();
	});
	
	var subj = $("#inp-subject").val();
	$("#inp-subject").autocomplete({
		minLength:1,
		autoFocus:false,
		source:function(request, response){
			$.ajax({
				url: "/bin/php/autocomplete_var.php",
				data: { 'q':request.term, 'var':'games albums' },
				success: function(data){
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
		open: function(){ $(this).autocomplete("widget").width(350).css("max-height", "300px") },
		select:function(event, ui){
			console.log(ui);
			$(this).val(ui.item.tag ? ui.item.tag : ui.item.value);
			return false;
		}
	}).change(function(){
		$("#playlog").slideUp();
		$(this).animate({opacity:1}, 200, function(){
			subj = $("#inp-subject").val();
			if(subj == ''){
				nnRmSubjectPagelabel();
				return;
			}
			$("#subject-details").html('<div class="loading">Loading details...</div>').load("/pages/label.php", {"title":subj}, function(){
				$("#subject-details .pglabel").append('<a class="rm ximg-small" onclick="nnRmSubjectPagelabel()">x</a>');
				$("#inp-subject").parent().hide();
			});
		});
	});
	if(subj) $("#inp-subject").change();
	
	$("#audiofile .rm").click(function(){
		$("#inp-audiofile").val('');
		$("#audiofile").hide().siblings("iframe").show();
	});
	
	$("#postopts h4 > a").click(function(e){
		e.preventDefault();
		$(this).toggleClass("arrow-toggle-on").parent().next().toggle();
	});
	
	$("#editswitcher-nub").mousedown(function(){
		if( $(this).hasClass("left") ) $.address.value("preview");
		else $.address.value("edit");
	});
	
	$("#NNform .buttons input").click(function(){
		confirm_exit = false;
		$.cookie('unsavedSess', null, {path:'/'});
	});
	
	$("#postshare input[type='checkbox']").click(function(){
		if($("#postshare input[type='checkbox']:checked").length) $("#status_text").show();
		else $("#status_text").hide();
	});
	
	//track certain form changes
	//change them automatically via AJAX returns, but not if they've been manually changed
	$("#NNform .trackchange").each(function(){
		if($(this).val()) $(this).addClass("changed");
	}).change(function(){
		if($(this).val()=="") $(this).removeClass("changed");
		else $(this).addClass("changed");
	});
	
});

function nnHandleAjaxRes(res){
	//handle errors and other returned data
	if(res.errors){ handleErrors(res.errors); }
	if(res.warnings){ handleErrors(res.warnings, false); }
	if(res.success) $.jGrowl(res.success, { sticky: true });
	//status text
	if(!$("#status_text").hasClass("changed") && res.status_text) $("#status_text").val(res.status_text);
	if($("#postshare input[type='checkbox']:checked").length) $("#status_text").show();
	//permalink
	if(!$("#inp-permalink").hasClass("changed") && res.permalink) $("#inp-permalink").val(res.permalink);
	//description
	if(!$("#inp-description").hasClass("changed") && res.description) $("#inp-description").val(res.description);
	//nid
	if(res.nid) $("#permalink-nid").text(res.nid);
}

function nnFormInput(){
	$("#NNform .trackchange").each(function(){
		if(!$(this).hasClass("changed")) $(this).prop("disabled", true);
	});
	var inp = $("#NNform").serialize();
	$("#NNform .trackchange").prop("disabled", false);
	return inp;
}
	
function nnSubmitForm(){
	console.log("submit");
	
	//force preview
	/*if($.address.value() != "preview"){
		$.address.value("preview");
		$.jGrowl('Preview your post before submitting -- if it looks good click "Submit" at the bottom of the page');
		return;
	}*/
	
	loading.on();
	$("#inpcontent").css({opacity:".5"});
	$("#NNform .buttons input").prop("disabled", true);
	$.post(
		"/posts/process.php",
		{ "submit_post_action":'submit', "ajaxforminput":nnFormInput() },
		function(res){
			loading.off();
			$("#inpcontent").css({opacity:1});
			$("#NNform .buttons input").prop("disabled", false);
			if(res.errors){ handleErrors(res.errors); }
			else if(res.goto) window.location = res.goto;
		}
	);
}

function nnInitOauth(provider){
	console.log("Oauth "+provider);
	loading.on();
	$.post(
		"/posts/process.php",
		{ "submit_post_action":'draft', "ajaxforminput":nnFormInput() },
		function(res){
			if(res.errors){ handleErrors(res.errors); }
			else if(res.nid){
				$.cookie('lastpage', '/posts/manage.php?edit='+res.nid, {expires:1, path:'/'});
				$.cookie('unsavedSess', null, {path:'/'});
				confirm_exit = false;
				if(provider == "facebook") window.location =  "/login_fb.php";
				else if(provider == "twitter") window.location = "/bin/php/twitter/connect.php";
				else alert("Provider name error?");
				loading.off();
			}
		}
	);
}

function nnRmSubjectPagelabel(){
	$("#inp-subject").val('').removeClass("notempty").addClass("empty").parent().show();
	$("#subject-details .pglabel").hide();
}

/*function NNuploadhimg(_src) {
	
	if(_src == "") return false;
	if(_src == "http://") return false;
	
	var x = Array();
	x = _src.split("/");
	var br = x.length - 1;
	var fname = x[br];
	var dot = fname.lastIndexOf(".");
	var ext = fname.substr(dot,fname.length).toLowerCase();
	if(ext != ".gif" && ext != ".jpg" && ext != ".png") {
		alert("Please upload only JPG, GIF, or PNG images ["+ext+"]");
		document.uplhimgform.reset();
		return false;
	}
	
	document.uplhimgform.submit();
	$("#uplhimgform :input").attr("disabled", "disabled");
	$("#uplhimgform .loading").show();
	
}*/

var minsSinceLastSave = 0;
var draftNotSaved = true;
function saveDraft(ch) {
	
	if(ch) {
		minsSinceLastSave++;
		//update the timer and check if 3 minutes have passed before saving
		if(minsSinceLastSave < 3) {
			if(!draftNotSaved) $("#draftmsg").html("Draft saved "+minsSinceLastSave+" minute"+(minsSinceLastSave != 1 ? 's' : '')+" ago");
			return;
		}
	}
	
	confirm_exit = false;
	$.cookie('unsavedSess', null, {path:'/'});
	
	//save
	draftNotSaved = false;
	minsSinceLastSave = 0;
	$("#savedraftbutton").prop("disabled", true).addClass("loading");
	$.post(
		"/posts/process.php",
		{ "submit_post_action":'draft', "ajaxforminput":nnFormInput() },
		function(res){
			nnHandleAjaxRes(res);
			$("#savedraftbutton").prop("disabled", false).removeClass("loading");
			if(!res.errors) $("#draftmsg").html("Draft saved a few seconds ago");
		}
	);
	
}

function retFormRev() {
	return retFormRevClick;
}

function nnSelectImage(ev, img){
	$("#"+ev.fieldId).siblings("a.imgupl").attr("href", "/image/"+img.img_name).siblings("strong").text(img.img_name);
}

function nnHandleImageDrop(evt, vars){
		
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
	      nnImageItem(vars.parent_key, '', e.target.result);
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
	//formdata.append("img_category_id", vars["img_category_id"]);
	formdata.append("handler", vars["handler"]);
	//formdata.append("img_tag[]", pgtitle);
	
	var xhr = new XMLHttpRequest();
	xhr.open("POST", "/uploadhandle.php", true);
	xhr.onload = function(){
		var res = JSON.parse(xhr.responseText);
		if(res.error){
			alert(res.error);
			if(!res.src_box) nnImageItem(vars.parent_key, '', '');
		}
		if(res.src_box) nnImageItem(vars.parent_key, res.img_name, res.src_box);
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
function nnImageItem(key, img_name, img_src){
	// key: pub_i, val: image file name, src: image src
	console.log('nnImageItem('+key+', '+img_name+', '+img_src+');');
	var el = $("#"+key+"-filename");
	if(!$(el).length) return;
	$(el).val(img_name);
	$("#"+key+"-img").attr("src", img_src).siblings("a.imgupl").attr("href", "/image/"+img_name).siblings("strong").html(img_name);
}