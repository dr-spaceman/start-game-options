<?php

namespace Vgsite;

/**
 * Handle database queries and logging, object storage into IdentityMap
 */

abstract class Mapper
{
    /**
     * Database table name and primary key field name
     */
    protected $db_table;
    protected $db_id_field = 'id';

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

    protected $identity_map;
    
    /**
     * PDO statements
     * @var PDOStatement object
     */
    protected $select_sql = "SELECT * FROM `%s` WHERE `%s`=? LIMIT 1";
    protected $select_statement;
    protected $select_all_sql = "SELECT * FROM `%s`";
    protected $select_all_statement;
    protected $save_statement;
    protected $insert_statement;
    protected $delete_statement;

    public function __construct()
    {
        $this->pdo = Registry::get('pdo');
        $this->logger = Registry::get('logger');
        $this->identity_map = new IdentityMap();

        $this->select_statement = $this->pdo->prepare(sprintf($this->select_sql, $this->db_table, $this->db_id_field));
        $this->select_all_statement = $this->pdo->prepare(sprintf($this->select_all_sql, $this->db_table));
    }

    public function findById(int $id): ?DomainObject
    {
        if (true === $this->identity_map->hasId($id)) {
            return $this->identity_map->getObject($id);
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

    /**
     * Create a DomainObject from an array
     * @param  array  $row A multidimentional array, or one ordered as the class constructor requires
     * @return DomainObject
     */
    public function createObject(array $row): DomainObject
    {
        $obj = $this->doCreateObject($row);
        
        $this->identity_map->set($obj);

        return $obj;
    }

    public function insert(DomainObject $obj)
    {
        $insert_result = $this->doInsert($obj);

        return $insert_result;
    }

    public function __destruct()
    {
        unset($this->pdo, $this->logger, $this->identity_map);
    }
    
    abstract public function getCollection(array $rows): Collection;
    abstract protected function doCreateObject(array $row): DomainObject;
    abstract public function save(DomainObject $object);
    abstract protected function doInsert(DomainObject $object);
    abstract protected function targetClass(): string;
}