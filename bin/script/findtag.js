
$.getScript("/bin/script/jquery.fieldselection.js");

$(document).ready(function() {
	
	$("body").append('<div id="findtag-container"><form action="" onsubmit="return insFindTag()"><input type="hidden" id="findtag-field" value=""/><input type="text" id="findtag-inp"/><input type="submit"/></form></div>');
	
	$("#findtag-inp").autocomplete(
		"/bin/php/autocomplete.php",
		{ minChars:2,
			max:50,
			width:250,
			selectFirst:true,
			formatItem:function(row){
				return '<small>'+row[1]+"</small>"+row[0];
			}
		}
	).result(function(event, data, formatted){
		insFindTag(data[2]);
	});
	
	var lastKey;
	var tagPhrase = '';
	var openTag = 0;
	$(":input.findtag").keydown(function(ev){
		var key = ev.which; console.log(key+';'+lastKey);
		if(key === 221){ openTag=false; return; }
		if(openTag) return;
		if(key === 219 && lastKey === 219) openTag = true;
		else openTag = false;
		lastKey = key;
	}).keyup(function(){
		if(openTag){
			var pos = { "start": $(this).val().lastIndexOf('[['), "end" : $(this).selectionStart }
			var range = $(this).getSelection();
			tagPhrase = $(this).val().substr(($(this).val().lastIndexOf('[[') + 2), range.start);
			console.log("tagPhrase:"+tagPhrase);
		}
	});
	
});