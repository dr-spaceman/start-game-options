<?php

namespace Vgsite\API;

use OutOfBoundsException;
use Respect\Validation\Validator as v;
use Vgsite\API\Exceptions\APIException;
use Vgsite\User;
use Vgsite\API\Exceptions\APIInvalidArgumentException;
use Vgsite\API\Exceptions\APINotFoundException;
use Vgsite\Registry;
use Vgsite\UserMapper;

/**
 * @OA\Schema(schema="user",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="username", type="string"),
 *     @OA\Property(property="password", type="string", description="A hashed string; Only given if explicitly requested using the `fields` parameter."),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="verified", type="boolean"),
 *     @OA\Property(property="gender", type="string", description="enum('he', 'she', 'them')"),
 *     @OA\Property(property="region", type="string", description="enum('us', 'jp', 'eu', 'au')"),
 *     @OA\Property(property="rank", type="integer"),
 *     @OA\Property(property="avatar", type="string"),
 *     @OA\Property(property="timezone", type="string"),
 *     @OA\Property(property="data_created", type="date-time"),
 *     @OA\Property(property="data_modified", type="date-time"),
 *     @OA\Property(property="activity", type="date-time"),
 *     @OA\Property(property="previous_activity", type="date-time"),
 *     @OA\Property(property="href", type="string"),
 * )
 */

class UserController extends Controller
{
    const SORTABLE_FIELDS = ['user_id', 'username', 'email', 'rank', 'region', 'timezone'];
    const ALLOWED_FIELDS = ['user_id', 'password', 'username', 'email', 'rank', 'region', 'timezone'];
    const REQUIRED_FIELDS = ['user_id', 'username'];
    const BASE_URI = API_BASE_URI . '/users';

    /**
     * @OA\Get(
     *     path="/users/{id}",
     *     description="A user",
     *     operationId="Users:GetOne",
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
        $user = $this->findById($id);
        
        $results[] = $this->parseRow($user);

        $this->setPayload($results)->render(200);
    }

    protected function findById(int $id): User
    {
        if (! v::IntVal()->validate($id)) {
            throw new APIInvalidArgumentException('User ID must be numeric', 'id');
        }

        try {
            $mapper = Registry::getMapper(User::class);
            $user = $mapper->findById($id, false);
        } catch (OutOfBoundsException $e) {
            throw new APINotFoundException($e);
        }

        return $user;
    }

    /**
     * @OA\Get(
     *     path="/users",
     *     description="A list of users",
     *     operationId="Users:GetAll",
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

        $mapper = Registry::getMapper(User::class);
        $users = $mapper->findAll($search, $sort_sql, $limit_min, $limit_max, ['query' => $query]);

        if ($users->count == 0) {
            throw new APINotFoundException();
        }

        foreach ($users->getGenerator() as $user) {
            $results[] = $this->parseRow($user);
        }

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
        } else {
            foreach (['data_created', 'data_modified', 'activity', 'previous_activity'] as $key) {
                $row[$key] = $user->{$key} ?: null;
            }
        }

        if (isset($row['verified'])) {
            $row['verified'] = $row['verified'] ? true : false;
        }

        // Don't include password unless it's explicitly requested
        if (!in_array('password', $fields)) {
            unset($row['password']);
        }

        $row['links'] = array(
            "page" => 'http://' . getenv('HOST_DOMAIN') . $user->getUrl(),
        );

        $row['href'] = $this->parseLink($row['user_id']);

        return $row;
    }

    /**
     * @OA\Post(
     *     path="/users/",
     *     description="Create a user",
     *     operationId="Users:Create",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/user")
     *     ),
     *     @OA\Response(response=200,
     *         description="User modified",
     *         @OA\JsonContent(ref="#/components/schemas/user")
     *     ),
     *     @OA\Response(response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(response=403,
     *         description="Forbidden",
     *     ),
     *     @OA\Response(response=409,
     *         description="Conflict: Parameter not valid",
     *     ),
     * )
     */
    protected function createFromRequest($body): void
    {
        $input = $this->parseBodyJson($body);
        // Force prototype user
        $input['user_id'] = -1;

        try {
            $user = new User($input);
            // Hash password
            $user->setPassword($input['password'], true);
        } catch (\OutOfRangeException $e) {
            throw new APIInvalidArgumentException($e, '', '', 409);
        } catch (\Exception $e) {
            throw new APIInvalidArgumentException($e);
        }

        /** @var UserMapper */
        $mapper = Registry::getMapper(User::class);
        $mapper->insert($user);

        $this->getOne($user->getId());
    }

    /**
     * @OA\Patch(
     *     path="/users/{id}",
     *     description="Modify a user",
     *     operationId="Users:Patch",
     *     @OA\Parameter(ref="#/components/parameters/id"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/user")
     *     ),
     *     @OA\Response(response=200,
     *         description="User modified",
     *         @OA\JsonContent(ref="#/components/schemas/user")
     *     ),
     *     @OA\Response(response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(response=403,
     *         description="Forbidden",
     *     ),
     *     @OA\Response(response=404,
     *         description="Requested user not found",
     *     ),
     *     @OA\Response(response=409,
     *         description="Conflict: Parameter not valid",
     *     ),
     * )
     */
    protected function updateFromRequest($id, $body): void
    {
        // validate user object
        $user = $this->findById($id);

        $input = $this->parseBodyJson($body);

        try {
            if ($input['password']) {
                // Hash password
                $user->setPassword($input['password'], true);
                unset($input['password']);
            }

            foreach ($input as $key => $value) {
                $user->setProp($key, $value);
            }
        } catch (\OutOfRangeException $e) {
            throw new APIInvalidArgumentException($e, '', '', 409);
        } catch (\Exception $e) {
            throw new APIInvalidArgumentException($e);
        }

        /** @var UserMapper */
        $mapper = Registry::getMapper(User::class);
        $mapper->save($user);

        $this->getOne($id);
    }

    protected function delete($id): void
    {
        throw new APIException('Method not supported', null, 'METHOD_NOT_SUPPORTED', 405);
    }
}
