
$(document).ready(function(){
	
	$("#tagspace .close").hover(
		function(){
			$(this).css("cursor", "pointer").children('a').addClass("hov");
		}, function(){
			$(this).css("cursor", "").children('a').removeClass("hov");
		}
	).click(function(){
		$("#tagspace").fadeOut();
	});
	
	var swto = "";
	$("#tagspace .nav a").click(function(Ev){
		$(this).addClass("on").siblings().removeClass("on");
		swto = $(this).children("span").html();
		$("#tagspace .tag-space-"+swto).show().siblings('.space2').hide();
	});
	
	$("#tags .tagcont").hover(
		function(){
			$(this).children(".rm").show();
		}, function(){
			$(this).children(".rm").hide();
		}
	);
	
});

var db_data_loaded = false;
function suggestNewTag(_cont, _id) {
	$("body, a[href='#suggest_new_tag']").css("cursor", "wait").animate({ opacity:1 }, 500, function(){
		$("#tag-free").val('');
		$("#tagspace").fadeIn();
		if(!db_data_loaded) {
			db_data_loaded = true;
			//game data
			var gdata = $.ajax({ url:"/bin/php/autocomplete_load.php", data:"what=games", async:false }).responseText.split("|");
			$("#tag-q-game").autocomplete(
				gdata, {
					matchContains:true, 
					formatItem:function(row) {
						var dat = row[0].split("`");
						return dat[0];
					}
				}
			).result(function(_event, _data, formatted) {
				var dat = _data[0].split("`");
				submitNewTag(dat[3], dat[0], _cont, _id);
				return;
			});
			//people data
			var pdata = $.ajax({ url:"/bin/php/autocomplete_load.php", data:"what=people", async:false }).responseText.split("|");
			$("#tag-q-person").autocomplete(
				pdata, {
					matchContains:true, 
					formatItem:function(row) {
						var dat = row[0].split("`");
						return dat[0];
					}
				}
			).result(function(_event, _data, formatted) {
				var dat = _data[0].split("`");
				submitNewTag(dat[3], dat[0], _cont, _id);
				return;
			});
			//freetag data
			var pdata = $.ajax({ type:"POST", url:"/bin/php/tag.php", data:"_action=load_freetags", async:false, success:function(){ $("body, a[href='#suggest_new_tag']").css("cursor", ""); } }).responseText.split("``");
			$("#tag-free").autocomplete(
				pdata, {
					selectFirst:false,
					matchContains:true, 
					formatItem:function(row) {
						return row[0];
					}
				}
			).result(function(_event, _data, formatted) {
				submitNewTag(_data[0], _data[0], _cont, _id);
				return;
			});
		} else {
			$("body, a[href='#suggest_new_tag']").css("cursor", "");
		}
	});
}

var newtagnum = 0;
function submitNewTag(_tag, _label, _cont, _id) {
	_tag = _tag.replace(/\//g, "|").replace(/\\/g, "|"); //don't allow slashes
	if(_cont == "post") {
		//news/blog/content post
		$("#tag-loading").show();
		$.post(
			"/bin/php/tag.php",
			{ _action: "add_post_tag",
				_nid: _id,
				_tag: _tag
			}, function(txt) {
				$("#tag-loading").hide();
				if(txt) alert(txt);
				else {
					$("#tagspace").fadeOut().animate({ opacity: 1 }, 2000);
					newtagnum++;
					$("#newtagshere").append('<a href="../../../topics/'+escape(_tag)+'" id="newtag-'+newtagnum+'">'+_label+'</a> &middot; ');
					$("#newtag-"+newtagnum).livequery(function(){
						$(this).animate({ opacity:1 }, 500, function(){
							$(this).animate({ opacity:0 }, 500, function(){
								$(this).animate({ opacity:1 }, 1000);
							})
						})
					});
				}
			}
		);
	}
}

function loadTagSelectList(what) {
	var space = $("#tagspace .tag-space-"+what+"-select");
	var sel = $(space).children("select");
	$(space).show().siblings('.space2').hide();
	if( !$(sel).html() ) $(sel).html('<option value="">Loading selection...</option>').load("/bin/php/tag.php", { _action:"load_sellist", _list:what });
}

function removeTag(table, tid) {
	if(confirm("Permantly remove this tag?")) {
		$.post(
			"/bin/php/tag.php",
			{ _action:"remove_tag", _table:table, _tag_id:tid },
			function(txt) {
				if(txt) {
					alert(txt);
				} else {
					$("#tagcont-"+tid).hide();
				}
			}
		);
	}
}