<?php

namespace Vgsite\API;

use OutOfBoundsException;
use Vgsite\API\Exceptions\APIAuthorizationException;
use Vgsite\HTTP\Request;
use Vgsite\Registry;
use Vgsite\UserMapper;

class AccessToken
{
    /** @var string Access token */
    private $token;

    const EXPIRES = 36000;

    public function __construct($grant_type=null, $client_id=null, $client_secret=null)
    {
        if (! $client_id) {
            throw new APIAuthorizationException('Request does not include a Client ID', 'client_id');
        }

        if (! $client_secret) {
            throw new APIAuthorizationException('Request does not include a Client Secret', 'client_secret');
        }

        switch ($grant_type) {
            case 'client_credentials':
            case 'authorization_code':
                $this->validateCredentials($client_id, $client_secret);
                $this->generateToken($client_id);
                break;

            case 'password':
                throw new APIAuthorizationException("Grant type `password` is not operational yet.", 'grant_type');

                // This works, but cannot yet generate token given user info
                try {
                    $user = Registry::getMapper(null, UserMapper::class)->findByUsername($client_id);
                    $user->verifyPassword($client_secret);
                } catch (\Exception $e) {
                    throw new APIAuthorizationException($e);
                }
                break;

            default:
                throw new APIAuthorizationException("Requested Greant Type `{$grant_type}` is not recognized.", 'grant_type');
        }

    }

    public function __toString()
    {
        return $this->token;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    private function generateToken(int $client_id): void
    {
        $token = bin2hex(random_bytes(32));

        $sql = "INSERT INTO `api_access` (`client_id`, `access_token`, `expires`) VALUES (?, ?, TIMESTAMPADD(SECOND,".self::EXPIRES.",NOW()));";
        $statement = Registry::get('pdo')->prepare($sql);
        $statement->execute([$client_id, $token]);

        $this->token = $token;
    }

    private function validateCredentials(int $client_id, string $client_secret): void
    {
        $statement = Registry::get('pdo')->prepare('SELECT 1 FROM `api_clients` WHERE `client_id`=? AND `client_secret`=? LIMIT 1');
        $statement->execute([$client_id, $client_secret]);
        if (! $statement->fetchColumn()) {
            throw new APIAuthorizationException('Client ID or Client Secret could not be validated.');
        }
    }

    public static function validateToken(string $token): void
    {
        $statement = Registry::get('pdo')->prepare('SELECT 1 FROM `api_access` WHERE `access_token`=? AND `expires` > NOW() LIMIT 1');
        $statement->execute([$token]);
        if (!$statement->fetchColumn()) {
            throw new APIAuthorizationException('Access token not valid.');
        }
    }

    public static function assertAuthorization(Request $request): void
    {
        if (! $request->hasHeader('Authorization')) {
            throw new APIAuthorizationException('Request must include header `Authorization` with a token (`Bearer <token>`)');
        }

        $test = '/Bearer (?P<token>[a-zA-Z0-9]+)/i';
        preg_match($test, $request->getHeader('Authorization'), $matches);
        if (! $token = $matches['token']) {
            throw new APIAuthorizationException('Could not extract token from Authorization header. Try: `Authorization: Bearer <TOKEN>');
        }

        static::validateToken($token);
    }
}
