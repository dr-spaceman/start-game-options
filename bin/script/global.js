
if (!window.console) var console = { log: function() {} };

var confirm_exit = false,
		confirm_exit_msg = "If you leave this page all your changes will be discarded.";

var $body = $("body");

$(document).ready(function(){
	
	//Check if browser supports HTML5
	if(!$("header").length){
		$("body").html('<h1>Incompatible browser</h1><p>Sorry for the inconvenience, but your browser isn\'t compatible with Videogam.in. Please download a modern browser or upgrade your browser to access this site and improve your internet browsing experience.<ul><li><a href="http://www.google.com/chrome">Google Chrome</a></li><li><a href="http://www.mozilla.com/firefox/">Mozilla Firefox</a></li><li><a href="http://www.opera.com/download/">Opera</a></li><li><a href="http://www.apple.com/safari/download/">Apple Safari</a></li><li><a href="http://www.microsoft.com/download">Microsoft Internet Explorer</a></li></ul>');
	}
	
	$(window).bind("beforeunload",function(event){
		if(confirm_exit) return confirm_exit_msg;
	});
	
	//initiate tooltips
	tooltip();
	
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
	
	$(".fftt").on("click", ".tt", function(){
		$(this).siblings(".ff").focus();
	}).on("keyup", ".ff", function(){
		if($(this).val()) $(this).addClass("notempty").removeClass("empty");
		else $(this).removeClass("notempty").addClass("empty");
	});
	
	$(".preventdefault").click(function(Ev){Ev.preventDefault()});
	
	if( $("#notify").html() ) showNotifications();
	
	//latest tweet
	//if( !$("#head").hasClass("condensed") && $("#twitter_div").is(":visible") ) $("body").append('<script type="text/javascript" src="http://twitter.com/javascripts/blogger.js"></script><script type="text/javascript" src="http://twitter.com/statuses/user_timeline/videogamin.json?callback=twitterCallback2&amp;count=1"></script>');
	
	$("#head.condensed").hover(function(){$("#head, #head > *").addClass("hov")},function(){$("#head, #head > *").removeClass("hov")});
	
	$(".spoiler").live('mouseover mouseout', function(event){if(event.type == 'mouseover'){$(this).addClass("hov");}else{$(this).removeClass("hov");}});
	
	$("form.#footfeedback").submit(function(){
		
		var ffbinp = $(this).serialize();
		
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
				else if(res.success) {
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
			// vars obj { action [select, insert, return]; field, fieldId (input field to insert selected img name); }
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
				
			}
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
			console.log("img.edit ["+imgname+"]");
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
	  				initTagAutocomplete();
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
					"/posts/ajax.php",
					{ "load_postslist":ev.queryString+"&"+$("#postsqueryparams :input.dontget").serialize() },
					function(res){
						if(res.errors) for(var i = 0; i < res.errors.length; i++) $.jGrowl(res.errors[i]);
						if(res.formatted){
							$("#posts").html(res.formatted);
							postsDom.init();
							postShare.init();
							//$("#posts aside:eq(0)").html(res.formatted_aside_legend);
							//$("#posts aside:eq(1)").html(res.formatted_aside);
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
    
    /* load video embedcode (old method)
    $(".video-tn").live("click", function(ev){
			ev.preventDefault();
			var nid = $(this).attr("rel");
			console.log("load video nid "+nid);
			if(!nid){ console.log("No NID found in REL attr; continue through to permalink."); return; }
			ev.preventDefault();
			$(this).hide();
			$.post(
				"/posts/ajax.php",
				{ 'load_video':nid },
				function(res){
					if(res.formatted){
						$("#nid-item-"+nid+" .listitem").html(res.formatted);
					}
				}, "json"
			)
		});*/
		
	} else console.log("No posts functionality");
	
	$(".posts").on("click", ".hrate a", function(ev){
		ev.preventDefault();
		
		//break and request login if no usr session
		if( !$("#usrid").val() ) {
			login.init();
			return;
		}
		
		var nid = $(this).data("nid"),
				rating = $(this).data("rating"),
				$el = $(this).parent();
		
		$el.addClass("loading");
		//$(this).hide().siblings("a").show();
		
		$.post(
			"/posts/ajax.php",
			{ nid:nid, set_rating:rating },
			function(res){
				if(res.errors){ handleErrors(res.errors); }
				$el.find(".rating").attr("title", res.title).html(res.outp).animate({opacity:1}, 500, function(){ $el.removeClass("loading") });
			}, "json"
		);
	});
	
	// tagging
	/*var tg = { elval:'', numOpenParens:0, q:'', tag:'', tagStartPos:0, tagEnd:0, caretPos:0, keypressed:'' },
		tgReset = function(){
			tg = { elval:'', numOpenParens:0, q:'', tag:'', tagStartPos:0, tagEnd:0, caretPos:0, keypressed:'' }
		};
	$(".tagging").keydown(function(event){
		console.log(event.keyCode);
		if(event.keyCode == 219){
			//track [ keypress; on second [[, set the position to start recording the tag
			if(++tg["numOpenParens"] == 2) tg["tagStartPos"] = event.currentTarget.selectionStart + 1;
		} else if(event.keyCode == 221 || event.keyCode == 220){
			// closing or separating tags ] |
			tgReset();
			$(".tagging").autocomplete("close");
		} else {
			tg["numOpenParens"]=0;
		}
		// don't navigate away from the field on tab when selecting an item
		if ( event.keyCode === $.ui.keyCode.TAB && $( this ).data( "autocomplete" ).menu.active ) {
			event.preventDefault();
		}
	}).autocomplete({
		minLength:1,
		autoFocus:true,
		focus: function(){ return true; },
		search:function( event, ui ){
			console.log(".tagging search:");
			console.log(event);
			
			tg["caretPos"] = event.currentTarget.selectionStart;
			
			if(tg["tagStartPos"]){
				tg["tagEndPos"] = tg["caretPos"];
				tg["q"] = $(this).val().slice(tg["tagStartPos"], tg["tagEndPos"]);
			}
			if(tg["q"]){
				console.log(".tagging searchterm:" + tg["q"] );
			} else {
				console.log("search return false");
				return false;
			}
		},
		source:function(request, response){
			console.log(".tagging source; q:"+tg["q"]);
			$.ajax({
				url: "/bin/php/autocomplete_tags.php",
				data: { 'q':tg["q"], 'noincludetags':1 },
				success: function(data){
					if(data.num_results == 0){
						$(".tagging").autocomplete("close");
					} else {
						response($.map(data.results, function(item){
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
		open: function(){ $(this).autocomplete("widget").width(350).css("max-height", "300px") },
		select:function(event, ui){
			console.log(".tagging select:");console.log(ui);
			tg["tag"] = ui.item.tag;
			tg.elval = $(this).val();
			tg.elval = tg.elval.slice(0, tg["tagStartPos"]) + tg["tag"] + "]]" + tg.elval.slice((tg["tagEndPos"] + 1));
			$(this).val(tg.elval);
			tg["caretPos"] = tg["caretPos"] + tg["tag"].length + 2 - tg["q"].length;
			$(this).selectRange(tg["caretPos"], tg["caretPos"]);
			tgReset();
			return false;
		}
	}).data( "autocomplete" )._renderItem = function( ul, item ) {
		var label = item.label ? item.label : item.tag,
	      term = tg["q"].split(' ').join('|'),
		    re = new RegExp("(" + term + ")", "gi"),
		    t = label.replace(re,"<b>$1</b>");
		return $( '<li></li>' )
			.data( "item.autocomplete", item )
			.append('<a><small>'+item.category+'</small><dfn>'+t+'</dfn></a>')
			.appendTo( ul );
	}*/
	
	// Give embedded flash videos the wmode attribute so they dont conflict with positioned css layers
	/*$('.video iframe').each(function(){
		var videourl = $(this).attr("src");
		$(this).attr("src",videourl + (videourl.indexOf("?") ? "&" : "?") + "wmode=transparent");
	});*/
	
	//Autosize input
	if($("textarea.autosize").length > 0){
		console.log("Load autosize");
		jQuery('<script/>').attr('src', "/bin/script/jquery.textareaautosize.js").appendTo('head');
		$("textarea.autosize").autosize();
	}
	
	//Pagedown Editor (WMD)
	if ($('.wmd-input').length > 0) {
		
		console.log("Load WMD");
		
		var elId, converter, help, editor;
		
		jQuery('<script/>').attr('src', "/bin/script/Markdown.Converter.js").appendTo('head');
		jQuery('<script/>').attr('src', "/bin/script/Markdown.Sanitizer.js").appendTo('head');
		jQuery('<script/>').attr('src', "/bin/script/Markdown.Editor.custom.js").appendTo('head');
		
		$('.wmd-input').each(function(){
			
	    elId = $(this).attr("id");
	    
			console.log("Wmd-input ["+elId+"]:");console.log($(this));
	  
	    converter = new Markdown.Converter();
	    help = function () { window.open('http://videogam.in/formatting-help'); };
	    editor = new Markdown.Editor(converter, elId, { handler: help });
			
	    editor.hooks.set('insertImageDialog', function(callback) {
	    	img.init({fieldId:elId});
	      return true; // tell the editor that we'll take care of getting the image url
	    });
	    
	    //Insert a blank toolbar if one hasnt been made
	    if(!$("#"+elId+"-toolbar").length) jQuery('<div/>').attr("id", elId+"-toolbar").insertBefore($("#"+elId));
	
	    editor.run();
	    
	  });
		
	}
	
	//Inline citations
	if($(".citation.inline_citations").length > 0){
		jQuery('<div/>').attr('id', "citation-tooltip").appendTo('body');
		var cite_ref,
		    cite_pos,
	      $cite_tt = $("#citation-tooltip");
	  
	  //A bunch of binding mumbojumbo to open the tooltip on hover over the subscripted reference, and keep it open unless mouseleaves both the reference and tooltip
		$(".citation.inline_citations")
			.bind('mouseover',function(e){
				//create the tooltip contents from the hidden reference list and show it
				cite_ref = $(this).children("a").attr("href").replace("#", "").replace(":", "\\:");
				cite_pos = $(this).offset();
				if($("#"+cite_ref).length > 0){
					$cite_tt.html($("#"+cite_ref).html())
						.css({"left":( cite_pos.left - 10 )+"px", "top":( cite_pos.top - $cite_tt.outerHeight() - 10 )+"px"})
						.fadeIn(100);
				}
			})
			.bind('mouseleave',function(){
				setTimeout(function(){
					if(!$cite_tt.data('over_second')) $cite_tt.stop(true,true).fadeOut(50);
				},50);
			});
			$cite_tt
				.bind('mouseover',function(e){
			    $(this)
			        .stop(true,true)
			        .fadeIn(100)
			        .data('over_second',true);
				})
				.bind('mouseleave', function(e) {
			    $(this)
			        .stop(true,true)
			        .fadeOut(100)
			        .removeData('over_second');
			});
	}
	
});


//track changes made to an input field and retrigger plugin functions if needed
window.triggerInputChangeEnvent = function(elem){
	console.log("triggerInputChangeEnvent:");console.log(elem);
	if($(elem).hasClass("autosize")) $(elem).trigger('autosize');
}


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
var postsDom = {
	init:function(){
		$(".postslist > .post-item").hoverIntent(postsDom.toggleHov, postsDom.toggleHov);
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
		var $cons = $(this).children(".shareconsole");
		$cons.show();
		if( !$cons.text() ){
			var nid = $(this).attr("id").replace("share-", "");
			$cons.load("/posts/ajax.php", {"load_share":nid, "desc":$("#nid-item-"+nid+" dt .description").text()});
		}
	}, closeConsole: function(){ //postShare.closeConsole()
		$(this).children(".shareconsole").hide();
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
		$body.addClass("loading");
		if(anim !== false) $("#loading2").animate({right:"20px"});
	},
	off:function(){
		loading.loading = false;
		$body.removeClass("loading");
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

function handleErrors(err, sticky){
	if(sticky !== false) sticky = true;
	if(typeof err === 'string'){
		$.jGrowl(err);
	} else {
		for(var i = 0; i < err.length; i++) $.jGrowl(err[i], { sticky: sticky });
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

$.fn.selectRange = function(start, end) {
    return this.each(function() {
        if (this.setSelectionRange) {
            this.focus();
            this.setSelectionRange(start, end);
        } else if (this.createTextRange) {
            var range = this.createTextRange();
            range.collapse(true);
            range.moveEnd('character', end);
            range.moveStart('character', start);
            range.select();
        }
    });
};

/**
* hoverIntent r6 // 2011.02.26 // jQuery 1.5.1+
* <http://cherne.net/brian/resources/jquery.hoverIntent.html>
* 
* @param  f  onMouseOver function || An object with configuration options
* @param  g  onMouseOut function  || Nothing (use configuration options object)
* @author    Brian Cherne brian(at)cherne(dot)net
*/
(function($){$.fn.hoverIntent=function(f,g){var cfg={sensitivity:7,interval:85,timeout:0};cfg=$.extend(cfg,g?{over:f,out:g}:f);var cX,cY,pX,pY;var track=function(ev){cX=ev.pageX;cY=ev.pageY};var compare=function(ev,ob){ob.hoverIntent_t=clearTimeout(ob.hoverIntent_t);if((Math.abs(pX-cX)+Math.abs(pY-cY))<cfg.sensitivity){$(ob).unbind("mousemove",track);ob.hoverIntent_s=1;return cfg.over.apply(ob,[ev])}else{pX=cX;pY=cY;ob.hoverIntent_t=setTimeout(function(){compare(ev,ob)},cfg.interval)}};var delay=function(ev,ob){ob.hoverIntent_t=clearTimeout(ob.hoverIntent_t);ob.hoverIntent_s=0;return cfg.out.apply(ob,[ev])};var handleHover=function(e){var ev=jQuery.extend({},e);var ob=this;if(ob.hoverIntent_t){ob.hoverIntent_t=clearTimeout(ob.hoverIntent_t)}if(e.type=="mouseenter"){pX=ev.pageX;pY=ev.pageY;$(ob).bind("mousemove",track);if(ob.hoverIntent_s!=1){ob.hoverIntent_t=setTimeout(function(){compare(ev,ob)},cfg.interval)}}else{$(ob).unbind("mousemove",track);if(ob.hoverIntent_s==1){ob.hoverIntent_t=setTimeout(function(){delay(ev,ob)},cfg.timeout)}}};return this.bind('mouseenter',handleHover).bind('mouseleave',handleHover)}})(jQuery);

/*
* tooltip
*/
this.tooltip = function(){
	/* CONFIG */		
		yOffset = 5;
		xOffset = -11;		
		// these 2 variable determine popup's distance from the cursor
		// you might want to adjust to get the right result		
	/* END CONFIG */		
	$(".tooltip").hover(function(e){
		
		if( $(this).hasClass("tooltip-offset") ) yOffset = -150;
		else yOffset = 5;
		
		this.t = this.title;
		this.title = "";
		$("body").append("<p id='tooltip'>"+ this.t +"</p>");
		$("#tooltip")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px")
			.fadeIn("fast");
    },
	function(){
		this.title = this.t;		
		$("#tooltip").remove();
  });
  
  //move the tooltip with the cursor
		$(".tooltip").not(".tooltip-fixed").mousemove(function(e){
			$("#tooltip")
				.css("top",(e.pageY - xOffset) + "px")
				.css("left",(e.pageX + yOffset) + "px");
		});

	
	$(".tooltip-bubble-el").hover(function(){
		var pos = $(this).siblings(".tooltip-bubble").position();
		$(this).siblings(".tooltip-bubble").fadeIn();
	}, function(){
		$(this).siblings(".tooltip-bubble").fadeOut();
	});
	
};


/*
* jGrowl
*/
(function($){$.jGrowl=function(m,o){if($('#jGrowl').size()==0)
$('<div id="jGrowl"></div>').addClass((o&&o.position)?o.position:$.jGrowl.defaults.position).appendTo('body');$('#jGrowl').jGrowl(m,o);};$.fn.jGrowl=function(m,o){if($.isFunction(this.each)){var args=arguments;return this.each(function(){var self=this;if($(this).data('jGrowl.instance')==undefined){$(this).data('jGrowl.instance',$.extend(new $.fn.jGrowl(),{notifications:[],element:null,interval:null}));$(this).data('jGrowl.instance').startup(this);}
if($.isFunction($(this).data('jGrowl.instance')[m])){$(this).data('jGrowl.instance')[m].apply($(this).data('jGrowl.instance'),$.makeArray(args).slice(1));}else{$(this).data('jGrowl.instance').create(m,o);}});};};$.extend($.fn.jGrowl.prototype,{defaults:{pool:0,header:'',group:'',sticky:false,position:'top-right',glue:'after',theme:'default',themeState:'highlight',corners:'10px',check:250,life:3000,closeDuration:'normal',openDuration:'normal',easing:'swing',closer:true,closeTemplate:'&times;',closerTemplate:'<div>Close All</div>',log:function(e,m,o){},beforeOpen:function(e,m,o){},afterOpen:function(e,m,o){},open:function(e,m,o){},beforeClose:function(e,m,o){},close:function(e,m,o){},animateOpen:{opacity:'show'},animateClose:{opacity:'hide'}},notifications:[],element:null,interval:null,create:function(message,o){var o=$.extend({},this.defaults,o);if(typeof o.speed!=='undefined'){o.openDuration=o.speed;o.closeDuration=o.speed;}
this.notifications.push({message:message,options:o});o.log.apply(this.element,[this.element,message,o]);},render:function(notification){var self=this;var message=notification.message;var o=notification.options;var notification=$('<div class="jGrowl-notification '+o.themeState+' ui-corner-all'+
((o.group!=undefined&&o.group!='')?' '+o.group:'')+'">'+'<div class="jGrowl-close">'+o.closeTemplate+'</div>'+'<div class="jGrowl-header">'+o.header+'</div>'+'<div class="jGrowl-message">'+message+'</div></div>').data("jGrowl",o).addClass(o.theme).children('div.jGrowl-close').bind("click.jGrowl",function(){$(this).parent().trigger('jGrowl.close');}).parent();$(notification).bind("mouseover.jGrowl",function(){$('div.jGrowl-notification',self.element).data("jGrowl.pause",true);}).bind("mouseout.jGrowl",function(){$('div.jGrowl-notification',self.element).data("jGrowl.pause",false);}).bind('jGrowl.beforeOpen',function(){if(o.beforeOpen.apply(notification,[notification,message,o,self.element])!=false){$(this).trigger('jGrowl.open');}}).bind('jGrowl.open',function(){if(o.open.apply(notification,[notification,message,o,self.element])!=false){if(o.glue=='after'){$('div.jGrowl-notification:last',self.element).after(notification);}else{$('div.jGrowl-notification:first',self.element).before(notification);}
//$(this).animate(o.animateOpen,o.openDuration,o.easing,function(){
$(this).slideDown(function(){
if($.browser.msie&&(parseInt($(this).css('opacity'),10)===1||parseInt($(this).css('opacity'),10)===0))
this.style.removeAttribute('filter');$(this).data("jGrowl").created=new Date();$(this).trigger('jGrowl.afterOpen');});}}).bind('jGrowl.afterOpen',function(){o.afterOpen.apply(notification,[notification,message,o,self.element]);}).bind('jGrowl.beforeClose',function(){if(o.beforeClose.apply(notification,[notification,message,o,self.element])!=false)
$(this).trigger('jGrowl.close');}).bind('jGrowl.close',function(){$(this).data('jGrowl.pause',true);
//$(this).animate(o.animateClose,o.closeDuration,o.easing,function(){
$(this).slideUp(function(){
$(this).remove();var close=o.close.apply(notification,[notification,message,o,self.element]);if($.isFunction(close))
close.apply(notification,[notification,message,o,self.element]);});}).trigger('jGrowl.beforeOpen');if(o.corners!=''&&$.fn.corner!=undefined)$(notification).corner(o.corners);if($('div.jGrowl-notification:parent',self.element).size()>1&&$('div.jGrowl-closer',self.element).size()==0&&this.defaults.closer!=false){$(this.defaults.closerTemplate).addClass('jGrowl-closer ui-state-highlight ui-corner-all').addClass(this.defaults.theme).appendTo(self.element).animate(this.defaults.animateOpen,this.defaults.speed,this.defaults.easing).bind("click.jGrowl",function(){$(this).siblings().trigger("jGrowl.beforeClose");if($.isFunction(self.defaults.closer)){self.defaults.closer.apply($(this).parent()[0],[$(this).parent()[0]]);}});};},update:function(){$(this.element).find('div.jGrowl-notification:parent').each(function(){if($(this).data("jGrowl")!=undefined&&$(this).data("jGrowl").created!=undefined&&($(this).data("jGrowl").created.getTime()+parseInt($(this).data("jGrowl").life))<(new Date()).getTime()&&$(this).data("jGrowl").sticky!=true&&($(this).data("jGrowl.pause")==undefined||$(this).data("jGrowl.pause")!=true)){$(this).trigger('jGrowl.beforeClose');}});if(this.notifications.length>0&&(this.defaults.pool==0||$(this.element).find('div.jGrowl-notification:parent').size()<this.defaults.pool))
this.render(this.notifications.shift());if($(this.element).find('div.jGrowl-notification:parent').size()<2){$(this.element).find('div.jGrowl-closer').animate(this.defaults.animateClose,this.defaults.speed,this.defaults.easing,function(){$(this).remove();});}},startup:function(e){this.element=$(e).addClass('jGrowl').append('<div class="jGrowl-notification"></div>');this.interval=setInterval(function(){$(e).data('jGrowl.instance').update();},parseInt(this.defaults.check));if($.browser.msie&&parseInt($.browser.version)<7&&!window["XMLHttpRequest"]){$(this.element).addClass('ie6');}},shutdown:function(){$(this.element).removeClass('jGrowl').find('div.jGrowl-notification').remove();clearInterval(this.interval);},close:function(){$(this.element).find('div.jGrowl-notification').each(function(){$(this).trigger('jGrowl.beforeClose');});}});$.jGrowl.defaults=$.fn.jGrowl.prototype.defaults;})(jQuery);