<?

$pgtypes = array(
	"game"     => "games",
	"person"   => "people",
	"category" => "categories",
	"topic"    => "topics",
	"template" => "templates"
);
$pgsubcategories = array(
	"Game character" => "characters",
	"Game location"  => "locations",
	"Game developer" => "developers",
	"Game development role"
	                 => "roles",
	"Game series"    => "series", 
	"Game console"   => "consoles", 
	"Game concept"   => "concepts",
	"Game genre"     => "genres"
);

define("PAGES_NAMESPACES", array("Category", "Tag", "AlbumID", "User", "Special"));

$pg_tags_tables = array(
	"posts_tags"  => "tag",
	"forums_tags" => "tag",
	"groups_tags" => "tag",
	"albums_tags" => "tag",
	"images_tags" => "tag"
);

$pf_acronyms = array(
	"nintendo entertainment system" 
	                 => "NES",
	"nintendo 64"    => "N64",
	"super nes"      => "SNES",
	"nintendo ds"    => "DS",
	"nintendo 3ds"   => "3DS",
	"gamecube"       => "GCN",
	"game boy"       => "GB",
	"game boy color" => "GBC",
	"game boy advance"
	                 => "GBA",
	"nintendo eshop" => "eShop",
	"playstation"    => "PS",
	"playstation 2"  => "PS2",
	"playstation 3"  => "PS3",
	"playstation network"
	                 => "PSN",
	"playstation portable"
	                 => "PSP",
	"playstation vita"
	                 => "Vita",
	"personal computer"
	                 => "PC",
	"macintosh"      => "Mac",
	"sega genesis"   => "Genesis",
	"sega saturn"    => "Saturn",
	"sega dreamcast" => "DC",
	"commodore 64"   => "C64",
	"turbografx-16"  => "TG16"
);

$pf_shorthand = array(
	"nintendo entertainment system" 
	                 => "NES",
	"playstation network"
	                 => "PS Network",
	"playstation portable"
	                 => "PSP",
	"playstation vita"
	                 => "PS Vita",
	"personal computer"
	                 => "PC",
	"other/miscellaneous"
									 => "other"
);

$pf_regions_expanded = array(
	array("region" => "North America", "abbr" => "us"),
	array("region" => "Europe", "abbr" => "eu"),
	array("region" => "Japan", "abbr" => "jp"),
	array("region" => "Australia", "abbr" => "au"),
	array("region" => "International", "abbr" => "un")
);
$pf_regions = array("North America" => "us", "Europe" => "eu", "Japan" => "jp", "Australia" => "au", "International" => "un");

function getPlatforms($getKeywords=''){
	require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/db.php";
	$not_platforms = array("Game console", "Handheld game console", "Online distribution platform");
	$query = "SELECT `title`, `title_sort`, `keywords` FROM pages_links LEFT JOIN pages ON (pages_links.from_pgid = pages.pgid) WHERE (`to` = 'Game console' OR `to` = 'Game platform') AND `namespace` = 'Category' AND `redirect_to` = '' ORDER BY `title`";
	$res = mysqli_query($GLOBALS['db']['link'], $query);
	while($row = mysqli_fetch_assoc($res)){
		if(in_array($row['title'], $not_platforms)) continue;
		$ret[] = $getKeywords ? $row : $row['title'];
	}
	return $ret;
}

?>