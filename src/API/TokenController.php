<?php

namespace Vgsite\API;

use Vgsite\HTTP\Response;

class TokenController extends Controller
{
    const ALLOWED_METHODS = ['POST'];

    const BASE_URI = API_BASE_URI . '/token';
    
    protected function doProcessRequest(): void
    {
        if (!in_array($this->request->getMethod(), static::ALLOWED_METHODS)) {
            $this->invalidRequestMethod();
        }

        $this->assertBodyJson();
        $body_raw = $this->request->getBody();
        $input = $this->parseBodyJson($body_raw);

        $token_params = [
            $input['grant_type'],
            $input['client_id'],
            $input['client_secret']
        ];
        $access = new AccessToken(...$token_params);

        $response = new Response(200);
        $response->render(
            json_encode([
                "access_token" => $access->getToken(),
                "created" => date('Y-m-d H:i:s'),
                "expires_in" => $access::EXPIRES
            ])
        );
    }

    protected function getOne($id): void
    {
        $this->invalidRequestMethod();
    }
    
    protected function getAll(): void
    {
        $this->invalidRequestMethod();
    }
    
    protected function createFromRequest($body): void
    {
        $this->invalidRequestMethod();
    }
    
    protected function updateFromRequest($id, $body): void
    {
        $this->invalidRequestMethod();
    }
    
    protected function delete($id): void
    {
        $this->invalidRequestMethod();
    }
    
}
