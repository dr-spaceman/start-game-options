
$(document).ready(function(){
	
	if( $("#genres .more").length ) {
		$("#genres").hover(function(){
			$(this).addClass("genres-more");
			$('<a id="genres-dummy" class="more">'+ $("#genres .more").html() +'</a>').insertAfter("#genres");
		}, function(){
			$(this).removeClass("genres-more");
			$("#genres-dummy").remove();
		});
	}
	
});