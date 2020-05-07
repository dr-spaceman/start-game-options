<?php

namespace Vgsite;

/**
 * Handle database queries and logging
 */
abstract class Mapper
{
    /**
     * Database primary key field name
     */
    protected $db_id_field = 'id';
    abstract protected $db_table;

    /**
     * Registry object
     * @var PDO object
     */
    protected $pdo;

    /**
     * Registry object
     * @var Monolog object
     */
    protected $logger;
    
    /**
     * PDO statements
     * @var PDOStatement object
     */
    protected $select_sql = "SELECT * FROM `%s` WHERE `%s`=? LIMIT 1";
    protected $select_statement;
    protected $select_all_statement;
    protected $save_statement;
    protected $insert_statement;
    protected $delete_statement;

    public function __construct()
    {
        $registry = Registry::instance();
        $this->pdo = $registry->get('pdo');
        $this->logger = $registry->get('logger');

        $this->select_statement = $this->pdo->prepare(sprintf($this->select_sql, $this->db_table, $this->db_id_field));
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    public function findById(int $id): ?DomainObject
    {
        $cached = $this->getCached($id);
        if (!is_null($cached)) {
            return $cached;
        }

        $this->select_statement->execute([$id]);
        $row = $this->select_statement->fetch();
        $this->select_statement->closeCursor();

        if (!is_array($row)) {
            return null;
        }

        return $this->createObject($row);
    }

    public function findAll(): Collection
    {
        $this->select_all_statement->execute([]);
        $rows = array();
        while ($row = $this->select_all_statement->fetch()) {
            $rows[] = $row;
        }

        return $this->getCollection($rows);
    }

    protected function getCached($id=null): ?DomainObject
    {
        if (is_null($id) || $id < 1) {
            return null;
        }

        return ObjectCache::exists(
            $this->targetClass(),
            $id
        );
    }

    protected function addCache(DomainObject $obj)
    {
        ObjectCache::add($obj);
    }

    /**
     * Create a DomainObject from an array
     * @param  array  $row A multidimentional array, or one ordered as the class constructor requires
     * @return DomainObject
     */
    public function createObject(array $row): DomainObject
    {
        $cached = $this->getCached($row[$this->id_field]);
        if (!is_null($cached)) {
            return $cached;
        }

        $obj = $this->doCreateObject($row);
        $this->addCache($obj);

        return $obj;
    }

    public function insert(DomainObject $obj)
    {
        $this->addCache($obj);
        return $this->doInsert($obj);
    }
    
    abstract public function getCollection(array $rows): Collection;
    abstract protected function doCreateObject(array $row): DomainObject;
    abstract public function save(DomainObject $object);
    abstract protected function doInsert(DomainObject $object);
    abstract protected function targetClass(): string;
}