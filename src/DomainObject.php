<?php

namespace Vgsite;

/**
 * Common attributes and methods for objects
 */
abstract class DomainObject
{
    protected $id = -1;

    public function __construct(int $id)
    {
        echo 'new DomainObject['.static::class.':'.$id.']'.PHP_EOL;
        if (!is_null($id)) {
            $this->id = $id;
        }
    }

    protected function getMapper()
    {
        return Registry::getMapper(static::class);
    }

    /**
     * Static entry points
     * Load registered mapper and use the mapper method to access data
     * 
     * @param  int    $id Database primary key
     * @return DomainObject|null
     */
    public static function findById(int $id): ?DomainObject
    {
        return self::getMapper()->findById($id);
    }

    public static function findAll(): Collection
    {
        return self::getMapper()->findAll();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }
 
    public static function getCollection(string $type): Collection
    {
        return Collection::getCollection($type); 
    }

    public function __clone()
    {
        $this->id = -1;
    }
}
?>
