<?php

namespace Vgsite;

class AlbumMapper extends Mapper
{
    protected $id_field = 'id';
    protected $select_statement;
    protected $select_all_statement;
    protected $save_statement;
    protected $insert_statement;
    protected $delete_statement;

    public function __construct()
    {
        parent::__construct();
        $this->select_statement = $this->pdo->prepare("SELECT * FROM albums WHERE `id`=?");
        $this->select_all_statement = $this->pdo->prepare("SELECT * FROM albums");
        $this->save_statement = $this->pdo->prepare("UPDATE albums WHERE `id`=?");
        $this->insert_statement = $this->pdo->prepare("INSERT INTO albums () VALUES ();");
        $this->delete_statement = $this->pdo->prepare("DELETE FROM albums WHERE `id`=?");
    }

    public function findByAlbumId(string $albumid): ?DomainObject
    {
        $statement = $this->pdo->prepare("SELECT * FROM albums WHERE `albumid`=?");
        $statement->execute([$albumid]);
        $row = $statement->fetch();

        if (!is_array($row)) {
            return null;
        }

        return $this->createObject($row);
    }

    public function searchBy(string $field, string $query, string $sort='title', string $sort_dir='ASC', array $fields = []): Collection
    {
        $fields_sql = $this->prepareFields($fields);
        if (!in_array($sort_dir, ['ASC', 'DESC'])) {
            $sort_dir = 'ASC';
        }
        
        $sql = "SELECT $fields_sql FROM albums 
                WHERE (:field LIKE CONCAT('%', :query, '%') OR `keywords` LIKE CONCAT('%', :query, '%') OR cid=:query) AND `view`='1' 
                ORDER BY :sort $sort_dir";
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['query' => $query, 'sort' => $sort, 'field' => $field]);

        $rows = [];
        while ($row = $statement->fetch()) {
            $rows[] = $row;
        }

        return $this->getCollection($rows);
    }

    protected function targetClass(): string
    {
        return Album::class;
    }

    public function getCollection(array $rows): Collection
    {
        return new AlbumCollection($rows, $this);
    }

    protected function doCreateObject(array $row): DomainObject
    {
        return new Album($row['id'], $row);
    }

    protected function doInsert(DomainObject $album): bool
    {
        $values = [];
        $this->insert_statement->execute($values);
        $id = $this->pdo->lastInsertId();
        $album->setId((int) $id);

        if ($this->logger) $this->logger->info("Insert Album data ", $values);

        return true;
    }

    public function save(DomainObject $album): bool
    {
        $values = [];
        $this->save_statement->execute($values);

        if ($this->logger) $this->logger->info("Update Album data ", $values);

        return true;
    }

    public function delete(DomainObject $album): bool
    {
        $values = [$album->getId()];
        $this->delete_statement->execute($values);

        if ($this->logger) $this->logger->info("Delete Album data ", $album->getProperties());

        return true;
    }

    public function selectStatement(): \PDOStatement
    {
        return $this->select_statement;
    }
}