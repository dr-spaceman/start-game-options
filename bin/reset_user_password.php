<?php

require_once dirname(__FILE__) . '/../config/bootstrap_scripts.php';

use Vgsite\User;
use Vgsite\UserMapper;

# $argc is an integer variable containing the argument count
# $argv is an array variable containing each argument’s value. The first argument is always the name of your PHP script file

if ($argc !== 3) {
    echo "Usage: php reset_user_password.php <username> <password>.\n";
    exit(1);
}
$username = $argv[1];
$password = $argv[2];

$mapper = new UserMapper();
$user = $mapper->findByUsername($username);
$user->setPassword($password, true);
$mapper->save($user);

echo sprintf("%s's password changed.", $username) . PHP_EOL;
