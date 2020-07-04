<?php

namespace Vgsite\API;

use InvalidArgumentException;
use Respect\Validation\Rules\IntVal;
use Respect\Validation\Validator as v;
use Vgsite\Registry;
use Vgsite\API\Exceptions\APIException;
use Vgsite\API\Exceptions\APIInvalidArgumentException;
use Vgsite\API\Exceptions\APINotFoundException;

class GameController extends Controller
{
    private $pdo;

    public function __construct(string $request_method, array $queries=[])
    {
        $this->pdo = Registry::get('pdo');
        parent::__construct($request_method, $queries);
    }

    protected function getOne($id): array
    {
        if (! v::IntVal()->validate($id)) {
            throw new APIInvalidArgumentException('Game ID must be numeric');
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

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['payload'] = $results;

        return $response;
    }

    protected function getAll(): array {
        $sql = sprintf(
            "SELECT * FROM pages_games WHERE `release` IS NOT NULL %s ORDER BY `release`",
            ($this->queries[0] ? "AND `title` LIKE CONCAT('%', :query, '%')" : "")
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

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['payload'] = $results;

        return $response;
    }
}