<?php declare(strict_types=1);

require_once dirname(__FILE__) . '/../config/bootstrap_api.php';
require_once dirname(__FILE__) . '/../config/bootstrap_tests.php';

use PHPUnit\Framework\TestCase;
use Respect\Validation\Validator as v;
use Vgsite\API\AccessToken;
use Vgsite\API\Exceptions\APIAuthorizationException;

class APITest extends TestCase
{
    protected static $client;

    public static function setUpBeforeClass(): void
    {
        $client = new GuzzleHttp\Client(['base_uri' => API_BASE_URL.'/', 'http_errors' => false]);
        self::$client = $client;

        echo API_BASE_URL;
    }

    public function testHttpClient()
    {
        $this->assertInstanceOf(GuzzleHttp\Client::class, self::$client);
        $this->assertTrue(method_exists(self::$client, 'get'));
    }

    public function testClientCanConnectToApi()
    {
        $this->assertInstanceOf(GuzzleHttp\Client::class, self::$client);
        $response = self::$client->get('users');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json; charset=UTF-8', $response->getHeaderLine('content-type'));
        $body = (string) $response->getBody();
        $response_obj = json_decode($body, true);
        $this->assertEquals('collection', array_keys((array) $response_obj)[0]);
        $this->assertArrayHasKey('items', $response_obj['collection']);
    }

    public function testInvalidRequestMethod()
    {
        $response = self::$client->request('INVALID_REQ_METHOD', 'search?q=foo');
        $this->assertEquals(405, $response->getStatusCode());
    }

    public function testSearchMethodReturns422WhenNoQueryGiven()
    {
        $response = self::$client->get('search');
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testSearchMethodCanFindDonkeyKong()
    {
        $response = self::$client->get('search?q=donkey');
        $this->assertEquals(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $response_obj = json_decode($body, true);
        $items = $response_obj['collection']['items'];
        $this->assertTrue(count($items) > 0);
        $found_Donkey_Kong = array_search('Donkey Kong', array_column($items, 'title'));
        $this->assertNotFalse($found_Donkey_Kong);
    }

    public function testSearchMethodReturns404OnEmptySearchResults()
    {
        $response = self::$client->get('search?q=marypoppins69burt_foobar_loremipsum');
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testGameMethodFailsWhenNotGivenIntegerId()
    {
        $response = self::$client->get('games/notaninteger');
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testFieldsParameterRestriction()
    {
        $response = self::$client->get('users/1?fields=foo,bar');
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testGrantTokenFailsWhenGivenWackyCredentials()
    {
        $this->expectException(APIAuthorizationException::class);
        $access = new AccessToken(12345, 'invalid');
    }

    public function testGrantToken()
    {
        $access = new AccessToken('client_credentials', getenv('API_CLIENT_ID'), getenv('API_CLIENT_SECRET'));
        $this->assertNotEmpty($access->getToken());
        $this->assertEquals($access->getToken(), (string) $access->getToken());

        return $access->getToken();
    }

    public function testGrantTokenPost()
    {
        $url = sprintf(
            'token?grant_type=client_credentials&client_id=%s&client_secret=%s',
            getenv('API_CLIENT_ID'),
            getenv('API_CLIENT_SECRET')
        );
        $response = self::$client->post($url);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @depends testGrantToken */
    public function testValidateToken(string $token)
    {
        $this->assertNotEmpty($token);
        AccessToken::validateToken($token);
    }
}
