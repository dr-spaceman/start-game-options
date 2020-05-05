<?php

namespace Vgsite;

class Userstream
{
    public function __construct()
    {
        $registry = Registry::instance();
        $this->pdo = $registry->get('pdo');
        $this->logger = $registry->get('logger');
    }

    public static function insert($action, $action_type, $user_id)
    {
        $sql = "INSERT INTO stream (`action`, `action_type`, user_id) VALUES (?, ?, ?)";
        $statement = $this->pdo->prepare($sql);
        $statement->execute([$action, $action_type, $user_id]);
    }
}