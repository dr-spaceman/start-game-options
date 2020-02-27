
$(document).ready(function(){
	
	$(".set-pf-primary").click(function() {
		$(this).hide().next().show();
		var chbox = $(this);
		var ch = '0';
		if($(this).is(":checked")) ch = '1';
		
		$.post(
			"/ninadmin/games-misc.php", 
			{ setPrimary: $(this).val(), setTo: ch },
			function(t) {
				$(chbox).show().next().hide();
				if(t != "ok") alert(t);
			}
		);
		
	});
	
});

function checkTitle(t) {
	
	var space = document.getElementById('space');
	
	space.innerHTML = '<p><img src="/bin/img/loading-thickbox.gif" alt="loading"/></p>';
	
	asyncRequest(
		"post",
		"/ninadmin/games-add-submit.php",
		function(response) {
			if(response.responseText) {
				space.innerHTML = response.responseText
			}
		},
		"action=check_title&title="+t.replace(/&/g, "[AMP]")
	);
	
}

function checkTitleUrl(t) {
	
	var space = document.getElementById('check-title-url-results');
	
	space.innerHTML = '<img src="/bin/img/loading-thickbox.gif" alt="loading"/>';
	
	asyncRequest(
		"post",
		"/ninadmin/games-add-submit.php",
		function(response) {
			if(response.responseText) {
				space.innerHTML = response.responseText
			}
		},
		"action=check_title_url&title_url="+t
	);
	
}

var oktosubmit = '';
function checkTitleForm() {
	
	if (oktosubmit == '1') return true;
	else return false;
	
}

var i = 0;
function insertSeries() {
	
	var sel = document.getElementById('series-put-select');
	var inp = document.getElementById('series-put-input');
	var space = document.getElementById('series-field');
	var full = space.innerHTML;
	
	if(inp.value) var x = inp.value;
	else var x = sel.value;
	
	if(x) {
		
		space.innerHTML = '<img src="/bin/img/loading-thickbox.gif" alt="loading"/>';
		
		asyncRequest(
			"post",
			"/ninadmin/games-add-submit.php",
			function(response) {
				if(response.responseText == "bad") {
					space.innerHTML = "Couldn't add to database";
				} else {
					i++;
					document.getElementById('none').style.display='none';
					space.innerHTML = full+'<div id="series-'+i+'"><span id="series-words-'+i+'">'+response.responseText+'</span> <a href="javascript:void(0)" onclick="removeSeries(\''+i+'\');" class="x">X</a></div>';
					sel.value='';
					inp.value='';
				}
			},
			"action=insert_series&gid="+document.getElementById('editid').value+"&series="+x.replace(/&/g, '[AMP]')
		);
		
	}
	
}

function removeSeries(n) {
	
	var x = document.getElementById('series-words-'+n).innerHTML;
	
	if(x) {
		
		asyncRequest(
			"post",
			"/ninadmin/games-add-submit.php",
			function(response) {
				if(response.responseText == "ok") {
					document.getElementById('series-'+n).style.display='none';
				} else {
					alert(response.responseText);
				}
			},
			"action=delete_series&gid="+document.getElementById('editid').value+"&series="+x.replace(/&/, '[AMP]')
		);
		
	}
	
}

function addToMyGames(n, x) {
	
	var a = document.getElementById('addtomygames');
	var b = document.getElementById('addtomygames-button-'+n);
	b.value = 'Added to My Games';
	b.disabled = 'true'; 
	if(a.innerHTML) a.innerHTML = a.innerHTML+'``'+x;
	else a.innerHTML = x;
	
}