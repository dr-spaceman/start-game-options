
function wikiPreview() {
	
	var field = document.getElementById('wiki-text').value.replace(/&/, '[AMP]');
	var space = document.getElementById('wiki-preview-space');
	space.innerHTML = '<img src="/bin/img/loading-thickbox.gif" alt="loading"/>';
	toggle('wiki-preview','wiki-edit');
	
	asyncRequest(
		"post",
		"/wiki.php",
		function(response) {
			if(t=response.responseText) {
				space.innerHTML=t;
			}
		},
		"aact=preview&text="+field
	);
	
}