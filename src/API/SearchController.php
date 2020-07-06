<?php

namespace Vgsite\API;

use Vgsite\Registry;
use Vgsite\API\Exceptions\APIException;
use Vgsite\API\Exceptions\APIInvalidArgumentException;
use Vgsite\HTTP\Request;

class SearchController extends Controller
{
    private $pdo;

    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->pdo = Registry::get('pdo');

        if (empty($request->getQuery())) {
            throw new APIInvalidArgumentException('No search term given');
        }

        $this->response->withHeader('Access-Control-Allow-Methods', 'GET');
    }

    protected function getOne($id): array
    {
        return Array("hits" => []);
    }
    
    protected function getAll(): array
    {
        $query = $this->queries[0];

        // This might be implemented in future
        $filter = '';

        // Populate with SQL queries
        $queries = Array();
        if (!$filter) {
            $queries[] = "SELECT `title`, `title_sort`, `subcategory`, `type`, `index_data` 
				FROM `pages` WHERE `redirect_to`='' AND (`title` LIKE CONCAT('%', :query, '%') OR `keywords` LIKE CONCAT('%', :query, '%')) 
				ORDER BY `title_sort` LIMIT 30";
        } else {
            $tables = array(
                "categories" => "SELECT `title`, title_sort, `subcategory`, `type`, index_data FROM pages WHERE `type` = 'category' AND `redirect_to` = '' AND (`title` LIKE CONCAT('%', :query, '%') OR `keywords` LIKE CONCAT('%', :query, '%')) ORDER BY `title` LIMIT 100",
                "games" => "SELECT `title`, title_sort, `subcategory`, `type`, index_data FROM pages WHERE `type` = 'game' AND `redirect_to` = '' AND (`title` LIKE CONCAT('%', :query, '%') OR `keywords` LIKE CONCAT('%', :query, '%')) ORDER BY `title` LIMIT 100",
                "people" => "SELECT `title`, title_sort, `subcategory`, `type`, index_data FROM pages WHERE `type` = 'person' AND `redirect_to` = '' AND (`title` LIKE CONCAT('%', :query, '%') OR `keywords` LIKE CONCAT('%', :query, '%')) ORDER BY `title` LIMIT 100",
                "characters" => "SELECT `title`, title_sort, `subcategory`, `type`, index_data FROM pages_links LEFT JOIN pages ON (pages_links.from_pgid = pages.pgid) WHERE (`to` = 'Game character') AND `namespace` = 'Category' AND `redirect_to` = '' AND (`title` LIKE CONCAT('%', :query, '%') OR `keywords` LIKE CONCAT('%', :query, '%')) ORDER BY `title`",
                "locations" => "SELECT `title`, title_sort, `subcategory`, `type`, index_data FROM pages_links LEFT JOIN pages ON (pages_links.from_pgid = pages.pgid) WHERE (`to` = 'Game location') AND `namespace` = 'Category' AND `redirect_to` = '' AND (`title` LIKE CONCAT('%', :query, '%') OR `keywords` LIKE CONCAT('%', :query, '%')) ORDER BY `title`",
                "publishers" => "SELECT `title`, title_sort, `subcategory`, `type`, index_data FROM pages_links LEFT JOIN pages ON (pages_links.from_pgid = pages.pgid) WHERE (`to` = 'Game publisher') AND `namespace` = 'Category' AND `redirect_to` = '' AND (`title` LIKE CONCAT('%', :query, '%') OR `keywords` LIKE CONCAT('%', :query, '%')) ORDER BY `title`"
            );
            foreach ($tables as $table => $query) {
                if (stristr($filter, $table)) {
                    $queries[] = $query;
                }
            }
        }print_r($queries);

        foreach ($queries as $sql) {
            $statement = $this->pdo->prepare($sql);
            $statement->execute(['query' => $query]);

            while ($row = $statement->fetch()) {
                $exact_match = strtolower($row['title']) == strtolower($q);
                $title_sort = strtolower($row['title_sort']);
                $o_title = $row['title'];

                if ($row['subcategory']) {
                    $category = str_replace('Game ', '', $row['subcategory']);
                    $o_title = str_replace(' (' . $category . ')', '', $row['title']);
                } else {
                    $category = $row['type'];
                }

                $arr = array(
                        "title" => $o_title,
                        "title_sort" => $row['title_sort'],
                        "type" => $row['type'],
                        "category" => $category,
                        "url" => pageURL($row['title'], $row['type'])
                    );

                // if(strstr($_GET['return_vars'], "data")) $arr["data"] = json_decode($row['index_data']);
                // if(strstr($_GET['return_vars'], "platform_shorthand")){
                // 	if(!$platform_shorthand = $pf_shorthand[strtolower($arr['data']->platform)]) $platform_shorthand = $arr['data']->platform;
                // 	$arr['data']->platform_shorthand = $platform_shorthand;
                // 	if(is_array($arr['data']->platforms)){
                // 		foreach($arr['data']->platforms as $pf){
                // 			if(!$platform_shorthand = $pf_shorthand[strtolower($pf)]) $platform_shorthand = $pf;
                // 			$arr['data']->platforms_shorthand[] = $platform_shorthand;
                // 		}
                // 	}
                // }
                // if(strstr($_GET['return_vars'], "platform_acronym")){
                // 	if(!$platform_acronym = $pf_acronyms[strtolower($arr['data']->platform)]) $platform_acronym = $arr['data']->platform;
                // 	$arr['data']->platform_acronym = $platform_acronym;
                // 	if(is_array($arr['data']->platforms)){
                // 		$arr['data']->platforms_acronym_formatted = '';
                // 		$i = 0;
                // 		foreach($arr['data']->platforms as $pf){
                // 			if(!$platform_acronym = $pf_acronyms[strtolower($pf)]) $platform_acronym = $pf;
                // 			$arr['data']->platforms_acronym[] = $platform_acronym;
                // 			if($i++ < 3) $arr['data']->platforms_acronym_formatted.= $platform_acronym.", ";
                // 		}
                // 	} else $arr['data']->platforms_acronym_formatted = $platform_acronym;
                // }

                $results[] = $arr;
            }
        }

        if (!$filter || stristr($filter, "albums")) {
            $sql = "SELECT title, subtitle, albumid, datesort FROM albums WHERE (`title` LIKE CONCAT('%', :query, '%') OR `keywords` LIKE CONCAT('%', :query, '%') OR cid=:query) AND `view`='1' ORDER BY `title` LIMIT 30";
            $statement = $this->pdo->prepare($sql);
            $statement->execute(['query' => $query]);

            while ($row = $statement->fetch()) {
                $title_sort = formatName($row['title'] . ($row['subtitle'] ? " " . $row['subtitle'] : ''), "sortable");
                $title_sort = strtolower($title_sort);
                $arr = array(
                        "title" => $row['title'] . ' ' . $row['subtitle'],
                        "title_sort" => $title_sort,
                        "type" => "album",
                        "category" => "music",
                        "url" => '/music/?id=' . $row['albumid'],
                        "tag" => 'AlbumID:' . $row['albumid']
                    );
                if (strstr($_GET['return_vars'], "data")) $arr["data"] = array("release_date" => $row['datesort']);
                $results[] = $arr;
            }
        }

        // if($_GET['add_db_link'] && !$exact_match) {
        // 	$ret['zzzzz'][] = array(
        // 		"title" => 'Add <i><b>'.$q.'</b></i> to the database',
        // 		"type" => '',
        // 		"category" => '',
        // 		"url" => '/content/Special:new?title='.urlencode($q)
        // 	);
        // }

        usort($results, function ($a, $b) {
            return strcmp($a['title_sort'], $b['title_sort']);
        });
        $results = array('hits' => $results);

        return $results;
    }
}