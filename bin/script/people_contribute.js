
$(document).ready(function(){
	
	$("#contribute-message").fadeIn(1000);
	
	$("#contribute-message").hover(function() {
		$(this).addClass("hov");
	}, function() {
		$(this).removeClass("hov");
	}).click(function() {
		if( $("#dontshowcm").is(":checked") ) {
			//make a cookie and don't show the message ever again!!!
			$.post(
				"/people/ajax.php",
				{ action:"set message cookie" }
			);
		}
		$(this).animate({ opacity:1 }, 200, function() { $(this).fadeOut(); });
	});
	
});

function AddGameCredit(pid) {
	
	var _gamedata = $.ajax({ url:"/bin/php/autocomplete_load.php", data:"what=games", async:false }).responseText.split("|");
	$("#agc-input-title").autocomplete(
		_gamedata, {
			matchContains:true, 
			selectFirst:true,
			formatItem:function(row) {
				var dat = row[0].split("`");
				return dat[0];
			}
		}
	).result(function(_event, _data, formatted) {
		var dat = _data[0].split("`");
		$(this).val(dat[0]);
		return;
	});
	
	var _roledata = $.ajax({ url:"/bin/php/autocomplete_load.php", data:"what=roles", async:false }).responseText.split("|");
	$("#agc-input-role").autocomplete(_roledata, {max:20});
	
	$("#contribute-message").hide();
	$("#add-game-credit").fadeIn();
	
}
	
function agcCheckTitle(pid) {
	
	if($("#agc-input-title").val() == "Start typing to find a game...") return;
	
	$("#agc-submit-title").attr("disabled", "disabled").val("Checking title...");
	$.post(
		"/people/ajax.php",
		{ _action: "check_game_title",
			_title: $("#agc-input-title").val(),
			_pid: pid
		}, function(res){
			
			$("#add-game-credit .agc-step").hide();
			$("#agc-title-space").html( $("#agc-input-title").val() );
			
			if(res) $("#agc-step2").html(res).show();
			else $("#agc-step3").show();
			
		}
	);
	
}

function agcSubmit(pid) {
	
	if($("#agc-input-role").val() == "Start typing to find a common role...") return;
	
	$("#agc-submit").attr('disabled', 'disabled');
	
	$.post(
		"/people/ajax.php",
		{ _action: "add_game_credit",
			_title: $("#agc-input-title").val(),
			_role: $("#agc-input-role").val(),
			_vital: $("#add-game-credit :input[name='vital']:checked").val(),
			_notes: $("#add-game-credit :input[name='role_notes']").val(),
			_pid: pid
		}, function(res){
			$("#agc-step3").hide().prev().html(res).show();
		}
	);
	
}

function agcClose() {
	$('#add-game-credit').fadeOut(100);
	$("#add-game-credit .agc-step").hide();
	$("#agc-step1").show();
	$("#agc-submit-title").removeAttr("disabled", "disabled").val("Next");
	$("#agc-submit").removeAttr('disabled');
	$("#add-game-credit .agc-inp").val('');
}