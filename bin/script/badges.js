
$(document).ready(function(){
	
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
	
});

$.fn.preload = function() {
	this.each(function(){
		$('<img/>')[0].src = this;
	});
}