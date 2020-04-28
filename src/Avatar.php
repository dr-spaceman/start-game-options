<?php declare(strict_types=1);

namespace Vgsite;

class Avatar
{
    public const AVATAR_DIR = 'public/assets/images/avatars';

    /**
     * A file name of an image in the avatars directory
     * @var string
     */
    private $src;

    public function __construct($src)
    {
        $this->src = $src;
        //old properties
        $this->avatar_src = "/bin/img/avatars/".($this->data['avatar'] ?: 'unknown.png');
        $this->avatar_tn_src = "/bin/img/avatars/tn/".($this->data['avatar'] ?: 'unknown.png');
    }
}