
var pgid    = "";
var pgtitle = "";

$(document).ready(function(){
	
	$("body").addClass("viewmode");
	
	pgtitle = $(":input[name='pgtitle']").val();
	
	$("#pghead").hover(function(){
		$(this).addClass("on").removeClass("off");
	}, function(){
		$(this).addClass("off").removeClass("on");
	});
	
	$("#pgcontnav a").live("click", function(ev){
		ev.preventDefault();
		$(this).addClass("bodybg").parent().addClass("on").siblings().removeClass("on").children().removeClass("bodybg");
		var pgcontsec = $(this).attr("href").replace("#", "");
		$("#"+pgcontsec).show().siblings(".toggle").hide();
	});
	
	$("#pgcont .editme").hover(function(){$(this).toggleClass("hov")});
	
	$(".watchpage").click(function(){
		if(!pgtitle) { $("#alert").fadeIn().children("DL").html('<dt>Error</dt><dd>No page title found</dd>'); return; }
		var chboxsp = $(this);
		if(chbox.isLoading(chboxsp)) return;
		$.post(
			"/pages/edit_ajax.php",
			{ _do: "watch_page",
				_pgtitle: pgtitle
			}, function(res){
				if(res.error) {
					$("#alert > DL").html('<dt>Error</dt><dd>'+res.error+'</dd>');
					$("#alert").fadeIn();
					$(chboxsp).toggleClass("checked");
				} else if(res.success) {
					$("#alert > DL").html('<dt>Page Added</dt><dd>'+res.success+'</dd>');
					$("#alert").fadeIn().delay(2500).fadeOut();
				}
				$(chboxsp).animate({opacity:1}, 300, function(){ $(this).removeClass("loading") });
			}, "json"
		);
	});
	
	var pubpos;
	//reposition publication labels if too high
	$("#publications dl dd").each(function(){
		pubpos = $(this).position();
		if(pubpos.top < 0) $(this).css("bottom","auto").css("top", "0");
	});
	var posbot;
	$("#publications dl").hover(function(){
		$(this).children("dd").animate({opacity:.9}, 400);
	}, function(){
		$(this).children("dd").animate({opacity:0}, 400);
	});
	
	var num_pubs = $("#publications dl").size();
	var pubfd = 0;
	var pubfs = "";
	var pubfind = 4;
	$("#publications .trav").click(function(v){
		v.preventDefault();
		if($(this).attr("href") == "#next") {
			pubfs = pubfd - 200;
			pubfind++;
			if(pubfind > num_pubs) {
				pubfs = 0;
				pubfind = 4;
			}
		} else {
			pubfs = pubfd + 200;
			pubfind--;
			if(pubfind < 4) {
				pubfs = 0 - ((num_pubs - 4) * 200);
				pubfind = num_pubs;
			}
		}
		$("#pubframe").animate({left:pubfs+"px"}, 200, function(){ pubfd = pubfs; });
	});
	
	if(num_pubs){
		var h1pos = $("#pghead h1").offset();
		h1pos.left = h1pos.left - 45;
		$("#pubframe").css("padding-left", h1pos.left+"px");
	}
	
	/*var posel;
	if( $("#pgcont p:eq(3)").length ){
		posel = $("#pgcont p:eq(1)");
		if( !$("#pgcont p:eq(1)").html() ){
			posel = $(posel).next();
		}
		if( $("#pgcont h5:eq(1)").length ){
			posel = $("#pgcont h5:eq(0)");
		}
		var linepos = $(posel).position();
		
		if( $(posel).is('p') ) linepos.top = linepos.top - 41;
		else if( $(posel).is('h5') ) linepos.top = linepos.top + 28;
			
		var sblogs = $("#sblogposts").removeClass("liquid3").addClass("liquidrcol").clone();
		$("#sblogposts").remove();
		$("#pgcont").before($(sblogs));
		$("#sblogposts").prev().css("height", linepos.top+"px");
	}*/
	
	/*$("#credits th").click(function(){
		
		//remove rowspans so we can sort it correctly
		$("#credits table tbody.span td").removeAttr("rowspan").removeClass("spanned");
                
    var header = $(this),
        i = $(this).attr("id"),
        inverse = false;//alert(i);
        
    header
        .closest('table')
        .find("td."+i)
        .sort(function(a, b){
            
            a = $(a).text();
            b = $(b).text();
            
            return (
                isNaN(a) || isNaN(b) ?
                    a > b : +a > +b
                ) ?
                    inverse ? -1 : 1 :
                    inverse ? 1 : -1;
                
        }, function(){
        		var ret = this.parentNode;
        		return ret;
        });
    
    inverse = !inverse;
    
	});*/
	
	var crel = '';
	$(".pgt-person #credits .release").each(function(){
		if( $(this).text() == crel ) $(this).parent().addClass("semiclear");
		crel = $(this).text();
	});
	
	/*$(".pgt-person tr").hover(function(){
		$(this).addClass("hov");
		$(this).nextUntil(".clear").addClass("hov");
	}, function(){
		$(this).removeClass("hov");
		$(this).nextUntil(".clear").removeClass("hov");
	});*/
	
	$("#links DL").hover(function(){
		$(this).addClass("hov");
	}, function(){
		$(this).removeClass("hov");
	});
	
	$("#pgops .op").click(function(v){
		v.preventDefault();
		var $el = $(this);
		$el.addClass("loading");
		$.post(
			"/pages/pgop.php",
			{ handler:$(this).attr("href").replace("#", '') },
			function(res){
				$el.animate({opacity:1}, 200, function(){
					alert("That feature is coming soon!");
					$el.removeClass("loading")
					if($el.hasClass("on")) $el.removeClass("on");
					else $el.addClass("on");
				});
			}
		);
	});
	
	var num_arms = $("#armedia ul li").size(); //count imgs
	var armpos = 0; //current css left position
	var armfind = 0; //current pos of 1st img
	$("#armedia").hover(
		function(){ $(this).addClass("hov"); },
		function(){ $(this).removeClass("hov"); }
	);
	$("#armedia .nav a").click(function(v){
		v.preventDefault();
		if($(this).attr("href") == "#next") {
			armfind++;
			if(armfind > (num_arms - 3)) {
				armfind = 0;
			}
		} else {
			armfind--;
			if(armfind < 0) {
				armfind = num_arms - 3;
			}
		}
		armpos = armfind * 200;
		$("#armedia ul").animate({left:"-"+armpos+"px"}, 200);
	});

});