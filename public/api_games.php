<?php

require_once dirname(__FILE__) . '/../config/bootstrap.php';

$query = filter_input(INPUT_GET, 'query');

$rows = [];
$sql = sprintf(
    "SELECT * FROM pages_games WHERE `release` IS NOT NULL %s ORDER BY `release`", 
    ($query ? "AND `title` LIKE CONCAT('%', :query, '%')" : "")
);
$statement = $pdo->prepare($sql);
$statement->execute(['query' => $query]);
while ($row = $statement->fetch()) {
    $rows[] = $row;
}

echo json_encode($rows);