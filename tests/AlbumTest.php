<?php

declare(strict_types=1);

define('TEST_ALBUM_ID', 2);

require_once dirname(__FILE__) . '/../config/bootstrap_tests.php';

use PHPUnit\Framework\TestCase;
use Vgsite\Album;
use Vgsite\AlbumCollection;
use Vgsite\AlbumMapper;
use Vgsite\Collection;
use Vgsite\Mapper;

class AlbumTest extends TestCase
{
    /** @var AlbumMapper */
    protected static $mapper;

    public static function setUpBeforeClass(): void
    {
        self::$mapper = new AlbumMapper();
    }
    
    public function testAlbumStaticFind(): void
    {
        $album = self::$mapper->findById(TEST_ALBUM_ID);
        $this->assertInstanceOf(Album::class, $album);
        $this->assertEquals($album->getId(), TEST_ALBUM_ID);

        $albums = self::$mapper->findAll();
        $this->assertInstanceOf(Collection::class, $albums);
    }

    public function testAlbumMapperInstantiates(): void
    {
        $this->assertInstanceOf(Mapper::class, self::$mapper);
    }

    public function testAlbumMapperSearch(): Collection
    {
        $results = self::$mapper->searchBy('title', 'final fantasy vi', 'release', '', ['id', 'albumid', 'title', 'release']);
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertNotEmpty($results);

        return $results;
    }

    /**
     * @depends testAlbumMapperSearch
     */
    public function testAlbumMapperSearchSorts(AlbumCollection $results): void
    {
        $this->assertNotEmpty($results);
        $generator = $results->getGenerator();
        $this->assertInstanceOf(Generator::class, $generator);

        $carry = '1979-01-01';
        $this->assertGreaterThan($carry, '1999');
        $i = 0;
        foreach ($generator as $album) {
            $release = $album->getProp('release');
            if (empty($release)) continue;
            $this->assertGreaterThanOrEqual($carry, $release);
            $carry = $release;
            if ($i++ > 10) break;
        }
    }

    /**
     * @depends testAlbumMapperSearch
     */
    public function testAlbumMapperSearchFields($results): void
    {
        $this->assertNotEmpty($results);
        $this->assertEmpty($results->getRow(0)->getProp('subtitle'));
    }

    public function testUserCRUDLifecycle()
    {
        $album = new Album([
            'id' => -1,
            'albumid' => 'test_' . uniqid(),
            'title' => 'Foo',
            'subtitle' => 'Bar',
            'release' => date('Y-m-d'),
        ]);
        $this->assertInstanceOf(Album::class, $album);

        $album = self::$mapper->insert($album);
        $this->assertGreaterThanOrEqual(1, $album->getId());

        $album->setProp('albumid', 'test_' . uniqid());
        $album->setProp('subtitle', 'Baz');
        $this->assertEquals($album, self::$mapper->save($album));

        $this->assertTrue(self::$mapper->delete($album));
    }
}
