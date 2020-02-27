
$(document).ready(function(){
	
	if($("#imggallerynav").length){
		
		$.address.change(function(ev){
			if(!ev.queryString) return;
			loading.on();
			$("#imggallerycontainer").animate({opacity:.5}, "fast");
			$.get(
				"/imagegallery.incl.php",
				{ "load":ev.queryString+'&'+$("#settype").val()+'='+$("#setid").val() },
				function(res){
					if(res.errors) for(var i = 0; i < res.errors.length; i++) $.jGrowl(res.errors[i]);
					if(res.formatted) $("#imggallery").html(res.formatted);
					if(res.nav) $("#imggallerynav h6").html(res.nav);
					if(res.pagination) $("#imggallerynav ul.pagination").html(res.pagination);
					tooltip();
					loading.off();
					$('html, body').animate({scrollTop:0 }, "fast");
					$("#imggallerycontainer").animate({opacity:1}, "fast");
				}
			)
		}).strict(false);
		
		$("#imggallerynav .viewmode").click(function(ev){
			ev.preventDefault();
	  	if($(this).hasClass("on")) return;
	  	$(this).addClass("on").siblings().removeClass("on");
	  	$("#imggallery").removeClass("viewmode-tn viewmode-sm").addClass($(this).attr("id"));
	  	$('html, body').animate({scrollTop:0 }, "fast");
	  });
	  
	  $(".pgn").live("click", function(ev){
	  	ev.preventDefault();
	  	$("#pgn").val($(this).attr("rel"));
	  });
		
		$(".igch").live("click", function(){
			if(loading.loading) return false;
			if(!$(this).hasClass("pgn")) $("#pgn").val("1"); //reset to the first page
			$.address.value("?" + $("#imggallerynav").serialize());
		});
		
		//scrolling sidebar
		var $sidebar   = $("#imggallerynav"),
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
  
});