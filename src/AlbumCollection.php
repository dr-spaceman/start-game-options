<?php

namespace Vgsite;

class AlbumCollection extends Collection
{
    protected function targetClass()
    {
        return Album::class;
    }
}
