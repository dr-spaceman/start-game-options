<?php

namespace Vgsite;

use Vgsite\User;

class UserLazyLoader
{
    private static $instances = array();
    
    public static function findById(int $id): User
    {
        if (!isset(self::$instances[$id])) {
            self::$instances[$id] = new User($id, 'test', 'password', 'test@test.com', 2);
        }
        
        return self::$instances[$id];
    }
    
    public static function getCount(): int
    {
        return count(self::$instances);
    }
}