<?php declare(strict_types=1);

require_once dirname(__FILE__) . '/../config/bootstrap_tests.php';

use PHPUnit\Framework\TestCase;
use Respect\Validation\Validator as v;
use Vgsite\Registry;
use Vgsite\Badge;
use Vgsite\BadgeCollection;
use Vgsite\BadgeMapper;
use Vgsite\User;

class APITest extends TestCase
{
    protected static $client;

    public static function setUpBeforeClass(): void
    {
        $client = new GuzzleHttp\Client(['base_uri' => 'http://vgsite/api/']);
        self::$client = $client;
    }

    public function testHttpClient()
    {
        $this->assertInstanceOf(GuzzleHttp\Client::class, self::$client);
        $this->assertTrue(method_exists(self::$client, 'get'));
    }

    public function testClientCanConnectToApi()
    {
        $this->assertInstanceOf(GuzzleHttp\Client::class, self::$client);
        $response = self::$client->get('/api');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json; charset=UTF-8', $response->getHeaderLine('content-type'));
        $body = (string) $response->getBody();
        $response_obj = json_decode($body, true);
        $this->assertEquals('collection', array_keys((array) $response_obj)[0]);
        $this->assertArrayHasKey('items', $response_obj['collection']);
    }

    public function testInvalidRequestMethod()
    {
        $response = self::$client->request('INVALID_REQ_METHOD', 'search/foo', ['http_errors' => false]);
        $this->assertEquals(400, $response->getStatusCode());
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

    public function testSearchMethodCannotFindSomething()
    {
        $response = self::$client->get('search?q=marypoppins69burt_foobar_loremipsum', ['http_errors' => false]);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testGameMethodFailsWhenNotGivenIntegerId()
    {
        $response = self::$client->get('games/notaninteger');
        $this->assertEquals(422, $response->getStatusCode());
    }
}