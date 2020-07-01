<?php

namespace Vgsite\API;

use Vgsite\API\Exceptions\APIInvalidArgumentException;
use Vgsite\API\Exceptions\APINotFoundException;

abstract class Controller
{

    private $request_method;
    protected $queries = Array();

    public function __construct(string $request_method, array $queries=[])
    {
        $this->request_method = $request_method;
        $this->queries = $queries;
    }

    public function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (! $this->queries[0]) {
                    $response = $this->getAll();
                } else {
                    $response = $this->getOne($this->queries[0]);
                };
                break;
            case 'POST':
                $response = $this->createFromRequest();
                break;
            case 'PUT':
                $response = $this->updateFromRequest();
                break;
            case 'DELETE':
                $response = $this->delete();
                break;
            default:
                throw new APIInvalidArgumentException(
                    sprintf(
                        'Request Method not valid (%s received). Try one of: GET, POST, PUT, DELETE.', 
                        $this->request_method
                    ), 400
                );
        }

        header($response['status_code_header']);
        if ($response['payload']) {
            echo json_encode($response['payload']);
        }
    }

    abstract protected function getOne($id): array;
    abstract protected function getAll(): array;

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

    private function validatePerson($input)
    {
        if (!isset($input['firstname'])) {
            return false;
        }
        if (!isset($input['lastname'])) {
            return false;
        }
        return true;
    }
}
