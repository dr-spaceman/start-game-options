<?php declare(strict_types=1);

namespace Vgsite;

use Vgsite\Exceptions\UserException;

class User extends DomainObject
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

    protected $id = -1;
    protected $username;
    protected $password;
    protected $email;
    protected $rank = 0;

    /**
     * Details loaded from the other table columns via mapper
     * There's probably a better way to do it!!!
     * @var array
     */
    public $details = [];

    /**
     * User construction
     * May be passed by static functions like self::getByEmail
     * Construction doesn't verify variables; Pass to set*() to filter
     */
    public function __construct(int $id, string $username, string $password, string $email, int $rank=self::GUEST)
    {
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
        $this->rank = $rank;
        parent::__construct($id);
	}

    /**
     * An array of private properties for Logging, debugging, etc.
     */
    public function getProperties(): array
    {
        return array(
            'user_id' => $this->id,
            'username' => $this->username,
            'password' => $this->password,
            'email' => $this->email,
            'rank' => $this->rank,
        );
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password, $hash=true)
    {
        $password_check = trim($password);
        if ($password_check != $password) {
            throw new \InvalidArgumentException("Password can't be blank or have whitespace at the beginning or end");
        }

        if ($hash === true) {
            $password = password_hash($password, PASSWORD_DEFAULT);
            if ($password === false) {
                throw new \Exception("Password couldn't be secured because of an error");
            }
        }

        $this->password = $password;
    }

    public function hashPassword(string $password)
    {

        $this->setPassword($password, true);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException("Email `{$email}` couldn't be validated");
        }

        $this->email = $email;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function setRank(int $rank)
    {
        if (!isset(static::$ranks[$rank])) {
            throw new \InvalidArgumentException('Rank "'.$rank.'" is not defined, use one of: '.implode(', ', array_keys(static::$ranks)));
        }

        $this->rank = $rank;
    }

    public function getAvatar(): Avatar
    {
        return new Avatar($this->data['avatar']);
    }

    public static function findById(int $id): ?DomainObject
    {
        return static::getMapper()->findById($id);
    }

    public static function findByUsername(string $username): ?DomainObject
    {
        return static::getMapper()->findByUsername($username);
    }

    public static function findByEmail(string $email): ?DomainObject
    {
        return static::getMapper()->findByEmail($email);
    }

    public static function getRanks(): array
    {
        return array_flip(static::$ranks);
    }

    public static function getRankName(int $rank): string
    {
        if (!isset(static::$ranks[$rank])) {
            throw new \InvalidArgumentException('Rank "'.$rank.'" is not defined, use one of: '.implode(', ', array_keys(static::$ranks)));
        }

        return static::$ranks[$rank];
    }

    public function getLastLogin()
    {
        return $this->getDetail('activity');
    }

    public function getDetail(string $key)
    {
        return $this->details[$key];
    }

    /**
     * Render user in HTML form
     */
    public function render($show_avatar=true, $link_profile=true)
    {
        $ret = '';
        if ($link_profile) $ret.= '<a href="/~'.$this->username.'" title="'.$this->username.'\'s profile">';
        if ($show_avatar) $ret.= $this->getAvatar()->avatar_tn_src;
        $ret.= '<span class="username">'.$this->username.'</span>';
        if ($link_profile) $ret.= '</a>';
        $ret = '<span class="user">'.$ret.'</span>';
        
        return $ret;
    }
}

class Admin extends User {

}
