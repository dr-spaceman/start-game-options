<?php

namespace Vgsite\API;

use Respect\Validation\Validator as v;
use Vgsite\Registry;
use Vgsite\API\Exceptions\APIException;
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
        [$limit_min, $limit_max] = $this->convertPageToLimit($page, $per_page);
        $sort = $this->parseQuery('sort', 'release', function ($var) {
            return in_array($var, static::SORTABLE_FIELDS);
        });
        $sort_dir = $this->parseQuery('sort_dir', 'asc');
        $sort_dir = strtoupper($sort_dir);
        $query = $this->parseQuery('q', '');
        $fields = $this->parseQuery('fields', '*');
        $search = $query ? "AND `title` LIKE CONCAT('%', :query, '%')" : '';

        $sql = "SELECT {$fields} FROM pages_games WHERE `release` IS NOT NULL {$search} ORDER BY `{$sort}` {$sort_dir} LIMIT {$limit_min}, {$limit_max}";
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['query' => $query]);
        $results = [];
        while ($row = $statement->fetch()) {
            echo $row;
            $row['href'] = $this->parseLink($row['id']);
            $results[] = $row;
        }

        if (empty($results)) {
            throw new APINotFoundException();
        }

        $this->setPayload($results)->render(200);
    }
}