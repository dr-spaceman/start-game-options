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

        $num_rows = $GLOBALS['pdo']->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $this->assertTrue($num_rows > 100);

        $foo = $GLOBALS['pdo']->query("SELECT * FROM test WHERE foo='baz_xyz'")->fetchColumn();
        var_dump($foo);

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
}
