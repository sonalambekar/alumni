<?php
require_once 'includes/db_config.php';

try {
    echo "<h2>Fixing Posts Table</h2>";
    
    // Check if is_active column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM posts LIKE 'is_active'");
    if ($stmt->rowCount() == 0) {
        // Add is_active column
        $pdo->exec("ALTER TABLE posts ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER status");
        echo "<p>✓ Added is_active column to posts table</p>";
    } else {
        echo "<p>✓ is_active column already exists</p>";
    }
    
    // Check if profile_picture column exists in users table
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
    if ($stmt->rowCount() == 0) {
        // Add profile_picture column
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) AFTER role");
        echo "<p>✓ Added profile_picture column to users table</p>";
    } else {
        echo "<p>✓ profile_picture column already exists</p>";
    }
    
    // Check if auth_token column exists in users table
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'auth_token'");
    if ($stmt->rowCount() == 0) {
        // Add auth_token column
        $pdo->exec("ALTER TABLE users ADD COLUMN auth_token VARCHAR(255) AFTER password");
        echo "<p>✓ Added auth_token column to users table</p>";
    } else {
        echo "<p>✓ auth_token column already exists</p>";
    }
    
    echo "<h3 style='color: green;'>✓ All fixes applied successfully!</h3>";
    echo "<p><a href='index.php'>Go to Home</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
