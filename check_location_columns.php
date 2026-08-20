<?php
require_once 'includes/db_config.php';

try {
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Users Table Columns</h2>";
    echo "<pre>" . print_r($columns, true) . "</pre>";
    
    $hasLatitude = in_array('latitude', $columns);
    $hasLongitude = in_array('longitude', $columns);
    
    echo "<h3>Location Columns:</h3>";
    echo "<p>Latitude: " . ($hasLatitude ? "✓ EXISTS" : "✗ MISSING") . "</p>";
    echo "<p>Longitude: " . ($hasLongitude ? "✓ EXISTS" : "✗ MISSING") . "</p>";
    
    if (!$hasLatitude || !$hasLongitude) {
        echo "<h3>Adding missing columns...</h3>";
        
        if (!$hasLatitude) {
            $pdo->exec("ALTER TABLE users ADD COLUMN latitude DECIMAL(10, 8) NULL");
            echo "<p>✓ Added latitude column</p>";
        }
        
        if (!$hasLongitude) {
            $pdo->exec("ALTER TABLE users ADD COLUMN longitude DECIMAL(11, 8) NULL");
            echo "<p>✓ Added longitude column</p>";
        }
        
        echo "<p style='color: green;'>✓ Location columns ready!</p>";
    } else {
        echo "<p style='color: green;'>✓ All location columns exist!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
