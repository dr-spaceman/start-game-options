<?php

use Vgsite\Image;

class ImageLazyLoader
{
    private static $instances = array();
    
    public static function getById(int $id): Image
    {
        if (!isset(self::$instances[$id])) {
            self::$instances[$id] = new Image(['img_id': $id]);
        }
        
        return self::$instances[$id];
    }
    
    public static function getByName(string $name): Image
    {
        $image = new Image(['img_name': $name]);
        self::$instances[$image->id] = $image;
        
        return self::$instances[$id];
    }
    
    public static function getCount(): int
    {
        return count(self::$instances);
    }
}