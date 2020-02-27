
$(["/bin/img/loading_ball.gif"]).preload();

var $streamContainer,
    colW = 220,
    columns = null;

//collection
var has_filters,
		offset_left;
    
function triggerMasonry(newElements){
	//resize gameshelves
	// this doesnt work!! the first item is always crooked
	/*$(".userstream .streamitem.shelf .shelf").each(function(){
		var shelf_height = $(this).find(".shelf-img").outerHeight() + 43;
		$(this).css("height", shelf_height+"px");
	});*/
	//build masonry
	if(newElements){
		$(".userstream").append('<div style="visibility:hidden">'+newElements+'</div>').imagesLoaded(function(){ triggerMasonry() });
	} else {
	  $(".userstream > div:last-child").isotope({
			itemSelector: ".streamitem",
			resizable: false,
			onLayout: function($elems){
				$(".userstream").css({"visibility":"visible"}).siblings(".loading").hide();
				$(".userstream > div").css({"visibility":"visible"});
			}
	  });
	}
}

function loadMore(section, userid, max, executeCollectionEditor){
	if(loading.loading) return;
	if(!section) section = "stream";
	$("#load-more").html("Loading more stuff...");
	loading.on();
	$.get(
		"/user_handler.php",
		{ load:true, section:section, min:$("#load-more").data("min"), max:max, usrid:userid },
		function(res){
			if(res.errors) for(var i = 0; i < res.errors.length; i++) $.jGrowl(res.errors[i]);
			if(res.formatted){
				if(section == "stream"){
					$("#load-more").animate({opacity:1}, 500, function(){ $(this).animate({bottom:"25px", opacity:"hide"}, function(){ $(this).remove() })});
					triggerMasonry(res.formatted);
				} else if(section == "collection"){
					$("#load-more").remove();
					$("#collection-shelf-end").before(res.formatted);
					shelf.bindActions($("#collection-shelf-end").parent());
				}
				if(executeCollectionEditor) collectionEdit.init();
			}
			loading.off();
		}, "json"
	);
}

function collectionNav(){
	$.address.value("/collection?"+$("#user-collection form").serialize());
}

$(document).ready(function(){
	
	// STREAM //
	
  $streamContainer = $(".userstream");
		
	$streamContainer.on("mouseenter mouseleave", ".streamitem", function(event){
		if(event.type == "mouseenter") $(this).removeClass("nohov").addClass("hov");
		else $(this).addClass("nohov").removeClass("hov");
	});
	
  if($streamContainer.length){
		
		//resize container
		/*var containerWidth=0,
		    frameWidth=940;
		if($("body").hasClass("fullwidth")){
			var itemTotalWidth = 0;
			$streamContainer.children().each(function(){
	      itemTotalWidth += $(this).outerWidth(true);console.log(itemTotalWidth+";"+$streamContainer.width());
	    });
	    var bodyColumns = Math.floor(($streamContainer.width() -10) / colW),
	        itemColumns = Math.floor(itemTotalWidth / colW),
	        currentColumns = Math.min(bodyColumns, itemColumns);
	    if (currentColumns !== columns) {
	      columns = currentColumns;
	      var containerWidth = (columns - 1) * colW,
	          frameWidth = columns * colW;
	      console.log(columns +","+ colW +"=>"+ frameWidth);
	      if(containerWidth < 660) containerWidth = 660;
	      if(frameWidth < 880) frameWidth = 880;
	      $streamContainer.width(containerWidth).parent().width(frameWidth);
	    }
	  }*/
	  
		$streamContainer.imagesLoaded(function(){
	  	triggerMasonry();
		});
		
	}
	
	var section = "stream",
	    user = {
	    	'id': $("#user-profile-header").data("uid"),
	    	'name': $("#user-profile-header").data("uname")
	    };
	
	$(window).scroll(function(){
		if($(window).scrollTop() == $(document).height() - $(window).height()){
			if($("#load-more").length) loadMore(section, user.id);
		}
	});
	
	$("#user-profile-container").on("click", "#load-more", function(){ loadMore(section, user.id) });
	
	$("a.user-profile-nav").live("click", function(ev){
		ev.preventDefault();
		var pnavto = $(this).attr("href").replace("/~"+user.name, "");
		if(pnavto == "") pnavto = "/stream";
		$.address.value(pnavto);
	});
	
	$.address.change(function(ev){
		console.log("user_handler addr ch:");
		console.log(ev);
		if(ev.pathNames[0]){
			
			if(section == "posts" && ev.pathNames[0] == "posts"){
				// let the posts handler deal with it
				return;
			}
			
			section = ev.pathNames[0];
			
			loading.on();
			$.get(
				"/user_handler.php",
				{ "load":true, "usrid":user.id, "path":ev.path, "vars":ev.queryString },
				function(res){
					if(res.errors) for(var i = 0; i < res.errors.length; i++) $.jGrowl(res.errors[i]);
					if(res.formatted){
						$("#user-profile").removeClass().addClass(section);
						$("#user-profile-header nav li[data-section='"+section+"']").addClass("on").siblings().removeClass("on");
						$("#user-profile-container").html(res.formatted);
						if(section == "stream"){
							$(".userstream").imagesLoaded(function(){ triggerMasonry() });
						} else if(section == "collection"){
							
							//bind collection actions
							shelf.bindActions($(".shelf")); // Label toggles, etc
							$("#user-collection").on("click", ".fauxselect", fauxselect); //fauxselects
							$("#user-collection nav form :input.submitonchange").on("change", function(){
								if(!$("#user-collection .filter-link").hasClass("open")) collectionNav();
							});
							offset_left = ($(window).width() - $('#collection-shelf-items').width()) / 2; //measure left offset for editor position
							
							//add game autocomplete
							if($("#collection-add-input").length != 0){
								var results_row, results_row_pf;
								$("#collection-add-input").autocomplete({
									minLength:3,
									appendTo:"#collection-add-results",
									source:function(request, response){
										$.ajax({
											url: "/bin/php/autocomplete.php",
											data: { q:request.term, filter_type:"games", return_vars:"data platform_acronym"  },
											success: function(data){
												$("#collection-add-input").removeClass("ui-autocomplete-loading");
												if(data.num_results == 0){ $("#collection-add-input").autocomplete("close"); return false; }
												response($.map(data.results, function(item){
													return { label:item.title, value:(item.tag ? item.tag : item.title), category:item.category, data:item.data }
												}));
											}
										});
									},
									select:function(event, ui){
										collectionAdd.init(ui.item.value);
										$(this).val(''); return false;
									}
								}).data("autocomplete")._renderItem = function(ul, item){
									results_row = item.category == "music" ? '<a><small>'+(item.data.release_date ? item.data.release_date.substr(0, 4) : '')+'</small><dfn><span class="albumlink">'+item.label+'</span></dfn></a>' : '<a><small>'+(item.data.platforms_acronym_formatted ? item.data.platforms_acronym_formatted.replace(/, /g, ' &middot; ') : '')+(item.data.first_release ? item.data.first_release.substr(0, 4) : '')+'</small><dfn>'+item.label+'</dfn></a>';
									return $('<li></li>')
										.data("item.autocomplete", item)
										.append(results_row)
										.appendTo(ul);
								}
							}
							
						}
						tooltip();
					}
					loading.off();
				}, "json"
			);
		}
	}).strict(false);
	
	//track Ctrl & Shift for shelf navigation
	var _keydown={};
	$(document).on('keyup keydown', function(e){
		_keydown["ctrl"] = e.ctrlKey;
		_keydown["shift"] = e.shiftKey;
	});
	
	//Collection editor
	$("#user-profile-container").on("click", "#collection-nav-edit", function(){
		$(this).toggleClass("active");
		if(!$(this).hasClass("active")){
			$("#collection-edit").animate({opacity:"hide", bottom:'0'});
			$("#collection-shelf-items").removeClass("sortable").sortable("destroy");
			$(".nav-top .console").slideUp();
		} else {
			if($("#load-more").length) loadMore(section, user.id, "*", true);
			else collectionEdit.init();
		}
	}).on("click", "#collection-nav-add", function(){
		$("#collection-add").fadeIn();
		$("#collection-add-input").focus();
		//turn off editing
		if($("#collection-nav-edit").hasClass("active")) $("#collection-nav-edit").trigger("click");
	}).on("click", "#collection-add", function(event){
		event.stopPropagation();
		if($(event.target).hasClass("bodyoverlay")) $("#collection-add").fadeOut();
	}).on("click", "#collection-shelf-items a", function(event){
		//don't follow page links on shelf edit mode
		if($("#collection-nav-edit").hasClass("active")){
			event.preventDefault();
			if(has_filters) return;
			if(_keydown["ctrl"]){
				$(this).closest(".shelf-item").insertBefore("#collection-shelf-end");
				collection.saveSortOrder();
			}
			if(_keydown["shift"]){
				$(this).closest(".shelf-item").prependTo("#collection-shelf-items");
				collection.saveSortOrder();
			}
		}
	}).on("dblclick", "#collection-shelf-items .shelf-item", function(){
		//edit shelf item on double click
		loading.on();
		var $shelf_item = $(this);
		$.get(
			"/pages/pgop.php",
			{ 'action':'edit', 'op':'collection', 'title':$shelf_item.find(".game-title").text() },
			function(res){
				loading.off();
				if(res.errors) for(var i = 0; i < res.errors.length; i++) $.jGrowl(res.errors[i]);
				if(res.success){}
				if(res.formatted){
					var shelf_item_position = $shelf_item.offset(),
							collection_form_orientation = "left",
							collection_form_css;
					console.log(shelf_item_position.left+" - "+offset_left);
					if(!offset_left){
						collection_form_css = {position:'absolute', margin:'0', top:'20%', left:'50%', marginLeft:'-315px' }
					} else if((shelf_item_position.left - offset_left) > 500){
						//if the item is toward the right, change the form orientation to box on the right
						collection_form_orientation = "right";
						collection_form_css = {position:'absolute', margin:'0', top:(shelf_item_position.top -54), left:(shelf_item_position.left - 427)}
					} else {
						//default form orientation with box on the left
						collection_form_css = {position:'absolute', margin:'0', top:(shelf_item_position.top -54), left:(shelf_item_position.left - 33)}
					}
					$("#pgop-form-collection").css(collection_form_css).attr("data-shelfposition", collection_form_orientation).fadeIn().children(".container").html(res.formatted);
				}
			}
		);
	});
	
	$(['/bin/img/collection_addgame_bg.png']).preload();
	
});

var collectionAdd = {
	init:function(title){
		if(!title) title = $("#collection-add-input").val();
		if(!title) return;
		loading.on();
		$("#collection-add").fadeOut();
		$.get(
			"/pages/pgop.php",
			{ 'action':'add', 'op':'collection', 'title':title },
			function(res){
				loading.off();
				if(res.errors) for(var i = 0; i < res.errors.length; i++) $.jGrowl(res.errors[i]);
				if(res.formatted){
					collection_form_css = {position:'fixed', top:'50%', left:'50%', 'margin':'-202px 0 0 -315px'};
					$("#pgop-form-collection").css(collection_form_css).fadeIn().children(".container").html(res.formatted);
				}
			}
		);
	}
}

var collectionEdit = {
	init:function(){
		$("#collection-edit").animate({opacity:"show", bottom:'5%'});
		//$(".nav-top .console").slideDown();
		var dropped_in_droppable,
				$draggable_sibling;
		if(!has_filters){
			$("#collection-shelf-items").addClass("sortable").sortable({
				cursorAt:{top:122, left:85},
				start: function(event, ui){
					$draggable_sibling = $(ui.item).prev();
				},
				stop:function(event, ui){
					console.log("stop");
					if(dropped_in_droppable){
						//revert to original order
						if($draggable_sibling.length == 0) $('#collection-shelf-items').prepend(ui.item); //it was the first child item
						else $draggable_sibling.after(ui.item);
						dropped_in_droppable = false;
					}
				},
				update: function(event, ui){
					collection.saveSortOrder();
				}
			}).disableSelection();
		}
		$("#user-collection .dropaction").droppable({
			hoverClass: "drophover",
			over: function(event, ui){
				$(ui.draggable).addClass("droppable");
			},
			out: function(event, ui){
				$(ui.draggable).removeClass("droppable");
			},
			drop: function(event, ui){
				$(ui.draggable).removeClass("droppable");
				dropped_in_droppable = true;
				var dropaction = $(this).data("dropaction"),
						shelf_item_id = $(ui.draggable).attr("id").replace("shelf-item-id-", ""),
						$shelf_item = $("#shelf-item-id-"+shelf_item_id);
				console.log("drop "+dropaction+" "+shelf_item_id);
				if(dropaction == "delete"){
					//delete shelf item
					if(!confirm("Permanently remove this from your collection?")) return;
					$shelf_item.hide();
					$.post("/bin/php/collection.php", { "remove":shelf_item_id }, function(res){
						if(res.errors) for(var i = 0; i < res.errors.length; i++) $.jGrowl(res.errors[i]);
						if(res.success) $shelf_item.remove();
						else $shelf_item.fadeIn();
					});
				} else if(dropaction == "edit"){
					
				}
			}
		});
		if(has_filters) $.jGrowl('Sorting is disabled when filters are present. <a href="#/collection">turn off filters</a>', {life:8000});
	}
}