<?php

namespace Vgsite\API;

use Vgsite\API\Exceptions\APIException;
use Vgsite\API\Exceptions\APIInvalidArgumentException;
use Vgsite\API\Exceptions\APINotFoundException;
use Vgsite\HTTP\Request;
use Vgsite\HTTP\Response;

abstract class Controller
{

    protected $queries = Array();
    protected $response;

    public function __construct(Request $request)
    {
        $this->request = $request;

        $res = new Response();
        $res->withHeader('Access-Control-Allow-Origin', '*');
        $res->withHeader('Content-Type', 'application/json; charset=UTF-8');
        $res->withHeader('Access-Control-Allow-Methods', 'OPTIONS,GET,POST,PUT,DELETE');
        $res->withHeader('Access-Control-Max-Age', '3600');
        $res->withHeader('Access-Control-Allow-Headers', 'Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
        $this->response = $res;
    }

    public function processRequest(): Controller
    {
        try {
            switch ($this->request->getMethod()) {
                case 'GET':
                    if (! $this->request->getPath()[1]) {
                        return $this->getAll();
                    } else {
                        return $this->getOne($this->request->getPath()[1]);
                    };
                break;
                case 'POST':
                    return $this->createFromRequest();
                break;
                case 'PUT':
                    return $this->updateFromRequest();
                break;
                case 'DELETE':
                    return $this->delete();
                break;
                default:
                    $message = sprintf(
                        'Request Method not valid (%s received). Try one of: GET, POST, PUT, DELETE.', 
                        $this->request_method
                    );
                    throw new APIException($message, null, 'INVALID_REQUEST_METHOD', 400);
            }
        } catch (APIException $e) {
            $code = $e->getCode();
            $this->response->withStatus($code);
            $this->response->getBody()->write($e);

            return $this;
        } catch (\Exception $e) {
            $this->response->withStatus(500);
            $message = ['message' => 'Server error', 'errors' => ['message' => (string) $e]];
            $this->response->getBody()->write(json_encode($message));

            return $this;
        }
    }

    public function processSearchRequest()
    {
        if ($this->request_method != 'GET') {
            throw new APIInvalidArgumentException(
                sprintf(
                    'Request Method not valid (%s received). Try: GET.',
                    $this->request_method
                ),
                405
            );
        }

        $response = $this->getSearchResults($this->queries[0]);

        header($response['status_code_header']);
        if ($response['payload']) {
            echo json_encode($response['payload']);
        }
    }

    abstract protected function getOne($id): Controller;
    abstract protected function getAll(): Controller;

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

    public function render(): void
    {
        header(
            sprintf(
                'HTTP/%s %d %s', 
                $this->response->getProtocolVersion(), 
                $this->response->getStatusCode(), 
                $this->response->getReasonPhrase()
            )
        );
        foreach ($this->response->getHeaders() as $header_name => $headers) {
            header(sprintf("%s: %s", $header_name, $this->response->getHeaderLine($header_name)));
        };

        echo $this->response->getBody();
    }
}
