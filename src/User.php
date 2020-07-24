<?php declare(strict_types=1);

namespace Vgsite;

use DateTime;
use InvalidArgumentException;
use Respect\Validation\Validator as v;

class User extends DomainObjectProps
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

    public const PROPS_KEYS = [
        'user_id', 'username', 'password', 'email', 'verified', 'gender', 
        'region', 'rank', 'avatar', 'timezone'
    ];
    public const PROPS_REQUIRED = ['user_id', 'username', 'password', 'email'];
    protected $username;
    protected $password;
    protected $email;
    protected $rank = self::GUEST;
    protected $gender;
    protected $region;
    protected $avatar;
    protected $timezone;
    protected $verified = '0';

    public function setUsername(string $username): User
    {
        if (! v::regex('/[a-zA-Z0-9_\-]/')->length(2, 25)->validate($username)) {
            throw new InvalidArgumentException("Username `{$username}` is not valid; Usernames must contain only: letters digits - _; and between 2 and 25 chatacters in length.");
        }

        $this->username = $username;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setPassword(string $password, $hash=false): User
    {
        if (! v::noWhitespace()->validate($password)) {
            throw new \InvalidArgumentException("Password can't be blank or have whitespace at the beginning or end");
        }

        if ($hash === true) {
            $password = password_hash($password, PASSWORD_DEFAULT);
            if ($password === false) {
                throw new \Exception("Password couldn't be secured because of an error");
            }
        }

        $this->password = $password;

        return $this;
    }

    public function hashPassword(string $password)
    {
        $this->setPassword($password, true);
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setEmail(string $email): User
    {
        if (! v::email()->validate($email)) {
            throw new \InvalidArgumentException("Email `{$email}` couldn't be validated");
        }

        $this->email = $email;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRank(): int
    {
        return (int) $this->rank;
    }

    public function setRank(int $rank): User
    {
        if (! isset(static::$ranks[$rank])) {
            throw new \InvalidArgumentException('Rank "'.$rank.'" is not defined, use one of: '.implode(', ', array_keys(static::$ranks)));
        }

        $this->rank = $rank;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function getAvatarObject(): Avatar
    {
        return new Avatar($this->data['avatar']);
    }

    public static function getRanks(): array
    {
        return array_flip(static::$ranks);
    }

    public static function getRankName(int $rank): string
    {
        if (! isset(static::$ranks[$rank])) {
            throw new \InvalidArgumentException('Rank `'.$rank.'` is not defined, use one of: '.implode(', ', array_keys(static::$ranks)));
        }

        return static::$ranks[$rank];
    }

    public function setGender($gender=''): User
    {
        if (empty($gender)) {
            $gender = 'they';
        }
        
        $genders = ['she', 'he', 'they', 'it', 'female', 'male', 'asexual'];
        if (! v::in($genders)->validate($gender)) {
            throw new InvalidArgumentException("Gender `{$gender}` not valid. Gender must be one of: " . implode(', ', $genders));
        }

        $this->gender = $gender;

        return $this;
    }

    public function setRegion($region='us'): User
    {
        v::in(['us', 'jp', 'eu', 'au'])->assert($region);

        $this->region = $region;

        return $this;
    }

    public function setTimezone($timezone=''): User
    {
        if (empty($timezone)) {
            $timezone = 'America/Los_Angeles';
        }

        $raw = file_get_contents(ROOT_DIR . '/assets/data/timezones.json');
        $timezones = json_decode($raw, true);

        if (! array_key_exists($timezone, $timezones)) {
            throw new InvalidArgumentException("Timezone `{$timezone}` is not a valid option.");
        }

        $this->timezone = $timezone;

        return $this;
    }

    public function getLastLogin(): DateTime
    {
        return new DateTime($this->getProp('activity'));
    }

    /**
     * Render user in HTML form
     */
    public function render($show_avatar=true, $link_profile=true): string
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
