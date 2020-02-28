var user_album_rating = '0';

$(document).ready(function(){
	
	$(".track-table tr").hover(function(){
		$(this).addClass("hov");
	}, function(){
		$(this).removeClass("hov");
	});
	
});

function rateAlbum(albumid, rating, usrid) {
	
	if(!usrid) {
		alert("Please log in");
	} else {
		
		$("#loading-rating").show();
		
		$.post(
			"/bin/php/ajax.albums.php",
			{ 'do':'rate',
				'rating':rating,
				'albumid':albumid
			},
			function(res){
				if(res.errors){
					for(var i = 0; i < res.errors.length; i++){
						$.jGrowl(res.errors[i]);
					}
				}
				if(res.success){
					$("#loading-rating").hide();
					user_album_rating = rating;
					revertToRating();
				}
			}, "json"
		);
	
	}
}

function revertToRating() {
	if(!user_album_rating) user_album_rating = '0';
	document.getElementById('star-rating-bg').className='rating-value-'+user_album_rating;
}

function setCollection(what, albumid, usrid) {

	if(!usrid) {
		alert("Please log in");
	} else {
		
		$('#loading-'+what).show();
		$('#check-'+what).hide();
		
		if(document.getElementById('check-'+what).checked==true) var ch=true;
		else var ch=false;
		
		$.post(
			"/bin/php/ajax.albums.php",
			{ 'do':'set_collection',
				'what':what,
				'set':ch,
				'albumid':albumid
			},
			function(res){
				if(res.errors){
					for(var i = 0; i < res.errors.length; i++){
						$.jGrowl(res.errors[i]);
					}
				}
				if(res.success){
					$('#loading-'+what).animate({opacity:1}, 500, function(){ $(this).hide(); $('#check-'+what).show(); });
				}
			}, "json"
		);
		
	}

}

var tra = new Array();
function toggleTrackRow(row, on) {
	if(on) tra[row] = 1;
	else {
		if(!tra[row]) tra[row] = 1;
		else tra[row] = 0;
	}
	if(tra[row]) {
		document.getElementById('row-'+row+'-ext-td').className='tracklist1';
		document.getElementById('row-'+row+'-ext-div').style.display='block';
	} else {
		document.getElementById('row-'+row+'-ext-td').className='nostyle';
		document.getElementById('row-'+row+'-ext-div').style.display='none';
	}
}

function outputTrackSample(row, f) {
	toggleTrackRow(row, true);
	$('#row-'+row+'-sample').css("width", "445px").html('<iframe src="/music/output_track_sample.php?file='+f+'" style="border:0; width:90%; height:30px; overflow:hidden;"></iframe>');
}

var showfans = 0;
function toggleFans(albumid) {
	
	var x = document.getElementById('fans-link');
	var y = document.getElementById('fans');
	
	showfans++;
	if(showfans % 2) {
		x.className='nostyle';
		y.className='';
		$.post(
			"/bin/php/ajax.albums.php",
			{ 'do':'get_fans',
				'albumid':albumid
			},
			function(res){
				if(res.errors){
					for(var i = 0; i < res.errors.length; i++){
						$.jGrowl(res.errors[i]);
					}
				}
				$('#loading-fans').hide();
				if(res.formatted) {
					$('#fans-list').html('<table border="0"cellpadding="0" cellspacing="0" width="100%">'+res.formatted+'</table>');
				}
			}, "json"
		);
	} else {
		x.className='';
		y.className='nostyle';
	}
}