<?php

namespace Vgsite;

use Vgsite\Exceptions\UserException;

class UserMapper extends Mapper
{
    protected $id_field = 'user_id';
    protected $select_statement;
    protected $select_all_statement;
    protected $save_statement;
    protected $insert_statement;
    protected $delete_statement;

    public function __construct()
    {
        parent::__construct();
        $this->select_statement = $this->pdo->prepare("SELECT * FROM users WHERE `user_id`=?");
        $this->select_all_statement = $this->pdo->prepare("SELECT * FROM users");
        $this->save_statement = $this->pdo->prepare("UPDATE users SET `password`=?,`email`=?,`rank`=? WHERE `user_id`=?");
        $this->insert_statement = $this->pdo->prepare("INSERT INTO users (`username`,`password`,`email`,`rank`) VALUES (?,?,?,?);");
        $this->delete_statement = $this->pdo->prepare("DELETE FROM users WHERE `user_id`=?");
    }

    public function findByUsername(string $username): ?DomainObject
    {
        $statement = $this->pdo->prepare("SELECT * FROM users WHERE `username`=?");
        $statement->execute([$username]);
        $row = $statement->fetch();

        if (!is_array($row)) {
            return null;
        }

        return $this->createObject($row);
    }

    public function findByEmail(string $email): ?DomainObject
    {
        $statement = $this->pdo->prepare("SELECT * FROM users WHERE `email`=?");
        $statement->execute([$email]);
        $row = $statement->fetch();

        if (!is_array($row)) {
            return null;
        }

        return $this->createObject($row);
    }

    protected function targetClass(): string
    {
        return User::class;
    }

    public function getCollection(array $rows): Collection
    {
        return new UserCollection($rows, $this);
    }

    protected function doCreateObject(array $row): DomainObject
    {
        $user = new User(
            (int)($row['user_id'] ?: $row[0]),
            $row['username'] ?: $row[1],
            $row['password'] ?: $row[2],
            $row['email'] ?: $row[3],
            (int)($row['rank'] ?: $row[4]),
        );

        $user->details = array_diff($row, $user->getProperties());

        return $user;
    }

    protected function doInsert(DomainObject $user): bool
    {
        $values = [
            $user->getUsername(), 
            $user->getPassword(), 
            $user->getEmail(),
            $user->getRank(), 
        ];
        $this->insert_statement->execute($values);
        // if (0 == $this->insert_statement->rowCount()) {
        //     throw new UserException("Error inserting User data", 0, null, $user);
        //     return false;
        // }
        $id = $this->pdo->lastInsertId();
        $user->setId((int) $id);

        if ($this->logger) $this->logger->info("Insert User data ", $values);

        return true;
    }

    public function save(DomainObject $user): bool
    {
        $values = [
            $user->getPassword(),
            $user->getEmail(),
            $user->getRank(),
            $user->getId(),
        ];
        $this->save_statement->execute($values);
        // if (0 == $this->save_statement->rowCount()) {
        //     throw new UserException("Error updating User data", 0, null, $user);
        //     return false;
        // }

        if ($this->logger) $this->logger->info("Update User data ", $values);

        return true;
    }

    public function delete(DomainObject $user): bool
    {
        $values = [$user->getId()];
        $this->delete_statement->execute($values);
        // if (0 == $this->delete_statement->rowCount()) {
        //     throw new UserException("Error deleting User data", 0, null, $user);
        //     return false;
        // }

        if ($this->logger) $this->logger->info("Delete User data ", $user->getProperties());

        return true;
    }

    public function selectStatement(): \PDOStatement
    {
        return $this->select_statement;
    }

    public function getPreferences(User $user): ?array
    {
        $sql = "SELECT * FROM users_prefs WHERE user_id=? LIMIT 1";
        $statement = $this->pdo->prepare();
        $statement->execute([$user->getId()]);
        if (!$row = $statement->fetch()) {
            return null;
        }

        return $row;
    }

    public function getAllDetails(User $user): array
    {
        $sql = "SELECT * FROM users LEFT JOIN users_details USING (user_id) WHERE user_id=? LIMIT 1";
        $statement = $this->pdo->prepare($sql);
        $statement->execute([$user->getId()]);
        if (!$row = $statement->fetch()) {
            return null;
        }

        return $row;
    }
}