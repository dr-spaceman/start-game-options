<?php

namespace Vgsite\API;

use Respect\Validation\Validator as v;
use Vgsite\User;
use Vgsite\API\Exceptions\APIInvalidArgumentException;
use Vgsite\API\Exceptions\APINotFoundException;
use Vgsite\HTTP\Request;
use Vgsite\Registry;
use Vgsite\UserMapper;

/**
 * @OA\Schema(schema="user",
 *     type="object",
 *     @OA\Property(property="id", type="string"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="genre", type="string"),
 *     @OA\Property(property="platforms", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="release", type="string", format="date"),
 *     @OA\Property(property="href", type="string"),
 * )
 */

class UserController extends Controller
{
    const SORTABLE_FIELDS = ['user_id', 'username', 'email', 'rank'];
    const ALLOWED_FIELDS = ['user_id', 'password', 'username', 'email', 'rank'];
    const REQUIRED_FIELDS = ['user_id', 'username'];
    const BASE_URI = API_BASE_URI . '/users';

    /**
     * @OA\Get(
     *     path="/users/{id}",
     *     description="A user",
     *     @OA\Parameter(ref="#/components/parameters/id"),
     *     @OA\Parameter(ref="#/components/parameters/fields"),
     *     @OA\Response(response=200,
     *         description="Success!",
     *         @OA\JsonContent(ref="#/components/schemas/user")
     *     ),
     *     @OA\Response(response=404,
     *         description="Requested user not found",
     *     ),
     * )
     */
    protected function getOne($id): void
    {
        if (! v::IntVal()->validate($id)) {
            throw new APIInvalidArgumentException('User ID must be numeric', 'id');
        }

        if (! $user = User::findById($id)) {
            throw new APINotFoundException();
        }
        
        $results[] = $this->parseRow($user);

        $this->setPayload($results)->render(200);
    }

    /**
     * @OA\Get(
     *     path="/users",
     *     description="A list of users",
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/per_page"),
     *     @OA\Parameter(ref="#/components/parameters/sort"),
     *     @OA\Parameter(ref="#/components/parameters/fields"),
     *     @OA\Parameter(ref="#/components/parameters/q"),
     *     @OA\Response(response=200,
     *         description="Success!",
     *         @OA\JsonContent(ref="#/components/schemas/user")
     *     ),
     * )
     */
    protected function getAll(): void
    {
        $pdo = Registry::get('pdo');

        $page = $this->parseQuery('page', 1);
        $per_page = $this->parseQuery('per_page', static::PER_PAGE);
        $sort_sql = $this->parseQuery('sort', '`username` ASC');
        $query = $this->parseQuery('q', '');
        $search = $query ? "`username` LIKE CONCAT('%', :query, '%')" : '';
        
        $statement = $pdo->prepare("SELECT count(1) FROM users {$search}");
        $statement->execute(['query' => $query]);
        $num_rows = $statement->fetchColumn();
        [$limit_min, $limit_max, $num_pages] = $this->convertPageToLimit($page, $per_page, $num_rows);

        $mapper = new UserMapper();
        $users = $mapper->findAll($search, $sort_sql, $limit_min, $limit_max, ['query' => $query]);

        if ($users->count == 0) {
            throw new APINotFoundException();
        }

        foreach ($users->getGenerator() as $user) {
            $results[] = $this->parseRow($user);
        }

        // $sql = "SELECT {$fields}, `user_id` FROM users {$search} ORDER BY {$sort_sql} LIMIT {$limit_min}, {$limit_max}";
        // $statement = $pdo->prepare($sql);
        // $statement->execute(['query' => $query]);
        // $results = Array();
        // while ($row = $statement->fetch()) {
        //     $results[] = $this->parseRow($row);
        // }


        $links = $this->buildLinks($page, $num_pages);

        $this->setPayload($results, $links)->render(200);
    }

    public function parseRow(User $user): array
    {
        $row = $user->getProps();

        $fields = [];
        if ($fields_sql = $this->parseQuery('fields', '')) {
            $fields = $this->parseFieldsSql($fields_sql);
            $row = array_filter($row, function ($key) use ($fields) {
                return in_array($key, $fields);
            }, ARRAY_FILTER_USE_KEY);
        }

        // Don't include password unless it's explicitly requested
        if (!in_array('password', $fields)) {
            unset($row['password']);
        }

        $row['href'] = $this->parseLink($row['user_id']);

        return $row;
    }
}
