<?php

namespace Vgsite\API;

use Respect\Validation\Validator as v;
use Vgsite\API\Exceptions\APIException;
use Vgsite\Registry;
use Vgsite\API\Exceptions\APIInvalidArgumentException;
use Vgsite\API\Exceptions\APINotFoundException;
use Vgsite\HTTP\Request;

/**
 * @OA\Schema(schema="person",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="name_sort", type="string", description="A modified name better used for natural sorting"),
 *     @OA\Property(property="keywords", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="created", type="string", format="datetime", description="When the database entry was created"),
 *     @OA\Property(property="modified", type="string", format="datetime", description="When the database entry was modified"),
 *     @OA\Property(property="contributors", type="array", @OA\Items(type="integer"), description="List of user IDs of users who helped build the data entry"),
 *     @OA\Property(property="developers", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="dob", type="string", format="date", description="Date of birth"),
 *     @OA\Property(property="nationality", type="string"),
 *     @OA\Property(property="href", type="string"),
 * )
 */

class PersonController extends Controller
{
    private $pdo;

    const SORTABLE_FIELDS = ['id', 'name', 'dob'];
    const ALLOWED_FIELDS = ['id', 'name', 'dob', 'nationality', 'developers'];
    const REQUIRED_FIELDS = ['id', 'name'];
    const BASE_URI = API_BASE_URI . '/people';

    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->pdo = Registry::get('pdo');
    }

    /**
     * @OA\Get(
     *     path="/people/{id}",
     *     description="A person",
     *     operationId="People:GetOne",
     *     @OA\Parameter(ref="#/components/parameters/id"),
     *     @OA\Parameter(ref="#/components/parameters/fields"),
     *     @OA\Response(response=200,
     *         description="Success!",
     *         @OA\JsonContent(ref="#/components/schemas/person")
     *     ),
     *     @OA\Response(response=404,
     *         description="Requested person not found",
     *     ),
     * )
     */
    protected function getOne($id): void
    {
        if (! v::IntVal()->validate($id)) {
            throw new APIInvalidArgumentException('Person ID must be numeric', 'id');
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
     *     path="/people",
     *     description="A list of people",
     *     operationId="People:GetAll",
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/per_page"),
     *     @OA\Parameter(ref="#/components/parameters/sort"),
     *     @OA\Parameter(ref="#/components/parameters/fields"),
     *     @OA\Parameter(ref="#/components/parameters/q"),
     *     @OA\Response(response=200,
     *         description="Success!",
     *         @OA\JsonContent(ref="#/components/schemas/person")
     *     ),
     * )
     */
    protected function getAll(): void
    {
        $page = $this->parseQuery('page', 1);
        $per_page = $this->parseQuery('per_page', static::PER_PAGE);
        $query = $this->parseQuery('q', '');
        $search = $query ? "AND `title` LIKE CONCAT('%', :query, '%') OR `keywords` LIKE CONCAT('%', :query, '%')" : '';
        
        $statement = $this->pdo->prepare("SELECT count(1) FROM pages WHERE `type`='person' AND `redirect_to`='' {$search}");
        $statement->execute(['query' => $query]);
        $num_rows = $statement->fetchColumn();
        [$limit_min, $limit_max, $num_pages] = $this->convertPageToLimit($page, $per_page, $num_rows);

        $sql = "SELECT * FROM pages WHERE `type`='person' AND `redirect_to`='' {$search} ORDER BY `title` LIMIT {$limit_min}, {$limit_max}";
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['query' => $query]);
        $results = [];
        while ($row = $statement->fetch()) {
            $results[] = $this->parseRow($row);
        }

        if (empty($results)) {
            throw new APINotFoundException();
        }

        $sort_sql = $this->parseQuery('sort', "`name_sort` ASC");
        [$sort, $sort_by] = $this->parseSortSql($sort_sql);
        usort($results, function ($a, $b) use ($sort, $sort_by, $query) {
            if ($sort == 'name_sort') {
                // Prioritize results with exact matches in the name
                if (strtolower($a['name']) == strtolower($query)) return -1;
                if (strtolower($b['name']) == strtolower($query)) return 1;
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
        $index_data = json_decode($row['index_data'], true) ?: [];
        $row = array_slice($row, 0, 7) + $index_data + array_slice($row, 7);

        $row['id'] = (int) $row['id'];
        $row['contributors'] = json_decode($row['contributors'], true) ?: [];

        $row2 = [];
        $row2['name'] = $row['title'];
        $row2['name_sort'] = $row['title_sort'];
        $row = array_slice($row, 0, 3) + $row2 + array_slice($row, 3);

        // Build links
        $domain = 'http://' . getenv('HOST_DOMAIN');
        $row['links']['page'] = $domain . pageURL($row['title'], 'person');
        $row['links']['portrait'] = $domain . $row['rep_image'];

        unset($row['index_data'], $row['rep_image'], $row['background_image'], $row['redirect_to'], 
        $row['creator'], $row['modifier'], $row['featured'], $row['title'], $row['title_sort']);
        
        if ($fields_sql = $this->parseQuery('fields', '')) {
            $fields = $this->parseFieldsSql($fields_sql);
            $row = array_filter($row, function($val, $key) use ($fields) {
                return in_array($key, $fields);
            }, ARRAY_FILTER_USE_BOTH);
        }
        
        $row['href'] = $this->parseLink($row['id']);

        return $row;
    }

    protected function createFromRequest($body): void {
        throw new APIException('Method not supported', null, 'METHOD_NOT_SUPPORTED', 405);
    }

    protected function updateFromRequest($id, $body): void
    {
        throw new APIException('Method not supported', null, 'METHOD_NOT_SUPPORTED', 405);
    }

    protected function delete($id): void
    {
        throw new APIException('Method not supported', null, 'METHOD_NOT_SUPPORTED', 405);
    }
}
