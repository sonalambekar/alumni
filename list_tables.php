<?php
require 'includes/db_config.php';
$q = $pdo->query("SHOW TABLES");
while ($row = $q->fetch(PDO::FETCH_NUM)) {
    echo $row[0] . "\n";
}
?>
