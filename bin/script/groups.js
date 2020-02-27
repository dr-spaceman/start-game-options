
$(document).ready(function(){
	
	$("#grouppage #options input").click(function() {
		$(this).hide().prev().show();
		var th = $(this);
		var val = '0';
		if( $(this).is(":checked") ) val = 1;
		$.ajax({
			type: "POST",
			url: "/groups-ajax.php",
			data: "do=update option&option="+$(this).attr("name")+"&val="+val+"&group_id="+$(this).val(),
			success: function(msg){
				if(msg != "ok") alert(msg);
				th.show().prev().hide();
			}
		});
	});
	
	$("#groupmng dt a").click(function(e) {
		e.preventDefault();
		$(this).toggleClass("on").parent().next().slideToggle();
	});
	
	$(".tag-selector").click(function(e) {
		e.preventDefault();
		$(this).toggleClass("arrow-toggle-on");
		var what = $(this).attr("rel");
		$("#tagsel-"+what).toggle();
	});
	
	$(".invite-link").click(function() {
		$(this).toggleClass("arrow-toggle-on");
		$("#invite").slideToggle();
	});
	
});

function checkMembersForm() {
	var ch = false;
	$("input[@name='managers[]']").each( function() {
		if( $(this).is(":checked") ) ch = true;
	});
	if(ch) return true;
	else {
		alert("Please select at least one manager");
		return false;
	}
}
