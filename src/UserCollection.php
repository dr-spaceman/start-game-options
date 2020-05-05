<?php

namespace Vgsite;

class UserCollection extends Collection
{
    // public function __construct(array $rows=[], MockMapper $mapper ) {
    //     parent::__construct( $array, $mapper );
    // }
    protected function targetClass()
    { return User::class; }
}
