
this.mousePosScroll = function(){
	$(".mouseposscroll").each(function(){
		var frame = $(this),
				container = $(frame).children(".mouseposscroll-container"),
		    sum = 0,
		    x = 0,
		    xOffset = $(frame).offset().left;
		/*console.log($(frame).width()+" x "+$(container).width());*/
		if($(frame).width() > $(container).width()) return;
		$(frame).css("overflow","hidden").
			/*mouseenter(function(e){
				x = -(((e.pageX - xOffset) / $(frame).width()) * ($(container).width() + parseInt($(container).css('paddingLeft')) + parseInt($(container).css('paddingRight')) - $(frame).width()));
				var coords = { var_x:x, _pageX:e.pageX, var_xOffset:xOffset, _frameWidth:$(frame).width(), _contWidth:$(container).width(), _contPaddLeft:parseInt($(container).css('paddingLeft')), contPaddRight:parseInt($(container).css('paddingRight')) }
				console.log(coords);
			}).*/
			mousemove(function(e){
				    x = -(((e.pageX - xOffset) / $(frame).width()) * ($(container).width() + parseInt($(container).css('paddingLeft')) + parseInt($(container).css('paddingRight')) - $(frame).width()));
				    $(container).css({
				        'marginLeft': x + 'px'
				    });
					})
	})
}

$(document).ready(function(){
	mousePosScroll();
});