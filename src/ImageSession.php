<?php

namespace Vgsite;

class ImageSession extends DomainObject
{
    public function __construct(int $session_id)
    {
    }

    public function getDir()
    {
        self::IMAGES_DIR.'/'.substr($this->img_session_id, 12, 7);
    }    
}