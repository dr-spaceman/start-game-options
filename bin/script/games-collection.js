
var GCsessid = '';

function addGame(gid, bypass, editid) {
	
	GCsessid = '';
	
	var space = document.getElementById('add-game');
	
	space.style.display = 'block';
	space.innerHTML='<div class="container"><div class="container-2"><img src="/bin/img/loading-thickbox.gif" alt="loading"/></div></div>';
	
	asyncRequest(
		"post",
		"/bin/php/games-collection.php",
		function(response) {
			if(response.responseText) {
				space.innerHTML = '<div class="container"><div class="container-2">'+response.responseText+'</div></div>';
				tooltip.init();
			}
		},
		"action=output_form&gid="+gid+"&bypass="+bypass+"&editid="+editid
	);
	
}

function selectBoxCover(boxid) {
	
	if(!curr_box) var curr_box = document.getElementById('AGselpubid').value;
	document.getElementById('box-'+curr_box).className='off';
	curr_box = boxid;
	document.getElementById('box-'+curr_box).className='on';
	x = document.getElementById('box-'+boxid);
	document.getElementById('AGselpubid').value = boxid;
	
}

function setGameAction(act) {
	
	var x = document.getElementById('action-'+act).value;
	if(x) {
		document.getElementById('action-'+act).value='';
		document.getElementById('action-'+act+'-button').className='';
		if(act == 'play_online') document.getElementById('playonline-input').style.display='none';
	} else {
		document.getElementById('action-'+act).value='1';
		document.getElementById('action-'+act+'-button').className='on';
		if(act == 'play_online') document.getElementById('playonline-input').style.display='block';
	}
	
}

function setGameRating(x) {
	
	var cval = document.getElementById('game-rating').value;
	if(cval == x) {
		document.getElementById('game-rating-'+x).className='';
		document.getElementById('game-rating').value='1';
	} else {
		document.getElementById('game-rating-'+x).className='on';
		if(cval != 1) document.getElementById('game-rating-'+cval).className='';
		document.getElementById('game-rating').value=x;
	}
	
}

function submitAddGame(gid) {
	
	if(GCsessid) {
		//check for required vals
		var AGplatform_id = document.getElementById('ag-uplinp-platform').value;
		if(!AGplatform_id) {
			alert("Please input a platform.");
			return;
		}
		var AGregion = document.getElementById('ag-uplinp-region').value;
		var AGregionother = document.getElementById('ag-uplinp-regionother').value;
		if(!AGregion) {
			if(!AGregionother) {
				alert("Please input a region.");
				return;
			} else AGregion = AGregionother;
		}
	} 
	var AGpubid = document.getElementById('AGselpubid').value;
	if(!GCsessid && !AGpubid) {
		alert("Can't add to your games since there's no publications. In order to add it you'll need up upload a cover image.");
		return;
	}
	
	var AGspace = document.getElementById('add-game-results');
	AGspace.innerHTML = '<img src="/bin/img/loading-thickbox.gif" alt="loading"/>';
	
	asyncRequest(
		"post",
		"/bin/php/games-collection.php",
		function(response) {
			if(isNaN(response.responseText)) {
				AGspace.innerHTML = response.responseText;
			} else {
				document.getElementById('dbaction').value = response.responseText;
				if(GCsessid) toggle('','add-game-submit-buttons');
				AGspace.innerHTML = '<b>Saved to your games.</b><br/><a href="javascript:void(0);" onclick="toggle(\'\', \'add-game\');">Close</a>';
			}
		},
		"gid="+gid
		+"&action=submit_add_game&publication_id="+AGpubid
		+"&dbaction="+document.getElementById('dbaction').value
		+"&action-own="+document.getElementById('action-own').value
		+"&action-play="+document.getElementById('action-play').value
		+"&action-play_online="+document.getElementById('action-play_online').value
		+"&online_id="+document.getElementById('online_id').value
		+"&game-rating="+document.getElementById('game-rating').value
		+"&gcsessid="+GCsessid
		+"&share_upload="+(document.getElementById('ag-uplinp-share').checked==true ? "1" : "")
		+"&title="+document.getElementById('ag-uplinp-title').value.replace(/&/g, '[AMP]')
		+"&platform_id="+AGplatform_id
		+"&region="+AGregion
	);
	
}

function deleteFromMyGames(id) {
	
	var space = document.getElementById('add-game-results');
	space.innerHTML = '<img src="/bin/img/loading-thickbox.gif" alt="loading"/>';
	
	asyncRequest(
		"post",
		"/bin/php/games-collection.php",
		function(response) {
			if(response.responseText == "ok") {
				document.getElementById('add-game').style.display='none';
			} else {
				space.innerHTML = response.responseText;
			}
		},
		"action=delete&id="+id
	);

}

var AGboxpg = 1;
function AGboxnavigate(what, maxi) {
	
	var to = '';
	if(what == "prev") {
		to = AGboxpg - 1;
		if(to < 1) return;
		toggle('boxes-pg-'+to, 'boxes-pg-'+AGboxpg);
		if(to == 1) document.getElementById('agboxnav-prev').className="arrow-left off";
		document.getElementById('agboxnav-next').className="arrow-right";
		AGboxpg = to;
	}
	if(what == "next") {
		to = AGboxpg + 1;
		if(to > maxi) return;
		toggle('boxes-pg-'+to, 'boxes-pg-'+AGboxpg);
		if(to == maxi) document.getElementById('agboxnav-next').className="arrow-right off";
		document.getElementById('agboxnav-prev').className="arrow-left";
		AGboxpg = to;
	}

}

var myCurrGames = 'default';
function showMyGames(what, uid) {
	
	document.getElementById('gamebox-nav-'+myCurrGames).className="";
	document.getElementById('gamebox-nav-'+what).className="on";
	
	if(what == "default") {
		toggle('gamebox-default', 'gamebox-space');
	} else {
		
		toggle('gamebox-space','gamebox-default');
		var space = document.getElementById('gamebox-space');
		space.innerHTML = '<div style="margin:15px"><img src="/bin/img/loading-thickbox.gif" alt="loading"/></div>';
		
		asyncRequest(
			"post",
			"/bin/php/games-collection.php",
			function(response) {
				if(response.responseText) {
					space.innerHTML = response.responseText;
					tooltip.init();
				}
			},
			"action=show my games&uid="+uid+"&whichones="+what
		);
		
	}
	
	myCurrGames = what;

}

function findGames(pg) {
	
	if(!pg) pg = 1;
	q = document.getElementById('ag-query').value;
	if(!q || q == 'Game title...') return;
	
	toggle('add-game-output','add-game-input');
	toggle('','gamebox-default');
	
	document.getElementById('add-game-title').innerHTML = q;
	rspace = document.getElementById('add-game-results-container');
	document.getElementById('add-game-results-loading').className='loading';
	
	asyncRequest(
		"post",
		"/bin/php/games-collection.php",
		function(response) {
			if(response.responseText) {
				rspace.innerHTML = response.responseText;
				document.getElementById('add-game-results-loading').className='';
				tooltip.init();
			}
		},
		"action=find games&query="+q.replace(/&/g, '[AMP]')+"&pg="+pg
	);
	
}
	
function addFoundGame(pubid, gid) {
	
	toggle('add-game-loading','');
	
	var t = "";
	asyncRequest(
		"post",
		"/bin/php/games-collection.php",
		function(response) {
			if(t = response.responseText) {
				document.getElementById('game-additions').innerHTML = t+document.getElementById('game-additions').innerHTML;
				tooltip.init();
				toggle('','add-game-loading');
			}
		},
		"action=add found game&pubid="+pubid+"&gid="+gid
	);
	
}

$(document).ready(function() {
	
	$("#my-game-edit-space").hide();
	
	var t;
	
	$("#gamebox .shadow .gamecover").hover(function () {
		
		t = setTimeout("$('#my-game-edit-space').show()",1000);
		$("#my-game-edit-space").hide();
		
		var pos = findPos(this);
		var whitesp = (screen.width - 930) / 2;
		var xPosInParent = pos[0] - whitesp;
		if(xPosInParent > 500) {
			pos[0] = pos[0] - 332;
			pos[1] = pos[1] - 14;
			$("#my-game-edit-space").addClass("right");
		} else {
			pos[0] = pos[0] - 14;
			pos[1] = pos[1] - 14;
			$("#my-game-edit-space").removeClass("right");
		}
		
		var x = $(this).parents(".container").children(".my-game-edit");
		$("#my-game-edit-space").html(x.html());
		$("#my-game-edit-space").css({ left:pos[0]+'px', top:pos[1]+'px' });
		
		var id = x.attr("title");
		editMyGame(id);
		
	}, function () {
		clearTimeout(t);
	});
	
});

function editMyGame(id) {
	
	var space = document.getElementById('my-game-edit-space');
	
	asyncRequest(
		"post",
		"/bin/php/games-collection.php",
		function(response) {
			if(t = response.responseText) {
				space.innerHTML = t;
				tooltip.init();
			}
		},
		"action=edit my game form&id="+id
	);
	
}



var showCollection = 0;
function toggleCollection() {
	showCollection++;
	if(showCollection % 2) {
		document.getElementById("collection-items").style.display="block";
	} else {
		document.getElementById("collection-items").style.display="none";
	}
}

function setCollection(what, gid, usrid) {

if(!usrid) {
	toggle('login');
	if(confirm("Please log in to access this function. Click OK to register for an account, or click Cancel and then log in at the top of the page.")) window.location='/register.php';
} else {

document.getElementById(what+'-words').style.backgroundImage='url(/bin/img/loading-arrows-small.gif)';

if(document.getElementById(what+'-check').checked==true) var ch=true;
else var ch=false;

asyncRequest(
	"post",
	"/bin/php/games-collection.php",
	function(response) {
		if(response.responseText) document.getElementById(what+'-words').style.backgroundImage='url(/bin/img/icons/'+what+'.png)';
	},
	"what="+what+"&set="+ch+"&gid="+gid
);

}

}