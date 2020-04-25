<?php

define("TEST_USER_ID", 2);

use PHPUnit\Framework\TestCase;
use Vgsite\User;
use Vgsite\DB;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

$pdo = DB::instance();

class DBTest extends TestCase
{
    public function testDBConnection()
    {
        $this->assertInstanceOf(PDO::class, $GLOBALS['pdo']);
    }

    public function testDBInsert()
    {
        $id_string = uniqid();
        $sql = sprintf("INSERT INTO `test` (`foo`, `bar`) VALUES ('fuuuu', '%s');", $id_string);
        $this->assertNotFalse($GLOBALS['pdo']->query($sql));

        return $id_string;
    }
    
    /**
     @depends testDBInsert
     */
    public function testDBFetch($id_string)
    {
        $sql = sprintf("SELECT foo, bar FROM test WHERE bar='%s' LIMIT 1", $id_string);
        $stmt = $GLOBALS['pdo']->query($sql);
        $this->assertStringContainsString('fuu', $stmt->fetchColumn());
        $this->assertFalse(strpos($stmt->fetchColumn(1), 'baz'));

        $data = $GLOBALS['pdo']->query("SELECT * FROM users")->fetchAll(PDO::FETCH_UNIQUE);
        $this->assertEquals($data[TEST_USER_ID]['email'], 'test@test.com');

        $stmt = $GLOBALS['pdo']->query("SELECT 1 FROM users WHERE email='foo123@marypoppins69burt.com'");
        $user_exists = $stmt->fetchColumn();
        $this->assertFalse($user_exists);
    }

    /**
     @depends testDBInsert
     */
    public function testDBDelete($id_string)
    {
        $sql = sprintf("DELETE FROM `test` WHERE bar = '%s'", $id_string);
        $this->assertNotFalse($GLOBALS['pdo']->query($sql));
    }

    public function testInstantiated()
    {
        $obj = \Vgsite\TestClassInstantiated::instance($GLOBALS['pdo'])->get(1);
        var_dump($obj);
        $this->assertEquals(1, $obj->id);
    }
}
