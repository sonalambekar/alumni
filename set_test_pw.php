<?php
$pdo = new PDO('mysql:host=localhost;dbname=alumni;charset=utf8mb4', 'root', '1234');
$hash = password_hash('password123', PASSWORD_BCRYPT);
$pdo->exec("UPDATE users SET password = '$hash' WHERE usn = 'TEST001'");
echo "Password updated!\n";
