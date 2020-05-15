<?php

require_once dirname(__FILE__) . '/../config/bootstrap_tests.php';

use PHPUnit\Framework\TestCase;
use Vgsite\User;
use Vgsite\Upload;
use Vgsite\Image;
use Vgsite\ImageMapper;
use Vgsite\ImageCollection;
use Vgsite\Collection;
use Vgsite\Exceptions\UploadException;

define("TEST_IMAGE_SRC", "http://videogamin.squarehaven.com/magus.jpg");
define("TEST_IMAGE_SRC_BOX", "http://videogamin.squarehaven.com/chrono_trigger-na_box.jpg");

class ImageTest extends TestCase
{
    public function testImageSizeConstants()
    {
        $this->assertEquals('op', Image::OPTIMAL);
        $sizes = Image::getSizes();
        $this->assertIsArray($sizes);
        $this->assertArrayHasKey(Image::MEDIUM, $sizes);
        $this->assertEquals([100, 100], Image::getSize(Image::THUMBNAIL));
    }

    public function testImageSizesThrowExceptionWhenInvalidArgumentGiven()
    {
        $this->expectException(InvalidArgumentException::class);
        Image::getSize('foo');
    }

    public function testImageCategoriesConstants()
    {
        $this->assertEquals(Image::SCREENSHOT, 1);
        $this->assertEquals(Image::getCategories()[Image::BOXART], 'Box art, Official');
        $this->assertEquals(Image::getCategoryName(Image::SCREENSHOT_TITLE), "Screenshots, Title screen");
        $this->assertEquals(Image::getCategoryDescription(Image::OFFICIALART), "Official illustrations");
    }

    public function testImageCategoriesThrowExceptionWhenInvalidArgumentGiven()
    {
        $this->expectException(InvalidArgumentException::class);
        Image::getCategoryName(99);
    }

    public function testImageCollectionConstruction()
    {
        $collection = new ImageCollection();
        $collection->description = 'Test Collection';
        $this->assertInstanceOf(ImageCollection::class, $collection);
        $this->assertTrue(is_int($collection->getId()));

        return $collection;
    }

    /**
     * @depends testImageCollectionConstruction
     */
    public function testUpload(ImageCollection $collection)
    {
        $upload = new Upload(TEST_IMAGE_SRC);
        $upload->setFilename(TEST_ID);
        $upload->prepare($collection->getId(), Image::OFFICIALART, $GLOBALS['current_user']);
        $this->assertFileExists(Image::IMAGES_DIR.'/'.$upload->image->getDir().'/'.TEST_ID.'.jpg');

        return $upload;
    }

    /**
     * @depends testUpload
     */
    public function testSaveUpload($upload)
    {
        $image = $upload->insertImage();
        $this->assertInstanceOf(Image::class, $image);
        $this->assertFileExists($image->getSrc(null, true));

        $image_check = Image::findByName(TEST_ID.'.jpg');
        $this->assertEquals($image->img_id, $image_check->img_id);

        return $image;
    }

    public function testLoadImageMapper()
    {
        $mapper = new ImageMapper();
        $this->assertInstanceOf(ImageMapper::class, $mapper);

        return $mapper;
    }

    /**
     * @depends testSaveUpload
     * @depends testLoadImageMapper
     */
    public function testImageInsertDuplicateFailure($image, $mapper)
    {
        $this->expectException(Exception::class);
        $mapper->insert($image);
    }
    
    /**
     * @depends testSaveUpload
     * @depends testLoadImageMapper
     */
    public function testImageSave($image, $mapper)
    {
        $image->img_title = 'foo';
        $image->img_description = 'bar';
        $this->assertTrue($mapper->save($image));
        $this->assertTrue($image->getId() > 0);

        $image_check = $mapper->findById($image->getId());
        $this->assertInstanceOf(Image::class, $image_check);
        $this->assertEquals($image->getId(), $image_check->getId());
        $this->assertEquals('foo', $image_check->img_title);
        $this->assertEquals('bar', $image_check->img_description);
    }

    public function testUploadNotAnImageThrowsException()
    {
        $this->expectException(UploadException::class);
        $upload = new Upload('http://squarehaven.com/features/albums/jp/SSCX-10039.txt');
    }

    /**
     * @depends testLoadImageMapper
     */
    public function testImageFetch($mapper)
    {
        $this->assertInstanceOf(ImageMapper::class, $mapper);
        $image = $mapper->findByName(TEST_ID.'.jpg');
        $this->assertNotNull($image);
        $this->assertNotNull($mapper->findById($image->getId()));
        $this->assertNotNull(Image::findById($image->getId()));
        $this->assertNotNull(Image::findByName(TEST_ID.'.jpg'));

        return $image;
    }

    /**
     * @depends testSaveUpload
     * @depends testImageCollectionConstruction
     */
    public function testImageCollectionAddItem($image, $collection)
    {
        $this->assertEquals(0, $collection->count);
        $collection->add($image);
        $this->assertEquals($collection->getId(), $image->img_session_id);

        $upload = new Upload(TEST_IMAGE_SRC_BOX);
        $upload->setFilename(TEST_ID.'_box');
        $upload->prepare($collection->getId(), Image::BOXART, $GLOBALS['current_user']);
        $image_2 = $upload->insertImage();
        $this->assertFileExists($image_2->getSrc(Image::BOX, true));

        $collection->add($image_2);
        $this->assertEquals(2, $collection->count);

        return $collection;
    }

    /**
     * @depends testImageCollectionAddItem
     * @depends testLoadImageMapper
     */
    public function testImageCollectionSave($collection, $mapper)
    {
        $this->assertTrue($mapper->saveSession($collection));
    }

    /**
     * @depends testImageCollectionAddItem
     */
    public function testImageCollectionGeneratorIterates($collection)
    {
        $i = 0;
        foreach ($collection->getGenerator() as $image) {
            $this->assertInstanceOf(Image::class, $image);
            $i++;
        }
        $this->assertEquals(2, $i);
    }

    /**
     * @depends testImageCollectionAddItem
     * @depends testLoadImageMapper
     */
    public function testImageCollectionFindAllBySessionId($collection, $mapper)
    {
        $session_id = $collection->getId();
        $collection_check = $mapper->findAllBySessionId($session_id);
        $this->assertInstanceOf(ImageCollection::class, $collection_check);
        $this->assertEquals(2, $collection_check->count);

        return $collection_check;
    }

    public function testResizeImagesAndThumbnails()
    {
        $image = Image::findByName(TEST_ID.'_box.jpg');
        $this->assertFileExists($image->getSrc(Image::OPTIMAL, true));
        $this->assertFileExists($image->getSrc(Image::MEDIUM, true));
        $this->assertFileExists($image->getSrc(Image::SMALL, true));
        $thumbnail = $image->getSrc(Image::THUMBNAIL, true);
        $this->assertFileExists($thumbnail);
        list($width, $height, $type, $attr) = getimagesize($thumbnail);
        $this->assertEquals(100, $width);
        $this->assertEquals(100, $height);
    }

    /**
     * @depends testImageCollectionFindAllBySessionId
     * @depends testLoadImageMapper
     */
    public function testImageDelete(ImageCollection $collection, ImageMapper $mapper)
    {
        foreach($collection->getGenerator() as $image) {
            $check_image_id[] = $image->getId();
            $this->assertTrue($mapper->delete($image));
        }

        foreach ($check_image_id as $id) {
            $this->assertNull($mapper->findById($id));
        }

        $this->assertNull($mapper->findAllBySessionId($collection->getId()));
    }
}
