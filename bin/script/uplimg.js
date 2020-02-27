
$(document).ready(function(){
	
	$('#file_upload').uploadify({
		    'uploader'  : '/uploadify_/uploadify.swf',
		    'script'    : '/uploadify_/uploadify.php',
		    'cancelImg' : '/uploadify_/cancel.png',
		    'auto'      : false,
		    'fileExt'   : '*.jpg;*.gif;*.png',
		    'multi'     : true,
		    'scriptData' : {
		    	'_action':'submimg',
		    	'sessid':'123'
		    },
		    'onAllComplete' : function(event,data){
		    	alert('fin.');
		    }
		  });
	
	$("#uplimginpfile").live("change", function(){
		var inp = document.getElementById('uplimginpfile');
		for(var x = 0; x < inp.files.length; x++){
			uplQ(inp.files[x]);
		}
	});
		
});