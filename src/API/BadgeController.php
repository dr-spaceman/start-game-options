<?php

namespace Vgsite\API;

use OutOfBoundsException;
use Respect\Validation\Validator as v;
use Vgsite\Registry;
use Vgsite\Badge;
use Vgsite\BadgeMapper;
use Vgsite\API\Exceptions\APIInvalidArgumentException;
use Vgsite\API\Exceptions\APINotFoundException;

/**
 * @OA\Schema(schema="badge",
 *     type="object",
 *     @OA\Property(property="id", type="string"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="value", type="boolean"),
 *     @OA\Property(property="value_description", type="string"),
 *     @OA\Property(property="sort", type="string"),
 * )
 */

class BadgeController extends Controller
{
    const SORTABLE_FIELDS = ['badge_id', 'name', 'description', 'value', 'sort'];
    const ALLOWED_FIELDS = ['badge_id', 'name', 'description', 'value', 'sort'];
    const REQUIRED_FIELDS = ['badge_id', 'name', 'description', 'value', 'sort'];
    const BASE_URI = API_BASE_URI . '/badges';

    /**
     * @OA\Get(
     *     path="/badges/{id}",
     *     description="A badge",
     *     @OA\Parameter(ref="#/components/parameters/id"),
     *     @OA\Parameter(ref="#/components/parameters/fields"),
     *     @OA\Response(response=200,
     *         description="Success!",
     *         @OA\JsonContent(ref="#/components/schemas/badge")
     *     ),
     *     @OA\Response(response=404,
     *         description="Requested badge not found",
     *     ),
     * )
     */
    protected function getOne($id): void
    {
        if (! v::IntVal()->validate($id)) {
            throw new APIInvalidArgumentException('Badge ID must be numeric', 'badge_id');
        }

        try {
            $mapper = new BadgeMapper();
            $badge = $mapper->findById($id);
        } catch (OutOfBoundsException $e) {
            throw new APINotFoundException($e);
        }
        
        $results[] = $this->parseRow($badge);

        $this->setPayload($results)->render(200);
    }

    /**
     * @OA\Get(
     *     path="/badges",
     *     description="A list of badges",
     *     @OA\Parameter(ref="#/components/parameters/sort"),
     *     @OA\Parameter(ref="#/components/parameters/fields"),
     *     @OA\Parameter(ref="#/components/parameters/q"),
     *     @OA\Response(response=200,
     *         description="Success!",
     *         @OA\JsonContent(ref="#/components/schemas/badge")
     *     ),
     * )
     */
    protected function getAll(): void
    {
        $pdo = Registry::get('pdo');

        $sort = "ORDER BY " . $this->parseQuery('sort', '`sort` ASC');
        $query = $this->parseQuery('q', '');
        $search = $query ? "WHERE `name` LIKE CONCAT('%', :query, '%')" : '';

        $statement = $pdo->prepare("SELECT * FROM badges {$search} {$sort}");
        $statement->execute(['query' => $query]);
        $results = [];
        while ($row = $statement->fetch()) {
            $badge = new Badge((int)$row['badge_id'], $row['name'], $row['description'], (int)$row['value'], (int)$row['sort']);
            $results[] = $this->parseRow($badge);
        }

        if (count($results) == 0) {
            throw new APINotFoundException();
        }

        $this->setPayload($results)->render(200);
    }

    public function parseRow(Badge $badge): array
    {
        $row = $badge->getProps();

        if ($fields_sql = $this->parseQuery('fields', '')) {
            $fields = $this->parseFieldsSql($fields_sql);
            $row = array_filter($row, function ($key) use ($fields) {
                return in_array($key, $fields);
            }, ARRAY_FILTER_USE_KEY);
        }

        $row['value_description'] = Badge::getValueName($row['value']);
        $row['href'] = $this->parseLink($row['badge_id']);

        return $row;
    }
}
