
$(document).ready(function(){
	
	$("#pendingc tr").hover(function(){
		$(this).addClass("hov");
	}, function(){
		$(this).removeClass("hov");
	});
	
});

function massSubmitContr(){
	
	if( !$('#denych').val() ) {
		if(!confirm('Approve all checked?')) return false;
		else document.contrlist.submit();
	} else {
		if(!confirm('Deny all checked?')) return false;
		else document.contrlist.submit();
	}
	
}