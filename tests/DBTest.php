<?php declare(strict_types=1);

require_once dirname(__FILE__) . '/../config/bootstrap_tests.php';

use PHPUnit\Framework\TestCase;
use Vgsite\PDO\MyPDOStatement;
use Vgsite\Registry;

class DBTest extends TestCase
{
    protected static $pdo;

    public static function setUpBeforeClass(): void
    {
        $pdo = Registry::get('pdo');
        self::$pdo = $pdo;
    }
    
    public function testDBConnection()
    {
        $this->assertInstanceOf(PDO::class, self::$pdo);
        $pdo2 = Registry::get('pdo');
        $this->assertEquals(self::$pdo, $pdo2);
    }

    public function testDBStatementUsesClassExtension()
    {
        $sql = "SELECT 1 FROM `test`";
        $statement = self::$pdo->query($sql);
        $this->assertInstanceOf(MyPDOStatement::class, $statement);
    }

    public function testDBInsertFailsOnInvalidColumnNames()
    {
        $this->expectException(\PDOException::class);
        $sql = sprintf("INSERT INTO `test` (`foo`, `invalid_column`) VALUES ('fuuuu', 'uuu');");
        self::$pdo->query($sql);
    }

    public function testDBInsert()
    {
        $sql = "INSERT INTO `test` (`foo`, `bar`) VALUES ('fuuuu', ?);";
        $statement = self::$pdo->prepare($sql);
        $this->assertNotFalse($statement->execute([TEST_ID]));
        $this->assertEquals(1, $statement->rowCount());
    }
    
    public function testDBFetch()
    {
        $sql = sprintf("SELECT foo, bar FROM test WHERE bar='%s' LIMIT 1", TEST_ID);
        $stmt = self::$pdo->query($sql);
        $this->assertStringContainsString('fuu', $stmt->fetchColumn());

        $num_rows = self::$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $this->assertTrue($num_rows > 100);

        $foo = self::$pdo->query("SELECT * FROM test WHERE foo='baz_xyz'")->fetchColumn();

        $stmt = self::$pdo->query("SELECT 1 FROM users WHERE email='foo123@marypoppins69burt.com'");
        $user_exists = $stmt->fetchColumn();
        $this->assertFalse($user_exists);
    }

    public function testDBUpdate()
    {
        $pdo = Registry::get('pdo');

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
        $statement = self::$pdo->prepare($sql);
        $this->assertNotFalse($statement->execute([TEST_ID]));
        $this->assertEquals(1, $statement->rowCount());
    }
}
