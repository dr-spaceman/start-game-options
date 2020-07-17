<?php

namespace Vgsite;

use OutOfBoundsException;

class AlbumMapper extends MapperProps
{
    protected $db_table = 'albums';
    protected $db_id_field = 'id';
    protected $select_statement;
    protected $select_all_statement;
    protected $save_statement;
    protected $insert_statement;
    protected $delete_statement;
    
    protected $save_fields = [
        'title', 'subtitle', 'keywords', 'coverimg', 'jp', 'publisher', 'cid', 'albumid',
        'datesort', 'release', 'price', 'no_commerce', 'compose', 'arrange', 'perform', 'series',
        'new', 'view', 'media', 'path'
    ];
    protected $insert_fields = [];

    public function findByAlbumId(string $albumid): Album
    {
        $statement = $this->pdo->prepare("SELECT * FROM albums WHERE `albumid`=?");
        $statement->execute([$albumid]);
        $row = $statement->fetch();

        if (!is_array($row)) {
            throw new OutOfBoundsException("The requested album id `{$albumid}` could not be found.");
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

    public function selectStatement(): \PDOStatement
    {
        return $this->select_statement;
    }
}
