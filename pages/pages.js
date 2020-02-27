
var pgid    = "";
var pgtitle = "";

$(document).ready(function(){
	
	$("body").addClass("viewmode");
	
	pgtitle = $("#pgtitle").val();
	
	//if( $("#bodybgimg").length ){ $("#bodybgimg").css("height", $("body").height()+"px") }
	
	$("#pghead").hover(function(){
		$(this).addClass("on").removeClass("off");
	}, function(){
		$(this).addClass("off").removeClass("on");
	});
	
	$("#watchpages").on("click", ".watchpage", function(){
		$el = $(this);
		if($el.hasClass("loading")) return;
		$el.addClass("loading");
		$.post(
			"/pages/edit_ajax.php",
			{ _do: "watch_page",
				_pgtitle: $(this).val()
			}, function(res){
				$el.animate({opacity:1}, 300, function(){ $el.removeClass("loading") });
				if(res.errors) for(var i = 0; i < res.errors.length; i++) $.jGrowl(res.errors[i]);
				if(res.added) $.jGrowl(res.added);
			}, "json"
		);
	});
	
	if( $(".shelf-container").length ){
		shelf.newpos = $("#pghead").offset().left - 15;
		$(".shelf-container").css({"padding-left": shelf.newpos+"px"});
	}
	
	$("#synopsis .categories ul li:last-child").addClass("last-child").after('<li class="clear"></li>');
	
	var crel = '';
	$(".pgt-person #credits .release").each(function(){
		if( $(this).text() == crel ) $(this).parent().addClass("semiclear");
		crel = $(this).text();
	});
	
	$("#links DL").hover(function(){
		$(this).addClass("hov");
	}, function(){
		$(this).removeClass("hov");
	});
	
	$("html").click(function(){ $("#pgops .form:visible").fadeOut(); /* if collection not added, rmclass on; */ }); // close the love/hate prompt if click outside dialog
	$("#pgops").click(function(event){ event.stopPropagation(); });
	$("#pgops").on("click", ".op", function(event){
		event.preventDefault();
		
		var $el = $(this),
		    $op = $el.data("op"),
		    $act = $el.hasClass("on") ? 'edit' : 'add';
		console.log("click op "+$op);
		
		if($el.hasClass("loading")) return;
		
		$(this).siblings(".form").toggle().children("textarea").focus();
		$(this).closest("li").siblings().find(".form").hide();
		
		if($op == "collection"){
			if($("#pgop-form-collection > .container").html()) return;
			$act = "add";
		}
		if($act == "edit") return;
		
		if(!$("#usrid").val()){
			login.init();
			return;
		}
		
		/*if($op == "collection"){
			if($el.hasClass("on")) return;
			fbAddPlay();
			$el.addClass("loading").animate({opacity:1}, 500, function(){ $el.removeClass("loading").addClass("on") });
			return;
		}*/
		
		$el.addClass("loading");
		
		$.get(
			"/pages/pgop.php",
			{ 'action':$act, 'op':$op, 'title':$("#pgtitle").val() },
			function(res){
				//$el.animate({opacity:1}, 200, function(){
					$el.removeClass("loading");
					if(res.errors) for(var i = 0; i < res.errors.length; i++) $.jGrowl(res.errors[i]);
					if(res.success){
						if($act == "rm") $el.removeClass("on");
						else $el.addClass("on");
					}
					if(res.formatted){
						$el.addClass("on");
						$("#pgop-form-"+$op).fadeIn().children(".container").html(res.formatted);
					}
				//});
			}
		)
	});
	$("#pgops").on("click", ".opform button", function(){
		// submit comments
		var $el=$(this), $op=$(this).data("op"), $remarks=$el.siblings("textarea").val();
		$el.attr("disabled", true).parent().addClass("loading");
		$.get(
			"/pages/pgop.php",
			{ 'action':'edit', 'op':$op, 'title':$("#pgtitle").val(), 'remarks':$remarks },
			function(res){
				if(res.error) $.jGrowl(res.error);
				if(res.success) $el.animate({"opacity":1}, 500, function(){ $el.attr("disabled", false).parent().removeClass("loading").fadeOut(500) });
			}
		);
		if($remarks) fbFan($op, $remarks);
	}).on("click", ".opform .unfan", function(){
		var $el=$(this);
		$el.closest(".form").fadeOut();
		$(".op[data-op='"+$el.data("op")+"']").addClass("loading")
		$.get(
			"/pages/pgop.php",
			{ 'action':'rm', 'op':$el.data("op"), 'title':$("#pgtitle").val() },
			function(res){
				if(res.error) $.jGrowl(res.error);
				if(res.success) $(".op[data-op='"+$el.data("op")+"']").removeClass("loading").removeClass("on");
			}
		);
	});
	
	$("#pgops .opform textarea").keydown(function(e){
		if($(this).val().length == 140) alert("Your remarks are limited to 140 characters");
		if(e.which==13) $(this).siblings("button").click();
	})
	
	var num_arms = $("#armedia ul li").size(); //count imgs
	var armpos = 0; //current css left position
	var armfind = 0; //current pos of 1st img
	$("#armedia").hover(
		function(){ $(this).addClass("hov"); },
		function(){ $(this).removeClass("hov"); }
	);
	$("#armedia .nav a").click(function(v){
		v.preventDefault();
		if($(this).attr("href") == "#next") {
			armfind++;
			if(armfind > (num_arms - 3)) {
				armfind = 0;
			}
		} else {
			armfind--;
			if(armfind < 0) {
				armfind = num_arms - 3;
			}
		}
		armpos = armfind * 200;
		$("#armedia ul").animate({left:"-"+armpos+"px"}, 200);
	});

});

var pgcont = {
	toggle:function(sec){
		$("#pgcontnav-"+sec).addClass("on").siblings().removeClass("on");
		$("#"+sec).show().siblings(".toggle").hide();
		if(sec=="synopsis") $("#repimg").slideDown();
		else if($("#repimg").is(":visible")){
			$("#repimg").slideUp();
			$("html, body").animate({scrollTop:$("#pgcont").offset().top}, 500);
		}
	}
};