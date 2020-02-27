$(document).ready(function(){
	
	$("#index table table tr").hover(
		function(){
			$(this).addClass("hov");
		},
		function(){
			$(this).removeClass("hov");
		}
	);
	
});