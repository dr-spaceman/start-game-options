
//fix IE no Array.indexOf support
if(!Array.indexOf){
	    Array.prototype.indexOf = function(obj){
	        for(var i=0; i<this.length; i++){
	            if(this[i]==obj){
	                return i;
	            }
	        }
	        return -1;
	    }
	}

var imgids = new Array();
var thisn = 0;

$(document).ready(function(){
	
	$(".hrate").hover(function(){
		$(this).addClass("hov");
	}, function(){
		$(this).removeClass("hov");
	});
	$(".hrate a").click(function(ev) {
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
	
	$(".newslist > dl").hover(function(){
		$(this).removeClass("nohov").addClass("hov");
	}, function(){
		$(this).removeClass("hov").addClass("nohov");
	});
	
	$(".pagenav .ddown").hover(function(){
		$(this).addClass("on");
	}, function(){
		$(this).removeClass("on");
	});
	
	$(".newsnav .pgnav .expcon a").click(function(e){
		e.preventDefault();
		if( $(this).parent().hasClass("on") ) return;
		$(this).parent().addClass("on").siblings(".expcon").removeClass("on");
		$(".newslist dl.toggleclosed").toggleClass("closed");
	});
	
	//img gallery nav
	if($("#imgids").length) imgids = $("#imgids").val().split(",");
	
	if(window.location.hash.substr(0, 4) == "#img"){
		var req = window.location.hash.replace("#img", "");
		if(imgids.indexOf(req) > 0) loadPostGalleryImg(req);
	}
	
	$(".news-gallery .tn, #galleryspace a").click(function(ev){
		if(!$("#galleryspace").length) return;
		if(!imgids.length) return;
		
		var h = $(this).attr("href").indexOf("#");
		if(h < 0) return;
		var loadme = $(this).attr("href").substr(h).replace("#", "");
		loadPostGalleryImg(loadme);
	});
	
	if($(".video-code").html() && $("#postform").html()){
		$(".video-code").hide();
	}
	
	//video: move sources to below video console
	var $videosources = $("#news-article.type-video .sources");
	if($videosources.length){
		$(".video-code").after($videosources);
	}
	
});

function loadPostGalleryImg($id){
	
	$id = $id.replace("#", "").replace("img", "");
	
	thisn = imgids.indexOf($id);
	var $prev = thisn - 1;
	if($prev < 0) $prev = imgids.length - 1;
	var $next = thisn + 1;
	if($next >= imgids.length) $next = 0;
	
	$("#img"+$id).show().siblings().hide();
	
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