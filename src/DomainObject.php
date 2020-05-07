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
        if (!is_null($id)) {
            $this->id = $id;
        }
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

    abstract public static function findById(int $id): ?DomainObject;

    public function __clone()
    {
        $this->id = -1;
    }
}
?>
