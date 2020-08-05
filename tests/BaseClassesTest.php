<?php

require_once dirname(__FILE__) . '/../config/bootstrap_tests.php';

use PHPUnit\Framework\TestCase;
use Vgsite\IdentityMap;
use Vgsite\Registry;
use Vgsite\User;

class BaseClassesTest extends TestCase
{
    public function testRegistrySetsAndGets()
    {
        Registry::set('foo', 'bar');
        $this->assertEquals('bar', Registry::get('foo'));
    }

    public function testIdentityMapSetsAndGets()
    {
        $props = [
            'user_id' => TEST_USER_ID,
            'username' => TEST_USER_USERNAME,
            'password' => TEST_USER_PASSWORD,
            'email' => TEST_USER_EMAIL,
        ];
        $user = new User($props);
        $imap = new IdentityMap();
        $imap->set($user);
        $this->assertTrue($imap->hasObject($user));
        $this->assertEquals(TEST_USER_ID, $imap->getId($user));

        return $imap;
    }

    /**
     * @depends testIdentityMapSetsAndGets
     */
    public function testIdentityMapReferencesChanges($imap)
    {
        $user = $imap->getObject(TEST_USER_ID);
        $this->assertTrue($imap->hasObject($user));
        $this->assertEquals(TEST_USER_ID, $imap->getId($user));

        $new_email = 'fuu@foobar.com';
        $user->setEmail($new_email);
        $this->assertEquals($new_email, $user->getEmail());

        $user_check = $imap->getObject(TEST_USER_ID);
        $this->assertEquals($new_email, $user_check->getEmail());
    }
}
