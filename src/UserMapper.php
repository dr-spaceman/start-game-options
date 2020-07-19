<?php

namespace Vgsite;

use Vgsite\Exceptions\UserException;

class UserMapper extends MapperProps
{
    protected $db_table = 'users';
    protected $db_id_field = 'user_id';
    protected $save_fields = [
        'username', 'password', 'email', 'verified', 'gender', 'region', 'rank', 'avatar', 'timezone'
    ];

    public function findByUsername(string $username): User
    {
        $statement = $this->pdo->prepare("SELECT * FROM users WHERE `username`=? LIMIT 1");
        $statement->execute([$username]);
        $row = $statement->fetch();

        if (empty($row)) {
            throw new \OutOfBoundsException("User with username `{$username}` could not be found.");
        }

        return $this->createObject($row);
    }

    public function findByEmail(string $email): User
    {
        $statement = $this->pdo->prepare("SELECT * FROM users WHERE `email`=? LIMIT 1");
        $statement->execute([$email]);
        $row = $statement->fetch();

        if (empty($row)) {
            throw new \OutOfBoundsException("User with email `{$email}` could not be found.");
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
