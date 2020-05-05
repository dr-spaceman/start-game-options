<?php

require_once dirname(__FILE__) . '/../config/bootstrap_tests.php';

use PHPUnit\Framework\TestCase;
use Vgsite\User;
use Verot\Upload;

define("TEST_IMAGE_SRC", "http://videogamin.squarehaven.com/magus.jpg");

class ImageTest extends TestCase
{
    public function testUpload()
    {
        $upload = new Upload(TEST_IMAGE_SRC);
        $this->assertTrue($upload->uploaded);
        $upload->file_new_name_body = TEST_ID;
        $upload_dir = __DIR__.'/../'.Image::UPLOAD_TEMP_DIR;
        $upload_temp_img = $upload_dir.'/'.TEST_ID.'.jpg';
        $upload->process->($upload_dir);
        $this->assertTrue(file_exists($upload_temp_img));

        return $upload_temp_img;
    }

    /**
     * @depends testUpload
     */
    public function testImageInsert($image_loc)
    {
        $image = Image::getBySrc($image_loc, $GLOBALS['pdo'], $GLOBALS['logger']);
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
}
