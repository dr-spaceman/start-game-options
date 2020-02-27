
var tagsubj;

$(document).ready(function(){
	
	initTagAutocomplete();
	
	tagsubj = $("#tagsubj").val();
	
	$("#suggested-tags .sugg a").click(function(e){
		
		e.preventDefault();
		var tag = $(this).html();
		newTag.submit(tag);
		$(this).parent().fadeOut(500, function(){
			$(this).remove();
			if(! $("#suggested-tags .sugg").html() ) $("#suggested-tags").hide();
		});
		
	}).each(function(){
		$(this).addClass("tooltip").attr("title", "click me to add to tag list!");
	});
	
	//remove a tag
	$(".tagitem .rm").live("click", function(){
		
		if(!confirm("Remove this tag?")) return;
		
		$(this).closest(".tagitem").fadeOut(600);
		var tagid = $(this).attr("id");
		
		$.post(
			"/bin/php/class.tags.php",
			{ _action: "rm_tag",
				_tag: tagid
			}, function(res) {
				if(res) {
					alert("Error: "+res);
					$(this).closest(".tagitem").show();
				} $(this).closest(".tagitem").animate({opacity:1}, 300, function(){ $(this).remove() });
			}
		);
		
	});
	
	$("#tags").live("click", ".suggestedtag", function(){
	});
	
});

function initSuggTag(i){
	$("#t"+i+"-tagspace").fadeIn();
	$(".embvideo").css("visibility", "hidden");
};

function submitNewTagForm(el){
	var tag = $(el).find(":input[name='inptag']").val();
	var subj = $(el).find(":input[name='tagsubj']").val();
	var taggroupid = $(el).find(":input[name='taggroupid']").val();
	newTag.submit(tag, subj, taggroupid);
	return false;
}

window.newTag = {
	init: function($el){
		
		console.log("newTag.init():"); console.log($el);
		
		if($el.hasClass("loading")) return;
		$el.addClass("loading");
		newTag["tag"] = $el.next().text();
		if(newTag["tag"] == ""){ $el.removeClass("loading"); console.log("No tag word found"); return; }
		newTag["taggroupid"] = $el.data("taggroupid");
		if($("#t"+newTag["taggroupid"]+"-newtagshere").length){
			newTag["rmEl"] = $el.closest(".suggtagitem");
		} else {
			newTag["rmEl"] = "";
			newTag["taggroupid"]='';
		}
		newTag.submit(newTag["tag"], $el.data("subject"), newTag["taggroupid"], newTag["rmEl"]);
		
	},
	submit: function(tag, subj, taggroupid, rmElOnAdd){
		
		console.log("newTag.submit(); TagGroupId:"+taggroupid);
		
		if(!subj) { $.jGrowl("Error: no subject given"); return; }
		if(!tag) { $.jGrowl("Error: no tag given"); return; }
		
		var $tagspace = taggroupid ? $("#t"+taggroupid+"-newtagshere") : "";
		
		if(taggroupid)
			$("#t"+taggroupid+"-inptag").val("").focus().autocomplete("close");
		
		confirm_exit = false;
		
		$.post(
			"/bin/php/class.tags.php",
			{ _action: "add_tag",
				_subject: subj,
				_tag: tag
			}, function(res) {
				if(res.error){ $.jGrowl("Error: "+res.error); }
				if(res.newtag){
					res.newtag = '<li id="tag-'+res.tagid+'" class="tagitem rmable" style="opacity:0;" onmouseover="$(this).addClass(\'hov\');" onmouseout="$(this).removeClass(\'hov\');"><span class="tag-wrap">'+res.newtag+'<a title="Remove this tag" id="'+res.tagrmid+'" class="rm">x</a></span></li>';
					if(rmElOnAdd){
						$(rmElOnAdd).before(res.newtag);
						$(rmElOnAdd).remove();
					} else if(taggroupid) {
						if($tagspace.length == 0)
							alert("Error locating tagspace; Consult console log for error information.");
						else 
							$tagspace.children('.sugg').before(res.newtag).siblings(".notags").remove();
					}
					$("#tag-"+res.tagid).animate({ opacity:1 }, 500, function(){
						$(this).animate({ opacity:.1 }, 500, function(){
							$(this).animate({ opacity:1 }, 1000);
						})
					});
					$tagspace.find(".notags").css("text-decoration", "line-through");
				}
				
			}, "json"
		);
		
	}
}

function initTagAutocomplete($field){
	
	if(!$field) $field = $(".inptag");
	
	if($field.length == 0) return;
	
	console.log("initTagAutocomplete():" + ($field.attr("id") ? " #"+$field.attr("id") : ''));console.log($field);
	
	var _subj, _spid;
	
	$field.each(function(){
		var $field_ = $(this);
		$field_.autocomplete({
			minLength:0,
			open: function(){ $(this).autocomplete("widget").width(234).css("max-height", "300px") },
			autoFocus:false,
			source:function(request, response){
				$.ajax({
					url: "/bin/php/autocomplete_tags.php",
					data: {
						q:request.term
					},
					success: function(data) {
						if(data.num_results == 0){
							$field_.autocomplete("close");
						} else {
							response( $.map( data.results, function( item ) {
								return {
									label: item.label,
									tag: item.tag,
									category: item.category
								}
							}));
						}
					}
				});
			},
			select: function(event, ui) {
				_subj = $(this).siblings("[name='tagsubj']").val();
				_spid = $(this).siblings("[name='taggroupid']").val();
				newTag.submit(ui.item.tag, _subj, _spid);
				return false;
			}
		}).data( "autocomplete" )._renderItem = function( ul, item ) {
			return $( '<li></li>' )
				.data( "item.autocomplete", item )
				.append('<a><small>'+item.category+'</small><dfn>'+(item.label ? item.label : item.tag)+'</dfn></a>')
				.appendTo( ul );
		}
	});
	
}