<?php

namespace Vgsite;

class ImageMapper extends Mapper
{
    private $select_statement;
    private $update_statement;
    private $insert_statement;
    private $delete_statement;

    public function __construct()
    {
        parent::__construct();
        $this->select_statement = $this->pdo->prepare("SELECT * FROM images WHERE `img_id`=?");
        $this->update_statement = $this->pdo->prepare("UPDATE images SET foo=? WHERE `img_id`=?");
        $this->insert_statement = $this->pdo->prepare("INSERT INTO images (foo,bar) VALUES (?,?);");
        $this->delete_statement = $this->pdo->prepare("DELETE FROM images WHERE `img_id`=?");
    }

    public function findByImagename(string $img_name): ?DomainObject
    {
        $statement = $this->pdo->prepare("SELECT * FROM images WHERE `img_name`=?");
        $statement->execute([$img_name]);
        $row = $statement->fetch();

        if (!is_array($row)) {
            return null;
        }

        return $this->createObject($row);
    }

    protected function targetClass(): string
    {
        return Image::class;
    }

    public function getCollection(array $row): Collection
    {
        return new ImageCollection($row, $this);
    }

    protected function doCreateObject(array $row): DomainObject
    {
        return new Image(
            (int) $row['img_id'],
            $row['username'],
            $row['password'],
            $row['email'],
            (int) $row['rank'],
        );
    }

    protected function doInsert(DomainObject $user): bool
    {
        $values = [
            $user->getImagename(), 
            $user->getPassword(), 
            $user->getEmail(),
            $user->getRank(), 
        ];
        $this->insert_statement->execute($values);
        // if (0 == $this->insert_statement->rowCount()) {
        //     throw new ImageException("Error inserting Image data", 0, null, $user);
        //     return false;
        // }
        $id = $this->pdo->lastInsertId();
        $user->setId((int) $id);

        if ($this->logger) $this->logger->info("Insert Image data ", $values);

        return true;
    }

    public function update(DomainObject $user): bool
    {
        $values = [
            $user->getPassword(),
            $user->getEmail(),
            $user->getRank(),
            $user->getId(),
        ];
        $this->update_statement->execute($values);
        // if (0 == $this->update_statement->rowCount()) {
        //     throw new ImageException("Error updating Image data", 0, null, $user);
        //     return false;
        // }

        if ($this->logger) $this->logger->info("Update Image data ", $values);

        return true;
    }

    public function delete(DomainObject $user): bool
    {
        $values = [$user->getId()];
        $this->delete_statement->execute($values);
        // if (0 == $this->delete_statement->rowCount()) {
        //     throw new ImageException("Error deleting Image data", 0, null, $user);
        //     return false;
        // }

        if ($this->logger) $this->logger->info("Delete Image data ", $user->getProperties());

        return true;
    }

    public function selectStatement(): \PDOStatement
    {
        return $this->select_statement;
    }
}