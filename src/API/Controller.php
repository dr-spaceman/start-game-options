<?php

namespace Vgsite\API;

use Exception;
use Vgsite\Registry;
use Vgsite\API\Exceptions\APIException;
use Vgsite\API\Exceptions\APIInvalidArgumentException;
use Vgsite\API\Exceptions\APINotFoundException;
use Vgsite\HTTP\Request;
use Vgsite\HTTP\Response;
use Respect\Validation\Validator as v;

/**
 * Request input, Response output
 */
abstract class Controller
{
    /** @var int Number of items per page */
    const PER_PAGE = 100;

    /** @var array For `?sort` parameter; List of keys to whitelist */
    const SORTABLE_FIELDS = Array();

    /** @var string The base URI for this constroller; Used to make links */
    const BASE_URI = API_BASE_URI . '/_';

    /** @var Request */
    protected $request;

    /** @var CollectionJson Collection+JSON object */
    private $collection;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->collection = new CollectionJson();
    }

    public function processRequest(): void
    {
        try {
            switch ($this->request->getMethod()) {
                case 'GET':
                    if (! $this->request->getPath()[1]) {
                        $this->getAll();
                    } else {
                        $this->getOne($this->request->getPath()[1]);
                    };
                break;
                case 'POST':
                    $this->createFromRequest();
                break;
                case 'PUT':
                    $this->updateFromRequest();
                break;
                case 'DELETE':
                    $this->delete();
                break;
                default:
                    $message = "Request Method not valid ({$this->request->getMethod()} received). Try one of: GET, POST, PUT, DELETE.";
                    throw new APIException($message, null, 'INVALID_REQUEST_METHOD', 405);
            }
        } catch (APIException $e) {
            $this->collection->setError($e->getErrorMessage());
            $this->render($e->getCode());
            throw new Exception($e);
        } catch (\Exception | \Error $e) {
            $error = ['title' => 'Server error', 'message' => $e->getMessage()];
            $this->collection->setError($error);
            $this->render(500);
            throw new Exception($e);
        }
    }

    abstract protected function getOne($id): void;
    abstract protected function getAll(): void;

    // Model response method
    private function getUser($id)
    {
        $result = $this->personGateway->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function createFromRequest()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validatePerson($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->personGateway->insert($input);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = null;
        return $response;
    }

    private function updateFromRequest()
    {
        $result = $this->personGateway->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validatePerson($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->personGateway->update($id, $input);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function delete()
    {
        $result = $this->personGateway->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $this->personGateway->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    public function setPayload(array $items, array $links=[]): self
    {
        $this->collection->setItems($items);

        if (! empty($links)) {
            $this->collection->setLinks($links);
        }

        return $this;
    }

    /**
     * Build an array of links based on pagination status for collection output
     *
     * @param integer $page Current page
     * @param integer $num_pages Total pages
     * 
     * @return array Array to push into $collection['links'] object
     */
    public function buildLinks(int $page, int $num_pages): array
    {
        if ($num_pages < 1) {
            return [];
        }
        
        $links = [
            'pagination' => [
                'page' => $page,
                'total_pages' => $num_pages,
            ]
        ];
        if ($page < $num_pages) {
            $req_query = $this->request->getQuery();
            $req_query['page'] = $page + 1;
            $querystring_next = http_build_query($req_query);

            $links['pagination']['next'] = [
                'href' => static::BASE_URI . '?' . $querystring_next
            ];
        }

        return $links;
    }

    /**
     * Render an HTTP response
     *
     * @param integer $code Status code
     * @param array $headers Key-value pairs
     * @param string $body Body; If null, renders the current collection object
     */
    public function render(int $code=200, array $headers=[], string $body=null): void
    {
        $response = new Response($code, $headers);
        $response->render($body ?? (string) $this->collection);
    }

    /**
     * Parse sort query to use variables freely
     *
     * @param string $sort_sql The MySQL fragment, Eg. "`fieldname` ASC"
     * 
     * @return array [fieldname, sort_direction(asc|desc)]
     */
    public function parseSortSql(string $sort_sql): array
    {
        $test = '/^`?(?P<sort>[a-z_\-]+)`? ?(?P<sort_by>asc|desc)+$/i';
        preg_match($test, $sort_sql, $matches);
        
        return [$matches['sort'], $matches['sort_by']];
    }

    /**
     * Parse a variable in the query string. Returned value is appropriate to
     * use to fetch data or some other operation.
     *
     * @param string $key Key in querystring
     * @param int|string $default Default value to return if the 
     * @param callable $test Additional test to perform; Throw exception on failure
     *
     * @return string Prepared MySQL query fragment
     */
    public function parseQuery(string $key, $default, callable $test=null): string
    {
        $query = $this->request->getQuery();
        $value = $query[$key];

        if (empty($value)) {
            return $default;
        }

        // Run default tests
        switch ($key) {
            case 'page':
                if (! v::IntVal()->validate($value)) {
                    throw new APIInvalidArgumentException('Property `page` must be an integer.', '?page');
                }
                if ($value < 1) {
                    throw new APIInvalidArgumentException('Property `page` must be an integer greater than zero.', '?page');
                }
            break;

            case 'per_page':
                // static invocation allows extending classes to modify the constant PER_PAGE
                if ($value > static::PER_PAGE) {
                    throw new APIInvalidArgumentException(
                        sprintf('Requested number of items per page `%s` exceeds the maximum of %d', $value, static::PER_PAGE), '?per_page'
                    );
                }
            break;

            case 'sort':
                $test_regex = '/^\??(sort=)?(?P<sort>[a-z\-_]*):?(?P<sort_by>asc|desc)*$/i';
                if (!preg_match($test_regex, $value, $matches)) {
                    throw new APIInvalidArgumentException('Sort parameter not in valid format. Try: `?sort={field_name}[:asc|desc]`', '?sort');
                }

                if (!empty(static::SORTABLE_FIELDS) && false === array_search($matches['sort'], static::SORTABLE_FIELDS)) {
                    throw new APIInvalidArgumentException(
                        sprintf(
                            'Requested sort key `%s` is out of the range of options available. Try one of: %s.', 
                            $matches['sort'], 
                            implode(', ', static::SORTABLE_FIELDS)
                        ), '?sort'
                    );
                }

                $value = sprintf('`%s` %s', $matches['sort'], $matches['sort_by'] ? strtoupper($matches['sort_by']) : 'ASC');
            break;

            case 'sort_dir':
                throw new APIInvalidArgumentException('The parameter `sort_dir` is depreciated. Try: `?sort={field_name}[:asc|desc]`', '?sort_dir');
            break;

            case 'q':
                if (mb_strlen($value) < 3) {
                    throw new APIInvalidArgumentException('Search query must be at least 3 characters long', '?q');
                }
            break;

            case 'fields':
                $fields_pass = explode(',', $value);
                $fields_pass = array_map(function ($field) {
                    $field = trim($field);
                    if (empty($field)) return null;
                    // Nullify any fields with anything except alphanumerics, -, _
                    if (preg_match('/[^a-z0-9\-_]/i', $field)) return null;
                    return $field;
                }, $fields_pass);
                $fields_pass = array_filter($fields_pass);

                if (empty($fields_pass)) {
                    return '*';
                }

                $value = implode(', ', array_map(function ($field) {
                    return '`' . $field . '`';
                }, $fields_pass));
        }

        if (isset($test)) {
            if (false === call_user_func($test, $value)) {
                throw new APIInvalidArgumentException(
                    sprintf('Invalid parameter given for key `%s`. Suggested value: %s.', $key, $default),
                    sprintf('?%s', $key)
                );
            }
        }

        return $value;
    }

    /**
     * Make a SQL LIMIT query fragment based on page request
     *
     * @param integer $page
     * @param integer $per_page
     * @param integer $total_num Optional
     * 
     * @return array [$min, $max[, $num_pages]] => 'LIMIT $min $max'
     */
    public function convertPageToLimit(int $page, int $per_page, int $total_num=null): array
    {
        $min = ($page - 1) * $per_page;
        $return = [$min, $per_page];
        if (! empty($total_num)) {
            $num_pages = ceil($total_num / $per_page);
            array_push($return, $num_pages);
        }

        return $return;
    }

    /**
     * Make an API href link
     *
     * @param string $id Id
     * 
     * @return string Href link
     */
    public function parseLink(string $id): string
    {
        return static::BASE_URI . '/' . $id;
    }
}
