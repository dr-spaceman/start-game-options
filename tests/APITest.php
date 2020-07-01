<?php

declare(strict_types=1);

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
        $client = new GuzzleHttp\Client(['base_uri' => 'http://vgsite/api/v1/']);
        self::$client = $client;
    }

    public function testApiClient()
    {
        $this->assertInstanceOf(GuzzleHttp\Client::class, self::$client);
    }

    public function testClientCanConnectToApi()
    {
        $this->assertInstanceOf(GuzzleHttp\Client::class, self::$client);
        $response = self::$client->get('/api/v1');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json; charset=UTF-8', $response->getHeaderLine('content-type'));
        $body = (string) $response->getBody();
        $response_obj = json_decode($body);
        $this->assertEquals('resources', array_keys((array) $response_obj)[0]);
    }

    public function testSearchMethodCanFindDonkeyKong()
    {
        $response = self::$client->get('search/donkey');
        $this->assertEquals(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $response_obj = json_decode($body, true);
        $this->assertEquals('hits', array_keys((array) $response_obj)[0]);
        $found_Donkey_Kong = array_filter ($response_obj['hits'], function($item) {
            if ($item['title'] == "Donkey Kong") return true;
        });
        $this->assertNotEmpty($found_Donkey_Kong);
    }

    public function testSearchMethodCannotFindSomething()
    {
        $response = self::$client->get('search/marypoppins69burt_foobar_loremipsum');
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testGameMethodFailsWhenNotGivenIntegerId()
    {
        $response = self::$client->get('game/notaninteger');
        $this->assertEquals(422, $response->getStatusCode());
    }
}