<?php

use PHPUnit\Framework\TestCase;
use Vgsite\User;
use Vgsite\DB;
use Verot\Upload;

define("TEST_USER_ID", 2);
define("TEST_IMAGE_SRC", "http://videogamin.squarehaven.com/magus.jpg");
define("TEST_ID", uniqid());

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

$pdo = DB::instance();

class ImageTest extends TestCase
{
    public function testUpload()
    {
        $upload = new Upload(TEST_IMAGE_SRC);
        $this->assertTrue($upload->uploaded);
        $upload->file_new_name_body = TEST_ID;
        $upload->process->(__DIR__.'/../'.Image::UPLOAD_TEMP_DIR);
    }
    public function testImageInsert()
    {
        $image = new Image(['img_name' => 'test.png'], $GLOBALS['pdo']);
        $this->assertTrue($image->insert());

        return $image;
    }

    /**
     @depends testImageInsert
     */
    public function testImageInsertDuplicateFailure($image)
    {
        $this->expectException(Exception::class);
        $image->insert();
    }
    
    public function testImageFetch()
    {
        $image = Image::getByName()
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
