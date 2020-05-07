<?php

namespace Vgsite;

class BadgeMapper extends Mapper
{
    protected $id_field = 'badge_id';
    protected $select_statement;
    protected $select_all_statement;

    public function __construct()
    {
        parent::__construct();
        
        $this->select_all_statement = $this->pdo->prepare("SELECT * FROM badges ORDER BY `sort`");
    }

    public function findByName(string $name): ?DomainObject
    {
        $statement = $this->pdo->prepare("SELECT * FROM badges WHERE `name`=?");
        $statement->execute([$name]);
        $row = $statement->fetch();

        if (!is_array($row)) {
            return null;
        }

        return $this->createObject($row);
    }

    protected function targetClass(): string
    {
        return Badge::class;
    }

    public function getCollection(array $rows): Collection
    {
        return new BadgeCollection($rows, $this);
    }

    protected function doCreateObject(array $row): DomainObject
    {
        $obj = new Badge(
            (int)($row['badge_id'] ?: $row[0]),
            $row['name'] ?: $row[1],
            $row['description'] ?: $row[2],
            (int)($row['value'] ?: $row[3]),
            (int)($row['rank'] ?: $row[4]),
        );

        return $obj;
    }

    protected function doInsert(DomainObject $obj): bool
    {
    }

    public function save(DomainObject $obj): bool
    {
    }

    public function delete(DomainObject $obj): bool
    {
    }

    public function selectStatement(): \PDOStatement
    {
        return $this->select_statement;
    }
}