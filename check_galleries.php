<?php
// Start session and include database configuration
session_start();
require_once 'includes/db_config.php';

echo "<h1>Galleries Table Structure</h1>";

try {
    // Get table structure
    $stmt = $pdo->query("DESCRIBE galleries");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Get row count
    $count = $pdo->query("SELECT COUNT(*) as count FROM galleries")->fetch();
    echo "<h3>Total Galleries: " . $count['count'] . "</h3>";
    
    // Show first few rows
    $galleries = $pdo->query("SELECT * FROM galleries LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($galleries) > 0) {
        echo "<h3>Sample Data (first 5 rows):</h3>";
        echo "<table border='1' cellpadding='5'>";
        // Table header
        echo "<tr>";
        foreach (array_keys($galleries[0]) as $column) {
            echo "<th>" . htmlspecialchars($column) . "</th>";
        }
        echo "</tr>";
        // Table rows
        foreach ($galleries as $gallery) {
            echo "<tr>";
            foreach ($gallery as $value) {
                echo "<td>" . htmlspecialchars(substr($value, 0, 50)) . (strlen($value) > 50 ? '...' : '') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No galleries found in the table.</p>";
    }
    
} catch (PDOException $e) {
    echo "<h3 style='color:red;'>Error:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
?>
