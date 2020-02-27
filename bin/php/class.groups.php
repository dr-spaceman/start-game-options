<?

$page->css[] = "/bin/css/groups.css";
$page->javascripts[] = "/bin/script/groups.js";

class groups {
	
	function header() {
		
		?>
		<form action="/groups/" method="get">
			<div id="groups-header">
				<div style="float:right; margin:20px 0 0;">
					<input type="text" name="find" value="<?=htmlSC($_GET['find'])?>" size="15"/>&nbsp;
					<input type="submit" value="Find Group"/>
				</div>
				<h2>Groups<sup style="color:#BBB; font-size:14px;">BETA</sup></h2>
				<ul>
					<li><a href="/groups/">All</a></li>
					<li><a href="/groups/yours">Yours</a></li>
					<li><a href="/groups/create">Create</a></li>
				</ul>
			</div>
		</form>
		<?
		
	}
	
}

?>