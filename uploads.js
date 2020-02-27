
$(document).ready(function(){
	
	$("#imgsetedit").sortable({
		handle: '.sort',
		items: '.sortme',
		update: function(event, ui){
			$("body").addClass("loading");
			//clears.setClears();
			imgsort = $(this).sortable('toArray').toString();
			$.post(
				"/uploads.php",
				{ "sort": imgsort },
				function(ret){
					if(ret != "ok") alert(ret);
					$("body").removeClass("loading");
				}
			)
		}
	}).disableSelection();
	// Make sure the input elents are still clickable-focusable
  $('#imgsetedit :input').bind('click.sortable mousedown.sortable',function(ev){
    ev.target.focus();
  });
	
	$("#imgsetedit > *").hover(
		function(){ $(this).addClass("hov").removeClass("nohov"); },
		function(){ $(this).addClass("nohov").removeClass("hov"); }
	);
	
	$("#img_session_description").focus(function(){
		$(this).select();
	}).focus();
	
	$(".ileditme").click(function(ev){
		//ev.preventDefault();
		iledit.activate(this);
	});
	iledit.activate($("#iledit-imgsessdesc")); //activate the session description onload
	
	$(".iledit").not(".nobuttons").append('<p></p><button type="submit" class="submit">Save</button> &nbsp; <button type="button" class="cancel" onclick="iledit.cancel($(this).closest(\'form\'))">Cancel</button>');
	
	$("form.iledit").submit(function(){
		iledit.submit($(this));
		return false;
	});
	
	$(".consoleact").click(function(ev){
		ev.preventDefault();
		if(!$("#imgsetedit .imgcontainer.checked").length){ alert("Please make a selection first."); return; }
		if($(this).hasClass("neveron")) return;
		var cact = $(this).attr("href").replace("#", "");
		
		if(cact == "deleteSelected"){
			rmImg(getCheckedImgs());
			return;
		}
		
		$("#consoleform").show();
		$("#"+cact).fadeIn().siblings(".console-section").hide();
		$("#"+cact+" .focusonme").focus();
		if(cact == "addTagsSelected"){
			var fields = getCheckedImgs();
			$("#imgseltaglist").html("Loading tags...").load("/uploads.php", {"load_tags": fields.toString()}, function(){});
		}
	});
	
	if($("#imgeditconsole").length){
		var $sidebar   = $("#imgeditconsole"),
        $window    = $(window),
        offset     = $sidebar.offset();
    $window.scroll(function() {
        if ($window.scrollTop() > offset.top) {
            $sidebar.stop().animate({marginTop:$window.scrollTop() - offset.top}, "fast").addClass("scroll");
        } else {
            $sidebar.stop().animate({marginTop:0}, "fast").removeClass("scroll");
        }
    });
  }
  
  $("#imgeditconsole .viewmode").click(function(ev){
  	ev.preventDefault();
  	if($(this).hasClass("on")) return;
  	$(this).addClass("on").siblings().removeClass("on");
  	$("#imgsetedit").removeClass("tn").removeClass("sm").addClass($(this).children("a").attr("href").replace("#", ""));
  });
  
  $("#gen-img-code textarea").live("click", function(){
  	$(this).select();
  });
  
  $("#checkmass").click(function(){
  	chb = $(this).children("input[type='checkbox']");
  	if(chb.is(":checked")) $('#imgsetedit .imgcontainer').addClass('checked').find('input:checkbox').attr('checked',true);
  	else $('#imgsetedit .imgcontainer').removeClass('checked').find('input:checkbox').attr('checked',false);
  	checkedimgs.recount();
  })
  
  $(".imgselch").click(function(){
  	checkedimgs.recount();
  });
  
  var checkedimgs = { recount:function(){
  	numCh = $(".imgselch:checked").length;
  	if(numCh < 1) numCh = "None";
  	else if(numCh == $(".imgselch").length) numCh = "<b>All</b>";
  	else numCh = "<b>"+numCh+"</b>";
  	$("#numimgssel").html(numCh);
  }}
  
  $("#inp-addtag").autocomplete({
  	minLength:0,
		open: function(){ $(this).autocomplete("widget").width(444).css("max-height", "250px") },
		source:function(request, response){
			$.ajax({
				url: "/bin/php/autocomplete_tags.php",
				data: {q:request.term},
				success: function(data) {
					response( $.map( data.results, function( item ) {
							return {
								label: item.label,
								tag: item.tag,
								category: item.category
							}
						}));
				}
			});
		},
		select: function(event, ui) {
			addTagsSelected(ui.item.tag)
			$(this).val(''); return false;
		}
	}).data("autocomplete")._renderItem = function(ul, item) {
		return $( '<li></li>' )
			.data( "item.autocomplete", item )
			.append('<a><small>'+item.category+'</small><dfn>'+(item.label ? item.label : item.tag)+'</dfn></a>')
			.appendTo( ul );
	}
	
	$(".rmImg").click(function(ev){
		ev.preventDefault();
		rmImg([$(this).attr("rel")]);
	});
  
});

function genImgCode(imgFile){
	if(imgFile == "gallery"){
		sel = $("#imgsetedit .imgcontainer.checked");
		if(!sel.length) return;
		imgFile = new Array();
		$(sel).each(function(){ imgFile.push( $(this).find("dt input[name='img_name']").val() ) });
	}
	$("#alert").fadeIn().find("dl").html('<dt>Display Code</dt><dd><img src="/bin/img/loading_bar.gif" alt="loading display code..."/></dd>');
	$.post(
		"/uploads.php",
		{ "gen_img_code":imgFile },
		function(ret){
			$("#alert dl").html(ret);
		}
	);
}

var iledit = {
	activate:function(el){
		//iledit.activate()
		console.log("activate "+el);
		$(el).hide().siblings(".iledit").show().find(".ileditinp").focus();
	},
	submit:function(el){
		var ser = $(el).serialize();
		console.log("Submit iledit "+ser);
		$(el).hide().siblings(".ileditme").show().animate({opacity:.3}, 300);
		$.post(
			"/uploads.php",
			{ "iledit": ser },
			function(ret){
				if(ret.error) alert(ret.error);
				else{
					if(ret.res == "" || ret.res==null) ret.res = '<i class="null">NULL</i>';
					$(el).siblings(".ileditme").html(ret.res).animate({opacity:1}, 300);
				}
			}, "json"
		);
	},
	cancel:function(el){
		$(el).hide().siblings(".ileditme").show();
	}
}

function addTagsSelected(tag){
	
	console.log("Add tag: "+tag);
	if(tag == '') return false;
	
	//format the tag (html) and append to tag list
	$("#imgseltaglist ul").animate({opacity:.5}, 500);
	$.post(
		"/uploads.php",
		{ "mass_add_tag": tag },
		function(res){
			if(res.error) $.jGrowl(res.error);
			if(res.formatted) $("#imgseltaglist ul").animate({opacity:1}, 500).prepend(res.formatted).children(".null").hide();
		}, "json"
	);
	
	//add the tag to all selected images
	var fields = getCheckedImgs(),
	    taggroupid;
	for (var i = 0; i < fields.length; i++){
		taggroupid = $("#img-"+fields[i]+" .taglist ul:eq(0)").data("taggroupid");
		newTag.submit(tag, "images_tags:img_id:"+fields[i], taggroupid, '', true);
	}
	
	//reset the form
	$('#inp-addtag').val('').focus();
	
	//don't submit the form
	return false;
	
}

function rmTagSelected(el){
	var tag = $(el).next().val();
	var img_ids = getCheckedImgs();
	if(!confirm("Remove the tag [["+tag+"]] from all selected images?")) return false;
	console.log("rm "+tag+" from "+img_ids);
	$(el).parent().fadeOut();
	$.post(
		"/uploads.php",
		{ mass_rm_tag:tag,
			ivar:img_ids.toString() 
		}, function(res){
			if(res.error){
				$.jGrowl(res.error);
				$(el).parent().fadeIn();
			}
			if(res.tag_ids){
				for(var i = 0; i < res.tag_ids.length; i++){
					var tagElId = $(":input[name='tag-ref[images_tags:"+res.tag_ids[i]+"]']").val();
					$("#"+tagElId).addClass("removed").removeClass("rmable");
				}
			}
		}, "json"
	);
}

function submitMassClassify(){
	$("#consoleform").fadeOut();
	var img_ids = getCheckedImgs();
	$.post(
		"/uploads.php",
		{ mass_classify:$("form#massclassimgs").serialize(),
			ivar:img_ids.toString() 
		}, function(res){
			if(res.error){
				$.jGrowl(res.error);
				$("#consoleform").fadeIn();
			}
			if(res.formatted){
				$.jGrowl(img_ids.length+" images classified");
				for(var i = 0; i < img_ids.length; i++){
					$("#img-"+img_ids[i]+" .catg .ileditme").html(res.formatted);
				}
			}
		}, "json"
	);
	return false;
}

function rmImg(img_ids){
	
	if(!confirm('For real delete image(s) forever?')) return;
	$.post(
		"/uploads.php",
		{ 'rm_img_ids[]': img_ids,
			'sessid': $("#img_session_id").val() },
		function(res){
			if(res.errors){
				for(var i = 0; i < res.errors.length; i++){
					$.jGrowl(res.errors[i]);
				}
			}
			if(res.rm_img_ids){
				for(var i = 0; i < res.rm_img_ids.length; i++){
					$("#img-"+res.rm_img_ids[i]).fadeOut(function(){ $(this).remove() });
				}
			}
		}, "json"
	);
	
}

function getCheckedImgs(){
	var fields = [];
	$(".imgselch:checked").each(function(i, field){
		fields[i] = field.value;
	});
	return fields;
}