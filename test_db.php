<?php
// Simple database connection test
try {
    $pdo = new PDO("mysql:host=localhost;dbname=alumni", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection successful!";
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
