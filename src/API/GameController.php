<?php

namespace Vgsite\API;

use Respect\Validation\Validator as v;
use Vgsite\Registry;
use Vgsite\API\Exceptions\APIInvalidArgumentException;
use Vgsite\API\Exceptions\APINotFoundException;
use Vgsite\HTTP\Request;

/**
 * @OA\Schema(schema="game",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="title_sort", type="string", description="A modified title better used for natural sorting"),
 *     @OA\Property(property="keywords", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="created", type="string", format="datetime", description="When the database entry was created"),
 *     @OA\Property(property="modified", type="string", format="datetime", description="When the database entry was modified"),
 *     @OA\Property(property="contributors", type="array", @OA\Items(type="integer"), description="List of user IDs of users who helped build the data entry"),
 *     @OA\Property(property="genres", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="developers", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="series", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="platforms", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="platform", type="string", description="Main platform"),
 *     @OA\Property(property="release", type="string", format="date", description="Main platform release date"),
 *     @OA\Property(property="first_release", type="string", format="date", description="Earliest release for any platform or region"),
 *     @OA\Property(property="href", type="string"),
 * )
 */

class GameController extends Controller
{
    private $pdo;

    const SORTABLE_FIELDS = ['id', 'title', 'release', 'first_release', 'platform'];
    const ALLOWED_FIELDS = ['id', 'title', 'genres', 'developers', 'series', 'platforms', 'platform', 'release', 'first_release'];
    const REQUIRED_FIELDS = ['id', 'title'];
    const BASE_URI = API_BASE_URI . '/games';

    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->pdo = Registry::get('pdo');
    }

    /**
     * @OA\Get(
     *     path="/games/{id}",
     *     description="A game",
     *     @OA\Parameter(ref="#/components/parameters/id"),
     *     @OA\Parameter(ref="#/components/parameters/fields"),
     *     @OA\Response(response=200,
     *         description="Success!",
     *         @OA\JsonContent(ref="#/components/schemas/game")
     *     ),
     *     @OA\Response(response=404,
     *         description="Requested game not found",
     *     ),
     * )
     */
    protected function getOne($id): void
    {
        if (! v::IntVal()->validate($id)) {
            throw new APIInvalidArgumentException('Game ID must be numeric', 'id');
        }

        $sql = "SELECT * FROM pages WHERE `id`=:id LIMIT 1";
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $id]);
        $results = [];
        while ($row = $statement->fetch()) {
            $results[] = $this->parseRow($row);
        }

        if (empty($results)) {
            throw new APINotFoundException();
        }

        $this->setPayload($results)->render(200);
    }

    /**
     * @OA\Get(
     *     path="/games",
     *     description="A list of games",
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/per_page"),
     *     @OA\Parameter(ref="#/components/parameters/sort"),
     *     @OA\Parameter(ref="#/components/parameters/fields"),
     *     @OA\Parameter(ref="#/components/parameters/q"),
     *     @OA\Response(response=200,
     *         description="Success!",
     *         @OA\JsonContent(ref="#/components/schemas/game")
     *     ),
     * )
     */
    protected function getAll(): void
    {
        $page = $this->parseQuery('page', 1);
        $per_page = $this->parseQuery('per_page', static::PER_PAGE);
        $query = $this->parseQuery('q', '');
        $search = $query ? "AND `title` LIKE CONCAT('%', :query, '%') OR `keywords` LIKE CONCAT('%', :query, '%')" : '';
        
        $statement = $this->pdo->prepare("SELECT count(1) FROM pages WHERE `type`='game' AND `redirect_to`='' {$search}");
        $statement->execute(['query' => $query]);
        $num_rows = $statement->fetchColumn();
        [$limit_min, $limit_max, $num_pages] = $this->convertPageToLimit($page, $per_page, $num_rows);

        $sql = "SELECT * FROM pages WHERE `type`='game' AND `redirect_to`='' {$search} ORDER BY `title` LIMIT {$limit_min}, {$limit_max}";
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['query' => $query]);
        $results = [];
        while ($row = $statement->fetch()) {
            $results[] = $this->parseRow($row);
        }

        if (empty($results)) {
            throw new APINotFoundException();
        }

        $sort_sql = $this->parseQuery('sort', "`title_sort` ASC");
        [$sort, $sort_by] = $this->parseSortSql($sort_sql);
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

        $links = $this->buildLinks($page, $num_pages);

        $this->setPayload($results, $links)->render(200);
    }

    public function parseRow(array $row): array
    {
        $row['contributors'] = json_decode($row['contributors'], true) ?: [];
        $row['id'] = (int) $row['id'];

        $index_data = json_decode($row['index_data'], true) ?: [];
        $row = array_merge($row, $index_data);
        $row['links']['page'] = pageURL($row['title'], 'game');
        $row['links']['box_art'] = $row['rep_image'];
        unset($row['index_data'], $row['rep_image'], $row['rep_image'], $row['background_image'], $row['redirect_to'], $row['creator'], $row['modifier'], $row['featured']);
        
        if ($fields_sql = $this->parseQuery('fields', '')) {
            $fields = $this->parseFieldsSql($fields_sql);
            $row = array_filter($row, function($val, $key) use ($fields) {
                return in_array($key, $fields);
            }, ARRAY_FILTER_USE_BOTH);
        }
        
        $row['href'] = $this->parseLink($row['id']);

        return $row;
    }

    protected function updateFromRequest(): void {}
}
