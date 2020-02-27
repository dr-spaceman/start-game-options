
$(document).ready(function(){
	
	$("input.ch-relalbum").click(function(){
		$(this).closest("tr").toggleClass("sel-relalbum-on");
		var aid = $(this).attr("id");
		$("#step2-"+aid).toggle();
	});
	
	var tooldata = [];
	
	if($("#input-title").length){
		$("#input-title").autocomplete({
			source:function(request, response){
				$.ajax({
					url: "/bin/php/autocomplete_var.php",
					data: { 'q':request.term, 'var':"games" },
					success: function(data){
						$("#input-title").removeClass("ui-autocomplete-loading");
						if(data.num_results == 0){
							$("#input-title").autocomplete("close");
							return false;
						}
						response($.map(data.results, function(item){
							return {
								label: item.title,
								value: item.title
							}
						}));
					}
				});
			},
			open: function(){ $(this).autocomplete("widget").width(250).css("max-height", "300px") }
		})
	}
	
	if($("#input-series").length){
		$.ajax({ url:"/bin/php/autocomplete.json.php", dataType:'json', data:"components=series", async:false,
		  success:function(data){
				$.each(data, function(key, val){
			    tooldata[key] = val;
			  });
			}
		});
		$("#input-series").autocomplete({
			source:tooldata['series'],
			open:function(){ $(this).autocomplete("widget").width(237).css("max-height", "300px") }
		});
	}
	
	if($("input.autocomplete-name").length){
		$("input.autocomplete-name").autocomplete({
			source:function(request, response){
				$.ajax({
					url: "/bin/php/autocomplete_var.php",
					data: { 'q':request.term, 'var':"people" },
					success: function(data){
						$("input.autocomplete-name").removeClass("ui-autocomplete-loading");
						if(data.num_results == 0){
							$("input.autocomplete-name").autocomplete("close");
							return false;
						}
						response($.map(data.results, function(item){
							return {
								label: item.title,
								value: item.title
							}
						}));
					}
				});
			},
			open: function(){ $(this).autocomplete("widget").width(250).css("max-height", "300px") }
		})
	}
	
});

function toggle(a, b){
	$("#"+a).show();
	if(b) $("#"+b).hide();
}

function foo() {
	asyncRequest(
		"post",
		"/ninadmin/albums_edit.php",
		function(response) {
			alert(response.responseText);
		},
		"do=foo"
	);
}

function addTrack() {
	var sbutton = document.getElementById('add-track-submit-button');
	var loading = document.getElementById('add-track-loading');
	var outp = document.getElementById('add-track-result');
	var albumid = document.getElementById('albumid').value;
	var disc = document.getElementById('input-disc').value.replace(/&/g, '[[AMP]]');
	var track_name = document.getElementById('input-track_name').value.replace(/&/g, '[[AMP]]');
	var track_number = document.getElementById('input-track_number').value;
	var artist = document.getElementById('input-artist').value.replace(/&/g, '[[AMP]]');
	var ttype = document.getElementById('input-type').value.replace(/&/g, '[[AMP]]');
	var tlocation = document.getElementById('input-location').value.replace(/&/g, '[[AMP]]');
	var ttime = document.getElementById('input-time').value;
	
	sbutton.value = "Adding...";
	sbutton.disabled = true;
	loading.style.display = 'inline';
	
	asyncRequest(
		"post",
		"/ninadmin/albums.php",
		function(response) {
			var resp = response.responseText;
			if(resp) {
				sbutton.value = "Add Track";
				sbutton.disabled = false;
				loading.style.display = 'none';
			}
			if(resp == "ok") {
				outp.innerHTML = '<i>'+track_name.replace("[[AMP]]", "&")+'</i> (track # '+track_number+') successfully added';
				document.getElementById('input-track_name').value = "";
				document.getElementById('input-track_number').value = (parseInt(document.getElementById('input-track_number').value) + 1);
				document.getElementById('select-artist').value = "";
				document.getElementById('input-artist').value = "";
				document.getElementById('select-type').value = "";
				document.getElementById('input-type').value = "";
				document.getElementById('input-location').value = "";
				document.getElementById('input-time').value = "";
			} else {
				outp.innerHTML = "<b>Error</b>: "+resp;
			}
		},
		"dbupdate=1&do=add_track&albumid="+albumid+"&disc="+disc+"&track_name="+track_name+"&track_number="+track_number+"&artist="+artist+"&type="+ttype+"&location="+tlocation+"&time="+ttime
	);
}

function addLink() {
	
	var lname = $("input[name='link_name']").val();
	var lurl = $("input[name='link_url']").val();
	
	if(lname == "") { alert("You must input a site name"); return; }
	if(lurl == "" || lurl == "http://") { alert("You must input a http:// URL"); return; }
	
	$("#nolinks").hide();
	$("#linkshere").append('<div style="margin:5px 0; font-size:15px;">'+lname+' <span style="color:#888;">&lt;</span> '+lurl+' <span style="color:#888;">&gt;</span> <a href="javascript:void(0);" style="color:#D22D2D;" onclick="$(this).parent().remove();">remove</a><div style="display:none;"><textarea name="in[links][name][]">'+lname+'</textarea><textarea name="in[links][url][]">'+lurl+'</textarea></div></div>');
	
	$("input[name='link_name']").val('');
	$("input[name='link_url']").val('http://');
	
}