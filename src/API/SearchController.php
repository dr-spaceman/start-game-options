<?php

namespace Vgsite\API;

use Vgsite\AlbumMapper;
use Vgsite\Registry;
use Vgsite\API\Exceptions\APIInvalidArgumentException;
use Vgsite\API\Exceptions\APINotFoundException;
use Vgsite\HTTP\Request;

class SearchController extends Controller
{
    private $pdo;

    const SORTABLE_FIELDS = ['title', 'title_sort', 'type', 'category'];

    const BASE_URI = API_BASE_URI . '/search';

    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->pdo = Registry::get('pdo');
    }

    protected function getOne($id): void
    {
        throw new APINotFoundException();
    }

    /**
     * @OA\Get(
     *     path="/search",
     *     description="Search all the things",
     *     @OA\Parameter(ref="#/components/parameters/q", required=true, minLength=3),
     *     @OA\Parameter(ref="#/components/parameters/sort"),
     *     @OA\Response(response=200,
     *         description="Things matching request query {q}",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="title_sort", type="string"),
     *             @OA\Property(property="type", type="string"),
     *             @OA\Property(property="category", type="string"),
     *             @OA\Property(property="url", type="string"),
     *         )
     *     )
     * )
     */
    protected function getAll(): void
    {
        $query = $this->parseQuery('q', '');
        if (empty($query)) {
            throw new APIInvalidArgumentException('No search term given. Try using the `q` parameter.', '?q');
        }
        $sort_sql = $this->parseQuery('sort', "`title_sort` ASC");
        [$sort, $sort_by] = $this->parseSortSql($sort_sql);

        // This might be implemented in future
        $filter = null;

        // Populate with SQL sql queries
        $sql = Array();
        if (! $filter) {
            $sql[] = "SELECT `title`, `title_sort`, `subcategory`, `type`, `index_data` 
				FROM `pages` WHERE `redirect_to`='' AND (`title` LIKE CONCAT('%', :query, '%') OR `keywords` LIKE CONCAT('%', :query, '%')) 
				ORDER BY {$sort_sql} LIMIT 100";
        } else {
            $tables = array(
                "categories" => "SELECT `title`, title_sort, `subcategory`, `type`, index_data FROM pages WHERE `type` = 'category' AND `redirect_to` = '' AND (`title` LIKE CONCAT('%', :query, '%') OR `keywords` LIKE CONCAT('%', :query, '%')) ORDER BY {$sort_sql} LIMIT 100",
                "games" => "SELECT `title`, title_sort, `subcategory`, `type`, index_data FROM pages WHERE `type` = 'game' AND `redirect_to` = '' AND (`title` LIKE CONCAT('%', :query, '%') OR `keywords` LIKE CONCAT('%', :query, '%')) ORDER BY {$sort_sql} LIMIT 100",
                "people" => "SELECT `title`, title_sort, `subcategory`, `type`, index_data FROM pages WHERE `type` = 'person' AND `redirect_to` = '' AND (`title` LIKE CONCAT('%', :query, '%') OR `keywords` LIKE CONCAT('%', :query, '%')) ORDER BY {$sort_sql} LIMIT 100",
                "characters" => "SELECT `title`, title_sort, `subcategory`, `type`, index_data FROM pages_links LEFT JOIN pages ON (pages_links.from_pgid = pages.pgid) WHERE (`to` = 'Game character') AND `namespace` = 'Category' AND `redirect_to` = '' AND (`title` LIKE CONCAT('%', :query, '%') OR `keywords` LIKE CONCAT('%', :query, '%')) ORDER BY {$sort_sql}",
                "locations" => "SELECT `title`, title_sort, `subcategory`, `type`, index_data FROM pages_links LEFT JOIN pages ON (pages_links.from_pgid = pages.pgid) WHERE (`to` = 'Game location') AND `namespace` = 'Category' AND `redirect_to` = '' AND (`title` LIKE CONCAT('%', :query, '%') OR `keywords` LIKE CONCAT('%', :query, '%')) ORDER BY {$sort_sql}",
                "publishers" => "SELECT `title`, title_sort, `subcategory`, `type`, index_data FROM pages_links LEFT JOIN pages ON (pages_links.from_pgid = pages.pgid) WHERE (`to` = 'Game publisher') AND `namespace` = 'Category' AND `redirect_to` = '' AND (`title` LIKE CONCAT('%', :query, '%') OR `keywords` LIKE CONCAT('%', :query, '%')) ORDER BY {$sort_sql}"
            );
            foreach ($tables as $table => $query) {
                if (stristr($filter, $table)) {
                    $sql[] = $query;
                }
            }
        }

        foreach ($sql as $sql) {
            $statement = $this->pdo->prepare($sql);
            $statement->execute(['query' => $query]);

            while ($row = $statement->fetch()) {
                $title_sort = strtolower($row['title_sort']);
                $title = $row['title'];

                if ($row['subcategory']) {
                    $category = str_replace('Game ', '', $row['subcategory']);
                    $title = str_replace(' (' . $category . ')', '', $row['title']);
                } else {
                    $category = $row['type'];
                }

                $arr = array(
                    "title" => $title,
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

        // Search albums
        if (! $filter || stristr($filter, "albums")) {
            $mapper = new AlbumMapper();
            $album_results = $mapper->searchBy('title', $query);
            
            foreach ($album_results->getGenerator() as $album) {
                $title_sort = formatName($album->parseTitle(), "sortable");
                $title_sort = strtolower($title_sort);
                $params = array(
                    "title" => $album->parseTitle(),
                    "title_sort" => $title_sort,
                    "type" => "album",
                    "category" => "music",
                    "url" => $album->parseUrl(),
                    "tag" => 'AlbumID:' . $album->getProp('albumid'),
                    "release_date" => $album->getProp('datesort'),
                );

                $results[] = $params;
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
        
        if (empty($results)) {
            throw new APINotFoundException("The requested query `{$query}` returned no results.");
        }

        usort($results, function ($a, $b) use ($sort, $sort_by, $query) {
            if ($sort == 'title_sort') {
                // Prioritize results with exact matches in the title
                if (strtolower($a['title']) == strtolower($query)) return -1;
                if (strtolower($b['title']) == strtolower($query)) return 1;
            }
            
            if ($sort_by == 'DESC') {
                return strcmp($b[$sort], $a[$sort]);
            }
            return strcmp($a[$sort], $b[$sort]);
        });

        $this->setPayload($results)->render(200);
    }
}