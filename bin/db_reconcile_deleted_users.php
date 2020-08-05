<?php

require_once dirname(__FILE__) . '/../config/bootstrap_scripts.php';

use Vgsite\Registry;
use Vgsite\User;
use Vgsite\UserMapper;

# $argc is an integer variable containing the argument count
# $argv is an array variable containing each argument’s value. The first argument is always the name of your PHP script file

if ($argc < 2) {
    echo "Usage: php ".__FILE__." <table> [<[placeholder|deleted]>](Replace User instead of delete)\n";
    exit(1);
}
$table = $argv[1];
if ($argv[2] == "placeholder") $replace = User::PLACEHOLDER_USER;
if ($argv[2] == "deleted") $replace = User::DELETED_USER;

$sql = "SELECT `user_id`, `username` FROM `{$table}` LEFT JOIN `users` using (user_id) ORDER BY `users`.`username` ASC";
$statement = Registry::get('pdo')->prepare($sql);
$statement->execute();
$num_rows = 0;
$user_ids = [];
while ($row = $statement->fetch()) {
    if (empty($row['username']) || empty($row['user_id'])) {
        $num_rows++;
        $user_ids[] = $row['user_id'];
    }
}

if ($replace) {
    $statement = Registry::get('pdo')->prepare("UPDATE {$table} SET `user_id` = {$replace} WHERE `user_id` = ?;");
} else {
    $statement = Registry::get('pdo')->prepare("DELETE FROM {$table} WHERE `user_id` = ?;");
}

foreach (array_unique($user_ids) as $user_id) {
    $statement->execute([$user_id]);
}

echo ($replace ? 'Replaced' : 'Removed') . ' '.$num_rows . PHP_EOL;
echo "It is suggested you execute the following SQL statement manually:" . PHP_EOL;
echo "ALTER TABLE `{$table}` ADD CONSTRAINT `{$table}_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;";