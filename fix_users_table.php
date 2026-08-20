<?php
require_once 'includes/db_config.php';

try {
    echo "<h2>Fixing Users Table</h2>";
    
    // Get current columns
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Current columns:</strong> " . implode(', ', $columns) . "</p>";
    
    // Add missing columns
    $columnsToAdd = [
        'phone' => "VARCHAR(20) AFTER email_id",
        'batch' => "VARCHAR(50) AFTER phone",
        'department' => "VARCHAR(100) AFTER batch",
        'profile_picture' => "VARCHAR(255) AFTER department",
        'auth_token' => "VARCHAR(255) AFTER password"
    ];
    
    foreach ($columnsToAdd as $column => $definition) {
        if (!in_array($column, $columns)) {
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN $column $definition");
                echo "<p>✓ Added $column column</p>";
            } catch (PDOException $e) {
                echo "<p style='color: orange;'>⚠ Could not add $column: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p>✓ $column column already exists</p>";
        }
    }
    
    echo "<h3 style='color: green;'>✓ Users table fixed!</h3>";
    echo "<p><a href='api/users/discover.php'>Test User Discovery API</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
