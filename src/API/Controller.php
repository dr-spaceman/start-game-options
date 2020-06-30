<?php

namespace Vgsite\API;

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
                if ($this->queries[0]) {
                    $response = $this->getOne($this->queries[0]);
                } else {
                    $response = $this->getAll();
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
                $response = $this->notFoundResponse();
                break;
        }

        header($response['status_code_header']);
        if ($response['payload']) {
            echo json_encode($response['payload']);
        }
    }

    abstract protected function getOne($id): array;
    abstract protected function getAll(): array;

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

    protected function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'error' => 'Invalid input'
        ]);
        return $response;
    }

    protected function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
}
