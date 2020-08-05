<?php

require_once dirname(__FILE__) . '/../config/bootstrap_scripts.php';

use Vgsite\User;
use Vgsite\UserMapper;

# $argc is an integer variable containing the argument count
# $argv is an array variable containing each argument’s value. The first argument is always the name of your PHP script file

if ($argc !== 4) {
    echo "Usage: php ".__FILE__." <username> <password> <email>".PHP_EOL;
    exit(1);
}

$props = [
    'user_id' => -1,
    'username' => $argv[1],
    'password' => $argv[2],
    'email' => $argv[3],
];

try {
    $user = new User($props);
    $user->hashPassword();
    $mapper = new UserMapper();
    $user = $mapper->insert($user);

    echo "{$username} created; user_id: {$user->getId()}" . PHP_EOL;
} catch (Exception $e) {
    echo $e->getMessage();
}
