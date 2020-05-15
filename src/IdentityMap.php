<?php

namespace Vgsite;

class IdentityMap
{
    /**
     * @var ArrayObject
     */
    protected $idToObject;

    /**
    * @var SplObjectStorage
    */
    protected $objectToId;

    public function __construct()
    {
        $this->objectToId = new \SplObjectStorage();
        $this->idToObject = new \ArrayObject();
        echo 'new IdentityMap*******************'.PHP_EOL;
    }

    /**
    * @param integer $id
    * @param mixed $object
    */
    public function set(DomainObject $object)
    {
        $id = $object->getId();
        $this->idToObject[$id]     = $object;
        $this->objectToId[$object] = $id;
        echo 'IdentityMap::set('.get_class($object).$id.')'.PHP_EOL;
    }

    /**
    * @param mixed $object
    * @throws OutOfBoundsException
    * @return integer
    */
    public function getId(DomainObject $object): int
    {
        if (false === $this->hasObject($object)) {
            throw new \OutOfBoundsException();
        }

        return $this->objectToId[$object];
    }

    /**
    * @param integer $id
    * @return boolean
    */
    public function hasId(int $id): bool
    {
        return isset($this->idToObject[$id]);
    }

    /**
    * @param mixed $object
    * @return boolean
    */
    public function hasObject(DomainObject $object): bool
    {
        return isset($this->objectToId[$object]);
    }

    /**
    * @param integer $id
    * @throws OutOfBoundsException
    * @return object
    */
    public function getObject(int $id): DomainObject
    {
        echo 'IdentityMap::get('.get_class($this->idToObject[$id]).$id.')'.PHP_EOL;
        if (false === $this->hasId($id)) {
            throw new \OutOfBoundsException();
        }

        return $this->idToObject[$id];
    }
}