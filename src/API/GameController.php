<?php

namespace Vgsite\API;

use Respect\Validation\Validator as v;
use Vgsite\Registry;
use Vgsite\API\Exceptions\APIInvalidArgumentException;
use Vgsite\API\Exceptions\APINotFoundException;
use Vgsite\HTTP\Request;

class GameController extends Controller
{
    private $pdo;

    const SORTABLE_FIELDS = ['id', 'title', 'genre', 'platform', 'release'];

    const BASE_URI = API_BASE_URI . '/games';

    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->pdo = Registry::get('pdo');
    }

    protected function getOne($id): void
    {
        if (! v::IntVal()->validate($id)) {
            throw new APIInvalidArgumentException('Game ID must be numeric', 'id');
        }

        $sql = "SELECT * FROM pages_games WHERE `id`=:id LIMIT 1";
        $sql = sprintf("SELECT * FROM pages_games WHERE `id`=%d LIMIT 1", $id);
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $id]);
        $results = [];
        while ($row = $statement->fetch()) {
            $row['href'] = $this->parseLink($row['id']);
            $results[] = $row;
        }

        if (empty($results)) {
            throw new APINotFoundException();
        }

        $this->setPayload($results)->render(200);
    }

    protected function getAll(): void
    {
        $page = $this->parseQuery('page', 1);
        $per_page = $this->parseQuery('per_page', static::PER_PAGE);
        $sort_sql = $this->parseQuery('sort', '`release` ASC');
        // [$sort, $sort_by] = $this->parseSortSql($sort_sql);
        $fields = $this->parseQuery('fields', '*');
        $query = $this->parseQuery('q', '');
        $search = $query ? "AND `title` LIKE CONCAT('%', :query, '%')" : '';
        
        $statement = $this->pdo->prepare("SELECT count(1) FROM pages_games WHERE `release` IS NOT NULL {$search}");
        $statement->execute(['query' => $query]);
        $num_rows = $statement->fetchColumn();
        [$limit_min, $limit_max, $num_pages] = $this->convertPageToLimit($page, $per_page, $num_rows);

        $sql = "SELECT {$fields}, `id` FROM pages_games WHERE `release` IS NOT NULL {$search} ORDER BY {$sort_sql} LIMIT {$limit_min}, {$limit_max}";
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['query' => $query]);
        $results = [];
        while ($row = $statement->fetch()) {
            $row['href'] = $this->parseLink($row['id']);
            $results[] = $row;
        }

        if (empty($results)) {
            throw new APINotFoundException();
        }

        $links = $this->buildLinks($page, $num_pages);

        $this->setPayload($results, $links)->render(200);
    }
}
