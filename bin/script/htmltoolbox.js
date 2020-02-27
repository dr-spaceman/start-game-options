

function toolboxInsert(open, close, msgfield) {
	var xfield = document.getElementById(msgfield);
	if(!open && !close) return;
	if(open == '<a href="">') {
		var ahref = prompt("URL of the link", "http://");
		open = '<a href="'+ahref+'">';
	}
	if(open == '[url=]') {
		var ahref = prompt("URL of the link", "http://");
		var desc  = prompt("description of the link", "");
		if(desc) open = '[url='+ahref+']'+desc;
		else open = '[url]'+ahref;
	}
	if(open == '[cite=]') {
		var desc  = prompt("Source name", "");
		var ahref = prompt("URL of the source (if applicable)", "http://");
		if(ahref && ahref != "http://") open = '[cite='+ahref+']'+desc;
		else open = '[cite]'+desc;
	}
	if(open == '[img]') {
		return
	}
	if(open == '<!--emoticon:') {
		return;
	}
	if(open) open = open.replace(/NL/g, "\n");
	if(close) close = close.replace(/NL/g, "\n");
  if (document.selection && document.selection.createRange) {
      xfield.focus();
      sel = document.selection.createRange();
      sel.text = open + sel.text + close;
      xfield.focus();
  } else if (xfield.selectionStart || xfield.selectionStart == "0") {
      var startPos = xfield.selectionStart;
      var endPos = xfield.selectionEnd;
      xfield.value = xfield.value.substring(0, startPos) + open + xfield.value.substring(startPos, endPos) + close + xfield.value.substring(endPos, xfield.value.length);
      xfield.selectionStart = xfield.selectionEnd = endPos + open.length + close.length;
      xfield.focus();
  } else {
      xfield.value += open + close;
      xfield.focus();
  }
  return false;
}

function loadEmotes(itr, field) {
	var emsp = $("#emoticon-space-"+itr);
	if( $(emsp).html() ) {
		$(emsp).hide().html('');
		return;
	}
	$(emsp).show().html('&nbsp;Loading emoticons...').load("/bin/php/htmltoolbox.php", { _action:"load_emoticons", _field:field });
}

function TBimgGen(itr, field) {
	var imgsp = $("#imggen-space-"+itr);
	if( $(imgsp).html() ) {
		$(imgsp).hide().html('');
		return;
	}
	$(imgsp).show().html('<img src="/bin/img/loading...gif" alt="loading..."/>').load("/bin/php/htmltoolbox.php", { 'imggen_form':'1', 'field':field });
}

function TBloadMediaDir(space, dir, field, pg) {
	$(space).load("/bin/php/htmltoolbox.php", {'load_media_dir':dir, 'field':field, 'pg':pg});
}

this.TBinit = function() {
	
	//read input fields like <input class="toolbox [bbcode]" rel="b,i,big,small,img"/>
	$(":input.toolbox").each(function(){
		var field = $(this).attr("id");
		$.post(
			"/bin/php/htmltoolbox.php",
			{ "_action": "init",
				"_field": field,
				"_include": $(this).attr("rel"),
				"_bbcode": $(this).hasClass("bbcode")
			}, function(ret){
				$("#"+field).before(ret);
			}
		)
	});
	
	$(".htmltools a.insert").live("click", function(e) {
		e.preventDefault();
		var xfield = $(this).closest(".htmltools").children("input[@name='htmltools-field']").val();
		var str = $(this).attr("rel");
		var arr = str.split(",");
		if( $(this).parents(".bbcode").length ) {
			var tago = arr[2];
			if(arr[3]) var tagc = arr[3];
		} else {
			var tago = arr[0];
			if(arr[1]) var tagc = arr[1];
		}
		toolboxInsert(tago, tagc, xfield);
	});
	
	var xfield;
	if($(".linkgen input[type=text]").length){
		$(".linkgen input[type=text]").focus(function(){
			$(this).removeClass("resetonfocus").val('');
		}).autocomplete({
			minLength:3,
			source:function(request, response){
				$.ajax({
					url: "/bin/php/autocomplete.php",
					data: { q:request.term },
					success: function(data){
						response($.map( data.results, function(item){
							return { label:item.title, value:item.title, category:item.category, tag:item.tag }
						}));
					}
				});
			},
			open:function(){ $(this).autocomplete("widget").width($(this).outerWidth()).css("max-height", "300px") },
			select:function(event, ui){
				xfield = $(this).parents(".htmltools").children("input[name='htmltools-field']").val();
				toolboxInsert('[['+(ui.item.tag ? ui.item.tag : ui.item.value)+']]', '', xfield);
				$(this).val('');
				return false;
			}
		}).data( "autocomplete" )._renderItem = function(ul, item){
			return $( '<li></li>' )
				.data("item.autocomplete", item)
				.append('<a><small>'+item.category+'</small><dfn>'+item.label+'</dfn></a>')
				.appendTo( ul );
		}
	}
	
	$(".linkgen-typesel").change(function(){
		
		var what = $(this).val();
		$(this).val("");
		var f1 = $(this).parent(".linkgen1");
		var f2 = $(this).parent(".linkgen1");
		$(f1).hide();
		$(f2).show();
		$(f2).children("span.space").html('<img src="/bin/img/loading-thickbox.gif" alt="loading"/>');
		
		asyncRequest(
			"post",
			"/bin/php/htmltoolbox.php",
			function(response) {
				if(response.responseText) {
					$(f2).children("span.space").html(response.responseText);
				}
			},
			"genlink="+what+"&field="+$(this).parents(".htmltools").children("input[@name='htmltools-field']").val()
		);
		
	});
	
	$(".linkgen-toggleback").click(function(){
		$(this).parent(".linkgen2").hide();
		$(this).parent(".linkgen2").siblings(".linkgen1").show();
	});
	$(".linkgen-selected").change(function(){
		alert(';');
		$(this).parents(".linkgen2").hide();
		$(this).parents(".linkgen2").siblings(".linkgen1").show();
	});
	
};

$(document).ready( TBinit );