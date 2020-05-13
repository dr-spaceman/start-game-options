<?php

namespace Vgsite;

class PrivateMessage
{
    public function __construct()
    {}

    public static function checkForNew(User $user): int
    {
        $pdo = Registry::get('pdo');
        $sql = "SELECT count(1) FROM pm WHERE `to`=? AND `read`='0'";
        $statement = $pdo->prepare($sql);
        $statement->execute([$user->getId()]);
        return $statement->fetchColumn();
    }
}