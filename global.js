
var confirm_exit = false;
var confirm_exit_msg = "If you leave this page all your changes will be discarded.";
window.onbeforeunload = function(){
  if(confirm_exit) return confirm_exit_msg;
}

$(document).ready(function(){
	
	//arr: 0=title; 1=type; 2=url; 3=tag
	$("#topsearchin").
		focus(function(){ $('#topsearch').addClass('foc').prevUntil('.first-child').hide() }).
		blur(function(){ $('#topsearch').removeClass('foc').prevUntil('.first-child').show() }).
		autocomplete(
			"/bin/php/autocomplete.php",
			{ minChars:3,
				max:50,
				width:280,
				selectFirst:false,
				matchContains:true,
				formatItem:function(row) {
					return '<small>'+row[1]+"</small>"+row[0];
				}
			}
		).result(function(event, data, formatted){
			document.location=data[2];
		});
	
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
		$("#login-username").focus();
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
	
	$(".fftt .ff").each(function(){
		if( $(this).val() ) $(this).siblings(".tt").hide();
	}).live("focus", function(){
		$(this).siblings(".tt").addClass("foc");
	}).live("keydown", function(){
		$(this).siblings(".tt").removeClass("foc").addClass("off");
	}).live("blur", function(){
		if( $(this).val() == '' ){ $(this).siblings(".tt").removeClass("off").removeClass("foc"); }
		else $(this).siblings(".tt").addClass("off");
	});
	$(".fftt .tt").live("click", function(){
		$(this).siblings(".ff").focus();
	});
	
	$("input[type='button'], button, input[type='submit'], input[type='reset']").hover(function(){$(this).addClass("over")},function(){$(this).removeClass("over")})
	.mousedown(function(){$(this).addClass("down")}).mouseup(function(){$(this).removeClass("down")}).mouseout(function(){$(this).removeClass("down")});
	
	$(".preventdefault").click(function(Ev){Ev.preventDefault()});
	
	if( $("#notify").html() ) showNotifications();
	
	window.chbox = { isLoading:function(el){
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
	});
	
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
	
	//gameshelf
	if( $(".shelf").length ){
		
		$(".shelf dl").mouseenter(function(){
			var dt_height = $(this).children("dt").height();
			$(this).children("dt").fadeIn();//.animate({top:'10px', opacity:1});
		}).mouseleave(function(){
			$(this).children("dt").fadeOut();//animate({top:'5px', opacity:0}, function(){ $(this).hide().css({top:'15px'}) })
		});
		
		var gameshelf = {
			num_pubs: $(".shelf dl").size(),
			pubfd: 0,
			pubfs: '',
			pubfind: 3
		}
		$(".shelf .trav").click(function(ev){
			ev.preventDefault();
			if($(this).attr("href") == "#next"){
				gameshelf.pubfs = gameshelf.pubfd - 200;
				gameshelf.pubfind++;
				if(gameshelf.pubfind > gameshelf.num_pubs) {
					gameshelf.pubfs = 0;
					gameshelf.pubfind = 4;
				}
			} else {
				gameshelf.pubfs = gameshelf.pubfd + 200;
				gameshelf.pubfind--;
				if(gameshelf.pubfind < 4) {
					gameshelf.pubfs = 0 - ((gameshelf.num_pubs - 4) * 200);
					gameshelf.pubfind = gameshelf.num_pubs;
				}
			}
			gameshelf.pubfd = gameshelf.pubfs;
			$(".shelf .frame").animate({left:gameshelf.pubfs+"px"}, 200);
		});
	
	}
	
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
	
	//insert img
	window.img = {
		searchTagData:[],
		insert:function(){ //initiate upload image form
			$("body").append('<div id="insimg" class="imgframe"><div class="loading"></div></div>');
			$("#insimg").slideDown(function(){
				$(this).load(
				"/bin/php/imginsert.php",
				{"_action": "load_ins_form"},
				function(){
					$("#insimg-frame .frame").tinyscrollbar({axis:'x'})
				})
			})
		},
		loadForm:function(_frame, _key, _val){
			$("#insimg-"+_frame).html('<div class="loading"></div>').load("/bin/php/imginsert.php", {"_action":"load_form", "form_key":_key, "form_val":_val});
		},
		closeForm:function(){
			$('#insimg').slideUp(function(){$('#insimg').remove()});
		}
	}
	$("#insimg .nav li").live("click", function(){
		var frame = $(this).attr("id").replace("insimg-nav-", "");
		var frameOffset = "0px";
		if(frame == "upload") frameOffset = "140px";
		else if(frame == "search") frameOffset = "280px";
		$("#insimg-nav-"+frame).addClass("on").siblings().removeClass("on");
		$("#insimg-"+frame).slideDown().siblings().slideUp();
	})
	
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
			console.log(ev);
			if( ev.pathNames[0]=="posts" ){
				if(!$("#postsqueryparams").length) return;
				$("html, body").animate({scrollTop:$("#posts").offset().top}, 1000);
				if(!ev.queryString) return;
				$("html, body").addClass("loading");
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
						$("html, body").removeClass("loading");
						$("#posts").css("opacity", "1");
					}, "json"
				);
			}
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
		
	}
	
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
	
	//$(".postlist > dl").live("mouseenter mouseleave", function(ev){ $(this).toggleClass("nohov hov") });
	
	if($(".video-code").html() && $("#postform").html()){
		$(".video-code").hide();
	}
	
	//video: move sources to below video console
	var $videosources = $("#post-article.type-video .sources");
	if($videosources.length){
		$(".video-code").after($videosources);
	}
	
});


/** IMG **/

var lightbox = {
	activeNav:false, //track keydown for navigating
	'open':function(){
		lightbox.activeNav = true;
		$("body").append('<div id="lightbox" class="lightbox loading" style="position:fixed; top:0;"><div id="lightbox-label"></div><div class="close" onclick="lightbox.close()"><span>Close</span></div><div class="loading"></div></div><div id="lightbox-img" class="lightbox"><div class="container"></div></div><div id="lightbox-nav" class="lightbox"></div><div class="bodyoverlay lightbox"></div>');
		$("#lightbox-img").css("top", $("#lightbox").offset().top+"px");
		$(".lightbox").fadeIn();
	},
	'close':function(){
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
		if(rel.active + 1 <= rel.num){
			$([lightbox.getImgSrc($("a[rel='"+imgrel+"']:eq("+(rel.active + 1)+")"))]).preload();
		}
		
	},
	// lightbox.nav
	'nav':function(i, imgrel){
		if( $("#lightbox").hasClass("loading") ) return;
		var f = $("a[rel='"+imgrel+"']:eq("+i+")");
		if(!$(f).length){ alert("An navigation error ocurred!"); return; }
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
		var console = $(this).children(".shareconsole");
		$(console).slideDown();
		if( !$(console).text() ){
			var nid = $(this).attr("id").replace("share-", "");
			$(console).load("/bin/php/ajax.posts.php", {"load_share":nid, "desc":$("#nid-item-"+nid+" dt .description").text()});
		}
	}, closeConsole: function(){ //postShare.closeConsole()
		$(this).children(".shareconsole").slideUp();
	}
}

/** MISC **/

if(!window.console || !console.firebug){
    var names = ["log", "debug", "info", "warn", "error", "assert", "dir", "dirxml",
    "group", "groupEnd", "time", "timeEnd", "count", "trace", "profile", "profileEnd"];

    window.console = {};
    for (var i = 0; i < names.length; ++i)
        window.console[names[i]] = function() {}
}

function showNotifications() {
	$("#notify:hidden").slideDown(500).append('<a href="javascript:void(0);" style="position:absolute; right:15px; bottom:12px;" onclick="$(this).parent().slideUp(300);" onmouseover="$(this).children(\'span\').addClass(\'ximg-hover\');"><span class="ximg" style="margin:1px 0 0 -20px;">x</span><b>CLOSE THIS MESSAGE</b></a>');
}
	
var asyncRequest = function(){return;}();//depreciated

function htmlSC(what){
	what = what.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\"/g, '&quot;').replace(/\'/g, '&#039;');
	return what;
}

//preload images
//ie: $(['img1.jpg','img2.jpg','img3.jpg']).preload();
$.fn.preload = function(){
	this.each(function(){
		console.log("preload "+this);
		$('<img/>')[0].src = this;
	});
}
$(['/bin/img/loading_bar.gif']).preload();

function toggleTopnavItem(){ $(this).toggleClass("hov") }

/**
* hoverIntent r6 // 2011.02.26 // jQuery 1.5.1+
* <http://cherne.net/brian/resources/jquery.hoverIntent.html>
* 
* @param  f  onMouseOver function || An object with configuration options
* @param  g  onMouseOut function  || Nothing (use configuration options object)
* @author    Brian Cherne brian(at)cherne(dot)net
*/
(function($){$.fn.hoverIntent=function(f,g){var cfg={sensitivity:7,interval:85,timeout:0};cfg=$.extend(cfg,g?{over:f,out:g}:f);var cX,cY,pX,pY;var track=function(ev){cX=ev.pageX;cY=ev.pageY};var compare=function(ev,ob){ob.hoverIntent_t=clearTimeout(ob.hoverIntent_t);if((Math.abs(pX-cX)+Math.abs(pY-cY))<cfg.sensitivity){$(ob).unbind("mousemove",track);ob.hoverIntent_s=1;return cfg.over.apply(ob,[ev])}else{pX=cX;pY=cY;ob.hoverIntent_t=setTimeout(function(){compare(ev,ob)},cfg.interval)}};var delay=function(ev,ob){ob.hoverIntent_t=clearTimeout(ob.hoverIntent_t);ob.hoverIntent_s=0;return cfg.out.apply(ob,[ev])};var handleHover=function(e){var ev=jQuery.extend({},e);var ob=this;if(ob.hoverIntent_t){ob.hoverIntent_t=clearTimeout(ob.hoverIntent_t)}if(e.type=="mouseenter"){pX=ev.pageX;pY=ev.pageY;$(ob).bind("mousemove",track);if(ob.hoverIntent_s!=1){ob.hoverIntent_t=setTimeout(function(){compare(ev,ob)},cfg.interval)}}else{$(ob).unbind("mousemove",track);if(ob.hoverIntent_s==1){ob.hoverIntent_t=setTimeout(function(){delay(ev,ob)},cfg.timeout)}}};return this.bind('mouseenter',handleHover).bind('mouseleave',handleHover)}})(jQuery);