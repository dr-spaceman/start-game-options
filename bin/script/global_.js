
if (!window.console) var console = { log: function() {} };

var confirm_exit = false,
		confirm_exit_msg = "If you leave this page all your changes will be discarded.";

$(document).ready(function(){
	
	//Check if browser supports HTML5
	if(!$("header").length){
		$("body").html('<h1>Incompatible browser</h1><p>Sorry for the inconvenience, but your browser isn\'t compatible with Videogam.in. Please download a modern browser or upgrade your browser to access this site and improve your internet browsing experience.<ul><li><a href="http://www.google.com/chrome">Google Chrome</a></li><li><a href="http://www.mozilla.com/firefox/">Mozilla Firefox</a></li><li><a href="http://www.opera.com/download/">Opera</a></li><li><a href="http://www.apple.com/safari/download/">Apple Safari</a></li><li><a href="http://www.microsoft.com/download">Microsoft Internet Explorer</a></li></ul>');
	}
	
	$(window).bind("beforeunload",function(event){
		if(confirm_exit) return confirm_exit_msg;
	});
	
	$("a[href='/js.htm']").click(function(ev){ev.preventDefault()});
	
	if($("html").width() < 1100) $("body").addClass("lowres");
	
	$("#topsearchin").
		focus(function(){ $('#topsearch').addClass('foc').prevUntil('.first-child').hide() }).
		autocomplete({
			minLength:3,
			autoFocus:false,
			appendTo: "#topsearch-results",
			source:function(request, response){
				$.ajax({
					url: "/bin/php/autocomplete.php",
					data: { q:request.term, add_db_link:1 },
					success: function(data){
						$("#topsearchin").removeClass("ui-autocomplete-loading");
						if(data.num_results == 0){
							$("#topsearchin").autocomplete("close");
							return false;
						}
						response($.map(data.results, function(item){
							return {
								label: item.title,
								value: item.title,
								category: item.category,
								url: item.url
							}
						}));
					}
				});
			},
			select:function(event, ui){
				document.location = ui.item.url;
				return false;
			}
		}).data("autocomplete")._renderItem = function( ul, item ) {
			return $( '<li></li>' )
				.data( "item.autocomplete", item )
				.append('<a href="'+item.url+'"><small>'+item.category+'</small><dfn>'+item.label+'</dfn></a>')
				.appendTo( ul );
		};
	
	//$("#topnav dd ul").prev("a").attr("title", ""); //remove nav titles if there's a dropdown
	$("#topnav .hovact").hoverIntent({ over:toggleTopnavItem, timeout:200, out:toggleTopnavItem });
	
	window.tweets = {
		quotes: ["Son of a submariner!", "What a horrible night to have a curse.", "It's dangerous to go alone! Take this.", "GET THE HECK OUT OF HERE, YOU NERD!", "The cake is a lie", "A Slime draws near! Command?", "You spoony bard!", "I am the reinforcements.", "I FEEL ASLEEP!!!", "Join the Nintendo fun club today! Mac.", "Just a girl. Get out of here!"],
		init: function(){
			var tweet;
			if( $("#headbgn").attr("rel") == "1" ) tweet="Join the Nintendo fun club today! Mac.";
			else{
				tweet = Math.floor(Math.random() * tweets.quotes.length);
				tweet = tweets.quotes[tweet];
			}
			$("#tweet").text(tweet);
			$("#twitter_div").show();
		}
	}
	
	window.login = { init:function(){
		if($("#login").is(":visible")) return;
		//$("#login input[type='text']").each(function(){ if($(this).val() != '') $(this).siblings("label").hide(); }).
		$("#login, #login-overlay").fadeIn();
		//$("#login-username").focus();
	}}
	$("a[href='#login'], a[href='/login.php']").click(function(Ev) {
		Ev.preventDefault();
		login.init();
	}).each(function(){
		//if the login link has class="prompt", prompt automatically
		if( $(this).hasClass("prompt") ) login.init();
	});
	$("#login-close, #login-overlay").click(function(){
		$("#login, #login-overlay").fadeOut();
	});
	
	/*$("input.styled, textarea.styled").focus(function(){
		$(this).addClass("styled-on");
	}).blur(function() {
		$(this).removeClass("styled-on");
	});*/
	
	/*$(".resetonfocus").each(function(){
		var ival = $(this).val();
		$(this).focus(function() {
			if( $(this).val() == ival ) {
				$(this).removeClass("resetonfocus").val("");
			};
		}).blur(function(){
			if( $(this).val() == "" ) $(this).addClass("resetonfocus").val(ival);
		});
	});*/
	//clear field set as resetonfocus upon form submit
	/*$("form").submit(function(){
		$(this).children(".resetonfocus").val('');
	});*/
	
	/*$(".fftt .ff").each(function(){
		if( $(this).val() ) $(this).siblings(".tt").hide();
	}).live("focus", function(){
		$(this).addClass("foc");
	}).live("keydown", function(){
		$(this).siblings(".tt").removeClass("foc").addClass("off");
	}).live("blur", function(){
		if( $(this).val() == '' ){ $(this).siblings(".tt").removeClass("off").removeClass("foc"); }
		else $(this).siblings(".tt").addClass("off");
	});*/
	$(".fftt").on("click", ".tt", function(){
		$(this).siblings(".ff").focus();
	}).on("keyup", ".ff", function(){
		if($(this).val()) $(this).addClass("notempty").removeClass("empty");
		else $(this).removeClass("notempty").addClass("empty");
	});
	
	/*$("input[type='button'], button, input[type='submit'], input[type='reset']").hover(function(){$(this).addClass("over")},function(){$(this).removeClass("over")})
	.mousedown(function(){$(this).addClass("down")}).mouseup(function(){$(this).removeClass("down")}).mouseout(function(){$(this).removeClass("down")});*/
	
	$(".preventdefault").click(function(Ev){Ev.preventDefault()});
	
	if( $("#notify").html() ) showNotifications();
	
	/*window.chbox = { isLoading:function(el){
		if( $(el).hasClass("loading") ) return true;
		$(el).addClass("loading");
		return false;
	}}
	$(".chbox").click(function(){
		if($(this).hasClass("checked")) $(this).removeClass("checked").find("input:checkbox").attr('checked', false);
		else $(this).addClass("checked").find("input:checkbox").attr('checked', true);
	}).hover(function(){
		$(this).addClass("hov");
	},function(){
		$(this).removeClass("hov");
	});*/
	
	/*$(".thumbnail:visible").not(".embaudio").not(".noresize").find(".container").each(function(){
		w = $(this).find('img').width();
		if(w < 100) w = 100;
		$(this).css("width", w+"px");
		if(w <= 200) $(this).find('.caption').wrapInner('<small />');
	});*/
	$(".thumbnail").hover(function(){$(this).addClass("hov")},function(){$(this).removeClass("hov")});
	
	//if(!$.browser.msie) { //strange IE bug when messing with opacity here
		//$("#twitter_div").hover(function(){$(this).animate({'opacity':1})},function(){$(this).animate({'opacity':.6})});
	//}
	
	//latest tweet
	//if( !$("#head").hasClass("condensed") && $("#twitter_div").is(":visible") ) $("body").append('<script type="text/javascript" src="http://twitter.com/javascripts/blogger.js"></script><script type="text/javascript" src="http://twitter.com/statuses/user_timeline/videogamin.json?callback=twitterCallback2&amp;count=1"></script>');
	
	$("#head.condensed").hover(function(){$("#head, #head > *").addClass("hov")},function(){$("#head, #head > *").removeClass("hov")});
	
	$(".spoiler").live('mouseover mouseout', function(event){if(event.type == 'mouseover'){$(this).addClass("hov");}else{$(this).removeClass("hov");}});
	
	$("form.#footfeedback").submit(function(){
		
		var ffbinp = $(this).serialize();
		
		$("#footfeedback .loading").show();
		$("#footfeedback :input").attr("disabled", "disabled");
		$("#footfeedbacksend").val('Sending...');
		
		if($("#feedback-inp-name").val() != ''){
			//Spam! ignore that shit
			alert("k thx");
			return;
		}
		
		$.post(
			"/contact.php",
			{ _input:ffbinp,
				_ajax:1 },
			function(res){
				if(res.error) alert(res.error);
				else {
					alert("Your feedback query has been successfully transmitted to the appropriate human outlet. Thanks for your feedback!");
					$("#footfeedback .inp").val('');
				}
				$("#footfeedback .loading").hide();
				$("#footfeedback :input").removeAttr("disabled");
				$("#footfeedbacksend").val('Send');
			}, "json"
		);
		
  	return false;
  	
	});
	
	$(".fauxselect").on("click", fauxselect);
	
	$(".fauxselect-autocomplete").on("keyup", fauxautocomplete);
	
	/*
	$(".fauxselect").live("click", function(ev){
		ev.preventDefault();
		$(this).toggleClass("foc").next(".select").toggle();
	});
	
	$(".fauxselect .select a").live("click", function(){
		$(this).closest(".fauxselect").children("input").val($(this).text());
		$(this).closest(".fauxselect").children(".selected").removeClass("foc").find("a").css("background-image", "url(/bin/img/flags/"+$(this).attr("rel")+".png)");
		$(this).closest("ol").hide();
	});*/
	
	/** IMG **/
	
	$("a.imgupl, a.lightbox").live("click", function(ev){
		ev.preventDefault();
		var imguplfile =  $(this).hasClass("imgupl") ? $(this).attr("href").replace("/image/", "") : '';
		lightbox.open();
		if($(this).attr("rel")){
			$(this).addClass("imgrelon");
			lightbox.openNav( $(this).attr("rel") );
		}
		if(imguplfile) lightbox.load(imguplfile);
		else lightbox.fill($(this));
	});
	
	// insert img
	window.img = {
		searchTagData:[],
		vars:{},
		// img.init
		init:function(vars){ //initiate upload image form
			// vars obj { action [select, insert]; field, fieldId (input field to insert selected img name); }
			console.log("Initiate img insert:");
			console.log(vars);
			
			if($("#uplimg-editconsole").length && !confirm("The image console is already open. Do you want to continue? Any changes to the currently selected image will not be saved.")) return;
			$("#uplimg-editconsole").remove();
			
			img.vars = vars;
			
			if(vars.field){
				if(!$(field).length){
					alert("Error: insert field not found");
					return;
				}
				img.activeInsField = field;
			} else if(vars.fieldId){
				if(!$("#"+vars.fieldId).length){
					alert("Error: insert field not found");
					return;
				}
				img.activeInsField = $("#"+vars.fieldId);
				img.activeInsFieldId = vars.fieldId;
			} else {
				alert("Error: no insert field specified");
				return;
			}
			img.activeInsFieldSrc = vars.fieldSrc;
			img.insAction = vars.action ? vars.action : "insert";
			img.onSelect = vars.onSelect;
			if(!vars.nav) vars.nav = "select";
			if(!vars.uploadVars) vars.uploadVars = {};
			loading.on(false);
			$.get(
				"/bin/php/imginsert.php",
				{ "action":"load_ins_frame", "form_action":img.insAction, "upload_vars":vars.uploadVars },
				function(ret){
					$("body > .insimg").remove();
					$("body").append(ret);
					$("#insimg").slideDown();
					loading.off();
					mousePosScroll();
					img.nav(vars.nav);
				}
			)
		},
		loadForm:function(frame, vars){
			vars.action = "load_form";
			var fTarget = $("#insimg-"+frame);
			if($(fTarget).children(".container").length) fTarget = $("#insimg-"+frame+" > .container");
			loading.on(false);
			$(fTarget).load("/bin/php/imginsert.php", vars, function(){ mousePosScroll(); loading.off(); });
		},
		closeForm:function(keepDom){
			$('.insimg').slideUp(500).animate({opacity:1}, 500, function(){ if(keepDom !== true) $(this).remove() });
		},
		nav:function(frame){
			//img.insAction = frame == "search" ? "select" : frame;
			var frameOffset = "0px";
			if(frame == "upload") frameOffset = "140px";
			else if(frame == "search") frameOffset = "280px";
			$("#insimg-nav-"+frame).addClass("on").siblings().removeClass("on");
			$("#insimg-"+frame).show().siblings().hide();
			if(frame == "search"){
				$("#imgtagsq").focus();
			}
		},
		selected:{},
		select:function(imgVars){
			if(imgVars.img_name) img.selected = imgVars;
			console.log("Select Img (Action:"+img.insAction+"):");console.log(img.selected);
			if(img.insAction == "insert"){
				$("#insimg-gencode").append('<input type="hidden" name="img_name[]" value="'+img.selected.img_name+'" class="selimgs" data-imgname="'+img.selected.img_name+'"/>');
				img.generateCode();
			}
			if(img.insAction == "select" || img.insAction == "upload"){
				$(img.activeInsField).val(img.selected.img_name);
				if(img.activeInsFieldSrc) $(img.activeInsFieldSrc).attr("src", img.selected.src_tn).animate({opacity:0}, 800, function(){$(this).animate({opacity:1}, 800)});
				img.closeForm();
			}
			if(img.onSelect) eval(img.onSelect(img.vars, imgVars));
		},
		deselect:function(imgVars){
			if(imgVars.img_name) img.selected = imgVars;
			console.log("Deselect Img:");console.log(img.selected);
			$("#insimg-gencode .selimgs").each(function(){
				if($(this).data("imgname") == img.selected.img_name) $(this).remove();
			})
			img.generateCode();
		},
		generateCode:function(){
			var $selImgs = $("#insimg-gencode .selimgs");
			if(!$selImgs.length){
				$(".insimg-selimgs").attr("disabled", false);
				$("#insimg-code").fadeOut();
				return;
			}
			$("#insimg-code").fadeIn().addClass("loading");
			if(img.xhr && img.xhr.readyState != 4) img.xhr.abort();
			img.xhr = $.post(
				"/bin/php/imginsert.php",
				{ "action":"generate_code", vars:$("#insimg-gencode").serialize() },
				function(res){
					$("#insimg-code").removeClass("loading");
					if(res.error) alert(res.error);
					$("#insimg-gencode-code").val(res.code);
					$("#insimg-gencode-preview").html(res.formatted);
					$("#insimg-gencode-preview .imgupl").each(function(){
						$(this).wrap('<div class="imguplwrapper">').after('<a class="rm ximg2" onclick="img.deselect({img_name:\''+$(this).data("imgname")+'\'})">Remove</a>');
					})
				}, "json"
			);
		},
		insertCode:function(){
			var code = $("#insimg-gencode-code").val();
			var xfield = document.getElementById(img.activeInsFieldId);
			if (document.selection && document.selection.createRange) {
		      xfield.focus();
		      sel = document.selection.createRange();
		      sel.text = sel.text + code;
		      xfield.focus();
		  } else if (xfield.selectionStart || xfield.selectionStart == "0") {
		      var startPos = xfield.selectionStart,
		          endPos = xfield.selectionEnd;
		      xfield.value = xfield.value.substring(0, startPos) + xfield.value.substring(startPos, endPos) + code + xfield.value.substring(endPos, xfield.value.length);
		      xfield.selectionStart = xfield.selectionEnd = endPos + code.length;
		      xfield.focus();
		  } else {
		      xfield.value += code;
		      xfield.focus();
		  }
		  $("#insimg-code").fadeOut();
		},
		edit:function(imgname){
			//load the edit console to edit an image, add/rm tags, etc
			loading.on();
			if($("#uplimg-editconsole").length && !confirm("Do you want to continue? Any changes to the currently selected image will not be saved.")) return;
			$("#uplimg-editconsole").remove();
			$.post(
				"/bin/php/imginsert.php",
	  		{ action:'load_edit_form', img_name:imgname },
	  		function(res){
	  			if(res.error) alert(res.error);
	  			if(res.formatted){
	  				$("body").append(res.formatted);
	  				$("#uplimg-editconsole").slideDown(500);
	  			}
	  			else alert("an unknown error ocurred");
	  			loading.off();
	  		}
	  	)
		},
		saveUplImgInpData:function(){
	  	$("#uplimg-editconsole").slideUp(300);
	  	if(img.selected.img_name) img.select(img.selected);
	  	$.post(
	  		"/bin/php/imginsert.php",
	  		{ action:'set_img_data', 'in':$("#uplimg-edit-form").serialize() },
	  		function(res){
	  			if(res.success) $("#uplimg-editconsole").remove();
	  			else if(res.error) alert(res.error);
	  			else alert("An unknown error occurred.");
	  		}
	  	)
	  }
	}
	
	//keynav
	if($(".imgupl").length){
		var isCtrl = false;
		$(document).keyup(function(e){
			if(e.which == 17) isCtrl = false;
		}).keydown(function(e){
			if(!lightbox.activeNav) return;
			console.log("track keydown for lightbox nav");
			if(e.ctrlKey){
				isCtrl=true;
			} else return;
			if(e.which == 37){ //left
				if(!isCtrl) return;
				$("#lightbox-nav .prev").click();
			} else if(e.which == 39){ //right
				if(!isCtrl) return;
				$("#lightbox-nav .next").click();
			} else return;
		});
	}
	
	//shelf
	window.shelf = {
		bindActions: function($el){ $el.find(".shelf-item").hoverIntent(shelf.showLabel, shelf.hideLabel) },
		toggleLabel: function(){ $(this).children(".shelf-headings").fadeToggle() },
		showLabel: function(){
			if($(this).parent().hasClass("sortable")) return;
			$(this).children(".shelf-headings").css({top:"-10px"}).animate({opacity:"show", top:0});
		},
		hideLabel: function(){
			$(this).children(".shelf-headings").animate({opacity:"hide", top:"-10px"});
		},
		traverse:function($el, direction, limit, speed){
			if(!$el.length) return;
			if(!direction) return;
			var $container = $el.children(".shelf-container");
			shelf.num_items = $el.find(".shelf-item").size();
			shelf.item_width = $el.find(".shelf-item").eq(0).outerWidth(true);
			shelf.pos = $container.position();
			shelf.frame_width = limit ? (limit * shelf.item_width) : $el.width();
			shelf.container_width = shelf.num_items * shelf.item_width;
			if(typeof(direction)=="object") shelf.new_pos = 0 - (+direction.position) * shelf.item_width;
			else shelf.new_pos = shelf.pos.left - shelf.item_width * direction;
			console.log("shelf.traverse ("+direction+") ("+direction.position+") items:"+shelf.num_items+" itemWidth:"+shelf.item_width+" containerPosition:"+shelf.pos.left+" => "+shelf.new_pos+" containerWidth:"+shelf.container_width+" ("+$container.width()+") frameWidth:"+shelf.frame_width+" ["+($container.width() + shelf.new_pos)+"]");if(limit) console.log("limit:"+limit+" itemWidth:"+shelf.item_width+" ["+(limit * shelf.item_width) + "] newPos:"+shelf.new_pos);
			if(shelf.new_pos > 0) shelf.new_pos = 0;
			else{
				if((shelf.container_width + shelf.new_pos + shelf.item_width) < shelf.frame_width) shelf.new_pos = shelf.new_pos + shelf.item_width;
				if((shelf.container_width + shelf.new_pos + shelf.item_width) < shelf.frame_width) shelf.new_pos = shelf.new_pos + shelf.item_width;
			}
			$container.animate({'left':shelf.new_pos+"px"}, speed);
		}
	}
	shelf.bindActions($(".shelf"));
	
	/** COLLECTION **/
	$(['/bin/img/loading_ken.gif']).preload();
	window.collection = {
		update:function($el){
			if(!$el.length) return;
			if($el.hasClass("loading")) return;
			$el.addClass("loading");
			collection["reset_shelf_item"] = $("#collection-shelf-items").length == 0 ? false : true; //request the new shelf item be returned and formatted so we can refresh the current shelf item
			$.post("/bin/php/collection.php", { collection_entry_input: $el.serialize(), return_new_shelf_item:collection["reset_shelf_item"] }, function(res){
				if(res.errors){ for(var i = 0; i < res.errors.length; i++) $.jGrowl(res.errors[i]); $el.removeClass("loading"); return; }
				if(res.id){
					$("#collection-id").val(res.id);
					$("#collection-rm").show();
				}
				if(res.success) setTimeout(function(){ $el.addClass("finished") }, 200);
				if(res.formatted && res.shelf_id){
					//new shelf item
					var $shelf_item = $("#shelf-item-id-"+res.shelf_id);
					if(res.added){ // user just added it
						if(res.shelf_position == 0) $("#collection-shelf-items").prepend(res.formatted);
						else{
							if($("#load-more").length == 0) $("#collection-shelf-end").before(res.formatted);
							$.jGrowl("Game appended to the bottom of your collection");
						}
					} else if($shelf_item.length == 0){
						console.log("Error: couldn't find #shelf-item-id-"+res.shelf_id);
					} else {
						var shelf_item_index = $shelf_item.index();
						$shelf_item.remove();
						$("#collection-shelf-items .shelf-item").eq(shelf_item_index).before(res.formatted);
					}
				}
				$("#pgop-form-collection").pause(1400).fadeOut(function(){ $el.removeClass("loading finished") });
			})
		},
		selectBox:function($el, changePlatform){
			if(!$el.length){ $.jGrowl("Error: Couldn't select box [id element missing]."); return; }
			$el.addClass("on").siblings().removeClass("on");
			$("#collection-field-img :input[name='img_name']").val($el.data("img")).siblings(":input[name='img_orientation']").val($el.data("platform"));
			//alert($("#collection-field-img :input[name='img_name']").val());alert($("#collection-field-img :input[name='img_orientation']").val());
			if(changePlatform !== false){
				collection.changeField("platform", $el.data("platform"));
				if($el.data("distribution") == "digital" && $("#collection-field-ownership-input").val() == "own") collection.changeField("ownership", "own-digital");
				else if($el.data("distribution") != "digital" && $("#collection-field-ownership-input").val() == "own-digital") collection.changeField("ownership", "own");
			}
			$("#pgop-form-collection .fauxselect-option.releaseday").attr("data-value", $el.data("release"));
		},
		completion_fields: ["Haven\'t played it", "Played it some", "Played it a lot", "Beat it", "Mastered it"],
		slider_range_colors: ["#F5C0C0", "#EC8282", "#E65757", "#DD2222", ""],
		slider_range_colors_off: ["rgba(0,0,0,.075)", "rgba(0,0,0,.075)", "rgba(0,0,0,.15)", "rgba(0,0,0,.3)", "rgba(0,0,0,.5)"],
		changeField:function(field, val, ref){
			console.log("collection.changeField "+field);
			//var ref is "field" by default (matches #collection-field-FIELD), but is open for "nav" to match #collection-nav-FIELD
			if(!ref) ref = "field";
			if(field=="completion"){
				$("#collection-"+ref+"-completion-input").val(val);
				var val_semantic = (+val) / 33,
				    $output_field = $("#collection-"+ref+"-completion output"),
				    //$slider_range = $("#collection-"+ref+"-completion .ui-slider-range-min"),
				    $slider_steps = $("#collection-"+ref+"-completion .slider .steps"),
				    posleft;
				$output_field.siblings(".pt").css({left:val+"%"});
				if(val==100) $("#collection-"+ref+"-completion .mguidance").css({opacity:1});
				else $("#collection-"+ref+"-completion .mguidance").css({opacity:.3});
				val_semantic = Math.ceil(val_semantic);
				if(val_semantic < 0) val_semantic = 0;
				if(val_semantic > 4) val_semantic = 4;
				//console.log("val_semantic:"+val_semantic);
				var i=0;
				$slider_steps.children().each(function(){
					if(i < val_semantic) $(this).css({"background-color": collection["slider_range_colors"][i]});
					else $(this).css({"background-color": collection["slider_range_colors_off"][i]});
					i++;
				});
				val_semantic = collection["completion_fields"][val_semantic];
				$output_field.css({left:val+"%", right:"auto"}).html('<span>'+val_semantic+'</span>');
				posleft = $output_field.position().left;
				if(($output_field.outerWidth(true) + posleft - 11) > $output_field.parent().outerWidth()) $output_field.css({left:"auto", right:"-11px"});
				if(val==0) $("#collection-"+ref+"-playingdetails").slideUp();
				else $("#collection-"+ref+"-playingdetails:hidden").slideDown();
				return;
			}
			if(!val || val == "undefined") return;
			//console.log("collection.changeField: "+field+", "+val);
			var $inp = $("#collection-"+ref+"-"+field+"-input"),
			    $outp = $("#collection-"+ref+"-"+field+"-output");
			$inp.val(val).siblings(".fauxselect-options").children("[data-value='"+val+"']").addClass("selected").siblings().removeClass("selected");
			if(field == "ownership"){
				$("#collection-"+ref+"-condition").show();
				$("#collection-"+ref+"-purchase").show();
				$("#collection-"+ref+"-productid").show();
				if(val != "own") $("#collection-"+ref+"-productid, #collection-"+ref+"-condition").hide();
				if(val == "want") $("#collection-"+ref+"-purchase, #collection-"+ref+"-productid, #collection-"+ref+"-condition").hide();
				if(val == "own-digital") val = "own a digital copy of";
				if(val == "play"){
					val = "have played";
					if($("#collection-"+ref+"-completion-input").val() == '0') collection.changeField("completion", 1);
					$("#collection-"+ref+"-purchase").hide();
				}
			}
			$outp.text(val);
		},
		remove:function(){
			collection["id"] = $("#collection-id").val();
			if(!collection["id"]){
				$.jGrowl("Error removing this game: Couldn't find [collection-id]");
				return;
			}
			if(!confirm("For real remove this from your game collection?")) return;
			$("#pgop-form-collection").fadeOut();
			$("#pgop-collection").addClass("loading");
			$.post("/bin/php/collection.php", { "remove":collection["id"] }, function(res){
				if(res.errors){ for(var i = 0; i < res.errors.length; i++) $.jGrowl(res.errors[i]); return; }
				if(res.success){
					$("#pgop-collection").removeClass("loading on");
					$("#collection-id").val('');
					$("#collection-rm").hide();
				}
			});
		},
		saveSortOrder:function(){
			shelf["sortorder"] = $("#collection-shelf-items").sortable('toArray').toString();
			$.post(
				"/bin/php/collection.php",
				{ "sort": shelf["sortorder"] },
				function(res){
					if(res.errors) for(var i = 0; i < res.errors.length; i++) $.jGrowl(res.errors[i]);
					if(res.not_sorted) for(var i = 0; i < res.not_sorted.length; i++) $("#shelf-item-id-"+res.not_sorted[i]).addClass("error").animate({opacity:1}, 10000, function(){ $(this).removeClass("error") });
					if(!res.success) $.jGrowl("There were errors updating your sort preferences.");
				}, "json"
			);
		}
	}
	
	$("#pgop-form-collection").on("click", "#collection-rm", function(){ collection.remove() });
	
	/** BADGES **/
	
	$(['/bin/img/decorative_bg.jpg']).preload();
	
	if( $(".badgeearn").length ){
		$("body > .bodyoverlay").fadeIn(800, function(){ $(".badgeearn:eq(0)").fadeIn(800); });
	}
	
	$(".badgeearn").click(function(){
		$(this).fadeOut(800, function(){
			var $nextBadge = $(this).next(".badgeearn");
			if( $nextBadge.length ) $nextBadge.fadeIn(800);
			else $("body > .bodyoverlay").fadeOut();
		});
	});
	
	/** POSTS **/
	
	$("a.postsnavlink").live("click", function(ev){
		ev.preventDefault();
		var pnavto = $(this).attr("href").replace("handle.php", "");
		if( $("#posts").length ) $.address.value(pnavto);
		else document.location = '/#' + pnavto;
	});
	
	if($("#posts").length){
		
		$.address.change(function(ev){
			console.log("posts funct addr ch; event follows:");
			console.log(ev);
			if( ev.pathNames[0]=="posts" ){
				if(!$("#postsqueryparams").length) return;
				$("html, body").animate({scrollTop:$("#posts").offset().top}, 1000);
				if(!ev.queryString) return;
				loading.on();
				$("#posts").css("opacity", ".5");
				$.post(
					"/bin/php/ajax.posts.php",
					{ "load_postslist":ev.queryString+"&"+$("#postsqueryparams :input.dontget").serialize() },
					function(res){
						if(res.errors) for(var i = 0; i < res.errors.length; i++) $.jGrowl(res.errors[i]);
						if(res.formatted){
							$("#posts").html(res.formatted);
							postsDom.init();
							postShare.init();
							//$("#posts aside:eq(0)").html(res.formatted_aside_legend);
							//$("#posts aside:eq(1)").html(res.formatted_aside);
							//tooltip();
						}
						loading.off();
						$("#posts").css("opacity", "1");
					}, "json"
				);
			} else console.log("No posts event");
		}).strict(false);
		
		postsDom.init();
		postShare.init();
		
		$("#postsqueryparams .togglechecks :input[type='checkbox']").live("change", function(){
    	if($(this).attr("checked")) $(this).parent().next().attr("checked", false);
    	else $(this).parent().next().attr("checked", true);
    });
    
    $(".video-tn").live("click", function(ev){
			ev.preventDefault();
			var nid = $(this).attr("rel");
			console.log("load video nid "+nid);
			if(!nid){ console.log("No NID found in REL attr; continue through to permalink."); return; }
			ev.preventDefault();
			$(this).hide();
			$.post(
				"/bin/php/ajax.posts.php",
				{ 'load_video':nid },
				function(res){
					if(res.formatted){
						$("#nid-item-"+nid+" .listitem").html(res.formatted);
					}
				}, "json"
			)
		});
		
	} else console.log("No posts functionality");
	
	$(".hrate a").live("click", function(ev){
		
		ev.preventDefault();
		
		//break and request login if no usr session
		if( !$("#usrid").val() ) {
			login.init();
			return;
		}
		
		var nid = $(this).parent().attr("id").replace("rate-nid-", "");
		var rating = ( $(this).html() == "+" ? 1 : 0 );
		var el = $(this).parent();
		
		$(el).addClass("loading");
		$(this).hide().siblings("a").show();
		
		$.post(
			"/bin/php/ajax.posts.php",
			{ nid: nid,
				set_rating: rating
			}, function(res){
				if(res.error) alert("Error: "+res.error);
				$(el).removeClass("loading").find(".rating").attr("title", res.title).html(res.outp);
			}, "json"
		);
	});
	
	// tagging
	var tg = { el:'', elval:'', open:0, tag:'', tagStart:0, tagEnd:0 }
	$(".tagging").after('<input type="text" class="tagging-space" style="visibility:hidden; height:0;"/>');
	$(".tagging-space").autocomplete({
		minLength:1,
		autoFocus:true,
		focus: function(){ return false; },
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
			tg["tag"] = ui.item.tag ? ui.item.tag : ui.item.value;
			tg.elval = $(tg.el).val();
			tg.elval = tg.elval.slice(0, tg["tagStart"]) + tg["tag"] + "]]" + tg.elval.slice((tg["tagEnd"] + 1));
			$(tg.el).val(tg.elval);
			return false;
		}
	});
	$( ".tagging" ).bind( "keydown", function(event){
		tg.el = $(this);
		//console.log(event);
		if(event.keyCode == 219){
			if(++tg["open"] == 2) tg.tagStart = event.currentTarget.selectionStart + 1;
		} else {
			if(tg.tagStart){
				tg["tagEnd"] = event.currentTarget.selectionStart;
				tg["tag"] = $(this).val().slice(tg["tagStart"], tg["tagEnd"]);
			}
		}
		if(tg["tag"]){
			$(this).siblings(".tagging-space").autocomplete("search", tg["tag"]);
		}
		// don't navigate away from the field on tab when selecting an item
		if ( event.keyCode === $.ui.keyCode.TAB && $( this ).data( "autocomplete" ).menu.active ) {
			event.preventDefault();
		}
	});
	
	/*
            .autocomplete({
                minLength: 1,
                source: function( request, response ) {
                    console.log("ac source");console.log(request);console.log(response);
                },
                focus: function() {
                    // prevent value inserted on focus
                    return false;
                },
                select: function( event, ui ){
                    var terms = split( this.value );
                    // remove the current input
                    terms.pop();
                    // add the selected item
                    terms.push( ui.item.value );
                    // add placeholder to get the comma-and-space at the end
                    terms.push( "" );
                    this.value = terms.join( ", " );
                    return false;
                }
            });*/
	
});


/** IMG **/

var lightbox = {
	activeNav:false, //track keydown for navigating
	'open':function(){
		lightbox.activeNav = true;
		loading.on();
		$("body").append('<div id="lightbox" class="lightbox loading"><div id="lightbox-label"></div><div class="close" onclick="lightbox.close()"><span>Close</span></div></div><div id="lightbox-img" class="lightbox"><div class="container"></div></div><div id="lightbox-nav" class="lightbox"></div><div class="bodyoverlay lightbox"></div>');
		$("#lightbox-img").css("top", $("#lightbox").offset().top+"px");
		$(".lightbox").fadeIn();
	},
	'close':function(){
		loading.off();
		lightbox.activeNav = false;
		$("body > div.lightbox").fadeOut(function(){$(this).remove()});
		$("a.imgrelon").removeClass("imgrelon");
	},
	'openNav':function(imgrel){
		console.log("openNav: "+imgrel);
		var rel = { 'num':0, 'active':'' }
		$("a[rel='"+imgrel+"']").each(function(){
			if($(this).hasClass("imgrelon")) rel.active = rel.num;
			rel.num++;
		});
		//if(rel.num < 2) return;
		$("#lightbox-nav").html('<span><a href="#next" onclick="lightbox.nav('+((rel.active + 1) < rel.num ? rel.active + 1 : 0)+', \''+imgrel+'\')" class="next">Next</a><a href="#prev" onclick="lightbox.nav('+((rel.active - 1) >= 0 ? rel.active - 1 : rel.num - 1)+', \''+imgrel+'\')" class="prev">Previous</a><i>Ctrl</i></span><div><ul></ul></div>');
		for(i=0; i<rel.num; i++) $("#lightbox-nav ul").append('<li class="'+(i==rel.active?'on':'')+'"><a href="#navigate" onclick="lightbox.nav('+i+', \''+imgrel+'\')">'+(i+1)+'</a></li>');
		
		//preload this and the next image
		$([lightbox.getImgSrc($("a[rel='"+imgrel+"']:eq("+rel.active+")"))]).preload();
		if(rel.active + 1 < rel.num){
			console.log("preload... "+(rel.active+1)+"/"+rel.num);
			$([lightbox.getImgSrc($("a[rel='"+imgrel+"']:eq("+(rel.active + 1)+")"))]).preload();
		}
		
	},
	// lightbox.nav
	'nav':function(i, imgrel){
		if( $("#lightbox").hasClass("loading") ) return;
		var f = $("a[rel='"+imgrel+"']:eq("+i+")");
		if(!$(f).length){ alert("An navigation error ocurred!"); return; }
		loading.on();
		$("#lightbox").addClass("loading");
		var imguplfile = $(f).hasClass("imgupl") ? $(f).attr("href").replace("/image/", "") : '';
		if(imguplfile) lightbox.load(imguplfile, imgrel);
		else lightbox.fill( f );
		$("a.imgrelon").removeClass("imgrelon");
		$(f).addClass("imgrelon");
	},
	'load':function(imguplfile, imgrel){
		console.log("load lightbox: "+imguplfile);
		var x_max = 800;//$("#lightbox").width() - 300;
		var y_max = 0;//$("#lightbox").height() - 40;
		if(!$("#lightbox").length) return;
		$.get(
			"/bin/php/ajax.img.php",
			{ load_img_data:imguplfile, x_max:x_max, y_max:y_max },
			function(res){
				loading.off();
				$("#lightbox").removeClass("loading");
				if(res.errors){
					for(var i = 0; i < res.errors.length; i++) $.jGrowl(res.errors[i]);
				}
				if(res.img) $("#lightbox-img .container").html(res.img);
				if(res.label) $("#lightbox-label").html(res.label);
				if(imgrel) lightbox.openNav(imgrel);
			}, "json"
		)
	},
	// lightbox.fill
	'fill':function(el){
		var imgrel = $(el).attr("rel");
		$("#lightbox").animate({opacity:1}, 500, function(){
			//delay for a bit to load image and get size
			
			var img = new Image();
			img.src = $(el).attr("href");
			img.alt = $(el).attr("title");
			if(img.width > 800){
				$(img).addClass("scaled").click(function(){ $(img).toggleClass("scaled full") });
				/*img.className = "scaled"; img.onClick = lightbox.toggleScaled({scaled_x:800,full_x:img.width});*/
			}
			$("#lightbox-img .container").html(img);
			$("#lightbox-label").html('<ul><li class="h"><h5>'+$(el).attr("title")+'</h5></li></ul>');
			loading.off();
			$("#lightbox").removeClass("loading");
			if(imgrel) lightbox.openNav(imgrel);
			
		});
	},
	toggleScaled:function(dimensions){
		var img = $("#lightbox-img img");
		if($(img).hasClass("scaled")){
			$(img).removeClass("scaled").addClass("full").attr({"width":dimensions.full_x, "height":dimensions.full_y});
		} else {
			$(img).removeClass("full").addClass("scaled").attr({"width":dimensions.scaled_x, "height":dimensions.scaled_y});
		}
	},
	'getImgSrc':function(el){
		// where var el is <a>:
		// <a class="imgupl" rel="71749" href="/image/dkcr_ca_7_1.jpg"><img src="/images/0000001/tn/dkcr_ca_7_1.jpg.png"></a>
		console.log(el);
		return $(el).children("img").attr("src").substr(0, 16) + $(el).attr("href").substr(7);
	}
}


/** POSTS **/

function filterPosts(){
	var qs = $("#postsqueryparams :input[value]").not(".dontget").serialize()
	$.address.value("/posts/?" + (qs ? qs : 'filter=no'));
	return false;
}
function confirmDesc(nid) {
	var pd = $("#postdesc").val();
	$.post(
		"/posts/process.php",
		{ _action:"confirmdesc",
			desc:pd,
			nid:nid 
		}, function(res){
			if(res) alert(res);
			else {
				$("#postform").fadeOut();
				$(".video-code").show();
			}
		}
	);
}
var postsDom = {
	init:function(){
		$(".postlist > dl").hoverIntent(postsDom.toggleHov, postsDom.toggleHov);
	},
	toggleHov:function(){ $(this).toggleClass("hov nohov") }
}
var postShare = {
	init: function(){ //postShare.init()
		$("#posts .share").hoverIntent({
			over: postShare.openConsole,
			out: postShare.closeConsole
		});
	}, openConsole: function(){ //postShare.openConsole()
		var cons = $(this).children(".shareconsole");
		$(cons).slideDown();
		if( !$(cons).text() ){
			var nid = $(this).attr("id").replace("share-", "");
			$(cons).load("/bin/php/ajax.posts.php", {"load_share":nid, "desc":$("#nid-item-"+nid+" dt .description").text()});
		}
	}, closeConsole: function(){ //postShare.closeConsole()
		$(this).children(".shareconsole").slideUp();
	}
}

/** MISC **/

function showNotifications() {
	$("#notify:hidden").slideDown(500).append('<a href="javascript:void(0);" style="position:absolute; right:15px; bottom:12px;" onclick="$(this).parent().slideUp(300);" onmouseover="$(this).children(\'span\').addClass(\'ximg-hover\');"><span class="ximg" style="margin:1px 0 0 -20px;">x</span><b>CLOSE THIS MESSAGE</b></a>');
}
	
var asyncRequest = function(){return;}();//depreciated

function htmlSC(what){
	what = what.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\"/g, '&quot;').replace(/\'/g, '&#039;');
	return what;
}

window.loading = {
	loading:false,
	on:function(anim){
		loading.loading = true;
		if($("body").hasClass("loading")) return;
		$("body").addClass("loading");
		if(anim !== false) $("#loading2").animate({right:"20px"});
	},
	off:function(){
		loading.loading = false;
		$("body").removeClass("loading");
		$("#loading2").animate({right:"-20px"});
	}
}

function toggleTopnavItem(){ $(this).toggleClass("hov") }
	
function fauxselect(event){
	event.stopPropagation();
	console.log("fauxselect.click: "+event.target+", "+event.currentTarget);console.log(event);
	var $el = $(event.currentTarget).hasClass("fauxselect") ? $(event.currentTarget) : $(event.target).closest(".fauxselect"),
			$sel = $el.children(".fauxselect-options"),
			$op = $(event.target).hasClass("fauxselect-option") ? $(event.target) : $(event.target).closest(".fauxselect-option");
	$(".fauxselect").not($el).removeClass("on");
	$el.toggleClass("on").find(".fauxselect-input").attr("autocomplete", "off");
	if($op.data("value")){
		$op.addClass("selected").siblings().removeClass("selected").parent().siblings(".fauxselect-output").text($op.data("value"));
		$op.parent().siblings(".fauxselect-input").val($op.data("value")).triggerHandler("change");
		return $op.data("value");
	}
	if($el.hasClass("on")){
		$(document).one("click", function(event){
			$el.removeClass("on");
			event.stopPropagation();
		});
	}
}

function fauxautocomplete(event){
	console.log(event);
	var $el = $(event.target),
			match1 = $el.val().toLowerCase().replace("[[", "").replace("]]", "")
			match2 = '',
			KEY = {
				UP: 38,
				DOWN: 40,
				DEL: 46,
				TAB: 9,
				RETURN: 13,
				ESC: 27,
				COMMA: 188,
				PAGEUP: 33,
				PAGEDOWN: 34,
				BACKSPACE: 8
			};
		
		if(match1 == ""){
			$el.siblings(".fauxselect-options").children(".fauxselect-option").show();
			return;
		}
		
		$el.siblings(".fauxselect-options").children(".fauxselect-option").each(function(){
			match2 = $(this).attr("title") + $(this).text();
			if(match2.toLowerCase().indexOf(match1)>=0) $(this).show();
			else $(this).hide();
		});
		
		return;
		switch(event.keyCode){
		
			case KEY.UP:
				event.preventDefault();
				if ( select.visible() ) {
					select.prev();
				} else {
					onChange(0, true);
				}
				break;
				
			case KEY.DOWN:
				event.preventDefault();
				if ( select.visible() ) {
					select.next();
				} else {
					onChange(0, true);
				}
				break;
				
			case KEY.PAGEUP:
				event.preventDefault();
				if ( select.visible() ) {
					select.pageUp();
				} else {
					onChange(0, true);
				}
				break;
				
			case KEY.PAGEDOWN:
				event.preventDefault();
				if ( select.visible() ) {
					select.pageDown();
				} else {
					onChange(0, true);
				}
				break;
		}
}

function handleErrors(err){
	if(typeof err === 'string'){
		$.jGrowl(err);
	} else {
		for(var i = 0; i < err.length; i++) $.jGrowl(err[i]);
	}
}

/* PLUGINS */

//preload images
//ie: $(['img1.jpg','img2.jpg','img3.jpg']).preload();
$.fn.preload = function(){
	this.each(function(){
		$('<img/>')[0].src = this;
	});
}
$(['/bin/img/loading_bar.gif','/bin/img/icons/sprites/littlemac_jog.gif']).preload();

(function($){
  $.fn.pause = function(duration) {
		this.animate({ dummy: 1 }, duration);
		return this;
  };
})(jQuery);

/**
* hoverIntent r6 // 2011.02.26 // jQuery 1.5.1+
* <http://cherne.net/brian/resources/jquery.hoverIntent.html>
* 
* @param  f  onMouseOver function || An object with configuration options
* @param  g  onMouseOut function  || Nothing (use configuration options object)
* @author    Brian Cherne brian(at)cherne(dot)net
*/
(function($){$.fn.hoverIntent=function(f,g){var cfg={sensitivity:7,interval:85,timeout:0};cfg=$.extend(cfg,g?{over:f,out:g}:f);var cX,cY,pX,pY;var track=function(ev){cX=ev.pageX;cY=ev.pageY};var compare=function(ev,ob){ob.hoverIntent_t=clearTimeout(ob.hoverIntent_t);if((Math.abs(pX-cX)+Math.abs(pY-cY))<cfg.sensitivity){$(ob).unbind("mousemove",track);ob.hoverIntent_s=1;return cfg.over.apply(ob,[ev])}else{pX=cX;pY=cY;ob.hoverIntent_t=setTimeout(function(){compare(ev,ob)},cfg.interval)}};var delay=function(ev,ob){ob.hoverIntent_t=clearTimeout(ob.hoverIntent_t);ob.hoverIntent_s=0;return cfg.out.apply(ob,[ev])};var handleHover=function(e){var ev=jQuery.extend({},e);var ob=this;if(ob.hoverIntent_t){ob.hoverIntent_t=clearTimeout(ob.hoverIntent_t)}if(e.type=="mouseenter"){pX=ev.pageX;pY=ev.pageY;$(ob).bind("mousemove",track);if(ob.hoverIntent_s!=1){ob.hoverIntent_t=setTimeout(function(){compare(ev,ob)},cfg.interval)}}else{$(ob).unbind("mousemove",track);if(ob.hoverIntent_s==1){ob.hoverIntent_t=setTimeout(function(){delay(ev,ob)},cfg.timeout)}}};return this.bind('mouseenter',handleHover).bind('mouseleave',handleHover)}})(jQuery);
	