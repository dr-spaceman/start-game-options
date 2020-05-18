<?
use Vgsite\Page;
$page = new Page();
$page->title = "Page Management / Build Index";

$page->javascript.='
<script type="text/javascript">
$(document).ready(function(){
$("#inSelect").change(function(){
	if($(this).val() == "") return;
	$(this).attr("disabled", "true");
	var rebuildTables = $("#rebuildtables-inp").is(":checked") ? "1" : "";
	$("#indexbuildframe").attr("src", "/bin/php/pages_index_build.include.php?_rebuildtables="+rebuildTables+"&_onFin=buildIndexResetForm&_type="+$(this).val());
});
})
function buildIndexResetForm(){
	$("#inSelect").removeAttr("disabled").val(\'\');
}
</script>
';

$page->header();

?>
<h1>(Re)build Index</h1>

<select id="inSelect">
	<option value="">Select an index to build...</option>
	<option value="game">Games</option>
	<option value="person">People</option>
	<option value="category">Categories</option>
	<option value="category">Subcategories (Characters, Genres, Consoles...)</option>
	<option value="topic">Topics</option>
</select> &nbsp;
<label><input type="checkbox" name="rebuildtables" value="1" id="rebuildtables-inp"/> Rebuild database tables<sup class="a tooltip" title="Rebuild data tables like Credits, Publications, etc. This takes longer to complete.">?</sup></label>
<br/><br/>
<iframe id="indexbuildframe" src="" frameborder="0" style="width:500px; height:100px;"></iframe>
<?

$page->footer();
		

?>