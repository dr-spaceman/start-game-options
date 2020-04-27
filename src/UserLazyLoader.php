<?php

use Vgsite\User;

class UserLazyLoader
{
    private static $instances = array();
    
    public static function getById(int $id): User
    {
        if (!isset(self::$instances[$id])) {
            self::$instances[$id] = new User(['user_id': $id]);
        }
        
        return self::$instances[$id];
    }
    
    public static function getCount(): int
    {
        return count(self::$instances);
    }
}