<?php

namespace Vgsite;

class Badge extends DomainObject
{
    public const GARBAGE = 0;
    public const BRONZE = 1;
    public const SILVER = 2;
    public const GOLD = 3;
    public const _SOMENAME_ = 4;
    public const _SOMEOTHERNAME_ = 5;

    private static $values = [
        self::GARBAGE => 'garbage',
        self::BRONZE => 'bronze',
        self::SILVER => 'silver',
        self::GOLD => 'gold',
        self::_SOMENAME_ => 'gold',
        self::_SOMEOTHERNAME_ => 'gold',
    ];

    protected $name;
    protected $description;
    protected $value;
    protected $sort;
    
    public function __construct(int $badge_id, string $name, string $description, int $value=1, int $sort=0) {
        $this->name = $name;
        $this->description = $description;
        $this->value = ($this->getValueName[$value] ? $value : null);
        $this->sort = $sort;

        parent::__construct($badge_id);
    }

    /**
     * Add a badge to User collection
     * @param  User   $user     User object
     * @return bool             Return true if success adding | false if badge is already in collection
     */
    public function earn(User $user): bool
    {
        //make sure hasn't already earned
        $earned = $this->getMapper()->findEarned($this->getId(), $user->getId());
        if (!is_null($earned)) {
            return false;
        }
        
        $this->getMapper()->insertEarned($this->getId(), $user->getId());
        
        if ($user->getId() == $GLOBALS['user_id']) $_SESSION['newbadges'][] = $this->getId();

        // Insert stream
        // Don't stream Newbie badge
        if ($this->getId() == 1) return true;
        
        $url = '/~'.$user->getUsername().'/#/badges/'.$this->getId().'/'.formatNameURL($badge->name);
        $action = '[[User:'.$user->getUsername().']] earned the <a href="'.$url.'">'.$badge->name.'</a> badge.'.
                '<div class="badge"><a href="'.$url.'"><img src="/bin/img/badges/'.$this->getId().'.png" alt="badge: '.htmlSC($badge->name).'" width="50" height="50"/></a></div>';
        Userstream::insert($action, 'earn badge', $user);
        
        return true;

    }
    
    public function showEarned(User $user)
    {
        $ret = '
        <div class="badge badgeearn">
            <div class="container">
                <a href="#close" class="preventdefault ximg close">close</a>
                <h5>You earned a new badge!</h5>
                <h6>'.$this->name.'</h6>
                <div class="badgeimg"><img src="/bin/img/badges/'.$this->id.'.png" alt="badge: '.htmlSC($this->name).'"/></div>
                <big>'.bb2html($this->description).'</big>
                <div class="message"><b>Congratulations'.$user->getUsername().'!</b> This <span class="value '.static::$values[$this->value].'">'.ucwords(static::$values[$this->value]).' badge</span> will be shown on your profile page along with dozens of other badges you can earn for contributing at Videogam.in.</div>
            </div>
        </div>
        ';
        
        if ($GLOBALS['user_id'] == $user->getId()) {
            $this->markShown($user);
        }
        
        return $ret;
        
    }

    public function markShown(User $user)
    {
        $this->mapper->markShown($badge, $user);
    }
    
    function show($badge_id, $user_id=''){
        
        //show a given badge
        //if $user_id, show when user earned it
        
        $this->get($badge_id);
        
        if($user_id){
            $q = "SELECT * FROM badges_earned WHERE badge_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $badge_id)."' AND user_id = '".$user_id."' LIMIT 1";
            if(!$earneddat = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) return false;
        }
        
        $num = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM badges_earned WHERE badge_id='".mysqli_real_escape_string($GLOBALS['db']['link'], $badge_id)."';"));
        
        $ret = '
        <div class="showbadge badge">
            <div class="badgeimg"><img src="/bin/img/badges/'.$badge_id.'.png" alt="badge: '.$this->badges[$badge_id]['name'].'"/></div>
            <h6>'.$this->badges[$badge_id]['name'].'</h6>
            <big>'.bb2html($this->badges[$badge_id]['description']).'</big>
            '.($user_id ? '<div class="message">This <span class="value '.$this->badges[$badge_id]['value2'].'">'.$this->badges[$badge_id]['value2'].' badge</span> was earned by '.outputUser($user_id).' on '.formatDate($earneddat->datetime).'</div>' : '').'
            <small><span class="arrow-right">&nbsp;</span>&nbsp;&nbsp;<b>'.$num.'</b> '.($num != 1 ? 'people have' : 'person has').' earned this badge.</small>
        </div>
        <div class="clear"></div>';
        
        return $ret;
        
    }

    /**
     * Gets all supported values
     *
     * @return array Assoc array with human-readable level names => level codes.
     */
    public static function getValues(): array
    {
        return array_flip(static::$values);
    }

    /**
     * Gets the name of a badge value.
     *
     * @throws \Psr\Log\InvalidArgumentException If value is not defined
     */
    public static function getValueName(int $value): string
    {
        if (!isset(static::$values[$value])) {
            throw new \InvalidArgumentException('Value "'.$value.'" is not defined, use one of: '.implode(', ', array_keys(static::$values)));
        }

        return static::$values[$value];
    }
}