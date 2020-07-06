<?php

namespace Vgsite\API;

use Respect\Validation\Validator as v;
use Vgsite\Registry;
use Vgsite\API\Exceptions\APIException;
use Vgsite\API\Exceptions\APIInvalidArgumentException;
use Vgsite\API\Exceptions\APINotFoundException;
use Vgsite\HTTP\Request;
use Vgsite\HTTP\Response;

class GameController extends Controller
{
    private $pdo;

    const SORTABLE_FIELDS = ['id', 'title', 'genre', 'platform', 'release'];

    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->pdo = Registry::get('pdo');
        $this->response->withHeader('Access-Control-Allow-Methods', 'GET');
    }

    protected function getOne($id): Controller
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
            $results[] = $row;
        }

        if (empty($results)) {
            throw new APINotFoundException();
        }

        $this->response->withStatus(200);
        $this->response->setPayload($results);

        return $this;
    }

    protected function getAll(): Controller
    {
        $limit = '0, 100';
        if ($this->request->hasHeader('range')) {
            $ranges = $this->request->parseRange($this->request->getHeaderLine('range'));
            $limit = sprintf('%d, %d', $ranges[0], ($ranges[1] - $ranges[0]));
        }

        $sort = '`release`';
        $sort_dir = 'ASC';
        if ($sort_query = $this->request->getQuery()['sort']) {
            $parse_results = $this->request->parseSortQuery($sort_query, self::SORTABLE_FIELDS);
            $sort = sprintf('`%s`', $parse_results[0]);
            $sort_dir = $parse_results[1] ?: $sort_dir;
            $sort_dir = strtoupper($sort_dir);
        }

        $sql = sprintf(
            "SELECT * FROM pages_games WHERE `release` IS NOT NULL %s ORDER BY %s %s LIMIT %s",
            ($this->queries[0] ? "AND `title` LIKE CONCAT('%', :query, '%')" : ""), $sort, $sort_dir, $limit
        );
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['query' => $this->queries[0]]);
        $results = [];
        while ($row = $statement->fetch()) {
            $results[] = $row;
        }

        if (empty($results)) {
            throw new APINotFoundException();
        }

        $this->response->withStatus(200);
        $this->response->setPayload($results);

        return $this;
    }
}