<?php

namespace Vgsite\API;

use OutOfBoundsException;
use Respect\Validation\Validator as v;
use Vgsite\Album;
use Vgsite\API\Exceptions\APIInvalidArgumentException;
use Vgsite\API\Exceptions\APINotFoundException;
use Vgsite\Registry;
use Vgsite\AlbumMapper;
use Vgsite\API\Exceptions\APIException;

/**
 * @OA\Schema(schema="album",
 *     type="object",
 *     @OA\Property(property="id", type="string"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="subtitle", type="string"),
 *     @OA\Property(property="keywords", type="boolean"),
 *     @OA\Property(property="publisher", type="string"),
 *     @OA\Property(property="cid", type="string"),
 *     @OA\Property(property="albumid", type="string"),
 *     @OA\Property(property="release", type="string"),
 *     @OA\Property(property="price", type="string"),
 *     @OA\Property(property="href", type="string"),
 * )
 */

class AlbumController extends Controller
{
    const SORTABLE_FIELDS = ['id', 'title', 'publisher', 'release', 'series'];
    const ALLOWED_FIELDS = [
        'id', 'title', 'subtitle', 'keywords', 'coverimg', 'publisher', 'cid', 'albumid',
        'release', 'price', 'compose', 'arrange', 'perform', 'series', 'new', 'view', 
        'media', 'path'];
    const REQUIRED_FIELDS = ['id', 'title', 'subtitle'];
    const BASE_URI = API_BASE_URI . '/albums';

    protected function findOrFail(int $id): Album
    {
        if (!v::IntVal()->validate($id)) {
            throw new APIInvalidArgumentException('User ID must be numeric', 'id');
        }

        try {
            $album = Registry::getMapper(Album::class)->findById($id, false);
        } catch (OutOfBoundsException $e) {
            throw new APINotFoundException($e);
        }

        return $album;
    }

    /**
     * @OA\Get(
     *     path="/albums/{id}",
     *     description="An album",
     *     operationId="Albums:GetOne",
     *     @OA\Parameter(ref="#/components/parameters/id"),
     *     @OA\Parameter(ref="#/components/parameters/fields"),
     *     @OA\Response(response="200",
     *         description="Success!",
     *         @OA\JsonContent(ref="#/components/schemas/album")
     *     ),
     *     @OA\Response(response="404",
     *         description="Requested album not found",
     *     ),
     * )
     */
    protected function getOne($id): void
    {
        $album = $this->findOrFail($id);

        $results[] = $this->parseRow($album);

        $this->setPayload($results)->render(200);
    }

    /**
     * @OA\Get(
     *     path="/albums",
     *     description="A list of albums",
     *     operationId="Albums:GetAll",
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/per_page"),
     *     @OA\Parameter(ref="#/components/parameters/sort"),
     *     @OA\Parameter(ref="#/components/parameters/fields"),
     *     @OA\Parameter(ref="#/components/parameters/q"),
     *     @OA\Response(response="200",
     *         description="Success!",
     *         @OA\JsonContent(ref="#/components/schemas/album")
     *     ),
     * )
     */
    protected function getAll(): void
    {
        $pdo = Registry::get('pdo');

        $page = $this->parseQuery('page', 1);
        $per_page = $this->parseQuery('per_page', static::PER_PAGE);
        $sort_sql = $this->parseQuery('sort', '`title` ASC');
        $query = $this->parseQuery('q', '');
        $search = $query ? "`keywords` LIKE CONCAT('%', :query, '%')" : '';
        
        $statement = $pdo->prepare("SELECT count(1) FROM albums {$search}");
        $statement->execute(['query' => $query]);
        $num_rows = $statement->fetchColumn();
        [$limit_min, $limit_max, $num_pages] = $this->convertPageToLimit($page, $per_page, $num_rows);

        $mapper = new AlbumMapper();
        $albums = $mapper->findAll($search, $sort_sql, $limit_min, $limit_max, ['query' => $query]);

        if ($albums->count == 0) {
            throw new APINotFoundException();
        }

        foreach ($albums->getGenerator() as $album) {
            $results[] = $this->parseRow($album);
        }

        // $sql = "SELECT {$fields}, `album_id` FROM albums {$search} ORDER BY {$sort_sql} LIMIT {$limit_min}, {$limit_max}";
        // $statement = $pdo->prepare($sql);
        // $statement->execute(['query' => $query]);
        // $results = Array();
        // while ($row = $statement->fetch()) {
        //     $results[] = $this->parseRow($row);
        // }


        $links = $this->buildLinks($page, $num_pages);

        $this->setPayload($results, $links)->render(200);
    }

    public function parseRow(Album $album): array
    {
        $row = $album->getProps();

        if ($fields_sql = $this->parseQuery('fields', '')) {
            $fields = $this->parseFieldsSql($fields_sql);
            $row = array_filter($row, function ($key) use ($fields) {
                return in_array($key, $fields);
            }, ARRAY_FILTER_USE_KEY);
        }

        $row['links'] = array(
            "page" => 'http://' . getenv('HOST_DOMAIN') . $album->getUrl(),
        );

        $row['href'] = $this->parseLink($row['id']);

        return $row;
    }

    /**
     * @OA\Post(
     *     path="/albums/",
     *     description="Create an album",
     *     operationId="Albums:Create",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/album")
     *     ),
     *     @OA\Response(response="200",
     *         description="Album modified",
     *         @OA\JsonContent(ref="#/components/schemas/album")
     *     ),
     *     @OA\Response(response="401",
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(response="403",
     *         description="Forbidden",
     *     ),
     *     @OA\Response(response="409",
     *         description="Conflict: Parameter not valid",
     *     ),
     * )
     */
    protected function createFromRequest($body): void
    {
        $input = $this->parseBodyJson($body);
        // Force prototype album
        $input['id'] = -1;

        try {
            $album = new Album($input);
        } catch (\OutOfRangeException $e) {
            throw new APIInvalidArgumentException($e, '', '', 409);
        } catch (\Exception $e) {
            throw new APIInvalidArgumentException($e);
        }

        Registry::getMapper(Album::class)->insert($album);

        $this->getOne($album->getId());
    }

    /**
     * @OA\Patch(
     *     path="/albums/{id}",
     *     description="Modify an album",
     *     operationId="Albums:Patch",
     *     @OA\Parameter(ref="#/components/parameters/id"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/album")
     *     ),
     *     @OA\Response(response="200",
     *         description="Album modified",
     *         @OA\JsonContent(ref="#/components/schemas/album")
     *     ),
     *     @OA\Response(response="401",
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(response="403",
     *         description="Forbidden",
     *     ),
     *     @OA\Response(response="404",
     *         description="Requested album not found",
     *     ),
     *     @OA\Response(response="409",
     *         description="Conflict: Parameter not valid",
     *     ),
     * )
     */
    protected function updateFromRequest($id, $body): void
    {
        // validate album object
        $album = $this->findOrFail($id);

        $input = $this->parseBodyJson($body);

        try {
            foreach ($input as $key => $value) {
                $album->setProp($key, $value);
            }
        } catch (\OutOfRangeException $e) {
            throw new APIInvalidArgumentException($e, '', '', 409);
        } catch (\Exception $e) {
            throw new APIInvalidArgumentException($e);
        }

        Registry::getMapper(Album::class)->save($album);

        $this->getOne($id);
    }

    /**
     * @OA\Delete(
     *     path="/albums/{id}",
     *     description="Remove an album",
     *     operationId="Albums:Remove",
     *     @OA\Parameter(ref="#/components/parameters/id"),
     *     @OA\Response(response=204, description="Album successfully removed")
     * )
     */
    protected function delete($id): void
    {
        // validate album object
        $album = $this->findOrFail($id);

        Registry::getMapper(Album::class)->delete($album);

        $this->render(204);
    }
}
