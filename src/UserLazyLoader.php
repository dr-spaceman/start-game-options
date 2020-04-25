<?php

use Vgsite\User;

class UserLazyLoader
{
    private static $instances = array();
    
    public static function getUserById(int $id): User
    {
        if (!isset(self::$instances[$id])) {
            self::$instances[$id] = new User(['user_id': $id]);
        }
        
        return self::$instances[$id];
    }
    
    public static function getUserCount(): int
    {
        return count(self::$instances);
    }
}