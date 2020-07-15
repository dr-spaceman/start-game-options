<?php

declare(strict_types=1);

define('TEST_ALBUM_ID', 2);

require_once dirname(__FILE__) . '/../config/bootstrap_tests.php';

use PHPUnit\Framework\TestCase;
use Vgsite\Album;
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
        $album = static::$mapper->findById(TEST_ALBUM_ID);
        $this->assertInstanceOf(Album::class, $album);
        $this->assertEquals($album->getProp('id'), TEST_ALBUM_ID);

        $albums = static::$mapper->findAll();
        $this->assertInstanceOf(Collection::class, $albums);
    }

    public function testAlbumMapperInstantiates(): void
    {
        $this->assertInstanceOf(Mapper::class, self::$mapper);
    }

    public function testAlbumMapperSearch(): Collection
    {
        $results = self::$mapper->searchBy('title', 'final fantasy vi', 'datesort', '', ['id', 'albumid', 'title']);
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertNotEmpty($results);

        return $results;
    }

    /**
     * @depends testAlbumMapperSearch
     */
    public function testAlbumMapperSearchSorts($results): void
    {
        $this->assertNotEmpty($results);
        $generator = $results->getGenerator();
        $this->assertInstanceOf(Generator::class, $generator);

        $carry = '1979-01-01';
        $this->assertGreaterThan($carry, '1999');
        $i = 0;
        foreach ($generator as $album) {
            $datesort = $album->getProp('datesort');
            if (empty($datesort)) continue;
            $this->assertGreaterThan($carry, $datesort);
            $carry = $datesort;
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
}