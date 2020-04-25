<?php

define("TEST_USER_ID", 2);

use PHPUnit\Framework\TestCase;
use Vgsite\User;
use Vgsite\DB;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

// require __DIR__."/../config/config_db.php";
$pdo = DB::instance();

class DBTest extends TestCase
{
    public function testDBConnection()
    {
        $this->assertInstanceOf(PDO::class, $GLOBALS['pdo']);
    }
    
    public function testDBFetch()
    {
        $stmt = $GLOBALS['pdo']->query("SELECT foo, bar FROM test WHERE id=1 LIMIT 1");
        $this->assertEquals($stmt->fetchColumn(), 'foo');
        $this->assertStringContainsString($stmt->fetchColumn(1), 'bar');
        $this->assertFalse(strpos($stmt->fetchColumn(1), 'baz'));

        $data = $GLOBALS['pdo']->query("SELECT * FROM users")->fetchAll(PDO::FETCH_UNIQUE);
        $this->assertEquals($data[TEST_USER_ID]['email'], 'test@test.com');

        $stmt = $GLOBALS['pdo']->query("SELECT 1 FROM users WHERE email='foo123@marypoppins69burt.com'");
        $user_exists = $stmt->fetchColumn();
        $this->assertFalse($user_exists);
    }

    public function testDBInsert()
    {
        $id_string = uniqid();
        $sql = sprintf("INSERT INTO `test` (`foo`, `bar`) VALUES ('fuuuu', '%s');", $id_string);
        $this->assertNotFalse($GLOBALS['pdo']->query($sql));

        $sql = sprintf("SELECT bar FROM test WHERE bar='%s' LIMIT 1", $id_string);
        $stmt = $GLOBALS['pdo']->query($sql);
        $this->assertEquals($stmt->fetchColumn(), $id_string);
    }

    public function testUserStaticFetch()
    {
        // $user = User::instance($pdo, $logger)->getByUsername('test');

    }
}
