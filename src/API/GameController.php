<?php

namespace Vgsite\API;

class GameController extends Controller
{
    private $pdo;

    public function __construct(string $request_method, array $queries = [])
    {
        $this->pdo = \Vgsite\Registry::get('pdo');
        parent::__construct($request_method, $queries);
    }

    protected function getOne($id): array
    {
        $sql = "SELECT * FROM pages_games WHERE `id`=:id LIMIT 1";
        $sql = sprintf("SELECT * FROM pages_games WHERE `id`=%d LIMIT 1", $id);
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $id]);
        $results = [];
        while ($row = $statement->fetch()) {
            $results[] = $row;
        }

        if (empty($results)) {
            return parent::notFoundResponse();
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
            return parent::notFoundResponse();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['payload'] = $results;

        return $response;
    }
}