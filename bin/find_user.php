<?php

require_once dirname(__FILE__) . '/../config/bootstrap_scripts.php';

use Vgsite\User;
use Vgsite\UserMapper;

# $argc is an integer variable containing the argument count
# $argv is an array variable containing each argument’s value. The first argument is always the name of your PHP script file

if ($argc !== 3) {
    echo "Usage: php ".__FILE__." <identifier:user_id|username|email> <user_property> (Eg. php ".__FILE__." user_id 123)".PHP_EOL;
    exit(1);
}

$methods = [
    'user_id' => 'findById',
    'username' => 'findByUsername',
    'email' => 'findByEmail',
];

try {
    $mapper = new UserMapper();
    $user = $mapper->{$methods[$argv[1]]}($argv[2]);
    var_dump($user);
} catch (Exception $e) {
    echo $e->getMessage();
}
