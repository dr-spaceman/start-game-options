<?php

namespace Vgsite;

use Vgsite\User;

class Session {
    public function login(string $username, string $password): bool
    {
        return true;
    }

    public function logout()
    {

    }
}