
var submit_true = false;

$(document).ready(function(){
	
	var dev_data = $.ajax({ url:"/bin/php/autocomplete_load.php", data:"what=developers", async:false }).responseText.split("|");
	$("#inp-dev").autocomplete(
		dev_data, 
		{ max:20, width:254, minChars:0, matchContains:true }
	).result(function(event, data, formatted) {
		AGinsert(formatted, 'dev', true);
	});
	
	var gen_data = $.ajax({ url:"/bin/php/autocomplete_load.php", data:"what=genres", async:false }).responseText.split("|");
	$("#inp-gen").autocomplete(
		gen_data, 
		{ max:20, width:254, minChars:0, matchContains:true }
	).result(function(event, data, formatted) {
		AGinsert(formatted, 'gen', true);
	});
	
	var ser_data = $.ajax({ url:"/bin/php/autocomplete_load.php", data:"what=series", async:false }).responseText.split("|");
	$("#inp-ser").autocomplete(
		ser_data, 
		{ max:20, width:254, minChars:0, matchContains:true }
	).result(function(event, data, formatted) {
		AGinsert(formatted, 'ser', true);
	});
	
});

var val;
var vals = Array();
var n;
/*function AGinsert(what, into) {
	
	if(!what) return;
	if(!into) return;
	$("#"+into).focus();
	val = $("#"+into).val();
	vals = val.split("\n");
	n = vals.length - 1;
	if(vals[n]) $("#"+into).val(val+"\n"+what);
	else $("#"+into).val(val+what);
	
}*/

function AGinsert(what, into, indb) {
	
	if(!what) return;
	if(!into) return;
	what = htmlSC(what);
	val = $("#insert-"+into+" textarea").val();
	$("#insert-"+into+" textarea").val(val+what+"\n");
	$("#insert-"+into+"-no").remove();
	$("#insert-"+into).append('<p><span>'+what+(!indb ? ' <span class="tooltip" title="This value is not yet listed in the database. Please make sure it isn\'t yet listed under another similar name." style="color:#D92626;">[<span style="text-decoration:underline;">Not in the Database</span>]</span>' : '')+'</span> <a href="javascript:void(0);" class="x" onclick="AGremove($(this).prev().text(), \''+into+'\'); $(this).parent().remove();">x</a></p>');
	$("#inp-"+into).val('');
	
}

function AGremove(what, into) {
	
	if(!what) return;
	if(!into) return;
	what = htmlSC(what);
	val = $("#insert-"+into+" textarea").val().replace(what+"\n", "");
	$("#insert-"+into+" textarea").val(val);
	
}

/*function addToMyGames(n, x) {
	
	var a = document.getElementById('addtomygames');
	var b = document.getElementById('addtomygames-button-'+n);
	b.value = 'Added to My Games';
	b.disabled = 'true'; 
	if(a.innerHTML) a.innerHTML = a.innerHTML+'``'+x;
	else a.innerHTML = x;
	
}*/

function AGFchecktitle() {
	
	var gtitle = document.getElementById('agf-inp-title').value;
	if(!gtitle) return;
	
	toggle('agf-title-loading','agf-title-button','inline');
	
	asyncRequest(
		"post",
		"/games/add.php",
		function(response) {
			var t = '';
			if(t=response.responseText) {
				if(t == "ok") {
					toggle('agf-details-form', 'agf-title-form');
					$('.put-agf-gametitle').html(gtitle);
					document.getElementById('agf-rec-title').value=gtitle;
					//var fletter = gtitle.substr(0,1);
					//$("#link-to-gameindex").attr("href","/games/"+fletter);
				} else {
					toggle('agf-title-button','agf-title-loading','inline');
					toggle('agf-title-exists','');
					document.getElementById('agf-title-exists').innerHTML=t;
				}
			}
		},
		"action=check title&title="+gtitle.replace(/&/g, '[AMP]')
	);
	
}
