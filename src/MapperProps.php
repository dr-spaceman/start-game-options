<?php

namespace Vgsite;

use PDOStatement;

/**
 * Extension of Mapper for DomainObjects that use Props trait
 */
abstract class MapperProps extends Mapper
{
    /** The following props should be defined in child class. */

    /** @var string Database table to query */
    protected $db_table;
    /** @var string Database field that holds the primary key. */
    protected $db_id_field;

    /** @var array List of fields for an UPDATE statement; Corresponds to
     * DomainObject props. */
    protected $save_fields = Array();

    /** @var array List of fields for an INSERT statement; Corresponds to
     * DomainObject props. All fields except primary key will be used by
     * default. */
    protected $insert_fields = Array();

    public function __construct()
    {
        parent::__construct();
    }

    protected function doCreateObject(array $row): DomainObject
    {
        $class = $this->targetClass();
        return new $class($row);
    }

    /**
     * INSERT into dabtase
     *
     * @param DomainObject $obj
     * 
     * @return DomainObject Modified object based on newly-created DB row
     */
    protected function doInsert(DomainObject &$obj): DomainObject
    {
        $insert_fields = $this->insert_fields ?: array_diff($this->targetClass()::PROPS_KEYS, [$this->db_id_field]);
        $insert = array();
        $insert_sql = array();
        foreach ($insert_fields as $field) {
            if (is_null($obj->getProp($field))) continue;
            $insert[$field] = $obj->getProp($field);
            $insert_sql["`{$field}`"] = ":{$field}";
        }
        $sql = sprintf("INSERT INTO {$this->db_table} (%s) VALUES (%s);", implode(',', array_keys($insert_sql)), implode(',', array_values($insert_sql)));
        $statement = $this->pdo->prepare($sql);
        
        $input_parameters = array();
        foreach ($insert as $key => $val) {
            $statement->bindValue($key, $val);
        }

        $statement->execute();
        $id = $this->pdo->lastInsertId();
        $obj->setId($id);

        if ($this->logger) $this->logger->info("Insert row " . $this->targetClass(), $input_parameters);

        return $obj;
    }

    /**
     * UPDATE a database row
     *
     * @param DomainObject $obj
     * @return DomainObject
     */
    public function save(DomainObject $obj): DomainObject
    {
        $save_keys = array_reduce($this->save_fields, function ($carry, $field) use ($obj) {
            if (is_null($obj->getProp($field))) return $carry;
            return ($carry ? $carry . "," : "") . "`{$field}`=:{$field}";
        });
        $sql = "UPDATE `{$this->db_table}` SET {$save_keys} WHERE {$this->db_id_field}=:{$this->db_id_field} LIMIT 1";
        $statement = $this->pdo->prepare($sql);

        foreach ($this->save_fields as $key) {
            $statement->bindValue($key, $obj->getProp($key));
        }
        $statement->bindValue($this->db_id_field, $obj->getId());
        $statement->execute();

        if ($this->logger) $this->logger->info("Update row " . $this->targetClass(), $obj->getProps());

        return $obj;
    }

    /**
     * DELETE a database row
     *
     * @param DomainObject $obj
     * @return boolean
     */
    public function delete(DomainObject $obj): bool
    {
        $input_parameters = [$obj->getId()];
        $statement = $this->pdo->prepare("DELETE FROM {$this->db_table} WHERE `{$this->db_id_field}`=?");
        $statement->execute($input_parameters);

        if (! $statement->rowCount()) {
            throw new \Exception('Delete statement was executed, but no result recorded.');
        }

        if ($this->logger) $this->logger->info("Delete row " . $this->targetClass(), $obj->getProps());

        return true;
    }
}
