

var img = {
	insert:function(){
		
		//initiate upload image form
		
		$("#bodywrap").before('<div id="insimg" class="imgframe"><div class="loading" style="margin:65px 0 0 420px;"><img src="/bin/img/loading_bar.gif" alt="Loading"/></div></div>');
		$("#insimg").slideDown(function(){ $(this).load("/bin/php/class.img.php", {"_action": "load_ins_form"}) });
		
	},
	loadForm:function(_key, _val){
		
		$("#insimg").html('<div class="loading"></div>').load("/bin/php/class.img.php", {"_action":"load_ins_form", "form_key":_key, "form_val":_val});
		
	},
	closeForm:function(){
		$('#insimg').slideUp(function(){$('#insimg').remove()});
	}
}

var lightbox = {
	open:function(){
		$("body").append('<div id="lightbox" class="bodyoverlay loading"><div id="lightbox-cont"><div class="container"></div></div><div id="lightbox-label"></div><div class="close" onclick="lightbox.close()"><span>Close</span></div><div class="loading"><img src="/bin/img/loading_bar.gif" alt="loading" width="120" height="10"/></div></div>');
		$("#lightbox").fadeIn()
	},
	close:function(){
		$("#lightbox, .lightbox-fullimg").fadeOut(function(){$(this).remove()});
	},
	load:function(imguplfile){
		console.log("load lightbox: "+imguplfile);
		var x_max = $("#lightbox").width() - 200,
				y_max = $("#lightbox").height() - 40;
		if(!$("#lightbox").length) return;
		$.get(
			"/bin/php/ajax.img.php",
			{ load_img_data:imguplfile, x_max:x_max, y_max:y_max },
			function(res){
				$("#lightbox").removeClass("loading");
				if(res.errors){
					for(var i = 0; i < res.errors.length; i++){
						$.jGrowl(res.errors[i]);
					}
				}
				if(res.img){
					$("#lightbox-cont .container").html(res.img);
				}
				if(res.img_full){
					$("#lightbox").after(res.img_full);
					$("#lightbox").next().css({"margin-left": "-"+(res.img_width / 2)+"px", "top": $("#lightbox").offset().top+"px"});
				}
				if(res.label) $("#lightbox-label").html(res.label);
			}, "json"
		)
	},
	toggleScaled:function(show){
		$("#lightbox, .lightbox-fullimg").toggle();
		/*if(show == "full"){
			$("#lightbox").hide().next().show();*/
	}
}

$(document).ready(function(){
	
	$("a.imgupl").click(function(ev){
		ev.preventDefault();
		var imguplfile = $(this).attr("href").replace("/image/", "");
		lightbox.open();
		//$("#lightbox-cont .container").html($(this).children("img").clone());
		lightbox.load(imguplfile);
	})
	
});