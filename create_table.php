<?php
require 'includes/db_config.php';
$sql = file_get_contents('database/create_notifications_table.sql');
$pdo->exec($sql);
echo 'Table created successfully!';
?>
