<?php

namespace Vgsite;

use Carbon\Carbon;
use OutOfBoundsException;
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

    /**
     * @param User $obj
     * @return User
     */
    public function save(DomainObject $obj): DomainObject
    {
        // Check for major changes first
        $user_check = $this->findById($obj->getId(), false);
        if ($user_check->getUsername() != $obj->getUsername()) {
            $statement = $this->pdo->prepare("SELECT (1) FROM `users_change_username` WHERE username_old=? AND user_id !=?");
            $statement->execute([$obj->getUsername(), $obj->getId()]);
            if ($statement->fetchColumn()) {
                throw new OutOfBoundsException("Username `{$obj->getUsername()}` has been previously used.");
            }

            $statement = $this->pdo->prepare("SELECT * FROM `users_change_username` WHERE user_id=? ORDER BY date_changed DESC");
            $statement->execute([$obj->getId()]);
            if ($row = $statement->fetch()) {
                $last_changed = new Carbon($row['date_changed']);
                if ($last_changed->diffInDays(Carbon::now()) < 30) {
                    throw new \Exception("Account username has been changed recently and cannot be changed right now.");
                }
            }

            $new_username = true;
            $change_username_message = "User changed username from `{$user_check->getUsername()}` to `{$obj->getUsername()}`";
        }

        if ($user_check->getEmail() != $obj->getEmail()) {
            
        }

        $obj = parent::save($obj);

        if ($new_username) {
            $sql = "INSERT INTO `users_change_username` (`user_id`, `username_old`, `username_new`) VALUES (?, ?, ?);";
            $statement = $this->pdo->prepare($sql);
            if (! $statement->execute([$obj->getId(), $obj->getUsername(), $user_check->getUsername()])) {
                if ($this->logger) $this->logger->error("Could not INSERT INTO users_change_username table on username change");
            }

            if ($this->logger) $this->logger->notice($change_username_message, $obj->getProps());
        }

        return $obj;
    }
}
