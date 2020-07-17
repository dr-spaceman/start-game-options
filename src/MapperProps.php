<?php

namespace Vgsite;

/**
 * Extension of Mapper for DomainObjects that use Props trait
 */
abstract class MapperProps extends Mapper
{
    protected $db_table;
    protected $db_id_field;
    /** @var PDOStatement */
    protected $save_statement;
    /** @var PDOStatement */
    protected $insert_statement;

    /** @var PDO */
    protected $pdo;

    /** @var array List of fields for an UPDATE statement; Corresponds to DomainObject props. */
    protected $save_fields = Array();

    /** @var array List of fields for an INSERT statement; Corresponds to DomainObject props. */
    protected $insert_fields = Array();

    public function __construct()
    {
        parent::__construct();

        // Parse UPDATE statement fields and build SQL query
        $save_keys = implode(',', array_map(function ($field) {
            return "`{$field}`=:{$field}";
        }, $this->save_fields));
        $save_sql = "UPDATE `{$this->db_table}` SET {$save_keys} WHERE {$this->db_id_field}=:{$this->db_id_field} LIMIT 1";
        $this->save_statement = $this->pdo->prepare($save_sql);

        // Parse INSERT statement fields and build SQL query
        // Uses all props except ID
        $this->insert_fields = array_diff($this->targetClass()::PROPS_KEYS, [$this->db_id_field]);
        $insert_keys = implode(',', $this->insert_fields);
        $insert_vals = implode(',', array_fill(0, count($this->insert_fields), '?'));
        $this->insert_statement = $this->pdo->prepare("INSERT INTO {$this->db_table} ({$insert_keys}) VALUES ({$insert_vals});");
    }

    protected function doCreateObject(array $row): DomainObject
    {
        $class = $this->targetClass();
        return new $class($row[$this->db_id_field], $row);
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
        $input_parameters = array();
        foreach ($this->insert_fields as $key) {
            $input_parameters[] = $obj->getProp($key);
        }

        $this->insert_statement->execute($input_parameters);
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
        foreach ($this->save_fields as $key) {
            $this->save_statement->bindValue($key, $obj->getProp($key));
        }
        $this->save_statement->bindValue($this->db_id_field, $obj->getId());
        $this->save_statement->execute();

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
        $this->delete_statement->execute($input_parameters);

        if (!$this->delete_statement->rowCount()) {
            throw new \Exception('Delete statement was executed, but no result recorded.');
        }

        if ($this->logger) $this->logger->info("Delete row " . $this->targetClass(), $obj->getProps());

        return true;
    }
}
