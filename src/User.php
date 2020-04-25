<?php declare(strict_types=1);

namespace Vgsite;

use Vgsite\Exceptions\UserException;
use Vgsite\DB;

class User 
{
	public const GUEST = 0;
	public const RESTRICTED = 1;
	public const MEMBER = 2;
	public const VIP = 3;
	public const TRUSTED = 4;
	public const MODERATOR = 5;
	public const ADMIN = 6;
	public const MIDADMIN = 7;
	public const HIGHADMIN = 8;
	public const SUPERADMIN = 9;

	protected static $ranks = [
        self::GUEST      => 'GUEST',
        self::RESTRICTED => 'RESTRICTED',
        self::MEMBER     => 'MEMBER',
        self::VIP        => 'VIP',
        self::TRUSTED    => 'TRUSTED',
        self::MODERATOR  => 'MODERATOR',
        self::ADMIN      => 'ADMIN',
        self::HIGHADMIN  => 'HIGHADMIN',
        self::SUPERADMIN => 'SUPERADMIN',
    ];

    /**
     * Database handler
     * @var PDO
     */
    private $dbh;

    /**
     * Logger handler
     * @var Monolog
     */
    private $logger;

    private $user_id;
    private $rank = 0;

    /**
     * Data that corresponds to the Database columns
     * @var array
     */
    public $data = [];

    /**
     * User construction
     * May be passed by static functions like self::getByEmail
     * @param array    $params  Corresponds to DB Users table
     * @param PDO      $dbh     Database Injection
     * @param Monolog  $logger  Logger Injection
     */
    public function __construct(array $params, $dbh, $logger=[])
    {
        if (!$this->instance) {}

        if (isset($params['user_id'])) $this->user_id = (int) $params['user_id'];
        if (isset($params['rank'])) $this->rank = $params['rank'];

        foreach ($params as $key => $val) {
            $this->data[$key] = $val;
        }
	}

    public static function instance($dbh, $logger=[]): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
        $this->dbh = $dbh;
        if (!empty($logger)) {
            $this->logger = $logger;
            $this->logger->debug("User object construction", $params);
        }
    }

    public function getId(): int
    {
        return $this->user_id;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function getAvatar()
    {
        $this->avatar_src = "/bin/img/avatars/".($this->data['avatar'] ?: 'unknown.png');
        $this->avatar_tn_src = "/bin/img/avatars/tn/".($this->data['avatar'] ?: 'unknown.png');
    }

    public static function getById(int $user_id, $dbh, $logger=[]): ?self
    {
        $sql = "SELECT * FROM users WHERE user_id = :user_id LIMIT 1";
        $statement = $dbh->prepare($sql);
        $statement->bindValue(':user_id', $user_id);
        $statement->execute();
        if (!$row = $statement->fetch()) {
            return null;
        }

        return new self($row, $dbh, $logger);
    }

    public static function getByUsername($username, $dbh, $logger=[]): ?self
    {
        $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
        $statement = $dbh->prepare($sql);
        $statement->bindValue(1, $username);
        $statement->execute();
        if (!$row = $statement->fetch()) {
            return null;
        }

        return new self($row, $dbh, $logger);
    }

    public static function getByEmail($email, $dbh, $logger=[]): ?self
    {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $statement = $dbh->prepare($sql);
        $statement->bindValue(1, $email);
        $statement->execute();
        if (!$row = $statement->fetch()) {
            return null;
        }

        return new self($row, $dbh, $logger);
    }

    public static function getAll()
    {

    }

	/**
     * Gets all supported ranks.
     *
     * @return array Assoc array with human-readable level names => level codes.
     */
    public static function getRanks(): array
    {
        return array_flip(static::$ranks);
    }

    /**
     * Gets the name of the logging level.
     *
     * @throws \Psr\Log\InvalidArgumentException If rank is not defined
     */
    public static function getRankName(int $rank): string
    {
        if (!isset(static::$ranks[$rank]))
            throw new \InvalidArgumentException('Rank "'.$rank.'" is not defined, use one of: '.implode(', ', array_keys(static::$ranks)));

        return static::$ranks[$rank];
    }

	public function isLoggedIn(): bool
    {
		return $this->logged_in;
	}

	/**
     * Update the user in the database using $this->data parameters
     * @return Boolean    
     */
    public function save(): bool
    {
        if (isset($this->user_id) === false)
            throw new Exception("Couldn't save User: The user id hasn't been set.");

        $sql = "UPDATE `users` SET `email`=:email,`password`=:password,`rank`=:rank WHERE `user_id`=:user_id";
        $statement = $this->dbh->prepare($sql);
        $statement->bindValue(':email', $this->data['email']);
        $statement->bindValue(':password', $this->data['password']);
        $statement->bindValue(':rank', $this->data['rank']);
        $statement->bindValue(':user_id', $this->user_id);
        if (!$statement->execute()) {
            throw new Exception("Error saving User data");
            if ($this->logger) $this->logger->error("Error saving User data at User::save()", $this->data);
        }

        if ($this->logger) $this->logger->info("Save User data ", $this->data);

        return true;
    }

    public function insert(): bool
    {
        $datetime = date("Y-m-d H:i:s");
        $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password);";
        $statement = $this->dbh->prepare($sql);
        $statement->bindValue(':username', $this->data['username']);
        $statement->bindValue(':email', $this->data['email']);
        $statement->bindValue(':password', $this->data['password']);
        $statement->execute();

        $this->user_id = $this->dbh->lastInsertId();

        $_SESSION['logged_in'] = 'true';
        $_SESSION['user_id'] = $this->user_id;

        if ($this->logger) $this->logger->info("Insert into Users user_id:".$this->user_id, $this->data);
         
        return true;
    }

    public function delete(): bool
    {
        if (!$this->user_id)
            throw new Exception("Couldn't delete User: user_id hasn't been set.");

        $sql = sprintf("DELETE FROM users WHERE user_id = %d LIMIT 1", (int) $this->user_id);
        $statement = $this->dbh->query($sql);
        $statement->execute();

        if ($this->logger) $this->logger->info("DELETE user user_id:".$this->user_id, $this->data);

        return true;
    }

    public function getPreferences(): array
    {
        $sql = "SELECT * FROM users_prefs WHERE user_id = $this->getId() LIMIT 1";
        $statement = $dbh->query();
        if (!$row = $statement->fetch()) {
            return null;
        }

        return $row;
    }
}

class Admin extends User {

}
