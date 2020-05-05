<?php declare(strict_types=1);

require_once dirname(__FILE__) . '/../config/bootstrap_tests.php';

use PHPUnit\Framework\TestCase;
use Vgsite\Registry;

class DBTest extends TestCase
{
    public function testDBConnection()
    {
        $pdo = Registry::instance()->get('pdo');
        $this->assertInstanceOf(PDO::class, $pdo);
        $pdo2 = Registry::instance()->get('pdo');
        $this->assertEquals($pdo, $pdo2);
    }

    public function testDBInsertFailsOnInvalidColumnNames()
    {
        $this->expectException(\PDOException::class);
        $sql = sprintf("INSERT INTO `test` (`foo`, `invalid_column`) VALUES ('fuuuu', 'uuu');");
        $GLOBALS['pdo']->query($sql);
    }

    public function testDBInsert()
    {
        $sql = "INSERT INTO `test` (`foo`, `bar`) VALUES ('fuuuu', ?);";
        $statement = $GLOBALS['pdo']->prepare($sql);
        $this->assertNotFalse($statement->execute([TEST_ID]));
        $this->assertEquals(1, $statement->rowCount());
    }
    
    public function testDBFetch()
    {
        $sql = sprintf("SELECT foo, bar FROM test WHERE bar='%s' LIMIT 1", TEST_ID);
        $stmt = $GLOBALS['pdo']->query($sql);
        $this->assertStringContainsString('fuu', $stmt->fetchColumn());

        $data = $GLOBALS['pdo']->query("SELECT * FROM users")->fetchAll(PDO::FETCH_UNIQUE);
        $this->assertEquals($data[TEST_USER_ID]['email'], 'test@test.com');

        $stmt = $GLOBALS['pdo']->query("SELECT 1 FROM users WHERE email='foo123@marypoppins69burt.com'");
        $user_exists = $stmt->fetchColumn();
        $this->assertFalse($user_exists);
    }

    public function testDBUpdate()
    {
        $pdo = Registry::instance()->get('pdo');

        $sql = sprintf("UPDATE `test` SET foo='fuuuuuuuuuu' WHERE bar='%s';", TEST_ID);
        $statement = $pdo->prepare($sql);
        $statement->execute();
        $this->assertEquals(1, $statement->rowCount());

        // Check if PDO::rowcount() considers update of identical data
        // Required setting: PDO::MYSQL_ATTR_FOUND_ROWS => true
        $statement = $pdo->prepare($sql);
        $statement->execute();
        $this->assertEquals(1, $statement->rowCount());
    }

    public function testDBDelete()
    {
        $sql = "DELETE FROM `test` WHERE bar=?";
        $statement = $GLOBALS['pdo']->prepare($sql);
        $this->assertNotFalse($statement->execute([TEST_ID]));
        $this->assertEquals(1, $statement->rowCount());
    }

    public function testInstantiated()
    {
        $obj = \Vgsite\TestClassInstantiated::instance($GLOBALS['pdo'])->get(1);
        $this->assertEquals(1, $obj->id);
    }
}
